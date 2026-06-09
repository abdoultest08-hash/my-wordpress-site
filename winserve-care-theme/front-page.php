<?php get_header(); ?>

<!-- HERO -->
<section class="hero-section">
  <div class="hero-overlay"></div>
  <div class="container">
    <div class="hero-card">
      <span class="eyebrow">CQC Registered &bull; Leeds &amp; Cornwall</span>
      <h1>Care is at the heart of what we do.</h1>
      <p>Winserve Care Services provides compassionate, person-centred domiciliary and supported living care across Leeds and Cornwall.</p>
      <div class="hero-btns">
        <a href="<?php echo esc_url(home_url('/services')); ?>" class="btn-teal">Our Care Services</a>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-outline">Free Assessment</a>
      </div>
    </div>
  </div>
  <div class="hero-scroll">&#8595;</div>
</section>

<!-- INFO STRIP -->
<div class="info-strip">
  <div class="info-strip-grid container" style="max-width:1200px;margin:0 auto;">
    <div class="info-strip-item">
      <div class="info-strip-icon">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
      </div>
      <div>
        <span class="info-strip-label">24/7 Care Available</span>
        <a href="tel:01133408777" class="info-strip-value">0113 340 8777</a>
      </div>
    </div>
    <div class="info-strip-item">
      <div class="info-strip-icon">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      </div>
      <div>
        <span class="info-strip-label">Send an Enquiry</span>
        <a href="mailto:enquiries@winservecare.co.uk" class="info-strip-value">enquiries@winservecare.co.uk</a>
      </div>
    </div>
    <div class="info-strip-item">
      <div class="info-strip-icon">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </div>
      <div>
        <span class="info-strip-label">Serving Two Regions</span>
        <span class="info-strip-value">Leeds &amp; Cornwall</span>
      </div>
    </div>
  </div>
</div>

<!-- INTRO SECTION -->
<section class="intro-section">
  <div class="container">
    <div class="intro-grid">
      <div>
        <span class="eyebrow">+ Who We Are</span>
        <span class="teal-line"></span>
        <h2 class="intro-heading">Would you prefer your loved one to stay at home rather than go into a care facility?</h2>
        <p class="intro-text">At Winserve Care Services, we believe everyone deserves to live with dignity and independence — in the comfort of their own home. We provide high-quality domiciliary and supported living care, tailored to each individual, delivered by trained and compassionate professionals.</p>
        <p class="intro-text">CQC registered and rated <strong>Good</strong> across all five domains in June 2025, we are trusted by families, local authorities, and NHS teams across Leeds and Cornwall.</p>
        <a href="<?php echo esc_url(home_url('/about')); ?>" class="btn-navy" style="margin-top:8px;">Learn About Winserve</a>
      </div>
      <div>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-teal" style="display:block;text-align:center;padding:16px;margin-bottom:16px;font-size:14px;">&#128222; Request a Free Care Assessment</a>
        <div style="background:var(--section-bg);border:1px solid var(--border);border-radius:4px;padding:24px 20px;">
          <p style="font-family:var(--font-body);font-size:13px;color:var(--text-light);margin-bottom:16px;font-weight:600;text-transform:uppercase;letter-spacing:1px;">Our care services include:</p>
          <ul style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <?php
            $services = ['Domiciliary Care','Supported Living','Dementia Care','Palliative Care','Live-In Care','Complex Care','Personal Care','Medication Support','Companionship','Learning Disabilities','Mental Health Support','Physical Disabilities'];
            foreach ($services as $s) {
              echo '<li style="font-family:var(--font-body);font-size:13px;color:var(--text);display:flex;align-items:center;gap:6px;"><span style="color:var(--teal);font-size:16px;">&#10003;</span> ' . esc_html($s) . '</li>';
            }
            ?>
          </ul>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="btn-teal" style="margin-top:20px;display:block;text-align:center;font-size:12px;">View All Care Services &rarr;</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CQC WIDGET -->
<div class="cqc-widget-strip">
  <div class="container cqc-widget-inner">
    <p class="cqc-widget-label">Regulated by the Care Quality Commission — Rated Good June 2025</p>
    <div id="cqc-widget-home"></div>
    <script type="text/javascript" src="https://www.cqc.org.uk/sites/all/modules/custom/cqc_widget/widget.js?data-id=1-8945106634&data-host=https://www.cqc.org.uk&type=location"></script>
  </div>
</div>

