<?php get_header(); ?>

<div class="container page-content">
    <section class="error-404">
        <header class="page-header">
            <h1 class="page-title"><?php esc_html_e( '404 — Page Not Found', 'mybrand' ); ?></h1>
        </header>
        <div class="page-content">
            <p><?php esc_html_e( 'It looks like nothing was found at this location.', 'mybrand' ); ?></p>
            <?php get_search_form(); ?>
            <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php esc_html_e( '&larr; Back to Home', 'mybrand' ); ?>
            </a>
        </div>
    </section>
</div>

<?php get_footer(); ?>
