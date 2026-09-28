/* CineBook ve MST Çocuk sayfası
 * - Perde arka planı, zaman kodu, akan jenerik, klaketler, perdelik oynatıcı ve filmografi süzgeci
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

  /* Perdelik oynatıcı: "Şimdi izle" ve afişler aynı pencerede açar; kapanınca video durur, odak geri döner */
  function perdelik() {
    var d = document.getElementById('cb-perdelik');
    if (!d) return;
    var yer = d.querySelector('.cb-perdelik__yer'), donus = null;
    function ac(id, baslik, kaynak) {
      var ig = id.indexOf('ig:') === 0, f = document.createElement('iframe');
      f.src = ig ? 'https://www.instagram.com/p/' + encodeURIComponent(id.slice(3)) + '/embed/' : ytAdres(id, 'autoplay=1&rel=0&playsinline=1');
      f.title = baslik || 'Video';
      f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
      f.allowFullscreen = true;
      var k = document.createElement('div');
      k.className = 'cb-video is-oynuyor' + (ig ? ' cb-video--dikey' : '');
      d.classList.toggle('cb-perdelik--dikey', ig);
      k.appendChild(f);
      yer.innerHTML = ''; yer.appendChild(k);
      d.setAttribute('aria-label', baslik || 'Video');
      donus = kaynak;
      if (typeof d.showModal === 'function') d.showModal(); else d.setAttribute('open', '');
      document.documentElement.classList.add('cb-perde-acik');
    }
    function kapat() { if (d.close) d.close(); else { d.removeAttribute('open'); d.dispatchEvent(new Event('close')); } }
    document.querySelectorAll('[data-cb-video-ac]').forEach(function (b) {
      b.addEventListener('click', function () { ac(b.getAttribute('data-cb-video-ac'), b.getAttribute('data-cb-baslik'), b); });
    });
    d.addEventListener('click', function (e) {
      if (e.target === d || e.target.closest('[data-cb-kapat]')) kapat();
    });
    d.addEventListener('close', function () {
      yer.innerHTML = '';
      document.documentElement.classList.remove('cb-perde-acik');
      if (donus) donus.focus();
    });
  }

  var azHareket = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Girişte süzülen altın toz: küçük, yavaş, göz kırpan zerreler.
   * Görünmüyorken ve sekme arka plandayken durur; hareketi azalt açıksa tek durgun kare çizilir. */
  function toz() {
    var t = document.querySelector('.cb-toz');
    if (!t || !t.getContext) return;
    var c = t.getContext('2d'), kok = t.parentNode, W = 0, H = 0, z = [], son = 0, calisiyor = false, gorunur = true;
    function yeni(ilk) {
      return { x: Math.random() * W, y: ilk ? Math.random() * H : H + 10, r: .5 + Math.random() * 1.6,
               v: 6 + Math.random() * 14, s: 8 + Math.random() * 18, f: Math.random() * 6.28, h: .4 + Math.random() * .9 };
    }
    function kur() {
      var r = kok.getBoundingClientRect(), dpr = Math.min(window.devicePixelRatio || 1, 2);
      W = r.width; H = r.height; t.width = Math.round(W * dpr); t.height = Math.round(H * dpr);
      c.setTransform(dpr, 0, 0, dpr, 0, 0);
      z = []; for (var i = 0, n = W < 640 ? 40 : 90; i < n; i++) z.push(yeni(true));
    }
    function ciz(dt) {
      c.clearRect(0, 0, W, H);
      for (var i = 0; i < z.length; i++) {
        var p = z[i];
        p.y -= p.v * dt; p.f += dt * p.h;
        if (p.y < -10) { z[i] = yeni(false); continue; }
        var x = p.x + Math.sin(p.f) * p.s;
        c.globalAlpha = (.25 + .55 * (.5 + .5 * Math.sin(p.f * 2.3))) * Math.min(1, (H - p.y) / 120);
        c.fillStyle = p.r > 1.5 ? '#ffe6b0' : '#f0c36e';
        c.beginPath(); c.arc(x, p.y, p.r, 0, 6.283); c.fill();
      }
      c.globalAlpha = 1;
    }
    function dongu(ms) {
      if (!calisiyor) return;
      var dt = son ? Math.min(.05, (ms - son) / 1000) : .016; son = ms;
      ciz(dt); requestAnimationFrame(dongu);
    }
    function baslat() { if (!calisiyor && gorunur && !document.hidden) { calisiyor = true; son = 0; requestAnimationFrame(dongu); } }
    kur();
    if (azHareket) { ciz(0); return; }
    var zm; window.addEventListener('resize', function () { clearTimeout(zm); zm = setTimeout(kur, 150); });
    if ('IntersectionObserver' in window) new IntersectionObserver(function (e) { gorunur = e[0].isIntersecting; gorunur ? baslat() : (calisiyor = false); }).observe(kok);
    document.addEventListener('visibilitychange', function () { document.hidden ? (calisiyor = false) : baslat(); });
    baslat();
  }

  /* Vizördeki zaman kodu (24 kare/sn); hareketi azalt açıksa durur */
  function zamanKodu() {
    var el = document.querySelector('[data-cb-zaman]');
    if (!el || azHareket) return;
    var bas = performance.now(), iki = function (n) { return (n < 10 ? '0' : '') + n; };
    (function ciz(t) {
      var kare = Math.floor((t - bas) / (1000 / 24)), sn = Math.floor(kare / 24);
      el.textContent = iki(Math.floor(sn / 3600)) + ':' + iki(Math.floor(sn / 60) % 60) + ':' + iki(sn % 60) + ':' + iki(kare % 24);
      requestAnimationFrame(ciz);
    })(bas);
  }

  /* Akan jenerik: kesintisiz döngü için satırlar bir kez daha eklenir (ekran okuyucudan gizli) */
  function jenerik() {
    var l = document.querySelector('.cb-jenerik__akis');
    if (!l) return;
    Array.prototype.slice.call(l.children).forEach(function (li) {
      var k = li.cloneNode(true); k.setAttribute('aria-hidden', 'true'); l.appendChild(k);
    });
  }

  /* Klaketler görününce sırayla "çakar" */
  function klaketler() {
    var k = document.querySelectorAll('.cb-klaket');
    if (!k.length || azHareket || !('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (g) {
      g.forEach(function (x) {
        if (!x.isIntersecting) return;
        io.unobserve(x.target);
        var i = Array.prototype.indexOf.call(k, x.target);
        setTimeout(function () {
          x.target.classList.add('is-cak');
          setTimeout(function () { x.target.classList.remove('is-cak'); }, 420);
        }, 180 * i + 200);
      });
    }, { threshold: .6 });
    k.forEach(function (x) { io.observe(x); });
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

  function basla() { videolar(); reel(); perdelik(); suzgec(); toz(); zamanKodu(); jenerik(); klaketler(); turBaglantilari(); form(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
})();
