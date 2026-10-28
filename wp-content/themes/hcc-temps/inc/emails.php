<?php
/**
 * Automatic emails for registration and onboarding.
 *
 * Staffing inbox (Appearance → Customize → Contact details → jobs email):
 *   - registration submitted: every answer line by line, every document Submitted / Not submitted,
 *     a "Missing" list at the end, CV attached (all other files stay in the Staff portal)
 *   - uploads after submitting: one email per upload visit listing everything in it
 * Candidate:
 *   - email code, welcome, reminders (24 hours and 3 days), registration received,
 *     update email (whatever staff put in the tray), ready to work
 */

defined( 'ABSPATH' ) || exit;

function hcc_email_code( $email, $first, $code ) {
	return hcc_mail( $email, 'Your HCC-Temps code: ' . $code,
		'<p>Hi ' . esc_html( $first ) . ',</p><p>Your code to confirm your email is:</p><p style="font-size:30px;font-weight:bold;letter-spacing:.15em;color:#171A45">' . esc_html( substr( $code, 0, 3 ) . ' ' . substr( $code, 3 ) ) . '</p><p style="font-size:13px;color:#5B6078">It works for 10 minutes. If you didn\'t ask for this, ignore this email.</p>' );
}

function hcc_email_welcome( $id ) {
	return hcc_mail( hcc_meta( $id, 'email' ), 'Welcome to HCC-Temps, let\'s finish your registration',
		'<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>Your account is set up. Your reference is <b>' . esc_html( hcc_meta( $id, 'ref' ) ) . '</b>.</p><p>Next: answer a few questions and upload your documents. It takes about 15 minutes, and you can stop and come back.</p>'
			. hcc_button( hcc_apply_url(), 'Continue my registration' ), array(), hcc_opt( 'email_jobs' ) );
}

function hcc_email_reminder( $id, $n ) {
	$left = max( 1, 7 - (int) hcc_meta( $id, 'step', 1 ) );
	return hcc_mail( hcc_meta( $id, 'email' ), 'Your registration is nearly done',
		'<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>You\'re ' . $left . ' step' . ( $left > 1 ? 's' : '' ) . ' away from finishing your registration as a <b>' . esc_html( hcc_role_name( hcc_meta( $id, 'role_key' ) ) ) . '</b>. Most candidates are ready to work within 3 days of finishing.</p>'
			. hcc_button( hcc_login_url(), 'Carry on' ), array(), hcc_opt( 'email_jobs' ) );
}

function hcc_email_received( $id ) {
	$d = hcc_counts( $id, hcc_items( $id, 'docs' ) );
	$t = hcc_counts( $id, hcc_items( $id, 'train' ) );
	return hcc_mail( hcc_meta( $id, 'email' ), 'We have your registration (' . hcc_meta( $id, 'ref' ) . ')',
		'<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>Thank you. Our compliance team will review your answers and files, usually within 1 working day.</p>'
			. '<p>You added <b>' . $d['have'] . ' of ' . $d['need'] . '</b> documents and <b>' . $t['have'] . ' of ' . $t['need'] . '</b> training certificates.' . ( $d['have'] < $d['need'] || $t['have'] < $t['need'] ? ' You can add the rest any time from your portal.' : '' ) . '</p>'
			. hcc_button( hcc_portal_url(), 'Open my portal' ), array(), hcc_opt( 'email_jobs' ) );
}

/** One table row group for the staff summary. */
function hcc_email_table( $groups ) {
	$h = '<table cellpadding="5" style="border-collapse:collapse;font-size:13px;width:100%">';
	foreach ( $groups as $title => $rows ) {
		$h .= '<tr><th colspan="2" style="background:#171A45;color:#fff;text-align:left;padding:6px 8px">' . esc_html( $title ) . '</th></tr>';
		foreach ( $rows as $k => $v ) {
			$h .= '<tr><td style="color:#5B6078;border-bottom:1px solid #E1E7E6;width:44%;vertical-align:top">' . esc_html( $k ) . '</td><td style="border-bottom:1px solid #E1E7E6">' . ( is_array( $v ) ? $v[0] : esc_html( '' === (string) $v ? '—' : $v ) ) . '</td></tr>';
		}
	}
	return $h . '</table>';
}

