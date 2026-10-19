<?php
/**
 * Template Name: Staff portal
 *
 * Views: list (default), ?candidate=ID (candidate folder), ?view=team (staff accounts).
 */
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( get_permalink() ), hcc_login_url() ) );
	exit;
}
if ( ! hcc_is_staff() ) {
	wp_safe_redirect( hcc_portal_url() );
	exit;
}

$stages   = hcc_stages();
$labels   = hcc_doc_statuses();
$cand     = isset( $_GET['candidate'] ) ? absint( $_GET['candidate'] ) : 0;
$view     = $cand && 'hcc_applicant' === get_post_type( $cand ) ? 'candidate' : ( isset( $_GET['view'] ) && 'team' === $_GET['view'] && current_user_can( 'hcc_manage_staff' ) ? 'team' : 'list' );
$msg      = isset( $_GET['msg'] ) ? sanitize_key( $_GET['msg'] ) : '';
$messages = array(
	'invited'      => array( 'ok', 'Invite sent. The candidate has an email to create their password.' ),
	'invite_fail'  => array( 'err', 'The invite could not be sent. Check the candidate has a valid email that is not used by a staff account.' ),
	'reset'        => array( 'ok', 'Password reset email sent.' ),
	'no_account'   => array( 'err', 'This candidate has not been invited yet, so there is no account to reset.' ),
	'saved'        => array( 'ok', 'Saved.' ),
	'error'        => array( 'err', 'That did not work. Please try again.' ),
	'staff_added'  => array( 'ok', 'Staff account created. They have an email to create their password.' ),
	'staff_exists' => array( 'err', 'An account with that email already exists.' ),
);
get_header();
?>
<main id="main" class="staff">
	<section class="staff-bar">
		<div class="wrap">
			<div>
				<span class="eyebrow" style="color:var(--aqua)">Staff portal</span>
				<h1><?php echo 'team' === $view ? 'Staff accounts' : ( 'candidate' === $view ? esc_html( get_post_meta( $cand, '_hcc_first', true ) . ' ' . get_post_meta( $cand, '_hcc_last', true ) ) : 'Applications' ); ?></h1>
			</div>
			<nav class="staff-nav" aria-label="Staff portal">
				<a href="<?php echo esc_url( hcc_staff_url() ); ?>"<?php echo 'team' !== $view ? ' aria-current="page"' : ''; ?>>Applications</a>
				<?php if ( current_user_can( 'hcc_manage_staff' ) ) : ?><a href="<?php echo esc_url( hcc_staff_url( array( 'view' => 'team' ) ) ); ?>"<?php echo 'team' === $view ? ' aria-current="page"' : ''; ?>>Staff accounts</a><?php endif; ?>
				<a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">My security</a>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
			</nav>
		</div>
	</section>

	<section class="section" style="padding-top:32px">
		<div class="wrap">
			<?php if ( $msg && isset( $messages[ $msg ] ) ) : ?>
				<p class="<?php echo 'ok' === $messages[ $msg ][0] ? 'form-ok' : 'form-alert'; ?>" role="status"><?php echo esc_html( $messages[ $msg ][1] ); ?></p>
			<?php endif; ?>

