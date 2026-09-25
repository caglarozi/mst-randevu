# MST Yazar Adayı Randevu

Yazar adaylarının müsait saatlerden görüşme randevusu aldığı WordPress eklentisi.

- Aday gün ve saat seçer, ad soyad + telefon (+ isteğe bağlı kitap notu) bırakır.
- Her saate en fazla **2 kişi** randevu alabilir (panelden 1'e düşürülebilir; üst sınır `MST_Randevu::KISI`). Eski sürümden gelen ileri tarihli saatler güncellemede kendiliğinden 2'ye çekilir (2'den fazla randevusu olan saatte kimse düşmez). Son yer kaldığında saat turuncu **"Dolmak üzere"**, dolduğunda kırmızı **"Dolu"** olarak görünür (dolu saat seçilemez).
- Aynı numara ileri tarihli ikinci randevu alamaz; bot tuzağı ve IP başına deneme sınırı vardır.
- Her randevuda e-posta ve (tanımlıysa) webhook bildirimi gider; webhook ile MST CRM'e aday kaydı düşer (aşağıya bakın).
- Telefonda tam uyumlu: saat seçilince ekranın altında sabit "Devam Et" çubuğu çıkar. Mobil menüdeki WhatsApp düğmesi çubuğun gri tonundadır (yalnızca simgesi yeşil).
- Randevu alınınca onay ekranında **Yazar Kariyer Akademisi** kartı çıkar (Akademi sayfası yayımlanmışsa).
- **Karanlık mod:** telefon/tarayıcı karanlık moddaysa randevu sayfası kendi koyu temasına (antrasit + altın) geçer; tarayıcının "zorla karartma"sı sayfayı bozmaz. Yazar Paneli ve Akademi sayfaları zorla karartılmaz (`color-scheme: only light`).

## Kurulum

1. `mst-randevu/` klasörünü zip'leyin (klasörün kendisi zip'in içinde olmalı).
2. WordPress → **Eklentiler → Yeni Ekle → Eklenti Yükle** → zip'i yükleyip etkinleştirin.
3. **Yazar Randevu → Saatler**: tarih aralığı, ilk/son randevu saati (ör. 10:00 → 17:30), aralık ve kişi sınırını girip saatleri oluşturun.
4. Sayfaya ekleyin:
   - **Tam sayfa (önerilen):** yeni sayfa → *Sayfa Özellikleri → Şablon →* **MST Randevu (Tam Sayfa)**. Temanın üst kısmı yerine ortada MST logosu, en sağda WhatsApp + "Siteye Git" butonları olan çubuk gelir.
   - **Tema içinde:** sayfa içeriğine `[mst_randevu]` kısa kodunu yazın.
5. **Bildirim & Ayarlar**: bildirim e-postası, webhook adresi, WhatsApp numarası ve metinler.

## Güncellemeler (otomatik)

Eklenti kendini bu depodaki GitHub sürümlerinden (Releases) günceller — zip'i elle yüklemek gerekmez.

- `mst-randevu/mst-randevu.php` içinde **hem** `Version:` başlığı **hem** `MST_RANDEVU_VER` artırılıp (ör. `1.2.0 → 1.2.1`) `main`'e birleştirilince GitHub Actions (`.github/workflows/eklenti-surumu.yml`) `v1.2.1` sürümünü açar ve `mst-randevu.zip`'i ekler.
- WordPress bunu birkaç saat içinde görür (hemen görmek için **Eklentiler** sayfasında "Güncellemeleri kontrol et" bağlantısı). **Şimdi güncelle** ile ya da eklentinin **Otomatik güncellemeleri etkinleştir** seçeneğiyle kurulur.
- Sürüm numarası artırılmayan değişiklikler siteye gitmez.
- Dikkat: otomatik güncelleme açıksa `main`'e giren her sürüm doğrudan canlı siteye gider.

## MST Yazar Paneli tanıtım sayfası

Yeni sayfa → *Sayfa Özellikleri → Şablon →* **MST Yazar Paneli Tanıtım (Tam Sayfa)** → Yayımla. Üst çubuk randevu sayfasıyla aynıdır. Panel yalnızca MST yazarlarına açık olduğu için ana butonlar ziyaretçiyi **ücretsiz ön görüşmeye** (randevu sayfası) ve WhatsApp'a yönlendirir; mevcut yazarlar için yalnızca küçük bir "Zaten MST yazarı mısınız? Panele giriş" bağlantısı vardır (**Bildirim & Ayarlar → Yazar Paneli adresi**, boşsa `https://app.mstyayincilik.com/`). Telefon ve paneldeki ekranlar temsili çizimlerdir (gerçek veri değildir).

Sayfada **kitap perisi** maskotu var. Sayfa açılınca sağ alttan uçarak gelir ve rehberlik isteyip istemediğinizi sorar (**Evet / Hayır**). Evet: bölüm değiştikçe o bölümün kutusunun yanına kavis çizerek uçar (girişte telefonun, yayın sürecinde Dağıtım adımının yanı…), sayfa kayarken kutuyu takip eder ve kutusu ekrana girince başının üstünde "•••" işareti belirir; açıklama içeriği kapatmasın diye kendiliğinden açılmaz, periye dokununca açılır (her bölüm için bir kez işaret çıkar). Canlı durur: göz kırpar, el/asa sallar (konuşurken elleriyle anlatır), asasından ışıltı saçar, zıplar, konunca yaylanır, beklerken ara sıra takla atarak tur atar. Balondaki × yalnızca balonu kapatır. Hayır ya da perinin üstündeki ×: el sallayıp vedalaşır ve uçarak gider; sağ altta küçük bir **peri düğmesi** kalır, basınca geri gelir. Cevap o oturum boyunca hatırlanır. Poz ve cümleler şablondaki `$peri('poz', 'cümle', 'kutu seçicisi', 'yer sırası')` çağrılarındadır; her poz `assets/peri/` altında kanat katmanı + gövde kareleri (ana, göz kırpma, ikinci hareket karesi) olarak görsellerdir (`araclar/peri-blender.py` ile Blender'da çizildi); kanatlar çırpar, uçarken hızlanır. "Hareketi azalt" ayarında peri hareketsiz görünür.

## MST Yazar Kariyer Akademisi sayfası

Yeni sayfa → başlık (ör. “Yazar Kariyer Akademisi”) → *Şablon →* **MST Yazar Kariyer Akademisi (Tam Sayfa)** → Yayımla.

- Yalnızca bilgi sayfasıdır; başvuru formu yoktur. İletişim WhatsApp (her programda o programın adıyla hazır mesaj) ve ücretsiz ön görüşme randevusu üzerinden yürür.
- Açılış bir **sahne**: “Yazmak başlangıçtır.” daktiloyla yazılır, sahne ışığı yanar ve “Görünür olmak kariyerdir.” ışığın altında parlar.
- Sahnenin altında üç programın “jeneriği” vardır; birine basınca o programın sekmesi açılır.
- Bölümler: neden akademi, eğitim döngüsü ve kurallar, 12 temel alan (içindekiler sayfası gibi), programlar (kontenjan/görüşme grafiği + sekmeli program kitapçıkları; fiyat her programın sonunda), karşılaştırma, ücretsiz eğitim (davetiye bileti), güven, kalite standardı (%80 / %70 halkaları) ve kapsam sınırları, SSS, “Sahne hazır.” kapanışı. Girişten sonra üstte sabit bir bölüm menüsü vardır; `#program-temel`, `#program-marka`, `#program-mentorluk` adresleri ilgili programı açar.
- Vurgular Cormorant Garamond italik, künye ve numaralar IBM Plex Mono (yalnızca bu sayfada yüklenir).
- **Yazar Randevu → Akademi Ayarları**: ücretsiz eğitim tarihi (geçince kendiliğinden “yeni tarih yakında” yazar), yeri, süresi ve sonraki dönem başlangıcı.

## Yerel önizleme (WordPress olmadan)

```bash
node demo-sunucu.js
```

Ardından http://localhost:8788 — eklentinin kendi CSS/JS'i sahte verilerle çalışır; kayıtlar yalnızca sayfada tutulur.

Yazar Kariyer Akademisi: http://localhost:8788/akademi (`demo/akademi.html`).

MST Yazar Paneli tanıtım sayfası: http://localhost:8788/uygulama (`demo/uygulama.html`, `templates/uygulama.php`'nin çıktısından üretilmiştir; şablon değişince yeniden üretilmeli).

## Dosyalar

| Yol | İçerik |
|---|---|
| `mst-randevu/mst-randevu.php` | Eklenti: veritabanı, AJAX, bildirim, yönetim paneli |
| `mst-randevu/templates/tam-sayfa.php` | "MST Randevu (Tam Sayfa)" sayfa şablonu |
| `mst-randevu/akademi.php` + `templates/akademi.php` + `assets/akademi.css/js` | Yazar Kariyer Akademisi bilgi sayfası ve ayarları |
| `mst-randevu/templates/uygulama.php` + `assets/uygulama.css` | "MST Yazar Paneli Tanıtım (Tam Sayfa)" şablonu — yazar uygulamasının tanıtım sayfası |
| `mst-randevu/assets/` | CSS, JS, logo |
| `mst-randevu/lib/plugin-update-checker/` | GitHub'dan güncelleme kütüphanesi (Plugin Update Checker 5.7, MIT) |
| `.github/workflows/eklenti-surumu.yml` | Sürüm artınca zip'li GitHub sürümü açar |
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
