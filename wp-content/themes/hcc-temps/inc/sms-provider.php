<?php
/**
 * Two Factor plugin provider: the 6-digit login code by text message (Twilio).
 * Loaded by the Two Factor plugin through the two_factor_providers filter in inc/sms.php.
 * Email stays available on the same screen as the backup.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Two_Factor_Provider' ) || class_exists( 'HCC_Two_Factor_SMS' ) ) {
	return;
}

class HCC_Two_Factor_SMS extends Two_Factor_Provider {

	const META     = '_hcc_sms_code';
	const SENDS    = '_hcc_sms_sends';
	const TTL      = 600;  // a code works for 10 minutes
	const MAX_SEND = 5;    // texts per hour per person
	const RESEND   = 'hcc-sms-resend';

	public function get_label() {
		return 'Text message (SMS)';
	}

	public function get_alternative_provider_label() {
		return 'Send the code by text message instead';
	}

	public function is_available_for_user( $user ) {
		return $user instanceof WP_User && hcc_sms_codes_on() && '' !== hcc_sms_user_phone( $user->ID );
	}

	private function has_live_code( $user_id ) {
		$c = get_user_meta( $user_id, self::META, true );
		return is_array( $c ) && ! empty( $c['hash'] ) && time() - (int) $c['at'] < self::TTL;
	}

	/** True when the code was texted; otherwise a message to show. */
	private function send_code( $user ) {
		$sends = array_filter( (array) get_user_meta( $user->ID, self::SENDS, true ), function ( $t ) { return (int) $t > time() - HOUR_IN_SECONDS; } );
		if ( count( $sends ) >= self::MAX_SEND ) {
			return 'We have sent a lot of codes to your phone in the last hour. Please use the email option below.';
		}
		$code = '';
		for ( $i = 0; $i < 6; $i++ ) {
			$code .= (string) random_int( 0, 9 );
		}
		$aid = in_array( 'hcc_candidate', (array) $user->roles, true ) ? hcc_applicant_for_user( $user->ID ) : 0;
		$r   = hcc_sms_send( hcc_sms_user_phone( $user->ID ), 'Your HCC-Temps login code is ' . substr( $code, 0, 3 ) . ' ' . substr( $code, 3 ) . '. It works for 10 minutes. Never share this code.', 'code', $aid );
		if ( true !== $r ) {
			delete_user_meta( $user->ID, self::META );
			return 'We couldn\'t send a text message just now. Please use the email option below to get your code.';
		}
		$sends[] = time();
		update_user_meta( $user->ID, self::SENDS, array_values( $sends ) );
		update_user_meta( $user->ID, self::META, array( 'hash' => wp_hash( $code . '|' . $user->ID ), 'at' => time() ) );
		return true;
	}

	public function authentication_page( $user ) {
		if ( ! ( $user instanceof WP_User ) ) {
			return;
		}
		$sent = true;
		if ( ! $this->has_live_code( $user->ID ) ) {
			$sent = $this->send_code( $user );
		}
		$phone = hcc_sms_user_phone( $user->ID );
		require_once ABSPATH . '/wp-admin/includes/template.php';
		do_action( 'two_factor_before_authentication_prompt', $this );
		if ( true === $sent ) {
			echo '<p class="two-factor-prompt">We\'ve sent a 6-digit code by text message to your mobile ending <b>' . esc_html( substr( $phone, -3 ) ) . '</b>.</p>';
		} else {
			echo '<p class="two-factor-prompt hcc-sms-fail">' . esc_html( $sent ) . '</p>';
		}
		do_action( 'two_factor_after_authentication_prompt', $this );
		?>
		<p>
			<label for="authcode">Login code</label>
			<input type="text" inputmode="numeric" name="hcc-sms-code" id="authcode" class="input authcode" value="" size="20" pattern="[0-9 ]*" autocomplete="one-time-code" placeholder="123 456" data-digits="6">
		</p>
		<?php
		do_action( 'two_factor_after_authentication_input', $this );
		submit_button( 'Log in' );
		?>
		<p class="two-factor-email-resend"><input type="submit" class="button" name="<?php echo esc_attr( self::RESEND ); ?>" value="Text me a new code"></p>
		<?php
		wp_enqueue_script( 'two-factor-login' );
	}

	public function pre_process_authentication( $user ) {
		if ( $user instanceof WP_User && isset( $_REQUEST[ self::RESEND ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			delete_user_meta( $user->ID, self::META );
			$this->send_code( $user );
			return true;
		}
		return false;
	}

	public function validate_authentication( $user ) {
		if ( ! ( $user instanceof WP_User ) || empty( $_REQUEST['hcc-sms-code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}
		$code = preg_replace( '/\D/', '', (string) wp_unslash( $_REQUEST['hcc-sms-code'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$c    = get_user_meta( $user->ID, self::META, true );
		if ( 6 !== strlen( $code ) || ! $this->has_live_code( $user->ID ) || ! hash_equals( (string) $c['hash'], wp_hash( $code . '|' . $user->ID ) ) ) {
			return false;
		}
		delete_user_meta( $user->ID, self::META );
		return true;
	}

	public static function uninstall_user_meta_keys() {
		return array( self::META, self::SENDS );
	}
}
