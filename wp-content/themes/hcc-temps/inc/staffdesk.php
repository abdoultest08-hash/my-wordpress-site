<?php
/**
 * Staff portal actions and lists.
 *
 * Changes that the candidate should hear about (approvals, replace requests, missing items, notes for the candidate)
 * go into the candidate's "Update for candidate" tray and are emailed together when staff press Send.
 * Nothing is sent automatically: unsent trays show on the dashboard under "Updates not sent".
 * Every action is written to the candidate's activity log with the staff member's name.
 */

defined( 'ABSPATH' ) || exit;

function hcc_desk_back( $id, $args = array(), $anchor = '' ) {
	$url = hcc_staff_url( array_merge( array( 'candidate' => $id ), $args ) );
	wp_safe_redirect( $url . ( $anchor ? '#' . $anchor : '' ) );
	exit;
}

/** Add a change to the candidate's update tray. Re-deciding an item replaces its earlier entry. */
function hcc_queue( $id, $type, $key = '', $text = '' ) {
	$items = hcc_all_items( $id );
	$q     = (array) hcc_meta( $id, 'queue', array() );
	if ( 'note' !== $type && $key ) {
		$q = array_values( array_filter( $q, function ( $x ) use ( $key ) { return ! ( $x['key'] === $key && 'note' !== $x['type'] ); } ) );
	}
	$q[] = array( 'type' => $type, 'key' => $key, 'label' => $key && isset( $items[ $key ] ) ? $items[ $key ]['label'] : '', 'text' => $text, 'by' => hcc_staff_name(), 'at' => time() );
	hcc_set( $id, 'queue', $q );
}

function hcc_item_label( $id, $key ) {
	$items = hcc_all_items( $id );
	return isset( $items[ $key ] ) ? $items[ $key ]['label'] : $key;
}

function hcc_desk_approve( $id, $keys, $how = 'Approved' ) {
	foreach ( $keys as $k ) {
		hcc_set_review( $id, $k, 'approved' );
		hcc_queue( $id, 'approved', $k );
	}
	if ( $keys ) {
		hcc_log( $id, $how . ': ' . implode( ', ', array_map( function ( $k ) use ( $id ) { return hcc_item_label( $id, $k ); }, $keys ) ) );
	}
}

/** Items still not approved or completed, optionally only uploaded ones. */
function hcc_open_items( $id, $only_uploaded = false ) {
	$out = array();
	foreach ( hcc_all_items( $id ) as $k => $it ) {
		$s = hcc_item_status( $id, $k, $it );
		if ( in_array( $s, array( 'na', 'approved', 'override' ), true ) || ( ! empty( $it['optional'] ) && 'todo' === $s ) ) {
			continue;
		}
		if ( $only_uploaded && 'review' !== $s ) {
			continue;
		}
		$out[] = $k;
	}
	return $out;
}

