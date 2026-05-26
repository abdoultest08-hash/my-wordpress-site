<?php
$email   = get_theme_mod( 'contact_email',   'info@winservecare.co.uk' );
$phone   = get_theme_mod( 'contact_phone',   '0161 123 4567' );
$address = get_theme_mod( 'contact_address', 'Manchester, United Kingdom' );
?>
<section id="contact" class="section section-dark">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label"><?php esc_html_e( 'Get In Touch', 'mybrand' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Contact Winserve Care', 'mybrand' ); ?></h2>
            <p class="section-desc"><?php esc_html_e( 'Whether you are a family member, professional, or commissioner — we are here to help. Get in touch and our team will respond promptly.', 'mybrand' ); ?></p>
        </div>

        <div class="contact-inner">
            <div class="contact-form-col">
                <form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
                    <input type="hidden" name="action" value="mybrand_contact">
                    <?php wp_nonce_field( 'mybrand_contact_nonce' ); ?>
                    <div class="form-group">
                        <label for="cf-name"><?php esc_html_e( 'Your Name', 'mybrand' ); ?></label>
                        <input type="text" id="cf-name" name="cf_name" required placeholder="<?php esc_attr_e( 'Jane Smith', 'mybrand' ); ?>">
                    </div>
                    <div class="form-group">
                        <label for="cf-email"><?php esc_html_e( 'Email Address', 'mybrand' ); ?></label>
                        <input type="email" id="cf-email" name="cf_email" required placeholder="<?php esc_attr_e( 'jane@example.com', 'mybrand' ); ?>">
                    </div>
                    <div class="form-group">
                        <label for="cf-subject"><?php esc_html_e( 'Subject', 'mybrand' ); ?></label>
                        <input type="text" id="cf-subject" name="cf_subject" placeholder="<?php esc_attr_e( 'Care enquiry', 'mybrand' ); ?>">
                    </div>
                    <div class="form-group">
                        <label for="cf-message"><?php esc_html_e( 'Message', 'mybrand' ); ?></label>
                        <textarea id="cf-message" name="cf_message" rows="5" required placeholder="<?php esc_attr_e( 'How can we help you?', 'mybrand' ); ?>"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full"><?php esc_html_e( 'Send Message', 'mybrand' ); ?></button>
                </form>
            </div>

            <div class="contact-info-col">
                <div class="contact-info-item">
                    <span class="contact-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    </span>
                    <div>
                        <strong><?php esc_html_e( 'Phone', 'mybrand' ); ?></strong>
                        <a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <span class="contact-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    </span>
                    <div>
                        <strong><?php esc_html_e( 'Email', 'mybrand' ); ?></strong>
                        <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <span class="contact-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    </span>
                    <div>
                        <strong><?php esc_html_e( 'Address', 'mybrand' ); ?></strong>
                        <span><?php echo esc_html( $address ); ?></span>
                    </div>
                </div>
                <div class="contact-info-item">
                    <span class="contact-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <strong><?php esc_html_e( 'Office Hours', 'mybrand' ); ?></strong>
                        <span><?php esc_html_e( 'Mon–Fri 9am–5pm (24/7 on-call)', 'mybrand' ); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
