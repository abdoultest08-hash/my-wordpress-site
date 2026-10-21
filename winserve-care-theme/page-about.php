<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge">About Us</span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; About Us</p>
  </div>
</section>

<!-- SECTION 1: WHO WE ARE -->
<section class="story-section">
  <div class="container">
    <div class="story-grid">
      <div class="story-img-wrap">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-with-user.jpg" alt="Winserve team with a service user" class="story-img" loading="lazy" decoding="async" width="940" height="788">
      </div>
      <div class="story-content">
        <span class="eyebrow">+ Who We Are</span>
        <h2>A Care Company Built on Purpose, Not Just Process</h2>
        <p>Winserve Care Services Ltd is a CQC-registered domiciliary and supported living care provider operating across Yorkshire. Founded with a clear mission, to deliver genuinely compassionate, person-centred home care, we have grown over six years into a trusted name in health and social care.</p>
        <p>We are not a franchise. We are an independent, values-led organisation where every decision is guided by one question: <em>what is best for the people we support?</em> That principle drives how we hire, how we train, and how we show up every day for our service users and their families.</p>
        <p>Our CQC Good rating, achieved across all five key lines of enquiry in June 2025, reflects the hard work of our 40+ carers and the leadership team that supports them.</p>
        <ul class="story-bullets">
          <li>Founded and led by an experienced care management team</li>
          <li>CQC registered and rated Good across all 5 domains, June 2025</li>
          <li>Dual registered: Personal Care and Treatment of Disease, Disorder or Injury</li>
          <li>Clinical lead on the team, so care is clinically overseen</li>
          <li>40+ dedicated, DBS-checked and trained carers</li>
          <li>Serving families and commissioners across Yorkshire</li>
          <li>Real Living Wage employer, because we value our team</li>
          <li>Armed Forces Covenant signatory</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 2: STATS -->
<section class="stats-bar">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-col">
        <span class="stat-num">40<sup>+</sup></span>
        <span class="stat-lbl">Dedicated Carers</span>
      </div>
      <div class="stat-col">
        <span class="stat-num">3<sup>:1</sup></span>
        <span class="stat-lbl">Staffing Ratio Packages</span>
      </div>
      <div class="stat-col">
        <span class="stat-num">100<sup>%</sup></span>
        <span class="stat-lbl">CQC Compliant</span>
      </div>
      <div class="stat-col">
        <span class="stat-num">6<sup>+</sup></span>
        <span class="stat-lbl">Years Operating</span>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 3: CHARITY & COMMUNITY, REAL STORIES -->
