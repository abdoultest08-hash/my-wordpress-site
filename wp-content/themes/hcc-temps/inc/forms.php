<?php
/**
 * Form handlers: job applications and registrations (with CV), document uploads
 * through a private link, contact messages and client cover requests.
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

function hcc_guard( $action, $anchor ) {
	if ( ! isset( $_POST['hcc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hcc_nonce'] ) ), $action ) ) {
		hcc_back( array( 'hcc_err' => 'expired' ), $anchor );
	}
	if ( ! empty( $_POST['website'] ) ) { // Honeypot: real visitors never see this field.
		hcc_back( array( 'hcc_ok' => '1' ), $anchor );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'hcc_rl_' . md5( $action . $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 8 ) {
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

/** Check and move one uploaded file into the applicant's private folder. */
function hcc_store_file( $file, $applicant_id, $doc_label, $allowed, $max_mb ) {
	if ( empty( $file['name'] ) || UPLOAD_ERR_OK !== $file['error'] ) {
		return new WP_Error( 'missing', 'No file' );
	}
	if ( $file['size'] > $max_mb * MB_IN_BYTES ) {
		return new WP_Error( 'size', 'Too big' );
	}
	$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed );
	$ext   = $check['ext'] ? $check['ext'] : '';
	if ( ! $ext ) {
		return new WP_Error( 'type', 'Wrong type' );
	}
	$m    = function ( $k ) use ( $applicant_id ) { return get_post_meta( $applicant_id, '_hcc_' . $k, true ); };
	$name = hcc_doc_filename( $m( 'ref' ), $m( 'first' ), $m( 'last' ), $doc_label, $ext );
	$dest = hcc_private_dir( $applicant_id ) . '/' . $name;
	if ( ! move_uploaded_file( $file['tmp_name'], $dest ) ) {
		return new WP_Error( 'move', 'Could not save' );
	}
	$docs               = (array) get_post_meta( $applicant_id, '_hcc_docs', true );
	$docs[ $doc_label ] = array( 'file' => $name, 'time' => time() );
	update_post_meta( $applicant_id, '_hcc_docs', array_filter( $docs ) );
	return $dest;
}

/* ---------- Candidate reference + private upload link ---------- */

function hcc_next_ref() {
	$n = (int) get_option( 'hcc_ref_counter', 1000 ) + 1;
	update_option( 'hcc_ref_counter', $n, false );
	return 'HCC-' . str_pad( (string) $n, 6, '0', STR_PAD_LEFT );
}

function hcc_token_hash( $token ) {
	return hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );
}

/** Create a fresh link (old ones stop working). */
function hcc_upload_link( $applicant_id ) {
	$token = wp_generate_password( 32, false );
	update_post_meta( $applicant_id, '_hcc_token', hcc_token_hash( $token ) );
	update_post_meta( $applicant_id, '_hcc_token_expires', time() + DAY_IN_SECONDS * max( 1, (int) hcc_opt( 'link_valid_days' ) ) );
	return add_query_arg( array( 'ref' => get_post_meta( $applicant_id, '_hcc_ref', true ), 't' => $token ), hcc_page_url( 'upload', '/upload-documents/' ) );
}

/** Applicant for a ref + token pair, or null if the link is wrong or expired. */
function hcc_applicant_from_link( $ref, $token ) {
	if ( ! $ref || ! $token ) {
		return null;
	}
	$found = get_posts( array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'meta_key' => '_hcc_ref', 'meta_value' => $ref, 'numberposts' => 1 ) );
	if ( ! $found ) {
		return null;
	}
	$id = $found[0]->ID;
	if ( ! hash_equals( (string) get_post_meta( $id, '_hcc_token', true ), hcc_token_hash( $token ) ) || time() > (int) get_post_meta( $id, '_hcc_token_expires', true ) ) {
		return null;
	}
	return $id;
}

