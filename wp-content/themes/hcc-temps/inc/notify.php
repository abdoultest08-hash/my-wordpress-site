<?php
/**
 * Candidate notifications and to-do list (candidate portal → Dashboard and Notifications).
 *
 * Every notification has a type:
 *   action   something the candidate must do (a to-do): replace a document, upload one, get training, pay, sign
 *   update   for information: approved, a message from the team, payment confirmed
 *   reminder a date coming up: a certificate about to expire
 * To-dos tick themselves off when the candidate does the thing (for example uploads the document),
 * and can also be marked as done by hand. Information can be marked as read.
 *
 * Stored on the candidate record (meta _hcc_notes), newest last:
 *   id, at, type, title, text, link (#anchor in the portal), key (to find it again), todo, done, read
 */

defined( 'ABSPATH' ) || exit;

/** All notifications for a candidate (oldest first). Old update emails are turned into notifications once. */
function hcc_notes( $id ) {
	$notes = hcc_meta( $id, 'notes', null );
	if ( null === $notes || '' === $notes ) {
		$notes = array();
		foreach ( (array) hcc_meta( $id, 'messages', array() ) as $m ) {
			foreach ( hcc_notes_from_items( (array) $m['items'] ) as $n ) {
				$n['at']   = (int) $m['at'];
				$n['todo'] = false; // older updates: shown as history only
				$n['read'] = (int) $m['at'];
				$n['id']   = count( $notes ) + 1;
				$notes[]   = $n;
			}
		}
		hcc_set( $id, 'notes', $notes );
	}
	return (array) $notes;
}

function hcc_notes_save( $id, $notes ) {
	$notes = array_values( $notes );
	if ( count( $notes ) > 300 ) {
		$notes = array_slice( $notes, -300 );
	}
	hcc_set( $id, 'notes', $notes );
}

/**
 * Add a notification. An open to-do with the same key is refreshed instead of repeated.
 *
 * @param array $n type, title, text, link, key, todo (defaults: update, not a to-do).
 */
function hcc_notify( $id, $n ) {
	$n     = wp_parse_args( $n, array( 'type' => 'update', 'title' => '', 'text' => '', 'link' => '', 'key' => '', 'todo' => false ) );
	$notes = hcc_notes( $id );
	if ( $n['key'] ) {
		foreach ( $notes as $i => $old ) {
			if ( $old['key'] === $n['key'] && ! empty( $old['todo'] ) && empty( $old['done'] ) ) {
				$notes[ $i ] = array_merge( $old, array( 'at' => time(), 'title' => $n['title'], 'text' => $n['text'], 'link' => $n['link'], 'type' => $n['type'], 'read' => 0 ) );
				hcc_notes_save( $id, $notes );
				return $old['id'];
			}
		}
	}
	$max   = $notes ? max( wp_list_pluck( $notes, 'id' ) ) : 0;
	$new   = array( 'id' => $max + 1, 'at' => time(), 'type' => $n['type'], 'title' => $n['title'], 'text' => $n['text'], 'link' => $n['link'], 'key' => $n['key'], 'todo' => (bool) $n['todo'], 'done' => 0, 'read' => 0 );
	$notes[] = $new;
	hcc_notes_save( $id, $notes );
	return $new['id'];
}

/** Tick off open to-dos whose key matches exactly, or starts with "$key:" when $prefix is true. */
function hcc_todo_done( $id, $key, $prefix = false ) {
	$notes   = hcc_notes( $id );
	$changed = false;
	foreach ( $notes as $i => $n ) {
		if ( ! empty( $n['todo'] ) && empty( $n['done'] ) && ( $n['key'] === $key || ( $prefix && 0 === strpos( (string) $n['key'], $key . ':' ) ) ) ) {
			$notes[ $i ]['done'] = time();
			$notes[ $i ]['read'] = $notes[ $i ]['read'] ? $notes[ $i ]['read'] : time();
			$changed             = true;
		}
	}
	if ( $changed ) {
		hcc_notes_save( $id, $notes );
	}
}

function hcc_open_todos( $id ) {
	return array_values( array_filter( hcc_notes( $id ), function ( $n ) { return ! empty( $n['todo'] ) && empty( $n['done'] ); } ) );
}

function hcc_unread_notes( $id ) {
	return count( array_filter( hcc_notes( $id ), function ( $n ) { return empty( $n['read'] ); } ) );
}

