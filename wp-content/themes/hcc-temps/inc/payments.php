<?php
/**
 * Payments: the DBS check and training, paid by the candidate through Monzo payment links.
 *
 * How it works:
 * 1. Once a candidate has submitted their registration, the portal works out what they need to buy:
 *    - a DBS check if they have no DBS or a paper one (not on the Update Service);
 *    - training for any certificate still missing: the full bundle for their role when more than
 *      2 courses are missing (care £50, hospitality £35), otherwise each course on its own.
 *    Staff can also ask for a payment by hand from the candidate folder.
 * 2. The candidate sees a to-do and a checkout page (Payments) with one "Pay with Monzo" button per
 *    item, pays, and presses "I've paid".
 * 3. Staff see it under Staff portal → Payments, check their Monzo app and press "Payment received".
 * 4. Staff apply for the DBS / assign the courses and mark it done. The candidate is told at each step.
 *
 * Monzo cannot tell the website a payment arrived, so step 3 is done by staff.
 * Prices and links: hcc_pay_products() and Staff portal → Payments → Payment links (managers).
 */

defined( 'ABSPATH' ) || exit;

/** Products: key => array( label, price in pounds ). Prices from the client's price list. */
function hcc_pay_products() {
	return array(
		'dbs'         => array( 'Enhanced DBS check', 70 ),
		'bundle_care' => array( 'Care training bundle (all courses)', 50 ),
		'bundle_hosp' => array( 'Hospitality training bundle (all courses)', 35 ),
		'course1'     => array( '1 training course', 12 ),
		'course2'     => array( '2 training courses', 24 ),
		'food1'       => array( 'Food Safety & Hygiene Level 1', 10 ),
		'food2'       => array( 'Food Safety & Hygiene Level 2', 15 ),
	);
}

/** Monzo (and optional Ayan Pay) payment links, set by a manager. */
function hcc_pay_links() {
	return wp_parse_args( (array) get_option( 'hcc_pay_links', array() ), array_fill_keys( array_merge( array_keys( hcc_pay_products() ), array( 'ayan', 'uniform_care', 'uniform_hosp' ) ), '' ) );
}

/* ---------- Uniform: HCC-Temps does not supply uniforms ---------- */

/** What uniform the role needs and where to buy it: array( name, link ). */
function hcc_uniform_for( $id ) {
	$role  = hcc_meta( $id, 'role_key' );
	$links = hcc_pay_links();
	if ( in_array( $role, array( 'nurse', 'hca', 'senior-carer' ), true ) ) {
		return array( 'blue tunic', $links['uniform_care'] );
	}
	if ( in_array( $role, array( 'chef', 'head-chef', 'kitchen', 'catering', 'barista' ), true ) ) {
		return array( 'chef whites or kitchen uniform', $links['uniform_hosp'] );
	}
	return array( 'work uniform', '' );
}

/** 'need' (said No and not bought yet), 'got' (bought since), or '' (has one). */
function hcc_uniform_state( $id ) {
	$a = (array) hcc_meta( $id, 'answers', array() );
	if ( ! isset( $a['uniform'] ) || 'No' !== $a['uniform'] ) {
		return '';
	}
	return hcc_meta( $id, 'uniform_got' ) ? 'got' : 'need';
}

add_action( 'admin_post_hcc_uniform_got', function () {
	$id = hcc_applicant_for_user( get_current_user_id() );
	if ( ! $id || ! check_admin_referer( 'hcc_uniform_' . $id ) ) {
		wp_die( 'Your session has expired. Please log in again.', 403 );
	}
	hcc_set( $id, 'uniform_got', time() );
	hcc_todo_done( $id, 'uniform' );
	hcc_log( $id, 'Candidate says they now have their ' . hcc_uniform_for( $id )[0], 'Candidate' );
	wp_safe_redirect( hcc_portal_url() . '#payments' );
	exit;
} );

/** Which bundle a role can buy ('' = none: each course is bought on its own). */
function hcc_pay_bundle_for( $role ) {
	if ( in_array( $role, array( 'nurse', 'hca', 'senior-carer' ), true ) ) {
		return 'bundle_care';
	}
	return in_array( $role, array( 'chef', 'head-chef', 'kitchen', 'catering', 'barista' ), true ) ? 'bundle_hosp' : '';
}

/**
 * How a training item can be bought: 'external' (not sold by HCC: book it face to face),
 * 'food1' / 'food2' (Food Safety at £10 / £15), or 'course' (£12).
 */
function hcc_pay_course_kind( $label, $role ) {
	if ( preg_match( '/basic life support/i', $label ) ) {
		return 'external';
	}
	if ( preg_match( '/food safety & hygiene/i', $label ) ) {
		return in_array( $role, array( 'catering', 'barista' ), true ) ? 'food1' : 'food2';
	}
	return 'course';
}

