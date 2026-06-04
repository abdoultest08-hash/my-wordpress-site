<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge">Vacancies</span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; Vacancies</p>
  </div>
</section>

<!-- Intro Section -->
<section style="padding:72px 0 48px;text-align:center;background:#fff;">
  <div class="container">
    <span class="eyebrow">+ Work With Us</span>
    <h2 style="font-family:var(--font-heading);font-size:38px;color:var(--navy);margin-bottom:16px;line-height:1.2;">Join the Winserve Team</h2>
    <p style="font-family:var(--font-body);font-size:14px;color:#555;max-width:620px;margin:0 auto;line-height:1.85;">At Winserve Care Services, we believe that great care starts with a great team. We are a Real Living Wage employer, committed to a supportive, inclusive working culture where every member of staff is valued, trained, and empowered to deliver their best. All roles require a DBS check and the right to work in the UK.</p>
  </div>
</section>

<!-- Recruitment Image Strip -->
<section style="padding:0 0 60px;background:#fff;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/recruit-carer-with-car.png" alt="Now hiring in Leeds — company vehicle provided" style="width:100%;border-radius:10px;box-shadow:0 8px 32px rgba(0,50,120,0.12);">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/recruit-split-panel.png" alt="Join Leeds' best care team" style="width:100%;border-radius:10px;box-shadow:0 8px 32px rgba(0,50,120,0.12);">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/recruit-warmth.png" alt="Make a real difference every day" style="width:100%;border-radius:10px;box-shadow:0 8px 32px rgba(0,50,120,0.12);">
    </div>
  </div>
</section>

