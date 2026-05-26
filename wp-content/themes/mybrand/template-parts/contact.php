<?php
$email   = get_theme_mod( 'contact_email',   'hello@yourdomain.com' );
$phone   = get_theme_mod( 'contact_phone',   '+1 (555) 000-0000' );
$address = get_theme_mod( 'contact_address', '123 Your Street, City, Country' );
?>
<section id="contact" class="section section-contact section-dark">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label"><?php esc_html_e( 'Get In Touch', 'mybrand' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Contact Us', 'mybrand' ); ?></h2>
            <p class="section-desc"><?php esc_html_e( 'Ready to start a project? We\'d love to hear from you.', 'mybrand' ); ?></p>
        </div>

        <div class="contact-inner">

            <!-- Contact form — install Contact Form 7 and paste its shortcode here -->
            <div class="contact-form-col">
                <?php
                // If Contact Form 7 is active, replace the shortcode ID.
                if ( shortcode_exists( 'contact-form-7' ) ) {
                    echo do_shortcode( '[contact-form-7 id="YOUR_FORM_ID" title="Contact form"]' );
                } else {
                    // Fallback: basic HTML5 form (no server handling — install CF7 or WPForms)
                    ?>
                    <form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
                        <input type="hidden" name="action" value="mybrand_contact">
                        <?php wp_nonce_field( 'mybrand_contact_nonce' ); ?>

                        <div class="form-group">
                            <label for="cf-name"><?php esc_html_e( 'Your Name', 'mybrand' ); ?></label>
                            <input type="text" id="cf-name" name="cf_name" required placeholder="<?php esc_attr_e( 'Jane Doe', 'mybrand' ); ?>">
                        </div>
                        <div class="form-group">
                            <label for="cf-email"><?php esc_html_e( 'Email Address', 'mybrand' ); ?></label>
                            <input type="email" id="cf-email" name="cf_email" required placeholder="<?php esc_attr_e( 'jane@example.com', 'mybrand' ); ?>">
                        </div>
                        <div class="form-group">
                            <label for="cf-subject"><?php esc_html_e( 'Subject', 'mybrand' ); ?></label>
                            <input type="text" id="cf-subject" name="cf_subject" placeholder="<?php esc_attr_e( 'Project enquiry', 'mybrand' ); ?>">
                        </div>
                        <div class="form-group">
                            <label for="cf-message"><?php esc_html_e( 'Message', 'mybrand' ); ?></label>
                            <textarea id="cf-message" name="cf_message" rows="5" required placeholder="<?php esc_attr_e( 'Tell us about your project…', 'mybrand' ); ?>"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full"><?php esc_html_e( 'Send Message', 'mybrand' ); ?></button>
                    </form>
                <?php } ?>
            </div>

            <!-- Contact info -->
            <div class="contact-info-col">
                <div class="contact-info-item">
                    <span class="contact-icon" aria-hidden="true">✉️</span>
                    <div>
                        <strong><?php esc_html_e( 'Email', 'mybrand' ); ?></strong>
                        <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <span class="contact-icon" aria-hidden="true">📞</span>
                    <div>
                        <strong><?php esc_html_e( 'Phone', 'mybrand' ); ?></strong>
                        <a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <span class="contact-icon" aria-hidden="true">📍</span>
                    <div>
                        <strong><?php esc_html_e( 'Address', 'mybrand' ); ?></strong>
                        <span><?php echo esc_html( $address ); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