/** Training this candidate still needs, split into what HCC sells and what must be booked elsewhere. */
function hcc_pay_training_need( $id ) {
	$role  = hcc_meta( $id, 'role_key' );
	$items = hcc_items( $id, 'train' );
	$need  = array( 'buy' => array(), 'optional' => array(), 'external' => array() );
	foreach ( hcc_missing_training( $id ) as $k => $label ) {
		$kind = hcc_pay_course_kind( $label, $role );
		if ( 'external' === $kind ) {
			$need['external'][ $k ] = $label;
		} elseif ( ! empty( $items[ $k ]['optional'] ) ) {
			$need['optional'][ $k ] = array( $label, $kind );
		} else {
			$need['buy'][ $k ] = array( $label, $kind );
		}
	}
	return $need;
}

/** Order lines for a set of courses: the bundle when more than 2, otherwise priced one by one. */
function hcc_pay_training_lines( $id, $courses ) {
	$p      = hcc_pay_products();
	$bundle = hcc_pay_bundle_for( hcc_meta( $id, 'role_key' ) );
	if ( count( $courses ) > 2 && $bundle ) {
		return array( array( $bundle, $p[ $bundle ][0], $p[ $bundle ][1] ) );
	}
	$lines = array();
	$plain = 0;
	foreach ( $courses as $c ) {
		if ( 'course' === $c[1] ) {
			$plain++;
		} else {
			$lines[] = array( $c[1], $p[ $c[1] ][0], $p[ $c[1] ][1] );
		}
	}
	for ( ; $plain >= 2; $plain -= 2 ) {
		$lines[] = array( 'course2', $p['course2'][0], $p['course2'][1] );
	}
	if ( $plain ) {
		$lines[] = array( 'course1', $p['course1'][0], $p['course1'][1] );
	}
	return $lines;
}

/* ---------- Orders ---------- */

function hcc_orders( $id ) {
	return (array) hcc_meta( $id, 'orders', array() );
}

function hcc_orders_save( $id, $orders ) {
	hcc_set( $id, 'orders', array_values( $orders ) );
}

function hcc_order_total( $o ) {
	return array_sum( wp_list_pluck( $o['lines'], 2 ) );
}

function hcc_order_status_label( $o ) {
	$l = array( 'due' => 'Waiting for payment', 'claimed' => 'Paid, waiting for HCC to confirm', 'paid' => 'dbs' === $o['kind'] ? 'Payment confirmed · DBS being applied for' : 'Payment confirmed · courses being set up', 'done' => 'dbs' === $o['kind'] ? 'DBS applied for' : 'Courses assigned', 'cancelled' => 'Cancelled' );
	return $l[ $o['status'] ];
}

/** Open (not done or cancelled) order of a kind. */
function hcc_open_order( $id, $kind ) {
	foreach ( hcc_orders( $id ) as $o ) {
		if ( $o['kind'] === $kind && in_array( $o['status'], array( 'due', 'claimed', 'paid' ), true ) ) {
			return $o;
		}
	}
	return null;
}

function hcc_order_create( $id, $kind, $lines, $courses = array(), $by = 'System' ) {
	$orders   = hcc_orders( $id );
	$o        = array( 'id' => count( $orders ) + 1, 'kind' => $kind, 'lines' => $lines, 'courses' => $courses, 'status' => 'due', 'created' => time(), 'by' => $by, 'paid_lines' => array(), 'log' => array() );
	$orders[] = $o;
	hcc_orders_save( $id, $orders );
	$title = 'dbs' === $kind ? 'Pay for your DBS check (£' . hcc_order_total( $o ) . ')' : 'Pay for your training (£' . hcc_order_total( $o ) . ')';
	hcc_notify( $id, array( 'type' => 'action', 'title' => $title, 'text' => 'dbs' === $kind ? 'We apply for your enhanced DBS once you have paid.' : 'Courses: ' . implode( ', ', $courses ) . '. We set them up once you have paid.', 'link' => '#payments', 'key' => 'pay:' . $o['id'], 'todo' => true ) );
	hcc_log( $id, 'Payment requested: ' . $title, $by );
	return $o;
}

function hcc_order_update( $id, $oid, $changes, $log = '' ) {
	$orders = hcc_orders( $id );
	foreach ( $orders as $i => $o ) {
		if ( (int) $o['id'] === (int) $oid ) {
			$orders[ $i ] = array_merge( $o, $changes );
			if ( $log ) {
				$orders[ $i ]['log'][] = array( 'at' => time(), 'text' => $log );
			}
			hcc_orders_save( $id, $orders );
			return $orders[ $i ];
		}
	}
	return null;
}

/**
 * Work out what a submitted candidate has to pay for, and keep it up to date:
 * a DBS order when needed; a training order that follows the courses still missing (while it is
 * unpaid), and closes itself if nothing is missing any more.
 */
