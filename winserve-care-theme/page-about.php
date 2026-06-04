<?php get_header(); ?>

<section class="page-hero">
  <div class="container">
    <h1>About Us</h1>
    <p class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; <span>About Us</span></p>
  </div>
</section>

<section class="story-section">
  <div class="container">
    <div class="story-grid">
      <div>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about-main.jpg" alt="Winserve care team" class="story-img">
      </div>
      <div class="story-content">
        <span class="section-eyebrow">+ Our Story</span>
        <h2>A Decade of Compassionate Care in Leeds &amp; Cornwall</h2>
        <p>Winserve Care Services Ltd was founded over 10 years ago by Amon Mutyasira with a clear vision: to provide outstanding, person-centred care that enables individuals to live independently in their own homes.</p>
        <p>Starting in Leeds, we quickly built a reputation for reliability, compassion, and excellence. Our services have since expanded to Cornwall, where we now support a growing community of clients with domiciliary and supported living care.</p>
        <p>We are registered with and regulated by the Care Quality Commission (CQC), which rated us <strong>Good</strong> on 30 June 2025. This reflects our commitment to safe, effective, caring, responsive, and well-led services.</p>
        <p>Today, over 70 dedicated carers work across both regions, supported by a strong management team and guided by our core values of compassion, integrity, and excellence.</p>
      </div>
    </div>
  </div>
</section>

<section class="values-section">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Our Values</span>
      <h2>What Guides Everything We Do</h2>
    </div>
    <div class="values-grid">
      <div class="value-card">
        <div class="value-icon">
          <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </div>
        <h3>Compassion</h3>
        <p>We treat every person we support with genuine kindness and empathy, recognising their unique needs and aspirations.</p>
      </div>
      <div class="value-card">
        <div class="value-icon">
          <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h3>Integrity</h3>
        <p>We act with honesty and transparency in everything we do, building trust with clients, families, and partners.</p>
      </div>
      <div class="value-card">
        <div class="value-icon">
          <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        </div>
        <h3>Excellence</h3>
        <p>We continuously strive to improve our services, investing in training and development to deliver the best possible outcomes.</p>
      </div>
    </div>
  </div>
</section>

<section class="cqc-section">
  <div class="container">
    <div class="cqc-inner">
      <div class="cqc-badge">CQC Rated: Good</div>
      <h2>Independently Inspected &amp; Rated Good</h2>
      <p>Winserve Care Services Ltd was inspected by the Care Quality Commission and awarded a <strong>Good</strong> rating on 30 June 2025, across all five key areas: Safe, Effective, Caring, Responsive, and Well-led.</p>
      <a href="#" class="btn-outline-white">View Our CQC Report &rarr;</a>
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
