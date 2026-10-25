<?php
/**
 * Theme setup, assets, Customizer options and small helpers.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 144, 'width' => 266, 'flex-height' => true, 'flex-width' => true ) );
	register_nav_menus( array(
		'primary' => __( 'Main menu', 'hcc' ),
		'footer'  => __( 'Footer links', 'hcc' ),
	) );
} );

/** Asset version from file modification time, so uploads always bust the browser cache. */
function hcc_asset_ver( $rel ) {
	$f = HCC_DIR . $rel;
	return file_exists( $f ) ? (string) filemtime( $f ) : HCC_VERSION;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'hcc-fonts', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Figtree:wght@400;500;600;700&display=swap', array(), null );
	wp_enqueue_style( 'hcc-main', HCC_URI . '/assets/css/main.css', array(), hcc_asset_ver( '/assets/css/main.css' ) );
	wp_enqueue_script( 'hcc-main', HCC_URI . '/assets/js/main.js', array(), hcc_asset_ver( '/assets/js/main.js' ), true );
} );

add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	if ( is_front_page() ) {
		echo '<link rel="preload" as="image" href="' . esc_url( hcc_hero_image() ) . '">' . "\n";
	}
}, 1 );

/* ---------- Options (Appearance → Customize → HCC-Temps) ---------- */

function hcc_defaults() {
	return array(
		'slogan'        => 'Temp made easy',
		'since'         => 'Open 24 hours · Nationwide cover',
		'phone'         => '0330 321 3124',
		'email'         => 'hello@hcctemps.co.uk',
		'company'       => 'HCC-Temp Staffing Ltd',
		'company_no'    => '11486985',
		'address'       => 'Crown House, 94 Armley Road, Armley, Leeds LS12 2EJ',
		'address2'      => 'Also in London · Covering the whole UK',
		'email_jobs'    => get_option( 'admin_email' ),
		'hero_title'    => 'Temp made easy for care and hospitality',
		'hero_text'     => 'Registered nurses, care assistants and chefs, nationwide and 24 hours a day. Find your next shift, or get cover for yours.',
		'hero_image'    => '',
		'stat1_num'     => '10,000+',
		'stat1_label'   => 'Shifts covered',
		'stat2_num'     => '2 hrs',
		'stat2_label'   => 'Emergency cover, nationwide',
		'stat3_num'     => '3 days',
		'stat3_label'   => 'Registration to ready to work',
		'stat4_num'     => '24/7',
		'stat4_label'   => 'Phone line, always open',
		'trustpilot'    => 'https://uk.trustpilot.com/review/healthcarecateringtemps.com',
		'social_x'      => '',
		'social_linkedin'  => '',
		'social_instagram' => '',
		'social_facebook'  => '',
		'turnstile_site'   => '',
		'turnstile_secret' => '',
	);
}

function hcc_opt( $key ) {
	$d = hcc_defaults();
	$v = get_theme_mod( 'hcc_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
	return ( '' === $v && isset( $d[ $key ] ) ) ? $d[ $key ] : $v;
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_panel( 'hcc', array( 'title' => 'HCC-Temps', 'priority' => 20 ) );
	$sections = array(
		'hcc_contact' => array( 'Contact details and emails', array(
			'slogan' => 'Slogan (top bar)', 'since' => 'Top bar strapline', 'phone' => 'Phone number', 'email' => 'Public email',
			'company' => 'Company name (footer)', 'company_no' => 'Company number', 'address' => 'Head office address', 'address2' => 'Second line under the address',
			'email_jobs' => 'Staffing inbox: registrations, uploads, cover requests and contact form',
		) ),
		'hcc_home' => array( 'Homepage', array(
			'hero_title' => 'Hero heading', 'hero_text' => 'Hero text',
			'stat1_num' => 'Figure 1 (rolls like a slot machine)', 'stat1_label' => 'Figure 1 label', 'stat2_num' => 'Figure 2', 'stat2_label' => 'Figure 2 label',
			'stat3_num' => 'Figure 3', 'stat3_label' => 'Figure 3 label', 'stat4_num' => 'Figure 4', 'stat4_label' => 'Figure 4 label',
			'trustpilot' => 'Trustpilot page URL',
		) ),
		'hcc_social' => array( 'Social links', array(
			'social_x' => 'X (Twitter) URL', 'social_linkedin' => 'LinkedIn URL', 'social_instagram' => 'Instagram URL', 'social_facebook' => 'Facebook URL',
		) ),
		'hcc_security' => array( 'Robot check (Cloudflare Turnstile)', array(
			'turnstile_site' => 'Turnstile site key', 'turnstile_secret' => 'Turnstile secret key',
		) ),
	);
	$d = hcc_defaults();
	foreach ( $sections as $id => $sec ) {
		$wp_customize->add_section( $id, array( 'title' => $sec[0], 'panel' => 'hcc' ) );
		foreach ( $sec[1] as $key => $label ) {
			$is_email = 0 === strpos( $key, 'email' );
			$is_url   = 0 === strpos( $key, 'social' ) || 'trustpilot' === $key;
			$wp_customize->add_setting( 'hcc_' . $key, array(
				'default'           => $d[ $key ],
				'sanitize_callback' => $is_email ? 'sanitize_email' : ( $is_url ? 'esc_url_raw' : 'sanitize_text_field' ),
			) );
			$wp_customize->add_control( 'hcc_' . $key, array(
				'label'   => $label,
				'section' => $id,
				'type'    => 'hero_text' === $key ? 'textarea' : ( $is_email ? 'email' : ( $is_url ? 'url' : 'text' ) ),
			) );
		}
	}
	$wp_customize->add_setting( 'hcc_hero_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hcc_hero_image', array( 'label' => 'Hero photo', 'section' => 'hcc_home' ) ) );
} );

