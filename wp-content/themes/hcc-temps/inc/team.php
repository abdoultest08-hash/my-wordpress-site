<?php
/**
 * Meet the team (About us). wp-admin → Team: title = first name, Role field, featured image = photo,
 * Order field = position. The seven people are added on first activation with photos from the theme.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'hcc_team', array(
		'labels'        => array( 'name' => 'Team', 'singular_name' => 'Team member', 'add_new_item' => 'Add team member', 'edit_item' => 'Edit team member', 'featured_image' => 'Photo', 'set_featured_image' => 'Set photo' ),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-id',
		'menu_position' => 27,
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
	) );
} );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'hcc_team_role', 'Role', function ( $post ) {
		wp_nonce_field( 'hcc_team', 'hcc_team_nonce' );
		echo '<p><input type="text" name="hcc_team_role" value="' . esc_attr( get_post_meta( $post->ID, '_hcc_role_title', true ) ) . '" class="widefat" placeholder="e.g. Compliance Manager"></p><p class="description">Shown under the name on About us. Use the first name only as the title. Order (in Page attributes) sets the position.</p>';
	}, 'hcc_team', 'normal', 'high' );
} );

add_action( 'save_post_hcc_team', function ( $id ) {
	if ( ! isset( $_POST['hcc_team_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hcc_team_nonce'] ) ), 'hcc_team' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	update_post_meta( $id, '_hcc_role_title', isset( $_POST['hcc_team_role'] ) ? sanitize_text_field( wp_unslash( $_POST['hcc_team_role'] ) ) : '' );
} );

/** Team members in order: name, role, photo URL, first (founder) flag. */
function hcc_team_members() {
	$out = array();
	foreach ( get_posts( array( 'post_type' => 'hcc_team', 'post_status' => 'publish', 'numberposts' => 50, 'orderby' => 'menu_order title', 'order' => 'ASC' ) ) as $i => $p ) {
		$photo = get_the_post_thumbnail_url( $p, 'medium_large' );
		$file  = get_post_meta( $p->ID, '_hcc_theme_img', true );
		$out[] = array( 'name' => get_the_title( $p ), 'role' => get_post_meta( $p->ID, '_hcc_role_title', true ), 'img' => $photo ? $photo : ( $file ? hcc_img( 'team/' . $file ) : '' ), 'lead' => 0 === $i );
	}
	return $out;
}

/** The seven people from HCC's checklist (photos ship with the theme). */
function hcc_seed_team() {
	if ( get_posts( array( 'post_type' => 'hcc_team', 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids' ) ) ) {
		return;
	}
	$people = array( array( 'David', 'Founder & Senior Business Development Manager', 'david.jpg' ), array( 'Geraldine', 'Compliance Manager', 'geraldine.jpg' ), array( 'Arjun', 'Consultant', 'arjun.jpg' ), array( 'Debbie', 'Compliance Admin', 'debbie.jpg' ), array( 'Temi', 'Compliance Admin', 'temi.jpg' ), array( 'Ashanti', 'Onboarding Manager', 'ashanti.jpg' ), array( 'Eddie', 'Client Manager', 'eddie.jpg' ) );
	foreach ( $people as $i => $p ) {
		$id = wp_insert_post( array( 'post_type' => 'hcc_team', 'post_status' => 'publish', 'post_title' => $p[0], 'menu_order' => $i + 1 ) );
		update_post_meta( $id, '_hcc_role_title', $p[1] );
		update_post_meta( $id, '_hcc_theme_img', $p[2] );
	}
}
add_action( 'after_switch_theme', 'hcc_seed_team' );
add_action( 'init', function () {
	if ( '1' !== get_option( 'hcc_team_seeded' ) && did_action( 'init' ) ) {
		update_option( 'hcc_team_seeded', '1' );
		hcc_seed_team();
	}
}, 40 );
