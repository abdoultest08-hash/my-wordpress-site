<?php get_header(); ?>

<!-- SECTION 3: HERO -->
<section class="hero-section" role="banner">
  <div class="hero-overlay" aria-hidden="true"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">
        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
        Welcome to Winserve Care Services
      </div>
      <h1>Trusted Care, Right in Your Own Home.</h1>
      <p class="hero-sub">Compassionate domiciliary and supported living care across Leeds and Cornwall &mdash; provided by over 70 dedicated, CQC-registered carers.</p>
      <div class="hero-actions">
        <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn-primary">Get Started &rarr;</a>
        <button class="btn-play" aria-label="Watch our story">
          <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><polygon points="5,3 19,12 5,21"/></svg>
        </button>
      </div>
    </div>
  </div>
  <div class="hero-circle" aria-hidden="true">
    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#4db8ff"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
    <span>Care &amp; Love</span>
  </div>
</section>

<!-- SECTION 4: OVERLAP CARDS -->
<div class="overlap-cards" role="region" aria-label="Our highlights">
  <div class="overlap-card" style="background-image:url('<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/about-main.jpg')">
    <div class="overlap-card-overlay" aria-hidden="true"></div>
    <div class="overlap-card-content">
      <div class="overlap-card-icon">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </div>
      <h3>Dedicated Team</h3>
      <p>70+ trained carers across Leeds &amp; Cornwall</p>
    </div>
  </div>
  <div class="overlap-card" style="background-image:url('<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/service-domiciliary.jpg')">
    <div class="overlap-card-overlay" aria-hidden="true"></div>
    <div class="overlap-card-content">
      <div class="overlap-card-icon">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
      </div>
      <h3>Person-Centred Care</h3>
      <p>Every care plan tailored individually</p>
    </div>
  </div>
  <div class="overlap-card" style="background-image:url('<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/why-choose-us.jpg')">
    <div class="overlap-card-overlay" aria-hidden="true"></div>
    <div class="overlap-card-content">
      <div class="overlap-card-icon">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
      </div>
      <h3>CQC Registered</h3>
      <p>Rated Good by the Care Quality Commission</p>
    </div>
  </div>
</div>

<!-- SECTION 5: ABOUT -->
<section class="about-section" id="about">
  <div class="container">
    <div class="about-images">
      <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/about-main.jpg" alt="Winserve carer with service user" class="about-main-img">
      <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/about-inset.jpg" alt="Care team" class="about-inset-img">
      <div class="experience-badge">
        <span class="num">10+</span>
        <span class="label">Years of Experience</span>
      </div>
    </div>
    <div class="about-content">
      <p class="about-eyebrow">+ Learn About Winserve</p>
      <h2>Learn About Our <em>Winserve</em> Professional Care <span class="accent">Services</span></h2>
      <div class="stat-box">
        <span class="stat-num">70+</span>
        <p class="stat-desc">Dedicated carers working across Leeds and Cornwall, many with NHS partnerships and specialist training in complex care needs.</p>
      </div>
      <p class="about-body">Winserve Care Services Ltd has been delivering compassionate, person-centred care across Leeds and Cornwall for over a decade. Founded by Amon Mutyasira, our mission is simple: to enable people to live independently, with dignity, in the comfort of their own homes.</p>
      <p class="about-body">We are fully registered with the Care Quality Commission and rated Good. Every member of our team is DBS checked, professionally trained, and dedicated to the highest standards of care.</p>
      <div class="founder-row">
        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/about-inset.jpg" alt="Amon Mutyasira" class="founder-avatar">
        <div>
          <div class="founder-name">Amon Mutyasira</div>
          <div class="founder-title">Managing Director &amp; Founder</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 6: STATS BAR -->
<section class="stats-bar" role="region" aria-label="Our statistics">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-item">
        <span class="stat-number">70<sup>+</sup></span>
        <span class="stat-label">Dedicated Carers</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">2</span>
        <span class="stat-label">Regions Served</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">100<sup>%</sup></span>
        <span class="stat-label">CQC Compliant</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">10<sup>+</sup></span>
        <span class="stat-label">Years Operating</span>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 7: SERVICES -->
