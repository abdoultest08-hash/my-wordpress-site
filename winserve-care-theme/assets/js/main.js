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

  // Overlap cards scroll-reveal (staggered fade-up)
  if ('IntersectionObserver' in window) {
    var cardObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('card-visible');
          cardObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    document.querySelectorAll('.overlap-card').forEach(function (card) {
      cardObserver.observe(card);
    });
  } else {
    // Fallback: show immediately
    document.querySelectorAll('.overlap-card').forEach(function (c) {
      c.classList.add('card-visible');
    });
  }

  // Stats counter animation (slot-machine style)
  function animateCounter(el) {
    var html = el.innerHTML;
    // Parse the numeric value from text (ignoring sup tags)
    var clone = el.cloneNode(true);
    clone.querySelectorAll('sup').forEach(function (s) { s.remove(); });
    var text = clone.textContent.trim().replace(/[^\d]/g, '');
    var target = parseInt(text, 10);
    if (isNaN(target) || target === 0) return;

    var suffix = el.querySelector('sup') ? el.querySelector('sup').outerHTML : '';
    var duration = 1600;
    var startTime = null;

    function easeOut(t) { return 1 - Math.pow(1 - t, 3); }

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var current = Math.floor(easeOut(progress) * target);
      el.innerHTML = current + suffix;
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        el.innerHTML = target + suffix;
      }
    }

    requestAnimationFrame(step);
  }

  if ('IntersectionObserver' in window) {
    var statsObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          statsObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stat-num').forEach(function (el) {
      statsObserver.observe(el);
    });
  }

})();
