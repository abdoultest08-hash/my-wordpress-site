<?php
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
$cat = get_the_category();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <?php echo $cat ? esc_html( $cat[0]->name ) : 'Blog'; ?></div>
			<h1><?php the_title(); ?></h1>
			<p class="lead"><?php echo esc_html( get_the_date() ); ?></p>
		</div>
	</section>
	<section class="section">
		<article class="wrap entry" style="max-width:780px">
			<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', array( 'style' => 'border-radius:20px;margin-bottom:28px' ) ); } ?>
			<?php the_content(); ?>
		</article>
	</section>
</main>
<?php
get_footer();
