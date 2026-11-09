<?php
/**
 * Expiry dates: training certificates, NMC registration and visas.
 *
 * - Candidates enter the expiry date when they upload a certificate (or their NMC renewal date).
 * - Staff confirm it, or correct it, in the candidate folder ("Confirm date"). A date changed by the
 *   candidate afterwards needs confirming again. Visa expiry dates are typed in by staff, so they
 *   count as confirmed.
 * - Reminders go out at 30, 14 and 2 days before, and when it has expired: a notification and a
 *   to-do in the candidate portal, plus one email listing everything due.
 * - Staff portal → Expiring soon lists every date coming up, with a report to download (Excel or PDF)
 *   for the rows ticked.
 */

defined( 'ABSPATH' ) || exit;

/** Reminder stage for a number of days left: 0 (expired or today), 2, 14, 30, or '' (later). */
function hcc_exp_stage( $days ) {
	if ( $days <= 0 ) {
		return 0;
	}
	foreach ( array( 2, 14, 30 ) as $s ) {
		if ( $days <= $s ) {
			return $s;
		}
	}
	return '';
}

function hcc_days_left( $date ) {
	$today = new DateTimeImmutable( 'today', wp_timezone() );
	$d     = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, wp_timezone() );
	return $d ? (int) $today->diff( $d )->format( '%r%a' ) : null;
}

/** Text and colour for a number of days left. */
function hcc_exp_badge( $days ) {
	if ( null === $days ) {
		return '';
	}
	if ( $days < 0 ) {
		return '<span class="exp-b exp-gone">Expired ' . abs( $days ) . ' day' . ( -1 === $days ? '' : 's' ) . ' ago</span>';
	}
	if ( 0 === $days ) {
		return '<span class="exp-b exp-gone">Expires today</span>';
	}
	$cls = $days <= 2 ? 'exp-2' : ( $days <= 14 ? 'exp-14' : ( $days <= 30 ? 'exp-30' : 'exp-ok' ) );
	return '<span class="exp-b ' . $cls . '">' . $days . ' day' . ( 1 === $days ? '' : 's' ) . ' left</span>';
}

/**
 * Every expiry date for a candidate.
 * Each: key, label, exp (Y-m-d), days, ok (confirmed), by, at, kind (training, nmc, visa).
 */