<!-- SERVICES -->
<section class="services-section">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ What We Offer</span>
      <span class="teal-line"></span>
      <h2 class="section-title">Our Home Care Services</h2>
      <p>We provide a full range of domiciliary and supported living services, each individually tailored and delivered with genuine compassion and professionalism.</p>
    </div>

    <div class="services-row">
      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-new-1.jpg');"></div>
        <div class="service-card-bar"><span>Domiciliary Care</span><a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a></div>
        <div class="service-card-body">Professional home care visits tailored to each individual's needs, supporting independence in familiar surroundings.</div>
      </div>
      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-new-2.jpg');"></div>
        <div class="service-card-bar"><span>Supported Living</span><a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a></div>
        <div class="service-card-body">Enabling adults with learning disabilities, mental health needs, or physical disabilities to live independently with the right support.</div>
      </div>
      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-new-3.jpg');"></div>
        <div class="service-card-bar"><span>Dementia &amp; Alzheimer's</span><a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a></div>
        <div class="service-card-body">Specialist support helping those living with dementia maintain routine, safety, and dignity at home.</div>
      </div>
    </div>

    <div class="services-row">
      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-new-4.jpg');"></div>
        <div class="service-card-bar"><span>Live-In Care</span><a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a></div>
        <div class="service-card-body">A dedicated carer lives in the home providing round-the-clock support so individuals can remain in familiar surroundings.</div>
      </div>
      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-new-5.jpg');"></div>
        <div class="service-card-bar"><span>Complex Care</span><a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a></div>
        <div class="service-card-body">Specialist care for high-level health needs including acquired brain injuries, spinal conditions, and long-term conditions.</div>
      </div>
      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-new-6.jpg');"></div>
        <div class="service-card-bar"><span>Palliative Care</span><a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a></div>
        <div class="service-card-body">Compassionate end-of-life care focused on comfort, dignity, and quality of life for individuals and their families.</div>
      </div>
    </div>

    <div style="text-align:center;margin-top:36px;">
      <a href="<?php echo esc_url(home_url('/services')); ?>" class="btn-teal">View All Services &rarr;</a>
    </div>

    <!-- Quick Enquiry Form -->
    <div class="quick-enquiry-wrap">
      <div class="quick-enquiry-inner">
        <div class="quick-enquiry-left">
          <span class="section-eyebrow">+ Quick Enquiry</span>
          <h3>Not sure which service you need?</h3>
          <p>Leave your details and our team will call you back to help find the right care.</p>
        </div>
        <div class="quick-enquiry-right">
          <?php if (isset($_GET['sent']) && $_GET['sent'] === '1') : ?>
            <div class="alert-success">Thank you — we&rsquo;ll be in touch shortly.</div>
          <?php else : ?>
          <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="quick-enquiry-form">
            <input type="hidden" name="action" value="winserve_contact">
            <input type="hidden" name="pathway" value="Quick Enquiry">
            <?php wp_nonce_field('winserve_contact', 'winserve_nonce'); ?>
            <div class="qe-row">
              <input type="text" name="full_name" required placeholder="Your name">
              <input type="tel" name="phone" required placeholder="Phone number">
            </div>
            <div class="qe-row">
              <input type="email" name="email" required placeholder="Email address">
              <input type="text" name="postcode" placeholder="Your location / postcode">
            </div>
            <select name="subject">
              <option value="">Which service are you interested in?</option>
              <option>Domiciliary Care</option><option>Supported Living</option>
              <option>Dementia Care</option><option>Live-In Care</option>
              <option>Respite Care</option><option>Learning Disabilities Support</option>
              <option>Medication Assistance</option><option>Palliative Care</option>
              <option>Not sure — need advice</option>
            </select>
            <textarea name="message" rows="2" placeholder="Anything else you'd like us to know? (optional)"></textarea>
            <button type="submit" class="btn-teal" style="width:100%;border:none;cursor:pointer;font-size:13px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:14px;">Send Enquiry &rarr;</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- WHY CHOOSE US — FEATURE CARDS -->
<section class="features-section">
  <div class="container">
    <div class="features-header">
      <div>
        <span class="section-eyebrow">+ Why Choose Winserve</span>
        <span class="teal-line"></span>
        <h2 class="section-title" style="margin-bottom:0;">Care You Can Trust, Every Single Day</h2>
      </div>
      <a href="<?php echo esc_url(home_url('/about')); ?>" class="btn-teal">Read Testimonials</a>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></div>
        <h4>Person-Centred Care</h4>
        <p>Every care plan is built around the individual — their preferences, routines, and relationships.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <h4>Available When Needed</h4>
        <p>We are available to support families and service users whenever they need us — flexible and responsive.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
        <h4>CQC Rated Good</h4>
        <p>Independently inspected and rated Good across all five CQC domains in June 2025.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        <h4>High Calibre Carers</h4>
        <p>Every carer is DBS checked, fully trained, and selected for their compassion as much as their skills.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        <h4>Consistent Care Teams</h4>
        <p>We match service users with a small, consistent group of carers — continuity builds trust.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
        <h4>NHS &amp; Council Partner</h4>
        <p>We work with NHS discharge teams, local authorities, and social workers for seamless transitions.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></div>
        <h4>Ongoing Training</h4>
        <p>All carers complete our full induction, Care Certificate, and ongoing training through Click Learning.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon"><svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
        <h4>Transparent &amp; Open</h4>
        <p>Families can always reach us. We communicate openly and act quickly on feedback — always.</p>
      </div>
    </div>
  </div>
