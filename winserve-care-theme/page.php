<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge"><?php the_title(); ?></span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; <?php the_title(); ?></p>
  </div>
</section>

<!-- Page Content -->
<section style="padding:72px 0;">
  <div class="container">
    <?php while (have_posts()) : the_post(); ?>
      <div class="post-content" style="max-width:800px;margin:0 auto;">
        <?php the_content(); ?>
      </div>
    <?php endwhile; ?>
  </div>
</section>

<?php get_footer(); ?>
