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
    <meta name="color-scheme" content="light dark" id="mst-renk-semasi">
    <script>
    /* Açık/koyu görünüm: ziyaretçi üst çubuktaki güneş/ay düğmesiyle seçtiyse o, seçmediyse cihazın ayarı.
       Sayfa çizilmeden önce uygulanır ki açılışta renk sıçraması olmasın. */
    (function () {
        var secim = null;
        try { secim = localStorage.getItem('mst-tema'); } catch (e) {}
        var koyu = secim ? secim === 'koyu' : !!(window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('mst-koyu', koyu);
        if (secim) document.getElementById('mst-renk-semasi').content = koyu ? 'dark' : 'only light';
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
<?php echo MST_Randevu::header_html(false, null, null, true); ?>

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
