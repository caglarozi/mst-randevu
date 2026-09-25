/* MST Yazar Kariyer Akademisi — açılış sahnesi, program sekmeleri ve bölüm menüsü.
 * - "Yazmak başlangıçtır." daktiloyla yazılır; sahne ışığının huzmesinde toz zerreleri süzülür
 * - Programlar sekmeli; jenerik bağlantıları ve #program-x adresi ilgili programı açar
 * - Bölümler kaydırınca belirir; grafik çubukları ve belge halkaları o an dolar
 * - Bölüm menüsünde ekrandaki bölüm işaretlenir
 * Hareketi azalt açıksa daktilo, toz ve belirme çalışmaz; sayfa durağan görünür. */
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

  // Program sekmeleri: bir anda tek program kitapçığı görünür; jenerik bağlantıları ve #program-x adresi de sekmeyi açar
  function sekmeler() {
    var dugmeler = [].slice.call(document.querySelectorAll('[data-akd-sekme]'));
    var paneller = [].slice.call(document.querySelectorAll('[data-akd-panel]'));
    if (!dugmeler.length) return;
    function ac(k, odak) {
      if (!document.querySelector('[data-akd-panel="' + k + '"]')) return false;
      dugmeler.forEach(function (d) {
        var bu = d.getAttribute('data-akd-sekme') === k;
        d.setAttribute('aria-selected', bu ? 'true' : 'false');
        d.tabIndex = bu ? 0 : -1;
        if (bu && odak) d.focus();
      });
      paneller.forEach(function (p) {
        var bu = p.getAttribute('data-akd-panel') === k;
        if (bu && p.hidden) { p.classList.remove('is-geldi'); void p.offsetWidth; p.classList.add('is-geldi'); }
        p.hidden = !bu;
      });
      return true;
    }
    dugmeler.forEach(function (d, i) {
      d.addEventListener('click', function () { ac(d.getAttribute('data-akd-sekme')); });
      d.addEventListener('keydown', function (e) {
        var y = e.key === 'ArrowRight' || e.key === 'ArrowDown' ? 1 : e.key === 'ArrowLeft' || e.key === 'ArrowUp' ? -1 : 0;
        if (!y) return;
        e.preventDefault();
        ac(dugmeler[(i + y + dugmeler.length) % dugmeler.length].getAttribute('data-akd-sekme'), true);
      });
    });
    document.querySelectorAll('[data-akd-sekme-ac]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        if (!ac(a.getAttribute('data-akd-sekme-ac'))) return;
        e.preventDefault();
        var hedef = document.querySelector('.akd-sekmeler') || document.getElementById('programlar');
        hedef.scrollIntoView({ behavior: azHareket ? 'auto' : 'smooth', block: 'start' });
        if (history.replaceState) history.replaceState(null, '', '#program-' + a.getAttribute('data-akd-sekme-ac'));
      });
    });
    function adres() {
      var m = /^#program-([a-z]+)$/.exec(location.hash);
      if (m && ac(m[1])) { var s = document.querySelector('.akd-sekmeler'); if (s) s.scrollIntoView(); }
    }
    ac(dugmeler[0].getAttribute('data-akd-sekme'));
    adres();
    window.addEventListener('hashchange', adres);
  }

  // Kaydırınca belirme. Sınıf yalnızca gözlemci varsa eklenir; o zamana kadar her şey görünür durur.
  function belir() {
    var ogeler = document.querySelectorAll('[data-akd-belir]');
    if (!ogeler.length || !('IntersectionObserver' in window) || azHareket) return;
    var io = new IntersectionObserver(function (girdiler) {
      girdiler.forEach(function (g) {
        if (g.isIntersecting) { g.target.classList.add('is-gorunur'); io.unobserve(g.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    var ekranAlti = window.innerHeight;
    ogeler.forEach(function (o) {
      // İlk ekranda zaten görünenler bekletilmez
      if (o.getBoundingClientRect().top < ekranAlti) o.classList.add('is-gorunur'); else io.observe(o);
    });
    document.documentElement.classList.add('akd-belir-hazir');
  }

  function basla() {
    sekmeler(); belir(); daktilo(); toz(); gezinti();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
})();
