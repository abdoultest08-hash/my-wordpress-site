<?php
/**
 * Tools → Import jobs. Upload a CSV, check the preview, then import.
 * A row with a Ref that already exists updates that job; otherwise a new job is created.
 * Sectors, locations, job types and contracts are created if they don't exist.
 */

defined( 'ABSPATH' ) || exit;

/** CSV columns: header => [ help text, example ]. */
function hcc_import_columns() {
	return array(
		'ref'         => array( 'Job reference. Matches an existing job to update it. Leave blank for new jobs.', 'HCC-1001' ),
		'title'       => array( 'Job title (required)', 'Registered Nurse' ),
		'sector'      => array( 'Healthcare, Chefs, Hospitality…', 'Healthcare' ),
		'job_type'    => array( 'e.g. Nursing, Care, Kitchen', 'Nursing' ),
		'location'    => array( 'Town or city', 'Leeds' ),
		'contract'    => array( 'Temporary, Permanent, Temp to perm', 'Temporary' ),
		'pay'         => array( 'Pay as shown on the site', '£25.00 – £28.00 per hour' ),
		'pay_min'     => array( 'Lowest hourly rate, number only', '25' ),
		'shifts'      => array( 'Shift pattern', 'Long days, nights' ),
		'badge'       => array( 'new, urgent or blank', 'urgent' ),
		'closing'     => array( 'Closing date YYYY-MM-DD or DD/MM/YYYY', '2026-12-31' ),
		'status'      => array( 'live or expired (blank = live)', 'live' ),
		'slug'        => array( 'Web address of the advert (optional). Keeps links from the old website, Google and job boards working, and matches jobs already on the site so they are updated, not duplicated.', 'registered-nurse-leeds' ),
		'description' => array( 'Job description. Plain text or HTML. Blank lines start new paragraphs.', 'Nights in a nursing home…' ),
	);
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=job_vacancies', 'Import jobs', 'Import jobs (CSV)', 'edit_posts', 'hcc-import', 'hcc_import_page' );
	add_management_page( 'Import jobs', 'Import jobs (CSV)', 'edit_posts', 'hcc-import-tools', function () {
		wp_safe_redirect( admin_url( 'edit.php?post_type=job_vacancies&page=hcc-import' ) );
		exit;
	} );
} );

/** Template download. */
add_action( 'admin_post_hcc_import_template', function () {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'Not allowed.' );
	}
	$cols = hcc_import_columns();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="hcc-jobs-template.csv"' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" );
	fputcsv( $out, array_keys( $cols ) );
	fputcsv( $out, wp_list_pluck( $cols, 1 ) );
	fclose( $out );
	exit;
} );

/** Read an uploaded CSV into rows keyed by column name. */
function hcc_import_read( $path ) {
	$fh = fopen( $path, 'r' );
	if ( ! $fh ) {
		return new WP_Error( 'read', 'The file could not be read.' );
	}
	$head = fgetcsv( $fh );
	if ( ! $head ) {
		return new WP_Error( 'empty', 'The file is empty.' );
	}
	$head = array_map( function ( $h ) {
		return strtolower( trim( preg_replace( '/^\xEF\xBB\xBF/', '', $h ) ) );
	}, $head );
	if ( ! in_array( 'title', $head, true ) ) {
		return new WP_Error( 'cols', 'The first row must contain the column names, including "title". Download the template to see the format.' );
	}
	$rows = array();
	while ( ( $line = fgetcsv( $fh ) ) !== false ) {
		if ( ! array_filter( $line, 'strlen' ) ) {
			continue;
		}
		$row = array();
		foreach ( hcc_import_columns() as $k => $c ) {
			$i         = array_search( $k, $head, true );
			$row[ $k ] = false === $i || ! isset( $line[ $i ] ) ? '' : trim( $line[ $i ] );
		}
		$rows[] = $row;
	}
	fclose( $fh );
	return $rows;
}

