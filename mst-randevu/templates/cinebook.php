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
    ['#nedir', 'CineBook nedir?'], ['#surec', 'Süreç'], ['#projeler', 'Projeler'], ['#cocuk', 'MST Çocuk'],
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
    ['Fragman yapımı', 'Görsel tasarım, kurgu, seslendirme ve müzik bir araya gelir.'],
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
    <script>document.documentElement.classList.add('uyg-js');</script>
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

    <!-- ============ Giriş ============ -->
    <section class="cb-giris" id="giris">
        <div class="uyg-kap cb-giris__kap">
            <div class="cb-giris__metin">
                <p class="cb-ust">MST Yayıncılık · CineBook</p>
                <h1>Kitabınız ekranda <em>hayat bulsun.</em></h1>
                <p class="cb-giris__alt">CineBook, kitapları fragmana, kısa dijital anlatılara ve sosyal medya içeriklerine dönüştüren MST Yayıncılık yapım birimidir. MST Çocuk ise çocuk kitaplarını çizgi filme uyarlar.</p>
                <div class="cb-giris__yollar">
                    <a class="cb-yol" href="#basvuru" data-cb-tur="cinebook"><small>Kitabım için</small><strong>CineBook başvurusu</strong></a>
                    <a class="cb-yol cb-yol--cocuk" href="#cocuk"><small>Çocuk kitabım için</small><strong>MST Çocuk</strong></a>
                </div>
            </div>
            <div class="cb-giris__sahne">
                <div class="cb-serit">
                    <?php echo $oynatici($fragman, $o['fragman_ad'] ?: 'Öne çıkan fragman', 'Şimdi izle · ' . ($o['fragman_ad'] ?: 'Fragman') . ' — İlk fragman', 'cb-video--genis'); ?>
                </div>
                <p class="cb-giris__imza"><span>Şimdi izle</span> <?php echo esc_html($o['fragman_ad'] ?: 'Öne çıkan fragman'); ?> — İlk fragman</p>
            </div>
        </div>
        <div class="uyg-kap">
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

    <!-- ============ CineBook nedir? ============ -->
    <section class="cb-bolum cb-bolum--koyu" id="nedir">
        <div class="uyg-kap cb-iki">
            <header class="cb-bas">
                <p class="cb-ust">CineBook nedir?</p>
                <h2>Kitabın görünür hâli.</h2>
                <p>Bir kitabın okura ulaşmasının yolu artık yalnızca raflardan geçmiyor. CineBook, eserin hikâyesini kısa ve etkileyici görsel anlatılarla izleyiciye taşır; izleyiciyi kitabın okuruna dönüştürür.</p>
            </header>
            <ol class="cb-liste">
                <?php foreach ($nedir as $i => $n) : ?>
                    <li><span><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span><div><h3><?php echo esc_html($n[0]); ?></h3><p><?php echo esc_html($n[1]); ?></p></div></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ Süreç ============ -->
    <section class="cb-bolum cb-bolum--siyah" id="surec">
        <div class="uyg-kap">
            <header class="cb-bas cb-bas--orta">
                <p class="cb-ust">Süreç</p>
                <h2>Kitaptan ekrana dört adım</h2>
            </header>
            <ol class="cb-film">
                <?php foreach ($surec as $i => $s) : ?>
                    <li><span class="cb-film__no"><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span><h3><?php echo esc_html($s[0]); ?></h3><p><?php echo esc_html($s[1]); ?></p></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ Projeler ============ -->
    <section class="cb-bolum cb-bolum--koyu" id="projeler">
        <div class="uyg-kap">
            <header class="cb-bas cb-bas--orta">
                <p class="cb-ust">Projeler</p>
                <h2>Öne çıkan hikâyeler</h2>
            </header>
            <div class="cb-projeler">
                <article class="cb-proje">
                    <div class="cb-afis"><span class="cb-afis__ust">CineBook sunar</span><strong><?php echo esc_html($o['fragman_ad'] ?: 'Gökbörü'); ?></strong><span class="cb-afis__alt">Afiş eklenecek</span></div>
                    <div class="cb-proje__alt"><p><span class="cb-rozet">Yayında</span> İlk fragman</p><a href="#giris">İzle</a></div>
                </article>
                <article class="cb-proje">
                    <div class="cb-afis cb-afis--yakinda"><span class="cb-afis__ust">Yapım aşamasında</span><strong>Yakında</strong><span class="cb-afis__alt">Proje adı eklenecek</span></div>
                    <div class="cb-proje__alt"><p><span class="cb-rozet cb-rozet--gri">Yakında</span> CineBook’ta</p></div>
                </article>
                <a class="cb-proje cb-proje--davet" href="#basvuru" data-cb-tur="cinebook">
                    <span>Sıradaki hikâye</span><strong>sizinki olabilir.</strong><em>Başvurun</em>
                </a>
            </div>
        </div>
    </section>

    <!-- ============ MST Çocuk ============ -->
    <section class="cb-cocuk" id="cocuk">
        <svg class="cb-dalga" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0,0 H1440 V38 C1260,82 1080,82 900,52 C720,22 540,22 360,52 C220,76 100,70 0,44 Z" fill="#0f0f12"/></svg>
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
    <section class="cb-bolum cb-bolum--siyah" id="basvuru">
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
    <section class="cb-bolum cb-bolum--koyu" id="sss">
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
                <span><strong>MST Yayıncılık</strong><small>CineBook · MST Çocuk</small></span>
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