function hcc_pay_sync( $id ) {
	if ( ! hcc_meta( $id, 'submitted' ) || 'in_progress' !== hcc_state( $id ) ) {
		return;
	}
	$a = (array) hcc_meta( $id, 'answers', array() );
	if ( isset( $a['dbs'] ) && in_array( $a['dbs'], array( 'none', 'cert' ), true ) && ! hcc_meta( $id, 'pay_dbs_made' ) ) {
		$p = hcc_pay_products();
		hcc_order_create( $id, 'dbs', array( array( 'dbs', $p['dbs'][0], $p['dbs'][1] ) ) );
		hcc_set( $id, 'pay_dbs_made', time() );
	}
	if ( 'need' === hcc_uniform_state( $id ) && ! hcc_meta( $id, 'uniform_todo' ) ) {
		$u = hcc_uniform_for( $id );
		hcc_notify( $id, array( 'type' => 'action', 'title' => 'Get your ' . $u[0], 'text' => 'We don\'t supply uniforms. You\'ll need your own ' . $u[0] . ' before your first shift.', 'link' => '#payments', 'key' => 'uniform', 'todo' => true ) );
		hcc_set( $id, 'uniform_todo', time() );
	} elseif ( 'need' !== hcc_uniform_state( $id ) ) {
		hcc_todo_done( $id, 'uniform' );
	}
	$need    = hcc_pay_training_need( $id );
	$courses = wp_list_pluck( $need['buy'], 0 );
	$open    = hcc_open_order( $id, 'training' );
	if ( $open && 'due' === $open['status'] && 'System' === $open['by'] ) {
		if ( ! $courses ) {
			hcc_order_update( $id, $open['id'], array( 'status' => 'cancelled' ), 'No courses missing any more' );
			hcc_todo_done( $id, 'pay:' . $open['id'] );
		} elseif ( array_values( $open['courses'] ) !== array_values( $courses ) ) {
			hcc_order_update( $id, $open['id'], array( 'courses' => array_values( $courses ), 'lines' => hcc_pay_training_lines( $id, $need['buy'] ) ), 'Courses updated' );
		}
	} elseif ( ! $open && $courses && ! hcc_meta( $id, 'pay_train_made' ) ) {
		hcc_order_create( $id, 'training', hcc_pay_training_lines( $id, $need['buy'] ), array_values( $courses ) );
		hcc_set( $id, 'pay_train_made', time() );
	}
}

/* Keep orders in step: when the candidate opens their portal, and after staff updates or uploads. */
add_action( 'hcc_portal_before', 'hcc_pay_sync' );
add_action( 'hcc_item_uploaded', function ( $id ) {
	hcc_pay_sync( $id );
} );
add_action( 'hcc_registration_submitted', 'hcc_pay_sync' );

/* ---------- Candidate: "I've paid" ---------- */

add_action( 'admin_post_hcc_pay_claim', function () {
	$id = hcc_applicant_for_user( get_current_user_id() );
	if ( ! $id || ! check_admin_referer( 'hcc_pay_claim_' . $id ) ) {
		wp_die( 'Your session has expired. Please log in again.', 403 );
	}
	$oid  = isset( $_POST['order'] ) ? absint( $_POST['order'] ) : 0;
	$note = isset( $_POST['pay_note'] ) ? sanitize_text_field( wp_unslash( $_POST['pay_note'] ) ) : '';
	$how  = isset( $_POST['how'] ) && 'ayan' === $_POST['how'] ? 'Ayan Pay' : 'Monzo';
	foreach ( hcc_orders( $id ) as $o ) {
		if ( (int) $o['id'] === $oid && 'due' === $o['status'] ) {
			$o = hcc_order_update( $id, $oid, array( 'status' => 'claimed', 'claimed' => time(), 'how' => $how, 'note' => $note ), 'Candidate said they paid by ' . $how . ( $note ? ' (' . $note . ')' : '' ) );
			hcc_todo_done( $id, 'pay:' . $oid );
			hcc_log( $id, 'Candidate says they paid £' . hcc_order_total( $o ) . ' by ' . $how . ' for ' . ( 'dbs' === $o['kind'] ? 'the DBS check' : 'training' ) . ( $note ? ': ' . $note : '' ), 'Candidate' );
			hcc_mail( hcc_opt( 'email_jobs' ), '[PAYMENT] ' . hcc_meta( $id, 'ref' ) . ' ' . hcc_name( $id ) . ' says they paid £' . hcc_order_total( $o ) . ( 'dbs' === $o['kind'] ? ' (DBS)' : ' (training)' ),
				'<p>' . esc_html( hcc_name( $id ) ) . ' says they have paid <b>£' . esc_html( hcc_order_total( $o ) ) . '</b> by ' . esc_html( $how ) . ' for ' . ( 'dbs' === $o['kind'] ? 'their DBS check' : 'training: ' . esc_html( implode( ', ', $o['courses'] ) ) ) . '.</p>' . ( $note ? '<p>Their note: ' . esc_html( $note ) . '</p>' : '' ) . '<p>Payment reference to look for: <b>' . esc_html( hcc_meta( $id, 'ref' ) ) . '</b></p><p>Check the Monzo app, then confirm it in the Staff portal.</p>' . hcc_button( hcc_staff_url( array( 'view' => 'payments' ) ), 'Open Payments' ), array(), hcc_meta( $id, 'email' ) );
		}
	}
	wp_safe_redirect( hcc_portal_url() . '#payments' );
	exit;
} );

/* ---------- Candidate portal: Payments view ---------- */

