/* MST Yazar Kariyer Akademisi — açılış sahnesi ve bölüm menüsü.
 * - "MST Yayıncılık" bir kez daktiloyla yazılır (döngü yok), altında "Yazar Akademisi" ışıkla yanar; sahne ışığının huzmesinde toz zerreleri süzülür
 * - Bölüm menüsünde ekrandaki bölüm işaretlenir
 * - Telefonda uzun müfredat listeleri kapalı başlar, programlar sekmeli görünür
 * Hareketi azalt açıksa daktilo ve toz çalışmaz; sayfa durağan görünür. */
(function () {
  'use strict';
  var azHareket = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Üst satır ("MST Yayıncılık") bir kez daktiloyla yazılır; imleç birkaç kez yanıp söner ve kaybolur.
  // Metin HTML'de hazır durur (arama motoru ve JS'siz ziyaretçi için); JS yalnızca görünümü canlandırır.
  function daktilo() {
    var el = document.querySelector('[data-akd-daktilo]');
    if (!el || azHareket) return;
    // Yazılmamış kısım görünmez ama yerini korur: başlık baştan son yerinde durur, sayfa zıplamaz
    var parcalar = [], yuru = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null), n, dugumler = [];
    while ((n = yuru.nextNode())) dugumler.push(n);
    dugumler.forEach(function (d) {
      var yazildi = document.createElement('span'), kalan = document.createElement('span');
      kalan.style.visibility = 'hidden'; kalan.textContent = d.nodeValue;
      d.parentNode.insertBefore(yazildi, d); d.parentNode.insertBefore(kalan, d); d.parentNode.removeChild(d);
      parcalar.push({ yazildi: yazildi, kalan: kalan, metin: kalan.textContent });
    });
    var imlec = document.createElement('i');
    imlec.className = 'akd-imlec'; imlec.setAttribute('aria-hidden', 'true');
    var p = 0, k = 0;
    function yerlestir() { var par = parcalar[p]; par.kalan.parentNode.insertBefore(imlec, par.kalan); }
    yerlestir();
    setTimeout(function yaz() {
      if (p >= parcalar.length) {
        setTimeout(function () { imlec.classList.add('is-bitti'); setTimeout(function () { imlec.remove(); }, 450); }, 2600);
        return;
      }
      var par = parcalar[p];
      k += 1; par.yazildi.textContent = par.metin.slice(0, k); par.kalan.textContent = par.metin.slice(k);
      if (k >= par.metin.length) { p += 1; k = 0; if (p < parcalar.length) yerlestir(); }
      setTimeout(yaz, 55 + Math.random() * 45);
    }, 300);
  }

  // Işık huzmesinin içinde yavaşça süzülen toz zerreleri (huzme dışına çıkanlar görünmez)
  function toz() {
    var c = document.querySelector('.akd-sahne__toz'), sahne = document.querySelector('.akd-sahne');
    if (!c || !sahne || azHareket || !c.getContext) return;
    var g = c.getContext('2d'), dpr = Math.min(window.devicePixelRatio || 1, 2), W = 0, H = 0, z = [], acik = true, son = 0;
    var EGIM = Math.tan(21 * Math.PI / 180);
    function boyut() {
      W = sahne.clientWidth; H = sahne.clientHeight;
      c.width = W * dpr; c.height = H * dpr; g.setTransform(dpr, 0, 0, dpr, 0, 0);
      var n = W < 700 ? 45 : 90;
      z = [];
      for (var i = 0; i < n; i++) z.push({ x: (Math.random() - 0.5) * 2, y: Math.random(), r: 0.6 + Math.random() * 1.8, h: 4 + Math.random() * 10, f: Math.random() * 6.28 });
    }
    function kare(t) {
      if (!acik) { son = 0; return; }
      var dt = son ? Math.min(0.05, (t - son) / 1000) : 0; son = t;
      g.clearRect(0, 0, W, H);
      z.forEach(function (p) {
        p.y -= (p.h / H) * dt; p.x += Math.sin(t / 2400 + p.f) * 0.0006;
        if (p.y < 0.02) { p.y = 1; p.x = (Math.random() - 0.5) * 2; }
        var y = p.y * H, yari = Math.max(20, y * EGIM), x = W / 2 + p.x * yari;
        var kenar = 1 - Math.min(1, Math.abs(p.x)); // huzmenin ortasında daha parlak
        g.globalAlpha = kenar * (0.25 + 0.55 * (0.5 + 0.5 * Math.sin(t / 700 + p.f))) * (1 - p.y * 0.5);
        g.fillStyle = '#ffe3a8';
        g.beginPath(); g.arc(x, y, p.r, 0, Math.PI * 2); g.fill();
      });
      g.globalAlpha = 1;
      requestAnimationFrame(kare);
    }
    boyut();
    window.addEventListener('resize', boyut);
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (e) { var once = acik; acik = e[0].isIntersecting; if (acik && !once) requestAnimationFrame(kare); }).observe(sahne);
    }
    requestAnimationFrame(kare);
  }

  // Bölüm menüsü: ekranın üst kısmındaki bölüm işaretlenir
  function gezinti() {
    var linkler = document.querySelectorAll('[data-akd-gez]');
    if (!linkler.length || !('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (girdiler) {
      girdiler.forEach(function (g) {
        if (!g.isIntersecting) return;
        linkler.forEach(function (l) {
          var bu = l.getAttribute('data-akd-gez') === g.target.id, kap = l.parentElement;
          l.classList.toggle('is-aktif', bu);
          if (bu && kap.scrollWidth > kap.clientWidth) kap.scrollTo({ left: l.offsetLeft - 20, behavior: 'smooth' });
        });
      });
    }, { rootMargin: '-30% 0px -65% 0px' });
    linkler.forEach(function (l) { var b = document.getElementById(l.getAttribute('data-akd-gez')); if (b) io.observe(b); });
  }

  // Telefonda programlar sekmeli: aynı anda tek program görünür, üstteki 01/02/03 etiketleriyle geçilir.
  // Bilgisayarda üç program alt alta açık kalır (etiketler yalnızca ilgili programa kaydırır).
  function programSekme() {
    var bolum = document.getElementById('programlar');
    var sekmeler = bolum ? [].slice.call(bolum.querySelectorAll('.akd-program-sekme a')) : [];
    var programlar = bolum ? [].slice.call(bolum.querySelectorAll('.akd-program')) : [];
    if (!sekmeler.length || sekmeler.length !== programlar.length) return;
    var mq = window.matchMedia('(max-width: 640px)'), secili = 0;
    function goster(i) {
      secili = i;
      sekmeler.forEach(function (a, k) { a.setAttribute('aria-selected', k === i ? 'true' : 'false'); a.tabIndex = k === i ? 0 : -1; });
      programlar.forEach(function (p, k) { p.classList.toggle('is-gizli', k !== i); });
    }
    function uygula() {
      bolum.classList.toggle('akd-sekmeli', mq.matches);
      if (mq.matches) goster(secili);
      else programlar.forEach(function (p) { p.classList.remove('is-gizli'); });
    }
    sekmeler.forEach(function (a, i) {
      a.addEventListener('click', function (e) {
        if (!mq.matches) return;
        e.preventDefault();
        goster(i);
        var bar = bolum.querySelector('.akd-program-sekme');
        window.scrollTo({ top: bar.getBoundingClientRect().top + window.pageYOffset - 140, behavior: azHareket ? 'auto' : 'smooth' });
      });
    });
    var m = /^#program-(.+)$/.exec(location.hash);
    if (m) programlar.forEach(function (p, i) { if (p.id === 'program-' + m[1]) secili = i; });
    uygula();
    if (mq.addEventListener) mq.addEventListener('change', uygula); else if (mq.addListener) mq.addListener(uygula);
  }

  function basla() {
    daktilo(); toz(); gezinti(); programSekme();
    if (window.matchMedia('(max-width: 640px)').matches) {
      document.querySelectorAll('.akd-acilir[open]').forEach(function (d) { d.open = false; });
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
})();
