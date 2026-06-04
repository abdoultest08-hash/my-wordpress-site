<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function winserve_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'html5', [ 'search-form','comment-form','comment-list','gallery','caption' ] );
    add_theme_support( 'responsive-embeds' );

    register_nav_menus( [
        'primary-menu' => __( 'Primary Menu', 'winserve-care' ),
        'footer-menu'  => __( 'Footer Menu', 'winserve-care' ),
    ] );

    add_image_size( 'winserve-hero', 1920, 700, true );
    add_image_size( 'winserve-service-card', 600, 400, true );
    add_image_size( 'winserve-team-card', 400, 400, true );
}
add_action( 'after_setup_theme', 'winserve_setup' );

function winserve_enqueue() {
    wp_enqueue_style(
        'winserve-google-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap',
        [],
        null
    );
    wp_enqueue_style( 'winserve-main', get_template_directory_uri() . '/assets/css/main.css', ['winserve-google-fonts'], '1.0.0' );
    wp_enqueue_style( 'winserve-style', get_stylesheet_uri(), ['winserve-main'], '1.0.0' );
    wp_enqueue_script( 'winserve-js', get_template_directory_uri() . '/assets/js/main.js', [], '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'winserve_enqueue' );

// ACF editable sections - add fields if ACF is active
// Fields: hero_headline, hero_subtext, about_body_text, cta_heading