<section style="padding:80px 0;background:#fff;">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Giving Back</span>
      <h2 class="section-title">More Than a Care Company</h2>
      <p style="font-family:var(--font-body);font-size:15px;color:#555;max-width:680px;margin:16px auto 0;line-height:1.85;">Care is at the heart of what we do, and that extends beyond our service users. From running marathons to donating to those sleeping rough, Winserve gives back to the communities we serve.</p>
    </div>

    <!-- Story 1: Marathon -->
    <div class="csr-story-block" style="display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center;margin-top:64px;">
      <div class="csr-story-images">
        <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:12px;">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/marathon-1.jpg" alt="Winserve Managing Director running the Manchester Marathon for Cancer Research UK" style="width:100%;border-radius:10px;object-fit:cover;object-position:center 22%;height:280px;" loading="lazy" decoding="async" width="769" height="484">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/marathon-2.jpg" alt="Completing the Manchester Marathon with Cancer Research UK medal" style="width:100%;border-radius:10px;object-fit:cover;object-position:center 8%;height:280px;" loading="lazy" decoding="async" width="474" height="533">
        </div>
      </div>
      <div class="csr-story-content">
        <span class="eyebrow" style="color:var(--blue);">+ Manchester Marathon 2023</span>
        <h3 style="font-family:var(--font-heading);font-size:28px;color:var(--navy);margin:12px 0 16px;line-height:1.25;">Running for a Cause That Hits Close to Home</h3>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:16px;">In April 2023, our Managing Director completed the Manchester Marathon, raising over <strong>&pound;1,200 for Cancer Research UK</strong>. The motivation was deeply personal, having lost both his father and his sister to cancer, and working daily alongside service users in palliative care, he understood better than most why this research matters.</p>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:16px;">Months of early-morning training runs and late-night sessions after work led to that finish line. But the real reason behind it was simple: a belief that if you can do something, you should.</p>
        <blockquote style="border-left:3px solid var(--blue);padding-left:16px;margin:20px 0;font-family:var(--font-body);font-size:14px;color:var(--navy);font-style:italic;line-height:1.8;">"We work every day with service users who have cancer. I lost my father and my sister to cancer, and several of our service users in palliative care are fighting cancer. We realise the importance of Cancer Research to reduce people's suffering."</blockquote>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;">This is what we mean when we say <em>care is at the heart of what we do.</em> It is not a slogan, it is the reason this company exists.</p>
      </div>
    </div>

    <!-- Divider -->
    <hr style="border:none;border-top:1px solid var(--border);margin:72px 0;">

    <!-- Story 2: Homeless Hampers -->
    <div class="csr-story-block" style="display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center;">
      <div class="csr-story-content">
        <span class="eyebrow" style="color:var(--blue);">+ Homeless Hampers, Leeds</span>
        <h3 style="font-family:var(--font-heading);font-size:28px;color:var(--navy);margin:12px 0 16px;line-height:1.25;">Spreading Warmth &amp; Hope in Our Community</h3>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:16px;">Since 2021, Winserve has made annual donations to <strong>Homeless Hampers</strong>, a Leeds-based charity providing warm clothing and essentials to those sleeping rough. We began donating due to the significant number of homeless people in Leeds who have served in the military, a cause close to our hearts as an Armed Forces Covenant signatory.</p>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:16px;">In 2023, we gifted <strong>&pound;500 worth of warm clothing</strong>, scarves, hats, coats, thermals, leggings, underwear, and jumpers, items that do more than provide comfort. In the coldest months, warm clothing saves lives, reducing the risk of cold-related illnesses like pneumonia among those most vulnerable.</p>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;">Through our work with local councils, we support many individuals who have experienced homelessness in the past. Giving back to those still facing it is something we are proud to do year after year.</p>
      </div>
      <div>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/homeless-clotehs.jpg" alt="Winserve team members with Homeless Hampers donation boxes" style="width:100%;border-radius:10px;object-fit:cover;object-position:center top;max-height:320px;" loading="lazy" decoding="async" width="1092" height="825">
        <p style="font-family:var(--font-body);font-size:12px;color:#888;margin-top:10px;text-align:center;font-style:italic;">Our team with Tina, lead manager of Homeless Hampers Leeds</p>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/charity-st-petrocs.jpg" alt="Winserve team delivering donated sleeping bags and warm clothing to a homelessness charity" style="width:100%;border-radius:10px;object-fit:cover;object-position:center top;max-height:320px;margin-top:18px;" loading="lazy" decoding="async" width="1130" height="765">
        <p style="font-family:var(--font-body);font-size:12px;color:#888;margin-top:10px;text-align:center;font-style:italic;">Delivering sleeping bags, thermals and warm clothing to a homelessness charity partner</p>
      </div>
    </div>

    <!-- Divider -->
    <hr style="border:none;border-top:1px solid var(--border);margin:72px 0;">

    <!-- Certificates of Recognition -->
    <div>
      <div style="text-align:center;margin-bottom:40px;">
        <span class="eyebrow" style="color:var(--blue);">+ Recognised Contributions</span>
        <h3 style="font-family:var(--font-heading);font-size:28px;font-weight:800;color:var(--navy);margin:12px 0 12px;line-height:1.25;letter-spacing:-0.02em;">Certificates of Recognition</h3>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;max-width:620px;margin:0 auto;">As an Armed Forces Covenant signatory, supporting veterans matters to us. These certificates recognise our ongoing contributions to charities working with homeless and former service personnel.</p>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">
        <div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:18px;box-shadow:0 4px 20px rgba(0,50,120,0.06);">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/cert-homeless-soldiers.jpg" alt="Homeless Soldier Project certificate of recognition awarded to Winserve Care Services" style="width:100%;border-radius:6px;display:block;" loading="lazy" decoding="async" width="900" height="632">
          <div style="padding:16px 6px 4px;">
            <div style="font-family:var(--font-heading);font-size:16px;font-weight:700;color:var(--navy);margin-bottom:5px;">Homeless Soldier Project</div>
            <p style="font-family:var(--font-body);font-size:13px;color:#666;line-height:1.75;margin:0;">Recognised for supporting the Homeless Soldier Project, helping former service personnel who have found themselves sleeping rough.</p>
          </div>
        </div>

        <div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:18px;box-shadow:0 4px 20px rgba(0,50,120,0.06);">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/cert-royal-fusiliers.jpg" alt="Royal Regiment of Fusiliers certificate of recognition awarded to Winserve Care Services" style="width:100%;border-radius:6px;display:block;" loading="lazy" decoding="async" width="900" height="609">
          <div style="padding:16px 6px 4px;">
            <div style="font-family:var(--font-heading);font-size:16px;font-weight:700;color:var(--navy);margin-bottom:5px;">Royal Regiment of Fusiliers</div>
            <p style="font-family:var(--font-body);font-size:13px;color:#666;line-height:1.75;margin:0;">Recognised for our support of the Royal Regiment of Fusiliers and the welfare work they carry out for serving and veteran members.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>


