<?php
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
$cat     = get_the_category();
$blog    = hcc_blog_url();
$related = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'category__in' => $cat ? array( $cat[0]->term_id ) : array(), 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
$is_mgr  = $cat && false !== stripos( $cat[0]->name, 'manager' );
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <a href="<?php echo esc_url( $blog ); ?>">Blog</a><?php echo $cat ? ' / <a href="' . esc_url( get_category_link( $cat[0] ) ) . '">' . esc_html( $cat[0]->name ) . '</a>' : ''; ?></div>
			<h1><?php the_title(); ?></h1>
			<p class="lead"><?php echo esc_html( get_the_date( 'j F Y' ) . ' · ' . hcc_reading_time() . ' min read' ); ?></p>
		</div>
	</section>
	<section class="section">
		<article class="wrap entry post-body" style="max-width:780px">
			<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', array( 'class' => 'post-hero-img', 'alt' => '' ) ); } ?>
			<?php the_content(); ?>

			<aside class="post-cta">
				<?php if ( $is_mgr ) : ?>
					<b>Need staff for your care home?</b>
					<p>Vetted nurses, carers and chefs, with emergency cover within 2 hours. Our line is open 24 hours a day.</p>
					<div class="form-actions" style="margin-top:10px"><a class="btn btn-sun" href="<?php echo esc_url( hcc_page_url( 'clients', '/for-clients/' ) . '#request' ); ?>">Request cover</a><a class="btn btn-ghost" href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', hcc_opt( 'phone' ) ) ); ?>">Call <?php echo esc_html( hcc_opt( 'phone' ) ); ?></a></div>
				<?php else : ?>
					<b>Looking for flexible shifts?</b>
					<p>Register with HCC-Temps in about 15 minutes. Most candidates are ready to work within 3 days.</p>
					<div class="form-actions" style="margin-top:10px"><a class="btn btn-sun" href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">Register now</a><a class="btn btn-ghost" href="<?php echo esc_url( hcc_jobs_url() ); ?>">See jobs</a></div>
				<?php endif; ?>
			</aside>
		</article>
	</section>
	<?php if ( $related->have_posts() ) : $fallback = array( 'kitchen.jpg', 'nurse.jpg', 'carer.jpg' ); $k = 0; ?>
		<section class="section alt">
			<div class="wrap">
				<div class="row-head"><h2>More from the blog</h2><a class="link-arrow" href="<?php echo esc_url( $blog ); ?>">All articles <span aria-hidden="true">→</span></a></div>
				<div class="posts">
					<?php while ( $related->have_posts() ) : $related->the_post(); $rc = get_the_category(); ?>
						<article class="post">
							<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><img src="<?php echo esc_url( has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'medium_large' ) : hcc_img( $fallback[ $k % 3 ] ) ); ?>" alt="" width="720" height="540" loading="lazy"></a>
							<?php if ( $rc ) : ?><div class="cat"><?php echo esc_html( $rc[0]->name ); ?></div><?php endif; ?>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						</article>
					<?php $k++; endwhile; wp_reset_postdata(); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
