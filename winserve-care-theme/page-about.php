<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge">About Us</span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; About Us</p>
  </div>
</section>

<!-- SECTION 1: WHO WE ARE (text left) + ENQUIRY FORM (right) -->
<section class="story-section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start;">

      <!-- Left: Who We Are text -->
      <div class="story-content">
        <span class="eyebrow">+ Who We Are</span>
        <h2>A Care Company Built on Purpose, Not Just Process</h2>
        <p>Winserve Care Services Ltd is a CQC-registered domiciliary and supported living care provider operating across Leeds and Cornwall. Founded with a clear mission — to deliver genuinely compassionate, person-centred home care — we have grown over six years into a trusted name in health and social care.</p>
        <p>We are not a franchise. We are an independent, values-led organisation where every decision is guided by one question: <em>what is best for the people we support?</em> That principle drives how we hire, how we train, and how we show up every day for our service users and their families.</p>
        <ul class="story-bullets">
          <li>Founded and led by an experienced care management team</li>
          <li>CQC registered and rated Good across all 5 domains &mdash; June 2025</li>
          <li>50+ dedicated, DBS-checked and trained carers</li>
          <li>Serving Leeds and Cornwall across two regions of England</li>
          <li>Real Living Wage employer &mdash; because we value our team</li>
          <li>Armed Forces Covenant signatory</li>
        </ul>
      </div>

      <!-- Right: Enquiry Form -->
      <div style="background:var(--section-bg);border:1px solid var(--border);border-radius:12px;padding:36px 32px;">
        <span class="eyebrow">+ Get in Touch</span>
        <h3 style="font-family:var(--font-heading);font-size:24px;color:var(--navy);margin:10px 0 6px;">Make a Care Enquiry</h3>
        <p style="font-family:var(--font-body);font-size:13px;color:#666;line-height:1.7;margin-bottom:24px;">Tell us a little about your needs and a member of our team will be in touch within one working day.</p>

        <?php if (isset($_GET['sent']) && $_GET['sent'] === '1'): ?>
          <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-family:var(--font-body);font-size:13px;color:#2e7d32;">
            Thank you — we have received your enquiry and will be in touch shortly.
          </div>
        <?php endif; ?>

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="display:flex;flex-direction:column;gap:14px;">
          <input type="hidden" name="action" value="winserve_contact">
          <?php wp_nonce_field('winserve_contact_nonce', 'winserve_nonce'); ?>
          <input type="hidden" name="form_source" value="about_enquiry">

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
              <label style="font-family:var(--font-body);font-size:12px;color:var(--navy);font-weight:600;display:block;margin-bottom:5px;">Full Name *</label>
              <input type="text" name="name" required placeholder="Your name" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:6px;font-family:var(--font-body);font-size:13px;color:#333;background:#fff;box-sizing:border-box;">
            </div>
            <div>
              <label style="font-family:var(--font-body);font-size:12px;color:var(--navy);font-weight:600;display:block;margin-bottom:5px;">Phone Number *</label>
              <input type="tel" name="phone" required placeholder="Your phone" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:6px;font-family:var(--font-body);font-size:13px;color:#333;background:#fff;box-sizing:border-box;">
            </div>
          </div>

          <div>
            <label style="font-family:var(--font-body);font-size:12px;color:var(--navy);font-weight:600;display:block;margin-bottom:5px;">Email Address *</label>
            <input type="email" name="email" required placeholder="Your email" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:6px;font-family:var(--font-body);font-size:13px;color:#333;background:#fff;box-sizing:border-box;">
          </div>

          <div>
            <label style="font-family:var(--font-body);font-size:12px;color:var(--navy);font-weight:600;display:block;margin-bottom:5px;">Type of Care Needed</label>
            <select name="service" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:6px;font-family:var(--font-body);font-size:13px;color:#333;background:#fff;box-sizing:border-box;">
              <option value="">Please select...</option>
              <option>Domiciliary / Home Care</option>
              <option>Supported Living</option>
              <option>Dementia Care</option>
              <option>Palliative / End of Life Care</option>
              <option>Live-In Care</option>
              <option>Complex Care</option>
              <option>Not sure — need advice</option>
            </select>
          </div>

          <div>
            <label style="font-family:var(--font-body);font-size:12px;color:var(--navy);font-weight:600;display:block;margin-bottom:5px;">Your Message</label>
            <textarea name="message" rows="3" placeholder="Tell us a little about your situation..." style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:6px;font-family:var(--font-body);font-size:13px;color:#333;background:#fff;resize:vertical;box-sizing:border-box;"></textarea>
          </div>

          <button type="submit" style="background:var(--blue);color:#fff;font-family:var(--font-body);font-size:14px;font-weight:600;padding:13px 28px;border:none;border-radius:6px;cursor:pointer;transition:background 0.2s;text-align:center;">Send Enquiry &rarr;</button>
        </form>
      </div>

    </div>
  </div>
