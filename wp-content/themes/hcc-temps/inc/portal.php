<?php
/**
 * Candidate portal and Staff portal.
 *
 * Flow: a candidate applies (details + CV) → staff review it in the Staff portal
 * and press "Approve & invite" → WordPress creates a candidate login and emails a
 * "create your password" link → the candidate logs in, uploads each document →
 * staff mark each one Approved or Needs amendment and leave notes → the candidate
 * sees every change on their "My application" page and by email.
 *
 * Accounts are normal WordPress users with three theme roles:
 *   hcc_candidate  – can see only their own application
 *   hcc_staff      – can work with every candidate
 *   hcc_manager    – staff, plus can add other staff
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Roles, pages and set-up (runs once per portal version) ---------- */

define( 'HCC_PORTAL_VERSION', '1' );

add_action( 'init', function () {
	if ( get_option( 'hcc_portal_version' ) === HCC_PORTAL_VERSION ) {
		return;
	}
	add_role( 'hcc_candidate', 'HCC Candidate', array( 'read' => true ) );
	add_role( 'hcc_staff', 'HCC Staff', array( 'read' => true, 'hcc_manage_candidates' => true ) );
	add_role( 'hcc_manager', 'HCC Manager', array( 'read' => true, 'hcc_manage_candidates' => true, 'hcc_manage_staff' => true ) );
	$admin = get_role( 'administrator' );
	if ( $admin ) {
		$admin->add_cap( 'hcc_manage_candidates' );
		$admin->add_cap( 'hcc_manage_staff' );
	}
	$pages = array(
		'portal-login' => array( 'Candidate Login', 'candidate-login' ),
		'portal'       => array( 'My Application', 'my-application' ),
		'staff'        => array( 'Staff Portal', 'staff' ),
	);
	foreach ( $pages as $tpl => $p ) {
		$exists = get_posts( array( 'post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'templates/' . $tpl . '.php', 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids' ) );
		if ( ! $exists ) {
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $p[0], 'post_name' => $p[1] ) );
			update_post_meta( $id, '_wp_page_template', 'templates/' . $tpl . '.php' );
		}
	}
	update_option( 'hcc_portal_version', HCC_PORTAL_VERSION );
}, 30 );

function hcc_is_staff( $user = null ) {
	return $user ? user_can( $user, 'hcc_manage_candidates' ) : current_user_can( 'hcc_manage_candidates' );
}

function hcc_login_url() {
	return hcc_page_url( 'portal-login', '/candidate-login/' );
}
function hcc_portal_url() {
	return hcc_page_url( 'portal', '/my-application/' );
}
function hcc_staff_url( $args = array() ) {
	return add_query_arg( $args, hcc_page_url( 'staff', '/staff/' ) );
}

/* ---------- Documents candidates upload in the portal ---------- */

/**
 * Each document: label, help text, required, type (file|files|sharecode), photo tips.
 * Filter `hcc_portal_docs` to change the list per role.
 */
function hcc_portal_docs( $role = '' ) {
	$id_tips    = array( 'Lay it flat on a plain, dark surface', 'Use daylight or a bright room, no flash', 'Show all four corners, nothing cut off', 'Make sure every word is sharp and readable', 'No glare or shadows across the text' );
	$paper_tips = array( 'Scan it, or photograph it flat from directly above', 'Include every page', 'Make sure your name and the date are readable' );
	$docs = array(
		'passport'  => array( 'Passport', 'The photo page of your current passport.', true, 'file', $id_tips ),
		'licence'   => array( 'Driving licence', 'Front and back of your photocard licence, if you have one.', false, 'files', $id_tips ),
		'rtw'       => array( 'Right to work check', 'Your right to work evidence, for example your BRP, eVisa screenshot or birth certificate with National Insurance letter.', true, 'files', $paper_tips ),
		'sharecode' => array( 'Share code', 'Only if you are not a British or Irish citizen. Get it from gov.uk/prove-right-to-work.', false, 'sharecode', array() ),
		'dbs'       => array( 'DBS certificate', 'Your enhanced DBS certificate. If you are on the Update Service, upload it and we will check it online.', true, 'files', $paper_tips ),
		'training'  => array( 'Training certificates', 'Mandatory training, Care Certificate, NVQ, Food Hygiene or any other certificates for your role.', true, 'files', $paper_tips ),
	);
	return apply_filters( 'hcc_portal_docs', $docs, $role );
}

function hcc_stages() {
	return array(
		'new'       => 'New',
		'invited'   => 'Invited',
		'documents' => 'Documents',
		'checks'    => 'Checks',
		'ready'     => 'Ready to work',
		'closed'    => 'Not progressing',
	);
}

function hcc_doc_statuses() {
	return array(
		''         => array( 'Not uploaded', 'none' ),
		'pending'  => array( 'Received, being checked', 'pending' ),
		'approved' => array( 'Approved', 'approved' ),
		'amend'    => array( 'Needs amendment', 'amend' ),
	);
}

/** Portal data for one applicant: docs, notes, log. */
function hcc_pget( $id, $key ) {
	$v = get_post_meta( $id, '_hcc_' . $key, true );
	return is_array( $v ) ? $v : array();
}

function hcc_log( $id, $text, $by = null ) {
	$log   = hcc_pget( $id, 'log' );
	$user  = $by ? $by : wp_get_current_user();
	$log[] = array( 'time' => time(), 'text' => $text, 'by' => $user && $user->ID ? $user->display_name : 'System' );
	update_post_meta( $id, '_hcc_log', $log );
	update_post_meta( $id, '_hcc_updated', time() );
}

function hcc_set_stage( $id, $stage ) {
	$stages = hcc_stages();
	if ( isset( $stages[ $stage ] ) && get_post_meta( $id, '_hcc_stage', true ) !== $stage ) {
		update_post_meta( $id, '_hcc_stage', $stage );
		$times           = hcc_pget( $id, 'stage_times' );
		$times[ $stage ] = time();
		update_post_meta( $id, '_hcc_stage_times', $times );
		hcc_log( $id, 'Stage changed to ' . $stages[ $stage ] );
	}
}

/** Required documents done (approved) and received (any file). */
function hcc_doc_counts( $id ) {
	$docs = hcc_pget( $id, 'pdocs' );
	$req  = 0;
	$ok   = 0;
	$in   = 0;
	$fix  = 0;
	foreach ( hcc_portal_docs( get_post_meta( $id, '_hcc_role', true ) ) as $key => $d ) {
		$st = isset( $docs[ $key ]['status'] ) ? $docs[ $key ]['status'] : '';
		if ( 'amend' === $st ) {
			$fix++;
		}
		if ( ! $d[2] ) {
			continue;
		}
		$req++;
		$ok += 'approved' === $st ? 1 : 0;
		$in += '' !== $st ? 1 : 0;
	}
	return array( 'required' => $req, 'approved' => $ok, 'received' => $in, 'amend' => $fix );
}

function hcc_applicant_for_user( $user_id ) {
	$id = (int) get_user_meta( $user_id, 'hcc_applicant_id', true );
	return $id && 'hcc_applicant' === get_post_type( $id ) ? $id : 0;
}

/** Guess sector and role type, so the Staff portal can filter. Stored at apply time. */
function hcc_classify_role( $role, $job_id = 0 ) {
	$sector = $job_id ? hcc_first_term( $job_id, 'job_sector' ) : '';
	$type   = $job_id ? hcc_first_term( $job_id, 'job_type' ) : '';
	$r      = strtolower( $role . ' ' . $type );
	if ( ! $type ) {
		$type = $role;
	}
	if ( ! $sector ) {
		if ( preg_match( '/nurse|care|health|hca/', $r ) ) {
			$sector = 'Healthcare';
		} elseif ( preg_match( '/recept|admin/', $r ) ) {
			$sector = 'Admin & Reception';
		} elseif ( preg_match( '/clean|housekeep/', $r ) ) {
			$sector = 'Housekeeping & Cleaning';
		} elseif ( preg_match( '/chef|cook/', $r ) ) {
			$sector = 'Chefs';
		} else {
			$sector = 'Hospitality';
		}
	}
	return array( $sector, $type );
}

add_action( 'save_post_hcc_applicant', function ( $id, $post, $update ) {
	if ( $update || get_post_meta( $id, '_hcc_stage', true ) ) {
		return;
	}
	update_post_meta( $id, '_hcc_stage', 'new' );
	update_post_meta( $id, '_hcc_updated', time() );
}, 10, 3 );

