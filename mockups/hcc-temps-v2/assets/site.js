/* Shared mockup data and behaviour. In WordPress the JOBS list comes from the Job Board (job_vacancies). */
var HCC = (function () {
  var JOBS = [
    { id: 'rgn-solihull', title: 'Registered Staff Nurse (RGN)', sector: 'Healthcare', role: 'Nurse', place: 'Solihull, B90', region: 'Midlands', pay: '£25.00 – £26.75 per hour', payMin: 25, contract: 'Temporary', shifts: 'Days & nights', img: 'img/hero-nurse.jpg', alt: 'Registered nurse in a navy uniform in a care home corridor', badge: 'urgent', posted: 1 },
    { id: 'chef-maryport', title: 'Care Home Chef', sector: 'Chefs', role: 'Chef', place: 'Maryport, Cumbria', region: 'North', pay: '£15.00 – £20.00 per hour', payMin: 15, contract: 'Temporary', shifts: 'Weekdays', img: 'img/chef.jpg', alt: 'Chef plating meals in a stainless steel kitchen', badge: 'new', posted: 1 },
    { id: 'senior-carer-basingstoke', title: 'Senior Care Assistant', sector: 'Healthcare', role: 'Senior Care Assistant', place: 'Basingstoke, Hampshire', region: 'South', pay: '£13.30 – £14.75 per hour', payMin: 13.3, contract: 'Temporary', shifts: 'Long days', img: 'img/carer.jpg', alt: 'Care assistant walking arm in arm with an older man in a garden', badge: '', posted: 2 },
    { id: 'kitchen-asst-london', title: 'School Kitchen Assistant', sector: 'Hospitality', role: 'Kitchen Assistant', place: 'London', region: 'London', pay: '£12.21 – £12.75 per hour', payMin: 12.21, contract: 'Temporary', shifts: 'Term time', img: 'img/kitchen.jpg', alt: 'Kitchen assistant chopping vegetables in a commercial kitchen', badge: 'new', posted: 2 },
    { id: 'barista-london', title: 'Temp Barista', sector: 'Hospitality', role: 'Barista', place: 'Central London', region: 'London', pay: '£12.21 – £12.75 per hour', payMin: 12.21, contract: 'Temporary', shifts: 'Flexible', img: 'img/barista.jpg', alt: 'Barista pouring latte art at a cafe counter', badge: '', posted: 3 },
    { id: 'cleaner-catford', title: 'Temp Cleaner', sector: 'Housekeeping & cleaning', role: 'Cleaner', place: 'Catford, SE6', region: 'London', pay: '£12.21 – £12.75 per hour', payMin: 12.21, contract: 'Temporary', shifts: 'Early starts', img: 'img/cleaner.jpg', alt: 'Cleaner sweeping the floor of an office reception', badge: 'urgent', posted: 3 },
    { id: 'housekeeper-southampton', title: 'Hotel Housekeeper', sector: 'Housekeeping & cleaning', role: 'Housekeeper', place: 'Southampton', region: 'South', pay: '£12.40 – £13.00 per hour', payMin: 12.4, contract: 'Temporary', shifts: 'Weekends', img: 'img/housekeeping.jpg', alt: 'Hotel housekeeper making a bed with white sheets', badge: '', posted: 4 },
    { id: 'events-newcastle', title: 'Events Hospitality Assistant', sector: 'Hospitality', role: 'Catering / Hospitality', place: 'Newcastle, NE20', region: 'North', pay: '£12.50 – £13.25 per hour', payMin: 12.5, contract: 'Temporary', shifts: 'Evenings', img: 'img/hospman.jpg', alt: 'Hospitality worker in a black shirt standing in a hotel restaurant', badge: 'new', posted: 4 },
    { id: 'receptionist-guildford', title: 'Care Home Receptionist', sector: 'Admin & reception', role: 'Receptionist', place: 'Guildford, Surrey', region: 'South', pay: '£12.80 – £13.40 per hour', payMin: 12.8, contract: 'Temp to perm', shifts: 'Weekdays', img: 'img/reception.jpg', alt: 'Receptionist smiling behind an office front desk', badge: '', posted: 5 },
    { id: 'hca-portsmouth', title: 'Healthcare Assistant', sector: 'Healthcare', role: 'Health Care Assistant', place: 'Portsmouth', region: 'South', pay: '£12.90 – £14.00 per hour', payMin: 12.9, contract: 'Temporary', shifts: 'Days & nights', img: 'img/hero.jpg', alt: 'Healthcare assistant in teal scrubs on a phone call', badge: 'urgent', posted: 5 },
    { id: 'cleaner-lymington', title: 'Temp Cleaner', sector: 'Housekeeping & cleaning', role: 'Cleaner', place: 'Lymington, SO41', region: 'South', pay: '£12.21 – £12.60 per hour', payMin: 12.21, contract: 'Temporary', shifts: 'Mornings', img: 'img/cleaner.jpg', alt: 'Cleaner sweeping the floor of an office reception', badge: '', posted: 6 },
    { id: 'chef-cornwall', title: 'Temp Chef', sector: 'Chefs', role: 'Chef', place: 'Truro, Cornwall', region: 'South', pay: '£14.50 – £16.00 per hour', payMin: 14.5, contract: 'Temporary', shifts: 'Weekends', img: 'img/chef.jpg', alt: 'Chef plating meals in a stainless steel kitchen', badge: '', posted: 7 },
    { id: 'kitchen-asst-portsmouth', title: 'Kitchen Assistant', sector: 'Hospitality', role: 'Kitchen Assistant', place: 'Portsmouth', region: 'South', pay: '£12.40 – £13.00 per hour', payMin: 12.4, contract: 'Temporary', shifts: 'Lunchtimes', img: 'img/kitchen.jpg', alt: 'Kitchen assistant chopping vegetables in a commercial kitchen', badge: '', posted: 8 },
    { id: 'hca-southampton', title: 'Healthcare Assistant', sector: 'Healthcare', role: 'Health Care Assistant', place: 'Southampton', region: 'South', pay: '£12.90 – £13.80 per hour', payMin: 12.9, contract: 'Temporary', shifts: 'Nights', img: 'img/carer.jpg', alt: 'Care assistant walking arm in arm with an older man in a garden', badge: '', posted: 9 }
  ];

  /* Documents each role needs. HCC to confirm the real lists. */
  var CHECKLISTS = {
    healthcare: [
      ['Photo ID', 'Passport or UK driving licence', true],
      ['Right to work in the UK', 'Passport, share code or BRP', true],
      ['DBS certificate', 'Enhanced DBS, or tell us if you need one', true],
      ['Care Certificate or NVQ', 'Or other care training certificates', true],
      ['Proof of address', 'Bank statement or utility bill from the last 3 months', true],
      ['Mandatory training', 'Manual handling, safeguarding, first aid', false]
    ],
    nurse: [
      ['Photo ID', 'Passport or UK driving licence', true],
      ['Right to work in the UK', 'Passport, share code or BRP', true],
      ['NMC PIN', 'Your current NMC registration', true],
      ['DBS certificate', 'Enhanced DBS, or tell us if you need one', true],
      ['Proof of address', 'Bank statement or utility bill from the last 3 months', true],
      ['Mandatory training', 'Manual handling, safeguarding, BLS', false]
    ],
    kitchen: [
      ['Photo ID', 'Passport or UK driving licence', true],
      ['Right to work in the UK', 'Passport, share code or BRP', true],
      ['Food Hygiene Level 2', 'Certificate in date', true],
      ['Proof of address', 'Bank statement or utility bill from the last 3 months', true],
      ['DBS certificate', 'Needed for schools and care homes', false]
    ],
    general: [
      ['Photo ID', 'Passport or UK driving licence', true],
      ['Right to work in the UK', 'Passport, share code or BRP', true],
      ['Proof of address', 'Bank statement or utility bill from the last 3 months', true],
      ['DBS certificate', 'Needed for schools and care homes', false]
    ]
  };

  /* Required documents and training per role, from HCC's "Mandatory Documents" list (Oct 2026). */
  var DOCS = ['CV', 'Valid passport or driving licence', 'Right to work: share code and supporting documents (non-British nationals, e.g. university term-time letter or certificate of sponsorship)', '2 x proof of address, dated within the last 3 months', 'Proof of National Insurance number', 'Professional photograph', 'Enhanced DBS certificate on the Update Service', 'Two references'];
  var CARE_TRAIN = ['Basic Life Support', 'Manual Handling', 'Safeguarding Adults', 'Safeguarding Children', 'Infection Prevention & Control', 'Health & Safety', 'Food Safety', 'Fire Safety', 'First Aid', 'Equality & Diversity', 'Information Governance / GDPR', 'Mental Capacity Act & DoLS'];
  var FOOD = function (lvl) { return ['Food Safety & Hygiene (' + lvl + ')', 'Health & Safety', 'First Aid', 'Allergen Awareness']; };
  var REQS = {
    'nurse': { name: 'Registered Nurse', docs: DOCS.concat(['Immunisation record (where applicable)', 'NMC PIN number', 'NMC Statement of Entry', 'Nursing diploma or degree']), train: CARE_TRAIN.concat(['Medication Administration', 'Autism Awareness & Learning Disabilities Awareness']) },
    'hca': { name: 'Healthcare Assistant', docs: DOCS, train: CARE_TRAIN.map(function (x) { return x === 'Manual Handling' ? 'Manual Handling (theory and practical)' : x; }).concat(['Autism Awareness & Learning Disabilities Awareness']) },
    'senior-carer': { name: 'Senior Carer', docs: DOCS, train: CARE_TRAIN.map(function (x) { return x === 'Manual Handling' ? 'Manual Handling (theory and practical)' : x; }).concat(['Medication Administration (theory and practical)', 'Autism Awareness & Learning Disabilities Awareness']) },
    'chef': { name: 'Chef', docs: DOCS, train: FOOD('Level 2 minimum') },
    'head-chef': { name: 'Head Chef', docs: DOCS, train: FOOD('Level 2 minimum, Level 3 preferred') },
    'kitchen': { name: 'Kitchen Assistant', docs: DOCS, train: FOOD('Level 1 minimum, Level 2 preferred') },
    'catering': { name: 'Catering Assistant', docs: DOCS, train: FOOD('Level 1 minimum, Level 2 preferred') },
    'barista': { name: 'Barista', docs: DOCS, train: FOOD('Level 1 minimum, Level 2 preferred') },
    'domestic': { name: 'Domestic Assistant', docs: DOCS, train: ['Health & Safety', 'Infection Control (preferred, not mandatory)', 'COSHH (preferred, not mandatory)'] },
    'admin': { name: 'Administrative Assistant', docs: DOCS, train: ['GDPR / Data Protection', 'Information Governance', 'Equality & Diversity', 'Health & Safety', 'Fire Safety'], trainNote: 'Preferred, not mandatory' }
  };
  function reqKey(role) {
    if (/nurse|rgn/i.test(role)) return 'nurse';
    if (/senior/i.test(role)) return 'senior-carer';
    if (/care|health/i.test(role)) return 'hca';
    if (/head chef/i.test(role)) return 'head-chef';
    if (/chef/i.test(role)) return 'chef';
    if (/kitchen/i.test(role)) return 'kitchen';
    if (/barista/i.test(role)) return 'barista';
    if (/clean|house|domestic/i.test(role)) return 'domestic';
    if (/recep|admin/i.test(role)) return 'admin';
    return 'catering';
  }

  function checklistFor(role) {
    if (/nurse/i.test(role)) return CHECKLISTS.nurse;
    if (/care|health/i.test(role)) return CHECKLISTS.healthcare;
    if (/chef|kitchen|catering/i.test(role)) return CHECKLISTS.kitchen;
    return CHECKLISTS.general;
  }

  var ICON_PIN = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>';
  var ICON_PAY = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M16 6.5A4 4 0 0 0 9 9v3m0 0v6h8M6 12h8"/></svg>';
  function sectorClass(s) { return s === 'Healthcare' || s === 'Admin & reception' ? 'health' : s === 'Chefs' ? 'chef' : 'hosp'; }
  function esc(t) { return String(t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function card(j, extra) {
    var badge = j.badge ? '<span class="badge ' + j.badge + '">' + (j.badge === 'urgent' ? 'Urgent' : 'New') + '</span>' : '';
    return '<article class="job' + (extra ? ' extra' : '') + '">' +
      '<a class="job-media" href="job.html#' + j.id + '" tabindex="-1" aria-hidden="true"><img src="' + j.img + '" alt="" width="720" height="540" loading="lazy">' + badge + '</a>' +
      '<div class="job-body"><span class="sector ' + sectorClass(j.sector) + '">' + esc(j.sector) + '</span>' +
      '<h3>' + esc(j.title) + '</h3>' +
      '<div class="meta"><div>' + ICON_PIN + esc(j.place) + '</div><div class="pay">' + ICON_PAY + esc(j.pay) + '</div></div>' +
      '<div class="job-foot"><span class="tag">' + esc(j.contract) + ' · ' + esc(j.shifts) + '</span><a class="apply-link" href="job.html#' + j.id + '">View &amp; apply <span aria-hidden="true">→</span></a></div></div></article>';
  }
  function byId(id) { for (var i = 0; i < JOBS.length; i++) if (JOBS[i].id === id) return JOBS[i]; return null; }

  /* Candidate reference + renamed file, as the live site will do on upload */
  function today() { var d = new Date(); return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
  function newRef() { return 'HCC-' + String(1000 + Math.floor(Math.random() * 9000)).padStart(6, '0'); }
  function slug(t) { return String(t).normalize('NFKD').replace(/[^\w\s-]/g, '').trim().replace(/\s+/g, '-'); }
  function renamed(ref, first, last, doc, original) {
    var ext = (original.match(/\.[a-z0-9]+$/i) || ['.pdf'])[0].toLowerCase();
    var docKey = doc.replace(/[^A-Za-z0-9 ]/g, ' ').split(/\s+/).filter(Boolean).map(function (w) { return w[0].toUpperCase() + w.slice(1); }).join('');
    return ref + '_' + slug(last || 'Surname') + '-' + slug(first || 'First') + '_' + docKey + '_' + today() + ext;
  }

  /* File names: documents numbered in checklist order, training prefixed T, so a zip sorts in order.
     e.g. "01 - Alex Morgan - CV.pdf", "T03 - Alex Morgan - Safeguarding Adults.pdf". Replacements get (v2), (v3)... */
  var SHORT = [[/^CV/, 'CV'], [/passport or driving/i, 'Passport or driving licence'], [/^Right to work/, 'Right to work'], [/proof of address/i, 'Proof of address'], [/National Insurance/, 'Proof of NI'], [/photograph/i, 'Professional photo'], [/^Enhanced DBS/, 'DBS certificate'], [/^Immunisation/, 'Immunisation record'], [/^NMC Statement/, 'NMC Statement of Entry'], [/diploma/i, 'Nursing diploma or degree']];
  function shortLabel(d) { for (var i = 0; i < SHORT.length; i++) if (SHORT[i][0].test(d)) return SHORT[i][1]; return d.replace(/\s*\(.*\)$/, ''); }
  function fileFor(S, key, isTrain, original, prev) {
    var list = isTrain ? REQS[S.role].train : REQS[S.role].docs, n = list.indexOf(key) + 1;
    var num = (isTrain ? 'T' : '') + String(n).padStart(2, '0');
    var ext = ((original || '').match(/\.[a-z0-9]+$/i) || ['.pdf'])[0].toLowerCase();
    var v = prev && prev.file ? ((prev.v || 1) + 1) : 1;
    return { file: num + ' - ' + S.acct.first + ' ' + S.acct.last + ' - ' + (isTrain ? key.replace(/\s*\(.*\)$/, '') : shortLabel(key)) + (v > 1 ? ' (v' + v + ')' : '') + ext, v: v, at: new Date().toISOString() };
  }

  /* Sample candidate for the portal and staff views when nobody has registered in this browser session */
  function demoCandidate() {
    var role = 'nurse', S = { role: role, ref: 'HCC-001234', step: 7, registered: new Date(Date.now() - 864e5).toISOString(), prescreenDone: new Date(Date.now() - 82e6).toISOString(), submitted: new Date(Date.now() - 8e7).toISOString(),
      acct: { first: 'Alex', last: 'Morgan', email: 'alex.morgan@example.com', phone: '07700 900456', address: '12 Park Lane, Leeds LS12 2EJ' },
      answers: { title: 'Mx', dob: '1992-04-18', ni: 'QQ 12 34 56 C', british: 'No', visa: 'Health and Care Worker visa', share: 'W12 345 678', licence: 'Full', vehicle: 'Yes', dbs: 'update', dbsno: '001234567890', edu: 'Degree', emp: 'Agency work', exp: '3 to 5 years', trainok: 'Some', uniform: 'Yes', size: 'M', r1n: 'Jane Smith', r1p: 'Ward Manager, St Mary\'s Hospital', r1e: 'jane.smith@example.com', r1t: '0113 496 0000', r2n: 'Tom Patel', r2p: 'Deputy Manager, Oak House', r2e: 'tom.patel@example.com', r2t: '0113 496 0001', kn: 'Sam Morgan', kr: 'Sibling', kt: '07700 900789', ka: '4 Hill Road, Leeds LS6 1AA', ban: 'A Morgan', btype: 'Personal', bsc: '00-00-00', bno: '12345678' },
      docs: {}, train: {} };
    REQS[role].docs.forEach(function (d) {
      if (/^NMC PIN/.test(d)) S.docs[d] = { pin: '12A3456E', exp: '2027-08-31' };
      else if (/National Insurance/.test(d)) S.docs[d] = { missing: true };
      else if (!/^Two references/.test(d)) S.docs[d] = fileFor(S, d, false, /photograph/i.test(d) ? 'photo.jpg' : 'scan.pdf');
    });
    REQS[role].train.forEach(function (t, i) { var f = fileFor(S, t, true, 'cert.pdf'); f.exp = '2027-03-01'; S.train[t] = i % 5 === 4 ? { missing: true } : f; });
    return S;
  }

  function initMenu() {
    var toggle = document.getElementById('menuToggle'), mnav = document.getElementById('mobileNav');
    if (!toggle || !mnav) return;
    toggle.addEventListener('click', function () {
      var open = mnav.hidden;
      mnav.hidden = !open;
      toggle.setAttribute('aria-expanded', String(open));
    });
  }

  /* Short application form shared by the job page and the register page */
  function initApply(form, getRole) {
    var drop = form.querySelector('.drop'), file = form.querySelector('input[type=file]'), name = form.querySelector('.file-name');
    if (drop && file) {
      file.addEventListener('change', function () { name.textContent = file.files[0] ? '✓ ' + file.files[0].name : ''; });
      ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('drag'); }); });
      ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('drag'); }); });
      drop.addEventListener('drop', function (e) { var f = e.dataTransfer.files[0]; if (f) name.textContent = '✓ ' + f.name; drop.dataset.dropped = f ? f.name : ''; });
    }
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var err = form.querySelector('.error-msg');
      var first = form.querySelector('[name=first]').value.trim(), last = form.querySelector('[name=last]').value.trim();
      var email = form.querySelector('[name=email]').value.trim(), consent = form.querySelector('[name=consent]');
      var cv = (file && file.files[0]) || (drop && drop.dataset.dropped);
      var missing = [];
      if (!first) missing.push('first name'); if (!last) missing.push('last name'); if (!email) missing.push('email');
      if (!cv) missing.push('CV'); if (consent && !consent.checked) missing.push('consent tick');
      if (missing.length) { err.textContent = 'Please add your ' + missing.join(', ') + '.'; return; }
      err.textContent = '';
      var ref = newRef(), role = getRole();
      try { sessionStorage.setItem('hcc-candidate', JSON.stringify({ ref: ref, first: first, last: last, role: role })); } catch (x) {}
      var out = document.getElementById(form.dataset.success);
      out.innerHTML =
        '<div class="success"><div><div class="tick"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg></div>' +
        '<h2>Thanks ' + esc(first) + ', you\'ve applied</h2><p>Your reference is <b>' + ref + '</b>. We\'ve emailed you the next step.</p>' +
        '<ol><li>Open the email from HCC-Temps</li><li>Upload your documents using the private link</li><li>We check them and call you about shifts</li></ol></div>' +
        '<div><span class="email-tag">Automatic email preview</span><div class="email" style="margin-top:10px"><div class="email-meta"><span>From: <b>HCC-Temps</b> &lt;jobs@hcctemps.co.uk&gt;</span><span>To: ' + esc(email) + '</span><span>Subject: <b>Next step for your ' + esc(role) + ' application (' + ref + ')</b></span></div>' +
        '<div class="email-body"><img class="logo-sm" src="img/logo.png" alt="HCC-Temps" width="74" height="40"><p>Hi ' + esc(first) + ',</p><p>Thanks for applying for <b>' + esc(role) + '</b>. We\'ve got your CV.</p><p>To get you working faster, please upload your documents. The link is private to you and works for 14 days. You can upload some now and come back later.</p>' +
        '<a class="btn btn-aqua" href="upload.html">Upload my documents</a><p style="font-size:13px;color:var(--muted)">Questions? Call 0330 321 3124. Your reference is ' + ref + '.</p></div></div></div></div>';
      form.hidden = true;
      out.hidden = false;
      out.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  /* FAQ tabs: candidates / employers */
  function initFaqTabs() {
    document.querySelectorAll('.faq-tabs').forEach(function (list) {
      var tabs = list.querySelectorAll('[role=tab]');
      function select(t) {
        tabs.forEach(function (o) { var on = o === t; o.setAttribute('aria-selected', String(on)); o.tabIndex = on ? 0 : -1; document.getElementById(o.getAttribute('aria-controls')).hidden = !on; });
      }
      tabs.forEach(function (t, i) {
        t.addEventListener('click', function () { select(t); });
        t.addEventListener('keydown', function (e) { if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { var n = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length]; select(n); n.focus(); } });
      });
    });
  }

  /* Close the Log in dropdown when clicking elsewhere */
  document.addEventListener('click', function (e) {
    document.querySelectorAll('.nav-login[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });
  initMenu();
  return { initFaqTabs: initFaqTabs, JOBS: JOBS, card: card, byId: byId, checklistFor: checklistFor, REQS: REQS, reqKey: reqKey, demoCandidate: demoCandidate, fileFor: fileFor, shortLabel: shortLabel, renamed: renamed, initApply: initApply, esc: esc };
})();
