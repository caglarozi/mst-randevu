<?php
/**
 * "MST CineBook ve MST Çocuk (Tam Sayfa)" şablonu.
 * Yapım şirketi sitesi düzeni: showreel girişi, CineBook nedir, afişlerden filmografi, stüdyo ve
 * kitapların filme yolculuğu, MST Çocuk ve adım adım başvuru formu.
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
$fr_gorsel = $o['fragman_gorsel'] ?: (sanitize_title($fr_ad) === 'gokboru' ? MST_Randevu::varlik('gokboru-afis.jpg') : ($fr_yt ? 'https://i.ytimg.com/vi/' . $fr_yt . '/maxresdefault.jpg' : ''));
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
$sosyal_ikon = [
    'youtube'   => '<rect x="2.5" y="5" width="19" height="14" rx="4"/><path d="m10 8.5 5 3.5-5 3.5z"/>',
    'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.7" r=".8" fill="currentColor" stroke="none"/>',
    'tiktok'    => '<path d="M14 3v11.2a4.2 4.2 0 1 1-4.2-4.2"/><path d="M14 3c.5 2.7 2.5 4.5 5 4.8"/>',
    'facebook'  => '<path d="M14.5 21v-8h3l.5-4h-3.5V7.2c0-1.2.4-2 2-2H18V2.2c-.5-.1-1.6-.2-2.8-.2-3 0-4.9 1.8-4.9 5.1V9H7.5v4h2.8v8z"/>',
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
<header class="mst-top mst-top--cb">
    <div class="mst-top__in">
        <a class="mst-top__logo" href="#showreel" aria-label="CineBook">
            <img src="<?php echo esc_url(MST_Randevu::varlik('cinebook-logo-transparent.png')); ?>" alt="CineBook" style="height: 42px; width: auto; object-fit: contain; margin-top: 4px;">
        </a>
        <button type="button" class="mst-top__menu" aria-label="Menüyü aç" aria-expanded="false" aria-controls="mst-top-menu"><span></span><span></span><span></span></button>
        <div class="mst-top__cta" id="mst-top-menu">
            <a class="cb-top-link" href="#showreel">Ana Sayfa</a>
            <a class="cb-top-link" href="#nedir">Hakkında</a>
            <a class="cb-top-link" href="#yapimlar">Vizyonda</a>
            <a class="cb-top-link" href="#cocuk">MST Çocuk</a>
            <a class="cb-top-link cb-top-link--altin" href="#basvuru">Başvuru Yap</a>
        </div>
    </div>
</header>

<main class="uyg cb" lang="tr">

    <!-- ============ Perde: kamera vizörü ============
         Sinema bandı, ışık, toz ve gren korunur. Öne çıkan afiş videoyu perdelikte açar. -->
    <section class="cb-reel<?php echo $fr_gorsel ? ' cb-reel--gorselli' : ''; ?>" id="showreel">
        <div class="cb-reel__arka" aria-hidden="true"></div>
        <!-- Altın ışık ve toz: arkada yavaş gezinen sıcak ışık lekeleri, havada süzülen toz (cinebook.js) -->
        <div class="cb-isiklar" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
        <canvas class="cb-toz" aria-hidden="true"></canvas>
        <div class="cb-gren" aria-hidden="true"></div>
        <div class="cb-karartma" aria-hidden="true"></div>



        <div class="cb-vizor">

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
                    <a class="cb-cizgi-btn" href="#yapimlar">Yapımları keşfet</a>
                </div>
            </div>
            <?php if ($fr_gorsel) : ?>
                <figure class="cb-reel__afis">
                    <?php if ($fragman) : ?><button type="button" class="cb-reel__afis-kapak" data-cb-video-ac="<?php echo esc_attr($fragman); ?>" data-cb-baslik="<?php echo esc_attr($fr_ad . ' · ' . $fr_etiket); ?>" aria-label="<?php echo esc_attr($fr_ad . ' videosunu izle'); ?>"><?php else : ?><div class="cb-reel__afis-kapak"><?php endif; ?>
                        <img src="<?php echo esc_url($fr_gorsel); ?>" alt="<?php echo esc_attr($fr_ad . ' afişi'); ?>" fetchpriority="high" decoding="async">
                        <?php if ($fragman) : ?><span class="cb-gosterim__play" aria-hidden="true"><?php echo $ik('oynat', 28); ?></span><?php endif; ?>
                    <?php echo $fragman ? '</button>' : '</div>'; ?>
                    <figcaption><strong><?php echo esc_html($fr_ad); ?></strong><span><?php echo esc_html($fr_etiket); ?><?php echo $fragman ? ' · İzle ↗' : ''; ?></span></figcaption>
                </figure>
            <?php endif; ?>
        </div>

    </section>

    <dialog class="cb-perdelik" id="cb-perdelik" aria-label="Video">
        <button type="button" class="cb-perdelik__kapat" data-cb-kapat aria-label="Kapat">×</button>
        <div class="cb-perdelik__yer"></div>
    </dialog>

    <!-- ============ CineBook nedir? 35 mm film şeridi ============
         Kaydırınca yazı süzülür, kareler sırayla "yanar" (data-cb-gir, cinebook.js). -->
    <section class="cb-bolum cb-nedir" id="nedir">
        <div class="uyg-kap">
            <p class="cb-etiket" data-cb-gir><span lang="en">CineBook</span> nedir?</p>
            <h2 class="cb-nedir__soz" data-cb-gir>Kitabın <em>ekrana taşınan</em> versiyonu.</h2>
        </div>
        <div class="cb-serit" data-cb-gir="serit" tabindex="0" aria-label="CineBook nedir: üç kare">
            <span class="cb-serit__sizinti" aria-hidden="true"></span>
            <ol class="cb-serit__kareler">
                <?php foreach ($nedir as $i => $n) : ?>
                    <li class="cb-kare" data-cb-gir="kare" style="--sira: <?php echo (int) $i; ?>">
                        <div class="cb-kare__goruntu">
                            <span class="cb-kare__no" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span>
                            <h3><?php echo esc_html($n[0]); ?></h3>
                            <p><?php echo esc_html($n[1]); ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ Vizyonda: öne çıkan yapımın afişi ve diğer yapımlar ============ -->
    <?php
    $gosterim = null; $digerleri = [];
    foreach ($yapimlar as $y) { if (!$gosterim && $y['video']) $gosterim = $y; else $digerleri[] = $y; }
    ?>
    <section class="cb-bolum cb-bolum--yapim cb-vizyon" id="yapimlar">
        <div class="uyg-kap">
            <header class="cb-vizyon__bas" data-cb-gir>
                <div class="cb-tabela" aria-hidden="true"><span>Vizyonda</span></div>
                <h2 class="cb-gizli">Yapımlar</h2>
                <p class="cb-bas__metin">Yayındaki ilk bölümü izleyin; sıradaki yapımları keşfedin.</p>
            </header>

            <?php if ($gosterim) :
                $ig = !MST_CineBook::youtube_mu($gosterim['video']);
                $ig_url = $ig ? 'https://www.instagram.com/p/' . substr($gosterim['video'], 3) . '/' : '';
                $gosterim_kapak = $gosterim['afis'] ?: ($ig ? '' : 'https://i.ytimg.com/vi/' . $gosterim['video'] . '/hqdefault.jpg');
                ?>
                <article class="cb-gosterim" data-cb-gir>
                    <button type="button" class="cb-gosterim__kapak" data-cb-video-ac="<?php echo esc_attr($gosterim['video']); ?>"
                            data-cb-baslik="<?php echo esc_attr($gosterim['ad'] . ' · ' . $gosterim['etiket']); ?>"
                            aria-label="<?php echo esc_attr($gosterim['ad'] . ' videosunu izle'); ?>">
                        <?php if ($gosterim_kapak) : ?><img src="<?php echo esc_url($gosterim_kapak); ?>" alt="<?php echo esc_attr($gosterim['ad'] . ' afişi'); ?>" loading="lazy" decoding="async"><?php endif; ?>
                        <span class="cb-gosterim__play" aria-hidden="true"><?php echo $ik('oynat', 28); ?></span>
                    </button>
                    <div class="cb-gosterim__bilgi">
                        <p class="cb-gosterim__ust">Şimdi vizyonda <span><?php echo esc_html($gosterim['etiket']); ?></span></p>
                        <h3><?php echo esc_html($gosterim['ad']); ?></h3>
                        <p class="cb-gosterim__lead"><?php echo sanitize_title($gosterim['ad']) === 'gokboru' ? 'Bir bakış. Bir kılıç. Gökbörü.' : 'Bir hikâye, yeni bir sahne.'; ?></p>
                        <p class="cb-gosterim__metin"><?php echo sanitize_title($gosterim['ad']) === 'gokboru' ? 'Gökbörü’nün karanlık ve destansı atmosferi ilk bölümüyle ekranda. Hikâyeyi şimdi izleyin.' : esc_html($gosterim['ad']) . ' yapımının dünyasına ilk adımı atın. Hikâyeyi şimdi izleyin.'; ?></p>
                        <div class="cb-gosterim__cta">
                            <button type="button" class="cb-gosterim__birincil" data-cb-video-ac="<?php echo esc_attr($gosterim['video']); ?>" data-cb-baslik="<?php echo esc_attr($gosterim['ad'] . ' · ' . $gosterim['etiket']); ?>">Bölümü izle <span aria-hidden="true">↗</span></button>
                            <?php if ($ig) : ?><a class="cb-gosterim__harici" href="<?php echo esc_url($ig_url); ?>" target="_blank" rel="noopener">Instagram’da aç ↗</a><?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endif; ?>

            <p class="cb-etiket cb-vizyon__ara" data-cb-gir>Yakında</p>
            <ul class="cb-filmografi">
                <?php foreach ($digerleri as $i => $y) :
                    $afis  = $y['afis'] ?: (MST_CineBook::youtube_mu($y['video']) ? 'https://i.ytimg.com/vi/' . $y['video'] . '/hqdefault.jpg' : '');
                    $durum = $y['video'] ? 'Vizyonda' : 'Yakında';
                    $etk   = $y['video'] ? 'button type="button" data-cb-video-ac="' . esc_attr($y['video']) . '" data-cb-baslik="' . esc_attr($y['ad'] . ' · ' . $y['etiket']) . '" aria-label="' . esc_attr($y['ad'] . ' videosunu izle') . '"' : 'div';
                    ?>
                    <li data-cb-grup="<?php echo esc_attr($y['tur']); ?>" data-cb-gir="kare" style="--sira: <?php echo (int) $i; ?>">
                        <<?php echo $etk; // öznitelikler yukarıda kaçırıldı ?> class="cb-afis cb-afis--<?php echo esc_attr(sanitize_title($y['ad'])); ?><?php echo $y['afis'] ? ' cb-afis--gorsel' : ($afis ? ' cb-afis--kapak' : ' cb-afis--yazi'); ?>">
                            <?php if ($afis) : ?><img src="<?php echo esc_url($afis); ?>" alt="" loading="lazy" decoding="async"><?php endif; ?>
                            <?php if (!$y['afis']) : ?>
                                <span class="cb-afis__sahne" aria-hidden="true"><svg class="cb-afis__ray" viewBox="0 0 270 405" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false"><defs><linearGradient id="cbRay" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#8a6a44"/><stop offset=".75" stop-color="#e9c68a"/><stop offset="1" stop-color="#fff1cf"/></linearGradient></defs><g class="cb-afis__traversler" stroke="#6e5538" stroke-linecap="round"><line x1="134.3" y1="173.5" x2="135.7" y2="173.5" stroke-width="0.62"/><line x1="132.3" y1="177.8" x2="137.7" y2="177.8" stroke-width="0.66"/><line x1="129.2" y1="184.5" x2="140.8" y2="184.5" stroke-width="0.74"/><line x1="124.9" y1="193.6" x2="145.1" y2="193.6" stroke-width="0.84"/><line x1="119.6" y1="204.9" x2="150.4" y2="204.9" stroke-width="0.97"/><line x1="113.2" y1="218.6" x2="156.8" y2="218.6" stroke-width="1.12"/><line x1="105.8" y1="234.4" x2="164.2" y2="234.4" stroke-width="1.30"/><line x1="97.4" y1="252.5" x2="172.6" y2="252.5" stroke-width="1.50"/><line x1="87.9" y1="272.6" x2="182.1" y2="272.6" stroke-width="1.72"/><line x1="77.5" y1="294.9" x2="192.5" y2="294.9" stroke-width="1.97"/><line x1="66.1" y1="319.4" x2="203.9" y2="319.4" stroke-width="2.24"/><line x1="53.7" y1="345.8" x2="216.3" y2="345.8" stroke-width="2.54"/><line x1="40.3" y1="374.4" x2="229.7" y2="374.4" stroke-width="2.86"/><line x1="26.0" y1="405.0" x2="244.0" y2="405.0" stroke-width="3.20"/></g><path d="M40,405 L133,172" stroke="url(#cbRay)" stroke-width="3" fill="none"/><path d="M230,405 L137,172" stroke="url(#cbRay)" stroke-width="3" fill="none"/></svg><i class="cb-afis__far"></i><i class="cb-afis__sis"></i></span>
                                <span class="cb-afis__yazi">
                                    <small><?php echo $y['tur'] === 'cocuk' ? 'MST Çocuk' : '<bdi lang="en">CineBook</bdi>'; ?> sunar</small>
                                    <strong><?php echo esc_html($y['ad']); ?></strong>
                                    <em><?php echo esc_html($y['etiket']); ?></em>
                                    <span class="cb-afis__jenerik">MST Yayıncılık · <?php echo $y['tur'] === 'cocuk' ? 'MST Çocuk' : '<bdi lang="en">CineBook</bdi>'; ?> · <?php echo esc_html($y['yil'] ?: wp_date('Y')); ?></span>
                                </span>
                                <?php if (!$y['video']) : ?><span class="cb-afis__damga" aria-hidden="true">Çok yakında</span><?php endif; ?>
                            <?php endif; ?>
                            <?php if ($y['video']) : ?><span class="cb-afis__izle"><i aria-hidden="true"></i>İzle</span><?php endif; ?>
                        </<?php echo $y['video'] ? 'button' : 'div'; ?>>
                        <h3><?php echo esc_html($y['ad']); ?></h3>
                        <p><?php echo esc_html($y['etiket']); ?><?php echo $y['yil'] ? ' · ' . esc_html($y['yil']) : ''; ?> · <span class="cb-durum<?php echo $y['video'] ? '' : ' is-yapimda'; ?>"><?php echo esc_html($durum); ?></span></p>
                    </li>
                <?php endforeach; ?>
                <li data-cb-grup="hepsi" class="cb-filmografi__siradaki" data-cb-gir="kare" style="--sira: <?php echo count($digerleri); ?>">
                    <a class="cb-bilet" href="#basvuru">
                        <span class="cb-bilet__ust"><small>CineBook</small><small>Başvuru</small></span>
                        <span class="cb-bilet__orta"><small>Sıradaki yapım</small><strong>Sizin kitabınız</strong><em>Ekrana taşıyalım</em></span>
                        <span class="cb-bilet__koc">Başvurun <i aria-hidden="true">→</i></span>
                    </a>
                    <h3>Başvuru</h3>
                    <p>Kitabınızı ekrana taşıyın · <span class="cb-durum">Başvuruya açık</span></p>
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
                    <h2 class="cb-studyo__soz">Kitabın ruhunu bilen ekip, <em>onu ekrana da taşır.</em></h2>
                    <p class="cb-studyo__metin"><span lang="en">CineBook</span>, MST Yayıncılık’ın yapım stüdyosudur. Eserin hikâyesini, karakterlerini ve atmosferini fragmana, kısa sahnelere ve sosyal medya videolarına uyarlar; çocuk kitaplarını MST Çocuk etiketiyle çizgi filme dönüştürür.</p>
                </div>
                <div class="cb-jenerik" aria-label="CineBook yapısı">
                    <div class="cb-jenerik__pencere">
                        <ul class="cb-jenerik__akis">
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
        <svg class="cb-dalga" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0,0 L1440,0 L1440,24 C1120,44 880,12 680,26 C440,42 220,14 0,26 Z" fill="#1b1916"/></svg>
        <div class="uyg-kap">
            <div class="cb-cocuk__giris">
                <div>
                    <p class="cb-cocuk__rozet">MST Çocuk</p>
                    <h2>Çocuk kitapları <span>çizgi filme</span> dönüşüyor.</h2>
                    <p class="cb-cocuk__alt">Resimli çocuk kitaplarını, karakterlerine ve çizimlerine sadık kalarak kısa çizgi filmlere uyarlıyoruz. Çocuklar sevdikleri hikâyeyi hem okuyor hem izliyor.</p>
                    <a class="cb-cocuk__btn" href="#takip">Yeni çizgi filmleri takip edin</a>
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

            <div class="cb-cocuk__afisler" style="margin-top: clamp(64px, 9vw, 100px);">
                <div class="cb-vizyon__bas" data-cb-gir style="margin-bottom: 30px;">
                    <h2 style="font-family: var(--mst-font); font-size: clamp(32px, 4vw, 50px); font-weight: 800; color: var(--mst-koyu); letter-spacing: -.02em;">Gelecek Yapımlar</h2>
                    <p class="cb-bas__metin" style="color: var(--mst-yazi);">MST Çocuk'un eğlenceli ve öğretici dünyasında yakında vizyona girecek çizgi filmler.</p>
                </div>
                
                <article class="cb-gosterim cb-gosterim--cocuk" data-cb-gir style="gap: clamp(30px, 5vw, 60px);">
                    <div class="cb-gosterim__kapak" style="cursor: default;">
                        <img src="<?php echo esc_url(MST_RANDEVU_URL . 'assets/yoksul-cocuk.png'); ?>" alt="Yoksul Çocuk afişi" loading="lazy" decoding="async" style="object-position: top;">
                    </div>
                    <div class="cb-gosterim__bilgi">
                        <p class="cb-gosterim__ust">Çok yakında sizlerle <span>Çizgi Film</span></p>
                        <h3>Yoksul Çocuk</h3>
                        <p class="cb-gosterim__lead">Sımsıcak bir dostluk hikâyesi ekranlara geliyor.</p>
                        <p class="cb-gosterim__metin">MST Yayıncılık bünyesinde sevilen <strong>Yoksul Çocuk</strong> kitabı, karakterlerine sadık kalınarak kısa çizgi filme uyarlanıyor. Çocuklar sevdikleri hikâyeyi çok yakında izleme fırsatı bulacak.</p>
                    </div>
                </article>
            </div>
        </div>
        <svg class="cb-dalga cb-dalga--alt" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0,60 L1440,60 L1440,36 C1120,16 880,48 680,34 C440,18 220,46 0,34 Z" fill="#1b1916"/></svg>
    </section>



    <!-- ============ Başvuru: randevu sistemi gibi adım adım ============
         1) Tür  2) Eser  3) İletişim ve onay. JS yoksa tüm adımlar alt alta görünür ve form yine çalışır.
         Gönderim: admin-ajax mst_cinebook_basvuru (cinebook.php). Altında küçük sosyal medya bağlantıları. -->
    <section class="cb-bolum cb-bolum--koyu cb-iletisim cb-basvuru" id="basvuru">
        <div class="uyg-kap">
            <p class="cb-iletisim__ust">Sıradaki sahne <em>sizin.</em></p>
            <h2 class="cb-iletisim__baslik">Başvuru</h2>
            <div class="cb-basvuru__in">
                <div class="cb-basvuru__sol">
                    <p class="cb-iletisim__metin">MST Yayıncılık yazarıysanız ve kitabınızın ekranda hayat bulmasını istiyorsanız eserinizi gönderin. Başvurunuzu inceleyip sizi arıyoruz; uygun eserler için uyarlama planını birlikte hazırlıyoruz.</p>
                    <p class="cb-basvuru__kosul"><strong>Yalnızca MST Yayıncılık yazarlarına açıktır.</strong> <bdi lang="en">CineBook</bdi> ve MST Çocuk, kitabı MST Yayıncılık’tan yayımlanan eserler için yapılır.</p>
                    <ol class="cb-basvuru__yol">
                        <li><span>1</span><div><strong>Türü seçin</strong><small><bdi lang="en">CineBook</bdi> ya da MST Çocuk</small></div></li>
                        <li><span>2</span><div><strong>Eserinizi tanıtın</strong><small>Adı, kısa özeti ve MST yayını onayı</small></div></li>
                        <li><span>3</span><div><strong>Sizi arayalım</strong><small>Ekibimiz değerlendirip dönüş yapar</small></div></li>
                    </ol>
                    <?php if ($wa) : ?><a class="cb-wa" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo MST_Randevu::icon('wa'); ?> Sorunuz varsa WhatsApp’tan yazın</a><?php endif; ?>
                </div>

                <form class="cb-form cb-form--adim" data-cb-form novalidate>
                    <p class="cb-form__koc" aria-hidden="true"><span>Başvuru bileti</span><span><bdi lang="en">CineBook</bdi> · MST Çocuk</span></p>
                    <ol class="cb-adimlar" aria-label="Başvuru adımları">
                        <li class="is-aktif" data-cb-adim-isaret="1"><i>1</i><span>Tür</span></li>
                        <li data-cb-adim-isaret="2"><i>2</i><span>Eser</span></li>
                        <li data-cb-adim-isaret="3"><i>3</i><span>İletişim</span></li>
                    </ol>

                    <fieldset class="cb-adim is-aktif" data-cb-adim="1">
                        <legend class="cb-adim__baslik">Ne için başvuruyorsunuz?</legend>
                        <p class="cb-adim__not">Başvurular yalnızca kitabı MST Yayıncılık’tan yayımlanan yazarlarımıza açıktır.</p>
                        <div class="cb-form__tur">
                            <label><input type="radio" name="tur" value="cinebook" checked><span><strong><bdi lang="en">CineBook</bdi></strong><small>Kitaptan fragman, kısa sahne ya da dijital film</small></span></label>
                            <label><input type="radio" name="tur" value="cocuk"><span><strong>MST Çocuk</strong><small>Çocuk kitabından çizgi film</small></span></label>
                        </div>
                        <div class="cb-adim__alt"><button type="button" class="cb-adim__ileri" data-cb-ileri>Devam <span aria-hidden="true">→</span></button></div>
                    </fieldset>

                    <fieldset class="cb-adim" data-cb-adim="2">
                        <legend class="cb-adim__baslik">Eseriniz</legend>
                        <label class="cb-alan"><span>Eser adı</span><input type="text" name="eser_adi" required maxlength="150" autocomplete="off"></label>
                        <label class="cb-alan"><span>Eserinizi kısaca anlatın <em>(isteğe bağlı)</em></span><textarea name="ozet" rows="4" maxlength="1500" placeholder="Tür, konu, hedef okur yaşı…"></textarea></label>
                        <label class="cb-onay cb-onay--mst"><input type="checkbox" name="mst_yazari" value="1" required><span>Bu kitap <strong>MST Yayıncılık</strong>’tan yayımlandı.</span></label>
                        <div class="cb-adim__alt"><button type="button" class="cb-adim__geri" data-cb-geri>← Geri</button><button type="button" class="cb-adim__ileri" data-cb-ileri>Devam <span aria-hidden="true">→</span></button></div>
                    </fieldset>

                    <fieldset class="cb-adim" data-cb-adim="3">
                        <legend class="cb-adim__baslik">Size nasıl ulaşalım?</legend>
                        <p class="cb-adim__ozet" data-cb-ozet hidden></p>
                        <label class="cb-alan"><span>Ad soyad</span><input type="text" name="yazar_adi" required minlength="3" maxlength="100" autocomplete="name"></label>
                        <div class="cb-form__ikili">
                            <label class="cb-alan"><span>Telefon</span><input type="tel" name="telefon" required inputmode="tel" autocomplete="tel" placeholder="05XX XXX XX XX"></label>
                            <label class="cb-alan"><span>E-posta <em>(isteğe bağlı)</em></span><input type="email" name="eposta" autocomplete="email" placeholder="ornek@eposta.com"></label>
                        </div>
                        <label class="cb-hp" aria-hidden="true">Web sitesi <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        <label class="cb-onay"><input type="checkbox" name="kvkk" value="1" required><span><a href="<?php echo esc_url(MST_Randevu::kvkk_url()); ?>" target="_blank" rel="noopener">KVKK Aydınlatma Metni</a>’ni okudum. Kişisel verilerimin başvurumun değerlendirilmesi ve benimle iletişim kurulması amacıyla MST Yayıncılık tarafından işlenmesini kabul ediyorum.</span></label>
                        <div class="cb-adim__alt"><button type="button" class="cb-adim__geri" data-cb-geri>← Geri</button><button type="submit" class="uyg-btn uyg-btn--altin cb-form__gonder">Başvuruyu Gönder</button></div>
                    </fieldset>

                    <p class="cb-form__hata" data-cb-hata role="alert" hidden></p>
                    <div class="cb-form__tamam" data-cb-tamam hidden></div>
                </form>
            </div>

            <?php if ($sosyal) : ?>
                <div class="cb-basvuru__takip" id="takip">
                    <span>Bizi takip edin</span>
                    <ul>
                        <?php foreach ($sosyal as $s) : $url = $o[$s[0]]; ?>
                            <li><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($s[1] . ' hesabını yeni sekmede aç'); ?>" title="<?php echo esc_attr($s[1]); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $sosyal_ikon[$s[0]]; ?></svg></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
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
                <a href="#basvuru">Başvuru</a>
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
