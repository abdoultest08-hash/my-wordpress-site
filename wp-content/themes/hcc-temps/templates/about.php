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
				<span class="eyebrow">Who we are</span>
				<h2>A healthcare and hospitality recruitment agency</h2>
				<p>HCC-Temps has supplied staff to care homes, hospitals, hotels and schools since 2006. We are based in London and work with clients across England.</p>
				<p>We place registered nurses and healthcare assistants alongside chefs, kitchen staff and cleaners. Every candidate is vetted before their first shift, so you get people who are ready for the job on day one.</p>
				<div class="facts-row">
					<div><b>2006</b><span>Supplying staff since</span></div>
					<div><b>2</b><span>Sectors: healthcare and hospitality</span></div>
					<div><b>4</b><span>Regions across England</span></div>
				</div>
			</div>
			<div class="pic"><img src="<?php echo esc_url( hcc_img( 'nurse.jpg' ) ); ?>" alt="Nurse talking with two residents in a care home lounge" width="720" height="540" loading="lazy"></div>
		</div>
	</section>

	<section class="section alt">
		<div class="wrap">
			<div class="sec-head">
				<span class="eyebrow">What makes us different</span>
				<h2>Nationwide cover, with a team that knows your site</h2>
				<p>We work with NHS trusts, private hospitals, care homes, hotels, restaurants and schools.</p>
			</div>
			<div class="regions">
				<div class="region"><h3>London</h3><p>Our home base, with same-day cover across the capital.</p></div>
				<div class="region"><h3>South</h3><p>Hampshire, Surrey, Kent, the South West and the coast.</p></div>
				<div class="region"><h3>Midlands</h3><p>Birmingham, Solihull, Derbyshire and surrounding areas.</p></div>
				<div class="region"><h3>North</h3><p>Cumbria, the North East and beyond.</p></div>
			</div>
			<div class="settings"><span>NHS trusts</span><span>Private hospitals</span><span>Care and nursing homes</span><span>Hotels</span><span>Restaurants</span><span>Schools</span></div>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<div class="vision">
				<div><span class="eyebrow" style="color:var(--sun)">Our vision</span><h2 style="margin-top:10px">Raising the standard of temporary staffing</h2></div>
				<div>
					<p>We want to be the UK's most trusted staffing partner in healthcare and hospitality. That means sending trained, caring and reliable people who help our clients run safe, well-run services.</p>
					<p>We treat our temps fairly, pay them properly and support their careers, because people who are looked after do better work.</p>
				</div>
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
			<span class="eyebrow">Testimonial</span>
			<div style="margin-top:14px"><?php echo hcc_stars(); // phpcs:ignore -- static SVG. ?></div>
			<blockquote>"HCC-Temps has been an absolute game-changer for our care home. Every temporary nurse and caregiver has been skilled, professional and compassionate. We couldn't be happier with the partnership."</blockquote>
			<div class="who">Sarah L.</div>
			<div class="role">Care Home Manager</div>
		</div>
	</section>

	<section class="cta" aria-label="Work with us">
		<div class="wrap">
			<h2>Work with HCC-Temps</h2>
			<p>Looking for shifts, or need staff for your service? We'd like to hear from you.</p>
			<div class="hero-actions">
				<a class="btn btn-sun pulse" href="<?php echo esc_url( hcc_jobs_url() ); ?>">Browse jobs</a>
				<a class="btn btn-ghost-light" href="<?php echo esc_url( hcc_page_url( 'clients', '/for-clients/' ) ); ?>">Join as a company</a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
