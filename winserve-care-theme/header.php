<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- TOPBAR -->
<div class="topbar">
  <div class="container">
    <div class="topbar-inner">
      <div class="topbar-left">
        <span>CQC Registered &middot; Serving Leeds &amp; Cornwall</span>
        <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="topbar-badge">Book Assessment</a>
      </div>
      <div class="topbar-right">
        <a href="<?php echo esc_url( home_url( '/about' ) ); ?>">Company History</a>
        <a href="#">Careers</a>
        <a href="<?php echo esc_url( home_url( '/staff-portal' ) ); ?>">Staff Portal</a>
      </div>
    </div>
  </div>
</div>

<!-- NAVIGATION -->
<header class="site-header" id="site-header">
  <div class="container">
    <nav class="nav-inner" role="navigation" aria-label="Primary Navigation">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" aria-label="<?php bloginfo('name'); ?> Home">
        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/logo.png" alt="<?php bloginfo('name'); ?> logo" width="160" height="52">
      </a>
      <button class="menu-toggle" id="menu-toggle" aria-label="Toggle navigation" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <div class="primary-nav" id="primary-nav">
        <?php
        wp_nav_menu( [
            'theme_location' => 'primary-menu',
            'container'      => false,
            'items_wrap'     => '%3$s',
            'fallback_cb'    => 'winserve_fallback_menu',
        ] );
        ?>
        <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="nav-cta">Free Assessment &rarr;</a>
      </div>
    </nav>
  </div>
</header>

<?php
function winserve_fallback_menu() {
    echo '<a href="' . esc_url( home_url( '/' ) ) . '">Home</a>';
    echo '<a href="' . esc_url( home_url( '/about' ) ) . '">About Us</a>';
    echo '<a href="' . esc_url( home_url( '/services' ) ) . '">Our Services</a>';
    echo '<a href="' . esc_url( home_url( '/team' ) ) . '">Our Team</a>';
    echo '<a href="' . esc_url( home_url( '/blog' ) ) . '">Blog</a>';
    echo '<a href="' . esc_url( home_url( '/contact' ) ) . '">Contact Us</a>';
}
?>
