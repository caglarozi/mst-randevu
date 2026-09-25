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
    <!-- Normal tarayıcılarda sayfa hep açık renkte açılır ve zorla karartılmaz ("only light").
         Bu talimatı dinlemeyip sayfayı zorla karartan tarayıcılarda (Samsung İnternet, Xiaomi, Huawei,
         Oppo, Opera) karanlık moddayken kendi koyu temamız devreye girer; böylece renkler bozulmaz. -->
    <meta name="color-scheme" content="only light" id="mst-renk-semasi">
    <script>
    (function () {
        var zorlayan = /SamsungBrowser|MiuiBrowser|HuaweiBrowser|HeyTapBrowser|\bOPR\//.test(navigator.userAgent || '');
        if (!zorlayan || !window.matchMedia) return;
        document.getElementById('mst-renk-semasi').content = 'light dark';
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
        echo '<link rel="stylesheet" id="mst-randevu-yedek-css" href="' . esc_url(MST_Randevu::varlik('randevu.css')) . '">' . "\n";
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
