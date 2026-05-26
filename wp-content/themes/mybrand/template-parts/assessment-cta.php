<section class="assessment-cta" id="free-assessment">
    <div class="container">
        <div class="assessment-inner">

            <div class="assessment-content">
                <span class="section-label" style="color:rgba(255,255,255,.65);"><?php esc_html_e( 'No Obligation', 'mybrand' ); ?></span>
                <h2><?php esc_html_e( 'Request a Free Care Assessment', 'mybrand' ); ?></h2>
                <p><?php esc_html_e( 'Not sure what support is needed? Our care specialists will visit at a time that suits you, assess the individual\'s needs, and provide a fully personalised recommendation — completely free of charge.', 'mybrand' ); ?></p>
                <ul class="assessment-bullets">
                    <?php
                    $bullets = [
                        __( 'No cost, no commitment', 'mybrand' ),
                        __( 'Carried out by an experienced care specialist', 'mybrand' ),
                        __( 'Personalised care plan provided', 'mybrand' ),
                        __( 'Results typically within 48 hours', 'mybrand' ),
                    ];
                    foreach ( $bullets as $bullet ) :
                    ?>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <?php echo esc_html( $bullet ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="assessment-form-wrap">
                <h3><?php esc_html_e( 'Book Your Free Assessment', 'mybrand' ); ?></h3>

                <?php
                $submitted = isset( $_GET['assessment'] ) && sanitize_text_field( wp_unslash( $_GET['assessment'] ) ) === 'sent';
                if ( $submitted ) :
                ?>
                    <div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:1rem;border-radius:8px;font-size:0.95rem;">
                        <strong><?php esc_html_e( 'Thank you!', 'mybrand' ); ?></strong> <?php esc_html_e( "We've received your request and will be in touch within 24 hours.", 'mybrand' ); ?>
                    </div>
                <?php else : ?>
                    <form class="assessment-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
                        <?php wp_nonce_field( 'winserve_assessment_action', 'winserve_assessment_nonce' ); ?>
                        <input type="hidden" name="action" value="winserve_assessment">

                        <div class="form-group">
                            <label for="assess_name"><?php esc_html_e( 'Your Name', 'mybrand' ); ?> <span style="color:var(--color-red)">*</span></label>
                            <input type="text" id="assess_name" name="assess_name" required placeholder="<?php esc_attr_e( 'Jane Smith', 'mybrand' ); ?>">
                        </div>
                        <div class="form-group">
                            <label for="assess_phone"><?php esc_html_e( 'Phone Number', 'mybrand' ); ?> <span style="color:var(--color-red)">*</span></label>
                            <input type="tel" id="assess_phone" name="assess_phone" required placeholder="<?php esc_attr_e( '07700 900000', 'mybrand' ); ?>">
                        </div>
                        <div class="form-group">
                            <label for="assess_email"><?php esc_html_e( 'Email Address', 'mybrand' ); ?></label>
                            <input type="email" id="assess_email" name="assess_email" placeholder="<?php esc_attr_e( 'jane@example.com', 'mybrand' ); ?>">
                        </div>
                        <div class="form-group">
                            <label for="assess_service"><?php esc_html_e( 'Type of Care Needed', 'mybrand' ); ?></label>
                            <select id="assess_service" name="assess_service">
                                <option value=""><?php esc_html_e( 'Select a service…', 'mybrand' ); ?></option>
                                <option value="supported-living"><?php esc_html_e( 'Supported Living', 'mybrand' ); ?></option>
                                <option value="learning-disabilities"><?php esc_html_e( 'Learning Disabilities', 'mybrand' ); ?></option>
                                <option value="autism"><?php esc_html_e( 'Autism Spectrum', 'mybrand' ); ?></option>
                                <option value="mental-health"><?php esc_html_e( 'Mental Health', 'mybrand' ); ?></option>
                                <option value="physical-disabilities"><?php esc_html_e( 'Physical Disabilities', 'mybrand' ); ?></option>
                                <option value="complex-care"><?php esc_html_e( 'Complex / Challenging Needs', 'mybrand' ); ?></option>
                                <option value="other"><?php esc_html_e( 'Not sure / Other', 'mybrand' ); ?></option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full"><?php esc_html_e( 'Request Free Assessment', 'mybrand' ); ?></button>
                        <p class="form-privacy"><?php esc_html_e( 'We will never share your details. See our Privacy Policy.', 'mybrand' ); ?></p>
                    </form>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>
