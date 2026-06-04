<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge">Contact Us</span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; Contact Us</p>
  </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
  <div class="container">

    <?php if (isset($_GET['sent']) && $_GET['sent'] === '1') : ?>
      <div class="alert-success">
        <strong>Thank you for getting in touch!</strong> We have received your message and will respond within 1&ndash;2 working days.
      </div>
    <?php endif; ?>

    <!-- Info Boxes -->
    <div class="contact-info-bar">

      <div class="contact-info-box">
        <div class="contact-info-icon">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
          <h4>Office Address</h4>
          <p>Unit 52, Pure Offices<br>Turnberry Park Road<br>Morley, Leeds, LS27 7LE</p>
        </div>
      </div>

      <div class="contact-info-box">
        <div class="contact-info-icon">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div>
          <h4>Phone &amp; Email</h4>
          <p><a href="tel:01133408777">0113 340 8777</a></p>
          <p><a href="mailto:info@winservecare.co.uk">info@winservecare.co.uk</a></p>
        </div>
      </div>

      <div class="contact-info-box">
        <div class="contact-info-icon">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <h4>Office Hours</h4>
          <p>Monday &ndash; Friday<br>09:00 &ndash; 17:00</p>
          <p style="font-size:12px;color:#999;margin-top:4px;">For urgent out-of-hours care support, please contact your care coordinator directly.</p>
        </div>
      </div>

    </div><!-- .contact-info-bar -->

    <!-- Contact Grid -->
    <div class="contact-grid">

      <!-- Contact Form -->
      <div class="contact-form-wrap">
        <h2>Send Us a Message</h2>
        <p class="form-sub">Fill in the form below and a member of our team will get back to you within 1&ndash;2 working days.</p>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <?php wp_nonce_field('winserve_contact', 'winserve_nonce'); ?>
          <input type="hidden" name="action" value="winserve_contact">

          <div class="form-group">
            <label for="full_name">Full Name <span style="color:var(--blue);">*</span></label>
            <input type="text" id="full_name" name="full_name" required placeholder="Your full name">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="contact_email">Email Address <span style="color:var(--blue);">*</span></label>
              <input type="email" id="contact_email" name="email" required placeholder="your@email.com">
            </div>
            <div class="form-group">
              <label for="contact_phone">Phone Number</label>
              <input type="tel" id="contact_phone" name="phone" placeholder="Optional">
            </div>
          </div>

          <div class="form-group">
            <label for="contact_subject">Subject</label>
            <select id="contact_subject" name="subject">
              <option value="General Enquiry">General Enquiry</option>
              <option value="Care Assessment Request">Care Assessment Request</option>
              <option value="Domiciliary Care">Domiciliary Care</option>
              <option value="Supported Living">Supported Living</option>
              <option value="Complex Care">Complex Care</option>
              <option value="Live-In Care">Live-In Care</option>
              <option value="Dementia Care">Dementia Care</option>
              <option value="Palliative Care">Palliative Care</option>
              <option value="Other Services">Other Services</option>
              <option value="Feedback / Complaint">Feedback / Complaint</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label for="contact_message">Message <span style="color:var(--blue);">*</span></label>
            <textarea id="contact_message" name="message" required placeholder="Tell us how we can help you..."></textarea>
          </div>

          <button type="submit" class="btn-submit">Send Message &rarr;</button>
        </form>
      </div>

      <!-- Map & Additional Info -->
      <div>
        <div class="map-wrap" style="margin-bottom:24px;">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2358.2!2d-1.5985!3d53.7477!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x48795e5c7a2b9f8d%3A0x1!2sPure+Offices%2C+Turnberry+Park+Rd%2C+Morley%2C+Leeds+LS27+7LE!5e0!3m2!1sen!2suk!4v1" width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Winserve Care Services Office Location"></iframe>
        </div>

        <div style="background:var(--section-bg);border:1px solid var(--border);border-radius:8px;padding:24px;">
          <h3 style="font-family:var(--font-heading);font-size:24px;color:var(--navy);margin-bottom:12px;">Additional Contacts</h3>
          <p style="font-family:var(--font-body);font-size:13px;color:#666;margin-bottom:8px;"><strong style="color:var(--navy);">HR Enquiries:</strong> <a href="mailto:hr@winservecare.co.uk" style="color:var(--blue);">hr@winservecare.co.uk</a></p>
          <p style="font-family:var(--font-body);font-size:13px;color:#666;margin-bottom:8px;"><strong style="color:var(--navy);">WhatsApp:</strong> <a href="https://api.whatsapp.com/send?phone=447514113988" target="_blank" rel="noopener" style="color:var(--blue);">Message us on WhatsApp</a></p>
          <p style="font-family:var(--font-body);font-size:13px;color:#666;margin-bottom:8px;"><strong style="color:var(--navy);">Staff Portal:</strong> <a href="https://portal.winservecare.co.uk/" target="_blank" rel="noopener" style="color:var(--blue);">portal.winservecare.co.uk</a></p>
          <p style="font-family:var(--font-body);font-size:13px;color:#666;"><strong style="color:var(--navy);">Reviews:</strong> <a href="https://www.homecare.co.uk/homecare/agency.cfm/id/65432238891" target="_blank" rel="noopener" style="color:var(--blue);">Homecare.co.uk Profile</a></p>
        </div>
      </div>

    </div><!-- .contact-grid -->

  </div><!-- .container -->
</section>

<?php get_footer(); ?>