/** Registration submitted: full summary to the staffing inbox, CV attached. */
function hcc_email_staff_summary( $id ) {
	$groups  = hcc_answer_groups( $id, true );
	$missing = array();
	$ok      = '<b style="color:#17694A">Submitted</b>';
	$no      = '<b style="color:#A1271F">Not submitted</b>';
	$na      = '<span style="color:#5B6078">Not needed</span>';
	foreach ( array( 'docs' => 'Documents', 'train' => 'Training certificates' ) as $set => $title ) {
		$rows = array();
		foreach ( hcc_items( $id, $set ) as $k => $it ) {
			$st = hcc_file_state( $id, $k );
			if ( ! empty( $it['na'] ) ) {
				$rows[ $it['short'] ] = array( $na . ( 'refs' === $k ? ' (referee details above)' : '' ) );
				continue;
			}
			if ( 'pin' === $it['kind'] ) {
				$rows[ $it['short'] ] = ! empty( $st['pin'] ) ? array( esc_html( $st['pin'] . ( ! empty( $st['exp'] ) ? ' · expires ' . wp_date( 'd/m/Y', strtotime( $st['exp'] ) ) : '' ) ) ) : array( $no );
			} else {
				$rows[ $it['short'] ] = hcc_has_upload( $st ) ? array( $ok . ( 'cv' === $k ? ' (attached)' : '' ) . ( ! empty( $st['exp'] ) ? ' · exp ' . esc_html( wp_date( 'd/m/Y', strtotime( $st['exp'] ) ) ) : '' ) ) : array( $no );
			}
			if ( ! hcc_has_upload( $st ) && empty( $it['optional'] ) ) {
				$missing[] = $it['label'];
			}
		}
		$groups[ $title ] = $rows;
	}
	$cv  = hcc_file_state( $id, 'cv' );
	$att = array();
	if ( ! empty( $cv['files'][0]['name'] ) && is_readable( hcc_private_dir( $id ) . '/' . $cv['files'][0]['name'] ) ) {
		$att[] = hcc_private_dir( $id ) . '/' . $cv['files'][0]['name'];
	}
	$body = '<p>A new registration has been submitted.</p>'
		. ( $att ? '<p style="background:#F4F8F7;padding:8px 10px;border-radius:8px;font-size:13px">Attached: ' . esc_html( basename( $att[0] ) ) . '</p>' : '' )
		. hcc_email_table( $groups )
		. ( $missing ? '<div style="background:#FFF2F1;border:1px solid #F2B8B3;border-radius:10px;padding:10px 14px;margin-top:14px"><b>Missing, to be submitted (' . count( $missing ) . ')</b><ul>' . implode( '', array_map( function ( $m ) { return '<li>' . esc_html( $m ) . '</li>'; }, $missing ) ) . '</ul></div>' : '<p><b>Everything has been submitted.</b></p>' )
		. hcc_button( hcc_staff_url( array( 'candidate' => $id ) ), 'Open in Staff portal' )
		. '<p style="font-size:13px;color:#5B6078">Only the CV is attached. All other files are stored securely in the Staff portal.</p>';
	return hcc_mail( hcc_opt( 'email_jobs' ), '[REGISTRATION] ' . hcc_meta( $id, 'ref' ) . ' ' . hcc_name( $id ) . ', ' . hcc_role_name( hcc_meta( $id, 'role_key' ) ) . ': ' . ( $missing ? count( $missing ) . ' item' . ( count( $missing ) > 1 ? 's' : '' ) . ' missing' : 'all submitted' ), $body, $att, hcc_meta( $id, 'email' ) );
}

/** Uploads after submitting: one email listing everything uploaded since the last one. */
function hcc_email_upload_digest( $id ) {
	$pending = (array) hcc_meta( $id, 'pending', array() );
	if ( ! $pending ) {
		return false;
	}
	$items = hcc_all_items( $id );
	$li    = '';
	foreach ( $pending as $p ) {
		$label = isset( $items[ $p['key'] ] ) ? $items[ $p['key'] ]['label'] : $p['key'];
		$li   .= '<li>' . esc_html( $label ) . ' (' . ( ! empty( $p['replaced'] ) ? 'replacement' . ( ! empty( $p['after'] ) ? ', after "' . esc_html( $p['after'] ) . '"' : '' ) : 'new' ) . ')</li>';
	}
	$still = array();
	foreach ( $items as $k => $it ) {
		if ( empty( $it['na'] ) && empty( $it['optional'] ) && ! hcc_has_upload( hcc_file_state( $id, $k ) ) ) {
			$still[] = $it['label'];
		}
	}
	$n = count( $pending );
	hcc_set( $id, 'pending', array() );
	return hcc_mail( hcc_opt( 'email_jobs' ), '[DOCUMENTS] ' . hcc_meta( $id, 'ref' ) . ' ' . hcc_name( $id ) . ' uploaded ' . $n . ' document' . ( $n > 1 ? 's' : '' ),
		'<p>' . esc_html( hcc_name( $id ) ) . ' uploaded the following in the candidate portal:</p><ul>' . $li . '</ul>'
			. ( $still ? '<p>Still missing: ' . esc_html( implode( ', ', $still ) ) . '.</p>' : '<p>Nothing is missing now.</p>' )
			. hcc_button( hcc_staff_url( array( 'candidate' => $id ) ), 'Log in and check' ), array(), hcc_meta( $id, 'email' ) );
}
add_action( 'hcc_upload_digest', 'hcc_email_upload_digest' );

