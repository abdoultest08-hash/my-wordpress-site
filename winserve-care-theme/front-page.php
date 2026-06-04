<?php get_header(); ?>

<!-- SECTION 1: HERO -->
<section class="hero-section">
  <div class="hero-overlay"></div>
  <div class="container">
    <div class="hero-inner">
      <span class="hero-badge">
        <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        CQC Registered &bull; Leeds &amp; Cornwall
      </span>
      <h1>Trusted Care, Right in Your Own Home.</h1>
      <p class="hero-sub">Winserve Care Services provides compassionate, person-centred domiciliary and supported living care across Leeds and Cornwall. Care is at the heart of everything we do.</p>
      <div class="hero-actions">
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-hero">Contact Us &rarr;</a>
        <a href="<?php echo esc_url(home_url('/services')); ?>" class="btn-play" aria-label="View our services">
          <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        </a>
      </div>
    </div>
  </div>
  <div class="hero-circle">
    <span style="font-family:var(--font-heading);font-size:28px;font-weight:700;color:#fff;line-height:1;">CQC</span>
    <span>Rated Good</span>
    <span>2025</span>
  </div>
</section>

<!-- SECTION 2: OVERLAP CARDS -->
<div class="container" style="position:relative;">
  <div class="overlap-cards">
    <div class="overlap-card" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-domiciliary.jpg');">
      <div class="overlap-card-overlay"></div>
      <div class="overlap-card-body">
        <div class="overlap-icon">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <h3>Dedicated Team</h3>
        <p>70+ trained carers across Leeds &amp; Cornwall</p>
      </div>
    </div>
    <div class="overlap-card" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-supported-living.jpg');">
      <div class="overlap-card-overlay"></div>
      <div class="overlap-card-body">
        <div class="overlap-icon">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </div>
        <h3>Person-Centred Care</h3>
        <p>Every care plan tailored individually</p>
      </div>
    </div>
    <div class="overlap-card" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/hero-bg.jpg');">
      <div class="overlap-card-overlay"></div>
      <div class="overlap-card-body">
        <div class="overlap-icon">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h3>CQC Registered</h3>
        <p>Rated Good by CQC &bull; June 2025</p>
      </div>
    </div>
  </div>
</div>

<!-- SECTION 3: ABOUT -->
<section class="about-section">
  <div class="container">
    <div class="about-grid">
      <div class="about-images">
        <div class="exp-badge">
          <span class="num">5<sup>+</sup></span>
          <span class="lbl">Years of Care</span>
        </div>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about-main.jpg" alt="Winserve Care team member with service user" class="about-main-img">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about-inset.jpg" alt="Winserve Care home visit" class="about-inset-img">
      </div>
      <div class="about-content">
        <span class="eyebrow">+ Learn About Winserve</span>
        <h2>Compassionate Care from <em>Winserve</em> — Rooted in Leeds, Reaching Cornwall</h2>
        <div class="about-stat-box">
          <span class="snum">50<sup>+</sup></span>
          <p>Dedicated carers delivering high-quality, person-centred care across two regions of England. Every carer is DBS checked, trained, and supported.</p>
        </div>
        <p class="about-body">Founded by Amon Mutyasira, Winserve Care Services Ltd has grown over 5 years into a trusted name in domiciliary and supported living care. We are CQC registered with a Good rating (June 2025) and proud to be a Real Living Wage employer.</p>
        <p class="about-body">Our mission is simple: to enable every individual to live with dignity, independence, and confidence — in the comfort of their own home.</p>
        <a href="<?php echo esc_url(home_url('/about')); ?>" class="btn-more">Learn More About Us &rarr;</a>
        <div class="founder-row">
          <div class="founder-icon">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div>
            <div class="founder-name">Amon Mutyasira</div>
            <div class="founder-role">Managing Director &amp; Founder, Winserve Care Services Ltd</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CQC WIDGET — between About and Stats -->
<div class="cqc-widget-strip">
  <div class="container cqc-widget-inner">
    <p class="cqc-widget-label">Regulated by the Care Quality Commission</p>
    <div id="cqc-widget-home"></div>
    <script type="text/javascript" src="https://www.cqc.org.uk/sites/all/modules/custom/cqc_widget/widget.js?data-id=1-8945106634&data-host=https://www.cqc.org.uk&type=location"></script>
  </div>
</div>

<!-- SECTION 4: STATS BAR -->
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
        <span class="stat-num">5<sup>+</sup></span>
        <span class="stat-lbl">Years Operating</span>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 5: SERVICES PREVIEW -->
