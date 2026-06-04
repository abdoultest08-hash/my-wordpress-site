<?php get_header(); ?>

<section class="page-hero">
  <div class="container">
    <h1>Latest News &amp; Updates</h1>
    <p class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; <span>Blog</span></p>
  </div>
</section>

<section class="blog-section">
  <div class="container">
    <?php if ( have_posts() ) : ?>
      <div class="blog-grid">
        <?php while ( have_posts() ) : the_post(); ?>
          <article class="blog-card" id="post-<?php the_ID(); ?>">
            <?php if ( has_post_thumbnail() ) : ?>
              <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'winserve-service-card', [ 'class' => 'blog-card-img', 'alt' => get_the_title() ] ); ?>
              </a>
            <?php else : ?>
              <div class="blog-card-img-placeholder"></div>
            <?php endif; ?>
            <div class="blog-card-body">
              <p class="blog-date"><?php echo get_the_date(); ?></p>
              <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
              <p class="blog-excerpt"><?php the_excerpt(); ?></p>
              <a href="<?php the_permalink(); ?>" class="read-more">Read More &rarr;</a>
            </div>
          </article>
        <?php endwhile; ?>
      </div>
      <div class="pagination">
        <?php the_posts_pagination( [ 'prev_text' => '&larr;', 'next_text' => '&rarr;' ] ); ?>
      </div>
    <?php else : ?>
      <p style="text-align:center;font-family:var(--font-body);color:#555;padding:60px 0;">No posts found. Check back soon for updates from the Winserve team.</p>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
