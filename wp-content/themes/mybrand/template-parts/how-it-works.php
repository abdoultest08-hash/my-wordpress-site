<?php
$steps = [
    [
        'num'   => '1',
        'title' => 'Get in touch',
        'desc'  => 'Call us, send an email, or fill in the form on this page. Our team will get back to you within 24 hours to have a proper conversation about what you need.',
    ],
    [
        'num'   => '2',
        'title' => 'Free assessment',
        'desc'  => 'We visit the person at a time that works for them, learn about their daily life, preferences, and what they want from their care. No cost, no pressure.',
    ],
    [
        'num'   => '3',
        'title' => 'Care starts',
        'desc'  => 'We put together a care plan, match the right team members, and get started. For commissioner referrals we can often mobilise within a matter of days.',
    ],
];
?>
<section class="section section-blue">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Simple Process</span>
            <h2 class="section-title">How we get started</h2>
            <p class="section-desc">Getting care in place should not be complicated. Here is how we work with families, individuals, and commissioners.</p>
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
                Request Your Free Assessment
            </a>
        </div>
    </div>
</section>
