<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================================================
   THEME SETUP
   ========================================================= */
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

/* =========================================================
   ENQUEUE SCRIPTS & STYLES
   ========================================================= */
function winserve_enqueue() {
    wp_enqueue_style(
        'winserve-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap',
        [], null
    );
    wp_enqueue_style( 'winserve-main', get_template_directory_uri() . '/assets/css/main.css', ['winserve-fonts'], '2.1.0' );
    wp_enqueue_style( 'winserve-style', get_stylesheet_uri(), ['winserve-main'], '2.1.0' );
    wp_enqueue_script( 'winserve-js', get_template_directory_uri() . '/assets/js/main.js', [], '2.1.0', true );
}
add_action( 'wp_enqueue_scripts', 'winserve_enqueue' );

/* =========================================================
   SEO: META TAGS, OPEN GRAPH, TWITTER CARDS, JSON-LD
   ========================================================= */
function winserve_seo_head() {
    // Skip if an SEO plugin is active
    if ( defined('WPSEO_VERSION') || defined('RANKMATH_VERSION') || defined('AIOSEO_VERSION') ) return;

    $site_name   = 'Winserve Care Services Ltd';
    $site_url    = home_url('/');
    $default_img = get_template_directory_uri() . '/assets/images/hero-bg.jpg';
    $default_desc = 'CQC-rated Good domiciliary and supported living care across Leeds and Cornwall. Compassionate, professional home care. Call 0113 340 8777.';

    // Per-page meta
    $meta = [];

    if ( is_front_page() ) {
        $meta = [
            'title' => 'Trusted Home Care in Leeds &amp; Cornwall | Winserve Care Services',
            'desc'  => 'Winserve Care Services Ltd is a CQC Good-rated domiciliary and supported living care provider serving Leeds and Cornwall. Person-centred, compassionate home care. Free assessment — call 0113 340 8777.',
            'img'   => $default_img,
        ];
    } elseif ( is_page('about') ) {
        $meta = [
            'title' => 'About Us | Winserve Care Services Ltd — CQC Rated Good',
            'desc'  => 'Learn about Winserve Care Services — our story, our mission, why commissioners choose us, and how we deliver safe, person-centred care across Leeds and Cornwall. CQC Rated Good June 2025.',
            'img'   => get_template_directory_uri() . '/assets/images/about-main.jpg',
        ];
    } elseif ( is_page('services') ) {
        $meta = [
            'title' => 'Home Care Services in Leeds &amp; Cornwall | Winserve Care Services',
            'desc'  => 'Winserve provides domiciliary care, supported living, dementia care, live-in care, palliative care and more across Leeds and Cornwall. CQC-registered. Enquire today.',
            'img'   => get_template_directory_uri() . '/assets/images/service-dementia-care.png',
        ];
    } elseif ( is_page('our-team') ) {
        $meta = [
            'title' => 'Our Team | Winserve Care Services Ltd',
            'desc'  => 'Meet the dedicated team behind Winserve Care Services — from our founding Managing Director to our 50+ frontline carers across Leeds and Cornwall.',
            'img'   => $default_img,
        ];
    } elseif ( is_page('contact') ) {
        $meta = [
            'title' => 'Contact Us | Winserve Care Services — Leeds &amp; Cornwall',
            'desc'  => 'Get in touch with Winserve Care Services. Request a free care assessment, enquire about our services, or apply to join our team. Call 0113 340 8777.',
            'img'   => $default_img,
        ];
    } elseif ( is_page('vacancies') ) {
        $meta = [
            'title' => 'Care Jobs in Leeds | Winserve Care Services — Now Hiring',
            'desc'  => 'Join the Winserve Care team. We are hiring Care Assistants and Service Delivery Coordinators in Leeds. Full training provided. Company car. Real Living Wage.',
            'img'   => get_template_directory_uri() . '/assets/images/recruit-warmth.png',
        ];
    } elseif ( is_page('blog') || is_home() ) {
        $meta = [
            'title' => 'Care Advice &amp; News | Winserve Care Services Blog',
            'desc'  => 'Read the latest care advice, domiciliary care guides, and news from the Winserve Care Services team. Expert insight on home care, CQC standards and more.',
            'img'   => $default_img,
        ];
    } elseif ( is_page('privacy-policy') || is_page('privacy') ) {
        $meta = [
            'title' => 'Privacy Policy | Winserve Care Services Ltd',
            'desc'  => 'Read the Winserve Care Services privacy policy. How we collect, use and protect your personal data in accordance with UK GDPR.',
            'img'   => $default_img,
        ];
    } elseif ( is_single() ) {
        $meta = [
            'title' => get_the_title() . ' | Winserve Care Blog',
            'desc'  => wp_trim_words( get_the_excerpt(), 25, '...' ),
            'img'   => has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'winserve-hero') : $default_img,
        ];
    } else {
        $meta = [
            'title' => get_the_title() . ' | ' . $site_name,
            'desc'  => $default_desc,
            'img'   => $default_img,
        ];
    }

    $title = isset($meta['title']) ? esc_attr(wp_strip_all_tags($meta['title'])) : esc_attr($site_name);
    $desc  = isset($meta['desc'])  ? esc_attr(wp_strip_all_tags($meta['desc']))  : esc_attr($default_desc);
    $img   = isset($meta['img'])   ? esc_url($meta['img'])                        : esc_url($default_img);
    $url   = esc_url( is_singular() ? get_permalink() : $site_url );

    echo "\n<!-- Winserve SEO Meta -->\n";
    echo '<meta name="description" content="' . $desc . '">' . "\n";
    echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">' . "\n";
    echo '<link rel="canonical" href="' . $url . '">' . "\n";

    // Open Graph
    echo '<meta property="og:type" content="' . ( is_single() ? 'article' : 'website' ) . '">' . "\n";
    echo '<meta property="og:title" content="' . $title . '">' . "\n";
    echo '<meta property="og:description" content="' . $desc . '">' . "\n";
    echo '<meta property="og:image" content="' . $img . '">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
    echo '<meta property="og:url" content="' . $url . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta property="og:locale" content="en_GB">' . "\n";

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . $title . '">' . "\n";
    echo '<meta name="twitter:description" content="' . $desc . '">' . "\n";
    echo '<meta name="twitter:image" content="' . $img . '">' . "\n";

    echo "<!-- /Winserve SEO Meta -->\n\n";
}
add_action( 'wp_head', 'winserve_seo_head', 1 );

