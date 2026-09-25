<?php
/**
 * "MST Yazar Paneli Tanıtım (Tam Sayfa)" şablonu: yazarların kullandığı web uygulamasının
 * (MST Yazar Paneli) tanıtım sayfası. Üst çubuk randevu sayfasıyla ortaktır.
 * Telefon ve panellerdeki ekranlar temsili çizimdir; gerçek veri değildir.
 */
if (!defined('ABSPATH')) {
    exit;
}

$panel   = MST_Randevu::uygulama_url();
$randevu = MST_Randevu::randevu_url();
$wa      = MST_Randevu::wa_link('Merhaba, MST Yazar Paneli hakkında bilgi almak istiyorum.');

/** Çizgi simgeler (24px ızgara). */
$ik = function ($n, $boy = 22) {
    $d = [
        'rota'     => '<circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M12 19h4.5a3.5 3.5 0 0 0 0-7h-8a3.5 3.5 0 0 1 0-7H12"/>',
        'grafik'   => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
        'kutu'     => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
        'cuzdan'   => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18M16 15h2"/>',
        'megafon'  => '<path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1z"/><path d="M15 9a4 4 0 0 1 0 6M18 6a8 8 0 0 1 0 12"/>',
        'hedef'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'yildiz'   => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
        'kep'      => '<path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5M22 9v6"/>',
        'zil'      => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10 21a2 2 0 0 0 4 0"/>',
        'kisiler'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M22 20a7 7 0 0 0-4-6.3"/>',
        'kivilcim' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/>',
        'onay'     => '<path d="M20 6 9 17l-5-5"/>',
        'ok'       => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'telefon'  => '<rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/>',
        'kalem'    => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13 7 4 4"/>',
        'sayfa'    => '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
        'resim'    => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/>',
        'barkod'   => '<path d="M4 5v14M8 5v14M11 5v14M15 5v14M18 5v14M20 5v14"/>',
        'etiket'   => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
        'baski'    => '<path d="M7 9V3h10v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M7 14h10v7H7z"/>',
        'kamyon'   => '<path d="M2 6h12v10H2zM14 10h4l4 4v2h-8"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
    ];
    return '<svg class="uyg-ic" width="' . (int) $boy . '" height="' . (int) $boy . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($d[$n] ?? '') . '</svg>';
};
// Kitap perisi: bölüme girilince perinin pozu, söyleyeceği cümle ve yanına konacağı kutu
// $hedef: bölüm içindeki kutunun seçicisi (birden çok eşleşirse sonuncusu), $yer: tercih sırası
// (sag, sol: kutunun yanı; ust: üstü; kose-sag, kose-sol: üst köşesi) — sığmayan atlanır
$peri = function ($poz, $soz, $hedef = '', $yer = '') {
    return 'data-peri-poz="' . esc_attr($poz) . '" data-peri-soz="' . esc_attr($soz) . '"'
        . ($hedef ? ' data-peri-hedef="' . esc_attr($hedef) . '" data-peri-yer="' . esc_attr($yer) . '"' : '');
};

/** Madde listesi (altın onay işaretli). */
$liste = function (array $maddeler) use ($ik) {
    $h = '<ul class="uyg-liste">';
    foreach ($maddeler as $m) $h .= '<li>' . $ik('onay', 16) . '<span>' . esc_html($m) . '</span></li>';
    return $h . '</ul>';
};

$moduller = [
    ['#surec',    'rota',     'Yayın süreci'],
    ['#satis',    'grafik',   'Satış & stok'],
    ['#telif',    'cuzdan',   'Telif'],
    ['#tanitim',  'megafon',  'Reklam & tanıtım'],
    ['#kariyer',  'hedef',    'Kariyer & Akademi'],
    ['#asistan',  'kivilcim', 'AI Yazar Asistanı'],
    ['#topluluk', 'kisiler',  'Başarı & topluluk'],
];

$asamalar = [
    ['kalem', 'Editör', 'bitti'], ['sayfa', 'Mizanpaj', 'bitti'], ['resim', 'Kapak', 'simdi'],
    ['barkod', 'ISBN', ''], ['etiket', 'Bandrol', ''], ['baski', 'Baskı', ''], ['kamyon', 'Dağıtım', ''],
];

