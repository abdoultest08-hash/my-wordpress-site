</main><!-- #main-content -->

<footer class="site-footer" id="colophon" role="contentinfo">
    <div class="container footer-inner">

        <!-- Footer widget columns -->
        <div class="footer-widgets">
            <?php foreach ( [ 'footer-1', 'footer-2', 'footer-3' ] as $sidebar ) : ?>
                <?php if ( is_active_sidebar( $sidebar ) ) : ?>
                    <div class="footer-col">
                        <?php dynamic_sidebar( $sidebar ); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div><!-- .footer-widgets -->

        <div class="footer-bottom">
            <!-- Footer navigation -->
            <?php
            wp_nav_menu( [
                'theme_location' => 'footer',
                'container'      => 'nav',
                'container_class'=> 'footer-nav',
                'depth'          => 1,
                'fallback_cb'    => false,
            ] );
            ?>

            <p class="copyright">
                &copy; <?php echo esc_html( date( 'Y' ) ); ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>.
                <?php esc_html_e( 'All rights reserved.', 'mybrand' ); ?>
            </p>
        </div><!-- .footer-bottom -->

    </div><!-- .container -->
</footer><!-- #colophon -->

<?php wp_footer(); ?>
</body>
</html>
