<?php
/**
 * CineBook ve MST Çocuk: kitaptan fragmana / çizgi filme tanıtım sayfası ve başvuruları.
 * Başvurular yönetimde "CineBook Başvuruları" listesinde tutulur, bildirim e-postasına gider ve
 * randevu webhook'u (MST CRM) üzerinden "cinebook.basvuru" olayı olarak iletilir.
 */
if (!defined('ABSPATH')) {
    exit;
}

class MST_CineBook
{
    const SABLON = 'mst-cinebook-tam-sayfa';
    const OPT    = 'mst_cinebook_ayarlar';
    const CPT    = 'mst_cb_basvuru';
    const NONCE  = 'mst_cinebook';

    /** Başvuru türleri */
    const TURLER = [
        'cinebook' => 'CineBook (kitaptan fragman / film)',
        'cocuk'    => 'MST Çocuk (çocuk kitabından çizgi film)',
    ];

    public static function init()
    {
        add_filter('theme_page_templates', [__CLASS__, 'page_templates']);
        add_filter('template_include', [__CLASS__, 'template_include'], 100);
        add_action('init', [__CLASS__, 'cpt']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 20);
        add_action('wp_enqueue_scripts', [__CLASS__, 'isolate_styles'], 9999);
        add_action('admin_menu', [__CLASS__, 'admin_menu'], 21);
        add_action('admin_post_mst_cinebook_ayar', [__CLASS__, 'handle_ayar']);
        add_action('wp_ajax_mst_cinebook_basvuru', [__CLASS__, 'ajax_basvuru']);
        add_action('wp_ajax_nopriv_mst_cinebook_basvuru', [__CLASS__, 'ajax_basvuru']);
        add_action('save_post_page', [__CLASS__, 'sayfa_kaydedildi']);
    }

    public static function sayfa_kaydedildi($id)
    {
        if (wp_is_post_revision($id) || wp_is_post_autosave($id)) return;
        if (get_page_template_slug($id) === self::SABLON) MST_Randevu::onbellek_temizle();
    }

    /* ------------------------------------------------------------------ */
    /*  Ayarlar                                                            */
    /* ------------------------------------------------------------------ */

    public static function defaults()
    {
        return [
            'fragman_url'  => '',   // öne çıkan fragman (YouTube)
            'fragman_ad'   => 'Gökbörü',
            'fragman_gorsel' => '', // öne çıkan fragmanın afişi / kapak görseli
            'cocuk_url'    => '',   // MST Çocuk örnek çizgi film (YouTube)
            'youtube'      => 'https://www.youtube.com/@cinebookoffical',
            'instagram'    => 'https://www.instagram.com/cinebookofficial',
            'facebook'     => 'https://www.facebook.com/search/top?q=cinebookoffical',
            'tiktok'       => 'https://www.tiktok.com/@cinebookaffical',
        ];
    }

    public static function opts()
    {
        // Boş bırakılan alanlarda varsayılan (CineBook hesapları) kullanılır
        $kayit = array_filter((array) get_option(self::OPT, []), function ($v) { return is_array($v) || (string) $v !== ''; });
        return wp_parse_args($kayit, self::defaults());
    }

    /**
     * Filmografi. Yönetimden girilen yapımlar; hiç yoksa öne çıkan fragman ve MST Çocuk videosundan üretilir.
     * Her satır: ad, tur (cinebook|cocuk), etiket, yil, video (YouTube kimliği), afis (görsel adresi).
     */
    public static function yapimlar()
    {
        $o = self::opts();
        $l = [];
        foreach ((array) ($o['yapimlar'] ?? []) as $y) {
            if (empty($y['ad'])) continue;
            $l[] = [
                'ad' => $y['ad'], 'tur' => $y['tur'] === 'cocuk' ? 'cocuk' : 'cinebook',
                'etiket' => $y['etiket'] ?: ($y['tur'] === 'cocuk' ? 'Çizgi film' : 'Kitap fragmanı'),
                'yil' => $y['yil'] ?? '', 'video' => self::youtube_id($y['video'] ?? ''), 'afis' => $y['afis'] ?? '',
            ];
        }
        if (!$l) {
            $l[] = ['ad' => $o['fragman_ad'] ?: 'Gökbörü', 'tur' => 'cinebook', 'etiket' => 'İlk fragman', 'yil' => '',
                    'video' => self::youtube_id($o['fragman_url']), 'afis' => $o['fragman_gorsel']];
            if (self::youtube_id($o['cocuk_url'])) {
                $l[] = ['ad' => 'MST Çocuk', 'tur' => 'cocuk', 'etiket' => 'Çizgi film', 'yil' => '', 'video' => self::youtube_id($o['cocuk_url']), 'afis' => ''];
            }
        }
        return $l;
    }

