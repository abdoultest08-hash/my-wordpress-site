<?php
$features = [
    [
        'title' => __( 'CQC Registered &amp; Rated Good', 'mybrand' ),
        'desc'  => __( 'Fully compliant with all CQC fundamental standards. Our inspection reports are available on request.', 'mybrand' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>',
    ],
    [
        'title' => __( 'Rapid Placement', 'mybrand' ),
        'desc'  => __( 'We can mobilise care packages quickly, often within days of referral — reducing hospital stays and placement delays.', 'mybrand' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>',
    ],
    [
        'title' => __( 'Transparent Reporting', 'mybrand' ),
        'desc'  => __( 'Regular outcome reports, incident transparency, and open communication with commissioners and families.', 'mybrand' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
    ],
    [
        'title' => __( 'Specialist Expertise', 'mybrand' ),
        'desc'  => __( 'Our staff are trained in PBS, positive behaviour support, and evidence-based approaches for complex and co-occurring needs.', 'mybrand' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>',
    ],
    [
        'title' => __( 'Cost-Effective Packages', 'mybrand' ),
        'desc'  => __( 'Flexible funding models and competitive rates, helping councils deliver better value without compromising quality of care.', 'mybrand' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    ],
    [
        'title' => __( 'Reducing Hospital Admissions', 'mybrand' ),
        'desc'  => __( 'Proactive care planning and robust risk management that supports people in the community, reducing costly hospital stays.', 'mybrand' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>',
    ],
];
?>
<section class="section section-dark">
    <div class="container">
        <div class="section-header" style="display:grid;grid-template-columns:1fr auto;gap:2rem;align-items:center;">
            <div>
                <span class="section-label"><?php esc_html_e( 'For Commissioners', 'mybrand' ); ?></span>
                <h2 class="section-title"><?php esc_html_e( 'Why Local Authorities Choose Winserve', 'mybrand' ); ?></h2>
                <p class="section-desc" style="margin-inline:0;"><?php esc_html_e( 'We work in partnership with local authorities, NHS trusts, and ICBs to deliver care that meets the highest standards while achieving better outcomes for commissioners and the people they serve.', 'mybrand' ); ?></p>
            </div>
            <div style="flex-shrink:0;">
                <a class="btn btn-outline" href="<?php echo esc_url( home_url( '/for-commissioners/' ) ); ?>">
                    <?php esc_html_e( 'Commissioner Information', 'mybrand' ); ?>
                </a>
            </div>
        </div>

        <div class="commissioners-grid">
            <?php foreach ( $features as $feature ) : ?>
                <div class="comm-card">
                    <div class="comm-card-icon" aria-hidden="true">
                        <?php echo $feature['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                    <h3><?php echo wp_kses_post( $feature['title'] ); ?></h3>
                    <p><?php echo esc_html( $feature['desc'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