/* =========================================================
   JSON-LD STRUCTURED DATA (LocalBusiness + homepage only)
   ========================================================= */
function winserve_structured_data() {
    if ( defined('WPSEO_VERSION') || defined('RANKMATH_VERSION') ) return;

    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => ['LocalBusiness', 'MedicalOrganization'],
        'name'     => 'Winserve Care Services Ltd',
        'description' => 'CQC-registered domiciliary and supported living care provider serving Leeds and Cornwall. Rated Good by the Care Quality Commission, June 2025.',
        'url'         => home_url('/'),
        'logo'        => get_template_directory_uri() . '/assets/images/logo-white.png',
        'image'       => get_template_directory_uri() . '/assets/images/hero-bg.jpg',
        'telephone'   => '+441133408777',
        'email'       => 'info@winservecare.co.uk',
        'address'     => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => 'Unit 52, Pure Offices, Turnberry Park Road, Morley',
            'addressLocality' => 'Leeds',
            'postalCode'      => 'LS27 7LE',
            'addressCountry'  => 'GB',
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => '53.7477',
            'longitude' => '-1.5985',
        ],
        'areaServed'    => [
            ['@type' => 'City', 'name' => 'Leeds'],
            ['@type' => 'County', 'name' => 'Cornwall'],
        ],
        'openingHoursSpecification' => [[
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
            'opens'     => '08:00',
            'closes'    => '17:00',
        ]],
        'sameAs' => [
            'https://www.facebook.com/WinserveCareUK/',
            'https://uk.linkedin.com/company/winserve-care-services-ltd',
            'https://www.homecare.co.uk/homecare/agency.cfm/id/65432238891',
            'https://www.cqc.org.uk/location/1-8945106634',
        ],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name'  => 'Care Services',
            'itemListElement' => array_map(function($s) {
                return ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $s]];
            }, ['Domiciliary Care','Supported Living','Dementia Care','Live-In Care','Complex Care','Palliative Care','Learning Disabilities Support','Mental Health Support']),
        ],
        'aggregateRating' => [
            '@type'       => 'AggregateRating',
            'ratingValue' => '9.8',
            'reviewCount' => '74',
            'bestRating'  => '10',
            'worstRating' => '1',
        ],
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";

    // Breadcrumb structured data on inner pages
    if ( is_page() && ! is_front_page() ) {
        $breadcrumb = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => get_the_title()],
            ],
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($breadcrumb, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}
add_action( 'wp_head', 'winserve_structured_data', 2 );

/* =========================================================
   DYNAMIC TITLE TAG
   ========================================================= */
function winserve_title_tag( $title ) {
    if ( defined('WPSEO_VERSION') || defined('RANKMATH_VERSION') ) return $title;

    $parts = [];
    if ( is_front_page() ) {
        $parts[] = 'Winserve Care Services | Home Care Leeds &amp; Cornwall | CQC Rated Good';
    } elseif ( is_singular() || is_page() ) {
        $parts[] = get_the_title();
        $parts[] = 'Winserve Care Services Ltd';
    } elseif ( is_home() ) {
        $parts[] = 'Care Blog';
        $parts[] = 'Winserve Care Services';
    } else {
        return $title;
    }
    return implode( ' &#124; ', array_filter($parts) ) . ' ';
}
add_filter( 'pre_get_document_title', 'winserve_title_tag' );

