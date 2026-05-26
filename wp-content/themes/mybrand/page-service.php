<?php
/**
 * Template Name: Service Page
 * Template Post Type: page
 *
 * Used for individual care service landing pages.
 */

get_header();

$services_nav = [
    'Supported Living'              => '/services/supported-living/',
    'Learning Disabilities'         => '/services/learning-disabilities/',
    'Autism Spectrum'               => '/services/autism-spectrum/',
    'Mental Health'                 => '/services/mental-health/',
    'Physical Disabilities'         => '/services/physical-disabilities/',
    'Complex &amp; Challenging Needs' => '/services/complex-care/',
    'Young Adults (16–25)'          => '/services/young-adults/',
    'Dementia Care'                 => '/services/dementia-care/',
];

while ( have_posts() ) :
    the_post();
    $current_url = get_permalink();
?>

<!-- Page hero -->
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'mybrand' ); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'mybrand' ); ?></a>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Services', 'mybrand' ); ?></a>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span><?php the_title(); ?></span>
        </nav>
        <h1 class="page-hero__title"><?php the_title(); ?></h1>
        <?php if ( has_excerpt() ) : ?>
            <p class="page-hero__desc"><?php the_excerpt(); ?></p>
        <?php endif; ?>
    </div>
</section>

<div class="container">
    <div class="service-page-layout">

        <!-- Main content -->
        <div class="service-page-content">
            <?php the_content(); ?>

            <!-- Default content if no page content yet -->
            <?php if ( ! get_the_content() ) : ?>
                <h2><?php printf( esc_html__( 'About Our %s Service', 'mybrand' ), get_the_title() ); ?></h2>
                <p><?php esc_html_e( 'At Winserve Care Services, we provide compassionate, expert support tailored to the individual needs of each person we work with. Our team is trained to the highest standards and works in close partnership with families, healthcare professionals, and local authorities to deliver outstanding care.', 'mybrand' ); ?></p>
                <p><?php esc_html_e( 'Every care plan is person-centred and regularly reviewed to ensure it continues to meet the individual\'s evolving needs, aspirations, and goals.', 'mybrand' ); ?></p>

                <div class="service-feature-list">
                    <h3><?php esc_html_e( 'What We Provide', 'mybrand' ); ?></h3>
                    <ul>
                        <?php
                        $defaults = [
                            __( 'Personalised, person-centred care plans', 'mybrand' ),
                            __( 'Trained and DBS-checked support staff', 'mybrand' ),
                            __( 'Regular reviews and progress monitoring', 'mybrand' ),
                            __( '24/7 support available where required', 'mybrand' ),
                            __( 'Close partnership with families and professionals', 'mybrand' ),
                            __( 'Positive behaviour support (PBS) approach', 'mybrand' ),
                        ];
                        foreach ( $defaults as $item ) :
                        ?>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <?php echo esc_html( $item ); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <h2><?php esc_html_e( 'Who We Support', 'mybrand' ); ?></h2>
                <p><?php esc_html_e( 'We work with adults and young people (16+) across a range of need levels. We accept referrals from individuals, families, social workers, and commissioning teams.', 'mybrand' ); ?></p>
                <p><?php esc_html_e( 'All of our services are delivered in a safe, supportive environment that promotes dignity, choice, and independence.', 'mybrand' ); ?></p>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="service-page-sidebar">
            <!-- Quick enquiry -->
            <div class="service-sidebar-card service-sidebar-card--cta">
                <h3><?php esc_html_e( 'Request a Free Assessment', 'mybrand' ); ?></h3>
                <p><?php esc_html_e( 'Not sure if this service is right for you? Our team can help. Free, no-obligation assessment available.', 'mybrand' ); ?></p>
                <a class="btn btn-primary btn-full" href="<?php echo esc_url( home_url( '/free-assessment/' ) ); ?>">
                    <?php esc_html_e( 'Book Free Assessment', 'mybrand' ); ?>
                </a>
            </div>

            <!-- All services nav -->
            <div class="service-sidebar-card">
                <h3><?php esc_html_e( 'All Care Services', 'mybrand' ); ?></h3>
                <nav class="service-nav-list" aria-label="<?php esc_attr_e( 'Services navigation', 'mybrand' ); ?>">
                    <?php foreach ( $services_nav as $label => $path ) : ?>
                        <a href="<?php echo esc_url( home_url( $path ) ); ?>"
                           <?php if ( trailingslashit( $current_url ) === trailingslashit( home_url( $path ) ) ) : ?>
                               class="active" aria-current="page"
                           <?php endif; ?>>
                            <?php echo wp_kses_post( $label ); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Contact card -->
            <div class="service-sidebar-card">
                <h3><?php esc_html_e( 'Talk to Our Team', 'mybrand' ); ?></h3>
                <?php $phone = get_theme_mod( 'contact_phone', '0161 123 4567' ); ?>
                <p style="margin-bottom:0.75rem;font-size:0.9rem;color:var(--color-mid-gray);"><?php esc_html_e( 'Speak directly with a care specialist:', 'mybrand' ); ?></p>
                <a class="btn btn-outline-blue btn-full" href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $phone ) ); ?>">
                    <?php echo esc_html( $phone ); ?>
                </a>
            </div>
        </aside>

    </div>
</div>

<?php get_template_part( 'template-parts/assessment-cta' ); ?>

<?php endwhile; ?>
<?php get_footer(); ?>
