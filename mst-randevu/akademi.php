<?php
/**
 * MST Yayıncılık Yazar Kariyer Akademisi: bilgi sayfası şablonu ve ücretsiz eğitim / dönem ayarları.
 * Sayfada başvuru formu yoktur; iletişim WhatsApp ve ön görüşme randevusu üzerinden yürür.
 * Üst çubuk ve renkler randevu / Yazar Paneli sayfalarıyla aynıdır.
 */
if (!defined('ABSPATH')) {
    exit;
}

class MST_Akademi
{
    const SABLON = 'mst-akademi-tam-sayfa';
    const OPT    = 'mst_akademi_ayarlar';

    /** Programlar: anahtar => [ad, kısa ad] */
    const PROGRAMLAR = [
        'temel'     => ['Yazar Akademisi Temel Programı', 'Temel Program'],
        'marka'     => ['Yazar Marka ve Görünürlük Programı', 'Marka ve Görünürlük'],
        'mentorluk' => ['Yazar Kariyer Mentorluk Programı', 'Kariyer Mentorluk'],
    ];

    public static function init()
    {
        add_filter('theme_page_templates', [__CLASS__, 'page_templates']);
        add_filter('template_include', [__CLASS__, 'template_include'], 100);
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 20);
        add_action('wp_enqueue_scripts', [__CLASS__, 'isolate_styles'], 9999);
        add_action('admin_menu', [__CLASS__, 'admin_menu'], 20);
        add_action('admin_post_mst_akademi_ayar', [__CLASS__, 'handle_ayar']);
        add_action('save_post_page', [__CLASS__, 'sayfa_kaydedildi']);
    }

    /** Akademi sayfası yayımlanınca/değişince önbellek temizlenir: randevu onay ekranındaki Akademi kartı hemen çıksın. */
    public static function sayfa_kaydedildi($id)
    {
        if (wp_is_post_revision($id) || wp_is_post_autosave($id)) return;
        if (get_page_template_slug($id) === self::SABLON) MST_Randevu::onbellek_temizle();
    }

    /* ------------------------------------------------------------------ */
    /*  Ayarlar: ücretsiz eğitim ve dönem                                  */
    /* ------------------------------------------------------------------ */

    public static function defaults()
    {
        return [
            'egitim_tarihi' => '',   // ücretsiz eğitimin tarihi (Y-m-d H:i); geçmişse "yeni tarih yakında"
            'egitim_yeri'   => 'Çevrim içi, canlı',
            'egitim_suresi' => '',
            'donem_tarihi'  => '',   // bir sonraki program dönemi başlangıcı (Y-m-d)
        ];
    }

    public static function opts()
    {
        return wp_parse_args((array) get_option(self::OPT, []), self::defaults());
    }

    /** Ücretsiz eğitim: tarih ileride ise [zaman damgası, metin], değilse null. */
    public static function ucretsiz_egitim()
    {
        $o = self::opts();
        if (!$o['egitim_tarihi']) return null;
        try {
            $t = new DateTime($o['egitim_tarihi'], wp_timezone());
        } catch (Exception $e) {
            return null;
        }
        if ($t->getTimestamp() <= time()) return null;
        return [$t->getTimestamp(), self::tr_tarih($t->getTimestamp(), true)];
    }

    public static function donem()
    {
        $o = self::opts();
        if (!$o['donem_tarihi']) return null;
        $ts = strtotime($o['donem_tarihi'] . ' 00:00:00');
        return ($ts && $ts > time() - DAY_IN_SECONDS) ? self::tr_tarih($ts) : null;
    }

    /** Site dili ne olursa olsun Türkçe tarih: "4 Ekim 2026 Pazar, 20:30". */
    public static function tr_tarih($ts, $saat = false)
    {
        $aylar  = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        $gunler = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        $d = (new DateTime('@' . $ts))->setTimezone(wp_timezone());
        $m = $d->format('j') . ' ' . $aylar[(int) $d->format('n') - 1] . ' ' . $d->format('Y');
        return $saat ? $m . ' ' . $gunler[(int) $d->format('w')] . ', ' . $d->format('H:i') : $m;
    }

    /* ------------------------------------------------------------------ */
    /*  Şablon ve dosyalar                                                 */
    /* ------------------------------------------------------------------ */

    public static function is_page()
    {
        return is_page() && get_page_template_slug() === self::SABLON;
    }

    public static function url()
    {
        $p = get_posts(['post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => self::SABLON, 'fields' => 'ids']);
        return $p ? get_permalink($p[0]) : '';
    }

    public static function page_templates($templates)
    {
        $templates[self::SABLON] = 'MST Yazar Kariyer Akademisi (Tam Sayfa)';
        return $templates;
    }

    public static function template_include($template)
    {
        if (!self::is_page()) return $template;
        foreach (['LITESPEED_NO_OPTM', 'DONOTROCKETOPTIMIZE', 'DONOTMINIFYCSS', 'DONOTMINIFYJS'] as $sabit) {
            if (!defined($sabit)) define($sabit, true);
        }
        return __DIR__ . '/templates/akademi.php';
    }

    public static function assets()
    {
        if (!self::is_page()) return;
        wp_register_style('mst-akademi', MST_Randevu::varlik('akademi.css'), ['mst-uygulama'], null);
        wp_register_script('mst-akademi', MST_Randevu::varlik('akademi.js'), [], null, true);
        // Yalnızca bu sayfada: daktilo satırı ve bilet için IBM Plex Mono, bilet başlığı için Cormorant Garamond italik
        wp_enqueue_style('mst-akademi-font', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,600&family=IBM+Plex+Mono:wght@500&display=swap', [], null);
        wp_enqueue_style('mst-randevu-font');
        wp_enqueue_style('mst-akademi');
        wp_enqueue_script('mst-randevu');
        wp_enqueue_script('mst-uygulama');
        wp_enqueue_script('mst-akademi');
    }

    public static function isolate_styles()
    {
        if (!self::is_page()) return;
        $keep = ['mst-randevu', 'mst-randevu-font', 'mst-akademi-font', 'mst-uygulama', 'mst-akademi', 'admin-bar', 'dashicons'];
        foreach (wp_styles()->queue as $handle) {
            if (!in_array($handle, $keep, true)) wp_dequeue_style($handle);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Yönetim: Yazar Randevu → Akademi Ayarları                          */
    /* ------------------------------------------------------------------ */

    public static function admin_menu()
    {
        add_submenu_page('mst-randevu', 'Akademi Ayarları', 'Akademi Ayarları', 'manage_options', 'mst-akademi', [__CLASS__, 'admin_page']);
    }

    public static function handle_ayar()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_akademi_ayar');
        $o = self::opts();
        $t = sanitize_text_field(wp_unslash($_POST['egitim_tarihi'] ?? ''));
        $o['egitim_tarihi'] = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $t) ? str_replace('T', ' ', $t) : '';
        $d = sanitize_text_field(wp_unslash($_POST['donem_tarihi'] ?? ''));
        $o['donem_tarihi']  = preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : '';
        $o['egitim_yeri']   = sanitize_text_field(wp_unslash($_POST['egitim_yeri'] ?? ''));
        $o['egitim_suresi'] = sanitize_text_field(wp_unslash($_POST['egitim_suresi'] ?? ''));
        update_option(self::OPT, $o);
        MST_Randevu::onbellek_temizle(); // tarih değişince sayfanın önbellekteki eski hâli kalmasın
        wp_safe_redirect(add_query_arg(['page' => 'mst-akademi', 'mst_msg' => rawurlencode('Ayarlar kaydedildi.')], admin_url('admin.php')));
        exit;
    }

    public static function admin_page()
    {
        $o     = self::opts();
        $msg   = isset($_GET['mst_msg']) ? sanitize_text_field(wp_unslash($_GET['mst_msg'])) : '';
        $sayfa = self::url();
        ?>
        <div class="wrap">
            <h1>Yazar Kariyer Akademisi</h1>
            <?php if ($msg) : ?><div class="notice notice-info is-dismissible"><p><?php echo esc_html($msg); ?></p></div><?php endif; ?>
            <?php if (!$sayfa) : ?>
                <div class="notice notice-warning"><p>Akademi sayfası henüz yok: <strong>Sayfalar → Yeni Sayfa Ekle</strong> → başlık (ör. “Yazar Kariyer Akademisi”) → <strong>Şablon: MST Yazar Kariyer Akademisi (Tam Sayfa)</strong> → Yayımla.</p></div>
            <?php else : ?>
                <p>Sayfa: <a href="<?php echo esc_url($sayfa); ?>" target="_blank"><?php echo esc_html($sayfa); ?></a></p>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('mst_akademi_ayar'); ?>
                <input type="hidden" name="action" value="mst_akademi_ayar">
                <h2>Ücretsiz eğitim</h2>
                <table class="form-table">
                    <tr><th>Tarih ve saat</th><td><input type="datetime-local" name="egitim_tarihi" value="<?php echo esc_attr(str_replace(' ', 'T', $o['egitim_tarihi'])); ?>">
                        <p class="description">Sayfada yalnızca ileri bir tarihse gösterilir; tarih geçince kendiliğinden “Yeni tarih yakında açıklanacak” yazar.</p></td></tr>
                    <tr><th>Yer</th><td><input type="text" name="egitim_yeri" class="regular-text" value="<?php echo esc_attr($o['egitim_yeri']); ?>" placeholder="Çevrim içi, canlı"></td></tr>
                    <tr><th>Süre</th><td><input type="text" name="egitim_suresi" class="regular-text" value="<?php echo esc_attr($o['egitim_suresi']); ?>" placeholder="ör. 90 dakika"></td></tr>
                </table>
                <h2>Program dönemi</h2>
                <table class="form-table">
                    <tr><th>Sonraki dönem başlangıcı</th><td><input type="date" name="donem_tarihi" value="<?php echo esc_attr($o['donem_tarihi']); ?>">
                        <p class="description">Boş bırakılırsa sayfada tarih yazmaz (“kayıtlar dönem başlangıcında alınır” yazar).</p></td></tr>
                </table>
                <?php submit_button('Kaydet'); ?>
            </form>
        </div>
        <?php
    }
}

MST_Akademi::init();
