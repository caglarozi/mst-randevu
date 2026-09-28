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

  /* Kitaptan perdeye: kitabın ortasından harfler yükselir, yükseldikçe film karelerine dönüşür; ışıkta toz süzülür.
   * Görünmüyorken ve sekme arka plandayken durur; hareketi azalt açıksa tek bir durgun kare çizilir. */
  function sahne() {
    var tuval = document.querySelector('.cb-sahne__tuval'), kitap = document.querySelector('.cb-sahne__kitap');
    if (!tuval || !kitap || !tuval.getContext) return;
    var c = tuval.getContext('2d'), kok = tuval.parentNode, W = 0, H = 0, dpr = 1, g = null;
    var HARF = 'ABCÇDEFGĞHIİJKLMNOÖPRSŞTUÜVYZ', parca = [], toz = [], son = 0, calisiyor = false, gorunur = true;
    var serif = '"Cormorant Garamond", Garamond, serif';

    function olc() {
      var r = kok.getBoundingClientRect(), k = kitap.getBoundingClientRect();
      dpr = Math.min(window.devicePixelRatio || 1, 2);
      W = r.width; H = r.height;
      tuval.width = Math.round(W * dpr); tuval.height = Math.round(H * dpr);
      c.setTransform(dpr, 0, 0, dpr, 0, 0);
      // oluk (kitabın ortası) ve sayfa üstü: SVG viewBox 640x380, oluk x=320, sayfa üstü y≈140
      g = { x: k.left - r.left + k.width / 2, y: k.top - r.top + k.height * (150 / 380), gen: k.width * .62, yuk: k.top - r.top + k.height * .3 };
    }
    function yeniParca(ilk) {
      var u = (Math.random() - .5), omur = 7 + Math.random() * 6;
      return {
        x0: g.x + u * g.gen, y: g.y + Math.random() * 10, u: u,
        v: 22 + Math.random() * 26, ya: 0, omur: omur, t: ilk ? Math.random() * omur : 0,
        h: HARF.charAt(Math.floor(Math.random() * HARF.length)), boy: 13 + Math.random() * 15,
        don: (Math.random() - .5) * .6, faz: Math.random() * 6.28, kare: Math.random() < .72
      };
    }
    function yeniToz() {
      return { x: g.x + (Math.random() - .5) * g.gen * 2.2, y: Math.random() * g.y, r: .5 + Math.random() * 1.3, v: 3 + Math.random() * 8, faz: Math.random() * 6.28 };
    }
    function kur() {
      olc(); parca = []; toz = [];
      var n = W < 640 ? 14 : 28;
      for (var i = 0; i < n; i++) parca.push(yeniParca(true));
      for (var j = 0; j < (W < 640 ? 30 : 70); j++) toz.push(yeniToz());
    }
    function filmKaresi(x, y, w, a) {
      var h = w * .72;
      c.globalAlpha = a;
      c.strokeStyle = '#f3cf86'; c.lineWidth = 1.2;
      c.strokeRect(x - w / 2, y - h / 2, w, h);
      c.fillStyle = 'rgba(255, 214, 150, .16)';
      c.fillRect(x - w / 2 + 2, y - h / 2 + 3.5, w - 4, h - 7);
      c.fillStyle = '#f3cf86';
      for (var i = 0; i < 4; i++) {
        var dx = x - w / 2 + 2.5 + i * (w - 5) / 3.5;
        c.fillRect(dx, y - h / 2 + .8, 1.8, 1.6); c.fillRect(dx, y + h / 2 - 2.4, 1.8, 1.6);
      }
    }
    function ciz(dt) {
      c.clearRect(0, 0, W, H);
      var yukseklik = Math.max(g.y - 40, 1);
      // toz
      c.fillStyle = '#ffe3b0';
      for (var j = 0; j < toz.length; j++) {
        var d = toz[j];
        d.y -= d.v * dt; d.faz += dt * .8; d.x += Math.sin(d.faz) * .12;
        if (d.y < -10) { toz[j] = yeniToz(); toz[j].y = g.y; continue; }
        var ic = 1 - Math.min(1, Math.abs(d.x - g.x) / (g.gen * (.35 + (g.y - d.y) / yukseklik * 1.1)));
        c.globalAlpha = Math.max(0, ic) * (.35 + .35 * Math.sin(d.faz * 2));
        c.beginPath(); c.arc(d.x, d.y, d.r, 0, 6.283); c.fill();
      }
      // harfler ve film kareleri
      for (var i = 0; i < parca.length; i++) {
        var p = parca[i];
        p.t += dt; p.ya += p.v * dt; p.faz += dt;
        if (p.t > p.omur || g.y - p.ya < -30) { parca[i] = yeniParca(false); continue; }
        var ilerle = Math.min(1, p.ya / yukseklik);                     // 0: sayfada, 1: perdede
        var x = p.x0 + p.u * g.gen * ilerle * 1.6 + Math.sin(p.faz * .9) * 6;
        var y = g.y - p.ya;
        var a = Math.min(1, p.t / 1.2) * (1 - Math.max(0, (ilerle - .7) / .3));
        var don = p.kare ? Math.min(1, Math.max(0, (ilerle - .32) / .22)) : 0; // harften kareye geçiş
        if (don < 1) {
          c.save(); c.translate(x, y); c.rotate(p.don * Math.sin(p.faz * .5));
          c.globalAlpha = a * (1 - don) * .8;
          c.font = 'italic 500 ' + p.boy + 'px ' + serif;
          c.fillStyle = '#f7dfae'; c.textAlign = 'center'; c.textBaseline = 'middle';
          c.fillText(p.h, 0, 0); c.restore();
        }
        if (don > 0) filmKaresi(x, y, p.boy * (1 + ilerle * .5), a * don * .85);
      }
      c.globalAlpha = 1;
    }
    function dongu(t) {
      if (!calisiyor) return;
      var dt = son ? Math.min(.05, (t - son) / 1000) : .016; son = t;
      ciz(dt); requestAnimationFrame(dongu);
    }
    function baslat() { if (!calisiyor && gorunur && !document.hidden) { calisiyor = true; son = 0; requestAnimationFrame(dongu); } }
    function durdur() { calisiyor = false; }

    kur();
    if (azHareket) { for (var k = 0; k < 90; k++) ciz(.05); return; }
    var zaman; window.addEventListener('resize', function () { clearTimeout(zaman); zaman = setTimeout(kur, 150); });
    if ('IntersectionObserver' in window) new IntersectionObserver(function (e) { gorunur = e[0].isIntersecting; gorunur ? baslat() : durdur(); }).observe(kok);
    document.addEventListener('visibilitychange', function () { document.hidden ? durdur() : baslat(); });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { olc(); });
    setTimeout(olc, 1900); // kitabın giriş hareketi bitince konumu yeniden ölç
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

  function basla() { videolar(); reel(); perdelik(); suzgec(); sahne(); zamanKodu(); jenerik(); klaketler(); turBaglantilari(); form(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
})();
