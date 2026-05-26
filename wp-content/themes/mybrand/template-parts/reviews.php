<?php
$reviews = [
    [
        'text'   => "Winserve has been absolutely brilliant with my son. The support workers are consistent, kind, and they genuinely understand his needs. We've seen such a positive change in him since they started — we couldn't be happier.",
        'name'   => 'Sarah M.',
        'role'   => __( 'Family Member', 'mybrand' ),
        'source' => 'homecare.co.uk',
    ],
    [
        'text'   => "As a social worker I have placed several clients with Winserve Care. Their response times, quality of care planning, and communication are exemplary. They are my first call when looking for supported living placements.",
        'name'   => 'David R.',
        'role'   => __( 'Social Worker, Local Authority', 'mybrand' ),
        'source' => 'Commissioner feedback',
    ],
    [
        'text'   => "The staff at Winserve treat my daughter with so much respect. They always involve her in decisions about her own care, which makes a real difference to her confidence and independence.",
        'name'   => 'Priya K.',
        'role'   => __( 'Parent of Service User', 'mybrand' ),
        'source' => 'homecare.co.uk',
    ],
];
?>
<section class="section section-light">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label"><?php esc_html_e( 'Testimonials', 'mybrand' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'What Families &amp; Partners Say', 'mybrand' ); ?></h2>
            <p class="section-desc"><?php esc_html_e( 'Real feedback from the families, commissioners, and professionals who trust Winserve Care Services.', 'mybrand' ); ?></p>
        </div>

        <div class="reviews-grid">
            <?php foreach ( $reviews as $review ) : ?>
                <div class="review-card">
                    <div class="review-stars" aria-label="<?php esc_attr_e( '5 stars', 'mybrand' ); ?>">
                        <?php for ( $i = 0; $i < 5; $i++ ) : ?>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd"/></svg>
                        <?php endfor; ?>
                    </div>
                    <blockquote class="review-text">&ldquo;<?php echo esc_html( $review['text'] ); ?>&rdquo;</blockquote>
                    <div class="review-footer">
                        <div>
                            <div class="reviewer-name"><?php echo esc_html( $review['name'] ); ?></div>
                            <div class="reviewer-role"><?php echo esc_html( $review['role'] ); ?></div>
                        </div>
                        <div>
                            <span class="verified-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <?php esc_html_e( 'Verified', 'mybrand' ); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section-cta text-center">
            <a class="btn btn-outline-blue" href="<?php echo esc_url( home_url( '/reviews/' ) ); ?>">
                <?php esc_html_e( 'Read All Reviews', 'mybrand' ); ?>
            </a>
        </div>
    </div>
</section>
