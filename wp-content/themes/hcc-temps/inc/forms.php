<?php
/**
 * Shared form helpers, the contact form and client cover requests.
 * Candidate registration lives in inc/register.php.
 *
 * Every form posts to admin-post.php with a nonce, a honeypot field and a
 * per-visitor rate limit, then redirects back with a status in the URL.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Shared helpers ---------- */

function hcc_field( $key, $type = 'text' ) {
	if ( ! isset( $_POST[ $key ] ) ) {
		return '';
	}
	$v = wp_unslash( $_POST[ $key ] );
	if ( 'email' === $type ) {
		return sanitize_email( $v );
	}
	return 'textarea' === $type ? sanitize_textarea_field( $v ) : sanitize_text_field( $v );
}

/** Back to the page the form was on, with status args and an anchor. */
function hcc_back( $args, $anchor = '' ) {
	$ref = wp_get_referer();
	$url = $ref ? remove_query_arg( array( 'hcc_ok', 'hcc_err', 'ref', 't', 'saved' ), $ref ) : home_url( '/' );
	$url = preg_replace( '/#.*$/', '', $url );
	wp_safe_redirect( add_query_arg( $args, $url ) . ( $anchor ? '#' . $anchor : '' ) );
	exit;
}

function hcc_guard( $action, $anchor, $limit = 8 ) {
	if ( ! isset( $_POST['hcc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hcc_nonce'] ) ), $action ) ) {
		hcc_back( array( 'hcc_err' => 'expired' ), $anchor );
	}
	if ( ! empty( $_POST['website'] ) ) { // Honeypot: real visitors never see this field.
		hcc_back( array( 'hcc_ok' => '1' ), $anchor );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'hcc_rl_' . md5( $action . $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= $limit ) {
		hcc_back( array( 'hcc_err' => 'busy' ), $anchor );
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );
}

/** Branded HTML email. */
function hcc_mail( $to, $subject, $body_html, $attachments = array(), $reply_to = '' ) {
	$html = '<div style="font-family:Arial,sans-serif;font-size:15px;line-height:1.55;color:#1E2140;max-width:560px">'
		. '<div style="height:6px;background:linear-gradient(90deg,#7C4925 0 20%,#2D338C 20% 40%,#6DC0B8 40% 60%,#D93831 60% 80%,#F3D45E 80%)"></div>'
		. '<p style="margin:20px 0"><img src="' . esc_url( hcc_logo_url() ) . '" alt="HCC-Temps" width="110" style="height:auto"></p>'
		. $body_html
		. '<p style="margin-top:28px;font-size:13px;color:#5B6078">HCC-Temps · ' . esc_html( hcc_opt( 'phone' ) ) . ' · ' . esc_html( hcc_opt( 'email' ) ) . '</p></div>';
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	if ( $reply_to ) {
		$headers[] = 'Reply-To: ' . $reply_to;
	}
	return wp_mail( $to, $subject, $html, $headers, $attachments );
}

function hcc_button( $url, $label ) {
	return '<p style="margin:22px 0"><a href="' . esc_url( $url ) . '" style="background:#237D74;color:#fff;text-decoration:none;font-weight:bold;padding:13px 22px;border-radius:999px;display:inline-block">' . esc_html( $label ) . '</a></p>';
}

function hcc_rows( $rows ) {
	$out = '<table cellpadding="6" style="border-collapse:collapse;font-size:14px">';
	foreach ( $rows as $k => $v ) {
		if ( '' !== (string) $v ) {
			$out .= '<tr><td style="color:#5B6078;padding-right:16px;vertical-align:top">' . esc_html( $k ) . '</td><td><b>' . nl2br( esc_html( $v ) ) . '</b></td></tr>';
		}
	}
	return $out . '</table>';
}

/* ---------- Candidate reference ---------- */

function hcc_next_ref() {
	$n = (int) get_option( 'hcc_ref_counter', 1000 ) + 1;
	update_option( 'hcc_ref_counter', $n, false );
	return 'HCC-' . str_pad( (string) $n, 6, '0', STR_PAD_LEFT );
}

/* ---------- Contact form: to the staffing inbox, copy to the sender if they ask ---------- */