/** Turn the items of a staff update (the update tray) into notifications. */
function hcc_notes_from_items( $items ) {
	$out = array();
	foreach ( $items as $q ) {
		$label = isset( $q['label'] ) ? $q['label'] : '';
		$key   = isset( $q['key'] ) ? $q['key'] : '';
		$text  = isset( $q['text'] ) ? (string) $q['text'] : '';
		switch ( $q['type'] ) {
			case 'amend':
				$out[] = array( 'type' => 'action', 'title' => 'Replace your ' . $label, 'text' => $text ? 'Reason: ' . $text : 'Please upload a new copy.', 'link' => '#item-' . $key, 'key' => 'file:' . $key, 'todo' => true );
				break;
			case 'missing':
				$out[] = array( 'type' => 'action', 'title' => 'Upload your ' . $label, 'text' => $text ? $text : 'We still need this from you.', 'link' => '#item-' . $key, 'key' => 'file:' . $key, 'todo' => true );
				break;
			case 'training':
				$out[] = array( 'type' => 'action', 'title' => 'Training needed: ' . $label, 'text' => 'Complete the course and upload your certificate.', 'link' => '#item-' . $key, 'key' => 'file:' . $key, 'todo' => true );
				break;
			case 'approved':
				$out[] = array( 'type' => 'update', 'title' => 'Approved: ' . $label, 'text' => 'Thank you. This has been checked and approved.', 'link' => $key ? '#item-' . $key : '', 'key' => 'ok:' . $key );
				break;
			case 'note':
				$out[] = array( 'type' => 'update', 'title' => 'New message from the team', 'text' => $text, 'link' => '#messages', 'key' => '' );
				break;
		}
	}
	return $out;
}

/* A staff update was sent: add the notifications, and close to-dos for anything approved. */
add_action( 'hcc_update_sent', function ( $id, $items ) {
	foreach ( hcc_notes_from_items( $items ) as $n ) {
		if ( 0 === strpos( $n['key'], 'ok:' ) ) {
			hcc_todo_done( $id, 'file:' . substr( $n['key'], 3 ) );
		}
		hcc_notify( $id, $n );
	}
}, 10, 2 );

/* The candidate uploaded a file: the to-do for that document is done (now waiting for review). */
add_action( 'hcc_item_uploaded', function ( $id, $key ) {
	hcc_todo_done( $id, 'file:' . $key );
}, 10, 2 );

/* ---------- Candidate actions: open, mark as read, mark as done ---------- */

function hcc_note_url( $id, $do, $n = 0 ) {
	return add_query_arg( array( 'action' => 'hcc_note', 'do' => $do, 'n' => $n, '_n' => wp_create_nonce( 'hcc_note_' . $id ) ), admin_url( 'admin-post.php' ) );
}

