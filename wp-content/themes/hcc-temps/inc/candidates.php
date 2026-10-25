<?php
/**
 * Candidate records: one private hcc_applicant post per candidate, linked to their WordPress user.
 *
 * Meta (all prefixed _hcc_):
 *   ref, first, last, email, phone, addr1, town, postcode, role_key, user_id, folder
 *   step (registration step reached, 1-7), registered, prescreen_done, submitted
 *   answers   array of pre-screening answers
 *   files     key => array( files => [ [name, at] ], v, at, missing, pin, exp )
 *   review    key => array( status approved|override|amend|requested, reason, by, at )
 *   dnotes    key => list of notes on one document
 *   cnotes    list of compliance notes / messages
 *   checks    rtw|dbs|nmc|id => array( date, by, result, outcome, visa_exp )
 *   refs      list of referees with sent/received/outcome and history
 *   queue     changes waiting to be emailed to the candidate
 *   pending   uploads waiting for the staff digest email
 *   log       activity log
 *   dec_sent, dec_signed, completed, closed, dup_ok, stage
 */

defined( 'ABSPATH' ) || exit;

function hcc_meta( $id, $key, $default = '' ) {
	$v = get_post_meta( $id, '_hcc_' . $key, true );
	return ( '' === $v || null === $v ) ? $default : $v;
}

function hcc_set( $id, $key, $value ) {
	update_post_meta( $id, '_hcc_' . $key, $value );
}

function hcc_name( $id ) {
	return trim( hcc_meta( $id, 'first' ) . ' ' . hcc_meta( $id, 'last' ) );
}

function hcc_applicant_for_user( $user_id ) {
	$id = (int) get_user_meta( $user_id, 'hcc_applicant_id', true );
	return ( $id && 'hcc_applicant' === get_post_type( $id ) ) ? $id : 0;
}

/** Activity log entry. $by defaults to the logged-in person. */
function hcc_log( $id, $text, $by = null ) {
	if ( null === $by ) {
		$u  = wp_get_current_user();
		$by = $u && $u->ID ? ( $u->first_name ? $u->first_name : $u->display_name ) : 'System';
	}
	$log   = (array) hcc_meta( $id, 'log', array() );
	$log[] = array( 'time' => time(), 'text' => $text, 'by' => $by );
	hcc_set( $id, 'log', $log );
}

function hcc_staff_name() {
	$u = wp_get_current_user();
	return $u && $u->ID ? ( $u->first_name ? $u->first_name : $u->display_name ) : '';
}

/** Where a candidate is: in_progress, completed or closed. */
function hcc_state( $id ) {
	if ( hcc_meta( $id, 'closed' ) ) {
		return 'closed';
	}
	return hcc_meta( $id, 'completed' ) ? 'completed' : 'in_progress';
}

/* ---------- Checklist items and their status ---------- */

/** Documents or training for a candidate, with "not needed" rules from their answers. */
function hcc_items( $id, $set = 'docs' ) {
	$role = hcc_meta( $id, 'role_key', 'nurse' );
	$a    = (array) hcc_meta( $id, 'answers', array() );
	if ( 'train' === $set ) {
		return hcc_role_training( $role );
	}
	$docs = hcc_role_docs( $role );
	foreach ( $docs as $k => &$d ) {
		$d['na'] = false;
		$d['note'] = $d['hint'];
		if ( 'rtw' === $k ) {
			if ( isset( $a['british'] ) && 'Yes' === $a['british'] ) {
				$d['na']   = true;
				$d['note'] = 'Not needed: as a British or Irish citizen your passport covers this.';
			} elseif ( ! empty( $a['visa'] ) ) {
				$d['note'] = $a['visa'] . '. ' . ( 'Student visa' === $a['visa'] ? 'Include your university term dates letter.' : ( false !== strpos( $a['visa'], 'Worker' ) ? 'Include your certificate of sponsorship.' : 'Include your share code result and any supporting letter.' ) );
			}
		}
		if ( 'refs' === $k ) {
			$d['na']   = true;
			$d['note'] = 'Nothing to upload: we contact the referees you gave us.';
		}
		if ( 'dbs' === $k && isset( $a['dbs'] ) ) {
			if ( 'none' === $a['dbs'] ) {
				$d['na']   = true;
				$d['note'] = 'You told us you don\'t have one. We will apply for an enhanced DBS for you.';
			} else {
				$d['note'] = ( 'update' === $a['dbs'] ? 'On the Update Service' . ( ! empty( $a['dbsno'] ) ? ', certificate ' . $a['dbsno'] : '' ) . '. ' : 'We will check whether it can be used. ' ) . 'Upload both pages.';
			}
		}
	}
	return $docs;
}

