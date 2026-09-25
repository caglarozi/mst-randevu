<?php
/**
 * "MST Yazar Kariyer Akademisi (Tam Sayfa)" şablonu: akademinin bilgi ve başvuru sayfası.
 * Renkler ve ortak bileşenler (üst çubuk, .uyg-* bölümleri) Yazar Paneli tanıtım sayfasıyla aynıdır;
 * akademiye özgü stiller assets/akademi.css'tedir. Bölümler kitap bölümü gibi numaralıdır (I–IX);
 * simge kutuları, kart ızgaraları ve parlama efektleri bilinçli olarak kullanılmaz.
 * Program sayfası sıralaması: fiyat her programın en sonunda gösterilir.
 */
if (!defined('ABSPATH')) {
    exit;
}

$wa      = MST_Randevu::wa_link('Merhaba, Yazar Kariyer Akademisi hakkında bilgi almak istiyorum.');
$akd_rnd = MST_Randevu::akademi_randevu_url(); // Akademi ön görüşme randevu sayfası (yoksa boş)
$randevu = $akd_rnd ?: MST_Randevu::randevu_url();

/**
 * "Bilgi alın" düğmesi: dokununca iki seçenek açılır: WhatsApp'tan yazmak ya da ön görüşme randevusu
 * almak (ilgilenilen program randevu formunda seçili gelir). Akademi randevu sayfası yoksa doğrudan WhatsApp.
 * JS olmadan da çalışır (<details>); telefonda alttan açılan panel olur.
 */
$secim = function ($etiket, $wa_metin, $program = '', $wa_ikon = false) use ($akd_rnd) {
    $wa = MST_Randevu::wa_link($wa_metin);
    if (!$akd_rnd) {
        return $wa ? '<a class="uyg-btn uyg-btn--altin" href="' . esc_url($wa) . '" target="_blank" rel="noopener">' . ($wa_ikon ? MST_Randevu::icon('wa') . ' ' : '') . esc_html($etiket) . '</a>' : '';
    }
    $rnd = $program ? add_query_arg('program', $program, $akd_rnd) : $akd_rnd;
    $h  = '<details class="akd-secim"><summary class="uyg-btn uyg-btn--altin">' . esc_html($etiket) . '</summary>';
    $h .= '<div class="akd-secim__panel"><p class="akd-secim__baslik">Nasıl bilgi almak istersiniz?</p>';
    if ($wa) $h .= '<a class="akd-secim__secenek" href="' . esc_url($wa) . '" target="_blank" rel="noopener">' . MST_Randevu::icon('wa') . '<span><strong>WhatsApp’tan yazın</strong><small>Sorularınızı yazılı iletin</small></span></a>';
    $h .= '<a class="akd-secim__secenek" href="' . esc_url($rnd) . '">' . MST_Randevu::icon('saat') . '<span><strong>Ön görüşme randevusu alın</strong><small>Size uygun saatte sizi arayalım</small></span></a>';
    return $h . '</div></details>';
};
$donem  = MST_Akademi::donem();

/** Sade madde listesi (kısa çizgili). */
$liste = function (array $maddeler, $sinif = '') {
    $h = '<ul class="akd-liste ' . esc_attr($sinif) . '">';
    foreach ($maddeler as $m) $h .= '<li>' . esc_html($m) . '</li>';
    return $h . '</ul>';
};
/** Bölüm başlığı: solda bölüm numarası ve adı, sağda başlık ve açıklama (kitap bölümü gibi). */
$bas = function ($no, $ad, $baslik, $aciklama = '') {
    return '<header class="akd-bas"><p class="akd-bas__no"><span>' . esc_html($no) . '</span>' . esc_html($ad) . '</p><div><h2>' . esc_html($baslik) . '</h2>'
        . ($aciklama !== '' ? '<p class="akd-bas__acik">' . $aciklama . '</p>' : '') . '</div></header>';
};

$bolumler = [
    ['#sorun', 'Neden akademi?'], ['#yaklasim', 'Eğitim yaklaşımı'], ['#programlar', 'Programlar'], ['#karsilastirma', 'Karşılaştırma'],
    ['#ucretsiz', 'Ücretsiz eğitim'], ['#guven', 'Neden MST'], ['#kapsam', 'Kapsam'], ['#sss', 'SSS'],
];

