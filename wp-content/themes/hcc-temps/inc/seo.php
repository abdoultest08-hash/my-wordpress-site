<?php
/**
 * Search and sharing basics, since the site runs without an SEO plugin:
 * meta description, Open Graph and Twitter cards on every page, BlogPosting and Organization
 * structured data, and "noindex" on private pages (portals, login, registration).
 * If an SEO plugin is installed later (Yoast, All in One SEO, Rank Math), this steps aside.
 */

defined( 'ABSPATH' ) || exit;

function hcc_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'AIOSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' );
}

/** Templates that are private to logged-in people or part of a form flow. */
function hcc_seo_private() {
	foreach ( array( 'portal-login', 'apply', 'portal', 'staff' ) as $tpl ) {
		if ( is_page_template( 'templates/' . $tpl . '.php' ) ) {
			return true;
		}
	}
	return is_search() || is_404();
}

function hcc_seo_trim( $text, $len = 158 ) {
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( (string) $text ) ) ) );
	if ( mb_strlen( $text ) <= $len ) {
		return $text;
	}
	$cut = mb_substr( $text, 0, $len - 1 );
	return preg_replace( '/\s+\S*$/', '', $cut ) . '…';
}

/** The description for the current page. */
function hcc_seo_description() {
	$defaults = array(
		'register' => 'Register with HCC-Temps for flexible nursing, care, chef and hospitality shifts. See the documents and training each role needs. Most candidates are ready in 3 days.',
		'clients'  => 'Emergency staff cover within 2 hours, nationwide. Vetted nurses, carers, chefs and hospitality staff for care homes, hospitals, schools and hotels. 24-hour line.',
		'about'    => 'HCC-Temps is a care and hospitality staffing agency with offices in Leeds and London, supplying vetted staff across the UK, 24 hours a day.',
		'contact'  => 'Contact HCC-Temps: call 0330 321 3124, 24 hours a day, or send us a message. Offices in Leeds and London, covering the whole UK.',
		'privacy'  => 'How HCC-Temp Staffing Ltd collects, uses and protects your personal information under UK GDPR.',
		'terms'    => 'The terms for using the HCC-Temps website and registering with us as a candidate.',
	);
	if ( is_front_page() ) {
		return hcc_seo_trim( hcc_opt( 'hero_title' ) . '. ' . hcc_opt( 'hero_text' ) );
	}
	if ( is_singular( 'job_vacancies' ) ) {
		$j = hcc_job();
		return hcc_seo_trim( $j['title'] . ( $j['place'] ? ' in ' . $j['place'] : '' ) . ( $j['pay'] ? ', ' . $j['pay'] : '' ) . '. ' . get_post_field( 'post_content', get_queried_object_id() ) );
	}
	if ( is_singular() ) {
		$id = get_queried_object_id();
		foreach ( $defaults as $tpl => $d ) {
			if ( is_page_template( 'templates/' . $tpl . '.php' ) ) {
				return $d;
			}
		}
		$ex = get_post_field( 'post_excerpt', $id );
		return hcc_seo_trim( $ex ? $ex : get_post_field( 'post_content', $id ) );
	}
	if ( is_home() ) {
		return 'News and guides from HCC-Temps for nurses, carers, chefs and care home managers: training, DBS, right to work, staffing and more.';
	}
	if ( is_category() ) {
		$t = single_cat_title( '', false );
		return hcc_seo_trim( term_description() ? term_description() : 'HCC-Temps articles: ' . $t . '.' );
	}
	if ( is_post_type_archive( 'job_vacancies' ) || is_tax() ) {
		return 'Live nursing, care, chef, kitchen and hospitality jobs across the UK with HCC-Temps. See the pay and shift pattern up front and apply online.';
	}
	return get_bloginfo( 'description' );
}

function hcc_seo_image() {
	if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( get_queried_object_id() ), 'large' );
		if ( $src ) {
			return $src[0];
		}
	}
	return hcc_img( 'hero-nurse.jpg' );
}

add_action( 'wp_head', function () {
	if ( hcc_seo_plugin_active() ) {
		return;
	}
	if ( hcc_seo_private() ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
		return;
	}
	$desc  = hcc_seo_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
	$type  = is_singular( 'post' ) ? 'article' : 'website';
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:site_name" content="HCC-Temps">' . "\n";
	echo '<meta property="og:locale" content="en_GB">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:image" content="' . esc_url( hcc_seo_image() ) . '">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	if ( is_singular( 'post' ) ) {
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
	}
}, 2 );

/* Structured data: the organisation on the homepage, an article on each blog post. */
add_action( 'wp_head', function () {
	if ( hcc_seo_plugin_active() || hcc_seo_private() ) {
		return;
	}
	$org = array(
		'@type'     => 'EmploymentAgency',
		'@id'       => home_url( '/#organization' ),
		'name'      => hcc_opt( 'company' ) ? hcc_opt( 'company' ) : 'HCC-Temps',
		'url'       => home_url( '/' ),
		'logo'      => hcc_logo_url(),
		'telephone' => hcc_opt( 'phone' ),
		'email'     => hcc_opt( 'email' ),
		'address'   => array( '@type' => 'PostalAddress', 'streetAddress' => 'Crown House, 94 Armley Road, Armley', 'addressLocality' => 'Leeds', 'postalCode' => 'LS12 2EJ', 'addressCountry' => 'GB' ),
		'areaServed' => 'GB',
		'openingHoursSpecification' => array( '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ), 'opens' => '00:00', 'closes' => '23:59' ),
	);
	$data = null;
	if ( is_front_page() ) {
		$data = array( '@context' => 'https://schema.org' ) + $org;
	} elseif ( is_singular( 'post' ) ) {
		$id   = get_queried_object_id();
		$data = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'headline'         => get_the_title( $id ),
			'description'      => hcc_seo_description(),
			'image'            => hcc_seo_image(),
			'datePublished'    => get_the_date( 'c', $id ),
			'dateModified'     => get_the_modified_date( 'c', $id ),
			'author'           => array( '@type' => 'Organization', 'name' => 'HCC-Temps', 'url' => home_url( '/' ) ),
			'publisher'        => array( '@type' => 'Organization', 'name' => 'HCC-Temps', 'logo' => array( '@type' => 'ImageObject', 'url' => hcc_logo_url() ) ),
			'mainEntityOfPage' => get_permalink( $id ),
		);
	}
	if ( $data ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}, 3 );

/* Keep private pages out of the WordPress sitemap (wp-sitemap.xml). */
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $post_type ) {
	if ( 'page' === $post_type ) {
		$private = get_posts( array( 'post_type' => 'page', 'numberposts' => -1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_wp_page_template', 'value' => array( 'templates/portal-login.php', 'templates/apply.php', 'templates/portal.php', 'templates/staff.php' ), 'compare' => 'IN' ) ) ) );
		$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), $private );
	}
	return $args;
}, 10, 2 );
