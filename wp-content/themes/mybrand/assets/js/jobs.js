/* Winserve Job Board — jobs.js */
(function () {
    'use strict';

    // ── File upload: show chosen filename ──────────────────────────────────────
    const cvInput = document.getElementById('applicant_cv');
    const fileLabel = document.querySelector('.file-upload-label');
    const fileNameDisplay = document.getElementById('file-name-display');

    if (cvInput && fileLabel && fileNameDisplay) {
        cvInput.addEventListener('change', () => {
            const file = cvInput.files[0];
            if (file) {
                fileNameDisplay.textContent = file.name;
                fileLabel.classList.add('has-file');
                fileLabel.querySelector('.file-upload-text').textContent = 'File selected — click to change';
            } else {
                fileNameDisplay.textContent = '';
                fileLabel.classList.remove('has-file');
            }
        });
    }

    // ── Smooth scroll to apply form when "Apply Now" clicked ──────────────────
    document.querySelectorAll('a[href="#apply-form"]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const target = document.getElementById('apply-form');
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Focus first input after scroll
                setTimeout(() => {
                    const firstInput = target.querySelector('input, textarea');
                    if (firstInput) firstInput.focus();
                }, 500);
            }
        });
    });

    // ── Auto-submit filter form on select change ───────────────────────────────
    document.querySelectorAll('.jobs-filter__select').forEach((select) => {
        select.addEventListener('change', () => {
            select.closest('form').submit();
        });
    });

    // ── Scroll to error notice if present ─────────────────────────────────────
    const errorNotice = document.querySelector('.form-notice--error');
    if (errorNotice) {
        setTimeout(() => {
            errorNotice.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }

    // ── Success notice: scroll to top of form ─────────────────────────────────
    const successNotice = document.querySelector('.form-notice--success');
    if (successNotice) {
        setTimeout(() => {
            successNotice.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }
})();
