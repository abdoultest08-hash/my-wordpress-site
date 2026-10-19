<?php
/**
 * Job Board: post type, taxonomies, job details box, filtering and display helpers.
 *
 * The live site already stores vacancies as `job_vacancies` with the taxonomies
 * job_sector, job_type, location, contract and job_status. We register the same
 * names only when nothing else has, so existing jobs keep working.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	if ( ! post_type_exists( 'job_vacancies' ) ) {
		register_post_type( 'job_vacancies', array(
			'labels'       => array(
				'name'          => 'Job Board',
				'singular_name' => 'Job',
				'add_new_item'  => 'Add new job',
				'edit_item'     => 'Edit job',
				'all_items'     => 'All jobs',
				'menu_name'     => 'Job Board',
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'job-vacancies', 'with_front' => false ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'    => 'dashicons-id-alt',
			'show_in_rest' => true,
		) );
	}
	$taxes = array(
		'job_sector' => array( 'Sectors', 'Sector', 'job-sector' ),
		'job_type'   => array( 'Job types', 'Job type', 'job-type' ),
		'location'   => array( 'Locations', 'Location', 'job-location' ),
		'contract'   => array( 'Contracts', 'Contract', 'contract' ),
		'job_status' => array( 'Job status', 'Job status', 'job-status' ),
	);
	foreach ( $taxes as $tax => $l ) {
		if ( ! taxonomy_exists( $tax ) ) {
			register_taxonomy( $tax, 'job_vacancies', array(
				'labels'            => array( 'name' => $l[0], 'singular_name' => $l[1] ),
				'hierarchical'      => true,
				'show_admin_column' => in_array( $tax, array( 'job_sector', 'location' ), true ),
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => $l[2] ),
			) );
		} else {
			register_taxonomy_for_object_type( $tax, 'job_vacancies' );
		}
	}
}, 20 );

/* ---------- Job details meta box ---------- */

function hcc_job_fields() {
	return array(
		'_hcc_pay'     => array( 'Pay (shown on the site)', 'text', 'e.g. £14.20 – £15.00 per hour' ),
		'_hcc_pay_min' => array( 'Lowest hourly rate (for sorting and Google Jobs)', 'number', 'e.g. 14.20' ),
		'_hcc_shifts'  => array( 'Shift pattern', 'text', 'e.g. Long days, nights' ),
		'_hcc_badge'   => array( 'Badge', 'select', array( '' => 'None', 'new' => 'New', 'urgent' => 'Urgent' ) ),
		'_hcc_closing' => array( 'Closing date (job hides itself after this date)', 'date', '' ),
		'_hcc_ref'     => array( 'Job reference', 'text', 'Left blank, one is created automatically' ),
	);
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'hcc_job', 'Job details', 'hcc_job_box', 'job_vacancies', 'normal', 'high' );
} );

