<?php
/**
 * Accounts, logins and portal pages.
 *
 * Roles: hcc_candidate (own registration only), hcc_staff (all candidates), hcc_manager (staff + add staff,
 * sees bank details in full). Every candidate and staff login needs an emailed 6-digit code (Two Factor plugin)
 * and a robot check (Cloudflare Turnstile when keys are set in Appearance → Customize, otherwise a tick box).
 */

defined( 'ABSPATH' ) || exit;

define( 'HCC_PORTAL_VERSION', '2' );

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
		'portal-login' => array( 'Log in', 'candidate-login' ),
		'apply'        => array( 'Registration', 'registration' ),
		'portal'       => array( 'My Portal', 'my-application' ),
		'staff'        => array( 'Staff Portal', 'staff' ),
	);
	foreach ( $pages as $tpl => $p ) {
		$exists = get_posts( array( 'post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'templates/' . $tpl . '.php', 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids' ) );
		if ( ! $exists ) {
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $p[0], 'post_name' => $p[1] ) );
			update_post_meta( $id, '_wp_page_template', 'templates/' . $tpl . '.php' );
		}
	}
	// The old "upload documents" link page is replaced by the candidate portal.
	foreach ( get_posts( array( 'post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'templates/upload.php', 'numberposts' => -1, 'fields' => 'ids' ) ) as $old ) {
		wp_trash_post( $old );
	}
	update_option( 'hcc_portal_version', HCC_PORTAL_VERSION );
}, 30 );

function hcc_is_staff( $user = null ) {
	return $user ? user_can( $user, 'hcc_manage_candidates' ) : current_user_can( 'hcc_manage_candidates' );
}

function hcc_is_manager( $user = null ) {
	return $user ? user_can( $user, 'hcc_manage_staff' ) : current_user_can( 'hcc_manage_staff' );
}

function hcc_login_url( $staff = false ) {
	$u = hcc_page_url( 'portal-login', '/candidate-login/' );
	return $staff ? add_query_arg( 'for', 'staff', $u ) : $u;
}
function hcc_apply_url( $role = '' ) {
	$u = hcc_page_url( 'apply', '/registration/' );
	return $role ? add_query_arg( 'role', $role, $u ) : $u;
}
function hcc_portal_url() {
	return hcc_page_url( 'portal', '/my-application/' );
}
function hcc_staff_url( $args = array() ) {
	return add_query_arg( $args, hcc_page_url( 'staff', '/staff/' ) );
}

/* ---------- Where people land, and keeping candidates out of wp-admin ---------- */

add_filter( 'login_redirect', function ( $redirect, $requested, $user ) {
	if ( is_wp_error( $user ) || ! $user ) {
		return $redirect;
	}
	if ( user_can( $user, 'manage_options' ) ) {
		return $requested ? $requested : $redirect;
	}
	// A link from an email (e.g. "Open in Staff portal" for one candidate) brings people back to that page.
	$req = $requested ? wp_validate_redirect( $requested, '' ) : '';
	if ( hcc_is_staff( $user ) ) {
		return $req && 0 === strpos( $req, hcc_staff_url() ) ? $req : hcc_staff_url();
	}
	$app = hcc_applicant_for_user( $user->ID );
	return ( $app && ! hcc_meta( $app, 'submitted' ) ) ? hcc_apply_url() : hcc_portal_url();
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

add_action( 'wp_login_failed', function () {
	$ref = wp_get_referer();
	if ( $ref && 0 === strpos( $ref, hcc_page_url( 'portal-login', '/candidate-login/' ) ) ) {
		wp_safe_redirect( add_query_arg( 'login', 'failed', $ref ) );
		exit;
	}
} );

/** "Log in" links (after a password reset, or a logged-out portal visit) go to the branded login page. */
add_filter( 'login_url', function ( $url, $redirect ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $url;
	}
	$page = hcc_login_url();
	return $redirect ? add_query_arg( 'redirect_to', rawurlencode( $redirect ), $page ) : $page;
}, 10, 2 );

/* ---------- Branded wp-login.php (password reset and the code screen) ---------- */

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
		.login input[type=text]:focus, .login input[type=password]:focus { border-color: #237D74; box-shadow: 0 0 0 2px #6DC0B8; }
		.login #nav a, .login #backtoblog a { color: #2D338C; }
		/* Jetpack SSO off: always show the real form and the code box. */
		.jetpack-sso-wrap, #jetpack-sso-wrap, .jetpack-sso-or, .jetpack-sso-toggle, .jetpack-sso-wrap__user { display: none !important; }
		#loginform p, #loginform .user-pass-wrap, #loginform input, #loginform label { display: block; }
		#loginform input[type=checkbox] { display: inline-block; }
		#loginform #authcode, #loginform input[name="two-factor-email-code"] { display: block !important; width: 100%; font-size: 24px; letter-spacing: .2em; text-align: center; }
		#loginform .submit, #loginform #submit { display: block !important; }
	</style>
	<?php
}, 20 );
add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return 'HCC-Temps'; } );

