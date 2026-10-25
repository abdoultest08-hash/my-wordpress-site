<?php
/**
 * Template Name: Staff portal
 */
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( get_permalink() ), hcc_login_url( true ) ) );
	exit;
}
if ( ! hcc_is_staff() ) {
	wp_safe_redirect( hcc_portal_url() );
	exit;
}
$cid  = isset( $_GET['candidate'] ) ? absint( $_GET['candidate'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
$view = $cid && 'hcc_applicant' === get_post_type( $cid ) ? 'folder' : ( isset( $_GET['view'] ) && 'team' === $_GET['view'] ? 'team' : 'dash' ); // phpcs:ignore WordPress.Security.NonceVerification
$msg  = isset( $_GET['msg'] ) ? sanitize_key( $_GET['msg'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$msgs = array(
	'approved' => 'Approved. Added to the update for the candidate.', 'queued' => 'Added to the update for the candidate. Press "Preview and send" when you are ready.', 'note' => 'Saved to the profile.', 'sent' => 'Update email sent to the candidate.',
	'check' => 'Check saved.', 'check_result' => 'Choose a result before saving the check.', 'ref' => 'Reference details saved.', 'need_note' => 'Please add a note before marking this complete.', 'override_done' => 'Items marked complete.',
	'finished' => 'Onboarding finished. Moved to Staff completed.', 'cant_finish' => 'Finish needs: profile 100%, declaration sent and signed, and no unsent updates.', 'closed' => 'Application closed and moved to Withdrawn / not qualified.', 'reopened' => 'Application reopened.',
	'close_need' => 'Add a reason and your name to close the application.', 'staff_added' => 'Staff member added. They have been emailed a link to set their password.', 'staff_exists' => 'There is already an account with that email.', 'error' => 'Something went wrong. Please try again.', 'nothing' => 'Tick at least one item first.',
);
$post = esc_url( admin_url( 'admin-post.php' ) );
$me   = hcc_staff_name();
$role_label = function ( $id ) { return hcc_role_name( hcc_meta( $id, 'role_key' ) ); };
get_header();
?>
<main class="staff-page">
	<div class="staff-bar">
		<div class="wrap">
			<b>Staff portal</b>
			<nav aria-label="Staff portal">
				<a href="<?php echo esc_url( hcc_staff_url() ); ?>"<?php echo 'dash' === $view ? ' aria-current="page"' : ''; ?>>Dashboard</a>
				<?php if ( hcc_is_manager() ) : ?><a href="<?php echo esc_url( hcc_staff_url( array( 'view' => 'team' ) ) ); ?>"<?php echo 'team' === $view ? ' aria-current="page"' : ''; ?>>Team</a><?php endif; ?>
			</nav>
			<span class="who-in"><?php echo esc_html( wp_get_current_user()->display_name ); ?> · <a href="<?php echo esc_url( wp_logout_url( hcc_login_url( true ) ) ); ?>">Log out</a></span>
		</div>
	</div>

	<section class="section staff-body">
		<div class="wrap">
			<?php if ( isset( $msgs[ $msg ] ) ) : ?><p class="<?php echo in_array( $msg, array( 'cant_finish', 'need_note', 'close_need', 'check_result', 'staff_exists', 'error', 'nothing' ), true ) ? 'form-alert' : 'form-ok'; ?>" role="status"><?php echo esc_html( $msgs[ $msg ] ); ?></p><?php endif; ?>

<?php if ( 'dash' === $view ) :
	$search = hcc_field_get( 'q' );
	$tab    = in_array( hcc_field_get( 'tab' ), array( 'in_progress', 'completed', 'closed' ), true ) ? hcc_field_get( 'tab' ) : 'in_progress';
	$lists  = array( 'in_progress' => hcc_desk_list( 'in_progress', $search ), 'completed' => hcc_desk_list( 'completed', $search ), 'closed' => hcc_desk_list( 'closed', $search ) );
	$all_in = $search ? hcc_desk_list( 'in_progress' ) : $lists['in_progress'];
	$unsent = array_filter( $all_in, function ( $id ) { return (bool) hcc_meta( $id, 'queue' ); } );
	$review = array_filter( $all_in, function ( $id ) { return hcc_meta( $id, 'submitted' ) && hcc_counts( $id, hcc_all_items( $id ) )['review'] > 0; } );
	$done30 = array_filter( hcc_desk_list( 'completed' ), function ( $id ) { $c = hcc_meta( $id, 'completed' ); return is_array( $c ) && $c['at'] > time() - 30 * DAY_IN_SECONDS; } );
	$days   = array();
	foreach ( hcc_desk_list( 'completed' ) as $id ) {
		$c = hcc_meta( $id, 'completed' );
		$days[] = max( 0, ( $c['at'] - hcc_started( $id ) ) / DAY_IN_SECONDS );
	}
	?>
			<div class="s-head" style="margin-top:0">
				<div><h1>Dashboard</h1><p>Candidates in onboarding, updates waiting to be sent, and completed staff.</p></div>
				<div class="s-actions"><a class="btn btn-ghost btn-sm" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hcc_export&tab=' . $tab ), 'hcc_export' ) ); ?>">Export to Excel (CSV)</a></div>
			</div>
			<div class="kpis kpis-5">
				<div><b><?php echo count( $all_in ); ?></b><span>In progress</span></div>
				<div><b><?php echo count( $review ); ?></b><span>With files to review</span></div>
				<div class="<?php echo $unsent ? 'warn' : ''; ?>"><b><?php echo count( $unsent ); ?></b><span>Candidates with updates not sent</span></div>
				<div><b><?php echo count( $done30 ); ?></b><span>Completed (30 days)</span></div>
				<div><b><?php echo $days ? esc_html( number_format( array_sum( $days ) / count( $days ), 1 ) ) . ' days' : '—'; ?></b><span>Average start to sign-off</span></div>
			</div>

			<div class="p-card unsent<?php echo $unsent ? ' has' : ''; ?>" id="unsent">
				<div class="p-card-head"><h2>Updates not sent <span class="tray-n"><?php echo count( $unsent ); ?></span></h2><span class="p-count">Changes made in a candidate folder but not emailed yet</span></div>
				<?php if ( $unsent ) : ?>
					<div class="d-table"><div class="d-row d-hd"><span>Candidate</span><span>Changes</span><span>Made by</span><span>Waiting</span><span></span></div>
					<?php foreach ( $unsent as $id ) : $q = (array) hcc_meta( $id, 'queue' ); $first = reset( $q ); ?>
						<div class="d-row"><span><b><?php echo esc_html( hcc_name( $id ) ); ?></b><small><?php echo esc_html( hcc_meta( $id, 'ref' ) . ' · ' . $role_label( $id ) ); ?></small></span><span data-l="Changes"><?php echo count( $q ); ?> change<?php echo count( $q ) > 1 ? 's' : ''; ?></span><span data-l="Made by"><?php echo esc_html( implode( ', ', array_unique( wp_list_pluck( $q, 'by' ) ) ) ); ?></span><span data-l="Waiting" class="<?php echo time() - $first['at'] > DAY_IN_SECONDS ? 'late' : ''; ?>"><?php echo esc_html( hcc_ago( $first['at'] ) ); ?></span><span><a class="btn btn-sun btn-sm" href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $id ) ) ); ?>#tray">Open and send</a></span></div>
					<?php endforeach; ?></div>
				<?php else : ?><p class="hint">All updates have been sent.</p><?php endif; ?>
			</div>

			<form class="d-tabs" method="get" action="<?php echo esc_url( hcc_staff_url() ); ?>">
				<?php foreach ( array( 'in_progress' => 'In progress', 'completed' => 'Staff completed', 'closed' => 'Withdrawn / not qualified' ) as $k => $l ) : ?>
					<a role="tab" aria-selected="<?php echo $k === $tab ? 'true' : 'false'; ?>" href="<?php echo esc_url( add_query_arg( array_filter( array( 'tab' => $k, 'q' => $search ) ), hcc_staff_url() ) ); ?>"><?php echo esc_html( $l ); ?> (<?php echo count( $lists[ $k ] ); ?>)</a>
				<?php endforeach; ?>
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">
				<input type="search" name="q" value="<?php echo esc_attr( $search ); ?>" placeholder="Search name, reference, role, email or phone" aria-label="Search candidates">
			</form>
			<div class="p-card"><div class="d-table">
				<?php if ( 'in_progress' === $tab ) : ?>
					<div class="d-row d-hd d-prog"><span>Candidate</span><span>Started</span><span>Stage</span><span>Profile</span><span>Missing</span><span>Last activity</span></div>
					<?php foreach ( $lists['in_progress'] as $id ) :
						$c   = hcc_counts( $id, hcc_all_items( $id ) );
						$pct = hcc_progress( $id );
						$dup = ! hcc_meta( $id, 'dup_ok' ) && hcc_duplicates( $id );
						$stage = hcc_meta( $id, 'submitted' ) ? hcc_meta( $id, 'stage', 'Documents' ) : 'Registering (step ' . (int) hcc_meta( $id, 'step', 1 ) . ' of 7)';
						?>
						<a class="d-row d-prog" href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $id ) ) ); ?>"><span><b><?php echo esc_html( hcc_name( $id ) ); ?></b><?php echo $dup ? ' <em class="dup-tag">possible duplicate</em>' : ''; ?><?php echo hcc_meta( $id, 'queue' ) ? ' <em class="unsent-tag">update not sent</em>' : ''; ?><small><?php echo esc_html( hcc_meta( $id, 'ref' ) . ' · ' . $role_label( $id ) ); ?></small></span><span data-l="Started"><?php echo esc_html( wp_date( 'j M Y', hcc_started( $id ) ) ); ?></span><span data-l="Stage"><?php echo esc_html( $stage ); ?></span><span data-l="Profile"><span class="mini-bar"><i style="width:<?php echo (int) $pct; ?>%"></i></span><?php echo (int) $pct; ?>%</span><span data-l="Missing"><?php echo (int) $c['todo']; ?></span><span data-l="Last activity"><?php echo esc_html( hcc_ago( hcc_last_activity( $id ) ) ); ?></span></a>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="d-row d-hd d-done"><span>Candidate</span><span>Application started</span><span><?php echo 'completed' === $tab ? 'Signed off' : 'Closed'; ?></span><span><?php echo 'completed' === $tab ? 'Days' : 'Outcome'; ?></span><span>Signed off by</span><span></span></div>
					<?php foreach ( $lists[ $tab ] as $id ) :
						$e = hcc_meta( $id, 'completed' === $tab ? 'completed' : 'closed' );
						$e = is_array( $e ) ? $e : array( 'at' => 0, 'by' => '' );
						?>
						<div class="d-row d-done"><span><b><?php echo esc_html( hcc_name( $id ) ); ?></b><small><?php echo esc_html( hcc_meta( $id, 'ref' ) . ' · ' . $role_label( $id ) . ( ! empty( $e['why'] ) ? ' · ' . $e['why'] : '' ) ); ?></small></span><span data-l="Started"><?php echo esc_html( wp_date( 'j M Y', hcc_started( $id ) ) ); ?></span><span data-l="<?php echo 'completed' === $tab ? 'Signed off' : 'Closed'; ?>"><?php echo esc_html( wp_date( 'j M Y', (int) $e['at'] ) ); ?></span><span data-l="<?php echo 'completed' === $tab ? 'Days' : 'Outcome'; ?>"><?php echo esc_html( 'completed' === $tab ? max( 0, round( ( $e['at'] - hcc_started( $id ) ) / DAY_IN_SECONDS ) ) : $e['outcome'] ); ?></span><span data-l="By"><?php echo esc_html( $e['by'] ); ?></span><span><a class="link-btn" href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $id ) ) ); ?>">Open file</a></span></div>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if ( ! $lists[ $tab ] ) : ?><p class="hint" style="padding:12px 6px">No candidates here<?php echo $search ? ' matching "' . esc_html( $search ) . '"' : ''; ?>.</p><?php endif; ?>
			</div></div>