<!-- SECTION 4: OUR GOALS & MISSION -->
<section class="values-section">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Our Mission &amp; Goals</span>
      <h2 class="section-title">What We Are Here to Do</h2>
      <p style="font-family:var(--font-body);font-size:15px;color:#555;line-height:1.85;max-width:760px;margin:16px auto 0;">Our mission is simple. We help every person we support live with dignity, independence, and confidence, in their own home or community setting, surrounded by people they trust. Great care is not just about completing tasks. It is about building genuine relationships, listening carefully, and treating every person as the individual they are.</p>
    </div>

    <div class="values-rows">

      <div class="value-row">
        <div class="value-row-icon">
          <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </div>
        <div class="value-row-title"><span class="value-row-num">01</span><h3>Compassion</h3></div>
        <p>Every interaction starts with empathy. We treat every person we support the way we would want our own family to be treated, and we take the time to understand what matters to them.</p>
      </div>

      <div class="value-row">
        <div class="value-row-icon">
          <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div class="value-row-title"><span class="value-row-num">02</span><h3>Integrity</h3></div>
        <p>Honest, transparent, and accountable in everything we do. Families and commissioners always know where they stand with us, and we welcome scrutiny because we have nothing to hide.</p>
      </div>

      <div class="value-row">
        <div class="value-row-icon">
          <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div class="value-row-title"><span class="value-row-num">03</span><h3>Excellence</h3></div>
        <p>We hold ourselves to the highest standards. Our CQC Good rating across all five domains, and a 9.5 out of 10 score from independently verified reviews, are the evidence of that commitment.</p>
      </div>

    </div>
  </div>
</section>

