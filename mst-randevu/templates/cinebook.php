<?php
/**
 * "MST CineBook ve MST Çocuk (Tam Sayfa)" şablonu.
 * Üstte CineBook (koyu, sinematik), ortada MST Çocuk (açık, renkli), altta ortak başvuru formu.
 * Fragman ve sosyal medya adresleri Yazar Randevu → CineBook Ayarları'ndan gelir; boşsa yer tutucu görünür.
 */
if (!defined('ABSPATH')) {
    exit;
}

$o        = MST_CineBook::opts();
$fragman  = MST_CineBook::youtube_id($o['fragman_url']);
$cocuk_vd = MST_CineBook::youtube_id($o['cocuk_url']);
$wa       = MST_Randevu::wa_link('Merhaba, CineBook hakkında bilgi almak istiyorum.');

/** Tıklayınca yüklenen YouTube oynatıcısı (sayfa açılışında YouTube yüklenmez). Video yoksa yer tutucu. */
$oynatici = function ($id, $baslik, $etiket, $sinif = '') {
    if (!$id) {
        return '<div class="cb-video cb-video--bos ' . esc_attr($sinif) . '"><span class="cb-video__oynat" aria-hidden="true"></span><p><strong>' . esc_html($baslik) . '</strong><small>Video adresi CineBook Ayarları’ndan eklenecek</small></p></div>';
    }
    return '<button type="button" class="cb-video ' . esc_attr($sinif) . '" data-cb-video="' . esc_attr($id) . '" aria-label="' . esc_attr($baslik . ' videosunu oynat') . '"'
        . ' style="background-image:url(https://i.ytimg.com/vi/' . esc_attr($id) . '/hqdefault.jpg)">'
        . '<span class="cb-video__oynat" aria-hidden="true"></span><span class="cb-video__etiket">' . esc_html($etiket) . '</span></button>';
};

$bolumler = [
    ['#nedir', 'CineBook'], ['#surec', 'Süreç'], ['#projeler', 'Projeler'], ['#cocuk', 'MST Çocuk'],
    ['#izle', 'İzleyin'], ['#basvuru', 'Başvuru'], ['#sss', 'SSS'],
];

$nedir = [
    ['Dijital anlatı', 'Kitabın atmosferi fragman, kısa sahneler ve seslendirmeyle ekrana taşınır.'],
    ['Yazar, okur, izleyici', 'Okur kitabı izleyerek tanır; izleyici kitabın okuruna dönüşür.'],
    ['Sosyal medyaya uygun yayın', 'Her içerik YouTube, Instagram, Facebook ve TikTok için ayrı biçimlerde hazırlanır.'],
];

