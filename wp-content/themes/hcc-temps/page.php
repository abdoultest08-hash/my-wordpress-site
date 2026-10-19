<?php
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <?php the_title(); ?></div>
			<h1><?php the_title(); ?></h1>
		</div>
	</section>
	<section class="section">
		<div class="wrap entry" style="max-width:820px"><?php the_content(); ?></div>
	</section>
</main>
<?php
get_footer();
