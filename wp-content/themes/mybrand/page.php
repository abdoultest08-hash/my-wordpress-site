<?php get_header(); ?>

<div class="container page-content">
    <article id="post-<?php the_ID(); ?>" <?php post_class( 'page-article' ); ?>>

        <header class="entry-header">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
        </header>

        <?php if ( has_post_thumbnail() ) : ?>
            <div class="entry-thumbnail">
                <?php the_post_thumbnail( 'hero-banner' ); ?>
            </div>
        <?php endif; ?>

        <div class="entry-content">
            <?php the_content(); ?>
            <?php
            wp_link_pages( [
                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'mybrand' ),
                'after'  => '</div>',
            ] );
            ?>
        </div><!-- .entry-content -->

    </article>
</div><!-- .container -->

<?php get_footer(); ?>
