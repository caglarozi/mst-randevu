<?php
/**
 * "MST Yazar Kariyer Akademisi (Tam Sayfa)" şablonu: akademinin bilgi ve başvuru sayfası.
 * Renkler ve ortak bileşenler (üst çubuk, .uyg-* bölümleri, giriş ışığı) Yazar Paneli
 * tanıtım sayfasıyla aynıdır; akademiye özgü stiller assets/akademi.css'tedir.
 * Program sayfası sıralaması: fiyat her programın en sonunda gösterilir.
 */
if (!defined('ABSPATH')) {
    exit;
}

$wa      = MST_Randevu::wa_link('Merhaba, Yazar Kariyer Akademisi hakkında bilgi almak istiyorum.');
$randevu = MST_Randevu::randevu_url();
$egitim = MST_Akademi::ucretsiz_egitim();
$donem  = MST_Akademi::donem();
$ao     = MST_Akademi::opts();

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
        'megafon'  => '<path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1z"/><path d="M15 9a4 4 0 0 1 0 6M18 6a8 8 0 0 1 0 12"/>',
        'mikrofon' => '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/>',
        'grafik'   => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
        'onay'     => '<path d="M20 6 9 17l-5-5"/>',
        'ok'       => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'dongu'    => '<path d="M20 12a8 8 0 0 1-14.9 4M4 12a8 8 0 0 1 14.9-4"/><path d="M19 3v5h-5M5 21v-5h5"/>',
        'kilit'    => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'saat'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'yildiz'   => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
        'kalkan'   => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
        'carpi'    => '<path d="M6 6l12 12M18 6 6 18"/>',
        'soru'     => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.7M12 17h.01"/>',
        'wa'       => '',
    ];
    return '<svg class="uyg-ic" width="' . (int) $boy . '" height="' . (int) $boy . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($d[$n] ?? '') . '</svg>';
};
$liste = function (array $maddeler, $sinif = '') use ($ik) {
    $h = '<ul class="uyg-liste ' . esc_attr($sinif) . '">';
    foreach ($maddeler as $m) $h .= '<li>' . $ik('onay', 16) . '<span>' . esc_html($m) . '</span></li>';
    return $h . '</ul>';
};

$bolumler = [
    ['#sorun', 'Neden akademi?'], ['#yaklasim', 'Eğitim yaklaşımı'], ['#programlar', 'Programlar'], ['#karsilastirma', 'Karşılaştırma'],
    ['#ucretsiz', 'Ücretsiz eğitim'], ['#kapsam', 'Kapsam'], ['#sss', 'SSS'], ['#iletisim', 'İletişim'],
];

$dongu = ['Analiz', 'Eğitim', 'Uygulama', 'Değerlendirme', 'Yeni plan'];

