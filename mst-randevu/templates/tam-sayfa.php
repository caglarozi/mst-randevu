<?php
/**
 * "MST Randevu (Tam Sayfa)" şablonu: temanın üst kısmı yerine MST çubuğu (logo, WhatsApp, Siteye Git).
 * Sayfa içeriği yazılmışsa randevu kutusunun üstünde gösterilir.
 */
if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1a1a1a">
    <!-- Sayfanın kendi karanlık teması var: tarayıcı zorla karartmasın -->
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
    <?php echo MST_Randevu::paylasim_meta('randevu'); ?>
    <?php wp_head(); ?>
    <?php
    // Güvence: bir eklenti stil dosyasını kuyruktan düşürdüyse doğrudan ekle
    if (!wp_style_is('mst-randevu', 'done')) {
        echo '<link rel="stylesheet" id="mst-randevu-yedek-css" href="' . esc_url(MST_RANDEVU_URL . 'assets/randevu.css?ver=' . MST_RANDEVU_VER) . '">' . "\n";
    }
    ?>
</head>
<body <?php body_class('mst-sayfa mst-sayfa--randevu'); ?>>
<?php wp_body_open(); ?>
<?php echo MST_Randevu::header_html(); ?>

<main class="mst-sayfa__main">
    <?php
    while (have_posts()) {
        the_post();
        $icerik = trim(get_the_content());
        if ($icerik !== '' && !has_shortcode($icerik, 'mst_randevu')) {
            echo '<div class="mst-sayfa__intro">' . apply_filters('the_content', $icerik) . '</div>';
        }
    }
    echo MST_Randevu::shortcode();
    ?>
</main>

<?php wp_footer(); ?>
</body>
</html>