<section class="services-section">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ What We Offer</span>
      <h2 class="section-title">Care Services Designed Around <em style="font-style:italic;color:var(--blue);">You</em></h2>
    </div>
    <div class="services-grid">

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-domiciliary.jpg');"></div>
        <div class="service-card-bar">
          <span>Domiciliary Care</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Professional home care visits tailored to each individual's needs, supporting independence and wellbeing in familiar surroundings.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-supported-living.jpg');"></div>
        <div class="service-card-bar">
          <span>Supported Living</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Enabling adults with learning disabilities, mental health needs, or physical disabilities to live independently with the right support.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-domiciliary.jpg');"></div>
        <div class="service-card-bar">
          <span>Complex Care</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Specialist care for those with high-level health needs including acquired brain injuries, spinal conditions, and long-term complex conditions.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-supported-living.jpg');"></div>
        <div class="service-card-bar">
          <span>Dementia &amp; Alzheimer's Care</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Specialist support for those living with dementia and Alzheimer's, helping maintain routine, safety, and dignity at home.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-domiciliary.jpg');"></div>
        <div class="service-card-bar">
          <span>Live-In Care</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">A dedicated carer lives in the home providing round-the-clock support, allowing individuals to remain in familiar surroundings.</div>
      </div>

      <div class="service-card">
        <div class="service-card-img" style="background-image:url('<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-supported-living.jpg');"></div>
        <div class="service-card-bar">
          <span>Palliative Care</span>
          <a href="<?php echo esc_url(home_url('/services')); ?>" class="service-card-plus">+</a>
        </div>
        <div class="service-card-body">Compassionate end-of-life care focused on comfort, dignity, and quality of life for individuals and their families.</div>
      </div>

    </div>
    <div style="text-align:center;margin-top:44px;">
      <a href="<?php echo esc_url(home_url('/services')); ?>" class="btn-more">View All 18 Services &rarr;</a>
    </div>
  </div>
</section>

<!-- SECTION 6: WHY CHOOSE US -->
<section class="why-section">
  <div class="why-image">
    <div class="why-image-overlay">
      <button class="play-btn" aria-label="Find out more">
        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
      </button>
    </div>
  </div>
  <div class="why-content">
    <span class="eyebrow">+ Why Choose Us</span>
    <h2>Care You Can Trust, Every Single Day</h2>
    <p class="why-quote">"Our carers don't just deliver care — they build relationships. We believe that every person we support deserves to be treated with dignity, warmth, and genuine compassion."</p>

    <div class="why-item">
      <div class="why-icon">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      </div>
      <div>
        <h4>Home Visit Care</h4>
        <p>We bring professional care directly to your door, preserving the comfort and familiarity of home life for every service user.</p>
      </div>
    </div>

    <div class="why-item">
      <div class="why-icon">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
      </div>
      <div>
        <h4>Fully Regulated &amp; DBS Checked</h4>
        <p>All our carers are thoroughly vetted, DBS checked, and trained. We are registered with the Care Quality Commission and rated Good.</p>
      </div>
    </div>

    <div class="why-item">
      <div class="why-icon">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </div>
      <div>
        <h4>NHS &amp; Council Partnerships</h4>
        <p>We work collaboratively with NHS teams, local authorities, and social workers to deliver seamless, coordinated care packages.</p>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 7: HOMECARE REVIEWS CAROUSEL -->
