<?php
/**
 * Template Name: About us
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main">
	<section class="about-hero">
		<img src="<?php echo esc_url( has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'full' ) : hcc_img( 'team.jpg' ) ); ?>" alt="" width="1600" height="900">
		<div class="wrap">
			<span class="eyebrow">About HCC-Temps</span>
			<h1>The right people, on shift, when you need them</h1>
		</div>
	</section>

	<section class="section">
		<div class="wrap split">
			<div>
				<span class="eyebrow">Our story</span>
				<h2>We started HCC-Temps to help</h2>
				<p>HCC-Temps was created to support the care industry: to help care homes, hospitals and hospitality businesses find reliable people, and to look after the staff who do that work.</p>
				<p>We believe in continuity of care. Wherever we can, we send familiar, fully vetted staff so residents and guests see the same faces. Our line is open 24 hours, because care doesn't stop at 5pm.</p>
				<div class="facts-row">
					<div><b>24/7</b><span>Phone line, always open</span></div>
					<div><b>2 hrs</b><span>Emergency cover, nationwide</span></div>
					<div><b>3 days</b><span>Registration to ready to work</span></div>
				</div>
			</div>
			<div class="pic"><img src="<?php echo esc_url( hcc_img( 'nurse.jpg' ) ); ?>" alt="Nurse talking with two residents in a care home lounge" width="720" height="540" loading="lazy"></div>
		</div>
	</section>

	<section class="section alt">
		<div class="wrap">
			<div class="sec-head"><span class="eyebrow">What we stand for</span><h2>Why clients and candidates choose us</h2></div>
			<div class="values">
				<?php foreach ( array( array( 'aqua', 'Reliability', 'When we say someone is coming, they come.' ), array( 'navy', 'A steady supply of staff', 'A large pool of checked candidates ready for shifts.' ), array( 'coral', 'Fully vetted', 'DBS, right to work and certificates checked before day one.' ), array( 'sun', 'Caring for vulnerable people', 'Our staff support people who depend on them, and we never forget it.' ), array( 'cocoa', 'We care about everyone', 'Clients and candidates both get a team that listens and calls back.' ), array( 'aqua-deep', 'Cover, fast', 'Emergency cover within 2 hours, anywhere in the UK.' ) ) as $v ) : ?>
					<div class="value"><span class="dot" style="background:var(--<?php echo esc_attr( $v[0] ); ?>)"></span><h3><?php echo esc_html( $v[1] ); ?></h3><p><?php echo esc_html( $v[2] ); ?></p></div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section" id="team">
		<div class="wrap">
			<div class="sec-head"><span class="eyebrow">Meet the team</span><h2>The people behind HCC-Temps</h2><p>Based in Leeds and London, covering the whole UK.</p></div>
			<div class="team">
				<?php foreach ( hcc_team_members() as $m ) : ?>
					<div class="member<?php echo $m['lead'] ? ' lead' : ''; ?>"><img src="<?php echo esc_url( $m['img'] ); ?>" class="skip-lazy" data-skip-lazy alt="Portrait of <?php echo esc_attr( $m['name'] ); ?>" width="640" height="640" decoding="async"><h3><?php echo esc_html( $m['name'] ); ?></h3><p><?php echo esc_html( $m['role'] ); ?></p></div>
				<?php endforeach; ?>
			</div>
			<div class="offices">
				<div class="office"><h3>Leeds</h3><p><?php echo esc_html( hcc_opt( 'address' ) ); ?></p></div>
				<div class="office"><h3>London</h3><p>Our London office, serving the capital and the South.</p></div>
				<div class="office nat"><h3>Nationwide</h3><p>We cover everywhere in the UK. Call <?php echo esc_html( hcc_opt( 'phone' ) ); ?>, 24 hours a day.</p></div>
			</div>
		</div>
	</section>

	<?php
	// Anything typed into the page editor appears here, below the fixed sections.
	$content = get_post_field( 'post_content', get_the_ID() );
	if ( trim( $content ) ) :
		?>
		<section class="section"><div class="wrap entry" style="max-width:820px"><?php the_content(); ?></div></section>
	<?php endif; ?>

	<section class="section alt">
		<div class="wrap big-quote">
			<span class="eyebrow">From Trustpilot</span>
			<div class="stars tp-stars" style="margin-top:14px" aria-label="5 out of 5 stars"><?php echo str_repeat( hcc_icon( 'star', 22 ), 5 ); // phpcs:ignore ?></div>
			<blockquote>"It has been an awesome experience working with the agency as a chef. It has given great work experience, working in different kitchen locations, dealing with different cuisines. It's been pure bliss, growth and work fulfilment."</blockquote>
			<div class="who">Oreoluwa F.</div>
			<div class="role">Chef · Review on Trustpilot</div>
		</div>
	</section>

	<section class="cta" aria-label="Work with us">
		<div class="wrap">
			<h2>Work with HCC-Temps</h2>
			<p>Looking for shifts, or need staff for your service? We'd like to hear from you.</p>
			<div class="hero-actions">
				<a class="btn btn-sun pulse" href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">Start registration</a>
				<a class="btn btn-ghost-light" href="<?php echo esc_url( hcc_page_url( 'clients', '/for-clients/' ) . '#request' ); ?>">Request cover</a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
