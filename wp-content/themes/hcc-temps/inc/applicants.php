<?php
/**
 * Applicants: one private record per application or registration, holding the
 * candidate's details, CV and uploaded documents. Files live in a private folder
 * and are only served to logged-in staff through hcc_download.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'hcc_applicant', array(
		'labels'          => array(
			'name'          => 'Applicants',
			'singular_name' => 'Applicant',
			'edit_item'     => 'Applicant',
			'all_items'     => 'All applicants',
			'search_items'  => 'Search applicants',
		),
		'public'          => false,
		'show_ui'         => true,
		'menu_icon'       => 'dashicons-groups',
		'menu_position'   => 26,
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

/** Documents each role needs. Each row: label, help text, required. */
function hcc_checklist( $role ) {
	$id     = array( 'Photo ID', 'Passport or UK driving licence', true );
	$rtw    = array( 'Right to work in the UK', 'Passport, share code or BRP', true );
	$addr   = array( 'Proof of address', 'Bank statement or utility bill from the last 3 months', true );
	$dbs    = array( 'DBS certificate', 'Enhanced DBS, or tell us if you need one', true );
	$dbs_op = array( 'DBS certificate', 'Needed for schools and care homes', false );
	$r      = strtolower( $role );
	if ( false !== strpos( $r, 'nurse' ) || false !== strpos( $r, 'rgn' ) ) {
		$list = array( $id, $rtw, array( 'NMC PIN', 'Your current NMC registration', true ), $dbs, $addr, array( 'Mandatory training', 'Manual handling, safeguarding, BLS', false ) );
	} elseif ( preg_match( '/care|health|hca/', $r ) ) {
		$list = array( $id, $rtw, $dbs, array( 'Care Certificate or NVQ', 'Or other care training certificates', true ), $addr, array( 'Mandatory training', 'Manual handling, safeguarding, first aid', false ) );
	} elseif ( preg_match( '/chef|cook|kitchen|catering/', $r ) ) {
		$list = array( $id, $rtw, array( 'Food Hygiene Level 2', 'Certificate in date', true ), $addr, $dbs_op );
	} else {
		$list = array( $id, $rtw, $addr, $dbs_op );
	}
	return apply_filters( 'hcc_checklist', $list, $role );
}

/** Private folder for one applicant. The random suffix keeps paths unguessable on servers that ignore .htaccess. */
function hcc_private_dir( $applicant_id ) {
	$up   = wp_upload_dir();
	$base = trailingslashit( $up['basedir'] ) . 'hcc-private';
	if ( ! is_dir( $base ) ) {
		wp_mkdir_p( $base );
		file_put_contents( $base . '/.htaccess', "Require all denied\nDeny from all\n" );
		file_put_contents( $base . '/index.php', "<?php // Silence.\n" );
	}
	$folder = get_post_meta( $applicant_id, '_hcc_folder', true );
	if ( ! $folder ) {
		$folder = get_post_meta( $applicant_id, '_hcc_ref', true ) . '-' . wp_generate_password( 12, false );
		update_post_meta( $applicant_id, '_hcc_folder', $folder );
	}
	$dir = $base . '/' . $folder;
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		file_put_contents( $dir . '/index.php', "<?php // Silence.\n" );
	}
	return $dir;
}

/** File name the compliance team sees: REF_Surname-First_DocType_YYYY-MM-DD.ext */
function hcc_doc_filename( $ref, $first, $last, $doc, $ext ) {
	$doc_key = str_replace( ' ', '', ucwords( preg_replace( '/[^A-Za-z0-9 ]+/', ' ', $doc ) ) );
	$name    = sanitize_file_name( remove_accents( $last ) ) . '-' . sanitize_file_name( remove_accents( $first ) );
	return $ref . '_' . $name . '_' . $doc_key . '_' . current_time( 'Y-m-d' ) . '.' . strtolower( $ext );
}

/* ---------- Admin list ---------- */

add_filter( 'manage_hcc_applicant_posts_columns', function () {
	return array(
		'cb'        => '<input type="checkbox">',
		'title'     => 'Applicant',
		'hcc_role'  => 'Role',
		'hcc_phone' => 'Phone',
		'hcc_docs'  => 'Documents',
		'date'      => 'Applied',
	);
} );

add_action( 'manage_hcc_applicant_posts_custom_column', function ( $col, $id ) {
	if ( 'hcc_role' === $col ) {
		echo esc_html( get_post_meta( $id, '_hcc_role', true ) );
	} elseif ( 'hcc_phone' === $col ) {
		echo esc_html( get_post_meta( $id, '_hcc_phone', true ) );
	} elseif ( 'hcc_docs' === $col ) {
		$docs     = (array) get_post_meta( $id, '_hcc_docs', true );
		$required = array_filter( hcc_checklist( get_post_meta( $id, '_hcc_role', true ) ), function ( $d ) { return $d[2]; } );
		$have     = 0;
		foreach ( $required as $d ) {
			$have += isset( $docs[ $d[0] ] ) ? 1 : 0;
		}
		$done = $have === count( $required );
		printf( '<strong style="color:%s">%d of %d required</strong>', $done ? '#237D74' : '#B4462F', (int) $have, count( $required ) );
	}
}, 10, 2 );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'hcc_applicant', 'Details and documents', 'hcc_applicant_box', 'hcc_applicant', 'normal', 'high' );
	remove_meta_box( 'slugdiv', 'hcc_applicant', 'normal' );
} );