    /** YouTube adresinden video kimliği (watch?v=, youtu.be/, shorts/, embed/). */
    public static function youtube_id($url)
    {
        return preg_match('~(?:youtu\.be/|v=|shorts/|embed/)([A-Za-z0-9_-]{11})~', (string) $url, $m) ? $m[1] : '';
    }

    /** Sosyal medya adresinden görünen hesap adı: instagram.com/cinebook → @cinebook */
    public static function hesap_adi($url)
    {
        $yol = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
        $yol = explode('/', $yol)[0] ?? '';
        if ($yol === 'search') { // facebook.com/search/top?q=ad
            parse_str((string) wp_parse_url($url, PHP_URL_QUERY), $q);
            $yol = $q['q'] ?? '';
        }
        return $yol === '' ? '' : '@' . ltrim($yol, '@');
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
        $templates[self::SABLON] = 'MST CineBook ve MST Çocuk (Tam Sayfa)';
        return $templates;
    }

    public static function template_include($template)
    {
        if (!self::is_page()) return $template;
        foreach (['LITESPEED_NO_OPTM', 'DONOTROCKETOPTIMIZE', 'DONOTMINIFYCSS', 'DONOTMINIFYJS'] as $sabit) {
            if (!defined($sabit)) define($sabit, true);
        }
        return __DIR__ . '/templates/cinebook.php';
    }

