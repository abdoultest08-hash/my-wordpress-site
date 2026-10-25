<?php
/**
 * Template Name: Candidate portal
 */
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( get_permalink() ), hcc_login_url() ) );
	exit;
}
if ( hcc_is_staff() ) {
	wp_safe_redirect( hcc_staff_url() );
	exit;
}
$id = hcc_my_record();
if ( $id && ! hcc_meta( $id, 'submitted' ) ) {
	wp_safe_redirect( hcc_apply_url() );
	exit;
}
$ok_msgs = array( 'notified' => 'Thank you. We have let the compliance team know about your new files.', 'nothing' => 'There are no new files to tell the team about.', 'answers' => 'Your answers have been saved.' );
$ok      = isset( $_GET['hcc_ok'] ) ? sanitize_key( $_GET['hcc_ok'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
get_header();
?>
<main>
<?php if ( ! $id ) : ?>
	<section class="section"><div class="wrap" style="max-width:720px"><div class="form-card"><h2>No registration found</h2><p class="sub">This account isn't linked to a candidate registration. Call us on <?php echo esc_html( hcc_opt( 'phone' ) ); ?> and we'll sort it out.</p></div></div></section>
<?php else :
	$docs  = hcc_items( $id, 'docs' );
	$train = hcc_items( $id, 'train' );
	$d     = hcc_counts( $id, $docs );
	$t     = hcc_counts( $id, $train );
	$state = hcc_state( $id );
	$stages = array(
		array( 'Account created', true ),
		array( 'Pre-screening', (bool) hcc_meta( $id, 'prescreen_done' ) ),
		array( 'Documents', $d['have'] >= $d['need'] && $t['have'] >= $t['need'] ? true : ( $d['have'] ? 'part' : false ) ),
		array( 'Compliance review', 'completed' === $state ? true : 'part' ),
		array( 'Ready to work', 'completed' === $state ),
	);
	$messages = array_reverse( (array) hcc_meta( $id, 'messages', array() ) );
	?>
	<section class="portal-head">
		<div class="wrap">
			<div class="ph-top">
				<div>
					<span class="eyebrow" style="color:var(--aqua)">Candidate portal</span>
					<h1>Hi <?php echo esc_html( hcc_meta( $id, 'first' ) ); ?></h1>
					<p><?php echo esc_html( hcc_role_name( hcc_meta( $id, 'role_key' ) ) ); ?> · Reference <?php echo esc_html( hcc_meta( $id, 'ref' ) ); ?></p>
				</div>
				<a class="btn btn-ghost-light btn-sm" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
			</div>
			<ol class="tracker">
				<?php foreach ( $stages as $s ) : ?><li class="<?php echo true === $s[1] ? 'done' : ( 'part' === $s[1] ? 'part' : '' ); ?>"><span></span><?php echo esc_html( $s[0] ); ?></li><?php endforeach; ?>
			</ol>
		</div>
	</section>

	<section class="section portal-body">
		<div class="wrap">
			<?php if ( isset( $ok_msgs[ $ok ] ) ) : ?><p class="form-ok"><?php echo esc_html( $ok_msgs[ $ok ] ); ?></p><?php endif; ?>
			<?php echo hcc_notice(); // phpcs:ignore ?>
			<div class="msg-card">
				<?php if ( 'closed' === $state ) : ?>
					<div class="msg"><b>Your registration is closed</b><p>Please call us on <?php echo esc_html( hcc_opt( 'phone' ) ); ?> if you have any questions.</p></div>
				<?php elseif ( 'completed' === $state ) : ?>
					<div class="msg msg-ok"><b>All checks complete</b><p>You are ready to work. We will be in touch about shifts.</p></div>
				<?php elseif ( ! $messages ) : ?>
					<div class="msg msg-ok"><b>Registration received</b><p>Thank you. Our compliance team is reviewing your answers and files, usually within 1 working day. You can still add or replace files below.</p></div>
				<?php endif; ?>
				<?php foreach ( array_slice( $messages, 0, 3 ) as $i => $m ) : ?>
					<div class="msg<?php echo $i ? ' msg-old' : ''; ?>"><b>HCC compliance team · <?php echo esc_html( wp_date( 'j M, H:i', (int) $m['at'] ) ); ?></b>
						<?php foreach ( array( 'approved' => 'Approved', 'amend' => 'Please replace', 'missing' => 'Still needed from you', 'note' => 'Messages from our team' ) as $type => $title ) :
							$rows = array_filter( (array) $m['items'], function ( $q ) use ( $type ) { return $q['type'] === $type; } );
							if ( ! $rows ) { continue; } ?>
							<p><b><?php echo esc_html( $title ); ?></b></p><ul><?php foreach ( $rows as $q ) : ?><li><?php echo esc_html( trim( $q['label'] . ( $q['label'] && $q['text'] ? ': ' : '' ) . $q['text'] ) ); ?></li><?php endforeach; ?></ul>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="portal-grid">
				<div class="p-col">
					<div class="p-card" data-set="docs">
						<div class="p-card-head"><h2>Your documents</h2><span class="p-count" data-count><?php echo esc_html( $d['have'] . ' of ' . $d['need'] . ' added' ); ?></span></div>
						<p class="hint" style="margin-bottom:10px">Photo tips: lay the document flat in daylight, show all four corners, no flash or glare. PDF, JPG or PNG up to 10MB.</p>
						<div class="doc-list p-docs"><?php foreach ( $docs as $k => $it ) { echo hcc_item_row( $id, $k, $it, 'portal' ); } // phpcs:ignore ?></div>
					</div>
					<details class="p-card p-folder" data-set="train">
						<summary><span class="folder-ico" aria-hidden="true"><?php echo hcc_icon( 'doc', 22 ); // phpcs:ignore ?></span><span><b>Training certificates</b><small data-count><?php echo esc_html( $t['have'] . ' of ' . $t['need'] . ' added' . ( $t['later'] ? ' · ' . $t['later'] . ' to follow' : '' ) . ' · tap to open' ); ?></small></span><span class="folder-arrow" aria-hidden="true">▾</span></summary>
						<div class="doc-list p-docs"><?php foreach ( $train as $k => $it ) { echo hcc_item_row( $id, $k, $it, 'portal' ); } // phpcs:ignore ?></div>
					</details>
					<form class="p-card done-card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="hcc_portal_done"><input type="hidden" name="hcc_nonce" value="<?php echo esc_attr( wp_create_nonce( 'hcc_portal_done' ) ); ?>">
						<div><b>Added or replaced files?</b><span>We tell the compliance team automatically. To let them know straight away, press the button.</span></div>
						<button class="btn btn-navy btn-sm" type="submit">I've finished uploading</button>
					</form>
				</div>
				<div class="p-col">
					<div class="p-card">
						<div class="p-card-head"><h2>Your details</h2><a class="link-btn" href="<?php echo esc_url( add_query_arg( 'step', 4, hcc_apply_url() ) ); ?>">Edit answers →</a></div>
						<?php
						$i = 0;
						foreach ( hcc_answer_groups( $id, true ) as $title => $rows ) :
							if ( 'Bank details' === $title ) {
								$rows = array( 'Account' => ! empty( $rows['Account number'] ) ? 'Ending ' . substr( $rows['Account number'], -4 ) . ' · ' . $rows['Type'] : 'Not added yet' );
							}
							?>
							<details class="det"<?php echo $i++ < 2 ? ' open' : ''; ?>><summary><?php echo esc_html( $title ); ?></summary><dl>
								<?php foreach ( $rows as $k => $v ) : ?><div><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( '' !== (string) $v ? $v : '—' ); ?></dd></div><?php endforeach; ?>
							</dl></details>
						<?php endforeach; ?>
					</div>
					<div class="help" style="margin-top:0"><h3>Questions about your registration?</h3><p>Call the compliance team, 24 hours a day.</p><div class="line"><?php echo esc_html( hcc_opt( 'phone' ) ); ?></div></div>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>
</main>
<?php
get_footer();