<!-- SECTION 5: ACCREDITATIONS & PARTNER LOGOS -->
<section style="padding:64px 0;background:#fff;border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
  <div class="container" style="text-align:center;">
    <span class="eyebrow">+ Our Accreditations &amp; Partners</span>
    <h2 style="font-family:var(--font-heading);font-size:32px;color:var(--navy);margin:12px 0 8px;">Certified, Compliant &amp; Trusted</h2>
    <p style="font-family:var(--font-body);font-size:14px;color:#666;max-width:600px;margin:0 auto 40px;line-height:1.8;">Our partnerships and accreditations reflect our commitment to operating to the highest standards of quality, safety, and governance.</p>

    <div class="about-logos-grid">
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-cqc.png" alt="Care Quality Commission, rated Good" loading="lazy" decoding="async" width="420" height="233"></div>
        <span class="about-logo-caption">CQC Rated Good</span>
      </div>
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-nhs.jpg" alt="NHS England" loading="lazy" decoding="async" width="440" height="248"></div>
        <span class="about-logo-caption">NHS Partner</span>
      </div>
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-living-wage.png" alt="Real Living Wage Employer" loading="lazy" decoding="async" width="400" height="316"></div>
        <span class="about-logo-caption">Real Living Wage</span>
      </div>
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-armed-forces.webp" alt="Armed Forces Covenant" loading="lazy" decoding="async" width="300" height="113"></div>
        <span class="about-logo-caption">Armed Forces Covenant</span>
      </div>
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-chas.webp" alt="CHAS Standard" loading="lazy" decoding="async" width="300" height="300"></div>
        <span class="about-logo-caption">CHAS Accredited</span>
      </div>
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-dbs.png" alt="Disclosure and Barring Service" loading="lazy" decoding="async" width="420" height="280"></div>
        <span class="about-logo-caption">DBS Checked Staff</span>
      </div>
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-qcs.webp" alt="QCS Quality Compliance Systems" loading="lazy" decoding="async" width="500" height="149"></div>
        <span class="about-logo-caption">QCS Compliance</span>
      </div>
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-click-learning.webp" alt="Click Learning" loading="lazy" decoding="async" width="300" height="369"></div>
        <span class="about-logo-caption">Click Learning</span>
      </div>
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-bpr-heaton.webp" alt="BPR Heaton Chartered Accountants" loading="lazy" decoding="async" width="500" height="91"></div>
        <span class="about-logo-caption">BPR Heaton</span>
      </div>
      <div class="about-logo-item">
        <div class="about-logo-box"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-homecare.png" alt="Homecare.co.uk" loading="lazy" decoding="async" width="435" height="116"></div>
        <span class="about-logo-caption">Homecare.co.uk</span>
      </div>
    </div>
  </div>
</section>


<!-- SECTION 6: WHAT MAKES US DIFFERENT -->
<section style="padding:80px 0;background:var(--section-bg);">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ What Makes Us Different</span>
      <h2 class="section-title">Why Families &amp; Commissioners Choose Winserve</h2>
    </div>
    <div class="diff-grid">
      <div class="diff-card">
        <div class="diff-num">01</div>
        <h4>Genuinely Person-Centred</h4>
        <p>Every care plan is built around the individual, their preferences, routines, goals, and relationships. We don't use templates. We listen, then we build.</p>
      </div>
      <div class="diff-card">
        <div class="diff-num">02</div>
        <h4>Consistent Care Teams</h4>
        <p>We match service users with a small, consistent group of carers, not a rotating roster of strangers. Continuity builds trust, and trust is the foundation of great care.</p>
      </div>
      <div class="diff-card">
        <div class="diff-num">03</div>
        <h4>CQC Rated Good</h4>
        <p>Our June 2025 inspection confirmed Good ratings across all five CQC domains: Safe, Effective, Caring, Responsive, and Well-led. An independent validation of our quality.</p>
      </div>
      <div class="diff-card">
        <div class="diff-num">04</div>
        <h4>Highly Trained Staff</h4>
        <p>All carers complete our full induction programme, Care Certificate, and ongoing training via Click Learning. Every team member is DBS checked before their first visit.</p>
      </div>
      <div class="diff-card">
        <div class="diff-num">05</div>
        <h4>Transparent &amp; Responsive</h4>
        <p>Families and commissioners can always reach us. We communicate openly, act quickly on feedback, and welcome scrutiny, because we have nothing to hide.</p>
      </div>
      <div class="diff-card">
        <div class="diff-num">06</div>
        <h4>Values-Led Organisation</h4>
        <p>We are a Real Living Wage employer. We invest in our staff because great staff deliver great care. Our team stays with us because we look after them too.</p>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 7: WHO WE WORK WITH -->