<section class="services-section" id="services">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ What We Offer</span>
      <h2>We Provide Care to Individuals Who Need It Most</h2>
    </div>
    <div class="services-grid">
      <article class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/service-domiciliary.jpg')" role="img" aria-label="Domiciliary Care"></div>
        <div class="service-card-bar">
          <span>Domiciliary Care</span>
          <a href="<?php echo esc_url( home_url( '/services' ) ); ?>#domiciliary" class="service-card-btn" aria-label="Learn more about Domiciliary Care">+</a>
        </div>
        <div class="service-card-body">Supporting clients with personal care, medication management, and daily living activities in the comfort of their own homes.</div>
      </article>
      <article class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/service-supported-living.jpg')" role="img" aria-label="Supported Living"></div>
        <div class="service-card-bar">
          <span>Supported Living</span>
          <a href="<?php echo esc_url( home_url( '/services' ) ); ?>#supported-living" class="service-card-btn" aria-label="Learn more about Supported Living">+</a>
        </div>
        <div class="service-card-body">Enabling adults with learning disabilities and mental health needs to live independently with the right level of support.</div>
      </article>
      <article class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/service-complex-care.jpg')" role="img" aria-label="Complex Care"></div>
        <div class="service-card-bar">
          <span>Complex Care</span>
          <a href="<?php echo esc_url( home_url( '/services' ) ); ?>#complex-care" class="service-card-btn" aria-label="Learn more about Complex Care">+</a>
        </div>
        <div class="service-card-body">Specialist care for clients with complex health needs including acquired brain injuries, physical disabilities, and long-term conditions.</div>
      </article>
    </div>
  </div>
</section>

<!-- SECTION 8: WHY CHOOSE US -->
<section class="why-section" id="why-us">
  <div class="why-image" role="img" aria-label="A carer supporting a client at home">
    <div class="why-image-overlay">
      <button class="why-play-btn" aria-label="Watch video">
        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><polygon points="5,3 19,12 5,21"/></svg>
      </button>
    </div>
  </div>
  <div class="why-content">
    <p class="about-eyebrow">+ Why Choose Us</p>
    <h2>Trusted &amp; Experienced Care Provider Across England</h2>
    <blockquote class="why-quote">"We believe every person deserves dignified, compassionate care &mdash; in their own home, on their own terms."</blockquote>
    <div class="why-item">
      <div class="why-icon">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      </div>
      <div>
        <h4>Home Visit Care</h4>
        <p>We deliver care in familiar surroundings, preserving independence and reducing the need for residential placement.</p>
      </div>
    </div>
    <div class="why-item">
      <div class="why-icon">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
      </div>
      <div>
        <h4>Fully Regulated &amp; DBS Checked</h4>
        <p>All carers are DBS checked, professionally trained, and working under CQC-registered oversight.</p>
      </div>
    </div>
    <div class="why-item">
      <div class="why-icon">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      </div>
      <div>
        <h4>NHS &amp; Council Partnerships</h4>
        <p>We work closely with NHS trusts, local councils, and social care commissioners across Leeds and Cornwall.</p>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 9: TESTIMONIALS -->
<section class="testimonials-section" id="testimonials">
  <div class="testimonials-content">
    <p class="about-eyebrow">+ Our Testimonials</p>
    <h2>What Families Say About Our Care &amp; Support</h2>
    <p class="testimonial-quote">"The carers from Winserve have been absolutely incredible with my mother. They are professional, kind, and genuinely care about her wellbeing. I feel confident she is in safe hands every day, and I can't thank the team enough for the peace of mind they've given our whole family."</p>
    <div class="testimonial-nav">
      <div class="testimonial-author">
        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/testimonial-photo.jpg" alt="Family member" class="testimonial-avatar">
        <div>
          <div class="testimonial-name">Sarah Mitchell</div>
          <div class="testimonial-role">Family Member, Leeds</div>
        </div>
      </div>
      <div class="testimonial-arrows">
        <button class="testimonial-arrow" aria-label="Previous testimonial">&larr;</button>
        <button class="testimonial-arrow" aria-label="Next testimonial">&rarr;</button>
      </div>
    </div>
  </div>
  <div class="testimonial-photo" role="img" aria-label="Happy family receiving care"></div>
</section>

<!-- SECTION 10: CTA BANNER -->
<section class="cta-banner">
  <div class="container">
    <div class="cta-inner">
      <div class="cta-text">
        <p class="cta-eyebrow">+ Get In Touch</p>
        <h2>Book Your Free, No-Obligation Assessment Today</h2>
        <p class="cta-sub">Speak to one of our care specialists and find the right support for you or your loved one.</p>
      </div>
      <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn-cta">Contact Us &rarr;</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
