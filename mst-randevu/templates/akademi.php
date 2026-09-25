<?php
/**
 * "MST Yazar Kariyer Akademisi (Tam Sayfa)" şablonu: akademinin bilgi sayfası.
 * Renkler sitenin paleti (antrasit, bronz, altın, krem; akademi bölümlerinde lacivert, sınırlarda bordo).
 * Kurgu: sahne ışığıyla açılan giriş → neden akademi → eğitim döngüsü → 12 alan → programlar
 * (sekmeli; fiyat her programın sonunda) → karşılaştırma → ücretsiz eğitim → güven → belge ve kapsam
 * → SSS → kapanış. Başvuru formu yoktur; iletişim WhatsApp ve ön görüşme randevusu üzerindendir.
 */
if (!defined('ABSPATH')) {
    exit;
}

$wa      = MST_Randevu::wa_link('Merhaba, Yazar Kariyer Akademisi hakkında bilgi almak istiyorum.');
$randevu = MST_Randevu::randevu_url();
$egitim  = MST_Akademi::ucretsiz_egitim();
$donem   = MST_Akademi::donem();
$ao      = MST_Akademi::opts();

/** Çizgi simgeler (24px ızgara). */
$ik = function ($n, $boy = 22) {
    $d = [
        'kalem'    => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13 7 4 4"/>',
        'dosya'    => '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
        'kitap'    => '<path d="M12 6c-2-1.5-5-2-8-2v14c3 0 6 .5 8 2 2-1.5 5-2 8-2V4c-3 0-6 .5-8 2z"/><path d="M12 6v14"/>',
        'hedef'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'kisi'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'kisiler'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M22 20a7 7 0 0 0-4-6.3"/>',
        'profil'   => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="11" r="2.5"/><path d="M5.5 17a4 4 0 0 1 7 0M15 9h3M15 13h3"/>',
        'takvim'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'kamera'   => '<rect x="2" y="6" width="14" height="12" rx="2"/><path d="m16 10 6-3v10l-6-3"/>',
        'kivilcim' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/>',
        'roket'    => '<path d="M5 15c-1.5 1.5-2 5-2 5s3.5-.5 5-2"/><path d="M9 15 6 12c1-3 4-8 12-9-1 8-6 11-9 12z"/><circle cx="14.5" cy="9.5" r="1.5"/>',
        'mikrofon' => '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/>',
        'grafik'   => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
        'onay'     => '<path d="M20 6 9 17l-5-5"/>',
        'ok'       => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'dongu'    => '<path d="M20 12a8 8 0 0 1-14.9 4M4 12a8 8 0 0 1 14.9-4"/><path d="M19 3v5h-5M5 21v-5h5"/>',
        'carpi'    => '<path d="M6 6l12 12M18 6 6 18"/>',
    ];
    return '<svg class="akd-ic" width="' . (int) $boy . '" height="' . (int) $boy . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($d[$n] ?? '') . '</svg>';
};

$bolumler = [
    ['sorun', 'Neden akademi?'], ['yaklasim', 'Yaklaşım'], ['alanlar', '12 alan'], ['programlar', 'Programlar'],
    ['karsilastirma', 'Karşılaştırma'], ['ucretsiz', 'Ücretsiz eğitim'], ['kapsam', 'Belge ve kapsam'], ['sss', 'SSS'],
];

$dongu = [
    ['Analiz', 'Mevcut durumunuz, hedefiniz ve ihtiyaçlarınız belirlenir.'],
    ['Eğitim', 'Sabit müfredatla canlı, çevrim içi dersler.'],
    ['Uygulama', 'Her dersin bir uygulama görevi vardır.'],
    ['Değerlendirme', 'Çalışmalarınız program seviyesine göre değerlendirilir.'],
    ['Yeni plan', 'Bir sonraki dönem için yol haritanız çıkar.'],
];

$kurallar = [
    'Programlar sabit müfredata sahiptir; katılımcılar ders konularını seçmez.',
    'Programlar dönem sistemiyle açılır; dönem ortasında katılımcı eklenmez.',
    'Her dersin bir uygulama görevi bulunur.',
    'Dersler canlı ve çevrim içi yapılır.',
    'Kayıt erişimi program seviyesine göre sunulur.',
    'Geri bildirim kapsamı program seviyesine göre artar.',
    'Üst programlarda kontenjan azalır, kişisel takip artar.',
    'Program yalnızca video izlenen bir kurs değildir; her katılımcı somut çalışma dosyaları oluşturur.',
];

$alanlar = [
    ['kisi', 'Yazar kimliği ve konumlandırma'], ['hedef', 'Hedef okur ve okur psikolojisi'], ['kalem', 'Yazma disiplini ve eser planlama'],
    ['dosya', 'Dosya sunumu, özet ve tanıtım metni'], ['profil', 'Kişisel marka ve profil mimarisi'], ['takvim', 'İçerik stratejisi ve yayın takvimi'],
    ['kamera', 'Kısa video, kamera ve anlatım'], ['kivilcim', 'Yapay zekâ ile içerik üretimi'], ['roket', 'Kitap lansmanı ve kampanya planı'],
    ['mikrofon', 'PR, medya ve röportaj hazırlığı'], ['kisiler', 'Topluluk, etkinlik ve okur bağı'], ['grafik', 'Satış kanalları ve performans takibi'],
];

