<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge">Services</span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; Services</p>
  </div>
</section>

<!-- Services Intro -->
<section class="services-intro">
  <div class="container">
    <span class="eyebrow">+ What We Offer</span>
    <h2 style="font-family:var(--font-heading);font-size:40px;color:var(--navy);line-height:1.2;">Specialist Care Services Across Yorkshire</h2>
    <p>We provide care that makes a real difference to the lives of individuals and families across Yorkshire. Winserve is CQC registered for Personal Care and for Treatment of Disease, Disorder or Injury, so we can support people whose needs go beyond everyday personal care, in their own home.</p>
  </div>
</section>

<!-- 6 Core Services Grid -->
<div class="container">
  <div class="services-full-grid">

    <div class="service-full-card service-full-card--lead">
      <div class="service-full-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/activity-outdoors.jpg');"></div>
      <span class="service-flag">Our Speciality</span>
      <div class="service-full-content">
        <h3>Supported Living</h3>
        <p>Our main area of expertise. We support adults to live in their own homes with the right level of care around them. We deliver packages at staffing ratios up to 3:1, and local authorities approach us directly when they need a provider they can rely on.</p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="service-link">Enquire Now &rarr;</a>
      </div>
    </div>

    <div class="service-full-card">
      <div class="service-full-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-domiciliary-car.jpg');"></div>
      <div class="service-full-content">
        <h3>Domiciliary Care</h3>
        <p>Regular visits to support people in their own homes with personal care, medication, meals, and day-to-day tasks. Our carers are consistent, punctual, and trained to a high standard. Families across Yorkshire trust us to show up and do the job properly.</p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="service-link">Enquire Now &rarr;</a>
      </div>
    </div>

    <div class="service-full-card">
      <div class="service-full-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/home-social.jpg');"></div>
      <div class="service-full-content">
        <h3>Private Care Packages</h3>
        <p>Not everyone goes through a local authority. If you are arranging care privately, we work directly with you and your family. We take the time to understand what you need and build a package around it, without the bureaucracy.</p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="service-link">Enquire Now &rarr;</a>
      </div>
    </div>

    <div class="service-full-card">
      <div class="service-full-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/activity-alpaca-group.jpg');"></div>
      <div class="service-full-content">
        <h3>Learning Disabilities</h3>
        <p>Specialist support for people with mild to profound learning disabilities. We focus on building confidence, independence, and quality of life at whatever pace works for the individual, including supported activities out in the community.</p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="service-link">Enquire Now &rarr;</a>
      </div>
    </div>

    <div class="service-full-card">
      <div class="service-full-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-end-of-life.jpg');"></div>
      <div class="service-full-content">
        <h3>End of Life Care</h3>
        <p>We support individuals and families through one of the most difficult times they will face. Our carers approach end of life care with sensitivity, patience, and deep respect, making sure the person is comfortable, dignified, and never alone.</p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="service-link">Enquire Now &rarr;</a>
      </div>
    </div>

    <div class="service-full-card">
      <div class="service-full-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-mental-health.jpg');"></div>
      <div class="service-full-content">
        <h3>Mental Health Support</h3>
        <p>Recovery-focused support for people with mental health needs. We work alongside community mental health teams and families, keeping communication open and care consistent so people feel safe and supported day to day.</p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="service-link">Enquire Now &rarr;</a>
      </div>
    </div>

  </div>
</div>
</div>


