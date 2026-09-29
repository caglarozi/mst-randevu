// Randevu formunu ve Yazar Paneli tanıtım sayfasını WordPress olmadan yerelde gösterir:
//   http://localhost:8788           randevu
//   http://localhost:8788/uygulama  MST Yazar Paneli tanıtım
//   http://localhost:8788/akademi   MST Yazar Kariyer Akademisi
//   http://localhost:8788/akademi-randevu  Akademi ön görüşme randevusu
//   http://localhost:8788/cinebook  CineBook ve MST Çocuk
//
// Canlı yerel önizleme (onizleme.bat bunu kullanır):
//   node demo-sunucu.js --canli
// - Demo veya stil dosyaları değişince açık sayfaları yeniler.
// - Her 20 saniyede GitHub'daki main-dayiyo dalına bakar; yeni sürüm varsa kendisi indirir
//   (kaydırarak ilerletir ya da birleştirir), sayfa kendiliğinden yenilenir. Yerel dosyaları
//   sıfırlamaz; kaydedilmemiş değişiklikle ya da çakışmayla karşılaşırsa hiçbir şeye dokunmadan
//   pencereye yazar (o zaman GUNCELLE.bat çalıştırılır).
// - GitHub'a bakmasın istenirse: node demo-sunucu.js --canli --yerel
//
// İsteğe bağlı — alınan randevuları gerçek MST CRM'e iletmek için:
//   node demo-sunucu.js --crm ANAHTAR
// ANAHTAR, CRM servisindeki RANDEVU_SECRET ile aynı olmalı. Anahtar yalnızca bu
// sunucuda kalır, tarayıcıya gönderilmez. Farklı bir CRM adresi için:
//   node demo-sunucu.js --crm ANAHTAR --crm-url https://…/randevu
const http = require('http'), fs = require('fs'), path = require('path'), { execFile } = require('child_process');
const root = __dirname;
const types = { '.html': 'text/html; charset=utf-8', '.css': 'text/css; charset=utf-8', '.js': 'text/javascript; charset=utf-8', '.webp': 'image/webp', '.png': 'image/png', '.jpg': 'image/jpeg', '.mp4': 'video/mp4', '.mov': 'video/quicktime' };

const arg = ad => { const i = process.argv.indexOf(ad); return i > -1 ? process.argv[i + 1] : ''; };
const CRM_ANAHTAR = arg('--crm');
const CRM_URL = arg('--crm-url') || 'https://yazar-crm-whatsapp-webhook.mst-ajans.workers.dev/randevu';
const CANLI = process.argv.includes('--canli');
const OTOMATIK = CANLI && !process.argv.includes('--yerel');

/* ---- Canlı önizleme: yerel dosya değişince sayfayı yenileme ---- */
const dinleyenler = new Set();
function yenile() {
  for (const r of dinleyenler) r.write('data: yenile\n\n');
}

/* ---- Otomatik güncelleme: GitHub'daki yeni sürümü kendisi indirir ---- */
const git = (...a) => new Promise(ok => execFile('git', a, { cwd: root, timeout: 60000 }, (h, cikti) => ok(h ? null : String(cikti).trim())));
const saat = () => new Date().toLocaleTimeString('tr-TR');
let guncelleniyor = false, sonUyari = '';
function uyar(m) { if (m !== sonUyari) { console.log(saat() + '  ' + m); sonUyari = m; } }
async function otomatikGuncelle() {
  if (guncelleniyor) return;
  guncelleniyor = true;
  try {
    if ((await git('fetch', '-q', 'origin', 'main-dayiyo')) === null) return; // internet yoksa sessizce bekle
    const geride = parseInt(await git('rev-list', '--count', 'HEAD..FETCH_HEAD'), 10) || 0;
    if (!geride) { sonUyari = ''; return; }
    let r = await git('merge', '--ff-only', '-q', 'FETCH_HEAD');
    if (r === null) { // yerelde GitHub'da olmayan kayıtlar var: birleştir (yalnızca klasör temizse)
      if (await git('status', '--porcelain', '--untracked-files=no')) return uyar('Yeni sürüm var ama klasörde kaydedilmemiş değişiklik var; GUNCELLE.bat dosyasını çalıştırın.');
      r = await git('merge', '--no-edit', '-q', 'FETCH_HEAD');
      if (r === null) { await git('merge', '--abort'); return uyar('Yeni sürüm yerel çalışmayla çakışıyor; hiçbir şey değiştirilmedi. GUNCELLE.bat dosyasını çalıştırın.'); }
    }
    sonUyari = '';
    console.log(saat() + '  Yeni sürüm indirildi: ' + ((await git('log', '-1', '--format=%s')) || '').slice(0, 90));
  } finally { guncelleniyor = false; }
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
  if (p === '/surec') p = '/demo/surec.html';       // MST Yayıncılık Süreç Sayfası
  const file = path.normalize(path.join(root, p));
  if (!file.startsWith(root) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) { res.writeHead(404); return res.end('Bulunamadı'); }
  if (req.headers.range && path.extname(file) === '.mp4') {
    const stat = fs.statSync(file);
    const parts = req.headers.range.replace(/bytes=/, '').split('-');
    const start = parseInt(parts[0], 10);
    const end = parts[1] ? parseInt(parts[1], 10) : stat.size - 1;
    res.writeHead(206, {
      'Content-Range': `bytes ${start}-${end}/${stat.size}`,
      'Accept-Ranges': 'bytes',
      'Content-Length': (end - start) + 1,
      'Content-Type': 'video/mp4'
    });
    return fs.createReadStream(file, { start, end }).pipe(res);
  }
  const stat = fs.statSync(file);
  res.writeHead(200, {
    'Content-Type': types[path.extname(file)] || 'application/octet-stream',
    'Content-Length': stat.size,
    'Accept-Ranges': 'bytes',
    'Cache-Control': 'no-store'
  });
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
    console.log('Canlı önizleme açık: dosyalar değişince sayfa yenilenir. Bu pencereyi kapatmayın.');
    if (OTOMATIK) {
      console.log('Otomatik güncelleme açık: GitHub\'daki yeni sürümler 20 saniye içinde kendiliğinden gelir.');
      otomatikGuncelle(); setInterval(otomatikGuncelle, 20000);
    }
    let bekle;
    [path.join(root, 'demo'), path.join(root, 'mst-randevu', 'assets')].forEach(dizin => {
      try { fs.watch(dizin, () => { clearTimeout(bekle); bekle = setTimeout(yenile, 250); }); }
      catch (e) { /* klasör yoksa (henüz indirilmediyse) izleme atlanır; sunucu kapanmaz */ }
    });
  }
});
