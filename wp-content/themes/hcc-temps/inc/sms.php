<?php
/**
 * Text messages (SMS) through Twilio.
 *
 * - Login codes: candidates (and staff who add a mobile) get their 6-digit login code by text,
 *   with "send the code by email instead" as the backup on the same screen.
 * - Short alerts next to the emails: an update has been sent, documents are ready to sign,
 *   a certificate is about to expire. Candidates can turn alerts off in their profile.
 *
 * Settings: Users → Text messages (SMS). The Twilio details can also be put in wp-config.php
 * (HCC_TWILIO_SID, HCC_TWILIO_TOKEN, HCC_TWILIO_FROM), which then take priority.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Settings ---------- */

function hcc_sms_opts() {
	return wp_parse_args( (array) get_option( 'hcc_sms', array() ), array( 'codes' => 0, 'staff' => 0, 'alerts' => 0, 'sid' => '', 'from' => '', 'token' => '', 'verify' => '' ) );
}

function hcc_sms_secret_key() {
	return sodium_crypto_generichash( 'hcc-sms|' . hcc_crypto_key(), '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
}

/** The Auth Token is kept encrypted in the database (with the site's file key). */
function hcc_sms_seal( $plain ) {
	if ( ! function_exists( 'sodium_crypto_secretbox' ) || ! function_exists( 'hcc_crypto_key' ) ) {
		return 'p:' . base64_encode( $plain ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}
	$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
	return 's:' . base64_encode( $nonce . sodium_crypto_secretbox( $plain, $nonce, hcc_sms_secret_key() ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
}

function hcc_sms_unseal( $sealed ) {
	$sealed = (string) $sealed;
	if ( 0 === strpos( $sealed, 'p:' ) ) {
		return (string) base64_decode( substr( $sealed, 2 ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}
	if ( 0 !== strpos( $sealed, 's:' ) || ! function_exists( 'sodium_crypto_secretbox_open' ) ) {
		return '';
	}
	$raw = base64_decode( substr( $sealed, 2 ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	if ( strlen( $raw ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) {
		return '';
	}
	$out = sodium_crypto_secretbox_open( substr( $raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ), substr( $raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ), hcc_sms_secret_key() );
	return false === $out ? '' : $out;
}

/** Twilio details: wp-config.php first, then the settings page. */
function hcc_sms_creds() {
	$o = hcc_sms_opts();
	return array(
		'sid'   => defined( 'HCC_TWILIO_SID' ) && HCC_TWILIO_SID ? (string) HCC_TWILIO_SID : $o['sid'],
		'token' => defined( 'HCC_TWILIO_TOKEN' ) && HCC_TWILIO_TOKEN ? (string) HCC_TWILIO_TOKEN : hcc_sms_unseal( $o['token'] ),
		'from'  => defined( 'HCC_TWILIO_FROM' ) && HCC_TWILIO_FROM ? (string) HCC_TWILIO_FROM : $o['from'],
	);
}

function hcc_sms_ready() {
	$c = hcc_sms_creds();
	return $c['sid'] && $c['token'] && $c['from'];
}

/** Login codes need the account details and either Twilio Verify or a sender number. */
function hcc_sms_codes_on() {
	$c = hcc_sms_creds();
	return $c['sid'] && $c['token'] && ( hcc_sms_verify_sid() || $c['from'] ) && hcc_sms_opts()['codes'];
}

function hcc_sms_alerts_on() {
	return hcc_sms_ready() && hcc_sms_opts()['alerts'];
}

/* ---------- Phone numbers ---------- */

/** UK numbers to +44…, others must start with +. Returns '' when it is not a usable mobile number. */
function hcc_sms_e164( $n ) {
	$n = preg_replace( '/[^\d+]/', '', (string) $n );
	if ( 0 === strpos( $n, '00' ) ) {
		$n = '+' . substr( $n, 2 );
	}
	if ( preg_match( '/^0(7\d{9})$/', $n, $m ) ) {
		$n = '+44' . $m[1];
	} elseif ( preg_match( '/^44(7\d{9})$/', $n, $m ) ) {
		$n = '+44' . $m[1];
	} elseif ( preg_match( '/^7\d{9}$/', $n ) ) {
		$n = '+44' . $n;
	}
	if ( preg_match( '/^\+44/', $n ) ) {
		return preg_match( '/^\+447\d{9}$/', $n ) ? $n : ''; // UK: mobiles only
	}
	return preg_match( '/^\+[1-9]\d{9,14}$/', $n ) ? $n : '';
}

function hcc_sms_mask( $n ) {
	return strlen( $n ) > 7 ? substr( $n, 0, 4 ) . str_repeat( '•', strlen( $n ) - 7 ) . substr( $n, -3 ) : $n;
}

/** The mobile number used for a WordPress user's login codes. */
function hcc_sms_user_phone( $user_id ) {
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return '';
	}
	if ( in_array( 'hcc_candidate', (array) $user->roles, true ) ) {
		$aid = hcc_applicant_for_user( $user_id );
		return $aid ? hcc_sms_e164( hcc_meta( $aid, 'phone' ) ) : '';
	}
	if ( array_intersect( array( 'hcc_staff', 'hcc_manager', 'administrator' ), (array) $user->roles ) && hcc_sms_opts()['staff'] ) {
		return hcc_sms_e164( get_user_meta( $user_id, 'hcc_mobile', true ) );
	}
	return '';
}

/* ---------- Sending ---------- */

/**
 * Send one text message. Returns true, or a WP_Error with Twilio's reason.
 *
 * @param string $to   Number in any common format.
 * @param string $body Message text.
 * @param string $kind Short label for the log (code, test, update, sign, expiry).
 * @param int    $aid  Candidate record, for their activity log.
 */
function hcc_sms_send( $to, $body, $kind = 'alert', $aid = 0 ) {
	$c  = hcc_sms_creds();
	$to = hcc_sms_e164( $to );
	if ( ! $c['sid'] || ! $c['token'] || ! $c['from'] ) {
		$r = new WP_Error( 'hcc_sms_setup', 'Text messages are not set up yet.' );
	} elseif ( ! $to ) {
		$r = new WP_Error( 'hcc_sms_number', 'That is not a mobile number we can text.' );
	} else {
		$r = hcc_twilio_post( 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode( $c['sid'] ) . '/Messages.json', array( 'To' => $to, 'From' => $c['from'], 'Body' => $body ) );
		$r = is_wp_error( $r ) ? $r : true;
	}
	hcc_sms_done( $to, $kind, $r, $aid );
	return $r;
}

/** POST to Twilio. Returns the decoded reply, or a WP_Error with a plain-English reason. */
function hcc_twilio_post( $url, $body ) {
	$c   = hcc_sms_creds();
	$res = wp_remote_post( $url, array(
		'timeout' => 12,
		'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $c['sid'] . ':' . $c['token'] ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		'body'    => $body,
	) );
	if ( is_wp_error( $res ) ) {
		return new WP_Error( 'hcc_sms_net', 'Could not reach Twilio: ' . $res->get_error_message() );
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$json = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( $code >= 200 && $code < 300 ) {
		return is_array( $json ) ? $json : array();
	}
	$tc  = isset( $json['code'] ) ? (int) $json['code'] : 0;
	$why = isset( $json['message'] ) ? $json['message'] : 'Error ' . $code;
	if ( 21608 === $tc || 21219 === $tc ) {
		$why = 'This number is not verified on the Twilio trial account. Add it in Twilio under Phone Numbers → Verified Caller IDs, or upgrade the account.';
	} elseif ( 20003 === $tc ) {
		$why = 'Twilio did not accept the Account SID or Auth Token. Check them on this page.';
	} elseif ( 20404 === $tc && false !== strpos( $url, 'verify.twilio.com' ) ) {
		$why = 'Twilio could not find the Verify Service SID. Check it on this page (it starts with VA).';
	} elseif ( 60203 === $tc ) {
		$why = 'Too many codes sent to this number. Wait 10 minutes or use the email option.';
	} elseif ( false !== stripos( $why, 'template' ) ) {
		$why = 'Twilio trial accounts can only send Twilio\'s own preset messages. Login codes work through Twilio Verify (add the Verify Service SID on this page). Alerts and test messages need the account to be upgraded.';
	}
	return new WP_Error( 'hcc_sms_twilio', $why );
}

/** Log a text in the settings page list and the candidate's activity log. */
function hcc_sms_done( $to, $kind, $r, $aid ) {
	hcc_sms_log( $to, $kind, $r );
	if ( $aid ) {
		$labels = array( 'code' => 'login code', 'update' => 'update alert', 'sign' => 'documents ready to sign', 'expiry' => 'expiry reminder', 'test' => 'test message' );
		$lab    = isset( $labels[ $kind ] ) ? $labels[ $kind ] : $kind;
		hcc_log( $aid, true === $r ? 'Text message sent: ' . $lab : 'Text message not sent (' . $lab . '): ' . $r->get_error_message(), 'System' );
	}
}

/* ---------- Twilio Verify (login codes; works on trial accounts) ---------- */

function hcc_sms_verify_sid() {
	return defined( 'HCC_TWILIO_VERIFY' ) && HCC_TWILIO_VERIFY ? (string) HCC_TWILIO_VERIFY : hcc_sms_opts()['verify'];
}

/** Ask Twilio Verify to text a code. True or WP_Error. */
function hcc_sms_verify_start( $to, $aid = 0, $kind = 'code' ) {
	$to = hcc_sms_e164( $to );
	if ( ! $to ) {
		$r = new WP_Error( 'hcc_sms_number', 'That is not a mobile number we can text.' );
	} else {
		$r = hcc_twilio_post( 'https://verify.twilio.com/v2/Services/' . rawurlencode( hcc_sms_verify_sid() ) . '/Verifications', array( 'To' => $to, 'Channel' => 'sms' ) );
		$r = is_wp_error( $r ) ? $r : true;
	}
	hcc_sms_done( $to, $kind, $r, $aid );
	return $r;
}

/** Check a code with Twilio Verify. */
function hcc_sms_verify_check( $to, $code ) {
	$r = hcc_twilio_post( 'https://verify.twilio.com/v2/Services/' . rawurlencode( hcc_sms_verify_sid() ) . '/VerificationCheck', array( 'To' => hcc_sms_e164( $to ), 'Code' => $code ) );
	return ! is_wp_error( $r ) && isset( $r['status'] ) && 'approved' === $r['status'];
}

/** The last 40 messages for the settings page (never the message text, so no codes are kept). */
function hcc_sms_log( $to, $kind, $r ) {
	$log   = (array) get_option( 'hcc_sms_log', array() );
	$log[] = array( 'at' => time(), 'to' => hcc_sms_mask( $to ), 'kind' => $kind, 'ok' => true === $r, 'err' => true === $r ? '' : $r->get_error_message() );
	update_option( 'hcc_sms_log', array_slice( $log, -40 ), false );
}

/* ---------- Login codes (Two Factor plugin) ---------- */

add_filter( 'two_factor_providers', function ( $providers ) {
	$providers['HCC_Two_Factor_SMS'] = __DIR__ . '/sms-provider.php';
	return $providers;
} );

add_filter( 'two_factor_enabled_providers_for_user', function ( $providers, $user_id ) {
	if ( hcc_sms_codes_on() && hcc_sms_user_phone( $user_id ) ) {
		$providers = (array) $providers;
		if ( ! in_array( 'HCC_Two_Factor_SMS', $providers, true ) ) {
			$providers[] = 'HCC_Two_Factor_SMS';
		}
		if ( ! in_array( 'Two_Factor_Email', $providers, true ) ) {
			$providers[] = 'Two_Factor_Email'; // email stays as the backup
		}
	}
	return $providers;
}, 20, 2 );

add_filter( 'two_factor_primary_provider_for_user', function ( $provider, $user_id ) {
	if ( hcc_sms_codes_on() && hcc_sms_user_phone( $user_id ) && class_exists( 'HCC_Two_Factor_SMS' ) ) {
		return 'HCC_Two_Factor_SMS';
	}
	return $provider;
}, 10, 2 );


/* ---------- Alerts ---------- */

function hcc_sms_alert( $aid, $kind, $text ) {
	if ( ! hcc_sms_alerts_on() || hcc_meta( $aid, 'sms_off' ) ) {
		return false;
	}
	$to = hcc_sms_e164( hcc_meta( $aid, 'phone' ) );
	if ( ! $to ) {
		return false;
	}
	return hcc_sms_send( $to, $text, $kind, $aid );
}

function hcc_sms_short_portal( $hash = '' ) {
	return preg_replace( '#^https?://#', '', untrailingslashit( hcc_portal_url() ) ) . '/' . $hash;
}

add_action( 'hcc_update_sent', function ( $aid ) {
	hcc_sms_alert( $aid, 'update', 'HCC-Temps: Hi ' . hcc_meta( $aid, 'first' ) . ', there is an update on your registration. We have emailed you the details, or log in: ' . hcc_sms_short_portal() );
} );

add_action( 'hcc_pack_released', function ( $aid ) {
	hcc_sms_alert( $aid, 'sign', 'HCC-Temps: Hi ' . hcc_meta( $aid, 'first' ) . ', your onboarding documents are ready to sign (about 10 minutes): ' . hcc_sms_short_portal( '#sign' ) );
} );

add_action( 'hcc_expiry_reminded', function ( $aid, $due ) {
	$first = reset( $due );
	hcc_sms_alert( $aid, 'expiry', 'HCC-Temps: ' . ( count( $due ) > 1 ? 'Some of your certificates are expiring' : $first[0] ) . '. Please upload the new one in your portal: ' . hcc_sms_short_portal( '#training' ) );
}, 10, 2 );

/* Candidate: turn text alerts on or off (login codes are not affected). */
add_action( 'admin_post_hcc_sms_pref', function () {
	$aid = hcc_applicant_for_user( get_current_user_id() );
	if ( ! $aid || ! check_admin_referer( 'hcc_sms_pref' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$on = ! empty( $_POST['sms_on'] ); // phpcs:ignore WordPress.Security.NonceVerification
	hcc_set( $aid, 'sms_off', $on ? '' : time() );
	hcc_log( $aid, $on ? 'Turned text message alerts on' : 'Turned text message alerts off', 'Candidate' );
	wp_safe_redirect( add_query_arg( 'ok', 'sms', hcc_portal_url() ) . '#profile' );
	exit;
} );

function hcc_sms_pref_card( $aid ) {
	if ( ! hcc_sms_alerts_on() ) {
		return;
	}
	$to = hcc_sms_e164( hcc_meta( $aid, 'phone' ) );
	echo '<div class="p-card" id="sms-pref"><div class="p-card-head"><h2>Text message alerts</h2></div>';
	if ( ! $to ) {
		echo '<p class="hint">We can\'t text the mobile number on your profile (' . esc_html( hcc_meta( $aid, 'phone' ) ) . '). Send us a message with your mobile number if you would like text alerts.</p></div>';
		return;
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="hcc_sms_pref">' . wp_nonce_field( 'hcc_sms_pref', '_wpnonce', true, false ); // phpcs:ignore
	echo '<label class="consent"><input type="checkbox" name="sms_on" value="1"' . checked( ! hcc_meta( $aid, 'sms_off' ), true, false ) . '> Text me at <b>' . esc_html( hcc_meta( $aid, 'phone' ) ) . '</b> when there is an update, documents to sign or a certificate about to expire. Emails still come as usual.</label>';
	echo '<div class="form-actions" style="margin-top:10px"><button class="btn btn-ghost btn-sm" type="submit">Save</button></div></form></div>';
}

/* ---------- Staff mobile (for staff login codes) ---------- */

$hcc_sms_profile = function ( $user ) {
	if ( ! array_intersect( array( 'hcc_staff', 'hcc_manager', 'administrator' ), (array) $user->roles ) ) {
		return;
	}
	echo '<h2>HCC-Temps login codes</h2><table class="form-table"><tr><th><label for="hcc_mobile">Mobile number</label></th><td><input type="tel" name="hcc_mobile" id="hcc_mobile" class="regular-text" value="' . esc_attr( get_user_meta( $user->ID, 'hcc_mobile', true ) ) . '"><p class="description">Used for login codes by text when "Staff get login codes by text" is on (Users → Text messages).</p></td></tr></table>';
};
add_action( 'show_user_profile', $hcc_sms_profile );
add_action( 'edit_user_profile', $hcc_sms_profile );
$hcc_sms_profile_save = function ( $user_id ) {
	if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['hcc_mobile'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		update_user_meta( $user_id, 'hcc_mobile', sanitize_text_field( wp_unslash( $_POST['hcc_mobile'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
};
add_action( 'personal_options_update', $hcc_sms_profile_save );
add_action( 'edit_user_profile_update', $hcc_sms_profile_save );

/* ---------- Settings page: Users → Text messages (SMS) ---------- */

add_action( 'admin_menu', function () {
	add_users_page( 'Text messages (SMS)', 'Text messages (SMS)', 'manage_options', 'hcc-sms', 'hcc_sms_page' );
} );

function hcc_sms_page() {
	$o   = hcc_sms_opts();
	$c   = hcc_sms_creds();
	$cfg = defined( 'HCC_TWILIO_SID' ) && HCC_TWILIO_SID;
	$m   = isset( $_GET['m'] ) ? sanitize_key( $_GET['m'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	echo '<div class="wrap"><h1>Text messages (SMS)</h1>';
	if ( 'saved' === $m ) {
		echo '<div class="notice notice-success"><p>Saved.</p></div>';
	} elseif ( 'sent' === $m ) {
		echo '<div class="notice notice-success"><p>Sent. It should arrive within a few seconds.</p></div>';
	} elseif ( 'fail' === $m ) {
		$log = (array) get_option( 'hcc_sms_log', array() );
		$l   = end( $log );
		echo '<div class="notice notice-error"><p><b>The test message was not sent.</b> ' . esc_html( $l ? $l['err'] : '' ) . '</p></div>';
	}
	echo '<p>Login codes and short alerts by text message, sent through your <a href="https://console.twilio.com/" target="_blank" rel="noopener">Twilio</a> account. Emails keep going as usual: texts are sent as well, never instead.</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="hcc_sms_save">';
	wp_nonce_field( 'hcc_sms_save' );
	echo '<h2>Twilio account</h2>';
	if ( $cfg ) {
		echo '<p>The Twilio details come from <code>wp-config.php</code> (HCC_TWILIO_SID, HCC_TWILIO_TOKEN, HCC_TWILIO_FROM). Change them there.</p><table class="form-table"><tr><th>Account SID</th><td><code>' . esc_html( substr( $c['sid'], 0, 6 ) . '…' ) . '</code></td></tr><tr><th>Sender</th><td><code>' . esc_html( $c['from'] ) . '</code></td></tr><tr><th>Verify Service SID</th><td><code>' . esc_html( hcc_sms_verify_sid() ? substr( hcc_sms_verify_sid(), 0, 6 ) . '…' : 'not set' ) . '</code></td></tr></table>';
	} else {
		echo '<table class="form-table">';
		echo '<tr><th><label for="sid">Account SID</label></th><td><input type="text" id="sid" name="sid" class="regular-text" value="' . esc_attr( $o['sid'] ) . '" placeholder="AC…" autocomplete="off"><p class="description">Twilio Console home page, under Account Info.</p></td></tr>';
		echo '<tr><th><label for="token">Auth Token</label></th><td><input type="password" id="token" name="token" class="regular-text" value="" placeholder="' . ( $o['token'] ? '●●●●●●●● saved: leave empty to keep it' : '32 letters and numbers' ) . '" autocomplete="new-password"><p class="description">Kept encrypted on the website. To change it, paste the new one; leave empty to keep the saved one.</p></td></tr>';
		echo '<tr><th><label for="from">Send from</label></th><td><input type="text" id="from" name="from" class="regular-text" value="' . esc_attr( $o['from'] ) . '" placeholder="+447460077297"><p class="description">Your Twilio phone number with +44, or later a sender name of up to 11 letters (for example HCCTemps) once the account is upgraded.</p></td></tr>';
		echo '<tr><th><label for="verify">Verify Service SID</label></th><td><input type="text" id="verify" name="verify" class="regular-text" value="' . esc_attr( $o['verify'] ) . '" placeholder="VA…" autocomplete="off"><p class="description">Used for login codes. In Twilio: Explore products → Verify → Services → Create new (name it HCC-Temps, tick SMS), then copy the Service SID. <b>Needed on a trial account</b>, which only allows Twilio\'s own preset messages.</p></td></tr>';
		echo '</table>';
	}
	echo '<h2>What to send</h2><table class="form-table">';
	echo '<tr><th>Login codes</th><td><label><input type="checkbox" name="codes" value="1"' . checked( $o['codes'], 1, false ) . '> Candidates get their 6-digit login code by text</label><p class="description">They can choose "send the code by email instead" on the same screen, so nobody is locked out if a text does not arrive.</p>';
	echo '<label style="display:block;margin-top:8px"><input type="checkbox" name="staff" value="1"' . checked( $o['staff'], 1, false ) . '> Staff get login codes by text too</label><p class="description">Only staff who have added their mobile number in their WordPress profile (Users → Profile). Everyone else keeps email codes.</p></td></tr>';
	echo '<tr><th>Alerts</th><td><label><input type="checkbox" name="alerts" value="1"' . checked( $o['alerts'], 1, false ) . '> Text candidates when an update is sent, when documents are ready to sign, and when a certificate is about to expire</label><p class="description">Candidates can turn alerts off in their portal profile.</p></td></tr>';
	echo '</table>';
	submit_button( 'Save' );
	echo '</form>';

	echo '<h2>Test it</h2>';
	$c_ok = hcc_sms_creds();
	if ( ! $c_ok['sid'] || ! $c_ok['token'] ) {
		echo '<p>Add the Account SID and Auth Token above first.</p>';
	} else {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="hcc_sms_test">';
		wp_nonce_field( 'hcc_sms_test' );
		echo '<p><input type="tel" name="to" class="regular-text" placeholder="07700 900123" required> ';
		if ( hcc_sms_verify_sid() ) {
			echo '<button class="button button-primary" name="kind" value="verify">Send a test login code</button> ';
		}
		if ( $c_ok['from'] ) {
			echo '<button class="button" name="kind" value="message">Send a test message</button>';
		}
		echo '</p><p class="description">On a Twilio trial account the number must be verified first in Twilio (Phone Numbers → Verified Caller IDs). Trial accounts can send login codes (through Verify) but not our own messages, so "Send a test message" and the alerts only work once the account is upgraded.</p></form>';
	}

	$log = array_reverse( (array) get_option( 'hcc_sms_log', array() ) );
	echo '<h2>Recent messages</h2>';
	if ( ! $log ) {
		echo '<p>None yet.</p>';
	} else {
		echo '<table class="widefat striped" style="max-width:860px"><thead><tr><th>When</th><th>To</th><th>What</th><th>Result</th></tr></thead><tbody>';
		foreach ( array_slice( $log, 0, 20 ) as $l ) {
			echo '<tr><td>' . esc_html( wp_date( 'j M, H:i', (int) $l['at'] ) ) . '</td><td>' . esc_html( $l['to'] ) . '</td><td>' . esc_html( $l['kind'] ) . '</td><td>' . ( $l['ok'] ? '<span style="color:#17694A">Sent</span>' : '<span style="color:#B3261E">' . esc_html( $l['err'] ) . '</span>' ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
	echo '</div>';
}

add_action( 'admin_post_hcc_sms_save', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	check_admin_referer( 'hcc_sms_save' );
	$o = hcc_sms_opts();
	$p = function ( $k ) { return isset( $_POST[ $k ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ) : ''; }; // phpcs:ignore WordPress.Security.NonceVerification
	foreach ( array( 'codes', 'staff', 'alerts' ) as $k ) {
		$o[ $k ] = $p( $k ) ? 1 : 0;
	}
	if ( isset( $_POST['sid'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$o['sid']    = preg_replace( '/[^A-Za-z0-9]/', '', $p( 'sid' ) );
		$o['verify'] = preg_replace( '/[^A-Za-z0-9]/', '', $p( 'verify' ) );
		$from      = $p( 'from' );
		$o['from'] = preg_match( '/^[A-Za-z][A-Za-z0-9 ]{0,10}$/', $from ) ? $from : ( hcc_sms_e164( $from ) ? hcc_sms_e164( $from ) : preg_replace( '/[^\d+]/', '', $from ) );
		$tok       = preg_replace( '/\s+/', '', $p( 'token' ) );
		if ( '' !== $tok ) {
			$o['token'] = hcc_sms_seal( $tok );
		}
	}
	update_option( 'hcc_sms', $o, false );
	wp_safe_redirect( admin_url( 'users.php?page=hcc-sms&m=saved' ) );
	exit;
} );

add_action( 'admin_post_hcc_sms_test', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	check_admin_referer( 'hcc_sms_test' );
	$to = isset( $_POST['to'] ) ? sanitize_text_field( wp_unslash( $_POST['to'] ) ) : '';
	$r  = isset( $_POST['kind'] ) && 'verify' === $_POST['kind'] && hcc_sms_verify_sid() ? hcc_sms_verify_start( $to, 0, 'test code' ) : hcc_sms_send( $to, 'HCC-Temps: this is a test message from your website. Text messages are working.', 'test' ); // phpcs:ignore WordPress.Security.NonceVerification
	wp_safe_redirect( admin_url( 'users.php?page=hcc-sms&m=' . ( true === $r ? 'sent' : 'fail' ) ) );
	exit;
} );
