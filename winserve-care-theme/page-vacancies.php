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

<!-- Vacancies Section -->
<section class="vacancies-section">
  <div class="container">

    <?php if (isset($_GET['applied']) && $_GET['applied'] === '1') : ?>
      <div class="alert-success">
        <strong>Thank you for your application.</strong> Our HR team will be in touch within 5 working days.
      </div>
    <?php endif; ?>

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
        <p>This is an exciting opportunity to join Winserve Care Services Ltd as a Service Delivery Coordinator. You will play a central role in the operational management of our care delivery, ensuring that our service users receive consistent, high-quality support while our carers are well-scheduled and supported.</p>
        <p>You will work closely with the management team to coordinate rotas, manage service delivery logistics, support quality assurance processes, and contribute to tendering and finance activities. This is a varied, fast-paced role that requires excellent organisational skills, a calm approach under pressure, and a genuine passion for the care sector.</p>

        <h3>What You&rsquo;ll Be Doing</h3>

        <p><strong>Operations &amp; Rota Management</strong></p>
        <ul>
          <li>Coordinate and manage the scheduling of care visits across the service, ensuring full rota coverage at all times</li>
          <li>Act as the first point of contact for carer queries relating to scheduling and service delivery</li>
          <li>Monitor and respond to last-minute changes, absences, and emergencies to maintain continuity of care</li>
          <li>Liaise with service users, their families, and external professionals to ensure care plans are delivered as agreed</li>
          <li>Support the onboarding and induction of new carers, including matching them to appropriate service users</li>
          <li>Maintain accurate and up-to-date records on the care management system</li>
          <li>Carry out regular compliance checks to ensure all care visits are documented correctly</li>
        </ul>

        <p><strong>Tendering, Finance &amp; Quality</strong></p>
        <ul>
          <li>Support the preparation of tender submissions and quality assurance documentation</li>
          <li>Assist with invoicing, payroll data, and financial reporting as required</li>
          <li>Contribute to internal audits and CQC compliance activities</li>
          <li>Identify opportunities for service improvement and raise these with the management team</li>
          <li>Support the organisation in maintaining and building upon its CQC Good rating</li>
        </ul>

        <h3>What We&rsquo;re Looking For</h3>

        <p><strong>Essential</strong></p>
        <ul>
          <li>Previous experience in a care coordination, scheduling, or operations role within the health and social care sector</li>
          <li>Strong organisational and time management skills with the ability to prioritise effectively</li>
          <li>Excellent communication skills — written and verbal — with the ability to build positive relationships with carers, service users, and professionals</li>
          <li>Proficiency with care management software or rostering systems</li>
          <li>A positive, solution-focused attitude and ability to remain calm under pressure</li>
          <li>Right to work in the UK (no visa sponsorship available)</li>
          <li>Enhanced DBS check (can be obtained on appointment)</li>
        </ul>

        <p><strong>Desirable</strong></p>
        <ul>
          <li>NVQ Level 3 or above in Health and Social Care or equivalent</li>
          <li>Experience supporting CQC inspection preparation</li>
          <li>Knowledge of domiciliary or supported living care environments</li>
          <li>Experience with tendering processes in the care sector</li>
          <li>Familiarity with finance or payroll processes in a care setting</li>
        </ul>

        <h3>Training &amp; Development</h3>
        <p><strong>What We&rsquo;ll Train You In</strong></p>
        <ul>
          <li>Winserve care management systems and internal processes</li>
          <li>CQC compliance and quality assurance frameworks</li>
          <li>Safeguarding adults at risk</li>
          <li>Mental Capacity Act and Deprivation of Liberty Safeguards (DoLS)</li>
          <li>Medication management awareness</li>
          <li>Leadership and management development (progression pathway)</li>
        </ul>

        <h3>What You&rsquo;ll Get</h3>
        <ul>
          <li>Salary of &pound;31,000 &ndash; &pound;35,000 per year (dependent on experience)</li>
          <li>Real Living Wage commitment — we are a Living Wage employer</li>
          <li>28 days annual leave (including bank holidays)</li>
          <li>Ongoing professional development and training</li>
          <li>Wellbeing support and an open, supportive management culture</li>
          <li>Opportunity to grow within a purpose-driven, expanding care organisation</li>
          <li>Enhanced DBS check funded by the company</li>
        </ul>

        <h3>Career Pathway</h3>
        <ul>
          <li><strong>Step 1:</strong> Service Delivery Coordinator &mdash; Lead on rota management, operations, and quality</li>
          <li><strong>Step 2:</strong> Senior Coordinator / Team Leader &mdash; Oversee a team of coordinators and lead service improvement projects</li>
          <li><strong>Step 3:</strong> Registered Manager / Operations Manager &mdash; Take full responsibility for service delivery and CQC registration</li>
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
      </div><!-- .apply-form -->

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
