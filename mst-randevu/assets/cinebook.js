/* CineBook ve MST Çocuk sayfası
 * - Video kartına dokununca YouTube oynatıcı yüklenir (sayfa açılışında YouTube yüklenmez)
 * - "CineBook / MST Çocuk başvurusu" bağlantıları formda türü seçer
 * - Başvuru formu AJAX ile gönderilir */
(function () {
  'use strict';

  function videolar() {
    document.querySelectorAll('[data-cb-video]').forEach(function (b) {
      b.addEventListener('click', function () {
        var f = document.createElement('iframe');
        f.src = 'https://www.youtube-nocookie.com/embed/' + b.getAttribute('data-cb-video') + '?autoplay=1&rel=0';
        f.title = b.getAttribute('aria-label') || 'Video';
        f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        f.allowFullscreen = true;
        b.appendChild(f);
        b.removeAttribute('data-cb-video');
      }, { once: true });
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

  function basla() { videolar(); turBaglantilari(); form(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
})();