</section>

<!-- STATS CIRCLES -->
<section class="stats-section">
  <div class="container">
    <div class="stats-circles">
      <div class="stat-circle">
        <div class="stat-circle-ring">
          <svg class="stat-icon" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span class="stat-num">50<sup>+</sup></span>
        </div>
        <span class="stat-lbl">Dedicated Carers</span>
      </div>
      <div class="stat-circle">
        <div class="stat-circle-ring">
          <svg class="stat-icon" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
          <span class="stat-num">70<sup>+</sup></span>
        </div>
        <span class="stat-lbl">5&#9733; Reviews on Homecare.co.uk</span>
      </div>
      <div class="stat-circle">
        <div class="stat-circle-ring">
          <svg class="stat-icon" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <span class="stat-num">100<sup>%</sup></span>
        </div>
        <span class="stat-lbl">CQC Compliant</span>
      </div>
      <div class="stat-circle">
        <div class="stat-circle-ring">
          <svg class="stat-icon" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span class="stat-num">6<sup>+</sup></span>
        </div>
        <span class="stat-lbl">Years Delivering Quality Care</span>
      </div>
    </div>
  </div>
</section>

<!-- HOMECARE REVIEWS -->
<section class="reviews-section">
  <div class="container">
    <div class="reviews-homecare-header">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-homecare.png" alt="Homecare.co.uk" class="homecare-logo">
      <p class="homecare-caption">Independently verified reviews from the UK&rsquo;s No.1 home care website</p>
    </div>
    <div class="reviews-rating-bar">
      <span class="reviews-score">9.8</span>
      <div>
        <div class="reviews-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="reviews-source">from 74 reviews on Homecare.co.uk</div>
      </div>
    </div>
  </div>

  <div class="reviews-track-wrap">
    <div class="reviews-track">
      <?php
      $reviews = [
        ['name'=>'Sophie T','role'=>'Service User','date'=>'January 2026','stars'=>5,'text'=>'Service has been amazing. All staff members I have interacted with have been absolutely amazing and very professional. I cannot thank the team enough for the professional and empathetic care provided during the hardest time of my life.'],
        ['name'=>'Nicole S','role'=>'Daughter of Service User','date'=>'October 2025','stars'=>5,'text'=>'Winserve provided excellent care for my father after he left hospital following a stroke. The carers were approachable, respectful of his independence and privacy, and always professional even when things were difficult.'],
        ['name'=>'Beverley C','role'=>'Wife of Service User','date'=>'August 2025','stars'=>5,'text'=>'The care given to my husband I can only describe as warm and truly friendly. My husband has dementia and I was so pleased he had company and was in great hands while I was at work. The team genuinely cared.'],
        ['name'=>'Eleni-Rose B','role'=>'Granddaughter of Service User','date'=>'June 2025','stars'=>5,'text'=>'Nice and clear instructions and always receptive to our questions and needs. My grandmother says the care is excellent. I have been extremely impressed and grateful for everything this team does.'],
        ['name'=>'Hannah P','role'=>'Carer of Service User','date'=>'September 2025','stars'=>5,'text'=>'Winserve have provided my mum with superb care. I cannot thank them enough for the excellent care and compassion they have shown. The whole team deserve enormous credit.'],
        ['name'=>'Nicolas M','role'=>'Service User','date'=>'April 2025','stars'=>5,'text'=>'I am incredibly delighted by the service I receive. All they aim to do is please and I feel truly safe. The consistency and warmth of the care I receive deserves recognition.'],
        ['name'=>'Eleanor S','role'=>'Daughter of Service User','date'=>'December 2024','stars'=>5,'text'=>'They treated my mother and our family with great respect throughout her care. I would highly recommend Winserve Care to anyone looking for compassionate, reliable home care.'],
        ['name'=>'Molly A','role'=>'Daughter of Service User','date'=>'March 2025','stars'=>5,'text'=>'Our carers are absolutely brilliant — kind, thorough, punctual and incredibly gentle. The manager and the whole team are wonderful. We feel very well looked after.'],
      ];
      $all = array_merge($reviews,$reviews);
      foreach ($all as $r) {
        echo '<div class="review-card">';
        echo '<div class="review-stars">' . str_repeat('&#9733;',$r['stars']) . '</div>';
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
      <a href="https://www.homecare.co.uk/homecare/agency.cfm/id/65432238891" target="_blank" rel="noopener" class="btn-outline">Read all 74 reviews on Homecare.co.uk &rarr;</a>
    </div>
  </div>
</section>

<!-- FEATURED TESTIMONIAL -->
<div class="testi-featured">
  <div class="testi-content">
    <span class="eyebrow">+ Featured Testimonial</span>
    <h2>Trusted by Families Across Leeds &amp; Cornwall</h2>
    <blockquote class="testi-quote">"Service has been amazing. All staff members I have interacted with have been absolutely amazing and very professional. I cannot thank the team enough for the professional and empathetic care provided during the hardest time of my life."</blockquote>
    <div class="testi-nav">
      <div class="testi-author-info">
        <div class="tname">Sophie T</div>
        <div class="trole">Service User &mdash; January 2026</div>
      </div>
      <div class="testi-arrows">
        <button class="testi-arrow" aria-label="Previous">&#8592;</button>
        <button class="testi-arrow" aria-label="Next">&#8594;</button>
      </div>
    </div>
  </div>
  <div class="testi-photo"></div>
</div>

<!-- MINI TESTIMONIALS -->
<section class="mini-testis">
  <div class="container">
    <div class="mini-testis-grid">
      <div class="mini-testi-card">
        <div class="mini-testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="mini-testi-name">Beverley C</div>
        <div class="mini-testi-role">Wife of Service User</div>
        <p class="mini-testi-text"><span class="mini-testi-quote-mark">&ldquo;</span>The care given to my husband I can only describe as warm and truly friendly. My husband has dementia and I was so pleased he had company and was in great hands. The team genuinely cared."</p>
      </div>
      <div class="mini-testi-card">
        <div class="mini-testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="mini-testi-name">Hannah P</div>
        <div class="mini-testi-role">Carer of Service User</div>
        <p class="mini-testi-text"><span class="mini-testi-quote-mark">&ldquo;</span>Winserve have provided my mum with superb care. I cannot thank them enough for the excellent care and compassion they have shown. The whole team deserve enormous credit."</p>
      </div>
      <div class="mini-testi-card">
        <div class="mini-testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="mini-testi-name">Nicolas M</div>
        <div class="mini-testi-role">Service User</div>
        <p class="mini-testi-text"><span class="mini-testi-quote-mark">&ldquo;</span>I am incredibly delighted by the service I receive. All they aim to do is please and I feel truly safe. The consistency and warmth of the care I receive deserves recognition."</p>
      </div>
    </div>
  </div>
</section>

<!-- BLOG PREVIEW -->
<?php
$recent = new WP_Query(['posts_per_page'=>3,'post_status'=>'publish']);
if ($recent->have_posts()) :
?>
<section class="blog-section">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Latest News</span>
      <span class="teal-line"></span>
      <h2 class="section-title">Stay Up to Date With Winserve</h2>
      <p>Care news, health guidance, company updates, and stories from our team.</p>
    </div>
    <div class="blog-grid">
      <?php while ($recent->have_posts()) : $recent->the_post(); ?>
        <article class="blog-card">
          <div class="blog-card-img-wrap" style="position:relative;">
            <?php if (has_post_thumbnail()) : ?>
              <img src="<?php the_post_thumbnail_url('winserve-card'); ?>" alt="<?php the_title_attribute(); ?>" class="blog-card-img" style="width:100%;height:200px;object-fit:cover;">
            <?php else : ?>
              <div class="blog-card-img-ph"></div>
            <?php endif; ?>
            <div class="blog-date-badge"><?php echo get_the_date('d M'); ?></div>
          </div>
          <div class="blog-card-body">
            <div class="blog-meta"><?php echo get_the_category_list(', '); ?></div>
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p class="blog-excerpt"><?php echo wp_trim_words(get_the_excerpt(),18); ?></p>
            <a href="<?php the_permalink(); ?>" class="read-more">Read More &rarr;</a>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a href="<?php echo esc_url(home_url('/blog')); ?>" class="btn-outline">View All Posts &rarr;</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA BANNER -->
<section class="cta-banner">
  <div class="container">
    <h2>We do whatever it takes to bring you peace of mind.</h2>
    <p>Contact us today for a free, no-obligation care needs assessment. Our team is ready to help you find the right care.</p>
    <div class="cta-banner-btns">
      <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-outline-white">Request a Callback</a>
      <div class="cta-banner-phone">
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        <div><span>24/7 service available</span>0113 340 8777</div>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