$programlar = [
    'temel' => [
        'no' => '01', 'seviye' => 'Başlangıç', 'kontenjan' => 40, 'gorusme' => 0, 'sure' => '12 hafta',
        'kimler' => 'Yazma aşamasında olan veya dosyasını yeni tamamlayan kişiler için başlangıç programı.',
        'sorun' => 'Kitap fikri ya da yarım bir dosya var; ama yazma düzeni kurulamıyor, hedef okur ve yazar kimliği belirsiz, sosyal medyada ne paylaşılacağı bilinmiyor.',
        'hedef' => 'Yazar kimliğinizi, hedef okurunuzu ve sürdürülebilir üretim sisteminizi kurmak.',
        'kunye' => [['Süre', '12 hafta'], ['Canlı ders', '24 ders · 75 dk'], ['Toplam', '30 saat canlı eğitim'], ['Bireysel görüşme', 'Yok'], ['Kayıt erişimi', '90 gün'], ['Kontenjan', 'En fazla 40 kişi']],
        'yapi' => ['Aylık 8 canlı buluşma: 4 ana eğitim, 2 uygulama atölyesi, 1 çalışma değerlendirmesi, 1 soru-cevap ve gelişim kliniği', 'Haftada 2 ders, her ders 75 dakika', 'Haftalık uygulama görevi', 'Eğitim topluluğuna erişim'],
        'degerlendirme' => 'Ayda 1 toplu çalışma değerlendirmesi.',
        'mufredat_baslik' => 'Sabit 12 haftalık müfredat',
        'mufredat' => [
            ['1. hafta', 'Mevcut durum ve hedef belirleme', 'Katılımcının yazarlık aşaması, hedefleri ve ihtiyaçları değerlendirilir.'],
            ['2. hafta', 'Yazar kimliği ve konumlandırma', 'Yazarın kendisini ve üretim alanını nasıl anlatacağı belirlenir.'],
            ['3. hafta', 'Hedef okur ve okur psikolojisi', 'Kitabın ve yazarın ulaşmak istediği okur profili oluşturulur.'],
            ['4. hafta', 'Yazma disiplini ve eser planı', 'Yazma takvimi, çalışma düzeni ve eser planlama sistemi kurulur.'],
            ['5. hafta', 'Yazar markasının temelleri', 'Yazarın dijital kimliği ve iletişim dili ele alınır.'],
            ['6. hafta', 'İçerik sütunları ve yayın düzeni', 'Yazarın düzenli paylaşım yapabileceği ana içerik alanları belirlenir.'],
            ['7. hafta', 'Sosyal medya metni ve hikâye anlatımı', 'Paylaşım metni, dikkat çekici açılış ve içerik anlatımı çalışılır.'],
            ['8. hafta', 'Kamera ve kısa video', 'Kamera karşısında konuşma ve kısa video senaryosu hazırlanır.'],
            ['9. hafta', 'Yapay zekânın etik kullanımı', 'Araştırma, fikir geliştirme ve içerik desteği için yapay zekâ sistemi kurulur.'],
            ['10. hafta', 'Görsel ve video üretim düzeni', 'Haftalık içerik hazırlama ve yayınlama iş akışı oluşturulur.'],
            ['11. hafta', 'PR ve lansmana giriş', 'Kitap tanıtımının, lansmanın ve medya iletişiminin temelleri anlatılır.'],
            ['12. hafta', 'Performans ve devam planı', 'Katılımcının 90 günlük gelişim yol haritası hazırlanır.'],
        ],
        'ciktilar' => ['Yazar kimliği ve konumlandırma belgesi', 'Hedef okur profili', '30 günlük yazma sistemi', 'Düzenlenmiş sosyal medya biyografisi', 'Dört ana içerik alanı', '30 günlük içerik planı', '12 paylaşım fikri', '4 kısa video senaryosu', 'Kişisel yapay zekâ prompt dosyası', 'Temel kitap tanıtım planı', '90 günlük gelişim planı'],
        'fiyat' => 'Aylık 2.490 TL + KDV', 'fiyat_alt' => 'Program süresi 3 ay · toplam program bedeli 7.470 TL + KDV',
    ],
    'marka' => [
        'no' => '02', 'seviye' => 'Uygulamalı gelişim', 'kontenjan' => 16, 'gorusme' => 3, 'sure' => '12 hafta',
        'kimler' => 'Dosyası hazır veya kitabı yayımlanmış yazarlar için uygulamalı gelişim programı.',
        'sorun' => 'Eser hazır ya da raflarda; fakat hedef okur netleşmemiş, düzenli içerik ve video üretimi yok, lansman ve PR hazırlığı dağınık ilerliyor.',
        'hedef' => 'Profesyonel yazar markanızı; içerik, video, lansman ve PR sisteminizi kurmak.',
        'kunye' => [['Süre', '12 hafta'], ['Grup çalışması', '24 ders + 6 laboratuvar'], ['Toplam', 'Yaklaşık 40 saat canlı'], ['Bireysel görüşme', '3 × 45 dakika'], ['Kayıt erişimi', '180 gün'], ['Kontenjan', 'En fazla 16 kişi']],
        'yapi' => ['24 ana canlı ders ve 6 uygulama laboratuvarı', '3 bireysel strateji görüşmesi (45 dakika)', 'Haftalık uygulama görevi', 'Yaklaşık 40 saat canlı çalışma'],
        'degerlendirme' => '6 kişisel çalışma değerlendirmesi ve 3 bireysel strateji görüşmesi.',
        'mufredat_baslik' => 'Sabit müfredat · 6 blok',
        'mufredat' => [
            ['1–2. hafta', 'Stratejik temel', 'Yazar kariyer analizi · Eser analizi · Hedef okur psikolojisi · Yazar konumlandırması · Benzer yazar ve pazar incelemesi'],
            ['3–4. hafta', 'Yazar markası', 'Marka kişiliği · Profesyonel biyografi · İletişim dili · Sosyal medya profili · Görsel kimlik · Güven ve uzmanlık algısı'],
            ['5–6. hafta', 'İçerik sistemi', 'İçerik sütunları · İçerik takvimi · Hikâye anlatımı · Seri içerikler · Yapay zekâ ile araştırma ve metin geliştirme · Sürdürülebilir üretim düzeni'],
            ['7–8. hafta', 'Video ve kamera', 'Kamera karşısında anlatım · Reels açılışları · İzleyici tutma · Video senaryosu · Çekim planı · Mobil montaj · Kapak ve başlık sistemi'],
            ['9–10. hafta', 'Lansman ve PR', 'Kitabın ana mesajı · Lansman hikâyesi ve takvimi · Basın bülteni · Röportaj hazırlığı · Medyaya sunum · Kitap kulüpleri · İmza günü ve etkinlik planlaması'],
            ['11–12. hafta', 'Büyüme ve ölçüm', 'Satış kanalları · Organik tanıtım · Ücretli tanıtımın temelleri · Meta reklam mantığı · İçerik performansı · Temel KPI sistemi · 90 günlük görünürlük planı'],
        ],
        'ciktilar' => ['Yazar marka stratejisi', 'Profesyonel biyografi', 'Profil düzenleme raporu', 'Görsel ve iletişim dili', '30 günlük içerik takvimi', '20 Reels konusu', '8 ayrıntılı video senaryosu', 'Kitap lansman planı', 'Basın bülteni taslağı', 'Röportaj soru-cevap dosyası', 'Satış kanalı analizi', '90 günlük görünürlük yol haritası'],
        'fiyat' => '24.900 TL + KDV', 'fiyat_alt' => '12 haftalık program bedeli',
    ],
    'mentorluk' => [
        'no' => '03', 'seviye' => 'Sınırlı kontenjan', 'kontenjan' => 8, 'gorusme' => 8, 'sure' => '16 hafta',
        'kimler' => 'Kitabı yayımlanmış veya yayına hazır; profesyonel yazar markası, medya görünürlüğü ve uzun vadeli kariyer sistemi kurmak isteyenler için.',
        'sorun' => 'Kitap raflarda; fakat görünürlük, medya ilişkileri, okur topluluğu ve ikinci eser tek bir kariyer planına bağlanmamış.',
        'hedef' => 'Kişisel takip ve mentorlukla 12 aylık profesyonel yazar kariyer sisteminizi kurmak.',
        'kunye' => [['Süre', '16 hafta'], ['Grup çalışması', '32 ders + 8 lab + 4 kurul'], ['Toplam', 'Yaklaşık 55–60 saat'], ['Bireysel görüşme', '8 × 45 dakika'], ['Kayıt erişimi', '12 ay'], ['Kontenjan', 'En fazla 8 kişi']],
        'yapi' => ['32 canlı grup dersi, 8 uzmanlık laboratuvarı, 4 kariyer kurulu değerlendirmesi', '8 bireysel mentorluk görüşmesi (45 dakika)', 'Haftalık kişisel görev takibi', 'Aylık gelişim raporu'],
        'degerlendirme' => 'Haftalık kişisel görev takibi, aylık gelişim raporu ve 4 kariyer kurulu değerlendirmesi.',
        'mufredat_baslik' => 'Sabit müfredat · 6 aşama',
        'mufredat' => [
            ['1. aşama', 'Stratejik kimlik', 'Mevcut durum analizi · Kariyer hedefleri · Yazar konumlandırması · Hedef okur analizi · Eserin pazardaki yeri · Yazarın iletişim dili'],
            ['2. aşama', 'Marka altyapısı', 'Yazar marka mimarisi · Profesyonel profil · Biyografi · Görsel dil · Dijital varlık planı · İçerik sütunları · Web sitesi ihtiyaç planı'],
            ['3. aşama', 'İçerik ve üretim', '90 günlük içerik sistemi · Kamera eğitimi · Reels ve kısa video · Video senaryoları · Yapay zekâ üretim sistemi · Görsel üretim · Mobil montaj · Yayın düzeni'],
            ['4. aşama', 'Kitap lansmanı', 'Lansman hikâyesi · Kampanya mesajları · Tanıtım takvimi · Organik içerik planı · Reklam kreatif briefleri · Lansman performansı · Satış kanalları'],
            ['5. aşama', 'PR ve medya', 'Basın bülteni · Medya tanıtım dosyası · Röportaj hazırlığı · Kamera karşısında konuşma · Podcast hazırlığı · Haber ve makale fikirleri · Yorum ve kriz yönetimi'],
            ['6. aşama', 'Kariyer gelişimi', 'Okur topluluğu · Kitap kulüpleri · Etkinlikler · İmza günü planı · Marka iş birlikleri · İkinci eser stratejisi · KPI takip sistemi · 12 aylık kariyer planı'],
        ],
        'ciktilar' => ['Kişisel yazar marka kitabı', 'Profesyonel biyografi', 'Hedef okur raporu', 'Eser ve konumlandırma raporu', 'Medya tanıtım dosyası', '90 günlük içerik takvimi', '24 kısa video konusu', '12 ayrıntılı video senaryosu', 'Basın bülteni', 'Röportaj hazırlık dosyası', 'Kitap lansman planı', 'Reklam mesajları', 'Reklam kreatif briefleri', 'KPI takip tablosu', 'İkinci eser stratejisi', '12 aylık yazar kariyer yol haritası'],
        'fiyat' => '59.900 TL + KDV', 'fiyat_alt' => '16 haftalık program bedeli · ön görüşme ile',
    ],
];

