/* MST Yazar Kariyer Akademisi — açılış sahnesi, yol haritası ve Yazar Kariyer Analizi formu.
 * - Açılış: "Yazmak başlangıçtır." daktiloyla yazılır; sahne ışığının huzmesinde toz zerreleri süzülür
 * - Girişteki aşama kartı seçilince yol haritası o aşamaya geçer, formdaki aşama işaretlenir,
 *   önerilen program vurgulanır
 * - Bölüm menüsünde ekrandaki bölüm işaretlenir
 * - Program / ücretsiz eğitim butonları formdaki "ilgilendiğiniz program" alanını doldurur
 * - Form dört adımdır; her adımda zorunlu alanlar denetlenir, sonunda AJAX ile gönderilir
 * - Reklam kaynağı (utm_*, fbclid, gclid) ilk girişte saklanır ve başvuruyla gönderilir */
(function () {
  'use strict';
  var ONERI = { yazma: 'temel', dosya: 'marka', yayimlandi: 'marka' };
  var AD = { temel: 'Yazar Akademisi Temel Programı', marka: 'Yazar Marka ve Görünürlük Programı', mentorluk: 'Yazar Kariyer Mentorluk Programı' };

  function basla() {
    var form = document.getElementById('akd-form');
    if (!form) return;
    var adimlar = form.querySelectorAll('.akd-adim'), etiketler = form.querySelectorAll('.akd-form__adimlar li');
    var cubuk = form.querySelector('.akd-form__ilerleme span'), hata = form.querySelector('.akd-hata');
    var ileri = form.querySelector('[data-akd-ileri]'), geri = form.querySelector('[data-akd-geri]'), gonder = form.querySelector('[data-akd-gonder]');
    var oneriKutu = form.querySelector('[data-akd-oneri]');
    var simdi = 0;
    form.querySelector('[name="_t"]').value = Math.floor(Date.now() / 1000);

    /* ---------- Reklam kaynağı ---------- */
    var KAYNAK = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'gclid'];
    var kaynak = {};
    try { kaynak = JSON.parse(sessionStorage.getItem('mst_akd_kaynak') || '{}') || {}; } catch (e) { kaynak = {}; }
    var q = new URLSearchParams(location.search), yeni = false;
    KAYNAK.forEach(function (k) { if (q.get(k)) { kaynak[k] = q.get(k); yeni = true; } });
    if (!kaynak.ilk_sayfa) { kaynak.ilk_sayfa = location.href.split('#')[0]; kaynak.referans = document.referrer || ''; yeni = true; }
    if (yeni) { try { sessionStorage.setItem('mst_akd_kaynak', JSON.stringify(kaynak)); } catch (e) { /* depolama kapalı */ } }

    /* ---------- Program önerisi ---------- */
    function oneriGoster(asama) {
      var k = ONERI[asama];
      if (!k) return;
      oneriKutu.hidden = false;
      oneriKutu.innerHTML = '';
      oneriKutu.appendChild(document.createTextNode('Aşamanıza göre ilk önerimiz: '));
      var b = document.createElement('b'); b.textContent = AD[k]; oneriKutu.appendChild(b);
      oneriKutu.appendChild(document.createTextNode('. Ekibimiz başvurunuzu inceleyip kesinleştirir.'));
      document.querySelectorAll('.akd-program').forEach(function (p) { p.classList.toggle('is-onerilen', p.id === 'program-' + k); });
      document.querySelectorAll('[data-akd-asama]').forEach(function (d) { d.setAttribute('aria-pressed', d.getAttribute('data-akd-asama') === asama ? 'true' : 'false'); });
      yolGoster(asama);
    }
    // Yol haritası paneli: seçilen aşamanın paneli görünür
    function yolGoster(asama) {
      document.querySelectorAll('[data-akd-yol]').forEach(function (b) { b.setAttribute('aria-selected', b.getAttribute('data-akd-yol') === asama ? 'true' : 'false'); });
      document.querySelectorAll('[data-akd-yol-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-akd-yol-panel') !== asama; });
    }
    function asamaSec(a) {
      var r = form.querySelector('[name="asama"][value="' + a + '"]');
      if (r) { r.checked = true; oneriGoster(a); } else yolGoster(a);
    }
    document.querySelectorAll('[data-akd-yol]').forEach(function (b) {
      b.addEventListener('click', function () { asamaSec(b.getAttribute('data-akd-yol')); });
    });
    document.querySelectorAll('[data-akd-basvur]').forEach(function (b) {
      b.addEventListener('click', function () { asamaSec(b.getAttribute('data-akd-basvur')); adimaGit(0); });
    });
    form.querySelectorAll('[name="asama"]').forEach(function (r) {
      r.addEventListener('change', function () { oneriGoster(r.value); hataGizle(); });
    });
    document.querySelectorAll('[data-akd-asama]').forEach(function (d) {
      d.addEventListener('click', function () {
        asamaSec(d.getAttribute('data-akd-asama'));
        adimaGit(0);
        var yol = document.getElementById('yol');
        if (yol) yol.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
    document.querySelectorAll('[data-akd-ilgi]').forEach(function (a) {
      a.addEventListener('click', function () {
        var s = form.querySelector('[name="ilgi"]');
        if (s) s.value = a.getAttribute('data-akd-ilgi');
      });
    });

    /* ---------- Koşullu alan: yayımlanmış kitap adı ---------- */
    function kosullar() {
      form.querySelectorAll('[data-akd-kosul]').forEach(function (el) {
        var p = el.getAttribute('data-akd-kosul').split('='), sec = form.querySelector('[name="' + p[0] + '"]:checked');
        el.hidden = !(sec && sec.value === p[1]);
      });
    }
    form.addEventListener('change', kosullar);
    kosullar();

    /* ---------- Adımlar ---------- */
    function adimaGit(i) {
      simdi = Math.max(0, Math.min(adimlar.length - 1, i));
      adimlar.forEach(function (a, j) { a.hidden = j !== simdi; a.classList.toggle('is-aktif', j === simdi); });
      etiketler.forEach(function (e, j) { e.classList.toggle('is-aktif', j === simdi); e.classList.toggle('is-bitti', j < simdi); });
      cubuk.style.width = ((simdi + 1) / adimlar.length * 100) + '%';
      geri.hidden = simdi === 0;
      ileri.hidden = simdi === adimlar.length - 1;
      gonder.hidden = simdi !== adimlar.length - 1;
      hataGizle();
    }
    function hataGoster(m, alan) {
      hata.textContent = m; hata.hidden = false;
      if (alan) {
        var el = form.querySelector('[name="' + alan + '"]');
        if (el) {
          var kap = el.closest('.akd-alan, .akd-onay');
          if (kap) kap.classList.add('is-hata');
          var adim = el.closest('.akd-adim');
          if (adim && adim.hidden) { adimaGit(Array.prototype.indexOf.call(adimlar, adim)); hata.textContent = m; hata.hidden = false; kap && kap.classList.add('is-hata'); }
          try { el.focus({ preventScroll: false }); } catch (e) { el.focus(); }
        }
      }
    }
    function hataGizle() {
      hata.hidden = true;
      form.querySelectorAll('.is-hata').forEach(function (e) { e.classList.remove('is-hata'); });
    }
    function denetle(i) {
      if (i === 0 && !form.querySelector('[name="asama"]:checked')) return ['Lütfen yazarlık aşamanızı seçin.', 'asama'];
      if (i === 1) {
        var ad = form.ad_soyad.value.trim(), tel = form.telefon.value.replace(/\D+/g, ''), ep = form.eposta.value.trim();
        if (ad.length < 3) return ['Lütfen adınızı ve soyadınızı yazın.', 'ad_soyad'];
        if (tel.length < 10) return ['Geçerli bir telefon numarası girin (ör. 0532 123 45 67).', 'telefon'];
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(ep)) return ['Geçerli bir e-posta adresi girin.', 'eposta'];
      }
      if (i === 3 && !form.kvkk.checked) return ['Devam etmek için aydınlatma metni onayını işaretleyin.', 'kvkk'];
      return null;
    }
    ileri.addEventListener('click', function () {
      var h = denetle(simdi);
      if (h) { hataGoster(h[0], h[1]); return; }
      adimaGit(simdi + 1);
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    geri.addEventListener('click', function () { adimaGit(simdi - 1); });
    form.addEventListener('input', function (e) {
      var k = e.target.closest('.is-hata');
      if (k) k.classList.remove('is-hata');
    });

    /* ---------- Gönder ---------- */
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      for (var i = 0; i < adimlar.length; i++) {
        var h = denetle(i);
        if (h) { hataGoster(h[0], h[1]); return; }
      }
      if (form.classList.contains('is-gonderiliyor')) return;
      form.classList.add('is-gonderiliyor');
      gonder.setAttribute('aria-busy', 'true');
      var veri = new FormData(form);
      veri.append('action', 'mst_akademi_basvuru');
      Object.keys(kaynak).forEach(function (k) { veri.append(k, kaynak[k]); });
      fetch((window.MST_AKADEMI && MST_AKADEMI.ajax) || '/wp-admin/admin-ajax.php', { method: 'POST', body: veri, credentials: 'same-origin' })
        .then(function (r) { return r.json().catch(function () { return { success: false, data: { mesaj: 'Sunucu yanıt vermedi. Lütfen tekrar deneyin.' } }; }); })
        .then(function (j) {
          form.classList.remove('is-gonderiliyor');
          gonder.removeAttribute('aria-busy');
          if (!j || !j.success) {
            var d = (j && j.data) || {};
            hataGoster(d.mesaj || 'Başvuru gönderilemedi. Lütfen tekrar deneyin.', d.alan);
            return;
          }
          var s = form.querySelector('.akd-sonuc');
          form.querySelector('[data-akd-sonuc-mesaj]').textContent = j.data.mesaj;
          form.querySelector('[data-akd-sonuc-oneri]').textContent = j.data.oneri_adi;
          form.querySelector('[data-akd-sonuc-link]').setAttribute('href', '#program-' + j.data.oneri);
          form.classList.add('is-tamam');
          s.hidden = false;
          s.focus();
          form.scrollIntoView({ behavior: 'smooth', block: 'start' });
          // Reklam ve analiz araçları sayfada kuruluysa dönüşümü bildir
          try {
            (window.dataLayer = window.dataLayer || []).push({ event: 'akademi_basvuru', asama: veri.get('asama'), oneri: j.data.oneri });
            if (typeof window.fbq === 'function') window.fbq('track', 'Lead', { content_name: 'Yazar Kariyer Analizi', content_category: j.data.oneri });
            if (typeof window.gtag === 'function') window.gtag('event', 'generate_lead', { event_category: 'akademi', event_label: j.data.oneri });
          } catch (x) { /* analiz aracı yok */ }
        })
        .catch(function () {
          form.classList.remove('is-gonderiliyor');
          gonder.removeAttribute('aria-busy');
          hataGoster('Bağlantı kurulamadı. İnternetinizi kontrol edip tekrar deneyin.');
        });
    });

    adimaGit(0);
    // Telefonda uzun müfredat listeleri kapalı başlasın
    if (window.matchMedia('(max-width: 640px)').matches) {
      document.querySelectorAll('.akd-acilir[open]').forEach(function (d) { d.open = false; });
    }
  }
  /* ---------- Açılış sahnesi ---------- */
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
        var a = kenar * (0.25 + 0.55 * (0.5 + 0.5 * Math.sin(t / 700 + p.f))) * (1 - p.y * 0.5);
        g.globalAlpha = a;
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
  // Bölüm menüsü: ekranın üst üçte birindeki bölüm işaretlenir
  function gezinti() {
    var linkler = document.querySelectorAll('[data-akd-gez]');
    if (!linkler.length || !('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (girdiler) {
      girdiler.forEach(function (g) {
        if (!g.isIntersecting) return;
        linkler.forEach(function (l) {
          var bu = l.getAttribute('data-akd-gez') === g.target.id;
          l.classList.toggle('is-aktif', bu);
          if (bu && l.scrollIntoView && l.parentElement.scrollWidth > l.parentElement.clientWidth) {
            l.parentElement.scrollTo({ left: l.offsetLeft - 20, behavior: 'smooth' });
          }
        });
      });
    }, { rootMargin: '-30% 0px -65% 0px' });
    linkler.forEach(function (l) { var b = document.getElementById(l.getAttribute('data-akd-gez')); if (b) io.observe(b); });
  }

  function hepsi() { daktilo(); toz(); gezinti(); basla(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', hepsi); else hepsi();
})();