/* Jetpack's "Log in with WordPress.com" hides the password form and code box; portal users never use it. */
add_filter( 'option_jetpack_active_modules', function ( $m ) { return is_array( $m ) ? array_values( array_diff( $m, array( 'sso' ) ) ) : $m; } );
add_filter( 'jetpack_active_modules', function ( $m ) { return is_array( $m ) ? array_values( array_diff( $m, array( 'sso' ) ) ) : $m; } );
add_filter( 'jetpack_sso_default_to_sso_login', '__return_false' );

/* ---------- 6-digit email code at every login (Two Factor plugin) ---------- */

add_filter( 'two_factor_enabled_providers_for_user', function ( $providers, $user_id ) {
	$user = get_userdata( $user_id );
	if ( ! $user || ! array_intersect( array( 'hcc_candidate', 'hcc_staff', 'hcc_manager' ), (array) $user->roles ) ) {
		return $providers;
	}
	if ( empty( $providers ) ) {
		$providers[] = 'Two_Factor_Email';
	}
	return $providers;
}, 10, 2 );

add_action( 'admin_notices', function () {
	if ( ! class_exists( 'Two_Factor_Core' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p><b>HCC-Temps:</b> install and activate the free <a href="' . esc_url( admin_url( 'plugin-install.php?s=two-factor&tab=search&type=term' ) ) . '">Two Factor</a> plugin so candidates and staff get a 6-digit code by email at every login.</p></div>';
	}
} );

add_filter( 'two_factor_email_token_length', function () { return 6; } );
add_filter( 'two_factor_token_email_subject', function () { return 'Your HCC-Temps login code'; } );
add_filter( 'two_factor_token_email_message', function ( $message, $token, $user_id ) {
	$u = get_userdata( $user_id );
	return 'Hi ' . ( $u && $u->first_name ? $u->first_name : 'there' ) . ",\n\nYour HCC-Temps login code is:\n\n" . substr( $token, 0, 3 ) . ' ' . substr( $token, 3 ) . "\n\nIt works for 15 minutes. If you didn't try to log in, change your password and call us on " . hcc_opt( 'phone' ) . ".\n\nHCC-Temps";
}, 10, 3 );

/*
 * Pages with forms must never come from the page cache (WordPress.com's CDN caches pages for
 * logged-out visitors): a cached copy carries an old security key and an expired Jetpack sum,
 * which sent people to Jetpack's extra "Prove your humanity" page.
 */
add_action( 'template_redirect', function () {
	foreach ( array( 'portal-login', 'apply', 'portal', 'staff', 'contact', 'clients' ) as $tpl ) {
		if ( is_user_logged_in() || is_page_template( 'templates/' . $tpl . '.php' ) ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			nocache_headers();
			header( 'X-Accel-Expires: 0' );
			return;
		}
	}
}, 1 );

/* ---------- Robot check ---------- */

function hcc_turnstile_on() {
	return hcc_opt( 'turnstile_site' ) && hcc_opt( 'turnstile_secret' );
}

/** The robot check field for any form. */
function hcc_robot_field() {
	if ( hcc_turnstile_on() ) {
		wp_enqueue_script( 'cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
		return '<div class="robot full"><div class="cf-turnstile" data-sitekey="' . esc_attr( hcc_opt( 'turnstile_site' ) ) . '"></div></div>';
	}
	return '<div class="robot full"><label><input type="checkbox" name="hcc_robot" value="1" required> I\'m not a robot</label><small>Robot check</small></div>';
}

/** True if the robot check on the posted form passed. */
function hcc_robot_ok() {
	if ( ! hcc_turnstile_on() ) {
		return ! empty( $_POST['hcc_robot'] ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! $token ) {
		return false;
	}
	$r = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array( 'timeout' => 8, 'body' => array( 'secret' => hcc_opt( 'turnstile_secret' ), 'response' => $token, 'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ) ) );
	$b = is_wp_error( $r ) ? null : json_decode( wp_remote_retrieve_body( $r ), true );
	return ! empty( $b['success'] );
}

/**
 * True when Jetpack's "Prove your humanity" sum is switched on for this request. Checked on the server
 * so that posting a made-up Jetpack field can't be used to skip our own robot check.
 */
function hcc_jetpack_sum_on() {
	global $wp_filter;
	if ( empty( $wp_filter['login_form'] ) ) {
		return false;
	}
	foreach ( $wp_filter['login_form']->callbacks as $cbs ) {
		foreach ( $cbs as $cb ) {
			if ( is_array( $cb['function'] ) && is_object( $cb['function'][0] ) && false !== stripos( get_class( $cb['function'][0] ), 'math' ) ) {
				return true;
			}
		}
	}
	return false;
}

/*
 * The branded login form posts an hcc_login flag; its robot check is enforced here. This runs after
 * WordPress's own password check (priority 20), which would otherwise replace an earlier error with
 * the logged-in user, so a bot posting straight to wp-login.php can't skip the check.
 */
add_filter( 'authenticate', function ( $user, $username ) {
	if ( empty( $_POST['hcc_login'] ) || ! $username || ! ( $user instanceof WP_User ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return $user;
	}
	if ( ! empty( $_POST['jetpack_protect_num'] ) && hcc_jetpack_sum_on() ) { // phpcs:ignore WordPress.Security.NonceVerification
		return $user; // Jetpack's own sum was on the form and Jetpack checks the answer.
	}
	if ( ! hcc_robot_ok() ) {
		return new WP_Error( 'hcc_robot', 'Please complete the robot check.' );
	}
	return $user;
}, 99, 2 );

/* ---------- Staff accounts ---------- */

function hcc_set_password_link( $user ) {
	$key = get_password_reset_key( $user );
	if ( is_wp_error( $key ) ) {
		return '';
	}
	return network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user->user_login ), 'login' );
}

add_filter( 'wp_new_user_notification_email', function ( $email, $user ) {
	if ( ! hcc_is_staff( $user ) ) {
		return $email;
	}
	$email['subject'] = 'Your HCC-Temps staff portal account';
	$email['message'] = 'Hi ' . $user->first_name . ",\n\nAn account has been created for you on the HCC-Temps Staff portal.\n\nCreate your password here:\n" . hcc_set_password_link( $user ) . "\n\nThen log in at " . hcc_login_url( true ) . "\n\nYou will be asked for a 6-digit code from your email each time you log in.";
	return $email;
}, 10, 2 );

add_action( 'admin_post_hcc_add_staff', function () {
	if ( ! hcc_is_manager() || ! check_admin_referer( 'hcc_add_staff' ) ) {
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

/* ---------- Logins in the candidate's activity log ---------- */

function hcc_log_login( $user ) {
	$app = hcc_applicant_for_user( $user->ID );
	if ( $app ) {
		hcc_log( $app, 'Logged in', 'Candidate' );
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