function hcc_handle_contact() {
	hcc_guard( 'hcc_contact', 'message' );
	$labels = array( 'candidate' => 'Looking for work', 'employer' => 'An employer', 'other' => 'Something else' );
	$who    = isset( $labels[ hcc_field( 'who' ) ] ) ? hcc_field( 'who' ) : 'other';
	$name   = hcc_field( 'name' );
	$email  = hcc_field( 'email', 'email' );
	$msg    = hcc_field( 'message', 'textarea' );
	$org    = hcc_field( 'org' );
	if ( ! $name || ! is_email( $email ) || ! $msg || empty( $_POST['consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		hcc_back( array( 'hcc_err' => 'fields' ), 'message' );
	}
	$kind = array( 'candidate' => 'Candidate enquiry', 'employer' => 'Employer enquiry', 'other' => 'Enquiry' );
	hcc_mail(
		hcc_opt( 'email_jobs' ),
		'[CONTACT] ' . $kind[ $who ] . ' from ' . $name . ( $org ? ', ' . $org : '' ),
		'<p>New message from the website contact form:</p>' . hcc_rows( array( 'I am' => $labels[ $who ], 'Name' => $name, 'Organisation' => $org, 'Email' => $email, 'Phone' => hcc_field( 'phone' ), 'Message' => $msg ) ) . '<p style="font-size:13px;color:#5B6078">Reply to this email to answer them directly.</p>',
		array(),
		$email
	);
	$copy = ! empty( $_POST['copy'] ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( $copy ) {
		hcc_mail( $email, 'We have your message: HCC-Temps', '<p>Hi ' . esc_html( $name ) . ',</p><p>Thank you for contacting HCC-Temps. We reply within one working day.</p><p style="background:#E4F4F2;padding:12px 14px;border-radius:10px">Your message: "' . nl2br( esc_html( $msg ) ) . '"</p><p>For emergency cover, call <b>' . esc_html( hcc_opt( 'phone' ) ) . '</b>, 24 hours a day.</p>', array(), hcc_opt( 'email_jobs' ) );
	}
	hcc_back( array( 'hcc_ok' => $copy ? 'sent_copy' : 'sent' ), 'message' );
}
add_action( 'admin_post_nopriv_hcc_contact', 'hcc_handle_contact' );
add_action( 'admin_post_hcc_contact', 'hcc_handle_contact' );

/* ---------- Client cover request: summary to the staffing inbox, confirmation to the client ---------- */

function hcc_handle_cover() {
	hcc_guard( 'hcc_cover', 'request' );
	$org   = hcc_field( 'org' );
	$name  = hcc_field( 'name' );
	$email = hcc_field( 'email', 'email' );
	$phone = hcc_field( 'phone' );
	$role  = hcc_field( 'role' );
	$num   = max( 1, absint( hcc_field( 'num' ) ) );
	if ( ! $org || ! $name || ! is_email( $email ) || ! $phone ) {
		hcc_back( array( 'hcc_err' => 'fields' ), 'request' );
	}
	$from  = hcc_field( 'from' );
	$notes = hcc_field( 'notes', 'textarea' );
	$what  = $num . ' ' . $role . ( $num > 1 && 's' !== substr( $role, -1 ) ? 's' : '' );
	hcc_mail(
		hcc_opt( 'email_jobs' ),
		'[COVER REQUEST] ' . $org . ' is looking for ' . $what,
		'<p><b>' . esc_html( $org ) . '</b> is looking for the following:</p>'
			. hcc_rows( array( 'Role needed' => $role, 'Number of staff' => $num, 'First shift' => $from ? wp_date( 'D j M Y', strtotime( $from ) ) : '', 'Shift details' => $notes, 'Site postcode' => strtoupper( hcc_field( 'postcode' ) ) ) )
			. '<p style="margin-top:14px"><b>Contact</b></p>'
			. hcc_rows( array( 'Organisation' => $org, 'Name' => $name, 'Email' => $email, 'Phone' => $phone, 'Sent' => wp_date( 'j M Y, H:i' ) ) )
			. '<p style="font-size:13px;color:#5B6078">Reply to this email to answer the client directly.</p>',
		array(),
		$email
	);
	hcc_mail(
		$email,
		'We have your cover request: ' . $role . ' x' . $num,
		'<p>Hi ' . esc_html( $name ) . ',</p><p>Thank you. We have your request for <b>' . esc_html( $what ) . '</b> at ' . esc_html( $org ) . ( $from ? ' from <b>' . esc_html( wp_date( 'D j M', strtotime( $from ) ) ) . '</b>' : '' ) . '. A consultant will call you back shortly to confirm.</p>'
			. '<p>For emergency cover, call <b>' . esc_html( hcc_opt( 'phone' ) ) . '</b>. We answer 24 hours a day and can cover within 2 hours.</p>'
			. ( $notes ? '<p style="font-size:13px;color:#5B6078">Your request: ' . esc_html( $notes ) . '</p>' : '' ),
		array(),
		hcc_opt( 'email_jobs' )
	);
	hcc_back( array( 'hcc_ok' => 'sent' ), 'request' );
}
add_action( 'admin_post_nopriv_hcc_cover', 'hcc_handle_cover' );
add_action( 'admin_post_hcc_cover', 'hcc_handle_cover' );

/** Message shown after a redirect. */
function hcc_notice() {
	$msgs = array(
		'fields'   => 'Please fill in the fields marked as required and tick the consent box.',
		'robot'    => 'Please complete the robot check.',
		'exists'   => 'There is already an account with this email. Please log in instead.',
		'password' => 'Please choose a password of at least 8 characters, and type it the same way twice.',
		'expired'  => 'The form timed out. Please try again.',
		'busy'     => 'Too many attempts. Please wait a while or call us.',
		'server'   => 'Something went wrong. Please try again or call us.',
	);
	$err = isset( $_GET['hcc_err'] ) ? sanitize_key( $_GET['hcc_err'] ) : '';
	return isset( $msgs[ $err ] ) ? '<p class="form-alert" role="alert">' . esc_html( $msgs[ $err ] ) . '</p>' : '';
}
