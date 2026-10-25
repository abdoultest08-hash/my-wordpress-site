/* HCC-Temps theme behaviour. Each block checks its elements exist, so one file serves every page. */
(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function $(id) { return document.getElementById(id); }

  /* Mobile menu */
  var toggle = $('menuToggle'), mnav = $('mobileNav');
  if (toggle && mnav) {
    toggle.addEventListener('click', function () {
      var open = mnav.hidden;
      mnav.hidden = !open;
      toggle.setAttribute('aria-expanded', String(open));
    });
  }

  /* Log in dropdown: close when clicking elsewhere */
  document.addEventListener('click', function (e) {
    document.querySelectorAll('.nav-login[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });

  /* Hero: framed nurse photo grows to full bleed as the page scrolls; the chef card sits under its left edge */
  var track = $('heroTrack');
  if (track) {
    var stage = $('heroStage'), photo = $('heroPhoto'), frame = $('heroFrame'), shade = $('heroShade'), chef = $('heroChef'), nurseTag = $('nurseTag');
    var ease = function (t) { return t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; };
    var clamp = function (v) { return Math.max(0, Math.min(1, v)); };
    var heroFrame = function () {
      var W = stage.clientWidth, H = stage.clientHeight;
      var range = track.offsetHeight - H;
      var p = reduce || range <= 0 ? 0 : clamp(-track.getBoundingClientRect().top / range);
      var g = ease(clamp(p / 0.8)), narrow = W < 640;
      var w0 = Math.min(narrow ? W * 0.84 : 600, W - 32), h0 = H * (narrow ? 0.34 : 0.42);
      var x0 = (W - w0) / 2, y0 = H - h0 + (narrow ? 0 : H * 0.04);
      var x = x0 * (1 - g), y = y0 * (1 - g), w = w0 + (W - w0) * g, h = h0 + (H - h0) * g;
      photo.style.cssText = 'left:' + x + 'px;top:' + y + 'px;width:' + w + 'px;height:' + h + 'px;border-radius:' + (6 * (1 - g)) + 'px';
      var pad = 14 * (1 - g);
      frame.style.cssText = 'left:' + (x - pad) + 'px;top:' + (y - pad) + 'px;width:' + (w + pad * 2) + 'px;height:' + (h + pad * 2) + 'px;transform:rotate(' + (-2.5 * (1 - g)) + 'deg);opacity:' + (1 - g);
      if (chef) {
        var cw = narrow ? w0 * 0.34 : Math.min(200, w0 * 0.36), ch = cw * 4 / 3;
        var cx = x0 - cw * (narrow ? 0.12 : 0.55), cy = narrow ? H - ch - 14 : Math.min(y0 + h0 * 0.12, H - ch - 64);
        chef.style.cssText = 'left:' + cx + 'px;top:' + (cy + g * 80) + 'px;width:' + cw + 'px;height:' + ch + 'px;opacity:' + (1 - clamp(g * 1.8));
      }
      if (nurseTag) nurseTag.style.opacity = String(1 - clamp(g * 2.2));
      var dark = clamp((p - 0.1) / 0.3);
      shade.style.opacity = String(dark);
      stage.classList.toggle('is-dark', dark > 0.35);
    };
    var ticking = false;
    var onScroll = function () { if (!ticking) { ticking = true; requestAnimationFrame(function () { heroFrame(); ticking = false; }); } };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    heroFrame();
  }

  /* Featured jobs: first click shows 3 more, second goes to the Job Board */
  var more = $('moreJobs'), grid = $('jobGrid');
  if (more && grid) {
    more.addEventListener('click', function () {
      if (!grid.classList.contains('expanded')) {
        grid.classList.add('expanded');
        more.setAttribute('aria-expanded', 'true');
        more.textContent = more.dataset.label;
      } else {
        window.location.href = more.dataset.href;
      }
    });
  }

  /* Five registration steps with a live preview card */
  var ui = $('uiCard');
  if (ui) {
    var cards = [
      { s: 'Step 1 · Create account', t: 'Register', rows: [['Name', 'Your name'], ['Email', 'you@email.com'], ['Password', '••••••••••']], foot: '<span class="ok">✓ Account created</span>' },
      { s: 'Step 2 · Choose role', t: 'What do you do?', rows: [['Email', '<span class="ok">✓ Confirmed</span>'], ['Role', 'Registered Nurse'], ['Area', 'Leeds']], foot: '<span class="ok">Checklist ready →</span>' },
      { s: 'Step 3 · Documents', t: 'Your checklist', rows: [['Passport', '<span class="ok">✓ Uploaded</span>'], ['NMC PIN', '<span class="ok">✓ Added</span>'], ['Enhanced DBS', '<span class="wait">To upload</span>']], foot: '2 of 3 done · <b>in your portal</b>' },
      { s: 'Step 4 · Checks', t: 'Compliance check', rows: [['Documents', '<span class="ok">✓ Verified</span>'], ['References', '<span class="ok">✓ Received</span>'], ['Status', '<span class="ok">Ready to work</span>']], foot: 'Day 3 · We call you to confirm' },
      { s: 'Step 5 · Start', t: 'Shift confirmed', rows: [['Where', 'Willow Court, Leeds'], ['When', 'Mon 07:00 – 19:00'], ['Pay', '£25.00/hr']], foot: '<span class="ok">✓ Sent by text and email</span>' }
    ];
    var btns = document.querySelectorAll('.step-btn'), cur = 0, timer = null;
    var showStep = function (n) {
      cur = n;
      btns.forEach(function (b, k) { if (k === n) b.setAttribute('aria-current', 'step'); else b.removeAttribute('aria-current'); });
      var c = cards[n];
      ui.innerHTML = '<div class="ui-step">' + c.s + '</div><div class="ui-title">' + c.t + '</div><div class="ui-rows">' +
        c.rows.map(function (r) { return '<div class="ui-row"><span>' + r[0] + '</span><b>' + r[1] + '</b></div>'; }).join('') +
        '</div><div class="ui-rows" style="margin-top:12px;padding-top:10px;border-top:1px solid var(--line)"><div class="ui-row">' + c.foot + '</div></div>';
      ui.classList.remove('swap'); void ui.offsetWidth; ui.classList.add('swap');
    };
    btns.forEach(function (b) { b.addEventListener('click', function () { clearInterval(timer); showStep(+b.dataset.step); }); });
    showStep(0);
    if (!reduce) timer = setInterval(function () { showStep((cur + 1) % cards.length); }, 3800);
  }

  /* Slot-machine counter, runs once when it scrolls into view */
  var slot = document.querySelector('.slot');
  if (slot && !reduce && 'IntersectionObserver' in window) {
    var val = slot.dataset.value;
    slot.innerHTML = val.split('').map(function (c) {
      if (!/[0-9]/.test(c)) return '<span class="reel-sep" aria-hidden="true">' + c + '</span>';
      var digits = ''; for (var k = 0; k < 3; k++) for (var n = 0; n < 10; n++) digits += '<span>' + n + '</span>';
      return '<span class="reel" aria-hidden="true"><span class="reel-strip">' + digits + '<span>' + c + '</span></span></span>';
    }).join('') + (slot.dataset.plus ? '<span class="reel-sep" aria-hidden="true">+</span>' : '');
    var io = new IntersectionObserver(function (entries) {
      if (!entries[0].isIntersecting) return;
      io.disconnect();
      slot.querySelectorAll('.reel').forEach(function (r, i) {
        r.firstChild.style.transition = 'transform ' + (1.4 + i * 0.35) + 's cubic-bezier(.15,.85,.25,1)';
        r.firstChild.style.transform = 'translateY(-30em)';
      });
    }, { threshold: 0.6 });
    io.observe(slot);
  }

  /* Why HCC: promise slides */
  var collage = $('collage');
  if (collage) {
    var slides = [
      { big: '2 hrs', lbl: 'to cover an emergency call, nationwide', dark: 'Reliable, fully vetted staff for people who depend on care, any hour of the day.', q1: 'Fully vetted staff: DBS, right to work and training checked before their first shift.', c1: 'Our compliance promise', q2: 'One phone number, answered 24 hours a day by a team that knows your site.', c2: 'Our service promise' },
      { big: '3 days', lbl: 'from registration to ready to work', dark: 'A steady supply of checked candidates, so the same faces can come back each week.', q1: 'We support vulnerable people, so we only send staff we would trust with our own families.', c1: 'Our care promise', q2: 'Candidates get a clear checklist and a named contact, so they are ready to work in 3 days.', c2: 'Our candidate promise' }
    ];
    var si = 0;
    var showSlide = function (n) {
      si = (n + slides.length) % slides.length;
      Object.keys(slides[si]).forEach(function (k) { var el = collage.querySelector('[data-slot="' + k + '"]'); if (el) el.textContent = slides[si][k]; });
    };
    $('whyPrev').addEventListener('click', function () { showSlide(si - 1); });
    $('whyNext').addEventListener('click', function () { showSlide(si + 1); });
  }

  /* FAQ tabs */
  document.querySelectorAll('.faq-tabs').forEach(function (list) {
    var tabs = list.querySelectorAll('[role=tab]');
    var select = function (t) {
      tabs.forEach(function (o) { var on = o === t; o.setAttribute('aria-selected', String(on)); o.tabIndex = on ? 0 : -1; $(o.getAttribute('aria-controls')).hidden = !on; });
    };
    tabs.forEach(function (t, k) {
      t.addEventListener('click', function () { select(t); });
      t.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { var n = tabs[(k + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length]; select(n); n.focus(); }
      });
    });
  });

  /* Job Board: filters apply as soon as they change; mobile filter panel */
  var board = $('boardForm');
  if (board) {
    board.addEventListener('change', function (e) {
      if (e.target.matches('input[type=checkbox], select')) board.submit();
    });
    var ft = $('filterToggle'), fl = $('filters');
    if (ft) ft.addEventListener('click', function () { var o = !fl.classList.contains('open'); fl.classList.toggle('open', o); ft.setAttribute('aria-expanded', String(o)); });
  }

  /* For candidates: tap a role to open what it needs */
  var picks = document.querySelectorAll('.role-pick[data-id]');
  if (picks.length) {
    var showRole = function (id, scroll) {
      var panel = $('role-' + id); if (!panel) return;
      picks.forEach(function (b) { b.setAttribute('aria-pressed', String(b.dataset.id === id)); });
      document.querySelectorAll('.role-detail').forEach(function (p) { p.hidden = p !== panel; });
      if (scroll) panel.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
    };
    picks.forEach(function (b) { b.addEventListener('click', function () { showRole(b.dataset.id, true); history.replaceState(null, '', '#role-' + b.dataset.id); }); });
    var m = location.hash.match(/^#role-(.+)$/);
    if (m) setTimeout(function () { showRole(m[1], true); }, 60);
  }

  /* Registration: follow-up questions appear only when relevant */
  document.querySelectorAll('form').forEach(function (f) {
    var shows = f.querySelectorAll('[data-show]');
    if (!shows.length) return;
    var val = function (name) {
      var r = f.querySelector('[name="' + name + '"]:checked');
      if (r) return r.value;
      var el = f.querySelector('select[name="' + name + '"], input[name="' + name + '"]:not([type=radio])');
      return el ? el.value : '';
    };
    var run = function () { shows.forEach(function (el) { var p = el.dataset.show.split('='); el.hidden = p[1].split('|').indexOf(val(p[0])) < 0; }); };
    f.addEventListener('change', run); run();
  });

  /* Email code: allow "482 913" and paste */
  var code = document.querySelector('.code-input');
  if (code) code.addEventListener('input', function () { code.value = code.value.replace(/[^0-9 ]/g, '').slice(0, 7); });

  /* Documents and training: each file uploads as soon as it is chosen; phone photos are made smaller first */
  function shrink(file) {
    return new Promise(function (resolve) {
      if (!/^image\/(jpeg|png)$/.test(file.type) || file.size < 1.5 * 1024 * 1024 || !window.createImageBitmap) return resolve(file);
      createImageBitmap(file).then(function (img) {
        var max = 2000, sc = Math.min(1, max / Math.max(img.width, img.height));
        var c = document.createElement('canvas'); c.width = Math.round(img.width * sc); c.height = Math.round(img.height * sc);
        c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
        c.toBlob(function (b) { resolve(b && b.size < file.size ? new File([b], file.name.replace(/\.png$/i, '.jpg'), { type: 'image/jpeg' }) : file); }, 'image/jpeg', 0.85);
      }).catch(function () { resolve(file); });
    });
  }
  function counts(row, c) {
    var wrap = row.closest('[data-set]'); if (!wrap || !c) return;
    var t = wrap.querySelector('[data-count]'); if (t) t.textContent = c.text;
    var bar = wrap.querySelector('[data-bar] i'); if (bar) bar.style.width = c.pct + '%';
  }
  function send(form, extra) {
    var row = form.closest('.doc'), msg = row.querySelector('.up-msg'), fd = new FormData(form);
    fd.delete('file[]'); fd.append('ajax', '1');
    Object.keys(extra || {}).forEach(function (k) { if (k !== 'files') fd.set(k, extra[k]); });
    var files = (extra && extra.files) || [];
    row.classList.add('uploading'); msg.textContent = files.length ? 'Uploading…' : 'Saving…';
    Promise.all(files.map(shrink)).then(function (list) {
      list.forEach(function (f) { fd.append('file[]', f, f.name); });
      return fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' });
    }).then(function (r) { return r.json(); }).then(function (j) {
      if (!j.success) { row.classList.remove('uploading'); msg.textContent = (j.data && j.data.message) || 'That did not work. Please try again.'; return; }
      var tmp = document.createElement('div'); tmp.innerHTML = j.data.html;
      var fresh = tmp.firstChild; row.replaceWith(fresh); counts(fresh, j.data.counts);
      var m2 = fresh.querySelector('.up-msg'); if (m2 && files.length) m2.textContent = 'Saved.';
    }).catch(function () { row.classList.remove('uploading'); msg.textContent = 'Upload failed. Check your connection and try again.'; });
  }
  document.addEventListener('change', function (e) {
    var t = e.target, form = t.closest && t.closest('.item-form');
    if (!form) return;
    if (t.classList.contains('up-input') && t.files.length) send(form, { files: Array.prototype.slice.call(t.files) });
    else if (t.name === 'missing') send(form, { missing: t.checked ? '1' : '0' });
    else if (t.name === 'exp' && !form.querySelector('[name=pin]')) send(form, { exp: t.value });
  });
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form.classList.contains('item-form') && form.querySelector('[name=pin]')) { e.preventDefault(); send(form, {}); }
  });

  /* Staff portal: Expand all / Collapse all sections */
  document.querySelectorAll('[data-sections]').forEach(function (b) {
    b.addEventListener('click', function () {
      var open = b.dataset.sections === 'open';
      document.querySelectorAll('.p-sec, .p-folder').forEach(function (d) { d.open = open; });
    });
  });
  document.addEventListener('click', function (e) {
    document.querySelectorAll('.mini-pop[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });

  /* Contact: show the organisation field for employers */
  var org = $('orgField');
  if (org) {
    document.querySelectorAll('input[name=who]').forEach(function (r) {
      r.addEventListener('change', function () { org.hidden = r.value !== 'employer'; });
    });
  }

  /* Bring success and error messages into view after a form redirect */
  if (/[?&](hcc_(ok|err)|msg)=/.test(location.search) && location.hash) {
    var target = document.querySelector(location.hash);
    if (target) { if (target.tagName === 'DETAILS') target.open = true; target.scrollIntoView({ block: 'start' }); }
  }
})();
