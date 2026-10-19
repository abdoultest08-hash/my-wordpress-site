<?php
defined( 'ABSPATH' ) || exit;
get_header();

$jobs_url  = hcc_jobs_url();
$clients   = hcc_page_url( 'clients', '/for-clients/' );
$job_count = hcc_live_job_count();
$featured  = hcc_live_jobs( array( 'posts_per_page' => 9 ) );
$sectors   = hcc_filter_terms( 'job_sector' );
?>
<main id="main">
	<section class="hero-track" id="heroTrack" aria-label="Introduction">
		<div class="hero-stage" id="heroStage">
			<div class="dots" id="dots" aria-hidden="true"></div>
			<div class="hero-frame" id="heroFrame" aria-hidden="true"></div>
			<div class="hero-photo" id="heroPhoto">
				<img src="<?php echo esc_url( hcc_hero_image() ); ?>" alt="" width="1600" height="900" fetchpriority="high">
				<div class="hero-shade" id="heroShade"></div>
			</div>
			<div class="hero-copy">
				<h1><?php echo esc_html( hcc_opt( 'hero_title' ) ); ?></h1>
				<p><?php echo esc_html( hcc_opt( 'hero_text' ) ); ?></p>
				<div class="hero-actions">
					<a class="btn btn-sun pulse" href="<?php echo esc_url( $jobs_url ); ?>"><?php echo hcc_icon( 'search', 18 ); ?> Search jobs</a>
					<a class="btn join" href="<?php echo esc_url( $clients ); ?>">Join as a company</a>
				</div>
			</div>
			<div class="scroll-hint" aria-hidden="true">Scroll</div>
		</div>
	</section>

	<section class="next-move" id="jobs">
		<div class="wrap">
			<h2>Your next move starts here</h2>
			<form class="search" role="search" method="get" action="<?php echo esc_url( $jobs_url ); ?>">
				<div class="field"><label for="q">Job title</label><input id="q" name="kw" placeholder="Chef, carer, nurse…" autocomplete="off"></div>
				<div class="field"><label for="where">Where</label><input id="where" name="where" placeholder="Town or postcode" autocomplete="off"></div>
				<button class="btn btn-sun" type="submit">Search</button>
			</form>
			<?php if ( $sectors ) : ?>
				<div class="browse" aria-label="Browse by sector">
					<span>Browse by sector</span>
					<?php foreach ( $sectors as $t ) : ?>
						<a class="chip" href="<?php echo esc_url( add_query_arg( 'sector[]', $t->slug, $jobs_url ) ); ?>"><?php echo esc_html( $t->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="featured-head">
				<div>
					<h2>Find the work that works for you</h2>
					<p>New shifts are added every day. These are a few of the latest.</p>
				</div>
				<a class="btn btn-ghost-light btn-sm" href="<?php echo esc_url( $jobs_url ); ?>">View all <?php echo (int) $job_count; ?> vacancies</a>
			</div>

			<div class="jobs" id="jobGrid">
				<?php
				$i = 0;
				while ( $featured->have_posts() ) {
					$featured->the_post();
					hcc_job_card( null, $i >= 6 );
					$i++;
				}
				wp_reset_postdata();
				?>
			</div>
			<div class="jobs-more">
				<?php if ( $i > 6 ) : ?>
					<button class="btn btn-sun" id="moreJobs" type="button" aria-controls="jobGrid" aria-expanded="false" data-href="<?php echo esc_url( $jobs_url ); ?>" data-label="See all <?php echo (int) $job_count; ?> vacancies →">Show more jobs</button>
				<?php else : ?>
					<a class="btn btn-sun" href="<?php echo esc_url( $jobs_url ); ?>">See all vacancies</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="steps-sec" id="how">
		<div class="wrap">
			<div class="sec-head">
				<span class="eyebrow">For candidates</span>
				<h2>It's simple to get started</h2>
				<p>Apply in about a minute. We guide you through the rest by email, one step at a time.</p>
			</div>
			<div class="steps">
				<div class="steps-visual">
					<div class="block" aria-hidden="true"></div>
					<div class="block2" aria-hidden="true"></div>
					<div class="photo"><img src="<?php echo esc_url( hcc_img( 'phone.jpg' ) ); ?>" alt="Woman on a sofa applying for a job on her phone" width="720" height="960" loading="lazy"></div>
					<div class="ui-card" id="uiCard" aria-live="polite"></div>
				</div>
				<ol class="step-list" id="stepList">
					<li><button class="step-btn" type="button" data-step="0"><span class="num">1</span><span class="t">Find a role you like</span><span class="d">Search by role and town. Every job shows the pay, location and shift pattern up front.</span></button></li>
					<li><button class="step-btn" type="button" data-step="1"><span class="num">2</span><span class="t">Apply in about a minute</span><span class="d">Your name, phone number, email and CV. That's all we ask for at this stage.</span></button></li>
					<li><button class="step-btn" type="button" data-step="2"><span class="num">3</span><span class="t">Upload your documents</span><span class="d">We email you a private link with a checklist for your role. Upload now or come back later.</span></button></li>
					<li><button class="step-btn" type="button" data-step="3"><span class="num">4</span><span class="t">We check everything</span><span class="d">Our compliance team checks your DBS, right to work and references, then calls you.</span></button></li>
					<li><button class="step-btn" type="button" data-step="4"><span class="num">5</span><span class="t">Start your shifts</span><span class="d">Pick shifts that suit you. We confirm each one by text and email.</span></button></li>
				</ol>
			</div>
		</div>
	</section>

	<section class="stats-band" aria-label="HCC-Temps in numbers">
		<div class="wrap">
			<div class="stats">
				<div>
					<h2><?php echo $job_count >= 20 ? 'Over ' . esc_html( floor( $job_count / 10 ) * 10 ) . ' jobs open right now' : 'New jobs added every week'; ?></h2>
					<a class="btn btn-sun" href="<?php echo esc_url( $jobs_url ); ?>">Browse jobs now</a>
				</div>
				<div class="nums">
					<?php for ( $n = 1; $n <= 4; $n++ ) : ?>
						<div><div class="num-big"><?php echo esc_html( hcc_opt( "stat{$n}_num" ) ); ?></div><div class="num-label"><?php echo esc_html( hcc_opt( "stat{$n}_label" ) ); ?></div></div>
					<?php endfor; ?>
				</div>
			</div>
		</div>
	</section>

	<?php
	$posts = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
	if ( $posts->have_posts() ) :
		$fallback = array( 'kitchen.jpg', 'nurse.jpg', 'housekeeping.jpg' );
		$blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : '';
		?>
		<section class="blog" id="blog">
			<div class="wrap">
				<div class="row-head">
					<h2>From the HCC blog</h2>
					<?php if ( $blog_url ) : ?><a class="link-arrow" href="<?php echo esc_url( $blog_url ); ?>">View all posts <span aria-hidden="true">→</span></a><?php endif; ?>
				</div>
				<div class="posts">
					<?php $k = 0; while ( $posts->have_posts() ) : $posts->the_post(); $cat = get_the_category(); ?>
						<article class="post">
							<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><img src="<?php echo esc_url( has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'medium_large' ) : hcc_img( $fallback[ $k % 3 ] ) ); ?>" alt="" width="720" height="540" loading="lazy"></a>
							<?php if ( $cat ) : ?><div class="cat"><?php echo esc_html( $cat[0]->name ); ?></div><?php endif; ?>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						</article>
					<?php $k++; endwhile; wp_reset_postdata(); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="why" id="why">
		<div class="wrap">
			<h2>Why we make the difference at HCC-Temps</h2>
			<div class="collage" id="collage">
				<div class="stripes" aria-hidden="true"></div>
				<div class="c-stat"><div class="big" data-slot="big">96%</div><div class="lbl" data-slot="lbl">of shifts filled on the first call</div></div>
				<div class="c-dark" data-slot="dark">Care homes, schools and hotels across England rely on us for cover.</div>
				<blockquote class="c-quote q1"><span data-slot="q1">"They found us a chef for the same afternoon when ours called in sick. The food was great and the residents never noticed the change."</span><cite data-slot="c1">Care home manager, Cumbria</cite></blockquote>
				<blockquote class="c-quote q2"><span data-slot="q2">"Every nurse they send has their paperwork checked before they arrive. That saves our team hours every week."</span><cite data-slot="c2">Deputy manager, nursing home, Solihull</cite></blockquote>
				<div class="c-pill" aria-hidden="true">Sample figures</div>
			</div>
			<div class="collage-nav">
				<button class="round" type="button" id="whyPrev" aria-label="Previous"><?php echo hcc_icon( 'left', 18 ); ?></button>
				<button class="round" type="button" id="whyNext" aria-label="Next"><?php echo hcc_icon( 'right', 18 ); ?></button>
			</div>
		</div>
	</section>

	<section class="review" aria-label="What our temps say">
		<div class="wrap review-grid">
			<div>
				<div class="quote-mark" aria-hidden="true">“</div>
				<?php echo hcc_stars(); // phpcs:ignore -- static SVG. ?>
				<blockquote id="revText">I applied on my phone on the bus home. My documents were checked by Thursday and I worked my first shift on Monday.</blockquote>
				<div class="who" id="revWho">Daniel A.</div>
				<div class="role" id="revRole">Hospitality assistant · sample review</div>
				<div class="controls">
					<button class="round" type="button" id="revPrev" aria-label="Previous review"><?php echo hcc_icon( 'left', 18 ); ?></button>
					<button class="round" type="button" id="revNext" aria-label="Next review"><?php echo hcc_icon( 'right', 18 ); ?></button>
				</div>
			</div>
			<div class="review-photo">
				<div class="dotgrid" aria-hidden="true"></div>
				<img id="revImg" src="<?php echo esc_url( hcc_img( 'hospman.jpg' ) ); ?>" alt="Portrait of the reviewer" width="500" height="500">
			</div>
		</div>
	</section>

	<section class="section alt" id="faq" aria-labelledby="faqTitle">
		<div class="wrap">
			<div class="sec-head">
				<span class="eyebrow">Questions</span>
				<h2 id="faqTitle">Frequently asked questions</h2>
			</div>
			<div class="faq-tabs" role="tablist" aria-label="Who is asking">
				<button role="tab" id="tabCand" aria-selected="true" aria-controls="faqCand" type="button">I'm looking for work</button>
				<button role="tab" id="tabEmp" aria-selected="false" aria-controls="faqEmp" type="button" tabindex="-1">I need staff</button>
			</div>
			<?php
			get_template_part( 'template-parts/faq', null, array( 'items' => hcc_faqs( 'candidates' ), 'id' => 'faqCand', 'attrs' => 'role="tabpanel" aria-labelledby="tabCand"' ) );
			get_template_part( 'template-parts/faq', null, array( 'items' => hcc_faqs( 'employers' ), 'id' => 'faqEmp', 'attrs' => 'role="tabpanel" aria-labelledby="tabEmp" hidden' ) );
			?>
		</div>
	</section>

	<section class="cta" aria-label="Get started">
		<div class="blob" style="width:18px;height:18px;background:var(--coral);left:24%;top:30%" aria-hidden="true"></div>
		<div class="blob" style="width:12px;height:12px;background:var(--sun);right:28%;top:18%" aria-hidden="true"></div>
		<div class="blob" style="width:22px;height:22px;background:var(--aqua);right:24%;bottom:26%" aria-hidden="true"></div>
		<div class="blob" style="width:14px;height:14px;background:var(--cocoa);left:30%;bottom:20%" aria-hidden="true"></div>
		<div class="float f1" aria-hidden="true"><img src="<?php echo esc_url( hcc_img( 'hero.jpg' ) ); ?>" alt="" style="object-position:30% 25%" loading="lazy"></div>
		<div class="float f2" aria-hidden="true"><img src="<?php echo esc_url( hcc_img( 'chef.jpg' ) ); ?>" alt="" style="object-position:55% 15%" loading="lazy"></div>
		<div class="float f3" aria-hidden="true"><img src="<?php echo esc_url( hcc_img( 'nurse.jpg' ) ); ?>" alt="" style="object-position:58% 20%" loading="lazy"></div>
		<div class="float f4" aria-hidden="true"><img src="<?php echo esc_url( hcc_img( 'kitchen.jpg' ) ); ?>" alt="" style="object-position:58% 12%" loading="lazy"></div>
		<div class="wrap">
			<h2>Get started today</h2>
			<p>Register in a minute and we'll be in touch about shifts near you. Need staff? Tell us what you need and we'll call you back.</p>
			<div class="hero-actions">
				<a class="btn btn-sun pulse" href="<?php echo esc_url( $jobs_url ); ?>">Browse jobs now</a>
				<a class="btn btn-ghost-light" href="<?php echo esc_url( $clients ); ?>">Join as a company</a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