/* =========================================================
   CONTACT FORM HANDLER (care enquiry, general, services)
   ========================================================= */
function winserve_handle_contact() {
    if ( ! isset($_POST['winserve_nonce']) || ! wp_verify_nonce($_POST['winserve_nonce'], 'winserve_contact') ) {
        wp_die('Security check failed');
    }

    $to       = 'info@winservecare.co.uk';
    $pathway  = sanitize_text_field( $_POST['pathway'] ?? 'General Enquiry' );

    // Build full name from either field format
    $full_name = sanitize_text_field( $_POST['full_name'] ?? '' );
    if ( empty($full_name) ) {
        $full_name = trim( sanitize_text_field($_POST['first_name'] ?? '') . ' ' . sanitize_text_field($_POST['last_name'] ?? '') );
    }

    $email   = sanitize_email( $_POST['email'] ?? '' );
    $phone   = sanitize_text_field( $_POST['phone'] ?? '' );
    $subject = 'Website Enquiry [' . $pathway . ']';

    $body = "ENQUIRY TYPE: {$pathway}\n";
    $body .= str_repeat('-', 40) . "\n";
    $body .= "Name: {$full_name}\n";
    $body .= "Email: {$email}\n";
    $body .= "Phone: {$phone}\n";

    // Care-specific fields
    if ( ! empty($_POST['care_for']) )      $body .= "Care For: "       . sanitize_text_field($_POST['care_for'])      . "\n";
    if ( ! empty($_POST['care_needs']) )    $body .= "Care Needs: "     . sanitize_text_field($_POST['care_needs'])    . "\n";
    if ( ! empty($_POST['postcode']) )      $body .= "Postcode: "       . sanitize_text_field($_POST['postcode'])      . "\n";
    if ( ! empty($_POST['hours_per_week']) ) $body .= "Hours/Week: "    . sanitize_text_field($_POST['hours_per_week']). "\n";
    if ( ! empty($_POST['funding']) )       $body .= "Funding: "        . sanitize_text_field($_POST['funding'])       . "\n";

    // Service enquiry field
    if ( ! empty($_POST['subject']) )       $body .= "Service Interest: ". sanitize_text_field($_POST['subject'])     . "\n";

    // General message
    if ( ! empty($_POST['message']) ) {
        $body .= "\nMessage:\n" . sanitize_textarea_field($_POST['message']) . "\n";
    }

    $headers = ['Content-Type: text/plain; charset=UTF-8', 'From: Winserve Website <info@winservecare.co.uk>', 'Reply-To: ' . $email];
    $sent = wp_mail( $to, $subject, $body, $headers );
    $param = $sent ? ['sent' => '1'] : ['sent' => 'error'];
    wp_redirect( add_query_arg($param, wp_get_referer()) );
    exit;
}
add_action( 'admin_post_nopriv_winserve_contact', 'winserve_handle_contact' );
add_action( 'admin_post_winserve_contact',        'winserve_handle_contact' );

/* =========================================================
   VACANCY APPLICATION HANDLER
   ========================================================= */
function winserve_handle_application() {
    if ( ! isset($_POST['app_nonce']) || ! wp_verify_nonce($_POST['app_nonce'], 'winserve_application') ) {
        wp_die('Security check failed');
    }
    $to    = 'hr@winservecare.co.uk';
    $role  = sanitize_text_field( $_POST['role'] ?? 'Unknown Role' );
    $fname = sanitize_text_field( $_POST['first_name'] ?? '' );
    $lname = sanitize_text_field( $_POST['last_name']  ?? '' );
    $email = sanitize_email( $_POST['email'] ?? '' );

    $subject = 'Job Application: ' . $role;
    $body    = "Role Applied For: {$role}\n";
    $body   .= str_repeat('-', 40) . "\n";
    $body   .= "Name: {$fname} {$lname}\n";
    $body   .= "Email: {$email}\n";
    $body   .= "Phone: "       . sanitize_text_field($_POST['phone']      ?? '') . "\n";
    $body   .= "Location: "    . sanitize_text_field($_POST['location']   ?? '') . "\n";
    $body   .= "Experience: \n". sanitize_textarea_field($_POST['experience'] ?? '') . "\n\n";
    $body   .= "Why Applying:\n" . sanitize_textarea_field($_POST['why_applying'] ?? '');

    $headers = ['Content-Type: text/plain; charset=UTF-8', 'From: Winserve Website <info@winservecare.co.uk>', 'Reply-To: ' . $email];
    $sent = wp_mail( $to, $subject, $body, $headers );
    $param = $sent ? ['applied' => '1'] : ['applied' => 'error'];
    wp_redirect( add_query_arg($param, wp_get_referer()) );
    exit;
}
add_action( 'admin_post_nopriv_winserve_application', 'winserve_handle_application' );
add_action( 'admin_post_winserve_application',        'winserve_handle_application' );
