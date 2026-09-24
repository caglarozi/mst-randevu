(function () {
  'use strict';
  var CFG = window.MST_RANDEVU || {};

  function el(tag, cls, text) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (text != null) e.textContent = text;
    return e;
  }

  function post(data) {
    return fetch(CFG.ajax, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { success: false, data: { mesaj: 'Sunucu yanıtı okunamadı.' } }; }); });
  }

  /* ---------------- Randevu kutusu ---------------- */
  function init(root) {
    var $ = function (s) { return root.querySelector(s); };
    var daysBox = $('[data-days]'), timesBox = $('[data-times]'), timesLabel = $('[data-times-label]');
    var step = $('[data-step="saat"]'), form = $('[data-form]'), picked = $('[data-picked]');
    var nextBar = $('[data-next]'), nextText = $('[data-next-text]');
    var errBox = $('[data-error]'), done = $('[data-done]');
    var steps = root.querySelectorAll('[data-steps] li');
    var nonce = '', byDay = {}, order = [], activeDay = null, chosen = null;

    function setStep(n) {
      steps.forEach(function (li, i) {
        li.classList.toggle('is-active', i === n - 1);
        li.classList.toggle('is-done', i < n - 1);
      });
    }

    function showError(msg) { errBox.textContent = msg; errBox.hidden = !msg; }

    function slotText(s) { return s.etiket + ' · ' + s.saat; }

    function load(flashMsg) {
      daysBox.innerHTML = '';
      daysBox.appendChild(el('div', 'mst-rnd__loading', 'Müsait saatler yükleniyor…'));
      timesBox.innerHTML = ''; timesLabel.hidden = true; nextBar.hidden = true; chosen = null;
      var fd = new FormData(); fd.append('action', 'mst_randevu_slotlar');
      post(fd).then(function (res) {
        if (!res.success) throw new Error();
        nonce = res.data.nonce; byDay = {}; order = [];
        res.data.slotlar.forEach(function (s) {
          if (!byDay[s.gun]) { byDay[s.gun] = []; order.push(s.gun); }
          byDay[s.gun].push(s);
        });
        renderDays();
        if (flashMsg) flash(flashMsg);
      }).catch(function () {
        daysBox.innerHTML = '';
        daysBox.appendChild(el('div', 'mst-rnd__empty', 'Saatler yüklenemedi. Sayfayı yenileyip tekrar deneyin.'));
      });
    }

    function flash(msg) {
      var n = el('div', 'mst-rnd__error', msg);
      step.insertBefore(n, step.firstChild);
      setTimeout(function () { n.remove(); }, 6000);
    }

    function renderDays() {
      daysBox.innerHTML = '';
      if (!order.length) {
        daysBox.appendChild(el('div', 'mst-rnd__empty', 'Şu an müsait saat bulunmuyor. Lütfen daha sonra tekrar kontrol edin ya da WhatsApp’tan bize yazın.'));
        return;
      }
      order.forEach(function (gun) {
        var f = byDay[gun][0];
        var b = el('button', 'mst-rnd__day');
        b.type = 'button';
        b.dataset.gun = gun;
        b.setAttribute('aria-pressed', 'false');
        b.setAttribute('aria-label', f.etiket + ', ' + byDay[gun].length + ' müsait saat');
        b.appendChild(el('span', 'mst-rnd__day-name', f.kisa));
        b.appendChild(el('span', 'mst-rnd__day-no', f.no));
        b.appendChild(el('span', 'mst-rnd__day-count', byDay[gun].length + ' saat'));
        b.addEventListener('click', function () { selectDay(gun); });
        daysBox.appendChild(b);
      });
      selectDay(activeDay && byDay[activeDay] ? activeDay : order[0]);
    }

    function selectDay(gun) {
      activeDay = gun;
      daysBox.querySelectorAll('.mst-rnd__day').forEach(function (b) {
        b.setAttribute('aria-pressed', b.dataset.gun === gun ? 'true' : 'false');
      });
      timesLabel.hidden = false;
      timesBox.innerHTML = '';
      byDay[gun].forEach(function (s) {
        var b = el('button', 'mst-rnd__time', s.saat);
        b.type = 'button';
        // Son yer kaldıysa kırmızı "Dolmak üzere" uyarısı
        if (s.kalan === 1) {
          b.classList.add('is-az');
          b.appendChild(el('small', null, 'Dolmak üzere'));
        }
        b.setAttribute('aria-pressed', chosen && chosen.id === s.id ? 'true' : 'false');
        b.addEventListener('click', function () { chooseSlot(s, b); });
        timesBox.appendChild(b);
      });
    }

    function chooseSlot(s, btn) {
      chosen = s;
      timesBox.querySelectorAll('.mst-rnd__time').forEach(function (b) { b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'); });
      nextText.textContent = slotText(s);
      nextBar.hidden = false;
    }

    function openForm() {
      if (!chosen) return;
      form.slot_id.value = chosen.id;
      picked.innerHTML = '';
      var info = el('div');
      info.appendChild(el('small', null, 'Seçilen saat · ' + chosen.sure + ' dk'));
      info.appendChild(el('strong', null, slotText(chosen)));
      var change = el('button', null, 'Değiştir');
      change.type = 'button';
      change.addEventListener('click', backToTimes);
      picked.appendChild(info); picked.appendChild(change);
      step.hidden = true; nextBar.hidden = true; form.hidden = false; showError('');
      setStep(2);
      scrollToTop();
      setTimeout(function () { form.ad_soyad.focus({ preventScroll: true }); }, 250);
    }

    function backToTimes() {
      form.hidden = true; step.hidden = false; showError('');
      nextBar.hidden = !chosen;
      setStep(1);
    }

    function scrollToTop() {
      var top = root.getBoundingClientRect().top + window.pageYOffset - 90;
      if (window.pageYOffset > top) window.scrollTo({ top: top, behavior: 'smooth' });
    }

    $('[data-continue]').addEventListener('click', openForm);
    $('[data-back]').addEventListener('click', backToTimes);

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      showError('');
      if (form.ad_soyad.value.trim().length < 3) { showError('Lütfen adınızı ve soyadınızı yazın.'); form.ad_soyad.focus(); return; }
      if (form.telefon.value.replace(/\D/g, '').length < 10) { showError('Geçerli bir telefon numarası girin.'); form.telefon.focus(); return; }
      if (!form.kvkk.checked) { showError('Devam etmek için onay kutusunu işaretleyin.'); return; }

      var btn = form.querySelector('[type="submit"]');
      btn.disabled = true; btn.dataset.t = btn.textContent; btn.textContent = 'Gönderiliyor…';

      var fd = new FormData(form);
      fd.append('action', 'mst_randevu_al');
      fd.append('nonce', nonce);

      post(fd).then(function (res) {
        if (res.success) { showDone(res.data); return; }
        var d = res.data || {};
        showError(d.mesaj || 'Bir hata oluştu, lütfen tekrar deneyin.');
        if (d.alan && form[d.alan] && form[d.alan].focus) form[d.alan].focus();
        if (d.yenile) { backToTimes(); load(d.mesaj); }
      }).catch(function () {
        showError('Bağlantı hatası. Lütfen tekrar deneyin.');
      }).finally(function () {
        btn.disabled = false; btn.textContent = btn.dataset.t;
      });
    });

    function showDone(d) {
      form.hidden = true;
      setStep(3);
      steps.forEach(function (li) { li.classList.add('is-done'); li.classList.remove('is-active'); });
      done.innerHTML = '';
      done.appendChild(el('div', 'mst-rnd__done-icon', '✓'));
      done.appendChild(el('p', 'mst-rnd__done-msg', d.mesaj || CFG.basari));
      done.appendChild(el('p', 'mst-rnd__done-date', d.tarih));
      var acts = el('div', 'mst-rnd__done-actions');
      if (d.takvim) {
        var cal = el('a', 'mst-rnd__btn mst-rnd__btn--ghost', 'Takvime Ekle');
        cal.href = d.takvim; cal.target = '_blank'; cal.rel = 'noopener';
        acts.appendChild(cal);
      }
      var site = el('a', 'mst-rnd__btn mst-rnd__btn--yesil', 'Siteye Dön');
      site.href = CFG.site || '/';
      acts.appendChild(site);
      done.appendChild(acts);
      done.hidden = false;
      scrollToTop();
    }

    load();
  }

  function boot() {
    document.querySelectorAll('[data-mst-randevu]').forEach(init);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