function hcc_job_box( $post ) {
	wp_nonce_field( 'hcc_job_save', 'hcc_job_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( hcc_job_fields() as $key => $f ) {
		$val = get_post_meta( $post->ID, $key, true );
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
		if ( 'select' === $f[1] ) {
			echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
			foreach ( $f[2] as $k => $label ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( $val, $k, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
		} else {
			$step = 'number' === $f[1] ? ' step="0.01" min="0"' : '';
			echo '<input class="regular-text" type="' . esc_attr( $f[1] ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" placeholder="' . esc_attr( $f[2] ) . '"' . $step . '>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table><p class="description">Set the sector, job type, location and contract in the boxes on the right. Add a featured image for the job card, or a photo is picked to match the role.</p>';
}

add_action( 'save_post_job_vacancies', function ( $post_id ) {
	if ( ! isset( $_POST['hcc_job_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hcc_job_nonce'] ) ), 'hcc_job_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array_keys( hcc_job_fields() ) as $key ) {
		$val = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		if ( '_hcc_pay_min' === $key ) {
			$val = '' === $val ? '' : (string) round( (float) $val, 2 );
		}
		update_post_meta( $post_id, $key, $val );
	}
} );

/* ---------- Data helpers ---------- */

function hcc_first_term( $post_id, $tax ) {
	$terms = get_the_terms( $post_id, $tax );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
}

/** Photo for a job card: featured image, demo image, or one matched to the role. */
function hcc_job_image( $post_id, $role = '', $sector = '' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, 'medium_large' );
	}
	$demo = get_post_meta( $post_id, '_hcc_image', true );
	if ( $demo ) {
		return hcc_img( $demo );
	}
	$text = strtolower( $role . ' ' . $sector . ' ' . get_the_title( $post_id ) );
	$map  = array(
		'nurse' => 'nurse.jpg', 'senior' => 'carer.jpg', 'care' => 'carer.jpg', 'health' => 'hero.jpg', 'chef' => 'chef.jpg', 'cook' => 'chef.jpg',
		'kitchen' => 'kitchen.jpg', 'barista' => 'barista.jpg', 'housekeep' => 'housekeeping.jpg', 'clean' => 'cleaner.jpg',
		'recept' => 'reception.jpg', 'admin' => 'reception.jpg', 'catering' => 'hospman.jpg', 'hospitality' => 'hospman.jpg',
	);
	foreach ( $map as $needle => $file ) {
		if ( false !== strpos( $text, $needle ) ) {
			return hcc_img( $file );
		}
	}
	return hcc_img( 'hospman.jpg' );
}

function hcc_job( $post = null ) {
	$post   = get_post( $post );
	$id     = $post->ID;
	$sector = hcc_first_term( $id, 'job_sector' );
	$role   = hcc_first_term( $id, 'job_type' );
	$ref    = get_post_meta( $id, '_hcc_ref', true );
	return array(
		'id'       => $id,
		'title'    => get_the_title( $id ),
		'url'      => get_permalink( $id ),
		'sector'   => $sector,
		'role'     => $role ? $role : get_the_title( $id ),
		'place'    => hcc_first_term( $id, 'location' ),
		'contract' => hcc_first_term( $id, 'contract' ),
		'pay'      => get_post_meta( $id, '_hcc_pay', true ),
		'pay_min'  => (float) get_post_meta( $id, '_hcc_pay_min', true ),
		'shifts'   => get_post_meta( $id, '_hcc_shifts', true ),
		'badge'    => get_post_meta( $id, '_hcc_badge', true ),
		'closing'  => get_post_meta( $id, '_hcc_closing', true ),
		'ref'      => $ref ? $ref : 'HCC-J' . $id,
		'img'      => hcc_job_image( $id, $role, $sector ),
	);
}

function hcc_sector_class( $sector ) {
	$s = strtolower( $sector );
	if ( false !== strpos( $s, 'chef' ) ) {
		return 'chef';
	}
	return ( false !== strpos( $s, 'health' ) || false !== strpos( $s, 'admin' ) ) ? 'health' : 'hosp';
}

function hcc_job_card( $post = null, $extra = false ) {
	$j = hcc_job( $post );
	$tag = trim( $j['contract'] . ( $j['contract'] && $j['shifts'] ? ' · ' : '' ) . $j['shifts'] );
	?>
	<article class="job<?php echo $extra ? ' extra' : ''; ?>">
		<a class="job-media" href="<?php echo esc_url( $j['url'] ); ?>" tabindex="-1" aria-hidden="true">
			<img src="<?php echo esc_url( $j['img'] ); ?>" alt="" width="720" height="540" loading="lazy" decoding="async">
			<?php if ( $j['badge'] ) : ?><span class="badge <?php echo esc_attr( $j['badge'] ); ?>"><?php echo 'urgent' === $j['badge'] ? 'Urgent' : 'New'; ?></span><?php endif; ?>
		</a>
		<div class="job-body">
			<?php if ( $j['sector'] ) : ?><span class="sector <?php echo esc_attr( hcc_sector_class( $j['sector'] ) ); ?>"><?php echo esc_html( $j['sector'] ); ?></span><?php endif; ?>
			<h3><a href="<?php echo esc_url( $j['url'] ); ?>"><?php echo esc_html( $j['title'] ); ?></a></h3>
			<div class="meta">
				<?php if ( $j['place'] ) : ?><div><?php echo hcc_icon( 'pin' ); echo esc_html( $j['place'] ); ?></div><?php endif; ?>
				<?php if ( $j['pay'] ) : ?><div class="pay"><?php echo hcc_icon( 'pay' ); echo esc_html( $j['pay'] ); ?></div><?php endif; ?>
			</div>
			<div class="job-foot">
				<span class="tag"><?php echo esc_html( $tag ); ?></span>
				<a class="apply-link" href="<?php echo esc_url( $j['url'] ); ?>">View &amp; apply <span aria-hidden="true">→</span></a>
			</div>
		</div>
	</article>
	<?php
}

/* ---------- Hide expired jobs, apply Job Board filters ---------- */

function hcc_live_jobs_meta_query() {
	return array(
		'relation' => 'OR',
		array( 'key' => '_hcc_closing', 'compare' => 'NOT EXISTS' ),
		array( 'key' => '_hcc_closing', 'value' => '', 'compare' => '=' ),
		array( 'key' => '_hcc_closing', 'value' => current_time( 'Y-m-d' ), 'compare' => '>=', 'type' => 'DATE' ),
	);
}

function hcc_expired_status_tax_query() {
	$expired = get_term_by( 'slug', 'expired-jobs', 'job_status' );
	return $expired ? array( array( 'taxonomy' => 'job_status', 'field' => 'term_id', 'terms' => array( $expired->term_id ), 'operator' => 'NOT IN' ) ) : array();
}

function hcc_is_job_listing( $q ) {
	return $q->is_post_type_archive( 'job_vacancies' ) || $q->is_tax( array( 'job_sector', 'job_type', 'location', 'contract' ) );
}

add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! hcc_is_job_listing( $q ) ) {
		return;
	}
	$q->set( 'post_type', 'job_vacancies' );
	$q->set( 'posts_per_page', 12 );
	$q->set( 'meta_query', array( hcc_live_jobs_meta_query() ) );

	$tax = hcc_expired_status_tax_query();
	foreach ( array( 'sector' => 'job_sector', 'role' => 'job_type', 'contract' => 'contract', 'where_term' => 'location' ) as $param => $taxonomy ) {
		if ( empty( $_GET[ $param ] ) ) {
			continue;
		}
		$vals  = array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $param ] ) );
		$tax[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $vals );
	}
	if ( $tax ) {
		$tax['relation'] = 'AND';
		$q->set( 'tax_query', $tax );
	}
	if ( ! empty( $_GET['kw'] ) ) {
		$q->set( 's', sanitize_text_field( wp_unslash( $_GET['kw'] ) ) );
	}
	if ( ! empty( $_GET['where'] ) ) {
		$where = sanitize_text_field( wp_unslash( $_GET['where'] ) );
		$terms = get_terms( array( 'taxonomy' => 'location', 'name__like' => $where, 'fields' => 'ids', 'hide_empty' => false ) );
		$tq    = (array) $q->get( 'tax_query' );
		$tq[]  = array( 'taxonomy' => 'location', 'field' => 'term_id', 'terms' => $terms ? $terms : array( 0 ) );
		$tq['relation'] = 'AND';
		$q->set( 'tax_query', $tq );
	}
	$sort = isset( $_GET['sort'] ) ? sanitize_key( $_GET['sort'] ) : 'new';
	if ( 'pay' === $sort ) {
		$mq   = (array) $q->get( 'meta_query' );
		$mq[] = array( 'relation' => 'OR', 'pay_clause' => array( 'key' => '_hcc_pay_min', 'type' => 'DECIMAL(10,2)', 'compare' => 'EXISTS' ), array( 'key' => '_hcc_pay_min', 'compare' => 'NOT EXISTS' ) );
		$q->set( 'meta_query', $mq );
		$q->set( 'orderby', array( 'pay_clause' => 'DESC', 'date' => 'DESC' ) );
	} elseif ( 'az' === $sort ) {
		$q->set( 'orderby', 'title' );
		$q->set( 'order', 'ASC' );
	}
} );

