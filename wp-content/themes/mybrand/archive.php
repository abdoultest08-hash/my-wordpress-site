<?php get_header(); ?>

<div class="container page-content">
    <div class="content-area">
        <?php if ( have_posts() ) : ?>

            <header class="page-header">
                <h1 class="page-title">
                    <?php
                    if ( is_category() )      single_cat_title();
                    elseif ( is_tag() )       single_tag_title();
                    elseif ( is_author() )    the_author();
                    elseif ( is_year() )      echo esc_html( get_the_date( 'Y' ) );
                    elseif ( is_month() )     echo esc_html( get_the_date( 'F Y' ) );
                    elseif ( is_day() )       echo esc_html( get_the_date() );
                    else                      esc_html_e( 'Archives', 'mybrand' );
                    ?>
                </h1>
                <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
            </header>

            <div class="posts-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/card', 'post' ); ?>
                <?php endwhile; ?>
            </div>

            <?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?>

        <?php else : ?>
            <p><?php esc_html_e( 'No posts found.', 'mybrand' ); ?></p>
        <?php endif; ?>
    </div>

    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
