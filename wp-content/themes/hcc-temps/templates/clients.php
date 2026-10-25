<?php
/**
 * Template Name: For clients (request cover)
 */
defined( 'ABSPATH' ) || exit;
get_header();
$sent = isset( $_GET['hcc_ok'] );
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / For clients</div>
			<h1>Emergency cover within <span style="color:var(--aqua)">2 hours</span>, nationwide</h1>
			<p class="lead">Registered nurses, care assistants, chefs and hospitality staff for care homes, hospitals, hotels and schools anywhere in the UK. Our line is open 24 hours.</p>
			<div class="hero-actions" style="justify-content:flex-start">
				<a class="btn btn-sun pulse" href="#request">Request cover</a>
				<a class="btn btn-ghost-light" href="<?php echo esc_attr( hcc_tel( hcc_opt( 'phone' ) ) ); ?>">Call <?php echo esc_html( hcc_opt( 'phone' ) ); ?></a>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<div class="sec-head"><span class="eyebrow">Who we supply</span><h2>Staff for every part of your service</h2></div>
			<div class="sector-cards">
				<?php foreach ( array( array( 'nurse.jpg', 'Nursing', 'RGNs and RMNs with a current NMC PIN.' ), array( 'carer.jpg', 'Care', 'Healthcare assistants and senior carers.' ), array( 'chef.jpg', 'Kitchen', 'Chefs, cooks and kitchen assistants.' ), array( 'housekeeping.jpg', 'Hospitality & housekeeping', 'Catering, housekeeping, cleaning and reception.' ) ) as $s ) : ?>
					<div class="s-card"><img src="<?php echo esc_url( hcc_img( $s[0] ) ); ?>" alt="" width="720" height="540" loading="lazy"><div><h3><?php echo esc_html( $s[1] ); ?></h3><p><?php echo esc_html( $s[2] ); ?></p></div></div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section alt">
		<div class="wrap">
			<div class="sec-head"><span class="eyebrow">How it works</span><h2>Three steps to cover</h2></div>
			<ol class="client-steps" style="padding:0">
				<li><h3>Tell us what you need</h3><p>Role, dates, shift times and any skills or training required.</p></li>
				<li><h3>We match and confirm</h3><p>We send vetted staff and confirm names by email before the shift.</p></li>
				<li><h3>They arrive ready to work</h3><p>Compliance checks done, profiles sent ahead, timesheets handled by us.</p></li>
			</ol>
		</div>
	</section>

	<section class="section">
		<div class="wrap" style="max-width:920px">
			<?php if ( $sent ) : ?>
				<div class="form-card success-card" id="request" tabindex="-1">
					<div class="tick"><?php echo hcc_icon( 'check', 30 ); ?></div>
					<h2>Thanks, we have your request</h2>
					<p>A consultant will call you back shortly. We have emailed you a confirmation. For emergency cover call <?php echo esc_html( hcc_opt( 'phone' ) ); ?>, any time, day or night.</p>
				</div>
			<?php else : ?>
				<form class="form-card" id="request" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="hcc_cover">
					<?php wp_nonce_field( 'hcc_cover', 'hcc_nonce' ); ?>
					<div class="hp" aria-hidden="true"><label for="website">Leave this empty</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
					<span class="eyebrow">Request cover</span>
					<h2 style="margin-top:8px">Tell us about the shifts</h2>
					<p class="sub">We'll call you back to confirm. For emergency cover, call <?php echo esc_html( hcc_opt( 'phone' ) ); ?> any time, day or night.</p>
					<?php echo hcc_notice(); // phpcs:ignore -- escaped inside. ?>
					<div class="form-grid">
						<div class="f"><label for="org">Organisation *</label><input type="text" id="org" name="org" autocomplete="organization" required placeholder="e.g. Willow Court Care Home"></div>
						<div class="f"><label for="cn">Your name *</label><input type="text" id="cn" name="name" autocomplete="name" required></div>
						<div class="f"><label for="ce">Email *</label><input type="email" id="ce" name="email" autocomplete="email" required></div>
						<div class="f"><label for="cp">Phone *</label><input type="tel" id="cp" name="phone" autocomplete="tel" required></div>
						<div class="f"><label for="role">Role needed</label><select id="role" name="role"><option>Registered Nurse</option><option>Healthcare Assistant</option><option>Senior Carer</option><option>Chef</option><option>Head Chef</option><option>Kitchen Assistant</option><option>Catering Assistant</option><option>Barista</option><option>Domestic Assistant</option><option>Administrative Assistant</option></select></div>
						<div class="f"><label for="num">Number of staff</label><input type="number" id="num" name="num" min="1" value="1"></div>
						<div class="f"><label for="from">First shift date</label><input type="date" id="from" name="from"></div>
						<div class="f"><label for="pc">Site postcode</label><input type="text" id="pc" name="postcode" autocomplete="postal-code"></div>
						<div class="f full"><label for="notes">Shift times and anything else we should know</label><textarea id="notes" name="notes" placeholder="e.g. 3 long days, 08:00 to 20:00, manual handling training needed"></textarea></div>
					</div>
					<div class="form-actions"><button class="btn btn-navy" type="submit">Send request</button><span class="form-note">By sending this form you agree to our <a href="<?php echo esc_url( hcc_page_url( 'privacy', '/privacy-policy/' ) ); ?>">privacy policy</a>.</span></div>
				</form>
			<?php endif; ?>
		</div>
	</section>

	<section class="section alt">
		<div class="wrap">
			<div class="sec-head"><span class="eyebrow">Questions</span><h2>Hiring through HCC-Temps</h2></div>
			<?php get_template_part( 'template-parts/faq', null, array( 'items' => hcc_faqs( 'clients' ) ) ); ?>
		</div>
	</section>
</main>
<?php
get_footer();
