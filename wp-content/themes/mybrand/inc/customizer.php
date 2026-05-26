<?php
defined( 'ABSPATH' ) || exit;

function mybrand_customize_register( WP_Customize_Manager $wp_customize ): void {

    // ── Hero Section ──────────────────────────────────────────────────────────
    $wp_customize->add_section( 'mybrand_hero', [
        'title'    => __( 'Hero Section', 'mybrand' ),
        'priority' => 30,
    ] );

    $hero_fields = [
        [ 'hero_headline',  __( 'We Build Brands That Matter', 'mybrand' ), 'text',     __( 'Hero Headline', 'mybrand' ) ],
        [ 'hero_subline',   __( 'Your slogan goes here.', 'mybrand' ),      'text',     __( 'Hero Sub-headline', 'mybrand' ) ],
        [ 'hero_cta_text',  __( 'See Our Work', 'mybrand' ),                'text',     __( 'CTA Button Text', 'mybrand' ) ],
        [ 'hero_cta_url',   '#portfolio',                                    'url',      __( 'CTA Button URL', 'mybrand' ) ],
        [ 'hero_cta2_text', __( 'Get In Touch', 'mybrand' ),                'text',     __( 'Secondary CTA Text', 'mybrand' ) ],
        [ 'hero_cta2_url',  '#contact',                                      'url',      __( 'Secondary CTA URL', 'mybrand' ) ],
        [ 'hero_bg_image',  '',                                              'url',      __( 'Background Image URL', 'mybrand' ) ],
    ];

    foreach ( $hero_fields as [ $id, $default, $type, $label ] ) {
        $wp_customize->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( $id, [ 'label' => $label, 'section' => 'mybrand_hero', 'type' => $type ] );
    }

    // ── About Section ─────────────────────────────────────────────────────────
    $wp_customize->add_section( 'mybrand_about', [
        'title'    => __( 'About Section', 'mybrand' ),
        'priority' => 35,
    ] );

    $wp_customize->add_setting( 'about_title', [ 'default' => __( 'About Us', 'mybrand' ), 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'about_title', [ 'label' => __( 'Title', 'mybrand' ), 'section' => 'mybrand_about', 'type' => 'text' ] );

    $wp_customize->add_setting( 'about_text', [ 'default' => '', 'sanitize_callback' => 'wp_kses_post' ] );
    $wp_customize->add_control( 'about_text', [ 'label' => __( 'About Text', 'mybrand' ), 'section' => 'mybrand_about', 'type' => 'textarea' ] );

    $wp_customize->add_setting( 'about_image', [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'about_image', [
        'label'   => __( 'About Image', 'mybrand' ),
        'section' => 'mybrand_about',
    ] ) );

    // ── Contact Info ──────────────────────────────────────────────────────────
    $wp_customize->add_section( 'mybrand_contact', [
        'title'    => __( 'Contact Info', 'mybrand' ),
        'priority' => 40,
    ] );

    foreach ( [
        [ 'contact_email',   'info@winservecare.co.uk',  __( 'Email Address', 'mybrand' ) ],
        [ 'contact_phone',   '0161 123 4567',             __( 'Phone Number',  'mybrand' ) ],
        [ 'contact_address', 'Manchester, United Kingdom',__( 'Address',       'mybrand' ) ],
    ] as [ $id, $default, $label ] ) {
        $wp_customize->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( $id, [ 'label' => $label, 'section' => 'mybrand_contact', 'type' => 'text' ] );
    }
}
add_action( 'customize_register', 'mybrand_customize_register' );
