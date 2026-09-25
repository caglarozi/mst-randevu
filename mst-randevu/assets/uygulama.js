/* MST Yazar Paneli tanıtım sayfası — hareketler.
 * - Kaydırınca belirme: bölümlerin içindeki öğeler görünür alana girince sırayla belirir
 * - Sayaç: telefondaki satış sayısı 0'dan yukarı sayar
 * - Canlı bildirim: girişteki bildirim balonu birkaç saniyede bir değişir
 * - 3B eğim: fare girişteki telefonun üstünde gezinince telefon hafifçe eğilir
 * - Kitap perisi: bölüm değiştikçe o bölümün kutusunun yanına kavis çizerek uçar (kaydırırken kutuyu
 *   takip eder), poz değiştirir ve her bölümü bir kez anlatır; beklerken ara sıra tur atar,
 *   arkasında ışıltı izi bırakır
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
    var ust = document.querySelector('.mst-top');
    var yazilan = document.createElement('span'), kalan = document.createElement('span');
    kalan.className = 'uyg-peri__kalan';
    soz.appendChild(yazilan); soz.appendChild(kalan);
    var aktif = null, bekleyen = null, yazi = 0, gizle = 0, gosterildi = new Set(), bitti = false;
    var W = window.innerWidth, H = window.innerHeight;
    // konum: perinin sol üst köşesi (ekran pikseli); gecis: bölüm değişince yapılan kavisli uçuş
    var konum = { x: W + 60, y: H * 0.5 }, gecis = null, turum = null, bakis = 1, egimAci = 0, sonTur = performance.now();
    kutu.hidden = false;
    var iz = azHareket || !tuval ? null : parcaciklar(tuval, govde);

    function boy() { return { w: govde.offsetWidth, h: govde.offsetHeight }; }
    function ustSinir() { return (ust ? Math.max(0, ust.getBoundingClientRect().bottom) : 0) + 6; }

    // Bölümün kutusunu bul (birden çok eşleşirse sonuncusu)
    function kutusu(b) {
      var sec = b.getAttribute('data-peri-hedef');
      if (!sec) return null;
      var hepsi = b.querySelectorAll(sec);
      return hepsi.length ? hepsi[hepsi.length - 1] : null;
    }
    // Kutunun yanında perinin duracağı yer; tercih sırasındaki ilk sığan yer seçilir
    function hedefNokta(b) {
      var d = boy(), k = W < 640 ? 6 : 10, el = b && kutusu(b);
      if (!el) return { x: W - d.w - k, y: H - d.h - k, ex: 0, gorunur: true };
      var r = el.getBoundingClientRect(), cx = r.left + r.width / 2;
      var yerler = (b.getAttribute('data-peri-yer') || 'sag,kose-sag').split(',');
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

    function ciz(x, y, aci, yon) {
      kutu.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0)';
      govde.style.transform = 'rotate(' + aci.toFixed(2) + 'deg) scaleX(' + yon + ')';
    }
    // Balon perinin üstünde (yer yoksa altında) açılır ve ekranın dışına taşmaz; kuyruğu periyi gösterir
    function balonYerlestir() {
      var d = boy(), bal = kutu.querySelector('.uyg-peri__balon'), bw = bal.offsetWidth || 220, bh = bal.offsetHeight || 80;
      var sol = konum.x + d.w / 2 < W / 2;
      var x = sol ? 12 : d.w - 12 - bw;
      x = Math.max(8 - konum.x, Math.min(W - 8 - bw - konum.x, x));
      bal.style.left = x.toFixed(0) + 'px';
      bal.style.setProperty('--kuyruk', Math.max(14, Math.min(bw - 28, d.w / 2 - x - 7)).toFixed(0) + 'px');
      kutu.classList.toggle('is-sol', sol);
      kutu.classList.toggle('is-alt', konum.y - bh - 14 < ustSinir());
    }
    function balonKapat() {
      clearInterval(yazi); clearTimeout(gizle);
      kutu.classList.remove('is-balon');
    }
    function konus(metin) {
      balonKapat();
      var okuma = 2200 + metin.length * 35;
      if (azHareket) {
        yazilan.textContent = metin; kalan.textContent = '';
        balonYerlestir(); kutu.classList.add('is-balon');
        gizle = setTimeout(balonKapat, okuma);
        return;
      }
      // Daktilo: balon baştan tam boyutta açılır, yazı içinde belirir; bitince okuma süresi kadar kalır
      var i = 0;
      yazilan.textContent = ''; kalan.textContent = metin;
      balonYerlestir(); kutu.classList.add('is-balon');
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
    function yumusak(u) { return u < 0.5 ? 4 * u * u * u : 1 - Math.pow(-2 * u + 2, 3) / 2; }

    function kare(t) {
      if (bitti) return;
      var h = hedefNokta(aktif), onceki = konum, x, y, ucuyor = false;
      if (gecis) {
        // Kavisli uçuş: hedef kutu kaydırmayla yer değiştirse de ona varır
        var u = Math.min(1, (t - gecis.t0) / gecis.sure), e = yumusak(u);
        x = gecis.a.x + (h.x - gecis.a.x) * e;
        y = gecis.a.y + (h.y - gecis.a.y) * e - Math.sin(Math.PI * u) * gecis.kavis;
        ucuyor = true;
        if (u >= 1) { var f = gecis.bitince; gecis = null; sonTur = t; if (f) f(); }
      } else {
        // Kutuyu takip et (sayfa kayarken peri de yanında uçar)
        x = konum.x + (h.x - konum.x) * 0.16;
        y = konum.y + (h.y - konum.y) * 0.16;
        if (Math.abs(h.x - x) + Math.abs(h.y - y) > 30) ucuyor = true;
        if (!turum && !kutu.classList.contains('is-balon') && t - sonTur > 14000) turum = { t0: t, yon: x + boy().w / 2 > W / 2 ? -1 : 1 };
      }
      var dx = x - onceki.x;
      konum = { x: x, y: y };
      // Beklerken olduğu yerde küçük bir tur (halka) atar
      var tx = 0, ty = 0;
      if (turum) {
        var v = Math.min(1, (t - turum.t0) / 1800), a = yumusak(v) * Math.PI * 2;
        tx = Math.sin(a) * 90 * turum.yon; ty = -(1 - Math.cos(a)) * 60;
        ucuyor = true;
        if (v >= 1) { turum = null; sonTur = t; }
      }
      if (ucuyor && Math.abs(dx) > 0.5) bakis = dx < 0 ? 1 : -1; // "göster" pozu sola bakar; sağa giderken aynala
      else if (!ucuyor && h.ex) bakis = h.ex < x + boy().w / 2 ? 1 : -1; // durunca kutuya baksın
      kutu.classList.toggle('is-ucuyor', ucuyor);
      var s = t / 1000;
      egimAci += (Math.max(-18, Math.min(18, dx * 1.2)) + Math.sin(s * 1.7) * 3 - egimAci) * 0.12;
      ciz(x + tx + Math.sin(s * 1.3) * 6, y + ty + Math.sin(s * 2.1) * 5, egimAci, bakis);
      if (bekleyen && !gecis) {
        if (bekleyen !== aktif) bekleyen = null;
        else if (h.gorunur) { ilkKezSoyle(bekleyen); bekleyen = null; }
      }
      if (kutu.classList.contains('is-balon')) balonYerlestir();
      requestAnimationFrame(kare);
    }

    function sec(b) {
      if (b === aktif) return;
      aktif = b;
      balonKapat();
      pozVer(b.getAttribute('data-peri-poz'));
      if (azHareket) { var h = hedefNokta(b); konum = h; ciz(h.x, h.y, 0, 1); ilkKezSoyle(b); return; }
      var hn = hedefNokta(b), mesafe = Math.abs(hn.x - konum.x) + Math.abs(hn.y - konum.y);
      turum = null;
      gecis = { a: { x: konum.x, y: konum.y }, t0: performance.now(), sure: Math.min(1900, 900 + mesafe * 0.9),
        kavis: Math.min(H * 0.3, 60 + mesafe * 0.25), bitince: function () {
          if (iz) iz.patla(20);
          if (aktif === b) bekleyen = b; // kutusu ekranda görününce konuşur
        } };
    }
    function simdikiBolum() {
      var orta = H / 2, bu = bolumler[0];
      bolumler.forEach(function (b) { var r = b.getBoundingClientRect(); if (r.top <= orta && r.bottom >= orta) bu = b; });
      return bu;
    }

    if (azHareket) sec(simdikiBolum());
    else {
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
    window.addEventListener('resize', function () { W = window.innerWidth; H = window.innerHeight; });
    if (azHareket) {
      // Hareketsiz modda da kutunun yanında kalsın
      var yenile = function () { if (bitti || !aktif) return; var h = hedefNokta(aktif); konum = h; ciz(h.x, h.y, 0, 1); if (kutu.classList.contains('is-balon')) balonYerlestir(); };
      window.addEventListener('scroll', yenile, { passive: true });
      window.addEventListener('resize', yenile);
    }

    // Tıklanınca bu bölümün mesajını yeniden söyler (yalnızca istenince)
    govde.addEventListener('click', function () {
      if (aktif) konus(aktif.getAttribute('data-peri-soz'));
      if (iz) iz.patla(30);
    });
    // Ziyaretçi periyi istediği an kapatabilir (o oturumda bir daha çıkmaz)
    kutu.querySelectorAll('[data-uyg-peri-kapat]').forEach(function (d) {
      d.addEventListener('click', function (ev) {
        ev.stopPropagation();
        balonKapat();
        bitti = true;
        kutu.hidden = true;
        if (iz) iz.dur();
        try { sessionStorage.setItem('mst_peri_gizli', '1'); } catch (e) { /* depolama kapalı */ }
      });
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