add_filter( 'hcc_portal_nav', function ( $nav, $id, $base ) {
	if ( ! hcc_orders( $id ) && ! hcc_uniform_state( $id ) ) {
		return $nav;
	}
	$due = count( array_filter( hcc_orders( $id ), function ( $o ) { return 'due' === $o['status']; } ) ) + ( 'need' === hcc_uniform_state( $id ) ? 1 : 0 );
	array_splice( $nav, 4, 0, array( array( 'payments', 'Payments', 'card', $base . '#payments', $due ? $due : '', $due ? 'data-hot="1"' : '' ) ) );
	return $nav;
}, 20, 3 );

add_action( 'hcc_portal_views', function ( $id, $ctx ) {
	$orders = array_reverse( hcc_orders( $id ) );
	$ustate = hcc_uniform_state( $id );
	if ( ! $orders && ! $ustate ) {
		return;
	}
	$links = hcc_pay_links();
	$live  = 'portal' === $ctx;
	echo '<section class="view" data-view="payments" id="payments"><div class="view-head"><h2>Payments</h2><p>' . ( $orders ? 'Your DBS check and training. Pay by bank transfer to our Monzo Business account, then press "I\'ve paid".' : 'What you need before your first shift.' ) . '</p></div>';
	foreach ( $orders as $o ) {
		if ( 'cancelled' === $o['status'] ) {
			continue;
		}
		$step = array( 'due' => 1, 'claimed' => 2, 'paid' => 3, 'done' => 4 );
		$n    = $step[ $o['status'] ];
		echo '<div class="p-card checkout"><div class="co-head"><h3>' . ( 'dbs' === $o['kind'] ? 'DBS check' : 'Training' ) . '</h3><span class="co-status co-' . esc_attr( $o['status'] ) . '">' . esc_html( hcc_order_status_label( $o ) ) . '</span></div>';
		echo '<ol class="co-steps">';
		foreach ( array( 'Pay', 'Payment confirmed', 'dbs' === $o['kind'] ? 'DBS applied for' : 'Courses assigned' ) as $i => $s ) {
			echo '<li class="' . ( $n > $i + 1 || 4 === $n ? 'done' : ( $n === $i + 1 ? 'now' : '' ) ) . '"><span>' . ( $i + 1 ) . '</span>' . esc_html( $s ) . '</li>';
		}
		echo '</ol><div class="co-grid"><div class="co-order"><b class="co-t">Your order</b><ul>';
		foreach ( $o['lines'] as $l ) {
			echo '<li><span>' . esc_html( $l[1] ) . '</span><b>£' . esc_html( number_format( $l[2], 2 ) ) . '</b></li>';
		}
		echo '</ul><div class="co-total"><span>Total</span><b>£' . esc_html( number_format( hcc_order_total( $o ), 2 ) ) . '</b></div>';
		if ( 'training' === $o['kind'] && $o['courses'] ) {
			echo '<p class="co-courses"><b>Courses:</b> ' . esc_html( implode( ', ', $o['courses'] ) ) . '</p>';
		}
		echo '</div><div class="co-pay">';
		if ( 'due' === $o['status'] ) {
			echo '<b class="co-t">How to pay</b><p class="hint">Use your reference <b>' . esc_html( hcc_meta( $id, 'ref' ) ) . '</b> as the payment reference.</p>';
			foreach ( $o['lines'] as $l ) {
				$url = isset( $links[ $l[0] ] ) ? $links[ $l[0] ] : '';
				echo $url && $live ? '<a class="btn btn-sun co-btn" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">Pay £' . esc_html( number_format( $l[2], 2 ) ) . ' with Monzo ↗</a>' : '<span class="co-nolink">Pay £' . esc_html( number_format( $l[2], 2 ) ) . ': payment link coming soon. Call us on ' . esc_html( hcc_opt( 'phone' ) ) . '.</span>';
			}
			if ( ! empty( $links['ayan'] ) && $live ) {
				echo '<a class="btn btn-ghost co-btn" href="' . esc_url( $links['ayan'] ) . '" target="_blank" rel="noopener">Spread the cost with Ayan Pay ↗</a><small class="hint">Instalments are subject to approval by Ayan Pay.</small>';
			}
			if ( $live ) {
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="co-claim"><input type="hidden" name="action" value="hcc_pay_claim"><input type="hidden" name="order" value="' . (int) $o['id'] . '">' . wp_nonce_field( 'hcc_pay_claim_' . $id, '_wpnonce', true, false );
				echo '<label for="pn' . (int) $o['id'] . '">Anything we should know? (optional)</label><input type="text" id="pn' . (int) $o['id'] . '" name="pay_note" maxlength="200" placeholder="e.g. Paid from my partner\'s account">';
				if ( ! empty( $links['ayan'] ) ) {
					echo '<label class="consent"><input type="checkbox" name="how" value="ayan"> I paid with Ayan Pay</label>';
				}
				echo '<button class="btn btn-navy" type="submit">I\'ve paid</button></form>';
			}
		} elseif ( 'claimed' === $o['status'] ) {
			echo '<b class="co-t">Thank you</b><p>You told us you paid on ' . esc_html( wp_date( 'j M Y', (int) $o['claimed'] ) ) . '. We\'ll confirm it as soon as it reaches our account, usually within 1 working day.</p>';
		} elseif ( 'paid' === $o['status'] ) {
			echo '<b class="co-t">Payment confirmed</b><p>' . ( 'dbs' === $o['kind'] ? 'We are applying for your enhanced DBS. Look out for an email from the DBS service.' : 'We are setting up your courses. We\'ll send your login details soon.' ) . '</p>';
		} else {
			echo '<b class="co-t">All done</b><p>' . ( 'dbs' === $o['kind'] ? 'Your DBS application has been submitted.' : 'Your courses have been assigned. Complete them, then upload each certificate under Compliance & training.' ) . '</p>' . ( ! empty( $o['done_note'] ) ? '<p class="hint">' . esc_html( $o['done_note'] ) . '</p>' : '' );
		}
		echo '</div></div></div>';
	}
	if ( $ustate ) {
		$u = hcc_uniform_for( $id );
		echo '<div class="p-card checkout uniform-card"><div class="co-head"><h3>Uniform</h3><span class="co-status ' . ( 'need' === $ustate ? 'co-due">Needed before your first shift' : 'co-done">Done' ) . '</span></div>';
		if ( 'need' === $ustate ) {
			echo '<p>We don\'t supply uniforms. You told us you don\'t have a ' . esc_html( $u[0] ) . ' yet, so please buy one before your first shift. It\'s bought directly from the shop, not paid to us.</p><div class="form-actions" style="margin-top:12px">';
			echo $u[1] ? '<a class="btn btn-sun" href="' . esc_url( $u[1] ) . '" target="_blank" rel="noopener">Buy your ' . esc_html( $u[0] ) . ' on Amazon ↗</a>' : '<a class="btn btn-ghost" href="#messages">Ask us where to buy one</a>';
			if ( $live ) {
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline"><input type="hidden" name="action" value="hcc_uniform_got">' . wp_nonce_field( 'hcc_uniform_' . $id, '_wpnonce', true, false ) . '<button class="btn btn-ghost" type="submit">I\'ve got my uniform now</button></form>';
			}
			echo '</div>';
		} else {
			echo '<p>Thank you. You told us on ' . esc_html( wp_date( 'j M Y', (int) hcc_meta( $id, 'uniform_got' ) ) ) . ' that you now have your ' . esc_html( $u[0] ) . '.</p>';
		}
		echo '</div>';
	}
	$need = hcc_pay_training_need( $id );
	if ( $need['external'] ) {
		echo '<div class="p-card"><div class="p-card-head"><h2>Courses to book elsewhere</h2></div><p class="hint">We can\'t provide these online. Please book a face-to-face course, then upload the certificate under Compliance &amp; training: <b>' . esc_html( implode( ', ', $need['external'] ) ) . '</b>.</p></div>';
	}
	echo '</section>';
}, 10, 2 );