/** Fill sector/type and the first log line once the application handler has saved its meta. */
add_action( 'updated_post_meta', 'hcc_portal_after_apply', 10, 3 );
add_action( 'added_post_meta', 'hcc_portal_after_apply', 10, 3 );
function hcc_portal_after_apply( $meta_id, $id, $key ) {
	if ( '_hcc_job_id' !== $key || 'hcc_applicant' !== get_post_type( $id ) || get_post_meta( $id, '_hcc_sector', true ) ) {
		return;
	}
	list( $sector, $type ) = hcc_classify_role( get_post_meta( $id, '_hcc_role', true ), (int) get_post_meta( $id, '_hcc_job_id', true ) );
	update_post_meta( $id, '_hcc_sector', $sector );
	update_post_meta( $id, '_hcc_role_type', $type );
	$log = hcc_pget( $id, 'log' );
	if ( ! $log ) {
		update_post_meta( $id, '_hcc_log', array( array( 'time' => time(), 'text' => 'Applied with CV', 'by' => 'Candidate' ) ) );
	}
}

/* ---------- Login, redirects and keeping non-admins out of wp-admin ---------- */

add_filter( 'login_redirect', function ( $redirect, $requested, $user ) {
	if ( is_wp_error( $user ) || ! $user ) {
		return $redirect;
	}
	if ( user_can( $user, 'manage_options' ) ) {
		return $requested ? $requested : $redirect;
	}
	if ( hcc_is_staff( $user ) ) {
		return hcc_staff_url();
	}
	return hcc_portal_url();
}, 10, 3 );

add_action( 'admin_init', function () {
	global $pagenow;
	if ( wp_doing_ajax() || 'admin-post.php' === $pagenow || 'profile.php' === $pagenow || current_user_can( 'edit_posts' ) ) {
		return;
	}
	wp_safe_redirect( hcc_is_staff() ? hcc_staff_url() : hcc_portal_url() );
	exit;
} );

add_filter( 'show_admin_bar', function ( $show ) {
	return current_user_can( 'edit_posts' ) ? $show : false;
} );

/** Branded wp-login.php (password set/reset and two-factor screens). */
add_action( 'login_enqueue_scripts', function () {
	?>
	<style>
		body.login { background: #EEF3F2; font-family: Figtree, "Segoe UI", system-ui, sans-serif; }
		body.login::before { content: ""; position: fixed; inset: 0 0 auto; height: 6px; background: linear-gradient(90deg,#7C4925 0 20%,#2D338C 20% 40%,#6DC0B8 40% 60%,#D93831 60% 80%,#F3D45E 80%); }
		#login h1 a, .login h1 a { background-image: url(<?php echo esc_url( hcc_logo_url() ); ?>) !important; background-size: contain !important; background-position: center !important; width: 200px !important; height: 108px !important; }
		.login .message, .login .notice { border-left-color: #6DC0B8 !important; border-radius: 12px; }
		.wp-core-ui .button, .wp-core-ui .button-secondary { color: #2D338C !important; border-color: #2D338C !important; border-radius: 999px !important; }
		.login form { border: 0; border-radius: 18px; box-shadow: 0 16px 40px rgba(23,26,69,.10); }
		.wp-core-ui .button-primary, .login .button-primary { background: #2D338C !important; border-color: #2D338C !important; color: #fff !important; border-radius: 999px !important; padding: 4px 22px !important; }
		.wp-core-ui .button-primary:hover { background: #171A45 !important; border-color: #171A45 !important; }
		.login input[type=text]:focus, .login input[type=password]:focus { border-color: #237D74; box-shadow: 0 0 0 2px #6DC0B8; }
		.login #nav a, .login #backtoblog a { color: #2D338C; }
	</style>
	<?php
} );
add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return 'HCC-Temps'; } );

/** After setting a password, send people to our login page. */
add_action( 'after_password_reset', function ( $user ) {
	update_user_meta( $user->ID, 'hcc_password_set', time() );
	$app = hcc_applicant_for_user( $user->ID );
	if ( $app ) {
		hcc_log( $app, 'Candidate created their password', $user );
	}
} );

/* ---------- Two-factor: email codes for every portal user (needs the Two Factor plugin) ---------- */

add_filter( 'two_factor_enabled_providers_for_user', function ( $providers, $user_id ) {
	$user = get_userdata( $user_id );
	if ( ! $user || ! array_intersect( array( 'hcc_candidate', 'hcc_staff', 'hcc_manager' ), (array) $user->roles ) ) {
		return $providers;
	}
	if ( ! in_array( 'Two_Factor_Email', $providers, true ) && empty( $providers ) ) {
		$providers[] = 'Two_Factor_Email';
	}
	return $providers;
}, 10, 2 );

/* ---------- Emails ---------- */

function hcc_set_password_link( $user ) {
	$key = get_password_reset_key( $user );
	if ( is_wp_error( $key ) ) {
		return '';
	}
	return network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user->user_login ), 'login' );
}

/** Invite a candidate: create (or reuse) their account and email the create-password link. */
function hcc_invite_candidate( $id ) {
	$email = get_post_meta( $id, '_hcc_email', true );
	$first = get_post_meta( $id, '_hcc_first', true );
	$last  = get_post_meta( $id, '_hcc_last', true );
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'email', 'This applicant has no valid email address.' );
	}
	$user_id = (int) get_post_meta( $id, '_hcc_user_id', true );
	if ( ! $user_id ) {
		$existing = get_user_by( 'email', $email );
		if ( $existing && ! hcc_is_staff( $existing ) && ! user_can( $existing, 'edit_posts' ) ) {
			$user_id = $existing->ID;
		} elseif ( $existing ) {
			return new WP_Error( 'email', 'That email belongs to a staff account.' );
		} else {
			$user_id = wp_insert_user( array(
				'user_login'   => $email,
				'user_email'   => $email,
				'user_pass'    => wp_generate_password( 32 ),
				'first_name'   => $first,
				'last_name'    => $last,
				'display_name' => trim( $first . ' ' . $last ),
				'role'         => 'hcc_candidate',
			) );
			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}
		}
		update_post_meta( $id, '_hcc_user_id', $user_id );
		update_user_meta( $user_id, 'hcc_applicant_id', $id );
	}
	$user = get_userdata( $user_id );
	$link = hcc_set_password_link( $user );
	hcc_mail(
		$email,
		'Next step: create your HCC-Temps candidate account',
		'<p>Hi ' . esc_html( $first ) . ',</p><p>Good news. We would like to move forward with your <b>' . esc_html( get_post_meta( $id, '_hcc_role', true ) ) . '</b> application.</p>'
			. '<p>Create your password to open your candidate portal. There you can upload your documents and follow your progress.</p>'
			. hcc_button( $link, 'Create my password' )
			. '<p style="font-size:13px;color:#5B6078">This link works for 24 hours. After that, log in at ' . esc_html( hcc_login_url() ) . ' and choose "Forgotten your password?".</p>'
			. '<p>Your reference is <b>' . esc_html( get_post_meta( $id, '_hcc_ref', true ) ) . '</b>.</p>'
	);
	if ( in_array( get_post_meta( $id, '_hcc_stage', true ), array( '', 'new' ), true ) ) {
		hcc_set_stage( $id, 'invited' );
	}
	update_post_meta( $id, '_hcc_invited', time() );
	hcc_log( $id, 'Invited to the candidate portal' );
	return $user_id;
}

/** Branded "set your password" email for staff created in Users → Add New. */
add_filter( 'wp_new_user_notification_email', function ( $email, $user ) {
	if ( ! hcc_is_staff( $user ) ) {
		return $email;
	}
	$link = hcc_set_password_link( $user );
	$email['subject'] = 'Your HCC-Temps staff portal account';
	$email['message'] = 'Hi ' . $user->first_name . ",\n\nAn account has been created for you on the HCC-Temps Staff portal.\n\nCreate your password here:\n" . $link . "\n\nThen log in at " . hcc_staff_url() . "\n\nYou will be asked for a 6-digit code from your email each time you log in.";
	return $email;
}, 10, 2 );

/* ---------- Serving private files (candidate's own, or staff) ---------- */

function hcc_file_url( $id, $key, $index, $download = false ) {
	return add_query_arg( array( 'action' => 'hcc_file', 'a' => $id, 'k' => $key, 'i' => $index, 'dl' => $download ? 1 : 0, '_n' => wp_create_nonce( 'hcc_file_' . $id ) ), admin_url( 'admin-post.php' ) );
}

add_action( 'admin_post_hcc_file', function () {
	$id  = isset( $_GET['a'] ) ? absint( $_GET['a'] ) : 0;
	$key = isset( $_GET['k'] ) ? sanitize_key( $_GET['k'] ) : '';
	$i   = isset( $_GET['i'] ) ? absint( $_GET['i'] ) : 0;
	$own = $id && hcc_applicant_for_user( get_current_user_id() ) === $id;
	if ( ! $id || ! ( hcc_is_staff() || $own ) || ! isset( $_GET['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_n'] ) ), 'hcc_file_' . $id ) ) {
		wp_die( 'You do not have permission to view this file.', 403 );
	}
	if ( 'cv' === $key ) {
		$docs = (array) get_post_meta( $id, '_hcc_docs', true );
		$name = isset( $docs['CV']['file'] ) ? $docs['CV']['file'] : '';
	} else {
		$docs = hcc_pget( $id, 'pdocs' );
		$name = isset( $docs[ $key ]['files'][ $i ]['file'] ) ? $docs[ $key ]['files'][ $i ]['file'] : '';
	}
	$path = $name ? hcc_private_dir( $id ) . '/' . basename( $name ) : '';
	if ( ! $path || ! is_readable( $path ) ) {
		wp_die( 'File not found.', 404 );
	}
	if ( ! empty( $_GET['dl'] ) ) {
		hcc_log( $id, 'Downloaded ' . basename( $path ) );
	}
	$type = wp_check_filetype( $path );
	nocache_headers();
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Content-Type: ' . ( $type['type'] ? $type['type'] : 'application/octet-stream' ) );
	header( 'Content-Disposition: ' . ( empty( $_GET['dl'] ) ? 'inline' : 'attachment' ) . '; filename="' . basename( $path ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path );
	exit;
} );

