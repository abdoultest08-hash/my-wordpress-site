<?php
/**
 * Candidate records (post type hcc_applicant) and their private file folder.
 * Staff work in the Staff portal; wp-admin shows a simple list that links to it.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'hcc_applicant', array(
		'labels'          => array(
			'name'          => 'Candidates',
			'singular_name' => 'Candidate',
			'edit_item'     => 'Candidate',
			'all_items'     => 'All candidates',
			'search_items'  => 'Search candidates',
		),
		'public'          => false,
		'show_ui'         => true,
		'menu_icon'       => 'dashicons-groups',
		'menu_position'   => 26,
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

/** Private folder for one candidate. The random suffix keeps paths unguessable on servers that ignore .htaccess. */
function hcc_private_dir( $applicant_id ) {
	$up   = wp_upload_dir();
	$base = trailingslashit( $up['basedir'] ) . 'hcc-private';
	if ( ! is_dir( $base ) ) {
		wp_mkdir_p( $base );
		file_put_contents( $base . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		file_put_contents( $base . '/index.php', "<?php // Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	$folder = get_post_meta( $applicant_id, '_hcc_folder', true );
	if ( ! $folder ) {
		$folder = get_post_meta( $applicant_id, '_hcc_ref', true ) . '-' . wp_generate_password( 12, false );
		update_post_meta( $applicant_id, '_hcc_folder', $folder );
	}
	$dir = $base . '/' . $folder;
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		file_put_contents( $dir . '/index.php', "<?php // Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $dir;
}

/* ---------- wp-admin list ---------- */

add_filter( 'manage_hcc_applicant_posts_columns', function () {
	return array(
		'cb'        => '<input type="checkbox">',
		'title'     => 'Candidate',
		'hcc_role'  => 'Role',
		'hcc_state' => 'Status',
		'hcc_pct'   => 'Profile',
		'date'      => 'Registered',
	);
} );

add_action( 'manage_hcc_applicant_posts_custom_column', function ( $col, $id ) {
	if ( 'hcc_role' === $col ) {
		echo esc_html( hcc_role_name( hcc_meta( $id, 'role_key' ) ) );
	} elseif ( 'hcc_state' === $col ) {
		$st = hcc_state( $id );
		echo esc_html( 'closed' === $st ? 'Withdrawn / not qualified' : ( 'completed' === $st ? 'Staff completed' : ( hcc_meta( $id, 'submitted' ) ? 'In progress' : 'Registering (step ' . (int) hcc_meta( $id, 'step', 1 ) . ')' ) ) );
	} elseif ( 'hcc_pct' === $col ) {
		echo (int) hcc_progress( $id ) . '%';
	}
}, 10, 2 );

add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( 'hcc_applicant' === $post->post_type ) {
		$actions = array( 'hcc_open' => '<a href="' . esc_url( hcc_staff_url( array( 'candidate' => $post->ID ) ) ) . '">Open in Staff portal</a>' ) + array_intersect_key( $actions, array( 'trash' => 1 ) );
	}
	return $actions;
}, 10, 2 );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'hcc_applicant', 'Candidate', function ( $post ) {
		echo '<p><b>' . esc_html( hcc_name( $post->ID ) ) . '</b> · ' . esc_html( hcc_meta( $post->ID, 'ref' ) ) . ' · ' . esc_html( hcc_role_name( hcc_meta( $post->ID, 'role_key' ) ) ) . ' · Profile ' . (int) hcc_progress( $post->ID ) . '%</p>';
		echo '<p>Documents, checks, references and notes are managed in the Staff portal.</p>';
		echo '<p><a class="button button-primary" href="' . esc_url( hcc_staff_url( array( 'candidate' => $post->ID ) ) ) . '">Open in Staff portal</a></p>';
	}, 'hcc_applicant', 'normal', 'high' );
	remove_meta_box( 'slugdiv', 'hcc_applicant', 'normal' );
} );

/** Deleting a candidate record also removes their private files. */
add_action( 'before_delete_post', function ( $id ) {
	if ( 'hcc_applicant' !== get_post_type( $id ) ) {
		return;
	}
	$folder = get_post_meta( $id, '_hcc_folder', true );
	if ( $folder ) {
		$up  = wp_upload_dir();
		$dir = trailingslashit( $up['basedir'] ) . 'hcc-private/' . basename( $folder );
		foreach ( (array) glob( $dir . '/*' ) as $f ) {
			if ( is_file( $f ) ) {
				unlink( $f );
			}
		}
		if ( is_dir( $dir ) ) {
			rmdir( $dir );
		}
	}
	$user = (int) get_post_meta( $id, '_hcc_user_id', true );
	$u    = $user ? get_userdata( $user ) : null;
	if ( $u && array( 'hcc_candidate' ) === array_values( (array) $u->roles ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
		wp_delete_user( $user );
	}
} );