/* ---------- Staff portal → Payments ---------- */

add_filter( 'hcc_staff_views', function ( $v ) {
	$v[] = 'payments';
	return $v;
} );

function hcc_pay_all() {
	$rows = array();
	foreach ( array( 'in_progress', 'completed', 'closed' ) as $tab ) {
		foreach ( hcc_desk_list( $tab ) as $aid ) {
			foreach ( hcc_orders( $aid ) as $o ) {
				$o['aid'] = $aid;
				$rows[]   = $o;
			}
		}
	}
	usort( $rows, function ( $a, $b ) { return ( isset( $b['claimed'] ) ? $b['claimed'] : $b['created'] ) - ( isset( $a['claimed'] ) ? $a['claimed'] : $a['created'] ); } );
	return $rows;
}

add_filter( 'hcc_staff_nav', function ( $nav ) {
	$n     = hcc_cached_count( 'pay', function () { return count( array_filter( hcc_pay_all(), function ( $o ) { return in_array( $o['status'], array( 'claimed', 'paid' ), true ); } ) ); } );
	$nav[] = array( 'payments', 'Payments', 'card', hcc_staff_url( array( 'view' => 'payments' ) ), $n ? $n : '', $n ? 'data-hot="1"' : '' );
	return $nav;
}, 30 );

/** Staff actions for one order (used on the Payments page and in the candidate folder). */
function hcc_pay_staff_actions( $aid, $o, $back ) {
	$f = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="pay-act"><input type="hidden" name="action" value="hcc_pay_staff"><input type="hidden" name="a" value="' . (int) $aid . '"><input type="hidden" name="order" value="' . (int) $o['id'] . '"><input type="hidden" name="back" value="' . esc_attr( $back ) . '">' . wp_nonce_field( 'hcc_pay_staff_' . $aid, '_wpnonce', true, false );
	if ( in_array( $o['status'], array( 'due', 'claimed' ), true ) ) {
		$f .= '<button class="btn btn-sm ' . ( 'claimed' === $o['status'] ? 'btn-sun' : 'btn-ghost' ) . '" name="do" value="paid">Payment received</button>';
	}
	if ( 'paid' === $o['status'] ) {
		$f .= '<input type="text" name="done_note" placeholder="' . ( 'dbs' === $o['kind'] ? 'DBS application reference (optional)' : 'Note, e.g. VideoTile login sent (optional)' ) . '"><button class="btn btn-sm btn-navy" name="do" value="done">' . ( 'dbs' === $o['kind'] ? 'DBS applied for' : 'Courses assigned' ) . '</button>';
	}
	if ( in_array( $o['status'], array( 'due', 'claimed' ), true ) ) {
		$f .= '<button class="link-btn" name="do" value="cancel" onclick="return confirm(\'Cancel this payment request? The candidate will not be asked to pay it.\')">Cancel request</button>';
	}
	return $f . '</form>';
}

