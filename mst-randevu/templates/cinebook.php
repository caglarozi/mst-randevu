<?php
/**
 * "MST CineBook ve MST Çocuk (Tam Sayfa)" şablonu.
 * Yapım şirketi sitesi düzeni: showreel girişi, CineBook nedir, afişlerden filmografi, stüdyo ve
 * kitapların filme yolculuğu, MST Çocuk, "Birlikte çalışalım" (başvuru formu).
 * Videolar, görseller, yapımlar ve sosyal medya Yazar Randevu → CineBook Ayarları'ndan gelir.
 * Boş alanlar ziyaretçiye yer tutucu olarak değil, "yakında" ya da hiç gösterilmeden yansır.
 */
if (!defined('ABSPATH')) {
    exit;
}

$o         = MST_CineBook::opts();
$fragman   = MST_CineBook::video_kodu($o['fragman_url']);
$cocuk_vd  = MST_CineBook::video_kodu($o['cocuk_url']);
$fr_yt     = MST_CineBook::youtube_mu($fragman) ? $fragman : ''; // arka planda yalnızca YouTube döner
$wa        = MST_Randevu::wa_link('Merhaba, CineBook hakkında bilgi almak istiyorum.');
$fr_ad     = $o['fragman_ad'] ?: 'Gökbörü';
$fr_etiket = $o['fragman_etiket'] ?: 'İlk bölüm';
$fr_gorsel = $o['fragman_gorsel'] ?: ($fr_yt ? 'https://i.ytimg.com/vi/' . $fr_yt . '/maxresdefault.jpg' : '');
$yonetici  = current_user_can('manage_options');
$yapimlar  = MST_CineBook::yapimlar();
$gruplar   = array_unique(array_column($yapimlar, 'tur'));

/** Tıklayınca yüklenen YouTube oynatıcısı (sayfa açılışında YouTube yüklenmez). Video yoksa "yakında" kartı. */
$oynatici = function ($id, $baslik, $etiket, $sinif = '', $kapak = '') use ($yonetici) {
    if (!$id) {
        return '<div class="cb-video cb-video--bos ' . esc_attr($sinif) . '"><span class="cb-video__oynat" aria-hidden="true"></span><p><strong>' . esc_html($baslik) . '</strong><small>Yakında burada</small>'
            . ($yonetici ? '<small class="cb-yonetici">Yalnızca yöneticiler görür: video adresini CineBook Ayarları’ndan ekleyin.</small>' : '') . '</p></div>';
    }
    if (!MST_CineBook::youtube_mu($id)) { // Instagram: kapak alınamaz, oynatıcı penceresinde açılır
        return '<button type="button" class="cb-video cb-video--ig ' . esc_attr($sinif) . '" data-cb-video-ac="' . esc_attr($id) . '" data-cb-baslik="' . esc_attr($baslik) . '" aria-label="' . esc_attr($baslik . ' videosunu oynat') . '"' . ($kapak ? ' style="background-image:url(' . esc_url($kapak) . ')"' : '') . '>'
            . '<span class="cb-video__oynat" aria-hidden="true"></span><span class="cb-video__etiket">' . esc_html($etiket) . '</span></button>';
    }
    $kapak = $kapak ?: 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg';
    return '<button type="button" class="cb-video ' . esc_attr($sinif) . '" data-cb-video="' . esc_attr($id) . '" aria-label="' . esc_attr($baslik . ' videosunu oynat') . '"'
        . ' style="background-image:url(' . esc_url($kapak) . ')">'
        . '<span class="cb-video__oynat" aria-hidden="true"></span><span class="cb-video__etiket">' . esc_html($etiket) . '</span></button>';
};

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
        'film'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 4v16M17 4v16M3 8h4M3 12h4M3 16h4M17 8h4M17 12h4M17 16h4"/>',
        'oynat'    => '<circle cx="12" cy="12" r="9"/><path d="m10 8 6 4-6 4z"/>',
        'klaket'   => '<path d="M4 11h16v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/><path d="m4 11 1.5-5 15 3L20 11M8.5 6.6l2 3.9M13.5 7.6l2 3.9"/>',
        'carpi'    => '<path d="M6 6l12 12M18 6 6 18"/>',
        'soru'     => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.7M12 17h.01"/>',
        'wa'       => '',
    ];
    return '<svg class="uyg-ic" width="' . (int) $boy . '" height="' . (int) $boy . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($d[$n] ?? '') . '</svg>';
};
$kunye = [
    ['4+', 'Sosyal medya platformu'],
    ['Yeni nesil', 'Dijital hikâye anlatımı'],
    ['Filmleşen', 'Kitap ve fragman projeleri'],
];

