<?php
/**
 * Demo content created when the theme is switched on.
 *
 * Pages are created only if no page uses that template yet. Sample jobs and
 * blog posts are created only on a site with no jobs, so the live site's real
 * vacancies are never touched. Everything created here is tagged _hcc_demo and
 * can be removed from Tools → HCC demo content.
 */

defined( 'ABSPATH' ) || exit;

function hcc_demo_jobs() {
	return array(
		array( 'Registered Staff Nurse (RGN)', 'Healthcare', 'Nurse', 'Solihull, B90', 'Temporary', '£24.00 – £26.50 per hour', 24, 'Days & nights', 'urgent', 'nurse.jpg' ),
		array( 'Care Home Chef', 'Chefs', 'Chef', 'Maryport, Cumbria', 'Temporary', '£14.00 – £15.50 per hour', 14, 'Weekdays', 'new', 'chef.jpg' ),
		array( 'Senior Care Assistant', 'Healthcare', 'Senior Care Assistant', 'Basingstoke, Hampshire', 'Temporary', '£14.20 – £15.00 per hour', 14.2, 'Long days', '', 'carer.jpg' ),
		array( 'School Kitchen Assistant', 'Hospitality', 'Kitchen Assistant', 'London', 'Temporary', '£12.75 – £13.50 per hour', 12.75, 'Term time', 'new', 'kitchen.jpg' ),
		array( 'Temp Barista', 'Hospitality', 'Barista', 'Central London', 'Temporary', '£12.60 – £13.20 per hour', 12.6, 'Flexible', '', 'barista.jpg' ),
		array( 'Temp Cleaner', 'Housekeeping & Cleaning', 'Cleaner', 'Catford, SE6', 'Temporary', '£12.21 – £12.75 per hour', 12.21, 'Early starts', 'urgent', 'cleaner.jpg' ),
		array( 'Hotel Housekeeper', 'Housekeeping & Cleaning', 'Housekeeping', 'Southampton', 'Temporary', '£12.40 – £13.00 per hour', 12.4, 'Weekends', '', 'housekeeping.jpg' ),
		array( 'Events Hospitality Assistant', 'Hospitality', 'Catering / Hospitality', 'Newcastle, NE20', 'Temporary', '£12.50 – £13.25 per hour', 12.5, 'Evenings', 'new', 'hospman.jpg' ),
		array( 'Care Home Receptionist', 'Admin & Reception', 'Admin Staff / Receptionist', 'Guildford, Surrey', 'Temporary to Permanent', '£12.80 – £13.40 per hour', 12.8, 'Weekdays', '', 'reception.jpg' ),
		array( 'Healthcare Assistant', 'Healthcare', 'Health Care Assistant', 'Portsmouth', 'Temporary', '£12.90 – £14.00 per hour', 12.9, 'Days & nights', 'urgent', 'hero.jpg' ),
		array( 'Temp Chef', 'Chefs', 'Chef', 'Truro, Cornwall', 'Temporary', '£14.50 – £16.00 per hour', 14.5, 'Weekends', '', 'chef.jpg' ),
		array( 'Kitchen Assistant', 'Hospitality', 'Kitchen Assistant', 'Portsmouth', 'Temporary', '£12.40 – £13.00 per hour', 12.4, 'Lunchtimes', '', 'kitchen.jpg' ),
		array( 'Healthcare Assistant', 'Healthcare', 'Health Care Assistant', 'Southampton', 'Temporary', '£12.90 – £13.80 per hour', 12.9, 'Nights', '', 'carer.jpg' ),
		array( 'Temp Cleaner', 'Housekeeping & Cleaning', 'Cleaner', 'Lymington, SO41', 'Temporary', '£12.21 – £12.60 per hour', 12.21, 'Mornings', '', 'cleaner.jpg' ),
	);
}

