<?php
$headline  = get_theme_mod( 'hero_headline',  __( 'We Build Brands That Matter', 'mybrand' ) );
$subline   = get_theme_mod( 'hero_subline',   __( 'Your slogan goes here — concise, bold, memorable.', 'mybrand' ) );
$cta_text  = get_theme_mod( 'hero_cta_text',  __( 'See Our Work', 'mybrand' ) );
$cta_url   = get_theme_mod( 'hero_cta_url',   '#portfolio' );
$cta2_text = get_theme_mod( 'hero_cta2_text', __( 'Get In Touch', 'mybrand' ) );
$cta2_url  = get_theme_mod( 'hero_cta2_url',  '#contact' );
$bg_img    = get_theme_mod( 'hero_bg_image',  '' );
?>
<section id="hero" class="hero" <?php if ( $bg_img ) echo 'style="background-image:url(' . esc_url( $bg_img ) . ')"'; ?>>
    <div class="hero-overlay"></div>
    <div class="container hero-inner">
        <div class="hero-content">
            <h1 class="hero-headline"><?php echo wp_kses_post( $headline ); ?></h1>
            <p class="hero-subline"><?php echo esc_html( $subline ); ?></p>
            <div class="hero-cta">
                <a class="btn btn-primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
                <a class="btn btn-outline" href="<?php echo esc_url( $cta2_url ); ?>"><?php echo esc_html( $cta2_text ); ?></a>
            </div>
        </div>
    </div>
    <a class="scroll-indicator" href="#services" aria-label="<?php esc_attr_e( 'Scroll down', 'mybrand' ); ?>">
        <span class="scroll-arrow"></span>
    </a>
</section>
