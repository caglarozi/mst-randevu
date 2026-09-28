// Randevu formunu ve Yazar Paneli tanıtım sayfasını WordPress olmadan yerelde gösterir:
//   http://localhost:8788           randevu
//   http://localhost:8788/uygulama  MST Yazar Paneli tanıtım
//   http://localhost:8788/akademi   MST Yazar Kariyer Akademisi
//   http://localhost:8788/akademi-randevu  Akademi ön görüşme randevusu
//   http://localhost:8788/cinebook  CineBook ve MST Çocuk
//
// Canlı önizleme (onizleme.bat bunu kullanır):
//   node demo-sunucu.js --canli
// Her 15 saniyede GitHub'daki main-dayiyo dalına bakar; yeni bir değişiklik varsa indirir ve
// açık sayfaları kendiliğinden yeniler. Bu klasörde elle yapılan değişiklikler silinir.
//
// İsteğe bağlı — alınan randevuları gerçek MST CRM'e iletmek için:
//   node demo-sunucu.js --crm ANAHTAR
// ANAHTAR, CRM servisindeki RANDEVU_SECRET ile aynı olmalı. Anahtar yalnızca bu
// sunucuda kalır, tarayıcıya gönderilmez. Farklı bir CRM adresi için:
//   node demo-sunucu.js --crm ANAHTAR --crm-url https://…/randevu
const http = require('http'), fs = require('fs'), path = require('path'), { execFile } = require('child_process');
const root = __dirname;
const types = { '.html': 'text/html; charset=utf-8', '.css': 'text/css; charset=utf-8', '.js': 'text/javascript; charset=utf-8', '.webp': 'image/webp', '.png': 'image/png', '.jpg': 'image/jpeg' };

const arg = ad => { const i = process.argv.indexOf(ad); return i > -1 ? process.argv[i + 1] : ''; };
const CRM_ANAHTAR = arg('--crm');
const CRM_URL = arg('--crm-url') || 'https://yazar-crm-whatsapp-webhook.mst-ajans.workers.dev/randevu';
const CANLI = process.argv.includes('--canli');

/* ---- Canlı önizleme: GitHub'dan otomatik güncelleme ve sayfayı yenileme ---- */
const dinleyenler = new Set();
const git = (...a) => new Promise(ok => execFile('git', a, { cwd: root }, (h, cikti) => ok(h ? null : String(cikti).trim())));
async function guncelle() {
  if ((await git('fetch', '-q', 'origin', 'main-dayiyo')) === null) return; // internet yoksa sessizce bekle
  const yerel = await git('rev-parse', 'HEAD'), uzak = await git('rev-parse', 'origin/main-dayiyo');
  if (!yerel || !uzak || yerel === uzak) return;
  if ((await git('reset', '-q', '--hard', 'origin/main-dayiyo')) === null) return console.log('Güncelleme uygulanamadı.');
  console.log(new Date().toLocaleTimeString('tr-TR') + '  Yeni sürüm indirildi, açık sayfalar yenileniyor.');
  for (const r of dinleyenler) r.write('data: yenile\n\n');
}
const CANLI_BETIK = '<script>(function(){try{var k=new EventSource("/canli");k.onmessage=function(){location.reload()}}catch(e){}})();</script>';

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
  if (p === '/canli') { // açık sayfalara "yenile" haberi
    res.writeHead(200, { 'Content-Type': 'text/event-stream', 'Cache-Control': 'no-store', Connection: 'keep-alive' });
    res.write(': bagli\n\n'); dinleyenler.add(res); req.on('close', () => dinleyenler.delete(res));
    return;
  }
  if (p === '/') p = '/demo/index.html';
  if (p === '/uygulama') p = '/demo/uygulama.html'; // MST Yazar Paneli tanıtım sayfası
  if (p === '/akademi') p = '/demo/akademi.html';   // MST Yazar Kariyer Akademisi
  if (p === '/akademi-randevu') p = '/demo/akademi-randevu.html'; // Akademi ön görüşme randevusu
  if (p === '/cinebook') p = '/demo/cinebook.html'; // CineBook ve MST Çocuk
  const file = path.normalize(path.join(root, p));
  if (!file.startsWith(root) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.writeHead(404); return res.end('Bulunamadı'); }
  res.writeHead(200, { 'Content-Type': types[path.extname(file)] || 'application/octet-stream', 'Cache-Control': 'no-store' });
  if (CANLI && path.extname(file) === '.html') return res.end(fs.readFileSync(file, 'utf8').replace('</body>', CANLI_BETIK + '</body>'));
  fs.createReadStream(file).pipe(res);
}).listen(8788, () => {
  console.log('hazir http://localhost:8788  (randevu)');
  console.log('      http://localhost:8788/uygulama  (MST Yazar Paneli tanıtım)');
  console.log('      http://localhost:8788/akademi   (Yazar Kariyer Akademisi)');
  console.log('      http://localhost:8788/akademi-randevu  (Akademi ön görüşme randevusu)');
  console.log('      http://localhost:8788/cinebook  (CineBook ve MST Çocuk)');
  console.log(CRM_ANAHTAR ? `Randevular CRM'e iletilecek: ${CRM_URL}` : 'CRM\'e iletim kapalı (açmak için: node demo-sunucu.js --crm ANAHTAR)');
  if (CANLI) {
    console.log('Canlı önizleme açık: yeni sürümler kendiliğinden iner, sayfa kendiliğinden yenilenir. Bu pencereyi kapatmayın.');
    guncelle(); setInterval(guncelle, 15000);
  }
});
