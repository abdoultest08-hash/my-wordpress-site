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

    <div class="team-card team-card--no-img">
      <div class="team-card-avatar">AM</div>
      <div class="team-card-bar">
        <div class="team-card-name">Amon Mutyasira</div>
        <div class="team-card-role">Managing Director &amp; Founder</div>
      </div>
      <div class="team-card-bio">
        <p>Amon founded Winserve Care Services Ltd 5 years ago with a clear mission: to provide high-quality, compassionate domiciliary care that genuinely improves lives. Under his leadership, the company has grown to serve both Leeds and Cornwall, achieving a CQC rating of Good in June 2025. Amon is passionate about empowering both service users and staff, ensuring a culture of dignity, respect, and continuous improvement.</p>
      </div>
    </div>

    <div class="team-card team-card--no-img">
      <div class="team-card-avatar">AP</div>
      <div class="team-card-bar">
        <div class="team-card-name">Alex Parcei</div>
        <div class="team-card-role">HR &amp; Administration</div>
      </div>
      <div class="team-card-bio">
        <p>Alex oversees all human resources and administrative operations at Winserve, ensuring that every member of staff is properly trained, supported, and compliant with CQC and industry regulations. Alex plays a key role in recruitment, onboarding, and the ongoing development of our care team — helping to ensure that Winserve remains an employer of choice in the care sector.</p>
      </div>
    </div>

    <div class="team-card team-card--no-img">
      <div class="team-card-avatar">
        <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </div>
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
