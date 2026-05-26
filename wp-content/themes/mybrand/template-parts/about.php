<?php
$about_title = get_theme_mod( 'about_title', __( 'About Us', 'mybrand' ) );
$about_text  = get_theme_mod( 'about_text',  __( 'We are a passionate team of designers, developers, and strategists dedicated to crafting exceptional digital experiences. Replace this text with your story via Appearance → Customize → About Section.', 'mybrand' ) );
$about_img   = get_theme_mod( 'about_image', '' );
$stats       = [
    [ 'number' => '150+', 'label' => __( 'Projects Delivered', 'mybrand' ) ],
    [ 'number' => '50+',  'label' => __( 'Happy Clients',      'mybrand' ) ],
    [ 'number' => '8+',   'label' => __( 'Years Experience',   'mybrand' ) ],
    [ 'number' => '12',   'label' => __( 'Team Members',       'mybrand' ) ],
];
?>
<section id="about" class="section section-about">
    <div class="container about-inner">

        <div class="about-image-col">
            <?php if ( $about_img ) : ?>
                <img class="about-img" src="<?php echo esc_url( $about_img ); ?>" alt="<?php esc_attr_e( 'About us', 'mybrand' ); ?>" loading="lazy">
            <?php else : ?>
                <div class="about-img about-img-placeholder">
                    <span><?php esc_html_e( 'Add image via Customizer', 'mybrand' ); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="about-content-col">
            <span class="section-label"><?php esc_html_e( 'Who We Are', 'mybrand' ); ?></span>
            <h2 class="section-title"><?php echo esc_html( $about_title ); ?></h2>
            <div class="about-text"><?php echo wp_kses_post( wpautop( $about_text ) ); ?></div>

            <div class="about-stats">
                <?php foreach ( $stats as $stat ) : ?>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo esc_html( $stat['number'] ); ?></span>
                        <span class="stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <a class="btn btn-primary" href="<?php echo esc_url( get_permalink( get_page_by_path( 'about' ) ) ); ?>">
                <?php esc_html_e( 'Learn More About Us', 'mybrand' ); ?>
            </a>
        </div>

    </div>
</section>