$sss = [
    ['MST Yazar Paneli\'ni kimler kullanabilir?', 'MST Yayıncılık ile kitabını yayımlayan yazarlarımız. Yayın süreciniz başladığında panele giriş bilgileriniz size iletilir.'],
    ['Telefonumda nasıl kullanırım?', 'Panel bir web uygulamasıdır; telefonunuzun tarayıcısından giriş yapabilirsiniz. Tarayıcı menüsündeki "Ana ekrana ekle" seçeneğiyle uygulama gibi tek dokunuşla açabilirsiniz.'],
    ['Giriş bilgilerimi bulamıyorum, ne yapmalıyım?', 'WhatsApp üzerinden bize yazın; ekibimiz hesabınızla ilgili yardımcı olur.'],
    ['Henüz MST yazarı değilim, paneli görebilir miyim?', 'Panel, yayın sürecindeki yazarlarımıza özeldir. Kitabınızı birlikte yayımlamak için ücretsiz ön görüşme randevusu alabilirsiniz.'],
];
MST_Randevu::seo_hazirla(); // arama başlığı/açıklaması (wp_head'den önce)
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1a1a1a">
    <!-- Sayfanın kendi karanlık teması var: tarayıcı renkleri kendisi ters çevirmesin -->
    <meta name="color-scheme" content="light dark">
    <script>
    /* Koyu tema yalnızca cihaz/tarayıcı karanlık istediğinde (karanlık mod ya da zorla karartma):
       sayfa çizilmeden önce uygulanır, cihaz ayarı değişince de güncellenir. */
    (function () {
        if (!window.matchMedia) return;
        var mq = matchMedia('(prefers-color-scheme: dark)'), kok = document.documentElement;
        var uygula = function () { kok.classList.toggle('mst-koyu', mq.matches); };
        uygula();
        if (mq.addEventListener) mq.addEventListener('change', uygula); else if (mq.addListener) mq.addListener(uygula);
    })();
    </script>
    <?php echo MST_Randevu::paylasim_meta('uygulama'); ?>
    <?php echo MST_Randevu::seo_uygulama($sss); ?>
    <script>document.documentElement.classList.add('uyg-js');</script>
    <?php wp_head(); ?>
    <?php
    // Güvence: bir eklenti stil dosyalarını kuyruktan düşürdüyse doğrudan ekle
    foreach (['mst-randevu' => 'randevu.css', 'mst-uygulama' => 'uygulama.css'] as $h => $dosya) {
        if (!wp_style_is($h, 'done')) {
            echo '<link rel="stylesheet" id="' . esc_attr($h) . '-yedek-css" href="' . esc_url(MST_RANDEVU_URL . 'assets/' . $dosya . '?ver=' . MST_RANDEVU_VER) . '">' . "\n";
        }
    }
    ?>
</head>
<body <?php body_class('mst-sayfa mst-uyg'); ?>>
<?php wp_body_open(); ?>
<?php echo MST_Randevu::header_html(true); ?>

