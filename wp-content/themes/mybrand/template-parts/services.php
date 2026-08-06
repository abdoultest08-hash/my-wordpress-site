<?php
$imgdir = get_template_directory_uri() . '/assets/images';

$services = [
    [
        'slug'      => 'supported-living',
        'title'     => 'Supported Living',
        'desc'      => 'Our main area of expertise. We support adults with complex needs to live in their own homes with the right level of care around them. We have delivered 3:1 ratio packages and are regularly approached by local authorities when they need a provider they can rely on.',
        'highlight' => true,
        'img'       => $imgdir . '/activity-outdoors.png',
        'img_alt'   => 'Winserve carers supporting service users outdoors',
        'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>',
    ],
    [
        'slug'      => 'domiciliary-care',
        'title'     => 'Domiciliary Care',
        'desc'      => 'We provide regular visits to support people in their own homes with personal care, medication, meals, and day-to-day tasks. Our carers are consistent, punctual, and trained to a high standard. Families across Leeds trust us to show up and do the job properly.',
        'highlight' => false,
        'img'       => $imgdir . '/carer-indoor-gift.png',
        'img_alt'   => 'Winserve carer with service user at home',
        'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
    ],
    [
        'slug'      => 'private-care',
        'title'     => 'Private Care Packages',
        'desc'      => 'Not everyone goes through a local authority. If you are arranging care privately, we can work directly with you and your family. We will take the time to understand what you need and build a package around it, without the bureaucracy.',
        'highlight' => false,
        'img'       => $imgdir . '/team-with-user.png',
        'img_alt'   => 'Winserve team with service user',
        'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>',
    ],
    [
        'slug'      => 'learning-disabilities',
        'title'     => 'Learning Disabilities',
        'desc'      => 'Specialist support for people with mild to profound learning disabilities. We focus on building confidence, independence, and quality of life at whatever pace works for the individual — including supported activities in the community.',
        'highlight' => false,
        'img'       => $imgdir . '/activity-swing.png',
        'img_alt'   => 'Carer supporting service user on a swing',
        'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>',
    ],
    [
        'slug'      => 'end-of-life',
        'title'     => 'End of Life Care',
        'desc'      => 'We support individuals and families through one of the most difficult times they will face. Our carers approach end of life care with sensitivity, patience, and deep respect, ensuring the person is comfortable, dignified, and never alone.',
        'highlight' => false,
        'img'       => null,
        'img_alt'   => '',
        'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>',
    ],
    [
        'slug'      => 'mental-health',
        'title'     => 'Mental Health Support',
        'desc'      => 'Recovery-focused support for people with mental health needs. We work alongside community mental health teams and families, keeping communication open and care consistent so that people feel safe and supported day to day.',
        'highlight' => false,
        'img'       => null,
        'img_alt'   => '',
        'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>',
    ],
];
?>
<section id="services" class="section section-services">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">What We Do</span>
            <h2 class="section-title">Our Care Services</h2>
            <p class="section-desc">We specialise in supported living and domiciliary care for adults across Leeds. Our three priority services are listed first — these are where our experience runs deepest.</p>
        </div>

        <div class="services-grid">
            <?php foreach ( $services as $service ) : ?>
                <div class="service-card<?php echo $service['highlight'] ? ' service-card--featured' : ''; ?>">
                    <?php if ( $service['highlight'] ) : ?>
                        <div class="service-card-badge">Our Speciality</div>
                    <?php endif; ?>
                    <?php if ( $service['img'] ) : ?>
                        <div class="service-card-img">
                            <img src="<?php echo esc_url( $service['img'] ); ?>" alt="<?php echo esc_attr( $service['img_alt'] ); ?>" loading="lazy">
                        </div>
                    <?php else : ?>
                        <div class="service-icon-wrap" aria-hidden="true">
                            <?php echo $service['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php endif; ?>
                    <div class="service-card-body">
                        <h3 class="service-title"><?php echo esc_html( $service['title'] ); ?></h3>
                        <p class="service-desc"><?php echo esc_html( $service['desc'] ); ?></p>
                        <a class="service-link" href="<?php echo esc_url( home_url( '/services/' . $service['slug'] . '/' ) ); ?>">
                            Find out more
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section-cta text-center">
            <a class="btn btn-outline-blue" href="<?php echo esc_url( home_url( '/services/' ) ); ?>">
                View All Services
            </a>
        </div>
    </div>
</section>
