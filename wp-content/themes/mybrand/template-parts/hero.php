<?php
$bg_img = get_theme_mod( 'hero_bg_image', get_template_directory_uri() . '/assets/images/hero-bg.jpg' );
?>
<section id="hero" class="hero">
    <?php if ( $bg_img ) : ?>
        <div class="hero-bg" style="background-image:url(<?php echo esc_url( $bg_img ); ?>)" aria-hidden="true"></div>
    <?php endif; ?>
    <div class="container">
        <div class="hero-inner">

            <div class="hero-content">
                <span class="hero-label">CQC Registered &amp; Rated Good &mdash; Leeds</span>
                <h1 class="hero-headline">Care that puts people <em>first</em></h1>
                <p class="hero-subline">We are a supported living and domiciliary care provider based in Leeds. Trusted by local councils and NHS partners since 2022 to deliver consistent, high-quality care for people with complex needs.</p>
                <div class="hero-cta">
                    <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/free-assessment/' ) ); ?>">Request a Free Assessment</a>
                    <a class="btn btn-outline" href="<?php echo esc_url( home_url( '/services/' ) ); ?>">Our Services</a>
                </div>
                <div class="hero-trust">
                    <span class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>
                        CQC Rated Good
                    </span>
                    <span class="trust-sep" aria-hidden="true">|</span>
                    <span class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Around 40 Members of Staff
                    </span>
                    <span class="trust-sep" aria-hidden="true">|</span>
                    <span class="trust-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        24/7 Support Available
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
                        <div class="float-card-num">3:1</div>
                        <div class="float-card-label">Complex Packages Delivered</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
