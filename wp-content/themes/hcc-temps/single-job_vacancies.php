<?php
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
$j      = hcc_job();
$closed = $j['closing'] && $j['closing'] < current_time( 'Y-m-d' );
?>
<main id="main">
	<section class="job-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / <a href="<?php echo esc_url( hcc_jobs_url() ); ?>">Job Board</a> / <?php echo esc_html( $j['title'] ); ?></div>
			<div class="top">
				<div class="thumb"><img src="<?php echo esc_url( $j['img'] ); ?>" alt="" width="96" height="96"></div>
				<div>
					<?php if ( $j['sector'] ) : ?><span class="sector" style="color:var(--aqua)"><?php echo esc_html( $j['sector'] ); ?></span><?php endif; ?>
					<h1><?php echo esc_html( $j['title'] ); ?></h1>
					<div class="chips">
						<?php if ( 'urgent' === $j['badge'] ) : ?><span class="hot">Urgent</span><?php endif; ?>
						<?php foreach ( array( $j['place'], $j['pay'], $j['contract'], $j['shifts'] ) as $chip ) : if ( $chip ) : ?><span><?php echo esc_html( $chip ); ?></span><?php endif; endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<div class="wrap">
		<div class="job-sheet">
			<div class="job-desc entry">
				<?php the_content(); ?>
			</div>
			<aside>
				<div class="facts">
					<dl>
						<?php foreach ( array( 'Pay' => $j['pay'], 'Location' => $j['place'], 'Contract' => $j['contract'], 'Shifts' => $j['shifts'], 'Closing date' => $j['closing'] ? wp_date( 'j F Y', strtotime( $j['closing'] ) ) : '', 'Job reference' => $j['ref'] ) as $dt => $dd ) : if ( $dd ) : ?>
							<div><dt><?php echo esc_html( $dt ); ?></dt><dd><?php echo esc_html( $dd ); ?></dd></div>
						<?php endif; endforeach; ?>
					</dl>
					<?php if ( ! $closed ) : ?><a class="btn btn-sun pulse" href="#apply">Apply now</a><?php endif; ?>
				</div>
				<div class="help">
					<h3>Questions about this job?</h3>
					<p>Talk to the team before you apply.</p>
					<div class="line"><?php echo hcc_icon( 'phone' ); ?><a href="<?php echo esc_attr( hcc_tel( hcc_opt( 'phone' ) ) ); ?>"><?php echo esc_html( hcc_opt( 'phone' ) ); ?></a></div>
					<div class="line"><?php echo hcc_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( hcc_opt( 'email' ) ); ?>"><?php echo esc_html( hcc_opt( 'email' ) ); ?></a></div>
				</div>
				<?php
				$share = rawurlencode( get_permalink() );
				$text  = rawurlencode( $j['title'] . ' – ' . $j['place'] );
				?>
				<div class="share" aria-label="Share this job">Share
					<a class="dot" style="background:#25D366" href="https://wa.me/?text=<?php echo esc_attr( $text . '%20' . $share ); ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp">W</a>
					<a class="dot" style="background:#1877F2" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $share ); ?>" target="_blank" rel="noopener" aria-label="Share on Facebook">f</a>
					<a class="dot" style="background:#0A66C2" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr( $share ); ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn">in</a>
					<a class="dot" style="background:var(--navy)" href="mailto:?subject=<?php echo esc_attr( $text ); ?>&amp;body=<?php echo esc_attr( $share ); ?>" aria-label="Share by email">@</a>
				</div>
			</aside>
		</div>
	</div>

	<section class="section">
		<div class="wrap" style="max-width:880px">
			<?php if ( $closed ) : ?>
				<div class="form-card" id="apply"><h2>This job has closed</h2><p class="sub">Have a look at our other vacancies, or register and we'll match you to new shifts.</p><div class="form-actions"><a class="btn btn-navy" href="<?php echo esc_url( hcc_jobs_url() ); ?>">See live jobs</a></div></div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/apply-form', null, array( 'job_id' => $j['id'], 'title' => 'Apply for ' . $j['title'] ) ); ?>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$related_args = array( 'posts_per_page' => 3, 'post__not_in' => array( $j['id'] ) );
	$sector_terms = get_the_terms( $j['id'], 'job_sector' );
	if ( $sector_terms && ! is_wp_error( $sector_terms ) ) {
		$related_args['tax_query'] = array_merge( hcc_expired_status_tax_query(), array( array( 'taxonomy' => 'job_sector', 'field' => 'term_id', 'terms' => wp_list_pluck( $sector_terms, 'term_id' ) ) ) );
	}
	$related = hcc_live_jobs( $related_args );
	if ( $related->have_posts() ) :
		?>
		<section class="section alt">
			<div class="wrap">
				<div class="row-head"><h2>Similar jobs</h2><a class="link-arrow" href="<?php echo esc_url( hcc_jobs_url() ); ?>">View all jobs <span aria-hidden="true">→</span></a></div>
				<div class="jobs"><?php while ( $related->have_posts() ) { $related->the_post(); hcc_job_card(); } wp_reset_postdata(); ?></div>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