function hcc_all_items( $id ) {
	return array_merge( hcc_items( $id, 'docs' ), hcc_items( $id, 'train' ) );
}

function hcc_file_state( $id, $key ) {
	$files = (array) hcc_meta( $id, 'files', array() );
	return isset( $files[ $key ] ) ? (array) $files[ $key ] : array();
}

function hcc_has_upload( $state ) {
	return ! empty( $state['files'] ) || ! empty( $state['pin'] );
}

/** na | approved | override | review | amend | requested | todo */
function hcc_item_status( $id, $key, $item = null ) {
	if ( $item && ! empty( $item['na'] ) ) {
		return 'na';
	}
	$review = (array) hcc_meta( $id, 'review', array() );
	if ( ! empty( $review[ $key ]['status'] ) ) {
		return $review[ $key ]['status'];
	}
	return hcc_has_upload( hcc_file_state( $id, $key ) ) ? 'review' : 'todo';
}

function hcc_set_review( $id, $key, $status, $reason = '' ) {
	$review = (array) hcc_meta( $id, 'review', array() );
	if ( '' === $status ) {
		unset( $review[ $key ] );
	} else {
		$review[ $key ] = array( 'status' => $status, 'reason' => $reason, 'by' => hcc_staff_name(), 'at' => time() );
	}
	hcc_set( $id, 'review', $review );
}

/** Counts for a set of items. */
function hcc_counts( $id, $items ) {
	$c = array( 'need' => 0, 'have' => 0, 'ok' => 0, 'review' => 0, 'todo' => 0, 'amend' => 0, 'later' => 0 );
	foreach ( $items as $k => $it ) {
		if ( ! empty( $it['na'] ) ) {
			continue;
		}
		$c['need']++;
		$st = hcc_file_state( $id, $k );
		if ( hcc_has_upload( $st ) ) {
			$c['have']++;
		} elseif ( ! empty( $st['missing'] ) ) {
			$c['later']++;
		}
		$s = hcc_item_status( $id, $k, $it );
		if ( in_array( $s, array( 'approved', 'override' ), true ) ) {
			$c['ok']++;
		} elseif ( 'review' === $s ) {
			$c['review']++;
		} elseif ( 'amend' === $s ) {
			$c['amend']++;
		} else {
			$c['todo']++;
		}
	}
	return $c;
}

function hcc_progress( $id ) {
	$c = hcc_counts( $id, hcc_all_items( $id ) );
	return $c['need'] ? (int) round( $c['ok'] / $c['need'] * 100 ) : 0;
}

/* ---------- Storing files ---------- */

function hcc_allowed_types() {
	return array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'heic' => 'image/heic', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' );
}

/**
 * Save one or more uploaded files for a checklist item.
 * Names: "01 - Alex Morgan - CV.pdf", training "T03 - Alex Morgan - Safeguarding Adults.pdf",
 * replacements "(v2)", several files for one item "(1)", "(2)". Earlier versions stay in the folder.
 *
 * @param array $uploads list of $_FILES-style arrays
 * @return array|WP_Error saved file names
 */
