<?php get_header(); ?>

<div class="container page-content">
    <div class="content-area">
        <?php if ( have_posts() ) : ?>
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e( 'Latest Posts', 'mybrand' ); ?></h1>
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
    </div><!-- .content-area -->

    <?php get_sidebar(); ?>
</div><!-- .container -->

<?php get_footer(); ?>
