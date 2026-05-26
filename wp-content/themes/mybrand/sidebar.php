<?php if ( is_active_sidebar( 'sidebar-blog' ) ) : ?>
    <aside class="sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Blog Sidebar', 'mybrand' ); ?>">
        <?php dynamic_sidebar( 'sidebar-blog' ); ?>
    </aside>
<?php endif; ?>
