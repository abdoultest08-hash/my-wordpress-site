<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge">Our Team</span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; Our Team</p>
  </div>
</section>

<!-- Team Intro -->
<section class="team-intro-section">
  <div class="container">
    <span class="eyebrow">+ The People Behind Your Care</span>
    <h2 style="font-family:var(--font-heading);font-size:38px;color:var(--navy);margin-bottom:16px;line-height:1.2;">Dedicated, Trained &amp; Compassionate</h2>
    <p style="font-family:var(--font-body);font-size:14px;color:#555;max-width:600px;margin:0 auto;line-height:1.85;">Every member of the Winserve team — from our leadership to our frontline carers — is united by a shared commitment to delivering outstanding, person-centred care with dignity and warmth.</p>
  </div>
</section>

<!-- Team Grid -->
<div class="container">
  <div class="team-grid">

    <div class="team-card">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-amon.jpg" alt="Amon Mutyasira — Managing Director" class="team-card-img">
      <div class="team-card-bar">
        <div class="team-card-name">Amon Mutyasira</div>
        <div class="team-card-role">Managing Director &amp; Founder</div>
      </div>
      <div class="team-card-bio">
        <p>Amon founded Winserve Care Services Ltd over 10 years ago with a clear mission: to provide high-quality, compassionate domiciliary care that genuinely improves lives. Under his leadership, the company has grown to serve both Leeds and Cornwall, achieving a CQC rating of Good in June 2025. Amon is passionate about empowering both service users and staff, ensuring a culture of dignity, respect, and continuous improvement.</p>
      </div>
    </div>

    <div class="team-card">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-hr.jpg" alt="Alex Parcei — HR &amp; Administration" class="team-card-img">
      <div class="team-card-bar">
        <div class="team-card-name">Alex Parcei</div>
        <div class="team-card-role">HR &amp; Administration</div>
      </div>
      <div class="team-card-bio">
        <p>Alex oversees all human resources and administrative operations at Winserve, ensuring that every member of staff is properly trained, supported, and compliant with CQC and industry regulations. Alex plays a key role in recruitment, onboarding, and the ongoing development of our care team — helping to ensure that Winserve remains an employer of choice in the care sector.</p>
      </div>
    </div>

    <div class="team-card">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-carers.jpg" alt="Winserve Care Team" class="team-card-img">
      <div class="team-card-bar">
        <div class="team-card-name">The Care Team</div>
        <div class="team-card-role">50+ Dedicated Carers</div>
      </div>
      <div class="team-card-bio">
        <p>Our frontline team of 50+ carers are the heart of Winserve. Each carer is thoroughly DBS checked, trained to a high standard, and supported with ongoing professional development. They bring warmth, professionalism, and genuine compassion to every visit — delivering person-centred care that families across Leeds and Cornwall rely on every day.</p>
      </div>
    </div>

  </div>
</div>

<!-- CTA Banner (careers focused) -->
<section class="cta-banner">
  <div class="container cta-banner-inner">
    <div>
      <span class="cta-eyebrow">+ Join Our Team</span>
      <h2>Passionate About Care? We&rsquo;d Love to Hear From You</h2>
      <p>We are always looking for compassionate, dedicated individuals to join the Winserve family. View our current vacancies and apply today.</p>
    </div>
    <a href="<?php echo esc_url(home_url('/vacancies')); ?>" class="btn-cta">View Vacancies &rarr;</a>
  </div>
</section>

<?php get_footer(); ?>