function hcc_store_item( $id, $key, $item, $uploads ) {
	$saved = array();
	$prev  = hcc_file_state( $id, $key );
	$v     = ! empty( $prev['files'] ) ? ( isset( $prev['v'] ) ? (int) $prev['v'] : 1 ) + 1 : 1;
	$base  = $item['num'] . ' - ' . trim( preg_replace( '/[^A-Za-z0-9 \'-]+/', '', remove_accents( hcc_name( $id ) ) ) ) . ' - ' . $item['short'] . ( $v > 1 ? ' (v' . $v . ')' : '' );
	$base  = str_replace( array( '/', '\\', ':', '*', '?', '"', '<', '>', '|' ), '-', $base );
	$dir   = hcc_private_dir( $id );
	$many  = count( $uploads ) > 1;
	foreach ( array_values( $uploads ) as $n => $f ) {
		if ( empty( $f['name'] ) || UPLOAD_ERR_OK !== (int) $f['error'] ) {
			return new WP_Error( 'missing', 'The file did not arrive. Please try again.' );
		}
		if ( $f['size'] > 10 * MB_IN_BYTES ) {
			return new WP_Error( 'size', 'Files must be 10MB or smaller.' );
		}
		$check = wp_check_filetype_and_ext( $f['tmp_name'], $f['name'], hcc_allowed_types() );
		if ( empty( $check['ext'] ) ) {
			return new WP_Error( 'type', 'Please upload a PDF, JPG, PNG or Word file.' );
		}
		if ( 'cv' !== $key && in_array( $check['ext'], array( 'doc', 'docx' ), true ) && 'cv' !== $key ) {
			// Word files cannot be previewed in the browser; allowed, but PDFs are preferred.
		}
		$name = $base . ( $many ? ' (' . ( $n + 1 ) . ')' : '' ) . '.' . strtolower( $check['ext'] );
		if ( ! @move_uploaded_file( $f['tmp_name'], $dir . '/' . $name ) && ! @rename( $f['tmp_name'], $dir . '/' . $name ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
			return new WP_Error( 'move', 'We could not save your file. Please try again.' );
		}
		$saved[] = array( 'name' => $name, 'at' => time(), 'size' => (int) $f['size'] );
	}
	$files         = (array) hcc_meta( $id, 'files', array() );
	$state         = isset( $files[ $key ] ) ? (array) $files[ $key ] : array();
	$state['old']  = array_merge( isset( $state['old'] ) ? (array) $state['old'] : array(), isset( $state['files'] ) ? (array) $state['files'] : array() );
	$state['files'] = $saved;
	$state['v']     = $v;
	$state['at']    = time();
	unset( $state['missing'] );
	$files[ $key ] = $state;
	hcc_set( $id, 'files', $files );
	return wp_list_pluck( $saved, 'name' );
}

/** Update non-file parts of an item: missing flag, PIN, expiry date. */
function hcc_update_item( $id, $key, $changes ) {
	$files = (array) hcc_meta( $id, 'files', array() );
	$state = isset( $files[ $key ] ) ? (array) $files[ $key ] : array();
	foreach ( $changes as $k => $v ) {
		if ( null === $v || '' === $v || false === $v ) {
			unset( $state[ $k ] );
		} else {
			$state[ $k ] = $v;
		}
	}
	$files[ $key ] = $state;
	hcc_set( $id, 'files', $files );
}

/* ---------- Serving private files ---------- */

function hcc_file_url( $id, $key, $n = 0, $download = false ) {
	return add_query_arg( array( 'action' => 'hcc_file', 'a' => $id, 'k' => $key, 'n' => $n, 'dl' => $download ? 1 : 0, '_n' => wp_create_nonce( 'hcc_file_' . $id ) ), admin_url( 'admin-post.php' ) );
}

add_action( 'admin_post_hcc_file', function () {
	$id  = isset( $_GET['a'] ) ? absint( $_GET['a'] ) : 0;
	$key = isset( $_GET['k'] ) ? sanitize_key( $_GET['k'] ) : '';
	$n   = isset( $_GET['n'] ) ? absint( $_GET['n'] ) : 0;
	$own = $id && hcc_applicant_for_user( get_current_user_id() ) === $id;
	if ( ! $id || ! ( hcc_is_staff() || $own ) || ! isset( $_GET['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_n'] ) ), 'hcc_file_' . $id ) ) {
		wp_die( 'You do not have permission to view this file.', 403 );
	}
	$st   = hcc_file_state( $id, $key );
	$name = isset( $st['files'][ $n ]['name'] ) ? $st['files'][ $n ]['name'] : '';
	$path = $name ? hcc_private_dir( $id ) . '/' . basename( $name ) : '';
	if ( ! $path || ! is_readable( $path ) ) {
		wp_die( 'File not found.', 404 );
	}
	$dl = ! empty( $_GET['dl'] );
	if ( hcc_is_staff() ) {
		hcc_log( $id, ( $dl ? 'Downloaded ' : 'Viewed ' ) . basename( $path ) );
	}
	$type = wp_check_filetype( $path );
	nocache_headers();
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Content-Type: ' . ( $type['type'] ? $type['type'] : 'application/octet-stream' ) );
	header( 'Content-Disposition: ' . ( $dl ? 'attachment' : 'inline' ) . '; filename="' . basename( $path ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
} );

/** Zip: one folder per candidate, documents in checklist order, Training/ subfolder, summary page. Latest versions only. */
add_action( 'admin_post_hcc_zip', function () {
	$id = isset( $_GET['a'] ) ? absint( $_GET['a'] ) : 0;
	if ( ! $id || ! hcc_is_staff() || ! check_admin_referer( 'hcc_zip_' . $id ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	if ( ! class_exists( 'ZipArchive' ) ) {
		wp_die( 'Zip downloads are not available on this server. Download files one by one.' );
	}
	$folder = hcc_meta( $id, 'ref' ) . ' ' . hcc_name( $id ) . ' - ' . hcc_role_name( hcc_meta( $id, 'role_key' ) );
	$folder = trim( str_replace( array( '/', '\\', ':' ), '-', $folder ) );
	$dir    = hcc_private_dir( $id );
	$tmp    = wp_tempnam( 'hcc-zip' );
	$zip    = new ZipArchive();
	$zip->open( $tmp, ZipArchive::OVERWRITE );
	foreach ( array( 'docs' => '', 'train' => 'Training/' ) as $set => $sub ) {
		foreach ( hcc_items( $id, $set ) as $k => $it ) {
			$st = hcc_file_state( $id, $k );
			foreach ( isset( $st['files'] ) ? $st['files'] : array() as $f ) {
				if ( is_readable( $dir . '/' . $f['name'] ) ) {
					$zip->addFile( $dir . '/' . $f['name'], $folder . '/' . $sub . $f['name'] );
				}
			}
		}
	}
	$zip->addFromString( $folder . '/' . hcc_meta( $id, 'ref' ) . ' ' . hcc_name( $id ) . ' - Summary.html', hcc_summary_page( $id ) );
	$zip->close();
	hcc_log( $id, 'Downloaded all files (zip)' );
	nocache_headers();
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . $folder . '.zip"' );
	header( 'Content-Length: ' . filesize( $tmp ) );
	readfile( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	unlink( $tmp );
	exit;
} );

/* ---------- Answers, summary, duplicates ---------- */

/** Pre-screening answers as labelled groups (used by the portal, staff folder, emails, report). */
function hcc_answer_groups( $id, $with_bank = true ) {
	$a = (array) hcc_meta( $id, 'answers', array() );
	$g = function ( $k ) use ( $a ) { return isset( $a[ $k ] ) ? $a[ $k ] : ''; };
	$dbs = hcc_dbs_options();
	$groups = array(
		'Candidate'         => array( 'Reference' => hcc_meta( $id, 'ref' ), 'Role applied for' => hcc_role_name( hcc_meta( $id, 'role_key' ) ), 'Title' => $g( 'title' ), 'First name' => hcc_meta( $id, 'first' ), 'Surname' => hcc_meta( $id, 'last' ), 'Date of birth' => $g( 'dob' ) ? wp_date( 'd/m/Y', strtotime( $g( 'dob' ) ) ) : '', 'Email' => hcc_meta( $id, 'email' ), 'Mobile' => hcc_meta( $id, 'phone' ), 'Address' => trim( hcc_meta( $id, 'addr1' ) . ', ' . hcc_meta( $id, 'town' ) . ' ' . hcc_meta( $id, 'postcode' ), ', ' ), 'NI number' => $g( 'ni' ) ),
		'Right to work'     => array( 'British or Irish citizen' => $g( 'british' ), 'Visa / status' => 'No' === $g( 'british' ) ? $g( 'visa' ) : '', 'Share code' => $g( 'share' ), 'Driving licence' => $g( 'licence' ), 'Own vehicle' => $g( 'vehicle' ) ),
		'DBS'               => array( 'Enhanced DBS' => isset( $dbs[ $g( 'dbs' ) ] ) ? $dbs[ $g( 'dbs' ) ] : '', 'Certificate number' => $g( 'dbsno' ) ),
		'Experience'        => array( 'Education' => $g( 'edu' ), 'Employment status' => $g( 'emp' ), 'Experience in role' => $g( 'exp' ), 'Training in date' => $g( 'trainok' ), 'Uniform' => trim( $g( 'uniform' ) . ( $g( 'size' ) ? ' · size ' . $g( 'size' ) : '' ) ) ),
		'Referee 1'         => array( 'Name' => $g( 'r1n' ), 'Position' => $g( 'r1p' ), 'Email' => $g( 'r1e' ), 'Phone' => $g( 'r1t' ) ),
		'Referee 2'         => array( 'Name' => $g( 'r2n' ), 'Position' => $g( 'r2p' ), 'Email' => $g( 'r2e' ), 'Phone' => $g( 'r2t' ) ),
		'Emergency contact' => array( 'Name' => trim( $g( 'kn' ) . ( $g( 'kr' ) ? ' (' . $g( 'kr' ) . ')' : '' ) ), 'Phone' => $g( 'kt' ), 'Address' => $g( 'ka' ) ),
	);
	if ( $with_bank ) {
		$groups['Bank details'] = array( 'Account name' => $g( 'ban' ), 'Type' => $g( 'btype' ), 'Sort code' => $g( 'bsc' ), 'Account number' => $g( 'bno' ) );
	}
	return $groups;
}

/** Printable summary (zip file and candidate report). */
function hcc_summary_page( $id ) {
	$h = '<!doctype html><meta charset="utf-8"><title>' . esc_html( hcc_meta( $id, 'ref' ) . ' ' . hcc_name( $id ) ) . '</title><style>body{font:14px Arial,sans-serif;color:#1E2140;max-width:760px;margin:24px auto;padding:0 16px}h1{font-size:22px}h2{font-size:15px;background:#171A45;color:#fff;padding:6px 8px;margin:18px 0 0}table{width:100%;border-collapse:collapse}td,th{border-bottom:1px solid #ddd;padding:5px 8px;text-align:left;vertical-align:top}th{color:#5B6078;width:42%;font-weight:normal}</style>';
	$h .= '<h1>' . esc_html( hcc_name( $id ) ) . ' · ' . esc_html( hcc_meta( $id, 'ref' ) ) . '</h1><p>' . esc_html( hcc_role_name( hcc_meta( $id, 'role_key' ) ) ) . ' · Registered ' . esc_html( wp_date( 'j M Y', (int) hcc_meta( $id, 'registered', time() ) ) ) . ' · Profile ' . hcc_progress( $id ) . '% complete</p>';
	foreach ( hcc_answer_groups( $id ) as $title => $rows ) {
		$h .= '<h2>' . esc_html( $title ) . '</h2><table>';
		foreach ( $rows as $k => $v ) {
			$h .= '<tr><th>' . esc_html( $k ) . '</th><td>' . esc_html( $v ? $v : '—' ) . '</td></tr>';
		}
		$h .= '</table>';
	}
	foreach ( array( 'docs' => 'Documents', 'train' => 'Training certificates' ) as $set => $title ) {
		$h .= '<h2>' . $title . '</h2><table>';
		foreach ( hcc_items( $id, $set ) as $k => $it ) {
			$h .= '<tr><th>' . esc_html( $it['label'] ) . '</th><td>' . esc_html( hcc_status_label( hcc_item_status( $id, $k, $it ) ) ) . '</td></tr>';
		}
		$h .= '</table>';
	}
	$checks = (array) hcc_meta( $id, 'checks', array() );
	if ( $checks ) {
		$h .= '<h2>Checks</h2><table>';
		foreach ( $checks as $k => $c ) {
			$h .= '<tr><th>' . esc_html( hcc_check_label( $k ) ) . '</th><td>' . esc_html( $c['result'] . ' · ' . $c['date'] . ' · ' . $c['by'] . ( ! empty( $c['outcome'] ) ? ' · ' . $c['outcome'] : '' ) . ( ! empty( $c['visa_exp'] ) ? ' · visa expires ' . $c['visa_exp'] : '' ) ) . '</td></tr>';
		}
		$h .= '</table>';
	}
	return $h;
}

function hcc_status_label( $s ) {
	$l = array( 'approved' => 'Approved', 'override' => 'Completed by staff', 'review' => 'Uploaded, to review', 'amend' => 'Replacement requested', 'requested' => 'Requested from candidate', 'todo' => 'Not submitted', 'na' => 'Not needed' );
	return isset( $l[ $s ] ) ? $l[ $s ] : $s;
}

function hcc_check_label( $k ) {
	$l = array( 'rtw' => 'Right to work', 'dbs' => 'DBS', 'nmc' => 'NMC register', 'id' => 'Identity' );
	return isset( $l[ $k ] ) ? $l[ $k ] : $k;
}

/** Other candidates with the same NI number or phone number (never name alone). */
function hcc_duplicates( $id ) {
	$norm = function ( $x ) { return strtoupper( preg_replace( '/[^0-9A-Za-z]/', '', (string) $x ) ); };
	$a    = (array) hcc_meta( $id, 'answers', array() );
	$ni   = $norm( isset( $a['ni'] ) ? $a['ni'] : '' );
	$ph   = $norm( hcc_meta( $id, 'phone' ) );
	if ( ! $ni && ! $ph ) {
		return array();
	}
	$out = array();
	$q   = new WP_Query( array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'post__not_in' => array( $id ), 'no_found_rows' => true ) );
	foreach ( $q->posts as $other ) {
		$on = array();
		$oa = (array) hcc_meta( $other, 'answers', array() );
		if ( $ni && $norm( isset( $oa['ni'] ) ? $oa['ni'] : '' ) === $ni ) {
			$on[] = 'NI number';
		}
		if ( $ph && $norm( hcc_meta( $other, 'phone' ) ) === $ph ) {
			$on[] = 'phone number';
		}
		if ( $on ) {
			$out[] = array( 'id' => $other, 'on' => $on );
		}
	}
	return $out;
}
