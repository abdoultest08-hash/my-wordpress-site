<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<section class="page-hero">
  <div class="container">
    <h1><?php the_title(); ?></h1>
    <p class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; <span><?php the_title(); ?></span></p>
  </div>
</section>

<section class="page-content">
  <div class="container">
    <div class="page-content-inner">
      <?php the_content(); ?>
    </div>
  </div>
</section>

<?php endwhile; ?>

<?php get_footer(); ?>