$karsilastirma = [
    ['Süre', '12 hafta', '12 hafta', '16 hafta'],
    ['Grup çalışması', '24', '30', '44'],
    ['Bireysel görüşme', 'Yok', '3', '8'],
    ['Değerlendirme', 'Toplu', '6 kişisel çalışma', 'Haftalık takip'],
    ['Kontenjan', '40', '16', '8'],
    ['Kayıt erişimi', '90 gün', '180 gün', '12 ay'],
    ['Ana sonuç', 'Temel üretim sistemi', 'Marka ve görünürlük sistemi', 'Profesyonel kariyer sistemi'],
    ['Fiyat', '2.490 TL / ay', '24.900 TL', '59.900 TL'],
];

$farklar = [
    'Yayıncılık sektörünün içinden gelen bir eğitim sistemi',
    'Yazarın gerçek ihtiyaçlarına göre hazırlanmış sabit müfredat',
    'Sosyal medya, yapay zekâ, video, PR ve lansman birlikte ele alınır',
    'Teorik anlatımın yanında uygulama ve değerlendirme',
    'Program seviyesine göre kişisel geri bildirim',
    'Program sonunda kullanabileceğiniz somut dosyalar',
    'Kitap yayımlandıktan sonraki kariyer sürecine odaklanma',
];

