<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<h1>We can't find that page</h1>
			<p class="lead">It may have moved, or the job may have been filled.</p>
			<div class="hero-actions" style="justify-content:flex-start">
				<a class="btn btn-sun" href="<?php echo esc_url( hcc_jobs_url() ); ?>">See live jobs</a>
				<a class="btn btn-ghost-light" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go to the homepage</a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