</section>

<!-- SECTION 2: STATS -->
<section class="stats-bar">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-col">
        <span class="stat-num">50<sup>+</sup></span>
        <span class="stat-lbl">Dedicated Carers</span>
      </div>
      <div class="stat-col">
        <span class="stat-num">2</span>
        <span class="stat-lbl">Regions Served</span>
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

<!-- SECTION 3: THE STORY BEHIND WINSERVE -->
<section style="padding:80px 0;background:#fff;">
  <div class="container">

    <!-- Marathon: text LEFT, images RIGHT -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;margin-bottom:72px;">
      <div>
        <span class="eyebrow" style="color:var(--blue);">+ The Story Behind Winserve</span>
        <h2 style="font-family:var(--font-heading);font-size:34px;color:var(--navy);margin:12px 0 20px;line-height:1.2;">Why Care Is at the Heart of What We Do</h2>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:16px;">Winserve was not built on a business plan. It was built on a personal promise. Our Managing Director lost both his father and his sister to cancer. He works daily alongside service users in palliative care — people bravely fighting the same disease that took his own family. That experience is not background noise. It is the reason this company exists.</p>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:16px;">In April 2023, he ran the Manchester Marathon — months of early morning training, late nights after work — and raised over <strong>&pound;1,200 for Cancer Research UK</strong>. Not because it was easy. Because it mattered.</p>
        <blockquote style="border-left:3px solid var(--blue);padding-left:18px;margin:24px 0;font-family:var(--font-body);font-size:14px;color:var(--navy);font-style:italic;line-height:1.8;">"We work every day with service users who have cancer. I lost my father and my sister to cancer, and several of our service users in palliative care are fighting cancer. We realise the importance of Cancer Research to reduce people's suffering."</blockquote>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;">This is what we mean when we say <em>care is at the heart of what we do.</em> It is not a slogan — it is the reason this company exists.</p>
      </div>
      <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:12px;">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/marathon-1.png" alt="Managing Director running the Manchester Marathon for Cancer Research UK" style="width:100%;border-radius:10px;object-fit:cover;height:300px;">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/marathon-2.png" alt="Finishing the Manchester Marathon with Cancer Research UK medal" style="width:100%;border-radius:10px;object-fit:cover;height:300px;">
      </div>
    </div>

    <hr style="border:none;border-top:1px solid var(--border);margin-bottom:72px;">

    <!-- Homeless Hampers: image LEFT, text RIGHT -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;">
      <div>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/homeless-clotehs.png" alt="Winserve team with Homeless Hampers donation boxes" style="width:100%;border-radius:10px;object-fit:cover;max-height:380px;">
        <p style="font-family:var(--font-body);font-size:12px;color:#888;margin-top:10px;text-align:center;font-style:italic;">Our team with Tina, lead manager of Homeless Hampers Leeds</p>
      </div>
      <div>
        <span class="eyebrow" style="color:var(--blue);">+ Homeless Hampers, Leeds</span>
        <h2 style="font-family:var(--font-heading);font-size:34px;color:var(--navy);margin:12px 0 20px;line-height:1.2;">Spreading Warmth &amp; Hope in Our Community</h2>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:16px;">Since 2021, Winserve has made annual donations to <strong>Homeless Hampers</strong> — a Leeds-based charity providing warm clothing and essentials to those sleeping rough. We first got involved because of the significant number of homeless veterans in Leeds — a cause deeply aligned with our Armed Forces Covenant commitment.</p>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;margin-bottom:16px;">In 2023, we donated <strong>&pound;500 worth of warm clothing</strong> — scarves, hats, coats, thermals, leggings, underwear, and jumpers. In the coldest months, warm clothing is not a comfort. It is a lifeline, reducing the risk of cold-related illness and pneumonia among the most vulnerable.</p>
        <p style="font-family:var(--font-body);font-size:14px;color:#555;line-height:1.85;">Through our work with local councils, we support many individuals who have experienced homelessness in the past. Giving back to those still facing it is something we are proud to do — every single year.</p>
      </div>
    </div>

  </div>
</section>

