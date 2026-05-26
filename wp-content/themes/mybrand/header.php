<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Skip to content', 'mybrand' ); ?></a>

<!-- Top bar -->
<div class="site-topbar" role="complementary" aria-label="<?php esc_attr_e( 'Contact information', 'mybrand' ); ?>">
    <div class="container topbar-inner">
        <div class="topbar-contact">
            <?php $phone = get_theme_mod( 'contact_phone', '0161 123 4567' ); ?>
            <?php $email = get_theme_mod( 'contact_email', 'info@winservecare.co.uk' ); ?>
            <a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $phone ) ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                <?php echo esc_html( $phone ); ?>
            </a>
            <a href="mailto:<?php echo esc_attr( $email ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                <?php echo esc_html( $email ); ?>
            </a>
        </div>
        <span class="topbar-cqc">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>
            CQC Registered &nbsp;<span class="cqc-badge">Good</span>
        </span>
    </div>
</div>

<header class="site-header" id="masthead" role="banner">
    <div class="container header-inner">

        <!-- Logo -->
        <div class="site-branding">
            <?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a class="site-title-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Winserve Care Services – home', 'mybrand' ); ?>">
                    <!-- Winserve inline SVG logo -->
                    <svg class="site-logo-svg" viewBox="0 0 200 52" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <!-- Heart/care icon -->
                        <path d="M22 12c-2.5-4-8-4-10 0-2 4 0 8 4 10l6 4 6-4c4-2 6-6 4-10-2-4-7.5-4-10 0z" fill="#2B78BF"/>
                        <circle cx="26" cy="22" r="3" fill="white" opacity="0.7"/>
                        <!-- WIN in dark red -->
                        <text x="42" y="32" font-family="Poppins, sans-serif" font-weight="800" font-size="22" fill="#8B1A1A">WIN</text>
                        <!-- SERVE in navy -->
                        <text x="88" y="32" font-family="Poppins, sans-serif" font-weight="800" font-size="22" fill="#0E2A47">SERVE</text>
                        <!-- CARE SERVICES tagline -->
                        <text x="42" y="44" font-family="Inter, sans-serif" font-weight="400" font-size="9" fill="#6b7280" letter-spacing="2">CARE SERVICES</text>
                    </svg>
                </a>
            <?php endif; ?>
        </div>

        <!-- Primary navigation -->
        <nav class="site-nav" id="site-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Navigation', 'mybrand' ); ?>">
            <button class="nav-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle menu', 'mybrand' ); ?>">
                <span class="hamburger"></span>
                <span class="hamburger"></span>
                <span class="hamburger"></span>
            </button>

            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'menu_id'        => 'primary-menu',
                'container'      => false,
                'fallback_cb'    => 'mybrand_fallback_menu',
            ] );
            ?>
        </nav>

        <!-- Header CTA -->
        <a class="btn btn-primary header-cta" href="<?php echo esc_url( home_url( '/free-assessment/' ) ); ?>">
            <?php esc_html_e( 'Free Assessment', 'mybrand' ); ?>
        </a>

    </div>
</header>

<main id="main-content" class="site-main" role="main">
<?php

function mybrand_fallback_menu(): void {
    echo '<ul id="primary-menu"><li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Set up navigation', 'mybrand' ) . '</a></li></ul>';
}