function hcc_download_url( $id, $key ) {
	return wp_nonce_url( admin_url( 'admin-post.php?action=hcc_download&applicant=' . (int) $id . '&doc=' . rawurlencode( $key ) ), 'hcc_download_' . $id );
}

function hcc_applicant_box( $post ) {
	$m = function ( $k ) use ( $post ) { return get_post_meta( $post->ID, '_hcc_' . $k, true ); };
	$rows = array(
		'Reference' => $m( 'ref' ), 'Role' => $m( 'role' ), 'Job' => $m( 'job_id' ) ? get_the_title( $m( 'job_id' ) ) : 'Registered (no specific job)',
		'Email' => $m( 'email' ), 'Phone' => $m( 'phone' ), 'Postcode' => $m( 'postcode' ), 'Right to work' => $m( 'rtw' ), 'Will travel' => $m( 'travel' ),
		'Upload link expires' => $m( 'token_expires' ) ? wp_date( 'j M Y, H:i', (int) $m( 'token_expires' ) ) : '',
	);
	echo '<table class="widefat striped"><tbody>';
	foreach ( $rows as $label => $val ) {
		if ( '' !== $val ) {
			echo '<tr><th style="width:200px">' . esc_html( $label ) . '</th><td>' . esc_html( $val ) . '</td></tr>';
		}
	}
	echo '</tbody></table><h3>Documents</h3><table class="widefat striped"><thead><tr><th>Document</th><th>File</th><th>Uploaded</th></tr></thead><tbody>';
	$docs = (array) $m( 'docs' );
	$list = array_merge( array( array( 'CV', 'CV', true ) ), hcc_checklist( $m( 'role' ) ) );
	foreach ( $list as $d ) {
		$f = isset( $docs[ $d[0] ] ) ? $docs[ $d[0] ] : null;
		echo '<tr><td>' . esc_html( $d[0] ) . ( $d[2] ? '' : ' <em>(optional)</em>' ) . '</td><td>';
		echo $f ? '<a href="' . esc_url( hcc_download_url( $post->ID, $d[0] ) ) . '">' . esc_html( $f['file'] ) . '</a>' : '<span style="color:#B4462F">Not uploaded</span>';
		echo '</td><td>' . ( $f ? esc_html( wp_date( 'j M Y', (int) $f['time'] ) ) : '' ) . '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<p style="margin-top:16px"><a class="button button-primary" href="' . esc_url( hcc_staff_url( array( 'candidate' => $post->ID ) ) ) . '">Open in Staff portal</a></p>';
}

add_action( 'admin_post_hcc_download', function () {
	$id  = isset( $_GET['applicant'] ) ? absint( $_GET['applicant'] ) : 0;
	$key = isset( $_GET['doc'] ) ? sanitize_text_field( wp_unslash( $_GET['doc'] ) ) : '';
	if ( ! $id || ! current_user_can( 'edit_post', $id ) || ! check_admin_referer( 'hcc_download_' . $id ) ) {
		wp_die( 'You do not have permission to view this file.', 403 );
	}
	$docs = (array) get_post_meta( $id, '_hcc_docs', true );
	if ( empty( $docs[ $key ] ) ) {
		wp_die( 'File not found.', 404 );
	}
	$path = hcc_private_dir( $id ) . '/' . basename( $docs[ $key ]['file'] );
	if ( ! is_readable( $path ) ) {
		wp_die( 'File not found.', 404 );
	}
	$type = wp_check_filetype( $path );
	nocache_headers();
	header( 'Content-Type: ' . ( $type['type'] ? $type['type'] : 'application/octet-stream' ) );
	header( 'Content-Disposition: attachment; filename="' . basename( $path ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path );
	exit;
} );

add_action( 'admin_post_hcc_resend_link', function () {
	$id = isset( $_GET['applicant'] ) ? absint( $_GET['applicant'] ) : 0;
	if ( ! $id || ! current_user_can( 'edit_post', $id ) || ! check_admin_referer( 'hcc_resend_' . $id ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	hcc_send_upload_email( $id, true );
	wp_safe_redirect( admin_url( 'post.php?post=' . $id . '&action=edit&hcc_resent=1' ) );
	exit;
} );

add_action( 'admin_notices', function () {
	if ( isset( $_GET['hcc_resent'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>A new upload link has been emailed to the candidate.</p></div>';
	}
} );
