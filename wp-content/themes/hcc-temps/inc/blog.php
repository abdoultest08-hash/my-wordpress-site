<?php
/**
 * Blog: the posts page, and a one-click import of the HCC-Temps articles that ship with the theme
 * (the 8 posts from the current hcctemps.co.uk blog plus 8 new articles for 2026), with their images.
 * Posts → Import HCC blog posts. Importing again updates the same posts (matched by web address).
 */

defined( 'ABSPATH' ) || exit;

/** Make sure there is a Blog page listing every post (WordPress needs a static front page for that). */
function hcc_blog_setup_pages() {
	if ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) {
		$home = get_page_by_path( 'home' );
		$hid  = $home ? $home->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home', 'post_name' => 'home' ) );
		update_option( 'page_on_front', $hid );
		update_option( 'show_on_front', 'page' );
	}
	if ( ! get_option( 'page_for_posts' ) ) {
		$blog = get_page_by_path( 'blog' );
		$bid  = $blog ? $blog->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Blog', 'post_name' => 'blog' ) );
		if ( $blog && 'publish' !== $blog->post_status ) {
			wp_update_post( array( 'ID' => $bid, 'post_status' => 'publish' ) );
		}
		update_option( 'page_for_posts', $bid );
	}
}

/** The Blog page link (falls back to the homepage blog strip). */
function hcc_blog_url() {
	return get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/#blog' );
}

/* ---------- Import ---------- */

function hcc_blog_data() {
	$f = get_template_directory() . '/data/blog-posts.json';
	return is_readable( $f ) ? (array) json_decode( file_get_contents( $f ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/** Add a bundled image to the Media Library once (reused on later imports). Returns the attachment ID. */
function hcc_blog_media( $ref, $post_id = 0 ) {
	if ( 0 === strpos( $ref, 'theme:' ) ) {
		$path = get_template_directory() . '/assets/img/' . basename( substr( $ref, 6 ) );
	} else {
		$path = get_template_directory() . '/data/blog-img/' . basename( preg_replace( '/^bundle:/', '', $ref ) );
	}
	if ( ! is_readable( $path ) ) {
		return 0;
	}
	$key = basename( $path );
	$old = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_hcc_blog_src', 'meta_value' => $key, 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( $old ) {
		return (int) $old[0];
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( $key );
	copy( $path, $tmp );
	$id = media_handle_sideload( array( 'name' => $key, 'tmp_name' => $tmp ), $post_id );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore
		return 0;
	}
	update_post_meta( $id, '_hcc_blog_src', $key );
	return (int) $id;
}

/** Import (or update) every bundled post. Returns array( created, updated ). */
function hcc_blog_import( $remove_samples = true ) {
	$made = 0;
	$upd  = 0;
	if ( $remove_samples ) {
		foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'meta_key' => '_hcc_demo', 'numberposts' => -1, 'fields' => 'ids' ) ) as $demo ) {
			wp_delete_post( $demo, true );
		}
		$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $hello ) {
			wp_delete_post( $hello->ID, true );
		}
	}
	foreach ( hcc_blog_data() as $p ) {
		$cat = term_exists( $p['category'], 'category' );
		if ( ! $cat ) {
			$cat = wp_insert_term( $p['category'], 'category' );
		}
		$cat_id = is_array( $cat ) ? (int) $cat['term_id'] : (int) $cat;
		$exists = get_page_by_path( $p['slug'], OBJECT, 'post' );
		$post   = array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_title'    => $p['title'],
			'post_name'     => $p['slug'],
			'post_excerpt'  => $p['excerpt'],
			'post_date'     => $p['date'],
			'post_category' => $cat_id ? array( $cat_id ) : array(),
			'post_content'  => '',
		);
		if ( $exists ) {
			$post['ID'] = $exists->ID;
			$id         = wp_update_post( $post );
			$upd++;
		} else {
			$id = wp_insert_post( $post );
			$made++;
		}
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		// Images inside the article: {{img:file}} becomes the Media Library address.
		$content = preg_replace_callback( '/\{\{img:([^}]+)\}\}/', function ( $m ) use ( $id ) {
			$att = hcc_blog_media( 'bundle:' . $m[1], $id );
			return $att ? wp_get_attachment_url( $att ) : '';
		}, $p['content'] );
		wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $content ) ) );
		if ( ! empty( $p['image'] ) ) {
			$att = hcc_blog_media( $p['image'], $id );
			if ( $att ) {
				set_post_thumbnail( $id, $att );
			}
		}
		update_post_meta( $id, '_hcc_blog_import', 1 );
	}
	// "Uncategorised" (or "Uncategorized") is empty now: tidy it away from the lists.
	return array( $made, $upd );
}

