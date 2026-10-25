<?php
defined( 'ABSPATH' ) || exit;
$accred = array(
	array( 'disability-confident.png', 'Disability Confident Committed employer', 437, 204, true ),
	array( 'iso-27001.png', 'ISO 27001 certified, information security', 190, 191, false ),
	array( 'iso-9001.png', 'ISO 9001:2015 certified, quality management', 194, 195, false ),
	array( 'iso-14001.png', 'ISO 14001:2015 certified, environmental management', 177, 187, false ),
	array( 'ico.png', "Registered with the Information Commissioner's Office", 194, 195, false ),
);
$socials = array(
	'social_x'         => array( 'X', '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.8 3h3.1l-6.8 7.7 8 10.3h-6.3l-4.9-6.4L5.3 21H2.2l7.3-8.3L1.8 3h6.4l4.4 5.8zm-1.1 16.2h1.7L7.4 4.7H5.6z"/></svg>' ),
	'social_linkedin'  => array( 'LinkedIn', '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4V21H3zM9.5 9.5h3.8v1.6h.1c.5-1 1.8-2 3.7-2 4 0 4.7 2.6 4.7 6V21h-4v-5.2c0-1.2 0-2.8-1.7-2.8s-2 1.3-2 2.7V21h-4z"/></svg>' ),
	'social_instagram' => array( 'Instagram', '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>' ),
	'social_facebook'  => array( 'Facebook', '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8h3V4h-3c-2.8 0-4.5 1.8-4.5 4.6V11H7v4h2.5v7h4v-7H16l.6-4h-3.1V8.9c0-.6.3-.9.5-.9z"/></svg>' ),
);
?>
<!-- Theme build <?php echo esc_html( HCC_VERSION ); ?> / HCC-Temps -->
<?php if ( is_front_page() || is_page_template( array( 'templates/about.php', 'templates/contact.php' ) ) ) : // Accreditation banner: Home, About and Contact only. ?>
<section class="accred" aria-labelledby="accredTitle">
	<div class="wrap accred-head">
		<span class="eyebrow">Accredited provider</span>
		<h2 id="accredTitle">Recognised standards you can check</h2>
	</div>
	<div class="marquee">
		<div class="marquee-track">
			<?php foreach ( array( false, true ) as $dupe ) : foreach ( $accred as $a ) : ?>
				<div class="accred-item<?php echo $a[4] ? ' wide' : ''; ?>"<?php echo $dupe ? ' aria-hidden="true"' : ''; ?>><img src="<?php echo esc_url( hcc_img( 'accred/' . $a[0] ) ); ?>" alt="<?php echo $dupe ? '' : esc_attr( $a[1] ); ?>" width="<?php echo (int) $a[2]; ?>" height="<?php echo (int) $a[3]; ?>" loading="lazy" decoding="async"></div>
			<?php endforeach; endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<footer class="footer" id="footer">
	<div class="brand-stripe" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
	<div class="wrap">
		<div class="foot-grid">
			<div>
				<a class="foot-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( hcc_logo_url() ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="266" height="144" loading="lazy"></a>
				<p class="foot-about"><?php echo esc_html( hcc_opt( 'company' ) ); ?> supplies registered nurses, care assistants, chefs and hospitality staff nationwide. Our line is open 24 hours a day.</p>
				<div class="socials">
					<?php foreach ( $socials as $key => $s ) : $url = hcc_opt( $key ); if ( ! $url ) { continue; } ?>
						<a href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $s[0] ); ?>" rel="noopener" target="_blank"><?php echo $s[1]; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<div>
				<h4>Candidates</h4>
				<ul>
					<li><a href="<?php echo esc_url( hcc_jobs_url() ); ?>">Find jobs</a></li>
					<li><a href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">Register</a></li>
					<li><a href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) . '#how' ); ?>">How registration works</a></li>
					<li><a href="<?php echo esc_url( hcc_login_url() ); ?>">Candidate login</a></li>
				</ul>
			</div>
			<div>
				<h4>Clients</h4>
				<ul>
					<li><a href="<?php echo esc_url( hcc_page_url( 'clients', '/for-clients/' ) . '#request' ); ?>">Request cover</a></li>
					<li><a href="<?php echo esc_url( hcc_page_url( 'clients', '/for-clients/' ) ); ?>">Emergency cover in 2 hours</a></li>
					<li><a href="<?php echo esc_url( hcc_page_url( 'clients', '/for-clients/' ) ); ?>">Healthcare staffing</a></li>
					<li><a href="<?php echo esc_url( hcc_page_url( 'clients', '/for-clients/' ) ); ?>">Hospitality staffing</a></li>
				</ul>
			</div>
			<div>
				<h4>Company</h4>
				<ul>
					<li><a href="<?php echo esc_url( hcc_page_url( 'about', '/about-us/' ) ); ?>">About us</a></li>
					<li><a href="<?php echo esc_url( home_url( '/#blog' ) ); ?>">Blog</a></li>
					<li><a href="<?php echo esc_url( hcc_page_url( 'contact', '/contact/' ) ); ?>">Contact</a></li>
				</ul>
			</div>
			<div>
				<h4>Contact</h4>
				<ul class="foot-contact">
					<li><?php echo hcc_icon( 'phone' ); ?><span><a href="<?php echo esc_attr( hcc_tel( hcc_opt( 'phone' ) ) ); ?>"><?php echo esc_html( hcc_opt( 'phone' ) ); ?></a> <small class="foot-small">24 hours, 7 days</small></span></li>
					<li><?php echo hcc_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( hcc_opt( 'email' ) ); ?>"><?php echo esc_html( hcc_opt( 'email' ) ); ?></a></li>
					<li><?php echo hcc_icon( 'pin' ); ?><span><?php echo esc_html( hcc_opt( 'address' ) ); ?><small class="foot-small"><?php echo esc_html( hcc_opt( 'address2' ) ); ?></small></span></li>
				</ul>
			</div>
		</div>
		<div class="foot-bottom">
			<span>© <?php echo esc_html( wp_date( 'Y' ) . ' ' . hcc_opt( 'company' ) ); ?> · Registered in England and Wales, company no. <?php echo esc_html( hcc_opt( 'company_no' ) ); ?></span>
			<nav aria-label="Legal"><a href="<?php echo esc_url( hcc_page_url( 'privacy', '/privacy-policy/' ) ); ?>">Privacy policy</a><a href="<?php echo esc_url( hcc_page_url( 'terms', '/terms-and-conditions/' ) ); ?>">Terms &amp; conditions</a><a href="<?php echo esc_url( hcc_page_url( 'privacy', '/privacy-policy/' ) . '#cookies' ); ?>">Cookies</a></nav>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