add_action( 'admin_post_hcc_note', function () {
	$id = hcc_applicant_for_user( get_current_user_id() );
	if ( ! $id || ! isset( $_GET['_n'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_n'] ) ), 'hcc_note_' . $id ) ) {
		wp_die( 'Your session has expired. Please log in again.', 403 );
	}
	$do    = isset( $_GET['do'] ) ? sanitize_key( $_GET['do'] ) : '';
	$nid   = isset( $_GET['n'] ) ? absint( $_GET['n'] ) : 0;
	$notes = hcc_notes( $id );
	$link  = '#updates';
	foreach ( $notes as $i => $n ) {
		if ( 'readall' === $do ) {
			$notes[ $i ]['read'] = $n['read'] ? $n['read'] : time();
			continue;
		}
		if ( (int) $n['id'] !== $nid ) {
			continue;
		}
		$notes[ $i ]['read'] = $n['read'] ? $n['read'] : time();
		if ( 'done' === $do && ! empty( $n['todo'] ) ) {
			$notes[ $i ]['done'] = time();
			hcc_log( $id, 'Marked a to-do as done: ' . $n['title'], 'Candidate' );
		}
		if ( 'open' === $do && $n['link'] ) {
			$link = $n['link'];
		}
	}
	hcc_notes_save( $id, $notes );
	$back = isset( $_GET['back'] ) ? sanitize_key( $_GET['back'] ) : '';
	wp_safe_redirect( hcc_portal_url() . ( 'open' === $do ? $link : '#' . ( $back ? $back : 'updates' ) ) );
	exit;
} );

/* ---------- Showing them ---------- */

function hcc_note_icon( $n ) {
	if ( 'reminder' === $n['type'] ) {
		return array( 'cal', 'n-rem' );
	}
	if ( 'action' === $n['type'] ) {
		return ! empty( $n['done'] ) ? array( 'check', 'n-ok' ) : array( 'alert', 'n-todo' );
	}
	return 0 === strpos( (string) $n['key'], 'ok:' ) ? array( 'check', 'n-ok' ) : ( '#messages' === $n['link'] ? array( 'mail', 'n-msg' ) : array( 'bell', 'n-upd' ) );
}

/** One notification row. $ctx: portal (buttons work) or preview (read-only). */
function hcc_note_row( $id, $n, $ctx, $back = 'updates' ) {
	list( $ico, $cls ) = hcc_note_icon( $n );
	$live = 'portal' === $ctx;
	$open = $n['link'] ? ( $live ? hcc_note_url( $id, 'open', $n['id'] ) : $n['link'] ) : '';
	$h    = '<li class="note ' . esc_attr( $cls ) . ( empty( $n['read'] ) ? ' unread' : '' ) . ( ! empty( $n['done'] ) ? ' is-done' : '' ) . '" data-type="' . esc_attr( $n['type'] ) . '">';
	$h   .= '<span class="n-ico">' . hcc_icon( $ico, 18 ) . '</span>';
	$h   .= '<span class="n-body"><b>' . esc_html( $n['title'] ) . '</b>' . ( $n['text'] ? '<span>' . esc_html( $n['text'] ) . '</span>' : '' ) . '<small>' . esc_html( hcc_ago( (int) $n['at'] ) ) . ( ! empty( $n['done'] ) ? ' · done' : '' ) . '</small></span>';
	$h   .= '<span class="n-act">';
	if ( $open && empty( $n['done'] ) ) {
		$h .= '<a class="btn btn-sm ' . ( ! empty( $n['todo'] ) ? 'btn-navy' : 'btn-ghost' ) . '" href="' . esc_url( $open ) . '">' . ( ! empty( $n['todo'] ) ? 'Do it now' : 'Open' ) . '</a>';
	}
	if ( $live && ! empty( $n['todo'] ) && empty( $n['done'] ) ) {
		$h .= '<a class="link-btn" href="' . esc_url( add_query_arg( 'back', $back, hcc_note_url( $id, 'done', $n['id'] ) ) ) . '">Mark as done</a>';
	} elseif ( $live && empty( $n['read'] ) ) {
		$h .= '<a class="link-btn" href="' . esc_url( add_query_arg( 'back', $back, hcc_note_url( $id, 'read', $n['id'] ) ) ) . '">Mark as read</a>';
	}
	return $h . '</span></li>';
}

/* Dashboard: the to-do list and the latest notifications. */
add_action( 'hcc_portal_dashboard_top', function ( $id, $ctx ) {
	$todos = array_reverse( hcc_open_todos( $id ) );
	echo '<div class="p-card todo-card"><div class="p-card-head"><h2>Your to-do list</h2><span class="p-count">' . ( $todos ? count( $todos ) . ' to do' : 'All done' ) . '</span></div>';
	if ( $todos ) {
		echo '<ul class="notes">';
		foreach ( $todos as $n ) {
			echo hcc_note_row( $id, $n, $ctx, 'dashboard' ); // phpcs:ignore
		}
		echo '</ul>';
	} else {
		echo '<p class="hint">Nothing to do right now. We\'ll add anything we need from you here and email you.</p>';
	}
	echo '</div>';
}, 10, 2 );

add_action( 'hcc_portal_dashboard', function ( $id, $ctx ) {
	$latest = array_slice( array_reverse( array_filter( hcc_notes( $id ), function ( $n ) { return empty( $n['todo'] ) || ! empty( $n['done'] ); } ) ), 0, 3 );
	if ( ! $latest ) {
		return;
	}
	echo '<div class="p-card"><div class="p-card-head"><h2>Latest notifications</h2><a class="link-arrow" href="#updates">See all <span aria-hidden="true">→</span></a></div><ul class="notes">';
	foreach ( $latest as $n ) {
		echo hcc_note_row( $id, $n, $ctx, 'dashboard' ); // phpcs:ignore
	}
	echo '</ul></div>';
}, 20, 2 );

/* Notifications view: filters and the full list. */
add_action( 'hcc_portal_notifications', function ( $id, $ctx ) {
	$notes = array_reverse( hcc_notes( $id ) );
	$count = array( 'all' => count( $notes ), 'action' => 0, 'update' => 0, 'reminder' => 0 );
	foreach ( $notes as $n ) {
		$count[ $n['type'] ] = ( isset( $count[ $n['type'] ] ) ? $count[ $n['type'] ] : 0 ) + 1;
	}
	echo '<div class="p-card"><div class="note-bar"><div class="chips" role="group" aria-label="Show">';
	foreach ( array( 'all' => 'All', 'action' => 'Action required', 'update' => 'Updates', 'reminder' => 'Reminders' ) as $k => $l ) {
		echo '<button type="button" class="chip' . ( 'all' === $k ? ' is-on' : '' ) . '" data-note-filter="' . esc_attr( $k ) . '">' . esc_html( $l ) . ' (' . (int) $count[ $k ] . ')</button>';
	}
	echo '</div>';
	if ( 'portal' === $ctx && hcc_unread_notes( $id ) ) {
		echo '<a class="link-btn" href="' . esc_url( hcc_note_url( $id, 'readall' ) ) . '">Mark all as read</a>';
	}
	echo '</div>';
	if ( $notes ) {
		echo '<ul class="notes notes-all">';
		foreach ( $notes as $n ) {
			echo hcc_note_row( $id, $n, $ctx ); // phpcs:ignore
		}
		echo '</ul>';
	} else {
		echo '<p class="hint">No notifications yet. When the team reviews your registration, their updates appear here and by email.</p>';
	}
	echo '</div>';
}, 10, 2 );

/* Sidebar badge: unread notifications. */
add_filter( 'hcc_portal_nav', function ( $nav, $id ) {
	foreach ( $nav as $i => $it ) {
		if ( 'updates' === $it[0] ) {
			$u          = hcc_unread_notes( $id );
			$nav[ $i ][4] = $u ? $u : '';
			$nav[ $i ][5] = $u ? 'data-hot="1"' : '';
		}
	}
	return $nav;
}, 10, 2 );
