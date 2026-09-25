/* Rank Math içerik analizi: tam sayfa şablonlarının metnini analize ekler.
 * Sayfanın sitedeki hâli okunur, <main> içindeki metin (betikler, maskot ve süsler hariç)
 * Rank Math'in rank_math_content filtresiyle analiz edilen içeriğe eklenir; ardından analiz yenilenir.
 * Düzenleyiciye ya da sayfaya hiçbir şey yazılmaz. */
(function () {
  'use strict';
  var cfg = window.MST_RM || {};
  if (!cfg.url || !window.wp || !wp.hooks || !window.fetch || !window.DOMParser) return;
  var ek = '';
  wp.hooks.addFilter('rank_math_content', 'mst-randevu', function (icerik) {
    return ek ? (icerik || '') + ek : icerik;
  });
  fetch(cfg.url, { credentials: 'same-origin' })
    .then(function (r) { return r.ok ? r.text() : ''; })
    .then(function (html) {
      if (!html) return;
      var ana = new DOMParser().parseFromString(html, 'text/html').querySelector(cfg.secici);
      if (!ana) return;
      ana.querySelectorAll('script, style, noscript, template, canvas, svg, .uyg-peri, .uyg-peri-iz, .uyg-peri-cagir, [aria-hidden="true"]')
        .forEach(function (x) { x.remove(); });
      ek = '\n' + ana.innerHTML;
      if (window.rankMathEditor && typeof rankMathEditor.refresh === 'function') rankMathEditor.refresh('content');
    })
    .catch(function () {});
})();
