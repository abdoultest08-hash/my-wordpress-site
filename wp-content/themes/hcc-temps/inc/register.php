<?php
/**
 * Candidate registration (Registration page) and the candidate's own uploads (My Portal).
 *
 * Steps: 1 account → 2 email code → 3 confirm role → 4 pre-screening (no uploads) → 5 documents → 6 training → 7 submitted.
 * Anyone can register; unsuitable candidates are contacted by staff. Nothing ever blocks a candidate:
 * each document can be uploaded or marked "I don't have this yet".
 */

defined( 'ABSPATH' ) || exit;

/** The candidate record for the logged-in user, or 0. */
function hcc_my_record() {
	return is_user_logged_in() ? hcc_applicant_for_user( get_current_user_id() ) : 0;
}

function hcc_reg_back( $step, $args = array(), $anchor = '' ) {
	$url = add_query_arg( array_merge( array( 'step' => $step ), $args ), hcc_apply_url() );
	wp_safe_redirect( $url . ( $anchor ? '#' . $anchor : '' ) );
	exit;
}

function hcc_reg_nonce_ok( $action ) {
	return isset( $_POST['hcc_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hcc_nonce'] ) ), $action );
}

/** Logged-in candidate with a record, or bounce to login. */
function hcc_reg_require( $action, $json = false ) {
	$id = hcc_my_record();
	if ( ! $id || ! hcc_reg_nonce_ok( $action ) ) {
		if ( $json ) {
			wp_send_json_error( array( 'message' => 'Your session has expired. Please log in again.' ), 403 );
		}
		wp_safe_redirect( hcc_login_url() );
		exit;
	}
	return $id;
}

function hcc_reg_send_code( $id ) {
	$code = (string) wp_rand( 100000, 999999 );
	hcc_set( $id, 'code', wp_hash_password( $code ) );
	hcc_set( $id, 'code_exp', time() + 10 * MINUTE_IN_SECONDS );
	hcc_set( $id, 'code_tries', 0 );
	hcc_email_code( hcc_meta( $id, 'email' ), hcc_meta( $id, 'first' ), $code );
}

/* ---------- Step 1: create the account ---------- */

