<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<section class="page-hero">
  <div class="container">
    <h1><?php the_title(); ?></h1>
    <p class="breadcrumb">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo;
      <a href="<?php echo esc_url(get_post_type_archive_link('post')); ?>">Blog</a> &rsaquo;
      <span><?php the_title(); ?></span>
    </p>
  </div>
</section>

<section class="single-post">
  <div class="container">
    <div class="single-post-inner">
      <?php if ( has_post_thumbnail() ) : ?>
        <?php the_post_thumbnail( 'winserve-hero', [ 'class' => 'post-featured-img', 'alt' => get_the_title() ] ); ?>
      <?php endif; ?>
      <p class="post-meta">
        <span>Published: <?php echo get_the_date(); ?></span>
        <span>By: <?php the_author(); ?></span>
        <span><?php the_category(', '); ?></span>
      </p>
      <div class="post-content">
        <?php the_content(); ?>
      </div>
    </div>
  </div>
</section>

<?php endwhile; ?>

<section class="related-posts">
  <div class="container">
    <h2>More from Our Blog</h2>
    <?php
    $related = new WP_Query([
        'post_type'      => 'post',
        'posts_per_page' => 3,
        'post__not_in'   => [ get_the_ID() ],
        'orderby'        => 'rand',
    ]);
    if ( $related->have_posts() ) :
    ?>
    <div class="blog-grid">
      <?php while ( $related->have_posts() ) : $related->the_post(); ?>
        <article class="blog-card">
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
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
