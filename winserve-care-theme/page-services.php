<?php get_header(); ?>

<section class="page-hero">
  <div class="container">
    <h1>Our Services</h1>
    <p class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; <span>Our Services</span></p>
  </div>
</section>

<section class="service-detail" id="domiciliary">
  <div class="container">
    <div class="service-detail-grid">
      <div>
        <span class="section-eyebrow">+ Home Care</span>
        <h2>Domiciliary Care</h2>
        <p><strong>Who is this for?</strong> Domiciliary care is suitable for older adults and individuals with disabilities or long-term conditions who need support with daily activities while remaining in their own home.</p>
        <h3>What is included?</h3>
        <ul class="service-list">
          <li>Personal care: washing, dressing, grooming</li>
          <li>Medication administration and prompting</li>
          <li>Meal preparation and nutrition support</li>
          <li>Domestic tasks: housekeeping, laundry</li>
          <li>Companionship and social activities</li>
          <li>Shopping and errands</li>
          <li>Escort to appointments</li>
        </ul>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-link">Get in touch about Domiciliary Care &rarr;</a>
      </div>
      <div>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-domiciliary.jpg" alt="Domiciliary care" class="service-detail-img">
      </div>
    </div>
  </div>
</section>

<section class="service-detail" id="supported-living" style="background:var(--section-bg)">
  <div class="container">
    <div class="service-detail-grid reverse">
      <div>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-supported-living.jpg" alt="Supported living" class="service-detail-img">
      </div>
      <div>
        <span class="section-eyebrow">+ Independent Living</span>
        <h2>Supported Living</h2>
        <p><strong>Who is this for?</strong> Our supported living services are designed for adults with learning disabilities, autism, mental health needs, or physical disabilities who want to live independently in their own home or a shared housing setting.</p>
        <h3>What is included?</h3>
        <ul class="service-list">
          <li>Support with daily living skills</li>
          <li>Community access and social inclusion</li>
          <li>Budgeting and financial management</li>
          <li>Health and wellbeing support</li>
          <li>Employment and education support</li>
          <li>24/7 support available where needed</li>
        </ul>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-link">Get in touch about Supported Living &rarr;</a>
      </div>
    </div>
  </div>
</section>

<section class="service-detail" id="complex-care">
  <div class="container">
    <div class="service-detail-grid">
      <div>
        <span class="section-eyebrow">+ Specialist Care</span>
        <h2>Complex Care</h2>
        <p><strong>Who is this for?</strong> Complex care is provided to individuals with high-level or specialist health needs, including those with acquired brain injuries, spinal cord injuries, degenerative neurological conditions, or who require clinical-level support in the home.</p>
        <h3>What is included?</h3>
        <ul class="service-list">
          <li>Clinical and nursing-led care planning</li>
          <li>PEG feeding and enteral nutrition</li>
          <li>Tracheostomy and ventilator management</li>
          <li>Catheter and stoma care</li>
          <li>Pressure area management</li>
          <li>Specialist moving and handling</li>
          <li>Liaison with NHS and specialist teams</li>
        </ul>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-link">Get in touch about Complex Care &rarr;</a>
      </div>
      <div>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-complex-care.jpg" alt="Complex care" class="service-detail-img">
      </div>
    </div>
  </div>
</section>

<section class="cta-banner">
  <div class="container">
    <div class="cta-inner">
      <div class="cta-text">
        <p class="cta-eyebrow">+ Get In Touch</p>
        <h2>Book Your Free, No-Obligation Assessment Today</h2>
        <p class="cta-sub">Speak to one of our care specialists and find the right support for you or your loved one.</p>
      </div>
      <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-cta">Contact Us &rarr;</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