function hcc_demo_job_text( $title, $sector, $place ) {
	if ( 'Healthcare' === $sector ) {
		return "We are looking for a caring, reliable {$title} to join the team at a well-run home in {$place}. You will support residents with daily living and help them feel safe and respected.\n\n<h2>What you'll do</h2>\n<ul><li>Support residents with personal care, meals and mobility</li><li>Follow care plans and record care accurately</li><li>Spot changes in health and report them to the nurse in charge</li><li>Work with the permanent team on handover</li></ul>\n<h2>What you need</h2>\n<ul><li>Experience in care, or a Care Certificate</li><li>Enhanced DBS on the update service, or willing to apply</li><li>Right to work in the UK</li><li>Two references from recent work</li></ul>\n<h2>What you get</h2>\n<ul><li>Weekly pay for every hour you work</li><li>Choose the shifts that suit you</li><li>A named consultant you can call</li></ul>";
	}
	if ( 'Chefs' === $sector ) {
		return "A kitchen in {$place} needs an experienced chef to cook fresh, home-style meals, including texture-modified and special diets.\n\n<h2>What you'll do</h2>\n<ul><li>Cook breakfast, lunch and tea from set menus</li><li>Prepare soft, minced and puréed diets safely</li><li>Keep allergen and temperature records</li><li>Keep the kitchen clean and stock rotated</li></ul>\n<h2>What you need</h2>\n<ul><li>Previous chef experience</li><li>Food Hygiene Level 2 or above</li><li>Right to work in the UK</li></ul>\n<h2>What you get</h2>\n<ul><li>Weekly pay for every hour you work</li><li>Choose the shifts that suit you</li><li>A named consultant you can call</li></ul>";
	}
	return "We need a reliable {$title} for shifts in {$place}. Friendly team, clear instructions on day one, and regular hours if you want them.\n\n<h2>What you'll do</h2>\n<ul><li>Arrive on time and sign in with the supervisor</li><li>Follow the site's standards and safety rules</li><li>Work as part of a busy team</li></ul>\n<h2>What you need</h2>\n<ul><li>Some experience in a similar role is helpful</li><li>Right to work in the UK</li><li>Good spoken English</li></ul>\n<h2>What you get</h2>\n<ul><li>Weekly pay for every hour you work</li><li>Choose the shifts that suit you</li><li>A named consultant you can call</li></ul>";
}

