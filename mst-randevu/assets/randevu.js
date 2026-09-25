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
    var weeks = [], weekOf = {}, activeWeek = 0;

    // Mobilde günler haftalık gösterilir: ‹ 29 Eyl – 3 Eki ›  (masaüstünde gizli, tüm günler görünür)
    var weekNav = el('div', 'mst-rnd__week');
    var prevW = el('button', 'mst-rnd__week-btn', '‹'), nextW = el('button', 'mst-rnd__week-btn', '›');
    var weekLabel = el('span', 'mst-rnd__week-label');
    prevW.type = nextW.type = 'button';
    prevW.setAttribute('aria-label', 'Önceki hafta');
    nextW.setAttribute('aria-label', 'Sonraki hafta');
    weekLabel.setAttribute('aria-live', 'polite');
    weekNav.appendChild(prevW); weekNav.appendChild(weekLabel); weekNav.appendChild(nextW);
    weekNav.hidden = true;
    daysBox.parentNode.insertBefore(weekNav, daysBox);
    prevW.addEventListener('click', function () { goWeek(activeWeek - 1); });
    nextW.addEventListener('click', function () { goWeek(activeWeek + 1); });

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
      timesBox.innerHTML = ''; timesLabel.hidden = true; nextBar.hidden = true; weekNav.hidden = true; chosen = null;
      var fd = new FormData(); fd.append('action', 'mst_randevu_slotlar');
      fd.append('tur', root.getAttribute('data-tur') || 'yazar'); // yazar adayı / akademi: ayrı saatler
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

    /** 'YYYY-MM-DD' gününün ait olduğu haftanın pazartesisi (hafta anahtarı) */
    function monday(gun) {
      var p = gun.split('-'), d = new Date(+p[0], +p[1] - 1, +p[2]);
      d.setDate(d.getDate() - (d.getDay() + 6) % 7);
      return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
    }

    function goWeek(i) {
      if (i < 0 || i >= weeks.length) return;
      activeWeek = i;
      var w = weeks[i];
      daysBox.querySelectorAll('.mst-rnd__day').forEach(function (b) {
        b.classList.toggle('is-diger-hafta', weekOf[b.dataset.gun] !== i);
      });
      var ilk = byDay[w[0]][0], son = byDay[w[w.length - 1]][0];
      weekLabel.textContent = w.length > 1 ? ilk.no + ' – ' + son.no : ilk.no;
      prevW.disabled = i === 0;
      nextW.disabled = i === weeks.length - 1;
      // Hafta değişince o haftanın ilk müsait gününü seç
      if (weekOf[activeDay] !== i) selectDay(w.filter(musait)[0] || w[0]);
    }

    function renderDays() {
      daysBox.innerHTML = '';
      weeks = []; weekOf = {};
      if (!order.length) {
        weekNav.hidden = true;
        daysBox.appendChild(el('div', 'mst-rnd__empty', 'Şu an müsait saat bulunmuyor. Lütfen daha sonra tekrar kontrol edin ya da WhatsApp’tan bize yazın.'));
        return;
      }
      var sonHafta = null;
      order.forEach(function (gun) {
        var h = monday(gun);
        if (h !== sonHafta) { weeks.push([]); sonHafta = h; }
        weeks[weeks.length - 1].push(gun);
        weekOf[gun] = weeks.length - 1;
      });
      weekNav.hidden = false;
      // Mobil ızgaranın sütun sayısı en kalabalık haftaya göre; tek günlük hafta tüm satırı kaplamasın
      daysBox.style.setProperty('--mst-gun', Math.max(5, Math.max.apply(null, weeks.map(function (w) { return w.length; }))));
      order.forEach(function (gun) {
        var f = byDay[gun][0], bos = musait(gun);
        var b = el('button', 'mst-rnd__day' + (bos ? '' : ' is-dolu'));
        b.type = 'button';
        b.dataset.gun = gun;
        b.setAttribute('aria-pressed', 'false');
        b.setAttribute('aria-label', f.etiket + ', ' + (bos ? bos + ' müsait saat' : 'tüm saatler dolu'));
        b.appendChild(el('span', 'mst-rnd__day-name', f.kisa));
        // "28 Eyl": masaüstünde tek satır, mobilde gün numarası büyük ve ay altında
        var no = el('span', 'mst-rnd__day-no'), bol = f.no.indexOf(' ');
        no.appendChild(document.createTextNode(bol > 0 ? f.no.slice(0, bol) : f.no));
        if (bol > 0) no.appendChild(el('span', 'mst-rnd__day-ay', ' ' + f.no.slice(bol + 1)));
        b.appendChild(no);
        b.appendChild(el('span', 'mst-rnd__day-count', bos ? bos + ' saat' : 'Dolu'));
        b.addEventListener('click', function () { selectDay(gun); });
        daysBox.appendChild(b);
      });
      var ilk = order.filter(musait)[0] || order[0];
      selectDay(activeDay && byDay[activeDay] && musait(activeDay) ? activeDay : ilk);
      goWeek(weekOf[activeDay]);
    }

    function musait(gun) {
      return byDay[gun].filter(function (s) { return s.kalan > 0; }).length;
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
        // Dolu saat kırmızı ve seçilemez; son yer kaldıysa turuncu "Dolmak üzere"
        if (s.kalan < 1) {
          b.classList.add('is-dolu');
          b.disabled = true;
          b.setAttribute('aria-label', s.saat + ', dolu');
          b.appendChild(el('small', null, 'Dolu'));
          timesBox.appendChild(b);
          return;
        }
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
      info.appendChild(el('small', null, 'Seçilen saat'));
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
      var site = el('a', 'mst-rnd__btn mst-rnd__btn--koyu', 'Siteye Dön');
      site.href = CFG.site || '/';
      acts.appendChild(site);
      done.appendChild(acts);
      // Görüşmeyi beklerken Yazar Kariyer Akademisi'ni tanıtan kart
      if (CFG.akademi) {
        var ak = el('a', 'mst-rnd__akademi');
        ak.href = CFG.akademi;
        ak.appendChild(el('small', '', 'Görüşmeyi beklerken'));
        ak.appendChild(el('strong', '', 'Yazar Kariyer Akademisi\'ni inceleyin'));
        ak.appendChild(el('span', 'mst-rnd__akademi-ok', '→'));
        done.appendChild(ak);
      }
      done.hidden = false;
      scrollToTop();
    }

    load();
  }

  /* ---------------- Üst çubuk: mobil menü ---------------- */
  function initMenu(top) {
    var btn = top.querySelector('.mst-top__menu'), menu = top.querySelector('.mst-top__cta');
    if (!btn || !menu) return;
    function set(open) {
      top.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
    }
    btn.addEventListener('click', function () { set(!top.classList.contains('is-open')); });
    menu.addEventListener('click', function (e) { if (e.target.closest('a')) set(false); });
    document.addEventListener('click', function (e) { if (!top.contains(e.target)) set(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && top.classList.contains('is-open')) { set(false); btn.focus(); } });
  }

  function boot() {
    document.querySelectorAll('.mst-top').forEach(initMenu);
    document.querySelectorAll('[data-mst-randevu]').forEach(init);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
