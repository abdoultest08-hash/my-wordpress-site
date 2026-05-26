<?php
/**
 * Template Name: For Commissioners
 * Template Post Type: page
 */

get_header();
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'mybrand' ); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'mybrand' ); ?></a>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span><?php esc_html_e( 'For Commissioners', 'mybrand' ); ?></span>
        </nav>
        <h1 class="page-hero__title"><?php esc_html_e( 'Information for Local Authorities &amp; Commissioners', 'mybrand' ); ?></h1>
        <p class="page-hero__desc"><?php esc_html_e( 'Winserve Care Services is a trusted, CQC-registered provider delivering quality specialist care in partnership with local authorities, NHS Trusts, and Integrated Care Boards across the UK.', 'mybrand' ); ?></p>
    </div>
</section>

<?php get_template_part( 'template-parts/commissioners' ); ?>

<section class="section">
    <div class="container">

        <?php while ( have_posts() ) : the_post(); ?>
            <?php if ( get_the_content() ) : ?>
                <div class="entry-content"><?php the_content(); ?></div>
            <?php else : ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:start;">
                    <div>
                        <h2 style="font-family:var(--font-heading);font-size:1.75rem;font-weight:700;color:var(--color-primary);margin-bottom:1rem;">
                            <?php esc_html_e( 'Our Partnership Approach', 'mybrand' ); ?>
                        </h2>
                        <p style="color:var(--color-mid-gray);margin-bottom:1rem;line-height:1.8;"><?php esc_html_e( 'We understand that commissioners need providers they can trust — partners who are transparent, accountable, and consistently deliver excellent outcomes for the people they support.', 'mybrand' ); ?></p>
                        <p style="color:var(--color-mid-gray);margin-bottom:1rem;line-height:1.8;"><?php esc_html_e( 'At Winserve, we proactively communicate with commissioning teams, provide regular outcome reports, and work collaboratively to manage risk and ensure best value.', 'mybrand' ); ?></p>
                        <p style="color:var(--color-mid-gray);line-height:1.8;"><?php esc_html_e( 'Our CQC registration, robust safeguarding policies, and experienced management team give commissioners the confidence that every placement is in safe hands.', 'mybrand' ); ?></p>
                    </div>
                    <div>
                        <div style="background:var(--color-blue-light);border-radius:12px;padding:2.5rem;">
                            <h3 style="font-family:var(--font-heading);font-size:1.2rem;font-weight:700;color:var(--color-primary);margin-bottom:1.5rem;"><?php esc_html_e( 'Making a Referral', 'mybrand' ); ?></h3>
                            <p style="color:var(--color-mid-gray);font-size:0.95rem;margin-bottom:1.5rem;"><?php esc_html_e( 'To make a referral or discuss a potential placement, please contact our commissioning team directly:', 'mybrand' ); ?></p>
                            <?php $phone = get_theme_mod( 'contact_phone', '0161 123 4567' ); ?>
                            <?php $email = get_theme_mod( 'contact_email', 'info@winservecare.co.uk' ); ?>
                            <p style="margin-bottom:0.5rem;"><strong><?php esc_html_e( 'Phone:', 'mybrand' ); ?></strong> <a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $phone ) ); ?>" style="color:var(--color-accent);"><?php echo esc_html( $phone ); ?></a></p>
                            <p style="margin-bottom:1.5rem;"><strong><?php esc_html_e( 'Email:', 'mybrand' ); ?></strong> <a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:var(--color-accent);"><?php echo esc_html( $email ); ?></a></p>
                            <a class="btn btn-primary btn-full" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
                                <?php esc_html_e( 'Submit a Referral', 'mybrand' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>

    </div>
</section>

<?php get_footer(); ?>