<!-- Vacancies Section -->
<section class="vacancies-section">
  <div class="container">

    <?php if (isset($_GET['applied']) && $_GET['applied'] === '1') : ?>
      <div class="alert-success">
        <strong>Thank you for your application.</strong> Our HR team will be in touch within 5 working days.
      </div>
    <?php endif; ?>

    <!-- Vacancy Card: Care Assistant / Support Worker -->
    <div class="vacancy-card">
      <div class="vacancy-card-header">
        <div>
          <div class="vacancy-title">Care Assistant / Support Worker</div>
          <div class="vacancy-meta">
            <span class="vacancy-tag">&#128205; Leeds</span>
            <span class="vacancy-tag">&#128176; &pound;13.10 &ndash; &pound;15.00 per hour</span>
            <span class="vacancy-tag">&#128197; Part-time &amp; Full-time</span>
            <span class="vacancy-tag" style="background:var(--blue);color:#fff;">No Experience Needed</span>
          </div>
        </div>
        <button class="btn-apply-toggle">Apply for This Role &rarr;</button>
      </div>

      <div class="vacancy-body">

        <div class="visa-warning">
          &#9888; <strong>Important:</strong> We are unable to offer visa sponsorship for this role. All applicants must have the right to work in the UK.
        </div>

        <h3>About the Role</h3>
        <p>As a Care Assistant / Support Worker at Winserve, you will provide high-quality, person-centred care and support to adults living in the community across Leeds. You will help people with everyday tasks, support their independence, and be a friendly, reliable presence they can count on every day.</p>
        <p>No two days are the same — and that's what makes this role so rewarding. Whether you're brand new to care or a seasoned professional, you'll be welcomed into a supportive team and given everything you need to do a brilliant job.</p>

        <h3>What You&rsquo;ll Be Doing</h3>
        <ul>
          <li>Providing personal care with dignity, compassion, and respect</li>
          <li>Supporting people with daily living activities and getting out in the community</li>
          <li>Administering medication safely and in line with individual care plans</li>
          <li>Keeping clear, accurate care records after every visit</li>
          <li>Working as part of a team alongside families and healthcare professionals</li>
          <li>Promoting each person's independence, wellbeing, and quality of life</li>
        </ul>

        <h3>What We&rsquo;re Looking For</h3>
        <p>You do not need any previous care experience. We provide full on-the-job training and will support you to achieve your Care Certificate — fully funded by us.</p>
        <p><strong>Essential:</strong></p>
        <ul>
          <li>Right to work in the UK</li>
          <li>Fluent spoken and written English</li>
          <li>A genuine passion for helping and supporting others</li>
          <li>Reliability, compassion, and a positive attitude</li>
          <li>Based in Leeds or within commutable distance</li>
        </ul>
        <p><strong>Bonus (not required):</strong></p>
        <ul>
          <li>Previous experience in care or support work</li>
          <li>Full UK driving licence (fewer than 6 penalty points)</li>
          <li>Care Certificate or health and social care qualification</li>
        </ul>

        <h3>What You&rsquo;ll Get</h3>
        <ul>
          <li>&pound;13.10 &ndash; &pound;15.00 per hour based on experience</li>
          <li>Company car provided &mdash; no fuel costs or vehicle wear on your end</li>
          <li>Paid travel time between every visit</li>
          <li>Blue Light Card &mdash; thousands of discounts on your favourite brands</li>
          <li>Free confidential counselling whenever you need it</li>
          <li>Generous cash bonuses for referring friends who join the team</li>
          <li>Fully funded training from day one &mdash; Care Certificate, NVQs and beyond</li>
          <li>Flexible hours genuinely designed around your life</li>
          <li>Team events, competitions, rewards, and regular get-togethers</li>
          <li>Real opportunities to progress your career as we grow</li>
        </ul>

      </div><!-- .vacancy-body -->

      <!-- Application Form -->
      <div class="apply-form" id="apply-form-ca">
        <h3>Apply for: Care Assistant / Support Worker</h3>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <?php wp_nonce_field('winserve_application', 'app_nonce'); ?>
          <input type="hidden" name="action" value="winserve_application">
          <input type="hidden" name="role" value="Care Assistant / Support Worker">

          <div class="form-row">
            <div class="form-group">
              <label for="ca_first_name">First Name <span style="color:var(--blue);">*</span></label>
              <input type="text" id="ca_first_name" name="first_name" required placeholder="Your first name">
            </div>
            <div class="form-group">
              <label for="ca_last_name">Last Name <span style="color:var(--blue);">*</span></label>
              <input type="text" id="ca_last_name" name="last_name" required placeholder="Your last name">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="ca_email">Email Address <span style="color:var(--blue);">*</span></label>
              <input type="email" id="ca_email" name="email" required placeholder="your@email.com">
            </div>
            <div class="form-group">
              <label for="ca_phone">Phone Number <span style="color:var(--blue);">*</span></label>
              <input type="tel" id="ca_phone" name="phone" required placeholder="07xxx xxxxxx">
            </div>
          </div>

          <div class="form-group">
            <label for="ca_experience">Care experience</label>
            <select id="ca_experience" name="experience">
              <option value="No experience — willing to train">No experience &mdash; willing to train</option>
              <option value="Under 1 year">Under 1 year</option>
              <option value="1–2 years">1&ndash;2 years</option>
              <option value="3–5 years">3&ndash;5 years</option>
              <option value="5+ years">5+ years</option>
            </select>
          </div>

          <div class="form-group">
            <label for="ca_why">Why do you want to work at Winserve? <span style="color:var(--blue);">*</span></label>
            <textarea id="ca_why" name="why_applying" required placeholder="Tell us about yourself and your availability&hellip;"></textarea>
          </div>

          <button type="submit" class="btn-submit">Submit Application &rarr;</button>
          <p style="font-family:var(--font-body);font-size:12px;color:#888;margin-top:12px;">Your application will be sent directly to our HR team at hr@winservecare.co.uk. We aim to respond within 5 working days.</p>
        </form>
      </div>

    </div><!-- .vacancy-card -->

    <!-- Vacancy Card: Service Delivery Coordinator -->
    <div class="vacancy-card">
      <div class="vacancy-card-header">
        <div>
          <div class="vacancy-title">Service Delivery Coordinator</div>
          <div class="vacancy-meta">
            <span class="vacancy-tag">&#128205; Leeds</span>
            <span class="vacancy-tag">&#128176; &pound;31,000 &ndash; &pound;35,000 per year</span>
            <span class="vacancy-tag">&#128197; Full-time, Permanent</span>
          </div>
        </div>
        <button class="btn-apply-toggle">Apply for This Role &rarr;</button>
      </div>

      <div class="vacancy-body">

        <div class="visa-warning">
          &#9888; <strong>Important:</strong> We are unable to offer visa sponsorship for this role. All applicants must have the right to work in the UK.
        </div>

        <h3>About the Role</h3>
        <p>A central operations role managing care delivery across our service — coordinating rotas, supporting quality assurance, and working closely with the management team to ensure every service user receives consistent, high-quality support.</p>

        <h3>What You&rsquo;ll Be Doing</h3>
        <ul>
          <li>Coordinate and manage the scheduling of care visits, ensuring full rota coverage at all times</li>
          <li>Act as first point of contact for carers on scheduling and service delivery queries</li>
          <li>Monitor and respond to last-minute changes, absences, and emergencies</li>
          <li>Liaise with service users, families, and external professionals to ensure care plans are delivered as agreed</li>
          <li>Maintain accurate records on the care management system and carry out compliance checks</li>
          <li>Support tender submissions, invoicing, payroll data, and internal audits</li>
          <li>Contribute to maintaining and building upon the CQC Good rating</li>
        </ul>

        <h3>What We&rsquo;re Looking For</h3>
        <p><strong>Essential:</strong></p>
        <ul>
          <li>Previous experience in care coordination, scheduling, or operations within health and social care</li>
          <li>Strong organisational skills with the ability to prioritise under pressure</li>
          <li>Excellent communication skills &mdash; written and verbal</li>
          <li>Proficiency with care management software or rostering systems</li>
          <li>Right to work in the UK &bull; Enhanced DBS check required</li>
        </ul>
        <p><strong>Desirable:</strong> NVQ Level 3+ in Health and Social Care, CQC inspection experience, knowledge of domiciliary or supported living environments.</p>

        <h3>What You&rsquo;ll Get</h3>
        <ul>
          <li>Salary of &pound;31,000 &ndash; &pound;35,000 per year (dependent on experience)</li>
          <li>28 days annual leave including bank holidays</li>
          <li>Real Living Wage commitment and ongoing professional development</li>
          <li>Wellbeing support and an open, supportive management culture</li>
          <li>Clear progression pathway: Coordinator &rarr; Senior / Team Leader &rarr; Registered Manager</li>
        </ul>

      </div><!-- .vacancy-body -->

      <!-- Application Form -->
      <div class="apply-form" id="apply-form-sdc">
        <h3>Apply for: Service Delivery Coordinator</h3>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <?php wp_nonce_field('winserve_application', 'app_nonce'); ?>
          <input type="hidden" name="action" value="winserve_application">
          <input type="hidden" name="role" value="Service Delivery Coordinator">

          <div class="form-row">
            <div class="form-group">
              <label for="first_name">First Name <span style="color:var(--blue);">*</span></label>
              <input type="text" id="first_name" name="first_name" required placeholder="Your first name">
            </div>
            <div class="form-group">
              <label for="last_name">Last Name <span style="color:var(--blue);">*</span></label>
              <input type="text" id="last_name" name="last_name" required placeholder="Your last name">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="app_email">Email Address <span style="color:var(--blue);">*</span></label>
              <input type="email" id="app_email" name="email" required placeholder="your@email.com">
            </div>
            <div class="form-group">
              <label for="app_phone">Phone Number <span style="color:var(--blue);">*</span></label>
              <input type="tel" id="app_phone" name="phone" required placeholder="07xxx xxxxxx">
            </div>
          </div>

          <div class="form-group">
            <label for="location">Location (City / Town) <span style="color:var(--blue);">*</span></label>
            <input type="text" id="location" name="location" required placeholder="e.g. Leeds">
          </div>

          <div class="form-group">
            <label for="experience">Relevant Experience <span style="color:var(--blue);">*</span></label>
            <textarea id="experience" name="experience" required placeholder="Tell us about your relevant experience in care coordination, scheduling, or operations..."></textarea>
          </div>

          <div class="form-group">
            <label for="why_applying">Why Are You Applying for This Role? <span style="color:var(--blue);">*</span></label>
            <textarea id="why_applying" name="why_applying" required placeholder="Tell us why you want to join Winserve and what motivates you about this role..."></textarea>
          </div>

          <button type="submit" class="btn-submit">Submit Application &rarr;</button>
          <p style="font-family:var(--font-body);font-size:12px;color:#888;margin-top:12px;">Your application will be sent directly to our HR team at hr@winservecare.co.uk. We aim to respond within 5 working days.</p>
        </form>
      </div>

    </div><!-- .vacancy-card -->

  </div><!-- .container -->
</section>

<!-- CTA Banner -->
<section class="cta-banner">
  <div class="container cta-banner-inner">
    <div>
      <span class="cta-eyebrow">+ HR Enquiries</span>
      <h2>Have a Question About a Role?</h2>
      <p>Contact our HR team directly at hr@winservecare.co.uk or call us on 0113 340 8777.</p>
    </div>
    <a href="mailto:hr@winservecare.co.uk" class="btn-cta">Email HR Team &rarr;</a>
  </div>
</section>

<?php get_footer(); ?>