function hcc_reg_account() {
	hcc_guard( 'hcc_reg_account', 'account' );
	$f = array();
	foreach ( array( 'first', 'last', 'phone', 'addr1', 'town', 'postcode' ) as $k ) {
		$f[ $k ] = hcc_field( $k );
	}
	$f['email']    = strtolower( hcc_field( 'email', 'email' ) );
	$f['postcode'] = strtoupper( $f['postcode'] );
	$role          = hcc_role( hcc_field( 'role' ) ) ? hcc_field( 'role' ) : 'nurse';
	$keep          = array( 'role' => $role, 'first' => $f['first'], 'last' => $f['last'], 'email' => $f['email'], 'phone' => $f['phone'], 'addr1' => $f['addr1'], 'town' => $f['town'], 'postcode' => $f['postcode'] );
	$pw            = isset( $_POST['pw'] ) ? (string) wp_unslash( $_POST['pw'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$pw2           = isset( $_POST['pw2'] ) ? (string) wp_unslash( $_POST['pw2'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( in_array( '', array( $f['first'], $f['last'], $f['phone'], $f['addr1'], $f['postcode'] ), true ) || ! is_email( $f['email'] ) || empty( $_POST['terms'] ) || empty( $_POST['privacy'] ) ) {
		hcc_reg_back( 1, $keep + array( 'hcc_err' => 'fields' ) );
	}
	if ( strlen( $pw ) < 8 || $pw !== $pw2 ) {
		hcc_reg_back( 1, $keep + array( 'hcc_err' => 'password' ) );
	}
	if ( ! hcc_robot_ok() ) {
		hcc_reg_back( 1, $keep + array( 'hcc_err' => 'robot' ) );
	}
	if ( email_exists( $f['email'] ) || username_exists( $f['email'] ) ) {
		hcc_reg_back( 1, $keep + array( 'hcc_err' => 'exists' ) );
	}
	$uid = wp_insert_user( array( 'user_login' => $f['email'], 'user_email' => $f['email'], 'user_pass' => $pw, 'first_name' => $f['first'], 'last_name' => $f['last'], 'display_name' => $f['first'] . ' ' . $f['last'], 'role' => 'hcc_candidate' ) );
	if ( is_wp_error( $uid ) ) {
		hcc_reg_back( 1, $keep + array( 'hcc_err' => 'server' ) );
	}
	$ref = hcc_next_ref();
	$id  = wp_insert_post( array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'post_title' => $ref . ' · ' . $f['first'] . ' ' . $f['last'] . ' · ' . hcc_role_name( $role ) ) );
	foreach ( $f as $k => $v ) {
		hcc_set( $id, $k, $v );
	}
	hcc_set( $id, 'ref', $ref );
	hcc_set( $id, 'role_key', $role );
	hcc_set( $id, 'user_id', $uid );
	hcc_set( $id, 'step', 2 );
	hcc_set( $id, 'registered', time() );
	hcc_set( $id, 'stage', 'New registration' );
	update_user_meta( $uid, 'hcc_applicant_id', $id );
	hcc_log( $id, 'Account created (' . hcc_role_name( $role ) . ')', 'Candidate' );
	hcc_reg_send_code( $id );
	// Logged in straight away; everything past step 2 waits until the email code is entered.
	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true );
	hcc_reg_back( 2 );
}
add_action( 'admin_post_nopriv_hcc_reg_account', 'hcc_reg_account' );
add_action( 'admin_post_hcc_reg_account', 'hcc_reg_account' );

/* ---------- Step 2: email code ---------- */

add_action( 'admin_post_hcc_reg_verify', function () {
	$id    = hcc_reg_require( 'hcc_reg_verify' );
	$code  = preg_replace( '/\D/', '', hcc_field( 'code' ) );
	$tries = (int) hcc_meta( $id, 'code_tries', 0 );
	if ( $tries >= 5 ) {
		hcc_reg_back( 2, array( 'hcc_err' => 'code_locked' ) );
	}
	hcc_set( $id, 'code_tries', $tries + 1 );
	if ( time() > (int) hcc_meta( $id, 'code_exp', 0 ) ) {
		hcc_reg_back( 2, array( 'hcc_err' => 'code_old' ) );
	}
	if ( 6 !== strlen( $code ) || ! wp_check_password( $code, hcc_meta( $id, 'code' ) ) ) {
		hcc_reg_back( 2, array( 'hcc_err' => 'code' ) );
	}
	delete_post_meta( $id, '_hcc_code' );
	hcc_set( $id, 'verified', '1' );
	hcc_set( $id, 'step', max( 3, (int) hcc_meta( $id, 'step', 2 ) ) );
	hcc_log( $id, 'Email confirmed', 'Candidate' );
	hcc_email_welcome( $id );
	hcc_reg_back( 3 );
} );

add_action( 'admin_post_hcc_reg_resend', function () {
	$id = hcc_reg_require( 'hcc_reg_resend' );
	if ( time() - ( (int) hcc_meta( $id, 'code_exp', 0 ) - 10 * MINUTE_IN_SECONDS ) < 60 ) {
		hcc_reg_back( 2, array( 'hcc_err' => 'code_wait' ) );
	}
	hcc_reg_send_code( $id );
	hcc_reg_back( 2, array( 'hcc_ok' => 'resent' ) );
} );

/* Logging in later with the emailed login code also proves the email address. */
add_action( 'two_factor_user_authenticated', function ( $user ) {
	$id = hcc_applicant_for_user( $user->ID );
	if ( $id && ! hcc_meta( $id, 'verified' ) ) {
		hcc_set( $id, 'verified', '1' );
		hcc_set( $id, 'step', max( 3, (int) hcc_meta( $id, 'step', 2 ) ) );
	}
} );

/* ---------- Step 3: confirm or switch the role ---------- */

add_action( 'admin_post_hcc_reg_role', function () {
	$id   = hcc_reg_require( 'hcc_reg_role' );
	$role = hcc_field( 'role' );
	if ( hcc_role( $role ) && $role !== hcc_meta( $id, 'role_key' ) ) {
		hcc_log( $id, 'Changed role from ' . hcc_role_name( hcc_meta( $id, 'role_key' ) ) . ' to ' . hcc_role_name( $role ), 'Candidate' );
		hcc_set( $id, 'role_key', $role );
		wp_update_post( array( 'ID' => $id, 'post_title' => hcc_meta( $id, 'ref' ) . ' · ' . hcc_name( $id ) . ' · ' . hcc_role_name( $role ) ) );
	}
	hcc_set( $id, 'step', max( 4, (int) hcc_meta( $id, 'step', 3 ) ) );
	hcc_reg_back( 4 );
} );

/* ---------- Step 4: pre-screening answers ---------- */

function hcc_prescreen_fields() {
	return array( 'title', 'dob', 'ni', 'british', 'visa', 'share', 'licence', 'vehicle', 'dbs', 'dbsno', 'edu', 'emp', 'exp', 'trainok', 'uniform', 'size', 'r1n', 'r1p', 'r1e', 'r1t', 'r2n', 'r2p', 'r2e', 'r2t', 'kn', 'kr', 'kt', 'ka', 'ban', 'btype', 'bsc', 'bno' );
}

add_action( 'admin_post_hcc_reg_prescreen', function () {
	$id  = hcc_reg_require( 'hcc_reg_prescreen' );
	$old = (array) hcc_meta( $id, 'answers', array() );
	$a   = array();
	foreach ( hcc_prescreen_fields() as $k ) {
		$a[ $k ] = in_array( $k, array( 'r1e', 'r2e' ), true ) ? hcc_field( $k, 'email' ) : hcc_field( $k );
	}
	$a['ni']  = strtoupper( $a['ni'] );
	$a['bsc'] = preg_replace( '/\D/', '', $a['bsc'] );
	$a['bno'] = preg_replace( '/\D/', '', $a['bno'] );
	if ( strlen( $a['bsc'] ) === 6 ) {
		$a['bsc'] = substr( $a['bsc'], 0, 2 ) . '-' . substr( $a['bsc'], 2, 2 ) . '-' . substr( $a['bsc'], 4 );
	}
	hcc_set( $id, 'answers', $a ); // Saved even when something is missing, so nothing typed is lost.
	if ( ! $a['british'] || ! $a['dbs'] ) {
		hcc_reg_back( 4, array( 'hcc_err' => 'rtw_dbs' ), 'rtw' );
	}
	if ( ! $a['ban'] || ! $a['btype'] || strlen( preg_replace( '/\D/', '', $a['bsc'] ) ) !== 6 || strlen( $a['bno'] ) !== 8 ) {
		hcc_reg_back( 4, array( 'hcc_err' => 'bank' ), 'bank' );
	}
	if ( hcc_meta( $id, 'submitted' ) ) {
		$changed = array_keys( array_diff_assoc( $a, $old ) );
		if ( $changed ) {
			hcc_log( $id, 'Candidate updated their answers', 'Candidate' );
		}
		wp_safe_redirect( add_query_arg( 'hcc_ok', 'answers', hcc_portal_url() ) );
		exit;
	}
	if ( ! hcc_meta( $id, 'prescreen_done' ) ) {
		hcc_log( $id, 'Pre-screening submitted', 'Candidate' );
	}
	hcc_set( $id, 'prescreen_done', time() );
	hcc_set( $id, 'step', max( 5, (int) hcc_meta( $id, 'step', 4 ) ) );
	hcc_reg_back( 5 );
} );

/* ---------- Steps 5 and 6, and the portal: one upload at a time ---------- */

/** Normalise $_FILES[name] (single or multiple) into a list. */
function hcc_files_list( $field ) {
	if ( empty( $_FILES[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return array();
	}
	$f = $_FILES[ $field ]; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
	if ( ! is_array( $f['name'] ) ) {
		return empty( $f['name'] ) ? array() : array( $f );
	}
	$out = array();
	foreach ( $f['name'] as $i => $n ) {
		if ( $n ) {
			$out[] = array( 'name' => $n, 'type' => $f['type'][ $i ], 'tmp_name' => $f['tmp_name'][ $i ], 'error' => $f['error'][ $i ], 'size' => $f['size'][ $i ] );
		}
	}
	return $out;
}

/** Save one item's upload or changes; shared by JS (JSON) and plain form posts. */
function hcc_reg_save_item( $id, $key, $uploads, $changes ) {
	$items = hcc_all_items( $id );
	if ( ! isset( $items[ $key ] ) ) {
		return new WP_Error( 'item', 'Unknown document.' );
	}
	$item   = $items[ $key ];
	$after  = hcc_meta( $id, 'submitted' );
	$review = (array) hcc_meta( $id, 'review', array() );
	$was    = isset( $review[ $key ] ) ? $review[ $key ] : array();
	if ( $uploads ) {
		$had   = hcc_has_upload( hcc_file_state( $id, $key ) );
		$saved = hcc_store_item( $id, $key, $item, $uploads );
		if ( is_wp_error( $saved ) ) {
			return $saved;
		}
		if ( $after ) {
			hcc_set_review( $id, $key, '' ); // A new upload goes back to "to review".
			hcc_queue_upload( $id, $key, $had, ! empty( $was['reason'] ) && 'amend' === $was['status'] ? $was['reason'] : '' );
		}
		hcc_log( $id, ( $had ? 'Replaced ' : 'Uploaded ' ) . implode( ', ', $saved ), 'Candidate' );
	}
	$clean = array();
	if ( array_key_exists( 'missing', $changes ) ) {
		$clean['missing'] = $changes['missing'] ? 1 : null;
	}
	if ( array_key_exists( 'pin', $changes ) ) {
		$clean['pin'] = strtoupper( sanitize_text_field( $changes['pin'] ) );
		if ( $clean['pin'] && $after ) {
			hcc_set_review( $id, $key, '' );
			hcc_queue_upload( $id, $key, true );
		}
	}
	if ( array_key_exists( 'exp', $changes ) ) {
		$clean['exp'] = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $changes['exp'] ) ? $changes['exp'] : null;
	}
	if ( $clean ) {
		hcc_update_item( $id, $key, $clean );
	}
	return true;
}

add_action( 'admin_post_hcc_reg_item', function () {
	$json = ! empty( $_POST['ajax'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$id   = hcc_reg_require( 'hcc_reg_item', $json );
	$key  = sanitize_key( hcc_field( 'key' ) );
	$ch   = array();
	if ( isset( $_POST['missing'] ) ) {
		$ch['missing'] = '1' === hcc_field( 'missing' );
	}
	foreach ( array( 'pin', 'exp' ) as $k ) {
		if ( isset( $_POST[ $k ] ) ) {
			$ch[ $k ] = hcc_field( $k );
		}
	}
	$r = hcc_reg_save_item( $id, $key, hcc_files_list( 'file' ), $ch );
	if ( $json ) {
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		$items = hcc_all_items( $id );
		wp_send_json_success( array( 'html' => hcc_item_row( $id, $key, $items[ $key ], hcc_field( 'ctx' ) ), 'counts' => hcc_reg_counts_text( $id, 0 === strpos( $key, 't-' ) ? 'train' : 'docs' ) ) );
	}
	$back = 'portal' === hcc_field( 'ctx' ) ? hcc_portal_url() : add_query_arg( 'step', 0 === strpos( $key, 't-' ) ? 6 : 5, hcc_apply_url() );
	wp_safe_redirect( add_query_arg( is_wp_error( $r ) ? array( 'hcc_err' => 'upload', 'msg' => rawurlencode( $r->get_error_message() ) ) : array(), $back ) . '#item-' . $key );
	exit;
} );

function hcc_reg_counts_text( $id, $set ) {
	$c = hcc_counts( $id, hcc_items( $id, $set ) );
	return array( 'text' => $c['have'] . ' of ' . $c['need'] . ' added' . ( $c['later'] ? ' · ' . $c['later'] . ' to follow' : '' ), 'pct' => $c['need'] ? round( $c['have'] / $c['need'] * 100 ) : 100 );
}

add_action( 'admin_post_hcc_reg_next', function () {
	$id   = hcc_reg_require( 'hcc_reg_next' );
	$step = 6 === absint( hcc_field( 'to' ) ) ? 6 : 5;
	hcc_set( $id, 'step', max( $step, (int) hcc_meta( $id, 'step', 5 ) ) );
	hcc_reg_back( $step );
} );

/* ---------- Submit ---------- */

add_action( 'admin_post_hcc_reg_submit', function () {
	$id = hcc_reg_require( 'hcc_reg_submit' );
	if ( ! hcc_meta( $id, 'submitted' ) ) {
		hcc_set( $id, 'submitted', time() );
		hcc_set( $id, 'step', 7 );
		hcc_set( $id, 'stage', 'Documents' );
		hcc_log( $id, 'Registration submitted. Staffing inbox emailed with full summary and CV attached', 'Candidate' );
		hcc_email_staff_summary( $id );
		hcc_email_received( $id );
	}
	hcc_reg_back( 7 );
} );

/* "I've finished uploading" in the portal: send the staff digest now rather than in 10 minutes. */
add_action( 'admin_post_hcc_portal_done', function () {
	$id = hcc_reg_require( 'hcc_portal_done' );
	$next = wp_next_scheduled( 'hcc_upload_digest', array( $id ) );
	if ( $next ) {
		wp_unschedule_event( $next, 'hcc_upload_digest', array( $id ) );
	}
	$sent = hcc_email_upload_digest( $id );
	wp_safe_redirect( add_query_arg( 'hcc_ok', $sent ? 'notified' : 'nothing', hcc_portal_url() ) );
	exit;
} );

/* ---------- One checklist row (registration and portal) ---------- */

function hcc_status_chip( $s, $ctx = 'portal' ) {
	$l = 'portal' === $ctx
		? array( 'approved' => array( 'Approved', 'ok' ), 'override' => array( 'Approved', 'ok' ), 'review' => array( 'Uploaded · awaiting review', 'wait' ), 'amend' => array( 'Needs replacing', 'amend' ), 'requested' => array( 'Requested by our team', 'amend' ), 'todo' => array( 'To follow', 'todo' ), 'na' => array( 'Not needed', 'na' ) )
		: array( 'approved' => array( 'Approved', 'ok' ), 'override' => array( 'Completed by staff', 'ok' ), 'review' => array( 'To review', 'wait' ), 'amend' => array( 'Replace requested', 'amend' ), 'requested' => array( 'Requested', 'amend' ), 'todo' => array( 'Missing', 'todo' ), 'na' => array( 'Not needed', 'na' ) );
	$x = isset( $l[ $s ] ) ? $l[ $s ] : array( $s, 'na' );
	return '<span class="stat stat-' . $x[1] . '">' . esc_html( $x[0] ) . '</span>';
}

function hcc_item_row( $id, $key, $it, $ctx = 'wizard' ) {
	$st     = hcc_file_state( $id, $key );
	$has    = hcc_has_upload( $st );
	$status = hcc_item_status( $id, $key, $it );
	$review = (array) hcc_meta( $id, 'review', array() );
	$reason = isset( $review[ $key ]['reason'] ) && in_array( $status, array( 'amend' ), true ) ? $review[ $key ]['reason'] : '';
	$nonce  = wp_create_nonce( 'hcc_reg_item' );
	$cls    = 'doc' . ( $has || ! empty( $it['na'] ) ? ' done' : '' ) . ( ! empty( $st['missing'] ) && ! $has ? ' later' : '' ) . ' s-' . $status;
	$ico    = $has || ! empty( $it['na'] ) ? hcc_icon( 'check', 20 ) : hcc_icon( 'doc', 20 );
	$files  = '';
	foreach ( isset( $st['files'] ) ? $st['files'] : array() as $n => $f ) {
		$files .= '<span class="f-line"><span class="renamed">✓ ' . esc_html( $f['name'] ) . '</span><a class="f-act" href="' . esc_url( hcc_file_url( $id, $key, $n ) ) . '" target="_blank" rel="noopener">View</a></span>';
	}
	$h  = '<div class="' . esc_attr( $cls ) . '" id="item-' . esc_attr( $key ) . '" data-key="' . esc_attr( $key ) . '">';
	$h .= '<div class="ico">' . $ico . '</div><div class="d-main"><h3>' . esc_html( $it['label'] ) . ( ! empty( $it['optional'] ) ? '<span class="req opt">If applicable</span>' : '' ) . '</h3>';
	$h .= ! empty( $it['note'] ) || ! empty( $it['hint'] ) ? '<p>' . esc_html( ! empty( $it['note'] ) ? $it['note'] : $it['hint'] ) . '</p>' : '';
	$h .= $files;
	if ( ! empty( $st['pin'] ) && 'pin' === $it['kind'] ) {
		$h .= '<span class="renamed">✓ PIN ' . esc_html( $st['pin'] ) . ( ! empty( $st['exp'] ) ? ' · expires ' . esc_html( wp_date( 'd/m/Y', strtotime( $st['exp'] ) ) ) : '' ) . '</span>';
	}
	if ( $reason ) {
		$h .= '<span class="p-reason">' . esc_html( $reason ) . '</span>';
	}
	if ( ! empty( $st['missing'] ) && ! $has ) {
		$h .= '<div class="later-note">Marked to follow. You can upload it later from your portal.</div>';
	}
	$h .= '<p class="up-msg" role="status" aria-live="polite"></p></div>';
	// Right-hand controls.
	$h .= '<div class="act">';
	if ( 'portal' === $ctx ) {
		$h .= hcc_status_chip( $status, 'portal' );
	}
	if ( empty( $it['na'] ) && ! in_array( $status, array( 'approved', 'override' ), true ) ) {
		$form = '<form class="item-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data"><input type="hidden" name="action" value="hcc_reg_item"><input type="hidden" name="hcc_nonce" value="' . esc_attr( $nonce ) . '"><input type="hidden" name="key" value="' . esc_attr( $key ) . '"><input type="hidden" name="ctx" value="' . esc_attr( $ctx ) . '">';
		if ( 'pin' === $it['kind'] ) {
			$h .= $form . '<div class="pin-in"><input type="text" name="pin" placeholder="e.g. 12A3456E" value="' . esc_attr( isset( $st['pin'] ) ? $st['pin'] : '' ) . '" aria-label="NMC PIN"><input type="date" name="exp" value="' . esc_attr( isset( $st['exp'] ) ? $st['exp'] : '' ) . '" aria-label="PIN expiry date"><button class="btn btn-ghost btn-sm" type="submit">Save</button></div></form>';
		} else {
			$fid = 'f-' . $ctx . '-' . $key;
			$h  .= $form . '<label class="btn btn-ghost btn-sm" for="' . esc_attr( $fid ) . '">' . ( $has ? 'Replace' : 'Upload' ) . '</label><input class="up-input" type="file" id="' . esc_attr( $fid ) . '" name="file[]" accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx"' . ( ! empty( $it['multiple'] ) ? ' multiple' : '' ) . '>';
			if ( ! $has ) {
				$h .= '<label class="nohave"><input type="checkbox" name="missing" value="1"' . checked( ! empty( $st['missing'] ), true, false ) . '> I don\'t have this yet</label>';
			}
			if ( 0 === strpos( $key, 't-' ) && $has ) {
				$h .= '<label class="exp-in">Expiry <input type="date" name="exp" value="' . esc_attr( isset( $st['exp'] ) ? $st['exp'] : '' ) . '"></label>';
			}
			$h .= '<noscript><button class="btn btn-sm btn-navy" type="submit">Save</button></noscript></form>';
		}
	} elseif ( ! empty( $it['na'] ) && 'portal' !== $ctx ) {
		$h .= '<span class="stat stat-na">Not needed</span>';
	}
	return $h . '</div></div>';
}