function hcc_expiries( $id ) {
	$out   = array();
	$items = hcc_all_items( $id );
	foreach ( $items as $k => $it ) {
		$st = hcc_file_state( $id, $k );
		if ( empty( $st['exp'] ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $st['exp'] ) ) {
			continue;
		}
		$ok    = isset( $st['exp_ok'] ) && is_array( $st['exp_ok'] ) ? $st['exp_ok'] : array();
		$out[] = array(
			'key'   => $k,
			'label' => 'nmc_pin' === $k ? 'NMC registration' : ( isset( $it['short'] ) ? $it['short'] : $it['label'] ),
			'exp'   => $st['exp'],
			'days'  => hcc_days_left( $st['exp'] ),
			'ok'    => (bool) $ok,
			'by'    => isset( $ok['by'] ) ? $ok['by'] : '',
			'at'    => isset( $ok['at'] ) ? (int) $ok['at'] : 0,
			'kind'  => 0 === strpos( $k, 't-' ) ? 'training' : ( 'nmc_pin' === $k ? 'nmc' : 'document' ),
		);
	}
	$checks = (array) hcc_meta( $id, 'checks', array() );
	if ( ! empty( $checks['rtw']['visa_exp'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $checks['rtw']['visa_exp'] ) ) {
		$out[] = array( 'key' => 'visa', 'label' => 'Visa / right to work', 'exp' => $checks['rtw']['visa_exp'], 'days' => hcc_days_left( $checks['rtw']['visa_exp'] ), 'ok' => true, 'by' => isset( $checks['rtw']['by'] ) ? $checks['rtw']['by'] : '', 'at' => 0, 'kind' => 'visa' );
	}
	usort( $out, function ( $a, $b ) { return strcmp( $a['exp'], $b['exp'] ); } );
	return $out;
}

/* A date entered or changed by the candidate must be checked again, and its reminders start over. */
add_filter( 'hcc_item_changes', function ( $clean, $id, $key ) {
	if ( array_key_exists( 'exp', $clean ) ) {
		$st = hcc_file_state( $id, $key );
		if ( ( isset( $st['exp'] ) ? $st['exp'] : null ) !== $clean['exp'] ) {
			$clean['exp_ok']   = null;
			$clean['exp_sent'] = null;
		}
	}
	return $clean;
}, 10, 3 );

/* A new certificate uploaded: the expiry to-do for it is done (the candidate enters the new date). */
add_action( 'hcc_item_uploaded', function ( $id, $key ) {
	hcc_todo_done( $id, 'exp:' . $key );
}, 10, 2 );

/* ---------- Staff: confirm or correct a date (candidate folder) ---------- */

add_action( 'hcc_desk_item_verb', function ( $id, $verb, $key, $items ) {
	if ( 'expok' !== $verb || ! isset( $items[ $key ] ) ) {
		return;
	}
	$date = isset( $_POST['expv'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['expv'][ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		hcc_desk_back( $id, array( 'msg' => 'exp_bad' ), 'item-' . $key );
	}
	$st  = hcc_file_state( $id, $key );
	$old = isset( $st['exp'] ) ? $st['exp'] : '';
	$ch  = array( 'exp' => $date, 'exp_ok' => array( 'by' => hcc_staff_name(), 'at' => time() ) );
	if ( $old !== $date ) {
		$ch['exp_sent'] = null;
	}
	hcc_update_item( $id, $key, $ch );
	$label = $items[ $key ]['label'];
	hcc_log( $id, $old && $old !== $date ? 'Corrected the expiry date of ' . $label . ' from ' . wp_date( 'd/m/Y', strtotime( $old ) ) . ' to ' . wp_date( 'd/m/Y', strtotime( $date ) ) : 'Confirmed the expiry date of ' . $label . ': ' . wp_date( 'd/m/Y', strtotime( $date ) ) );
	hcc_desk_back( $id, array( 'msg' => 'exp_ok' ), 'item-' . $key );
}, 10, 4 );

/** The expiry line and confirm control for a row in the candidate folder. */
function hcc_exp_staff_line( $id, $key, $st, $it ) {
	$is_cert = 0 === strpos( $key, 't-' ) || 'nmc_pin' === $key;
	if ( ! $is_cert || ( empty( $st['exp'] ) && ! hcc_has_upload( $st ) && 'nmc_pin' !== $key ) ) {
		return '';
	}
	$exp = isset( $st['exp'] ) ? $st['exp'] : '';
	$ok  = isset( $st['exp_ok'] ) && is_array( $st['exp_ok'] ) ? $st['exp_ok'] : array();
	$h   = '<span class="exp-line">';
	if ( $exp ) {
		$h .= 'Expires <b>' . esc_html( wp_date( 'd/m/Y', strtotime( $exp ) ) ) . '</b> ' . hcc_exp_badge( hcc_days_left( $exp ) ) . ' ';
		$h .= $ok ? '<span class="exp-ok-tag">✓ Confirmed by ' . esc_html( $ok['by'] . ', ' . wp_date( 'j M', (int) $ok['at'] ) ) . '</span>' : '<span class="exp-wait-tag">Date not checked yet</span>';
	} else {
		$h .= '<span class="exp-wait-tag">No expiry date entered</span>';
	}
	$h .= '<span class="exp-fix"><input type="date" name="expv[' . esc_attr( $key ) . ']" value="' . esc_attr( $exp ) . '" aria-label="Expiry date for ' . esc_attr( $it['label'] ) . '"><button type="submit" class="btn btn-sm ' . ( $ok ? 'btn-ghost' : 'btn-navy' ) . '" name="act" value="expok:' . esc_attr( $key ) . '">' . ( $ok ? 'Update date' : ( $exp ? 'Confirm date' : 'Save date' ) ) . '</button></span>';
	return $h . '</span>';
}

/* ---------- Reminders (once a day) ---------- */

function hcc_exp_reminders_run() {
	$q = new WP_Query( array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true, 'meta_query' => array( array( 'key' => '_hcc_submitted', 'compare' => 'EXISTS' ) ) ) );
	$sent = 0;
	foreach ( $q->posts as $id ) {
		if ( 'closed' === hcc_state( $id ) ) {
			continue;
		}
		$due = array();
		foreach ( hcc_expiries( $id ) as $e ) {
			$stage = null === $e['days'] ? '' : hcc_exp_stage( $e['days'] );
			if ( '' === $stage ) {
				continue;
			}
			if ( 'visa' === $e['key'] ) {
				$done = (array) hcc_meta( $id, 'visa_exp_sent', array() );
			} else {
				$st   = hcc_file_state( $id, $e['key'] );
				$done = isset( $st['exp_sent'] ) ? (array) $st['exp_sent'] : array();
			}
			if ( in_array( $stage, $done, true ) ) {
				continue;
			}
			// Mark this stage and the earlier ones, so a date entered late only gets the latest reminder.
			$done = array_values( array_unique( array_merge( $done, array_filter( array( 30, 14, 2, 0 ), function ( $s ) use ( $stage ) { return $s >= $stage; } ) ) ) );
			if ( 'visa' === $e['key'] ) {
				hcc_set( $id, 'visa_exp_sent', $done );
			} else {
				hcc_update_item( $id, $e['key'], array( 'exp_sent' => $done ) );
			}
			$when  = wp_date( 'j M Y', strtotime( $e['exp'] ) );
			$title = $e['days'] < 0 ? 'Your ' . $e['label'] . ' has expired' : ( 0 === $e['days'] ? 'Your ' . $e['label'] . ' expires today' : 'Your ' . $e['label'] . ' expires in ' . $e['days'] . ' day' . ( 1 === $e['days'] ? '' : 's' ) );
			$text  = ( $e['days'] < 0 ? 'It expired on ' . $when . '.' : 'It expires on ' . $when . '.' ) . ( 'visa' === $e['key'] ? ' Please send us your new visa or share code.' : ' Please renew it and upload your new certificate.' );
			hcc_notify( $id, array( 'type' => 'reminder', 'title' => $title, 'text' => $text, 'link' => 'visa' === $e['key'] ? '#item-rtw' : '#item-' . $e['key'], 'key' => 'exp:' . $e['key'], 'todo' => true ) );
			$due[] = array( $title, $text );
			hcc_log( $id, 'Expiry reminder sent: ' . $e['label'] . ' (' . ( $e['days'] < 0 ? 'expired' : $e['days'] . ' days left' ) . ')', 'System' );
		}
		if ( $due ) {
			hcc_email_expiry( $id, $due );
			$sent++;
		}
	}
	return $sent;
}

function hcc_email_expiry( $id, $due ) {
	$list = '';
	foreach ( $due as $d ) {
		$list .= '<li><b>' . esc_html( $d[0] ) . '</b><br>' . esc_html( $d[1] ) . '</li>';
	}
	return hcc_mail( hcc_meta( $id, 'email' ), count( $due ) > 1 ? 'Reminder: some of your certificates are expiring' : 'Reminder: ' . $due[0][0],
		'<p>Hi ' . esc_html( hcc_meta( $id, 'first' ) ) . ',</p><p>To keep working with HCC-Temps, your certificates need to stay in date:</p><ul>' . $list . '</ul><p>When you have the new certificate, upload it in your portal and enter its new expiry date.</p>'
			. hcc_button( hcc_portal_url() . '#training', 'Open my portal' ), array(), hcc_opt( 'email_jobs' ) );
}

add_action( 'hcc_daily', function () {
	$today = wp_date( 'Y-m-d' );
	if ( get_option( 'hcc_exp_last_run' ) === $today || (int) wp_date( 'G' ) < 8 ) {
		return; // once a day, from 8am
	}
	update_option( 'hcc_exp_last_run', $today, false );
	hcc_exp_reminders_run();
}, 30 );

/* ---------- Candidate portal ---------- */

function hcc_exp_table( $id, $rows ) {
	$h = '<div class="exp-table">';
	foreach ( $rows as $e ) {
		$h .= '<a class="exp-row" href="#' . esc_attr( 'visa' === $e['key'] ? 'item-rtw' : 'item-' . $e['key'] ) . '"><span class="exp-name">' . esc_html( $e['label'] ) . '<small>' . ( $e['ok'] ? 'Checked by HCC' : 'Waiting for HCC to check the date' ) . '</small></span><span class="exp-date">' . esc_html( wp_date( 'j M Y', strtotime( $e['exp'] ) ) ) . '</span>' . hcc_exp_badge( $e['days'] ) . '</a>';
	}
	return $h . '</div>';
}

add_action( 'hcc_portal_dashboard', function ( $id ) {
	$soon = array_filter( hcc_expiries( $id ), function ( $e ) { return null !== $e['days'] && $e['days'] <= 30; } );
	if ( $soon ) {
		echo '<div class="p-card exp-card"><div class="p-card-head"><h2>Expiring soon</h2><a class="link-arrow" href="#training">All dates <span aria-hidden="true">→</span></a></div>' . hcc_exp_table( $id, $soon ) . '</div>'; // phpcs:ignore
	}
}, 15 );

add_action( 'hcc_portal_training_top', function ( $id ) {
	$all = hcc_expiries( $id );
	echo '<div class="p-card"><div class="p-card-head"><h2>Expiry dates</h2><span class="p-count">We remind you 30, 14 and 2 days before</span></div>';
	echo $all ? hcc_exp_table( $id, $all ) : '<p class="hint">When you upload a certificate, enter its expiry date. It will show here, and we\'ll remind you before it runs out.</p>'; // phpcs:ignore
	echo '</div>';
} );

/* ---------- Staff portal → Expiring soon ---------- */

add_filter( 'hcc_staff_views', function ( $v ) {
	$v[] = 'expiry';
	return $v;
} );

/** Rows for the Expiring soon page: every candidate who is not closed. */
function hcc_exp_all_rows() {
	$rows = array();
	foreach ( array( 'in_progress', 'completed' ) as $tab ) {
		foreach ( hcc_desk_list( $tab ) as $aid ) {
			foreach ( hcc_expiries( $aid ) as $e ) {
				$e['aid']   = $aid;
				$e['state'] = $tab;
				$rows[]     = $e;
			}
		}
	}
	usort( $rows, function ( $a, $b ) { return strcmp( $a['exp'], $b['exp'] ); } );
	return $rows;
}

function hcc_exp_filter( $rows, $win, $who, $search, $unchecked ) {
	return array_values( array_filter( $rows, function ( $r ) use ( $win, $who, $search, $unchecked ) {
		if ( 'expired' === $win && $r['days'] >= 0 ) {
			return false;
		}
		if ( is_numeric( $win ) && $r['days'] > (int) $win ) {
			return false;
		}
		if ( 'all' !== $who && $r['state'] !== $who ) {
			return false;
		}
		if ( $unchecked && $r['ok'] ) {
			return false;
		}
		if ( $search ) {
			$hay = strtolower( hcc_name( $r['aid'] ) . ' ' . hcc_meta( $r['aid'], 'ref' ) . ' ' . hcc_role_name( hcc_meta( $r['aid'], 'role_key' ) ) . ' ' . $r['label'] );
			if ( false === strpos( $hay, strtolower( $search ) ) ) {
				return false;
			}
		}
		return true;
	} ) );
}

function hcc_exp_counts() {
	static $c = null;
	if ( null === $c ) {
		$c = array( 'soon' => 0, 'urgent' => 0 );
		foreach ( hcc_exp_all_rows() as $r ) {
			$c['soon']   += $r['days'] <= 30 ? 1 : 0;
			$c['urgent'] += $r['days'] <= 2 ? 1 : 0;
		}
	}
	return $c;
}

add_filter( 'hcc_staff_nav', function ( $nav ) {
	$c     = hcc_exp_counts();
	$nav[] = array( 'expiry', 'Expiring soon', 'cal', hcc_staff_url( array( 'view' => 'expiry' ) ), $c['soon'] ? $c['soon'] : '', $c['urgent'] ? 'data-hot="1"' : '' );
	return $nav;
}, 20 );

add_action( 'hcc_staff_view_expiry', function () {
	$win       = isset( $_GET['win'] ) && in_array( $_GET['win'], array( 'expired', '2', '14', '30', '60', '90', 'all' ), true ) ? sanitize_key( $_GET['win'] ) : '30'; // phpcs:ignore
	$who       = isset( $_GET['who'] ) && in_array( $_GET['who'], array( 'all', 'in_progress', 'completed' ), true ) ? sanitize_key( $_GET['who'] ) : 'all'; // phpcs:ignore
	$search    = hcc_field_get( 'q' );
	$unchecked = ! empty( $_GET['unchecked'] ); // phpcs:ignore
	$rows      = hcc_exp_filter( hcc_exp_all_rows(), $win, $who, $search, $unchecked );
	$wins      = array( 'expired' => 'Expired', '2' => 'Within 2 days', '14' => 'Within 14 days', '30' => 'Within 30 days', '60' => 'Within 60 days', '90' => 'Within 90 days', 'all' => 'All dates' );
	?>
	<div class="s-head" style="margin-top:0"><div><h1>Expiring soon</h1><p>Certificates, NMC registrations and visas coming up for renewal. Tick rows and download a report, or download everything shown.</p></div></div>
	<form class="exp-filters p-card" method="get" action="<?php echo esc_url( hcc_staff_url() ); ?>">
		<input type="hidden" name="view" value="expiry">
		<label>Show<select name="win" onchange="this.form.submit()"><?php foreach ( $wins as $k => $l ) : ?><option value="<?php echo esc_attr( $k ); ?>"<?php selected( $win, (string) $k ); ?>><?php echo esc_html( $l ); ?></option><?php endforeach; ?></select></label>
		<label>Candidates<select name="who" onchange="this.form.submit()"><option value="all"<?php selected( $who, 'all' ); ?>>All (not closed)</option><option value="completed"<?php selected( $who, 'completed' ); ?>>Ready to work</option><option value="in_progress"<?php selected( $who, 'in_progress' ); ?>>In onboarding</option></select></label>
		<label class="consent" style="align-self:end;margin-bottom:10px"><input type="checkbox" name="unchecked" value="1"<?php checked( $unchecked ); ?> onchange="this.form.submit()">Only dates not checked yet</label>
		<label>Search<input type="search" name="q" value="<?php echo esc_attr( $search ); ?>" placeholder="Name, reference, course"></label>
		<button class="btn btn-ghost btn-sm" type="submit" style="align-self:end">Apply</button>
	</form>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="p-card exp-list" id="expForm">
		<input type="hidden" name="action" value="hcc_exp_report"><?php wp_nonce_field( 'hcc_exp_report' ); ?>
		<input type="hidden" name="win" value="<?php echo esc_attr( $win ); ?>"><input type="hidden" name="who" value="<?php echo esc_attr( $who ); ?>"><input type="hidden" name="q" value="<?php echo esc_attr( $search ); ?>"><input type="hidden" name="unchecked" value="<?php echo $unchecked ? 1 : 0; ?>">
		<div class="exp-bar"><b><?php echo count( $rows ); ?> date<?php echo 1 === count( $rows ) ? '' : 's'; ?></b><span class="exp-sel" id="expSel">None ticked: the report will include all <?php echo count( $rows ); ?></span>
			<span class="exp-dl"><button class="btn btn-sm btn-navy" name="format" value="xlsx" type="submit"<?php disabled( ! $rows ); ?>>Download Excel</button><button class="btn btn-sm btn-ghost" name="format" value="pdf" type="submit"<?php disabled( ! $rows ); ?>>Download PDF</button></span></div>
		<?php if ( $rows ) : ?>
		<div class="d-table exp-tbl">
			<div class="d-row d-hd"><span><label class="s-cb"><input type="checkbox" id="expAll" aria-label="Tick all"></label></span><span>Candidate</span><span>Certificate</span><span>Expires</span><span>Days left</span><span>Date checked</span><span></span></div>
			<?php foreach ( $rows as $r ) : ?>
				<div class="d-row<?php echo $r['days'] <= 2 ? ' exp-urgent' : ''; ?>">
					<span><label class="s-cb"><input type="checkbox" name="rows[]" value="<?php echo esc_attr( $r['aid'] . '|' . $r['key'] ); ?>" aria-label="Tick <?php echo esc_attr( hcc_name( $r['aid'] ) . ' ' . $r['label'] ); ?>"></label></span>
					<span><b><?php echo esc_html( hcc_name( $r['aid'] ) ); ?></b><small><?php echo esc_html( hcc_meta( $r['aid'], 'ref' ) . ' · ' . hcc_role_name( hcc_meta( $r['aid'], 'role_key' ) ) . ' · ' . ( 'completed' === $r['state'] ? 'Ready to work' : 'In onboarding' ) ); ?></small></span>
					<span data-l="Certificate"><?php echo esc_html( $r['label'] ); ?></span>
					<span data-l="Expires"><?php echo esc_html( wp_date( 'd/m/Y', strtotime( $r['exp'] ) ) ); ?></span>
					<span data-l="Days left"><?php echo hcc_exp_badge( $r['days'] ); // phpcs:ignore ?></span>
					<span data-l="Date checked"><?php echo $r['ok'] ? '<span class="exp-ok-tag">✓ ' . esc_html( $r['by'] ? $r['by'] : 'Yes' ) . '</span>' : '<span class="exp-wait-tag">Not checked</span>'; // phpcs:ignore ?></span>
					<span><a class="btn btn-sm btn-ghost" href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $r['aid'] ) ) . '#item-' . ( 'visa' === $r['key'] ? 'rtw' : $r['key'] ) ); ?>">Open</a></span>
				</div>
			<?php endforeach; ?>
		</div>
		<?php else : ?><p class="hint">Nothing to show for these filters.</p><?php endif; ?>
	</form>
	<?php
} );

