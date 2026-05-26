<?php
$about_img = get_theme_mod( 'about_image', 'https://images.unsplash.com/photo-1559839914-17aae19cec71?auto=format&fit=crop&w=900&q=80' );
?>
<section id="about" class="section section-about">
    <div class="container about-inner">

        <div class="about-img-col">
            <?php if ( $about_img ) : ?>
                <img class="about-img" src="<?php echo esc_url( $about_img ); ?>" alt="<?php esc_attr_e( 'Winserve care team member with service user', 'mybrand' ); ?>" loading="lazy">
            <?php else : ?>
                <div class="about-img-placeholder">
                    <span><?php esc_html_e( 'Add image via Customizer', 'mybrand' ); ?></span>
                </div>
            <?php endif; ?>
            <div class="about-img-badge">
                <strong>10+</strong>
                <span><?php esc_html_e( 'Years of Care', 'mybrand' ); ?></span>
            </div>
        </div>

        <div class="about-content-col">
            <span class="section-label"><?php esc_html_e( 'Who We Are', 'mybrand' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Dedicated to Transforming Lives Through Quality Care', 'mybrand' ); ?></h2>
            <p class="about-text"><?php esc_html_e( 'Winserve Care Services was founded with a single purpose: to provide exceptional, compassionate care that empowers individuals to live fulfilling, independent lives. We are a CQC registered provider delivering specialist support across supported living, learning disabilities, autism, mental health, and complex care needs.', 'mybrand' ); ?></p>
            <p class="about-text"><?php esc_html_e( 'Every member of our team is trained to the highest standard, and every care package is individually designed around the person — not a system.', 'mybrand' ); ?></p>

            <ul class="about-bullets">
                <?php
                $bullets = [
                    __( 'CQC Registered Provider — rated Good', 'mybrand' ),
                    __( 'Trained, DBS-checked staff with ongoing CPD', 'mybrand' ),
                    __( 'Personalised care plans reviewed regularly', 'mybrand' ),
                    __( 'Working in partnership with local authorities and NHS', 'mybrand' ),
                ];
                foreach ( $bullets as $bullet ) :
                ?>
                    <li class="about-bullet">
                        <span class="about-bullet-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <?php echo esc_html( $bullet ); ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem;">
                <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">
                    <?php esc_html_e( 'About Winserve', 'mybrand' ); ?>
                </a>
                <a class="btn btn-outline-blue" href="<?php echo esc_url( home_url( '/free-assessment/' ) ); ?>">
                    <?php esc_html_e( 'Book Free Assessment', 'mybrand' ); ?>
                </a>
            </div>
        </div>

    </div>
</section>
