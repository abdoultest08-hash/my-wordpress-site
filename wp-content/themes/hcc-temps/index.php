<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<h1><?php echo is_home() ? 'From the HCC blog' : esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
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