<!-- Reviews Carousel -->
<section class="reviews-section">
  <div class="container">
    <div class="reviews-homecare-header">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-homecare.png" alt="Homecare.co.uk, the UK's No.1 Home Care Website" class="homecare-logo" loading="lazy" decoding="async" width="435" height="116">
      <p class="homecare-caption">Independently verified reviews from the UK&rsquo;s No.1 home care website</p>
    </div>
    <div class="reviews-rating-bar">
      <span class="reviews-score">9.5</span>
      <div>
        <div class="reviews-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="reviews-source">from 75+ reviews on Homecare.co.uk</div>
      </div>
    </div>
  </div>

  <div class="reviews-track-wrap">
    <div class="reviews-track">
      <?php
      $svc_reviews = [
          ['name' => 'Sophie T', 'role' => 'Service User', 'date' => 'January 2026',
           'text' => 'Service has been amazing. All staff members I have interacted with have been absolutely amazing and very professional. I cannot thank the team enough for the professional and empathetic care provided during the hardest time of my life.'],
          ['name' => 'Nicole S', 'role' => 'Daughter of Service User', 'date' => 'October 2025',
           'text' => 'Winserve provided excellent care for my father after he left hospital following a stroke. The carers were approachable, respectful of his independence and privacy, and always professional even when things were difficult.'],
          ['name' => 'Beverley C', 'role' => 'Wife of Service User', 'date' => 'August 2025',
           'text' => 'The care given to my husband I can only describe as warm and truly friendly. My husband has dementia and I was so pleased he had company and was in great hands while I was at work. The team genuinely cared.'],
          ['name' => 'Eleni-Rose B', 'role' => 'Granddaughter of Service User', 'date' => 'June 2025',
           'text' => 'Nice and clear instructions and always receptive to our questions and needs. My grandmother says the care is excellent. I have been extremely impressed and grateful for everything this team does.'],
          ['name' => 'Hannah P', 'role' => 'Carer of Service User', 'date' => 'September 2025',
           'text' => 'Winserve have provided my mum with superb care. I cannot thank them enough for the excellent care and compassion they have shown. The whole team deserve enormous credit.'],
          ['name' => 'Nicolas M', 'role' => 'Service User', 'date' => 'April 2025',
           'text' => 'I am incredibly delighted by the service I receive. All they aim to do is please and I feel truly safe. The consistency and warmth of the care I receive deserves recognition. Thank you to the whole team.'],
          ['name' => 'Molly A', 'role' => 'Daughter of Service User', 'date' => 'March 2025',
           'text' => 'Our carers are absolutely brilliant. Kind, thorough, punctual and incredibly gentle. The manager and the whole team are wonderful. We feel very well looked after and I cannot recommend them highly enough.'],
          ['name' => 'Brenda D', 'role' => 'Wife of Service User', 'date' => 'November 2024',
           'text' => 'What a caring, consistent and effective team. They started promptly and every carer who came was wonderful. Thank you so much to all the staff at Winserve for the difference you have made to our lives.'],
      ];
      foreach (array_merge($svc_reviews, $svc_reviews) as $r) {
          echo '<div class="review-card">';
          echo '<div class="review-stars">' . str_repeat('&#9733;', 5) . '</div>';
          echo '<p class="review-text">' . esc_html($r['text']) . '</p>';
          echo '<div class="review-author">' . esc_html($r['name']) . '</div>';
          echo '<div class="review-role">' . esc_html($r['role']) . '</div>';
          echo '<div class="review-date">' . esc_html($r['date']) . '</div>';
          echo '</div>';
      }
      ?>
    </div>
  </div>

  <div class="container">
    <div class="reviews-cta">
      <a href="https://www.homecare.co.uk/homecare/agency.cfm/id/65432238891" target="_blank" rel="noopener" class="btn-outline">Read all reviews on Homecare.co.uk &rarr;</a>
    </div>
  </div>
</section>

<!-- Not Sure / Enquiry Split -->
<section class="services-enquiry-split">
  <div class="container services-enquiry-inner">

    <div class="services-enquiry-left">
      <span class="eyebrow" style="color:var(--light-blue);">+ Free Assessment</span>
      <h2>Not Sure Which Service You Need?</h2>
      <p>Our care coordinators are here to help. Simply tell us a little about your situation and we'll guide you to the right support, no obligation, no pressure.</p>
      <div class="contact-detail">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        <a href="tel:01133408777">0113 340 8777</a>
      </div>
      <div class="contact-detail">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        <a href="mailto:enquiries@winservecare.co.uk">enquiries@winservecare.co.uk</a>
      </div>
      <div class="contact-detail">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Mon&ndash;Fri 08:00&ndash;17:00</span>
      </div>
    </div>

    <div class="services-enquiry-right">
      <h3>Quick Service Enquiry</h3>
      <p class="sub">We&rsquo;ll get back to you within 1 working day.</p>

      <?php if (isset($_GET['sent']) && $_GET['sent'] === '1') : ?>
        <div class="alert-success">Thank you, we&rsquo;ll be in touch shortly.</div>
      <?php endif; ?>

      <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('winserve_contact', 'winserve_nonce'); ?>
        <input type="hidden" name="action" value="winserve_contact">
        <input type="hidden" name="pathway" value="Services Enquiry">

        <div class="form-row">
          <div class="form-group">
            <label for="se_name">Your Name <span style="color:var(--blue);">*</span></label>
            <input type="text" id="se_name" name="full_name" required placeholder="Full name">
          </div>
          <div class="form-group">
            <label for="se_phone">Phone Number <span style="color:var(--blue);">*</span></label>
            <input type="tel" id="se_phone" name="phone" required placeholder="07xxx xxxxxx">
          </div>
        </div>

        <div class="form-group">
          <label for="se_email">Email Address <span style="color:var(--blue);">*</span></label>
          <input type="email" id="se_email" name="email" required placeholder="your@email.com">
        </div>

        <div class="form-group">
          <label for="se_service">Which service are you enquiring about?</label>
          <select id="se_service" name="subject">
            <option value="Not sure, need guidance">Not sure, need guidance</option>
            <option value="Supported Living">Supported Living</option>
            <option value="Domiciliary / Home Care">Domiciliary / Home Care</option>
            <option value="Private Care Package">Private Care Package</option>
            <option value="Learning Disabilities">Learning Disabilities</option>
            <option value="End of Life Care">End of Life Care</option>
            <option value="Mental Health Support">Mental Health Support</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label for="se_message">Anything else you&rsquo;d like us to know?</label>
          <textarea id="se_message" name="message" rows="3" placeholder="Tell us briefly about the situation&hellip;" style="min-height:80px;"></textarea>
        </div>

        <button type="submit" class="btn-submit">Send Enquiry &rarr;</button>
      </form>
    </div>

  </div>
</section>

<?php get_footer(); ?>
