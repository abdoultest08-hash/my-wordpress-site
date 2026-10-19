<?php
/**
 * Template Name: Upload documents (private link)
 *
 * Reached from the link in the application email: ?ref=HCC-001234&t=TOKEN.
 * Logged-in staff without a link see a preview with sample details.
 */
defined( 'ABSPATH' ) || exit;

$ref     = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
$token   = isset( $_GET['t'] ) ? sanitize_text_field( wp_unslash( $_GET['t'] ) ) : '';
$id      = hcc_applicant_from_link( $ref, $token );
$preview = ! $id && current_user_can( 'edit_posts' );

if ( $id ) {
	$m     = function ( $k ) use ( $id ) { return get_post_meta( $id, '_hcc_' . $k, true ); };
	$first = $m( 'first' );
	$role  = $m( 'role' );
	$docs  = (array) $m( 'docs' );
	$exp   = (int) $m( 'token_expires' );
} else {
	$first = 'Priya';
	$role  = 'Senior Care Assistant';
	$ref   = 'HCC-001234';
	$docs  = array();
	$exp   = time() + 14 * DAY_IN_SECONDS;
}
$list  = hcc_checklist( $role );
$req   = array_filter( $list, function ( $d ) { return $d[2]; } );
$have  = 0;
foreach ( $req as $d ) {
	$have += isset( $docs[ $d[0] ] ) ? 1 : 0;
}
$saved = isset( $_GET['saved'] ) ? (int) $_GET['saved'] : -1;

get_header();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<?php if ( $id || $preview ) : ?>
				<div class="crumbs">Private link · <?php echo esc_html( $ref ); ?><?php echo $preview ? ' · <b style="color:var(--sun)">Staff preview with sample details</b>' : ''; ?></div>
				<h1>Hi <?php echo esc_html( $first ); ?>, let's get you ready to work</h1>
				<p class="lead">Upload the documents below for your <b><?php echo esc_html( $role ); ?></b> application. Do what you can now and come back to this link later. It works until <?php echo esc_html( wp_date( 'j F Y', $exp ) ); ?>.</p>
			<?php else : ?>
				<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / Upload documents</div>
				<h1>Upload your documents</h1>
				<p class="lead">Use the private link we emailed you after you applied. It opens your personal checklist.</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="section">
		<?php if ( ! $id && ! $preview ) : ?>
			<div class="wrap" style="max-width:760px">
				<div class="form-card" id="docs">
					<?php echo hcc_notice(); // phpcs:ignore -- escaped inside. ?>
					<h2>Can't find your link?</h2>
					<p class="sub">Links work for <?php echo (int) hcc_opt( 'link_valid_days' ); ?> days. If yours has expired or you've lost the email, call us on <?php echo esc_html( hcc_opt( 'phone' ) ); ?> and we'll send a new one straight away.</p>
					<p class="sub">Haven't applied yet? Start with a job on the <a href="<?php echo esc_url( hcc_jobs_url() ); ?>">Job Board</a> or <a href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">register with us</a>.</p>
				</div>
			</div>
		<?php else : ?>
			<div class="wrap upload-wrap">
				<form id="docs" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
					<input type="hidden" name="action" value="hcc_docs">
					<input type="hidden" name="ref" value="<?php echo esc_attr( $ref ); ?>">
					<input type="hidden" name="t" value="<?php echo esc_attr( $token ); ?>">
					<?php wp_nonce_field( 'hcc_docs', 'hcc_nonce' ); ?>
					<?php if ( $saved >= 0 ) : ?>
						<p class="form-ok" role="status"><?php echo $saved ? esc_html( sprintf( 'Thanks, %d document(s) saved. Our compliance team has been told.', $saved ) ) : 'No new files were chosen.'; ?></p>
					<?php endif; ?>
					<?php echo hcc_notice(); // phpcs:ignore -- escaped inside. ?>
					<div style="display:flex;justify-content:space-between;gap:12px;align-items:baseline;flex-wrap:wrap">
						<h2 style="font-size:clamp(24px,3vw,32px);color:var(--ink)">Your checklist</h2>
						<span id="progressText" style="font-weight:700;color:var(--navy)"><?php echo (int) $have; ?> of <?php echo count( $req ); ?> required documents</span>
					</div>
					<div class="progress" role="progressbar" aria-labelledby="progressText" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo (int) round( $have / max( 1, count( $req ) ) * 100 ); ?>"><i style="width:<?php echo (int) round( $have / max( 1, count( $req ) ) * 100 ); ?>%"></i></div>
					<div class="doc-list">
						<?php foreach ( $list as $i => $d ) : $done = isset( $docs[ $d[0] ] ); ?>
							<div class="doc<?php echo $done ? ' done' : ''; ?>" data-label="<?php echo esc_attr( $d[0] ); ?>">
								<div class="ico"><?php echo hcc_icon( $done ? 'check' : 'doc', 20 ); ?></div>
								<div>
									<h3><?php echo esc_html( $d[0] ); ?><span class="req<?php echo $d[2] ? '' : ' opt'; ?>"><?php echo $d[2] ? 'Required' : 'Optional'; ?></span></h3>
									<p><?php echo esc_html( $d[1] ); ?></p>
									<div class="renamed" aria-live="polite"><?php echo $done ? 'Received: ' . esc_html( $docs[ $d[0] ]['file'] ) : ''; ?></div>
								</div>
								<div class="act"><label class="btn btn-ghost btn-sm" for="doc_<?php echo (int) $i; ?>"><?php echo $done ? 'Replace' : 'Choose file'; ?></label><input type="file" id="doc_<?php echo (int) $i; ?>" name="doc_<?php echo (int) $i; ?>" accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx"></div>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="form-actions">
						<button class="btn btn-navy" type="submit"<?php echo $preview ? ' disabled title="Preview only"' : ''; ?>>Send documents to HCC</button>
						<span class="form-note">PDF, Word or photos, up to 10MB each. Send what you have now and come back for the rest.</span>
					</div>
				</form>
				<aside style="display:grid;gap:16px;align-content:start">
					<div class="side-card">
						<h3>Your reference</h3>
						<div class="ref" data-ref="<?php echo esc_attr( $ref ); ?>" data-name="<?php echo esc_attr( $id ? get_post_meta( $id, '_hcc_last', true ) . '-' . $first : 'Shah-Priya' ); ?>"><?php echo esc_html( $ref ); ?></div>
						<p style="font-size:14px;color:var(--muted)">Every file you upload is renamed with this reference, your name, the document type and today's date, so our compliance team can find it straight away.</p>
					</div>
					<div class="side-card" style="background:var(--white);border:1px solid var(--line)">
						<div class="lock"><?php echo hcc_icon( 'lock', 18 ); ?><span>Your documents are stored securely and only HCC's compliance team can see them. They are never public.</span></div>
						<div class="lock"><?php echo hcc_icon( 'clock', 18 ); ?><span>Photos from your phone are fine. Make sure all four corners and the text are clear.</span></div>
					</div>
					<div class="help" style="margin-top:0">
						<h3>Stuck on a document?</h3>
						<p>Call us and we'll talk you through it.</p>
						<div class="line"><a href="<?php echo esc_attr( hcc_tel( hcc_opt( 'phone' ) ) ); ?>"><?php echo esc_html( hcc_opt( 'phone' ) ); ?></a></div>
					</div>
				</aside>
			</div>
		<?php endif; ?>
	</section>
</main>
<?php
get_footer();
