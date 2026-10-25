<?php
defined( 'ABSPATH' ) || exit;
get_header();

$jobs_url  = hcc_jobs_url();
$clients   = hcc_page_url( 'clients', '/for-clients/' );
$register  = hcc_page_url( 'register', '/register/' );
$job_count = hcc_live_job_count();
$featured  = hcc_live_jobs( array( 'posts_per_page' => 9 ) );
$sectors   = hcc_filter_terms( 'job_sector' );
?>
<main id="main">
	<section class="hero-track" id="heroTrack" aria-label="Introduction">
		<div class="hero-stage" id="heroStage">
			<div class="hero-frame" id="heroFrame" aria-hidden="true"></div>
			<div class="hero-photo" id="heroPhoto">
				<img src="<?php echo esc_url( hcc_hero_image() ); ?>" alt="Registered nurse in a navy uniform smiling in a care home corridor" width="1600" height="900" fetchpriority="high">
				<div class="hero-shade" id="heroShade"></div>
				<span class="hero-tag" id="nurseTag">Registered Nurses <b>NMC PIN holders</b></span>
			</div>
			<div class="hero-chef" id="heroChef">
				<img src="<?php echo esc_url( hcc_img( 'hero-chef.jpg' ) ); ?>" alt="Chef in whites plating meals in a professional kitchen" width="600" height="800">
				<span class="hero-tag">Chefs <b>Level 2/3 certified</b></span>
			</div>
			<div class="hero-copy">
				<h1><?php echo esc_html( hcc_opt( 'hero_title' ) ); ?></h1>
				<p><?php echo esc_html( hcc_opt( 'hero_text' ) ); ?></p>
				<div class="hero-actions">
					<a class="btn btn-sun pulse" href="<?php echo esc_url( $jobs_url ); ?>"><?php echo hcc_icon( 'search', 18 ); // phpcs:ignore ?> Search jobs</a>
					<a class="btn join" href="<?php echo esc_url( $clients ); ?>">Join as a company</a>
				</div>
			</div>
			<div class="scroll-hint" aria-hidden="true">Scroll</div>
		</div>
	</section>

	<section class="usp-sec" aria-label="Why work with HCC-Temps">
		<div class="wrap">
			<div class="usp-grid">
				<a class="usp usp-client" href="<?php echo esc_url( $clients . '#request' ); ?>">
					<span class="usp-for">For employers</span>
					<span class="usp-big"><b>2 hours</b></span>
					<strong>Emergency cover within 2 hours, nationwide.</strong>
					<span class="usp-text">Care, healthcare or hospitality: one call to our 24-hour line and we find vetted staff for your shift, wherever you are in the UK.</span>
					<span class="usp-link">Request cover <span aria-hidden="true">→</span></span>
				</a>
				<a class="usp usp-cand" href="<?php echo esc_url( $register ); ?>">
					<span class="usp-for">For candidates</span>
					<span class="usp-big"><b>3 days</b></span>
					<strong>From registering to ready to work in 3 days.</strong>
					<span class="usp-text">Create your account, upload your documents and our compliance team completes your checks fast.</span>
					<span class="usp-link">Start registration <span aria-hidden="true">→</span></span>
				</a>
			</div>
		</div>
	</section>

	<section class="section priority" aria-labelledby="priorityTitle">
		<div class="wrap">
			<div class="sec-head">
				<span class="eyebrow">Who we're looking for</span>
				<h2 id="priorityTitle">Roles we need most</h2>
				<p>We are always looking for registered nurses, care assistants and chefs to join our team.</p>
			</div>
			<div class="pri-grid">
				<a class="pri pri-main" href="<?php echo esc_url( $register . '#role-nurse' ); ?>">
					<img src="<?php echo esc_url( hcc_img( 'hero-nurse.jpg' ) ); ?>" alt="" width="1600" height="900" loading="lazy" style="object-position:28% 30%">
					<span class="pri-body">
						<span class="pri-badge">Most in demand</span>
						<b>Registered Nurses (RGN / RMN)</b>
						<span class="pri-pay">From £25 per hour +</span>
						<span class="pri-text">Days and nights in nursing homes, care homes and hospitals across the UK. Bring your NMC PIN and we'll do the rest.</span>
						<span class="usp-link">Register as a nurse <span aria-hidden="true">→</span></span>
					</span>
				</a>
				<a class="pri" href="<?php echo esc_url( $register . '#role-hca' ); ?>">
					<img src="<?php echo esc_url( hcc_img( 'carer.jpg' ) ); ?>" alt="" width="720" height="540" loading="lazy">
					<span class="pri-body"><b>Care Assistants</b><span class="pri-pay">From £13.30 per hour</span><span class="usp-link">Register <span aria-hidden="true">→</span></span></span>
				</a>
				<a class="pri" href="<?php echo esc_url( $register . '#role-chef' ); ?>">
					<img src="<?php echo esc_url( hcc_img( 'hero-chef.jpg' ) ); ?>" alt="" width="600" height="800" loading="lazy" style="object-position:50% 25%">
					<span class="pri-body"><b>Chefs</b><span class="pri-pay">From £14 per hour</span><span class="usp-link">Register <span aria-hidden="true">→</span></span></span>
				</a>
			</div>
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
					<h2>Latest vacancies</h2>
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
				<p>Register online in a few minutes. Most candidates are ready to work within 3 days.</p>
			</div>
			<div class="steps">
				<div class="steps-visual">
					<div class="block" aria-hidden="true"></div>
					<div class="block2" aria-hidden="true"></div>
					<div class="photo"><img src="<?php echo esc_url( hcc_img( 'phone.jpg' ) ); ?>" alt="Woman on a sofa registering for work on her phone" width="720" height="960" loading="lazy"></div>
					<div class="ui-card" id="uiCard" aria-live="polite"></div>
				</div>
				<ol class="step-list" id="stepList">
					<li><button class="step-btn" type="button" data-step="0"><span class="num">1</span><span class="t">Create your account</span><span class="d">Your name, contact details and a password. Takes a few minutes.</span></button></li>
					<li><button class="step-btn" type="button" data-step="1"><span class="num">2</span><span class="t">Confirm your email and choose your role</span><span class="d">Enter the code we email you, then pick the role you want, such as nurse or chef.</span></button></li>
					<li><button class="step-btn" type="button" data-step="2"><span class="num">3</span><span class="t">Upload your documents</span><span class="d">Your portal shows the checklist for your role. Clear photos or PDFs, uploaded from your phone.</span></button></li>
					<li><button class="step-btn" type="button" data-step="3"><span class="num">4</span><span class="t">We check everything</span><span class="d">Our compliance team checks your DBS, right to work and certificates. You follow progress in your portal.</span></button></li>
					<li><button class="step-btn" type="button" data-step="4"><span class="num">5</span><span class="t">Ready to work in 3 days</span><span class="d">Pick shifts that suit you. We confirm each one by text and email.</span></button></li>
				</ol>
			</div>
		</div>
	</section>

	<section class="stats-band" aria-label="HCC-Temps in numbers">
		<div class="wrap">
			<div class="stats">
				<div>
					<h2>Nationwide cover, day and night</h2>
					<a class="btn btn-sun" href="<?php echo esc_url( $clients . '#request' ); ?>">Request cover</a>
				</div>
				<div class="nums">
					<?php for ( $n = 1; $n <= 4; $n++ ) : $num = hcc_opt( "stat{$n}_num" ); ?>
						<div><div class="num-big<?php echo 1 === $n ? ' slot' : ''; ?>"<?php echo 1 === $n ? ' data-value="' . esc_attr( rtrim( $num, '+' ) ) . '" data-plus="' . ( '+' === substr( $num, -1 ) ? '1' : '' ) . '" aria-label="' . esc_attr( $num ) . '"' : ''; ?>><?php echo esc_html( $num ); ?></div><div class="num-label"><?php echo esc_html( hcc_opt( "stat{$n}_label" ) ); ?></div></div>
					<?php endfor; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="why" id="why">
		<div class="wrap">
			<h2>Why we make the difference at HCC-Temps</h2>
			<div class="collage" id="collage">
				<div class="stripes" aria-hidden="true"></div>
				<div class="c-stat"><div class="big" data-slot="big">2 hrs</div><div class="lbl" data-slot="lbl">to cover an emergency call, nationwide</div></div>
				<div class="c-dark" data-slot="dark">Reliable, fully vetted staff for people who depend on care, any hour of the day.</div>
				<div class="c-quote q1"><span data-slot="q1">Fully vetted staff: DBS, right to work and training checked before their first shift.</span><span class="cite" data-slot="c1">Our compliance promise</span></div>
				<div class="c-quote q2"><span data-slot="q2">One phone number, answered 24 hours a day by a team that knows your site.</span><span class="cite" data-slot="c2">Our service promise</span></div>
			</div>
			<div class="collage-nav">
				<button class="round" type="button" id="whyPrev" aria-label="Previous"><?php echo hcc_icon( 'left', 18 ); // phpcs:ignore ?></button>
				<button class="round" type="button" id="whyNext" aria-label="Next"><?php echo hcc_icon( 'right', 18 ); // phpcs:ignore ?></button>
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

	<section class="review" aria-label="What our temps say">
		<div class="wrap review-grid">
			<div>
				<div class="quote-mark" aria-hidden="true">“</div>
				<div class="stars tp-stars" aria-label="5 out of 5 stars"><?php echo str_repeat( hcc_icon( 'star', 22 ), 5 ); // phpcs:ignore ?></div>
				<blockquote>HCC is a fantastic company to work with. The staff genuinely care about their employees. HCC assist with DBS, mandatory training and upskilling. This makes you feel like a valued member of staff. Nothing is too much trouble and someone is always on the other end of the phone, when you need them.</blockquote>
				<div class="who">Janine A.</div>
				<div class="role">Review on Trustpilot</div>
				<?php if ( hcc_opt( 'trustpilot' ) ) : ?><a class="link-arrow" style="margin-top:18px;display:inline-flex" href="<?php echo esc_url( hcc_opt( 'trustpilot' ) ); ?>" target="_blank" rel="noopener">Read our reviews on Trustpilot <span aria-hidden="true">→</span></a><?php endif; ?>
			</div>
			<div class="review-photo">
				<img src="<?php echo esc_url( hcc_img( 'carer.jpg' ) ); ?>" alt="Care assistant walking arm in arm with an older man in a garden" width="500" height="500" loading="lazy" style="object-position:55% 30%">
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
		<div class="float f1" aria-hidden="true"><img src="<?php echo esc_url( hcc_img( 'hero-nurse.jpg' ) ); ?>" alt="" style="object-position:28% 25%" loading="lazy"></div>
		<div class="float f2" aria-hidden="true"><img src="<?php echo esc_url( hcc_img( 'chef.jpg' ) ); ?>" alt="" style="object-position:55% 15%" loading="lazy"></div>
		<div class="float f3" aria-hidden="true"><img src="<?php echo esc_url( hcc_img( 'nurse.jpg' ) ); ?>" alt="" style="object-position:58% 20%" loading="lazy"></div>
		<div class="float f4" aria-hidden="true"><img src="<?php echo esc_url( hcc_img( 'kitchen.jpg' ) ); ?>" alt="" style="object-position:58% 12%" loading="lazy"></div>
		<div class="wrap">
			<h2>Get started today</h2>
			<p>Register in a few minutes and be ready to work in 3 days. Need staff? Tell us what you need and we'll call you back.</p>
			<div class="hero-actions">
				<a class="btn btn-sun pulse" href="<?php echo esc_url( $register ); ?>">Start registration</a>
				<a class="btn btn-ghost-light" href="<?php echo esc_url( $clients . '#request' ); ?>">Request cover</a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