$sss = [
    ['Programa katılmak için kitabımın yayımlanmış olması gerekir mi?', 'Hayır. Temel Program, yazma aşamasında olan veya dosyasını yeni tamamlayan kişiler için hazırlanmıştır. Diğer programlar dosyası hazır veya kitabı yayımlanmış yazarlara yöneliktir.'],
    ['Dersleri kendim seçebilir miyim?', 'Hayır. Her program, belirli bir yeterlilik seviyesine göre hazırlanmış sabit müfredata sahiptir.'],
    ['Programa dönem ortasında katılabilir miyim?', 'Hayır. Dersler birbirinin devamı olduğu için kayıtlar dönem başlangıcında alınır.'],
    ['Dersler canlı mı?', 'Evet. Eğitimler çevrim içi ve canlı olarak gerçekleştirilir.'],
    ['Ders kayıtlarına erişebilir miyim?', 'Evet. Temel Program’da 90 gün, Marka ve Görünürlük Programı’nda 180 gün, Kariyer Mentorluk Programı’nda 12 ay kayıt erişimi bulunur.'],
    ['Bireysel görüşme var mı?', 'Temel Program’da bireysel görüşme bulunmaz. Marka ve Görünürlük Programı’nda 3, Kariyer Mentorluk Programı’nda 8 bireysel görüşme bulunur.'],
    ['Program sonunda sertifika veriliyor mu?', 'Devam ve görev tamamlama koşullarını sağlayan katılımcılara MST Yayıncılık Yazar Kariyer Akademisi Program Tamamlama Belgesi verilebilir. Bu belge resmî veya üniversite onaylı bir sertifika değildir.'],
    ['Eğitim sonunda kitabımın satışı artar mı?', 'Eğitim programı satış garantisi vermez. Katılımcının hedef okurunu, içeriğini, tanıtımını ve kariyer planını daha düzenli yönetmesini amaçlar.'],
    ['Reklam bütçesi programa dahil mi?', 'Hayır. Reklam bütçesi, reklam yönetimi ve prodüksiyon hizmetleri ayrıca planlanır.'],
    ['Program ücretine kitap basımı dahil mi?', 'Hayır. Akademi programları ile yayın paketleri birbirinden ayrı hizmetlerdir.'],
];

/** Kontenjan grafiği: en büyük kontenjana göre yükseklik (%). */
$enCok = max(array_column($programlar, 'kontenjan'));
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#111111">
    <!-- Renkler tasarımın parçası: telefonun "zorla karanlık mod"u sayfayı ters çevirmesin -->
    <meta name="color-scheme" content="only light">
    <?php echo MST_Randevu::paylasim_meta('akademi'); ?>
    <script>document.documentElement.classList.add('akd-js');</script>
    <?php wp_head(); ?>
    <?php
    foreach (['mst-randevu' => 'randevu.css', 'mst-akademi' => 'akademi.css'] as $h => $dosya) {
        if (!wp_style_is($h, 'done')) {
            echo '<link rel="stylesheet" id="' . esc_attr($h) . '-yedek-css" href="' . esc_url(MST_Randevu::varlik($dosya)) . '">' . "\n";
        }
    }
    ?>
</head>
<body <?php body_class('mst-sayfa mst-akd'); ?>>
<?php wp_body_open(); ?>
<?php echo MST_Randevu::header_html(false, ['Ön Görüşme Al', $randevu], 'Merhaba, Yazar Kariyer Akademisi hakkında bilgi almak istiyorum.'); ?>