add_action( 'admin_post_hcc_pay_staff', function () {
	$aid = isset( $_POST['a'] ) ? absint( $_POST['a'] ) : 0;
	if ( ! $aid || ! hcc_is_staff() || ! check_admin_referer( 'hcc_pay_staff_' . $aid ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	$me   = hcc_staff_name();
	$do   = isset( $_POST['do'] ) ? sanitize_key( $_POST['do'] ) : '';
	$oid  = isset( $_POST['order'] ) ? absint( $_POST['order'] ) : 0;
	$back = isset( $_POST['back'] ) && 'folder' === $_POST['back'] ? hcc_staff_url( array( 'candidate' => $aid ) ) . '#payments-card' : hcc_staff_url( array( 'view' => 'payments' ) );
	if ( 'request' === $do ) {
		$kind = isset( $_POST['kind'] ) && 'dbs' === $_POST['kind'] ? 'dbs' : 'training';
		$p    = hcc_pay_products();
		if ( 'dbs' === $kind ) {
			hcc_order_create( $aid, 'dbs', array( array( 'dbs', $p['dbs'][0], $p['dbs'][1] ) ), array(), $me );
		} else {
			$need = hcc_pay_training_need( $aid );
			if ( $need['buy'] ) {
				hcc_order_create( $aid, 'training', hcc_pay_training_lines( $aid, $need['buy'] ), array_values( wp_list_pluck( $need['buy'], 0 ) ), $me );
			}
		}
		hcc_email_pay_request( $aid );
		wp_safe_redirect( add_query_arg( 'msg', 'pay_req', $back ) );
		exit;
	}
	foreach ( hcc_orders( $aid ) as $o ) {
		if ( (int) $o['id'] !== $oid ) {
			continue;
		}
		$what = 'dbs' === $o['kind'] ? 'DBS check' : 'training';
		if ( 'paid' === $do && in_array( $o['status'], array( 'due', 'claimed' ), true ) ) {
			hcc_order_update( $aid, $oid, array( 'status' => 'paid', 'paid' => time(), 'paid_by' => $me ), 'Payment confirmed by ' . $me );
			hcc_todo_done( $aid, 'pay:' . $oid );
			hcc_notify( $aid, array( 'type' => 'update', 'title' => 'Payment received for your ' . $what, 'text' => 'dbs' === $o['kind'] ? 'Thank you. We are now applying for your enhanced DBS.' : 'Thank you. We are now setting up your courses.', 'link' => '#payments', 'key' => 'paid:' . $oid ) );
			hcc_mail( hcc_meta( $aid, 'email' ), 'Payment received: ' . ucfirst( $what ), '<p>Hi ' . esc_html( hcc_meta( $aid, 'first' ) ) . ',</p><p>We have received your payment of <b>£' . esc_html( hcc_order_total( $o ) ) . '</b> for your ' . esc_html( $what ) . '. ' . ( 'dbs' === $o['kind'] ? 'We are now applying for your enhanced DBS.' : 'We are now setting up your courses and will send your login details.' ) . '</p>' . hcc_button( hcc_portal_url() . '#payments', 'Open my portal' ), array(), hcc_opt( 'email_jobs' ) );
			hcc_log( $aid, 'Payment confirmed: £' . hcc_order_total( $o ) . ' for ' . $what );
		}
		if ( 'done' === $do && 'paid' === $o['status'] ) {
			$note = isset( $_POST['done_note'] ) ? sanitize_text_field( wp_unslash( $_POST['done_note'] ) ) : '';
			hcc_order_update( $aid, $oid, array( 'status' => 'done', 'done' => time(), 'done_by' => $me, 'done_note' => $note ), ( 'dbs' === $o['kind'] ? 'DBS applied for' : 'Courses assigned' ) . ' by ' . $me . ( $note ? ': ' . $note : '' ) );
			hcc_notify( $aid, array( 'type' => 'dbs' === $o['kind'] ? 'update' : 'action', 'title' => 'dbs' === $o['kind'] ? 'Your DBS application has been submitted' : 'Your courses are ready', 'text' => 'dbs' === $o['kind'] ? 'Look out for an email from the DBS service.' : 'Complete each course, then upload your certificates.' . ( $note ? ' ' . $note : '' ), 'link' => 'dbs' === $o['kind'] ? '#payments' : '#training', 'key' => 'dbs' === $o['kind'] ? 'dbsdone:' . $oid : 'courses:' . $oid, 'todo' => 'training' === $o['kind'] ) );
			hcc_log( $aid, ( 'dbs' === $o['kind'] ? 'DBS applied for' : 'Courses assigned' ) . ( $note ? ': ' . $note : '' ) );
		}
		if ( 'cancel' === $do && in_array( $o['status'], array( 'due', 'claimed' ), true ) ) {
			hcc_order_update( $aid, $oid, array( 'status' => 'cancelled', 'cancelled_by' => $me ), 'Cancelled by ' . $me );
			hcc_todo_done( $aid, 'pay:' . $oid );
			hcc_log( $aid, 'Payment request cancelled: ' . $what );
		}
	}
	wp_safe_redirect( add_query_arg( 'msg', 'pay_' . $do, $back ) );
	exit;
} );

/** Email the candidate that there is something to pay. */
function hcc_email_pay_request( $id ) {
	return hcc_mail( hcc_meta( $id, 'email' ), 'Action needed: payment for your registration', '<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>To finish your registration, there is a payment to make in your portal. It shows exactly what is included and how to pay.</p>' . hcc_button( hcc_portal_url() . '#payments', 'See what to pay' ), array(), hcc_opt( 'email_jobs' ) );
}

add_filter( 'hcc_staff_msgs', function ( $m ) {
	return array_merge( $m, array( 'pay_paid' => 'Payment confirmed. The candidate has been told.', 'pay_done' => 'Marked as done. The candidate has been told.', 'pay_cancel' => 'Payment request cancelled.', 'pay_req' => 'Payment requested. The candidate has been emailed.', 'pay_links' => 'Payment links saved.' ) );
} );

add_action( 'hcc_staff_view_payments', function () {
	$all  = hcc_pay_all();
	$tabs = array( 'claimed' => 'To confirm', 'paid' => 'Paid: action needed', 'due' => 'Waiting for payment', 'done' => 'Completed', 'cancelled' => 'Cancelled' );
	$tab  = isset( $_GET['tab'] ) && isset( $tabs[ $_GET['tab'] ] ) ? sanitize_key( $_GET['tab'] ) : 'claimed'; // phpcs:ignore
	$rows = array_filter( $all, function ( $o ) use ( $tab ) { return $o['status'] === $tab; } );
	?>
	<div class="s-head" style="margin-top:0"><div><h1>Payments</h1><p>DBS checks and training paid through Monzo. When a candidate says they've paid, check the Monzo app for their reference, then press "Payment received".</p></div></div>
	<nav class="d-tabs" aria-label="Payment status">
		<?php foreach ( $tabs as $k => $l ) : $n = count( array_filter( $all, function ( $o ) use ( $k ) { return $o['status'] === $k; } ) ); ?>
			<a<?php echo $tab === $k ? ' aria-selected="true"' : ''; ?> href="<?php echo esc_url( hcc_staff_url( array( 'view' => 'payments', 'tab' => $k ) ) ); ?>"><?php echo esc_html( $l . ' (' . $n . ')' ); ?></a>
		<?php endforeach; ?>
	</nav>
	<div class="p-card">
		<?php if ( $rows ) : ?>
		<div class="d-table pay-tbl"><div class="d-row d-hd"><span>Candidate</span><span>For</span><span>Amount</span><span>When</span><span>Action</span></div>
			<?php foreach ( $rows as $o ) : $aid = $o['aid']; ?>
				<div class="d-row">
					<span><b><a href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $aid ) ) ); ?>#payments-card"><?php echo esc_html( hcc_name( $aid ) ); ?></a></b><small><?php echo esc_html( hcc_meta( $aid, 'ref' ) . ' · ' . hcc_role_name( hcc_meta( $aid, 'role_key' ) ) ); ?></small></span>
					<span data-l="For"><?php echo esc_html( 'dbs' === $o['kind'] ? 'DBS check' : 'Training' ); ?><small><?php echo esc_html( 'training' === $o['kind'] ? implode( ', ', $o['courses'] ) : '' ); ?></small><?php echo ! empty( $o['note'] ) ? '<small class="pay-note">"' . esc_html( $o['note'] ) . '"</small>' : ''; ?></span>
					<span data-l="Amount"><b>£<?php echo esc_html( number_format( hcc_order_total( $o ), 2 ) ); ?></b><small><?php echo esc_html( ! empty( $o['how'] ) ? $o['how'] : '' ); ?></small></span>
					<span data-l="When"><?php echo esc_html( 'claimed' === $o['status'] ? 'Paid ' . hcc_ago( (int) $o['claimed'] ) : ( 'paid' === $o['status'] ? 'Confirmed ' . hcc_ago( (int) $o['paid'] ) . ' by ' . $o['paid_by'] : ( 'done' === $o['status'] ? hcc_ago( (int) $o['done'] ) . ' by ' . $o['done_by'] : 'Requested ' . hcc_ago( (int) $o['created'] ) ) ) ); ?><?php echo 'done' === $o['status'] && ! empty( $o['done_note'] ) ? '<small>' . esc_html( $o['done_note'] ) . '</small>' : ''; ?></span>
					<span><?php echo hcc_pay_staff_actions( $aid, $o, 'payments' ); // phpcs:ignore ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<?php else : ?><p class="hint">Nothing here.</p><?php endif; ?>
	</div>
	<?php if ( hcc_is_manager() ) : $links = hcc_pay_links(); ?>
	<details class="p-card" style="margin-top:18px"<?php echo array_filter( $links ) ? '' : ' open'; ?>>
		<summary class="p-card-head"><h2>Payment links</h2><span class="p-count">Managers only · Monzo payment links and Amazon uniform links</span></summary>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pay-links">
			<input type="hidden" name="action" value="hcc_pay_links"><?php wp_nonce_field( 'hcc_pay_links' ); ?>
			<?php foreach ( hcc_pay_products() as $k => $p ) : ?>
				<label><span><?php echo esc_html( $p[0] . ' · £' . $p[1] ); ?></span><input type="url" name="links[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $links[ $k ] ); ?>" placeholder="https://monzo.me/..."></label>
			<?php endforeach; ?>
			<label><span>Uniform: blue tunics (Amazon link, care roles)</span><input type="url" name="links[uniform_care]" value="<?php echo esc_attr( $links['uniform_care'] ); ?>" placeholder="https://www.amazon.co.uk/..."></label>
			<label><span>Uniform: chef whites (Amazon link, kitchen roles)</span><input type="url" name="links[uniform_hosp]" value="<?php echo esc_attr( $links['uniform_hosp'] ); ?>" placeholder="https://www.amazon.co.uk/..."></label>
			<label><span>Ayan Pay (spread the cost, optional)</span><input type="url" name="links[ayan]" value="<?php echo esc_attr( $links['ayan'] ); ?>" placeholder="Leave empty to hide this option"></label>
			<div class="form-actions"><button class="btn btn-navy btn-sm" type="submit">Save links</button></div>
		</form>
	</details>
	<?php endif;
} );

