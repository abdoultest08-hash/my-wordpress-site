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
$staff  = isset( $_GET['for'] ) && 'staff' === $_GET['for'];
get_header();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / Log in</div>
			<h1><?php echo $staff ? 'Staff login' : 'Candidate login'; ?></h1>
			<p class="lead"><?php echo $staff ? 'Log in to the Staff portal to review applications and documents.' : 'Upload your documents and follow your application.'; ?></p>
			<nav class="login-switch" aria-label="Who is logging in">
				<a href="<?php echo esc_url( hcc_login_url() ); ?>"<?php echo $staff ? '' : ' aria-current="page"'; ?>>I'm a candidate</a>
				<a href="<?php echo esc_url( add_query_arg( 'for', 'staff', hcc_login_url() ) ); ?>"<?php echo $staff ? ' aria-current="page"' : ''; ?>>I'm HCC staff</a>
			</nav>
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
			<?php if ( $staff ) : ?>
			<aside class="side-card">
				<h3>Staff accounts</h3>
				<p>Your manager creates your account. You'll get an email to set your password, then you log in here with a code sent to your email.</p>
				<h3 style="margin-top:18px">Locked out?</h3>
				<p>Use "Forgotten your password?" or ask your manager to resend your invite.</p>
			</aside>
			<?php else : ?>
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
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