<section style="padding:80px 0;background:#fff;">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Who We Work With</span>
      <h2 class="section-title">NHS, Local Authority &amp; Private Clients</h2>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:32px;margin-top:48px;">

      <div style="background:var(--section-bg);border:1px solid var(--border);border-radius:10px;padding:32px 28px;">
        <div style="width:48px;height:48px;background:var(--card-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--blue);margin-bottom:16px;">
          <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <h3 style="font-family:var(--font-heading);font-size:22px;color:var(--navy);margin-bottom:10px;">Local Authorities &amp; Commissioners</h3>
        <p style="font-family:var(--font-body);font-size:13px;color:#666;line-height:1.8;">We hold contracts with local authority commissioners and work in partnership with integrated care systems across Yorkshire. Our compliance framework, QCS-managed policies, and CQC Good rating give commissioners the assurance they need to refer with confidence.</p>
      </div>

      <div style="background:var(--section-bg);border:1px solid var(--border);border-radius:10px;padding:32px 28px;">
        <div style="width:48px;height:48px;background:var(--card-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--blue);margin-bottom:16px;">
          <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </div>
        <h3 style="font-family:var(--font-heading);font-size:22px;color:var(--navy);margin-bottom:10px;">NHS &amp; Healthcare Partners</h3>
        <p style="font-family:var(--font-body);font-size:13px;color:#666;line-height:1.8;">We work closely with NHS discharge teams, community nurses, occupational therapists, and social workers to ensure seamless transitions from hospital to home. Our carers are trained to communicate effectively with clinical teams and escalate concerns promptly when a service user's condition changes.</p>
      </div>

      <div style="background:var(--section-bg);border:1px solid var(--border);border-radius:10px;padding:32px 28px;">
        <div style="width:48px;height:48px;background:var(--card-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--blue);margin-bottom:16px;">
          <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <h3 style="font-family:var(--font-heading);font-size:22px;color:var(--navy);margin-bottom:10px;">Private Clients &amp; Families</h3>
        <p style="font-family:var(--font-body);font-size:13px;color:#666;line-height:1.8;">For privately funded clients, we offer a fully personalised service with direct access to our management team. We are transparent about costs and care delivery, and families can contact us at any time. We take pride in becoming a trusted extension of the family for the people we support.</p>
      </div>

    </div>
  </div>
</section>

