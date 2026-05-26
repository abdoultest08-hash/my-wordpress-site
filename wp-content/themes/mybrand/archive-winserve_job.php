<?php get_header(); ?>

<section class="page-hero page-hero--jobs">
    <div class="container">
        <div class="page-hero__inner">
            <span class="section-label"><?php esc_html_e( 'Join Our Team', 'mybrand' ); ?></span>
            <h1 class="page-hero__title"><?php esc_html_e( 'Current Vacancies', 'mybrand' ); ?></h1>
            <p class="page-hero__desc"><?php esc_html_e( 'Be part of a team that truly values its carers. Browse our open roles across Leeds and Cornwall.', 'mybrand' ); ?></p>
        </div>
    </div>
</section>

<section class="section jobs-section">
    <div class="container">

        <!-- ── Filter bar ── -->
        <form class="jobs-filter" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'winserve_job' ) ); ?>">
            <div class="jobs-filter__inner">

                <?php
                $filter_taxonomies = [
                    'job_location'   => __( 'All Locations',   'mybrand' ),
                    'job_department' => __( 'All Departments',  'mybrand' ),
                    'job_contract'   => __( 'All Contract Types', 'mybrand' ),
                ];
                foreach ( $filter_taxonomies as $tax => $placeholder ) :
                    $terms = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => true ] );
                    if ( empty( $terms ) || is_wp_error( $terms ) ) continue;
                    $selected = isset( $_GET[ $tax ] ) ? sanitize_text_field( wp_unslash( $_GET[ $tax ] ) ) : '';
                ?>
                    <select name="<?php echo esc_attr( $tax ); ?>" class="jobs-filter__select">
                        <option value=""><?php echo esc_html( $placeholder ); ?></option>
                        <?php foreach ( $terms as $term ) : ?>
                            <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected, $term->slug ); ?>>
                                <?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary jobs-filter__btn"><?php esc_html_e( 'Filter', 'mybrand' ); ?></button>
                <?php if ( ! empty( array_filter( array_map( fn($t) => $_GET[$t] ?? '', array_keys( $filter_taxonomies ) ) ) ) ) : ?>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'winserve_job' ) ); ?>" class="btn btn-text jobs-filter__clear"><?php esc_html_e( '✕ Clear filters', 'mybrand' ); ?></a>
                <?php endif; ?>
            </div>
        </form>

        <?php
        // Build tax_query from GET params
        $tax_query = [ 'relation' => 'AND' ];
        foreach ( [ 'job_location', 'job_department', 'job_contract' ] as $tax ) {
            if ( ! empty( $_GET[ $tax ] ) ) {
                $tax_query[] = [
                    'taxonomy' => $tax,
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( wp_unslash( $_GET[ $tax ] ) ),
                ];
            }
        }

        $jobs = new WP_Query( [
            'post_type'      => 'winserve_job',
            'posts_per_page' => 20,
            'post_status'    => 'publish',
            'orderby'        => [ 'meta_value' => 'DESC', 'date' => 'DESC' ],
            'meta_key'       => '_job_urgent',
            'tax_query'      => count( $tax_query ) > 1 ? $tax_query : [],
        ] );
        ?>

        <?php if ( $jobs->have_posts() ) : ?>
            <p class="jobs-count">
                <?php printf(
                    esc_html( _n( '%s vacancy found', '%s vacancies found', $jobs->found_posts, 'mybrand' ) ),
                    '<strong>' . esc_html( $jobs->found_posts ) . '</strong>'
                ); ?>
            </p>

            <div class="jobs-grid">
                <?php while ( $jobs->have_posts() ) : $jobs->the_post(); ?>
                    <?php
                    $urgent   = winserve_is_urgent( get_the_ID() );
                    $location = winserve_get_job_terms( get_the_ID(), 'job_location' );
                    $dept     = winserve_get_job_terms( get_the_ID(), 'job_department' );
                    $contract = winserve_get_job_terms( get_the_ID(), 'job_contract' );
                    $salary   = get_post_meta( get_the_ID(), '_job_salary', true );
                    $ref      = get_post_meta( get_the_ID(), '_job_ref', true );
                    ?>
                    <article class="job-card <?php echo $urgent ? 'job-card--urgent' : ''; ?>">
                        <?php if ( $urgent ) : ?>
                            <div class="job-card__urgent-badge"><?php esc_html_e( 'Urgently Hiring', 'mybrand' ); ?></div>
                        <?php endif; ?>

                        <div class="job-card__body">
                            <?php if ( $dept ) : ?>
                                <span class="job-card__dept"><?php echo esc_html( $dept ); ?></span>
                            <?php endif; ?>

                            <h2 class="job-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <div class="job-card__meta">
                                <?php if ( $location ) : ?>
                                    <span class="job-meta-item job-meta-item--location">
                                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <?php echo esc_html( $location ); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( $contract ) : ?>
                                    <span class="job-meta-item job-meta-item--contract">
                                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <?php echo esc_html( $contract ); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( $salary ) : ?>
                                    <span class="job-meta-item job-meta-item--salary">
                                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                                        <?php echo esc_html( $salary ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="job-card__excerpt">
                                <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                            </div>
                        </div>

                        <div class="job-card__footer">
                            <?php if ( $ref ) : ?>
                                <span class="job-card__ref">Ref: <?php echo esc_html( $ref ); ?></span>
                            <?php endif; ?>
                            <a class="btn btn-primary btn-sm" href="<?php the_permalink(); ?>">
                                <?php esc_html_e( 'View & Apply', 'mybrand' ); ?> &rarr;
                            </a>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

        <?php else : ?>
            <div class="jobs-empty">
                <div class="jobs-empty__icon" aria-hidden="true">📋</div>
                <h2><?php esc_html_e( 'No vacancies right now', 'mybrand' ); ?></h2>
                <p><?php esc_html_e( "We don't have any open roles matching your search at the moment. Check back soon or send us your CV speculatively.", 'mybrand' ); ?></p>
                <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
                    <?php esc_html_e( 'Send a Speculative CV', 'mybrand' ); ?>
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Why work with us strip -->
<section class="section section-dark jobs-why">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label"><?php esc_html_e( 'Why Winserve?', 'mybrand' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Happy carers deliver better care. Full stop.', 'mybrand' ); ?></h2>
        </div>
        <div class="jobs-why__grid">
            <?php
            $reasons = [
                [ '💰', __( 'Fair Compensation',  'mybrand' ), __( 'Competitive pay that reflects the vital, skilled work our carers do every day.', 'mybrand' ) ],
                [ '🎓', __( 'Ongoing Training',   'mybrand' ), __( 'Specialist, continuous development — not just a one-day induction and hope for the best.', 'mybrand' ) ],
                [ '🤝', __( 'Genuine Support',    'mybrand' ), __( 'A management team that listens, responds and treats every carer as a valued professional.', 'mybrand' ) ],
                [ '🌱', __( 'Career Growth',      'mybrand' ), __( 'Clear pathways for progression — we grow our people, not just our business.', 'mybrand' ) ],
            ];
            foreach ( $reasons as [ $icon, $title, $desc ] ) : ?>
                <div class="jobs-why__card">
                    <span class="jobs-why__icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
                    <h3><?php echo esc_html( $title ); ?></h3>
                    <p><?php echo esc_html( $desc ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
