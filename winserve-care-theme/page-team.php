<?php get_header(); ?>

<section class="page-hero">
  <div class="container">
    <h1>Our Team</h1>
    <p class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; <span>Our Team</span></p>
  </div>
</section>

<section class="team-intro">
  <div class="container">
    <span class="section-eyebrow">+ Meet the Team</span>
    <h2 style="font-family:var(--font-heading);font-size:36px;color:var(--navy);">The People Behind Our Care</h2>
    <p>Our team of over 70 dedicated professionals brings a wealth of experience, compassion, and commitment to every client we support. From our leadership team to our frontline carers, every person plays a vital role in delivering outstanding care across Leeds and Cornwall.</p>
  </div>
</section>

<div class="container">
  <div class="team-grid">
    <div class="team-card">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-placeholder.jpg" alt="Amon Mutyasira" class="team-card-img">
      <div class="team-card-bar">
        <div class="team-card-name">Amon Mutyasira</div>
        <div class="team-card-role">Managing Director &amp; Founder</div>
      </div>
      <div class="team-card-bio">Amon founded Winserve Care Services Ltd over 10 years ago with a vision to provide compassionate, high-quality care. His leadership has guided the company to CQC Good status and expansion across two regions.</div>
    </div>
    <div class="team-card">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-placeholder.jpg" alt="Alex Parcei" class="team-card-img">
      <div class="team-card-bar">
        <div class="team-card-name">Alex Parcei</div>
        <div class="team-card-role">HR &amp; Administration</div>
      </div>
      <div class="team-card-bio">Alex oversees all HR and administrative functions, ensuring our team is well-supported, trained, and compliant with all regulatory requirements. Her organisational skills are the backbone of our operations.</div>
    </div>
    <div class="team-card">
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/team-placeholder.jpg" alt="Care Team" class="team-card-img">
      <div class="team-card-bar">
        <div class="team-card-name">Our Care Team</div>
        <div class="team-card-role">70+ Dedicated Carers</div>
      </div>
      <div class="team-card-bio">Our frontline care team are the heart of Winserve. All are DBS checked, professionally trained, and passionate about delivering the highest standards of person-centred care across Leeds and Cornwall.</div>
    </div>
  </div>
</div>

<section class="cta-banner">
  <div class="container">
    <div class="cta-inner">
      <div class="cta-text">
        <p class="cta-eyebrow">+ Join Our Team</p>
        <h2>Want to Make a Difference? We&rsquo;re Hiring</h2>
        <p class="cta-sub">We&rsquo;re always looking for compassionate, skilled carers to join our growing team in Leeds and Cornwall.</p>
      </div>
      <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-cta">Apply Now &rarr;</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