$dongu = [
    ['Analiz', 'Katılımcının aşaması, hedefi ve ihtiyacı belirlenir.'],
    ['Eğitim', 'Sabit müfredatla canlı ve çevrim içi dersler yapılır.'],
    ['Uygulama', 'Her dersin sonunda bir uygulama görevi verilir.'],
    ['Değerlendirme', 'Çalışmalar program seviyesine göre değerlendirilir.'],
    ['Yeni plan', 'Dönem sonunda bir sonraki adımın planı çıkarılır.'],
];

$kurallar = [
    'Programlar sabit müfredatla yürür; ders konuları katılımcıya göre değişmez.',
    'Programlar dönem sistemiyle açılır; dönem ortasında katılımcı alınmaz.',
    'Her dersin bir uygulama görevi vardır.',
    'Dersler canlı ve çevrim içi yapılır.',
    'Ders kayıtlarına erişim süresi program seviyesine göre belirlenir.',
    'Geri bildirim kapsamı program seviyesine göre genişler.',
    'Üst programlarda kontenjan azalır, kişisel takip artar.',
    'Her katılımcı program boyunca kendi çalışma dosyalarını oluşturur.',
];

$alanlar = [
    'Yazar kimliği ve konumlandırma', 'Hedef okur ve okur psikolojisi', 'Yazma disiplini ve eser planlama',
    'Dosya sunumu, özet ve tanıtım metni', 'Kişisel marka ve profil mimarisi', 'İçerik stratejisi ve yayın takvimi',
    'Kısa video, kamera ve anlatım', 'Yapay zekâ ile içerik üretimi', 'Kitap lansmanı ve kampanya planı',
    'PR, medya ve röportaj hazırlığı', 'Topluluk, etkinlik ve okur bağı', 'Satış kanalları ve performans takibi',
];

$farklar = [
    ['Yayıncılığın içinden', 'Editoryal, yayın, tanıtım ve kariyer süreçleri tek bir ekip tarafından birlikte ele alınır.'],
    ['Yazara özel içerik', 'Eğitimler genel sosyal medya anlatımı yerine yazarların ihtiyaçlarına göre hazırlanır.'],
    ['Uygulama ve değerlendirme', 'Her konu uygulama göreviyle pekiştirilir ve program seviyesine göre değerlendirilir.'],
    ['Somut çıktılar', 'Program sonunda kullanabileceğiniz plan ve çalışma dosyaları elinizde olur.'],
    ['Kişisel takip', 'Üst programlarda bireysel strateji görüşmeleri ve gelişim takibi yer alır.'],
];

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
        'fiyat_sayi' => '7470', 'sure_iso' => 'P12W',
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
        'fiyat_sayi' => '24900', 'sure_iso' => 'P12W',
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
        'fiyat_sayi' => '59900', 'sure_iso' => 'P16W',
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

$sss = [
    ['Programa katılmak için kitabımın yayımlanmış olması gerekir mi?', 'Hayır. Temel Program, yazma aşamasında olan veya dosyasını yeni tamamlayan kişiler için hazırlanmıştır. Diğer programlar dosyası hazır veya kitabı yayımlanmış yazarlara yöneliktir.'],
    ['Dersleri kendim seçebilir miyim?', 'Hayır. Her program, belirli bir yeterlilik seviyesine göre hazırlanmış sabit müfredata sahiptir.'],
    ['Programa dönem ortasında katılabilir miyim?', 'Hayır. Dersler birbirinin devamı olduğu için kayıtlar dönem başlangıcında alınır.'],
    ['Dersler canlı mı?', 'Evet. Eğitimler çevrim içi ve canlı olarak gerçekleştirilir.'],
    ['Ders kayıtlarına erişebilir miyim?', 'Evet. Temel Program’da 90 gün, Marka ve Görünürlük Programı’nda 180 gün, Kariyer Mentorluk Programı’nda 12 ay kayıt erişimi bulunur.'],
    ['Bireysel görüşme var mı?', 'Temel Program’da bireysel görüşme bulunmaz. Marka ve Görünürlük Programı’nda 3, Kariyer Mentorluk Programı’nda 8 bireysel görüşme bulunur.'],
    ['Program sonunda sertifika veriliyor mu?', 'Devam ve görev tamamlama koşullarını sağlayan katılımcılara MST Yayıncılık Yazar Kariyer Akademisi Program Tamamlama Belgesi verilebilir. Bu belge resmî veya üniversite onaylı bir sertifika değildir.'],
    ['Eğitim sonunda kitabımın satışı artar mı?', 'Programlar satış adedine ilişkin taahhüt içermez. Amaç, katılımcının hedef okurunu, içeriğini, tanıtımını ve kariyer planını düzenli biçimde yönetmesidir.'],
    ['Reklam bütçesi programa dahil mi?', 'Hayır. Reklam bütçesi, reklam yönetimi ve prodüksiyon hizmetleri ayrıca planlanır.'],
    ['Program ücretine kitap basımı dahil mi?', 'Hayır. Akademi programları ile yayın paketleri birbirinden ayrı hizmetlerdir.'],
];

