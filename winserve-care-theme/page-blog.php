<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge">Blog</span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; Blog</p>
  </div>
</section>

<!-- Blog Intro -->
<section class="team-intro-section">
  <div class="container">
    <span class="eyebrow">+ News &amp; Insights</span>
    <h2 style="font-family:var(--font-heading);font-size:38px;color:var(--navy);margin-bottom:16px;line-height:1.2;">From the Winserve Blog</h2>
    <p style="font-family:var(--font-body);font-size:14px;color:#555;max-width:600px;margin:0 auto;line-height:1.85;">Stay up to date with the latest news, care advice, and updates from the Winserve Care Services team.</p>
  </div>
</section>

<!-- Blog Grid -->
<section style="padding:60px 0;background:#fff;">
  <div class="container">
    <?php
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $blog_query = new WP_Query([
      'posts_per_page' => 9,
      'post_status'    => 'publish',
      'paged'          => $paged,
    ]);
    if ($blog_query->have_posts()) :
    ?>
    <div class="blog-grid">
      <?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
        <article class="blog-card">
          <?php if (has_post_thumbnail()) : ?>
            <img src="<?php the_post_thumbnail_url('winserve-card'); ?>" alt="<?php the_title_attribute(); ?>" class="blog-card-img">
          <?php else : ?>
            <div class="blog-card-img-ph"></div>
          <?php endif; ?>
          <div class="blog-card-body">
            <div class="blog-meta"><?php echo get_the_date(); ?> &bull; <?php echo get_the_category_list(', '); ?></div>
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p class="blog-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 24); ?></p>
            <a href="<?php the_permalink(); ?>" class="read-more">Read More &rarr;</a>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <!-- Pagination -->
    <div style="text-align:center;margin-top:48px;">
      <?php
      echo paginate_links([
        'total'   => $blog_query->max_num_pages,
        'current' => $paged,
        'mid_size' => 2,
      ]);
      ?>
    </div>

    <?php else : ?>
    <div style="text-align:center;padding:80px 0;">
      <p style="font-family:var(--font-body);font-size:16px;color:#777;">No posts have been published yet. Check back soon for news and updates from the Winserve team.</p>
      <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-more" style="margin-top:24px;display:inline-block;">Return to Homepage &rarr;</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- CTA Banner -->
<section class="cta-banner">
  <div class="container cta-banner-inner">
    <div>
      <span class="cta-eyebrow">+ Get In Touch</span>
      <h2>Need to Speak to Our Care Team?</h2>
      <p>We are here to answer any questions and help you find the right care solution for you or your loved one.</p>
    </div>
    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-cta">Contact Us &rarr;</a>
  </div>
</section>

<?php get_footer(); ?>
