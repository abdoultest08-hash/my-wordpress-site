<?php get_header(); ?>

<section class="page-hero">
  <div class="container">
    <h1>Staff Portal</h1>
    <p class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; <span>Staff Portal</span></p>
  </div>
</section>

<section class="staff-portal">
  <div class="container">
    <?php if ( is_user_logged_in() ) : ?>
      <div class="staff-welcome">
        <h2>Welcome Back, <?php echo esc_html( wp_get_current_user()->display_name ); ?></h2>
        <p>You are logged in to the Winserve Staff Portal. Use the links below to access your resources.</p>
        <div class="staff-links">
          <a href="<?php echo esc_url(admin_url()); ?>" class="btn-primary">Dashboard</a>
          <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="btn-outline-white" style="border-color:var(--border);color:var(--navy);">Log Out</a>
        </div>
      </div>
    <?php else : ?>
      <div class="portal-lock">
        <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
      </div>
      <h2>Staff Only Area</h2>
      <p>This area is for Winserve staff only. Please log in with your staff credentials to access the portal.</p>
      <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="btn-primary">Log In to Staff Portal</a>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
