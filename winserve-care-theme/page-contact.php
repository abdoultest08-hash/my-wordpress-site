<?php get_header(); ?>

<section class="page-hero">
  <div class="container">
    <h1>Contact Us</h1>
    <p class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; <span>Contact Us</span></p>
  </div>
</section>

<section class="contact-section">
  <div class="container">
    <div class="contact-grid">
      <div class="contact-form">
        <h2>Send Us a Message</h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <?php wp_nonce_field('winserve_contact_form', 'winserve_nonce'); ?>
          <input type="hidden" name="action" value="winserve_contact">
          <div class="form-group">
            <label for="full-name">Full Name *</label>
            <input type="text" id="full-name" name="full_name" required placeholder="Your full name">
          </div>
          <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" id="email" name="email" required placeholder="your@email.com">
          </div>
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="07xxx xxx xxx">
          </div>
          <div class="form-group">
            <label for="subject">Subject</label>
            <select id="subject" name="subject">
              <option value="">Please select...</option>
              <option value="assessment">Book a Free Assessment</option>
              <option value="domiciliary">Domiciliary Care Enquiry</option>
              <option value="supported-living">Supported Living Enquiry</option>
              <option value="complex-care">Complex Care Enquiry</option>
              <option value="careers">Careers</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="message">Message *</label>
            <textarea id="message" name="message" required placeholder="How can we help you?"></textarea>
          </div>
          <button type="submit" class="btn-submit">Send Message &rarr;</button>
        </form>
      </div>
      <div class="contact-info">
        <h2>Find Us</h2>
        <div class="map-embed">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2357.3!2d-1.59!3d53.74!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sPure+Offices+Morley!5e0!3m2!1sen!2suk!4v1"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Winserve Care Services Ltd office location"
          ></iframe>
        </div>
        <div class="contact-detail">
          <div class="contact-detail-icon">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div class="contact-detail-text">
            <div class="label">Address</div>
            <p>Unit 52, Pure Offices, Turnberry Park Road,<br>Morley, Leeds, LS27 7LE</p>
          </div>
        </div>
        <div class="contact-detail">
          <div class="contact-detail-icon">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          </div>
          <div class="contact-detail-text">
            <div class="label">Phone</div>
            <a href="tel:01133408777">0113 340 8777</a>
          </div>
        </div>
        <div class="contact-detail">
          <div class="contact-detail-icon">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <div class="contact-detail-text">
            <div class="label">Email</div>
            <a href="mailto:info@winservecare.co.uk">info@winservecare.co.uk</a>
          </div>
        </div>
        <div class="contact-detail">
          <div class="contact-detail-icon">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="contact-detail-text">
            <div class="label">Office Hours</div>
            <p>Monday &ndash; Friday: 09:00 &ndash; 17:00</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