add_action( 'admin_post_hcc_pay_links', function () {
	if ( ! hcc_is_manager() ) {
		wp_die( 'Not allowed.', 403 );
	}
	check_admin_referer( 'hcc_pay_links' );
	$in  = isset( $_POST['links'] ) ? (array) wp_unslash( $_POST['links'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$out = array();
	foreach ( array_keys( hcc_pay_links() ) as $k ) {
		$out[ $k ] = isset( $in[ $k ] ) ? esc_url_raw( trim( $in[ $k ] ) ) : '';
	}
	update_option( 'hcc_pay_links', $out, false );
	wp_safe_redirect( hcc_staff_url( array( 'view' => 'payments', 'msg' => 'pay_links' ) ) );
	exit;
} );

/* Candidate folder: Payments card. */
add_action( 'hcc_staff_folder_side', function ( $aid ) {
	hcc_pay_sync( $aid );
	$orders = array_reverse( hcc_orders( $aid ) );
	$need   = hcc_pay_training_need( $aid );
	echo '<details open class="p-sec p-card" id="payments-card"><summary class="p-card-head"><h2>Payments</h2><span class="p-count">DBS and training</span></summary>';
	foreach ( $orders as $o ) {
		echo '<div class="pay-item pay-' . esc_attr( $o['status'] ) . '"><div><b>' . esc_html( ( 'dbs' === $o['kind'] ? 'DBS check' : 'Training' ) . ' · £' . number_format( hcc_order_total( $o ), 2 ) ) . '</b><small>' . esc_html( hcc_order_status_label( $o ) . ( 'training' === $o['kind'] ? ' · ' . implode( ', ', $o['courses'] ) : '' ) ) . '</small></div>' . hcc_pay_staff_actions( $aid, $o, 'folder' ) . '</div>'; // phpcs:ignore
	}
	if ( ! $orders ) {
		echo '<p class="hint">No payments requested.</p>';
	}
	$f = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="pay-req"><input type="hidden" name="action" value="hcc_pay_staff"><input type="hidden" name="do" value="request"><input type="hidden" name="a" value="' . (int) $aid . '"><input type="hidden" name="back" value="folder">' . wp_nonce_field( 'hcc_pay_staff_' . $aid, '_wpnonce', true, false );
	if ( ! hcc_open_order( $aid, 'dbs' ) ) {
		$f .= '<button class="btn btn-sm btn-ghost" name="kind" value="dbs">Ask to pay for a DBS check (£' . hcc_pay_products()['dbs'][1] . ')</button>';
	}
	if ( ! hcc_open_order( $aid, 'training' ) && $need['buy'] ) {
		$f .= '<button class="btn btn-sm btn-ghost" name="kind" value="training">Ask to pay for training (' . count( $need['buy'] ) . ' course' . ( count( $need['buy'] ) > 1 ? 's' : '' ) . ')</button>';
	}
	echo $f . '</form>'; // phpcs:ignore
	$us = hcc_uniform_state( $aid );
	if ( $us ) {
		echo '<p class="hint" style="margin-top:8px">Uniform: ' . ( 'need' === $us ? '<b>does not have a ' . esc_html( hcc_uniform_for( $aid )[0] ) . '</b> (shown where to buy one)' : 'bought one since registering (' . esc_html( wp_date( 'j M', (int) hcc_meta( $aid, 'uniform_got' ) ) ) . ')' ) . '</p>'; // phpcs:ignore
	}
	if ( $need['external'] ) {
		echo '<p class="hint" style="margin-top:8px">To book face to face (not sold online): ' . esc_html( implode( ', ', $need['external'] ) ) . '</p>';
	}
	echo '</details>';
} );
