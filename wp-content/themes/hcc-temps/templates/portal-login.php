<?php
/**
 * Template Name: Portal login (candidates and staff)
 */
defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( hcc_is_staff() ? hcc_staff_url() : hcc_portal_url() );
	exit;
}
$failed = isset( $_GET['login'] ) && 'failed' === $_GET['login']; // phpcs:ignore WordPress.Security.NonceVerification
$staff  = isset( $_GET['for'] ) && 'staff' === $_GET['for']; // phpcs:ignore WordPress.Security.NonceVerification
$extra  = function () { return '<input type="hidden" name="hcc_login" value="1">' . hcc_robot_field(); };
add_filter( 'login_form_middle', $extra );
get_header();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / Log in</div>
			<h1><?php echo $staff ? 'Staff login' : 'Candidate login'; ?></h1>
			<p class="lead"><?php echo $staff ? 'Log in to the Staff portal to review candidates and documents.' : 'Upload your documents and follow your registration.'; ?></p>
			<?php if ( $staff ) : ?>
			<nav class="login-switch" aria-label="Who is logging in">
				<a href="<?php echo esc_url( hcc_login_url() ); ?>">I'm a candidate</a>
				<a href="<?php echo esc_url( hcc_login_url( true ) ); ?>" aria-current="page">I'm HCC staff</a>
			</nav>
			<?php endif; ?>
		</div>
	</section>

	<section class="section">
		<div class="wrap login-grid">
			<div class="form-card">
				<h2>Log in</h2>
				<?php if ( $failed ) : ?><p class="form-alert" role="alert">That didn't work. Check your email and password and complete the robot check, or reset your password below.</p><?php endif; ?>
				<?php
				wp_login_form( array(
					'redirect'       => '',
					'label_username' => 'Email',
					'label_password' => 'Password',
					'label_remember' => 'Keep me logged in on this device',
					'label_log_in'   => 'Log in',
					'remember'       => true,
					'id_submit'      => 'wp-submit',
				) );
				remove_filter( 'login_form_middle', $extra );
				?>
				<p class="form-note" style="margin-top:14px"><a href="<?php echo esc_url( wp_lostpassword_url( hcc_login_url( $staff ) ) ); ?>">Forgotten your password?</a> · <a href="<?php echo esc_url( hcc_page_url( 'privacy', '/privacy-policy/' ) ); ?>">Privacy policy</a></p>
				<div class="lock" style="margin-top:18px"><?php echo hcc_icon( 'lock', 18 ); // phpcs:ignore ?><span>For your security we email you a 6-digit code every time you log in. This applies to candidates and HCC staff.</span></div>
			</div>
			<?php if ( $staff ) : ?>
			<aside class="side-card">
				<h3>Staff and admin accounts</h3>
				<p>Your manager creates your account. You'll get an email to set your password, then log in here with a code sent to your email.</p>
				<h3 style="margin-top:18px">Locked out?</h3>
				<p>Use "Forgotten your password?" or ask your manager to resend your invite.</p>
			</aside>
			<?php else : ?>
			<aside class="side-card">
				<h3>New to HCC-Temps?</h3>
				<p>Create your account, choose your role and upload your documents. Most candidates are ready to work in 3 days.</p>
				<div class="form-actions" style="margin-top:6px">
					<a class="btn btn-sun btn-sm" href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">Start registration</a>
					<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( hcc_jobs_url() ); ?>">Find a job</a>
				</div>
				<h3 style="margin-top:18px">Need help?</h3>
				<p>Call <?php echo esc_html( hcc_opt( 'phone' ) ); ?>, 24 hours a day, and quote your reference number.</p>
			</aside>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
