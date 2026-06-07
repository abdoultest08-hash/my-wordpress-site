<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="topbar">
  <div class="topbar-inner container">
    <div class="topbar-left">
      <a href="tel:01133408777" class="topbar-link">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        0113 340 8777
      </a>
      <a href="mailto:info@winservecare.co.uk" class="topbar-link">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        info@winservecare.co.uk
      </a>
    </div>
    <div class="topbar-right">
      <a href="https://www.facebook.com/WinserveCareUK/" target="_blank" rel="noopener" aria-label="Facebook" class="topbar-social">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
      </a>
      <a href="https://api.whatsapp.com/send?phone=447514113988" target="_blank" rel="noopener" aria-label="WhatsApp" class="topbar-social">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.528 5.855L.057 23.7a.5.5 0 00.612.612l5.846-1.471A11.932 11.932 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.9a9.868 9.868 0 01-5.031-1.378l-.36-.214-3.733.938.955-3.617-.234-.372A9.865 9.865 0 012.1 12C2.1 6.534 6.534 2.1 12 2.1c5.466 0 9.9 4.434 9.9 9.9 0 5.466-4.434 9.9-9.9 9.9z"/></svg>
      </a>
      <a href="https://uk.linkedin.com/company/winserve-care-services-ltd" target="_blank" rel="noopener" aria-label="LinkedIn" class="topbar-social">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
      </a>
    </div>
  </div>
</div>

<header class="site-header" id="site-header">
  <div class="container">
    <nav class="nav-inner" role="navigation" aria-label="Primary">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" aria-label="Winserve Care Services Home">
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo-white-stacked.png" alt="Winserve Care Services Ltd" height="70" style="width:auto;max-width:120px;">
      </a>
      <button class="menu-toggle" id="menu-toggle" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <div class="primary-nav" id="primary-nav">
        <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
        <a href="<?php echo esc_url(home_url('/about')); ?>">About Us</a>
        <a href="<?php echo esc_url(home_url('/services')); ?>">Services</a>
        <a href="<?php echo esc_url(home_url('/our-team')); ?>">Our Team</a>
        <a href="<?php echo esc_url(home_url('/vacancies')); ?>">Vacancies</a>
        <a href="https://portal.winservecare.co.uk/" target="_blank" rel="noopener">Staff Portal</a>
        <a href="<?php echo esc_url(home_url('/blog')); ?>">Blog</a>
        <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact Us</a>
      </div>
    </nav>
  </div>
</header>
