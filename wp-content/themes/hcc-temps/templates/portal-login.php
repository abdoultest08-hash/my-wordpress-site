<?php
/**
 * Template Name: Portal login (candidates and staff)
 */
defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( hcc_is_staff() ? hcc_staff_url() : hcc_portal_url() );
	exit;
}
$failed = isset( $_GET['login'] ) && 'failed' === $_GET['login'];
get_header();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / Log in</div>
			<h1>Log in to your portal</h1>
			<p class="lead">Candidates can upload documents and follow their application. HCC staff log in here too.</p>
		</div>
	</section>

	<section class="section">
		<div class="wrap login-grid">
			<div class="form-card">
				<h2>Log in</h2>
				<?php if ( $failed ) : ?><p class="form-alert" role="alert">That email and password don't match. Try again, or reset your password below.</p><?php endif; ?>
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
				?>
				<p class="form-note" style="margin-top:14px"><a href="<?php echo esc_url( wp_lostpassword_url( hcc_login_url() ) ); ?>">Forgotten your password?</a></p>
				<div class="lock" style="margin-top:18px"><?php echo hcc_icon( 'lock', 18 ); ?><span>For your security we email you a 6-digit code each time you log in.</span></div>
			</div>
			<aside class="side-card">
				<h3>New to HCC-Temps?</h3>
				<p>You don't need to create an account yourself. Apply for a job or register, and once we've reviewed your CV we'll email you an invite to set up your portal.</p>
				<div class="form-actions" style="margin-top:6px">
					<a class="btn btn-sun btn-sm" href="<?php echo esc_url( hcc_jobs_url() ); ?>">Find a job</a>
					<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">Register</a>
				</div>
				<h3 style="margin-top:18px">Need help?</h3>
				<p>Call <?php echo esc_html( hcc_opt( 'phone' ) ); ?> and quote your reference number.</p>
			</aside>
		</div>
	</section>
</main>
<?php
get_footer();