<main class="uyg">

    <!-- ============ Giriş ============ -->
    <section class="uyg-hero" <?php echo $peri('goster', 'İşte Yazar Paneli: kitabınızla ilgili her şey bu ekranda.', '.uyg-tel', 'sag,kose-sag'); ?>>
        <div class="uyg-kap uyg-hero__in">
            <div class="uyg-hero__metin">
                <h1 aria-label="Kitabınızın tüm yolculuğu tek uygulamada."><span class="uyg-k" style="--i:0">Kitabınızın</span> <span class="uyg-k" style="--i:1">tüm</span> <span class="uyg-k" style="--i:2">yolculuğu</span> <em class="uyg-k uyg-parilti" style="--i:3">tek uygulamada.</em></h1>
                <p class="uyg-hero__alt uyg-gir" style="--d:.75s">Yayın sürecinden satışlara, telif kazancından kariyer planınıza kadar her şeyi anlık takip edin. 7/24 yapay zekâ destekli Yazar Asistanı her an yanınızda.</p>
                <div class="uyg-hero__cta uyg-gir" style="--d:.9s">
                    <a class="uyg-btn uyg-btn--altin" href="<?php echo esc_url($randevu); ?>">Ücretsiz Ön Görüşme Al <?php echo $ik('ok', 18); ?></a>
                    <?php if ($wa) : ?><a class="uyg-btn uyg-btn--cizgi" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener">WhatsApp'tan Bilgi Al</a><?php endif; ?>
                </div>
                <ul class="uyg-hero__cipler uyg-gir" style="--d:1.05s">
                    <li><?php echo $ik('telefon', 16); ?> Web'den ve telefondan</li>
                    <li><?php echo $ik('zil', 16); ?> Anlık bildirimler</li>
                    <li><?php echo $ik('kivilcim', 16); ?> 7/24 AI asistan</li>
                </ul>
                <?php // Panel yalnızca MST yazarlarına açık: giriş, deneme izlenimi vermeyecek küçük bir bağlantı ?>
                <p class="uyg-giris-link uyg-gir" style="--d:1.15s">Zaten MST yazarı mısınız? <a href="<?php echo esc_url($panel); ?>">Panele giriş</a></p>
            </div>

            <div class="uyg-hero__gorsel" aria-hidden="true" data-uyg-egim>
                <div class="uyg-tel">
                    <div class="uyg-tel__ekran">
                        <div class="uyg-tel__ust"><span>Merhaba, Yazarımız 👋</span><?php echo $ik('zil', 18); ?></div>
                        <div class="uyg-kart uyg-kart--koyu">
                            <small>Kitabınız</small>
                            <strong>Kitabınızın Adı</strong>
                            <div class="uyg-ilerleme"><span style="width:43%"></span></div>
                            <div class="uyg-kart__satir"><span>Aşama: <b>Kapak</b></span><span>3 / 7</span></div>
                        </div>
                        <div class="uyg-mini-ikili">
                            <div class="uyg-kart"><small>Toplam satış</small><strong data-uyg-say="1248">1.248</strong><em>+%12 bu ay</em></div>
                            <div class="uyg-kart"><small>Hak edilen telif</small><strong>₺ ••••</strong><em>Detayı gör</em></div>
                        </div>
                        <div class="uyg-kart">
                            <small>Bugün ne yapmalıyım?</small>
                            <div class="uyg-gorev uyg-gorev--bitti"><?php echo $ik('onay', 14); ?> Kapak önerisine yorum yap</div>
                            <div class="uyg-gorev"><i></i> Instagram'da ilk tanıtım gönderisi</div>
                        </div>
                    </div>
                </div>
                <div class="uyg-yuzen uyg-yuzen--1"><?php echo $ik('zil', 16); ?> <span data-uyg-canli><b>Yeni aşama:</b> Kapak tasarımı başladı</span></div>
                <div class="uyg-yuzen uyg-yuzen--2"><?php echo $ik('yildiz', 16); ?> <span><b>Rozet kazandınız:</b> İlk 30 gün</span></div>
            </div>
        </div>
    </section>

    <!-- ============ Modül şeridi ============ -->
    <nav class="uyg-seritler" aria-label="Özellikler">
        <div class="uyg-kap">
            <?php foreach ($moduller as $m) : ?>
                <a href="<?php echo esc_attr($m[0]); ?>"><?php echo $ik($m[1], 18); ?><span><?php echo esc_html($m[2]); ?></span></a>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- ============ 1. Yayın süreci ============ -->
    <section class="uyg-bolum" id="surec" <?php echo $peri('goster', 'Editörden baskıya, kitabınızın her adımını buradan anlık izlersiniz.', '.uyg-rota li', 'sag,ust,kose-sag'); ?>>
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('rota', 18); ?> Yayın süreci</span>
                <h2>Kitabınız hangi aşamada? Artık hep biliyorsunuz.</h2>
                <p>Editörden dağıtıma kadar her adımı anlık görün; yeni bir aşamaya geçildiğinde bildirim alın.</p>
            </header>
            <ol class="uyg-rota">
                <?php foreach ($asamalar as $i => $a) : ?>
                    <li class="<?php echo $a[2] ? 'is-' . esc_attr($a[2]) : ''; ?>">
                        <span class="uyg-rota__ic"><?php echo $a[2] === 'bitti' ? $ik('onay', 20) : $ik($a[0], 20); ?></span>
                        <strong><?php echo esc_html($a[1]); ?></strong>
                        <small><?php echo $a[2] === 'bitti' ? 'Tamamlandı' : ($a[2] === 'simdi' ? 'Devam ediyor' : 'Sırada'); ?></small>
                    </li>
                <?php endforeach; ?>
            </ol>
            <?php echo $liste([
                'Kitabınızın hangi aşamada olduğunu anlık takip edin',
                'Editör, mizanpaj, kapak, ISBN, bandrol, baskı ve dağıtım süreçlerini görüntüleyin',
                'Yayın sürecindeki yeni aşamalardan ve önemli gelişmelerden bildirimle haberdar olun',
            ]); ?>
        </div>
    </section>

    <!-- ============ 2. Satış & stok ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="satis" <?php echo $peri('goster', 'D&R\'dan Trendyol\'a, hangi mağazada kaç adet sattığınız tek ekranda!', '.uyg-panel', 'kose-sag'); ?>>
        <div class="uyg-kap uyg-iki">
            <div>
                <header class="uyg-baslik uyg-baslik--sol">
                    <span class="uyg-ust"><?php echo $ik('grafik', 18); ?> Satış, rapor & stok</span>
                    <h2>Satışlarınız nerede, ne kadar? Tek bakışta.</h2>
                    <p>Toplam satışınızı, hangi platformdan geldiğini ve stok durumunu kendiniz görün.</p>
                </header>
                <?php echo $liste([
                    'Toplam kitap satışını görüntüleyin',
                    'Satışların hangi platformlardan geldiğini takip edin',
                    'D&R, Kitapyurdu, Trendyol, Hepsiburada, n11 ve web sitemizdeki satış performansını izleyin',
                    'Günlük, aylık ve dönemsel satış raporlarına ulaşın',
                    'Kitabınızın güncel stok durumunu takip edin',
                    'Dağıtıma çıkan ve kalan kitap adetlerini görüntüleyin',
                ]); ?>
            </div>
            <div class="uyg-panel" aria-hidden="true">
                <div class="uyg-panel__ust"><strong>Satış raporu</strong><span class="uyg-sekmeler"><b>Aylık</b><i>Günlük</i><i>Dönemsel</i></span></div>
                <div class="uyg-cubuklar">
                    <?php foreach ([['Oca', 38], ['Şub', 52], ['Mar', 47], ['Nis', 66], ['May', 58], ['Haz', 82]] as $c) : ?>
                        <div><span style="height:<?php echo (int) $c[1]; ?>%"></span><small><?php echo esc_html($c[0]); ?></small></div>
                    <?php endforeach; ?>
                </div>
                <div class="uyg-kanallar">
                    <?php foreach ([['D&R', 24], ['Kitapyurdu', 21], ['Trendyol', 17], ['Web sitemiz', 15], ['Hepsiburada', 13], ['n11', 10]] as $k) : ?>
                        <div><span><?php echo esc_html($k[0]); ?></span><div class="uyg-ilerleme"><span style="width:<?php echo (int) $k[1]; ?>%"></span></div><b>%<?php echo (int) $k[1]; ?></b></div>
                    <?php endforeach; ?>
                </div>
                <div class="uyg-stok">
                    <div><small>Dağıtımda</small><strong>640</strong></div>
                    <div><small>Depoda kalan</small><strong>360</strong></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ 3. Telif ============ -->
    <section class="uyg-bolum" id="telif" <?php echo $peri('sevinc', 'Hak edişleriniz ve ödemeleriniz burada; her kuruşu görürsünüz.', '.uyg-panel', 'kose-sol,kose-sag'); ?>>
        <div class="uyg-kap uyg-iki uyg-iki--ters">
            <div>
                <header class="uyg-baslik uyg-baslik--sol">
                    <span class="uyg-ust"><?php echo $ik('cuzdan', 18); ?> Telif kazancı</span>
                    <h2>Emeğinizin karşılığı, şeffaf ve güncel.</h2>
                    <p>Hak ettiğiniz telifi, geçmiş ödemelerinizi ve satış hareketlerinizi dilediğiniz an görün.</p>
                </header>
                <?php echo $liste([
                    'Telif kazancınızı takip edin',
                    'Hak edilen telif tutarlarını görüntüleyin',
                    'Geçmiş telif ödemelerinize ulaşın',
                    'Sipariş ve satış hareketlerini görüntüleyin',
                ]); ?>
            </div>
            <div class="uyg-panel" aria-hidden="true">
                <div class="uyg-telif">
                    <small>Hak edilen telif</small>
                    <strong>₺ ••••••</strong>
                    <span>Bir sonraki ödeme dönemi</span>
                </div>
                <div class="uyg-hareketler">
                    <div><span>Telif ödemesi · 2. dönem</span><em>Ödendi</em></div>
                    <div><span>Satış hareketi · Trendyol</span><em class="gri">+31 adet</em></div>
                    <div><span>Satış hareketi · Kitapyurdu</span><em class="gri">+24 adet</em></div>
                    <div><span>Satış hareketi · Web sitemiz</span><em class="gri">+12 adet</em></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ 4. Reklam & tanıtım ============ -->
    <section class="uyg-bolum uyg-bolum--acik" id="tanitim" <?php echo $peri('goster', 'Kitabınız için yapılan her tanıtım çalışması önünüzde.', '.uyg-panel', 'kose-sag'); ?>>
        <div class="uyg-kap uyg-iki">
            <div>
                <header class="uyg-baslik uyg-baslik--sol">
                    <span class="uyg-ust"><?php echo $ik('megafon', 18); ?> Reklam & tanıtım</span>
                    <h2>Kitabınız için yapılan her çalışma önünüzde.</h2>
                    <p>Sosyal medya ve reklam çalışmalarını, kampanya sonuçlarını takip edin.</p>
                </header>
                <?php echo $liste([
                    'Reklam ve tanıtım çalışmalarını takip edin',
                    'Kitabınız için hazırlanan sosyal medya ve reklam çalışmalarını görüntüleyin',
                    'Kampanya sonuçlarını ve performans verilerini izleyin',
                ]); ?>
            </div>
            <div class="uyg-panel" aria-hidden="true">
                <div class="uyg-kampanya">
                    <div class="uyg-kampanya__gorsel"><?php echo $ik('resim', 28); ?></div>
                    <div><strong>Tanıtım kampanyası</strong><small>Instagram · Reels</small><span class="uyg-durum">Yayında</span></div>
                </div>
                <div class="uyg-metrikler">
                    <div><small>Erişim</small><strong>—</strong></div>
                    <div><small>Etkileşim</small><strong>—</strong></div>
                    <div><small>Tıklama</small><strong>—</strong></div>
                </div>
                <p class="uyg-panel__not">Kampanyanız yayına girdiğinde sonuçlar burada görünür.</p>
            </div>
        </div>
    </section>

    <!-- ============ 5. Kariyer & Akademi ============ -->
    <section class="uyg-bolum" id="kariyer" <?php echo $peri('dusun', '“Bugün ne yapmalıyım?” diye düşünmeyin; sıradaki adımınız hazır.', '.uyg-ozellik', 'kose-sag'); ?>>
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('hedef', 18); ?> Kariyer planı & Yazar Kariyer Akademisi</span>
                <h2>“Bugün ne yapmalıyım?” sorusunun cevabı hazır.</h2>
                <p>Size özel görevler, 30/60/90 günlük kariyer planı ve uygulamanın içinde eğitimler.</p>
            </header>
            <div class="uyg-kartlar">
                <article class="uyg-ozellik">
                    <span class="uyg-ozellik__ic"><?php echo $ik('onay', 22); ?></span>
                    <h3>Günlük ve haftalık görevler</h3>
                    <p>Kendinize özel görevleri görün, “Bugün ne yapmalıyım?” yönlendirmeleriyle adım adım ilerleyin.</p>
                </article>
                <article class="uyg-ozellik">
                    <span class="uyg-ozellik__ic"><?php echo $ik('hedef', 22); ?></span>
                    <h3>30 / 60 / 90 günlük plan</h3>
                    <p>İlk 30, 60 ve 90 gününüz için hazırlanan yazar kariyer planını takip edin.</p>
                    <div class="uyg-plan" aria-hidden="true"><span class="bitti">30</span><span class="simdi">60</span><span>90</span></div>
                </article>
                <article class="uyg-ozellik">
                    <span class="uyg-ozellik__ic"><?php echo $ik('kep', 22); ?></span>
                    <h3>Yazar Kariyer Akademisi</h3>
                    <p>Sosyal medya, içerik üretimi, kişisel marka ve kitap pazarlaması eğitimlerine uygulamadan ulaşın.</p>
                </article>
                <article class="uyg-ozellik">
                    <span class="uyg-ozellik__ic"><?php echo $ik('zil', 22); ?></span>
                    <h3>Eğitim takvimi</h3>
                    <p>Yaklaşan eğitimleri ve tamamladığınız eğitimleri tek yerde görün.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ============ 6. AI Yazar Asistanı ============ -->
    <section class="uyg-bolum uyg-bolum--koyu" id="asistan" <?php echo $peri('goz-kirp', 'Gece yarısı bir fikir mi lazım? Asistanınız hep uyanık.', '.uyg-sohbet', 'kose-sag'); ?>>
        <div class="uyg-kap uyg-iki">
            <div>
                <header class="uyg-baslik uyg-baslik--sol">
                    <span class="uyg-ust"><?php echo $ik('kivilcim', 18); ?> 7/24 Yapay Zekâ Yazar Asistanı</span>
                    <h2>Gece yarısı bir fikir mi lazım? Asistanınız uyanık.</h2>
                    <p>Kitap tanıtımından sosyal medya içeriğine, yazar markanızdan yayın sürecine kadar sorularınıza anında yanıt.</p>
                </header>
                <?php echo $liste([
                    'Kitap tanıtımı hakkında yapay zekâ desteği alın',
                    'Sosyal medya içerik fikirleri alın',
                    'Reels, gönderi ve tanıtım metni fikirleri oluşturun',
                    'Yazar markanızı geliştirmek için öneriler alın',
                    'Kitabınızı daha fazla kişiye ulaştırmak için yönlendirme alın',
                    'Yayıncılık süreciyle ilgili sorularınıza hızlı yanıt alın',
                ]); ?>
            </div>
            <div class="uyg-sohbet" aria-hidden="true">
                <div class="uyg-sohbet__ust"><span class="uyg-sohbet__avatar"><?php echo $ik('kivilcim', 18); ?></span><div><strong>Yazar Asistanı</strong><small>Çevrim içi · 7/24</small></div></div>
                <div class="uyg-balon uyg-balon--ben">Romanım için bu hafta hangi Reels'i çekmeliyim?</div>
                <div class="uyg-balon">Üç fikir hazırladım ✨<br>1. “İlk cümle” — kitabın ilk satırını sesli okuyun.<br>2. Kapak tasarımının öncesi/sonrası.<br>3. Yazarken dinlediğiniz şarkıyla 15 saniyelik masa başı çekimi.</div>
                <div class="uyg-balon uyg-balon--ben">İkincisi için açıklama yazar mısın?</div>
                <div class="uyg-balon uyg-balon--yaziyor"><i></i><i></i><i></i></div>
            </div>
        </div>
    </section>

    <!-- ============ 7. Başarı & topluluk ============ -->
    <section class="uyg-bolum" id="topluluk" <?php echo $peri('sevinc', 'Diğer MST yazarlarıyla tanışın, birlikte büyüyün!', '.uyg-rozetler', 'kose-sag'); ?>>
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('kisiler', 18); ?> Başarı & topluluk</span>
                <h2>Yalnız yazmıyorsunuz.</h2>
                <p>Görevleri tamamladıkça rozet kazanın, MST yazar topluluğuyla bağlantıda kalın.</p>
            </header>
            <div class="uyg-rozetler" aria-hidden="true">
                <?php foreach ([['yildiz', 'İlk 30 gün'], ['megafon', 'İlk tanıtım'], ['kep', 'Akademi mezunu'], ['kisiler', 'Topluluk üyesi']] as $r) : ?>
                    <div><span><?php echo $ik($r[0], 26); ?></span><small><?php echo esc_html($r[1]); ?></small></div>
                <?php endforeach; ?>
            </div>
            <div class="uyg-kartlar">
                <article class="uyg-ozellik">
                    <span class="uyg-ozellik__ic"><?php echo $ik('yildiz', 22); ?></span>
                    <h3>Başarı ve gelişim</h3>
                    <p>Gelişiminizi takip edin; görevleri tamamladıkça rozetler ve başarılar kazanın.</p>
                </article>
                <article class="uyg-ozellik">
                    <span class="uyg-ozellik__ic"><?php echo $ik('kisiler', 22); ?></span>
                    <h3>MST yazar topluluğu</h3>
                    <p>Topluluğa erişin, diğer yazarlarla iletişim ve etkileşim kurun.</p>
                </article>
                <article class="uyg-ozellik">
                    <span class="uyg-ozellik__ic"><?php echo $ik('megafon', 22); ?></span>
                    <h3>Duyurular ve fırsatlar</h3>
                    <p>MST duyurularını ve yazarlara özel fırsatları kaçırmayın.</p>
                </article>
                <article class="uyg-ozellik">
                    <span class="uyg-ozellik__ic"><?php echo $ik('zil', 22); ?></span>
                    <h3>Etkinlik bildirimleri</h3>
                    <p>Eğitim, etkinlik, seminer ve toplantılardan bildirimle haberdar olun.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ============ Tek uygulama ============ -->
    <section class="uyg-bolum uyg-bolum--acik" <?php echo $peri('goster', 'Başlamak çok kolay: üç adımda panelinizdesiniz.', '.uyg-adimlar li', 'kose-sag'); ?>>
        <div class="uyg-kap">
            <header class="uyg-baslik">
                <span class="uyg-ust"><?php echo $ik('telefon', 18); ?> Nasıl başlanır?</span>
                <h2>Tüm kitap ve kariyer süreciniz tek uygulamada.</h2>
            </header>
            <ol class="uyg-adimlar">
                <li><span>1</span><h3>Yayın sürecinizi başlatın</h3><p>MST Yayıncılık ile kitabınızın yayın yolculuğuna başlayın.</p></li>
                <li><span>2</span><h3>Giriş bilgilerinizi alın</h3><p>Hesabınız açılır, giriş bilgileriniz size iletilir.</p></li>
                <li><span>3</span><h3>Panelinize girin</h3><p>Telefonunuzun ana ekranına ekleyin; kitabınız artık cebinizde.</p></li>
            </ol>
        </div>
    </section>

    <!-- ============ SSS ============ -->
    <section class="uyg-bolum" id="sss" <?php echo $peri('dusun', 'Aklınıza takılan bir şey mi var? Cevaplar burada.', '.uyg-sss', 'sag,kose-sag'); ?>>
        <div class="uyg-kap uyg-dar">
            <header class="uyg-baslik"><h2>Sık sorulan sorular</h2></header>
            <div class="uyg-sss">
                <?php foreach ($sss as $s) : ?>
                    <details><summary><?php echo esc_html($s[0]); ?></summary><p><?php echo esc_html($s[1]); ?></p></details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ Kapanış ============ -->
    <section class="uyg-kapanis" <?php echo $peri('goz-kirp', 'Hadi, ilk adımı atın; gerisini birlikte hallederiz!', '.uyg-kapanis__cta', 'kose-sag'); ?>>
        <div class="uyg-kap uyg-kapanis__in">
            <div>
                <h2>Kitabınızın yolculuğunu birlikte yönetelim.</h2>
                <p>Kitabınızı yayımlamayı düşünüyorsanız ücretsiz ön görüşmede yol haritanızı birlikte çıkaralım. MST yazarı olduğunuzda Yazar Paneli'niz size açılır.</p>
            </div>
            <div class="uyg-kapanis__cta">
                <a class="uyg-btn uyg-btn--altin" href="<?php echo esc_url($randevu); ?>">Ücretsiz Ön Görüşme Al <?php echo $ik('ok', 18); ?></a>
                <?php if ($wa) : ?><a class="uyg-kapanis__wa" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo MST_Randevu::icon('wa'); ?> Sorunuz mu var? WhatsApp'tan yazın</a><?php endif; ?>
                <p class="uyg-giris-link">Zaten MST yazarı mısınız? <a href="<?php echo esc_url($panel); ?>">Panele giriş</a></p>
            </div>
        </div>
    </section>

    <?php
    // Sayfa düzenleyicide içerik yazıldıysa (ör. duyuru), sayfanın en sonunda gösterilir
    while (have_posts()) {
        the_post();
        $icerik = trim(get_the_content());
        if ($icerik !== '') echo '<section class="uyg-bolum"><div class="uyg-kap uyg-dar uyg-icerik">' . apply_filters('the_content', $icerik) . '</div></section>';
    }
    ?>