<main class="akd">

    <!-- ============ Açılış: sahne ============ -->
    <section class="akd-sahne" id="akademi">
        <div class="akd-sahne__isik" aria-hidden="true">
            <span class="akd-sahne__lamba"></span><span class="akd-sahne__huzme"></span>
            <canvas class="akd-sahne__toz"></canvas><span class="akd-sahne__zemin"></span><span class="akd-sahne__tahta"></span><span class="akd-gren"></span>
        </div>
        <div class="akd-kap akd-sahne__in">
            <p class="akd-kunye">MST Yayıncılık <i aria-hidden="true"></i> Yazar Kariyer Akademisi</p>
            <p class="akd-sahne__yaz" aria-hidden="true"><span data-akd-daktilo="Yazmak başlangıçtır.">Yazmak başlangıçtır.</span><i class="akd-imlec"></i></p>
            <h1 class="akd-sahne__baslik"><span class="akd-gizli-metin">Yazmak başlangıçtır. </span><span class="akd-sahne__ilk">Görünür olmak</span> <em>kariyerdir.</em></h1>
            <p class="akd-sahne__alt">Yazma aşamasından profesyonel yazar markasına kadar uzanan, uygulama ve takip temelli eğitim programları. Yazar kimliğinizi netleştirin, doğru okura ulaşın, içerik sisteminizi kurun ve kariyerinizi planlı biçimde yönetin.</p>
            <div class="akd-sahne__cta">
                <a class="akd-btn akd-btn--altin" href="#programlar">Programları inceleyin <?php echo $ik('ok', 18); ?></a>
                <?php if ($wa) : ?><a class="akd-btn akd-btn--cizgi" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo MST_Randevu::icon('wa'); ?> WhatsApp'tan bilgi alın</a><?php endif; ?>
            </div>
        </div>
        <ol class="akd-jenerik akd-kap" aria-label="Programlar">
            <?php foreach ($programlar as $k => $p) : ?>
                <li><a href="#program-<?php echo esc_attr($k); ?>" data-akd-sekme-ac="<?php echo esc_attr($k); ?>"><small><?php echo esc_html($p['no']); ?></small><strong><?php echo esc_html(MST_Akademi::PROGRAMLAR[$k][1]); ?></strong><span><?php echo esc_html($p['sure'] . ' · en fazla ' . $p['kontenjan'] . ' kişi'); ?></span></a></li>
            <?php endforeach; ?>
        </ol>
    </section>

    <nav class="akd-gezinti" aria-label="Sayfa bölümleri">
        <div class="akd-kap">
            <?php foreach ($bolumler as $b) : ?><a href="#<?php echo esc_attr($b[0]); ?>" data-akd-gez="<?php echo esc_attr($b[0]); ?>"><?php echo esc_html($b[1]); ?></a><?php endforeach; ?>
        </div>
    </nav>

    <!-- ============ Neden akademi ============ -->
    <section class="akd-bolum akd-bolum--krem" id="sorun">
        <div class="akd-kap">
            <p class="akd-etiket" data-akd-belir>Neden akademi?</p>
            <h2 class="akd-buyuk" data-akd-belir>İyi bir kitap, doğru okura ulaşmadığında <em>potansiyelinin altında</em> kalır.</h2>
            <div class="akd-sorun">
                <div class="akd-sorun__metin" data-akd-belir>
                    <p>Birçok yazar kitabını tamamladıktan sonra ne yapacağını bilemez. Sosyal medya kullanımı, video üretimi, yapay zekâ, lansman, PR, okur topluluğu ve satış kanalları birbirinden bağımsız ilerler.</p>
                    <p>Yazar Kariyer Akademisi bütün bu alanları <strong>belirli bir sıra içinde</strong> ele alır. Katılımcı konu seçmez; her program kendi yeterlilik seviyesine göre hazırlanmış sabit bir müfredatla yürür.</p>
                </div>
                <figure class="akd-duzen" data-akd-belir aria-label="Dağınık başlıklar, akademide sıralı bir sisteme dönüşür">
                    <div class="akd-duzen__daginik" aria-hidden="true">
                        <?php foreach (['Sosyal medya', 'Video', 'Yapay zekâ', 'Lansman', 'PR', 'Okur topluluğu', 'Satış kanalları'] as $c) : ?><span><?php echo esc_html($c); ?></span><?php endforeach; ?>
                    </div>
                    <span class="akd-duzen__ok" aria-hidden="true"><?php echo $ik('ok', 22); ?></span>
                    <ol class="akd-duzen__sirali">
                        <?php foreach (['Kimlik ve hedef okur', 'Marka ve profil', 'İçerik sistemi', 'Video ve yapay zekâ', 'Lansman ve PR', 'Topluluk ve satış'] as $c) : ?><li><?php echo esc_html($c); ?></li><?php endforeach; ?>
                    </ol>
                    <figcaption><span>Bugün: birbirinden bağımsız</span><span>Akademide: belirli bir sırayla</span></figcaption>
                </figure>
            </div>
            <blockquote class="akd-alinti" data-akd-belir>
                <p>MST Yayıncılık, yazarın yalnızca kitabını değil, <em>kariyerini de</em> geliştiren yayın ve eğitim merkezidir.</p>
            </blockquote>
        </div>
    </section>

    <!-- ============ Eğitim yaklaşımı ============ -->
    <section class="akd-bolum akd-bolum--koyu" id="yaklasim">
        <div class="akd-kap">
            <p class="akd-etiket" data-akd-belir>Eğitim yaklaşımı</p>
            <h2 class="akd-buyuk" data-akd-belir>İzlenen bir kurs değil; <em>uygulanan ve takip edilen</em> bir sistem.</h2>
            <ol class="akd-dongu" data-akd-belir>
                <?php foreach ($dongu as $i => $d) : ?>
                    <li><span class="akd-dongu__no"><?php echo $i + 1; ?></span><strong><?php echo esc_html($d[0]); ?></strong><p><?php echo esc_html($d[1]); ?></p></li>
                <?php endforeach; ?>
            </ol>
            <p class="akd-dongu__not" data-akd-belir><?php echo $ik('dongu', 18); ?> Her dönemin sonunda döngü, yeni planla yeniden başlar.</p>
            <div class="akd-kurallar">
                <h3 class="akd-orta" data-akd-belir>Sistemin kuralları</h3>
                <ol data-akd-belir>
                    <?php foreach ($kurallar as $i => $k) : ?><li><span><?php echo sprintf('%02d', $i + 1); ?></span><p><?php echo esc_html($k); ?></p></li><?php endforeach; ?>
                </ol>
            </div>
        </div>
    </section>

    <!-- ============ 12 alan ============ -->
    <section class="akd-bolum akd-bolum--krem" id="alanlar">
        <div class="akd-kap">
            <div class="akd-bas-ikili">
                <div>
                    <p class="akd-etiket" data-akd-belir>Müfredat havuzu</p>
                    <h2 class="akd-buyuk" data-akd-belir>Kariyer eğitimlerinin <em>12 temel alanı</em></h2>
                </div>
                <p class="akd-yan" data-akd-belir>Katılımcı bu alanlardan seçim yapmaz. İçerikler, ilgili programın sabit müfredatı içinde sırasıyla verilir.</p>
            </div>
            <ol class="akd-dizin" data-akd-belir>
                <?php foreach ($alanlar as $i => $a) : ?>
                    <li><span class="akd-dizin__no"><?php echo sprintf('%02d', $i + 1); ?></span><span class="akd-dizin__ad"><?php echo esc_html($a[1]); ?></span><?php echo $ik($a[0], 20); ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ Programlar ============ -->
    <section class="akd-bolum akd-bolum--lacivert" id="programlar">
        <div class="akd-kap">
            <div class="akd-bas-ikili akd-bas-ikili--grafik">
                <div>
                    <p class="akd-etiket" data-akd-belir>Programlar</p>
                    <h2 class="akd-buyuk" data-akd-belir>Seviyeniz yükseldikçe grup küçülür, <em>kişisel takip</em> artar.</h2>
                    <p class="akd-yan" data-akd-belir><?php echo $donem ? 'Sonraki dönem ' . esc_html($donem) . ' tarihinde başlıyor. ' : ''; ?>Kayıtlar dönem başlangıcında alınır; dönem ortasında katılımcı eklenmez.</p>
                </div>
                <figure class="akd-grafik" data-akd-belir>
                    <figcaption>Kontenjan ve bireysel görüşme</figcaption>
                    <div class="akd-grafik__alan">
                        <?php foreach ($programlar as $k => $p) : ?>
                            <div class="akd-grafik__sutun">
                                <div class="akd-grafik__cubuk-kap"><div class="akd-grafik__cubuk" style="--h:<?php echo round($p['kontenjan'] / $enCok * 100); ?>%"><b><?php echo (int) $p['kontenjan']; ?> kişi</b></div></div>
                                <div class="akd-grafik__noktalar" aria-hidden="true"><?php for ($i = 0; $i < 8; $i++) : ?><i class="<?php echo $i < $p['gorusme'] ? 'is-dolu' : ''; ?>"></i><?php endfor; ?></div>
                                <strong><?php echo esc_html(MST_Akademi::PROGRAMLAR[$k][1]); ?></strong>
                                <small><?php echo $p['gorusme'] ? (int) $p['gorusme'] . ' bireysel görüşme' : 'Bireysel görüşme yok'; ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="akd-grafik__lejant"><span><i class="akd-lj-cubuk"></i> Kontenjan</span><span><i class="akd-lj-nokta"></i> Bireysel görüşme (45 dk)</span></p>
                </figure>
            </div>

            <div class="akd-sekmeler" role="tablist" aria-label="Programlar">
                <?php $ilk = true; foreach ($programlar as $k => $p) : ?>
                    <button type="button" role="tab" id="sekme-<?php echo esc_attr($k); ?>" aria-controls="program-<?php echo esc_attr($k); ?>" aria-selected="<?php echo $ilk ? 'true' : 'false'; ?>" data-akd-sekme="<?php echo esc_attr($k); ?>">
                        <small><?php echo esc_html($p['no']); ?></small><strong><?php echo esc_html(MST_Akademi::PROGRAMLAR[$k][1]); ?></strong><span><?php echo esc_html($p['sure'] . ' · ' . $p['seviye']); ?></span>
                    </button>
                <?php $ilk = false; endforeach; ?>
            </div>

            <?php foreach ($programlar as $k => $p) : ?>
                <article class="akd-program akd-program--<?php echo esc_attr($k); ?>" id="program-<?php echo esc_attr($k); ?>" role="tabpanel" aria-labelledby="sekme-<?php echo esc_attr($k); ?>" data-akd-panel="<?php echo esc_attr($k); ?>">
                    <div class="akd-program__ust">
                        <div class="akd-program__kimlik">
                            <span class="akd-program__no" aria-hidden="true"><?php echo esc_html($p['no']); ?></span>
                            <span class="akd-program__seviye"><?php echo esc_html($p['seviye']); ?></span>
                            <h3><?php echo esc_html(MST_Akademi::PROGRAMLAR[$k][0]); ?></h3>
                            <p><?php echo esc_html($p['kimler']); ?></p>
                        </div>
                        <dl class="akd-kunye-tablo">
                            <?php foreach ($p['kunye'] as $x) : ?><div><dt><?php echo esc_html($x[0]); ?></dt><dd><?php echo esc_html($x[1]); ?></dd></div><?php endforeach; ?>
                        </dl>
                    </div>
                    <div class="akd-program__ikili">
                        <div class="akd-not"><small>Katılımcının yaşadığı sorun</small><p><?php echo esc_html($p['sorun']); ?></p></div>
                        <div class="akd-not akd-not--altin"><small>Programın hedefi</small><p><?php echo esc_html($p['hedef']); ?></p></div>
                    </div>
                    <div class="akd-program__govde">
                        <div class="akd-program__sol">
                            <h4>Ders sistemi</h4>
                            <ul class="akd-tik"><?php foreach ($p['yapi'] as $y) : ?><li><?php echo $ik('onay', 15); ?><span><?php echo esc_html($y); ?></span></li><?php endforeach; ?></ul>
                            <h4>Değerlendirme</h4>
                            <p class="akd-program__metin"><?php echo esc_html($p['degerlendirme']); ?></p>
                        </div>
                        <div class="akd-program__sag">
                            <h4><?php echo esc_html($p['mufredat_baslik']); ?></h4>
                            <ol class="akd-mufredat akd-mufredat--<?php echo count($p['mufredat']) > 6 ? 'uzun' : 'kisa'; ?>">
                                <?php foreach ($p['mufredat'] as $m) : ?>
                                    <li><span><?php echo esc_html($m[0]); ?></span><div><strong><?php echo esc_html($m[1]); ?></strong><p><?php echo esc_html($m[2]); ?></p></div></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>
                    <div class="akd-dosyalar">
                        <h4>Program sonunda elinizde olacak <?php echo count($p['ciktilar']); ?> çalışma dosyası</h4>
                        <ul><?php foreach ($p['ciktilar'] as $c) : ?><li><?php echo $ik('dosya', 16); ?><span><?php echo esc_html($c); ?></span></li><?php endforeach; ?></ul>
                    </div>
                    <footer class="akd-program__alt">
                        <div class="akd-fiyat"><small>Program bedeli</small><strong><?php echo esc_html($p['fiyat']); ?></strong><span><?php echo esc_html($p['fiyat_alt']); ?></span></div>
                        <p>Reklam bütçesi, prodüksiyon ve yayın hizmetleri fiyata dahil değildir. <a href="#kapsam">Kapsam</a></p>
                        <?php if ($wa) : ?><a class="akd-btn akd-btn--altin" href="<?php echo esc_url(MST_Randevu::wa_link('Merhaba, ' . MST_Akademi::PROGRAMLAR[$k][0] . ' hakkında bilgi almak istiyorum.')); ?>" target="_blank" rel="noopener">Bu program hakkında bilgi alın <?php echo $ik('ok', 18); ?></a><?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ Karşılaştırma ============ -->
    <section class="akd-bolum akd-bolum--beyaz" id="karsilastirma">
        <div class="akd-kap">
            <p class="akd-etiket" data-akd-belir>Karşılaştırma</p>
            <h2 class="akd-buyuk" data-akd-belir>Üç program, <em>yan yana</em>.</h2>
            <div class="akd-tablo-kap" data-akd-belir>
                <table class="akd-tablo">
                    <thead><tr><th scope="col"><span class="akd-gizli-metin">Özellik</span></th><?php foreach (MST_Akademi::PROGRAMLAR as $k => $p) : ?><th scope="col"><small><?php echo esc_html($programlar[$k]['no']); ?></small><?php echo esc_html($p[1]); ?></th><?php endforeach; ?></tr></thead>
                    <tbody>
                        <?php foreach ($karsilastirma as $s) : ?>
                            <tr><th scope="row"><?php echo esc_html($s[0]); ?></th><td><?php echo esc_html($s[1]); ?></td><td><?php echo esc_html($s[2]); ?></td><td><?php echo esc_html($s[3]); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="akd-dipnot">Tüm fiyatlara KDV eklenir.</p>
        </div>
    </section>

    <!-- ============ Ücretsiz eğitim ============ -->
    <section class="akd-bolum akd-bolum--koyu" id="ucretsiz">
        <div class="akd-kap akd-ucretsiz">
            <div class="akd-ucretsiz__metin">
                <p class="akd-etiket" data-akd-belir>Ücretsiz eğitim</p>
                <h2 class="akd-buyuk" data-akd-belir>Önce yolculuğunuzun <em>neresinde</em> olduğunuzu görün.</h2>
                <p class="akd-yan" data-akd-belir>Ücretli programların kısaltılmış hâli değildir. Mevcut durumunuzu fark etmenizi ve size uygun yolu seçmenizi sağlayan bir başlangıç buluşmasıdır.</p>
                <div class="akd-ucretsiz__listeler" data-akd-belir>
                    <div>
                        <h3>Eğitimde</h3>
                        <ul class="akd-tik"><?php foreach (['Yazarlık kariyerinin temel aşamaları', 'Yazar kimliği ve hedef okur', 'Kitap yayımlanmadan önce ve sonra yapılması gerekenler', 'Sosyal medyada yazar görünürlüğü', 'Yapay zekânın temel kullanımı ve içerik üretimi', 'PR ve lansmana giriş', 'Hangi programın kimler için uygun olduğu'] as $x) : ?><li><?php echo $ik('onay', 15); ?><span><?php echo esc_html($x); ?></span></li><?php endforeach; ?></ul>
                    </div>
                    <div>
                        <h3>Sonunda</h3>
                        <ul class="akd-tik"><?php foreach (['Kısa soru-cevap', 'Yazar Kariyer Analizi formu', 'Aşamanıza göre program önerisi', 'Uygun kişiler için ön görüşme'] as $x) : ?><li><?php echo $ik('onay', 15); ?><span><?php echo esc_html($x); ?></span></li><?php endforeach; ?></ul>
                    </div>
                </div>
            </div>
            <div class="akd-bilet-kap" data-akd-belir>
                <div class="akd-bilet">
                    <div class="akd-bilet__ana">
                        <span class="akd-bilet__ust">MST Yayıncılık · Davetiye</span>
                        <strong class="akd-bilet__baslik">Yazarlık Yolculuğunuzun Hangi Aşamasındasınız?</strong>
                        <dl>
                            <div><dt>Tarih</dt><dd><?php echo $egitim ? esc_html($egitim[1]) : 'Yeni tarih yakında açıklanacak'; ?></dd></div>
                            <div><dt>Yer</dt><dd><?php echo esc_html($ao['egitim_yeri'] ?: 'Çevrim içi, canlı'); ?></dd></div>
                            <?php if ($ao['egitim_suresi']) : ?><div><dt>Süre</dt><dd><?php echo esc_html($ao['egitim_suresi']); ?></dd></div><?php endif; ?>
                        </dl>
                    </div>
                    <div class="akd-bilet__kocan" aria-hidden="true"><span>Ücretsiz</span></div>
                </div>
                <?php if ($wa) : ?><a class="akd-btn akd-btn--altin" href="<?php echo esc_url(MST_Randevu::wa_link('Merhaba, Yazar Kariyer Akademisi ücretsiz eğitimine katılmak istiyorum.')); ?>" target="_blank" rel="noopener"><?php echo $egitim ? 'Yerimi ayırın' : 'Tarih açıklanınca haber verin'; ?> <?php echo $ik('ok', 18); ?></a><?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ============ Güven ============ -->
    <section class="akd-bolum akd-bolum--krem" id="guven">
        <div class="akd-kap akd-guven">
            <div>
                <p class="akd-etiket" data-akd-belir>Neden MST?</p>
                <h2 class="akd-buyuk" data-akd-belir>Yayıncılığın <em>içinden</em> gelen bir eğitim sistemi.</h2>
                <p class="akd-yan" data-akd-belir>MST Yayıncılık, yazarların editoryal, yayın, tanıtım ve kariyer süreçlerini birlikte ele alır. Eğitimler, genel sosyal medya anlatımı yerine yazarların ihtiyaçlarına göre hazırlanır.</p>
            </div>
            <ul class="akd-farklar" data-akd-belir>
                <?php foreach ($farklar as $f) : ?><li><?php echo $ik('onay', 16); ?><span><?php echo esc_html($f); ?></span></li><?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ============ Belge ve kapsam ============ -->
    <section class="akd-bolum akd-bolum--beyaz" id="kapsam">
        <div class="akd-kap">
            <p class="akd-etiket" data-akd-belir>Kalite standardı ve kapsam</p>
            <h2 class="akd-buyuk" data-akd-belir>Neyi vaat ettiğimizi ve <em>etmediğimizi</em> açıkça yazıyoruz.</h2>
            <div class="akd-belge" data-akd-belir>
                <div class="akd-belge__halkalar">
                    <?php foreach ([[80, 'devam'], [70, 'görev tamamlama']] as $h) : ?>
                        <div class="akd-halka" style="--p:<?php echo (int) $h[0]; ?>"><svg viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="52"/><circle cx="60" cy="60" r="52" class="akd-halka__dolu" pathLength="100"/></svg><strong>%<?php echo (int) $h[0]; ?></strong><span>en az <?php echo esc_html($h[1]); ?></span></div>
                    <?php endforeach; ?>
                </div>
                <div class="akd-belge__metin">
                    <h3>Program Tamamlama Belgesi</h3>
                    <p>Bu iki koşulu sağlayan katılımcılara <em>MST Yayıncılık Yazar Kariyer Akademisi Program Tamamlama Belgesi</em> verilir. Belge, resmî veya üniversite onaylı bir sertifika değildir.</p>
                    <ul class="akd-tik"><?php foreach (['Ders kayıtları program seviyesine göre sınırlı süreyle sunulur ve üçüncü kişilerle paylaşılamaz.', 'Grup içinde paylaşılan eser ve fikirlerin gizliliği korunur.', 'Bireysel görüşme süreleri ve adetleri her programda açıkça belirtilir.', 'Telafi dersi, iptal ve iade koşulları kayıt sözleşmesinde yer alır.'] as $x) : ?><li><?php echo $ik('onay', 15); ?><span><?php echo esc_html($x); ?></span></li><?php endforeach; ?></ul>
                </div>
            </div>
            <div class="akd-sinirlar" data-akd-belir>
                <div>
                    <h3>Eğitimler garanti vermez</h3>
                    <ul class="akd-carpi"><?php foreach (['Kitap satışı garantisi', 'Takipçi veya erişim garantisi', 'Basında yayın garantisi', 'Yayınevi tarafından kitap kabulü garantisi'] as $x) : ?><li><?php echo $ik('carpi', 15); ?><span><?php echo esc_html($x); ?></span></li><?php endforeach; ?></ul>
                    <p>Sonuçlar katılımcının uygulama düzenine, eserine ve hedef kitlesine göre değişir.</p>
                </div>
                <div>
                    <h3>Fiyata dahil değildir</h3>
                    <ul class="akd-carpi"><?php foreach (['Reklam bütçesi ve reklam yönetimi', 'Profesyonel video çekimi', 'Ücretli basın ve medya yayınları', 'Web sitesi yapımı (ayrıca fiyatlandırılır)', 'Kitap basımı ve yayın sözleşmesi'] as $x) : ?><li><?php echo $ik('carpi', 15); ?><span><?php echo esc_html($x); ?></span></li><?php endforeach; ?></ul>
                    <p>Eğitim içerikleri ve çalışma dosyaları izinsiz çoğaltılamaz.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ SSS ============ -->
    <section class="akd-bolum akd-bolum--krem" id="sss">
        <div class="akd-kap akd-sss">
            <div class="akd-sss__bas">
                <p class="akd-etiket">Sık sorulan sorular</p>
                <h2 class="akd-buyuk">Aklınızdaki <em>sorular</em></h2>
                <?php if ($wa) : ?><p class="akd-yan">Sorunuz burada yoksa <a href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener">WhatsApp'tan yazın</a>.</p><?php endif; ?>
            </div>
            <div class="akd-sss__liste">
                <?php foreach ($sss as $s) : ?>
                    <details><summary><?php echo esc_html($s[0]); ?></summary><p><?php echo esc_html($s[1]); ?></p></details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ Kapanış ============ -->
    <section class="akd-kapanis" id="iletisim">
        <div class="akd-kapanis__isik" aria-hidden="true"><span class="akd-kapanis__huzme"></span><span class="akd-gren"></span></div>
        <div class="akd-kap akd-kapanis__in">
            <p class="akd-etiket">İletişim</p>
            <h2 class="akd-kapanis__baslik">Sahne hazır. <em>Sıradaki adım sizin.</em></h2>
            <p class="akd-kapanis__alt">Hangi programın size uygun olduğunu birlikte konuşalım. WhatsApp'tan yazın ya da ücretsiz ön görüşme randevusu alın.</p>
            <div class="akd-sahne__cta">
                <?php if ($wa) : ?><a class="akd-btn akd-btn--altin" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo MST_Randevu::icon('wa'); ?> WhatsApp'tan bilgi alın</a><?php endif; ?>
                <a class="akd-btn akd-btn--cizgi" href="<?php echo esc_url($randevu); ?>">Ücretsiz ön görüşme randevusu</a>
            </div>
        </div>
    </section>

    <?php
    while (have_posts()) {
        the_post();
        $icerik = trim(get_the_content());
        if ($icerik !== '') echo '<section class="akd-bolum akd-bolum--beyaz"><div class="akd-kap akd-icerik">' . apply_filters('the_content', $icerik) . '</div></section>';
    }
    ?>
    <footer class="akd-alt">
        <div class="akd-kap">
            <span>© <?php echo esc_html(wp_date('Y')); ?> MST Yayıncılık · Yazar Kariyer Akademisi</span>
            <a href="<?php echo esc_url(MST_Randevu::kvkk_url()); ?>">KVKK Aydınlatma Metni</a>
            <a href="<?php echo esc_url(home_url('/')); ?>">mstyayincilik.com</a>
        </div>
    </footer>
</main>

<?php wp_footer(); ?>
</body>
</html>
