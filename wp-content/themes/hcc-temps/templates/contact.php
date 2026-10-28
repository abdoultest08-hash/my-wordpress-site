<?php
/**
 * Template Name: Contact
 */
defined( 'ABSPATH' ) || exit;
get_header();
$sent = isset( $_GET['hcc_ok'] );
?>
<main id="main">
	<section class="page-hero" style="padding-bottom:96px">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / Contact</div>
			<h1>Contact us</h1>
			<p class="lead">Looking for work or need staff? Call, email or send us a message and the right person will reply.</p>
		</div>
	</section>

	<div class="wrap">
		<div class="contact-cards">
			<div class="c-card"><span class="ic" style="background:var(--aqua);color:var(--ink)"><?php echo hcc_icon( 'phone', 22 ); ?></span><div><h3>Call us</h3><p><a href="<?php echo esc_attr( hcc_tel( hcc_opt( 'phone' ) ) ); ?>"><?php echo esc_html( hcc_opt( 'phone' ) ); ?></a></p></div></div>
			<div class="c-card"><span class="ic" style="background:var(--sun);color:var(--ink)"><?php echo hcc_icon( 'mail', 22 ); ?></span><div><h3>Email us</h3><p><a href="mailto:<?php echo esc_attr( hcc_opt( 'email' ) ); ?>"><?php echo esc_html( hcc_opt( 'email' ) ); ?></a></p></div></div>
			<div class="c-card"><span class="ic" style="background:var(--coral);color:var(--white)"><?php echo hcc_icon( 'pin', 22 ); ?></span><div><h3>Head office</h3><p><?php echo esc_html( hcc_opt( 'address' ) ); ?><br><span class="form-note"><?php echo esc_html( hcc_opt( 'address2' ) ); ?></span></p></div></div>
		</div>
	</div>

	<section class="section">
		<div class="wrap contact-grid">
			<div>
				<span class="eyebrow">Write to us</span>
				<h2 style="font-size:clamp(30px,3.6vw,44px);color:var(--ink);margin-top:10px">We reply within one working day</h2>
				<p style="margin-top:14px;color:var(--muted)">For emergency cover, call <?php echo esc_html( hcc_opt( 'phone' ) ); ?>. We answer 24 hours a day and can cover within 2 hours.</p>
				<div class="pic"><img src="<?php echo esc_url( hcc_img( 'reception.jpg' ) ); ?>" alt="Receptionist smiling behind an office front desk" width="720" height="540" loading="lazy"></div>
				<?php get_template_part( 'template-parts/homecare', null, array( 'style' => 'card' ) ); ?>
			</div>
			<?php if ( $sent ) : ?>
				<div class="form-card success-card" id="message" tabindex="-1">
					<div class="tick"><?php echo hcc_icon( 'check', 30 ); ?></div>
					<h2>Thanks, your message is on its way</h2>
					<p>We reply within one working day.<?php echo 'sent_copy' === $_GET['hcc_ok'] ? " We've emailed you a copy." : ''; // phpcs:ignore ?></p>
				</div>
			<?php else : ?>
				<form class="form-card" id="message" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="hcc_contact">
					<?php wp_nonce_field( 'hcc_contact', 'hcc_nonce' ); ?>
					<div class="hp" aria-hidden="true"><label for="website">Leave this empty</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
					<?php echo hcc_notice(); // phpcs:ignore -- escaped inside. ?>
					<fieldset class="f"><legend>I am</legend><div class="radios"><label class="radio"><input type="radio" name="who" value="candidate" checked><span>Looking for work</span></label><label class="radio"><input type="radio" name="who" value="employer"><span>An employer</span></label><label class="radio"><input type="radio" name="who" value="other"><span>Something else</span></label></div></fieldset>
					<div class="form-grid">
						<div class="f"><label for="cname">Full name *</label><input type="text" id="cname" name="name" autocomplete="name" required></div>
						<div class="f"><label for="cemail">Email *</label><input type="email" id="cemail" name="email" autocomplete="email" required></div>
						<div class="f"><label for="cphone">Phone</label><input type="tel" id="cphone" name="phone" autocomplete="tel"></div>
						<div class="f" id="orgField" hidden><label for="corg">Organisation</label><input type="text" id="corg" name="org" autocomplete="organization"></div>
						<div class="f full"><label for="cmsg">How can we help? *</label><textarea id="cmsg" name="message" required></textarea></div>
						<label class="consent full"><input type="checkbox" name="consent" value="1" required>I agree HCC-Temps can use these details to reply to me.</label>
						<label class="consent full"><input type="checkbox" name="copy" value="1" checked>Email me a copy of my message.</label>
					</div>
					<div class="form-actions"><button class="btn btn-navy" type="submit">Send message</button></div>
				</form>
			<?php endif; ?>
		</div>
	</section>

	<section class="section alt">
		<div class="wrap">
			<div class="sec-head"><span class="eyebrow">Questions</span><h2>Frequently asked questions</h2></div>
			<?php get_template_part( 'template-parts/faq', null, array( 'items' => hcc_faqs( 'contact' ) ) ); ?>
		</div>
	</section>
</main>
<?php
get_footer();
