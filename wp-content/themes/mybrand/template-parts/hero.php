<?php
$headline  = get_theme_mod( 'hero_headline',  'Exceptional Care, <em>Every Day</em>' );
$subline   = get_theme_mod( 'hero_subline',   'Winserve Care Services provides compassionate, person-centred support for adults and young people with learning disabilities, autism, mental health needs, and complex care requirements.' );
$cta_text  = get_theme_mod( 'hero_cta_text',  'Request a Free Assessment' );
$cta_url   = get_theme_mod( 'hero_cta_url',   home_url( '/free-assessment/' ) );
$cta2_text = get_theme_mod( 'hero_cta2_text', 'Explore Our Services' );
$cta2_url  = get_theme_mod( 'hero_cta2_url',  home_url( '/services/' ) );
$bg_img    = get_theme_mod( 'hero_bg_image',  'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1400&q=80' );
?>
<section id="hero" class="hero">
    <?php if ( $bg_img ) : ?>
        <div class="hero-bg" style="background-image:url(<?php echo esc_url( $bg_img ); ?>)" aria-hidden="true"></div>
    <?php endif; ?>
    <div class="container">
        <div class="hero-inner">

            <div class="hero-content">
                <span class="hero-label"><?php esc_html_e( 'CQC Registered &amp; Rated Good', 'mybrand' ); ?></span>
                <h1 class="hero-headline"><?php echo wp_kses( $headline, [ 'em' => [], 'strong' => [], 'span' => [ 'class' => [] ] ] ); ?></h1>
                <p class="hero-subline"><?php echo esc_html( $subline ); ?></p>
                <div class="hero-cta">
                    <a class="btn btn-primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
                    <a class="btn btn-outline" href="<?php echo esc_url( $cta2_url ); ?>"><?php echo esc_html( $cta2_text ); ?></a>
                </div>
                <div class="hero-trust">
                    <span class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>
                        <?php esc_html_e( 'CQC Rated Good', 'mybrand' ); ?>
                    </span>
                    <span class="trust-sep" aria-hidden="true">|</span>
                    <span class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <?php esc_html_e( '200+ Service Users Supported', 'mybrand' ); ?>
                    </span>
                    <span class="trust-sep" aria-hidden="true">|</span>
                    <span class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?php esc_html_e( '24/7 Support Available', 'mybrand' ); ?>
                    </span>
                </div>
            </div>

            <div class="hero-image-col" aria-hidden="true">
                <div class="hero-img-wrap">
                    <img src="<?php echo esc_url( $bg_img ); ?>" alt="" loading="eager">
                </div>
                <div class="hero-float-card">
                    <div class="float-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                    <div>
                        <div class="float-card-num">200+</div>
                        <div class="float-card-label"><?php esc_html_e( 'Lives Transformed', 'mybrand' ); ?></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