add_action( 'admin_post_hcc_desk', function () {
	$id = isset( $_POST['candidate'] ) ? absint( $_POST['candidate'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $id || ! hcc_is_staff() || 'hcc_applicant' !== get_post_type( $id ) || ! check_admin_referer( 'hcc_desk_' . $id ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$do    = sanitize_key( hcc_field( 'do' ) );
	$act   = isset( $_POST['act'] ) ? sanitize_text_field( wp_unslash( $_POST['act'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$items = hcc_all_items( $id );
	$me    = hcc_staff_name();

	/* Document and training rows (one form, many buttons). */
	if ( 'items' === $do && $act ) {
		list( $verb, $key ) = array_pad( explode( ':', $act, 2 ), 2, '' );
		$key = sanitize_key( $key );
		$sel = isset( $_POST['sel'] ) ? array_values( array_intersect( array_map( 'sanitize_key', (array) wp_unslash( $_POST['sel'] ) ), array_keys( $items ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
		if ( 'approve' === $verb && isset( $items[ $key ] ) ) {
			hcc_desk_approve( $id, array( $key ) );
			hcc_desk_back( $id, array( 'msg' => 'approved' ), 'item-' . $key );
		}
		if ( 'amend' === $verb && isset( $items[ $key ] ) ) {
			$reason = isset( $_POST['reason'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['reason'][ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			hcc_set_review( $id, $key, 'amend', $reason );
			hcc_queue( $id, 'amend', $key, $reason );
			hcc_log( $id, 'Asked to replace ' . $items[ $key ]['label'] . ': ' . $reason );
			hcc_desk_back( $id, array( 'msg' => 'queued' ), 'item-' . $key );
		}
		if ( in_array( $verb, array( 'note', 'notec' ), true ) && isset( $items[ $key ] ) ) { // note = staff only, notec = to the candidate
			$text     = isset( $_POST['dnote'][ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST['dnote'][ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$internal = 'note' === $verb;
			if ( $text ) {
				$notes           = (array) hcc_meta( $id, 'dnotes', array() );
				$notes[ $key ][] = array( 'text' => $text, 'internal' => $internal, 'by' => $me, 'at' => time() );
				hcc_set( $id, 'dnotes', $notes );
				if ( ! $internal ) {
					hcc_queue( $id, 'note', $key, $text );
				}
				hcc_log( $id, ( $internal ? 'Internal note on ' : 'Note to candidate on ' ) . $items[ $key ]['label'] . ': ' . $text );
			}
			hcc_desk_back( $id, array( 'msg' => $internal ? 'note' : 'queued' ), 'item-' . $key );
		}
		if ( 'bulk' === $verb && $sel ) {
			if ( 'approve' === $key ) {
				$up   = array_values( array_filter( $sel, function ( $k ) use ( $id ) { return hcc_has_upload( hcc_file_state( $id, $k ) ); } ) );
				$skip = array_diff( $sel, $up );
				hcc_desk_approve( $id, $up );
				hcc_desk_back( $id, array( 'msg' => $skip ? ( $up ? 'approved_some' : 'approve_none' ) : 'approved' ), 'documents' );
			}
			if ( 'missing' === $key ) {
				foreach ( $sel as $k ) {
					hcc_set_review( $id, $k, 'requested' );
					hcc_queue( $id, 'missing', $k );
				}
				hcc_log( $id, 'Requested as missing: ' . implode( ', ', array_map( function ( $k ) use ( $items ) { return $items[ $k ]['label']; }, $sel ) ) );
				hcc_desk_back( $id, array( 'msg' => 'queued' ), 'tray' );
			}
			if ( 'override' === $key ) {
				hcc_desk_back( $id, array( 'override' => implode( ',', $sel ) ), 'override' );
			}
		}
		hcc_desk_back( $id, array( 'msg' => 'nothing' ), 'documents' );
	}

	if ( 'approve_all' === $do ) {
		hcc_desk_approve( $id, hcc_open_items( $id, true ), 'Approved all uploaded' );
		hcc_desk_back( $id, array( 'msg' => 'approved' ) );
	}
	if ( 'complete_all' === $do ) {
		hcc_desk_approve( $id, hcc_open_items( $id, true ), 'Approved all uploaded' );
		$left = hcc_open_items( $id );
		hcc_desk_back( $id, $left ? array( 'override' => implode( ',', $left ) ) : array( 'msg' => 'approved' ), $left ? 'override' : '' );
	}

	/* Override: one item at a time, each with a reason and a note. */
	if ( 'override' === $do ) {
		$keys = array_values( array_intersect( array_map( 'sanitize_key', explode( ',', hcc_field( 'keys' ) ) ), array_keys( $items ) ) );
		$key  = array_shift( $keys );
		if ( $key && ! isset( $_POST['skip'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$note = hcc_field( 'note', 'textarea' );
			if ( ! $note ) {
				hcc_desk_back( $id, array( 'override' => implode( ',', array_merge( array( $key ), $keys ) ), 'msg' => 'need_note' ), 'override' );
			}
			$reason = hcc_field( 'reason' ) . ': ' . $note;
			hcc_set_review( $id, $key, 'override', $reason );
			hcc_queue( $id, 'approved', $key );
			hcc_log( $id, 'Marked complete (override) ' . $items[ $key ]['label'] . '. ' . $reason );
		}
		hcc_desk_back( $id, $keys ? array( 'override' => implode( ',', $keys ) ) : array( 'msg' => 'override_done' ), $keys ? 'override' : 'documents' );
	}

	/* The update tray. */
	if ( 'unqueue' === $do ) {
		$q = (array) hcc_meta( $id, 'queue', array() );
		$i = absint( hcc_field( 'i' ) );
		if ( isset( $q[ $i ] ) ) {
			array_splice( $q, $i, 1 );
			hcc_set( $id, 'queue', $q );
		}
		hcc_desk_back( $id, array(), 'tray' );
	}
	if ( 'send' === $do ) {
		$q = (array) hcc_meta( $id, 'queue', array() );
		if ( $q ) {
			hcc_email_update( $id, $q );
			$msgs   = (array) hcc_meta( $id, 'messages', array() );
			$msgs[] = array( 'at' => time(), 'by' => $me, 'items' => $q );
			hcc_set( $id, 'messages', $msgs );
			hcc_set( $id, 'queue', array() );
			hcc_log( $id, 'Update email sent to candidate (' . count( $q ) . ' change' . ( count( $q ) > 1 ? 's' : '' ) . ': ' . implode( ', ', array_map( function ( $x ) { return $x['label'] ? $x['label'] : 'message'; }, $q ) ) . ')' );
		}
		hcc_desk_back( $id, array( 'msg' => 'sent' ) );
	}

	/* Notes: internal compliance note (signed off, next action) or a message for the candidate. */
	if ( 'cnote' === $do ) {
		$text = hcc_field( 'text', 'textarea' );
		$type = 'cand' === hcc_field( 'type' ) ? 'cand' : 'internal';
		if ( $text ) {
			$n      = array( 'type' => $type, 'text' => $text, 'by' => hcc_field( 'by' ), 'next' => 'cand' === $type ? '' : hcc_field( 'next' ), 'due' => 'cand' === $type ? '' : hcc_field( 'due' ), 'at' => time() );
			$notes  = (array) hcc_meta( $id, 'cnotes', array() );
			$notes[] = $n;
			hcc_set( $id, 'cnotes', $notes );
			if ( 'cand' === $type ) {
				hcc_queue( $id, 'note', '', $text );
			}
			hcc_log( $id, ( 'cand' === $type ? 'Message to candidate added to update: ' : 'Internal note: ' ) . $text . ( $n['next'] ? ' · Next action: ' . $n['next'] . ( $n['due'] ? ' (due ' . $n['due'] . ')' : '' ) : '' ) . ( $n['by'] ? ' · signed off by ' . $n['by'] : '' ) );
		}
		hcc_desk_back( $id, array( 'msg' => 'cand' === $type ? 'queued' : 'note' ), 'messages' === hcc_field( 'back' ) ? 'tray' : 'notes' );
	}

	/* Checks: who, when, result, outcome; visa expiry typed in by hand. */
	if ( 'check' === $do ) {
		$k = sanitize_key( hcc_field( 'k' ) );
		$c = array( 'date' => hcc_field( 'date' ), 'by' => hcc_field( 'by' ), 'result' => hcc_field( 'result' ), 'outcome' => hcc_field( 'outcome' ), 'visa_exp' => hcc_field( 'visa_exp' ) );
		if ( in_array( $k, array( 'rtw', 'dbs', 'nmc', 'id' ), true ) && in_array( $c['result'], array( 'Pass', 'Fail', 'Follow-up needed' ), true ) ) {
			$checks       = (array) hcc_meta( $id, 'checks', array() );
			$checks[ $k ] = $c;
			hcc_set( $id, 'checks', $checks );
			hcc_log( $id, hcc_check_label( $k ) . ' check: ' . $c['result'] . ' (checked ' . $c['date'] . ' by ' . $c['by'] . ')' . ( $c['outcome'] ? '. ' . $c['outcome'] : '' ) . ( $c['visa_exp'] ? '. Visa expires ' . $c['visa_exp'] : '' ) );
			hcc_desk_back( $id, array( 'msg' => 'check' ), 'checks' );
		}
		hcc_desk_back( $id, array( 'msg' => 'check_result' ), 'checks' );
	}

	/* References: recorded by hand. */
	if ( in_array( $do, array( 'ref', 'ref_replace', 'ref_add' ), true ) ) {
		$refs = hcc_refs( $id );
		$i    = absint( hcc_field( 'i' ) );
		if ( 'ref_add' === $do ) {
			$refs[] = array( 'name' => hcc_field( 'name' ), 'pos' => hcc_field( 'pos' ), 'email' => hcc_field( 'email', 'email' ), 'phone' => hcc_field( 'phone' ), 'sent' => '', 'recv' => '', 'outcome' => '', 'history' => array() );
			hcc_log( $id, 'Added referee ' . count( $refs ) . ': ' . hcc_field( 'name' ) );
		} elseif ( isset( $refs[ $i ] ) && 'ref' === $do ) {
			foreach ( array( 'sent', 'recv', 'outcome' ) as $f ) {
				$refs[ $i ][ $f ] = hcc_field( $f );
			}
			hcc_log( $id, 'Referee ' . ( $i + 1 ) . ' (' . $refs[ $i ]['name'] . '): ' . implode( ', ', array_filter( array( $refs[ $i ]['sent'] ? 'sent ' . $refs[ $i ]['sent'] : '', $refs[ $i ]['recv'] ? 'received ' . $refs[ $i ]['recv'] : '', $refs[ $i ]['outcome'] ) ) ) );
		} elseif ( isset( $refs[ $i ] ) && 'ref_replace' === $do && hcc_field( 'name' ) ) {
			$old            = $refs[ $i ];
			$history        = isset( $old['history'] ) ? (array) $old['history'] : array();
			$history[]      = array( 'name' => $old['name'], 'pos' => $old['pos'], 'email' => $old['email'], 'phone' => $old['phone'], 'outcome' => $old['outcome'] ? $old['outcome'] : 'Not received', 'at' => time() );
			$refs[ $i ]     = array( 'name' => hcc_field( 'name' ), 'pos' => hcc_field( 'pos' ), 'email' => hcc_field( 'email', 'email' ), 'phone' => hcc_field( 'phone' ), 'sent' => '', 'recv' => '', 'outcome' => '', 'history' => $history );
			hcc_log( $id, 'Referee ' . ( $i + 1 ) . ' replaced: ' . $old['name'] . ' → ' . hcc_field( 'name' ) );
		}
		hcc_set( $id, 'refs', $refs );
		hcc_desk_back( $id, array( 'msg' => 'ref' ), 'refs' );
	}

	if ( 'dec' === $do ) {
		foreach ( array( 'dec_sent' => 'Declaration sent to candidate', 'dec_signed' => 'Signed declaration received' ) as $k => $label ) {
			$now = ! empty( $_POST[ $k ] ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( $now !== (bool) hcc_meta( $id, $k ) ) {
				hcc_set( $id, $k, $now ? time() : '' );
				hcc_log( $id, $now ? $label : $label . ' (unticked)' );
			}
		}
		hcc_desk_back( $id, array(), 'declaration' );
	}

	if ( 'stage' === $do ) {
		$stage = hcc_field( 'stage' );
		if ( in_array( $stage, hcc_stage_options(), true ) && $stage !== hcc_meta( $id, 'stage' ) ) {
			hcc_set( $id, 'stage', $stage );
			hcc_log( $id, 'Stage changed to ' . $stage );
		}
		hcc_desk_back( $id );
	}

	if ( 'finish' === $do ) {
		$by = hcc_field( 'by' ) ? hcc_field( 'by' ) : $me;
		if ( hcc_can_finish( $id ) ) {
			hcc_set( $id, 'completed', array( 'at' => time(), 'by' => $by ) );
			hcc_set( $id, 'stage', 'Ready to work' );
			hcc_log( $id, 'Onboarding finished and signed off by ' . $by . '. Moved to Staff completed' );
			hcc_email_ready( $id );
			hcc_desk_back( $id, array( 'msg' => 'finished' ) );
		}
		hcc_desk_back( $id, array( 'msg' => 'cant_finish' ) );
	}

	if ( 'close' === $do ) {
		$why = hcc_field( 'why', 'textarea' );
		$by  = hcc_field( 'by' );
		if ( ! $why || ! $by ) {
			hcc_desk_back( $id, array( 'msg' => 'close_need', 'closing' => 1 ), 'close' );
		}
		$out = in_array( hcc_field( 'outcome' ), array( 'Withdrawn by candidate', 'Not qualified', 'Not suitable' ), true ) ? hcc_field( 'outcome' ) : 'Withdrawn by candidate';
		hcc_set( $id, 'closed', array( 'outcome' => $out, 'why' => $why, 'by' => $by, 'date' => hcc_field( 'date' ) ? hcc_field( 'date' ) : wp_date( 'Y-m-d' ), 'at' => time() ) );
		hcc_set( $id, 'stage', 'Closed' );
		hcc_log( $id, 'Application closed: ' . $out . '. ' . $why . ' (signed off by ' . $by . ')' );
		hcc_desk_back( $id, array( 'msg' => 'closed' ) );
	}
	if ( 'reopen' === $do ) {
		$c  = hcc_meta( $id, 'closed' );
		$cm = hcc_meta( $id, 'completed' );
		delete_post_meta( $id, '_hcc_closed' );
		delete_post_meta( $id, '_hcc_completed' );
		hcc_set( $id, 'stage', is_array( $cm ) ? 'Checks in progress' : 'Documents' );
		hcc_log( $id, is_array( $cm ) ? 'Moved back to in progress (was signed off by ' . $cm['by'] . ')' : 'Application reopened' . ( is_array( $c ) ? ' (was: ' . $c['outcome'] . ')' : '' ) );
		hcc_desk_back( $id, array( 'msg' => 'reopened' ) );
	}

	/* Change the candidate's email (e.g. they phoned in): login, account and record all move to the new address. */
	if ( 'email' === $do ) {
		$old = hcc_meta( $id, 'email' );
		$r   = hcc_change_email( $id, hcc_field( 'new_email', 'email' ), $me );
		if ( is_wp_error( $r ) ) {
			hcc_desk_back( $id, array( 'msg' => 'email_' . $r->get_error_code() ), 'answers' );
		}
		delete_post_meta( $id, '_hcc_email_pending' );
		hcc_email_changed_notice( $id, $old );
		hcc_mail( hcc_meta( $id, 'email' ), 'Your HCC-Temps login email has been updated', '<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>Our team has updated the email for your registration (' . esc_html( hcc_meta( $id, 'ref' ) ) . '). Use this address to log in from now on.</p>' . hcc_button( hcc_login_url(), 'Log in' ), array(), hcc_opt( 'email_jobs' ) );
		hcc_desk_back( $id, array( 'msg' => 'email_changed' ), 'answers' );
	}

	if ( 'dup_ok' === $do ) {
		hcc_set( $id, 'dup_ok', time() );
		hcc_log( $id, 'Duplicate warning checked: not a duplicate' );
		hcc_desk_back( $id );
	}

	hcc_desk_back( $id );
} );

function hcc_stage_options() {
	return array( 'New registration', 'Documents', 'Checks in progress', 'Ready to work', 'Closed' );
}

/** Profile 100%, declaration sent and signed, and nothing waiting in the update tray. */
function hcc_can_finish( $id ) {
	return 100 === hcc_progress( $id ) && hcc_meta( $id, 'dec_sent' ) && hcc_meta( $id, 'dec_signed' ) && ! hcc_meta( $id, 'queue' ) && 'in_progress' === hcc_state( $id );
}

/** Referees, starting from the two the candidate gave in pre-screening. */
function hcc_refs( $id ) {
	$refs = hcc_meta( $id, 'refs', array() );
	if ( $refs ) {
		return (array) $refs;
	}
	$a = (array) hcc_meta( $id, 'answers', array() );
	$out = array();
	foreach ( array( 1, 2 ) as $n ) {
		$g = function ( $k ) use ( $a, $n ) { return isset( $a[ 'r' . $n . $k ] ) ? $a[ 'r' . $n . $k ] : ''; };
		$out[] = array( 'name' => $g( 'n' ), 'pos' => $g( 'p' ), 'email' => $g( 'e' ), 'phone' => $g( 't' ), 'sent' => '', 'recv' => '', 'outcome' => '', 'history' => array() );
	}
	return $out;
}

/** The checks shown for a candidate. */
function hcc_check_list( $id ) {
	$a    = (array) hcc_meta( $id, 'answers', array() );
	$brit = isset( $a['british'] ) && 'Yes' === $a['british'];
	$list = array(
		'rtw' => array( 'Right to work', $brit ? 'British or Irish: passport check' : trim( 'Share code ' . ( isset( $a['share'] ) ? $a['share'] : '—' ) . ' · ' . ( isset( $a['visa'] ) ? $a['visa'] : '' ) ), 'https://www.gov.uk/view-right-to-work', ! $brit ),
		'dbs' => array( 'DBS', isset( $a['dbs'] ) && 'none' === $a['dbs'] ? 'New enhanced DBS application by HCC' : ( isset( $a['dbs'] ) && 'update' === $a['dbs'] ? 'Update Service · certificate ' . ( ! empty( $a['dbsno'] ) ? $a['dbsno'] : '—' ) : 'Certificate not on the Update Service' ), 'https://secure.crbonline.gov.uk/crsc/check', false ),
	);
	if ( 'nurse' === hcc_meta( $id, 'role_key' ) ) {
		$pin         = hcc_file_state( $id, 'nmc_pin' );
		$list['nmc'] = array( 'NMC register', 'PIN ' . ( ! empty( $pin['pin'] ) ? $pin['pin'] : '—' ), 'https://www.nmc.org.uk/registration/search-the-register/', false );
	}
	$list['id'] = array( 'Identity', 'Photo ID matches the candidate', '', false );
	return $list;
}

/* ---------- Lists for the dashboard ---------- */

/** All candidate ids for a tab: in_progress, completed, closed. Optional search. */
function hcc_desk_list( $tab, $search = '' ) {
	$q   = new WP_Query( array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
	$out = array();
	foreach ( $q->posts as $id ) {
		if ( hcc_state( $id ) !== $tab ) {
			continue;
		}
		if ( $search ) {
			$hay = strtolower( hcc_name( $id ) . ' ' . hcc_meta( $id, 'ref' ) . ' ' . hcc_role_name( hcc_meta( $id, 'role_key' ) ) . ' ' . hcc_meta( $id, 'email' ) . ' ' . hcc_meta( $id, 'phone' ) );
			if ( false === strpos( $hay, strtolower( $search ) ) ) {
				continue;
			}
		}
		$out[] = $id;
	}
	return $out;
}

function hcc_last_activity( $id ) {
	$log = (array) hcc_meta( $id, 'log', array() );
	$l   = end( $log );
	return $l && ! empty( $l['time'] ) ? (int) $l['time'] : hcc_started( $id );
}

/** When the candidate created their account (older records fall back to the record date). */
function hcc_started( $id ) {
	$t = (int) hcc_meta( $id, 'registered', 0 );
	return $t ? $t : (int) get_post_time( 'U', true, $id );
}

function hcc_ago( $t ) {
	$h = (int) round( ( time() - $t ) / HOUR_IN_SECONDS );
	if ( $h < 1 ) {
		return 'just now';
	}
	return $h < 24 ? $h . ' hour' . ( $h > 1 ? 's' : '' ) . ' ago' : round( $h / 24 ) . ' day' . ( $h >= 36 ? 's' : '' ) . ' ago';
}

/* ---------- Export and printable report ---------- */

add_action( 'admin_post_hcc_export', function () {
	if ( ! hcc_is_staff() || ! check_admin_referer( 'hcc_export' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$tab = in_array( hcc_field_get( 'tab' ), array( 'in_progress', 'completed', 'closed' ), true ) ? hcc_field_get( 'tab' ) : 'in_progress';
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="hcc-candidates-' . $tab . '-' . wp_date( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fputcsv( $out, array( 'Reference', 'First name', 'Surname', 'Email', 'Mobile', 'Postcode', 'Role', 'Stage', 'Registered', 'Submitted', 'Profile %', 'Documents added', 'Training added', 'Right to work', 'DBS', 'Signed off', 'Signed off by', 'Outcome' ) );
	foreach ( hcc_desk_list( $tab ) as $id ) {
		$a  = (array) hcc_meta( $id, 'answers', array() );
		$d  = hcc_counts( $id, hcc_items( $id, 'docs' ) );
		$t  = hcc_counts( $id, hcc_items( $id, 'train' ) );
		$cm = hcc_meta( $id, 'completed' );
		$cl = hcc_meta( $id, 'closed' );
		$end = is_array( $cm ) ? $cm : ( is_array( $cl ) ? $cl : array() );
		fputcsv( $out, array_map( 'hcc_csv_safe', array( hcc_meta( $id, 'ref' ), hcc_meta( $id, 'first' ), hcc_meta( $id, 'last' ), hcc_meta( $id, 'email' ), hcc_meta( $id, 'phone' ), hcc_meta( $id, 'postcode' ), hcc_role_name( hcc_meta( $id, 'role_key' ) ), hcc_meta( $id, 'stage' ), wp_date( 'Y-m-d', hcc_started( $id ) ), hcc_meta( $id, 'submitted' ) ? wp_date( 'Y-m-d', (int) hcc_meta( $id, 'submitted' ) ) : '', hcc_progress( $id ), $d['have'] . '/' . $d['need'], $t['have'] . '/' . $t['need'], isset( $a['british'] ) && 'Yes' === $a['british'] ? 'British/Irish' : ( isset( $a['visa'] ) ? $a['visa'] : '' ), isset( $a['dbs'] ) ? $a['dbs'] : '', $end ? wp_date( 'Y-m-d', (int) $end['at'] ) : '', $end ? $end['by'] : '', is_array( $cl ) ? $cl['outcome'] : '' ) ) );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
} );

/** Text typed by candidates can't run as a formula when the export is opened in Excel. */
function hcc_csv_safe( $v ) {
	$v = (string) $v;
	return '' !== $v && in_array( $v[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ? "'" . $v : $v;
}

function hcc_field_get( $k ) {
	return isset( $_GET[ $k ] ) ? sanitize_text_field( wp_unslash( $_GET[ $k ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
}

add_action( 'admin_post_hcc_report', function () {
	$id = absint( hcc_field_get( 'a' ) );
	if ( ! $id || ! hcc_is_staff() || ! check_admin_referer( 'hcc_report_' . $id ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	hcc_log( $id, 'Opened candidate report' );
	echo hcc_summary_page( $id, hcc_is_manager() ) . '<script>window.print && setTimeout(function(){window.print()},400)</script>'; // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
} );
