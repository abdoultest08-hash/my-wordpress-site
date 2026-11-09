<?php
/**
 * Template Name: Candidate portal
 */
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( get_permalink() ), hcc_login_url() ) );
	exit;
}
// Staff can open any candidate's portal read-only from the Staff portal ("View as candidate").
$preview = hcc_is_staff() && isset( $_GET['preview'] ) ? absint( $_GET['preview'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
if ( hcc_is_staff() && ( ! $preview || 'hcc_applicant' !== get_post_type( $preview ) ) ) {
	wp_safe_redirect( hcc_staff_url() );
	exit;
}
$aid  = $preview ? $preview : hcc_my_record();
$ctx = $preview || ( $aid && 'closed' === hcc_state( $aid ) ) ? 'preview' : 'portal'; // Closed applications are read-only.
if ( $aid && ! $preview && ! hcc_meta( $aid, 'submitted' ) ) {
	wp_safe_redirect( hcc_apply_url() );
	exit;
}
$ok_msgs = array( 'notified' => 'Thank you. We have let the compliance team know about your new files.', 'nothing' => 'There are no new files to tell the team about.', 'answers' => 'Your answers have been saved.', 'email_changed' => 'Your email has been changed. Use the new address next time you log in.', 'email_code_sent' => 'We sent a code to your new email address. Enter it under "Change my email address" below.' );
$email_errs = array(
	'email_code'     => 'That code isn\'t right. Check the email we sent to your new address.',
	'email_code_old' => 'That code has expired or was tried too many times. Please start again.',
	'email_wait'     => 'We sent a code less than 30 seconds ago. Please wait a moment.',
	'email_taken'    => 'There is already an account with that email. Please call us if it\'s yours.',
	'email_email'    => 'Please enter a valid email address.',
	'email_same'     => 'That is already your email address.',
	'email_user'     => 'We could not change your email. Please call us.',
);
$err = isset( $_GET['hcc_err'] ) ? sanitize_key( $_GET['hcc_err'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$ok      = isset( $_GET['hcc_ok'] ) ? sanitize_key( $_GET['hcc_ok'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
get_header();
?>
<main class="app-wrap">
<?php if ( ! $aid ) : ?>
	<section class="section"><div class="wrap" style="max-width:720px"><div class="form-card"><h2>No registration found</h2><p class="sub">This account isn't linked to a candidate registration. Call us on <?php echo esc_html( hcc_opt( 'phone' ) ); ?> and we'll sort it out.</p><p style="margin-top:14px"><a class="btn btn-ghost btn-sm" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a></p></div></div></section>
<?php else :
	$docs  = hcc_items( $aid, 'docs' );
	$train = hcc_items( $aid, 'train' );
	$d     = hcc_counts( $aid, $docs );
	$t     = hcc_counts( $aid, $train );
	$state = hcc_state( $aid );
	$stages = array(
		array( 'Account created', true ),
		array( 'Pre-screening', (bool) hcc_meta( $aid, 'prescreen_done' ) ),
		array( 'Documents', $d['have'] >= $d['need'] && $t['have'] >= $t['need'] ? true : ( $d['have'] ? 'part' : false ) ),
		array( 'Compliance review', 'completed' === $state ? true : 'part' ),
		array( 'Ready to work', 'completed' === $state ),
	);
	if ( ! $preview ) {
		do_action( 'hcc_portal_before', $aid );
	}
	$messages = array_reverse( (array) hcc_meta( $aid, 'messages', array() ) );
	$thread   = array_slice( hcc_thread( $aid ), -30 );
	$base     = $preview ? add_query_arg( 'preview', $aid, hcc_portal_url() ) : hcc_portal_url();
	$nav      = apply_filters( 'hcc_portal_nav', array(
		array( 'dashboard', 'Dashboard', 'home', $base . '#dashboard' ),
		array( 'profile', 'My profile', 'user', $base . '#profile' ),
		array( 'documents', 'My documents', 'doc', $base . '#documents', $d['have'] . '/' . $d['need'] ),
		array( 'training', 'Compliance & training', 'shield', $base . '#training', $t['have'] . '/' . $t['need'] ),
		array( 'updates', 'Notifications', 'bell', $base . '#updates', $messages ? count( $messages ) : '' ),
		array( 'messages', 'Messages', 'chat', $base . '#messages' ),
		array( 'support', 'Support', 'help', $base . '#support' ),
	), $aid, $base );
	hcc_app_open( array(
		'kind'    => 'cand',
		'title'   => 'My portal',
		'sub'     => $preview ? 'Staff preview (read-only)' : 'Candidate portal',
		'items'   => $nav,
		'current' => 'dashboard',
		'who'     => hcc_name( $aid ),
		'logout'  => $preview ? hcc_staff_url( array( 'candidate' => $aid ) ) : wp_logout_url( home_url( '/' ) ),
	) );
	?>
			<?php if ( $preview ) : ?><p class="preview-bar">Staff preview: this is what <?php echo esc_html( hcc_name( $aid ) ); ?> sees. Read-only. <a href="<?php echo esc_url( hcc_staff_url( array( 'candidate' => $aid ) ) ); ?>">Back to their folder</a></p><?php endif; ?>
			<?php if ( isset( $ok_msgs[ $ok ] ) ) : ?><p class="form-ok"><?php echo esc_html( $ok_msgs[ $ok ] ); ?></p><?php endif; ?>
			<?php echo hcc_notice(); // phpcs:ignore ?>

			<section class="view" data-view="dashboard" id="dashboard">
				<div class="dash-hello">
					<div>
						<span class="eyebrow">Welcome back</span>
						<h1>Hi <?php echo esc_html( hcc_meta( $aid, 'first' ) ); ?></h1>
						<p><?php echo esc_html( hcc_role_name( hcc_meta( $aid, 'role_key' ) ) ); ?> · Reference <?php echo esc_html( hcc_meta( $aid, 'ref' ) ); ?></p>
					</div>
				</div>
				<ol class="tracker tracker-light">
					<?php foreach ( $stages as $st ) : ?><li class="<?php echo true === $st[1] ? 'done' : ( 'part' === $st[1] ? 'part' : '' ); ?>"><span></span><?php echo esc_html( $st[0] ); ?></li><?php endforeach; ?>
				</ol>
				<?php do_action( 'hcc_portal_dashboard_top', $aid, $ctx, $base ); ?>
				<div class="dash-tiles">
					<a class="tile" href="#documents"><span class="tile-ico"><?php echo hcc_icon( 'doc', 22 ); // phpcs:ignore ?></span><b><?php echo (int) $d['have']; ?><small> / <?php echo (int) $d['need']; ?></small></b><span>Documents added</span></a>
					<a class="tile" href="#training"><span class="tile-ico"><?php echo hcc_icon( 'shield', 22 ); // phpcs:ignore ?></span><b><?php echo (int) $t['have']; ?><small> / <?php echo (int) $t['need']; ?></small></b><span>Training certificates</span></a>
					<a class="tile" href="#messages"><span class="tile-ico"><?php echo hcc_icon( 'chat', 22 ); // phpcs:ignore ?></span><b><?php echo count( $thread ); ?></b><span>Messages</span></a>
				</div>
				<div class="msg-card dash-status">
					<?php if ( 'closed' === $state ) : ?>
						<div class="msg"><b>Your registration is closed</b><p>Please call us on <?php echo esc_html( hcc_opt( 'phone' ) ); ?> if you have any questions.</p></div>
					<?php elseif ( 'completed' === $state ) : ?>
						<div class="msg msg-ok"><b>All checks complete</b><p>You are ready to work. We will be in touch about shifts.</p></div>
					<?php elseif ( ! $messages ) : ?>
						<div class="msg msg-ok"><b>Registration received</b><p>Thank you. Our compliance team is reviewing your answers and files, usually within 1 working day. Updates from the team will appear here and by email.</p></div>
					<?php endif; ?>
				</div>
				<?php do_action( 'hcc_portal_dashboard', $aid, $ctx, $base ); ?>
			</section>

			<section class="view" data-view="updates" id="updates">
				<div class="view-head"><h2>Notifications</h2><p>What we need from you, updates from the compliance team, and reminders.</p></div>
				<?php do_action( 'hcc_portal_notifications', $aid, $ctx, $base ); ?>
			</section>

			<section class="view" data-view="messages" id="messages">
				<div class="view-head"><h2>Messages</h2><p>Write to the compliance team. They reply here and by email.</p></div>
				<div class="p-card">
				<?php if ( 'msg_sent' === $ok ) : ?><p class="form-ok">Message sent. The team will reply here and by email.</p><?php endif; ?>
				<?php if ( 'msg_empty' === $err ) : ?><p class="form-alert" role="alert">Please write a message first.</p><?php endif; ?>
				<?php if ( 'msg_many' === $err ) : ?><p class="form-alert" role="alert">You've sent a lot of messages today. Please call us on <?php echo esc_html( hcc_opt( 'phone' ) ); ?> if it's urgent.</p><?php endif; ?>
				<?php if ( $thread ) : ?>
					<ol class="thread">
						<?php foreach ( $thread as $m ) : ?>
							<li class="<?php echo 'cand' === $m['from'] ? 'me' : 'team'; ?>"><span class="who"><?php echo esc_html( ( 'cand' === $m['from'] ? 'You' : 'HCC team' ) . ' · ' . wp_date( 'j M, H:i', $m['at'] ) . ( $m['label'] ? ' · about ' . $m['label'] : '' ) ); ?></span><?php echo nl2br( esc_html( $m['text'] ) ); ?></li>
						<?php endforeach; ?>
					</ol>
				<?php else : ?>
					<p class="hint">Questions about your registration or a document? Send us a message here.</p>
				<?php endif; ?>
				<?php if ( 'portal' === $ctx ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="msg-form">
						<input type="hidden" name="action" value="hcc_portal_msg"><input type="hidden" name="hcc_nonce" value="<?php echo esc_attr( wp_create_nonce( 'hcc_portal_msg' ) ); ?>">
						<label for="pmsg" class="screen-reader-text">Your message</label>
						<textarea id="pmsg" name="msg" rows="3" maxlength="2000" placeholder="e.g. I've booked my DBS appointment for Friday." required><?php echo 'training' === ( isset( $_GET['ask'] ) ? sanitize_key( $_GET['ask'] ) : '' ) && hcc_missing_training( $aid ) ? esc_textarea( 'Please help me set up training for: ' . implode( ', ', hcc_missing_training( $aid ) ) . '.' ) : ''; // phpcs:ignore ?></textarea>
						<div class="form-actions" style="margin-top:8px"><button class="btn btn-navy btn-sm" type="submit">Send message</button><span class="form-note">For anything urgent, call <?php echo esc_html( hcc_opt( 'phone' ) ); ?>.</span></div>
					</form>
				<?php endif; ?>
				</div>
			</section>

			<section class="view" data-view="profile" id="profile">
			<div class="view-head"><h2>My profile</h2><p>What you told us at registration.</p></div>
			<details class="p-card p-answers" id="answers" open>
				<summary class="p-card-head"><h2>My answers</h2><span class="p-count">Tap to hide</span></summary>
				<div class="ans-grid">
				<?php foreach ( hcc_answer_groups( $aid, true ) as $title => $rows ) :
					if ( 'Bank details' === $title ) {
						$rows = array( 'Account name' => $rows['Account name'], 'Account' => ! empty( $rows['Account number'] ) ? 'Ending ' . substr( $rows['Account number'], -4 ) . ( $rows['Type'] ? ' · ' . $rows['Type'] : '' ) : 'Not added yet' );
					}
					?>
					<details class="ans-group" open><summary><?php echo esc_html( $title ); ?></summary><dl>
						<?php foreach ( $rows as $k => $v ) : if ( '' === (string) $v && in_array( $k, array( 'Visa / status', 'Share code', 'Certificate number' ), true ) ) { continue; } ?><div><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( '' !== (string) $v ? $v : '—' ); ?></dd></div><?php endforeach; ?>
					</dl></details>
				<?php endforeach; ?>
				</div>
				<?php if ( ! $preview && 'completed' !== $state && 'closed' !== $state ) : ?><div class="form-actions" style="margin-top:14px"><a class="btn btn-ghost btn-sm" href="<?php echo esc_url( add_query_arg( 'step', 4, hcc_apply_url() ) ); ?>">Change my answers</a></div><?php endif; ?>
				<?php if ( ! $preview && 'closed' !== $state ) :
					$pend = (array) hcc_meta( $aid, 'email_pending', array() );
					$pending = ! empty( $pend['email'] ) && time() < (int) $pend['exp'];
					?>
				<details class="email-fix" id="change-email"<?php echo $pending || isset( $email_errs[ $err ] ) || 'email_code_sent' === $ok ? ' open' : ''; ?>>
					<summary>Change my email address</summary>
					<?php if ( isset( $email_errs[ $err ] ) ) : ?><p class="form-alert" role="alert"><?php echo esc_html( $email_errs[ $err ] ); ?></p><?php endif; ?>
					<?php if ( $pending ) : ?>
						<p class="hint">We sent a 6-digit code to <b><?php echo esc_html( $pend['email'] ); ?></b>. Enter it to finish the change. It works for 15 minutes.</p>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="hcc_portal_email"><input type="hidden" name="stage" value="confirm"><input type="hidden" name="hcc_nonce" value="<?php echo esc_attr( wp_create_nonce( 'hcc_portal_email' ) ); ?>">
							<div class="f" style="max-width:260px"><label for="ecode">6-digit code</label><input class="code-input" type="text" id="ecode" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="7" required></div>
							<div class="form-actions" style="margin-top:10px"><button class="btn btn-navy btn-sm" type="submit">Confirm new email</button></div>
						</form>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px">
							<input type="hidden" name="action" value="hcc_portal_email"><input type="hidden" name="stage" value="cancel"><input type="hidden" name="hcc_nonce" value="<?php echo esc_attr( wp_create_nonce( 'hcc_portal_email' ) ); ?>">
							<button class="link-btn" type="submit">Cancel the change</button>
						</form>
					<?php else : ?>
						<p class="hint">Your email is <b><?php echo esc_html( hcc_meta( $aid, 'email' ) ); ?></b>. It's also your username. We'll send a code to the new address to check it's yours.</p>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="hcc_portal_email"><input type="hidden" name="hcc_nonce" value="<?php echo esc_attr( wp_create_nonce( 'hcc_portal_email' ) ); ?>">
							<div class="f" style="max-width:420px"><label for="pnew">New email address</label><input type="email" id="pnew" name="new_email" autocomplete="email" required></div>
							<div class="form-actions" style="margin-top:10px"><button class="btn btn-ghost btn-sm" type="submit">Send code to new email</button></div>
						</form>
					<?php endif; ?>
				</details>
				<?php endif; ?>
			</details>

			</section>

			<section class="view" data-view="documents" id="documents">
			<div class="view-head"><h2>My documents</h2><p>Upload, replace and check your documents.</p></div>
			<div class="p-card" data-set="docs">
				<div class="p-card-head"><h2>Documents</h2><span class="p-count" data-count><?php echo esc_html( $d['have'] . ' of ' . $d['need'] . ' added' ); ?></span></div>
				<?php if ( ! $preview ) : ?><p class="hint" style="margin-bottom:10px">Uploaded something by mistake? Press <b>Replace</b>. Photo tips: lay the document flat in daylight, show all four corners, no flash or glare.</p><?php endif; ?>
				<div class="doc-list p-docs"><?php foreach ( $docs as $k => $it ) { echo hcc_item_row( $aid, $k, $it, $ctx ); } // phpcs:ignore ?></div>
			</div>
			<?php $done_form = 'portal' === $ctx ? '<form class="p-card done-card" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="hcc_portal_done"><input type="hidden" name="hcc_nonce" value="' . esc_attr( wp_create_nonce( 'hcc_portal_done' ) ) . '"><div><b>Added or replaced files?</b><span>We tell the compliance team automatically. To let them know straight away, press the button.</span></div><button class="btn btn-navy btn-sm" type="submit">I\'ve finished uploading</button></form>' : ''; echo $done_form; // phpcs:ignore ?>
			</section>

			<section class="view" data-view="training" id="training">
			<div class="view-head"><h2>Compliance &amp; training</h2><p>Your training certificates and their expiry dates.</p></div>
			<?php do_action( 'hcc_portal_training_top', $aid, $ctx, $base ); ?>
			<div class="p-card" data-set="train">
				<div class="p-card-head"><h2>Training certificates</h2><span class="p-count" data-count><?php echo esc_html( $t['have'] . ' of ' . $t['need'] . ' added' . ( $t['later'] ? ' · ' . $t['later'] . ' to follow' : '' ) ); ?></span></div>
				<?php if ( 'portal' === $ctx ) { echo hcc_training_box( $aid, true, true ); } // phpcs:ignore ?>
				<div class="doc-list p-docs"><?php foreach ( $train as $k => $it ) { echo hcc_item_row( $aid, $k, $it, $ctx ); } // phpcs:ignore ?></div>
			</div>
			<?php echo $done_form; // phpcs:ignore ?>
			</section>
			<?php do_action( 'hcc_portal_views', $aid, $ctx, $base ); ?>

			<section class="view" data-view="support" id="support">
				<div class="view-head"><h2>Support</h2><p>We're here 24 hours a day.</p></div>
				<div class="help"><h3>Questions about your registration?</h3><p>Call the compliance team and quote <?php echo esc_html( hcc_meta( $aid, 'ref' ) ); ?>.</p><div class="line"><?php echo esc_html( hcc_opt( 'phone' ) ); ?></div></div>
				<div class="p-card" style="margin-top:18px">
					<div class="p-card-head"><h2>Other ways to reach us</h2></div>
					<ul class="support-list">
						<li><?php echo hcc_icon( 'chat', 18 ); // phpcs:ignore ?> <a href="#messages">Send a message to the compliance team</a></li>
						<li><?php echo hcc_icon( 'mail', 18 ); // phpcs:ignore ?> <a href="mailto:<?php echo esc_attr( hcc_opt( 'email' ) ); ?>"><?php echo esc_html( hcc_opt( 'email' ) ); ?></a></li>
						<li><?php echo hcc_icon( 'phone', 18 ); // phpcs:ignore ?> <a href="<?php echo esc_attr( hcc_tel( hcc_opt( 'phone' ) ) ); ?>"><?php echo esc_html( hcc_opt( 'phone' ) ); ?></a> (24 hours)</li>
					</ul>
				</div>
			</section>
	<?php hcc_app_close(); ?>
<?php endif; ?>
</main>
<?php
get_footer();
