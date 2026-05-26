<?php
// Services are managed via the Customizer (up to 6 items).
// Replace icon names with your chosen icon library (Font Awesome, Phosphor, etc.)
$services = [
    [
        'icon'  => '🎨',
        'title' => __( 'Brand Identity', 'mybrand' ),
        'desc'  => __( 'Logo design, color systems, typography, and brand guidelines that set you apart.', 'mybrand' ),
    ],
    [
        'icon'  => '💻',
        'title' => __( 'Web Design', 'mybrand' ),
        'desc'  => __( 'Beautiful, responsive websites engineered for performance and conversions.', 'mybrand' ),
    ],
    [
        'icon'  => '📈',
        'title' => __( 'Digital Marketing', 'mybrand' ),
        'desc'  => __( 'SEO, social media, and paid campaigns that drive real, measurable growth.', 'mybrand' ),
    ],
    [
        'icon'  => '📱',
        'title' => __( 'App Development', 'mybrand' ),
        'desc'  => __( 'Native and cross-platform mobile apps with polished UX from day one.', 'mybrand' ),
    ],
    [
        'icon'  => '🔍',
        'title' => __( 'SEO & Analytics', 'mybrand' ),
        'desc'  => __( 'Data-driven insights and optimization to keep you ahead of the competition.', 'mybrand' ),
    ],
    [
        'icon'  => '🛠️',
        'title' => __( 'Ongoing Support', 'mybrand' ),
        'desc'  => __( 'Maintenance plans, updates, and dedicated support so you never fly solo.', 'mybrand' ),
    ],
];
?>
<section id="services" class="section section-services">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label"><?php esc_html_e( 'What We Do', 'mybrand' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Our Services', 'mybrand' ); ?></h2>
            <p class="section-desc"><?php esc_html_e( 'We offer end-to-end solutions to grow your brand online.', 'mybrand' ); ?></p>
        </div>

        <div class="services-grid">
            <?php foreach ( $services as $service ) : ?>
                <div class="service-card">
                    <div class="service-icon" aria-hidden="true"><?php echo esc_html( $service['icon'] ); ?></div>
                    <h3 class="service-title"><?php echo esc_html( $service['title'] ); ?></h3>
                    <p class="service-desc"><?php echo esc_html( $service['desc'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