<!-- SECTION 5: OUR GOALS & MISSION -->
<section class="values-section">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Our Mission &amp; Goals</span>
      <h2 class="section-title">What We Are Here to Do</h2>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start;margin-top:0;">
      <div>
        <p style="font-family:var(--font-body);font-size:15px;color:#555;line-height:1.85;margin-bottom:20px;">Our mission is simple: to enable every individual we support to live with dignity, independence, and confidence — in the comfort of their own home or community setting, surrounded by people they trust.</p>
        <p style="font-family:var(--font-body);font-size:15px;color:#555;line-height:1.85;margin-bottom:20px;">We believe that great care is not just about completing tasks — it is about building genuine relationships, listening carefully, and treating every person as the unique individual they are.</p>
        <p style="font-family:var(--font-body);font-size:15px;color:#555;line-height:1.85;">Our long-term goals are to grow our presence across the UK, deepen our partnerships with NHS integrated care systems and local authorities, and continue setting the standard for what excellent community-based care looks like.</p>
      </div>
      <div class="values-grid" style="margin-top:0;">
        <div class="value-card">
          <div class="value-icon"><svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></div>
          <h3>Compassion</h3>
          <p>Every interaction starts with empathy. We treat every person as we would want our own family to be treated.</p>
        </div>
        <div class="value-card">
          <div class="value-icon"><svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
          <h3>Integrity</h3>
          <p>Honest, transparent, and accountable in everything we do. Families can trust us completely.</p>
        </div>
        <div class="value-card">
          <div class="value-icon"><svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
          <h3>Excellence</h3>
          <p>We hold ourselves to the highest standards — our CQC Good rating is evidence of that commitment.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 4: WHAT MAKES US DIFFERENT -->
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
        <p>Every care plan is built around the individual — their preferences, routines, goals, and relationships. We don't use templates. We listen, then we build.</p>
      </div>
      <div class="diff-card">
        <div class="diff-num">02</div>
        <h4>Consistent Care Teams</h4>
        <p>We match service users with a small, consistent group of carers — not a rotating roster of strangers. Continuity builds trust, and trust is the foundation of great care.</p>
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
        <p>Families and commissioners can always reach us. We communicate openly, act quickly on feedback, and welcome scrutiny — because we have nothing to hide.</p>
      </div>
      <div class="diff-card">
        <div class="diff-num">06</div>
        <h4>Values-Led Organisation</h4>
        <p>We are a Real Living Wage employer. We invest in our staff because great staff deliver great care. Our team stays with us because we look after them too.</p>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 5: WHO WE WORK WITH -->
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
        <p style="font-family:var(--font-body);font-size:13px;color:#666;line-height:1.8;">We hold contracts with local authority commissioners and work in partnership with integrated care systems across our operating regions. Our compliance framework, QCS-managed policies, and CQC Good rating give commissioners the assurance they need to refer with confidence.</p>
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

<!-- SECTION 6: OUR APPROACH TO COMPLIANCE -->
<section style="padding:80px 0;background:var(--navy);">
  <div class="container">
    <div class="section-header" style="margin-bottom:48px;">
      <span class="section-eyebrow" style="color:var(--light-blue);">+ Compliance &amp; Quality</span>
      <h2 class="section-title" style="color:#fff;">Our Approach to Compliance</h2>
      <p style="font-family:var(--font-body);font-size:14px;color:rgba(255,255,255,0.7);max-width:680px;margin:12px auto 0;line-height:1.85;">Compliance is not a box-ticking exercise for us — it is how we protect the people we care for and maintain the trust of everyone we work with. We have invested in the systems, training, and governance frameworks that make excellent, safe care possible every day.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start;">

      <div>
        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:32px;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">CQC Registration &amp; Good Rating</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">We are fully registered with the Care Quality Commission. Our most recent inspection (June 2025) resulted in a Good rating across all five domains — Safe, Effective, Caring, Responsive, and Well-led. We prepare year-round for CQC scrutiny, not just inspection periods.</p>
          </div>
        </div>

        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:32px;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">QCS — Quality Compliance Systems</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">We use Quality Compliance Systems (QCS) to manage our policies, procedures, and care documentation. QCS provides a comprehensive, regularly updated framework that keeps us aligned with the latest CQC standards, legislation, and best practice guidance across the sector.</p>
          </div>
        </div>

        <div style="display:flex;gap:20px;align-items:flex-start;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">CHAS Health &amp; Safety Accreditation</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">Winserve holds CHAS Standard accreditation — the UK's leading health and safety pre-qualification scheme. This certification demonstrates that our health and safety practices meet a recognised, independently assessed standard, providing assurance to contractors and commissioners alike.</p>
          </div>
        </div>
      </div>

      <div>
        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:32px;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">Click Learning — Staff Training Platform</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">Every member of our care team is enrolled on Click Learning, our dedicated online training platform. From mandatory modules (safeguarding, infection control, manual handling) to specialist care subjects, Click Learning ensures all staff training is recorded, up to date, and meets regulatory requirements.</p>
          </div>
        </div>

        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:32px;">
          <div style="width:48px;min-width:48px;height:48px;background:rgba(255,255,255,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--light-blue);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);font-size:20px;color:#fff;margin-bottom:6px;">Safer Recruitment &amp; DBS Checks</h4>
            <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.7);line-height:1.8;">We follow a rigorous safer recruitment process for every hire — enhanced DBS checks, right-to-work verification, two employment references, and a structured induction. No carer ever attends a visit unsupervised until we are fully confident in their suitability and competence.</p>
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

