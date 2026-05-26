<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <a class="card-thumb-link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
            <?php the_post_thumbnail( 'portfolio-thumb', [ 'class' => 'card-thumb' ] ); ?>
        </a>
    <?php endif; ?>

    <div class="card-body">
        <div class="card-meta">
            <?php the_category( ', ' ); ?>
            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
        </div>
        <?php the_title( '<h2 class="card-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
        <p class="card-excerpt"><?php the_excerpt(); ?></p>
        <a class="btn btn-text" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More &rarr;', 'mybrand' ); ?></a>
    </div>
</article>