add_action( 'admin_menu', function () {
	add_posts_page( 'Import HCC blog posts', 'Import HCC blog posts', 'edit_posts', 'hcc-blog-import', 'hcc_blog_import_page' );
} );

function hcc_blog_import_page() {
	$posts = hcc_blog_data();
	$demo  = count( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'meta_key' => '_hcc_demo', 'numberposts' => -1, 'fields' => 'ids' ) ) );
	echo '<div class="wrap"><h1>Import HCC blog posts</h1>';
	if ( isset( $_GET['done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		printf( '<div class="notice notice-success"><p>Done: %d posts added, %d updated. <a href="%s">See all posts</a> · <a href="%s">View the blog</a></p></div>', (int) $_GET['done'], (int) ( $_GET['upd'] ?? 0 ), esc_url( admin_url( 'edit.php' ) ), esc_url( hcc_blog_url() ) ); // phpcs:ignore
	}
	echo '<p>' . count( $posts ) . ' articles come with the theme: the posts from the current hcctemps.co.uk blog (same titles, dates and web addresses, with their images) and new articles for 2026. They are published straight away.</p>';
	echo '<table class="widefat striped" style="max-width:900px"><thead><tr><th>Date</th><th>Title</th><th>Category</th><th>On this site</th></tr></thead><tbody>';
	foreach ( array_reverse( $posts ) as $p ) {
		$on = get_page_by_path( $p['slug'], OBJECT, 'post' );
		printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', esc_html( wp_date( 'j M Y', strtotime( $p['date'] ) ) ), esc_html( $p['title'] ), esc_html( $p['category'] ), $on ? 'Yes (will be updated)' : 'New' );
	}
	echo '</tbody></table>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:16px"><input type="hidden" name="action" value="hcc_blog_import">';
	wp_nonce_field( 'hcc_blog_import' );
	if ( $demo ) {
		echo '<p><label><input type="checkbox" name="remove_samples" value="1" checked> Also remove the ' . (int) $demo . ' sample posts that came with the theme</label></p>';
	}
	echo '<p><button class="button button-primary">Import ' . count( $posts ) . ' posts</button></p><p class="description">Safe to repeat: posts are matched by their web address and updated, not duplicated. Images are added to the Media Library once.</p></form></div>';
}

add_action( 'admin_post_hcc_blog_import', function () {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'hcc_blog_import' );
	@set_time_limit( 300 ); // phpcs:ignore
	hcc_blog_setup_pages();
	list( $made, $upd ) = hcc_blog_import( ! empty( $_POST['remove_samples'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	wp_safe_redirect( add_query_arg( array( 'done' => $made, 'upd' => $upd ), admin_url( 'edit.php?page=hcc-blog-import' ) ) );
	exit;
} );

/* Reading time for a post. */
function hcc_reading_time( $id = null ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $id ? $id : get_the_ID() ) ) );
	return max( 1, (int) round( $words / 220 ) );
}

/* Blog and category pages: 12 posts per page (fills the 3-column grid). */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_home() || $q->is_category() ) ) {
		$q->set( 'posts_per_page', 12 );
	}
} );
