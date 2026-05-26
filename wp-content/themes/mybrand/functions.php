<?php
defined( 'ABSPATH' ) || exit;

define( 'MYBRAND_VERSION', '1.0.0' );
define( 'MYBRAND_DIR', get_template_directory() );
define( 'MYBRAND_URI', get_template_directory_uri() );

/* ------------------------------------------------------------------
   Theme setup
------------------------------------------------------------------ */
function mybrand_setup() {
    load_theme_textdomain( 'mybrand', MYBRAND_DIR . '/languages' );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'align-wide' );

    add_theme_support( 'custom-logo', [
        'height'      => 80,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ] );

    // Image sizes for portfolio grid
    add_image_size( 'portfolio-thumb', 600, 450, true );
    add_image_size( 'portfolio-large', 1200, 900, true );
    add_image_size( 'hero-banner',     1920, 800, true );

    register_nav_menus( [
        'primary' => __( 'Primary Navigation', 'mybrand' ),
        'footer'  => __( 'Footer Navigation',  'mybrand' ),
        'social'  => __( 'Social Links',        'mybrand' ),
    ] );
}
add_action( 'after_setup_theme', 'mybrand_setup' );

/* ------------------------------------------------------------------
   Widget areas
------------------------------------------------------------------ */
function mybrand_widgets_init() {
    $sidebars = [
        [ 'name' => __( 'Blog Sidebar', 'mybrand' ), 'id' => 'sidebar-blog' ],
        [ 'name' => __( 'Footer Col 1',  'mybrand' ), 'id' => 'footer-1' ],
        [ 'name' => __( 'Footer Col 2',  'mybrand' ), 'id' => 'footer-2' ],
        [ 'name' => __( 'Footer Col 3',  'mybrand' ), 'id' => 'footer-3' ],
    ];
    foreach ( $sidebars as $s ) {
        register_sidebar( [
            'name'          => $s['name'],
            'id'            => $s['id'],
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ] );
    }
}
add_action( 'widgets_init', 'mybrand_widgets_init' );

/* ------------------------------------------------------------------
   Enqueue styles & scripts
------------------------------------------------------------------ */
function mybrand_scripts() {
    // Google Fonts — swap families here when branding is set
    wp_enqueue_style(
        'mybrand-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'mybrand-style',
        MYBRAND_URI . '/assets/css/main.css',
        [ 'mybrand-fonts' ],
        MYBRAND_VERSION
    );

    // WordPress theme stylesheet last (holds CSS vars)
    wp_enqueue_style( 'mybrand-vars', get_stylesheet_uri(), [ 'mybrand-style' ], MYBRAND_VERSION );

    wp_enqueue_script(
        'mybrand-main',
        MYBRAND_URI . '/assets/js/main.js',
        [],
        MYBRAND_VERSION,
        true
    );

    // Job board styles & scripts — only on job pages
    if ( is_singular( 'winserve_job' ) || is_post_type_archive( 'winserve_job' ) ) {
        wp_enqueue_style(
            'mybrand-jobs',
            MYBRAND_URI . '/assets/css/jobs.css',
            [ 'mybrand-style' ],
            MYBRAND_VERSION
        );
        wp_enqueue_script(
            'mybrand-jobs',
            MYBRAND_URI . '/assets/js/jobs.js',
            [],
            MYBRAND_VERSION,
            true
        );
    }

    wp_localize_script( 'mybrand-main', 'MyBrand', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'mybrand_nonce' ),
    ] );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'mybrand_scripts' );

/* ------------------------------------------------------------------
   Customizer options
------------------------------------------------------------------ */
require_once MYBRAND_DIR . '/inc/customizer.php';

/* ------------------------------------------------------------------
   Job board
------------------------------------------------------------------ */
require_once MYBRAND_DIR . '/inc/jobs.php';

/* ------------------------------------------------------------------
   Helper: section wrapper for template parts
------------------------------------------------------------------ */
function mybrand_section( string $id, string $class, callable $cb ): void {
    printf( '<section id="%s" class="section %s">', esc_attr( $id ), esc_attr( $class ) );
    echo '<div class="container">';
    $cb();
    echo '</div></section>';
}

/* ------------------------------------------------------------------
   Excerpt length
------------------------------------------------------------------ */
add_filter( 'excerpt_length', fn() => 25 );
add_filter( 'excerpt_more',   fn() => '&hellip;' );
