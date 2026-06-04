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
    <?php if (isset($_GET['sent']) && $_GET['sent'] === '2') : ?>
      <div class="alert-success">
        <strong>Application received!</strong> Thank you for your interest in joining the Winserve team. We will be in touch shortly.
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['sent']) && $_GET['sent'] === 'error') : ?>
      <div class="alert-error">
        <strong>Sorry, there was a problem sending your message.</strong> Please try again, or contact us directly at <a href="mailto:info@winservecare.co.uk">info@winservecare.co.uk</a> or call <a href="tel:01133408777">0113 340 8777</a>.
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
          <p>Monday &ndash; Friday<br>08:00 &ndash; 17:00</p>
          <p style="font-size:12px;color:#999;margin-top:4px;">For urgent out-of-hours care support, please contact your care coordinator directly.</p>
        </div>
      </div>
    </div>

    <!-- Pathway Selector -->
    <div class="contact-pathways">
      <p class="pathway-label">What can we help you with today?</p>
      <div class="pathway-tabs" id="pathway-tabs">
        <button class="pathway-tab active" data-tab="care">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
          Looking for Care
        </button>
        <button class="pathway-tab" data-tab="job">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          Looking for a Job
        </button>
        <button class="pathway-tab" data-tab="general">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
          General Enquiry
        </button>
      </div>
    </div>

    <!-- Contact Grid -->
    <div class="contact-grid">

      <!-- TAB: LOOKING FOR CARE -->
      <div class="contact-form-wrap pathway-panel active" id="tab-care">
        <h2>Care Enquiry</h2>
        <p class="form-sub">Tell us about your care needs and we will be in touch to arrange a free, no-obligation assessment.</p>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <?php wp_nonce_field('winserve_contact', 'winserve_nonce'); ?>
          <input type="hidden" name="action" value="winserve_contact">
          <input type="hidden" name="pathway" value="Care Enquiry">

          <div class="form-row">
            <div class="form-group">
              <label for="care_first_name">First Name <span style="color:var(--blue);">*</span></label>
              <input type="text" id="care_first_name" name="first_name" required placeholder="First name">
            </div>
            <div class="form-group">
              <label for="care_last_name">Surname <span style="color:var(--blue);">*</span></label>
              <input type="text" id="care_last_name" name="last_name" required placeholder="Surname">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="care_email">Email Address <span style="color:var(--blue);">*</span></label>
              <input type="email" id="care_email" name="email" required placeholder="your@email.com">
            </div>
            <div class="form-group">
              <label for="care_phone">Phone Number <span style="color:var(--blue);">*</span></label>
              <input type="tel" id="care_phone" name="phone" required placeholder="Your phone number">
            </div>
          </div>

          <div class="form-group">
            <label for="care_for">Who is the care for? <span style="color:var(--blue);">*</span></label>
            <select id="care_for" name="care_for" required>
              <option value="">Please select&hellip;</option>
              <option value="Myself">Myself</option>
              <option value="Spouse / Partner">Spouse / Partner</option>
              <option value="Parent / Family Member">Parent / Family Member</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label for="care_needs">What are their care needs? <span style="color:var(--blue);">*</span></label>
            <select id="care_needs" name="care_needs" required>
              <option value="">Please select&hellip;</option>
              <option value="Domiciliary / Home Care">Domiciliary / Home Care</option>
              <option value="Supported Living">Supported Living</option>
              <option value="Dementia / Alzheimer's Care">Dementia / Alzheimer's Care</option>
              <option value="Complex Care">Complex Care</option>
              <option value="Live-In Care">Live-In Care</option>
              <option value="Palliative / End of Life Care">Palliative / End of Life Care</option>
              <option value="Respite Care">Respite Care</option>
              <option value="Physical Disabilities Support">Physical Disabilities Support</option>
              <option value="Learning Disabilities Support">Learning Disabilities Support</option>
              <option value="Other / Not Sure">Other / Not Sure</option>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="care_postcode">Postcode <span style="color:var(--blue);">*</span></label>
              <input type="text" id="care_postcode" name="postcode" required placeholder="e.g. LS27 7LE">
            </div>
            <div class="form-group">
              <label for="care_hours">Approximate hours of care needed per week</label>
              <select id="care_hours" name="hours_per_week">
                <option value="">Not sure</option>
                <option value="Under 5 hours">Under 5 hours</option>
                <option value="5–10 hours">5&ndash;10 hours</option>
                <option value="10–20 hours">10&ndash;20 hours</option>
                <option value="20–35 hours">20&ndash;35 hours</option>
                <option value="35+ hours / Live-in">35+ hours / Live-in</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="care_funding">How will the care be funded? <span style="color:var(--blue);">*</span></label>
            <select id="care_funding" name="funding" required>
              <option value="">Please select&hellip;</option>
              <option value="Private (self-funded)">Private (self-funded)</option>
              <option value="Local Authority / Council">Local Authority / Council</option>
              <option value="NHS Continuing Healthcare">NHS Continuing Healthcare</option>
              <option value="Combination / Not sure">Combination / Not sure</option>
            </select>
          </div>

          <div class="form-group">
            <label for="care_message">Any additional information or questions?</label>
            <textarea id="care_message" name="message" placeholder="Tell us anything else that would help us prepare for your assessment&hellip;"></textarea>
          </div>

          <button type="submit" class="btn-submit">Submit Care Enquiry &rarr;</button>
        </form>
      </div>

      <!-- TAB: LOOKING FOR A JOB -->
      <div class="contact-form-wrap pathway-panel" id="tab-job" style="display:none;">
        <h2>Job Application</h2>
        <p class="form-sub">Interested in joining the Winserve family? Fill in the form below and our HR team will be in touch.</p>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <?php wp_nonce_field('winserve_application', 'winserve_nonce'); ?>
          <input type="hidden" name="action" value="winserve_application">

          <div class="form-row">
            <div class="form-group">
              <label for="job_first_name">First Name <span style="color:var(--blue);">*</span></label>
              <input type="text" id="job_first_name" name="first_name" required placeholder="First name">
            </div>
            <div class="form-group">
              <label for="job_last_name">Surname <span style="color:var(--blue);">*</span></label>
              <input type="text" id="job_last_name" name="last_name" required placeholder="Surname">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="job_email">Email Address <span style="color:var(--blue);">*</span></label>
              <input type="email" id="job_email" name="email" required placeholder="your@email.com">
            </div>
            <div class="form-group">
              <label for="job_phone">Phone Number <span style="color:var(--blue);">*</span></label>
              <input type="tel" id="job_phone" name="phone" required placeholder="Your phone number">
            </div>
          </div>

          <div class="form-group">
            <label for="job_location">Preferred working location <span style="color:var(--blue);">*</span></label>
            <select id="job_location" name="location" required>
              <option value="">Please select&hellip;</option>
              <option value="Leeds">Leeds</option>
              <option value="Cornwall">Cornwall</option>
              <option value="Both / Either">Both / Either</option>
            </select>
          </div>

          <div class="form-group">
            <label for="job_experience">Care experience</label>
            <select id="job_experience" name="experience">
              <option value="No experience — willing to train">No experience &mdash; willing to train</option>
              <option value="Under 1 year">Under 1 year</option>
              <option value="1–2 years">1&ndash;2 years</option>
              <option value="3–5 years">3&ndash;5 years</option>
              <option value="5+ years">5+ years</option>
            </select>
          </div>

          <div class="form-group">
            <label for="job_why">Why do you want to work at Winserve? <span style="color:var(--blue);">*</span></label>
            <textarea id="job_why" name="why_applying" required placeholder="Tell us a bit about yourself and why you'd like to join the Winserve team&hellip;"></textarea>
          </div>

          <button type="submit" class="btn-submit">Submit Application &rarr;</button>
        </form>
      </div>

      <!-- TAB: GENERAL ENQUIRY -->
      <div class="contact-form-wrap pathway-panel" id="tab-general" style="display:none;">
        <h2>General Enquiry</h2>
        <p class="form-sub">Have a question? Send us a message and a member of our team will get back to you within 1&ndash;2 working days.</p>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <?php wp_nonce_field('winserve_contact', 'winserve_nonce'); ?>
          <input type="hidden" name="action" value="winserve_contact">
          <input type="hidden" name="pathway" value="General Enquiry">

          <div class="form-group">
            <label for="gen_name">Full Name <span style="color:var(--blue);">*</span></label>
            <input type="text" id="gen_name" name="full_name" required placeholder="Your full name">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="gen_email">Email Address <span style="color:var(--blue);">*</span></label>
              <input type="email" id="gen_email" name="email" required placeholder="your@email.com">
            </div>
            <div class="form-group">
              <label for="gen_phone">Phone Number</label>
              <input type="tel" id="gen_phone" name="phone" placeholder="Optional">
            </div>
          </div>

          <div class="form-group">
            <label for="gen_message">Message <span style="color:var(--blue);">*</span></label>
            <textarea id="gen_message" name="message" required placeholder="How can we help you?"></textarea>
          </div>

          <button type="submit" class="btn-submit">Send Message &rarr;</button>
        </form>
      </div>

      <!-- Map & Additional Info (always visible) -->
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

<script>
(function(){
  var tabs = document.querySelectorAll('.pathway-tab');
  var panels = document.querySelectorAll('.pathway-panel');
  tabs.forEach(function(tab){
    tab.addEventListener('click', function(){
      var target = this.getAttribute('data-tab');
      tabs.forEach(function(t){ t.classList.remove('active'); });
      panels.forEach(function(p){ p.style.display = 'none'; p.classList.remove('active'); });
      this.classList.add('active');
      var panel = document.getElementById('tab-' + target);
      if (panel) { panel.style.display = 'block'; panel.classList.add('active'); }
    });
  });
})();
</script>

<?php get_footer(); ?>
