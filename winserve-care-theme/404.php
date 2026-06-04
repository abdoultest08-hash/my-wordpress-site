<?php get_header(); ?>

<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge">Page Not Found</span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; 404</p>
  </div>
</section>

<section style="padding:100px 0;text-align:center;background:#fff;">
  <div class="container">
    <div style="font-family:var(--font-heading);font-size:120px;font-weight:700;color:var(--card-bg);line-height:1;margin-bottom:0;">404</div>
    <h1 style="font-family:var(--font-heading);font-size:40px;color:var(--navy);margin-bottom:16px;line-height:1.2;">Sorry, we couldn&rsquo;t find that page</h1>
    <p style="font-family:var(--font-body);font-size:15px;color:#666;max-width:480px;margin:0 auto 36px;line-height:1.75;">The page you&rsquo;re looking for may have moved or no longer exists. Try one of the links below, or return to the homepage.</p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-more">Back to Homepage</a>
      <a href="<?php echo esc_url(home_url('/services')); ?>" class="btn-more">Our Services</a>
      <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-more">Contact Us</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
