<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <?php echo is_home() ? 'Blog' : '<a href="' . esc_url( hcc_blog_url() ) . '">Blog</a> / ' . esc_html( single_cat_title( '', false ) ); ?></div>
			<h1><?php echo is_home() ? 'From the HCC blog' : esc_html( is_category() ? single_cat_title( '', false ) : wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
			<p class="lead">Guides and news for nurses, carers, chefs and care home managers.</p>
			<?php $cats = get_categories( array( 'hide_empty' => true ) ); if ( count( $cats ) > 1 ) : ?>
				<nav class="cat-chips" aria-label="Blog categories"><a href="<?php echo esc_url( hcc_blog_url() ); ?>"<?php echo is_home() ? ' aria-current="page"' : ''; ?>>All</a><?php foreach ( $cats as $c ) : ?><a href="<?php echo esc_url( get_category_link( $c ) ); ?>"<?php echo is_category( $c->term_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $c->name ); ?></a><?php endforeach; ?></nav>
			<?php endif; ?>
		</div>
	</section>
	<section class="section">
		<div class="wrap">
			<?php if ( have_posts() ) : $k = 0; $fallback = array( 'kitchen.jpg', 'nurse.jpg', 'housekeeping.jpg' ); ?>
				<div class="posts">
					<?php while ( have_posts() ) : the_post(); $cat = get_the_category(); ?>
						<article class="post">
							<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><img src="<?php echo esc_url( has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'medium_large' ) : hcc_img( $fallback[ $k % 3 ] ) ); ?>" alt="" width="720" height="540" loading="lazy"></a>
							<?php if ( $cat ) : ?><div class="cat"><?php echo esc_html( $cat[0]->name ); ?></div><?php endif; ?>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						</article>
					<?php $k++; endwhile; ?>
				</div>
				<nav class="pager" aria-label="Pages"><?php echo paginate_links(); // phpcs:ignore ?></nav>
			<?php else : ?>
				<p>Nothing here yet.</p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