$surec = [
    ['Eser seçimi', 'Hikâye, karakterler ve görsel anlatım potansiyeli değerlendirilir.'],
    ['Senaryo uyarlaması', 'Kitabın en güçlü sahneleri kısa bir anlatıya dönüştürülür.'],
    ['Storyboard ve yapım', 'Sahneler karelere çizilir; görsel tasarım, kurgu, seslendirme ve müzikle fragman hazırlanır.'],
    ['Dijital yayın', 'Fragman CineBook kanallarında ve yazarın hesaplarında yayınlanır.'],
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
    ['youtube', 'YouTube', 'Fragmanlar ve tam bölümler'],
    ['instagram', 'Instagram', 'Kısa sahneler ve kamera arkası'],
    ['facebook', 'Facebook', 'Duyurular ve topluluk'],
    ['tiktok', 'TikTok', 'Kısa dikey videolar'],
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
    <meta name="theme-color" content="#0b0b0d">
    <meta name="color-scheme" content="only light">
    <?php echo MST_Randevu::seo_aciklama('cinebook'); ?>
    <script>document.documentElement.classList.add('uyg-js');if(!(window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches)&&innerHeight>=480)document.documentElement.classList.add('cb-hareket');</script>
    <?php wp_head(); ?>
    <?php
    foreach (['mst-randevu' => 'randevu.css', 'mst-uygulama' => 'uygulama.css', 'mst-cinebook' => 'cinebook.css'] as $h => $dosya) {
        if (!wp_style_is($h, 'done')) {
            echo '<link rel="stylesheet" id="' . esc_attr($h) . '-yedek-css" href="' . esc_url(MST_Randevu::varlik($dosya)) . '">' . "\n";
        }
    }
    ?>
</head>
<body <?php body_class('mst-sayfa mst-uyg mst-cb'); ?>>
<?php wp_body_open(); ?>
<?php echo MST_Randevu::header_html(false, ['Başvuru Yapın', '#basvuru'], 'Merhaba, CineBook hakkında bilgi almak istiyorum.'); ?>

<main class="uyg cb" lang="tr">

    <!-- ============ Giriş: kitap sayfası film karesine dönüşür ============
         Kaydırdıkça (cinebook.js --cb-p değişkenini 0→1 yapar) sinema bantları kapanır, sayfa kararır ve
         film karesi belirir. JS yoksa ya da "hareketi azalt" açıksa sayfa ve kare alt alta durağan görünür. -->
    <section class="cb-sahne" id="giris" data-cb-sahne>
        <div class="cb-sahne__pin">
            <article class="cb-kagit" aria-labelledby="cb-baslik">
                <header class="cb-kagit__ust"><span lang="en">CineBook</span><span>MST Yayıncılık</span></header>
                <p class="cb-kagit__bolum">Birinci bölüm</p>
                <h1 id="cb-baslik">Kitabınız ekranda hayat bulsun.</h1>
                <p class="cb-kagit__metin">Her hikâye önce bir sayfada doğar. Okur onu satır satır kurar; karakterlerin yüzünü, sokakların sesini kendi zihninde çizer. CineBook bu sayfayı bir adım öteye taşır: kitabın en güçlü sahnelerini fragmana, kısa dijital anlatılara ve sosyal medya için hazırlanmış videolara dönüştürür. <span class="cb-kagit__son">Böylece kitap yalnızca rafta değil, ekranda da okurunu bulur.</span></p>
                <p class="cb-kagit__no">1</p>
                <p class="cb-kagit__ipucu" aria-hidden="true">Kaydırın, sayfa ekrana dönüşsün</p>
            </article>
            <div class="cb-kare">
                <div class="cb-kare__video">
                    <?php echo $oynatici($fragman, $o['fragman_ad'] ?: 'Öne çıkan fragman', 'Şimdi izle · ' . ($o['fragman_ad'] ?: 'Fragman') . ' — İlk fragman'); ?>
                    <p class="cb-altyazi" aria-hidden="true">Böylece kitap yalnızca rafta değil, ekranda da okurunu bulur.</p>
                </div>
                <p class="cb-kare__imza"><span>Şimdi izle</span> <?php echo esc_html($o['fragman_ad'] ?: 'Öne çıkan fragman'); ?> — İlk fragman</p>
            </div>
            <span class="cb-bant cb-bant--ust" aria-hidden="true"></span>
            <span class="cb-bant cb-bant--alt" aria-hidden="true"></span>
        </div>
    </section>

    <!-- ============ Jenerik: iki yol ve rakamlar ============ -->
    <section class="cb-jenerik" id="nedir">
        <div class="uyg-kap">
            <p class="cb-jenerik__sunar">MST Yayıncılık sunar</p>
            <h2 class="cb-jenerik__baslik">Kitabın görünür hâli: <em>CineBook</em></h2>
            <div class="cb-jenerik__satirlar">
                <?php foreach ($nedir as $n) : ?><p><span><?php echo esc_html($n[0]); ?></span><?php echo esc_html($n[1]); ?></p><?php endforeach; ?>
            </div>
            <div class="cb-giris__yollar">
                <a class="cb-yol" href="#basvuru" data-cb-tur="cinebook"><small>Kitabım için</small><strong>CineBook başvurusu</strong></a>
                <a class="cb-yol cb-yol--cocuk" href="#cocuk"><small>Çocuk kitabım için</small><strong>MST Çocuk</strong></a>
            </div>
            <dl class="cb-rakamlar">
                <div><dt>Yayınlanan fragman</dt><dd class="cb-yer">—</dd></div>
                <div><dt>Toplam izlenme</dt><dd class="cb-yer">—</dd></div>
                <div><dt>Uyarlanan kitap</dt><dd class="cb-yer">—</dd></div>
                <div><dt>Yayın platformu</dt><dd>4</dd></div>
            </dl>
        </div>
    </section>

    <nav class="cb-gezinti" aria-label="Sayfa bölümleri">
        <div class="uyg-kap cb-gezinti__in">
            <?php foreach ($bolumler as $b) : ?><a href="<?php echo esc_attr($b[0]); ?>"><?php echo esc_html($b[1]); ?></a><?php endforeach; ?>
        </div>
    </nav>

    <!-- ============ Süreç: aynı sahnenin dört hâli ============ -->
    <section class="cb-bolum cb-bolum--siyah" id="surec">
        <div class="uyg-kap">
            <header class="cb-bas cb-bas--orta">
                <p class="cb-ust">Süreç</p>
                <h2>Aynı sahne, dört hâl</h2>
                <p>Bir kitap sahnesinin ekrana ulaşana kadar geçtiği yol. <em class="cb-not">Örnek sahne</em></p>
            </header>
            <ol class="cb-hal">
                <li>
                    <div class="cb-hal__kagit cb-hal__kagit--kitap">
                        <p><span class="cb-ilk">G</span>üneş denize gömülürken Deniz, kumların arasında yarısı ıslanmış bir mektup buldu. Zarfın üzerinde, silik mürekkeple, kendi adı yazıyordu.</p>
                        <small>s. 112</small>
                    </div>
                    <h3><span>01</span><?php echo esc_html($surec[0][0]); ?></h3><p><?php echo esc_html($surec[0][1]); ?></p>
                </li>
                <li>
                    <div class="cb-hal__kagit cb-hal__kagit--senaryo">
                        <p class="cb-sn-bas">SAHNE 12 — DIŞ. SAHİL — ALACAKARANLIK</p>
                        <p>DENİZ (10) kumda eğilir, ıslak bir zarf çıkarır. Zarfı ışığa tutar.</p>
                        <p class="cb-sn-kim">DENİZ</p>
                        <p class="cb-sn-parantez">(fısıltıyla)</p>
                        <p class="cb-sn-soz">Bu… benim adım.</p>
                    </div>
                    <h3><span>02</span><?php echo esc_html($surec[1][0]); ?></h3><p><?php echo esc_html($surec[1][1]); ?></p>
                </li>
                <li>
                    <div class="cb-hal__kagit cb-hal__kagit--story">
                        <svg viewBox="0 0 240 150" aria-hidden="true">
                            <rect x="6" y="6" width="228" height="138" fill="none" stroke="#5c5750" stroke-width="2"/>
                            <path d="M8,92 C60,90 120,93 232,90" stroke="#77716a" stroke-width="1.6" fill="none"/>
                            <path d="M150,92 a26,26 0 0 1 52,0" stroke="#77716a" stroke-width="1.6" fill="none"/>
                            <path d="M8,112 C50,106 90,118 140,110 S210,104 232,112" stroke="#9a948c" stroke-width="1.2" fill="none"/>
                            <g stroke="#3f3a34" stroke-width="2.2" fill="none" stroke-linecap="round">
                                <circle cx="86" cy="96" r="6"/><path d="M86,102 C84,112 80,118 74,124 M83,110 L96,118 M78,122 L72,138 M76,124 L86,138"/>
                            </g>
                            <path d="M100,126 l14,-4 l2,6 z" fill="#b5ada2"/>
                            <path d="M30,30 L70,52" stroke="#c0392b" stroke-width="2" stroke-dasharray="5 4"/>
                            <path d="M70,52 l-9,-1 l5,-7" fill="none" stroke="#c0392b" stroke-width="2"/>
                        </svg>
                        <p class="cb-sb-not"><b>12A</b> Kamera: yavaş yaklaşma</p>
                    </div>
                    <h3><span>03</span><?php echo esc_html($surec[2][0]); ?></h3><p><?php echo esc_html($surec[2][1]); ?></p>
                </li>
                <li>
                    <div class="cb-hal__kagit cb-hal__kagit--ekran">
                        <svg viewBox="0 0 240 150" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                            <defs>
                                <linearGradient id="cbGok" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2b2046"/><stop offset=".55" stop-color="#c8643a"/><stop offset="1" stop-color="#f2b25c"/></linearGradient>
                                <linearGradient id="cbDeniz" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3a3350"/><stop offset="1" stop-color="#151320"/></linearGradient>
                            </defs>
                            <rect width="240" height="96" fill="url(#cbGok)"/>
                            <circle cx="176" cy="96" r="22" fill="#ffd79a"/>
                            <rect y="94" width="240" height="56" fill="url(#cbDeniz)"/>
                            <path d="M150,100 h52 M160,106 h32" stroke="#ffd79a" stroke-opacity=".5" stroke-width="2"/>
                            <g fill="#0d0b12"><circle cx="86" cy="100" r="5.5"/><path d="M82,106 C80,114 76,120 70,126 L96,122 C92,114 90,110 90,106 Z"/><path d="M70,124 L66,142 L72,142 L78,126 Z M88,122 L92,142 L98,142 L94,122 Z"/></g>
                        </svg>
                        <p class="cb-altyazi cb-altyazi--kucuk">Bu… benim adım.</p>
                    </div>
                    <h3><span>04</span><?php echo esc_html($surec[3][0]); ?></h3><p><?php echo esc_html($surec[3][1]); ?></p>
                </li>
            </ol>
        </div>
    </section>

    <!-- ============ Projeler: kontakt baskı ============ -->
    <section class="cb-bolum cb-bolum--koyu" id="projeler">
        <div class="uyg-kap">
            <header class="cb-bas cb-bas--orta">
                <p class="cb-ust">Projeler</p>
                <h2>Öne çıkan hikâyeler</h2>
            </header>
            <div class="cb-kontakt">
                <p class="cb-kontakt__kenar" aria-hidden="true"><span>MST CINEBOOK 400</span><span>▸ 23</span><span>▸ 23A</span><span>▸ 24</span><span>▸ 24A</span><span>▸ 25</span></p>
                <div class="cb-kontakt__kareler">
                    <article class="cb-kontakt__kare cb-kontakt__kare--secili">
                        <div class="cb-afis"><span class="cb-afis__ust"><span lang="en">CineBook</span> sunar</span><strong><?php echo esc_html($o['fragman_ad'] ?: 'Gökbörü'); ?></strong><span class="cb-afis__alt">Afiş eklenecek</span></div>
                        <svg class="cb-kalem" viewBox="0 0 300 420" preserveAspectRatio="none" aria-hidden="true"><path d="M150,14 C250,10 292,80 290,200 C288,330 236,408 146,406 C52,404 10,320 12,204 C14,90 60,20 162,18" fill="none" stroke="#e03a2b" stroke-width="4" stroke-linecap="round" vector-effect="non-scaling-stroke" opacity=".9"/></svg>
                        <p class="cb-kontakt__not"><b>Yayında</b> İlk fragman · <a href="#giris">İzle</a></p>
                    </article>
                    <article class="cb-kontakt__kare">
                        <div class="cb-afis cb-afis--yakinda"><span class="cb-afis__ust">Yapım aşamasında</span><strong>Yakında</strong><span class="cb-afis__alt">Proje adı eklenecek</span></div>
                        <p class="cb-kontakt__not"><b class="gri">Yakında</b> CineBook’ta</p>
                    </article>
                    <a class="cb-kontakt__kare cb-kontakt__kare--bos" href="#basvuru" data-cb-tur="cinebook">
                        <span class="cb-kontakt__bos"><small>Sıradaki kare</small><strong>Sizin hikâyeniz</strong><em>Başvurun</em></span>
                        <p class="cb-kontakt__not">&nbsp;</p>
                    </a>
                </div>
                <p class="cb-kontakt__kenar" aria-hidden="true"><span>▸ 25A</span><span>KODAK ◆ 5219</span><span>▸ 26</span><span>▸ 26A</span><span>MST</span><span>▸ 27</span></p>
            </div>
        </div>
    </section>

    <!-- ============ MST Çocuk ============ -->
    <section class="cb-cocuk" id="cocuk">
        <svg class="cb-dalga" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0,0 H1440 V38 C1260,82 1080,82 900,52 C720,22 540,22 360,52 C220,76 100,70 0,44 Z" fill="#131316"/></svg>
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
        <svg class="cb-dalga cb-dalga--alt" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0,80 H1440 V42 C1300,10 1140,6 960,30 C780,54 600,62 420,40 C260,20 120,22 0,40 Z" fill="#131316"/></svg>
    </section>

    <!-- ============ İzleyin ============ -->
    <section class="cb-bolum cb-bolum--koyu" id="izle">
        <div class="uyg-kap">
            <header class="cb-bas cb-bas--orta">
                <p class="cb-ust">İzleyin</p>
                <h2>Filmlerin yayınlandığı alan</h2>
                <p>Fragmanlar, kısa sahneler ve kamera arkası CineBook hesaplarında.</p>
            </header>
            <div class="cb-sosyal">
                <?php foreach ($sosyal as $s) :
                    $url = $o[$s[0]]; $ad = MST_CineBook::hesap_adi($url); ?>
                    <?php if ($url) : ?>
                        <a class="cb-sosyal__k cb-sosyal__k--<?php echo esc_attr($s[0]); ?>" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">
                    <?php else : ?>
                        <div class="cb-sosyal__k cb-sosyal__k--<?php echo esc_attr($s[0]); ?> cb-sosyal__k--bos">
                    <?php endif; ?>
                        <strong><?php echo esc_html($s[1]); ?></strong>
                        <span><?php echo esc_html($ad ?: 'Adres eklenecek'); ?></span>
                        <small><?php echo esc_html($s[2]); ?></small>
                    <?php echo $url ? '</a>' : '</div>'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ Başvuru ============ -->
    <section class="cb-bolum cb-bolum--kagit" id="basvuru">
        <div class="uyg-kap cb-iki cb-iki--basvuru">
            <header class="cb-bas">
                <p class="cb-ust">Başvuru</p>
                <h2>Hikâyenizi CineBook evrenine taşıyın.</h2>
                <p>Kitabınızın ekranda hayat bulmasını istiyorsanız doğru yerdesiniz. Formu doldurun, eserinizi inceleyelim.</p>
                <ol class="cb-adimlar">
                    <li><span>1</span>Başvurunuz incelenir.</li>
                    <li><span>2</span>Ekibimiz sizi arar.</li>
                    <li><span>3</span>Uygun eserler için uyarlama planı birlikte hazırlanır.</li>
                </ol>
                <?php if ($wa) : ?><a class="cb-wa" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo MST_Randevu::icon('wa'); ?> Sorunuz mu var? WhatsApp’tan yazın</a><?php endif; ?>
            </header>

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
    <section class="cb-bolum cb-bolum--kagit cb-bolum--sss" id="sss">
        <div class="uyg-kap uyg-dar">
            <header class="cb-bas cb-bas--orta">
                <p class="cb-ust">SSS</p>
                <h2>Sık sorulan sorular</h2>
            </header>
            <div class="cb-sss">
                <?php foreach ($sss as $s) : ?>
                    <details><summary><?php echo esc_html($s[0]); ?></summary><p><?php echo esc_html($s[1]); ?></p></details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="cb-alt" role="contentinfo">
        <div class="uyg-kap cb-alt__in">
            <a class="cb-alt__marka" href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo esc_url(MST_Randevu::logo_url()); ?>" alt="" width="40" height="40" loading="lazy">
                <span><strong>MST Yayıncılık</strong><small><span lang="en">CineBook</span> · MST Çocuk</small></span>
            </a>
            <nav class="cb-alt__linkler" aria-label="Alt menü">
                <a href="#projeler">Projeler</a>
                <a href="#cocuk">MST Çocuk</a>
                <a href="#basvuru">Başvuru</a>
                <a href="<?php echo esc_url(MST_Randevu::kvkk_url()); ?>">KVKK Aydınlatma Metni</a>
                <a href="<?php echo esc_url(home_url('/')); ?>">mstyayincilik.com</a>
            </nav>
            <p class="cb-alt__telif">© <?php echo esc_html(wp_date('Y')); ?> MST Yayıncılık. Tüm hakları saklıdır.</p>
        </div>
    </div>
</main>

<?php wp_footer(); ?>
</body>
</html>