/** All of a candidate's files in one zip (staff only). */
add_action( 'admin_post_hcc_zip', function () {
	$id = isset( $_GET['a'] ) ? absint( $_GET['a'] ) : 0;
	if ( ! $id || ! hcc_is_staff() || ! check_admin_referer( 'hcc_zip_' . $id ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	if ( ! class_exists( 'ZipArchive' ) ) {
		wp_die( 'Zip downloads are not available on this server. Download files one by one.' );
	}
	$dir = hcc_private_dir( $id );
	$tmp = wp_tempnam( 'hcc-zip' );
	$zip = new ZipArchive();
	$zip->open( $tmp, ZipArchive::OVERWRITE );
	foreach ( glob( $dir . '/*' ) as $f ) {
		if ( 'index.php' !== basename( $f ) ) {
			$zip->addFile( $f, basename( $f ) );
		}
	}
	$zip->close();
	hcc_log( $id, 'Downloaded all documents' );
	nocache_headers();
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . get_post_meta( $id, '_hcc_ref', true ) . '-documents.zip"' );
	header( 'Content-Length: ' . filesize( $tmp ) );
	readfile( $tmp );
	unlink( $tmp );
	exit;
} );

/* ---------- Candidate: upload a document, save a share code ---------- */

function hcc_portal_back( $args, $anchor = '' ) {
	wp_safe_redirect( add_query_arg( $args, hcc_portal_url() ) . ( $anchor ? '#' . $anchor : '' ) );
	exit;
}

add_action( 'admin_post_hcc_portal_upload', function () {
	$user = wp_get_current_user();
	$id   = hcc_applicant_for_user( $user->ID );
	$key  = isset( $_POST['doc'] ) ? sanitize_key( $_POST['doc'] ) : '';
	$defs = hcc_portal_docs( $id ? get_post_meta( $id, '_hcc_role', true ) : '' );
	if ( ! $id || ! isset( $defs[ $key ] ) || ! check_admin_referer( 'hcc_portal_' . $id ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$def  = $defs[ $key ];
	$docs = hcc_pget( $id, 'pdocs' );
	$doc  = isset( $docs[ $key ] ) ? $docs[ $key ] : array( 'files' => array(), 'status' => '' );

	if ( 'sharecode' === $def[3] ) {
		$code = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', hcc_field( 'sharecode' ) ) );
		$dob  = hcc_field( 'dob' );
		if ( 9 !== strlen( $code ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dob ) ) {
			hcc_portal_back( array( 'err' => 'sharecode' ), 'doc-' . $key );
		}
		$doc['code']   = trim( chunk_split( $code, 3, ' ' ) );
		$doc['dob']    = $dob;
		$doc['status'] = 'pending';
		$doc['reason'] = '';
		$docs[ $key ]  = $doc;
		update_post_meta( $id, '_hcc_pdocs', $docs );
		hcc_log( $id, 'Added share code', $user );
		hcc_portal_notify_staff( $id, array( $def[0] ) );
		hcc_portal_back( array( 'saved' => $key ), 'doc-' . $key );
	}

	$files = isset( $_FILES['files'] ) ? $_FILES['files'] : null;
	if ( ! $files || empty( $files['name'][0] ) ) {
		hcc_portal_back( array( 'err' => 'nofile' ), 'doc-' . $key );
	}
	$allowed = array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png' );
	$m       = function ( $k ) use ( $id ) { return get_post_meta( $id, '_hcc_' . $k, true ); };
	$max     = 'file' === $def[3] ? 1 : 6;
	$new     = array();
	foreach ( array_slice( array_keys( $files['name'] ), 0, $max ) as $n ) {
		if ( UPLOAD_ERR_OK !== $files['error'][ $n ] ) {
			continue;
		}
		if ( $files['size'][ $n ] > 10 * MB_IN_BYTES ) {
			hcc_portal_back( array( 'err' => 'size' ), 'doc-' . $key );
		}
		$check = wp_check_filetype_and_ext( $files['tmp_name'][ $n ], $files['name'][ $n ], $allowed );
		if ( ! $check['ext'] ) {
			hcc_portal_back( array( 'err' => 'type' ), 'doc-' . $key );
		}
		if ( 'pdf' !== $check['ext'] ) {
			$size = @getimagesize( $files['tmp_name'][ $n ] ); // phpcs:ignore
			if ( ! $size || max( $size[0], $size[1] ) < 900 ) {
				hcc_portal_back( array( 'err' => 'small' ), 'doc-' . $key );
			}
		}
		$name = hcc_doc_filename( $m( 'ref' ), $m( 'first' ), $m( 'last' ), $def[0], $check['ext'] );
		$base = preg_replace( '/\.[a-z]+$/', '', $name );
		$k    = 1;
		while ( file_exists( hcc_private_dir( $id ) . '/' . $name ) ) {
			$k++;
			$name = $base . '-' . $k . '.' . $check['ext'];
		}
		if ( move_uploaded_file( $files['tmp_name'][ $n ], hcc_private_dir( $id ) . '/' . $name ) ) {
			$new[] = array( 'file' => $name, 'time' => time() );
		}
	}
	if ( ! $new ) {
		hcc_portal_back( array( 'err' => 'nofile' ), 'doc-' . $key );
	}
	// A re-upload after "needs amendment" replaces the old files; otherwise add to them.
	$doc['files']  = ( 'amend' === $doc['status'] || 'file' === $def[3] ) ? $new : array_merge( $doc['files'], $new );
	$doc['status'] = 'pending';
	$doc['reason'] = '';
	$docs[ $key ]  = $doc;
	update_post_meta( $id, '_hcc_pdocs', $docs );
	hcc_log( $id, 'Uploaded ' . $def[0] . ' (' . count( $new ) . ' file' . ( count( $new ) > 1 ? 's' : '' ) . ')', $user );
	if ( 'invited' === get_post_meta( $id, '_hcc_stage', true ) ) {
		hcc_set_stage( $id, 'documents' );
	}
	hcc_portal_notify_staff( $id, array( $def[0] ) );
	hcc_portal_back( array( 'saved' => $key ), 'doc-' . $key );
} );

function hcc_portal_notify_staff( $id, $labels ) {
	$m = function ( $k ) use ( $id ) { return get_post_meta( $id, '_hcc_' . $k, true ); };
	hcc_mail(
		hcc_opt( 'email_jobs' ),
		'[DOCUMENTS] ' . $m( 'ref' ) . ' ' . $m( 'first' ) . ' ' . $m( 'last' ) . ': ' . implode( ', ', $labels ),
		'<p>' . esc_html( $m( 'first' ) . ' ' . $m( 'last' ) ) . ' added <b>' . esc_html( implode( ', ', $labels ) ) . '</b> in the candidate portal.</p>' . hcc_button( hcc_staff_url( array( 'candidate' => $id ) ), 'Review in Staff portal' )
	);
}

/* ---------- Staff actions ---------- */

function hcc_staff_back( $id, $msg, $anchor = '' ) {
	wp_safe_redirect( hcc_staff_url( array( 'candidate' => $id, 'msg' => $msg ) ) . ( $anchor ? '#' . $anchor : '' ) );
	exit;
}

function hcc_staff_guard( $id ) {
	if ( ! $id || 'hcc_applicant' !== get_post_type( $id ) || ! hcc_is_staff() || ! check_admin_referer( 'hcc_staff_' . $id ) ) {
		wp_die( 'Not allowed.', 403 );
	}
}

add_action( 'admin_post_hcc_staff', function () {
	$id = isset( $_POST['a'] ) ? absint( $_POST['a'] ) : 0;
	hcc_staff_guard( $id );
	$do    = sanitize_key( hcc_field( 'do' ) );
	$m     = function ( $k ) use ( $id ) { return get_post_meta( $id, '_hcc_' . $k, true ); };
	$first = $m( 'first' );

	switch ( $do ) {
		case 'invite':
			$r = hcc_invite_candidate( $id );
			hcc_staff_back( $id, is_wp_error( $r ) ? 'invite_fail' : 'invited' );
			break;

		case 'reset':
			$uid = (int) $m( 'user_id' );
			$u   = $uid ? get_userdata( $uid ) : null;
			if ( ! $u ) {
				hcc_staff_back( $id, 'no_account' );
			}
			hcc_mail( $u->user_email, 'Reset your HCC-Temps password', '<p>Hi ' . esc_html( $first ) . ',</p><p>Use the button below to set a new password for your candidate portal.</p>' . hcc_button( hcc_set_password_link( $u ), 'Set a new password' ) . '<p style="font-size:13px;color:#5B6078">This link works for 24 hours.</p>' );
			hcc_log( $id, 'Sent a password reset email' );
			hcc_staff_back( $id, 'reset' );
			break;

		case 'stage':
			hcc_set_stage( $id, sanitize_key( hcc_field( 'stage' ) ) );
			if ( 'ready' === hcc_field( 'stage' ) && $m( 'user_id' ) ) {
				hcc_mail( $m( 'email' ), "You're ready to work with HCC-Temps", '<p>Hi ' . esc_html( $first ) . ',</p><p>All your checks are complete and you are ready to work. We will be in touch about shifts.</p>' . hcc_button( hcc_portal_url(), 'Open my portal' ) );
			}
			hcc_staff_back( $id, 'saved' );
			break;

		case 'doc':
			$key    = sanitize_key( hcc_field( 'doc' ) );
			$status = sanitize_key( hcc_field( 'status' ) );
			$defs   = hcc_portal_docs( $m( 'role' ) );
			$docs   = hcc_pget( $id, 'pdocs' );
			if ( ! isset( $defs[ $key ], $docs[ $key ] ) || ! in_array( $status, array( 'approved', 'amend', 'pending' ), true ) ) {
				hcc_staff_back( $id, 'error' );
			}
			$reason                   = hcc_field( 'reason', 'textarea' );
			$docs[ $key ]['status']   = $status;
			$docs[ $key ]['reason']   = 'amend' === $status ? $reason : '';
			$docs[ $key ]['by']       = wp_get_current_user()->display_name;
			$docs[ $key ]['checked']  = time();
			update_post_meta( $id, '_hcc_pdocs', $docs );
			$labels = hcc_doc_statuses();
			hcc_log( $id, $defs[ $key ][0] . ': ' . $labels[ $status ][0] . ( 'amend' === $status && $reason ? ' (' . $reason . ')' : '' ) );
			if ( 'amend' === $status ) {
				hcc_mail( $m( 'email' ), 'Action needed: your ' . $defs[ $key ][0], '<p>Hi ' . esc_html( $first ) . ',</p><p>We need a new upload of your <b>' . esc_html( $defs[ $key ][0] ) . '</b>.</p>' . ( $reason ? '<p style="background:#FFF6D6;padding:12px 14px;border-radius:10px">' . esc_html( $reason ) . '</p>' : '' ) . hcc_button( hcc_portal_url(), 'Upload it again' ) );
			}
			$c = hcc_doc_counts( $id );
			if ( 'approved' === $status && $c['approved'] === $c['required'] && 'documents' === $m( 'stage' ) ) {
				hcc_set_stage( $id, 'checks' );
				hcc_mail( $m( 'email' ), 'All your documents are approved', '<p>Hi ' . esc_html( $first ) . ',</p><p>Thank you. All your required documents have been approved. We are now finishing your checks and will be in touch soon.</p>' . hcc_button( hcc_portal_url(), 'See my progress' ) );
			}
			hcc_staff_back( $id, 'saved', 'sdoc-' . $key );
			break;

		case 'note':
			$text    = hcc_field( 'note', 'textarea' );
			$visible = ! empty( $_POST['visible'] );
			if ( '' === trim( $text ) ) {
				hcc_staff_back( $id, 'error', 'notes' );
			}
			$notes   = hcc_pget( $id, 'notes' );
			$notes[] = array( 'time' => time(), 'text' => $text, 'visible' => $visible, 'by' => wp_get_current_user()->display_name );
			update_post_meta( $id, '_hcc_notes', $notes );
			hcc_log( $id, $visible ? 'Added a note for the candidate' : 'Added an internal note' );
			if ( $visible && $m( 'user_id' ) ) {
				hcc_mail( $m( 'email' ), 'New message from HCC-Temps', '<p>Hi ' . esc_html( $first ) . ',</p><p>You have a new message from the HCC-Temps team:</p><p style="background:#E4F4F2;padding:12px 14px;border-radius:10px">' . nl2br( esc_html( $text ) ) . '</p>' . hcc_button( hcc_portal_url(), 'Open my portal' ) );
			}
			hcc_staff_back( $id, 'saved', 'notes' );
			break;
	}
	hcc_staff_back( $id, 'error' );
} );

/** Managers add staff from the Staff portal. */
add_action( 'admin_post_hcc_add_staff', function () {
	if ( ! current_user_can( 'hcc_manage_staff' ) || ! check_admin_referer( 'hcc_add_staff' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$email = hcc_field( 'email', 'email' );
	$first = hcc_field( 'first' );
	$last  = hcc_field( 'last' );
	$role  = 'manager' === hcc_field( 'level' ) ? 'hcc_manager' : 'hcc_staff';
	$back  = hcc_staff_url( array( 'view' => 'team' ) );
	if ( ! is_email( $email ) || ! $first || email_exists( $email ) ) {
		wp_safe_redirect( add_query_arg( 'msg', email_exists( $email ) ? 'staff_exists' : 'error', $back ) );
		exit;
	}
	$uid = wp_insert_user( array( 'user_login' => $email, 'user_email' => $email, 'user_pass' => wp_generate_password( 32 ), 'first_name' => $first, 'last_name' => $last, 'display_name' => trim( $first . ' ' . $last ), 'role' => $role ) );
	if ( is_wp_error( $uid ) ) {
		wp_safe_redirect( add_query_arg( 'msg', 'error', $back ) );
		exit;
	}
	wp_new_user_notification( $uid, null, 'user' );
	wp_safe_redirect( add_query_arg( 'msg', 'staff_added', $back ) );
	exit;
} );

/** Spreadsheet (CSV) of every record in the current filter: one row per candidate. */
add_action( 'admin_post_hcc_export', function () {
	if ( ! hcc_is_staff() || ! check_admin_referer( 'hcc_export' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$q      = hcc_staff_query( wp_unslash( $_GET ), -1 );
	$defs   = hcc_portal_docs();
	$labels = hcc_doc_statuses();
	$stages = hcc_stages();
	$date   = function ( $t ) { return $t ? wp_date( 'Y-m-d H:i', (int) $t ) : ''; };
	hcc_csv_headers( 'hcc-candidate-records' );
	$out  = fopen( 'php://output', 'w' );
	$head = array( 'Reference', 'First name', 'Last name', 'Email', 'Phone', 'Postcode', 'Right to work', 'Sector', 'Role', 'Applied for', 'Stage', 'Applied', 'Invited', 'Password set', 'First upload', 'Checks started', 'Ready to work', 'Days to ready', 'Required approved', 'Documents to fix' );
	foreach ( $defs as $d ) {
		$head[] = $d[0] . ' status';
		$head[] = $d[0] . ' uploaded';
	}
	$head = array_merge( $head, array( 'Share code', 'Notes (internal)', 'Notes (to candidate)', 'Last activity' ) );
	fputcsv( $out, $head );
	foreach ( $q->posts as $p ) {
		$id    = $p->ID;
		$m     = function ( $k ) use ( $id ) { return get_post_meta( $id, '_hcc_' . $k, true ); };
		$c     = hcc_doc_counts( $id );
		$times = hcc_pget( $id, 'stage_times' );
		$docs  = hcc_pget( $id, 'pdocs' );
		$uid   = (int) $m( 'user_id' );
		$first = 0;
		foreach ( $docs as $d ) {
			foreach ( isset( $d['files'] ) ? $d['files'] : array() as $f ) {
				$first = $first ? min( $first, (int) $f['time'] ) : (int) $f['time'];
			}
		}
		$applied = get_post_time( 'U', true, $id );
		$ready   = isset( $times['ready'] ) ? $times['ready'] : 0;
		$notes   = hcc_pget( $id, 'notes' );
		$row     = array(
			$m( 'ref' ), $m( 'first' ), $m( 'last' ), $m( 'email' ), $m( 'phone' ), $m( 'postcode' ), $m( 'rtw' ), $m( 'sector' ), $m( 'role_type' ), $m( 'role' ),
			isset( $stages[ $m( 'stage' ) ] ) ? $stages[ $m( 'stage' ) ] : '', $date( $applied ), $date( $m( 'invited' ) ), $uid ? $date( get_user_meta( $uid, 'hcc_password_set', true ) ) : '',
			$date( $first ), $date( isset( $times['checks'] ) ? $times['checks'] : 0 ), $date( $ready ), $ready ? round( ( $ready - $applied ) / DAY_IN_SECONDS, 1 ) : '',
			$c['approved'] . '/' . $c['required'], $c['amend'],
		);
		foreach ( $defs as $key => $d ) {
			$st    = isset( $docs[ $key ]['status'] ) ? $docs[ $key ]['status'] : '';
			$row[] = $labels[ $st ][0];
			$row[] = ! empty( $docs[ $key ]['files'] ) ? $date( end( $docs[ $key ]['files'] )['time'] ) : '';
		}
		$row[] = isset( $docs['sharecode']['code'] ) ? $docs['sharecode']['code'] : '';
		$row[] = count( array_filter( $notes, function ( $n ) { return empty( $n['visible'] ); } ) );
		$row[] = count( array_filter( $notes, function ( $n ) { return ! empty( $n['visible'] ); } ) );
		$row[] = $date( $m( 'updated' ) );
		fputcsv( $out, $row );
	}
	fclose( $out );
	exit;
} );

/** Spreadsheet of every activity-log entry for the candidates in the current filter. */
add_action( 'admin_post_hcc_export_log', function () {
	if ( ! hcc_is_staff() || ! check_admin_referer( 'hcc_export' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$q = hcc_staff_query( wp_unslash( $_GET ), -1 );
	hcc_csv_headers( 'hcc-activity-log' );
	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'Date', 'Reference', 'Candidate', 'Activity', 'By' ) );
	foreach ( $q->posts as $p ) {
		$name = get_post_meta( $p->ID, '_hcc_first', true ) . ' ' . get_post_meta( $p->ID, '_hcc_last', true );
		foreach ( hcc_pget( $p->ID, 'log' ) as $e ) {
			fputcsv( $out, array( wp_date( 'Y-m-d H:i', (int) $e['time'] ), get_post_meta( $p->ID, '_hcc_ref', true ), $name, $e['text'], $e['by'] ) );
		}
	}
	fclose( $out );
	exit;
} );

function hcc_csv_headers( $name ) {
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $name . '-' . current_time( 'Y-m-d' ) . '.csv"' );
	echo "\xEF\xBB\xBF"; // Byte order mark so Excel reads £ and accents correctly.
}

/** Printable report for one candidate: details, documents, notes and full activity log. */
add_action( 'admin_post_hcc_report', function () {
	$id = isset( $_GET['a'] ) ? absint( $_GET['a'] ) : 0;
	if ( ! $id || 'hcc_applicant' !== get_post_type( $id ) || ! hcc_is_staff() || ! check_admin_referer( 'hcc_report_' . $id ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	hcc_log( $id, 'Downloaded candidate report' );
	$m      = function ( $k ) use ( $id ) { return get_post_meta( $id, '_hcc_' . $k, true ); };
	$defs   = hcc_portal_docs( $m( 'role' ) );
	$docs   = hcc_pget( $id, 'pdocs' );
	$labels = hcc_doc_statuses();
	$stages = hcc_stages();
	$times  = hcc_pget( $id, 'stage_times' );
	$d      = function ( $t ) { return $t ? esc_html( wp_date( 'j M Y, H:i', (int) $t ) ) : '–'; };
	$name   = $m( 'first' ) . ' ' . $m( 'last' );
	header( 'Content-Type: text/html; charset=utf-8' );
	?><!doctype html><html lang="en-GB"><head><meta charset="utf-8"><title><?php echo esc_html( $m( 'ref' ) . ' ' . $name ); ?> report</title>
	<style>
		body { font: 13px/1.5 Arial, sans-serif; color: #1E2140; margin: 32px; }
		h1 { font-size: 24px; margin: 0; } h2 { font-size: 15px; margin: 26px 0 8px; border-bottom: 2px solid #6DC0B8; padding-bottom: 4px; }
		table { width: 100%; border-collapse: collapse; } th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #DDE5E3; vertical-align: top; }
		th { background: #F4F8F7; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }
		.top { display: flex; justify-content: space-between; align-items: center; gap: 20px; border-bottom: 6px solid #6DC0B8; padding-bottom: 14px; }
		.top img { height: 56px; } .muted { color: #5B6078; } .print { margin: 18px 0; } button { padding: 8px 16px; border-radius: 999px; border: 0; background: #2D338C; color: #fff; font-weight: bold; cursor: pointer; }
		@media print { .print { display: none; } body { margin: 0; } }
	</style></head><body>
	<div class="top"><div><h1><?php echo esc_html( $name ); ?></h1><div class="muted"><?php echo esc_html( $m( 'ref' ) . ' · ' . $m( 'role' ) . ' · ' . $m( 'sector' ) ); ?></div></div><img src="<?php echo esc_url( hcc_logo_url() ); ?>" alt="HCC-Temps"></div>
	<p class="print"><button onclick="window.print()">Print or save as PDF</button> <span class="muted">Report created <?php echo esc_html( wp_date( 'j M Y, H:i' ) ); ?> by <?php echo esc_html( wp_get_current_user()->display_name ); ?></span></p>
	<h2>Details</h2>
	<table><tbody>
		<tr><th>Email</th><td><?php echo esc_html( $m( 'email' ) ); ?></td><th>Phone</th><td><?php echo esc_html( $m( 'phone' ) ); ?></td></tr>
		<tr><th>Postcode</th><td><?php echo esc_html( $m( 'postcode' ) ); ?></td><th>Right to work</th><td><?php echo esc_html( $m( 'rtw' ) ); ?></td></tr>
		<tr><th>Stage</th><td><?php echo esc_html( isset( $stages[ $m( 'stage' ) ] ) ? $stages[ $m( 'stage' ) ] : '' ); ?></td><th>Applied</th><td><?php echo $d( get_post_time( 'U', true, $id ) ); ?></td></tr>
		<tr><th>Invited</th><td><?php echo $d( $m( 'invited' ) ); ?></td><th>Ready to work</th><td><?php echo $d( isset( $times['ready'] ) ? $times['ready'] : 0 ); ?></td></tr>
	</tbody></table>
	<h2>Documents</h2>
	<table><thead><tr><th>Document</th><th>Status</th><th>Files</th><th>Checked</th></tr></thead><tbody>
	<?php foreach ( $defs as $key => $def ) : $doc = isset( $docs[ $key ] ) ? $docs[ $key ] : array(); $st = isset( $doc['status'] ) ? $doc['status'] : ''; ?>
		<tr><td><?php echo esc_html( $def[0] ); ?></td><td><?php echo esc_html( $labels[ $st ][0] ); ?><?php echo ! empty( $doc['reason'] ) ? '<br><span class="muted">' . esc_html( $doc['reason'] ) . '</span>' : ''; ?></td>
		<td><?php foreach ( isset( $doc['files'] ) ? $doc['files'] : array() as $f ) { echo esc_html( $f['file'] ) . ' <span class="muted">(' . $d( $f['time'] ) . ')</span><br>'; } echo ! empty( $doc['code'] ) ? 'Share code ' . esc_html( $doc['code'] ) : ''; ?></td>
		<td><?php echo ! empty( $doc['by'] ) ? esc_html( $doc['by'] ) . '<br><span class="muted">' . $d( $doc['checked'] ) . '</span>' : '–'; ?></td></tr>
	<?php endforeach; ?>
	</tbody></table>
	<h2>Notes</h2>
	<table><thead><tr><th>Date</th><th>By</th><th>Visibility</th><th>Note</th></tr></thead><tbody>
	<?php foreach ( hcc_pget( $id, 'notes' ) as $n ) : ?><tr><td><?php echo $d( $n['time'] ); ?></td><td><?php echo esc_html( $n['by'] ); ?></td><td><?php echo ! empty( $n['visible'] ) ? 'Shared with candidate' : 'Internal'; ?></td><td><?php echo nl2br( esc_html( $n['text'] ) ); ?></td></tr><?php endforeach; ?>
	</tbody></table>
	<h2>Activity log</h2>
	<table><thead><tr><th>Date</th><th>Activity</th><th>By</th></tr></thead><tbody>
	<?php foreach ( hcc_pget( $id, 'log' ) as $e ) : ?><tr><td><?php echo $d( $e['time'] ); ?></td><td><?php echo esc_html( $e['text'] ); ?></td><td><?php echo esc_html( $e['by'] ); ?></td></tr><?php endforeach; ?>
	</tbody></table>
	</body></html>
	<?php
	exit;
} );

/* ---------- Staff list query (filters, sort, search) ---------- */

function hcc_staff_query( $args, $per_page = 20 ) {
	$meta = array( 'relation' => 'AND' );
	foreach ( array( 'sector' => '_hcc_sector', 'type' => '_hcc_role_type', 'stage' => '_hcc_stage' ) as $param => $key ) {
		if ( ! empty( $args[ $param ] ) ) {
			$meta[] = array( 'key' => $key, 'value' => sanitize_text_field( $args[ $param ] ) );
		}
	}
	$q = array(
		'post_type'      => 'hcc_applicant',
		'post_status'    => 'private',
		'posts_per_page' => $per_page,
		'paged'          => max( 1, isset( $args['pg'] ) ? (int) $args['pg'] : 1 ),
		'meta_query'     => $meta,
	);
	if ( ! empty( $args['s'] ) ) {
		$s           = sanitize_text_field( $args['s'] );
		$q['s']      = $s; // Title holds ref, name and role.
		$by_contact  = get_posts( array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'numberposts' => 50, 'fields' => 'ids', 'meta_query' => array( 'relation' => 'OR', array( 'key' => '_hcc_email', 'value' => $s, 'compare' => 'LIKE' ), array( 'key' => '_hcc_phone', 'value' => $s, 'compare' => 'LIKE' ) ) ) );
		if ( $by_contact ) {
			unset( $q['s'] );
			$by_title     = get_posts( array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'numberposts' => 50, 'fields' => 'ids', 's' => $s ) );
			$q['post__in'] = array_merge( $by_contact, $by_title );
		}
	}
	$from = ! empty( $args['from'] ) ? sanitize_text_field( $args['from'] ) : '';
	$to   = ! empty( $args['to'] ) ? sanitize_text_field( $args['to'] ) : '';
	if ( $from || $to ) {
		$q['date_query'] = array( array_filter( array( 'after' => $from, 'before' => $to, 'inclusive' => true ) ) );
	} elseif ( ! empty( $args['period'] ) && hcc_period_start( $args['period'] ) ) {
		$q['date_query'] = array( array( 'after' => gmdate( 'Y-m-d H:i:s', hcc_period_start( $args['period'] ) ), 'column' => 'post_date_gmt' ) );
	}
	$sort = isset( $args['sort'] ) ? sanitize_key( $args['sort'] ) : 'new';
	if ( 'old' === $sort ) {
		$q['orderby'] = 'date';
		$q['order']   = 'ASC';
	} elseif ( 'name' === $sort ) {
		$q['meta_key'] = '_hcc_last';
		$q['orderby']  = 'meta_value';
		$q['order']    = 'ASC';
	} elseif ( 'updated' === $sort ) {
		$q['meta_key'] = '_hcc_updated';
		$q['orderby']  = 'meta_value_num';
		$q['order']    = 'DESC';
	} else {
		$q['orderby'] = 'date';
		$q['order']   = 'DESC';
	}
	$query = new WP_Query( $q );
	// "Documents" filter needs the computed counts, so it is applied after the query.
	if ( ! empty( $args['docs'] ) ) {
		$want         = sanitize_key( $args['docs'] );
		$query->posts = array_values( array_filter( $query->posts, function ( $p ) use ( $want ) {
			$c = hcc_doc_counts( $p->ID );
			if ( 'missing' === $want ) {
				return $c['received'] < $c['required'];
			}
			if ( 'review' === $want ) {
				$docs = hcc_pget( $p->ID, 'pdocs' );
				return (bool) array_filter( $docs, function ( $d ) { return isset( $d['status'] ) && 'pending' === $d['status']; } );
			}
			if ( 'amend' === $want ) {
				return $c['amend'] > 0;
			}
			return 'complete' === $want ? $c['approved'] === $c['required'] : true;
		} ) );
		$query->post_count = count( $query->posts );
	}
	return $query;
}

/** Distinct values of a meta key across applicants, for filter dropdowns. */
function hcc_applicant_values( $key ) {
	global $wpdb;
	return $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type = 'hcc_applicant' AND pm.meta_key = %s AND pm.meta_value <> '' ORDER BY pm.meta_value", $key ) ); // phpcs:ignore
}

/* ---------- Demo candidates (Tools → HCC demo content) ---------- */

/** A grey sample "document" image so the demo has something to preview. */
function hcc_sample_image( $path, $title, $name ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return copy( HCC_DIR . '/assets/img/reception.jpg', $path );
	}
	$im = imagecreatetruecolor( 1200, 800 );
	imagefill( $im, 0, 0, imagecolorallocate( $im, 236, 242, 241 ) );
	$ink  = imagecolorallocate( $im, 23, 26, 69 );
	$aqua = imagecolorallocate( $im, 109, 192, 184 );
	$grey = imagecolorallocate( $im, 170, 180, 190 );
	imagefilledrectangle( $im, 0, 0, 1200, 90, $aqua );
	imagefilledrectangle( $im, 70, 160, 380, 560, $grey );
	for ( $y = 180; $y <= 520; $y += 60 ) {
		imagefilledrectangle( $im, 440, $y, 1100 - ( $y % 120 ), $y + 22, $grey );
	}
	imagestring( $im, 5, 30, 35, strtoupper( 'SAMPLE ' . $title ), $ink );
	imagestring( $im, 5, 440, 600, $name, $ink );
	imagestring( $im, 3, 440, 640, 'Demo file created by the HCC-Temps theme', $ink );
	imagejpeg( $im, $path, 80 );
	imagedestroy( $im );
	return true;
}

function hcc_create_demo_candidates() {
	// name, role, stage, document statuses, days since applying, days to invite, days to ready.
	$people = array(
		array( 'Priya', 'Shah', 'Senior Care Assistant', 'documents', array( 'passport' => 'approved', 'rtw' => 'approved', 'dbs' => 'pending', 'training' => 'amend' ), 9, 1, 0 ),
		array( 'Daniel', 'Adeyemi', 'Events Hospitality Assistant', 'invited', array(), 4, 2, 0 ),
		array( 'Grace', 'Owusu', 'Healthcare Assistant', 'checks', array( 'passport' => 'approved', 'licence' => 'approved', 'rtw' => 'approved', 'dbs' => 'approved', 'training' => 'approved' ), 21, 1, 0 ),
		array( 'Tom', 'Reed', 'Care Home Chef', 'new', array(), 0.3, 0, 0 ),
		array( 'Marta', 'Kowalska', 'Kitchen Assistant', 'documents', array( 'passport' => 'pending', 'rtw' => 'pending', 'sharecode' => 'pending' ), 13, 2, 0 ),
		array( 'Aisha', 'Bello', 'Registered Staff Nurse (RGN)', 'ready', array( 'passport' => 'approved', 'rtw' => 'approved', 'dbs' => 'approved', 'training' => 'approved' ), 48, 1, 8 ),
		array( 'Liam', 'Carter', 'Temp Cleaner', 'new', array(), 2, 0, 0 ),
		array( 'Sofia', 'Rossi', 'Care Home Receptionist', 'closed', array(), 140, 0, 0 ),
		array( 'Kwame', 'Mensah', 'Healthcare Assistant', 'ready', array( 'passport' => 'approved', 'rtw' => 'approved', 'dbs' => 'approved', 'training' => 'approved' ), 95, 1, 12 ),
		array( 'Ella', 'Brooks', 'Hotel Housekeeper', 'documents', array( 'passport' => 'approved', 'dbs' => 'amend' ), 35, 3, 0 ),
		array( 'Omar', 'Haddad', 'Temp Chef', 'ready', array( 'passport' => 'approved', 'rtw' => 'approved', 'dbs' => 'approved', 'training' => 'approved' ), 230, 2, 6 ),
		array( 'Nina', 'Kaur', 'Registered Staff Nurse (RGN)', 'invited', array(), 1, 0.5, 0 ),
	);
	$defs  = hcc_portal_docs();
	$made  = array();
	foreach ( $people as $k => $p ) {
		$ref     = hcc_next_ref();
		$applied = time() - (int) ( $p[5] * DAY_IN_SECONDS );
		$id      = wp_insert_post( array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'post_title' => $ref . ' · ' . $p[0] . ' ' . $p[1] . ' · ' . $p[2], 'post_date' => get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $applied ) ), 'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $applied ) ) );
		$job = get_posts( array( 'post_type' => 'job_vacancies', 'title' => $p[2], 'numberposts' => 1, 'fields' => 'ids' ) );
		$meta = array( 'ref' => $ref, 'first' => $p[0], 'last' => $p[1], 'email' => strtolower( $p[0] ) . '.' . strtolower( $p[1] ) . '@example.com', 'phone' => '07700 9001' . str_pad( (string) $k, 2, '0', STR_PAD_LEFT ), 'postcode' => 'SO15 2AA', 'rtw' => 'Yes', 'role' => $p[2], 'demo' => 1 );
		foreach ( $meta as $mk => $mv ) {
			update_post_meta( $id, '_hcc_' . $mk, $mv );
		}
		update_post_meta( $id, '_hcc_job_id', $job ? $job[0] : 0 );
		$cv = hcc_private_dir( $id ) . '/' . hcc_doc_filename( $ref, $p[0], $p[1], 'CV', 'jpg' );
		hcc_sample_image( $cv, 'CV', $p[0] . ' ' . $p[1] );
		update_post_meta( $id, '_hcc_docs', array( 'CV' => array( 'file' => basename( $cv ), 'time' => time() ) ) );
		$docs = array();
		foreach ( $p[4] as $key => $status ) {
			if ( 'sharecode' === $key ) {
				$docs[ $key ] = array( 'files' => array(), 'status' => $status, 'code' => 'W7K 2PX 9QD', 'dob' => '1994-03-12' );
				continue;
			}
			$file = hcc_doc_filename( $ref, $p[0], $p[1], $defs[ $key ][0], 'jpg' );
			hcc_sample_image( hcc_private_dir( $id ) . '/' . $file, $defs[ $key ][0], $p[0] . ' ' . $p[1] );
			$docs[ $key ] = array( 'files' => array( array( 'file' => $file, 'time' => time() - $k * HOUR_IN_SECONDS ) ), 'status' => $status, 'reason' => 'amend' === $status ? 'The photo is blurry and the expiry date is cut off. Please retake it in good light.' : '' );
		}
		update_post_meta( $id, '_hcc_pdocs', $docs );
		update_post_meta( $id, '_hcc_stage', $p[3] );
		$invited = $p[6] ? $applied + (int) ( $p[6] * DAY_IN_SECONDS ) : 0;
		$times   = array( 'new' => $applied );
		if ( $invited ) {
			$times['invited'] = $invited;
			update_post_meta( $id, '_hcc_invited', $invited );
		}
		if ( $p[7] ) {
			$times['checks'] = $applied + (int) ( ( $p[7] - 1 ) * DAY_IN_SECONDS );
			$times['ready']  = $applied + (int) ( $p[7] * DAY_IN_SECONDS );
		}
		update_post_meta( $id, '_hcc_stage_times', $times );
		update_post_meta( $id, '_hcc_log', array(
			array( 'time' => $applied, 'text' => 'Applied with CV', 'by' => 'Candidate' ),
		) );
		update_post_meta( $id, '_hcc_updated', min( time(), $applied + DAY_IN_SECONDS * max( 1, $p[7] ) ) );
		if ( 'new' !== $p[3] ) {
			$uid = wp_insert_user( array( 'user_login' => $meta['email'], 'user_email' => $meta['email'], 'user_pass' => 'Demo-Candidate-2026', 'first_name' => $p[0], 'last_name' => $p[1], 'display_name' => $p[0] . ' ' . $p[1], 'role' => 'hcc_candidate' ) );
			if ( ! is_wp_error( $uid ) ) {
				update_post_meta( $id, '_hcc_user_id', $uid );
				update_user_meta( $uid, 'hcc_applicant_id', $id );
				update_user_meta( $uid, 'hcc_demo', 1 );
			}
			$log   = hcc_pget( $id, 'log' );
			$log[] = array( 'time' => $invited, 'text' => 'Invited to the candidate portal', 'by' => 'Demo Staff' );
			foreach ( $p[4] as $key => $status ) {
				$log[] = array( 'time' => min( time(), $invited + HOUR_IN_SECONDS * 5 ), 'text' => 'Uploaded ' . $defs[ $key ][0], 'by' => $p[0] . ' ' . $p[1] );
			}
			if ( ! empty( $times['ready'] ) ) {
				$log[] = array( 'time' => $times['ready'], 'text' => 'Stage changed to Ready to work', 'by' => 'Demo Staff' );
			}
			update_post_meta( $id, '_hcc_log', $log );
		}
		hcc_portal_after_apply( 0, $id, '_hcc_job_id' );
		$made[] = $id;
	}
	if ( $made ) {
		update_post_meta( $made[0], '_hcc_notes', array(
			array( 'time' => time() - DAY_IN_SECONDS, 'text' => 'Thanks Priya. Your passport and right to work are approved. Please re-upload your training certificates, the first page is blurry.', 'visible' => true, 'by' => 'Demo Staff' ),
			array( 'time' => time() - 20 * HOUR_IN_SECONDS, 'text' => 'Called Priya, available for long days from next week.', 'visible' => false, 'by' => 'Demo Staff' ),
		) );
	}
	if ( ! email_exists( 'staff@example.com' ) ) {
		$sid = wp_insert_user( array( 'user_login' => 'staff@example.com', 'user_email' => 'staff@example.com', 'user_pass' => 'Demo-Staff-2026', 'first_name' => 'Demo', 'last_name' => 'Staff', 'display_name' => 'Demo Staff', 'role' => 'hcc_manager' ) );
		if ( ! is_wp_error( $sid ) ) {
			update_user_meta( $sid, 'hcc_demo', 1 );
		}
	}
	return count( $made );
}

add_action( 'admin_post_hcc_demo_candidates', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'hcc_demo_candidates' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	hcc_create_demo_candidates();
	wp_safe_redirect( admin_url( 'tools.php?page=hcc-demo&candidates=1' ) );
	exit;
} );

add_action( 'admin_post_hcc_remove_demo_candidates', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'hcc_remove_demo_candidates' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	require_once ABSPATH . 'wp-admin/includes/user.php';
	foreach ( get_posts( array( 'post_type' => 'hcc_applicant', 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => '_hcc_demo', 'fields' => 'ids' ) ) as $id ) {
		$dir = hcc_private_dir( $id );
		foreach ( glob( $dir . '/*' ) as $f ) {
			unlink( $f );
		}
		rmdir( $dir );
		wp_delete_post( $id, true );
	}
	foreach ( get_users( array( 'meta_key' => 'hcc_demo', 'fields' => 'ids' ) ) as $uid ) {
		wp_delete_user( $uid );
	}
	wp_safe_redirect( admin_url( 'tools.php?page=hcc-demo&candidates=0' ) );
	exit;
} );

/** Extra panel on Tools → HCC demo content. */
add_action( 'hcc_demo_page_after', function () {
	$count = count( get_posts( array( 'post_type' => 'hcc_applicant', 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => '_hcc_demo', 'fields' => 'ids' ) ) );
	echo '<hr><h2>Demo candidates and staff</h2>';
	if ( isset( $_GET['candidates'] ) ) {
		echo '<div class="notice notice-success"><p>' . ( '1' === $_GET['candidates'] ? 'Demo candidates created.' : 'Demo candidates and accounts removed.' ) . '</p></div>';
	}
	echo '<p>Creates 12 sample candidates at different stages, with sample documents, plus demo logins so you can try both portals.</p>';
	if ( $count ) {
		echo '<table class="widefat striped" style="max-width:720px"><tbody>'
			. '<tr><th>Staff portal</th><td><a href="' . esc_url( hcc_staff_url() ) . '">' . esc_html( hcc_staff_url() ) . '</a><br>Email <code>staff@example.com</code> · Password <code>Demo-Staff-2026</code></td></tr>'
			. '<tr><th>Candidate portal</th><td><a href="' . esc_url( hcc_login_url() ) . '">' . esc_html( hcc_login_url() ) . '</a><br>Email <code>priya.shah@example.com</code> · Password <code>Demo-Candidate-2026</code></td></tr>'
			. '</tbody></table><p><em>Remove these before going live.</em></p>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin-right:8px">';
	echo '<input type="hidden" name="action" value="hcc_demo_candidates">';
	wp_nonce_field( 'hcc_demo_candidates' );
	submit_button( $count ? 'Add 12 more demo candidates' : 'Create demo candidates', 'primary', 'submit', false );
	echo '</form>';
	if ( $count ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block">';
		echo '<input type="hidden" name="action" value="hcc_remove_demo_candidates">';
		wp_nonce_field( 'hcc_remove_demo_candidates' );
		submit_button( 'Remove demo candidates and logins', 'delete', 'submit', false );
		echo '</form>';
	}
} );

/** One file tile in the Staff portal: image preview or PDF card, with View and Download. */
function hcc_staff_file( $id, $key, $i, $name, $time ) {
	$is_img = (bool) preg_match( '/\.(jpe?g|png)$/i', $name );
	$view   = hcc_file_url( $id, $key, $i );
	$thumb  = $is_img ? '<img src="' . esc_url( $view ) . '" alt="" loading="lazy">' : '<span class="pdf">PDF</span>';
	return '<figure class="sfile"><a class="thumb" href="' . esc_url( $view ) . '" target="_blank" rel="noopener" aria-label="View ' . esc_attr( $name ) . '">' . $thumb . '</a>'
		. '<figcaption><span class="fname">' . esc_html( $name ) . '</span><span class="small">' . esc_html( wp_date( 'j M Y, H:i', $time ) ) . '</span>'
		. '<span class="flinks"><a href="' . esc_url( $view ) . '" target="_blank" rel="noopener">View</a><a href="' . esc_url( hcc_file_url( $id, $key, $i, true ) ) . '">Download</a></span></figcaption></figure>';
}

/** Failed logins from our login page come back to it instead of wp-login.php. */
add_action( 'wp_login_failed', function () {
	$ref = wp_get_referer();
	if ( $ref && 0 === strpos( $ref, hcc_login_url() ) ) {
		wp_safe_redirect( add_query_arg( 'login', 'failed', hcc_login_url() ) );
		exit;
	}
} );

/* ---------- WordPress.com / Jetpack hosting ---------- */

/*
 * Jetpack's "Log in with WordPress.com" (SSO) replaces the username and password
 * form, and its styles also hide the two-factor code box. Portal users never have
 * WordPress.com accounts, so switch it off and always show the normal form.
 */
add_filter( 'option_jetpack_active_modules', function ( $modules ) {
	return is_array( $modules ) ? array_values( array_diff( $modules, array( 'sso' ) ) ) : $modules;
} );
add_filter( 'jetpack_active_modules', function ( $modules ) {
	return is_array( $modules ) ? array_values( array_diff( $modules, array( 'sso' ) ) ) : $modules;
} );
add_filter( 'jetpack_sso_default_to_sso_login', '__return_false' );

add_action( 'login_enqueue_scripts', function () {
	?>
	<style>
		/* If SSO is still forced by the host, hide its button and show the real form and code box. */
		.jetpack-sso-wrap, #jetpack-sso-wrap, .jetpack-sso-or, .jetpack-sso-toggle, .jetpack-sso-wrap__user { display: none !important; }
		.jetpack-sso-form-display #loginform > p, .jetpack-sso-form-display #loginform > div, .jetpack-sso-form-display #loginform .user-pass-wrap,
		#loginform p, #loginform .user-pass-wrap, #loginform input, #loginform label { display: block; }
		#loginform input[type=checkbox] { display: inline-block; }
		#loginform #authcode, #loginform input[name="two-factor-email-code"] { display: block !important; width: 100%; font-size: 24px; letter-spacing: .2em; text-align: center; }
		#loginform .submit, #loginform #submit { display: block !important; }
	</style>
	<?php
}, 20 );

/** Send "Log in" links (after a password reset, or from wp-admin) to the branded login page. */
add_filter( 'login_url', function ( $url, $redirect ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $url;
	}
	$page = hcc_login_url();
	return $redirect ? add_query_arg( 'redirect_to', rawurlencode( $redirect ), $page ) : $page;
}, 10, 2 );

/* ---------- Dashboard numbers ---------- */

function hcc_periods() {
	return array(
		'24h' => array( DAY_IN_SECONDS, 'Last 24 hours' ),
		'30d' => array( 30 * DAY_IN_SECONDS, '30 days' ),
		'60d' => array( 60 * DAY_IN_SECONDS, '60 days' ),
		'90d' => array( 90 * DAY_IN_SECONDS, '90 days' ),
		'6m'  => array( 182 * DAY_IN_SECONDS, '6 months' ),
		'12m' => array( 365 * DAY_IN_SECONDS, '12 months' ),
		'all' => array( 0, 'All time' ),
	);
}

/** Unix time a period starts, or 0 for all time. */
function hcc_period_start( $key ) {
	$p = hcc_periods();
	return isset( $p[ $key ] ) && $p[ $key ][0] ? time() - $p[ $key ][0] : 0;
}

/** Numbers for the top of the Staff portal, for applications received in the period. */
function hcc_dashboard( $period ) {
	$args = array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'numberposts' => -1, 'fields' => 'ids' );
	if ( hcc_period_start( $period ) ) {
		$args['date_query'] = array( array( 'after' => gmdate( 'Y-m-d H:i:s', hcc_period_start( $period ) ), 'column' => 'post_date_gmt' ) );
	}
	$ids  = get_posts( $args );
	$defs = hcc_portal_docs();
	$out  = array(
		'total'    => count( $ids ),
		'pipeline' => 0,
		'new'      => 0,
		'review'   => 0,
		'ready'    => 0,
		'closed'   => 0,
		'docs'     => array(),
		'days'     => array(),
		'invite'   => array(),
	);
	foreach ( $defs as $key => $d ) {
		$out['docs'][ $key ] = array( 'label' => $d[0], 'required' => $d[2], 'missing' => 0, 'review' => 0, 'amend' => 0 );
	}
	foreach ( $ids as $id ) {
		$stage   = get_post_meta( $id, '_hcc_stage', true );
		$applied = (int) get_post_time( 'U', true, $id );
		$times   = hcc_pget( $id, 'stage_times' );
		$invited = (int) get_post_meta( $id, '_hcc_invited', true );
		if ( in_array( $stage, array( 'new', 'invited', 'documents', 'checks' ), true ) ) {
			$out['pipeline']++;
		}
		if ( 'new' === $stage ) {
			$out['new']++;
		}
		if ( 'ready' === $stage ) {
			$out['ready']++;
		}
		if ( 'closed' === $stage ) {
			$out['closed']++;
		}
		if ( ! empty( $times['ready'] ) ) {
			$out['days'][] = ( $times['ready'] - $applied ) / DAY_IN_SECONDS;
		}
		if ( $invited ) {
			$out['invite'][] = ( $invited - $applied ) / DAY_IN_SECONDS;
		}
		if ( ! in_array( $stage, array( 'invited', 'documents', 'checks' ), true ) ) {
			continue;
		}
		$docs    = hcc_pget( $id, 'pdocs' );
		$pending = false;
		foreach ( $defs as $key => $d ) {
			$st = isset( $docs[ $key ]['status'] ) ? $docs[ $key ]['status'] : '';
			if ( '' === $st && $d[2] ) {
				$out['docs'][ $key ]['missing']++;
			} elseif ( 'pending' === $st ) {
				$out['docs'][ $key ]['review']++;
				$pending = true;
			} elseif ( 'amend' === $st ) {
				$out['docs'][ $key ]['amend']++;
			}
		}
		$out['review'] += $pending ? 1 : 0;
	}
	$avg               = function ( $a ) { return $a ? round( array_sum( $a ) / count( $a ), 1 ) : null; };
	$out['avg_days']   = $avg( $out['days'] );
	$out['avg_invite'] = $avg( $out['invite'] );
	return $out;
}

/* ---------- Activity: logins ---------- */

function hcc_log_login( $user ) {
	$app = hcc_applicant_for_user( $user->ID );
	if ( $app ) {
		hcc_log( $app, 'Logged in to the portal', $user );
	}
}
add_action( 'wp_login', function ( $login, $user ) {
	// With two-factor on, the login only counts once the code is accepted.
	if ( class_exists( 'Two_Factor_Core' ) && Two_Factor_Core::is_user_using_two_factor( $user->ID ) ) {
		return;
	}
	hcc_log_login( $user );
}, 5, 2 );
add_action( 'two_factor_user_authenticated', 'hcc_log_login' );
