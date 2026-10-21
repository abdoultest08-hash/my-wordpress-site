<?php get_header(); ?>

<!-- SECTION 1: SPLIT HERO -->
<section class="hero-split">
  <div class="hero-split-panel">
    <div class="hero-split-inner">
      <span class="hero-badge">
        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        CQC Rated Good &bull; Dual Registered &bull; Yorkshire
      </span>
      <h1>Care is at the heart of what we do.</h1>
      <p class="hero-sub">Supported living and home care across Yorkshire. CQC registered for Personal Care and Treatment of Disease, Disorder or Injury, with a clinical lead on our team. Trusted by local authorities and the NHS since 2020.</p>
      <div class="hero-actions">
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-hero">Find Care &rarr;</a>
        <div class="hero-callus">
          <span>Give us a call:</span>
          <a href="tel:01133408777">0113 340 8777</a>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-split-media" role="img" aria-label="The Winserve Care Services team at our Leeds office"></div>
</section>

<!-- SECTION 2: ABOUT -->
<section class="about-section">
  <div class="container">
    <div class="about-grid">
      <div class="about-images">
        <div class="exp-badge">
          <span class="num">6<sup>+</sup></span>
          <span class="lbl">Years of Care</span>
        </div>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-with-user.jpg" alt="Winserve carers with a service user" class="about-main-img" loading="lazy" decoding="async" width="940" height="788">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/carer-indoor-gift.jpg" alt="Winserve carer supporting a service user at home" class="about-inset-img" loading="lazy" decoding="async" width="940" height="788">
      </div>
      <div class="about-content">
        <span class="eyebrow">+ Learn About Winserve</span>
        <h2>Compassionate Care from <em>Winserve</em>, Rooted in Yorkshire</h2>
        <div class="about-stat-box">
          <span class="snum">40<sup>+</sup></span>
          <p>Dedicated carers delivering high-quality, person-centred care across Yorkshire. Every carer is DBS checked, trained, and properly supported.</p>
        </div>
        <p class="about-body">Winserve Care Services Ltd has grown over 6 years into a trusted name in supported living and home care. We are CQC registered with a Good rating from June 2025, and proud to be a Real Living Wage employer.</p>
        <p class="about-body">Our mission is simple. We help every person we support live with dignity, independence, and confidence in the comfort of their own home.</p>
        <a href="<?php echo esc_url(home_url('/about')); ?>" class="btn-more">Learn More About Us &rarr;</a>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 3: HOMECARE REVIEWS CAROUSEL (moved up, right after About) -->
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
      $reviews = [
          ['name' => 'Sophie T', 'role' => 'Service User', 'date' => 'January 2026', 'stars' => 5,
           'text' => 'Service has been amazing. All staff members I have interacted with have been absolutely amazing and very professional. I cannot thank the team enough for the professional and empathetic care provided during the hardest time of my life.'],
          ['name' => 'Nicole S', 'role' => 'Daughter of Service User', 'date' => 'October 2025', 'stars' => 5,
           'text' => 'Winserve provided excellent care for my father after he left hospital following a stroke. The carers were approachable, respectful of his independence and privacy, and always professional even when things were difficult. We would recommend them to others.'],
          ['name' => 'Beverley C', 'role' => 'Wife of Service User', 'date' => 'August 2025', 'stars' => 5,
           'text' => 'The care given to my husband I can only describe as warm and truly friendly. My husband has dementia and I was so pleased he had company and was in great hands while I was at work. The team genuinely cared.'],
          ['name' => 'Eleni-Rose B', 'role' => 'Granddaughter of Service User', 'date' => 'June 2025', 'stars' => 5,
           'text' => 'Nice and clear instructions and always receptive to our questions and needs. My grandmother says the care is excellent. I have been extremely impressed and grateful for everything this team does.'],
          ['name' => 'Hannah P', 'role' => 'Carer of Service User', 'date' => 'September 2025', 'stars' => 5,
           'text' => 'Winserve have provided my mum with superb care. I cannot thank them enough for the excellent care and compassion they have shown. The whole team deserve enormous credit.'],
          ['name' => 'Nicolas M', 'role' => 'Service User', 'date' => 'April 2025', 'stars' => 5,
           'text' => 'I am incredibly delighted by the service I receive. All they aim to do is please and I feel truly safe. The consistency and warmth of the care I receive deserves recognition. Thank you to the whole team.'],
          ['name' => 'Eleanor S', 'role' => 'Daughter of Service User', 'date' => 'December 2024', 'stars' => 5,
           'text' => 'They treated my mother and our family with great respect throughout her care. I would highly recommend Winserve Care to anyone looking for compassionate, reliable home care. They truly are amazing.'],
          ['name' => 'M', 'role' => 'Daughter of Service User', 'date' => 'August 2024', 'stars' => 5,
           'text' => 'Winserve were recommended to us and we are so pleased we chose them. Very caring and very professional. The manager checks in regularly and the transition from hospital to home care was handled brilliantly.'],
          ['name' => 'Molly A', 'role' => 'Daughter of Service User', 'date' => 'March 2025', 'stars' => 5,
           'text' => 'Our carers are absolutely brilliant. Kind, thorough, punctual and incredibly gentle. The manager and the whole team are wonderful. We feel very well looked after and I cannot recommend them highly enough.'],
          ['name' => 'Brenda D', 'role' => 'Wife of Service User', 'date' => 'November 2024', 'stars' => 5,
           'text' => 'What a caring, consistent and effective team. They started promptly and every carer who came was wonderful. Thank you so much to all the staff at Winserve for the difference you have made to our lives.'],
      ];
      // Duplicate for seamless infinite scroll
      $all_reviews = array_merge($reviews, $reviews);
      foreach ($all_reviews as $r) {
          $stars = str_repeat('&#9733;', $r['stars']);
          echo '<div class="review-card">';
          echo '<div class="review-stars">' . $stars . '</div>';
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

<!-- SECTION 4: STATS BAR -->
<section class="stats-bar">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-col">
        <span class="stat-num">40<sup>+</sup></span>
        <span class="stat-lbl">Dedicated Carers</span>
      </div>
      <div class="stat-col">
        <span class="stat-num">75<sup>+</sup></span>
        <span class="stat-lbl">5&#9733; Reviews</span>
      </div>
      <div class="stat-col">
        <span class="stat-num">3<sup>:1</sup></span>
        <span class="stat-lbl">Staffing Ratio Packages</span>
      </div>
      <div class="stat-col">
        <span class="stat-num">6<sup>+</sup></span>
        <span class="stat-lbl">Years Operating</span>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 5: SERVICES PREVIEW (6 services, Supported Living leads) -->
<section class="services-section">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ What We Offer</span>
      <h2 class="section-title">Care Services Designed Around <em style="color:var(--blue);">You</em></h2>
    </div>
    <div class="services-grid">

      <div class="service-card service-card--lead">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/activity-outdoors.jpg');"></div>
        <span class="service-flag">Our Speciality</span>
        <div class="service-card-bar">
          <span>Supported Living</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Our main area of expertise. We support adults to live in their own homes with the right level of care around them, including packages at 3:1 staffing ratios that local authorities trust us to deliver.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-domiciliary-car.jpg');"></div>
        <div class="service-card-bar">
          <span>Domiciliary Care</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Regular visits at home covering personal care, medication, meals, and daily tasks. Consistent carers who turn up on time and do the job properly.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/home-social.jpg');"></div>
        <div class="service-card-bar">
          <span>Private Care Packages</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Arranging care privately? We work directly with you and your family to build a package around what you actually need, without the bureaucracy.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/activity-alpaca-group.jpg');"></div>
        <div class="service-card-bar">
          <span>Learning Disabilities</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Support for people with mild to profound learning disabilities, building confidence and independence at whatever pace works for the individual.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-end-of-life.jpg');"></div>
        <div class="service-card-bar">
          <span>End of Life Care</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">We support people and their families through the most difficult time they will face, with sensitivity, patience, and complete respect.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-mental-health.jpg');"></div>
        <div class="service-card-bar">
          <span>Mental Health Support</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Recovery-focused support alongside community mental health teams and families, keeping communication open and care consistent.</div>
      </div>

    </div>
    <div style="text-align:center;margin-top:44px;">
      <a href="<?php echo esc_url(home_url('/services')); ?>" class="btn-more">View All Services &rarr;</a>
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
            <div class="alert-success" style="margin:0;">Thank you, we will be in touch shortly.</div>
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
              <option>Supported Living</option>
              <option>Domiciliary Care</option>
              <option>Private Care Package</option>
              <option>Learning Disabilities</option>
              <option>End of Life Care</option>
              <option>Mental Health Support</option>
              <option>Not sure, need advice</option>
            </select>
            <textarea name="message" rows="2" placeholder="Anything else you would like us to know? (optional)"></textarea>
            <button type="submit" class="btn-cta" style="width:100%;justify-content:center;">Send Enquiry &rarr;</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- TDDI BAND (private / family facing) -->
<section class="tddi-band">
  <div class="container">
    <div class="tddi-band-inner">
      <div class="tddi-band-mark">
        <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
      </div>
      <div>
        <h3>Care at home, led by a clinician</h3>
        <p>Winserve is CQC registered for Treatment of Disease, Disorder or Injury as well as Personal Care. Most home care agencies hold the first registration only. It means we have a clinical lead on our team and we can support people whose needs go beyond help with washing, dressing and meals, in their own home rather than in a hospital or a care home.</p>
      </div>
      <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-more" style="white-space:nowrap;">Talk to Our Team &rarr;</a>
    </div>
  </div>
</section>
<!-- SECTION 6: FOR COMMISSIONERS -->
<section class="commissioner-section">
  <div class="container">

    <div class="commissioner-grid">

      <div class="commissioner-content">
        <span class="eyebrow">+ For Commissioners &amp; Health Case Managers</span>
        <h2>Most domiciliary providers stop at personal care. We do not.</h2>
        <p class="commissioner-lead">Winserve holds CQC registration for <strong>Personal Care</strong> and for <strong>Treatment of Disease, Disorder or Injury</strong>. That second registration is the one most home care providers do not have. It is why commissioners call us when a package needs clinical oversight in someone's own home, and why fewer of your placements have to be turned down or handed back.</p>

        <div class="tddi-flag">
          <div class="tddi-flag-mark">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div>
            <strong>Dual registered, clinically led</strong>
            <span>Verify both registrations on our CQC profile. Provider ID 1-8945106634.</span>
          </div>
        </div>

        <div class="commissioner-points">

          <div class="commissioner-point">
            <div class="cp-icon">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <div>
              <h4>A clinical lead inside our team</h4>
              <p>Clinical oversight sits in our organisation, not with a third party. Decisions get made faster and accountability stays in one place.</p>
            </div>
          </div>

          <div class="commissioner-point">
            <div class="cp-icon">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
              <h4>We deliver up to 3:1 staffing</h4>
              <p>High-ratio supported living is routine work for us. We build and brief the team around the individual before the package starts, not after.</p>
            </div>
          </div>

          <div class="commissioner-point">
            <div class="cp-icon">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>
            </div>
            <div>
              <h4>Good in all five CQC domains</h4>
              <p>Safe, Effective, Caring, Responsive and Well-led. Inspected June 2025. We will send you the report before you ask for it.</p>
            </div>
          </div>

          <div class="commissioner-point">
            <div class="cp-icon">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <h4>We move at your timescale</h4>
              <p>Tell us when you need it running. We will give you a straight answer on whether we can meet it, and we do not accept packages we cannot staff properly.</p>
            </div>
          </div>

          <div class="commissioner-point">
            <div class="cp-icon">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
              <h4>Carers who stay</h4>
              <p>Real Living Wage employer with low turnover across 40+ staff. The people you place see familiar faces, not a different agency worker every visit.</p>
            </div>
          </div>

          <div class="commissioner-point">
            <div class="cp-icon">
              <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            </div>
            <div>
              <h4>Partners since 2020</h4>
              <p>We have worked with local authorities and the NHS for six years. Ask them about us. That is the reference that counts.</p>
            </div>
          </div>

        </div>

        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-commissioner">Talk to Us About a Placement &rarr;</a>
      </div>

      <div class="commissioner-image">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-training-1.jpg" alt="Winserve staff training session in Leeds" loading="lazy" decoding="async" width="940" height="788">
        <div class="commissioner-img-caption">Regular in-house training keeps our team prepared for every package we take on</div>
      </div>

    </div>
  </div>
</section>

<!-- SECTION 7: WHY CHOOSE US -->
<section class="why-section">
  <div class="why-image">
    <div class="why-image-overlay"></div>
  </div>
  <div class="why-content">
    <span class="eyebrow">+ Why Choose Us</span>
    <h2>Care You Can Trust, Every Single Day</h2>
    <p class="why-quote">"Our carers do not just deliver care, they build relationships. Every person we support deserves to be treated with dignity, warmth, and genuine compassion."</p>

    <div class="why-item">
      <div class="why-icon">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      </div>
      <div>
        <h4>Care in Your Own Home</h4>
        <p>We bring professional care directly to your door, preserving the comfort and familiarity of home life for every person we support.</p>
      </div>
    </div>

    <div class="why-item">
      <div class="why-icon">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
      </div>
      <div>
        <h4>Fully Vetted and DBS Checked</h4>
        <p>Every carer is thoroughly vetted, DBS checked, and trained before they step into someone's home. No exceptions.</p>
      </div>
    </div>

    <div class="why-item">
      <div class="why-icon">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </div>
      <div>
        <h4>A Team That Stays</h4>
        <p>We pay the Real Living Wage and invest in our carers. Low turnover means the people we support see familiar faces, not strangers.</p>
      </div>
    </div>
  </div>
</section>

<!-- CQC WIDGET (moved down, after Why Choose Us) -->
<div class="cqc-widget-strip">
  <div class="container cqc-widget-inner">
    <p class="cqc-widget-label">Regulated by the Care Quality Commission</p>
    <div id="cqc-widget-home"></div>
    <script type="text/javascript" src="https://www.cqc.org.uk/sites/all/modules/custom/cqc_widget/widget.js?data-id=1-8945106634&data-host=https://www.cqc.org.uk&type=location"></script>
  </div>
</div>

<!-- SECTION 8: CTA BANNER -->
<section class="cta-banner">
  <div class="container cta-banner-inner">
    <div>
      <span class="cta-eyebrow">+ Free Assessment</span>
      <h2>Book Your Free, No-Obligation Assessment</h2>
      <p>Speak to our care team and find out how we can support you or your loved one to live well at home.</p>
    </div>
    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-cta">Contact Us &rarr;</a>
  </div>
</section>

<?php
// SECTION 9: BLOG PREVIEW (only if posts exist)
$recent = new WP_Query(['posts_per_page' => 3, 'post_status' => 'publish']);
if ($recent->have_posts()) :
?>
<section class="services-section" style="background:#fff;">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Latest News</span>
      <h2 class="section-title">From the Winserve Blog</h2>
    </div>
    <div class="blog-grid">
      <?php while ($recent->have_posts()) : $recent->the_post(); ?>
        <article class="blog-card">
          <?php if (has_post_thumbnail()) : ?>
            <img src="<?php the_post_thumbnail_url('winserve-card'); ?>" alt="<?php the_title_attribute(); ?>" class="blog-card-img" loading="lazy" decoding="async">
          <?php else : ?>
            <div class="blog-card-img-ph"></div>
          <?php endif; ?>
          <div class="blog-card-body">
            <div class="blog-meta"><?php echo get_the_date(); ?></div>
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p class="blog-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
            <a href="<?php the_permalink(); ?>" class="read-more">Read More &rarr;</a>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a href="<?php echo esc_url(home_url('/blog')); ?>" class="btn-more">View All Posts &rarr;</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