<?php elseif ( 'team' === $view && hcc_is_manager() ) : ?>
			<div class="s-head" style="margin-top:0"><div><h1>Team</h1><p>People who can log in to the Staff portal. Managers can add staff and see bank details in full.</p></div></div>
			<div class="staff-grid">
				<div class="p-card"><div class="d-table">
					<div class="d-row d-hd"><span>Name</span><span>Email</span><span>Access</span><span></span><span></span></div>
					<?php foreach ( get_users( array( 'role__in' => array( 'hcc_staff', 'hcc_manager', 'administrator' ), 'orderby' => 'display_name' ) ) as $u ) : ?>
						<div class="d-row"><span><b><?php echo esc_html( $u->display_name ); ?></b></span><span><?php echo esc_html( $u->user_email ); ?></span><span><?php echo esc_html( in_array( 'hcc_staff', $u->roles, true ) ? 'Staff' : ( in_array( 'administrator', $u->roles, true ) ? 'Admin' : 'Manager' ) ); ?></span><span></span><span></span></div>
					<?php endforeach; ?>
				</div></div>
				<form class="p-card" method="post" action="<?php echo $post; ?>">
					<input type="hidden" name="action" value="hcc_add_staff"><?php wp_nonce_field( 'hcc_add_staff' ); ?>
					<div class="p-card-head"><h2>Add a staff member</h2></div>
					<div class="form-grid one">
						<div class="f"><label for="sf">First name</label><input type="text" id="sf" name="first" required></div>
						<div class="f"><label for="sl">Last name</label><input type="text" id="sl" name="last"></div>
						<div class="f"><label for="se">Email</label><input type="email" id="se" name="email" required></div>
						<fieldset class="f"><legend>Access</legend><div class="radios"><label class="radio"><input type="radio" name="level" value="staff" checked><span>Staff</span></label><label class="radio"><input type="radio" name="level" value="manager"><span>Manager</span></label></div></fieldset>
					</div>
					<div class="form-actions"><button class="btn btn-navy btn-sm" type="submit">Add and email invite</button></div>
				</form>
			</div>

