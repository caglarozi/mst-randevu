# MST Yazar Adayı Randevu

Yazar adaylarının müsait saatlerden görüşme randevusu aldığı WordPress eklentisi.

- Aday gün ve saat seçer, ad soyad + telefon (+ isteğe bağlı kitap notu) bırakır.
- Her saate en fazla **3 kişi** randevu alabilir (panelden değiştirilebilir); son yer kaldığında saat turuncu **"Dolmak üzere"**, dolduğunda kırmızı **"Dolu"** olarak görünür (dolu saat seçilemez).
- Aynı numara ileri tarihli ikinci randevu alamaz; bot tuzağı ve IP başına deneme sınırı vardır.
- Her randevuda e-posta ve (tanımlıysa) webhook bildirimi gider; webhook ile MST CRM'e aday kaydı düşer (aşağıya bakın).
- Telefonda tam uyumlu: saat seçilince ekranın altında sabit "Devam Et" çubuğu çıkar.

## Kurulum

1. `mst-randevu/` klasörünü zip'leyin (klasörün kendisi zip'in içinde olmalı).
2. WordPress → **Eklentiler → Yeni Ekle → Eklenti Yükle** → zip'i yükleyip etkinleştirin.
3. **Yazar Randevu → Saatler**: tarih aralığı, ilk/son randevu saati (ör. 10:00 → 17:30), aralık ve kişi sınırını girip saatleri oluşturun.
4. Sayfaya ekleyin:
   - **Tam sayfa (önerilen):** yeni sayfa → *Sayfa Özellikleri → Şablon →* **MST Randevu (Tam Sayfa)**. Temanın üst kısmı yerine ortada MST logosu, en sağda WhatsApp + "Siteye Git" butonları olan çubuk gelir.
   - **Tema içinde:** sayfa içeriğine `[mst_randevu]` kısa kodunu yazın.
5. **Bildirim & Ayarlar**: bildirim e-postası, webhook adresi, WhatsApp numarası ve metinler.

## Yerel önizleme (WordPress olmadan)

```bash
node demo-sunucu.js
```

Ardından http://localhost:8788 — eklentinin kendi CSS/JS'i sahte verilerle çalışır; kayıtlar yalnızca sayfada tutulur.

## Dosyalar

| Yol | İçerik |
|---|---|
| `mst-randevu/mst-randevu.php` | Eklenti: veritabanı, AJAX, bildirim, yönetim paneli |
| `mst-randevu/templates/tam-sayfa.php` | "MST Randevu (Tam Sayfa)" sayfa şablonu |
| `mst-randevu/assets/` | CSS, JS, logo |
| `demo/` + `demo-sunucu.js` | WordPress'siz yerel önizleme |

## MST CRM bağlantısı

Her randevu MST CRM'e (caglarozi/mstcrm) düşer:

- Telefon CRM'de kayıtlıysa (numara hangi biçimde yazılmış olursa olsun) randevu o yazarın kaydına eklenir; değilse **"Aday"** statüsünde, kaynağı **"Web randevu"** olan yeni kayıt açılır.
- Yazarın görüşme tarihi/saati randevu saatine ayarlanır — CRM'in mevcut randevu hatırlatıcısı çalışır.
- CRM'deki **Web Randevuları** sekmesinde tüm kullanıcılar görür; "Arandı / Ulaşılamadı" olarak işaretlenir.
- CRM uygulaması yüklü telefonlara bildirim gider.
- Panelden iptal edilen randevu CRM'de de "İptal" olur.

Kurulum (bir kez):

1. CRM servisine anahtar tanımlayıp yayınlayın (`mstcrm` deposunda):
   ```bash
   cd whatsapp-webhook
   npx wrangler secret put RANDEVU_SECRET   # uzun, rastgele bir metin yapıştırın
   npx wrangler deploy
   ```
2. WordPress → **Yazar Randevu → Bildirim & Ayarlar**:
   - Webhook URL: `https://yazar-crm-whatsapp-webhook.mst-ajans.workers.dev/randevu`
   - Webhook anahtarı: 1. adımdaki metnin aynısı
3. **Test bildirimi gönder** düğmesine basın: sonuç `webhook: 200` olmalı (test CRM'e kayıt yazmaz).
