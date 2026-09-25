/* MST Yazar Paneli tanıtım sayfası — hareketler.
 * - Kaydırınca belirme: bölümlerin içindeki öğeler görünür alana girince sırayla belirir
 * - Sayaç: telefondaki satış sayısı 0'dan yukarı sayar
 * - Canlı bildirim: girişteki bildirim balonu birkaç saniyede bir değişir
 * - 3B eğim: fare girişteki telefonun üstünde gezinince telefon hafifçe eğilir
 * - Kitap perisi: sağ altta süzülür, bölüm değiştikçe uçup poz değiştirir ve bölümü anlatır;
 *   arkasında ışıltı parçacıkları bırakır (kapatılırsa o oturumda bir daha çıkmaz)
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

  /* ---------- Kitap perisi ---------- */
  // Arkasında ışıltı izi bırakan parçacıklar: peri durunca kanatlarından hafifçe dökülür,
  // uçarken yol boyunca yoğun bir iz bırakır. Tuval perinin altında durur (arkasında görünür).
  function parcaciklar(tuval, hedef) {
    var ctx = tuval.getContext && tuval.getContext('2d');
    if (!ctx) return null;
    var dpr = Math.min(window.devicePixelRatio || 1, 2), W = 0, H = 0, liste = [], onceki = null, son = 0, calis = true;
    var dar = window.matchMedia('(max-width: 640px)').matches;
    function boyut() {
      W = window.innerWidth; H = window.innerHeight;
      tuval.width = Math.round(W * dpr); tuval.height = Math.round(H * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    function kare(renk, yildiz) {
      var c = document.createElement('canvas'), g = c.getContext('2d'), s = 64, m = s / 2;
      c.width = c.height = s;
      var r = g.createRadialGradient(m, m, 0, m, m, m);
      r.addColorStop(0, 'rgba(255,250,228,1)'); r.addColorStop(0.28, renk); r.addColorStop(1, 'rgba(236,150,16,0)');
      g.fillStyle = r; g.beginPath();
      if (yildiz) {
        for (var k = 0; k < 8; k++) {
          var a = k * Math.PI / 4, rr = k % 2 ? 7 : m;
          g.lineTo(m + Math.cos(a) * rr, m + Math.sin(a) * rr);
        }
      } else g.arc(m, m, m, 0, Math.PI * 2);
      g.fill();
      return c;
    }
    var KARELER = [kare('rgba(250,190,60,.95)'), kare('rgba(255,214,110,1)', true), kare('rgba(232,146,14,.9)'), kare('rgba(246,176,40,1)', true)];
    function ekle(x, y, vx, vy, boy) {
      if (liste.length > (dar ? 110 : 190)) liste.shift();
      liste.push({ x: x, y: y, vx: vx, vy: vy, yas: 0, omur: 0.9 + Math.random() * 1.1, boy: boy || (6 + Math.random() * 14),
        k: KARELER[(Math.random() * KARELER.length) | 0], t: Math.random() * 6.28, don: (Math.random() - 0.5) * 3 });
    }
    function kaynak() {
      var r = hedef.getBoundingClientRect();
      return { x: r.left + r.width * 0.5, y: r.top + r.height * 0.4, w: r.width };
    }
    function adim(t) {
      if (!calis) return;
      var dt = Math.min(0.05, (t - (son || t)) / 1000); son = t;
      var k = kaynak(), hiz = 0;
      if (onceki) {
        var dx = k.x - onceki.x, dy = k.y - onceki.y, yol = Math.sqrt(dx * dx + dy * dy);
        hiz = yol / Math.max(dt, 0.001);
        // Uçarken aradaki yol boyunca iz bırak
        var n = Math.min(12, Math.floor(yol / 5));
        for (var i = 0; i < n; i++) {
          var f = i / n;
          ekle(onceki.x + dx * f + (Math.random() - 0.5) * k.w * 0.3, onceki.y + dy * f + (Math.random() - 0.5) * k.w * 0.2,
            -dx * 0.6 + (Math.random() - 0.5) * 40, -dy * 0.6 + (Math.random() - 0.5) * 40);
        }
      }
      onceki = k;
      // Dururken kanatlardan hafif ışıltı dökülsün
      if (hiz < 60 && Math.random() < (dar ? 0.3 : 0.5)) {
        var yon = Math.random() < 0.5 ? -1 : 1;
        ekle(k.x + yon * k.w * (0.2 + Math.random() * 0.25), k.y + (Math.random() - 0.3) * k.w * 0.3,
          yon * (10 + Math.random() * 30), 15 + Math.random() * 25, 7 + Math.random() * 12);
      }
      ctx.clearRect(0, 0, W, H);
      for (var j = liste.length - 1; j >= 0; j--) {
        var p = liste[j];
        p.yas += dt;
        if (p.yas >= p.omur) { liste.splice(j, 1); continue; }
        p.vx *= 0.96; p.vy = p.vy * 0.96 + 26 * dt;
        p.x += p.vx * dt; p.y += p.vy * dt; p.t += dt * 9;
        var o = p.yas / p.omur, s = p.boy * (1 - o * 0.55);
        ctx.globalAlpha = (1 - o) * (0.55 + 0.45 * Math.sin(p.t));
        ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.don * p.yas);
        ctx.drawImage(p.k, -s / 2, -s / 2, s, s);
        ctx.restore();
      }
      ctx.globalAlpha = 1;
      requestAnimationFrame(adim);
    }
    boyut();
    window.addEventListener('resize', boyut);
    requestAnimationFrame(adim);
    return {
      patla: function (adet) {
        var k = kaynak();
        for (var i = 0; i < (adet || 26); i++) {
          var a = Math.random() * Math.PI * 2, v = 60 + Math.random() * 160;
          ekle(k.x, k.y, Math.cos(a) * v, Math.sin(a) * v - 40, 8 + Math.random() * 14);
        }
      },
      dur: function () { calis = false; ctx.clearRect(0, 0, W, H); }
    };
  }

  function peri() {
    var kutu = document.querySelector('[data-uyg-peri]');
    var bolumler = document.querySelectorAll('[data-peri-poz]');
    if (!kutu || !bolumler.length) return;
    try { if (sessionStorage.getItem('mst_peri_gizli') === '1') return; } catch (e) { /* depolama kapalı */ }
    var govde = kutu.querySelector('.uyg-peri__govde'), soz = kutu.querySelector('[data-uyg-peri-soz]');
    var resimler = kutu.querySelectorAll('.uyg-peri__ic img'), tuval = document.querySelector('.uyg-peri-iz');
    var aktif = null, yazi = 0, gizle = 0;
    var yazilan = document.createElement('span'), kalan = document.createElement('span');
    kalan.className = 'uyg-peri__kalan';
    soz.appendChild(yazilan); soz.appendChild(kalan);
    kutu.hidden = false;
    var iz = azHareket || !tuval ? null : parcaciklar(tuval, govde);

    function konus(metin) {
      clearInterval(yazi); clearTimeout(gizle);
      kutu.classList.add('is-balon');
      if (azHareket) { yazilan.textContent = metin; kalan.textContent = ''; }
      else {
        // Daktilo: balon baştan tam boyutta açılır, yazı içinde belirir
        var i = 0;
        yazilan.textContent = ''; kalan.textContent = metin;
        yazi = setInterval(function () {
          i += 1; yazilan.textContent = metin.slice(0, i); kalan.textContent = metin.slice(i);
          if (i >= metin.length) clearInterval(yazi);
        }, 24);
      }
      gizle = setTimeout(function () { kutu.classList.remove('is-balon'); }, 8000);
    }
    function uc() {
      if (azHareket || !govde.animate) return;
      govde.animate([
        { transform: 'none' },
        { transform: 'translate(-90px,-70px) rotate(-12deg)', offset: 0.4 },
        { transform: 'translate(-30px,-110px) rotate(8deg)', offset: 0.7 },
        { transform: 'none' }
      ], { duration: 1150, easing: 'cubic-bezier(.45,0,.25,1)' });
    }
    function sec(b, ilk) {
      if (b === aktif) return;
      aktif = b;
      var poz = b.getAttribute('data-peri-poz');
      resimler.forEach(function (r) { r.classList.toggle('is-aktif', r.getAttribute('data-poz') === poz); });
      konus(b.getAttribute('data-peri-soz'));
      if (ilk) { if (iz) iz.patla(24); return; }
      uc();
      if (iz) setTimeout(function () { iz.patla(18); }, 1050);
    }

    // Giriş: sağ alttan süzülerek gelsin
    if (!azHareket && govde.animate) {
      govde.animate([
        { transform: 'translate(260px,160px) rotate(25deg) scale(.6)', opacity: 0 },
        { transform: 'translate(-40px,-60px) rotate(-8deg) scale(1.05)', opacity: 1, offset: 0.7 },
        { transform: 'none', opacity: 1 }
      ], { duration: 1400, easing: 'cubic-bezier(.3,.7,.3,1)', delay: 700, fill: 'backwards' });
    }
    setTimeout(function () {
      // Sayfa ortadan açıldıysa (yenileme) o anki bölümle başla
      var orta = window.innerHeight / 2, ilk = bolumler[0];
      bolumler.forEach(function (b) { var r = b.getBoundingClientRect(); if (r.top <= orta && r.bottom >= orta) ilk = b; });
      sec(ilk, true);
    }, azHareket ? 0 : 2100);

    // Ekranın ortasından geçen bölüm "aktif" sayılır
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (girdiler) {
        girdiler.forEach(function (g) { if (g.isIntersecting && aktif) sec(g.target); });
      }, { rootMargin: '-48% 0px -48% 0px' });
      bolumler.forEach(function (b) { io.observe(b); });
    }

    govde.addEventListener('click', function () {
      if (aktif) konus(aktif.getAttribute('data-peri-soz'));
      if (iz) iz.patla(30);
      if (!azHareket && govde.animate) {
        govde.animate([{ transform: 'none' }, { transform: 'translateY(-18px) rotate(-10deg) scale(1.08)' }, { transform: 'none' }],
          { duration: 500, easing: 'ease-out' });
      }
    });
    kutu.querySelector('.uyg-peri__kapat').addEventListener('click', function () {
      clearInterval(yazi); clearTimeout(gizle);
      kutu.hidden = true;
      if (iz) iz.dur();
      try { sessionStorage.setItem('mst_peri_gizli', '1'); } catch (e) { /* depolama kapalı */ }
    });
  }

  function basla() {
    belirmeyiKur();
    sayaclar();
    canliBildirim();
    egim();
    peri();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', basla); else basla();
  // JS bir hata verirse içerik gizli kalmasın
  window.addEventListener('error', function () { kok.classList.remove('uyg-js'); hepsiniGoster(); });
})();