function hcc_import_date( $s ) {
	if ( '' === $s ) {
		return '';
	}
	if ( preg_match( '#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $s, $m ) ) {
		return sprintf( '%04d-%02d-%02d', $m[3], $m[2], $m[1] );
	}
	$t = strtotime( $s );
	return $t ? gmdate( 'Y-m-d', $t ) : '';
}

/**
 * The existing job for a row: first by job reference, then by web address (slug) for adverts that were
 * already on the site before the importer was used (they have no reference yet), so they are updated
 * in place rather than duplicated.
 */
function hcc_import_find( $ref, $slug = '' ) {
	if ( '' !== $ref ) {
		$ids = get_posts( array( 'post_type' => 'job_vacancies', 'post_status' => 'any', 'meta_key' => '_hcc_ref', 'meta_value' => $ref, 'numberposts' => 1, 'fields' => 'ids' ) );
		if ( $ids ) {
			return (int) $ids[0];
		}
	}
	$slug = sanitize_title( $slug );
	if ( '' !== $slug ) {
		$ids = get_posts( array( 'post_type' => 'job_vacancies', 'post_status' => 'any', 'name' => $slug, 'numberposts' => 1, 'fields' => 'ids' ) );
		if ( $ids && ( '' === $ref || ! get_post_meta( $ids[0], '_hcc_ref', true ) ) ) {
			return (int) $ids[0];
		}
	}
	return 0;
}

/** Problems with a row, or an empty array. */
function hcc_import_check( $r ) {
	$e = array();
	if ( '' === $r['title'] ) {
		$e[] = 'No title';
	}
	if ( '' !== $r['closing'] && '' === hcc_import_date( $r['closing'] ) ) {
		$e[] = 'Closing date not understood';
	}
	if ( '' !== $r['badge'] && ! in_array( strtolower( $r['badge'] ), array( 'new', 'urgent' ), true ) ) {
		$e[] = 'Badge must be new, urgent or blank';
	}
	return $e;
}

function hcc_import_row( $r ) {
	$id   = hcc_import_find( $r['ref'], $r['slug'] );
	$desc = $r['description'];
	if ( $desc && false === strpos( $desc, '<' ) ) {
		$desc = wpautop( $desc );
	}
	$post = array( 'post_type' => 'job_vacancies', 'post_status' => 'publish', 'post_title' => $r['title'] );
	if ( '' !== $r['slug'] ) {
		$post['post_name'] = sanitize_title( $r['slug'] ); // Same web address as the old advert.
	}
	if ( '' !== $desc || ! $id ) {
		$post['post_content'] = wp_kses_post( $desc );
	}
	if ( $id ) {
		$post['ID'] = $id;
		wp_update_post( $post );
	} else {
		$id = wp_insert_post( $post );
	}
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	$meta = array(
		'_hcc_pay'     => $r['pay'],
		'_hcc_pay_min' => '' === $r['pay_min'] ? '' : (string) round( (float) preg_replace( '/[^0-9.]/', '', $r['pay_min'] ), 2 ),
		'_hcc_shifts'  => $r['shifts'],
		'_hcc_badge'   => strtolower( $r['badge'] ),
		'_hcc_closing' => hcc_import_date( $r['closing'] ),
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, sanitize_text_field( $v ) );
	}
	if ( '' !== $r['ref'] ) {
		update_post_meta( $id, '_hcc_ref', sanitize_text_field( $r['ref'] ) );
	}
	foreach ( array( 'sector' => 'job_sector', 'job_type' => 'job_type', 'location' => 'location', 'contract' => 'contract' ) as $col => $tax ) {
		if ( '' !== $r[ $col ] ) {
			wp_set_object_terms( $id, array_map( 'trim', explode( '|', $r[ $col ] ) ), $tax );
		}
	}
	$status = 'expired' === strtolower( $r['status'] ) ? 'expired-jobs' : 'live-jobs';
	if ( ! term_exists( $status, 'job_status' ) ) {
		wp_insert_term( 'expired-jobs' === $status ? 'Expired Jobs' : 'Live Jobs', 'job_status', array( 'slug' => $status ) );
	}
	wp_set_object_terms( $id, $status, 'job_status' );
	return $id;
}