/* Dashboard alert. */
add_action( 'hcc_staff_dash_top', function () {
	$c = hcc_exp_counts();
	if ( $c['soon'] ) {
		echo '<a class="exp-alert' . ( $c['urgent'] ? ' hot' : '' ) . '" href="' . esc_url( hcc_staff_url( array( 'view' => 'expiry' ) ) ) . '">' . hcc_icon( 'cal', 20 ) . '<span><b>' . (int) $c['soon'] . ' date' . ( 1 === $c['soon'] ? '' : 's' ) . ' expiring within 30 days</b>' . ( $c['urgent'] ? ' · ' . (int) $c['urgent'] . ' expired or within 2 days' : '' ) . '</span><span class="link-arrow">See them →</span></a>'; // phpcs:ignore
	}
} );

/* ---------- Reports ---------- */

add_action( 'admin_post_hcc_exp_report', function () {
	if ( ! hcc_is_staff() ) {
		wp_die( 'Not allowed.', 403 );
	}
	check_admin_referer( 'hcc_exp_report' );
	$f    = function ( $k ) { return isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : ''; }; // phpcs:ignore WordPress.Security.NonceVerification
	$rows = hcc_exp_filter( hcc_exp_all_rows(), $f( 'win' ) ? $f( 'win' ) : '30', $f( 'who' ) ? $f( 'who' ) : 'all', $f( 'q' ), (bool) $f( 'unchecked' ) );
	$pick = isset( $_POST['rows'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['rows'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
	if ( $pick ) {
		$rows = array_values( array_filter( $rows, function ( $r ) use ( $pick ) { return in_array( $r['aid'] . '|' . $r['key'], $pick, true ); } ) );
	}
	$head = array( 'Candidate', 'Reference', 'Role', 'Status', 'Email', 'Phone', 'Certificate', 'Expires', 'Days left', 'Date checked by' );
	$data = array();
	foreach ( $rows as $r ) {
		$data[] = array( hcc_name( $r['aid'] ), hcc_meta( $r['aid'], 'ref' ), hcc_role_name( hcc_meta( $r['aid'], 'role_key' ) ), 'completed' === $r['state'] ? 'Ready to work' : 'In onboarding', hcc_meta( $r['aid'], 'email' ), hcc_meta( $r['aid'], 'phone' ), $r['label'], wp_date( 'd/m/Y', strtotime( $r['exp'] ) ), (int) $r['days'], $r['ok'] ? ( $r['by'] ? $r['by'] : 'Yes' ) : 'Not checked' );
	}
	$name = 'HCC expiring certificates ' . wp_date( 'Y-m-d' );
	if ( 'pdf' === $f( 'format' ) ) {
		hcc_exp_pdf( $name, $head, $data, $rows );
	}
	hcc_xlsx_download( $name, 'Expiring', $head, $data, array( 24, 13, 22, 15, 30, 16, 30, 12, 10, 18 ) );
} );

/** A simple Excel file (.xlsx) built by hand: one sheet, bold header row, frozen header, filters. */
function hcc_xlsx_download( $name, $sheet, $head, $rows, $widths = array() ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		wp_die( 'Excel downloads are not available on this server.' );
	}
	$x    = function ( $s ) { return htmlspecialchars( (string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8' ); };
	$col  = function ( $i ) { $s = ''; for ( $i++; $i > 0; $i = intdiv( $i - 1, 26 ) ) { $s = chr( 65 + ( $i - 1 ) % 26 ) . $s; } return $s; };
	$xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
	if ( $widths ) {
		$xml .= '<cols>';
		foreach ( $widths as $i => $w ) {
			$xml .= '<col min="' . ( $i + 1 ) . '" max="' . ( $i + 1 ) . '" width="' . (float) $w . '" customWidth="1"/>';
		}
		$xml .= '</cols>';
	}
	$xml .= '<sheetData>';
	foreach ( array_merge( array( $head ), $rows ) as $r => $row ) {
		$xml .= '<row r="' . ( $r + 1 ) . '">';
		foreach ( array_values( $row ) as $c => $v ) {
			$ref = $col( $c ) . ( $r + 1 );
			$xml .= ( is_int( $v ) || is_float( $v ) ) ? '<c r="' . $ref . '"><v>' . $v . '</v></c>' : '<c r="' . $ref . '" t="inlineStr"' . ( 0 === $r ? ' s="1"' : '' ) . '><is><t xml:space="preserve">' . $x( $v ) . '</t></is></c>';
		}
		$xml .= '</row>';
	}
	$xml .= '</sheetData><autoFilter ref="A1:' . $col( count( $head ) - 1 ) . ( count( $rows ) + 1 ) . '"/></worksheet>';
	$tmp = wp_tempnam( 'hcc-xlsx' );
	$zip = new ZipArchive();
	$zip->open( $tmp, ZipArchive::OVERWRITE );
	$zip->addFromString( '[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>' );
	$zip->addFromString( '_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>' );
	$zip->addFromString( 'xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . $x( $sheet ) . '" sheetId="1" r:id="rId1"/></sheets><definedNames><definedName name="_xlnm._FilterDatabase" localSheetId="0" hidden="1">\'' . $x( $sheet ) . '\'!$A$1:$' . $col( count( $head ) - 1 ) . '$' . ( count( $rows ) + 1 ) . '</definedName></definedNames></workbook>' );
	$zip->addFromString( 'xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>' );
	$zip->addFromString( 'xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF171A45"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>' );
	$zip->addFromString( 'xl/worksheets/sheet1.xml', $xml );
	$zip->close();
	nocache_headers();
	header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
	header( 'Content-Disposition: attachment; filename="' . $name . '.xlsx"' );
	header( 'Content-Length: ' . filesize( $tmp ) );
	readfile( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	unlink( $tmp );
	exit;
}

/** Load the bundled PDF library (dompdf, LGPL) and make a PDF from HTML. Returns the PDF bytes. */
function hcc_pdf( $html, $landscape = false ) {
	require_once get_template_directory() . '/lib/pdf/autoload.php';
	$tmp = trailingslashit( get_temp_dir() );
	$opt = new \Dompdf\Options();
	$opt->set( array( 'isRemoteEnabled' => false, 'tempDir' => $tmp, 'fontCache' => $tmp, 'chroot' => array( get_template_directory(), $tmp ), 'defaultFont' => 'DejaVu Sans' ) );
	$pdf = new \Dompdf\Dompdf( $opt );
	$pdf->loadHtml( $html, 'UTF-8' );
	$pdf->setPaper( 'A4', $landscape ? 'landscape' : 'portrait' );
	$pdf->render();
	return $pdf->output();
}

/** The logo as an embedded image, for PDFs. */
function hcc_pdf_logo( $width = 120 ) {
	$f = get_template_directory() . '/assets/img/logo.png';
	return is_readable( $f ) ? '<img src="data:image/png;base64,' . base64_encode( file_get_contents( $f ) ) . '" style="width:' . (int) $width . 'px">' : ''; // phpcs:ignore
}

function hcc_exp_pdf( $name, $head, $data, $rows ) {
	$cols = array( 0, 1, 2, 3, 6, 7, 8, 9 );
	$h    = '<html><head><meta charset="utf-8"><style>body{font-family:"DejaVu Sans",sans-serif;font-size:9.5px;color:#1E2140}h1{font-size:17px;color:#171A45;margin:6px 0 2px}p{margin:0 0 10px;color:#5B6078}table{width:100%;border-collapse:collapse}th{background:#171A45;color:#fff;text-align:left;padding:6px 5px;font-size:9px}td{padding:5px;border-bottom:1px solid #E1E7E6;vertical-align:top}.r2 td{background:#FDECEB}.r14 td{background:#FFF4E5}.d{font-weight:bold}.foot{position:fixed;bottom:-10px;left:0;right:0;font-size:8px;color:#5B6078}</style></head><body>';
	$h   .= hcc_pdf_logo( 110 ) . '<h1>Expiring certificates</h1><p>Generated ' . esc_html( wp_date( 'j F Y, H:i' ) ) . ' by ' . esc_html( hcc_staff_name() ) . ' · ' . count( $data ) . ' row' . ( 1 === count( $data ) ? '' : 's' ) . '</p><table><thead><tr>';
	foreach ( $cols as $c ) {
		$h .= '<th>' . esc_html( $head[ $c ] ) . '</th>';
	}
	$h .= '</tr></thead><tbody>';
	foreach ( $data as $i => $row ) {
		$d  = $rows[ $i ]['days'];
		$h .= '<tr class="' . ( $d <= 2 ? 'r2' : ( $d <= 14 ? 'r14' : '' ) ) . '">';
		foreach ( $cols as $c ) {
			$v  = 8 === $c ? ( $d < 0 ? 'Expired' : $d ) : $row[ $c ];
			$h .= '<td' . ( 8 === $c ? ' class="d"' : '' ) . '>' . esc_html( $v ) . '</td>';
		}
		$h .= '</tr>';
	}
	$h  .= '</tbody></table><div class="foot">HCC-Temps · Confidential: contains personal information. Do not share outside HCC-Temps.</div></body></html>';
	$out = hcc_pdf( $h, true );
	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . $name . '.pdf"' );
	header( 'Content-Length: ' . strlen( $out ) );
	echo $out; // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
}
