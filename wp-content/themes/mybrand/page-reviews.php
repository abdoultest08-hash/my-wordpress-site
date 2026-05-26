<?php
/**
 * Template Name: Reviews
 * Template Post Type: page
 */

get_header();
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'mybrand' ); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'mybrand' ); ?></a>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span><?php esc_html_e( 'Reviews', 'mybrand' ); ?></span>
        </nav>
        <h1 class="page-hero__title"><?php esc_html_e( 'What Our Families Say', 'mybrand' ); ?></h1>
        <p class="page-hero__desc"><?php esc_html_e( 'Genuine feedback from the families, service users, and professionals who trust Winserve Care Services every day.', 'mybrand' ); ?></p>
    </div>
</section>

<!-- Rating summary -->
<div style="background:var(--color-off-white);padding:3rem 0;border-bottom:1px solid var(--color-light-gray);">
    <div class="container" style="display:flex;align-items:center;gap:3rem;justify-content:center;flex-wrap:wrap;">
        <div style="text-align:center;">
            <div style="font-family:var(--font-heading);font-size:4rem;font-weight:800;color:var(--color-primary);line-height:1;">9.8</div>
            <div style="font-size:0.85rem;color:var(--color-mid-gray);"><?php esc_html_e( 'Average Score', 'mybrand' ); ?></div>
            <div style="font-size:0.78rem;color:var(--color-mid-gray);"><?php esc_html_e( 'homecare.co.uk', 'mybrand' ); ?></div>
        </div>
        <div style="display:flex;gap:6px;">
            <?php for ( $i = 0; $i < 5; $i++ ) : ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:36px;height:36px;color:#F59E0B;"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd"/></svg>
            <?php endfor; ?>
        </div>
        <div style="text-align:center;">
            <div style="font-family:var(--font-heading);font-size:2rem;font-weight:800;color:var(--color-primary);line-height:1;">50+</div>
            <div style="font-size:0.85rem;color:var(--color-mid-gray);"><?php esc_html_e( 'Verified Reviews', 'mybrand' ); ?></div>
        </div>
    </div>
</div>

<?php get_template_part( 'template-parts/reviews' ); ?>

<section class="section section-blue">
    <div class="container text-center">
        <h2 style="font-family:var(--font-heading);font-size:1.75rem;font-weight:700;color:var(--color-primary);margin-bottom:1rem;">
            <?php esc_html_e( 'Share Your Experience', 'mybrand' ); ?>
        </h2>
        <p style="color:var(--color-mid-gray);margin-bottom:2rem;max-width:500px;margin-inline:auto;">
            <?php esc_html_e( 'If you or a loved one has been supported by Winserve, we would love to hear your feedback.', 'mybrand' ); ?>
        </p>
        <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
            <?php esc_html_e( 'Leave a Review', 'mybrand' ); ?>
        </a>
    </div>
</section>

<?php get_footer(); ?>
