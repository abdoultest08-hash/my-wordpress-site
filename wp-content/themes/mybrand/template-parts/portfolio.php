<?php
// Fetches posts from the 'portfolio' custom post type.
// Register that CPT in functions.php or a plugin, or change post_type to 'post'
// and use a specific category.
$portfolio_query = new WP_Query( [
    'post_type'      => 'portfolio',
    'posts_per_page' => 6,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
] );
?>
<section id="portfolio" class="section section-portfolio section-dark">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label"><?php esc_html_e( 'Our Work', 'mybrand' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Portfolio', 'mybrand' ); ?></h2>
            <p class="section-desc"><?php esc_html_e( 'A selection of projects we\'re proud of.', 'mybrand' ); ?></p>
        </div>

        <?php if ( $portfolio_query->have_posts() ) : ?>
            <!-- Filter tabs — powered by JS -->
            <div class="portfolio-filters" role="tablist" aria-label="<?php esc_attr_e( 'Filter portfolio', 'mybrand' ); ?>">
                <button class="filter-btn active" data-filter="*"><?php esc_html_e( 'All', 'mybrand' ); ?></button>
                <?php
                $terms = get_terms( [ 'taxonomy' => 'portfolio_category', 'hide_empty' => true ] );
                if ( ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $term ) {
                        printf(
                            '<button class="filter-btn" data-filter=".%s">%s</button>',
                            esc_attr( $term->slug ),
                            esc_html( $term->name )
                        );
                    }
                }
                ?>
            </div>

            <div class="portfolio-grid">
                <?php while ( $portfolio_query->have_posts() ) : $portfolio_query->the_post(); ?>
                    <?php
                    $terms      = get_the_terms( get_the_ID(), 'portfolio_category' );
                    $term_slugs = $terms ? implode( ' ', wp_list_pluck( $terms, 'slug' ) ) : '';
                    ?>
                    <article class="portfolio-item <?php echo esc_attr( $term_slugs ); ?>">
                        <a class="portfolio-link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'portfolio-thumb', [ 'class' => 'portfolio-img' ] ); ?>
                            <?php else : ?>
                                <div class="portfolio-img portfolio-placeholder"></div>
                            <?php endif; ?>
                            <div class="portfolio-overlay">
                                <h3 class="portfolio-title"><?php the_title(); ?></h3>
                                <?php if ( $terms ) : ?>
                                    <span class="portfolio-category"><?php echo esc_html( $terms[0]->name ); ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

        <?php else : ?>
            <p class="no-items text-center">
                <?php esc_html_e( 'Portfolio items coming soon. Add posts to the "Portfolio" custom post type.', 'mybrand' ); ?>
            </p>
        <?php endif; ?>

        <div class="text-center section-cta">
            <a class="btn btn-outline-light" href="<?php echo esc_url( get_post_type_archive_link( 'portfolio' ) ); ?>">
                <?php esc_html_e( 'View All Projects', 'mybrand' ); ?>
            </a>
        </div>
    </div>
</section>
