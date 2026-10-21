/**
 * Winserve Care - Guided Assistant
 * Rule based. No AI, no third party calls. Runs entirely in the browser
 * and posts once to admin-ajax.php on submit.
 */
(function () {
  'use strict';

  var CFG = window.WS_CHAT || {};
  var panel, body, toggle, badge, statusEl;
  var state = { pathway: null, step: 0, answers: {}, flow: null };

  /* ---------- Conversation flows ---------- */

  var FLOWS = {
    care: {
      label: 'Arrange care',
      intro: 'Happy to help you arrange care. Just a few quick questions.',
      steps: [
        { key: 'Who is the care for', type: 'choice', q: 'Who is the care for?',
          options: ['Myself', 'My parent', 'My partner', 'Another relative', 'Someone I support professionally'] },
        { key: 'Service needed', type: 'choice', q: 'Which service are you interested in?',
          options: ['Supported Living', 'Domiciliary Care', 'Private Care Package', 'Learning Disabilities', 'End of Life Care', 'Mental Health Support', 'Not sure yet'] },
        { key: 'When needed', type: 'choice', q: 'When would you like care to start?',
          options: ['As soon as possible', 'Within a few weeks', 'Just planning ahead'] },
        { key: 'Area', type: 'text', q: 'Which area or postcode is the care needed in?', placeholder: 'e.g. Morley, LS27' },
        { key: 'Name', type: 'text', q: 'And your name?', placeholder: 'Your full name' },
        { key: 'Phone', type: 'tel', q: 'Best number to reach you on?', placeholder: 'e.g. 07123 456789' },
        { key: 'Email', type: 'email', q: 'And your email address?', placeholder: 'you@example.com' },
        { key: 'Notes', type: 'textarea', q: 'Anything else we should know? (optional)', placeholder: 'Tell us a little about the support needed', optional: true }
      ]
    },

    commissioner: {
      label: 'Commissioner or case manager',
      intro: 'Thanks for considering Winserve. Let us take some details and the right person will come back to you.',
      steps: [
        { key: 'Organisation', type: 'text', q: 'Which organisation are you with?', placeholder: 'e.g. Leeds City Council, NHS Trust' },
        { key: 'Your role', type: 'choice', q: 'What is your role?',
          options: ['Commissioner', 'Health case manager', 'Social worker', 'Discharge coordinator', 'Other'] },
        { key: 'Package type', type: 'choice', q: 'What sort of package are you looking to place?',
          options: ['Supported living', 'Domiciliary care', 'Higher staffing ratio (up to 3:1)', 'Multiple placements', 'Exploring options'] },
        { key: 'Timescale', type: 'choice', q: 'How quickly do you need this in place?',
          options: ['Urgent, within days', 'Within 2 to 4 weeks', 'Planning ahead', 'Just making contact'] },
        { key: 'Name', type: 'text', q: 'Your name?', placeholder: 'Your full name' },
        { key: 'Phone', type: 'tel', q: 'Contact number?', placeholder: 'Direct line or mobile' },
        { key: 'Email', type: 'email', q: 'Work email address?', placeholder: 'you@organisation.gov.uk' },
        { key: 'Notes', type: 'textarea', q: 'Any detail about the placement? (optional)', placeholder: 'Brief outline of the support required', optional: true }
      ]
    },

    job: {
      label: 'Apply for a job',
      intro: 'Great to hear you are interested in joining us. A few quick questions.',
      steps: [
        { key: 'Role', type: 'choice', q: 'Which role are you interested in?',
          options: ['Care Worker', 'Support Worker', 'Not sure, open to options'] },
        { key: 'Right to work in the UK', type: 'choice', q: 'Do you have the right to work in the UK?',
          options: ['Yes', 'No'], note: 'We are unable to offer visa sponsorship at this time.' },
        { key: 'Care experience', type: 'choice', q: 'Do you have previous care experience?',
          options: ['Yes, over 1 year', 'Yes, under 1 year', 'No, but keen to learn'] },
        { key: 'Driving licence', type: 'choice', q: 'Do you hold a full UK driving licence?',
          options: ['Yes, and I have a car', 'Yes, but no car', 'No'] },
        { key: 'Availability', type: 'choice', q: 'What availability are you looking for?',
          options: ['Full time', 'Part time', 'Weekends only', 'Flexible'] },
        { key: 'Name', type: 'text', q: 'Your full name?', placeholder: 'Your full name' },
        { key: 'Phone', type: 'tel', q: 'Contact number?', placeholder: 'e.g. 07123 456789' },
        { key: 'Email', type: 'email', q: 'Email address?', placeholder: 'you@example.com' },
        { key: 'About you', type: 'textarea', q: 'Tell us briefly why you want to work in care.', placeholder: 'A few sentences is plenty' }
      ]
    },

    general: {
      label: 'Something else',
      intro: 'No problem. Tell us what you need and we will point you in the right direction.',
      steps: [
        { key: 'Enquiry', type: 'textarea', q: 'What can we help with?', placeholder: 'Type your question here' },
        { key: 'Name', type: 'text', q: 'Your name?', placeholder: 'Your full name' },
        { key: 'Email', type: 'email', q: 'Email address?', placeholder: 'you@example.com' },
        { key: 'Phone', type: 'tel', q: 'Contact number? (optional)', placeholder: 'e.g. 07123 456789', optional: true }
      ]
    }
  };

  /* ---------- About Winserve: single answer, then back to menu ---------- */

  var ABOUT_TEXT =
    'Winserve Care Services Ltd is a CQC registered care provider based in Leeds, rated <strong>Good</strong> at our June 2025 inspection.' +
    '<br><br>' +
    'We have supported people across Yorkshire for over six years, and have worked with local authorities, the NHS and commissioners since 2020. Our speciality is <strong>supported living</strong>, including packages at staffing ratios up to 3:1, alongside domiciliary care and private care packages arranged directly with families.' +
    '<br><br>' +
    'We employ around <strong>40 carers</strong>, all DBS checked and fully trained before they start. We pay the Real Living Wage, which keeps our turnover low so the people we support see familiar faces rather than a rotating cast of agency staff.' +
    '<br><br>' +
    'Our management team carries out every initial assessment in person, so we know who we are placing and why. We hold a 9.8 out of 10 rating from 75+ independently verified reviews on Homecare.co.uk, and we are an Armed Forces Covenant signatory supporting veterans through the Homeless Soldier Project and the Royal Regiment of Fusiliers.' +
    '<br><br>' +
    '<em>Care is at the heart of what we do.</em>';

  /* ---------- Rendering helpers ---------- */

  function el(tag, cls, html) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (html !== undefined) n.innerHTML = html;
    return n;
  }

  function scrollDown() {
    window.requestAnimationFrame(function () { body.scrollTop = body.scrollHeight; });
  }

  function botSay(html, cb) {
    var typing = el('div', 'ws-msg ws-msg-bot ws-typing', '<span></span><span></span><span></span>');
    body.appendChild(typing);
    scrollDown();
    setTimeout(function () {
      typing.remove();
      var m = el('div', 'ws-msg ws-msg-bot', html);
      body.appendChild(m);
      scrollDown();
      if (cb) cb();
    }, 420);
  }

  function userSay(text) {
    body.appendChild(el('div', 'ws-msg ws-msg-user', escapeHtml(text)));
    scrollDown();
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function clearInputs() {
    var old = body.querySelectorAll('.ws-actions, .ws-input-wrap');
    Array.prototype.forEach.call(old, function (n) { n.remove(); });
  }

  /* ---------- Main menu ---------- */

  function showMenu(greet) {
    clearInputs();
    state = { pathway: null, step: 0, answers: {}, flow: null };

    var msg = greet !== false
      ? 'Hello, welcome to Winserve Care.<br>What can we help you with today?'
      : 'What else can we help with?';

    botSay(msg, function () {
      var wrap = el('div', 'ws-actions');
      [
        ['care',         'Arrange care for myself or a relative'],
        ['commissioner', 'I am a commissioner or case manager'],
        ['job',          'Apply for a job'],
        ['about',        'About Winserve, what we do'],
        ['general',      'Something else']
      ].forEach(function (o) {
        var b = el('button', 'ws-opt', o[1]);
        b.onclick = function () { userSay(o[1]); choosePath(o[0]); };
        wrap.appendChild(b);
      });
      body.appendChild(wrap);
      scrollDown();
    });
  }

  function choosePath(key) {
    clearInputs();

    if (key === 'about') {
      botSay(ABOUT_TEXT, function () {
        var wrap = el('div', 'ws-actions');
        var back = el('button', 'ws-opt ws-opt-primary', 'Back to main menu');
        back.onclick = function () { userSay('Back to main menu'); showMenu(false); };
        wrap.appendChild(back);
        body.appendChild(wrap);
        scrollDown();
      });
      return;
    }

    state.pathway = key;
    state.flow = FLOWS[key];
    state.step = 0;
    state.answers = {};
    botSay(state.flow.intro, function () { askStep(); });
  }

  /* ---------- Step engine ---------- */

  function askStep() {
    clearInputs();

    if (state.step >= state.flow.steps.length) { confirmStep(); return; }

    var s = state.flow.steps[state.step];
    var q = s.q + (s.note ? '<br><small style="opacity:.75">' + s.note + '</small>' : '');

    botSay(q, function () {
      if (s.type === 'choice') {
        var wrap = el('div', 'ws-actions');
        s.options.forEach(function (opt) {
          var b = el('button', 'ws-opt', opt);
          b.onclick = function () {
            userSay(opt);
            state.answers[s.key] = opt;
            // Hard stop if no right to work
            if (s.key === 'Right to work in the UK' && opt === 'No') {
              clearInputs();
              botSay('Thank you for letting us know. We are unfortunately unable to offer visa sponsorship at this time, so we cannot progress an application right now.', function () {
                var w = el('div', 'ws-actions');
                var b2 = el('button', 'ws-opt ws-opt-primary', 'Back to main menu');
                b2.onclick = function () { showMenu(false); };
                w.appendChild(b2);
                body.appendChild(w);
                scrollDown();
              });
              return;
            }
            state.step++;
            askStep();
          };
          wrap.appendChild(b);
        });
        body.appendChild(wrap);
        scrollDown();
      } else {
        showInput(s);
      }
    });
  }

  function showInput(s) {
    var wrap = el('div', 'ws-input-wrap');
    var field = s.type === 'textarea' ? el('textarea') : el('input');
    if (s.type !== 'textarea') field.type = s.type === 'tel' ? 'tel' : (s.type === 'email' ? 'email' : 'text');
    field.className = 'ws-input';
    field.placeholder = s.placeholder || '';
    if (s.type === 'textarea') field.rows = 2;

    var send = el('button', 'ws-send', '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>');
    send.setAttribute('aria-label', 'Send');

    function submitVal() {
      var v = field.value.trim();
      if (!v && !s.optional) { field.classList.add('ws-invalid'); field.focus(); return; }
      if (v && s.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
        field.classList.add('ws-invalid'); field.focus(); return;
      }
      userSay(v || 'Skipped');
      if (v) state.answers[s.key] = v;
      state.step++;
      askStep();
    }

    send.onclick = submitVal;
    field.onkeydown = function (e) {
      if (e.key === 'Enter' && (s.type !== 'textarea' || !e.shiftKey)) { e.preventDefault(); submitVal(); }
    };
    field.oninput = function () { field.classList.remove('ws-invalid'); };

    wrap.appendChild(field);
    wrap.appendChild(send);

    if (s.optional) {
      var skip = el('button', 'ws-skip', 'Skip');
      skip.onclick = function () { userSay('Skipped'); state.step++; askStep(); };
      wrap.appendChild(skip);
    }

    body.appendChild(wrap);
    scrollDown();
    setTimeout(function () { field.focus(); }, 80);
  }

  /* ---------- Confirm and send ---------- */

  function confirmStep() {
    clearInputs();
    var rows = Object.keys(state.answers).map(function (k) {
      return '<div class="ws-sum-row"><span>' + escapeHtml(k) + '</span><strong>' + escapeHtml(state.answers[k]) + '</strong></div>';
    }).join('');

    botSay('Here is what we have. Please check it over before we send it across.<div class="ws-summary">' + rows + '</div>', function () {
      var wrap = el('div', 'ws-actions');

      var consent = el('label', 'ws-consent');
      consent.innerHTML = '<input type="checkbox" id="ws-consent-box"> <span>I am happy for Winserve Care Services to contact me about this enquiry. See our <a href="' + CFG.privacy + '" target="_blank" rel="noopener">privacy policy</a>.</span>';
      wrap.appendChild(consent);

      var send = el('button', 'ws-opt ws-opt-primary', 'Send my enquiry');
      send.onclick = function () {
        var box = document.getElementById('ws-consent-box');
        if (!box || !box.checked) { consent.classList.add('ws-invalid-consent'); return; }
        doSend(send);
      };
      wrap.appendChild(send);

      var restart = el('button', 'ws-opt ws-opt-ghost', 'Start again');
      restart.onclick = function () { showMenu(false); };
      wrap.appendChild(restart);

      body.appendChild(wrap);
      scrollDown();
    });
  }

  function doSend(btn) {
    btn.disabled = true;
    btn.textContent = 'Sending...';

    var fd = new FormData();
    fd.append('action', 'winserve_chat');
    fd.append('nonce', CFG.nonce);
    fd.append('pathway', state.pathway);
    fd.append('answers', JSON.stringify(state.answers));
    fd.append('page_url', window.location.href);
    fd.append('website', '');

    fetch(CFG.ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        clearInputs();
        if (res && res.success) {
          botSay('Thank you. Your enquiry is on its way to our team and we will be in touch shortly.<br><br>If it is urgent, call us on <a href="tel:01133408777">' + CFG.phone + '</a>.', function () {
            var w = el('div', 'ws-actions');
            var b = el('button', 'ws-opt', 'Back to main menu');
            b.onclick = function () { showMenu(false); };
            w.appendChild(b);
            body.appendChild(w);
            scrollDown();
          });
        } else {
          var msg = (res && res.data && res.data.message) ? res.data.message : 'Something went wrong.';
          botSay(escapeHtml(msg) + '<br><br>You can reach us on <a href="tel:01133408777">' + CFG.phone + '</a> or <a href="mailto:' + CFG.email + '">' + CFG.email + '</a>.', function () {
            var w = el('div', 'ws-actions');
            var b = el('button', 'ws-opt', 'Back to main menu');
            b.onclick = function () { showMenu(false); };
            w.appendChild(b);
            body.appendChild(w);
            scrollDown();
          });
        }
      })
      .catch(function () {
        clearInputs();
        botSay('We could not send that just now. Please call <a href="tel:01133408777">' + CFG.phone + '</a> or email <a href="mailto:' + CFG.email + '">' + CFG.email + '</a>.');
      });
  }

  /* ---------- Open / close ---------- */

  function openChat() {
    panel.classList.add('ws-open');
    toggle.classList.add('ws-active');
    toggle.setAttribute('aria-expanded', 'true');
    if (badge) badge.style.display = 'none';
    if (!body.hasChildNodes()) { showMenu(true); }
    try { sessionStorage.setItem('ws_chat_opened', '1'); } catch (e) {}
  }

  function closeChat() {
    panel.classList.remove('ws-open');
    toggle.classList.remove('ws-active');
    toggle.setAttribute('aria-expanded', 'false');
  }

  /* ---------- Boot ---------- */

  document.addEventListener('DOMContentLoaded', function () {
    panel    = document.getElementById('ws-chat-panel');
    body     = document.getElementById('ws-chat-body');
    toggle   = document.getElementById('ws-chat-toggle');
    badge    = document.getElementById('ws-chat-badge');
    statusEl = document.getElementById('ws-chat-status');
    if (!panel || !toggle) return;

    // Open hours note
    var h = new Date().getHours(), d = new Date().getDay();
    var openNow = d >= 1 && d <= 5 && h >= 8 && h < 17;
    if (statusEl) statusEl.textContent = openNow ? 'Here to help' : 'Leave us a message';

    toggle.onclick = function () {
      panel.classList.contains('ws-open') ? closeChat() : openChat();
    };
    var min = document.getElementById('ws-chat-min');
    if (min) min.onclick = closeChat;

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && panel.classList.contains('ws-open')) closeChat();
    });

    // Hide the notification dot once seen
    try { if (sessionStorage.getItem('ws_chat_opened') && badge) badge.style.display = 'none'; } catch (e) {}
  });

})();
