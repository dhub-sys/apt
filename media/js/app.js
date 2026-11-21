/* Μικρές αλληλεπιδράσεις της εφαρμογής (χωρίς εξωτερικές βιβλιοθήκες). */
(function () {
  'use strict';
  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  function num(v) { v = String(v || '').trim().replace(/\s|€/g, ''); if (v.indexOf(',') >= 0) v = v.replace(/\./g, '').replace(',', '.'); var n = parseFloat(v); return isNaN(n) ? 0 : n; }
  function fmtP(n) { return n.toLocaleString('el-GR', { minimumFractionDigits: 2, maximumFractionDigits: 4 }); }
  function fmt(n, d) { return n.toLocaleString('el-GR', { minimumFractionDigits: d == null ? 2 : 0, maximumFractionDigits: d == null ? 2 : d }); }

  // Burger του sidebar: μάζεμα σε εικονίδια, με μνήμη ανά browser.
  var sbBtn = $('.kx-sb-toggle');
  if (sbBtn) {
    var rootEl = document.documentElement;
    var sbSync = function () { var c = rootEl.classList.contains('kx-sb-collapsed'); sbBtn.setAttribute('aria-expanded', c ? 'false' : 'true'); sbBtn.setAttribute('aria-label', c ? 'Ανάπτυξη μενού' : 'Σύμπτυξη μενού'); };
    sbBtn.addEventListener('click', function () {
      rootEl.classList.add('kx-sb-anim');
      var c = rootEl.classList.toggle('kx-sb-collapsed');
      try { localStorage.setItem('kxSidebar', c ? 'collapsed' : 'expanded'); } catch (x) {}
      sbSync();
    });
    sbSync();
  }

  // Πάνελ που ανοίγει/κλείνει από κουμπί (π.χ. «Προβολή εξαιρέσεων»).
  $$('[data-toggle-panel]').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = $(b.getAttribute('data-toggle-panel'));
      if (!t) return;
      t.hidden = !t.hidden;
      b.setAttribute('aria-expanded', t.hidden ? 'false' : 'true');
    });
  });

  // Εξαίρεση ιδιοκτησίας από τη θέρμανση: ζητά λόγο και στέλνει τη φόρμα.
  var exForm = $('#kx-heat-exclude-form');
  $$('[data-heat-exclude]').forEach(function (b) {
    b.addEventListener('click', function () {
      var reason = window.prompt('Λόγος εξαίρεσης της ιδιοκτησίας ' + b.getAttribute('data-code') + ' από τη θέρμανση (π.χ. αυτόνομος λέβητας αερίου):', '');
      if (reason === null || !exForm) return;
      exForm.querySelector('[name=unit_id]').value = b.getAttribute('data-heat-exclude');
      exForm.querySelector('[name=reason]').value = reason;
      exForm.submit();
    });
  });

  // Πίνακας ιδιοκτησιών: το πεδίο «λόγος εξαίρεσης» εμφανίζεται μόνο όταν τσεκαριστεί η εξαίρεση.
  document.addEventListener('change', function (e) {
    if (e.target.hasAttribute && e.target.hasAttribute('data-excl-toggle')) {
      var cell = e.target.closest('.kx-excl-cell');
      cell.classList.toggle('is-on', e.target.checked);
      if (e.target.checked) { var r = cell.querySelector('.kx-excl-reason'); if (r) r.focus(); }
    }
  });

  // «Όλες» / «Καμία» σε λίστες επιλογής ιδιοκτησιών.
  document.addEventListener('click', function (e) {
    var all = e.target.closest('[data-check-all]'), none = e.target.closest('[data-check-none]');
    var t = all ? $(all.getAttribute('data-check-all')) : none ? $(none.getAttribute('data-check-none')) : null;
    if (t) $$('input[type=checkbox][name="unit_ids[]"]', t).forEach(function (c) { c.checked = !!all; });
  });

  // Κινητό: άνοιγμα/κλείσιμο μενού (συρτάρι).
  var root = document.documentElement, openBtn = $('[data-drawer-open]');
  var setDrawer = function (open) {
    root.classList.toggle('kx-drawer-open', open);
    if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) { var f = $('#kx-side .kx-nav-link'); if (f) f.focus(); } else if (openBtn) openBtn.focus();
  };
  if (openBtn) openBtn.addEventListener('click', function () { setDrawer(true); });
  $$('[data-drawer-close]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(false); }); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && root.classList.contains('kx-drawer-open')) setDrawer(false); });

  // Καρτέλες: η ενεργή φαίνεται πάντα (σε στενή οθόνη κυλούν οριζόντια).
  $$('.kx-tabs').forEach(function (t) {
    var a = $('.is-active', t);
    if (a && t.scrollWidth > t.clientWidth) t.scrollLeft = a.offsetLeft - (t.clientWidth - a.offsetWidth) / 2;
  });

  // Προεπισκόπηση εκτύπωσης: η σελίδα A4 χωράει στο πλάτος της οθόνης (μόνο στην οθόνη).
  var pages = $('.kx-pages');
  if (pages && $('.kx-page', pages)) {
    var fit = function () {
      var w = $('.kx-page', pages).offsetWidth;
      var vw = Math.min(window.innerWidth, (window.screen && screen.width) || window.innerWidth);
      var z = Math.min(1, (vw - 16) / (w + 8));
      pages.style.setProperty('--kx-pv-zoom', z.toFixed(3));
    };
    fit();
    window.addEventListener('resize', fit);
  }

  // Κλείσιμο μηνυμάτων.
  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('kx-alert-x')) e.target.parentNode.remove();
  });

  // Επιβεβαίωση πριν από διαγραφές κ.λπ.
  document.addEventListener('submit', function (e) {
    var f = e.target, msg = f.getAttribute && f.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  }, true);

  // Εναλλαγή κλάσης (π.χ. εμφάνιση στηλών επικοινωνίας), με μνήμη ανά browser.
  $$('[data-toggle-class]').forEach(function (c) {
    var cls = c.getAttribute('data-toggle-class'), t = $(c.getAttribute('data-target'));
    var key = 'kx.' + cls;
    try { c.checked = localStorage.getItem(key) === '1'; } catch (x) {}
    var apply = function () { if (t) t.classList.toggle(cls, c.checked); try { localStorage.setItem(key, c.checked ? '1' : '0'); } catch (x) {} };
    c.addEventListener('change', apply);
    apply();
  });

  // Πίνακας ιδιοκτησιών: νέες γραμμές και ζωντανά σύνολα χιλιοστών.
  var rowCounter = 0;
  $$('[data-add-row]').forEach(function (b) {
    b.addEventListener('click', function () {
      var table = $(b.getAttribute('data-add-row')), tpl = $(b.getAttribute('data-template'));
      var n = +(b.getAttribute('data-count') || 1), first = null;
      for (var i = 0; i < n; i++) {
        rowCounter++;
        var html = tpl.innerHTML.replace(/__N__/g, 'n' + Date.now() + rowCounter);
        var tb = table.tBodies[0];
        tb.insertAdjacentHTML('beforeend', html);
        var row = tb.lastElementChild;
        row.classList.remove('kx-tpl-row');
        if (!first) first = row;
      }
      if (first) { var inp = first.querySelector('input'); if (inp) inp.focus(); }
      sums();
    });
  });
  function sums() {
    $$('[data-sum]').forEach(function (el) {
      var k = el.getAttribute('data-sum'), s = 0;
      $$('.kx-mill[data-key="' + k + '"]').forEach(function (i) {
        var tr = i.closest('tr'), del = tr && tr.querySelector('input[name$="[_delete]"]');
        if (!del || !del.checked) s += num(i.value);
      });
      el.textContent = fmt(s, 3);
      el.classList.toggle('is-ok', Math.abs(s - 1000) < 0.001);
      el.classList.toggle('is-off', Math.abs(s - 1000) >= 0.001);
    });
  }
  document.addEventListener('input', function (e) { if (e.target.classList && e.target.classList.contains('kx-mill')) sums(); });
  document.addEventListener('change', function (e) {
    if (e.target.name && /\[_delete\]$/.test(e.target.name)) { e.target.closest('tr').classList.toggle('is-deleted', e.target.checked); sums(); }
  });
  // Enter στο φύλλο ιδιοκτησιών: πήγαινε στο ίδιο κελί της επόμενης γραμμής (όπως στο Excel).
  var sheet = $('#kx-units');
  if (sheet) sheet.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' || e.target.tagName !== 'INPUT') return;
    e.preventDefault();
    var td = e.target.closest('td'), tr = td.parentNode, idx = Array.prototype.indexOf.call(tr.children, td);
    var next = tr.nextElementSibling;
    var inp = next && next.children[idx] && next.children[idx].querySelector('input:not([type=hidden]),select');
    if (inp) inp.focus();
  });

  // Ενδείξεις θέρμανσης: ζωντανή κατανάλωση (κενή τρέχουσα = καμία κατανάλωση).
  var readings = $('[data-readings]');
  if (readings) readings.addEventListener('input', function (e) {
    var tr = e.target.closest('tr'), ins = tr.querySelectorAll('input');
    if (ins.length < 2) return;
    if (ins[1].value.trim() === '') { $('[data-diff]', tr).textContent = ''; return; }
    var d = num(ins[1].value) - num(ins[0].value);
    $('[data-diff]', tr).textContent = d >= 0 ? fmt(d, 2) : '⚠ ' + fmt(d, 2);
  });

  // Καύσιμο: υπόλοιπο έναρξης + αγορές, μέτρηση, κατανάλωση με FIFO (ίδιος κανόνας με τον server).
  var fuel = $('[data-fuel]');
  if (fuel) {
    var unit = fuel.getAttribute('data-unit') || 'lt';
    var r2 = function (v) { return Math.round(v * 100) / 100; };
    var calcFuel = function () {
      var layers = [], total = 0, value = 0;
      ['stock', 'buy'].forEach(function (k) {
        $$('[data-rows="' + k + '"] tr', fuel).forEach(function (tr) {
          var q = num($('[data-q]', tr).value), p = num($('[data-p]', tr).value), v = r2(q * p);
          $('[data-v]', tr).textContent = q > 0 ? fmt(v) : '';
          if (q > 0) { layers.push([q, p]); total += q; value += v; }
        });
      });
      $('[data-total-q]', fuel).textContent = fmt(total, 3) + ' ' + unit;
      $('[data-total-v]', fuel).textContent = fmt(r2(value)) + ' €';
      var endIn = fuel.querySelector('[name=end_qty]'), out = $('[data-cons]', fuel), bd = $('[data-breakdown]', fuel), rem = $('[data-remain]', fuel), ev = $('[data-end-v]', fuel);
      if (endIn.value.trim() === '') { out.textContent = '—'; bd.innerHTML = ''; rem.textContent = ''; ev.value = ''; return; }
      var end = num(endIn.value);
      if (end > total + 0.0005) { out.textContent = '⚠ Το υπόλοιπο ξεπερνά το σύνολο'; bd.innerHTML = ''; rem.textContent = ''; ev.value = ''; return; }
      var left = Math.round((total - end) * 1000) / 1000, cost = 0, lines = [], remain = [], remV = 0;
      layers.forEach(function (l) {
        var take = Math.min(l[0], left);
        if (take > 0) { var a = r2(take * l[1]); cost += a; lines.push(fmt(take, 3) + ' ' + unit + ' × ' + fmtP(l[1]) + ' € = ' + fmt(a) + ' €'); left = Math.round((left - take) * 1000) / 1000; }
        if (l[0] - take > 0.0005) { remain.push(fmt(l[0] - take, 3) + ' ' + unit + ' × ' + fmtP(l[1]) + ' €'); remV += r2((l[0] - take) * l[1]); }
      });
      out.textContent = fmt(total - end, 3) + ' ' + unit + ' — ' + fmt(r2(cost)) + ' €';
      bd.innerHTML = lines.map(function (t) { return '<li>' + t + '</li>'; }).join('');
      rem.textContent = remain.length ? 'Μένει για τον επόμενο μήνα: ' + remain.join(' + ') : '';
      ev.value = fmt(r2(remV));
    };
    fuel.addEventListener('input', calcFuel);
    fuel.addEventListener('click', function (e) {
      var add = e.target.closest('[data-add-fuel]'), del = e.target.closest('[data-remove-row]');
      if (add) {
        var k = add.getAttribute('data-add-fuel');
        $('[data-rows="' + k + '"]', fuel).insertAdjacentHTML('beforeend', $('template[data-tpl="' + k + '"]', fuel).innerHTML);
        var rows = $$('[data-rows="' + k + '"] tr', fuel), inp = rows[rows.length - 1].querySelector('input');
        if (inp) inp.focus();
      }
      if (del) {
        var tb = del.closest('tbody');
        tb.removeChild(del.closest('tr'));
        if (!tb.children.length) tb.insertAdjacentHTML('beforeend', $('template[data-tpl="' + tb.getAttribute('data-rows') + '"]', fuel).innerHTML);
        calcFuel();
      }
    });
    calcFuel();
  }

  // Φόρμα δαπάνης.
  var ef = $('[data-expense-form]');
  if (ef) {
    var keymap = JSON.parse(ef.getAttribute('data-keymap') || '{}');
    var method = $('[data-method]', ef), key = $('[data-key]', ef), cat = $('[data-category]', ef);
    var custom = $('[data-custom]', ef), subset = $('[data-subset]', ef), scope = $('[data-scope]', ef), scopeWrap = $('[data-scope-wrap]', ef);
    var sync = function () {
      var m = method.value;
      key.closest('label').hidden = !(m === 'key' || m === 'heating');
      custom.hidden = m !== 'custom';
      scopeWrap.hidden = m === 'custom';
      subset.hidden = m === 'custom' || scope.value !== 'custom';
      var n = $$('input[name="unit_ids[]"]:checked', ef).length;
      $('[data-subset-count]', ef).textContent = n ? '(' + n + ' επιλεγμένες)' : '';
    };
    cat.addEventListener('change', function () {
      if (keymap[cat.value]) key.value = keymap[cat.value];
      if (cat.value === 'heating' && method.value === 'key' && method.querySelector('option[value=heating]')) method.value = 'heating';
      if (cat.value !== 'heating' && method.value === 'heating') method.value = 'key';
      sync();
    });
    method.addEventListener('change', sync);
    scope.addEventListener('change', function () { sync(); if (scope.value === 'custom') subset.open = true; });
    ef.addEventListener('change', function (e) { if (e.target.name === 'unit_ids[]') sync(); });
    // Αυτόματη κατηγορία από γνωστές περιγραφές.
    $('[name=title]', ef).addEventListener('change', function () {
      var t = this.value.toLowerCase();
      var c = /ανελκ|ασανσ/.test(t) ? 'elevator' : /πετρέλ|καυσ|θέρμαν|καυστ|φυσικό αέριο/.test(t) ? 'heating' : null;
      if (c && cat.value === 'general' && ef.querySelector('[name=expense_id]').value === '0') { cat.value = c; cat.dispatchEvent(new Event('change')); }
      if (/αποθεματικ/.test(t)) ef.querySelector('[name=payer]').value = 'owner';
    });
    var title = $('[data-expense-title]'), reset = $('[data-expense-reset]'), submit = $('[data-expense-submit]', ef);
    var clear = function () {
      ef.reset();
      ef.querySelector('[name=expense_id]').value = '0';
      title.textContent = 'Νέα δαπάνη';
      submit.textContent = 'Προσθήκη δαπάνης';
      reset.hidden = true;
      scope.value = 'all';
      sync();
    };
    reset.addEventListener('click', clear);
    $$('[data-edit-expense]').forEach(function (b) {
      b.addEventListener('click', function () {
        var x = JSON.parse(b.getAttribute('data-edit-expense'));
        clear();
        ef.querySelector('[name=expense_id]').value = x.id;
        ['title', 'amount', 'category', 'method', 'key_id', 'payer', 'supplier', 'doc_no', 'doc_date'].forEach(function (k) {
          var el = ef.querySelector('[name=' + k + ']');
          if (el && x[k] != null) el.value = x[k];
        });
        ef.querySelector('[name=recurring]').checked = !!x.recurring;
        $$('input[name="unit_ids[]"]', ef).forEach(function (c) { c.checked = x.unit_ids.indexOf(+c.value) >= 0; });
        scope.value = x.group_id ? 'g:' + x.group_id : (x.unit_ids.length ? 'custom' : 'all');
        if (scope.value === '' || scope.selectedIndex < 0) scope.value = 'all';
        Object.keys(x.custom || {}).forEach(function (u) { var el = ef.querySelector('[name="custom[' + u + ']"]'); if (el) el.value = x.custom[u]; });
        title.textContent = 'Επεξεργασία: ' + x.title;
        submit.textContent = 'Αποθήκευση αλλαγών';
        reset.hidden = false;
        sync();
        $('#expense-form').scrollIntoView({ behavior: 'smooth' });
      });
    });
    sync();
  }

  // Επιλογή τύπου εκτύπωσης.
  $$('[data-print-type]').forEach(function (r) {
    r.addEventListener('change', function () {
      $$('[data-for-type]').forEach(function (el) { el.hidden = el.getAttribute('data-for-type') !== r.value; });
    });
  });
})();
