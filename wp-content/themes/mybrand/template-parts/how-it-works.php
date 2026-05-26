<?php
$steps = [
    [
        'num'   => '1',
        'title' => __( 'Get in Touch', 'mybrand' ),
        'desc'  => __( 'Contact us by phone, email, or complete our online referral form. Our friendly team will respond within 24 hours to discuss your needs.', 'mybrand' ),
    ],
    [
        'num'   => '2',
        'title' => __( 'Free Assessment', 'mybrand' ),
        'desc'  => __( 'We conduct a comprehensive needs assessment — at no cost — to understand the individual\'s requirements, preferences, and goals.', 'mybrand' ),
    ],
    [
        'num'   => '3',
        'title' => __( 'Tailored Care Begins', 'mybrand' ),
        'desc'  => __( 'We create a personalised support plan and match the right team members. Care can start quickly — often within days of assessment.', 'mybrand' ),
    ],
];
?>
<section class="section section-blue">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label"><?php esc_html_e( 'Simple Process', 'mybrand' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'How We Get Started', 'mybrand' ); ?></h2>
            <p class="section-desc"><?php esc_html_e( 'Getting the right care in place doesn\'t have to be complicated. Here\'s how we work with families, individuals, and commissioners.', 'mybrand' ); ?></p>
        </div>

        <div class="how-it-works-grid">
            <?php foreach ( $steps as $step ) : ?>
                <div class="how-step">
                    <div class="how-step-num" aria-hidden="true"><?php echo esc_html( $step['num'] ); ?></div>
                    <h3><?php echo esc_html( $step['title'] ); ?></h3>
                    <p><?php echo esc_html( $step['desc'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section-cta text-center">
            <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/free-assessment/' ) ); ?>">
                <?php esc_html_e( 'Request Your Free Assessment', 'mybrand' ); ?>
            </a>
        </div>
    </div>
</section>
