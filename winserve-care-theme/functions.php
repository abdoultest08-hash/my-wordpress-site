<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function winserve_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'html5', ['search-form','comment-form','comment-list','gallery','caption'] );
    add_theme_support( 'responsive-embeds' );
    register_nav_menus([
        'primary-menu' => 'Primary Menu',
        'footer-menu'  => 'Footer Menu',
    ]);
    add_image_size( 'winserve-hero', 1920, 700, true );
    add_image_size( 'winserve-card', 600, 400, true );
    add_image_size( 'winserve-team', 400, 400, true );
}
add_action( 'after_setup_theme', 'winserve_setup' );

function winserve_enqueue() {
    wp_enqueue_style(
        'winserve-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap',
        [], null
    );
    wp_enqueue_style( 'winserve-main', get_template_directory_uri() . '/assets/css/main.css', ['winserve-fonts'], '2.0.0' );
    wp_enqueue_style( 'winserve-style', get_stylesheet_uri(), ['winserve-main'], '2.0.0' );
    wp_enqueue_script( 'winserve-js', get_template_directory_uri() . '/assets/js/main.js', [], '2.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'winserve_enqueue' );

// Contact form handler
function winserve_handle_contact() {
    if ( ! isset($_POST['winserve_nonce']) || ! wp_verify_nonce($_POST['winserve_nonce'], 'winserve_contact') ) {
        wp_die('Security check failed');
    }
    $to      = 'info@winservecare.co.uk';
    $subject = 'Website Enquiry: ' . sanitize_text_field($_POST['subject'] ?? 'General');
    $body    = "Name: " . sanitize_text_field($_POST['full_name'] ?? '') . "\n"
             . "Email: " . sanitize_email($_POST['email'] ?? '') . "\n"
             . "Phone: " . sanitize_text_field($_POST['phone'] ?? '') . "\n"
             . "Subject: " . sanitize_text_field($_POST['subject'] ?? '') . "\n\n"
             . "Message:\n" . sanitize_textarea_field($_POST['message'] ?? '');
    $headers = ['Content-Type: text/plain; charset=UTF-8', 'From: Website <info@winservecare.co.uk>'];
    wp_mail( $to, $subject, $body, $headers );
    wp_redirect( add_query_arg('sent', '1', wp_get_referer()) );
    exit;
}
add_action( 'admin_post_nopriv_winserve_contact', 'winserve_handle_contact' );
add_action( 'admin_post_winserve_contact',        'winserve_handle_contact' );

// Vacancy application handler
function winserve_handle_application() {
    if ( ! isset($_POST['app_nonce']) || ! wp_verify_nonce($_POST['app_nonce'], 'winserve_application') ) {
        wp_die('Security check failed');
    }
    $to      = 'hr@winservecare.co.uk';
    $role    = sanitize_text_field($_POST['role'] ?? 'Unknown Role');
    $subject = 'Job Application: ' . $role;
    $body    = "Role Applied For: {$role}\n\n"
             . "First Name: " . sanitize_text_field($_POST['first_name'] ?? '') . "\n"
             . "Last Name: "  . sanitize_text_field($_POST['last_name']  ?? '') . "\n"
             . "Email: "      . sanitize_email($_POST['email']           ?? '') . "\n"
             . "Phone: "      . sanitize_text_field($_POST['phone']      ?? '') . "\n"
             . "Location: "   . sanitize_text_field($_POST['location']   ?? '') . "\n"
             . "Experience: " . sanitize_textarea_field($_POST['experience'] ?? '') . "\n\n"
             . "Why Applying:\n" . sanitize_textarea_field($_POST['why_applying'] ?? '');
    $headers = ['Content-Type: text/plain; charset=UTF-8', 'From: Website <info@winservecare.co.uk>'];
    wp_mail( $to, $subject, $body, $headers );
    wp_redirect( add_query_arg('applied', '1', wp_get_referer()) );
    exit;
}
add_action( 'admin_post_nopriv_winserve_application', 'winserve_handle_application' );
add_action( 'admin_post_winserve_application',        'winserve_handle_application' );