<?php
/* ================= LIST ================= */
if ( 'list' === $view ) :
	$args = wp_unslash( $_GET );
	$period         = isset( $args['period'] ) && isset( hcc_periods()[ $args['period'] ] ) ? $args['period'] : '30d';
	$args['period'] = $period;
	$query          = hcc_staff_query( $args );
	$total = (int) $query->found_posts;
	$sel   = function ( $k ) use ( $args ) { return isset( $args[ $k ] ) ? sanitize_text_field( $args[ $k ] ) : ''; };
	// Stage counts for the summary chips.
	$chips = array();
	foreach ( array_keys( $stages ) as $st ) {
		$chip_q = array( 'post_type' => 'hcc_applicant', 'post_status' => 'private', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_hcc_stage', 'meta_value' => $st );
		if ( hcc_period_start( $period ) ) {
			$chip_q['date_query'] = array( array( 'after' => gmdate( 'Y-m-d H:i:s', hcc_period_start( $period ) ), 'column' => 'post_date_gmt' ) );
		}
		$chips[ $st ] = count( get_posts( $chip_q ) );
	}
	$dash = hcc_dashboard( $period );
	?>
			<div class="dash-head">
				<h2 class="portal-h">Overview</h2>
				<nav class="period-chips" aria-label="Period">
					<?php foreach ( hcc_periods() as $pk => $pv ) : ?>
						<a href="<?php echo esc_url( add_query_arg( array( 'period' => $pk, 'pg' => false ) ) ); ?>"<?php echo $period === $pk ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $pv[1] ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>
			<div class="kpis">
				<div class="kpi"><span>Applications</span><b><?php echo (int) $dash['total']; ?></b><small><?php echo esc_html( hcc_periods()[ $period ][1] ); ?></small></div>
				<div class="kpi"><span>In the pipeline</span><b><?php echo (int) $dash['pipeline']; ?></b><small><?php echo (int) $dash['new']; ?> new, not yet invited</small></div>
				<div class="kpi warn"><span>Waiting for review</span><b><?php echo (int) $dash['review']; ?></b><small>candidates with documents to check</small></div>
				<div class="kpi good"><span>Ready to work</span><b><?php echo (int) $dash['ready']; ?></b><small><?php echo $dash['total'] ? (int) round( $dash['ready'] / $dash['total'] * 100 ) : 0; ?>% of applications</small></div>
				<div class="kpi"><span>Average onboarding</span><b><?php echo null === $dash['avg_days'] ? '–' : esc_html( $dash['avg_days'] ) . '<i> days</i>'; ?></b><small>from applying to ready to work</small></div>
				<div class="kpi"><span>Average time to invite</span><b><?php echo null === $dash['avg_invite'] ? '–' : esc_html( $dash['avg_invite'] ) . '<i> days</i>'; ?></b><small>from applying to portal invite</small></div>
			</div>
			<div class="outstanding">
				<h3>Outstanding documents <span class="small">for candidates invited, uploading or in checks</span></h3>
				<div class="table-wrap">
					<table class="apps compact">
						<thead><tr><th>Document</th><th>Not uploaded</th><th>Waiting for review</th><th>Needs amendment</th></tr></thead>
						<tbody>
							<?php foreach ( $dash['docs'] as $dk => $dv ) : ?>
								<tr><td><b><?php echo esc_html( $dv['label'] ); ?></b><?php echo $dv['required'] ? '' : ' <span class="small">(if applies)</span>'; ?></td>
								<td><?php echo $dv['required'] ? '<span class="count-pill' . ( $dv['missing'] ? ' red' : '' ) . '">' . (int) $dv['missing'] . '</span>' : '<span class="small">–</span>'; ?></td>
								<td><span class="count-pill<?php echo $dv['review'] ? ' amber' : ''; ?>"><?php echo (int) $dv['review']; ?></span></td>
								<td><span class="count-pill<?php echo $dv['amend'] ? ' red' : ''; ?>"><?php echo (int) $dv['amend']; ?></span></td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>

			<h2 class="portal-h" style="margin:36px 0 14px">Applications</h2>
			<div class="chips-row">
				<a class="stage-chip<?php echo '' === $sel( 'stage' ) ? ' on' : ''; ?>" href="<?php echo esc_url( remove_query_arg( array( 'stage', 'pg' ) ) ); ?>">All stages <b><?php echo (int) array_sum( $chips ); ?></b></a>
				<?php foreach ( $stages as $st => $label ) : ?>
					<a class="stage-chip s-<?php echo esc_attr( $st ); ?><?php echo $sel( 'stage' ) === $st ? ' on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'stage' => $st, 'pg' => false ) ) ); ?>"><?php echo esc_html( $label ); ?> <b><?php echo (int) $chips[ $st ]; ?></b></a>
				<?php endforeach; ?>
			</div>

			<form class="staff-filters" method="get" action="<?php echo esc_url( hcc_staff_url() ); ?>">
				<?php if ( $sel( 'stage' ) ) : ?><input type="hidden" name="stage" value="<?php echo esc_attr( $sel( 'stage' ) ); ?>"><?php endif; ?>
				<input type="hidden" name="period" value="<?php echo esc_attr( $period ); ?>">
				<div class="f grow"><label for="s">Search</label><input type="text" id="s" name="s" value="<?php echo esc_attr( $sel( 's' ) ); ?>" placeholder="Name, reference, email or phone"></div>
				<div class="f"><label for="sector">Sector</label><select id="sector" name="sector"><option value="">All sectors</option><?php foreach ( hcc_applicant_values( '_hcc_sector' ) as $v ) : ?><option<?php selected( $sel( 'sector' ), $v ); ?>><?php echo esc_html( $v ); ?></option><?php endforeach; ?></select></div>
				<div class="f"><label for="type">Role</label><select id="type" name="type"><option value="">All roles</option><?php foreach ( hcc_applicant_values( '_hcc_role_type' ) as $v ) : ?><option<?php selected( $sel( 'type' ), $v ); ?>><?php echo esc_html( $v ); ?></option><?php endforeach; ?></select></div>
				<div class="f"><label for="docs">Documents</label><select id="docs" name="docs"><option value="">Any</option><option value="review"<?php selected( $sel( 'docs' ), 'review' ); ?>>Waiting for review</option><option value="amend"<?php selected( $sel( 'docs' ), 'amend' ); ?>>Needs amendment</option><option value="missing"<?php selected( $sel( 'docs' ), 'missing' ); ?>>Missing required</option><option value="complete"<?php selected( $sel( 'docs' ), 'complete' ); ?>>All approved</option></select></div>
				<div class="f"><label for="from">Applied from</label><input type="date" id="from" name="from" value="<?php echo esc_attr( $sel( 'from' ) ); ?>"></div>
				<div class="f"><label for="to">to</label><input type="date" id="to" name="to" value="<?php echo esc_attr( $sel( 'to' ) ); ?>"></div>
				<div class="f"><label for="sort">Sort</label><select id="sort" name="sort"><option value="new">Newest first</option><option value="old"<?php selected( $sel( 'sort' ), 'old' ); ?>>Oldest first</option><option value="updated"<?php selected( $sel( 'sort' ), 'updated' ); ?>>Recently updated</option><option value="name"<?php selected( $sel( 'sort' ), 'name' ); ?>>Surname A–Z</option></select></div>
				<div class="f actions"><button class="btn btn-navy btn-sm" type="submit">Apply</button><a class="clear" href="<?php echo esc_url( hcc_staff_url() ); ?>">Clear</a></div>
			</form>

			<div class="results-bar">
				<span class="n"><?php echo (int) ( $sel( 'docs' ) ? $query->post_count : $total ); ?> applications</span>
				<span class="folder-actions">
					<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( $args, array( 'period' => $period, 'action' => 'hcc_export' ) ), admin_url( 'admin-post.php' ) ), 'hcc_export' ) ); ?>">Export all records (Excel)</a>
					<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( $args, array( 'period' => $period, 'action' => 'hcc_export_log' ) ), admin_url( 'admin-post.php' ) ), 'hcc_export' ) ); ?>">Export activity log (Excel)</a>
				</span>
			</div>

			<div class="table-wrap">
				<table class="apps">
					<thead><tr><th>Candidate</th><th>Role</th><th>Sector</th><th>Applied</th><th>Stage</th><th>Documents</th><th>Last update</th></tr></thead>
					<tbody>
						<?php if ( ! $query->posts ) : ?>
							<tr><td colspan="7" class="empty-row">No applications match these filters.</td></tr>
						<?php endif; ?>
						<?php foreach ( $query->posts as $p ) : $pm = function ( $k ) use ( $p ) { return get_post_meta( $p->ID, '_hcc_' . $k, true ); }; $c = hcc_doc_counts( $p->ID ); $st = $pm( 'stage' ); ?>
							<tr>
								<td><a class="cand" href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $p->ID ) ) ); ?>"><b><?php echo esc_html( $pm( 'first' ) . ' ' . $pm( 'last' ) ); ?></b><span><?php echo esc_html( $pm( 'ref' ) ); ?></span></a></td>
								<td><?php echo esc_html( $pm( 'role' ) ); ?></td>
								<td><?php echo esc_html( $pm( 'sector' ) ); ?></td>
								<td class="nowrap"><?php echo esc_html( get_the_date( 'j M Y', $p ) ); ?></td>
								<td><span class="stage-pill s-<?php echo esc_attr( $st ); ?>"><?php echo esc_html( isset( $stages[ $st ] ) ? $stages[ $st ] : '' ); ?></span></td>
								<td class="nowrap"><?php echo in_array( $st, array( 'new' ), true ) ? '<span class="small">Not invited</span>' : esc_html( $c['approved'] . ' of ' . $c['required'] . ' approved' ) . ( $c['amend'] ? ' <span class="pill amend">' . (int) $c['amend'] . ' to fix</span>' : '' ); ?></td>
								<td class="nowrap small"><?php echo $pm( 'updated' ) ? esc_html( human_time_diff( (int) $pm( 'updated' ) ) . ' ago' ) : ''; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php
			if ( ! $sel( 'docs' ) && $query->max_num_pages > 1 ) :
				echo '<nav class="pager" aria-label="Pages">';
				for ( $n = 1; $n <= $query->max_num_pages; $n++ ) {
					$cur = max( 1, (int) $sel( 'pg' ) ) === $n;
					printf( '<a class="page-numbers%s" href="%s">%d</a>', $cur ? ' current' : '', esc_url( add_query_arg( 'pg', $n ) ), (int) $n );
				}
				echo '</nav>';
			endif;
			?>

