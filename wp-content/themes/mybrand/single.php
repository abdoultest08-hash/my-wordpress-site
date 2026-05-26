<?php get_header(); ?>

<div class="container page-content">
    <div class="content-area">
        <?php while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article' ); ?>>

                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    <div class="entry-meta">
                        <span class="post-author"><?php the_author_posts_link(); ?></span>
                        <span class="post-date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></span>
                        <?php the_category( ', ' ); ?>
                    </div>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="entry-thumbnail">
                        <?php the_post_thumbnail( 'hero-banner' ); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <footer class="entry-footer">
                    <?php the_tags( '<div class="post-tags">', ', ', '</div>' ); ?>
                </footer>

            </article><!-- #post -->

            <nav class="post-navigation" aria-label="<?php esc_attr_e( 'Post navigation', 'mybrand' ); ?>">
                <?php the_post_navigation( [
                    'prev_text' => '&larr; %title',
                    'next_text' => '%title &rarr;',
                ] ); ?>
            </nav>

            <?php if ( comments_open() || get_comments_number() ) : ?>
                <?php comments_template(); ?>
            <?php endif; ?>

        <?php endwhile; ?>
    </div><!-- .content-area -->

    <?php get_sidebar(); ?>
</div><!-- .container -->

<?php get_footer(); ?>
