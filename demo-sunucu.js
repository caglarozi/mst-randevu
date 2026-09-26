// Randevu formunu ve Yazar Paneli tanıtım sayfasını WordPress olmadan yerelde gösterir:
//   http://localhost:8788           randevu
//   http://localhost:8788/uygulama  MST Yazar Paneli tanıtım
//   http://localhost:8788/akademi   MST Yazar Kariyer Akademisi
//   http://localhost:8788/akademi-randevu  Akademi ön görüşme randevusu
//
// İsteğe bağlı — alınan randevuları gerçek MST CRM'e iletmek için:
//   node demo-sunucu.js --crm ANAHTAR
// ANAHTAR, CRM servisindeki RANDEVU_SECRET ile aynı olmalı. Anahtar yalnızca bu
// sunucuda kalır, tarayıcıya gönderilmez. Farklı bir CRM adresi için:
//   node demo-sunucu.js --crm ANAHTAR --crm-url https://…/randevu
const http = require('http'), fs = require('fs'), path = require('path');
const root = __dirname;
const types = { '.html': 'text/html; charset=utf-8', '.css': 'text/css; charset=utf-8', '.js': 'text/javascript; charset=utf-8', '.webp': 'image/webp', '.png': 'image/png', '.jpg': 'image/jpeg' };

const arg = ad => { const i = process.argv.indexOf(ad); return i > -1 ? process.argv[i + 1] : ''; };
const CRM_ANAHTAR = arg('--crm');
const CRM_URL = arg('--crm-url') || 'https://yazar-crm-whatsapp-webhook.mst-ajans.workers.dev/randevu';

function crmIlet(req, res) {
  let govde = '';
  req.on('data', c => { govde += c; if (govde.length > 20000) req.destroy(); });
  req.on('end', async () => {
    const cevap = (kod, o) => { res.writeHead(kod, { 'Content-Type': 'application/json; charset=utf-8' }); res.end(JSON.stringify(o)); };
    if (!CRM_ANAHTAR) return cevap(200, { iletildi: false, sebep: 'CRM anahtarı verilmedi (node demo-sunucu.js --crm ANAHTAR)' });
    try {
      const r = await fetch(CRM_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8', Authorization: 'Bearer ' + CRM_ANAHTAR },
        body: govde
      });
      const metin = await r.text();
      console.log(`CRM'e iletildi → ${r.status} ${metin}`);
      cevap(200, { iletildi: r.ok, durum: r.status, crm: metin });
    } catch (e) {
      console.log('CRM\'e iletilemedi:', e.message);
      cevap(200, { iletildi: false, sebep: e.message });
    }
  });
}

http.createServer((req, res) => {
  let p = decodeURIComponent(req.url.split('?')[0]);
  if (p === '/crm-ilet' && req.method === 'POST') return crmIlet(req, res);
  if (p === '/') p = '/demo/index.html';
  if (p === '/uygulama') p = '/demo/uygulama.html'; // MST Yazar Paneli tanıtım sayfası
  if (p === '/akademi') p = '/demo/akademi.html';   // MST Yazar Kariyer Akademisi
  if (p === '/akademi-randevu') p = '/demo/akademi-randevu.html'; // Akademi ön görüşme randevusu
  const file = path.normalize(path.join(root, p));
  if (!file.startsWith(root) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.writeHead(404); return res.end('Bulunamadı'); }
  res.writeHead(200, { 'Content-Type': types[path.extname(file)] || 'application/octet-stream', 'Cache-Control': 'no-store' });
  fs.createReadStream(file).pipe(res);
}).listen(8788, () => {
  console.log('hazir http://localhost:8788  (randevu)');
  console.log('      http://localhost:8788/uygulama  (MST Yazar Paneli tanıtım)');
  console.log('      http://localhost:8788/akademi   (Yazar Kariyer Akademisi)');
  console.log('      http://localhost:8788/akademi-randevu  (Akademi ön görüşme randevusu)');
  console.log(CRM_ANAHTAR ? `Randevular CRM'e iletilecek: ${CRM_URL}` : 'CRM\'e iletim kapalı (açmak için: node demo-sunucu.js --crm ANAHTAR)');
});
