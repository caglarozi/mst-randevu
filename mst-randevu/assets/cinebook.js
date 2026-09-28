/* CineBook ve MST Çocuk sayfası
 * - Showreel arka planı, perdelik oynatıcı ve filmografi süzgeci
 * - Video kartına dokununca YouTube oynatıcı yüklenir (sayfa açılışında YouTube yüklenmez)
 * - "CineBook / MST Çocuk başvurusu" bağlantıları formda türü seçer
 * - Başvuru formu AJAX ile gönderilir */
(function () {
  'use strict';

  function videolar(kok) {
    (kok || document).querySelectorAll('[data-cb-video]').forEach(function (b) {
      b.addEventListener('click', function () {
        var f = document.createElement('iframe');
        f.src = 'https://www.youtube-nocookie.com/embed/' + b.getAttribute('data-cb-video') + '?autoplay=1&rel=0';
        f.title = b.getAttribute('aria-label') || 'Video';
        f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        f.allowFullscreen = true;
        b.appendChild(f); b.classList.add('is-oynuyor');
        b.removeAttribute('data-cb-video');
      }, { once: true });
    });
  }

  function ytAdres(id, ek) {
    return 'https://www.youtube-nocookie.com/embed/' + id + '?' + ek;
  }

  /* Showreel: fragman varsa arka planda sessiz, döngüde oynar (hareketi azalt açıksa oynamaz) */
  function reel() {
    var s = document.querySelector('[data-cb-reel]');
    if (!s || (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches)) return;
    var id = s.getAttribute('data-cb-reel'), arka = s.querySelector('.cb-reel__arka');
    if (!arka) return;
    var f = document.createElement('iframe');
    f.src = ytAdres(id, 'autoplay=1&mute=1&controls=0&loop=1&playlist=' + id + '&playsinline=1&rel=0&modestbranding=1');
    f.title = ''; f.tabIndex = -1;
    f.setAttribute('aria-hidden', 'true');
    f.allow = 'autoplay; encrypted-media';
    f.addEventListener('load', function () { setTimeout(function () { f.classList.add('is-hazir'); }, 800); });
    arka.appendChild(f);
  }

  /* Perdelik oynatıcı: "Showreel'i izle" ve afişler açar; kapanınca video durur */
  function perdelik() {
    var d = document.getElementById('cb-reel-oynatici');
    if (!d) return;
    var ilk = d.innerHTML;
    function ac(id) {
      if (id) {
        var k = d.querySelector('.cb-video');
        var f = document.createElement('iframe');
        f.src = ytAdres(id, 'autoplay=1&rel=0');
        f.title = 'Video';
        f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        f.allowFullscreen = true;
        var b = document.createElement('div');
        b.className = 'cb-video is-oynuyor';
        b.appendChild(f);
        if (k) k.replaceWith(b); else d.appendChild(b);
      }
      if (typeof d.showModal === 'function') d.showModal(); else d.setAttribute('open', '');
    }
    function kapat() { if (d.close) d.close(); else d.removeAttribute('open'); }
    document.querySelectorAll('[data-cb-ac]').forEach(function (b) {
      b.addEventListener('click', function () { ac(null); });
    });
    document.querySelectorAll('[data-cb-video-ac]').forEach(function (b) {
      b.addEventListener('click', function () { ac(b.getAttribute('data-cb-video-ac')); });
    });
    d.addEventListener('click', function (e) {
      if (e.target === d || e.target.closest('[data-cb-kapat]')) kapat();
    });
    d.addEventListener('close', function () { d.innerHTML = ilk; videolar(d); });
  }

  /* Filmografi süzgeci */
  function suzgec() {
    var btn = document.querySelectorAll('[data-cb-suz]');
    btn.forEach(function (b) {
      b.addEventListener('click', function () {
        var g = b.getAttribute('data-cb-suz');
        btn.forEach(function (x) { x.classList.toggle('is-secili', x === b); x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
        document.querySelectorAll('.cb-filmografi [data-cb-grup]').forEach(function (li) {
          var lg = li.getAttribute('data-cb-grup');
          li.hidden = !(g === 'hepsi' || lg === 'hepsi' || lg === g);
        });
      });
    });
  }

  function turSec(tur) {
    var r = document.querySelector('.cb-form input[name="tur"][value="' + tur + '"]');
    if (r) r.checked = true;
  }

  function turBaglantilari() {
    document.querySelectorAll('[data-cb-tur]').forEach(function (a) {
      a.addEventListener('click', function () { turSec(a.getAttribute('data-cb-tur')); });
    });
    var m = /[?&]tur=(cinebook|cocuk)/.exec(location.search);
    if (m) turSec(m[1]);
  }

  function form() {
    var f = document.querySelector('[data-cb-form]');
    if (!f || !window.MST_CB) return;
    var hata = f.querySelector('[data-cb-hata]'), tamam = f.querySelector('[data-cb-tamam]'), btn = f.querySelector('.cb-form__gonder');
    function goster(m, alan) {
      hata.textContent = m; hata.hidden = false;
      f.querySelectorAll('.is-hata').forEach(function (e) { e.classList.remove('is-hata'); });
      var el = alan && f.querySelector('[name="' + alan + '"]');
      if (el) { var l = el.closest('.cb-alan'); if (l) l.classList.add('is-hata'); el.focus(); }
    }
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      hata.hidden = true;
      var fd = new FormData(f);
      fd.append('action', 'mst_cinebook_basvuru');
      fd.append('nonce', MST_CB.nonce);
      btn.disabled = true; btn.textContent = 'Gönderiliyor…';
      fetch(MST_CB.ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (j && j.success) {
            tamam.innerHTML = '<i>✓</i><strong>Başvurunuz alındı</strong><p></p>';
            tamam.querySelector('p').textContent = j.data.mesaj;
            tamam.hidden = false; f.classList.add('is-tamam');
            f.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } else {
            goster((j && j.data && j.data.mesaj) || 'Başvuru gönderilemedi, lütfen tekrar deneyin.', j && j.data && j.data.alan);
          }
        })
        .catch(function () { goster('Bağlantı hatası. Lütfen tekrar deneyin.'); })
        .then(function () { btn.disabled = false; btn.textContent = 'Başvuruyu Gönder'; });
    });
  }

  function basla() { videolar(); reel(); perdelik(); suzgec(); turBaglantilari(); form(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
})();
