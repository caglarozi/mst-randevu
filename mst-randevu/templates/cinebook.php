<?php
/**
 * "MST CineBook ve MST Çocuk (Tam Sayfa)" şablonu.
 * Akademi sayfasıyla aynı tasarım dili: sahne ışıklı açılış, bölüm menüsü, simgeli kartlar,
 * koyu/açık bölümler ve kurumsal alt bilgi (akademi.css / akademi.js). CineBook'a özgü parçalar
 * (fragman oynatıcı, afişler, MST Çocuk, başvuru formu) cinebook.css'tedir.
 * Fragman ve sosyal medya adresleri Yazar Randevu → CineBook Ayarları'ndan gelir; boşsa yer tutucu görünür.
 */
if (!defined('ABSPATH')) {
    exit;
}

$o        = MST_CineBook::opts();
$fragman  = MST_CineBook::youtube_id($o['fragman_url']);
$cocuk_vd = MST_CineBook::youtube_id($o['cocuk_url']);
$wa       = MST_Randevu::wa_link('Merhaba, CineBook hakkında bilgi almak istiyorum.');
$fr_ad    = $o['fragman_ad'] ?: 'Gökbörü';

/** Tıklayınca yüklenen YouTube oynatıcısı (sayfa açılışında YouTube yüklenmez). Video yoksa yer tutucu. */
$oynatici = function ($id, $baslik, $etiket, $sinif = '') {
    if (!$id) {
        return '<div class="cb-video cb-video--bos ' . esc_attr($sinif) . '"><span class="cb-video__oynat" aria-hidden="true"></span><p><strong>' . esc_html($baslik) . '</strong><small>Video adresi CineBook Ayarları’ndan eklenecek</small></p></div>';
    }
    return '<button type="button" class="cb-video ' . esc_attr($sinif) . '" data-cb-video="' . esc_attr($id) . '" aria-label="' . esc_attr($baslik . ' videosunu oynat') . '"'
        . ' style="background-image:url(https://i.ytimg.com/vi/' . esc_attr($id) . '/hqdefault.jpg)">'
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
$liste = function (array $maddeler, $sinif = '') use ($ik) {
    $h = '<ul class="uyg-liste ' . esc_attr($sinif) . '">';
    foreach ($maddeler as $m) $h .= '<li>' . $ik('onay', 16) . '<span>' . esc_html($m) . '</span></li>';
    return $h . '</ul>';
};

$bolumler = [
    ['#nedir', 'CineBook nedir?'], ['#surec', 'Süreç'], ['#fragman', 'Fragman'], ['#projeler', 'Projeler'],
    ['#cocuk', 'MST Çocuk'], ['#izle', 'İzleyin'], ['#basvuru', 'Başvuru'], ['#sss', 'SSS'],
];

$surec = [
    ['kitap', 'Eser seçimi', 'Hikâye, karakterler ve görsel anlatım potansiyeli değerlendirilir.'],
    ['kalem', 'Senaryo uyarlaması', 'Kitabın en güçlü sahneleri kısa ve etkileyici bir anlatıya dönüştürülür.'],
    ['klaket', 'Fragman yapımı', 'Görsel tasarım, kurgu, seslendirme ve müzik bir araya gelir.'],
    ['megafon', 'Dijital yayın', 'Fragman CineBook kanallarında ve yazarın hesaplarında yayınlanır.'],
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

$sosyal = [
    ['youtube', 'YouTube', 'Fragmanlar ve tam bölümler', 'oynat'],
    ['instagram', 'Instagram', 'Kısa sahneler ve kamera arkası', 'kamera'],
    ['facebook', 'Facebook', 'Duyurular ve topluluk', 'kisiler'],
    ['tiktok', 'TikTok', 'Kısa dikey videolar', 'film'],
];

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
    <meta name="theme-color" content="#161616">
    <meta name="color-scheme" content="only light">
    <?php echo MST_Randevu::seo_aciklama('cinebook'); ?>
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
<?php echo MST_Randevu::header_html(false, ['Başvuru Yapın', '#basvuru'], 'Merhaba, CineBook hakkında bilgi almak istiyorum.'); ?>

<main class="uyg akd cb" lang="tr">

    <!-- ============ Açılış: sahne ============ -->
    <section class="akd-sahne" id="cinebook">
        <div class="akd-sahne__isik" aria-hidden="true"><span class="akd-sahne__lamba"></span><span class="akd-sahne__huzme"></span><canvas class="akd-sahne__toz"></canvas><span class="akd-sahne__zemin"></span></div>
        <div class="uyg-kap akd-sahne__in">
            <p class="akd-sahne__yaz" data-akd-daktilo>MST Yayıncılık</p>
            <h1 class="akd-sahne__baslik"><span lang="en">Cine<em>Book</em></span></h1>
            <p class="akd-sahne__alt">Kitapları fragmana, kısa dijital anlatılara ve sosyal medya videolarına dönüştürüyoruz. Hikâyeniz yalnızca rafta değil, ekranda da okurunu bulsun. Çocuk kitapları için MST Çocuk ile çizgi film.</p>
            <div class="akd-sahne__cta">
                <a class="uyg-btn uyg-btn--altin" href="#fragman"><?php echo $ik('oynat', 18); ?> Fragmanı İzleyin</a>
                <a class="akd-bilet-btn" href="#basvuru" data-cb-tur="cinebook"><span class="akd-bilet-btn__kocan">Başvuru</span><span class="akd-bilet-btn__metin">Eserinizi gönderin</span></a>
            </div>
        </div>
    </section>

    <nav class="akd-gezinti" aria-label="Sayfa bölümleri">
        <div class="uyg-kap">
            <?php foreach ($bolumler as $b) : ?><a href="<?php echo esc_attr($b[0]); ?>" data-akd-gez="<?php echo esc_attr(substr($b[0], 1)); ?>"><?php echo esc_html($b[1]); ?></a><?php endforeach; ?>
        </div>
    </nav>

    <!-- ============ CineBook nedir? ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="nedir">
        <div class="uyg-kap uyg-iki">
            <div>
                <header class="uyg-baslik uyg-baslik--sol">
                    <span class="uyg-ust"><?php echo $ik('film', 18); ?> <bdi lang="en">CineBook</bdi> nedir?</span>
                    <h2>Bir kitabın okura ulaşan yolu artık ekrandan da geçiyor.</h2>
                </header>
                <p class="akd-paragraf">Okur bir kitabı çoğu zaman önce ekranda tanır: bir fragmanda, kısa bir sahnede, sosyal medyada karşısına çıkan bir videoda. CineBook, MST Yayıncılık’ın bu ihtiyaç için kurduğu yapım birimidir.</p>
                <p class="akd-paragraf">Kitabın hikâyesi, karakterleri ve atmosferi <strong>kısa ve etkileyici görsel anlatılara</strong> dönüştürülür; izleyici kitabın okuruna dönüşür.</p>
            </div>
            <div class="akd-duzen" aria-hidden="true">
                <div class="akd-duzen__daginik">
                    <?php foreach (['Karakterler', 'Sahneler', 'Diyaloglar', 'Atmosfer', 'Kapak', 'Hikâye', 'Okur'] as $i => $c) : ?>
                        <span style="--i:<?php echo (int) $i; ?>"><?php echo esc_html($c); ?></span>
                    <?php endforeach; ?>
                    <small>Kitabın sayfalarında</small>
                </div>
                <div class="akd-duzen__ok"><?php echo $ik('ok', 28); ?></div>
                <ol class="akd-duzen__sirali">
                    <?php foreach (['Fragman', 'Kısa sahneler', 'Seslendirme ve müzik', 'Dikey videolar', 'Sosyal medya yayını', 'Yeni okurlar'] as $c) : ?>
                        <li><?php echo esc_html($c); ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </section>

    <!-- ============ Süreç ============ -->
    <section class="uyg-bolum uyg-bolum--koyu" id="surec">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('dongu', 18); ?> Yapım süreci</span>
                <h2>Kitaptan ekrana dört adım</h2>
                <p>Her yapım aynı sırayla ilerler; eser seçiminden yayına kadar yazarla birlikte.</p>
            </header>
            <ol class="akd-cark cb-cark">
                <?php foreach ($surec as $i => $s) : ?><li><span><?php echo $i + 1; ?></span><?php echo esc_html($s[1]); ?></li><?php endforeach; ?>
            </ol>
            <div class="akd-kurallar cb-kurallar">
                <?php foreach ($surec as $s) : ?>
                    <div class="akd-kural"><span class="akd-kural__ic"><?php echo $ik($s[0], 20); ?></span><p><strong><?php echo esc_html($s[1]); ?></strong><?php echo esc_html($s[2]); ?></p></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ Öne çıkan fragman ============ -->
    <section class="uyg-bolum" id="fragman">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('oynat', 18); ?> Şimdi izleyin</span>
                <h2><?php echo esc_html($fr_ad); ?> — İlk fragman</h2>
                <p>CineBook’un ilk yapımı. Kitabın dünyası birkaç dakikalık bir fragmanda.</p>
            </header>
            <div class="cb-perde">
                <?php echo $oynatici($fragman, $fr_ad . ' — İlk fragman', 'Şimdi izle · ' . $fr_ad); ?>
            </div>
        </div>
    </section>

    <!-- ============ Projeler ============ -->
    <section class="uyg-bolum akd-lacivert" id="projeler">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('klaket', 18); ?> Projeler</span>
                <h2>Öne çıkan hikâyeler</h2>
                <p>Yayındaki ve yapım aşamasındaki CineBook projeleri.</p>
            </header>
            <div class="cb-projeler">
                <article class="cb-proje">
                    <div class="cb-afis"><span class="cb-afis__ust"><span lang="en">CineBook</span> sunar</span><strong><?php echo esc_html($fr_ad); ?></strong><span class="cb-afis__alt">Afiş eklenecek</span></div>
                    <div class="cb-proje__alt"><p><span class="cb-rozet">Yayında</span> İlk fragman</p><a href="#fragman">İzle <?php echo $ik('ok', 16); ?></a></div>
                </article>
                <article class="cb-proje">
                    <div class="cb-afis cb-afis--yakinda"><span class="cb-afis__ust">Yapım aşamasında</span><strong>Yakında</strong><span class="cb-afis__alt">Proje adı eklenecek</span></div>
                    <div class="cb-proje__alt"><p><span class="cb-rozet cb-rozet--gri">Yakında</span> CineBook’ta</p></div>
                </article>
                <a class="cb-proje cb-proje--davet" href="#basvuru" data-cb-tur="cinebook">
                    <span class="cb-proje__ic"><?php echo $ik('kivilcim', 28); ?></span>
                    <strong>Sıradaki hikâye sizinki olabilir.</strong>
                    <p>Kitabınızı ekrana taşımak için başvurun.</p>
                    <em>Başvuru yapın <?php echo $ik('ok', 16); ?></em>
                </a>
            </div>
        </div>
    </section>

    <!-- ============ MST Çocuk ============ -->
    <section class="cb-cocuk" id="cocuk">
        <svg class="cb-dalga" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0,0 H1440 V38 C1260,82 1080,82 900,52 C720,22 540,22 360,52 C220,76 100,70 0,44 Z" fill="#16223f"/></svg>
        <div class="uyg-kap">
            <div class="cb-cocuk__giris">
                <div>
                    <p class="cb-cocuk__rozet">MST Çocuk</p>
                    <h2>Çocuk kitapları <span>çizgi filme</span> dönüşüyor.</h2>
                    <p class="cb-cocuk__alt">Resimli çocuk kitaplarını, karakterlerine ve çizimlerine sadık kalarak kısa çizgi filmlere uyarlıyoruz. Çocuklar sevdikleri hikâyeyi hem okuyor hem izliyor.</p>
                    <a class="cb-cocuk__btn" href="#basvuru" data-cb-tur="cocuk">Çocuk kitabınız için başvurun</a>
                </div>
                <div class="cb-cocuk__sahne">
                    <?php echo $oynatici($cocuk_vd, 'MST Çocuk örnek çizgi film', 'Örnek çizgi filmi izle', 'cb-video--cocuk'); ?>
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
        <svg class="cb-dalga cb-dalga--alt" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0,80 H1440 V42 C1300,10 1140,6 960,30 C780,54 600,62 420,40 C260,20 120,22 0,40 Z" fill="#161616"/></svg>
    </section>


    <!-- ============ İzleyin ============ -->
    <section class="uyg-bolum uyg-bolum--koyu" id="izle">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('kamera', 18); ?> İzleyin</span>
                <h2>Filmlerin yayınlandığı alan</h2>
                <p>Fragmanlar, kısa sahneler ve kamera arkası CineBook hesaplarında.</p>
            </header>
            <div class="cb-sosyal">
                <?php foreach ($sosyal as $s) :
                    $url = $o[$s[0]]; $ad = MST_CineBook::hesap_adi($url);
                    $ic = '<span class="cb-sosyal__ic">' . $ik($s[3], 22) . '</span><strong>' . esc_html($s[1]) . '</strong><span class="cb-sosyal__ad">' . esc_html($ad ?: 'Adres eklenecek') . '</span><small>' . esc_html($s[2]) . '</small>'; ?>
                    <?php if ($url) : ?>
                        <a class="cb-sosyal__k cb-sosyal__k--<?php echo esc_attr($s[0]); ?>" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo $ic; ?></a>
                    <?php else : ?>
                        <div class="cb-sosyal__k cb-sosyal__k--<?php echo esc_attr($s[0]); ?> cb-sosyal__k--bos"><?php echo $ic; ?></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ Neden CineBook ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="guven">
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('kalkan', 18); ?> Neden <bdi lang="en">CineBook</bdi>?</span>
                <h2>Yayıncılığın içinden gelen bir yapım birimi</h2>
                <p>Kitabı en iyi, onu yayına hazırlayan ekip tanır.</p>
            </header>
            <div class="akd-guven">
                <?php foreach ([
                    ['kitap', 'Uyarlama, kitabın editoryal ekibiyle birlikte ve eserin özüne sadık kalınarak yapılır.'],
                    ['klaket', 'Senaryo, görsel tasarım, kurgu ve ses tek bir ekip içinde yürür.'],
                    ['megafon', 'Her içerik YouTube, Instagram, Facebook ve TikTok için ayrı biçimlerde hazırlanır.'],
                    ['kisi', 'Yazar sürecin her aşamasında bilgilendirilir.'],
                    ['grafik', 'Fragman, kitabın tanıtım ve lansman planının bir parçası olarak kullanılır.'],
                ] as $g) : ?>
                    <article class="uyg-ozellik"><span class="uyg-ozellik__ic"><?php echo $ik($g[0], 22); ?></span><p><?php echo esc_html($g[1]); ?></p></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ Başvuru ============ -->
    <section class="uyg-bolum uyg-bolum--koyu cb-basvuru" id="basvuru">
        <div class="uyg-kap uyg-iki">
            <div>
                <header class="uyg-baslik uyg-baslik--sol">
                    <span class="uyg-ust"><?php echo $ik('dosya', 18); ?> Başvuru</span>
                    <h2>Hikâyenizi CineBook evrenine taşıyın.</h2>
                    <p>Kitabınızın ekranda hayat bulmasını istiyorsanız doğru yerdesiniz. Formu doldurun, eserinizi inceleyelim.</p>
                </header>
                <ol class="cb-adimlar">
                    <li><span>1</span>Başvurunuz incelenir.</li>
                    <li><span>2</span>Ekibimiz sizi arar.</li>
                    <li><span>3</span>Uygun eserler için uyarlama planı birlikte hazırlanır.</li>
                </ol>
                <?php if ($wa) : ?><a class="cb-wa" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo MST_Randevu::icon('wa'); ?> Sorunuz mu var? WhatsApp’tan yazın</a><?php endif; ?>
            </div>
            <form class="cb-form" data-cb-form novalidate>
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

    <div class="akd-alt" role="contentinfo">
        <div class="uyg-kap akd-alt__in">
            <a class="akd-alt__marka" href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo esc_url(MST_Randevu::logo_url()); ?>" alt="" width="40" height="40" loading="lazy">
                <span><strong>MST Yayıncılık</strong><small><bdi lang="en">CineBook</bdi> · MST Çocuk</small></span>
            </a>
            <nav class="akd-alt__linkler" aria-label="Alt menü">
                <a href="#fragman">Fragman</a>
                <a href="#projeler">Projeler</a>
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