<!-- SECTION 7: CQC RATING -->
<section class="cqc-section">
  <div class="container cqc-inner">
    <div class="cqc-badge">CQC Rated: Good</div>
    <h2>Independently Verified Quality Care</h2>
    <p>The Care Quality Commission is the independent regulator of health and social care in England. Our Good rating confirms that Winserve Care Services delivers safe, effective, caring, responsive, and well-led care. CQC Provider ID: 1-8945106634.</p>
    <a href="https://www.cqc.org.uk/location/1-8945106634" target="_blank" rel="noopener" class="btn-outline-white">View Our CQC Report &rarr;</a>
  </div>
</section>

<!-- SECTION 8: HOMECARE REVIEWS WIDGET -->
<section class="homecare-widget-strip" style="padding:56px 0;">
  <div class="container">
    <p class="hw-label">What families say — independently verified reviews from Homecare.co.uk</p>
    <div class="homecare-widget-inner">
      <script async class='tg-review-widget' type='text/javascript' src='https://api.homecare.co.uk/assets/js/review_widget.js?displaydiv=tgrw-about-page&displayid=65432238891&displaycontent=snippet&displaywidth=300&displaycount=2&displayscore=true&displaylink=false&displayborder=true&displaybackgroundcolor=faded&displaypagination=false&displaystrapline=true&displayfontsize=default&displayminoverallrating=0&displayallratings=false&displaylogo=true&displaywrappers=true&displaybutton=true&displaysettingname=true&displayratingreview=true&linksnofollow=false'></script>
      <div class='tg-review-widget-container' id='tgrw-about-page'></div>
    </div>
  </div>
</section>


<!-- SECTION 10: ACCREDITATIONS & PARTNER LOGOS -->
<section style="padding:64px 0;background:var(--section-bg);border-top:1px solid var(--border);">
  <div class="container" style="text-align:center;">
    <span class="eyebrow">+ Our Accreditations &amp; Partners</span>
    <h2 style="font-family:var(--font-heading);font-size:32px;color:var(--navy);margin:12px 0 8px;">Certified, Compliant &amp; Trusted</h2>
    <p style="font-family:var(--font-body);font-size:14px;color:#666;max-width:600px;margin:0 auto 40px;line-height:1.8;">Our partnerships and accreditations reflect our commitment to operating to the highest standards of quality, safety, and governance.</p>

    <div class="about-logos-grid">
      <div class="about-logo-item">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-armed-forces.webp" alt="Armed Forces Covenant">
        <span class="about-logo-caption">Armed Forces Covenant</span>
      </div>
      <div class="about-logo-item about-logo-item--sm">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-chas.webp" alt="CHAS Standard">
        <span class="about-logo-caption">CHAS Standard</span>
      </div>
      <div class="about-logo-item">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-qcs.webp" alt="QCS Quality Compliance Systems">
        <span class="about-logo-caption">QCS Compliance</span>
      </div>
      <div class="about-logo-item about-logo-item--sm">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-click-learning.webp" alt="Click Learning">
        <span class="about-logo-caption">Click Learning</span>
      </div>
      <div class="about-logo-item">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-bpr-heaton.webp" alt="BPR Heaton Chartered Accountants">
        <span class="about-logo-caption">BPR Heaton</span>
      </div>
      <div class="about-logo-item">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-homecare.png" alt="Homecare.co.uk">
        <span class="about-logo-caption">Homecare.co.uk</span>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 11: TEAM INTRO + CTA -->
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