function hcc_send_upload_email( $applicant_id, $resent = false ) {
	$m    = function ( $k ) use ( $applicant_id ) { return get_post_meta( $applicant_id, '_hcc_' . $k, true ); };
	$link = hcc_upload_link( $applicant_id );
	$list = '';
	foreach ( hcc_checklist( $m( 'role' ) ) as $d ) {
		$list .= '<li>' . esc_html( $d[0] ) . ( $d[2] ? '' : ' (optional)' ) . '</li>';
	}
	$body = '<p>Hi ' . esc_html( $m( 'first' ) ) . ',</p>'
		. ( $resent ? '<p>Here is a new link to upload your documents.</p>' : '<p>Thanks for applying for <b>' . esc_html( $m( 'role' ) ) . '</b>. We\'ve got your CV.</p><p>To get you working faster, please upload your documents. The link is private to you and works for ' . (int) hcc_opt( 'link_valid_days' ) . ' days. You can upload some now and come back later.</p>' )
		. hcc_button( $link, 'Upload my documents' )
		. '<p>You will need:</p><ul>' . $list . '</ul>'
		. '<p>Questions? Call ' . esc_html( hcc_opt( 'phone' ) ) . '. Your reference is <b>' . esc_html( $m( 'ref' ) ) . '</b>.</p>';
	return hcc_mail( $m( 'email' ), 'Next step for your ' . $m( 'role' ) . ' application (' . $m( 'ref' ) . ')', $body, array(), hcc_opt( 'email_jobs' ) );
}

/* ---------- Apply for a job / register ---------- */

