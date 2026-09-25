/* MST Yazar Kariyer Akademisi — açılış sahnesi ve bölüm menüsü.
 * - "Yazmak başlangıçtır." daktiloyla yazılır; sahne ışığının huzmesinde toz zerreleri süzülür
 * - Bölüm menüsünde ekrandaki bölüm işaretlenir
 * - Telefonda uzun müfredat listeleri kapalı başlar
 * Hareketi azalt açıksa daktilo ve toz çalışmaz; sayfa durağan görünür. */
(function () {
  'use strict';
  var azHareket = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function daktilo() {
    var el = document.querySelector('[data-akd-daktilo]');
    if (!el || azHareket) return;
    var metin = el.getAttribute('data-akd-daktilo'), i = 0;
    el.textContent = '';
    setTimeout(function yaz() {
      i += 1; el.textContent = metin.slice(0, i);
      if (i < metin.length) setTimeout(yaz, 38 + Math.random() * 40);
    }, 250);
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

  function basla() {
    daktilo(); toz(); gezinti();
    if (window.matchMedia('(max-width: 640px)').matches) {
      document.querySelectorAll('.akd-acilir[open]').forEach(function (d) { d.open = false; });
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
})();
