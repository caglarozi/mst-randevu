/* CineBook ve MST Çocuk sayfası
 * - Perde arka planı, akan jenerik, klaketler, perdelik oynatıcı ve filmografi süzgeci
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

  /* Kaydırınca giriş: yalnızca açılışta ekranın altında kalan [data-cb-gir] öğeleri bekletilir,
   * görünür olunca sırayla gelir. JS yoksa ya da hareketi azalt açıksa her şey baştan görünür. */
  function girisler() {
    var l = document.querySelectorAll('[data-cb-gir]');
    if (!l.length || azHareket || !('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (g) {
      g.forEach(function (x) {
        if (!x.isIntersecting) return;
        var el = x.target; el.classList.add('cb-gir'); io.unobserve(el);
        // giriş bitince sınıflar kalkar; üzerine gelme gibi sonraki hareketler gecikmesiz çalışır
        setTimeout(function () { el.classList.remove('cb-bekle', 'cb-gir'); }, 2600);
      });
    }, { threshold: .2, rootMargin: '0px 0px -8% 0px' });
    l.forEach(function (el) {
      if (el.getBoundingClientRect().top > window.innerHeight * .92) { el.classList.add('cb-bekle'); io.observe(el); }
    });
  }

  /* Film şeridi sayfa kaydıkça ilerler: delikler ve kareler yana kayar (film makinesinde akıyormuş gibi) */
  function seritAkisi() {
    var s = document.querySelector('.cb-serit');
    if (!s || azHareket) return;
    var bekliyor = false, gorunur = true;
    function guncelle() {
      bekliyor = false;
      var r = s.getBoundingClientRect();
      s.style.setProperty('--kay', ((r.top + r.height / 2 - window.innerHeight / 2) * -.3).toFixed(1) + 'px');
    }
    if ('IntersectionObserver' in window) new IntersectionObserver(function (e) { gorunur = e[0].isIntersecting; }).observe(s);
    window.addEventListener('scroll', function () { if (gorunur && !bekliyor) { bekliyor = true; requestAnimationFrame(guncelle); } }, { passive: true });
    guncelle();
  }

  /* Instagram gömmesi: bölüm ekrana yaklaşınca Instagram'ın kendi betiği yüklenir ve videoyu sayfada gösterir.
   * Betik yüklenemezse "Instagram'da izleyin" bağlantısı görünür kalır. */
  function instagram() {
    var k = document.querySelector('[data-cb-ig]');
    if (!k) return;
    function yukle() {
      if (window.instgrm && window.instgrm.Embeds) { window.instgrm.Embeds.process(); return; }
      if (document.getElementById('cb-ig-betik')) return;
      var b = document.createElement('script');
      b.id = 'cb-ig-betik'; b.async = true; b.src = 'https://www.instagram.com/embed.js';
      document.body.appendChild(b);
    }
    if (!('IntersectionObserver' in window)) return yukle();
    var io = new IntersectionObserver(function (e) { if (e[0].isIntersecting) { io.disconnect(); yukle(); } }, { rootMargin: '600px 0px' });
    io.observe(k);
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

  function turSec(tur, ilerle) {
    var r = document.querySelector('.cb-form input[name="tur"][value="' + tur + '"]');
    if (!r) return;
    r.checked = true;
    var f = r.form;
    if (ilerle && f && f._cbGit) f._cbGit(2, true);
  }

  function turBaglantilari() {
    document.querySelectorAll('[data-cb-tur]').forEach(function (a) {
      a.addEventListener('click', function () { turSec(a.getAttribute('data-cb-tur'), true); });
    });
    var m = /[?&]tur=(cinebook|cocuk)/.exec(location.search);
    if (m) turSec(m[1]);
  }

  /* Başvuru formu: randevu sistemi gibi 3 adım (tür → eser → iletişim), sonra AJAX ile gönderim.
   * JS yoksa adımlar alt alta görünür ve form yine gönderilebilir (sunucu tüm alanları denetler). */
  function form() {
    var f = document.querySelector('[data-cb-form]');
    if (!f || !window.MST_CB) return;
    var hata = f.querySelector('[data-cb-hata]'), tamam = f.querySelector('[data-cb-tamam]'), btn = f.querySelector('.cb-form__gonder');
    var adimlar = f.querySelectorAll('[data-cb-adim]'), isaretler = f.querySelectorAll('[data-cb-adim-isaret]'), ozet = f.querySelector('[data-cb-ozet]');
    var ALAN_ADIM = { tur: 1, eser_adi: 2, ozet: 2, yazar_adi: 3, telefon: 3, eposta: 3, kvkk: 3 };
    var simdiki = 1;
    f.classList.add('is-adimli');

    function temizle() {
      hata.hidden = true;
      f.querySelectorAll('.is-hata').forEach(function (e) { e.classList.remove('is-hata'); });
    }
    function goster(m, alan) {
      if (alan && ALAN_ADIM[alan]) git(ALAN_ADIM[alan], true);
      hata.textContent = m; hata.hidden = false;
      var el = alan && f.querySelector('[name="' + alan + '"]');
      if (el) { var l = el.closest('.cb-alan'); if (l) l.classList.add('is-hata'); el.focus(); }
    }
    function turAdi() {
      var r = f.querySelector('input[name="tur"]:checked');
      return r && r.value === 'cocuk' ? 'MST Çocuk' : 'CineBook';
    }
    function git(n, sessiz) {
      simdiki = n;
      if (!sessiz) temizle();
      adimlar.forEach(function (a) { a.classList.toggle('is-aktif', +a.getAttribute('data-cb-adim') === n); });
      isaretler.forEach(function (i) {
        var k = +i.getAttribute('data-cb-adim-isaret');
        i.classList.toggle('is-aktif', k === n); i.classList.toggle('is-bitti', k < n);
      });
      if (n === 3 && ozet) {
        var eser = (f.elements.eser_adi.value || '').trim();
        ozet.textContent = turAdi() + (eser ? ' · ' + eser : '');
        ozet.hidden = false;
      }
      var hedef = f.querySelector('[data-cb-adim="' + n + '"]');
      var ilk = hedef && hedef.querySelector('input:not([type=radio]):not([type=hidden]), textarea, input[type=radio]:checked');
      if (ilk && !sessiz) ilk.focus({ preventScroll: true });
      var r = f.getBoundingClientRect();
      if (!sessiz && (r.top < 70 || r.top > window.innerHeight * .6)) f.scrollIntoView({ behavior: azHareket ? 'auto' : 'smooth', block: 'start' });
    }
    function denetle(n) {
      if (n === 2 && (f.elements.eser_adi.value || '').trim().length < 2) { goster('Lütfen eserinizin adını yazın.', 'eser_adi'); return false; }
      return true;
    }
    f.querySelectorAll('[data-cb-ileri]').forEach(function (b) {
      b.addEventListener('click', function () { if (denetle(simdiki)) git(simdiki + 1); });
    });
    f.querySelectorAll('[data-cb-geri]').forEach(function (b) {
      b.addEventListener('click', function () { git(simdiki - 1); });
    });
    // Türü seçmek randevu sistemindeki gibi bir sonraki adıma geçirir
    f.querySelectorAll('input[name="tur"]').forEach(function (r) {
      r.addEventListener('change', function () { setTimeout(function () { git(2); }, 180); });
    });
    // Eser adında Enter bir sonraki adıma geçirir (formu yarıda göndermesin)
    f.elements.eser_adi.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); if (denetle(2)) git(3); }
    });
    f._cbGit = git;

    f.addEventListener('submit', function (e) {
      e.preventDefault();
      if (simdiki !== 3) { if (denetle(simdiki)) git(simdiki + 1); return; }
      temizle();
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
            f.scrollIntoView({ behavior: azHareket ? 'auto' : 'smooth', block: 'center' });
          } else {
            goster((j && j.data && j.data.mesaj) || 'Başvuru gönderilemedi, lütfen tekrar deneyin.', j && j.data && j.data.alan);
          }
        })
        .catch(function () { goster('Bağlantı hatası. Lütfen tekrar deneyin.'); })
        .then(function () { btn.disabled = false; btn.textContent = 'Başvuruyu Gönder'; });
    });
  }

  function basla() { videolar(); reel(); perdelik(); suzgec(); toz(); girisler(); seritAkisi(); instagram(); jenerik(); klaketler(); form(); turBaglantilari(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
})();
