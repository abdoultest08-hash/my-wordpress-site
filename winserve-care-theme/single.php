<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

<!-- Page Hero with Post Title -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge" style="font-size:26px;max-width:800px;margin:0 auto;"><?php the_title(); ?></span>
    <p class="page-breadcrumb">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo;
      <a href="<?php echo esc_url(home_url('/blog')); ?>">Blog</a> &rsaquo;
      <?php the_title(); ?>
    </p>
  </div>
</section>

<!-- Single Post -->
<section class="single-post-section">
  <div class="container">
    <div class="single-post-inner">

      <?php if (has_post_thumbnail()) : ?>
        <img src="<?php the_post_thumbnail_url('winserve-hero'); ?>" alt="<?php the_title_attribute(); ?>" class="post-feat-img">
      <?php endif; ?>

      <div class="post-meta-bar">
        <span><?php echo get_the_date(); ?></span>
        <span>By <?php the_author(); ?></span>
        <?php $cats = get_the_category(); if ($cats) : ?>
          <span><?php echo esc_html($cats[0]->name); ?></span>
        <?php endif; ?>
      </div>

      <div class="post-content">
        <?php the_content(); ?>
      </div>

    </div>
  </div>
</section>

<?php endwhile; ?>

<!-- Related Posts -->
<?php
$cats = get_the_category();
$cat_ids = wp_list_pluck($cats, 'term_id');
$related = new WP_Query([
    'category__in'   => $cat_ids,
    'post__not_in'   => [get_the_ID()],
    'posts_per_page' => 3,
    'orderby'        => 'rand',
    'post_status'    => 'publish',
]);
if ($related->have_posts()) :
?>
<section class="related-posts-section">
  <div class="container">
    <h2>More From the Winserve Blog</h2>
    <div class="blog-grid">
      <?php while ($related->have_posts()) : $related->the_post(); ?>
        <article class="blog-card">
          <?php if (has_post_thumbnail()) : ?>
            <img src="<?php the_post_thumbnail_url('winserve-card'); ?>" alt="<?php the_title_attribute(); ?>" class="blog-card-img">
          <?php else : ?>
            <div class="blog-card-img-ph"></div>
          <?php endif; ?>
          <div class="blog-card-body">
            <div class="blog-meta"><?php echo get_the_date(); ?></div>
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p class="blog-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 18); ?></p>
            <a href="<?php the_permalink(); ?>" class="read-more">Read More &rarr;</a>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