<section class="reviews-section">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Our Reviews</span>
      <h2 class="section-title">What Families Say About Winserve Care</h2>
    </div>
    <div class="reviews-rating-bar">
      <span class="reviews-score">9.8</span>
      <div>
        <div class="reviews-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="reviews-source">from 74 reviews on Homecare.co.uk</div>
      </div>
    </div>
  </div>

  <div class="reviews-track-wrap">
    <div class="reviews-track">
      <?php
      $reviews = [
          ['name' => 'Sophie T', 'role' => 'Service User', 'date' => 'January 2026', 'stars' => 5,
           'text' => 'Service has been amazing. All staff members I have interacted with have been absolutely amazing and very professional. I cannot thank the team enough for the professional and empathetic care provided during the hardest time of my life.'],
          ['name' => 'Nicole S', 'role' => 'Daughter of Service User', 'date' => 'October 2025', 'stars' => 5,
           'text' => 'Winserve provided excellent care for my father after he left hospital following a stroke. The carers were approachable, respectful of his independence and privacy, and always professional even when things were difficult. We would recommend them to others.'],
          ['name' => 'Beverley C', 'role' => 'Wife of Service User', 'date' => 'August 2025', 'stars' => 5,
           'text' => 'The care given to my husband I can only describe as warm and truly friendly. My husband has dementia and I was so pleased he had company and was in great hands while I was at work. The team genuinely cared.'],
          ['name' => 'Eleni-Rose B', 'role' => 'Granddaughter of Service User', 'date' => 'June 2025', 'stars' => 5,
           'text' => 'Nice and clear instructions and always receptive to our questions and needs. My grandmother says the care is excellent. I have been extremely impressed and grateful for everything this team does.'],
          ['name' => 'Hannah P', 'role' => 'Carer of Service User', 'date' => 'September 2025', 'stars' => 5,
           'text' => 'Winserve have provided my mum with superb care. I cannot thank them enough for the excellent care and compassion they have shown. The whole team deserve enormous credit.'],
          ['name' => 'Nicolas M', 'role' => 'Service User', 'date' => 'April 2025', 'stars' => 5,
           'text' => 'I am incredibly delighted by the service I receive. All they aim to do is please and I feel truly safe. The consistency and warmth of the care I receive deserves recognition. Thank you to the whole team.'],
          ['name' => 'Eleanor S', 'role' => 'Daughter of Service User', 'date' => 'December 2024', 'stars' => 5,
           'text' => 'They treated my mother and our family with great respect throughout her care. I would highly recommend Winserve Care to anyone looking for compassionate, reliable home care — they truly are amazing.'],
          ['name' => 'M', 'role' => 'Daughter of Service User', 'date' => 'August 2024', 'stars' => 5,
           'text' => 'Winserve were recommended to us and we are so pleased we chose them. Very caring and very professional. The manager checks in regularly and the transition from hospital to home care was handled brilliantly.'],
          ['name' => 'Molly A', 'role' => 'Daughter of Service User', 'date' => 'March 2025', 'stars' => 5,
           'text' => 'Our carers are absolutely brilliant — kind, thorough, punctual and incredibly gentle. The manager and the whole team are wonderful. We feel very well looked after and I cannot recommend them highly enough.'],
          ['name' => 'Brenda D', 'role' => 'Wife of Service User', 'date' => 'November 2024', 'stars' => 5,
           'text' => 'What a caring, consistent and effective team. They started promptly and every carer who came was wonderful. Thank you so much to all the staff at Winserve for the difference you have made to our lives.'],
      ];
      // Duplicate for seamless infinite scroll
      $all_reviews = array_merge($reviews, $reviews);
      foreach ($all_reviews as $r) {
          $stars = str_repeat('&#9733;', $r['stars']);
          echo '<div class="review-card">';
          echo '<div class="review-stars">' . $stars . '</div>';
          echo '<p class="review-text">' . esc_html($r['text']) . '</p>';
          echo '<div class="review-author">' . esc_html($r['name']) . '</div>';
          echo '<div class="review-role">' . esc_html($r['role']) . '</div>';
          echo '<div class="review-date">' . esc_html($r['date']) . '</div>';
          echo '</div>';
      }
      ?>
    </div>
  </div>

  <div class="container">
    <div class="reviews-cta">
      <a href="https://www.homecare.co.uk/homecare/agency.cfm/id/65432238891" target="_blank" rel="noopener" class="btn-outline">Read all 74 reviews on Homecare.co.uk &rarr;</a>
    </div>
  </div>
</section>

<!-- SECTION 8: TESTIMONIALS -->
<section class="testimonials-section">
  <div class="testi-content">
    <span class="eyebrow">+ Featured Testimonial</span>
    <h2>Trusted by Families Across Leeds &amp; Cornwall</h2>
    <blockquote class="testi-quote">"Service has been amazing. All staff members I have interacted with have been absolutely amazing and very professional. I cannot thank the team enough for the professional and empathetic care provided during the hardest time of my life."</blockquote>
    <div class="testi-nav">
      <div class="testi-author-info">
        <div class="tname">Sophie T</div>
        <div class="trole">Service User &mdash; January 2026</div>
      </div>
      <div class="testi-arrows">
        <button class="testi-arrow" aria-label="Previous">&#8592;</button>
        <button class="testi-arrow" aria-label="Next">&#8594;</button>
      </div>
    </div>
  </div>
  <div class="testi-photo"></div>
</section>

<!-- SECTION 9: CTA BANNER -->
<section class="cta-banner">
  <div class="container cta-banner-inner">
    <div>
      <span class="cta-eyebrow">+ Free Assessment</span>
      <h2>Book Your Free, No-Obligation Assessment Today</h2>
      <p>Speak to our care team and find out how we can support you or your loved one to live well at home.</p>
    </div>
    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-cta">Contact Us &rarr;</a>
  </div>
</section>

<?php
// SECTION 10: BLOG PREVIEW (only if posts exist)
$recent = new WP_Query(['posts_per_page' => 3, 'post_status' => 'publish']);
if ($recent->have_posts()) :
?>
<section class="services-section" style="background:#fff;">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">+ Latest News</span>
      <h2 class="section-title">From the Winserve Blog</h2>
    </div>
    <div class="blog-grid">
      <?php while ($recent->have_posts()) : $recent->the_post(); ?>
        <article class="blog-card">
          <?php if (has_post_thumbnail()) : ?>
            <img src="<?php the_post_thumbnail_url('winserve-card'); ?>" alt="<?php the_title_attribute(); ?>" class="blog-card-img">
          <?php else : ?>
            <div class="blog-card-img-ph"></div>
          <?php endif; ?>
          <div class="blog-card-body">
            <div class="blog-meta"><?php echo get_the_date(); ?></div>
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p class="blog-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
            <a href="<?php the_permalink(); ?>" class="read-more">Read More &rarr;</a>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a href="<?php echo esc_url(home_url('/blog')); ?>" class="btn-more">View All Posts &rarr;</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