$nedir = [
    ['Dijital anlatı platformu', 'CineBook, kitapları yalnızca tanıtan değil, onları sahnelere ve görsel deneyimlere dönüştüren özel bir yapı sunar.'],
    ['Yazar, okur, izleyici', 'Aynı hikâyeyi üç farklı kitleye ulaştırır. Okuyan için eser, izleyen için sahne, yazar için vitrin oluşturur.'],
    ['Sosyal medya odaklı büyüme', 'Üretilen fragmanlar ve sahneler YouTube, Instagram, TikTok ve Facebook gibi mecralara uygun biçimde hazırlanır. Böylece hikâye rafta kalmaz, ekranda da yaşamaya devam eder.'],
];

$yetenekler = ['Senaryo uyarlaması', 'Storyboard', 'Karakter ve görsel tasarım', 'Kurgu', 'Seslendirme', 'Müzik ve ses tasarımı', 'Animasyon', 'Dikey video', 'Dijital yayın'];

$yolculuk = [
    ['Eser seçimi', 'Sinematik potansiyele sahip kitaplar seçilir ve projelendirilir.'],
    ['Senaryo uyarlaması', 'Hikâye sahnelere ayrılır, ritim ve görsel dünya kurulmaya başlanır.'],
    ['Fragman üretimi', 'Teaser, kısa sahne ve tanıtım videoları çok kanallı yayın için hazırlanır.'],
    ['Dijital yayın', 'İçerik sosyal medya platformlarında izleyiciyle buluşur ve büyütülür.'],
];

$cocuk_surec = [
    ['Karakter tasarımı', 'Kitaptaki çizimlere sadık kalınarak karakterler hareket edecek biçimde yeniden çizilir.'],
    ['Animasyon', 'Sahneler kitabın akışına göre canlandırılır.'],
    ['Seslendirme ve müzik', 'Çocuklara uygun seslendirme, müzik ve ses efektleri eklenir.'],
    ['Yayın', 'Çizgi film MST Çocuk kanallarında ve kitabın tanıtımında kullanılır.'],
];

$cocuk_kim = [
    ['Çocuk kitabı yazarları ve çizerleri', 'Kitabınızın karakterleri ekranda canlanır; kitap okurlarına yeni bir yoldan ulaşır.'],
    ['Aileler', 'Çocuklar sevdikleri hikâyeyi önce izler, sonra kitabını açıp okur.'],
    ['Öğretmenler ve okullar', 'Sınıfta okuma etkinliklerine görsel bir başlangıç olur.'],
];

$sosyal = array_filter([
    ['youtube', 'YouTube', 'Fragmanlar ve tam bölümler'],
    ['instagram', 'Instagram', 'Kısa sahneler ve kamera arkası'],
    ['tiktok', 'TikTok', 'Dikey kısa videolar'],
    ['facebook', 'Facebook', 'Duyurular ve topluluk'],
], function ($s) use ($o) { return !empty($o[$s[0]]); });

$sss = [
    ['Kitabımın yayımlanmış olması gerekir mi?', 'Başvuru sırasında kitabınızın yayımlanmış ya da yayına hazır olması değerlendirmeyi kolaylaştırır. Durumunuzu formdaki kısa özet alanına yazabilirsiniz.'],
    ['MST Yayıncılık dışında yayımlanmış kitaplar başvurabilir mi?', 'Evet, başvurabilir. Her başvuru hikâyesine ve uyarlama potansiyeline göre ayrı değerlendirilir.'],
    ['Fragman ya da çizgi film ne kadar sürede hazırlanır?', 'Süre; eserin uzunluğuna, sahne sayısına ve seslendirme ihtiyacına göre değişir. Değerlendirme sonrası size bir zaman planı sunulur.'],
    ['Hangi çocuk kitapları çizgi filme uygundur?', 'Resimli çocuk kitapları, masallar ve kısa öyküler en uygun eserlerdir. Kitabın çizimleri karakter tasarımının temelini oluşturur.'],
    ['Başvurudan sonra ne olur?', 'Başvurunuz incelenir ve ekibimiz sizi arar. Uygun bulunan eserler için uyarlama planı birlikte hazırlanır.'],
];