</main>

<!-- Kitap perisi: kaydırdıkça bölümleri tanıtan maskot (uygulama.js) -->
<canvas class="uyg-peri-iz" aria-hidden="true"></canvas>
<div class="uyg-peri" data-uyg-peri hidden>
    <div class="uyg-peri__balon" role="status" aria-live="polite">
        <p data-uyg-peri-soz></p>
        <div class="uyg-peri__secim" hidden>
            <button type="button" class="uyg-peri__cevap uyg-peri__cevap--evet" data-peri-cevap="evet">Evet, lütfen</button>
            <button type="button" class="uyg-peri__cevap" data-peri-cevap="hayir">Hayır, teşekkürler</button>
        </div>
        <button type="button" class="uyg-peri__kapat" aria-label="Mesajı kapat" data-uyg-peri-balon-kapat>&times;</button>
    </div>
    <button type="button" class="uyg-peri__govde" aria-label="Kitap perisi: mesajı göster">
        <span class="uyg-peri__ic">
        <?php
        // Her poz: kanat katmanı + gövde (ana kare, göz kırpma, el/asa sallamanın ikinci karesi).
        // yildiz: asanın yıldızının görüntüdeki yeri (%), iki kare için — ışıltı buradan saçılır
        $pozlar = [
            'selam'    => ['kirp' => true,  'yildiz' => '23.4,47.2;23.4,47.2'],
            'goster'   => ['kirp' => true,  'yildiz' => '6.3,43.4;9.0,32.6'],
            'dusun'    => ['kirp' => true,  'yildiz' => '86.1,55.1;78.6,50.9'],
            'sevinc'   => ['kirp' => false, 'yildiz' => '80.8,13.3;73.2,18.3'],
            'goz-kirp' => ['kirp' => true,  'yildiz' => '88.6,33.6;76.2,16.2'],
        ];
        $i = 0;
        foreach ($pozlar as $p => $bilgi) :
            $yol = MST_RANDEVU_URL . 'assets/peri/' . $p;
            $kare = function ($ek, $sinif) use ($yol) {
                return '<img class="' . $sinif . '" src="' . esc_url($yol . $ek . '.webp?ver=' . MST_RANDEVU_VER) . '" alt="" width="180" height="130" decoding="async" fetchpriority="low">';
            }; ?>
            <span class="uyg-peri__poz<?php echo $i++ === 0 ? ' is-aktif' : ''; ?>" data-poz="<?php echo esc_attr($p); ?>" data-yildiz="<?php echo esc_attr($bilgi['yildiz']); ?>">
                <?php
                echo $kare('-kanat', 'uyg-peri__kanat');
                echo $kare('-govde', 'uyg-peri__beden uyg-peri__beden--ana');
                if ($bilgi['kirp']) echo $kare('-kirp', 'uyg-peri__beden uyg-peri__beden--kirp');
                echo $kare('-govde2', 'uyg-peri__beden uyg-peri__beden--2');
                ?>
            </span>
        <?php endforeach; ?>
        </span>
    </button>
    <button type="button" class="uyg-peri__mesaj" aria-label="Kitap perisinin mesajını oku" data-uyg-peri-ac><span></span><span></span><span></span></button>
    <button type="button" class="uyg-peri__x" aria-label="Kitap perisini kapat" title="Periyi kapat" data-uyg-peri-kapat>&times;</button>
</div>
<button type="button" class="uyg-peri-cagir" data-uyg-peri-cagir hidden aria-label="Kitap perisini çağır" title="Kitap perisini çağır">
    <img src="<?php echo esc_url(MST_RANDEVU_URL . 'assets/peri/selam-govde.webp?ver=' . MST_RANDEVU_VER); ?>" alt="" width="64" height="46" loading="lazy" decoding="async">
</button>

<?php wp_footer(); ?>
</body>
</html>
