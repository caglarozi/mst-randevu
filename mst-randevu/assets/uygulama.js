/* MST Yazar Paneli tanıtım sayfası — hareketler.
 * - Kaydırınca belirme: bölümlerin içindeki öğeler görünür alana girince sırayla belirir
 * - Sayaç: telefondaki satış sayısı 0'dan yukarı sayar
 * - Canlı bildirim: girişteki bildirim balonu birkaç saniyede bir değişir
 * - 3B eğim: fare girişteki telefonun üstünde gezinince telefon hafifçe eğilir
 * - Kitap perisi: bölüm değiştikçe ekranın bir yanından öbürüne kavis çizerek uçar, poz değiştirir ve
 *   her bölümü bir kez anlatır; beklerken ara sıra tur atar, arkasında ışıltı izi bırakır
 *   (kapatılırsa o oturumda bir daha çıkmaz)
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
    var resimler = kutu.querySelectorAll('.uyg-peri__poz'), tuval = document.querySelector('.uyg-peri-iz');
    var yazilan = document.createElement('span'), kalan = document.createElement('span');
    kalan.className = 'uyg-peri__kalan';
    soz.appendChild(yazilan); soz.appendChild(kalan);
    var aktif = null, yazi = 0, gizle = 0, gosterildi = new Set(), bitti = false;
    var W = window.innerWidth, H = window.innerHeight;
    // Konum (perinin sol üst köşesi, ekran pikseli), uçuş ve yön
    var konum = { x: W + 60, y: H * 0.55 }, ucus = null, taraf = 'sag', bakis = 1, egimAci = 0, sonTur = performance.now(), basladi = false;
    kutu.hidden = false;
    var iz = azHareket || !tuval ? null : parcaciklar(tuval, govde);

    function boy() { return { w: govde.offsetWidth, h: govde.offsetHeight }; }
    // Konma noktaları: ekranın sağ ya da sol alt köşesi
    function durak(t) {
      var b = boy(), k = W < 640 ? 14 : 22;
      return { x: t === 'sol' ? k : W - b.w - k, y: H - b.h - (W < 640 ? 10 : 18) };
    }
    function ciz(x, y, aci, yon) {
      kutu.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0)';
      govde.style.transform = 'rotate(' + aci.toFixed(2) + 'deg) scaleX(' + yon + ')';
    }
    function balonKapat() {
      clearInterval(yazi); clearTimeout(gizle);
      kutu.classList.remove('is-balon');
    }
    function konus(metin) {
      balonKapat();
      kutu.classList.toggle('is-sol', taraf === 'sol');
      kutu.classList.add('is-balon');
      var okuma = 2200 + metin.length * 35;
      if (azHareket) {
        yazilan.textContent = metin; kalan.textContent = '';
        gizle = setTimeout(balonKapat, okuma);
        return;
      }
      // Daktilo: balon baştan tam boyutta açılır, yazı içinde belirir; bitince okuma süresi kadar kalır
      var i = 0;
      yazilan.textContent = ''; kalan.textContent = metin;
      yazi = setInterval(function () {
        i += 1; yazilan.textContent = metin.slice(0, i); kalan.textContent = metin.slice(i);
        if (i >= metin.length) { clearInterval(yazi); gizle = setTimeout(balonKapat, okuma); }
      }, 26);
    }
    // Bölümün mesajı yalnızca bir kez söylenir
    function ilkKezSoyle(b) {
      if (!b || gosterildi.has(b)) return;
      gosterildi.add(b);
      konus(b.getAttribute('data-peri-soz'));
    }
    function pozVer(poz) {
      resimler.forEach(function (r) { r.classList.toggle('is-aktif', r.getAttribute('data-poz') === poz); });
    }

    // Kübik eğri boyunca uçuş
    function uc(hedef, k1, k2, sure, bitince) {
      ucus = { a: { x: konum.x, y: konum.y }, b: hedef, k1: k1, k2: k2, t0: performance.now(), sure: sure, bitince: bitince };
    }
    function egri(u, a, k1, k2, b) {
      var v = 1 - u;
      return v * v * v * a + 3 * v * v * u * k1 + 3 * v * u * u * k2 + u * u * u * b;
    }
    function tarafaUc(yeniTaraf, bitince) {
      taraf = yeniTaraf; basladi = true;
      var h = durak(taraf), yuk = Math.min(H * 0.38, 320);
      // Ekranın ortasından yukarı doğru kavis çizerek karşı tarafa geçer
      uc(h, { x: konum.x + (h.x - konum.x) * 0.25, y: Math.min(konum.y, h.y) - yuk },
        { x: konum.x + (h.x - konum.x) * 0.75, y: Math.min(konum.y, h.y) - yuk }, Math.abs(h.x - konum.x) > 200 ? 1700 : 1100, bitince);
    }
    function tur() {
      // Beklerken olduğu yerde küçük bir tur atar
      var h = durak(taraf), ic = taraf === 'sag' ? -1 : 1, b = boy();
      uc(h, { x: h.x + ic * b.w * 1.9, y: h.y - b.h * 2.2 }, { x: h.x - ic * b.w * 0.5, y: h.y - b.h * 2.6 }, 1900);
    }

    function kare(t) {
      if (bitti) return;
      var x, y, hedefAci = 0;
      if (ucus) {
        var u = Math.min(1, (t - ucus.t0) / ucus.sure), e = u < 0.5 ? 4 * u * u * u : 1 - Math.pow(-2 * u + 2, 3) / 2;
        x = egri(e, ucus.a.x, ucus.k1.x, ucus.k2.x, ucus.b.x);
        y = egri(e, ucus.a.y, ucus.k1.y, ucus.k2.y, ucus.b.y);
        var dx = x - konum.x;
        if (Math.abs(dx) > 0.6) bakis = dx < 0 ? 1 : -1; // "göster" pozu sola bakar; sağa uçarken aynala
        hedefAci = Math.max(-18, Math.min(18, dx * 1.2));
        konum = { x: x, y: y };
        kutu.classList.add('is-ucuyor'); // kanatlar hızlı çırpsın
        if (u >= 1) { kutu.classList.remove('is-ucuyor'); var f = ucus.bitince; ucus = null; sonTur = t; if (f) f(); }
      } else if (basladi) {
        var h = durak(taraf);
        konum = { x: h.x, y: h.y };
        bakis = taraf === 'sol' ? -1 : 1; // köşede içeriğe doğru baksın
        if (!kutu.classList.contains('is-balon') && t - sonTur > 15000) tur();
      }
      // Havada süzülme: hafif yalpalama
      var s = t / 1000, sx = Math.sin(s * 1.3) * 10, sy = Math.sin(s * 2.1) * 7;
      egimAci += (hedefAci + Math.sin(s * 1.7) * 3 - egimAci) * 0.12;
      ciz(konum.x + sx, konum.y + sy, egimAci, bakis);
      requestAnimationFrame(kare);
    }

    function sec(b) {
      if (b === aktif) return;
      var ilk = !aktif;
      aktif = b;
      balonKapat();
      pozVer(b.getAttribute('data-peri-poz'));
      if (azHareket) { ilkKezSoyle(b); return; }
      var sira = Array.prototype.indexOf.call(bolumler, b);
      tarafaUc(ilk || sira % 2 === 0 ? 'sag' : 'sol', function () {
        if (iz) iz.patla(20);
        if (aktif === b) ilkKezSoyle(b);
      });
    }
    function simdikiBolum() {
      var orta = H / 2, bu = bolumler[0];
      bolumler.forEach(function (b) { var r = b.getBoundingClientRect(); if (r.top <= orta && r.bottom >= orta) bu = b; });
      return bu;
    }

    if (azHareket) {
      var d = durak('sag'); ciz(d.x, d.y, 0, 1);
      sec(simdikiBolum());
    } else {
      ciz(konum.x, konum.y, 0, 1);
      requestAnimationFrame(kare);
      // Giriş: ekranın dışından uçarak gelsin
      setTimeout(function () { sec(simdikiBolum()); }, 900);
    }

    // Ekranın ortasından geçen bölüm "aktif" sayılır
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (girdiler) {
        girdiler.forEach(function (g) { if (g.isIntersecting && aktif) sec(g.target); });
      }, { rootMargin: '-48% 0px -48% 0px' });
      bolumler.forEach(function (b) { io.observe(b); });
    }
    window.addEventListener('resize', function () {
      W = window.innerWidth; H = window.innerHeight;
      if (azHareket) { var d = durak('sag'); ciz(d.x, d.y, 0, 1); }
    });

    // Tıklanınca bu bölümün mesajını yeniden söyler (yalnızca istenince)
    govde.addEventListener('click', function () {
      if (aktif) konus(aktif.getAttribute('data-peri-soz'));
      if (iz) iz.patla(30);
    });
    kutu.querySelector('.uyg-peri__kapat').addEventListener('click', function () {
      balonKapat();
      bitti = true;
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
