<?php
/**
 * Template Name: For candidates (register)
 */
defined( 'ABSPATH' ) || exit;
get_header();
$groups = array();
foreach ( hcc_roles() as $k => $r ) {
	$groups[ $r['group'] ][ $k ] = $r;
}
$focus = array( 'nurse' => '28% 30%', 'chef' => '50% 20%' );
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / For candidates</div>
			<h1>Register with HCC-Temps</h1>
			<p class="lead">Create your account, choose your role and upload your documents. Most candidates are ready to work within 3 days.</p>
		</div>
	</section>

	<section class="section" id="how">
		<div class="wrap">
			<div class="sec-head">
				<span class="eyebrow">How registration works</span>
				<h2>From sign-up to your first shift</h2>
				<p>Everything happens in your own candidate portal, step by step.</p>
			</div>
			<figure class="illo"><img src="<?php echo esc_url( hcc_img( 'team-illustration.jpg' ) ); ?>" alt="Illustration of a chef, nurse, carer, cleaner, barista, receptionist and support worker together under one roof" width="1400" height="788" loading="lazy"></figure>
			<ol class="reg-steps">
				<li><b>Check what you need</b><span>Choose your role below and see the documents to have ready.</span></li>
				<li><b>Create your account</b><span>Name, contact details and a password. Accept the terms and privacy policy.</span></li>
				<li><b>Confirm your email</b><span>Enter the 6-digit code we email you.</span></li>
				<li><b>Answer and upload</b><span>A few questions, then clear photos or PDFs from your phone. We check each one.</span></li>
				<li><b>Ready in 3 days</b><span>Once your checks are complete, you can start picking shifts.</span></li>
			</ol>
		</div>
	</section>

	<section class="section alt" id="roles">
		<div class="wrap">
			<div class="sec-head">
				<span class="eyebrow">Step 1</span>
				<h2>Choose your role</h2>
				<p>Tap a role to see what you need before you start.</p>
			</div>
			<?php foreach ( $groups as $group => $roles ) : ?>
				<div class="role-group"><?php echo esc_html( $group ); ?></div>
				<div class="roles">
					<?php foreach ( $roles as $k => $r ) : ?>
						<button type="button" class="role-pick" aria-pressed="false" aria-controls="role-<?php echo esc_attr( $k ); ?>" data-id="<?php echo esc_attr( $k ); ?>"><img src="<?php echo esc_url( hcc_img( $r['img'] ) ); ?>" alt="" width="96" height="96" loading="lazy"<?php echo isset( $focus[ $k ] ) ? ' style="object-position:' . esc_attr( $focus[ $k ] ) . '"' : ''; ?>><span><b><?php echo esc_html( $r['name'] ); ?></b><small><?php echo esc_html( $r['sub'] ); ?></small></span></button>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<?php foreach ( hcc_roles() as $k => $r ) :
				$docs  = hcc_role_docs( $k );
				$train = hcc_role_training( $k );
				?>
				<div class="role-detail" id="role-<?php echo esc_attr( $k ); ?>" hidden>
					<div>
						<h3>Before you start: <?php echo esc_html( $r['name'] ); ?></h3>
						<p class="rd-sub">Have these ready. You upload them in your portal after creating your account.</p>
						<h4 class="need-h">Documents (<?php echo count( $docs ); ?>)</h4>
						<ul class="need"><?php foreach ( $docs as $d ) : ?><li><span><b><?php echo esc_html( $d['label'] ); ?></b></span></li><?php endforeach; ?></ul>
						<details class="train-box"><summary>Training certificates (<?php echo count( $train ); ?>)<?php echo ! empty( $r['train_note'] ) ? ' · ' . esc_html( $r['train_note'] ) : ''; ?></summary>
							<ul class="need"><?php foreach ( $train as $t ) : ?><li><span><b><?php echo esc_html( $t['label'] ); ?></b></span></li><?php endforeach; ?></ul>
						</details>
						<p class="req-note">Missing something? You can still register and upload what you have. We will tell you what is still needed.</p>
						<div class="form-actions" style="margin-top:20px"><a class="btn btn-sun" href="<?php echo esc_url( hcc_apply_url( $k ) ); ?>">Start registration as <?php echo esc_html( $r['name'] ); ?></a></div>
					</div>
					<aside class="rd-side">
						<h4>About your DBS</h4>
						<p>Every role needs an enhanced DBS on the Update Service. When you register we ask:</p>
						<div class="dbs-opt">
							<div><b>Yes, on the Update Service:</b> we will ask for your certificate in the next step.</div>
							<div><b>Yes, but not on the Update Service:</b> upload your certificate and we tell you if it can be used.</div>
							<div><b>No DBS yet:</b> no problem, we apply for one for you.</div>
						</div>
						<h4 style="margin-top:6px">Photo tips</h4>
						<p>Lay documents flat in daylight, show all four corners, no flash or glare. PDFs are best for certificates. Certificates must show your full name.</p>
					</aside>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="section" id="register">
		<div class="wrap" style="max-width:760px;text-align:center">
			<h2 style="font-size:clamp(21px,2.58vw,32px);color:var(--ink)">Ready to start?</h2>
			<p style="margin-top:10px;color:var(--muted)">Choose your role above, then press Start registration. It takes about 15 minutes and you can stop and come back.</p>
			<p class="login-strip">Already registered? <a href="<?php echo esc_url( hcc_login_url() ); ?>">Log in to your candidate portal</a></p>
		</div>
	</section>

	<section class="section alt" aria-labelledby="faqTitle">
		<div class="wrap">
			<div class="sec-head"><span class="eyebrow">Questions</span><h2 id="faqTitle">Registering with HCC-Temps</h2></div>
			<?php get_template_part( 'template-parts/faq', null, array( 'items' => hcc_faqs( 'candidates' ), 'id' => 'faqReg' ) ); ?>
		</div>
	</section>
</main>
<?php
get_footer();