<!-- SECTION 8: TRAINING & DEVELOPMENT -->
<section style="padding:80px 0;background:var(--section-bg);">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Investing in Our People</span>
      <h2 class="section-title">Trained, Supported, and Set Up to Succeed</h2>
      <p style="font-family:var(--font-body);font-size:15px;color:#555;max-width:680px;margin:16px auto 0;line-height:1.85;">Good care starts with well-trained carers who feel valued. We run regular in-house training, pay the Real Living Wage, and invest in ongoing development so our team can handle whatever a package needs.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;margin-top:56px;">
      <div>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-training-1.jpg" alt="Winserve carers in a training session" style="width:100%;border-radius:10px;object-fit:cover;height:340px;" loading="lazy" decoding="async" width="940" height="788">
      </div>
      <div>
        <h3 style="font-family:var(--font-heading);font-size:26px;font-weight:800;color:var(--navy);margin-bottom:16px;line-height:1.25;letter-spacing:-0.02em;">Training That Actually Prepares People</h3>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:16px;">Every carer completes a full induction before they set foot in someone's home. Beyond the mandatory training, we bring the team together regularly to work through real scenarios, share what is working, and make sure everyone is confident with the specific needs of the people we support.</p>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:24px;">For higher-support packages, including our 3:1 staffing ratio placements, staff receive additional preparation specific to that person before the package starts. Commissioners tell us this is one of the reasons they keep coming back.</p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div style="background:#fff;border:1px solid var(--border);border-radius:8px;padding:16px 18px;">
            <div style="font-family:var(--font-heading);font-size:14px;font-weight:700;color:var(--navy);margin-bottom:5px;">Full Induction</div>
            <p style="font-family:var(--font-body);font-size:12.5px;color:#666;line-height:1.7;margin:0;">Completed before any carer starts working with a service user.</p>
          </div>
          <div style="background:#fff;border:1px solid var(--border);border-radius:8px;padding:16px 18px;">
            <div style="font-family:var(--font-heading);font-size:14px;font-weight:700;color:var(--navy);margin-bottom:5px;">Ongoing CPD</div>
            <p style="font-family:var(--font-body);font-size:12.5px;color:#666;line-height:1.7;margin:0;">Continuous development through our Click Learning platform.</p>
          </div>
          <div style="background:#fff;border:1px solid var(--border);border-radius:8px;padding:16px 18px;">
            <div style="font-family:var(--font-heading);font-size:14px;font-weight:700;color:var(--navy);margin-bottom:5px;">Package-Specific Prep</div>
            <p style="font-family:var(--font-body);font-size:12.5px;color:#666;line-height:1.7;margin:0;">Extra training tailored to each individual placement.</p>
          </div>
          <div style="background:#fff;border:1px solid var(--border);border-radius:8px;padding:16px 18px;">
            <div style="font-family:var(--font-heading);font-size:14px;font-weight:700;color:var(--navy);margin-bottom:5px;">Real Living Wage</div>
            <p style="font-family:var(--font-body);font-size:12.5px;color:#666;line-height:1.7;margin:0;">We pay properly, so our carers stay and our service users see familiar faces.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="about-photo-strip" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:48px;">
      <div style="border-radius:10px;overflow:hidden;">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-training-2.jpg" alt="Winserve team meeting" style="width:100%;height:200px;object-fit:cover;display:block;" loading="lazy" decoding="async" width="940" height="788">
      </div>
      <div style="border-radius:10px;overflow:hidden;">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/activity-alpaca-walk.jpg" alt="Carer and service user on an alpaca walk" style="width:100%;height:200px;object-fit:cover;display:block;" loading="lazy" decoding="async" width="940" height="788">
      </div>
      <div style="border-radius:10px;overflow:hidden;">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/activity-garden.jpg" alt="Service user gardening with support" style="width:100%;height:200px;object-fit:cover;display:block;" loading="lazy" decoding="async" width="940" height="788">
      </div>
      <div style="border-radius:10px;overflow:hidden;">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/activity-coast.jpg" alt="Carer and service user exploring the coast" style="width:100%;height:200px;object-fit:cover;display:block;" loading="lazy" decoding="async" width="940" height="788">
      </div>
      <div style="border-radius:10px;overflow:hidden;">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/activity-alpaca-group.jpg" alt="Group alpaca walk with Winserve carers" style="width:100%;height:200px;object-fit:cover;display:block;" loading="lazy" decoding="async" width="940" height="788">
      </div>
      <div style="border-radius:10px;overflow:hidden;">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/carer-branded-car.jpg" alt="Winserve carer with a branded company vehicle" style="width:100%;height:200px;object-fit:cover;display:block;" loading="lazy" decoding="async" width="600" height="400">
      </div>
    </div>
  </div>
</section>

<!-- SECTION 9: OUR APPROACH TO COMPLIANCE -->
<section style="padding:80px 0;background:var(--navy);">
  <div class="container">
    <div class="section-header" style="margin-bottom:48px;">
      <span class="section-eyebrow" style="color:var(--light-blue);">+ Compliance &amp; Quality</span>
      <h2 class="section-title" style="color:#fff;">Our Approach to Compliance</h2>
      <p style="font-family:var(--font-body);font-size:14px;color:rgba(255,255,255,0.7);max-width:680px;margin:12px auto 0;line-height:1.85;">Compliance is not a box-ticking exercise for us, it is how we protect the people we care for and maintain the trust of everyone we work with. We have invested in the systems, training, and governance frameworks that make excellent, safe care possible every day.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start;">

      <div>
        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:32px;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">CQC Registration &amp; Good Rating</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">We are fully registered with the Care Quality Commission. Our most recent inspection (June 2025) resulted in a Good rating across all five domains, Safe, Effective, Caring, Responsive, and Well-led. We prepare year-round for CQC scrutiny, not just inspection periods.</p>
          </div>
        </div>

        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:32px;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">QCS, Quality Compliance Systems</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">We use Quality Compliance Systems (QCS) to manage our policies, procedures, and care documentation. QCS provides a comprehensive, regularly updated framework that keeps us aligned with the latest CQC standards, legislation, and best practice guidance across the sector.</p>
          </div>
        </div>

        <div style="display:flex;gap:20px;align-items:flex-start;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">CHAS Health &amp; Safety Accreditation</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">Winserve holds CHAS Standard accreditation, the UK's leading health and safety pre-qualification scheme. This certification demonstrates that our health and safety practices meet a recognised, independently assessed standard, providing assurance to contractors and commissioners alike.</p>
          </div>
        </div>
      </div>

      <div>
        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:32px;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">Click Learning, Staff Training Platform</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">Every member of our care team is enrolled on Click Learning, our dedicated online training platform. From mandatory modules (safeguarding, infection control, manual handling) to specialist care subjects, Click Learning ensures all staff training is recorded, up to date, and meets regulatory requirements.</p>
          </div>
        </div>

        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:32px;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">Safer Recruitment &amp; DBS Checks</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">We follow a rigorous safer recruitment process for every hire, enhanced DBS checks, right-to-work verification, two employment references, and a structured induction. No carer ever attends a visit unsupervised until we are fully confident in their suitability and competence.</p>
          </div>
        </div>

        <div style="display:flex;gap:20px;align-items:flex-start;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">Financial Governance &amp; Accountability</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">Winserve works with BPR Heaton Chartered Accountants for financial oversight, ensuring full accountability in our operations, invoicing, and reporting. Sound financial governance underpins the sustainability and reliability of our service delivery.</p>
          </div>
        </div>
      </div>

    </div><!-- grid -->
  </div>
