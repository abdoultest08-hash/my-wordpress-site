/* MyBrand theme — main.js */
(function () {
    'use strict';

    // ── Sticky header ──────────────────────────────────────────────────────────
    const header = document.getElementById('masthead');
    if (header) {
        const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 40);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // ── Mobile nav toggle ──────────────────────────────────────────────────────
    const navToggle = document.querySelector('.nav-toggle');
    const primaryMenu = document.getElementById('primary-menu');
    if (navToggle && primaryMenu) {
        navToggle.addEventListener('click', () => {
            const isOpen = primaryMenu.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', String(isOpen));
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!navToggle.contains(e.target) && !primaryMenu.contains(e.target)) {
                primaryMenu.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && primaryMenu.classList.contains('is-open')) {
                primaryMenu.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.focus();
            }
        });
    }

    // ── Portfolio filter (basic CSS-class toggle; no external library needed) ──
    const filterBtns = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    if (filterBtns.length && portfolioItems.length) {
        filterBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                filterBtns.forEach((b) => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;
                portfolioItems.forEach((item) => {
                    const show = filter === '*' || item.classList.contains(filter.replace('.', ''));
                    item.style.display = show ? '' : 'none';
                });
            });
        });
    }

    // ── Animate-on-scroll (IntersectionObserver, no library) ──────────────────
    const revealEls = document.querySelectorAll('.service-card, .portfolio-item, .stat-item, .post-card');
    if ('IntersectionObserver' in window && revealEls.length) {
        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12 }
        );
        revealEls.forEach((el) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            io.observe(el);
        });
        document.addEventListener('animationend', () => {}, { once: true });

        // Apply visible state
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.is-visible').forEach((el) => {
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
        });

        // Polyfill: make .is-visible actually show
        const styleTag = document.createElement('style');
        styleTag.textContent = '.is-visible{opacity:1!important;transform:none!important}';
        document.head.appendChild(styleTag);
    }

    // ── Smooth scroll for anchor links ────────────────────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (e) => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
})();
