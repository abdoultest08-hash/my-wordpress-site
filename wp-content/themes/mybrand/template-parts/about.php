<?php
$about_img = get_theme_mod( 'about_image', get_template_directory_uri() . '/assets/images/about-team.jpg' );
?>
<section id="about" class="section section-about">
    <div class="container about-inner">

        <div class="about-img-col">
            <?php if ( $about_img ) : ?>
                <img class="about-img" src="<?php echo esc_url( $about_img ); ?>" alt="Winserve care team with service user" loading="lazy">
            <?php else : ?>
                <div class="about-img-placeholder">
                    <span>Add image via Customizer</span>
                </div>
            <?php endif; ?>
            <div class="about-img-badge">
                <strong>2022</strong>
                <span>Trusted by NHS &amp; Councils</span>
            </div>
        </div>

        <div class="about-content-col">
            <span class="section-label">Who We Are</span>
            <h2 class="section-title">Care is at the heart of everything we do</h2>
            <p class="about-text">Winserve Care Services was built on a simple belief: that everyone deserves to be supported with dignity and genuine care. We are a CQC registered provider based in Leeds, delivering supported living and domiciliary care for adults with learning disabilities, autism, mental health needs, and complex support requirements.</p>
            <p class="about-text">We have built strong relationships with local councils and NHS commissioners since 2022, who trust us with their most complex placements because they know we will not cut corners. When a local authority needs a provider that will actually show up for their service users, they call us.</p>

            <ul class="about-bullets">
                <?php
                $bullets = [
                    'CQC Registered and Rated Good — inspected June 2025',
                    'Specialists in supported living, including 3:1 ratio packages',
                    'Trusted by Leeds City Council and NHS partners since 2022',
                    'Around 40 trained and DBS-checked staff',
                    'Private packages available directly to families',
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
                    About Winserve
                </a>
                <a class="btn btn-outline-blue" href="<?php echo esc_url( home_url( '/free-assessment/' ) ); ?>">
                    Book Free Assessment
                </a>
            </div>
        </div>

    </div>
</section>
