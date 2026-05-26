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

<header class="site-header" id="masthead" role="banner">
    <div class="container header-inner">

        <!-- Logo / Site title -->
        <div class="site-branding">
            <?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a class="site-title-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php bloginfo( 'name' ); ?>
                </a>
                <?php $desc = get_bloginfo( 'description' ); if ( $desc ) : ?>
                    <p class="site-tagline"><?php echo esc_html( $desc ); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div><!-- .site-branding -->

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
        </nav><!-- #site-navigation -->

    </div><!-- .container -->
</header><!-- #masthead -->

<main id="main-content" class="site-main" role="main">
<?php

function mybrand_fallback_menu(): void {
    echo '<ul id="primary-menu"><li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Add a menu', 'mybrand' ) . '</a></li></ul>';
}