function hcc_handle_application() {
	hcc_guard( 'hcc_apply', 'apply' );
	$f = array(
		'first'    => hcc_field( 'first' ),
		'last'     => hcc_field( 'last' ),
		'email'    => hcc_field( 'email', 'email' ),
		'phone'    => hcc_field( 'phone' ),
		'postcode' => strtoupper( hcc_field( 'postcode' ) ),
		'rtw'      => hcc_field( 'rtw' ),
		'travel'   => hcc_field( 'travel' ),
		'role'     => hcc_field( 'role' ),
		'job_id'   => absint( hcc_field( 'job_id' ) ),
	);
	if ( $f['job_id'] && 'job_vacancies' === get_post_type( $f['job_id'] ) ) {
		$f['role'] = get_the_title( $f['job_id'] );
	}
	if ( ! $f['first'] || ! $f['last'] || ! is_email( $f['email'] ) || ! $f['phone'] || ! $f['role'] || empty( $_POST['consent'] ) ) {
		hcc_back( array( 'hcc_err' => 'fields' ), 'apply' );
	}
	if ( empty( $_FILES['cv']['name'] ) ) {
		hcc_back( array( 'hcc_err' => 'cv' ), 'apply' );
	}

	$ref = hcc_next_ref();
	$id  = wp_insert_post( array(
		'post_type'   => 'hcc_applicant',
		'post_status' => 'private',
		'post_title'  => $ref . ' · ' . $f['first'] . ' ' . $f['last'] . ' · ' . $f['role'],
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		hcc_back( array( 'hcc_err' => 'server' ), 'apply' );
	}
	update_post_meta( $id, '_hcc_ref', $ref );
	foreach ( $f as $k => $v ) {
		update_post_meta( $id, '_hcc_' . $k, $v );
	}

	$cv = hcc_store_file( $_FILES['cv'], $id, 'CV', array( 'pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ), 5 );
	if ( is_wp_error( $cv ) ) {
		wp_delete_post( $id, true );
		hcc_back( array( 'hcc_err' => 'cv_' . $cv->get_error_code() ), 'apply' );
	}

	$job_line = $f['job_id'] ? get_the_title( $f['job_id'] ) . ' (' . get_post_meta( $f['job_id'], '_hcc_ref', true ) . ')' : 'Registration, no specific job';
	hcc_mail(
		hcc_opt( 'email_jobs' ),
		'[JOB APPLICATION] ' . $f['role'] . ' · ' . $f['first'] . ' ' . $f['last'] . ' (' . $ref . ')',
		'<p>New ' . ( $f['job_id'] ? 'application' : 'registration' ) . ' from the website. The CV is attached.</p>'
			. hcc_rows( array( 'Reference' => $ref, 'Job' => $job_line, 'Role' => $f['role'], 'Name' => $f['first'] . ' ' . $f['last'], 'Email' => $f['email'], 'Phone' => $f['phone'], 'Postcode' => $f['postcode'], 'Right to work' => $f['rtw'], 'Will travel' => $f['travel'] ) )
			. hcc_button( admin_url( 'post.php?post=' . $id . '&action=edit' ), 'Open in WordPress' ),
		array( $cv ),
		$f['email']
	);
	hcc_send_upload_email( $id );
	hcc_back( array( 'hcc_ok' => 'applied', 'ref' => $ref ), 'apply' );
}
add_action( 'admin_post_nopriv_hcc_apply', 'hcc_handle_application' );
add_action( 'admin_post_hcc_apply', 'hcc_handle_application' );

/* ---------- Document upload through the private link ---------- */

function hcc_handle_documents() {
	$ref   = hcc_field( 'ref' );
	$token = hcc_field( 't' );
	if ( ! isset( $_POST['hcc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hcc_nonce'] ) ), 'hcc_docs' ) ) {
		hcc_back( array( 'hcc_err' => 'expired', 'ref' => $ref, 't' => $token ), 'docs' );
	}
	$id = hcc_applicant_from_link( $ref, $token );
	if ( ! $id ) {
		hcc_back( array( 'hcc_err' => 'link' ), 'docs' );
	}
	$list    = hcc_checklist( get_post_meta( $id, '_hcc_role', true ) );
	$allowed = array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'heic' => 'image/heic', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' );
	$saved   = array();
	$failed  = 0;
	foreach ( $list as $i => $d ) {
		$key = 'doc_' . $i;
		if ( empty( $_FILES[ $key ]['name'] ) ) {
			continue;
		}
		$r = hcc_store_file( $_FILES[ $key ], $id, $d[0], $allowed, 10 );
		if ( is_wp_error( $r ) ) {
			$failed++;
		} else {
			$saved[] = $d[0];
		}
	}
	if ( $saved ) {
		$m = function ( $k ) use ( $id ) { return get_post_meta( $id, '_hcc_' . $k, true ); };
		hcc_mail(
			hcc_opt( 'email_jobs' ),
			'[DOCUMENTS] ' . $m( 'ref' ) . ' ' . $m( 'first' ) . ' ' . $m( 'last' ) . ' uploaded ' . count( $saved ) . ' document(s)',
			'<p>' . esc_html( $m( 'first' ) . ' ' . $m( 'last' ) ) . ' uploaded: <b>' . esc_html( implode( ', ', $saved ) ) . '</b>.</p><p>Files are stored privately in WordPress, not attached to this email.</p>'
				. hcc_button( admin_url( 'post.php?post=' . $id . '&action=edit' ), 'Check documents' )
		);
	}
	hcc_back( array( 'ref' => $ref, 't' => $token, 'saved' => count( $saved ), 'hcc_err' => $failed ? 'some' : false ), 'docs' );
}
add_action( 'admin_post_nopriv_hcc_docs', 'hcc_handle_documents' );
add_action( 'admin_post_hcc_docs', 'hcc_handle_documents' );

/* ---------- Contact form ---------- */

function hcc_handle_contact() {
	hcc_guard( 'hcc_contact', 'message' );
	$who   = in_array( hcc_field( 'who' ), array( 'candidate', 'employer', 'other' ), true ) ? hcc_field( 'who' ) : 'other';
	$name  = hcc_field( 'name' );
	$email = hcc_field( 'email', 'email' );
	$msg   = hcc_field( 'message', 'textarea' );
	if ( ! $name || ! is_email( $email ) || ! $msg || empty( $_POST['consent'] ) ) {
		hcc_back( array( 'hcc_err' => 'fields' ), 'message' );
	}
	$route = array(
		'candidate' => array( 'email_jobs', '[CANDIDATE ENQUIRY]' ),
		'employer'  => array( 'email_clients', '[CLIENT ENQUIRY]' ),
		'other'     => array( 'email_general', '[ENQUIRY]' ),
	);
	hcc_mail( hcc_opt( $route[ $who ][0] ), $route[ $who ][1] . ' ' . $name, '<p>New message from the website contact form.</p>' . hcc_rows( array( 'Name' => $name, 'Email' => $email, 'Phone' => hcc_field( 'phone' ), 'Organisation' => hcc_field( 'org' ), 'Message' => $msg ) ), array(), $email );
	hcc_mail( $email, 'We got your message', '<p>Hi ' . esc_html( $name ) . ',</p><p>Thanks for getting in touch. We reply within one working day. For anything urgent, call ' . esc_html( hcc_opt( 'phone' ) ) . '.</p>' );
	hcc_back( array( 'hcc_ok' => 'sent' ), 'message' );
}
add_action( 'admin_post_nopriv_hcc_contact', 'hcc_handle_contact' );
add_action( 'admin_post_hcc_contact', 'hcc_handle_contact' );

/* ---------- Client cover request ---------- */

function hcc_handle_cover() {
	hcc_guard( 'hcc_cover', 'request' );
	$org   = hcc_field( 'org' );
	$name  = hcc_field( 'name' );
	$email = hcc_field( 'email', 'email' );
	$phone = hcc_field( 'phone' );
	if ( ! $org || ! $name || ! is_email( $email ) || ! $phone ) {
		hcc_back( array( 'hcc_err' => 'fields' ), 'request' );
	}
	$rows = array(
		'Organisation' => $org, 'Name' => $name, 'Email' => $email, 'Phone' => $phone, 'Role needed' => hcc_field( 'role' ),
		'Number of staff' => hcc_field( 'num' ), 'First shift' => hcc_field( 'from' ), 'Site postcode' => strtoupper( hcc_field( 'postcode' ) ), 'Details' => hcc_field( 'notes', 'textarea' ),
	);
	hcc_mail( hcc_opt( 'email_clients' ), '[COVER REQUEST] ' . hcc_field( 'role' ) . ' · ' . $org, '<p>New cover request from the website.</p>' . hcc_rows( $rows ), array(), $email );
	hcc_mail( $email, 'Your cover request', '<p>Hi ' . esc_html( $name ) . ',</p><p>Thanks, we have your request and will call you to confirm. If the shift is in the next 24 hours, please call us on ' . esc_html( hcc_opt( 'phone' ) ) . '.</p>' . hcc_rows( $rows ) );
	hcc_back( array( 'hcc_ok' => 'sent' ), 'request' );
}
add_action( 'admin_post_nopriv_hcc_cover', 'hcc_handle_cover' );
add_action( 'admin_post_hcc_cover', 'hcc_handle_cover' );

/** Message shown after a redirect. */
function hcc_notice() {
	$msgs = array(
		'fields'   => 'Please fill in the fields marked as required and tick the consent box.',
		'cv'       => 'Please attach your CV.',
		'cv_size'  => 'Your CV is larger than 5MB. Please upload a smaller file.',
		'cv_type'  => 'Please upload your CV as a PDF or Word document.',
		'cv_move'  => 'We could not save your CV. Please try again.',
		'expired'  => 'The form timed out. Please try again.',
		'busy'     => 'Too many attempts. Please wait a while or call us.',
		'server'   => 'Something went wrong. Please try again or call us.',
		'link'     => 'This upload link has expired or is not valid. Call us and we will send a new one.',
		'some'     => 'Some files could not be saved. Use PDF, JPG or PNG files up to 10MB.',
	);
	$err = isset( $_GET['hcc_err'] ) ? sanitize_key( $_GET['hcc_err'] ) : '';
	return isset( $msgs[ $err ] ) ? '<p class="form-alert" role="alert">' . esc_html( $msgs[ $err ] ) . '</p>' : '';
}