function hcc_seed() {
	$pages = array(
		'register' => array( 'Register', 'register' ),
		'clients'  => array( 'For Clients', 'for-clients' ),
		'about'    => array( 'About Us', 'about-us' ),
		'contact'  => array( 'Contact', 'contact' ),
		'upload'   => array( 'Upload Documents', 'upload-documents' ),
	);
	foreach ( $pages as $tpl => $p ) {
		$exists = get_posts( array( 'post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'templates/' . $tpl . '.php', 'numberposts' => 1, 'post_status' => 'any' ) );
		if ( $exists ) {
			continue;
		}
		$by_slug = get_page_by_path( $p[1] );
		if ( $by_slug ) {
			update_post_meta( $by_slug->ID, '_wp_page_template', 'templates/' . $tpl . '.php' );
			continue;
		}
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $p[0], 'post_name' => $p[1] ) );
		update_post_meta( $id, '_wp_page_template', 'templates/' . $tpl . '.php' );
		update_post_meta( $id, '_hcc_demo', 1 );
	}

	$has_jobs = get_posts( array( 'post_type' => 'job_vacancies', 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids' ) );
	if ( ! $has_jobs ) {
		wp_insert_term( 'Live Jobs', 'job_status', array( 'slug' => 'live-jobs' ) );
		wp_insert_term( 'Expired Jobs', 'job_status', array( 'slug' => 'expired-jobs' ) );
		foreach ( array_reverse( hcc_demo_jobs() ) as $i => $j ) {
			$id = wp_insert_post( array(
				'post_type'    => 'job_vacancies',
				'post_status'  => 'publish',
				'post_title'   => $j[0],
				'post_content' => hcc_demo_job_text( $j[0], $j[1], $j[3] ),
				'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( 14 - $i ) * 6 * HOUR_IN_SECONDS ),
			) );
			wp_set_object_terms( $id, $j[1], 'job_sector' );
			wp_set_object_terms( $id, $j[2], 'job_type' );
			wp_set_object_terms( $id, $j[3], 'location' );
			wp_set_object_terms( $id, $j[4], 'contract' );
			wp_set_object_terms( $id, 'live-jobs', 'job_status' );
			update_post_meta( $id, '_hcc_pay', $j[5] );
			update_post_meta( $id, '_hcc_pay_min', $j[6] );
			update_post_meta( $id, '_hcc_shifts', $j[7] );
			update_post_meta( $id, '_hcc_badge', $j[8] );
			update_post_meta( $id, '_hcc_image', $j[9] );
			update_post_meta( $id, '_hcc_ref', 'HCC-J' . ( 1040 + $i ) );
			update_post_meta( $id, '_hcc_demo', 1 );
		}

		// A fresh install's "Hello world!" post would show on the homepage blog strip.
		$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $hello && $hello->post_date === $hello->post_modified ) {
			wp_trash_post( $hello->ID );
		}

		$posts = array(
			array( 'Do you need Food Hygiene Level 2 for kitchen work?', 'Hospitality', 'What the certificate covers, how long it takes and where to get it.' ),
			array( 'Your first agency shift in a care home: what to bring', 'Healthcare', 'ID, uniform, your PIN and the questions to ask when you arrive.' ),
			array( 'Planning winter cover before the rush', 'For clients', 'How care homes and hotels book temps for December without the stress.' ),
		);
		foreach ( $posts as $k => $p ) {
			$cat = wp_insert_term( $p[1], 'category' );
			$id  = wp_insert_post( array(
				'post_status'   => 'publish',
				'post_title'    => $p[0],
				'post_excerpt'  => $p[2],
				'post_content'  => '<p>' . $p[2] . '</p><p>Sample post for the demo. Replace with a real article.</p>',
				'post_category' => is_wp_error( $cat ) ? array( (int) $cat->get_error_data() ) : array( (int) $cat['term_id'] ),
				'post_date'     => gmdate( 'Y-m-d H:i:s', time() - ( 3 - $k ) * DAY_IN_SECONDS ),
			) );
			update_post_meta( $id, '_hcc_demo', 1 );
		}
	}
	update_option( 'hcc_seeded', HCC_VERSION );
	flush_rewrite_rules();
}

// Runs on the first request after switching, once init has registered the post types.
add_action( 'after_switch_theme', 'hcc_seed' );

/* ---------- Tools → HCC demo content ---------- */

add_action( 'admin_menu', function () {
	add_management_page( 'HCC demo content', 'HCC demo content', 'manage_options', 'hcc-demo', 'hcc_demo_page' );
} );

function hcc_demo_page() {
	$ids = get_posts( array( 'post_type' => array( 'job_vacancies', 'post' ), 'meta_key' => '_hcc_demo', 'numberposts' => -1, 'post_status' => 'any', 'fields' => 'ids' ) );
	echo '<div class="wrap"><h1>HCC demo content</h1>';
	if ( isset( $_GET['removed'] ) ) {
		echo '<div class="notice notice-success"><p>Demo jobs and posts removed.</p></div>';
	}
	echo '<p>The theme added ' . count( $ids ) . ' sample jobs and blog posts so the site looks complete in a demo. Remove them before going live. Pages (Register, Contact and so on) are kept.</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="hcc_remove_demo">';
	wp_nonce_field( 'hcc_remove_demo' );
	submit_button( 'Remove demo jobs and posts', 'delete', 'submit', false, $ids ? array() : array( 'disabled' => 'disabled' ) );
	echo '</form>';
	do_action( 'hcc_demo_page_after' );
	echo '</div>';
}

add_action( 'admin_post_hcc_remove_demo', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'hcc_remove_demo' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$ids = get_posts( array( 'post_type' => array( 'job_vacancies', 'post' ), 'meta_key' => '_hcc_demo', 'numberposts' => -1, 'post_status' => 'any', 'fields' => 'ids' ) );
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
	wp_safe_redirect( admin_url( 'tools.php?page=hcc-demo&removed=1' ) );
	exit;
} );