/* ---------- Helpers ---------- */

function hcc_img( $file ) {
	return HCC_URI . '/assets/img/' . $file;
}

function hcc_hero_image() {
	$custom = hcc_opt( 'hero_image' );
	return $custom ? $custom : hcc_img( 'hero-nurse.jpg' );
}

function hcc_logo_url() {
	$id = get_theme_mod( 'custom_logo' );
	$src = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
	return $src ? $src : hcc_img( 'logo.png' );
}

/** Find the page using one of our templates, so links survive slug changes. */
function hcc_page_url( $template, $fallback = '/' ) {
	static $cache = array();
	if ( ! isset( $cache[ $template ] ) ) {
		$pages = get_posts( array(
			'post_type'   => 'page',
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'templates/' . $template . '.php',
			'numberposts' => 1,
			'fields'      => 'ids',
		) );
		$cache[ $template ] = $pages ? get_permalink( $pages[0] ) : home_url( $fallback );
	}
	return $cache[ $template ];
}

function hcc_jobs_url() {
	$url = get_post_type_archive_link( 'job_vacancies' );
	return $url ? $url : home_url( '/job-vacancies/' );
}

function hcc_tel( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );
}

/** Inline SVG icons used across templates. */
function hcc_icon( $name, $size = 16 ) {
	$paths = array(
		'pin'    => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'pay'    => '<path d="M16 6.5A4 4 0 0 0 9 9v3m0 0v6h8M6 12h8"/>',
		'phone'  => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
		'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'left'   => '<path d="m15 18-6-6 6-6"/>',
		'right'  => '<path d="m9 18 6-6-6-6"/>',
		'check'  => '<path d="m5 12 5 5 9-10"/>',
		'doc'    => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/>',
		'lock'   => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
	);
	if ( 'star' === $name ) {
		return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 3 6.6 7.2.8-5.4 4.9 1.5 7.1L12 17.8 5.7 21.4l1.5-7.1L1.8 9.4 9 8.6z"/></svg>';
	}
	$sw = in_array( $name, array( 'left', 'right', 'check' ), true ) ? '2.6' : '2.2';
	return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $sw . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( isset( $paths[ $name ] ) ? $paths[ $name ] : '' ) . '</svg>';
}

function hcc_stars() {
	return '<div class="stars" aria-label="5 out of 5 stars">' . str_repeat( hcc_icon( 'star', 22 ), 5 ) . '</div>';
}

/** Fallback main menu when none is assigned under Appearance → Menus. */
function hcc_menu_fallback( $mobile = false ) {
	$items = array(
		array( hcc_jobs_url(), 'Find jobs', is_post_type_archive( 'job_vacancies' ) || is_singular( 'job_vacancies' ) ),
		array( hcc_page_url( 'register', '/register/' ), 'For candidates', is_page_template( 'templates/register.php' ) ),
		array( hcc_page_url( 'clients', '/for-clients/' ), 'For clients', is_page_template( 'templates/clients.php' ) ),
		array( hcc_page_url( 'about', '/about-us/' ), 'About us', is_page_template( 'templates/about.php' ) ),
		array( hcc_page_url( 'contact', '/contact/' ), 'Contact', is_page_template( 'templates/contact.php' ) ),
	);
	foreach ( $items as $i ) {
		if ( 'Contact' === $i[1] ) {
			hcc_login_menu( $mobile );
		}
		printf( '<a href="%s"%s>%s</a>', esc_url( $i[0] ), $i[2] ? ' aria-current="page"' : '', esc_html( $i[1] ) );
	}
}

/**
 * "Log in" menu item with Candidate and Staff options (a dropdown on desktop,
 * two plain links in the mobile menu). Logged-in users see their portal instead.
 */
function hcc_login_menu( $mobile = false ) {
	if ( ! function_exists( 'hcc_login_url' ) ) {
		return;
	}
	if ( is_user_logged_in() && ( hcc_is_staff() || hcc_applicant_for_user( get_current_user_id() ) ) ) {
		$url = hcc_is_staff() ? hcc_staff_url() : hcc_portal_url();
		printf( '<a href="%s">%s</a>', esc_url( $url ), hcc_is_staff() ? 'Staff portal' : 'My portal' );
		if ( $mobile ) {
			printf( '<a href="%s">Log out</a>', esc_url( wp_logout_url( home_url( '/' ) ) ) );
		}
		return;
	}
	$cand  = hcc_login_url();
	$staff = hcc_login_url( true );
	if ( $mobile ) {
		printf( '<a href="%s">Candidate login</a><a href="%s">Staff login</a>', esc_url( $cand ), esc_url( $staff ) );
		return;
	}
	printf(
		'<details class="nav-login"><summary%3$s>Log in</summary><div class="nav-login-menu"><a href="%1$s"><b>Candidate login</b><small>Your documents and progress</small></a><a href="%2$s"><b>Staff login</b><small>Staff and admin portal</small></a></div></details>',
		esc_url( $cand ),
		esc_url( $staff ),
		is_page_template( 'templates/portal-login.php' ) ? ' aria-current="page"' : ''
	);
}