</section>

<!-- SECTION 10: CQC RATING -->
<section class="cqc-section">
  <div class="container cqc-inner">
    <div class="cqc-badge">CQC Rated: Good</div>
    <h2>Independently Verified Quality Care</h2>
    <p>The Care Quality Commission is the independent regulator of health and social care in England. Our Good rating confirms that Winserve Care Services delivers safe, effective, caring, responsive, and well-led care. CQC Provider ID: 1-8945106634.</p>
    <a href="https://www.cqc.org.uk/location/1-8945106634" target="_blank" rel="noopener" class="btn-outline-white">View Our CQC Report &rarr;</a>
  </div>
</section>

<!-- SECTION 11: HOMECARE REVIEWS WIDGET -->
<section class="homecare-widget-strip" style="padding:56px 0;">
  <div class="container">
    <p class="hw-label">What families say, independently verified reviews from Homecare.co.uk</p>
    <div class="homecare-widget-inner">
      <script async class='tg-review-widget' type='text/javascript' src='https://api.homecare.co.uk/assets/js/review_widget.js?displaydiv=tgrw-about-page&displayid=65432238891&displaycontent=snippet&displaywidth=300&displaycount=2&displayscore=true&displaylink=false&displayborder=true&displaybackgroundcolor=faded&displaypagination=false&displaystrapline=true&displayfontsize=default&displayminoverallrating=0&displayallratings=false&displaylogo=true&displaywrappers=true&displaybutton=true&displaysettingname=true&displayratingreview=true&linksnofollow=false'></script>
      <div class='tg-review-widget-container' id='tgrw-about-page'></div>
    </div>
  </div>
</section>

<!-- SECTION 12: TEAM INTRO + CTA -->
<section style="padding:80px 0;text-align:center;background:#fff;">
  <div class="container">
    <span class="eyebrow">+ Meet the Team</span>
    <h2 style="font-family:var(--font-heading);font-size:38px;color:var(--navy);margin-bottom:16px;">The People Behind Your Care</h2>
    <p style="font-family:var(--font-body);font-size:14px;color:#555;max-width:560px;margin:0 auto 32px;line-height:1.85;">From our Managing Director to our frontline carers, every member of the Winserve team is committed to delivering outstanding, compassionate care.</p>
    <a href="<?php echo esc_url(home_url('/our-team')); ?>" class="btn-more">Meet Our Team &rarr;</a>
  </div>
</section>

<!-- CTA Banner -->
<section class="cta-banner">
  <div class="container cta-banner-inner">
    <div>
      <span class="cta-eyebrow">+ Book an Assessment</span>
      <h2>Ready to Start Your Care Journey?</h2>
      <p>Contact us today for a free, no-obligation care needs assessment.</p>
    </div>
    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-cta">Contact Us &rarr;</a>
  </div>
</section>

<?php get_footer(); ?>
