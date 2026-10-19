<?php
/**
 * Template Name: Candidate portal (My application)
 */
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( get_permalink() ), hcc_login_url() ) );
	exit;
}
if ( hcc_is_staff() && ! hcc_applicant_for_user( get_current_user_id() ) ) {
	wp_safe_redirect( hcc_staff_url() );
	exit;
}

$id     = hcc_applicant_for_user( get_current_user_id() );
$m      = function ( $k ) use ( $id ) { return get_post_meta( $id, '_hcc_' . $k, true ); };
$defs   = hcc_portal_docs( $id ? $m( 'role' ) : '' );
$docs   = $id ? hcc_pget( $id, 'pdocs' ) : array();
$labels = hcc_doc_statuses();
$stage  = $id ? $m( 'stage' ) : '';
$counts = $id ? hcc_doc_counts( $id ) : array( 'required' => 0, 'approved' => 0, 'received' => 0, 'amend' => 0 );
$notes  = $id ? array_reverse( array_filter( hcc_pget( $id, 'notes' ), function ( $n ) { return ! empty( $n['visible'] ); } ) ) : array();
$errors = array(
	'nofile'    => 'Please choose a file to upload.',
	'size'      => 'That file is larger than 10MB. Please upload a smaller file or a photo.',
	'type'      => 'Please upload a PDF, JPG or PNG file.',
	'small'     => 'That photo is too small to read. Please retake it closer, in good light.',
	'sharecode' => 'Please enter your 9-character share code and your date of birth.',
);
$err   = isset( $_GET['err'] ) ? sanitize_key( $_GET['err'] ) : '';
$saved = isset( $_GET['saved'] ) ? sanitize_key( $_GET['saved'] ) : '';
get_header();
?>
<main id="main" class="portal">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs">Candidate portal<?php echo $id ? ' · ' . esc_html( $m( 'ref' ) ) : ''; ?></div>
			<h1>Hi <?php echo esc_html( wp_get_current_user()->first_name ? wp_get_current_user()->first_name : 'there' ); ?></h1>
			<?php if ( $id ) : ?>
				<p class="lead">Your application for <b><?php echo esc_html( $m( 'role' ) ); ?></b>.</p>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( ! $id ) : ?>
		<section class="section"><div class="wrap" style="max-width:760px"><div class="form-card"><h2>No application found</h2><p class="sub">We couldn't find an application linked to this account. Please call <?php echo esc_html( hcc_opt( 'phone' ) ); ?>.</p></div></div></section>
	<?php else : ?>
		<section class="section" style="padding-top:40px">
			<div class="wrap">
				<ol class="tracker" aria-label="Application progress">
					<?php
					$steps = array( 'Applied', 'Invited', 'Documents', 'Checks', 'Ready to work' );
					// Index of the step the candidate is on now; everything before it is done.
					$cur_map = array( 'new' => 1, 'invited' => 2, 'documents' => 2, 'checks' => 3, 'ready' => 5 );
					$cur     = isset( $cur_map[ $stage ] ) ? $cur_map[ $stage ] : 0;
					foreach ( $steps as $i => $label ) :
						$state = $i < $cur ? 'done' : ( $i === $cur ? 'current' : '' );
						?>
						<li class="<?php echo esc_attr( $state ); ?>"<?php echo 'current' === $state ? ' aria-current="step"' : ''; ?>><span class="dot"><?php echo 'done' === $state ? hcc_icon( 'check', 14 ) : (int) ( $i + 1 ); ?></span><?php echo esc_html( $label ); ?></li>
					<?php endforeach; ?>
				</ol>
				<?php if ( 'closed' === $stage ) : ?><p class="form-alert">This application is no longer progressing. Please call us if you have questions.</p><?php endif; ?>

				<div class="portal-grid">
					<div>
						<?php if ( $err && isset( $errors[ $err ] ) ) : ?><p class="form-alert" role="alert"><?php echo esc_html( $errors[ $err ] ); ?></p><?php endif; ?>
						<?php if ( $saved && isset( $defs[ $saved ] ) ) : ?><p class="form-ok" role="status">Thanks, we've received your <?php echo esc_html( $defs[ $saved ][0] ); ?>. We'll check it and update you here.</p><?php endif; ?>

						<div style="display:flex;justify-content:space-between;gap:12px;align-items:baseline;flex-wrap:wrap">
							<h2 class="portal-h">Your documents</h2>
							<span style="font-weight:700;color:var(--navy)"><?php echo (int) $counts['approved']; ?> of <?php echo (int) $counts['required']; ?> required approved</span>
						</div>
						<div class="progress"><i style="width:<?php echo (int) round( $counts['approved'] / max( 1, $counts['required'] ) * 100 ); ?>%"></i></div>

						<div class="pdocs">
							<?php
							foreach ( $defs as $key => $d ) :
								$doc    = isset( $docs[ $key ] ) ? $docs[ $key ] : array( 'files' => array(), 'status' => '' );
								$status = isset( $doc['status'] ) ? $doc['status'] : '';
								$lab    = $labels[ $status ];
								$locked = 'approved' === $status;
								?>
								<article class="pdoc st-<?php echo esc_attr( $lab[1] ); ?>" id="doc-<?php echo esc_attr( $key ); ?>">
									<header>
										<div><h3><?php echo esc_html( $d[0] ); ?></h3><span class="req<?php echo $d[2] ? '' : ' opt'; ?>"><?php echo $d[2] ? 'Required' : 'If it applies to you'; ?></span></div>
										<span class="pill <?php echo esc_attr( $lab[1] ); ?>"><?php echo esc_html( $lab[0] ); ?></span>
									</header>
									<p class="help-text"><?php echo esc_html( $d[1] ); ?></p>
									<?php if ( 'amend' === $status && ! empty( $doc['reason'] ) ) : ?>
										<div class="amend-box"><b>What we need:</b> <?php echo esc_html( $doc['reason'] ); ?></div>
									<?php endif; ?>
									<?php if ( ! empty( $doc['files'] ) ) : ?>
										<ul class="files">
											<?php foreach ( $doc['files'] as $i => $f ) : ?>
												<li><?php echo hcc_icon( 'doc', 15 ); ?><a href="<?php echo esc_url( hcc_file_url( $id, $key, $i ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $f['file'] ); ?></a><span><?php echo esc_html( wp_date( 'j M', (int) $f['time'] ) ); ?></span></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
									<?php if ( 'sharecode' === $d[3] && ! empty( $doc['code'] ) ) : ?>
										<p class="files-code">Share code <b><?php echo esc_html( $doc['code'] ); ?></b></p>
									<?php endif; ?>

									<?php if ( ! $locked && 'closed' !== $stage ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="pdoc-form">
											<input type="hidden" name="action" value="hcc_portal_upload">
											<input type="hidden" name="doc" value="<?php echo esc_attr( $key ); ?>">
											<?php wp_nonce_field( 'hcc_portal_' . $id ); ?>
											<?php if ( 'sharecode' === $d[3] ) : ?>
												<div class="form-grid" style="margin-top:4px">
													<div class="f"><label for="sc-<?php echo esc_attr( $key ); ?>">Share code</label><input type="text" id="sc-<?php echo esc_attr( $key ); ?>" name="sharecode" placeholder="e.g. W7K 2PX 9QD" maxlength="11" autocomplete="off" value="<?php echo esc_attr( isset( $doc['code'] ) ? $doc['code'] : '' ); ?>"></div>
													<div class="f"><label for="dob-<?php echo esc_attr( $key ); ?>">Date of birth</label><input type="date" id="dob-<?php echo esc_attr( $key ); ?>" name="dob" value="<?php echo esc_attr( isset( $doc['dob'] ) ? $doc['dob'] : '' ); ?>"></div>
												</div>
												<div class="form-actions"><button class="btn btn-navy btn-sm" type="submit"><?php echo empty( $doc['code'] ) ? 'Save share code' : 'Update share code'; ?></button></div>
											<?php else : ?>
												<label class="drop compact" for="f-<?php echo esc_attr( $key ); ?>">
													<input type="file" id="f-<?php echo esc_attr( $key ); ?>" name="files[]" accept="application/pdf,image/jpeg,image/png"<?php echo 'files' === $d[3] ? ' multiple' : ''; ?>>
													<strong><?php echo empty( $doc['files'] ) ? 'Take a photo or choose a file' : ( 'amend' === $status ? 'Upload a new version' : ( 'file' === $d[3] ? 'Replace the file' : 'Add more files' ) ); ?></strong>
													<span class="form-note">PDF, JPG or PNG, up to 10MB<?php echo 'files' === $d[3] ? '. You can choose several.' : ''; ?></span>
													<span class="file-name" aria-live="polite"></span>
												</label>
												<div class="form-actions" style="margin-top:12px"><button class="btn btn-navy btn-sm" type="submit">Upload</button></div>
											<?php endif; ?>
										</form>
										<?php if ( ! empty( $d[4] ) ) : ?>
											<details class="tips"><summary>How to take a clear photo</summary><ul><?php foreach ( $d[4] as $t ) : ?><li><?php echo esc_html( $t ); ?></li><?php endforeach; ?></ul></details>
										<?php endif; ?>
									<?php endif; ?>
								</article>
							<?php endforeach; ?>
						</div>
					</div>

					<aside class="portal-side">
						<div class="side-card">
							<h3>Messages from the team</h3>
							<?php if ( $notes ) : ?>
								<ul class="msgs">
									<?php foreach ( $notes as $n ) : ?>
										<li><p><?php echo nl2br( esc_html( $n['text'] ) ); ?></p><span><?php echo esc_html( $n['by'] . ' · ' . wp_date( 'j M, H:i', (int) $n['time'] ) ); ?></span></li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<p class="small">No messages yet. We'll post updates here and email you.</p>
							<?php endif; ?>
						</div>
						<div class="side-card" style="background:var(--white);border:1px solid var(--line)">
							<h3>Your details</h3>
							<dl class="mini-dl">
								<div><dt>Reference</dt><dd><?php echo esc_html( $m( 'ref' ) ); ?></dd></div>
								<div><dt>Applied</dt><dd><?php echo esc_html( get_the_date( 'j F Y', $id ) ); ?></dd></div>
								<div><dt>Email</dt><dd><?php echo esc_html( $m( 'email' ) ); ?></dd></div>
								<div><dt>Phone</dt><dd><?php echo esc_html( $m( 'phone' ) ); ?></dd></div>
							</dl>
							<p class="small">Something wrong? Call <?php echo esc_html( hcc_opt( 'phone' ) ); ?>.</p>
						</div>
						<div class="side-card" style="background:var(--white);border:1px solid var(--line)">
							<div class="lock"><?php echo hcc_icon( 'lock', 18 ); ?><span>Your documents are stored privately. Only you and HCC's compliance team can see them.</span></div>
							<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
						</div>
					</aside>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