MST_Randevu::seo_hazirla('cinebook'); // arama başlığı/açıklaması (wp_head'den önce)
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0c0c0d">
    <meta name="color-scheme" content="only light">
    <?php echo MST_Randevu::seo_aciklama('cinebook'); ?>
    <?php echo MST_Randevu::paylasim_meta('cinebook'); ?>
    <script>document.documentElement.classList.add('uyg-js');</script>
    <?php wp_head(); ?>
    <?php
    foreach (['mst-randevu' => 'randevu.css', 'mst-uygulama' => 'uygulama.css', 'mst-akademi' => 'akademi.css', 'mst-cinebook' => 'cinebook.css'] as $h => $dosya) {
        if (!wp_style_is($h, 'done')) {
            echo '<link rel="stylesheet" id="' . esc_attr($h) . '-yedek-css" href="' . esc_url(MST_Randevu::varlik($dosya)) . '">' . "\n";
        }
    }
    ?>
</head>
<body <?php body_class('mst-sayfa mst-uyg mst-akd mst-cb'); ?>>
<?php wp_body_open(); ?>
<?php echo MST_Randevu::header_html(false, ['Birlikte Çalışalım', '#iletisim'], 'Merhaba, CineBook hakkında bilgi almak istiyorum.'); ?>

<main class="uyg cb" lang="tr">

    <!-- ============ Perde: kamera vizörü ============
         Üstte ve altta sinema bantları (letterbox), vizör köşeleri, akan zaman kodu, projektör ışığı ve gren.
         Fragman adresi varsa arka planda sessiz döner (hareketi azalt açıksa dönmez); fragman görseli
         video yüklenene kadar ve video yoksa arka plan olur. "Şimdi izle" aynı fragmanı sesli açar. -->
    <section class="cb-reel<?php echo $fr_gorsel ? ' cb-reel--gorselli' : ''; ?>" id="showreel" <?php echo $fr_yt ? 'data-cb-reel="' . esc_attr($fr_yt) . '"' : ''; ?>>
        <div class="cb-reel__arka" aria-hidden="true">
            <?php if ($fr_gorsel) : ?><img src="<?php echo esc_url($fr_gorsel); ?>" alt="" fetchpriority="high" decoding="async"><?php endif; ?>
        </div>
        <div class="cb-gren" aria-hidden="true"></div>

        <nav class="cb-bant cb-bant--ust" aria-label="Sayfa bölümleri">
            <span class="cb-bant__marka" lang="en">CineBook</span>
            <span class="cb-bant__linkler"><a href="#nedir">Hakkında</a><a href="#yapimlar">Vizyonda</a><a href="#studyo">Stüdyo</a><a href="#cocuk">MST Çocuk</a><a href="#iletisim">İletişim</a></span>
        </nav>

        <div class="cb-vizor">
            <span class="cb-vizor__k cb-vizor__k--1"></span><span class="cb-vizor__k cb-vizor__k--2"></span><span class="cb-vizor__k cb-vizor__k--3"></span><span class="cb-vizor__k cb-vizor__k--4"></span>
            <p class="cb-vizor__ust" aria-hidden="true"><span><i></i>REC</span><span data-cb-zaman>00:00:00:00</span><span>SAHNE 01 · ÇEKİM 01</span></p>

            <div class="cb-reel__in">
                <p class="cb-reel__ust">Yeni bir çağ</p>
                <h1 class="cb-reel__baslik"><span lang="en">CineBook</span></h1>
                <p class="cb-reel__soz">Hikâyeleri sadece yayımlamıyoruz, <em>ekrana taşıyoruz.</em></p>
                <p class="cb-reel__alt"><span lang="en">CineBook</span>, MST Yayıncılık bünyesinde kitapları fragmanlara, kısa sahnelere ve dijital film projelerine dönüştüren yeni nesil bir hikâye platformudur.</p>
                <div class="cb-reel__cta">
                    <?php if ($fragman) : ?>
                        <button type="button" class="cb-oynat-btn" data-cb-video-ac="<?php echo esc_attr($fragman); ?>" data-cb-baslik="<?php echo esc_attr($fr_ad . ' · ' . $fr_etiket); ?>">
                            <i aria-hidden="true"></i><span><strong>Şimdi izle</strong><small><?php echo esc_html($fr_ad . ' • ' . $fr_etiket); ?></small></span>
                        </button>
                    <?php else : ?>
                        <a class="cb-oynat-btn" href="#yapimlar"><i aria-hidden="true"></i><span><strong>Vizyonda</strong><small><?php echo esc_html($fr_ad . ' • ' . $fr_etiket); ?> yakında</small></span></a>
                    <?php endif; ?>
                    <a class="cb-cizgi-btn" href="#iletisim">Birlikte çalışalım</a>
                </div>
            </div>

            <dl class="cb-kunye">
                <?php foreach ($kunye as $k) : ?><div><dt><?php echo esc_html($k[0]); ?></dt><dd><?php echo esc_html($k[1]); ?></dd></div><?php endforeach; ?>
            </dl>
        </div>

        <p class="cb-bant cb-bant--alt cb-jenerik-blok">
            <span><small>MST Yayıncılık</small> sunar</span>
            <span><small>bir</small> <bdi lang="en">CineBook</bdi> <small>yapımı</small></span>
            <span><small>yazarların</small> kitaplarından <small>uyarlanmıştır</small></span>
            <span><small>senaryo</small> uyarlama <small>kurgu</small> ses <small>müzik</small> animasyon</span>
        </p>
    </section>

    <dialog class="cb-perdelik" id="cb-perdelik" aria-label="Video">
        <button type="button" class="cb-perdelik__kapat" data-cb-kapat aria-label="Kapat">×</button>
        <div class="cb-perdelik__yer"></div>
    </dialog>

    <!-- ============ CineBook nedir? 35 mm film şeridi ============ -->
    <section class="cb-bolum cb-nedir" id="nedir">
        <div class="uyg-kap">
            <p class="cb-etiket"><span lang="en">CineBook</span> nedir?</p>
            <h2 class="cb-nedir__soz">Kitabın <em>görünür hâle gelen</em> versiyonu.</h2>
        </div>
        <div class="cb-serit" tabindex="0" aria-label="CineBook nedir: üç kare">
            <ol class="cb-serit__kareler">
                <?php foreach ($nedir as $i => $n) : ?>
                    <li class="cb-kare">
                        <span class="cb-kare__kod" aria-hidden="true"><bdi lang="en">CINEBOOK</bdi> 35 ▸ <?php echo (int) $i + 1; ?>A</span>
                        <span class="cb-kare__no"><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span>
                        <h3><?php echo esc_html($n[0]); ?></h3>
                        <p><?php echo esc_html($n[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ Vizyonda: yapımlar ============ -->
    <section class="cb-bolum cb-bolum--yapim" id="yapimlar">
        <div class="uyg-kap">
            <header class="cb-tabela-bas">
                <div class="cb-tabela" aria-hidden="true"><span>Vizyonda</span></div>
                <h2 class="cb-gizli">Yapımlar</h2>
                <p class="cb-bas__metin">Kitaplardan uyarlanan fragmanlar, kısa sahneler ve çizgi filmler. Her yapım, eserin kendi dünyasından doğar.</p>
                <?php if (count($gruplar) > 1) : ?>
                    <div class="cb-suzgec" role="group" aria-label="Yapımları süz">
                        <button type="button" class="is-secili" aria-pressed="true" data-cb-suz="hepsi">Tümü</button>
                        <button type="button" aria-pressed="false" data-cb-suz="cinebook"><span lang="en">CineBook</span></button>
                        <button type="button" aria-pressed="false" data-cb-suz="cocuk">MST Çocuk</button>
                    </div>
                <?php endif; ?>
            </header>
            <ul class="cb-filmografi">
                <?php foreach ($yapimlar as $y) :
                    $afis  = $y['afis'] ?: (MST_CineBook::youtube_mu($y['video']) ? 'https://i.ytimg.com/vi/' . $y['video'] . '/hqdefault.jpg' : '');
                    $durum = $y['video'] ? 'Vizyonda' : 'Yapımda';
                    $etk   = $y['video'] ? 'button type="button" data-cb-video-ac="' . esc_attr($y['video']) . '" data-cb-baslik="' . esc_attr($y['ad'] . ' · ' . $y['etiket']) . '" aria-label="' . esc_attr($y['ad'] . ' videosunu izle') . '"' : 'div';
                    ?>
                    <li data-cb-grup="<?php echo esc_attr($y['tur']); ?>">
                        <<?php echo $etk; // öznitelikler yukarıda kaçırıldı ?> class="cb-afis<?php echo $y['afis'] ? ' cb-afis--gorsel' : ($afis ? ' cb-afis--kapak' : ' cb-afis--yazi'); ?>">
                            <?php if ($afis) : ?><img src="<?php echo esc_url($afis); ?>" alt="" loading="lazy" decoding="async"><?php endif; ?>
                            <?php if (!$y['afis']) : ?>
                                <span class="cb-afis__yazi">
                                    <small><?php echo $y['tur'] === 'cocuk' ? 'MST Çocuk' : '<bdi lang="en">CineBook</bdi>'; ?> sunar</small>
                                    <strong><?php echo esc_html($y['ad']); ?></strong>
                                    <em><?php echo esc_html($y['etiket']); ?></em>
                                    <span class="cb-afis__jenerik">MST Yayıncılık · <?php echo $y['tur'] === 'cocuk' ? 'MST Çocuk' : '<bdi lang="en">CineBook</bdi>'; ?> · <?php echo esc_html($y['yil'] ?: wp_date('Y')); ?></span>
                                </span>
                            <?php endif; ?>
                            <?php if ($y['video']) : ?><span class="cb-afis__izle"><i aria-hidden="true"></i>İzle</span><?php endif; ?>
                        </<?php echo $y['video'] ? 'button' : 'div'; ?>>
                        <h3><?php echo esc_html($y['ad']); ?></h3>
                        <p><?php echo esc_html($y['etiket']); ?><?php echo $y['yil'] ? ' · ' . esc_html($y['yil']) : ''; ?> · <span class="cb-durum<?php echo $y['video'] ? '' : ' is-yapimda'; ?>"><?php echo esc_html($durum); ?></span></p>
                    </li>
                <?php endforeach; ?>
                <li data-cb-grup="hepsi" class="cb-filmografi__siradaki">
                    <a class="cb-bilet" href="#iletisim">
                        <span class="cb-bilet__ust"><small>Bilet</small><small>No 00<?php echo count($yapimlar) + 1; ?></small></span>
                        <span class="cb-bilet__orta"><small>Sıradaki yapım</small><strong>Sizin kitabınız</strong><em>Fragman ya da çizgi film</em></span>
                        <span class="cb-bilet__koc">Başvurun <i aria-hidden="true">→</i></span>
                    </a>
                    <h3>Sizin kitabınız</h3>
                    <p>Fragman ya da çizgi film · <span class="cb-durum">Başvuruya açık</span></p>
                </li>
            </ul>
        </div>
    </section>

    <!-- ============ Stüdyo: jenerik ve klaketler ============ -->
    <section class="cb-bolum cb-bolum--koyu" id="studyo">
        <div class="uyg-kap">
            <div class="cb-studyo">
                <div>
                    <p class="cb-etiket">Stüdyo</p>
                    <h2 class="cb-studyo__soz">Kitabı ekrana taşıyan ekip, <em>kitabı yayına hazırlayan</em> ekiptir.</h2>
                    <p class="cb-studyo__metin"><span lang="en">CineBook</span>, MST Yayıncılık’ın yapım stüdyosudur. Eserin hikâyesini, karakterlerini ve atmosferini fragmana, kısa sahnelere ve sosyal medya videolarına uyarlar; çocuk kitaplarını MST Çocuk etiketiyle çizgi filme dönüştürür.</p>
                </div>
                <div class="cb-jenerik" aria-label="Neler yapıyoruz">
                    <div class="cb-jenerik__pencere">
                        <ul class="cb-jenerik__akis">
                            <li class="cb-jenerik__bas"><small>Neler yapıyoruz</small></li>
                            <?php foreach ($yetenekler as $y) : ?><li><?php echo esc_html($y); ?></li><?php endforeach; ?>
                            <li class="cb-jenerik__son"><small>Bir</small> <bdi lang="en">CineBook</bdi> <small>yapımı</small></li>
                        </ul>
                    </div>
                </div>
            </div>

            <h3 class="cb-yolculuk__baslik">Kitapların <em>filme</em> yolculuğu</h3>
            <ol class="cb-klaketler">
                <?php foreach ($yolculuk as $i => $s) : ?>
                    <li class="cb-klaket">
                        <span class="cb-klaket__cubuk" aria-hidden="true"></span>
                        <div class="cb-klaket__govde">
                            <p class="cb-klaket__satir" aria-hidden="true"><span><small>Sahne</small><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span><span><small>Çekim</small>1</span><span><small>Yapım</small><bdi lang="en">CineBook</bdi></span></p>
                            <h4><?php echo esc_html($s[0]); ?></h4>
                            <p><?php echo esc_html($s[1]); ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ MST Çocuk ============ -->
    <section class="cb-cocuk" id="cocuk">
        <svg class="cb-dalga" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0,0 H1440 V38 C1260,82 1080,82 900,52 C720,22 540,22 360,52 C220,76 100,70 0,44 Z" fill="#151517"/></svg>
        <div class="uyg-kap">
            <div class="cb-cocuk__giris">
                <div>
                    <p class="cb-cocuk__rozet">MST Çocuk</p>
                    <h2>Çocuk kitapları <span>çizgi filme</span> dönüşüyor.</h2>
                    <p class="cb-cocuk__alt">Resimli çocuk kitaplarını, karakterlerine ve çizimlerine sadık kalarak kısa çizgi filmlere uyarlıyoruz. Çocuklar sevdikleri hikâyeyi hem okuyor hem izliyor.</p>
                    <a class="cb-cocuk__btn" href="#iletisim" data-cb-tur="cocuk">Çocuk kitabınız için başvurun</a>
                </div>
                <div class="cb-cocuk__sahne">
                    <?php echo $oynatici($cocuk_vd, 'MST Çocuk’un ilk çizgi filmi', 'Çizgi filmi izle', 'cb-video--cocuk'); ?>
                </div>
            </div>

            <ol class="cb-cocuk__surec">
                <?php foreach ($cocuk_surec as $i => $s) : ?>
                    <li class="cb-renk-<?php echo (int) $i + 1; ?>"><span><?php echo (int) $i + 1; ?></span><h3><?php echo esc_html($s[0]); ?></h3><p><?php echo esc_html($s[1]); ?></p></li>
                <?php endforeach; ?>
            </ol>

            <div class="cb-cocuk__kim">
                <h3>Kimler için?</h3>
                <ul>
                    <?php foreach ($cocuk_kim as $k) : ?><li><strong><?php echo esc_html($k[0]); ?></strong><p><?php echo esc_html($k[1]); ?></p></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
        <svg class="cb-dalga cb-dalga--alt" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0,80 H1440 V42 C1300,10 1140,6 960,30 C780,54 600,62 420,40 C260,20 120,22 0,40 Z" fill="#151517"/></svg>
    </section>



    <!-- ============ Birlikte çalışalım ============ -->
    <section class="cb-bolum cb-bolum--koyu cb-iletisim" id="iletisim">
        <div class="uyg-kap">
            <p class="cb-iletisim__ust">Sıradaki sahne <em>sizin.</em></p>
            <h2 class="cb-iletisim__baslik">Birlikte çalışalım</h2>
            <div class="cb-iletisim__in">
                <div class="cb-iletisim__sol">
                    <p class="cb-iletisim__metin">Kitabınızın ekranda hayat bulmasını istiyorsanız eserinizi gönderin. Başvurunuzu inceleyip sizi arıyoruz; uygun eserler için uyarlama planını birlikte hazırlıyoruz.</p>
                    <?php if ($wa) : ?><a class="cb-wa" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo MST_Randevu::icon('wa'); ?> WhatsApp’tan yazın</a><?php endif; ?>
                </div>
            <form class="cb-form" data-cb-form novalidate>
                <p class="cb-form__koc" aria-hidden="true"><span>Başvuru bileti</span><span><bdi lang="en">CineBook</bdi> · MST Çocuk</span></p>
                <fieldset class="cb-form__tur">
                    <legend>Başvuru türü</legend>
                    <label><input type="radio" name="tur" value="cinebook" checked><span><strong>CineBook</strong><small>Kitaptan fragman / film</small></span></label>
                    <label><input type="radio" name="tur" value="cocuk"><span><strong>MST Çocuk</strong><small>Çocuk kitabından çizgi film</small></span></label>
                </fieldset>
                <label class="cb-alan"><span>Eser adı</span><input type="text" name="eser_adi" required maxlength="150" autocomplete="off"></label>
                <label class="cb-alan"><span>Ad soyad</span><input type="text" name="yazar_adi" required minlength="3" maxlength="100" autocomplete="name"></label>
                <div class="cb-form__ikili">
                    <label class="cb-alan"><span>Telefon</span><input type="tel" name="telefon" required inputmode="tel" autocomplete="tel" placeholder="05XX XXX XX XX"></label>
                    <label class="cb-alan"><span>E-posta <em>(isteğe bağlı)</em></span><input type="email" name="eposta" autocomplete="email" placeholder="ornek@eposta.com"></label>
                </div>
                <label class="cb-alan"><span>Eserinizi kısaca anlatın <em>(isteğe bağlı)</em></span><textarea name="ozet" rows="4" maxlength="1500" placeholder="Tür, konu, hedef okur yaşı, kitap yayımlandı mı…"></textarea></label>
                <label class="cb-hp" aria-hidden="true">Web sitesi <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                <label class="cb-onay"><input type="checkbox" name="kvkk" value="1" required><span><a href="<?php echo esc_url(MST_Randevu::kvkk_url()); ?>" target="_blank" rel="noopener">KVKK Aydınlatma Metni</a>’ni okudum. Kişisel verilerimin başvurumun değerlendirilmesi ve benimle iletişim kurulması amacıyla MST Yayıncılık tarafından işlenmesini kabul ediyorum.</span></label>
                <p class="cb-form__hata" data-cb-hata role="alert" hidden></p>
                <button type="submit" class="uyg-btn uyg-btn--altin cb-form__gonder">Başvuruyu Gönder</button>
                <div class="cb-form__tamam" data-cb-tamam hidden></div>
            </form>
                <div class="cb-iletisim__ek">
                    <?php if ($sosyal) : ?>
                        <p class="cb-etiket cb-etiket--ara" id="takip">Takip edin</p>
                        <ul class="cb-sosyal">
                            <?php foreach ($sosyal as $s) : $url = $o[$s[0]]; ?>
                                <li><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><strong><?php echo esc_html($s[1]); ?></strong><span><?php echo esc_html(MST_CineBook::hesap_adi($url)); ?></span><small><?php echo esc_html($s[2]); ?></small></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <div class="cb-sss">
                        <p class="cb-etiket cb-etiket--ara">Sık sorulanlar</p>
                        <?php foreach ($sss as $s) : ?>
                            <details><summary><?php echo esc_html($s[0]); ?></summary><p><?php echo esc_html($s[1]); ?></p></details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="akd-alt" role="contentinfo">
        <div class="uyg-kap akd-alt__in">
            <a class="akd-alt__marka" href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo esc_url(MST_Randevu::logo_url()); ?>" alt="" width="40" height="40" loading="lazy">
                <span><strong>MST Yayıncılık</strong><small><bdi lang="en">CineBook</bdi> · MST Çocuk</small></span>
            </a>
            <nav class="akd-alt__linkler" aria-label="Alt menü">
                <a href="#nedir">Hakkında</a>
                <a href="#yapimlar">Vizyonda</a>
                <a href="#studyo">Stüdyo</a>
                <a href="#cocuk">MST Çocuk</a>
                <a href="#iletisim">İletişim</a>
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