MST_Randevu::seo_hazirla('akademi'); // arama başlığı/açıklaması (wp_head'den önce)
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#161616">
    <!-- Renkler tasarımın parçası: telefonun "zorla karanlık mod"u sayfayı ters çevirmesin -->
    <meta name="color-scheme" content="only light">
    <?php echo MST_Randevu::paylasim_meta('akademi'); ?>
    <?php echo MST_Randevu::seo_akademi($programlar, $sss); ?>
    <script>document.documentElement.classList.add('uyg-js');</script>
    <?php wp_head(); ?>
    <?php
    foreach (['mst-randevu' => 'randevu.css', 'mst-uygulama' => 'uygulama.css', 'mst-akademi' => 'akademi.css'] as $h => $dosya) {
        if (!wp_style_is($h, 'done')) {
            echo '<link rel="stylesheet" id="' . esc_attr($h) . '-yedek-css" href="' . esc_url(MST_Randevu::varlik($dosya)) . '">' . "\n";
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
            <p class="akd-sahne__yaz" data-akd-daktilo>MST Yayıncılık</p>
            <h1 class="akd-sahne__baslik">Yazar <em>Akademisi</em></h1>
            <p class="akd-sahne__alt">Yazma aşamasından profesyonel yazar markasına kadar üç seviyede, uygulama ve takip temelli eğitim programları: yazar kimliği, hedef okur, içerik üretimi, kitap lansmanı, PR ve kariyer planlaması.</p>

            <div class="akd-sahne__cta">
                <a class="uyg-btn uyg-btn--altin" href="#programlar">Programları inceleyin</a>
                <a class="akd-bilet-btn" href="#ucretsiz"><span class="akd-bilet-btn__kocan">Ücretsiz</span><span class="akd-bilet-btn__metin">Başlangıç eğitimi</span></a>
            </div>
        </div>
    </section>

    <nav class="akd-gezinti" aria-label="Sayfa bölümleri">
        <div class="uyg-kap">
            <?php foreach ($bolumler as $b) : ?><a href="<?php echo esc_attr($b[0]); ?>" data-akd-gez="<?php echo esc_attr(substr($b[0], 1)); ?>"><?php echo esc_html($b[1]); ?></a><?php endforeach; ?>
        </div>
    </nav>

    <!-- ============ I. Neden akademi ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="sorun">
        <div class="uyg-kap">
            <?php echo $bas('I', 'Neden akademi', 'İyi bir kitap, doğru okura ulaşmadığında potansiyelinin altında kalır.'); ?>
            <div class="akd-ikili">
                <div>
                    <p class="akd-paragraf">Kitabını tamamlayan yazarların önemli bir kısmı yayın sonrasında hangi adımları, hangi sırayla atacağını bilemez. Sosyal medya, video, yapay zekâ, lansman, PR, okur topluluğu ve satış kanalları çoğu zaman birbirinden kopuk yürütülür.</p>
                    <p class="akd-paragraf">Yazar Kariyer Akademisi bu alanları belirli bir sırayla ele alır. Katılımcı konu seçmez; her program, kendi yeterlilik seviyesine göre hazırlanmış sabit bir müfredatla yürür.</p>
                </div>
                <div>
                    <p class="akd-kucuk-baslik">Müfredat sırası</p>
                    <ol class="akd-sira">
                        <?php foreach (['Kimlik ve hedef okur', 'Marka ve profil', 'İçerik sistemi', 'Video ve yapay zekâ', 'Lansman ve PR', 'Topluluk ve satış'] as $c) : ?><li><?php echo esc_html($c); ?></li><?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ II. Eğitim yaklaşımı ============ -->
    <section class="uyg-bolum uyg-bolum--koyu" id="yaklasim">
        <div class="uyg-kap">
            <?php echo $bas('II', 'Eğitim yaklaşımı', 'Her program aynı beş adımlık döngüyle ilerler.', 'Dersler izlenip bırakılmaz; her konu uygulanır, değerlendirilir ve bir sonraki adıma bağlanır.'); ?>
            <ol class="akd-adimlar">
                <?php foreach ($dongu as $i => $d) : ?><li><span><?php echo $i + 1; ?></span><strong><?php echo esc_html($d[0]); ?></strong><p><?php echo esc_html($d[1]); ?></p></li><?php endforeach; ?>
            </ol>
            <p class="akd-kucuk-baslik">Programların ortak kuralları</p>
            <ol class="akd-kurallar">
                <?php foreach ($kurallar as $k) : ?><li><?php echo esc_html($k); ?></li><?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ III. 12 konu alanı ============ -->
    <section class="uyg-bolum" id="alanlar">
        <div class="uyg-kap">
            <?php echo $bas('III', 'Eğitim alanları', 'Kariyer eğitimlerinin 12 temel alanı', 'İçerikler katılımcının seçimine bırakılmaz; ilgili programın müfredatı içinde sırasıyla işlenir.'); ?>
            <ol class="akd-icindekiler">
                <?php foreach ($alanlar as $i => $a) : ?><li><span><?php echo sprintf('%02d', $i + 1); ?></span><?php echo esc_html($a); ?></li><?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ IV. Programlar ============ -->
    <section class="uyg-bolum akd-lacivert" id="programlar">
        <div class="uyg-kap">
            <?php echo $bas('IV', 'Programlar', 'Seviyenize göre üç program', 'Üst programlarda kontenjan azalır, kişisel takip artar. ' . ($donem ? 'Sonraki dönem: <b>' . esc_html($donem) . '</b>.' : 'Kayıtlar dönem başlangıcında alınır.')); ?>
            <div class="akd-program-sekme" role="tablist">
                <?php foreach ($programlar as $k => $p) : ?>
                    <a href="#program-<?php echo esc_attr($k); ?>" role="tab"><span><?php echo esc_html($p['no']); ?></span><?php echo esc_html(MST_Akademi::PROGRAMLAR[$k][1]); ?></a>
                <?php endforeach; ?>
            </div>

            <?php foreach ($programlar as $k => $p) : ?>
                <article class="akd-program akd-program--<?php echo esc_attr($k); ?>" id="program-<?php echo esc_attr($k); ?>">
                    <header class="akd-program__bas">
                        <p class="akd-program__seviye">Program <?php echo esc_html($p['no']); ?> · <?php echo esc_html($p['seviye']); ?></p>
                        <h3><?php echo esc_html(MST_Akademi::PROGRAMLAR[$k][0]); ?></h3>
                        <p><?php echo esc_html($p['kimler']); ?></p>
                    </header>
                    <dl class="akd-olcu">
                        <?php foreach ($p['olcu'] as $o) : ?><div><dt><?php echo esc_html($o[0]); ?></dt><dd><?php echo esc_html($o[1]); ?></dd></div><?php endforeach; ?>
                    </dl>
                    <div class="akd-ikili akd-ikili--program">
                        <div><h4>Katılımcının durumu</h4><p class="akd-program__metin"><?php echo esc_html($p['sorun']); ?></p></div>
                        <div><h4>Programın hedefi</h4><p class="akd-program__metin"><?php echo esc_html($p['hedef']); ?></p></div>
                        <div><h4>Süre ve ders sistemi</h4><?php echo $liste($p['yapi']); ?></div>
                        <div><h4>Değerlendirme</h4><p class="akd-program__metin"><?php echo esc_html($p['degerlendirme']); ?></p></div>
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
                    <div class="akd-program__alt">
                        <div class="akd-fiyat"><small>Program bedeli</small><strong><?php echo esc_html($p['fiyat']); ?></strong><span><?php echo esc_html($p['fiyat_alt']); ?></span></div>
                        <p class="akd-program__not">Kayıtlar dönem başlangıcında alınır. Reklam bütçesi, prodüksiyon ve yayın hizmetleri program bedeline dahil değildir (<a href="#kapsam">hizmet kapsamı</a>).</p>
                        <?php echo $secim('Bu program hakkında bilgi alın', 'Merhaba, ' . MST_Akademi::PROGRAMLAR[$k][0] . ' hakkında bilgi almak istiyorum.', $k); ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ V. Karşılaştırma ============ -->
    <section class="uyg-bolum" id="karsilastirma">
        <div class="uyg-kap">
            <?php echo $bas('V', 'Karşılaştırma', 'Programlar yan yana'); ?>
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
            <div class="akd-kiyas" aria-hidden="true">
                <?php $sira = 0; foreach (MST_Akademi::PROGRAMLAR as $k => $p) : $sira++; ?>
                    <div class="akd-kiyas__kart akd-kiyas__kart--<?php echo esc_attr($k); ?>">
                        <strong class="akd-kiyas__ad"><span><?php echo esc_html($programlar[$k]['no']); ?></span> <?php echo esc_html($p[1]); ?></strong>
                        <dl>
                            <?php foreach ($karsilastirma as $sat) : ?><div><dt><?php echo esc_html($sat[0]); ?></dt><dd><?php echo esc_html($sat[$sira]); ?></dd></div><?php endforeach; ?>
                        </dl>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="akd-dipnot">Tüm fiyatlara KDV eklenir.</p>
        </div>
    </section>

    <!-- ============ VI. Ücretsiz eğitim ============ -->
    <section class="uyg-bolum uyg-bolum--koyu akd-ucretsiz" id="ucretsiz">
        <div class="uyg-kap">
            <?php echo $bas('VI', 'Ücretsiz eğitim', 'Yazarlık yolculuğunuzun hangi aşamasındasınız?', 'Mevcut durumunuzu değerlendirmenizi ve size uygun programı belirlemenizi sağlayan ücretsiz bir başlangıç eğitimidir.'); ?>
            <div class="akd-ikili">
                <div><h3>Eğitimin içeriği</h3><?php echo $liste(['Yazarlık kariyerinin temel aşamaları', 'Yazar kimliği ve hedef okur', 'Kitap yayımlanmadan önce ve sonra yapılması gerekenler', 'Sosyal medyada yazar görünürlüğü', 'Yapay zekânın temel kullanımı ve içerik üretimi', 'PR ve lansmana giriş', 'Programların kimler için uygun olduğu']); ?></div>
                <div>
                    <h3>Eğitimin sonunda</h3><?php echo $liste(['Soru-cevap', 'Yazar Kariyer Analizi formu', 'Aşamanıza göre program önerisi', 'Uygun katılımcılar için ön görüşme']); ?>
                    <p class="akd-ucretsiz__not">Katılım ücretsizdir. Tarih ve katılım bilgisi için bize yazın.</p>
                    <?php echo $secim('Ücretsiz eğitim hakkında bilgi alın', 'Merhaba, Yazar Kariyer Akademisi ücretsiz eğitimi hakkında bilgi almak istiyorum.', '', true); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ VII. Neden MST ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="guven">
        <div class="uyg-kap">
            <?php echo $bas('VII', 'Neden MST', 'Yayıncılığın içinden gelen bir eğitim sistemi', 'MST Yayıncılık, yazarların yayın sürecini ve yayın sonrası kariyerini birlikte yönetir.'); ?>
            <dl class="akd-farklar">
                <?php foreach ($farklar as $f) : ?><div><dt><?php echo esc_html($f[0]); ?></dt><dd><?php echo esc_html($f[1]); ?></dd></div><?php endforeach; ?>
            </dl>
        </div>
    </section>

    <!-- ============ VIII. Kalite ve kapsam ============ -->
    <section class="uyg-bolum" id="kapsam">
        <div class="uyg-kap">
            <?php echo $bas('VIII', 'Kalite standartları', 'Kalite Standartları ve Hizmet Kapsamı', 'Programlarımızın koşulları, kazanımları ve kapsam dışında kalan hizmetler aşağıda tanımlanmıştır.'); ?>
            <div class="akd-ikili akd-kapsam">
                <div>
                    <h3>Program Tamamlama Belgesi</h3>
                    <p class="akd-program__metin">En az <b>%80 devam</b> ve görevlerin en az <b>%70’ini tamamlama</b> şartını sağlayan katılımcılara MST Yayıncılık Yazar Kariyer Akademisi Program Tamamlama Belgesi düzenlenir. Belge, resmî veya üniversite onaylı sertifika niteliği taşımaz.</p>
                    <?php echo $liste(['Ders kayıtları: Program seviyesine göre belirlenen süre boyunca erişime açıktır; üçüncü kişilerle paylaşılamaz.', 'Gizlilik: Grup çalışmalarında paylaşılan eser ve fikirler gizli tutulur.', 'Bireysel görüşmeler: Süre ve adetler her programda ayrıca belirtilir.', 'Telafi, iptal ve iade: Koşullar kayıt sözleşmesinde düzenlenir.']); ?>
                </div>
                <div>
                    <h3>Sonuç Taahhüdü</h3>
                    <p class="akd-program__metin">Programlar aşağıdaki sonuçlara ilişkin taahhüt içermez; sonuçlar katılımcının uygulama düzenine, eserine ve hedef kitlesine göre farklılık gösterir.</p>
                    <?php echo $liste(['Kitap satış adedi', 'Takipçi sayısı ve erişim', 'Basın ve medyada yer alma', 'Yayınevi tarafından kabul']); ?>
                    <h3>Program Bedeline Dahil Olmayan Hizmetler</h3>
                    <?php echo $liste(['Reklam bütçesi ve reklam yönetimi', 'Profesyonel video prodüksiyonu', 'Ücretli basın ve medya yayınları', 'Web sitesi tasarımı ve yapımı', 'Kitap basımı ve yayın sözleşmesi']); ?>
                    <p class="akd-kapsam__not">Eğitim içerikleri ve çalışma dosyaları MST Yayıncılık’ın izni olmadan çoğaltılamaz ve paylaşılamaz.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ IX. SSS ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="sss">
        <div class="uyg-kap">
            <?php echo $bas('IX', 'Sık sorulan sorular', 'Sık sorulan sorular'); ?>
            <div class="uyg-sss akd-sss">
                <?php foreach ($sss as $s) : ?>
                    <details><summary><?php echo esc_html($s[0]); ?></summary><p><?php echo esc_html($s[1]); ?></p></details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ İletişim ============ -->
    <section class="uyg-bolum uyg-bolum--koyu akd-iletisim" id="iletisim">
        <div class="uyg-kap akd-iletisim__in">
            <h2>Hangi programın size uygun olduğunu birlikte belirleyelim.</h2>
            <p>Programlar, dönem tarihleri ve ücretsiz eğitim hakkında bilgi almak için WhatsApp’tan yazabilir ya da ücretsiz ön görüşme randevusu alabilirsiniz.</p>
            <div class="akd-iletisim__butonlar">
                <?php if ($wa) : ?><a class="uyg-btn uyg-btn--altin" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo MST_Randevu::icon('wa'); ?> WhatsApp’tan bilgi alın</a><?php endif; ?>
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
    <div class="akd-alt" role="contentinfo">
        <div class="uyg-kap akd-alt__in">
            <a class="akd-alt__marka" href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo esc_url(MST_Randevu::logo_url()); ?>" alt="" width="40" height="40" loading="lazy">
                <span><strong>MST Yayıncılık</strong><small>Yazar Kariyer Akademisi</small></span>
            </a>
            <nav class="akd-alt__linkler" aria-label="Alt menü">
                <a href="#programlar">Programlar</a>
                <a href="#ucretsiz">Ücretsiz eğitim</a>
                <a href="#sss">SSS</a>
                <a href="<?php echo esc_url(MST_Randevu::kvkk_url()); ?>">KVKK Aydınlatma Metni</a>
                <a href="<?php echo esc_url(home_url('/')); ?>">mstyayincilik.com</a>
            </nav>
            <p class="akd-alt__telif">© <?php echo esc_html(wp_date('Y')); ?> MST Yayıncılık. Tüm hakları saklıdır.</p>
        </div>
    </div>
</main>

<?php wp_footer(); ?>
</body>
</html>
