/* MST Yazar Paneli tanıtım sayfası — hareketler.
 * - Kaydırınca belirme: bölümlerin içindeki öğeler görünür alana girince sırayla belirir
 * - Sayaç: telefondaki satış sayısı 0'dan yukarı sayar
 * - Canlı bildirim: girişteki bildirim balonu birkaç saniyede bir değişir
 * - 3B eğim: fare girişteki telefonun üstünde gezinince telefon hafifçe eğilir
 * Hareketi azalt (prefers-reduced-motion) açıksa hepsi atlanır ve her şey hemen görünür. */
(function () {
  'use strict';
  var kok = document.documentElement;
  var azHareket = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function hepsiniGoster() {
    document.querySelectorAll('.uyg-belir').forEach(function (e) { e.classList.add('is-gorunur'); });
  }

  /* ---------- Kaydırınca belirme ---------- */
  var HEDEFLER = [
    '.uyg-bolum .uyg-baslik', '.uyg-liste li', '.uyg-panel', '.uyg-ozellik', '.uyg-rota li',
    '.uyg-adimlar li', '.uyg-sss details', '.uyg-rozetler > div', '.uyg-sohbet .uyg-balon', '.uyg-kapanis__in',
    '.uyg-cubuklar', '.uyg-kanallar', '.uyg-plan'
  ].join(',');

  function belirmeyiKur() {
    var sayac = new Map();
    document.querySelectorAll(HEDEFLER).forEach(function (e) {
      // Aynı kaptaki kardeşler sırayla (80 ms arayla) belirsin
      var ebeveyn = e.parentElement, n = sayac.get(ebeveyn) || 0;
      sayac.set(ebeveyn, n + 1);
      e.classList.add('uyg-belir');
      e.style.setProperty('--sira', Math.min(n, 8));
    });
    if (azHareket || !('IntersectionObserver' in window)) { hepsiniGoster(); return; }
    var io = new IntersectionObserver(function (girdiler) {
      girdiler.forEach(function (g) {
        if (!g.isIntersecting) return;
        var e = g.target;
        e.classList.add('is-gorunur');
        io.unobserve(e);
        // Belirme bitince sınıfları kaldır: kartların kendi hover geçişleri gecikmesiz çalışsın
        // (içinde ayrıca büyüyen çubuk/simge olanlar hariç; onların geçişi yarıda kesilmesin)
        if (e.matches('.uyg-cubuklar, .uyg-kanallar, .uyg-rota li')) return;
        e.addEventListener('transitionend', function son(ev) {
          if (ev.target !== e || ev.propertyName !== 'opacity') return;
          e.removeEventListener('transitionend', son);
          e.classList.remove('uyg-belir', 'is-gorunur');
        });
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    document.querySelectorAll('.uyg-belir').forEach(function (e) { io.observe(e); });
    // Güvence: bir sebeple tetiklenmeyen öğe kalmasın
    setTimeout(function () {
      document.querySelectorAll('.uyg-belir:not(.is-gorunur)').forEach(function (e) {
        if (e.getBoundingClientRect().top < window.innerHeight) e.classList.add('is-gorunur');
      });
    }, 2500);
  }

  /* ---------- Sayaç ---------- */
  function sayaclar() {
    document.querySelectorAll('[data-uyg-say]').forEach(function (e) {
      var hedef = parseInt(e.getAttribute('data-uyg-say'), 10) || 0;
      if (azHareket) return;
      var bas = null, sure = 1600, gecikme = 900;
      e.textContent = '0';
      setTimeout(function () {
        requestAnimationFrame(function adim(t) {
          if (bas === null) bas = t;
          var p = Math.min(1, (t - bas) / sure), k = 1 - Math.pow(1 - p, 3);
          e.textContent = Math.round(hedef * k).toLocaleString('tr-TR');
          if (p < 1) requestAnimationFrame(adim);
        });
      }, gecikme);
    });
  }

  /* ---------- Canlı bildirim ---------- */
  var BILDIRIMLER = [
    ['Yeni aşama:', 'Kapak tasarımı başladı'],
    ['D&R:', '+17 adet satış'],
    ['Telif:', 'Ödemeniz hesabınızda'],
    ['Akademi:', 'Yeni ders yayında'],
    ['Kampanya:', 'Reels yayına girdi']
  ];
  function canliBildirim() {
    var e = document.querySelector('[data-uyg-canli]');
    if (!e || azHareket) return;
    var i = 0, kutu = e.closest('.uyg-yuzen');
    setInterval(function () {
      i = (i + 1) % BILDIRIMLER.length;
      kutu.classList.add('is-degisiyor');
      setTimeout(function () {
        e.innerHTML = '';
        var b = document.createElement('b'); b.textContent = BILDIRIMLER[i][0];
        e.appendChild(b); e.appendChild(document.createTextNode(' ' + BILDIRIMLER[i][1]));
        kutu.classList.remove('is-degisiyor');
      }, 280);
    }, 3600);
  }

  /* ---------- 3B eğim ---------- */
  function egim() {
    var alan = document.querySelector('[data-uyg-egim]');
    if (!alan || azHareket || !window.matchMedia('(hover: hover)').matches) return;
    var kare = 0;
    alan.addEventListener('mousemove', function (ev) {
      var r = alan.getBoundingClientRect();
      var x = (ev.clientX - r.left) / r.width - 0.5, y = (ev.clientY - r.top) / r.height - 0.5;
      cancelAnimationFrame(kare);
      kare = requestAnimationFrame(function () {
        alan.style.setProperty('--ry', (x * 14).toFixed(2) + 'deg');
        alan.style.setProperty('--rx', (-y * 10).toFixed(2) + 'deg');
      });
    });
    alan.addEventListener('mouseleave', function () {
      alan.style.setProperty('--ry', '0deg');
      alan.style.setProperty('--rx', '0deg');
    });
  }

  function basla() {
    belirmeyiKur();
    sayaclar();
    canliBildirim();
    egim();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
  // JS bir hata verirse içerik gizli kalmasın
  window.addEventListener('error', function () { kok.classList.remove('uyg-js'); hepsiniGoster(); });
})();