$programlar = [
    'temel' => [
        'no' => '01', 'seviye' => 'Başlangıç',
        'kimler' => 'Yazma aşamasında olan veya dosyasını yeni tamamlayan kişiler için başlangıç programı.',
        'sorun' => 'Kitap fikri ya da yarım bir dosya var; ama yazma düzeni kurulamıyor, hedef okur ve yazar kimliği belirsiz, sosyal medyada ne paylaşılacağı bilinmiyor.',
        'hedef' => 'Yazar kimliğinizi, hedef okurunuzu ve sürdürülebilir üretim sisteminizi kurmak.',
        'olcu' => [['12 hafta', 'süre'], ['24 canlı ders', 'haftada 2 · 75 dk'], ['30 saat', 'canlı eğitim'], ['90 gün', 'kayıt erişimi'], ['En fazla 40', 'katılımcı']],
        'yapi' => ['Aylık 8 canlı buluşma: 4 ana eğitim, 2 uygulama atölyesi, 1 çalışma değerlendirmesi, 1 soru-cevap ve gelişim kliniği', 'Haftalık uygulama görevi', 'Ayda 1 toplu çalışma değerlendirmesi', 'Eğitim topluluğuna erişim'],
        'mufredat_baslik' => 'Sabit 12 haftalık müfredat',
        'mufredat' => [
            ['1. Hafta', 'Mevcut durum ve hedef belirleme', 'Katılımcının yazarlık aşaması, hedefleri ve ihtiyaçları değerlendirilir.'],
            ['2. Hafta', 'Yazar kimliği ve konumlandırma', 'Yazarın kendisini ve üretim alanını nasıl anlatacağı belirlenir.'],
            ['3. Hafta', 'Hedef okur ve okur psikolojisi', 'Kitabın ve yazarın ulaşmak istediği okur profili oluşturulur.'],
            ['4. Hafta', 'Yazma disiplini ve eser planı', 'Yazma takvimi, çalışma düzeni ve eser planlama sistemi kurulur.'],
            ['5. Hafta', 'Yazar markasının temelleri', 'Yazarın dijital kimliği ve iletişim dili ele alınır.'],
            ['6. Hafta', 'İçerik sütunları ve yayın düzeni', 'Yazarın düzenli paylaşım yapabileceği ana içerik alanları belirlenir.'],
            ['7. Hafta', 'Sosyal medya metni ve hikâye anlatımı', 'Paylaşım metni, dikkat çekici açılış ve içerik anlatımı çalışılır.'],
            ['8. Hafta', 'Kamera ve kısa video', 'Kamera karşısında konuşma ve kısa video senaryosu hazırlanır.'],
            ['9. Hafta', 'Yapay zekânın etik kullanımı', 'Araştırma, fikir geliştirme ve içerik desteği için yapay zekâ sistemi kurulur.'],
            ['10. Hafta', 'Görsel ve video üretim düzeni', 'Haftalık içerik hazırlama ve yayınlama iş akışı oluşturulur.'],
            ['11. Hafta', 'PR ve lansmana giriş', 'Kitap tanıtımının, lansmanın ve medya iletişiminin temelleri anlatılır.'],
            ['12. Hafta', 'Performans ve devam planı', 'Katılımcının 90 günlük gelişim yol haritası hazırlanır.'],
        ],
        'ciktilar' => ['Yazar kimliği ve konumlandırma belgesi', 'Hedef okur profili', '30 günlük yazma sistemi', 'Düzenlenmiş sosyal medya biyografisi', 'Dört ana içerik alanı', '30 günlük içerik planı', '12 paylaşım fikri', '4 kısa video senaryosu', 'Kişisel yapay zekâ prompt dosyası', 'Temel kitap tanıtım planı', '90 günlük gelişim planı'],
        'degerlendirme' => 'Ayda 1 toplu çalışma değerlendirmesi. Bu programda bireysel görüşme bulunmaz.',
        'fiyat' => 'Aylık 2.490 TL + KDV', 'fiyat_alt' => 'Program süresi 3 ay · Toplam program bedeli 7.470 TL + KDV',
    ],
    'marka' => [
        'no' => '02', 'seviye' => 'Uygulamalı gelişim',
        'kimler' => 'Dosyası hazır veya kitabı yayımlanmış yazarlar için uygulamalı gelişim programı.',
        'sorun' => 'Eser hazır ya da raflarda; fakat hedef okur netleşmemiş, düzenli içerik ve video üretimi yok, lansman ve PR hazırlığı dağınık ilerliyor.',
        'hedef' => 'Profesyonel yazar markanızı, içerik, video, lansman ve PR sisteminizi kurmak.',
        'olcu' => [['12 hafta', 'süre'], ['30 grup çalışması', '24 ders + 6 laboratuvar'], ['3 bireysel', 'strateji görüşmesi · 45 dk'], ['180 gün', 'kayıt erişimi'], ['En fazla 16', 'katılımcı']],
        'yapi' => ['24 ana canlı ders ve 6 uygulama laboratuvarı', '3 bireysel strateji görüşmesi (45 dakika)', '6 kişisel çalışma değerlendirmesi', 'Haftalık uygulama görevi', 'Yaklaşık 40 saat canlı çalışma'],
        'mufredat_baslik' => 'Sabit müfredat',
        'mufredat' => [
            ['1–2. haftalar', 'Stratejik temel', 'Yazar kariyer analizi · Eser analizi · Hedef okur psikolojisi · Yazar konumlandırması · Benzer yazar ve pazar incelemesi'],
            ['3–4. haftalar', 'Yazar markası', 'Marka kişiliği · Profesyonel biyografi · İletişim dili · Sosyal medya profili · Görsel kimlik · Güven ve uzmanlık algısı'],
            ['5–6. haftalar', 'İçerik sistemi', 'İçerik sütunları · İçerik takvimi · Hikâye anlatımı · Seri içerikler · Yapay zekâ ile araştırma ve metin geliştirme · Sürdürülebilir üretim düzeni'],
            ['7–8. haftalar', 'Video ve kamera', 'Kamera karşısında anlatım · Reels açılışları · İzleyici tutma · Video senaryosu · Çekim planı · Mobil montaj · Kapak ve başlık sistemi'],
            ['9–10. haftalar', 'Lansman ve PR', 'Kitabın ana mesajı · Lansman hikâyesi ve takvimi · Basın bülteni · Röportaj hazırlığı · Medyaya sunum · Kitap kulüpleri · İmza günü ve etkinlik planlaması'],
            ['11–12. haftalar', 'Büyüme ve ölçüm', 'Satış kanalları · Organik tanıtım · Ücretli tanıtımın temelleri · Meta reklam mantığı · İçerik performansı · Temel KPI sistemi · 90 günlük görünürlük planı'],
        ],
        'ciktilar' => ['Yazar marka stratejisi', 'Profesyonel biyografi', 'Profil düzenleme raporu', 'Görsel ve iletişim dili', '30 günlük içerik takvimi', '20 Reels konusu', '8 ayrıntılı video senaryosu', 'Kitap lansman planı', 'Basın bülteni taslağı', 'Röportaj soru-cevap dosyası', 'Satış kanalı analizi', '90 günlük görünürlük yol haritası'],
        'degerlendirme' => '6 kişisel çalışma değerlendirmesi ve 3 bireysel strateji görüşmesi (her biri 45 dakika).',
        'fiyat' => '24.900 TL + KDV', 'fiyat_alt' => '12 haftalık program bedeli',
    ],
    'mentorluk' => [
        'no' => '03', 'seviye' => 'Sınırlı kontenjan',
        'kimler' => 'Kitabı yayımlanmış veya yayına hazır; profesyonel yazar markası, medya görünürlüğü ve uzun vadeli kariyer sistemi kurmak isteyenler için.',
        'sorun' => 'Kitap raflarda; fakat görünürlük, medya ilişkileri, okur topluluğu ve ikinci eser tek bir kariyer planına bağlanmamış.',
        'hedef' => 'Kişisel takip ve mentorlukla 12 aylık profesyonel yazar kariyer sisteminizi kurmak.',
        'olcu' => [['16 hafta', 'süre'], ['44 grup çalışması', '32 ders + 8 lab + 4 kurul'], ['8 bireysel', 'mentorluk görüşmesi · 45 dk'], ['12 ay', 'kayıt erişimi'], ['En fazla 8', 'katılımcı']],
        'yapi' => ['32 canlı grup dersi, 8 uzmanlık laboratuvarı, 4 kariyer kurulu değerlendirmesi', '8 bireysel mentorluk görüşmesi (45 dakika)', 'Haftalık kişisel görev takibi', 'Aylık gelişim raporu', 'Yaklaşık 55–60 saat eğitim ve mentorluk'],
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
        'degerlendirme' => 'Haftalık kişisel görev takibi, aylık gelişim raporu, 4 kariyer kurulu değerlendirmesi ve 8 bireysel mentorluk görüşmesi (her biri 45 dakika).',
        'fiyat' => '59.900 TL + KDV', 'fiyat_alt' => '16 haftalık program bedeli · başvuru ve ön görüşme ile',
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

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#161616">
    <?php echo MST_Randevu::paylasim_meta('akademi'); ?>
    <script>document.documentElement.classList.add('uyg-js');</script>
    <?php wp_head(); ?>
    <?php
    foreach (['mst-randevu' => 'randevu.css', 'mst-uygulama' => 'uygulama.css', 'mst-akademi' => 'akademi.css'] as $h => $dosya) {
        if (!wp_style_is($h, 'done')) {
            echo '<link rel="stylesheet" id="' . esc_attr($h) . '-yedek-css" href="' . esc_url(MST_RANDEVU_URL . 'assets/' . $dosya . '?ver=' . MST_RANDEVU_VER) . '">' . "\n";
        }
    }
    ?>
</head>
<body <?php body_class('mst-sayfa mst-uyg mst-akd'); ?>>
<?php wp_body_open(); ?>
<?php echo MST_Randevu::header_html(false, ['Ön Görüşme Al', $randevu], 'Merhaba, Yazar Kariyer Akademisi hakkında bilgi almak istiyorum.'); ?>

<main class="uyg akd">

    <!-- ============ Açılış: sahne ============ -->
    <section class="akd-sahne" id="akademi">
        <div class="akd-sahne__isik" aria-hidden="true"><span class="akd-sahne__lamba"></span><span class="akd-sahne__huzme"></span><canvas class="akd-sahne__toz"></canvas><span class="akd-sahne__zemin"></span></div>
        <div class="uyg-kap akd-sahne__in">
            <span class="akd-marka">MST Yayıncılık · Yazar Kariyer Akademisi</span>
            <p class="akd-sahne__yaz" aria-hidden="true"><span data-akd-daktilo="Yazmak başlangıçtır.">Yazmak başlangıçtır.</span><i class="akd-imlec"></i></p>
            <h1 class="akd-sahne__baslik"><span class="akd-gizli-metin">Yazmak başlangıçtır. </span>Görünür olmak <em>kariyerdir.</em></h1>
            <p class="akd-sahne__alt">Yazma aşamasından profesyonel yazar markasına kadar uzanan, uygulama ve takip temelli eğitim programları. Yazar kimliğinizi netleştirin, doğru okura ulaşın, içerik sisteminizi kurun ve kariyerinizi planlı biçimde yönetin.</p>

            <div class="akd-sahne__cta">
                <a class="uyg-btn uyg-btn--altin" href="#programlar">Programları İnceleyin <?php echo $ik('ok', 18); ?></a>
                <a class="akd-sahne__link" href="#ucretsiz">Ücretsiz eğitim<?php echo $egitim ? ' <small>' . esc_html($egitim[1]) . '</small>' : ''; ?></a>
                <?php if ($wa) : ?><a class="akd-sahne__link" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener">WhatsApp'tan bilgi alın</a><?php endif; ?>
            </div>
        </div>
    </section>

    <nav class="akd-gezinti" aria-label="Sayfa bölümleri">
        <div class="uyg-kap">
            <?php foreach ($bolumler as $b) : ?><a href="<?php echo esc_attr($b[0]); ?>" data-akd-gez="<?php echo esc_attr(substr($b[0], 1)); ?>"><?php echo esc_html($b[1]); ?></a><?php endforeach; ?>
        </div>
    </nav>

    <!-- ============ Sorun alanı ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="sorun">
        <div class="uyg-kap uyg-iki">
            <div>
                <header class="uyg-baslik uyg-baslik--sol">
                    <span class="uyg-ust"><?php echo $ik('kitap', 18); ?> Neden akademi?</span>
                    <h2>İyi bir kitap, doğru okura ulaşmadığında potansiyelinin altında kalır.</h2>
                </header>
                <p class="akd-paragraf">Birçok yazar kitabını tamamladıktan sonra ne yapacağını bilemez. Sosyal medya kullanımı, video üretimi, yapay zekâ, lansman, PR, okur topluluğu ve satış kanalları birbirinden bağımsız ilerler.</p>
                <p class="akd-paragraf">Yazar Kariyer Akademisi bütün bu alanları <strong>belirli bir sıra içinde</strong> ele alır. Katılımcı konu seçmez; her program kendi yeterlilik seviyesine göre hazırlanmış sabit bir müfredatla yürür.</p>
            </div>
            <div class="akd-duzen" aria-hidden="true">
                <div class="akd-duzen__daginik">
                    <?php foreach (['Sosyal medya', 'Video', 'Yapay zekâ', 'Lansman', 'PR', 'Okur topluluğu', 'Satış kanalları'] as $i => $c) : ?>
                        <span style="--i:<?php echo (int) $i; ?>"><?php echo esc_html($c); ?></span>
                    <?php endforeach; ?>
                    <small>Birbirinden bağımsız</small>
                </div>
                <div class="akd-duzen__ok"><?php echo $ik('ok', 28); ?></div>
                <ol class="akd-duzen__sirali">
                    <?php foreach (['Kimlik ve hedef okur', 'Marka ve profil', 'İçerik sistemi', 'Video ve yapay zekâ', 'Lansman ve PR', 'Topluluk ve satış'] as $c) : ?>
                        <li><?php echo esc_html($c); ?></li>
                    <?php endforeach; ?>
                    <small>Belirli bir sırayla</small>
                </ol>
            </div>
        </div>
    </section>

    <!-- ============ Eğitim yaklaşımı ============ -->
    <section class="uyg-bolum uyg-bolum--koyu" id="yaklasim">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('dongu', 18); ?> Eğitim yaklaşımı</span>
                <h2>İzlenen bir kurs değil; uygulanan ve takip edilen bir sistem.</h2>
            </header>
            <ol class="akd-cark">
                <?php foreach ($dongu as $i => $d) : ?><li><span><?php echo $i + 1; ?></span><?php echo esc_html($d); ?></li><?php endforeach; ?>
            </ol>
            <div class="akd-kurallar">
                <?php foreach ($kurallar as $k) : ?>
                    <div class="akd-kural"><span class="akd-kural__ic"><?php echo $ik($k[0], 20); ?></span><p><?php echo esc_html($k[1]); ?></p></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ 12 konu alanı ============ -->
    <section class="uyg-bolum" id="alanlar">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('kitap', 18); ?> Eğitim havuzu</span>
                <h2>Kariyer eğitimlerinin 12 temel alanı</h2>
                <p>Katılımcı bu alanlardan seçim yapmaz; içerikler, ilgili programın sabit müfredatı içinde sırasıyla verilir.</p>
            </header>
            <ol class="akd-alanlar">
                <?php foreach ($alanlar as $i => $a) : ?>
                    <li><span class="akd-alanlar__no"><?php echo sprintf('%02d', $i + 1); ?></span><span class="akd-alanlar__ic"><?php echo $ik($a[0], 20); ?></span><strong><?php echo esc_html($a[1]); ?></strong></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ Programlar ============ -->
    <section class="uyg-bolum akd-lacivert" id="programlar">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('dosya', 18); ?> Programlar</span>
                <h2>Seviyenize göre üç program</h2>
                <p>Üst programlarda kontenjan azalır, kişisel takip artar. <?php echo $donem ? 'Sonraki dönem: <b>' . esc_html($donem) . '</b>.' : 'Kayıtlar dönem başlangıcında alınır.'; ?></p>
            </header>
            <div class="akd-program-sekme" role="tablist">
                <?php foreach ($programlar as $k => $p) : ?>
                    <a href="#program-<?php echo esc_attr($k); ?>" role="tab"><span><?php echo esc_html($p['no']); ?></span><?php echo esc_html(MST_Akademi::PROGRAMLAR[$k][1]); ?></a>
                <?php endforeach; ?>
            </div>

            <?php foreach ($programlar as $k => $p) : ?>
                <article class="akd-program akd-program--<?php echo esc_attr($k); ?>" id="program-<?php echo esc_attr($k); ?>">
                    <header class="akd-program__bas">
                        <span class="akd-program__no"><?php echo esc_html($p['no']); ?></span>
                        <div>
                            <span class="akd-program__seviye"><?php echo esc_html($p['seviye']); ?></span>
                            <h3><?php echo esc_html(MST_Akademi::PROGRAMLAR[$k][0]); ?></h3>
                            <p><?php echo esc_html($p['kimler']); ?></p>
                        </div>
                    </header>
                    <div class="akd-program__ikili">
                        <div class="akd-kutu"><small>Katılımcının yaşadığı sorun</small><p><?php echo esc_html($p['sorun']); ?></p></div>
                        <div class="akd-kutu akd-kutu--altin"><small>Programın hedefi</small><p><?php echo esc_html($p['hedef']); ?></p></div>
                    </div>
                    <dl class="akd-olcu">
                        <?php foreach ($p['olcu'] as $o) : ?><div><dt><?php echo esc_html($o[0]); ?></dt><dd><?php echo esc_html($o[1]); ?></dd></div><?php endforeach; ?>
                    </dl>
                    <div class="akd-program__ikili">
                        <div>
                            <h4>Süre ve ders sistemi</h4>
                            <?php echo $liste($p['yapi']); ?>
                        </div>
                        <div>
                            <h4>Değerlendirme</h4>
                            <p class="akd-program__metin"><?php echo esc_html($p['degerlendirme']); ?></p>
                        </div>
                    </div>
                    <details class="akd-acilir" <?php echo $k === 'temel' ? 'open' : ''; ?>>
                        <summary><?php echo esc_html($p['mufredat_baslik']); ?> <small><?php echo count($p['mufredat']); ?> başlık</small></summary>
                        <ol class="akd-mufredat">
                            <?php foreach ($p['mufredat'] as $m) : ?>
                                <li><span><?php echo esc_html($m[0]); ?></span><div><strong><?php echo esc_html($m[1]); ?></strong><p><?php echo esc_html($m[2]); ?></p></div></li>
                            <?php endforeach; ?>
                        </ol>
                    </details>
                    <details class="akd-acilir">
                        <summary>Program çıktıları <small><?php echo count($p['ciktilar']); ?> çalışma dosyası</small></summary>
                        <?php echo $liste($p['ciktilar'], 'akd-ciktilar'); ?>
                    </details>
                    <footer class="akd-program__alt">
                        <div class="akd-fiyat"><small>Program bedeli</small><strong><?php echo esc_html($p['fiyat']); ?></strong><span><?php echo esc_html($p['fiyat_alt']); ?></span></div>
                        <p class="akd-program__not">Kayıtlar dönem başlangıcında alınır; dönem ortasında katılımcı eklenmez. Reklam bütçesi, prodüksiyon ve yayın hizmetleri fiyata dahil değildir. <a href="#kapsam">Kapsam</a></p>
                        <?php if ($wa) : ?><a class="uyg-btn uyg-btn--altin" href="<?php echo esc_url(MST_Randevu::wa_link('Merhaba, ' . MST_Akademi::PROGRAMLAR[$k][0] . ' hakkında bilgi almak istiyorum.')); ?>" target="_blank" rel="noopener">Bu program hakkında bilgi alın <?php echo $ik('ok', 18); ?></a><?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ Karşılaştırma ============ -->
    <section class="uyg-bolum" id="karsilastirma">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('grafik', 18); ?> Karşılaştırma</span>
                <h2>Programlar yan yana</h2>
            </header>
            <div class="akd-tablo-kap">
                <table class="akd-tablo">
                    <thead><tr><th scope="col">Özellik</th><?php foreach (MST_Akademi::PROGRAMLAR as $k => $p) : ?><th scope="col" class="akd-tablo--<?php echo esc_attr($k); ?>"><?php echo esc_html($p[1]); ?></th><?php endforeach; ?></tr></thead>
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
    <section class="uyg-bolum uyg-bolum--koyu akd-ucretsiz" id="ucretsiz">
        <div class="uyg-kap uyg-iki">
            <div>
                <header class="uyg-baslik uyg-baslik--sol">
                    <span class="uyg-ust"><?php echo $ik('takvim', 18); ?> Ücretsiz eğitim</span>
                    <h2>Yazarlık Yolculuğunuzun Hangi Aşamasındasınız?</h2>
                    <p>Ücretli programların kısaltılmış hâli değildir. Mevcut durumunuzu görmenizi ve size uygun yolu seçmenizi sağlayan bir başlangıç buluşmasıdır.</p>
                </header>
                <div class="akd-tarih">
                    <?php if ($egitim) : ?>
                        <span class="akd-tarih__etiket">Sıradaki eğitim</span>
                        <strong><?php echo esc_html($egitim[1]); ?></strong>
                        <span><?php echo esc_html(trim($ao['egitim_yeri'] . ($ao['egitim_suresi'] ? ' · ' . $ao['egitim_suresi'] : ''))); ?></span>
                    <?php else : ?>
                        <span class="akd-tarih__etiket">Sıradaki eğitim</span>
                        <strong>Yeni tarih yakında açıklanacak</strong>
                        <span>WhatsApp'tan yazın; tarih belli olduğunda size haber verelim.</span>
                    <?php endif; ?>
                </div>
                <?php if ($wa) : ?><a class="uyg-btn uyg-btn--altin" href="<?php echo esc_url(MST_Randevu::wa_link('Merhaba, Yazar Kariyer Akademisi ücretsiz eğitimine katılmak istiyorum.')); ?>" target="_blank" rel="noopener">Ücretsiz eğitime katılmak istiyorum <?php echo $ik('ok', 18); ?></a><?php endif; ?>
            </div>
            <div class="akd-ucretsiz__icerik">
                <h3>Eğitimde neler var?</h3>
                <?php echo $liste(['Yazarlık kariyerinin temel aşamaları', 'Yazar kimliği ve hedef okur', 'Kitap yayımlanmadan önce ve sonra yapılması gerekenler', 'Sosyal medyada yazar görünürlüğü', 'Yapay zekânın temel kullanımı ve içerik üretimi', 'PR ve lansmana giriş', 'Hangi programın kimler için uygun olduğu']); ?>
                <h3>Eğitimin sonunda</h3>
                <?php echo $liste(['Kısa soru-cevap', 'Yazar Kariyer Analizi formu', 'Aşamanıza göre program önerisi', 'Uygun kişiler için ön görüşme']); ?>
            </div>
        </div>
    </section>

    <!-- ============ Güven ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="guven">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('kalkan', 18); ?> Neden MST?</span>
                <h2>Yayıncılığın içinden gelen bir eğitim sistemi</h2>
                <p>MST Yayıncılık, yazarın yalnızca kitabını değil kariyerini de geliştiren bir yayın ve eğitim merkezidir.</p>
            </header>
            <div class="akd-guven">
                <?php foreach ([
                    ['kitap', 'Editoryal, yayın, tanıtım ve kariyer süreçleri birlikte ele alınır.'],
                    ['kisi', 'Eğitimler genel sosyal medya anlatımı yerine yazarların ihtiyaçlarına göre hazırlanır.'],
                    ['dongu', 'Teorik bilgi, uygulama ve değerlendirme birlikte yürür.'],
                    ['dosya', 'Program sonunda kendi kullanabileceğiniz plan ve çalışma dosyalarına sahip olursunuz.'],
                    ['kisiler', 'Üst programlarda bireysel strateji görüşmeleri ve gelişim takibi bulunur.'],
                ] as $g) : ?>
                    <article class="uyg-ozellik"><span class="uyg-ozellik__ic"><?php echo $ik($g[0], 22); ?></span><p><?php echo esc_html($g[1]); ?></p></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ Kalite ve kapsam ============ -->
    <section class="uyg-bolum" id="kapsam">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('yildiz', 18); ?> Kalite standardı ve kapsam</span>
                <h2>Neyi vaat ettiğimizi ve etmediğimizi açıkça yazıyoruz.</h2>
            </header>
            <div class="akd-kapsam">
                <div class="akd-kapsam__kart">
                    <h3><?php echo $ik('yildiz', 20); ?> Program Tamamlama Belgesi</h3>
                    <p>En az <b>%80 devam</b> sağlayan ve görevlerin en az <b>%70’ini</b> tamamlayan katılımcılara <em>MST Yayıncılık Yazar Kariyer Akademisi Program Tamamlama Belgesi</em> verilir. Resmî veya üniversite onaylı sertifika değildir.</p>
                    <?php echo $liste(['Ders kayıtları program seviyesine göre sınırlı süreyle sunulur ve üçüncü kişilerle paylaşılamaz.', 'Grup içinde paylaşılan eser ve fikirlerin gizliliği korunur.', 'Bireysel görüşme süreleri ve adetleri her programda açıkça belirtilir.', 'Telafi dersi, iptal ve iade koşulları kayıt sözleşmesinde yer alır.']); ?>
                </div>
                <div class="akd-kapsam__kart akd-kapsam__kart--sinir">
                    <h3><?php echo $ik('carpi', 20); ?> Eğitimler garanti vermez</h3>
                    <ul class="akd-sinir">
                        <?php foreach (['Kitap satışı', 'Takipçi veya erişim', 'Basında yayın', 'Yayınevi tarafından kitap kabulü'] as $x) : ?><li><?php echo esc_html($x); ?> garantisi verilmez.</li><?php endforeach; ?>
                    </ul>
                    <p class="akd-kapsam__not">Sonuçlar katılımcının uygulama düzenine, eserine ve hedef kitlesine göre değişir.</p>
                    <h3 class="akd-kapsam__ara"><?php echo $ik('carpi', 20); ?> Fiyata dahil değildir</h3>
                    <ul class="akd-sinir">
                        <?php foreach (['Reklam bütçesi ve reklam yönetimi', 'Profesyonel video çekimi', 'Ücretli basın ve medya yayınları', 'Web sitesi yapımı (ayrıca fiyatlandırılır)', 'Kitap basımı ve yayın sözleşmesi (eğitim paketlerinden ayrıdır)'] as $x) : ?><li><?php echo esc_html($x); ?></li><?php endforeach; ?>
                    </ul>
                    <p class="akd-kapsam__not">Eğitim içerikleri ve çalışma dosyaları izinsiz çoğaltılamaz.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ SSS ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="sss">
        <div class="uyg-kap uyg-dar">
            <header class="uyg-baslik"><span class="uyg-ust"><?php echo $ik('soru', 18); ?> Sık sorulan sorular</span><h2>Aklınızdaki sorular</h2></header>
            <div class="uyg-sss">
                <?php foreach ($sss as $s) : ?>
                    <details><summary><?php echo esc_html($s[0]); ?></summary><p><?php echo esc_html($s[1]); ?></p></details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ İletişim ============ -->
    <section class="uyg-bolum uyg-bolum--koyu akd-iletisim" id="iletisim">
        <div class="uyg-kap akd-iletisim__in">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('soru', 18); ?> Sorularınız mı var?</span>
                <h2>Hangi programın size uygun olduğunu birlikte konuşalım.</h2>
                <p>Programlar, dönem tarihleri ve ücretsiz eğitim hakkında bilgi almak için WhatsApp'tan yazın ya da ücretsiz ön görüşme randevusu alın.</p>
            </header>
            <div class="akd-iletisim__butonlar">
                <?php if ($wa) : ?><a class="uyg-btn uyg-btn--altin" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo MST_Randevu::icon('wa'); ?> WhatsApp'tan bilgi alın</a><?php endif; ?>
                <a class="uyg-btn uyg-btn--cizgi" href="<?php echo esc_url($randevu); ?>">Ücretsiz ön görüşme randevusu</a>
            </div>
        </div>
    </section>

    <?php
    while (have_posts()) {
        the_post();
        $icerik = trim(get_the_content());
        if ($icerik !== '') echo '<section class="uyg-bolum"><div class="uyg-kap uyg-dar uyg-icerik">' . apply_filters('the_content', $icerik) . '</div></section>';
    }
    ?>
    <footer class="akd-alt">
        <div class="uyg-kap">
            <span>© <?php echo esc_html(wp_date('Y')); ?> MST Yayıncılık · Yazar Kariyer Akademisi</span>
            <a href="<?php echo esc_url(MST_Randevu::kvkk_url()); ?>">KVKK Aydınlatma Metni</a>
            <a href="<?php echo esc_url(home_url('/')); ?>">mstyayincilik.com</a>
        </div>
    </footer>
</main>

<?php wp_footer(); ?>
</body>
</html>