<?php else :
	$id      = $cid;
	$a       = (array) hcc_meta( $id, 'answers', array() );
	$state   = hcc_state( $id );
	$pct     = hcc_progress( $id );
	$queue   = (array) hcc_meta( $id, 'queue', array() );
	$review  = (array) hcc_meta( $id, 'review', array() );
	$dnotes  = (array) hcc_meta( $id, 'dnotes', array() );
	$dupes   = hcc_meta( $id, 'dup_ok' ) ? array() : hcc_duplicates( $id );
	$hidden  = '<input type="hidden" name="action" value="hcc_desk"><input type="hidden" name="candidate" value="' . (int) $id . '">' . wp_nonce_field( 'hcc_desk_' . $id, '_wpnonce', true, false );
	$ovkeys  = array_filter( array_map( 'sanitize_key', explode( ',', hcc_field_get( 'override' ) ) ) );
	$items   = hcc_all_items( $id );
	$closed  = hcc_meta( $id, 'closed' );
	$completed = hcc_meta( $id, 'completed' );
	?>
			<div class="crumbs" style="color:var(--muted)"><a href="<?php echo esc_url( hcc_staff_url() ); ?>">Dashboard</a> / <?php echo esc_html( hcc_name( $id ) ); ?></div>
			<div class="s-head">
				<div>
					<h1><?php echo esc_html( hcc_name( $id ) ); ?></h1>
					<div class="pct"><div class="progress"><i style="width:<?php echo (int) $pct; ?>%"></i></div><b>Profile <?php echo (int) $pct; ?>% complete</b></div>
					<p><?php echo esc_html( $role_label( $id ) ); ?> · <b><?php echo esc_html( hcc_meta( $id, 'ref' ) ); ?></b> · Registered <?php echo esc_html( wp_date( 'j M Y, H:i', hcc_started( $id ) ) ); ?> · <?php echo esc_html( hcc_meta( $id, 'email' ) ); ?> · <?php echo esc_html( hcc_meta( $id, 'phone' ) ); ?><?php echo hcc_meta( $id, 'submitted' ) ? '' : ' · <b>Still registering (step ' . (int) hcc_meta( $id, 'step', 1 ) . ' of 7)</b>'; ?></p>
				</div>
				<div class="s-actions">
					<form method="post" action="<?php echo $post; ?>" class="s-stage"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="stage"><label>Stage <select name="stage" onchange="this.form.submit()"><?php foreach ( hcc_stage_options() as $o ) : ?><option<?php selected( hcc_meta( $id, 'stage' ), $o ); ?>><?php echo esc_html( $o ); ?></option><?php endforeach; ?></select></label><noscript><button class="btn btn-ghost btn-sm" type="submit">Set</button></noscript></form>
					<?php if ( 'in_progress' === $state ) : ?>
					<form method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="approve_all"><button class="btn btn-aqua btn-sm" type="submit">Approve all uploaded</button></form>
					<form method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="complete_all"><button class="btn btn-sun btn-sm" type="submit">Complete profile (100%)</button></form>
					<?php endif; ?>
					<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hcc_zip&a=' . $id ), 'hcc_zip_' . $id ) ); ?>">Download all (zip)</a>
					<a class="btn btn-ghost btn-sm" target="_blank" rel="noopener" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=hcc_report&a=' . $id ), 'hcc_report_' . $id ) ); ?>">Candidate report</a>
					<a class="btn btn-ghost btn-sm" target="_blank" rel="noopener" href="<?php echo esc_url( add_query_arg( 'preview', $id, hcc_portal_url() ) ); ?>">View as candidate</a>
					<?php if ( $closed ) : ?>
						<form method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="reopen"><button class="btn btn-ghost btn-sm btn-red" type="submit">Reopen application</button></form>
					<?php elseif ( ! $completed ) : ?>
						<a class="btn btn-ghost btn-sm btn-red" href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $id, 'closing' => 1 ) ) ); ?>#close">Withdrawn / not qualified</a>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $dupes ) : ?>
				<div class="dup-card"><b>Possible duplicate</b>
					<?php foreach ( $dupes as $dp ) : ?><span>Same <?php echo esc_html( implode( ' and ', $dp['on'] ) ); ?> as <a href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $dp['id'] ) ) ); ?>"><?php echo esc_html( hcc_name( $dp['id'] ) . ' (' . hcc_meta( $dp['id'], 'ref' ) . ')' ); ?></a> · <?php echo esc_html( array( 'in_progress' => 'In progress', 'completed' => 'Staff completed', 'closed' => 'Withdrawn / not qualified' )[ hcc_state( $dp['id'] ) ] ); ?></span><?php endforeach; ?>
					<form method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="dup_ok"><button class="link-btn" type="submit">Checked: not a duplicate</button></form>
				</div>
			<?php endif; ?>

			<?php if ( hcc_field_get( 'closing' ) && ! $closed ) : ?>
				<form class="p-card close-panel" id="close" method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="close">
					<div class="p-card-head"><h2>Close this application</h2><a class="link-btn" href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $id ) ) ); ?>">Cancel</a></div>
					<p class="hint">Moves the candidate to <b>Withdrawn / not qualified</b> on the dashboard. The profile and files are kept until the retention period ends.</p>
					<div class="form-grid">
						<fieldset class="f full"><legend>Outcome</legend><div class="radios"><?php foreach ( array( 'Withdrawn by candidate', 'Not qualified', 'Not suitable' ) as $i => $o ) : ?><label class="radio"><input type="radio" name="outcome" value="<?php echo esc_attr( $o ); ?>"<?php checked( 0, $i ); ?>><span><?php echo esc_html( $o ); ?></span></label><?php endforeach; ?></div></fieldset>
						<div class="f full"><label for="why">Reason</label><textarea id="why" name="why" required placeholder="e.g. No NMC registration; candidate took a permanent job."></textarea></div>
						<div class="f"><label for="cby">Signed off by</label><input type="text" id="cby" name="by" value="<?php echo esc_attr( $me ); ?>" required></div>
						<div class="f"><label for="cdate">Date</label><input type="date" id="cdate" name="date" value="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>"></div>
					</div>
					<div class="form-actions"><button class="btn btn-navy" type="submit">Close application</button></div>
				</form>
			<?php endif; ?>

			<?php if ( $ovkeys && isset( $items[ $ovkeys[0] ] ) ) : $ok = $ovkeys[0]; ?>
				<form class="p-card ov-panel" id="override" method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="override"><input type="hidden" name="keys" value="<?php echo esc_attr( implode( ',', $ovkeys ) ); ?>">
					<div class="p-card-head"><h2>Mark complete (override)</h2><span class="p-count">Item 1 of <?php echo count( $ovkeys ); ?></span></div>
					<p class="hint">Each item needs a reason and a note. Your name, the reason and the note are saved in the activity log.</p>
					<div class="ov-item"><?php echo esc_html( $items[ $ok ]['label'] ); ?></div>
					<div class="form-grid" style="margin-top:12px">
						<div class="f"><label for="ovr">Reason</label><select id="ovr" name="reason"><?php foreach ( hcc_override_reasons() as $o ) : ?><option><?php echo esc_html( $o ); ?></option><?php endforeach; ?></select></div>
						<div class="f full"><label for="ovn">Note (required)</label><textarea id="ovn" name="note" placeholder="e.g. Original passport checked in the Leeds office, copy on file."></textarea></div>
					</div>
					<div class="form-actions"><button class="btn btn-navy" type="submit"><?php echo count( $ovkeys ) > 1 ? 'Confirm and next' : 'Confirm'; ?></button><button class="link-btn" type="submit" name="skip" value="1">Skip this item</button><a class="link-btn" href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $id ) ) ); ?>">Cancel</a></div>
				</form>
			<?php endif; ?>

			<?php if ( 'closed' === $state ) : ?>
				<div class="finish-card closed"><div><b><?php echo esc_html( $closed['outcome'] ); ?></b><span><?php echo esc_html( 'Closed ' . $closed['date'] . ' by ' . $closed['by'] . '. ' . $closed['why'] ); ?></span></div></div>
			<?php elseif ( 'completed' === $state ) : ?>
				<div class="finish-card complete"><div><b>Onboarding complete</b><span><?php echo esc_html( 'Signed off by ' . $completed['by'] . ' on ' . wp_date( 'j M Y, H:i', (int) $completed['at'] ) . '. Moved to Staff completed.' ); ?></span></div></div>
			<?php else :
				$can = hcc_can_finish( $id ); ?>
				<form class="finish-card<?php echo $can ? ' ready' : ''; ?>" method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="finish">
					<div><b>Finish onboarding</b><span><?php echo esc_html( implode( '   ', array( ( 100 === $pct ? '✓' : '✗' ) . ' Profile ' . $pct . '%', ( hcc_meta( $id, 'dec_sent' ) ? '✓' : '✗' ) . ' Declaration sent', ( hcc_meta( $id, 'dec_signed' ) ? '✓' : '✗' ) . ' Signed declaration received', ( $queue ? '✗ ' . count( $queue ) . ' update(s) not sent' : '✓ No unsent updates' ) ) ) ); ?></span></div>
					<div class="finish-act"><input type="text" name="by" value="<?php echo esc_attr( $me ); ?>" aria-label="Signed off by"><button class="btn btn-navy btn-sm" type="submit"<?php disabled( ! $can ); ?>>Finish onboarding and move to Staff completed</button></div>
				</form>
			<?php endif; ?>

			<div class="sec-toggle"><button class="link-btn" type="button" data-sections="open">Expand all</button> · <button class="link-btn" type="button" data-sections="close">Collapse all</button></div>
			<div class="staff-grid">
				<div>
					<form method="post" action="<?php echo $post; ?>" id="documents" class="items-form"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="items">
					<?php foreach ( array( 'docs' => 'Documents', 'train' => 'Training certificates' ) as $set => $title ) :
						$list = hcc_items( $id, $set );
						$c    = hcc_counts( $id, $list );
						$sum  = $c['need'] . ' items · ' . $c['ok'] . ' approved · ' . $c['review'] . ' to review · ' . $c['todo'] . ' missing' . ( $c['amend'] ? ' · ' . $c['amend'] . ' to replace' : '' );
						?>
						<details open class="<?php echo 'docs' === $set ? 'p-sec p-card' : 'p-card p-folder'; ?>">
							<?php if ( 'docs' === $set ) : ?>
								<summary class="p-card-head"><h2>Documents</h2><span class="p-count"><?php echo esc_html( $sum ); ?></span></summary>
								<div class="bulk"><span>With ticked items:</span><button class="btn btn-sm btn-aqua" type="submit" name="act" value="bulk:approve">Approve</button><button class="btn btn-sm btn-sun" type="submit" name="act" value="bulk:missing">Request as missing</button><button class="btn btn-sm btn-ghost-light" type="submit" name="act" value="bulk:override">Mark complete (override)</button></div>
							<?php else : ?>
								<summary><span class="folder-ico" aria-hidden="true"><?php echo hcc_icon( 'doc', 22 ); // phpcs:ignore ?></span><span><b>Training certificates</b><small><?php echo esc_html( $sum ); ?></small></span><span class="folder-arrow" aria-hidden="true">▾</span></summary>
							<?php endif; ?>
							<div class="s-table">
							<?php foreach ( $list as $k => $it ) :
								$s  = hcc_item_status( $id, $k, $it );
								$st = hcc_file_state( $id, $k );
								$r  = isset( $review[ $k ] ) ? $review[ $k ] : array();
								?>
								<div class="s-row s-<?php echo esc_attr( $s ); ?>" id="item-<?php echo esc_attr( $k ); ?>">
									<?php if ( 'na' === $s ) : ?><span class="s-cb"></span><?php else : ?><label class="s-cb"><input type="checkbox" name="sel[]" value="<?php echo esc_attr( $k ); ?>" aria-label="Select <?php echo esc_attr( $it['label'] ); ?>"></label><?php endif; ?>
									<div class="s-item"><b><?php echo esc_html( $it['label'] ); ?></b>
										<?php foreach ( isset( $st['files'] ) ? $st['files'] : array() as $n => $f ) : ?>
											<span class="f-line"><a class="p-file f-view" href="<?php echo esc_url( hcc_file_url( $id, $k, $n ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $f['name'] ); ?></a><a class="f-act" href="<?php echo esc_url( hcc_file_url( $id, $k, $n ) ); ?>" target="_blank" rel="noopener">View</a><a class="f-act" href="<?php echo esc_url( hcc_file_url( $id, $k, $n, true ) ); ?>">Download</a></span>
										<?php endforeach; ?>
										<?php if ( ! empty( $st['pin'] ) ) : ?><span class="p-file">PIN <?php echo esc_html( $st['pin'] ); ?> · <a href="https://www.nmc.org.uk/registration/search-the-register/" target="_blank" rel="noopener">check NMC register</a></span><?php endif; ?>
										<?php if ( ! empty( $st['exp'] ) ) : ?><span class="p-file">Expires <?php echo esc_html( wp_date( 'd/m/Y', strtotime( $st['exp'] ) ) ); ?></span><?php endif; ?>
										<?php if ( ! empty( $st['missing'] ) && ! hcc_has_upload( $st ) ) : ?><span class="p-file">Candidate ticked "I don't have this yet"</span><?php endif; ?>
										<?php if ( 'na' === $s && ! empty( $it['note'] ) ) : ?><span class="p-file" style="color:var(--muted)"><?php echo esc_html( $it['note'] ); ?></span><?php endif; ?>
										<?php if ( ! empty( $r['reason'] ) ) : ?><span class="<?php echo 'override' === $s ? 'p-ov' : 'p-reason'; ?>"><?php echo esc_html( ( 'override' === $s ? 'Override: ' : '' ) . $r['reason'] . ( ! empty( $r['by'] ) ? ' · ' . $r['by'] : '' ) ); ?></span><?php endif; ?>
										<?php foreach ( isset( $dnotes[ $k ] ) ? $dnotes[ $k ] : array() as $n ) : ?><span class="d-note<?php echo $n['internal'] ? ' internal' : ''; ?>"><?php echo esc_html( ( $n['internal'] ? 'Internal' : 'To candidate' ) . ' · ' . $n['by'] . ': ' . $n['text'] ); ?></span><?php endforeach; ?>
									</div>
									<?php echo hcc_status_chip( $s, 'staff' ); // phpcs:ignore ?>
									<?php if ( 'na' !== $s ) : ?>
									<span class="s-btns">
										<?php if ( in_array( $s, array( 'review', 'amend' ), true ) ) : ?><button type="submit" class="mini ok" name="act" value="approve:<?php echo esc_attr( $k ); ?>" title="Approve" aria-label="Approve <?php echo esc_attr( $it['label'] ); ?>">✓</button><?php endif; ?>
										<details class="mini-pop"><summary class="mini <?php echo hcc_has_upload( $st ) ? 'am' : 'nt'; ?>" title="Ask to replace or add a note" aria-label="More actions">⋯</summary>
											<div class="pop">
												<?php if ( hcc_has_upload( $st ) ) : ?>
													<label>Ask to replace<select name="reason[<?php echo esc_attr( $k ); ?>]"><?php foreach ( hcc_amend_reasons() as $o ) : ?><option><?php echo esc_html( $o ); ?></option><?php endforeach; ?></select></label>
													<button type="submit" class="btn btn-sm btn-sun" name="act" value="amend:<?php echo esc_attr( $k ); ?>">Add to update</button>
												<?php endif; ?>
												<label>Note<input type="text" name="dnote[<?php echo esc_attr( $k ); ?>]" placeholder="Note about this document"></label>
												<label class="nohave"><input type="checkbox" name="dint[<?php echo esc_attr( $k ); ?>]" value="1" checked> Internal (staff only)</label>
												<button type="submit" class="btn btn-sm btn-ghost" name="act" value="note:<?php echo esc_attr( $k ); ?>">Add note</button>
											</div>
										</details>
									</span>
									<?php else : ?><span></span><?php endif; ?>
								</div>
							<?php endforeach; ?>
							</div>
						</details>
					<?php endforeach; ?>
					</form>
				</div>

				<aside>
					<details open class="p-sec p-card" id="answers">
						<summary class="p-card-head"><h2>Pre-screening answers</h2><span class="p-count">What the candidate told us first</span></summary>
						<?php $i = 0;
						foreach ( hcc_answer_groups( $id, false ) as $title => $rows ) : ?>
							<details class="det"<?php echo $i++ < 2 ? ' open' : ''; ?>><summary><?php echo esc_html( $title ); ?></summary><dl><?php foreach ( $rows as $k => $v ) : ?><div><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( '' !== (string) $v ? $v : '—' ); ?></dd></div><?php endforeach; ?></dl></details>
						<?php endforeach; ?>
					</details>

					<div class="p-card tray<?php echo $queue ? ' has' : ''; ?>" id="tray">
						<div class="p-card-head"><h2>Update for <?php echo esc_html( hcc_meta( $id, 'first' ) ); ?></h2><span class="tray-n"><?php echo count( $queue ); ?></span></div>
						<?php if ( ! $queue ) : ?>
							<p class="hint">No changes waiting. Approvals, replace requests, missing items and notes for the candidate collect here and go in one email.</p>
						<?php else :
							foreach ( array( 'approved' => 'Approved', 'amend' => 'Please replace', 'missing' => 'Still needed from you', 'note' => 'Messages from our team' ) as $type => $title ) :
								$rows = array_filter( $queue, function ( $q ) use ( $type ) { return $q['type'] === $type; } );
								if ( ! $rows ) { continue; } ?>
								<div class="tray-g"><b><?php echo esc_html( $title ); ?></b><ul>
									<?php foreach ( $rows as $i => $q ) : ?><li><?php echo esc_html( trim( $q['label'] . ( $q['label'] && $q['text'] ? ': ' : '' ) . $q['text'] ) ); ?> <form method="post" action="<?php echo $post; ?>" class="inline"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="unqueue"><input type="hidden" name="i" value="<?php echo (int) $i; ?>"><button class="link-btn" type="submit">remove</button></form></li><?php endforeach; ?>
								</ul></div>
							<?php endforeach; ?>
							<details class="tray-preview"><summary class="btn btn-navy btn-sm">Preview and send email</summary>
								<div class="email" style="margin-top:12px"><div class="email-meta"><span>To: <?php echo esc_html( hcc_meta( $id, 'email' ) ); ?></span></div><div class="email-body">
									<p>Hi <?php echo esc_html( hcc_meta( $id, 'first' ) ); ?>,</p><p>Here is an update on your registration (<?php echo esc_html( hcc_meta( $id, 'ref' ) ); ?>):</p>
									<?php foreach ( array( 'approved' => 'Approved', 'amend' => 'Please replace', 'missing' => 'Still needed from you', 'note' => 'Messages from our team' ) as $type => $title ) :
										$rows = array_filter( $queue, function ( $q ) use ( $type ) { return $q['type'] === $type; } );
										if ( ! $rows ) { continue; } ?>
										<p><b><?php echo esc_html( $title ); ?></b></p><ul><?php foreach ( $rows as $q ) : ?><li><?php echo esc_html( trim( $q['label'] . ( $q['label'] && $q['text'] ? ': ' : '' ) . $q['text'] ) ); ?></li><?php endforeach; ?></ul>
									<?php endforeach; ?>
								</div></div>
								<form method="post" action="<?php echo $post; ?>" class="form-actions"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="send"><button class="btn btn-navy" type="submit">Send email</button></form>
							</details>
							<p class="hint" style="margin-top:10px">Not sent yet. Nothing is sent automatically: until someone presses Send, this shows on the dashboard under <b>Updates not sent</b>.</p>
						<?php endif; ?>
					</div>

					<details open class="p-sec p-card" id="checks">
						<summary class="p-card-head"><h2>Checks</h2><span class="p-count">Who checked, when, result</span></summary>
						<?php $checks = (array) hcc_meta( $id, 'checks', array() );
						foreach ( hcc_check_list( $id ) as $k => $c ) :
							$v = isset( $checks[ $k ] ) ? $checks[ $k ] : array(); ?>
							<details class="chk"><summary><span><b><?php echo esc_html( $c[0] ); ?></b><small><?php echo esc_html( $c[1] ); ?></small></span><?php echo ! empty( $v['result'] ) ? '<span class="stat stat-' . ( 'Pass' === $v['result'] ? 'ok' : ( 'Fail' === $v['result'] ? 'amend' : 'todo' ) ) . '">' . esc_html( $v['result'] ) . '</span>' : '<span class="stat stat-wait">Not checked</span>'; // phpcs:ignore ?></summary>
								<?php if ( ! empty( $v['result'] ) ) : ?><p class="chk-done">Checked <?php echo esc_html( $v['date'] . ' by ' . $v['by'] . ( $v['outcome'] ? ' · ' . $v['outcome'] : '' ) . ( ! empty( $v['visa_exp'] ) ? ' · Visa expires ' . wp_date( 'd/m/Y', strtotime( $v['visa_exp'] ) ) : '' ) ); ?></p><?php endif; ?>
								<form class="form-grid chk-form" method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="check"><input type="hidden" name="k" value="<?php echo esc_attr( $k ); ?>">
									<div class="f"><label>Date checked</label><input type="date" name="date" value="<?php echo esc_attr( ! empty( $v['date'] ) ? $v['date'] : wp_date( 'Y-m-d' ) ); ?>"></div>
									<div class="f"><label>Checked by</label><input type="text" name="by" value="<?php echo esc_attr( ! empty( $v['by'] ) ? $v['by'] : $me ); ?>"></div>
									<div class="f"><label>Result</label><select name="result"><option value="">Choose</option><?php foreach ( array( 'Pass', 'Fail', 'Follow-up needed' ) as $o ) : ?><option<?php selected( isset( $v['result'] ) ? $v['result'] : '', $o ); ?>><?php echo esc_html( $o ); ?></option><?php endforeach; ?></select></div>
									<?php if ( $c[3] ) : ?><div class="f"><label>Visa expiry date</label><input type="date" name="visa_exp" value="<?php echo esc_attr( isset( $v['visa_exp'] ) ? $v['visa_exp'] : '' ); ?>"></div><?php endif; ?>
									<div class="f full"><label>Outcome / notes</label><input type="text" name="outcome" value="<?php echo esc_attr( isset( $v['outcome'] ) ? $v['outcome'] : '' ); ?>" placeholder="e.g. Right to work confirmed, no restrictions on hours"></div>
									<div class="form-actions full" style="margin-top:0"><?php if ( $c[2] ) : ?><a class="link-btn" href="<?php echo esc_url( $c[2] ); ?>" target="_blank" rel="noopener">Open official check ↗</a><?php endif; ?><button class="btn btn-ghost btn-sm" type="submit">Save check</button></div>
								</form>
							</details>
						<?php endforeach; ?>
					</details>

					<details open class="p-sec p-card" id="refs">
						<summary class="p-card-head"><h2>References</h2><span class="p-count">Sent and checked manually</span></summary>
						<?php foreach ( hcc_refs( $id ) as $i => $rf ) :
							$stc = $rf['recv'] ? '<span class="stat stat-' . ( 'Concerns' === $rf['outcome'] ? 'amend' : 'ok' ) . '">Received ' . esc_html( wp_date( 'd/m/Y', strtotime( $rf['recv'] ) ) ) . '</span>' : ( $rf['sent'] ? '<span class="stat stat-wait">Sent ' . esc_html( wp_date( 'd/m/Y', strtotime( $rf['sent'] ) ) ) . '</span>' : '<span class="stat stat-todo">Not sent</span>' ); ?>
							<details class="chk"><summary><span><b>Referee <?php echo (int) $i + 1; ?>: <?php echo esc_html( $rf['name'] ? $rf['name'] : 'not given' ); ?></b><small><?php echo esc_html( implode( ' · ', array_filter( array( $rf['pos'], $rf['email'], $rf['phone'] ) ) ) ); ?></small><?php if ( ! empty( $rf['history'] ) ) : ?><small class="ref-hist">Replaced <?php echo count( $rf['history'] ); ?>×. Previous: <?php echo esc_html( implode( ', ', wp_list_pluck( $rf['history'], 'name' ) ) ); ?></small><?php endif; ?></span><?php echo $stc; // phpcs:ignore ?></summary>
								<form class="form-grid chk-form" method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="ref"><input type="hidden" name="i" value="<?php echo (int) $i; ?>">
									<div class="f"><label>Reference sent on</label><input type="date" name="sent" value="<?php echo esc_attr( $rf['sent'] ); ?>"></div>
									<div class="f"><label>Reference received on</label><input type="date" name="recv" value="<?php echo esc_attr( $rf['recv'] ); ?>"></div>
									<div class="f"><label>Outcome</label><select name="outcome"><option value="">Choose</option><?php foreach ( array( 'Satisfactory', 'Concerns', 'Not received' ) as $o ) : ?><option<?php selected( $rf['outcome'], $o ); ?>><?php echo esc_html( $o ); ?></option><?php endforeach; ?></select></div>
									<div class="form-actions full" style="margin-top:0"><button class="btn btn-ghost btn-sm" type="submit">Save</button></div>
								</form>
								<details class="ref-new-wrap"><summary class="link-btn">Replace referee (new details)</summary>
									<form class="ref-new" method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="ref_replace"><input type="hidden" name="i" value="<?php echo (int) $i; ?>">
										<div class="form-grid"><div class="f"><label>Full name</label><input type="text" name="name" required></div><div class="f"><label>Position and organisation</label><input type="text" name="pos"></div><div class="f"><label>Email</label><input type="email" name="email"></div><div class="f"><label>Phone</label><input type="tel" name="phone"></div></div>
										<div class="form-actions" style="margin-top:8px"><button class="btn btn-navy btn-sm" type="submit">Save new referee</button></div>
									</form>
								</details>
							</details>
						<?php endforeach; ?>
						<details class="ref-new-wrap" style="margin-top:10px"><summary class="link-btn">+ Add another referee</summary>
							<form class="ref-new" method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="ref_add">
								<div class="form-grid"><div class="f"><label>Full name</label><input type="text" name="name" required></div><div class="f"><label>Position and organisation</label><input type="text" name="pos"></div><div class="f"><label>Email</label><input type="email" name="email"></div><div class="f"><label>Phone</label><input type="tel" name="phone"></div></div>
								<div class="form-actions" style="margin-top:8px"><button class="btn btn-navy btn-sm" type="submit">Add referee</button></div>
							</form>
						</details>
					</details>


					<details open class="p-sec p-card" id="declaration">
						<summary class="p-card-head"><h2>Declaration</h2><span class="p-count">Sent manually</span></summary>
						<form method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="dec">
							<p class="hint" style="margin-bottom:10px">Send the declaration once all documents are approved, then record it here.</p>
							<label class="consent"><input type="checkbox" name="dec_sent" value="1"<?php checked( (bool) hcc_meta( $id, 'dec_sent' ) ); ?>>Declaration sent to candidate<?php echo hcc_meta( $id, 'dec_sent' ) ? ' (' . esc_html( wp_date( 'j M', (int) hcc_meta( $id, 'dec_sent' ) ) ) . ')' : ''; ?></label>
							<label class="consent" style="margin-top:8px"><input type="checkbox" name="dec_signed" value="1"<?php checked( (bool) hcc_meta( $id, 'dec_signed' ) ); ?>>Signed declaration received<?php echo hcc_meta( $id, 'dec_signed' ) ? ' (' . esc_html( wp_date( 'j M', (int) hcc_meta( $id, 'dec_signed' ) ) ) . ')' : ''; ?></label>
							<div class="form-actions" style="margin-top:10px"><button class="btn btn-ghost btn-sm" type="submit">Save</button></div>
						</form>
					</details>

					<details open class="p-sec p-card bank-card">
						<summary class="p-card-head"><h2>Bank details</h2><span class="p-count"><?php echo hcc_is_manager() ? 'Managers only' : 'Last 4 digits'; ?></span></summary>
						<dl class="bank-dl">
							<?php $bank = hcc_answer_groups( $id, true )['Bank details'];
							if ( ! hcc_is_manager() ) {
								$bank['Sort code']      = $bank['Sort code'] ? '**-**-' . substr( $bank['Sort code'], -2 ) : '';
								$bank['Account number'] = $bank['Account number'] ? '****' . substr( $bank['Account number'], -4 ) : '';
							}
							foreach ( $bank as $k => $v ) : ?><div><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( $v ? $v : '—' ); ?></dd></div><?php endforeach; ?>
						</dl>
					</details>

					<details open class="p-sec p-card" id="notes">
						<summary class="p-card-head"><h2>Notes</h2></summary>
						<form method="post" action="<?php echo $post; ?>"><?php echo $hidden; // phpcs:ignore ?><input type="hidden" name="do" value="cnote">
							<div class="radios note-type"><label class="radio"><input type="radio" name="type" value="internal" checked><span>Internal compliance note</span></label><label class="radio"><input type="radio" name="type" value="cand"><span>Message to candidate</span></label></div>
							<div class="f" style="margin-top:10px"><label for="ntext">Note</label><textarea id="ntext" name="text" placeholder="Internal notes are staff only. Messages go in the next update email."></textarea></div>
							<div class="form-grid" style="margin-top:10px">
								<div class="f"><label for="nby">Signed off by</label><input type="text" id="nby" name="by" value="<?php echo esc_attr( $me ); ?>"></div>
								<div class="f"><label for="ndue">Next action due</label><input type="date" id="ndue" name="due"></div>
								<div class="f full"><label for="nnext">Next action for candidate (optional)</label><input type="text" id="nnext" name="next" placeholder="e.g. Chase NI letter on Friday"></div>
							</div>
							<div class="form-actions" style="margin-top:10px"><button class="btn btn-ghost btn-sm" type="submit">Save to profile</button></div>
						</form>
						<div class="cnotes">
							<?php foreach ( array_reverse( (array) hcc_meta( $id, 'cnotes', array() ) ) as $n ) : ?>
								<div class="cnote<?php echo 'cand' === $n['type'] ? ' cand' : ''; ?>"><div class="cn-h"><b><?php echo 'cand' === $n['type'] ? 'Message to candidate' : 'Internal note'; ?></b><span><?php echo esc_html( wp_date( 'j M, H:i', (int) $n['at'] ) . ( $n['by'] ? ' · signed off by ' . $n['by'] : '' ) ); ?></span></div><p><?php echo esc_html( $n['text'] ); ?></p><?php if ( $n['next'] ) : ?><span class="cn-next">Next action: <?php echo esc_html( $n['next'] . ( $n['due'] ? ' · due ' . wp_date( 'd/m/Y', strtotime( $n['due'] ) ) : '' ) ); ?></span><?php endif; ?></div>
							<?php endforeach; ?>
						</div>
					</details>

					<details open class="p-sec p-card">
						<summary class="p-card-head"><h2>Activity log</h2></summary>
						<ol class="timeline">
							<?php foreach ( array_reverse( (array) hcc_meta( $id, 'log', array() ) ) as $l ) : ?><li><b><?php echo esc_html( $l['text'] ); ?></b><span><?php echo esc_html( wp_date( 'j M, H:i', (int) $l['time'] ) . ' · ' . ( is_string( $l['by'] ) ? $l['by'] : '' ) ); ?></span></li><?php endforeach; ?>
						</ol>
					</details>
				</aside>
			</div>
<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
