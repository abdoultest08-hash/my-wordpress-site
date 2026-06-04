<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge"><?php wp_title(''); ?></span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></p>
  </div>
</section>

<!-- Blog Loop -->
<section class="blog-section">
  <div class="container">
    <?php if (have_posts()) : ?>
      <div class="blog-grid">
        <?php while (have_posts()) : the_post(); ?>
          <article class="blog-card">
            <?php if (has_post_thumbnail()) : ?>
              <img src="<?php the_post_thumbnail_url('winserve-card'); ?>" alt="<?php the_title_attribute(); ?>" class="blog-card-img">
            <?php else : ?>
              <div class="blog-card-img-ph"></div>
            <?php endif; ?>
            <div class="blog-card-body">
              <div class="blog-meta"><?php echo get_the_date(); ?></div>
              <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
              <p class="blog-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 22); ?></p>
              <a href="<?php the_permalink(); ?>" class="read-more">Read More &rarr;</a>
            </div>
          </article>
        <?php endwhile; ?>
      </div>

      <div class="pagination">
        <?php
        echo paginate_links([
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        ]);
        ?>
      </div>

    <?php else : ?>
      <div style="text-align:center;padding:60px 0;">
        <h2 style="font-family:var(--font-heading);font-size:32px;color:var(--navy);margin-bottom:16px;">No content found</h2>
        <p style="font-family:var(--font-body);font-size:14px;color:#666;">Please check back soon.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-more" style="margin-top:24px;display:inline-flex;">Back to Home &rarr;</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