/** Live jobs for the homepage and "similar jobs". */
function hcc_live_jobs( $args = array() ) {
	return new WP_Query( array_merge( array(
		'post_type'           => 'job_vacancies',
		'posts_per_page'      => 9,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_query'          => array( hcc_live_jobs_meta_query() ),
		'tax_query'           => hcc_expired_status_tax_query(),
	), $args ) );
}

function hcc_live_job_count() {
	$q = hcc_live_jobs( array( 'posts_per_page' => 1, 'no_found_rows' => false, 'fields' => 'ids' ) );
	return (int) $q->found_posts;
}

/** Terms that have at least one job, for the filter sidebar. */
function hcc_filter_terms( $tax ) {
	$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/* ---------- Google Jobs structured data ---------- */

add_action( 'wp_head', function () {
	if ( ! is_singular( 'job_vacancies' ) ) {
		return;
	}
	$j    = hcc_job();
	$type = strtolower( $j['contract'] );
	$data = array(
		'@context'           => 'https://schema.org',
		'@type'              => 'JobPosting',
		'title'              => $j['title'],
		'description'        => wp_kses_post( wpautop( get_post_field( 'post_content', $j['id'] ) ) ),
		'identifier'         => array( '@type' => 'PropertyValue', 'name' => 'HCC-Temps', 'value' => $j['ref'] ),
		'datePosted'         => get_the_date( 'c', $j['id'] ),
		'employmentType'     => false !== strpos( $type, 'perm' ) ? array( 'TEMPORARY', 'FULL_TIME' ) : ( false !== strpos( $type, 'full' ) ? 'FULL_TIME' : ( false !== strpos( $type, 'part' ) ? 'PART_TIME' : 'TEMPORARY' ) ),
		'hiringOrganization' => array( '@type' => 'Organization', 'name' => 'HCC-Temps', 'sameAs' => home_url( '/' ), 'logo' => hcc_logo_url() ),
		'jobLocation'        => array( '@type' => 'Place', 'address' => array( '@type' => 'PostalAddress', 'addressLocality' => $j['place'], 'addressCountry' => 'GB' ) ),
		'directApply'        => true,
	);
	if ( $j['closing'] ) {
		$data['validThrough'] = $j['closing'] . 'T23:59:59';
	}
	if ( $j['pay_min'] > 0 ) {
		$data['baseSalary'] = array( '@type' => 'MonetaryAmount', 'currency' => 'GBP', 'value' => array( '@type' => 'QuantitativeValue', 'value' => $j['pay_min'], 'unitText' => 'HOUR' ) );
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
} );
