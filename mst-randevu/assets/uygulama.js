/* MST Yazar Paneli tanıtım sayfası — hareketler.
 * - Kaydırınca belirme: bölümlerin içindeki öğeler görünür alana girince sırayla belirir
 * - Sayaç: telefondaki satış sayısı 0'dan yukarı sayar
 * - Canlı bildirim: girişteki bildirim balonu birkaç saniyede bir değişir
 * - 3B eğim: fare girişteki telefonun üstünde gezinince telefon hafifçe eğilir
 * - Giriş arka planı: fotoğraf yerine telefonun arkasında dolaşan altın ışık, silik kitap deseni, ışıltı tozu
 * - Kitap perisi: önce rehberlik isteyip istemediğinizi sorar. Evet: bölüm değiştikçe o bölümün
 *   kutusunun yanına kavis çizerek uçar (kaydırırken takip eder); her bölüm için "•••" işareti çıkar,
 *   dokununca bir kez anlatır. Göz kırpar, el/asa sallar, zıplar, tur atarken takla atar.
 *   Hayır ya da ×: vedalaşıp uçarak gider, köşede geri çağırma düğmesi kalır (cevap o oturumda hatırlanır).
 *   Arkasında ışıltı izi bırakır
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

  /* ---------- Giriş arka planı: hareketli altın ışık ---------- */
  // Fotoğraf yerine kodla çizilir: telefonun arkasında yavaşça dolaşan altın ışık bulutları ve ışık
  // dalgaları (küçük bir tuvale çizilip büyütülür; bu yüzden yumuşak görünür ve ucuzdur), üstünde
  // çok silik bir açık kitap çizgi deseni ve perinin ışıltı tozu. Fare gezdikçe ışık hafifçe onu izler.
  var ISIK = {
    olcek: 0.25,            // ışık tuvalinin çözünürlüğü (küçük = daha yumuşak)
    toz: 80,                // ışıltı tozu (telefonda yarısı)
    desen: 0.09,            // kitap deseninin görünürlüğü
    fare: 40                // ışığın fareye doğru kayması (px)
  };
  // Işık bulutları: telefonun merkezine göre konum (ox, oy), yörünge genişliği (hx, hy), hızı (fx, fy)
  var BULUTLAR = [
    { k: 0, r: 0.62, a: 0.50, ox: 0, oy: 0, hx: 0.16, hy: 0.10, fx: 0.21, fy: 0.13, faz: 0 },
    { k: 1, r: 0.42, a: 0.42, ox: 0.14, oy: -0.2, hx: 0.2, hy: 0.14, fx: 0.15, fy: 0.23, faz: 1.7 },
    { k: 2, r: 0.55, a: 0.38, ox: -0.22, oy: 0.22, hx: 0.18, hy: 0.1, fx: 0.11, fy: 0.17, faz: 3.1 },
    { k: 3, r: 0.26, a: 0.30, ox: 0.22, oy: 0.18, hx: 0.14, hy: 0.18, fx: 0.26, fy: 0.19, faz: 4.4 },
    { k: 1, r: 0.34, a: 0.22, ox: -0.06, oy: -0.36, hx: 0.28, hy: 0.07, fx: 0.09, fy: 0.21, faz: 2.2 }
  ];
  // Işık dalgaları: telefonun arkasından sağa doğru yükselen, dalgalanan altın şeritler
  var DALGALAR = [
    { oy: 0.02, egim: -0.22, genlik: 0.07, sik: 2.3, hiz: 0.35, kalin: 0.05, a: 0.26 },
    { oy: 0.16, egim: -0.14, genlik: 0.05, sik: 3.2, hiz: -0.27, kalin: 0.034, a: 0.18 }
  ];

  function heroIsik() {
    var hero = document.querySelector('.uyg-hero');
    if (!hero) return;
    var tuval = document.createElement('canvas'), ctx = tuval.getContext && tuval.getContext('2d');
    if (!ctx) return;
    tuval.className = 'uyg-hero__isik';
    tuval.setAttribute('aria-hidden', 'true');
    hero.insertBefore(tuval, hero.firstChild);
    hero.classList.add('has-isik');
    var tel = hero.querySelector('.uyg-hero__gorsel');
    var kucuk = document.createElement('canvas'), kc = kucuk.getContext('2d');
    var desen = document.createElement('canvas');
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var W = 0, H = 0, U = 1, merkez = { x: 0, y: 0 }, dar = false, ortu = null, tozlar = [], serit = [];
    var fareHedef = { x: 0, y: 0, a: 0 }, fare = { x: 0, y: 0, a: 0 }, gorunur = true, calisiyor = false;

    function kure(renk) {
      var c = document.createElement('canvas'), g = c.getContext('2d');
      c.width = c.height = 128;
      var r = g.createRadialGradient(64, 64, 0, 64, 64, 64);
      r.addColorStop(0, renk); r.addColorStop(0.45, renk.replace(/[\d.]+\)$/, '0.45)')); r.addColorStop(1, 'rgba(0,0,0,0)');
      g.fillStyle = r; g.fillRect(0, 0, 128, 128);
      return c;
    }
    var KURELER = [kure('rgba(220,152,20,1)'), kure('rgba(240,180,60,1)'), kure('rgba(184,125,14,1)'), kure('rgba(255,214,130,1)')];
    function parilti(yildiz) {
      var c = document.createElement('canvas'), g = c.getContext('2d'), m = 16;
      c.width = c.height = 32;
      var r = g.createRadialGradient(m, m, 0, m, m, m);
      r.addColorStop(0, 'rgba(255,246,220,1)'); r.addColorStop(0.3, 'rgba(240,180,60,.8)'); r.addColorStop(1, 'rgba(220,152,20,0)');
      g.fillStyle = r; g.beginPath();
      if (yildiz) for (var k = 0; k < 8; k++) { var a = k * Math.PI / 4, rr = k % 2 ? 3 : m; g.lineTo(m + Math.cos(a) * rr, m + Math.sin(a) * rr); }
      else g.arc(m, m, m, 0, Math.PI * 2);
      g.fill();
      return c;
    }
    var PARILTI = [parilti(false), parilti(true)];

    // Çok silik açık kitap: telefonun altında, sayfalarında satır çizgileri
    function desenCiz() {
      desen.width = Math.round(W * dpr); desen.height = Math.round(H * dpr);
      var g = desen.getContext('2d');
      g.setTransform(dpr, 0, 0, dpr, 0, 0);
      var bw = Math.min(W * (dar ? 0.95 : 0.6), 760), bh = bw * 0.36;
      var cx = merkez.x, sy = merkez.y + (tel ? tel.offsetHeight * 0.3 : 120);
      g.strokeStyle = 'rgba(240,180,60,1)'; g.lineCap = 'round';
      [-1, 1].forEach(function (s) {
        g.lineWidth = 1.6;
        g.beginPath();
        g.moveTo(cx, sy);
        g.quadraticCurveTo(cx + s * bw * 0.24, sy - bh * 0.16, cx + s * bw / 2, sy - bh * 0.02);
        g.lineTo(cx + s * bw / 2, sy + bh);
        g.quadraticCurveTo(cx + s * bw * 0.24, sy + bh * 0.84, cx, sy + bh * 1.02);
        g.closePath(); g.stroke();
        g.lineWidth = 1;
        for (var i = 1; i < 10; i++) {
          var t = i / 10, y0 = sy + bh * t;
          g.beginPath();
          g.moveTo(cx + s * bw * 0.06, y0 + bh * 0.02);
          g.quadraticCurveTo(cx + s * bw * 0.25, y0 - bh * 0.15 * (1 - t * 0.6), cx + s * bw * (i === 9 ? 0.3 : 0.44), y0 - bh * 0.01);
          g.stroke();
        }
      });
      g.beginPath(); g.moveTo(cx, sy); g.lineTo(cx, sy + bh * 1.02); g.lineWidth = 2; g.stroke();
    }

    function boyutla() {
      var r = hero.getBoundingClientRect();
      W = Math.max(1, Math.round(r.width)); H = Math.max(1, Math.round(r.height));
      dar = W < 860; U = dar ? Math.max(W * 1.35, 520) : Math.min(W, 1000); // telefonda ışık dar ekrana göre büyütülür
      tuval.width = Math.round(W * dpr); tuval.height = Math.round(H * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      kucuk.width = Math.max(1, Math.round(W * ISIK.olcek)); kucuk.height = Math.max(1, Math.round(H * ISIK.olcek));
      if (tel) {
        // Telefonun giriş animasyonundaki kaymadan etkilenmemek için yerleşim (offset) değerleri
        var x = 0, y = 0, e = tel;
        while (e && e !== hero) { x += e.offsetLeft; y += e.offsetTop; e = e.offsetParent; }
        merkez = { x: x + tel.offsetWidth / 2, y: y + tel.offsetHeight / 2 };
      } else merkez = { x: W * 0.72, y: H * 0.5 };
      // Metin tarafı koyu ve sakin kalsın: masaüstünde soldan, telefonda üstten koyulaşan örtü
      if (dar) {
        var ust = tel ? Math.max(0.2, Math.min(0.8, (merkez.y - (tel.offsetHeight / 2) - 40) / H)) : 0.5;
        ortu = ctx.createLinearGradient(0, 0, 0, H);
        ortu.addColorStop(0, 'rgba(22,22,22,.9)'); ortu.addColorStop(ust, 'rgba(22,22,22,.6)'); ortu.addColorStop(Math.min(1, ust + 0.12), 'rgba(22,22,22,0)');
      } else {
        ortu = ctx.createLinearGradient(0, 0, W, 0);
        ortu.addColorStop(0, 'rgba(22,22,22,.92)'); ortu.addColorStop(0.36, 'rgba(22,22,22,.7)'); ortu.addColorStop(0.56, 'rgba(22,22,22,0)');
      }
      serit = DALGALAR.map(function (d) {
        var x0 = merkez.x - U * 0.85, x1 = W + 40, gr = kc.createLinearGradient(x0 * ISIK.olcek, 0, x1 * ISIK.olcek, 0);
        gr.addColorStop(0, 'rgba(240,180,60,0)'); gr.addColorStop(0.35, 'rgba(240,180,60,1)'); gr.addColorStop(0.7, 'rgba(220,152,20,.8)'); gr.addColorStop(1, 'rgba(220,152,20,0)');
        return { x0: x0, x1: x1, renk: gr };
      });
      var adet = Math.round(ISIK.toz * (dar ? 0.5 : 1));
      tozlar = [];
      for (var i = 0; i < adet; i++) tozlar.push(yeniToz(true));
      desenCiz();
    }
    function yeniToz(ilk) {
      // Çoğu telefonun çevresinde, sağ tarafta yoğun
      var x = merkez.x + (Math.random() - 0.5) * (dar ? W * 1.1 : W * 0.75);
      return {
        x: Math.max(0, Math.min(W, x)), y: ilk ? Math.random() * H : H + 10,
        hiz: 5 + Math.random() * 14, boy: 3 + Math.random() * 7, faz: Math.random() * 6.28, sik: 0.8 + Math.random() * 2.2,
        derin: 0.3 + Math.random() * 0.7, p: PARILTI[Math.random() < 0.22 ? 1 : 0]
      };
    }

    function ciz(t, dt) {
      var s = t / 1000, o = ISIK.olcek;
      fare.x += (fareHedef.x - fare.x) * 0.05; fare.y += (fareHedef.y - fare.y) * 0.05; fare.a += (fareHedef.a - fare.a) * 0.05;
      var mx = merkez.x + fare.x * ISIK.fare, my = merkez.y + fare.y * ISIK.fare;
      // 1) Işık: küçük tuvale bulutlar ve dalgalar
      kc.setTransform(1, 0, 0, 1, 0, 0);
      kc.globalCompositeOperation = 'source-over';
      kc.clearRect(0, 0, kucuk.width, kucuk.height);
      kc.globalCompositeOperation = 'lighter';
      BULUTLAR.forEach(function (b) {
        var x = mx + (b.ox + b.hx * Math.sin(s * b.fx + b.faz)) * U;
        var y = my + (b.oy + b.hy * Math.sin(s * b.fy + b.faz * 1.3)) * U * (dar ? 1.4 : 1);
        var R = b.r * U * (1 + 0.12 * Math.sin(s * 0.23 + b.faz));
        kc.globalAlpha = b.a * (0.85 + 0.15 * Math.sin(s * 0.4 + b.faz));
        kc.drawImage(KURELER[b.k], (x - R) * o, (y - R) * o, 2 * R * o, 2 * R * o);
      });
      DALGALAR.forEach(function (d, i) {
        var sr = serit[i], adim = 12, x, ust = [], alt = [];
        for (x = sr.x0; x <= sr.x1; x += adim) {
          var c = merkez.y + d.oy * U + d.egim * (x - merkez.x) + Math.sin(x / U * d.sik + s * d.hiz) * d.genlik * U;
          var k = d.kalin * U * (0.55 + 0.45 * Math.sin(x / U * 1.7 - s * 0.3 + i));
          ust.push([x, c - k]); alt.push([x, c + k]);
        }
        kc.globalAlpha = d.a;
        kc.fillStyle = sr.renk;
        kc.beginPath();
        ust.forEach(function (n, j) { if (j) kc.lineTo(n[0] * o, n[1] * o); else kc.moveTo(n[0] * o, n[1] * o); });
        for (var j = alt.length - 1; j >= 0; j--) kc.lineTo(alt[j][0] * o, alt[j][1] * o);
        kc.closePath(); kc.fill();
      });
      // Fare hero'nun içindeyse imlecin çevresinde hafif bir ışık
      if (fare.a > 0.01) {
        var fr = U * 0.22;
        kc.globalAlpha = 0.22 * fare.a;
        kc.drawImage(KURELER[3], (fare.px - fr) * o, (fare.py - fr) * o, 2 * fr * o, 2 * fr * o);
      }
      kc.globalAlpha = 1;
      // 2) Ana tuval: ışığı büyüterek çiz, üstüne desen, toz ve örtü
      ctx.clearRect(0, 0, W, H);
      ctx.globalCompositeOperation = 'source-over';
      ctx.imageSmoothingEnabled = true;
      ctx.drawImage(kucuk, 0, 0, W, H);
      ctx.globalAlpha = ISIK.desen * (0.7 + 0.3 * Math.sin(s * 0.5));
      ctx.drawImage(desen, fare.x * 10, fare.y * 10, W, H);
      ctx.globalCompositeOperation = 'lighter';
      tozlar.forEach(function (p, i) {
        if (dt) {
          p.y -= p.hiz * dt; p.x += Math.sin(s * 0.6 + p.faz) * 6 * dt;
          if (p.y < -12) tozlar[i] = p = yeniToz(false);
        }
        var par = 0.5 + 0.5 * Math.sin(s * p.sik + p.faz);
        ctx.globalAlpha = 0.15 + 0.75 * par * par;
        var b = p.boy * (0.7 + 0.3 * par);
        ctx.drawImage(p.p, p.x + fare.x * 18 * p.derin - b / 2, p.y + fare.y * 18 * p.derin - b / 2, b, b);
      });
      ctx.globalCompositeOperation = 'source-over';
      ctx.globalAlpha = 1;
      ctx.fillStyle = ortu; ctx.fillRect(0, 0, W, H);
    }

    var son = 0;
    function dongu(t) {
      if (!gorunur || document.hidden) { calisiyor = false; return; }
      var dt = son ? Math.min(0.05, (t - son) / 1000) : 0; son = t;
      ciz(t, dt);
      requestAnimationFrame(dongu);
    }
    function baslat() {
      if (calisiyor || azHareket) return;
      calisiyor = true; son = 0; requestAnimationFrame(dongu);
    }

    try {
      boyutla();
      ciz(7000, 0);
      tuval.classList.add('is-hazir');
    } catch (e) { tuval.remove(); hero.classList.remove('has-isik'); return; }
    var zaman = 0;
    var yenile = function () {
      clearTimeout(zaman);
      zaman = setTimeout(function () { boyutla(); if (azHareket) ciz(7000, 0); }, 150);
    };
    window.addEventListener('resize', yenile);
    if ('ResizeObserver' in window) new ResizeObserver(yenile).observe(hero);
    if (azHareket) return; // hareketi azalt: tek, durağan bir kare
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (g) { gorunur = g[0].isIntersecting; if (gorunur) baslat(); }).observe(hero);
    }
    document.addEventListener('visibilitychange', function () { if (!document.hidden) baslat(); });
    if (window.matchMedia('(hover: hover)').matches) {
      hero.addEventListener('pointermove', function (ev) {
        var r = hero.getBoundingClientRect();
        fareHedef.x = (ev.clientX - r.left) / r.width - 0.5; fareHedef.y = (ev.clientY - r.top) / r.height - 0.5; fareHedef.a = 1;
        fare.px = ev.clientX - r.left; fare.py = ev.clientY - r.top;
      });
      hero.addEventListener('pointerleave', function () { fareHedef.x = fareHedef.y = fareHedef.a = 0; });
    }
    baslat();
  }

  /* ---------- Kitap perisi ---------- */
  // Arkasında ışıltı izi bırakan parçacıklar: peri durunca kanatlarından hafifçe dökülür,
  // uçarken yol boyunca yoğun bir iz bırakır. Tuval perinin altında durur (arkasında görünür).
  function parcaciklar(tuval, hedef) {
    var ctx = tuval.getContext && tuval.getContext('2d');
    if (!ctx) return null;
    var dpr = Math.min(window.devicePixelRatio || 1, 2), W = 0, H = 0, liste = [], onceki = null, son = 0, calis = true, yay = true;
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
      // Peri gittiyse kalan ışıltılar sönünce dur
      if (!yay && !liste.length) { calis = false; ctx.clearRect(0, 0, W, H); return; }
      var dt = Math.min(0.05, (t - (son || t)) / 1000); son = t;
      var k = kaynak(), hiz = 0;
      if (!yay) onceki = null;
      else if (onceki) {
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
      if (yay) onceki = k;
      // Dururken kanatlardan hafif ışıltı dökülsün
      if (yay && hiz < 60 && Math.random() < (dar ? 0.3 : 0.5)) {
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
      // Belirli bir noktadan (asanın yıldızı) ışıltı saç
      nokta: function (x, y, adet, guc) {
        for (var i = 0; i < (adet || 1); i++) {
          var a = Math.random() * Math.PI * 2, v = (guc || 40) * (0.4 + Math.random());
          ekle(x, y, Math.cos(a) * v, Math.sin(a) * v - 20, 5 + Math.random() * 8);
        }
      },
      bitir: function () { yay = false; },
      baslat: function () {
        yay = true; onceki = null;
        if (!calis) { calis = true; son = 0; requestAnimationFrame(adim); }
      }
    };
  }

  function peri() {
    var kutu = document.querySelector('[data-uyg-peri]');
    var bolumler = document.querySelectorAll('[data-peri-poz]');
    var cagir = document.querySelector('[data-uyg-peri-cagir]');
    if (!kutu || !bolumler.length) return;
    var govde = kutu.querySelector('.uyg-peri__govde'), soz = kutu.querySelector('[data-uyg-peri-soz]');
    var balon = kutu.querySelector('.uyg-peri__balon'), secim = kutu.querySelector('.uyg-peri__secim');
    var resimler = kutu.querySelectorAll('.uyg-peri__poz'), tuval = document.querySelector('.uyg-peri-iz');
    var ust = document.querySelector('.mst-top');
    var yazilan = document.createElement('span'), kalan = document.createElement('span');
    kalan.className = 'uyg-peri__kalan';
    soz.appendChild(yazilan); soz.appendChild(kalan);
    var SORU = 'Merhaba! Ben kitap perisi. Sayfayı gezerken size rehberlik etmemi ister misiniz?';
    // mod: soru (cevap bekliyor) · rehber (bölüm bölüm yanında) · gidiyor (uçup çıkıyor) · yok
    var mod = 'yok', aktif = null, bekleyen = null, yazi = 0, gizle = 0, gosterildi = new Set(), dongu = false;
    var W = window.innerWidth, H = window.innerHeight;
    var konum = { x: W + 60, y: H * 0.5 }, gecis = null, turum = null, bakis = 1, yonAn = 1, egimAci = 0, sonTur = performance.now();
    var iz = null, aktifPoz = resimler[0];
    // Canlılık: göz kırpma, el/asa sallama (iki kare arasında gidip gelir), zıplama, konunca sekme
    var elBitis = 0, kirpBitis = 0, sonrakiKirp = performance.now() + 1800, zipla = null, sekme = -1e9;
    var sonrakiEylem = performance.now() + 4000, sonDikkat = 0;
    function el(sure) { elBitis = Math.max(elBitis, performance.now() + sure); }
    function ziplat() { if (!zipla) zipla = performance.now(); }
    function yildizNokta() {
      // Asanın yıldızının ekrandaki yeri (pozun iki karesi için görüntüde yüzde olarak kayıtlı)
      var v = (aktifPoz.getAttribute('data-yildiz') || '').split(';')[aktifPoz.classList.contains('is-hareket') ? 1 : 0];
      if (!v) return null;
      var p = v.split(','), r = govde.getBoundingClientRect(), px = +p[0];
      if (yonAn < 0) px = 100 - px;
      return { x: r.left + px / 100 * r.width, y: r.top + (+p[1]) / 100 * r.height };
    }

    function hatirla(d) { try { sessionStorage.setItem('mst_peri', d); } catch (e) { /* depolama kapalı */ } }
    function hatirlanan() { try { return sessionStorage.getItem('mst_peri'); } catch (e) { return null; } }
    function boy() { return { w: govde.offsetWidth, h: govde.offsetHeight }; }
    function ustSinir() { return (ust ? Math.max(0, ust.getBoundingClientRect().bottom) : 0) + 6; }

    // Bölümün kutusunu bul (birden çok eşleşirse sonuncusu)
    function kutusu(b) {
      var sec = b.getAttribute('data-peri-hedef');
      if (!sec) return null;
      var hepsi = b.querySelectorAll(sec);
      return hepsi.length ? hepsi[hepsi.length - 1] : null;
    }
    // Perinin duracağı yer: rehberlikte bölümün kutusunun yanı (tercih sırasındaki ilk sığan yer),
    // soru sorarken sağ alt köşe, giderken ekranın sağ üst dışı
    function hedefNokta() {
      var d = boy(), k = W < 640 ? 6 : 10;
      if (mod === 'gidiyor') return { x: W + 80, y: -d.h - 120, ex: 0, gorunur: false };
      var el = mod === 'rehber' && aktif && kutusu(aktif);
      if (!el) return { x: W - d.w - k - (W < 640 ? 0 : 14), y: H - d.h - k - (W < 640 ? 4 : 14), ex: 0, gorunur: true };
      var r = el.getBoundingClientRect(), cx = r.left + r.width / 2;
      var yerler = (aktif.getAttribute('data-peri-yer') || 'sag,kose-sag').split(',');
      var adaylar = {
        sag: { x: r.right + 8, y: r.top + r.height / 2 - d.h / 2 },
        sol: { x: r.left - d.w - 8, y: r.top + r.height / 2 - d.h / 2 },
        ust: { x: cx - d.w / 2, y: r.top - d.h + 6 },
        'kose-sag': { x: r.right - d.w * 0.6, y: r.top - d.h * 0.78 },
        'kose-sol': { x: r.left - d.w * 0.4, y: r.top - d.h * 0.78 }
      };
      var n = null;
      for (var i = 0; i < yerler.length && !n; i++) {
        var a = adaylar[yerler[i].trim()];
        if (a && a.x >= k && a.x + d.w <= W - k) n = a;
      }
      if (!n) n = adaylar[yerler[yerler.length - 1].trim()] || adaylar['kose-sag'];
      // Ekranın içinde kalsın (kutu ekrandan çıkınca kenarda bekler)
      return {
        x: Math.max(k, Math.min(W - d.w - k, n.x)),
        y: Math.max(ustSinir(), Math.min(H - d.h - k, n.y)),
        ex: cx,
        gorunur: r.bottom > ustSinir() + 60 && r.top < H * 0.7
      };
    }

    function ciz(x, y, aci, yon, ex, ey) {
      kutu.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0)';
      govde.style.transform = 'rotate(' + aci.toFixed(2) + 'deg) scale(' + ((+yon) * (ex || 1)).toFixed(3) + ',' + (ey || 1).toFixed(3) + ')';
    }
    // Balon perinin üstünde (yer yoksa altında) açılır ve ekranın dışına taşmaz; kuyruğu periyi gösterir
    function balonYerlestir() {
      var d = boy(), bw = balon.offsetWidth || 220, bh = balon.offsetHeight || 80;
      var sol = konum.x + d.w / 2 < W / 2;
      var x = sol ? 10 : d.w - 10 - bw;
      x = Math.max(8 - konum.x, Math.min(W - 8 - bw - konum.x, x));
      balon.style.left = x.toFixed(0) + 'px';
      balon.style.setProperty('--kuyruk', Math.max(14, Math.min(bw - 28, d.w / 2 - x - 7)).toFixed(0) + 'px');
      kutu.classList.toggle('is-sol', sol);
      kutu.classList.toggle('is-alt', konum.y - bh - 14 < ustSinir());
    }
    function balonKapat() {
      clearInterval(yazi); clearTimeout(gizle);
      kutu.classList.remove('is-balon');
    }
    // sure: yazı bittikten sonra balonun açık kalacağı süre (0: kendiliğinden kapanmaz)
    function konus(metin, sure, bitince) {
      balonKapat();
      secim.hidden = true;
      var okuma = sure === undefined ? 2200 + metin.length * 35 : sure;
      function bitti() {
        if (bitince) bitince();
        if (okuma) gizle = setTimeout(balonKapat, okuma);
      }
      if (azHareket) {
        yazilan.textContent = metin; kalan.textContent = '';
        balonYerlestir(); kutu.classList.add('is-balon');
        bitti();
        return;
      }
      // Daktilo: balon baştan tam boyutta açılır, yazı içinde belirir; yazarken elleriyle anlatır
      var i = 0;
      el(metin.length * 24 + 300);
      yazilan.textContent = ''; kalan.textContent = metin;
      balonYerlestir(); kutu.classList.add('is-balon');
      yazi = setInterval(function () {
        i += 1; yazilan.textContent = metin.slice(0, i); kalan.textContent = metin.slice(i);
        if (i >= metin.length) { clearInterval(yazi); bitti(); }
      }, 24);
    }
    // Okunmamış mesaj işareti: belirince dikkat çekmek için el sallayıp zıplar
    function isaret(acik) {
      kutu.classList.toggle('is-mesaj', !!acik);
      if (acik && !azHareket) { el(1300); ziplat(); sonDikkat = performance.now(); }
    }
    // Bölüm açıklaması kendiliğinden açılmaz (içeriği kapatmasın): "•••" işareti belirir, dokununca açılır
    function ilkKezSoyle(b) {
      if (!b || gosterildi.has(b)) return;
      gosterildi.add(b);
      isaret(true);
    }
    function pozVer(poz) {
      resimler.forEach(function (r) {
        var bu = r.getAttribute('data-poz') === poz;
        r.classList.toggle('is-aktif', bu);
        if (bu) aktifPoz = r;
      });
    }
    function yumusak(u) { return u < 0.5 ? 4 * u * u * u : 1 - Math.pow(-2 * u + 2, 3) / 2; }
    function uc(bitince) {
      var hn = hedefNokta(), mesafe = Math.abs(hn.x - konum.x) + Math.abs(hn.y - konum.y);
      turum = null;
      gecis = { a: { x: konum.x, y: konum.y }, t0: performance.now(), sure: Math.min(1900, 900 + mesafe * 0.9),
        kavis: mod === 'gidiyor' ? 40 : Math.min(H * 0.3, 60 + mesafe * 0.25), bitince: bitince };
    }

    function kare(t) {
      if (mod === 'yok') { dongu = false; return; }
      var h = hedefNokta(), onceki = konum, x, y, ucuyor = false;
      if (gecis) {
        // Kavisli uçuş: hedef kutu kaydırmayla yer değiştirse de ona varır
        var u = Math.min(1, (t - gecis.t0) / gecis.sure), e = yumusak(u);
        x = gecis.a.x + (h.x - gecis.a.x) * e;
        y = gecis.a.y + (h.y - gecis.a.y) * e - Math.sin(Math.PI * u) * gecis.kavis;
        ucuyor = true;
        if (u >= 1) { var f = gecis.bitince; gecis = null; sonTur = t; sekme = t; if (f) f(); }
      } else {
        // Kutuyu takip et (sayfa kayarken peri de yanında uçar)
        x = konum.x + (h.x - konum.x) * 0.16;
        y = konum.y + (h.y - konum.y) * 0.16;
        if (Math.abs(h.x - x) + Math.abs(h.y - y) > 30) ucuyor = true;
        if (mod === 'rehber' && !turum && !kutu.classList.contains('is-balon') && t - sonTur > 14000) { turum = { t0: t, yon: x + boy().w / 2 > W / 2 ? -1 : 1 }; el(1800); }
      }
      if (mod === 'yok') { dongu = false; return; } // uçuş bitince gizlendiyse
      var dx = x - onceki.x;
      konum = { x: x, y: y };
      // Beklerken olduğu yerde küçük bir tur (halka) atar
      var tx = 0, ty = 0, takla = 0;
      if (turum) {
        var v = Math.min(1, (t - turum.t0) / 1800), a = yumusak(v) * Math.PI * 2;
        tx = Math.sin(a) * 70 * turum.yon; ty = -(1 - Math.cos(a)) * 50;
        takla = -360 * yumusak(v) * turum.yon; // tur atarken takla da atar
        ucuyor = true;
        if (v >= 1) { turum = null; sonTur = t; sekme = t; }
      }
      // Yön: tüm pozlar sola dönük çizildi; sağa bakması gerekince aynalanır.
      // Gerçek uçuşta (bölüm değişimi, tur) gittiği yöne; dururken ve kutuyu takip ederken kutusuna,
      // kutusu tam altındaysa sayfanın ortasına bakar (kaydırmadaki küçük kıpırtılarla dönmez)
      var ucusta = gecis || turum, merkez = x + boy().w / 2;
      if (ucusta) {
        if (Math.abs(dx) > 1.5) bakis = dx < 0 ? 1 : -1;
      } else {
        var hedefX = h.ex || W / 2;
        if (Math.abs(hedefX - merkez) < 30) hedefX = W / 2;
        if (Math.abs(hedefX - merkez) > 12) bakis = hedefX < merkez ? 1 : -1;
      }
      yonAn += (bakis - yonAn) * 0.18; // dönüşü yumuşat (anlık aynalama yerine döner gibi)
      if (Math.abs(yonAn) < 0.08) yonAn = yonAn < 0 ? -0.08 : 0.08;
      kutu.classList.toggle('is-ucuyor', ucuyor);
      var s = t / 1000, egimHedef = ucusta ? Math.max(-18, Math.min(18, dx * 1.2)) : 0;
      egimAci += (egimHedef + Math.sin(s * 1.7) * 3 - egimAci) * 0.12;

      // Beklerken ara sıra bir şey yapar: el/asa sallar, zıplar ya da asasından ışıltı saçar
      var bosta = !ucusta && !kutu.classList.contains('is-balon');
      if (bosta && t > sonrakiEylem) {
        var r = Math.random();
        if (r < 0.4) el(1100); else if (r < 0.7) ziplat();
        else { var yp = yildizNokta(); if (yp && iz) iz.nokta(yp.x, yp.y, 14, 90); el(500); }
        sonrakiEylem = t + 3200 + Math.random() * 3300;
      }
      if (kutu.classList.contains('is-mesaj') && bosta && t - sonDikkat > 7000) { el(900); ziplat(); sonDikkat = t; }
      // Zıplama: çömelip sıçrar, inerken yaylanır
      var zy = 0, ex = 1, ey = 1;
      if (zipla) {
        var zu = (t - zipla) / 520;
        if (zu >= 1) { zipla = null; sekme = t; }
        else if (zu < 0.15) { var c = Math.sin(zu / 0.15 * Math.PI); ey -= 0.12 * c; ex += 0.1 * c; }
        else { var hu = (zu - 0.15) / 0.85; zy = -22 * Math.sin(Math.PI * hu); ey += 0.08 * Math.sin(Math.PI * hu); ex -= 0.05 * Math.sin(Math.PI * hu); }
      }
      // Konunca sekme (yaylanarak oturur)
      var sd = t - sekme;
      if (sd < 450) { var q = Math.sin(sd / 55) * Math.exp(-sd / 130) * 0.16; ey -= q; ex += q; }
      // Uçarken hafifçe uzar, süzülürken nefes alır gibi esner
      if (ucusta) { ey *= 1.05; ex *= 0.96; }
      else { ey *= 1 + 0.025 * Math.cos(s * 2.1); ex *= 1 - 0.02 * Math.cos(s * 2.1); }
      // Göz kırpma (ara sıra çift)
      if (t > sonrakiKirp) { kirpBitis = t + 130; sonrakiKirp = t + (Math.random() < 0.25 ? 320 : 2400 + Math.random() * 3200); }
      aktifPoz.classList.toggle('is-kirp', t < kirpBitis);
      // El/asa sallama: iki kare arasında gidip gelir; sallarken asadan ışıltı dökülür
      var sallar = t < elBitis;
      aktifPoz.classList.toggle('is-hareket', sallar && Math.floor(t / 230) % 2 === 1);
      if (iz && !gecis && Math.random() < (sallar ? 0.3 : 0.04)) { var yn = yildizNokta(); if (yn) iz.nokta(yn.x, yn.y, 1, 30); }

      ciz(x + tx + Math.sin(s * 1.3) * 4, y + ty + zy + Math.sin(s * 2.1) * 3, egimAci + takla, yonAn, ex, ey);
      if (mod === 'rehber' && bekleyen && !gecis) {
        if (bekleyen !== aktif) bekleyen = null;
        else if (h.gorunur) { ilkKezSoyle(bekleyen); bekleyen = null; }
      }
      if (kutu.classList.contains('is-balon')) balonYerlestir();
      requestAnimationFrame(kare);
    }
    function donguBaslat() {
      if (azHareket || dongu) return;
      dongu = true; requestAnimationFrame(kare);
    }
    function sabitCiz() {
      if (!azHareket || mod === 'yok') return;
      var h = hedefNokta(), hx = h.ex || W / 2, m = h.x + boy().w / 2;
      if (Math.abs(hx - m) < 30) hx = W / 2;
      konum = h; ciz(h.x, h.y, 0, hx < m ? 1 : -1);
      if (kutu.classList.contains('is-balon')) balonYerlestir();
    }

    function simdikiBolum() {
      var orta = H / 2, bu = bolumler[0];
      bolumler.forEach(function (b) { var r = b.getBoundingClientRect(); if (r.top <= orta && r.bottom >= orta) bu = b; });
      return bu;
    }
    function sec(b) {
      if (b === aktif) return;
      aktif = b;
      if (mod !== 'rehber') return;
      balonKapat();
      isaret(false);
      pozVer(b.getAttribute('data-peri-poz'));
      if (azHareket) { sabitCiz(); ilkKezSoyle(b); return; }
      bekleyen = null;
      uc(function () {
        if (iz) iz.patla(20);
        if (aktif === b) bekleyen = b; // kutusu ekranda görününce konuşur
      });
    }

    // Periyi ekrana getir: soru sorarak ya da doğrudan rehber olarak
    function gel(yeniMod) {
      mod = yeniMod;
      cagir && (cagir.hidden = true);
      kutu.hidden = false;
      if (!azHareket && tuval) { if (!iz) iz = parcaciklar(tuval, govde); else iz.baslat(); }
      aktif = simdikiBolum();
      if (mod === 'soru') {
        pozVer('selam');
        var sor = function () {
          if (iz) iz.patla(24);
          el(2200); ziplat();
          konus(SORU, 0, function () { secim.hidden = false; balonYerlestir(); });
        };
        if (azHareket) { sabitCiz(); sor(); } else { ciz(konum.x, konum.y, 0, yonAn); donguBaslat(); uc(sor); }
      } else {
        var b = aktif; aktif = null; sec(b);
        if (!azHareket) donguBaslat();
      }
    }
    // Vedalaşıp uçarak gider; köşede geri çağırma düğmesi kalır
    function git() {
      if (mod === 'yok' || mod === 'gidiyor') return;
      hatirla('hayir');
      secim.hidden = true;
      isaret(false);
      pozVer('selam');
      var bitir = function () {
        mod = 'yok';
        balonKapat();
        kutu.hidden = true;
        if (iz) iz.bitir();
        if (cagir) cagir.hidden = false;
        konum = { x: W + 60, y: H * 0.5 };
      };
      if (azHareket) { konus('Görüşmek üzere!', 900, null); setTimeout(bitir, 1000); return; }
      konus('Görüşmek üzere! İsterseniz beni köşeden yeniden çağırabilirsiniz.', 0, function () {
        setTimeout(function () {
          balonKapat();
          if (iz) iz.patla(26);
          mod = 'gidiyor'; bekleyen = null;
          uc(bitir);
        }, 1100);
      });
    }

    // Ekranın ortasından geçen bölüm "aktif" sayılır
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (girdiler) {
        girdiler.forEach(function (g) { if (g.isIntersecting && mod !== 'yok') { if (mod === 'rehber') sec(g.target); else aktif = g.target; } });
      }, { rootMargin: '-48% 0px -48% 0px' });
      bolumler.forEach(function (b) { io.observe(b); });
    }
    window.addEventListener('resize', function () { W = window.innerWidth; H = window.innerHeight; sabitCiz(); });
    window.addEventListener('scroll', sabitCiz, { passive: true });

    // Soru cevabı
    kutu.querySelectorAll('[data-peri-cevap]').forEach(function (d) {
      d.addEventListener('click', function () {
        if (d.getAttribute('data-peri-cevap') !== 'evet') { git(); return; }
        hatirla('evet');
        secim.hidden = true;
        mod = 'rehber';
        pozVer('sevinc');
        if (iz) iz.patla(30);
        ziplat(); el(1500);
        var b = aktif || simdikiBolum();
        gosterildi.add(b);
        konus('Harika! Aşağı kaydırın, her bölümde yanınızda olacağım.', undefined, null);
        setTimeout(function () { if (mod === 'rehber' && aktif === b) { aktif = null; gosterildi.delete(b); sec(b); } }, 3200);
      });
    });
    // Tıklanınca bu bölümün mesajını (soru bekliyorsa soruyu) yeniden söyler
    function ac() {
      isaret(false);
      if (mod === 'soru') konus(SORU, 0, function () { secim.hidden = false; balonYerlestir(); });
      else if (mod === 'rehber' && aktif) konus(aktif.getAttribute('data-peri-soz'));
      if (iz) iz.patla(30);
    }
    govde.addEventListener('click', ac);
    kutu.querySelector('[data-uyg-peri-ac]').addEventListener('click', ac);
    // Balondaki × yalnızca balonu kapatır (soru bekliyorsa 💬 işareti kalır)
    kutu.querySelector('[data-uyg-peri-balon-kapat]').addEventListener('click', function (ev) {
      ev.stopPropagation();
      balonKapat();
      if (mod === 'soru') isaret(true);
    });
    // Ziyaretçi periyi istediği an kapatabilir (perinin üstündeki ×): vedalaşıp uçar
    kutu.querySelectorAll('[data-uyg-peri-kapat]').forEach(function (d) {
      d.addEventListener('click', function (ev) { ev.stopPropagation(); git(); });
    });
    if (cagir) cagir.addEventListener('click', function () { hatirla('evet'); gosterildi = new Set(); gel('rehber'); });

    // Başlangıç: bu oturumda "hayır" dendiyse yalnızca çağırma düğmesi, "evet" dendiyse doğrudan rehber
    var onceki = hatirlanan();
    if (onceki === 'hayir') { if (cagir) cagir.hidden = false; return; }
    setTimeout(function () { gel(onceki === 'evet' ? 'rehber' : 'soru'); }, azHareket ? 0 : 900);
  }

  function basla() {
    heroIsik();
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