/** Render the primary menu as plain links (keeps the header markup flat). */
function hcc_menu( $mobile = false ) {
	if ( has_nav_menu( 'primary' ) ) {
		$locations = get_nav_menu_locations();
		$items     = wp_get_nav_menu_items( $locations['primary'] );
		foreach ( (array) $items as $item ) {
			if ( $item->menu_item_parent ) {
				continue;
			}
			$current = untrailingslashit( $item->url ) === untrailingslashit( home_url( add_query_arg( array() ) ) );
			printf( '<a href="%s"%s>%s</a>', esc_url( $item->url ), $current ? ' aria-current="page"' : '', esc_html( $item->title ) );
		}
		hcc_login_menu( $mobile );
		return;
	}
	hcc_menu_fallback( $mobile );
}


/** Draft FAQs, filterable so answers can be changed without editing templates. */
function hcc_faqs( $set ) {
	$faqs = array(
		'candidates' => array(
			array( 'How do I register?', 'Choose your role on the For candidates page and press Start registration. Create your account, confirm your email with the code we send, answer a few questions and upload your documents in your portal.' ),
			array( 'What documents will I need?', 'It depends on the role. Each role on the For candidates page and every job advert lists exactly what you need, including training certificates. Missing something? You can still register and upload what you have.' ),
			array( "I don't have a DBS. Can I still register?", 'Yes. Register as normal and choose "No" when we ask about your DBS. We will apply for an enhanced DBS for you.' ),
			array( 'How quickly can I start work?', 'Most candidates are ready to work within 3 days of registering, once all documents are uploaded and checked.' ),
			array( 'Can I choose my own shifts?', "Yes. Tell us when you're available and we offer you shifts that fit. You can say no to any shift." ),
			array( 'How and when do I get paid?', 'Draft: you are paid weekly for the hours on your approved timesheets. HCC to confirm pay day and method.' ),
		),
		'employers' => array(
			array( 'How quickly can you send staff?', 'For emergencies we cover shifts within 2 hours, anywhere in the UK. Our line is open 24 hours: call ' . hcc_opt( 'phone' ) . '.' ),
			array( 'Which roles can you cover?', 'Registered nurses, healthcare assistants, senior carers, chefs, kitchen and catering assistants, baristas, domestic assistants and administrative staff.' ),
			array( 'Are your staff vetted?', 'Yes. We check right to work, ID, enhanced DBS, references and role-specific training before anyone is placed with you.' ),
			array( 'Where do you supply staff?', 'Everywhere in the UK. Our offices are in Leeds and London and we cover nationwide.' ),
			array( 'Can I book the same temp each week?', 'Yes. Many clients ask for the same people so residents and guests see familiar faces. We do our best to keep them with you.' ),
			array( 'How much does it cost?', 'Draft: rates depend on the role, location and shift times. Request cover and we send you a quote. HCC to confirm wording.' ),
		),
		'clients' => array(
			array( 'How quickly can you send staff?', 'For emergencies we cover shifts within 2 hours, anywhere in the UK. Call ' . hcc_opt( 'phone' ) . ', 24 hours a day.' ),
			array( 'What checks do your staff have?', "Right to work, photo ID, enhanced DBS, two references and role-specific training. Nurses' NMC PINs are checked on the NMC register." ),
			array( 'Can we have the same staff each week?', 'Yes. Ask for named temps and we will keep them with you wherever we can.' ),
			array( 'Do you offer temp to perm?', "Yes. If you'd like to keep someone permanently, talk to us about the options." ),
			array( 'How are timesheets and invoices handled?', 'Draft: you approve timesheets weekly and we invoice you for the hours worked. HCC to confirm process.' ),
		),
		'contact' => array(
			array( 'What are your opening hours?', 'We are open 24 hours a day, 7 days a week. Call ' . hcc_opt( 'phone' ) . ' any time, including out of hours.' ),
			array( "I've registered. When will I hear back?", 'Usually within 1 working day. Uploading all your documents in your portal speeds things up.' ),
			array( 'I forgot my password', 'Use "Forgotten your password?" on the Log in page and we will email you a link.' ),
			array( 'Can I visit the office?', 'Yes, by appointment. Call us first so the right person is there to meet you.' ),
			array( 'How do I make a complaint?', 'Email us with "Complaint" in the subject. A manager will reply within five working days.' ),
		),
	);
	return apply_filters( 'hcc_faqs', isset( $faqs[ $set ] ) ? $faqs[ $set ] : array(), $set );
}