/** Queue an upload for the staff digest; the email goes 10 minutes after the last upload (or when the candidate presses "I've finished"). */
function hcc_queue_upload( $id, $key, $replaced, $after = '' ) {
	$pending   = (array) hcc_meta( $id, 'pending', array() );
	$pending[] = array( 'key' => $key, 'replaced' => $replaced, 'after' => $after, 'at' => time() );
	hcc_set( $id, 'pending', $pending );
	$next = wp_next_scheduled( 'hcc_upload_digest', array( $id ) );
	if ( $next ) {
		wp_unschedule_event( $next, 'hcc_upload_digest', array( $id ) );
	}
	wp_schedule_single_event( time() + 10 * MINUTE_IN_SECONDS, 'hcc_upload_digest', array( $id ) );
}

/** The staff "Update for candidate" tray as one email. */
function hcc_email_update( $id, $queue ) {
	$groups = array( 'approved' => 'Approved', 'amend' => 'Please replace', 'missing' => 'Still needed from you', 'note' => 'Messages from our team' );
	$body   = '<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>Here is an update on your registration (' . esc_html( hcc_meta( $id, 'ref' ) ) . '):</p>';
	$action = false;
	foreach ( $groups as $type => $title ) {
		$rows = array_filter( $queue, function ( $q ) use ( $type ) { return $q['type'] === $type; } );
		if ( ! $rows ) {
			continue;
		}
		$action = $action || in_array( $type, array( 'amend', 'missing' ), true );
		$body  .= '<p style="margin-bottom:4px"><b>' . $title . '</b></p><ul style="margin-top:0">';
		foreach ( $rows as $q ) {
			$body .= '<li>' . esc_html( trim( $q['label'] . ( $q['label'] && $q['text'] ? ': ' : '' ) . $q['text'] ) ) . '</li>';
		}
		$body .= '</ul>';
	}
	$body .= hcc_button( hcc_portal_url(), 'Open my portal' ) . '<p style="font-size:13px;color:#5B6078">Questions? Call ' . esc_html( hcc_opt( 'phone' ) ) . ', 24 hours a day.</p>';
	return hcc_mail( hcc_meta( $id, 'email' ), ( $action ? 'Action needed: ' : '' ) . 'Update on your HCC-Temps registration', $body, array(), hcc_opt( 'email_jobs' ) );
}

/** Sent to the old address after an email change, so a change nobody asked for doesn't go unnoticed. */
function hcc_email_changed_notice( $id, $old ) {
	return hcc_mail( $old, 'Your HCC-Temps email address was changed',
		'<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>The email address for your HCC-Temps registration (' . esc_html( hcc_meta( $id, 'ref' ) ) . ') was changed to <b>' . esc_html( hcc_meta( $id, 'email' ) ) . '</b>. From now on, log in with the new address.</p><p>If you didn\'t ask for this, please call us straight away on <b>' . esc_html( hcc_opt( 'phone' ) ) . '</b>.</p>', array(), hcc_opt( 'email_jobs' ) );
}

function hcc_email_ready( $id ) {
	return hcc_mail( hcc_meta( $id, 'email' ), 'You\'re ready to work with HCC-Temps',
		'<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>All your checks are complete and you\'re ready to work. We\'ll be in touch about shifts near you.</p>' . hcc_button( hcc_portal_url(), 'See my portal' ), array(), hcc_opt( 'email_jobs' ) );
}

/* ---------- Daily: reminders for unfinished registrations ---------- */

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'hcc_daily' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'hcc_daily' );
	}
} );

add_action( 'hcc_daily', function () {
	$q = new WP_Query( array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'posts_per_page' => 200, 'fields' => 'ids', 'no_found_rows' => true,
		'meta_query' => array( array( 'key' => '_hcc_submitted', 'compare' => 'NOT EXISTS' ), array( 'key' => '_hcc_verified', 'value' => '1' ) ) ) );
	foreach ( $q->posts as $id ) {
		if ( hcc_meta( $id, 'closed' ) ) {
			continue;
		}
		$age  = time() - (int) hcc_meta( $id, 'registered', time() );
		$sent = (int) hcc_meta( $id, 'reminders', 0 );
		if ( ( 0 === $sent && $age > DAY_IN_SECONDS ) || ( 1 === $sent && $age > 3 * DAY_IN_SECONDS ) ) {
			hcc_email_reminder( $id, $sent + 1 );
			hcc_set( $id, 'reminders', $sent + 1 );
			hcc_log( $id, 'Reminder email ' . ( $sent + 1 ) . ' sent (registration not finished)', 'System' );
		}
	}
} );
