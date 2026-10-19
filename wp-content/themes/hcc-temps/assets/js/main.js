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

  /* Hero: framed photo grows to full bleed as the page scrolls */
  var track = $('heroTrack');
  if (track) {
    var stage = $('heroStage'), photo = $('heroPhoto'), frame = $('heroFrame'), shade = $('heroShade'), dots = $('dots');
    var colours = ['#6DC0B8', '#2D338C', '#D93831', '#F3D45E', '#7C4925'];
    for (var i = 0; i < 46; i++) {
      var d = document.createElement('i'), s = 4 + Math.random() * 9;
      var x = Math.random() * 100, y = 40 + Math.random() * 58;
      if (x > 30 && x < 70 && y > 52) { x = x < 50 ? x - 22 : x + 22; }
      d.style.cssText = 'width:' + s + 'px;height:' + s + 'px;left:' + x + '%;top:' + y + '%;background:' + colours[i % 5] + ';opacity:' + (0.55 + Math.random() * 0.45);
      dots.appendChild(d);
    }
    var ease = function (t) { return t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; };
    var clamp = function (v) { return Math.max(0, Math.min(1, v)); };
    var heroFrame = function () {
      var W = stage.clientWidth, H = stage.clientHeight;
      var range = track.offsetHeight - H;
      var p = reduce || range <= 0 ? 0 : clamp(-track.getBoundingClientRect().top / range);
      var g = ease(clamp(p / 0.8));
      var narrow = W < 640;
      var w0 = Math.min(narrow ? W * 0.84 : 520, W - 32), h0 = H * (narrow ? 0.34 : 0.4);
      var x0 = (W - w0) / 2, y0 = H - h0 + (narrow ? 0 : H * 0.04);
      var px = x0 * (1 - g), py = y0 * (1 - g), w = w0 + (W - w0) * g, h = h0 + (H - h0) * g;
      photo.style.cssText = 'left:' + px + 'px;top:' + py + 'px;width:' + w + 'px;height:' + h + 'px;border-radius:' + (6 * (1 - g)) + 'px';
      var pad = 14 * (1 - g);
      frame.style.cssText = 'left:' + (px - pad) + 'px;top:' + (py - pad) + 'px;width:' + (w + pad * 2) + 'px;height:' + (h + pad * 2) + 'px;transform:rotate(' + (-2.5 * (1 - g)) + 'deg);opacity:' + (1 - g);
      dots.style.opacity = String(1 - clamp(p * 2.4));
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

  /* Five steps with a live preview card */
  var ui = $('uiCard');
  if (ui) {
    var cards = [
      { s: 'Step 1 · Find a role', t: 'Senior Care Assistant', rows: [['Location', 'Basingstoke'], ['Pay', '£14.20 – £15.00/hr'], ['Shifts', 'Long days']], foot: '<span class="ok">Apply in 1 minute →</span>' },
      { s: 'Step 2 · Apply', t: 'Your application', rows: [['Name', 'Priya Shah'], ['Phone', '07700 900123'], ['CV', 'priya-shah-cv.pdf']], foot: '<span class="ok">✓ Application sent</span>' },
      { s: 'Step 3 · Documents', t: 'Your checklist', rows: [['Right to work', '<span class="ok">✓ Uploaded</span>'], ['DBS certificate', '<span class="ok">✓ Uploaded</span>'], ['Care Certificate', '<span class="wait">To upload</span>']], foot: '2 of 3 done · <b>Link sent by email</b>' },
      { s: 'Step 4 · Checks', t: 'Compliance check', rows: [['Documents', '<span class="ok">✓ Verified</span>'], ['References', '<span class="ok">✓ Received</span>'], ['Status', '<span class="ok">Ready to work</span>']], foot: 'We will call you to confirm' },
      { s: 'Step 5 · Start', t: 'Shift confirmed', rows: [['Where', 'Willow Court, Basingstoke'], ['When', 'Mon 07:00 – 19:00'], ['Pay', '£14.20/hr']], foot: '<span class="ok">✓ Sent by text and email</span>' }
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

  /* Why HCC collage slides */
  var collage = $('collage');
  if (collage) {
    var slides = [
      { big: '96%', lbl: 'of shifts filled on the first call', dark: 'Care homes, schools and hotels across England rely on us for cover.', q1: '"They found us a chef for the same afternoon when ours called in sick. The food was great and the residents never noticed the change."', c1: 'Care home manager, Cumbria', q2: '"Every nurse they send has their paperwork checked before they arrive. That saves our team hours every week."', c2: 'Deputy manager, nursing home, Solihull' },
      { big: '2 hrs', lbl: 'average time to confirm urgent cover', dark: 'One phone number, a named consultant and cover day or night.', q1: '"We rang at 6am with two housekeepers off sick. Both shifts were covered before breakfast service."', c1: 'Hotel operations manager, Southampton', q2: '"The same faces come back each week, so our residents get to know them."', c2: 'Registered manager, care home, Hampshire' }
    ];
    var si = 0;
    var showSlide = function (n) {
      si = (n + slides.length) % slides.length;
      Object.keys(slides[si]).forEach(function (k) { var el = collage.querySelector('[data-slot="' + k + '"]'); if (el) el.textContent = slides[si][k]; });
    };
    $('whyPrev').addEventListener('click', function () { showSlide(si - 1); });
    $('whyNext').addEventListener('click', function () { showSlide(si + 1); });
  }

  /* Reviews */
  var revImg = $('revImg');
  if (revImg) {
    var base = revImg.src.replace(/[^/]+$/, '');
    var reviews = [
      { t: 'I applied on my phone on the bus home. My documents were checked by Thursday and I worked my first shift on Monday.', w: 'Daniel A.', r: 'Hospitality assistant · sample review', img: 'hospman.jpg', pos: 'center 20%' },
      { t: 'I choose my own shifts around the school run. The team always calls back, even on a Sunday.', w: 'Grace O.', r: 'Healthcare assistant · sample review', img: 'hero.jpg', pos: '30% 25%' },
      { t: 'Uploading my certificates took ten minutes and I never had to send the same document twice.', w: 'Marta K.', r: 'Kitchen assistant · sample review', img: 'kitchen.jpg', pos: '58% 15%' }
    ];
    var ri = 0;
    var showReview = function (n) {
      ri = (n + reviews.length) % reviews.length;
      var r = reviews[ri];
      $('revText').textContent = r.t;
      $('revWho').textContent = r.w;
      $('revRole').textContent = r.r;
      revImg.src = base + r.img; revImg.style.objectPosition = r.pos;
    };
    $('revPrev').addEventListener('click', function () { showReview(ri - 1); });
    $('revNext').addEventListener('click', function () { showReview(ri + 1); });
  }

  /* FAQ tabs: candidates / employers */
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
    ft.addEventListener('click', function () { var o = !fl.classList.contains('open'); fl.classList.toggle('open', o); ft.setAttribute('aria-expanded', String(o)); });
  }

  /* CV drop zone: show the chosen file and accept drag and drop */
  document.querySelectorAll('.drop').forEach(function (drop) {
    var input = drop.querySelector('input[type=file]'), name = drop.querySelector('.file-name');
    input.addEventListener('change', function () { name.textContent = input.files[0] ? '✓ ' + input.files[0].name : ''; });
    ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('drag'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('drag'); }); });
    drop.addEventListener('drop', function (e) {
      if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; input.dispatchEvent(new Event('change')); }
    });
  });

  /* Register: pick a role card */
  var roleInput = $('roleInput');
  if (roleInput) {
    var picks = document.querySelectorAll('.role-pick');
    picks.forEach(function (b) {
      b.addEventListener('click', function () {
        picks.forEach(function (o) { o.setAttribute('aria-pressed', 'false'); });
        b.setAttribute('aria-pressed', 'true');
        roleInput.value = b.dataset.role;
        $('chosen').textContent = b.dataset.role;
        $('apply').scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
      });
    });
    $('apply').addEventListener('submit', function (e) {
      if (!roleInput.value) {
        e.preventDefault();
        $('apply').querySelector('.error-msg').textContent = 'Please choose your role first.';
        picks[0].scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
      }
    });
  }

  /* Upload page: show the name each file will be saved under */
  var docs = $('docs');
  if (docs && docs.querySelector('.doc')) {
    var refEl = document.querySelector('.side-card .ref');
    var today = new Date().toISOString().slice(0, 10);
    docs.addEventListener('change', function (e) {
      var input = e.target;
      if (!input.files || !input.files[0]) return;
      var row = input.closest('.doc');
      var ext = (input.files[0].name.match(/\.[a-z0-9]+$/i) || ['.pdf'])[0].toLowerCase();
      var key = row.dataset.label.replace(/[^A-Za-z0-9 ]+/g, ' ').split(/\s+/).filter(Boolean).map(function (w) { return w[0].toUpperCase() + w.slice(1); }).join('');
      row.querySelector('.renamed').textContent = 'Will be saved as ' + refEl.dataset.ref + '_' + refEl.dataset.name + '_' + key + '_' + today + ext;
      row.querySelector('label.btn').textContent = 'Change file';
      row.classList.add('chosen');
    });
  }

  /* Contact: show the organisation field for employers */
  var org = $('orgField');
  if (org) {
    document.querySelectorAll('input[name=who]').forEach(function (r) {
      r.addEventListener('change', function () { org.hidden = r.value !== 'employer'; });
    });
  }

  /* Bring success and error messages into view after a form redirect */
  if (/[?&]hcc_(ok|err)=/.test(location.search) && location.hash) {
    var target = document.querySelector(location.hash);
    if (target) { target.scrollIntoView({ block: 'start' }); if (target.tabIndex < 0) target.focus({ preventScroll: true }); }
  }
})();
