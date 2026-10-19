<?php
defined( 'ABSPATH' ) || exit;
get_header();

global $wp_query;
$base     = hcc_jobs_url();
$get      = function ( $k ) { return isset( $_GET[ $k ] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $k ] ) ) : array(); };
$kw       = isset( $_GET['kw'] ) ? sanitize_text_field( wp_unslash( $_GET['kw'] ) ) : '';
$where    = isset( $_GET['where'] ) ? sanitize_text_field( wp_unslash( $_GET['where'] ) ) : '';
$sort     = isset( $_GET['sort'] ) ? sanitize_key( $_GET['sort'] ) : 'new';
$term_now = is_tax() ? get_queried_object() : null;
if ( $term_now && 'job_sector' === $term_now->taxonomy && ! $get( 'sector' ) ) {
	$_GET['sector'] = array( $term_now->slug );
}
$found = (int) $wp_query->found_posts;
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> / Job Board<?php echo $term_now ? ' / ' . esc_html( $term_now->name ) : ''; ?></div>
			<h1><?php echo $term_now ? esc_html( $term_now->name ) . ' jobs' : 'Find your next shift'; ?></h1>
			<p class="lead">Care, kitchen and hospitality work across England. Every job shows the pay and shift pattern up front.</p>
		</div>
	</section>

	<section class="section">
		<form class="wrap board" method="get" action="<?php echo esc_url( $base ); ?>" id="boardForm">
			<aside class="filters" id="filters" aria-label="Filter jobs">
				<div class="f"><label for="bq">Job title or keyword</label><input type="text" id="bq" name="kw" value="<?php echo esc_attr( $kw ); ?>" placeholder="Chef, carer, nurse…"></div>
				<div class="f"><label for="bw">Town or area</label><input type="text" id="bw" name="where" value="<?php echo esc_attr( $where ); ?>" placeholder="e.g. Southampton"></div>
				<?php
				foreach ( array( 'sector' => array( 'job_sector', 'Sector' ), 'contract' => array( 'contract', 'Contract' ) ) as $param => $t ) :
					$terms = hcc_filter_terms( $t[0] );
					if ( ! $terms ) {
						continue;
					}
					$chosen = $get( $param );
					?>
					<fieldset><legend><?php echo esc_html( $t[1] ); ?></legend>
						<?php foreach ( $terms as $term ) : ?>
							<label class="check"><input type="checkbox" name="<?php echo esc_attr( $param ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>"<?php checked( in_array( $term->slug, $chosen, true ) ); ?>><?php echo esc_html( $term->name ); ?><span class="count"><?php echo (int) $term->count; ?></span></label>
						<?php endforeach; ?>
					</fieldset>
				<?php endforeach; ?>
				<?php $roles = hcc_filter_terms( 'job_type' ); if ( $roles ) : $role_now = $get( 'role' ); ?>
					<div class="f"><label for="fRole">Role</label><select class="select" id="fRole" name="role"><option value="">All roles</option>
						<?php foreach ( $roles as $r ) : ?><option value="<?php echo esc_attr( $r->slug ); ?>"<?php selected( in_array( $r->slug, $role_now, true ) ); ?>><?php echo esc_html( $r->name ); ?></option><?php endforeach; ?>
					</select></div>
				<?php endif; ?>
				<button class="btn btn-navy btn-sm" type="submit">Show jobs</button>
				<a class="clear" href="<?php echo esc_url( $base ); ?>">Clear all filters</a>
			</aside>
			<div>
				<div class="results-bar">
					<span class="n" role="status"><?php echo esc_html( $found . ( 1 === $found ? ' job' : ' jobs' ) . ' found' ); ?></span>
					<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
						<button class="btn btn-ghost btn-sm filter-toggle" type="button" id="filterToggle" aria-controls="filters" aria-expanded="false">Filters</button>
						<label class="sort" for="sort">Sort by <select class="select" id="sort" name="sort"><option value="new"<?php selected( $sort, 'new' ); ?>>Newest</option><option value="pay"<?php selected( $sort, 'pay' ); ?>>Highest pay</option><option value="az"<?php selected( $sort, 'az' ); ?>>A to Z</option></select></label>
					</div>
				</div>
				<?php if ( have_posts() ) : ?>
					<div class="jobs">
						<?php while ( have_posts() ) { the_post(); hcc_job_card(); } ?>
					</div>
					<nav class="pager" aria-label="Pages">
						<?php
						echo paginate_links( array( // phpcs:ignore -- core output.
							'prev_text' => '‹',
							'next_text' => '›',
							'mid_size'  => 1,
						) );
						?>
					</nav>
				<?php else : ?>
					<div class="empty"><b>No jobs match these filters.</b><br>Try removing a filter, or <a href="<?php echo esc_url( hcc_page_url( 'register', '/register/' ) ); ?>">register</a> and we'll tell you when one comes up.</div>
				<?php endif; ?>
			</div>
		</form>
	</section>
</main>
<?php
get_footer();
