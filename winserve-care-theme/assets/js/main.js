(function () {
  'use strict';

  // Sticky nav
  var header = document.getElementById('site-header');
  if (header) {
    window.addEventListener('scroll', function () {
      header.classList.toggle('scrolled', window.scrollY > 60);
    }, { passive: true });
  }

  // Mobile menu
  var toggle = document.getElementById('menu-toggle');
  var nav = document.getElementById('primary-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', function (e) {
      if (!toggle.contains(e.target) && !nav.contains(e.target)) {
        nav.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var t = document.querySelector(this.getAttribute('href'));
      if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
    });
  });

  // Vacancy apply toggle
  document.querySelectorAll('.btn-apply-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = this.closest('.vacancy-card').querySelector('.apply-form');
      if (form) {
        form.classList.toggle('open');
        this.textContent = form.classList.contains('open') ? 'Close Form ✕' : 'Apply for This Role →';
        if (form.classList.contains('open')) {
          form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    });
  });

})();