/** Step 1 upload → preview (rows kept for 1 hour), step 2 import. */
add_action( 'admin_post_hcc_import', function () {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'hcc_import' );
	$back = admin_url( 'edit.php?post_type=job_vacancies&page=hcc-import' );
	$key  = 'hcc_import_' . get_current_user_id();
	if ( isset( $_POST['go'] ) ) {
		$rows = get_transient( $key );
		if ( ! $rows ) {
			wp_safe_redirect( add_query_arg( 'err', rawurlencode( 'The preview expired. Please upload the file again.' ), $back ) );
			exit;
		}
		$made = 0;
		$upd  = 0;
		foreach ( $rows as $r ) {
			if ( hcc_import_check( $r ) ) {
				continue;
			}
			$had = hcc_import_find( $r['ref'], $r['slug'] );
			if ( hcc_import_row( $r ) ) {
				$had ? $upd++ : $made++;
			}
		}
		delete_transient( $key );
		wp_safe_redirect( add_query_arg( array( 'done' => $made, 'upd' => $upd ), $back ) );
		exit;
	}
	if ( empty( $_FILES['csv']['tmp_name'] ) || ! is_uploaded_file( $_FILES['csv']['tmp_name'] ) ) {
		wp_safe_redirect( add_query_arg( 'err', rawurlencode( 'Choose a CSV file first.' ), $back ) );
		exit;
	}
	$rows = hcc_import_read( $_FILES['csv']['tmp_name'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( is_wp_error( $rows ) ) {
		wp_safe_redirect( add_query_arg( 'err', rawurlencode( $rows->get_error_message() ), $back ) );
		exit;
	}
	set_transient( $key, $rows, HOUR_IN_SECONDS );
	wp_safe_redirect( add_query_arg( 'preview', 1, $back ) );
	exit;
} );

function hcc_import_page() {
	$rows = isset( $_GET['preview'] ) ? get_transient( 'hcc_import_' . get_current_user_id() ) : false; // phpcs:ignore WordPress.Security.NonceVerification
	echo '<div class="wrap"><h1>Import jobs from a spreadsheet</h1>';
	if ( isset( $_GET['err'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-error"><p>' . esc_html( wp_unslash( $_GET['err'] ) ) . '</p></div>'; // phpcs:ignore
	}
	if ( isset( $_GET['done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		printf( '<div class="notice notice-success"><p>Done: %d new jobs created, %d jobs updated. <a href="%s">See all jobs</a></p></div>', (int) $_GET['done'], (int) ( $_GET['upd'] ?? 0 ), esc_url( admin_url( 'edit.php?post_type=job_vacancies' ) ) ); // phpcs:ignore
	}
	if ( $rows ) {
		$ok = 0;
		echo '<h2>Check before importing</h2><table class="widefat striped"><thead><tr><th>#</th><th>Action</th><th>Ref</th><th>Title</th><th>Sector</th><th>Location</th><th>Pay</th><th>Closing</th><th>Status</th><th>Problems</th></tr></thead><tbody>';
		foreach ( $rows as $i => $r ) {
			$err = hcc_import_check( $r );
			$ok += $err ? 0 : 1;
			$act = $err ? 'Skip' : ( hcc_import_find( $r['ref'], $r['slug'] ) ? 'Update' : 'New' );
			printf( '<tr><td>%d</td><td><b>%s</b></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td style="color:#b32d2e">%s</td></tr>', $i + 2, esc_html( $act ), esc_html( $r['ref'] ), esc_html( $r['title'] ), esc_html( $r['sector'] ), esc_html( $r['location'] ), esc_html( $r['pay'] ), esc_html( hcc_import_date( $r['closing'] ) ), esc_html( $r['status'] ? $r['status'] : 'live' ), esc_html( implode( ', ', $err ) ) );
		}
		echo '</tbody></table><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:16px"><input type="hidden" name="action" value="hcc_import">';
		wp_nonce_field( 'hcc_import' );
		printf( '<button class="button button-primary" name="go" value="1"%s>Import %d jobs</button> <a class="button" href="%s">Cancel</a></form>', $ok ? '' : ' disabled', (int) $ok, esc_url( admin_url( 'edit.php?post_type=job_vacancies&page=hcc-import' ) ) );
		echo '<p class="description">Row numbers match the rows in your spreadsheet. Rows with problems are skipped.</p></div>';
		return;
	}
	// One click: the adverts from the current hcctemps.co.uk website, shipped with the theme.
	$bundled = get_template_directory() . '/data/jobs-old-site-all-live.csv';
	if ( is_readable( $bundled ) ) {
		$n_old  = count( (array) hcc_import_read( $bundled ) );
		$n_demo = count( get_posts( array( 'post_type' => 'job_vacancies', 'post_status' => 'any', 'meta_key' => '_hcc_demo', 'numberposts' => -1, 'fields' => 'ids' ) ) );
		echo '<div class="card" style="max-width:820px;padding:16px 20px;margin:16px 0 24px"><h2 style="margin-top:0">Jobs from the current website</h2>';
		echo '<p>' . (int) $n_old . ' adverts copied from hcctemps.co.uk (October 2026). Jobs already on this site with the same web address are updated, not duplicated, and importing again is safe.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="hcc_import_bundled">';
		wp_nonce_field( 'hcc_import_bundled' );
		echo '<p><label><input type="radio" name="set" value="all-live" checked> <b>All live</b>: to see the job board working while the client reviews the adverts</label><br>';
		echo '<label><input type="radio" name="set" value="suggested"> <b>Suggested</b>: only the 3 adverts from the last 6 months live, older ones closed (hidden)</label></p>';
		if ( $n_demo ) {
			echo '<p><label><input type="checkbox" name="remove_demo" value="1" checked> Also remove the ' . (int) $n_demo . ' sample jobs that came with the theme</label></p>';
		}
		echo '<p><button class="button button-primary">Import ' . (int) $n_old . ' jobs</button></p></form>';
		echo '<p class="description">When the client sends back the reviewed spreadsheet, import that file below: it updates these same jobs.</p></div>';
	}
	echo '<h2>Import your own spreadsheet</h2>';
	echo '<ol><li><a href="' . esc_url( admin_url( 'admin-post.php?action=hcc_import_template' ) ) . '">Download the CSV template</a> and open it in Excel or Google Sheets.</li><li>Add one job per row. Keep the first row (the column names). Delete the example row.</li><li>Save as <b>CSV UTF-8</b>, upload it below and check the preview.</li></ol>';
	echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="hcc_import">';
	wp_nonce_field( 'hcc_import' );
	echo '<p><input type="file" name="csv" accept=".csv,text/csv" required> <button class="button button-primary">Upload and preview</button></p></form>';
	echo '<h2>Columns</h2><table class="widefat striped" style="max-width:900px"><tbody>';
	foreach ( hcc_import_columns() as $k => $c ) {
		printf( '<tr><th style="width:130px"><code>%s</code></th><td>%s</td><td><i>%s</i></td></tr>', esc_html( $k ), esc_html( $c[0] ), esc_html( $c[1] ) );
	}
	echo '</tbody></table><p class="description">To update a job, use the same ref. To close jobs, set status to expired or give a closing date in the past. Separate several locations with a | character.</p></div>';
}

/* Import the adverts shipped with the theme (copied from the old website). */
add_action( 'admin_post_hcc_import_bundled', function () {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'hcc_import_bundled' );
	$back = admin_url( 'edit.php?post_type=job_vacancies&page=hcc-import' );
	$file = get_template_directory() . '/data/jobs-old-site-' . ( 'suggested' === hcc_field( 'set' ) ? 'suggested' : 'all-live' ) . '.csv';
	$rows = hcc_import_read( $file );
	if ( is_wp_error( $rows ) ) {
		wp_safe_redirect( add_query_arg( 'err', rawurlencode( $rows->get_error_message() ), $back ) );
		exit;
	}
	if ( ! empty( $_POST['remove_demo'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		foreach ( get_posts( array( 'post_type' => 'job_vacancies', 'post_status' => 'any', 'meta_key' => '_hcc_demo', 'numberposts' => -1, 'fields' => 'ids' ) ) as $demo ) {
			wp_delete_post( $demo, true );
		}
	}
	@set_time_limit( 300 ); // phpcs:ignore
	$made = 0;
	$upd  = 0;
	foreach ( $rows as $r ) {
		if ( hcc_import_check( $r ) ) {
			continue;
		}
		$had = hcc_import_find( $r['ref'], $r['slug'] );
		if ( hcc_import_row( $r ) ) {
			$had ? $upd++ : $made++;
		}
	}
	wp_safe_redirect( add_query_arg( array( 'done' => $made, 'upd' => $upd ), $back ) );
	exit;
} );
