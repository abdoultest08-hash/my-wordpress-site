<?php get_header(); ?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <span class="page-hero-badge">Staff Portal</span>
    <p class="page-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &rsaquo; Staff Portal</p>
  </div>
</section>

<!-- Staff Portal Section -->
<section class="staff-portal-section">
  <div class="container">

    <div class="portal-icon-wrap">
      <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
    </div>

    <h2>Winserve Staff Portal</h2>
    <p>This area is for Winserve staff only. Access your rota, documents, and training resources through our secure staff portal. You will need your Winserve login credentials to proceed.</p>

    <a href="https://portal.winservecare.co.uk/" target="_blank" rel="noopener" class="btn-cta" style="font-size:15px;padding:16px 40px;">
      Access Staff Portal &rarr;
    </a>

    <p style="margin-top:32px;font-size:13px;color:#888;font-family:var(--font-body);">
      If you are having trouble accessing the portal, please contact your line manager or email <a href="mailto:info@winservecare.co.uk" style="color:var(--blue);">info@winservecare.co.uk</a>.
    </p>

    <div style="margin-top:48px;background:var(--section-bg);border:1px solid var(--border);border-radius:8px;padding:32px;max-width:560px;margin-left:auto;margin-right:auto;text-align:left;">
      <h3 style="font-family:var(--font-heading);font-size:24px;color:var(--navy);margin-bottom:12px;">What You Can Access</h3>
      <ul style="list-style:none;padding:0;">
        <li style="font-family:var(--font-body);font-size:14px;color:#555;padding:8px 0 8px 20px;position:relative;border-bottom:1px solid var(--border);">
          <span style="position:absolute;left:0;top:16px;width:8px;height:8px;background:var(--blue);border-radius:50%;display:block;"></span>
          Your weekly rota and visit schedule
        </li>
        <li style="font-family:var(--font-body);font-size:14px;color:#555;padding:8px 0 8px 20px;position:relative;border-bottom:1px solid var(--border);">
          <span style="position:absolute;left:0;top:16px;width:8px;height:8px;background:var(--blue);border-radius:50%;display:block;"></span>
          Care plans and service user documentation
        </li>
        <li style="font-family:var(--font-body);font-size:14px;color:#555;padding:8px 0 8px 20px;position:relative;border-bottom:1px solid var(--border);">
          <span style="position:absolute;left:0;top:16px;width:8px;height:8px;background:var(--blue);border-radius:50%;display:block;"></span>
          Training resources and mandatory e-learning
        </li>
        <li style="font-family:var(--font-body);font-size:14px;color:#555;padding:8px 0 8px 20px;position:relative;border-bottom:1px solid var(--border);">
          <span style="position:absolute;left:0;top:16px;width:8px;height:8px;background:var(--blue);border-radius:50%;display:block;"></span>
          Payslips and HR documentation
        </li>
        <li style="font-family:var(--font-body);font-size:14px;color:#555;padding:8px 0 8px 20px;position:relative;">
          <span style="position:absolute;left:0;top:16px;width:8px;height:8px;background:var(--blue);border-radius:50%;display:block;"></span>
          Incident reporting and compliance forms
        </li>
      </ul>
    </div>

  </div>
</section>

<?php get_footer(); ?>