    public static function assets()
    {
        if (!self::is_page()) return;
        // Kurumsal alt bilgi akademi.css'ten gelir; sayfanın kendi düzeni cinebook.css'te
        if (!wp_style_is('mst-akademi', 'registered')) wp_register_style('mst-akademi', MST_Randevu::varlik('akademi.css'), ['mst-uygulama'], null);
        wp_register_style('mst-cinebook', MST_Randevu::varlik('cinebook.css'), ['mst-akademi'], null);
        wp_register_script('mst-cinebook', MST_Randevu::varlik('cinebook.js'), [], null, true);
        wp_enqueue_style('mst-cinebook-font', 'https://fonts.googleapis.com/css2?family=Antonio:wght@500;600;700&family=IBM+Plex+Mono:wght@500&family=Instrument+Serif:ital@0;1&display=swap', [], null);
        wp_enqueue_style('mst-randevu-font');
        wp_enqueue_style('mst-cinebook');
        wp_enqueue_script('mst-randevu');
        wp_enqueue_script('mst-uygulama');
        wp_enqueue_script('mst-cinebook');
        wp_localize_script('mst-cinebook', 'MST_CB', [
            'ajax'  => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE),
        ]);
    }

    public static function isolate_styles()
    {
        if (!self::is_page()) return;
        $keep = ['mst-randevu', 'mst-randevu-font', 'mst-cinebook-font', 'mst-uygulama', 'mst-akademi', 'mst-cinebook', 'admin-bar', 'dashicons'];
        foreach (wp_styles()->queue as $handle) {
            if (!in_array($handle, $keep, true)) wp_dequeue_style($handle);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Başvurular                                                         */
    /* ------------------------------------------------------------------ */

    /** Başvurular yönetimde listelenir (sitede görünmez). */
    public static function cpt()
    {
        register_post_type(self::CPT, [
            'labels'       => ['name' => 'CineBook Başvuruları', 'singular_name' => 'CineBook Başvurusu', 'edit_item' => 'Başvuru', 'search_items' => 'Başvuru ara', 'not_found' => 'Henüz başvuru yok.'],
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'mst-randevu',
            'supports'     => ['title', 'editor'],
            'capability_type' => 'post',
            'capabilities' => ['create_posts' => 'do_not_allow'],
            'map_meta_cap' => true,
        ]);
    }

    public static function ajax_basvuru()
    {
        check_ajax_referer(self::NONCE, 'nonce');
        if (!empty($_POST['website'])) wp_send_json_error(['mesaj' => 'İstek reddedildi.'], 400);

        // IP başına saatte en fazla 5 başvuru
        $rk = 'mst_cb_' . md5(MST_Randevu::client_ip());
        $n  = (int) get_transient($rk);
        if ($n >= 5) wp_send_json_error(['mesaj' => 'Çok fazla deneme yapıldı. Lütfen biraz sonra tekrar deneyin.'], 429);
        set_transient($rk, $n + 1, HOUR_IN_SECONDS);

        $al  = function ($k, $max = 200) { return mb_substr(trim(sanitize_text_field(wp_unslash($_POST[$k] ?? ''))), 0, $max); };
        $tur   = sanitize_key(wp_unslash($_POST['tur'] ?? ''));
        $eser  = $al('eser_adi', 150);
        $yazar = $al('yazar_adi', 100);
        $eposta = sanitize_email(wp_unslash($_POST['eposta'] ?? ''));
        $tel   = MST_Randevu::normalize_phone(wp_unslash($_POST['telefon'] ?? ''));
        $ozet  = mb_substr(trim(sanitize_textarea_field(wp_unslash($_POST['ozet'] ?? ''))), 0, 1500);

        if (!isset(self::TURLER[$tur])) wp_send_json_error(['mesaj' => 'Lütfen başvuru türünü seçin.', 'alan' => 'tur']);
        if (mb_strlen($eser) < 2) wp_send_json_error(['mesaj' => 'Lütfen eserinizin adını yazın.', 'alan' => 'eser_adi']);
        if (mb_strlen($yazar) < 3) wp_send_json_error(['mesaj' => 'Lütfen adınızı ve soyadınızı yazın.', 'alan' => 'yazar_adi']);
        if (!$tel) wp_send_json_error(['mesaj' => 'Geçerli bir telefon numarası girin (ör. 0532 123 45 67).', 'alan' => 'telefon']);
        if ($eposta && !is_email($eposta)) wp_send_json_error(['mesaj' => 'E-posta adresi geçerli görünmüyor.', 'alan' => 'eposta']);
        if (empty($_POST['kvkk'])) wp_send_json_error(['mesaj' => 'Devam etmek için onay kutusunu işaretleyin.', 'alan' => 'kvkk']);

        $govde = sprintf(
            "Tür: %s\nEser: %s\nYazar: %s\nTelefon: %s\nE-posta: %s\n\nÖzet:\n%s",
            self::TURLER[$tur], $eser, $yazar, MST_Randevu::pretty_phone($tel), $eposta ?: '—', $ozet ?: '—'
        );
        $id = wp_insert_post([
            'post_type'    => self::CPT,
            'post_status'  => 'private',
            'post_title'   => ($tur === 'cocuk' ? '[MST Çocuk] ' : '[CineBook] ') . $eser . ' — ' . $yazar,
            'post_content' => $govde,
        ]);
        if (!$id || is_wp_error($id)) wp_send_json_error(['mesaj' => 'Başvuru kaydedilemedi, lütfen tekrar deneyin.'], 500);
        foreach (['tur' => $tur, 'eser' => $eser, 'yazar' => $yazar, 'telefon' => $tel, 'eposta' => $eposta] as $k => $v) {
            update_post_meta($id, '_cb_' . $k, $v);
        }

        // Bildirim: e-posta + CRM (webhook). CRM bu olayı tanımıyorsa yok sayar, başvuru yine kaydedilir.
        $o = MST_Randevu::opts();
        if (!empty($o['bildirim_eposta'])) {
            wp_mail($o['bildirim_eposta'], ($tur === 'cocuk' ? 'Yeni MST Çocuk başvurusu: ' : 'Yeni CineBook başvurusu: ') . $eser, $govde . "\n\nPanel: " . admin_url('edit.php?post_type=' . self::CPT));
        }
        if (!empty($o['webhook_url'])) {
            $headers = ['Content-Type' => 'application/json; charset=utf-8'];
            if (!empty($o['webhook_token'])) $headers['Authorization'] = 'Bearer ' . $o['webhook_token'];
            wp_remote_post($o['webhook_url'], [
                'timeout' => 6, 'blocking' => false, 'headers' => $headers,
                'body' => wp_json_encode([
                    'olay' => 'cinebook.basvuru', 'kaynak' => 'mst-randevu', 'site' => home_url(), 'basvuru_id' => (int) $id,
                    'tur' => $tur, 'tur_metin' => self::TURLER[$tur], 'eser_adi' => $eser, 'ad_soyad' => $yazar,
                    'telefon' => '+' . $tel, 'eposta' => $eposta, 'not' => $ozet,
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }
        wp_send_json_success(['mesaj' => 'Başvurunuz bize ulaştı. Eserinizi inceleyip en kısa sürede sizinle iletişime geçeceğiz.']);
    }

    /* ------------------------------------------------------------------ */
    /*  Yönetim: Yazar Randevu → CineBook Ayarları                         */
    /* ------------------------------------------------------------------ */

    public static function admin_menu()
    {
        add_submenu_page('mst-randevu', 'CineBook Ayarları', 'CineBook Ayarları', 'manage_options', 'mst-cinebook', [__CLASS__, 'admin_page']);
    }

    public static function handle_ayar()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_cinebook_ayar');
        $o = self::opts();
        foreach (['fragman_url', 'fragman_gorsel', 'cocuk_url', 'youtube', 'instagram', 'facebook', 'tiktok'] as $k) {
            $o[$k] = esc_url_raw(trim(wp_unslash($_POST[$k] ?? '')));
        }
        $o['fragman_ad'] = sanitize_text_field(wp_unslash($_POST['fragman_ad'] ?? ''));
        $o['yapimlar'] = [];
        foreach ((array) ($_POST['yapimlar'] ?? []) as $y) {
            $y = wp_unslash((array) $y);
            if (trim($y['ad'] ?? '') === '') continue;
            $o['yapimlar'][] = [
                'ad'     => sanitize_text_field($y['ad']),
                'tur'    => ($y['tur'] ?? '') === 'cocuk' ? 'cocuk' : 'cinebook',
                'etiket' => sanitize_text_field($y['etiket'] ?? ''),
                'yil'    => preg_replace('/\D/', '', (string) ($y['yil'] ?? '')),
                'video'  => esc_url_raw(trim($y['video'] ?? '')),
                'afis'   => esc_url_raw(trim($y['afis'] ?? '')),
            ];
        }
        update_option(self::OPT, $o);
        MST_Randevu::onbellek_temizle();
        wp_safe_redirect(add_query_arg(['page' => 'mst-cinebook', 'mst_msg' => rawurlencode('Ayarlar kaydedildi.')], admin_url('admin.php')));
        exit;
    }

    public static function admin_page()
    {
        $o     = self::opts();
        $msg   = isset($_GET['mst_msg']) ? sanitize_text_field(wp_unslash($_GET['mst_msg'])) : '';
        $sayfa = self::url();
        $alan  = function ($k, $etiket, $ipucu = '', $gorsel = false) use ($o) {
            echo '<tr><th>' . esc_html($etiket) . '</th><td><input type="url" name="' . esc_attr($k) . '" class="large-text" value="' . esc_attr($o[$k]) . '" placeholder="https://…">'
                . ($gorsel ? ' <button type="button" class="button" data-cb-medya>Ortam kitaplığından seç</button>' : '')
                . ($ipucu ? '<p class="description">' . esc_html($ipucu) . '</p>' : '') . '</td></tr>';
        };
        $yapimlar = array_values((array) ($o['yapimlar'] ?? []));
        $yapimlar[] = ['ad' => '', 'tur' => 'cinebook', 'etiket' => '', 'yil' => '', 'video' => '', 'afis' => '']; // boş satır
        wp_enqueue_media();
        ?>
        <div class="wrap">
            <h1>CineBook ve MST Çocuk</h1>
            <?php if ($msg) : ?><div class="notice notice-info is-dismissible"><p><?php echo esc_html($msg); ?></p></div><?php endif; ?>
            <?php if (!$sayfa) : ?>
                <div class="notice notice-warning"><p>Sayfa henüz yok: <strong>Sayfalar → Yeni Sayfa Ekle</strong> → başlık (ör. “CineBook”) → <strong>Şablon: MST CineBook ve MST Çocuk (Tam Sayfa)</strong> → Yayımla.</p></div>
            <?php else : ?>
                <p>Sayfa: <a href="<?php echo esc_url($sayfa); ?>" target="_blank"><?php echo esc_html($sayfa); ?></a> · <a href="<?php echo esc_url(admin_url('edit.php?post_type=' . self::CPT)); ?>">Başvurular</a></p>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('mst_cinebook_ayar'); ?>
                <input type="hidden" name="action" value="mst_cinebook_ayar">
                <h2>Videolar</h2>
                <table class="form-table">
                    <?php $alan('fragman_url', 'Öne çıkan fragman (YouTube)', 'Girişte arka planda sessiz döner; “Şimdi izle” sesli açar.'); ?>
                    <tr><th>Fragmanın adı</th><td><input type="text" name="fragman_ad" class="regular-text" value="<?php echo esc_attr($o['fragman_ad']); ?>" placeholder="Gökbörü"></td></tr>
                    <?php $alan('fragman_gorsel', 'Fragman görseli (afiş / kapak)', 'Girişin arka planında ve “Şimdi izle” kartında kullanılır. Yatay (16:9) görsel önerilir.', true); ?>
                    <?php $alan('cocuk_url', 'MST Çocuk örnek çizgi film (YouTube)', 'MST Çocuk bölümünde gösterilir. Boşsa “yakında” görünür.'); ?>
                </table>
                <h2>Yapımlar</h2>
                <p class="description">Sayfadaki filmografi. Hiç yapım girilmezse öne çıkan fragman tek yapım olarak gösterilir. Satırı silmek için adı boşaltıp kaydedin. Afiş için dikey (2:3) görsel önerilir; afiş yoksa videonun kapağı kullanılır.</p>
                <table class="widefat striped" style="max-width:1100px;margin-top:10px">
                    <thead><tr><th>Ad</th><th>Tür</th><th>Etiket</th><th>Yıl</th><th>YouTube adresi</th><th>Afiş görseli</th></tr></thead>
                    <tbody data-cb-yapimlar>
                    <?php foreach ($yapimlar as $i => $y) : $n = 'yapimlar[' . (int) $i . ']'; ?>
                        <tr>
                            <td><input type="text" name="<?php echo esc_attr($n); ?>[ad]" value="<?php echo esc_attr($y['ad']); ?>" placeholder="Gökbörü" style="width:100%"></td>
                            <td><select name="<?php echo esc_attr($n); ?>[tur]"><option value="cinebook">CineBook</option><option value="cocuk" <?php selected($y['tur'], 'cocuk'); ?>>MST Çocuk</option></select></td>
                            <td><input type="text" name="<?php echo esc_attr($n); ?>[etiket]" value="<?php echo esc_attr($y['etiket']); ?>" placeholder="Kitap fragmanı" style="width:100%"></td>
                            <td><input type="text" name="<?php echo esc_attr($n); ?>[yil]" value="<?php echo esc_attr($y['yil']); ?>" placeholder="<?php echo esc_attr(wp_date('Y')); ?>" size="5"></td>
                            <td><input type="url" name="<?php echo esc_attr($n); ?>[video]" value="<?php echo esc_attr($y['video']); ?>" placeholder="https://youtu.be/…" style="width:100%"></td>
                            <td><input type="url" name="<?php echo esc_attr($n); ?>[afis]" value="<?php echo esc_attr($y['afis']); ?>" placeholder="https://…" style="width:70%"> <button type="button" class="button" data-cb-medya>Seç</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p><button type="button" class="button" data-cb-satir>+ Yapım ekle</button></p>
                <h2>Sosyal medya</h2>
                <table class="form-table">
                    <?php $alan('youtube', 'YouTube kanalı'); $alan('instagram', 'Instagram'); $alan('facebook', 'Facebook sayfası'); $alan('tiktok', 'TikTok'); ?>
                </table>
                <?php submit_button('Kaydet'); ?>
            </form>
        </div>
        <script>
        (function () {
            document.addEventListener('click', function (e) {
                var b = e.target.closest('[data-cb-medya]');
                if (b && window.wp && wp.media) {
                    var girdi = b.parentNode.querySelector('input[type=url]');
                    var c = wp.media({ title: 'Görsel seç', library: { type: 'image' }, button: { text: 'Kullan' }, multiple: false });
                    c.on('select', function () { girdi.value = c.state().get('selection').first().toJSON().url; });
                    c.open();
                }
                if (e.target.closest('[data-cb-satir]')) {
                    var g = document.querySelector('[data-cb-yapimlar]'), son = g.lastElementChild, yeni = son.cloneNode(true), n = g.children.length;
                    yeni.querySelectorAll('input, select').forEach(function (x) { x.name = x.name.replace(/\[\d+\]/, '[' + n + ']'); if (x.tagName === 'INPUT') x.value = ''; });
                    g.appendChild(yeni);
                }
            });
        })();
        </script>
        <?php
    }
}

MST_CineBook::init();