<?php
/* ================= CANDIDATE FOLDER ================= */
elseif ( 'candidate' === $view ) :
	$id    = $cand;
	$m     = function ( $k ) use ( $id ) { return get_post_meta( $id, '_hcc_' . $k, true ); };
	$defs  = hcc_portal_docs( $m( 'role' ) );
	$docs  = hcc_pget( $id, 'pdocs' );
	$c     = hcc_doc_counts( $id );
	$st    = $m( 'stage' );
	$cv    = (array) $m( 'docs' );
	$nonce = function () use ( $id ) { wp_nonce_field( 'hcc_staff_' . $id ); echo '<input type="hidden" name="action" value="hcc_staff"><input type="hidden" name="a" value="' . (int) $id . '">'; };
	?>
			<a class="back-link" href="<?php echo esc_url( hcc_staff_url() ); ?>">← All applications</a>
			<div class="folder-head">
				<div>
					<div class="ref-line"><?php echo esc_html( $m( 'ref' ) ); ?> · <?php echo esc_html( $m( 'role' ) ); ?> · <?php echo esc_html( $m( 'sector' ) ); ?></div>
					<span class="stage-pill s-<?php echo esc_attr( $st ); ?>"><?php echo esc_html( isset( $stages[ $st ] ) ? $stages[ $st ] : '' ); ?></span>
					<span class="small"><?php echo (int) $c['approved']; ?> of <?php echo (int) $c['required']; ?> required documents approved</span>
				</div>
				<div class="folder-actions">
					<?php if ( ! $m( 'user_id' ) ) : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php $nonce(); ?><input type="hidden" name="do" value="invite"><button class="btn btn-sun pulse" type="submit">Approve &amp; invite to portal</button></form>
					<?php else : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php $nonce(); ?><input type="hidden" name="do" value="invite"><button class="btn btn-ghost btn-sm" type="submit">Resend invite</button></form>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php $nonce(); ?><input type="hidden" name="do" value="reset"><button class="btn btn-ghost btn-sm" type="submit">Send password reset</button></form>
					<?php endif; ?>
					<a class="btn btn-navy btn-sm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'hcc_zip', 'a' => $id ), admin_url( 'admin-post.php' ) ), 'hcc_zip_' . $id ) ); ?>">Download all (zip)</a>
					<a class="btn btn-ghost btn-sm" target="_blank" rel="noopener" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'hcc_report', 'a' => $id ), admin_url( 'admin-post.php' ) ), 'hcc_report_' . $id ) ); ?>">Candidate report</a>
				</div>
			</div>

			<div class="folder-grid">
				<div>
					<h2 class="portal-h">Documents</h2>
					<div class="sdocs">
						<article class="sdoc">
							<header><h3>CV</h3><span class="pill pending">From application</span></header>
							<?php if ( ! empty( $cv['CV']['file'] ) ) : ?>
								<div class="sfiles"><?php echo hcc_staff_file( $id, 'cv', 0, $cv['CV']['file'], (int) $cv['CV']['time'] ); // phpcs:ignore -- escaped inside. ?></div>
							<?php endif; ?>
						</article>
						<?php
						foreach ( $defs as $key => $d ) :
							$doc    = isset( $docs[ $key ] ) ? $docs[ $key ] : array( 'files' => array(), 'status' => '' );
							$status = isset( $doc['status'] ) ? $doc['status'] : '';
							?>
							<article class="sdoc" id="sdoc-<?php echo esc_attr( $key ); ?>">
								<header>
									<h3><?php echo esc_html( $d[0] ); ?><?php echo $d[2] ? '' : ' <span class="small">(if applies)</span>'; ?></h3>
									<span class="pill <?php echo esc_attr( $labels[ $status ][1] ); ?>"><?php echo esc_html( $labels[ $status ][0] ); ?></span>
								</header>
								<?php if ( ! empty( $doc['files'] ) ) : ?>
									<div class="sfiles"><?php foreach ( $doc['files'] as $i => $f ) { echo hcc_staff_file( $id, $key, $i, $f['file'], (int) $f['time'] ); } // phpcs:ignore ?></div>
								<?php endif; ?>
								<?php if ( ! empty( $doc['code'] ) ) : ?>
									<p class="files-code">Share code <b><?php echo esc_html( $doc['code'] ); ?></b> · Date of birth <b><?php echo esc_html( wp_date( 'j M Y', strtotime( $doc['dob'] ) ) ); ?></b> · <a href="https://www.gov.uk/view-right-to-work" target="_blank" rel="noopener">Check on gov.uk</a></p>
								<?php endif; ?>
								<?php if ( 'amend' === $status && ! empty( $doc['reason'] ) ) : ?><div class="amend-box"><b>Asked for:</b> <?php echo esc_html( $doc['reason'] ); ?></div><?php endif; ?>
								<?php if ( ! empty( $doc['by'] ) ) : ?><p class="small">Last checked by <?php echo esc_html( $doc['by'] ); ?>, <?php echo esc_html( wp_date( 'j M, H:i', (int) $doc['checked'] ) ); ?></p><?php endif; ?>
								<?php if ( '' !== $status ) : ?>
									<div class="doc-review">
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php $nonce(); ?><input type="hidden" name="do" value="doc"><input type="hidden" name="doc" value="<?php echo esc_attr( $key ); ?>"><input type="hidden" name="status" value="approved"><button class="btn btn-aqua btn-sm" type="submit"<?php disabled( 'approved', $status ); ?>>Approve</button></form>
										<details class="amend-form"<?php echo 'amend' === $status ? '' : ''; ?>>
											<summary class="btn btn-ghost btn-sm">Needs amendment</summary>
											<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php $nonce(); ?><input type="hidden" name="do" value="doc"><input type="hidden" name="doc" value="<?php echo esc_attr( $key ); ?>"><input type="hidden" name="status" value="amend">
												<label class="small" for="r-<?php echo esc_attr( $key ); ?>">Tell the candidate what to fix (they see this and get an email)</label>
												<textarea id="r-<?php echo esc_attr( $key ); ?>" name="reason" rows="3" required placeholder="e.g. The photo is blurry and the expiry date is cut off."></textarea>
												<button class="btn btn-navy btn-sm" type="submit">Send request</button>
											</form>
										</details>
									</div>
								<?php else : ?>
									<p class="small">Not uploaded yet.</p>
								<?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
				</div>

				<aside class="folder-side">
					<div class="side-card">
						<h3>Stage</h3>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="stage-form"><?php $nonce(); ?><input type="hidden" name="do" value="stage">
							<select name="stage" class="select" aria-label="Stage"><?php foreach ( $stages as $k => $l ) : ?><option value="<?php echo esc_attr( $k ); ?>"<?php selected( $st, $k ); ?>><?php echo esc_html( $l ); ?></option><?php endforeach; ?></select>
							<button class="btn btn-navy btn-sm" type="submit">Update</button>
						</form>
						<p class="small">Moving to "Ready to work" emails the candidate.</p>
					</div>

					<div class="side-card white">
						<h3>Details</h3>
						<dl class="mini-dl">
							<div><dt>Email</dt><dd><?php echo esc_html( $m( 'email' ) ); ?></dd></div>
							<div><dt>Phone</dt><dd><?php echo esc_html( $m( 'phone' ) ); ?></dd></div>
							<div><dt>Postcode</dt><dd><?php echo esc_html( $m( 'postcode' ) ); ?></dd></div>
							<div><dt>Right to work</dt><dd><?php echo esc_html( $m( 'rtw' ) ? $m( 'rtw' ) : '–' ); ?></dd></div>
							<div><dt>Applied</dt><dd><?php echo esc_html( get_the_date( 'j M Y, H:i', $id ) ); ?></dd></div>
							<div><dt>Portal account</dt><dd><?php echo $m( 'user_id' ) ? ( get_user_meta( (int) $m( 'user_id' ), 'hcc_password_set', true ) || get_user_meta( (int) $m( 'user_id' ), 'hcc_demo', true ) ? 'Active' : 'Invited, password not set yet' ) : 'Not invited'; ?></dd></div>
						</dl>
					</div>

					<div class="side-card white" id="notes">
						<h3>Notes</h3>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="note-form"><?php $nonce(); ?><input type="hidden" name="do" value="note">
							<label class="screen-reader-text" for="note">Note</label>
							<textarea id="note" name="note" rows="3" required placeholder="Add a note"></textarea>
							<label class="consent"><input type="checkbox" name="visible" value="1">Show to the candidate and email them</label>
							<button class="btn btn-navy btn-sm" type="submit">Add note</button>
						</form>
						<ul class="notes">
							<?php foreach ( array_reverse( hcc_pget( $id, 'notes' ) ) as $n ) : ?>
								<li class="<?php echo ! empty( $n['visible'] ) ? 'shared' : 'internal'; ?>"><span class="tag"><?php echo ! empty( $n['visible'] ) ? 'Visible to candidate' : 'Internal'; ?></span><p><?php echo nl2br( esc_html( $n['text'] ) ); ?></p><span class="small"><?php echo esc_html( $n['by'] . ' · ' . wp_date( 'j M, H:i', (int) $n['time'] ) ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="side-card white">
						<h3>Timeline</h3>
						<ol class="timeline-list">
							<?php foreach ( array_reverse( hcc_pget( $id, 'log' ) ) as $e ) : ?>
								<li><b><?php echo esc_html( $e['text'] ); ?></b><span><?php echo esc_html( $e['by'] . ' · ' . wp_date( 'j M Y, H:i', (int) $e['time'] ) ); ?></span></li>
							<?php endforeach; ?>
						</ol>
					</div>
				</aside>
			</div>

<?php
/* ================= TEAM ================= */
else :
	$team = get_users( array( 'role__in' => array( 'hcc_staff', 'hcc_manager', 'administrator' ), 'orderby' => 'display_name' ) );
	?>
			<div class="folder-grid">
				<div>
					<div class="table-wrap">
						<table class="apps">
							<thead><tr><th>Name</th><th>Email</th><th>Access</th><th>Last password set</th></tr></thead>
							<tbody>
								<?php foreach ( $team as $u ) : ?>
									<tr><td><b><?php echo esc_html( $u->display_name ); ?></b></td><td><?php echo esc_html( $u->user_email ); ?></td><td><?php echo esc_html( in_array( 'administrator', $u->roles, true ) ? 'Website admin' : ( in_array( 'hcc_manager', $u->roles, true ) ? 'Manager' : 'Staff' ) ); ?></td><td class="small"><?php $ps = get_user_meta( $u->ID, 'hcc_password_set', true ); echo $ps ? esc_html( wp_date( 'j M Y', (int) $ps ) ) : '–'; ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<p class="small" style="margin-top:12px">To remove someone, a website admin deletes them under Users in WordPress.</p>
				</div>
				<aside class="folder-side">
					<form class="side-card white" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<h3>Add a staff member</h3>
						<input type="hidden" name="action" value="hcc_add_staff">
						<?php wp_nonce_field( 'hcc_add_staff' ); ?>
						<div class="f"><label for="sf">First name</label><input type="text" id="sf" name="first" required></div>
						<div class="f"><label for="sl">Last name</label><input type="text" id="sl" name="last"></div>
						<div class="f"><label for="se">Work email</label><input type="email" id="se" name="email" required></div>
						<fieldset class="f"><legend>Access</legend><div class="radios"><label class="radio"><input type="radio" name="level" value="staff" checked><span>Staff</span></label><label class="radio"><input type="radio" name="level" value="manager"><span>Manager</span></label></div></fieldset>
						<p class="small">Staff can work on candidates. Managers can also add staff. They get an email to create their password.</p>
						<button class="btn btn-navy btn-sm" type="submit">Create account</button>
					</form>
				</aside>
			</div>
<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
