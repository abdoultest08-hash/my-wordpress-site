<?php get_header(); ?>

<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge"><?php the_title(); ?></span>
    <p class="page-breadcrumb">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo;
      <a href="<?php echo esc_url(home_url('/blog')); ?>">Blog</a> &rsaquo;
      <?php the_title(); ?>
    </p>
  </div>
</section>

<section style="padding:72px 0;background:#fff;">
  <div class="container">
    <div class="single-post-grid">

      <!-- Post Content -->
      <article>
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

          <div style="margin-bottom:28px;">
            <div style="font-family:var(--font-body);font-size:12px;color:#999;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">
              <?php echo get_the_date('j F Y'); ?>
              <?php $cats = get_the_category(); if ($cats) echo ' &bull; ' . esc_html($cats[0]->name); ?>
            </div>
            <h1 style="font-family:var(--font-heading);font-size:42px;color:var(--navy);line-height:1.2;"><?php the_title(); ?></h1>
          </div>

          <?php if (has_post_thumbnail()) : ?>
            <img src="<?php the_post_thumbnail_url('winserve-hero'); ?>" alt="<?php the_title_attribute(); ?>" style="width:100%;border-radius:8px;margin-bottom:36px;">
          <?php endif; ?>

          <div class="post-content">
            <?php the_content(); ?>
          </div>

          <div style="margin-top:48px;padding-top:28px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <a href="<?php echo esc_url(home_url('/blog')); ?>" class="btn-more">&larr; Back to Blog</a>
          </div>

        <?php endwhile; endif; ?>
      </article>

      <!-- Sidebar -->
      <aside>
        <div style="background:var(--navy);border-radius:10px;padding:32px;margin-bottom:32px;text-align:center;">
          <h3 style="font-family:var(--font-heading);font-size:26px;color:#fff;margin-bottom:12px;">Need Care Support?</h3>
          <p style="font-family:var(--font-body);font-size:13px;color:rgba(255,255,255,0.75);line-height:1.7;margin-bottom:20px;">Speak to our care team for a free, no-obligation assessment.</p>
          <a href="<?php echo esc_url(home_url('/contact')); ?>" style="display:inline-block;background:var(--blue);color:#fff;padding:12px 24px;border-radius:24px;font-family:var(--font-body);font-size:13px;font-weight:500;">Contact Us &rarr;</a>
        </div>
        <div style="background:var(--section-bg);border:1px solid var(--border);border-radius:10px;padding:28px;">
          <h4 style="font-family:var(--font-heading);font-size:22px;color:var(--navy);margin-bottom:16px;">Recent Articles</h4>
          <?php
          $recent = get_posts(['numberposts' => 5, 'post_status' => 'publish', 'exclude' => [get_the_ID()]]);
          foreach ($recent as $rp) :
            $permalink = get_permalink($rp->ID);
            $date = get_the_date('j M Y', $rp->ID);
          ?>
            <div style="padding:12px 0;border-bottom:1px solid var(--border);">
              <a href="<?php echo esc_url($permalink); ?>" style="font-family:var(--font-body);font-size:13px;color:var(--navy);font-weight:500;line-height:1.5;display:block;"><?php echo esc_html($rp->post_title); ?></a>
              <span style="font-family:var(--font-body);font-size:11px;color:#aaa;"><?php echo esc_html($date); ?></span>
            </div>
          <?php endforeach; wp_reset_postdata(); ?>
        </div>
      </aside>

    </div>
  </div>
</section>

<style>
.single-post-grid { display:grid; grid-template-columns:1fr 320px; gap:60px; align-items:start; }
.post-content { font-family:var(--font-body);font-size:15px;color:#444;line-height:1.9; }
.post-content h2 { font-family:var(--font-heading);font-size:30px;color:var(--navy);margin:36px 0 14px; }
.post-content h3 { font-family:var(--font-heading);font-size:24px;color:var(--navy);margin:28px 0 10px; }
.post-content p { margin-bottom:18px; }
.post-content ul, .post-content ol { padding-left:24px;margin-bottom:18px; }
.post-content li { margin-bottom:6px; }
.post-content a { color:var(--blue);text-decoration:underline; }
.post-content blockquote { border-left:3px solid var(--blue);padding:12px 20px;background:var(--section-bg);margin:24px 0;font-style:italic;color:#666; }
@media(max-width:960px){ .single-post-grid { grid-template-columns:1fr; } .single-post-grid aside { display:none; } }
</style>

<section class="cta-banner">
  <div class="container cta-banner-inner">
    <div>
      <span class="cta-eyebrow">+ Free Assessment</span>
      <h2>Interested in Care for You or a Loved One?</h2>
      <p>Our team is here to help. Contact us for a free, no-obligation conversation.</p>
    </div>
    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn-cta">Get in Touch &rarr;</a>
  </div>
</section>

<?php get_footer(); ?>
