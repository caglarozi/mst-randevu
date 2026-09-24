<?php
/**
 * Plugin Name: MST Yazar Adayı Randevu
 * Description: Yazar adaylarının müsait saatlerden görüşme randevusu alması. Kısa kod: [mst_randevu] — ya da sayfa şablonu olarak "MST Randevu (Tam Sayfa)".
 * Version:     1.1.0
 * Author:      MST Yayıncılık
 * Text Domain: mst-randevu
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MST_RANDEVU_VER', '1.1.0');
define('MST_RANDEVU_DB', 2);
define('MST_RANDEVU_URL', plugin_dir_url(__FILE__));

class MST_Randevu
{
    const OPT    = 'mst_randevu_ayarlar';
    const NONCE  = 'mst_randevu';
    const SABLON = 'mst-randevu-tam-sayfa';

    /* ------------------------------------------------------------------ */
    /*  Kurulum                                                            */
    /* ------------------------------------------------------------------ */

    public static function init()
    {
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        add_action('plugins_loaded', [__CLASS__, 'maybe_upgrade']);

        add_shortcode('mst_randevu', [__CLASS__, 'shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'isolate_styles'], 9999);
        add_filter('theme_page_templates', [__CLASS__, 'page_templates']);
        add_filter('template_include', [__CLASS__, 'template_include'], 99);

        // Ön yüz AJAX (giriş yapmamış ziyaretçiler dahil)
        foreach (['mst_randevu_slotlar' => 'ajax_slots', 'mst_randevu_al' => 'ajax_book'] as $action => $cb) {
            add_action('wp_ajax_' . $action, [__CLASS__, $cb]);
            add_action('wp_ajax_nopriv_' . $action, [__CLASS__, $cb]);
        }

        // Yönetim paneli
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_post_mst_randevu_slot_ekle', [__CLASS__, 'handle_add_slots']);
        add_action('admin_post_mst_randevu_slot_islem', [__CLASS__, 'handle_slot_action']);
        add_action('admin_post_mst_randevu_randevu_islem', [__CLASS__, 'handle_booking_action']);
        add_action('admin_post_mst_randevu_ayar_kaydet', [__CLASS__, 'handle_settings']);
        add_action('admin_post_mst_randevu_test', [__CLASS__, 'handle_test']);
    }

    public static function t_slot()
    {
        global $wpdb;
        return $wpdb->prefix . 'mst_randevu_slotlar';
    }

    public static function t_rnd()
    {
        global $wpdb;
        return $wpdb->prefix . 'mst_randevu_kayitlar';
    }

    public static function activate()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $c = $wpdb->get_charset_collate();

        // Saat: her saatin kişi sınırı (kapasite) ve dolu sayısı vardır
        dbDelta('CREATE TABLE ' . self::t_slot() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            baslangic datetime NOT NULL,
            sure smallint(5) unsigned NOT NULL DEFAULT 30,
            kapasite tinyint(3) unsigned NOT NULL DEFAULT 3,
            dolu tinyint(3) unsigned NOT NULL DEFAULT 0,
            durum varchar(20) NOT NULL DEFAULT 'acik',
            PRIMARY KEY  (id),
            UNIQUE KEY baslangic (baslangic),
            KEY durum (durum)
        ) $c;");

        // Randevu: bir saate bağlı aday kaydı
        dbDelta('CREATE TABLE ' . self::t_rnd() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            slot_id bigint(20) unsigned NOT NULL,
            ad_soyad varchar(191) NOT NULL DEFAULT '',
            telefon varchar(32) NOT NULL DEFAULT '',
            not_metni text NULL,
            olusturma datetime NOT NULL,
            durum varchar(20) NOT NULL DEFAULT 'aktif',
            bildirim_durumu varchar(191) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY slot_id (slot_id),
            KEY telefon (telefon)
        ) $c;");

        if (!get_option(self::OPT)) {
            add_option(self::OPT, self::defaults());
        }
        update_option('mst_randevu_db', MST_RANDEVU_DB);
    }

    public static function maybe_upgrade()
    {
        if ((int) get_option('mst_randevu_db') !== MST_RANDEVU_DB) {
            self::activate();
        }
    }

    public static function defaults()
    {
        return [
            'bildirim_eposta' => get_option('admin_email'),
            'webhook_url'     => '',
            'webhook_token'   => '',
            'min_saat'        => 2,   // randevu en geç kaç saat öncesine kadar alınabilir
            'gun_ileri'       => 30,  // kaç gün ilerisi gösterilsin
            'baslik'          => 'Yazar Adayı Görüşme Randevusu',
            'aciklama'        => 'Size uygun saati seçin, adınızı ve telefon numaranızı bırakın; editörümüz sizi belirtilen saatte arasın.',
            'rozetler'        => 'Editörümüz sizi telefonla arar, Ücretsiz ön değerlendirme',
            'whatsapp'        => '905514112004',
            'logo_url'        => '',
            'kvkk_metni'      => 'Kişisel verilerimin randevu ve iletişim amacıyla MST Yayıncılık tarafından işlenmesini kabul ediyorum.',
            'basari_mesaji'   => 'Randevunuz alındı! Belirtilen saatte sizi arayacağız.',
        ];
    }

    public static function opts()
    {
        return wp_parse_args((array) get_option(self::OPT, []), self::defaults());
    }

    /* ------------------------------------------------------------------ */
    /*  Yardımcılar                                                        */
    /* ------------------------------------------------------------------ */

    private static function now()
    {
        return new DateTime('now', wp_timezone());
    }

    private static function ts($mysql)
    {
        return (new DateTime($mysql, wp_timezone()))->getTimestamp();
    }

    private static function fmt($mysql, $format = 'j F Y l, H:i')
    {
        return wp_date($format, self::ts($mysql));
    }

    /** Türkiye numaralarını 905XXXXXXXXX biçimine getirir; geçersizse '' döner. */
    public static function normalize_phone($raw)
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        if (strlen($d) === 10) {
            $d = '90' . $d;
        } elseif (strlen($d) === 11 && $d[0] === '0') {
            $d = '9' . $d;
        } elseif (strlen($d) === 14 && strpos($d, '0090') === 0) {
            $d = substr($d, 2);
        }
        return (strlen($d) === 12 && strpos($d, '90') === 0 && in_array($d[2], ['2', '3', '4', '5', '8'], true)) ? $d : '';
    }

    public static function pretty_phone($d)
    {
        return strlen($d) === 12
            ? sprintf('+90 %s %s %s %s', substr($d, 2, 3), substr($d, 5, 3), substr($d, 8, 2), substr($d, 10, 2))
            : $d;
    }

    private static function client_ip()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '0';
    }

    /* ------------------------------------------------------------------ */
    /*  Ön yüz                                                             */
    /* ------------------------------------------------------------------ */

    public static function register_assets()
    {
        wp_register_style('mst-randevu-font', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap', [], null);
        wp_register_style('mst-randevu', MST_RANDEVU_URL . 'assets/randevu.css', ['mst-randevu-font'], MST_RANDEVU_VER);
        wp_register_script('mst-randevu', MST_RANDEVU_URL . 'assets/randevu.js', [], MST_RANDEVU_VER, true);

        // Stil dosyası <head> içinde yüklensin diye, randevu olan sayfaları önceden tanı
        $post = get_post();
        if (is_singular() && $post && (self::is_full_page() || has_shortcode((string) $post->post_content, 'mst_randevu'))) {
            self::enqueue();
        }
    }

    public static function enqueue()
    {
        $o = self::opts();
        wp_enqueue_style('mst-randevu');
        wp_enqueue_script('mst-randevu');
        wp_localize_script('mst-randevu', 'MST_RANDEVU', [
            'ajax'   => admin_url('admin-ajax.php'),
            'basari' => $o['basari_mesaji'],
            'site'   => home_url('/'),
        ]);
    }

    /* --- Tam sayfa şablonu: temanın üst kısmı yerine MST çubuğu --- */

    public static function is_full_page()
    {
        return is_page() && get_page_template_slug() === self::SABLON;
    }

    public static function page_templates($templates)
    {
        $templates[self::SABLON] = 'MST Randevu (Tam Sayfa)';
        return $templates;
    }

    public static function template_include($template)
    {
        return self::is_full_page() ? __DIR__ . '/templates/tam-sayfa.php' : $template;
    }

    /** Tam sayfada tema stillerini devre dışı bırakır; sayfa her temada aynı görünür. */
    public static function isolate_styles()
    {
        if (!self::is_full_page()) return;
        $keep = ['mst-randevu', 'mst-randevu-font', 'admin-bar', 'dashicons'];
        foreach (wp_styles()->queue as $handle) {
            if (!in_array($handle, $keep, true)) wp_dequeue_style($handle);
        }
    }

    public static function logo_url()
    {
        $o = self::opts();
        return $o['logo_url'] ?: MST_RANDEVU_URL . 'assets/mst-figur.png';
    }

    public static function figur_url()
    {
        return MST_RANDEVU_URL . 'assets/mst-figur.png';
    }

    public static function wa_link($text = '')
    {
        $n = preg_replace('/\D+/', '', (string) self::opts()['whatsapp']);
        return $n ? 'https://wa.me/' . $n . ($text ? '?text=' . rawurlencode($text) : '') : '';
    }

    public static function icon($name)
    {
        // Resmi WhatsApp simgesi (dolgulu)
        if ($name === 'wa') {
            return '<svg class="mst-ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>';
        }
        $d = [
            'saat' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'tel'  => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
            'onay' => '<path d="M20 6 9 17l-5-5"/>',
            'dis'  => '<path d="M14 4h6v6M20 4l-9 9M19 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/>',
        ];
        return '<svg class="mst-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($d[$name] ?? '') . '</svg>';
    }

    public static function header_html()
    {
        $home = home_url('/');
        $wa   = self::wa_link('Merhaba, yazar görüşmesi hakkında bilgi almak istiyorum.');
        ob_start(); ?>
        <header class="mst-top">
            <div class="mst-top__in">
                <a class="mst-top__logo" href="<?php echo esc_url($home); ?>" aria-label="Ana sayfa">
                    <img src="<?php echo esc_url(self::logo_url()); ?>" alt="MST" width="50" height="50">
                </a>
                <button type="button" class="mst-top__menu" aria-label="Menüyü aç" aria-expanded="false" aria-controls="mst-top-menu"><span></span><span></span><span></span></button>
                <div class="mst-top__cta" id="mst-top-menu">
                    <?php if ($wa) : ?><a class="mst-top__btn mst-top__btn--wa" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener" aria-label="WhatsApp'tan yazın"><?php echo self::icon('wa'); ?><span>WhatsApp</span></a><?php endif; ?>
                    <a class="mst-top__btn" href="<?php echo esc_url($home); ?>"><span>Siteye Git</span><?php echo self::icon('dis'); ?></a>
                </div>
            </div>
        </header>
        <?php
        return ob_get_clean();
    }

    public static function shortcode()
    {
        $o = self::opts();
        self::enqueue();
        $meta = array_values(array_filter(array_map('trim', explode(',', (string) $o['rozetler']))));
        $ikon = ['tel', 'onay', 'saat'];
        $wa   = self::wa_link('Merhaba, yazar görüşmesi hakkında bilgi almak istiyorum.');

        ob_start(); ?>
        <div class="mst-rnd-wrap">
        <div class="mst-rnd" data-mst-randevu>
            <aside class="mst-rnd__aside">
                <div class="mst-rnd__brand">
                    <span class="mst-rnd__avatar"><img src="<?php echo esc_url(self::figur_url()); ?>" alt="" width="40" height="40"></span>
                    <span class="mst-rnd__brand-txt"><strong>MST Ajans Production</strong><small>Editör Ekibi</small></span>
                </div>
                <h2 class="mst-rnd__title"><?php echo esc_html($o['baslik']); ?></h2>
                <?php if ($meta) : ?>
                    <ul class="mst-rnd__meta">
                        <?php foreach ($meta as $n => $m) : ?>
                            <li><span class="mst-rnd__meta-ic"><?php echo self::icon($ikon[$n] ?? 'onay'); ?></span><?php echo esc_html($m); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($o['aciklama']) : ?><p class="mst-rnd__desc"><?php echo esc_html($o['aciklama']); ?></p><?php endif; ?>
                <?php if ($wa) : ?><a class="mst-rnd__wa" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><?php echo self::icon('wa'); ?><span>Sorunuz mu var? WhatsApp'tan yazın</span></a><?php endif; ?>
            </aside>

            <section class="mst-rnd__main">
                <ol class="mst-rnd__steps" data-steps>
                    <li class="is-active"><span>1</span>Saat</li>
                    <li><span>2</span>Bilgiler</li>
                    <li><span>3</span>Onay</li>
                </ol>

                <div class="mst-rnd__step" data-step="saat">
                    <div class="mst-rnd__label">Gün seçin</div>
                    <div class="mst-rnd__days" data-days><div class="mst-rnd__loading">Müsait saatler yükleniyor…</div></div>
                    <div class="mst-rnd__label" data-times-label hidden>Saat seçin <small>Türkiye saati (GMT+3)</small></div>
                    <div class="mst-rnd__times" data-times></div>
                    <div class="mst-rnd__next" data-next hidden>
                        <div class="mst-rnd__next-info"><small>Seçilen saat</small><strong data-next-text></strong></div>
                        <button type="button" class="mst-rnd__btn" data-continue>Devam Et</button>
                    </div>
                </div>

                <form class="mst-rnd__form" data-form hidden novalidate>
                    <div class="mst-rnd__picked" data-picked></div>
                    <input type="hidden" name="slot_id" value="">
                    <label class="mst-rnd__field">
                        <span>Ad Soyad</span>
                        <input type="text" name="ad_soyad" autocomplete="name" required minlength="3" maxlength="100" placeholder="Adınız ve soyadınız">
                    </label>
                    <label class="mst-rnd__field">
                        <span>Telefon</span>
                        <input type="tel" name="telefon" autocomplete="tel" inputmode="tel" placeholder="05XX XXX XX XX" required>
                    </label>
                    <label class="mst-rnd__field">
                        <span>Kitabınız hakkında kısa not <em>(isteğe bağlı)</em></span>
                        <textarea name="not_metni" rows="3" maxlength="1000" placeholder="Tür, sayfa sayısı, tamamlandı mı…"></textarea>
                    </label>
                    <!-- bot tuzağı: insanlar görmez -->
                    <label class="mst-rnd__hp" aria-hidden="true">Web sitesi <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    <label class="mst-rnd__check">
                        <input type="checkbox" name="kvkk" value="1" required>
                        <span><?php echo esc_html($o['kvkk_metni']); ?></span>
                    </label>
                    <div class="mst-rnd__error" data-error role="alert" hidden></div>
                    <div class="mst-rnd__actions">
                        <button type="button" class="mst-rnd__btn mst-rnd__btn--ghost" data-back>Geri</button>
                        <button type="submit" class="mst-rnd__btn">Randevuyu Onayla</button>
                    </div>
                </form>

                <div class="mst-rnd__done" data-done hidden></div>
            </section>
        </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /** Açık saatleri (dolu olanlar dahil) AJAX ile verir; böylece sayfa önbelleğe alınsa bile saatler hep günceldir. */
    public static function ajax_slots()
    {
        global $wpdb;
        $o   = self::opts();
        $min = self::now()->modify('+' . (int) $o['min_saat'] . ' hours')->format('Y-m-d H:i:s');
        $max = self::now()->modify('+' . (int) $o['gun_ileri'] . ' days')->format('Y-m-d 23:59:59');

        $rows = $wpdb->get_results($wpdb->prepare(
            'SELECT id, baslangic, sure, kapasite, dolu FROM ' . self::t_slot() . "
             WHERE durum = 'acik' AND baslangic > %s AND baslangic <= %s
             ORDER BY baslangic ASC LIMIT 600",
            $min,
            $max
        ));

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id'     => (int) $r->id,
                'gun'    => substr($r->baslangic, 0, 10),
                'etiket' => self::fmt($r->baslangic, 'j F l'),
                'kisa'   => self::fmt($r->baslangic, 'D'),
                'no'     => self::fmt($r->baslangic, 'j M'),
                'saat'   => self::fmt($r->baslangic, 'H:i'),
                'sure'   => (int) $r->sure,
                'kalan'  => max(0, (int) $r->kapasite - (int) $r->dolu),
            ];
        }

        nocache_headers();
        wp_send_json_success(['slotlar' => $out, 'nonce' => wp_create_nonce(self::NONCE)]);
    }

    public static function ajax_book()
    {
        global $wpdb;
        check_ajax_referer(self::NONCE, 'nonce');

        if (!empty($_POST['website'])) {
            wp_send_json_error(['mesaj' => 'İstek reddedildi.'], 400);
        }

        // IP başına saatte en fazla 5 deneme
        $rk = 'mst_rnd_' . md5(self::client_ip());
        $n  = (int) get_transient($rk);
        if ($n >= 5) {
            wp_send_json_error(['mesaj' => 'Çok fazla deneme yapıldı. Lütfen biraz sonra tekrar deneyin.'], 429);
        }
        set_transient($rk, $n + 1, HOUR_IN_SECONDS);

        $id  = isset($_POST['slot_id']) ? absint($_POST['slot_id']) : 0;
        $ad  = isset($_POST['ad_soyad']) ? trim(sanitize_text_field(wp_unslash($_POST['ad_soyad']))) : '';
        $tel = self::normalize_phone(isset($_POST['telefon']) ? wp_unslash($_POST['telefon']) : '');
        $not = isset($_POST['not_metni']) ? mb_substr(sanitize_textarea_field(wp_unslash($_POST['not_metni'])), 0, 1000) : '';

        if (!$id) {
            wp_send_json_error(['mesaj' => 'Lütfen bir saat seçin.']);
        }
        if (mb_strlen($ad) < 3 || mb_strlen($ad) > 100) {
            wp_send_json_error(['mesaj' => 'Lütfen adınızı ve soyadınızı yazın.', 'alan' => 'ad_soyad']);
        }
        if (!$tel) {
            wp_send_json_error(['mesaj' => 'Geçerli bir telefon numarası girin (ör. 0532 123 45 67).', 'alan' => 'telefon']);
        }
        if (empty($_POST['kvkk'])) {
            wp_send_json_error(['mesaj' => 'Devam etmek için onay kutusunu işaretleyin.', 'alan' => 'kvkk']);
        }

        $ts  = self::t_slot();
        $tr  = self::t_rnd();
        $now = self::now()->format('Y-m-d H:i:s');
        $o   = self::opts();
        $min = self::now()->modify('+' . (int) $o['min_saat'] . ' hours')->format('Y-m-d H:i:s');

        // Aynı numarayla ileri tarihli ikinci randevu alınmasın
        $var = $wpdb->get_var($wpdb->prepare(
            "SELECT s.baslangic FROM $tr r JOIN $ts s ON s.id = r.slot_id
             WHERE r.telefon = %s AND r.durum = 'aktif' AND s.baslangic > %s LIMIT 1",
            $tel,
            $now
        ));
        if ($var) {
            wp_send_json_error(['mesaj' => sprintf('Bu numarayla zaten %s tarihine randevunuz var.', self::fmt($var))]);
        }

        // Atomik yer ayırma: sınır dolmamışsa dolu sayısını 1 artırır (aynı anda gelen başvurular sınırı aşamaz)
        $ok = $wpdb->query($wpdb->prepare(
            "UPDATE $ts SET dolu = dolu + 1 WHERE id = %d AND durum = 'acik' AND dolu < kapasite AND baslangic > %s",
            $id,
            $min
        ));
        if (!$ok) {
            wp_send_json_error(['mesaj' => 'Bu saat az önce doldu. Lütfen başka bir saat seçin.', 'yenile' => true], 409);
        }

        $ins = $wpdb->insert($tr, [
            'slot_id'   => $id,
            'ad_soyad'  => $ad,
            'telefon'   => $tel,
            'not_metni' => $not,
            'olusturma' => $now,
            'durum'     => 'aktif',
        ]);
        if (!$ins) {
            $wpdb->query($wpdb->prepare("UPDATE $ts SET dolu = GREATEST(dolu - 1, 0) WHERE id = %d", $id));
            wp_send_json_error(['mesaj' => 'Randevu kaydedilemedi, lütfen tekrar deneyin.'], 500);
        }

        $rnd = self::get_booking($wpdb->insert_id);
        self::notify($rnd);

        $bas = (new DateTime($rnd->baslangic, wp_timezone()))->setTimezone(new DateTimeZone('UTC'));
        $bit = (clone $bas)->modify('+' . (int) $rnd->sure . ' minutes');
        wp_send_json_success([
            'mesaj'  => $o['basari_mesaji'],
            'tarih'  => self::fmt($rnd->baslangic),
            'takvim' => add_query_arg([
                'action'  => 'TEMPLATE',
                'text'    => rawurlencode('MST Yayıncılık — Yazar görüşmesi'),
                'dates'   => $bas->format('Ymd\THis\Z') . '/' . $bit->format('Ymd\THis\Z'),
                'details' => rawurlencode('Editörümüz sizi ' . self::pretty_phone($tel) . ' numarasından arayacak.'),
            ], 'https://calendar.google.com/calendar/render'),
        ]);
    }

    /** Randevu kaydını saat bilgisiyle birlikte getirir. */
    public static function get_booking($id)
    {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            'SELECT r.*, s.baslangic, s.sure FROM ' . self::t_rnd() . ' r JOIN ' . self::t_slot() . ' s ON s.id = r.slot_id WHERE r.id = %d',
            $id
        ));
    }

    /* ------------------------------------------------------------------ */
    /*  Bildirim                                                           */
    /* ------------------------------------------------------------------ */

    public static function payload($r, $olay = 'randevu.olusturuldu')
    {
        $bas = new DateTime($r->baslangic, wp_timezone());
        return [
            'olay'           => $olay,
            'kaynak'         => 'mst-randevu',
            'site'           => home_url(),
            'randevu_id'     => (int) $r->id,
            'ad_soyad'       => $r->ad_soyad,
            'telefon'        => '+' . $r->telefon,
            'telefon_goster' => self::pretty_phone($r->telefon),
            'baslangic'      => $bas->format('c'),
            'bitis'          => (clone $bas)->modify('+' . (int) $r->sure . ' minutes')->format('c'),
            'sure_dk'        => (int) $r->sure,
            'tarih_metin'    => self::fmt($r->baslangic),
            'not'            => (string) $r->not_metni,
            'olusturma'      => $r->olusturma ? (new DateTime($r->olusturma, wp_timezone()))->format('c') : null,
            'mesaj'          => sprintf(
                "📅 Yeni yazar adayı randevusu\n%s\n%s\n%s%s",
                $r->ad_soyad,
                self::pretty_phone($r->telefon),
                self::fmt($r->baslangic),
                $r->not_metni ? "\nNot: " . $r->not_metni : ''
            ),
        ];
    }

    /** Webhook + e-posta gönderir. Sonucu kayda yazar ve döner. */
    public static function notify($r, $olay = 'randevu.olusturuldu')
    {
        global $wpdb;
        $o = self::opts();
        $p = self::payload($r, $olay);
        $sonuc = [];

        if (!empty($o['webhook_url'])) {
            $headers = ['Content-Type' => 'application/json; charset=utf-8'];
            if (!empty($o['webhook_token'])) {
                $headers['Authorization'] = 'Bearer ' . $o['webhook_token'];
                $headers['X-MST-Token']   = $o['webhook_token'];
            }
            $res = wp_remote_post($o['webhook_url'], [
                'timeout' => 8,
                'headers' => $headers,
                'body'    => wp_json_encode($p, JSON_UNESCAPED_UNICODE),
            ]);
            $sonuc[] = is_wp_error($res)
                ? 'webhook: HATA (' . $res->get_error_message() . ')'
                : 'webhook: ' . wp_remote_retrieve_response_code($res);
        }

        if (!empty($o['bildirim_eposta']) && $olay === 'randevu.olusturuldu') {
            $ok = wp_mail(
                $o['bildirim_eposta'],
                'Yeni randevu: ' . $r->ad_soyad . ' — ' . self::fmt($r->baslangic, 'j F H:i'),
                $p['mesaj'] . "\n\nPanel: " . admin_url('admin.php?page=mst-randevu')
            );
            $sonuc[] = 'e-posta: ' . ($ok ? 'ok' : 'HATA');
        }

        if (!empty($r->id) && $sonuc) {
            $wpdb->update(self::t_rnd(), ['bildirim_durumu' => mb_substr(implode(' | ', $sonuc), 0, 190)], ['id' => $r->id]);
        }

        do_action('mst_randevu_bildirim', $p, $r);
        return $sonuc;
    }

    /* ------------------------------------------------------------------ */
    /*  Yönetim paneli                                                     */
    /* ------------------------------------------------------------------ */

    public static function admin_menu()
    {
        add_menu_page('Yazar Randevuları', 'Yazar Randevu', 'manage_options', 'mst-randevu', [__CLASS__, 'admin_page'], 'dashicons-calendar-alt', 26);
    }

    private static function back($msg, $tab = 'randevular')
    {
        wp_safe_redirect(add_query_arg(['page' => 'mst-randevu', 'tab' => $tab, 'mst_msg' => rawurlencode($msg)], admin_url('admin.php')));
        exit;
    }

    public static function handle_add_slots()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_randevu_slot_ekle');
        global $wpdb;

        $tz       = wp_timezone();
        $bas_g    = sanitize_text_field(wp_unslash($_POST['bas_tarih'] ?? ''));
        $bit_g    = sanitize_text_field(wp_unslash($_POST['bit_tarih'] ?? '')) ?: $bas_g;
        $ilk      = sanitize_text_field(wp_unslash($_POST['ilk_saat'] ?? ''));
        $son      = sanitize_text_field(wp_unslash($_POST['son_saat'] ?? ''));
        $sure     = max(10, min(240, absint($_POST['sure'] ?? 30)));
        $kapasite = max(1, min(50, absint($_POST['kapasite'] ?? 3)));
        $gunler   = array_map('absint', (array) ($_POST['gunler'] ?? []));

        $d1 = DateTime::createFromFormat('!Y-m-d', $bas_g, $tz);
        $d2 = DateTime::createFromFormat('!Y-m-d', $bit_g, $tz);
        if (!$d1 || !$d2 || !preg_match('/^\d{2}:\d{2}$/', $ilk) || !preg_match('/^\d{2}:\d{2}$/', $son) || $ilk > $son) {
            self::back('Tarih veya saat aralığı geçersiz.', 'saatler');
        }
        if ($d2 < $d1 || $d1->diff($d2)->days > 120) {
            self::back('Tarih aralığı en fazla 120 gün olabilir.', 'saatler');
        }

        $eklenen = 0;
        $now = self::now();
        for ($d = clone $d1; $d <= $d2; $d->modify('+1 day')) {
            if ($gunler && !in_array((int) $d->format('N'), $gunler, true)) continue;
            $t    = new DateTime($d->format('Y-m-d') . ' ' . $ilk, $tz);
            $last = new DateTime($d->format('Y-m-d') . ' ' . $son, $tz);
            while ($t <= $last) { // son saat dahil: 17:30 seçilirse 17:30 randevusu da açılır
                if ($t > $now) {
                    $eklenen += (int) $wpdb->query($wpdb->prepare(
                        'INSERT IGNORE INTO ' . self::t_slot() . " (baslangic, sure, kapasite, dolu, durum) VALUES (%s, %d, %d, 0, 'acik')",
                        $t->format('Y-m-d H:i:s'),
                        $sure,
                        $kapasite
                    ));
                }
                $t->modify("+$sure minutes");
            }
        }

        self::back($eklenen ? "$eklenen saat eklendi (her birine $kapasite kişi)." : 'Yeni saat eklenmedi (geçmiş tarih ya da zaten var).', 'saatler');
    }

    public static function handle_slot_action()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_randevu_slot_islem');
        global $wpdb;
        $t     = self::t_slot();
        $ids   = array_filter(array_map('absint', (array) ($_POST['ids'] ?? [])));
        $islem = sanitize_key($_POST['islem'] ?? '');
        if (!$ids) self::back('Seçim yapılmadı.', 'saatler');

        $in = implode(',', $ids); // absint ile temizlendi
        switch ($islem) {
            case 'kapat':
                $n = $wpdb->query("UPDATE $t SET durum = 'kapali' WHERE id IN ($in)");
                self::back("$n saat kapatıldı (adaylara görünmez; mevcut randevular geçerli).", 'saatler');
            case 'ac':
                $n = $wpdb->query("UPDATE $t SET durum = 'acik' WHERE id IN ($in)");
                self::back("$n saat tekrar açıldı.", 'saatler');
            case 'sil':
                $n = $wpdb->query("DELETE FROM $t WHERE id IN ($in) AND dolu = 0");
                self::back("$n boş saat silindi. (Randevusu olan saatler silinmez; önce randevuları iptal edin.)", 'saatler');
            case 'kapasite':
                $k = max(1, min(50, absint($_POST['yeni_kapasite'] ?? 3)));
                $n = $wpdb->query($wpdb->prepare("UPDATE $t SET kapasite = GREATEST(%d, dolu) WHERE id IN ($in)", $k));
                self::back("$n saatin kişi sınırı $k yapıldı.", 'saatler');
        }
        self::back('Bilinmeyen işlem.', 'saatler');
    }

    public static function handle_booking_action()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_randevu_randevu_islem');
        global $wpdb;
        $ids   = array_filter(array_map('absint', (array) ($_POST['ids'] ?? [])));
        $islem = sanitize_key($_POST['islem'] ?? '');
        if (!$ids) self::back('Seçim yapılmadı.');

        $n = 0;
        foreach ($ids as $id) {
            $r = self::get_booking($id);
            if (!$r || $r->durum !== 'aktif') continue;
            if ($islem === 'iptal') {
                $wpdb->update(self::t_rnd(), ['durum' => 'iptal'], ['id' => $id]);
                $wpdb->query($wpdb->prepare('UPDATE ' . self::t_slot() . ' SET dolu = GREATEST(dolu - 1, 0) WHERE id = %d', $r->slot_id));
                // CRM'deki randevu da iptal görünsün (yalnızca webhook; e-posta gitmez)
                self::notify($r, 'randevu.iptal');
                $n++;
            } elseif ($islem === 'bildir') {
                self::notify($r);
                $n++;
            }
        }
        self::back($islem === 'iptal' ? "$n randevu iptal edildi, yerleri tekrar açıldı." : "$n bildirim yeniden gönderildi.");
    }

    public static function handle_settings()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_randevu_ayar_kaydet');
        $in = wp_unslash($_POST);
        update_option(self::OPT, [
            'bildirim_eposta' => sanitize_email($in['bildirim_eposta'] ?? ''),
            'webhook_url'     => esc_url_raw(trim($in['webhook_url'] ?? '')),
            'webhook_token'   => sanitize_text_field($in['webhook_token'] ?? ''),
            'min_saat'        => max(0, min(168, absint($in['min_saat'] ?? 2))),
            'gun_ileri'       => max(1, min(180, absint($in['gun_ileri'] ?? 30))),
            'baslik'          => sanitize_text_field($in['baslik'] ?? ''),
            'aciklama'        => sanitize_textarea_field($in['aciklama'] ?? ''),
            'rozetler'        => sanitize_text_field($in['rozetler'] ?? ''),
            'whatsapp'        => preg_replace('/\D+/', '', (string) ($in['whatsapp'] ?? '')),
            'logo_url'        => esc_url_raw(trim($in['logo_url'] ?? '')),
            'kvkk_metni'      => sanitize_textarea_field($in['kvkk_metni'] ?? ''),
            'basari_mesaji'   => sanitize_textarea_field($in['basari_mesaji'] ?? ''),
        ]);
        self::back('Ayarlar kaydedildi.', 'ayarlar');
    }

    public static function handle_test()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_randevu_test');
        $fake = (object) [
            'id' => 0, 'ad_soyad' => 'Test Adayı', 'telefon' => '905321234567', 'sure' => 30,
            'baslangic' => self::now()->modify('+1 day')->format('Y-m-d 14:00:00'),
            'not_metni' => 'Bu bir test bildirimidir.', 'olusturma' => self::now()->format('Y-m-d H:i:s'),
        ];
        $r = self::notify($fake, 'test');
        self::back('Test gönderildi → ' . ($r ? implode(' | ', $r) : 'webhook adresi tanımlı değil'), 'ayarlar');
    }

    public static function admin_page()
    {
        global $wpdb;
        $tab   = sanitize_key($_GET['tab'] ?? 'randevular');
        $msg   = isset($_GET['mst_msg']) ? sanitize_text_field(wp_unslash($_GET['mst_msg'])) : '';
        $o     = self::opts();
        $url   = admin_url('admin.php?page=mst-randevu');
        $now_s = self::now()->format('Y-m-d H:i:s');
        $from  = self::now()->modify('-7 days')->format('Y-m-d 00:00:00');
        $post  = esc_url(admin_url('admin-post.php'));
        $tabs  = ['randevular' => 'Randevular', 'saatler' => 'Saatler', 'ayarlar' => 'Bildirim & Ayarlar'];
        ?>
        <div class="wrap">
            <h1>Yazar Adayı Randevuları</h1>
            <?php if ($msg) : ?><div class="notice notice-info is-dismissible"><p><?php echo esc_html($msg); ?></p></div><?php endif; ?>

            <nav class="nav-tab-wrapper">
                <?php foreach ($tabs as $k => $v) : ?>
                    <a class="nav-tab <?php echo $tab === $k ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url($url . '&tab=' . $k); ?>"><?php echo esc_html($v); ?></a>
                <?php endforeach; ?>
            </nav>

        <?php if ($tab === 'ayarlar') : ?>
            <form method="post" action="<?php echo $post; ?>">
                <?php wp_nonce_field('mst_randevu_ayar_kaydet'); ?>
                <input type="hidden" name="action" value="mst_randevu_ayar_kaydet">
                <h2>Bildirim</h2>
                <table class="form-table">
                    <tr><th>Webhook URL</th><td>
                        <input type="url" name="webhook_url" class="large-text" value="<?php echo esc_attr($o['webhook_url']); ?>" placeholder="https://…">
                        <p class="description">Her yeni randevuda ve panelden iptal edilen randevuda bu adrese JSON POST atılır. MST CRM için CRM servisinin adresinin sonuna <code>/randevu</code> eklenir.</p>
                    </td></tr>
                    <tr><th>Webhook anahtarı</th><td>
                        <input type="text" name="webhook_token" class="regular-text" value="<?php echo esc_attr($o['webhook_token']); ?>" autocomplete="off">
                        <p class="description">Doluysa <code>Authorization: Bearer …</code> ve <code>X-MST-Token</code> başlıklarıyla gönderilir.</p>
                    </td></tr>
                    <tr><th>Bildirim e-postası</th><td>
                        <input type="email" name="bildirim_eposta" class="regular-text" value="<?php echo esc_attr($o['bildirim_eposta']); ?>">
                        <p class="description">Boş bırakılırsa e-posta gönderilmez.</p>
                    </td></tr>
                </table>
                <h2>Kurallar</h2>
                <table class="form-table">
                    <tr><th>En geç kaç saat önce</th><td><input type="number" name="min_saat" min="0" max="168" value="<?php echo (int) $o['min_saat']; ?>"> saat öncesine kadar randevu alınabilir</td></tr>
                    <tr><th>Kaç gün ileri</th><td><input type="number" name="gun_ileri" min="1" max="180" value="<?php echo (int) $o['gun_ileri']; ?>"> gün sonrasına kadar olan saatler gösterilir</td></tr>
                </table>
                <h2>Tam sayfa görünümü</h2>
                <p class="description">Sayfa düzenleyicide <strong>Sayfa Özellikleri → Şablon → "MST Randevu (Tam Sayfa)"</strong> seçilirse temanın üst kısmı yerine MST logosu, WhatsApp ve "Siteye Git" çubuğu kullanılır.</p>
                <table class="form-table">
                    <tr><th>WhatsApp numarası</th><td><input type="text" name="whatsapp" class="regular-text" value="<?php echo esc_attr($o['whatsapp']); ?>" placeholder="905XXXXXXXXX"><p class="description">Ülke koduyla, boşluksuz. Boş bırakılırsa WhatsApp butonları gizlenir.</p></td></tr>
                    <tr><th>Logo adresi</th><td><input type="url" name="logo_url" class="large-text" value="<?php echo esc_attr($o['logo_url']); ?>" placeholder="Boş = eklentideki MST logosu"></td></tr>
                </table>
                <h2>Metinler</h2>
                <table class="form-table">
                    <tr><th>Başlık</th><td><input type="text" name="baslik" class="large-text" value="<?php echo esc_attr($o['baslik']); ?>"></td></tr>
                    <tr><th>Açıklama</th><td><textarea name="aciklama" class="large-text" rows="2"><?php echo esc_textarea($o['aciklama']); ?></textarea></td></tr>
                    <tr><th>Görüşme bilgileri</th><td><input type="text" name="rozetler" class="large-text" value="<?php echo esc_attr($o['rozetler']); ?>"><p class="description">Virgülle ayırın. Sol sütunda simgeli liste olarak görünür (1. saat, 2. telefon, 3. onay simgesi).</p></td></tr>
                    <tr><th>KVKK onay metni</th><td><textarea name="kvkk_metni" class="large-text" rows="2"><?php echo esc_textarea($o['kvkk_metni']); ?></textarea></td></tr>
                    <tr><th>Başarı mesajı</th><td><textarea name="basari_mesaji" class="large-text" rows="2"><?php echo esc_textarea($o['basari_mesaji']); ?></textarea></td></tr>
                </table>
                <?php submit_button('Kaydet'); ?>
            </form>
            <form method="post" action="<?php echo $post; ?>">
                <?php wp_nonce_field('mst_randevu_test'); ?>
                <input type="hidden" name="action" value="mst_randevu_test">
                <?php submit_button('Test bildirimi gönder', 'secondary'); ?>
            </form>

        <?php elseif ($tab === 'saatler') :
            $rows  = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . self::t_slot() . ' WHERE baslangic >= %s ORDER BY baslangic ASC LIMIT 1500', $from));
            $bugun = self::now()->format('Y-m-d');
            ?>
            <h2>Müsait saat ekle</h2>
            <form method="post" action="<?php echo $post; ?>" style="background:#fff;padding:16px 20px;border:1px solid #dcdcde;max-width:920px">
                <?php wp_nonce_field('mst_randevu_slot_ekle'); ?>
                <input type="hidden" name="action" value="mst_randevu_slot_ekle">
                <p>
                    <label>Tarih: <input type="date" name="bas_tarih" value="<?php echo esc_attr($bugun); ?>" required></label>
                    <label>– <input type="date" name="bit_tarih" value="<?php echo esc_attr(self::now()->modify('+13 days')->format('Y-m-d')); ?>"></label>
                </p>
                <p>
                    <label>İlk randevu: <input type="time" name="ilk_saat" value="10:00" required></label>
                    &nbsp;&nbsp;
                    <label>Son randevu: <input type="time" name="son_saat" value="17:30" required></label>
                    &nbsp;&nbsp;
                    <label>Aralık: <input type="number" name="sure" value="30" min="10" max="240" style="width:64px"> dk</label>
                    &nbsp;&nbsp;
                    <label>Kişi sınırı: <input type="number" name="kapasite" value="3" min="1" max="50" style="width:56px"> kişi / saat</label>
                </p>
                <p>Günler:
                    <?php foreach ([1 => 'Pzt', 2 => 'Sal', 3 => 'Çar', 4 => 'Per', 5 => 'Cum', 6 => 'Cmt', 7 => 'Paz'] as $n => $g) : ?>
                        <label style="margin-right:8px"><input type="checkbox" name="gunler[]" value="<?php echo $n; ?>" <?php checked($n <= 5); ?>> <?php echo $g; ?></label>
                    <?php endforeach; ?>
                </p>
                <?php submit_button('Saatleri oluştur', 'primary', 'submit', false); ?>
                <p class="description">Örn. 10:00 → 17:30, 30 dk → 10:00, 10:30 … 17:00, 17:30 açılır; her saate en fazla 3 kişi randevu alabilir. Var olan saatler tekrar eklenmez.</p>
            </form>

            <h2 style="margin-top:28px">Saatler</h2>
            <form method="post" action="<?php echo $post; ?>">
                <?php wp_nonce_field('mst_randevu_slot_islem'); ?>
                <input type="hidden" name="action" value="mst_randevu_slot_islem">
                <div class="tablenav top">
                    <select name="islem" onchange="document.getElementById('mst-yk').style.display=this.value==='kapasite'?'inline-block':'none'">
                        <option value="">Toplu işlem…</option>
                        <option value="kapat">Kapat (gizle)</option>
                        <option value="ac">Tekrar aç</option>
                        <option value="kapasite">Kişi sınırını değiştir</option>
                        <option value="sil">Sil (yalnızca randevusuz)</option>
                    </select>
                    <input id="mst-yk" type="number" name="yeni_kapasite" value="3" min="1" max="50" style="width:64px;display:none">
                    <button class="button">Uygula</button>
                </div>
                <table class="widefat striped">
                    <thead><tr>
                        <td class="check-column"><input type="checkbox" onclick="this.closest('table').querySelectorAll('.mst-cb').forEach(c=>c.checked=this.checked)"></td>
                        <th>Tarih</th><th>Saat</th><th>Doluluk</th><th>Durum</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!$rows) : ?>
                        <tr><td colspan="5">Henüz saat eklenmedi.</td></tr>
                    <?php endif; foreach ($rows as $r) :
                        $gecmis = $r->baslangic <= $now_s;
                        $tam    = (int) $r->dolu >= (int) $r->kapasite; ?>
                        <tr style="<?php echo $gecmis ? 'opacity:.5' : ''; ?>">
                            <th class="check-column"><input class="mst-cb" type="checkbox" name="ids[]" value="<?php echo (int) $r->id; ?>"></th>
                            <td><?php echo esc_html(self::fmt($r->baslangic, 'j M Y D')); ?></td>
                            <td><strong><?php echo esc_html(self::fmt($r->baslangic, 'H:i')); ?></strong> <small>(<?php echo (int) $r->sure; ?> dk)</small></td>
                            <td><span style="font-weight:600;color:<?php echo $tam ? '#b32d2e' : ((int) $r->dolu ? '#996800' : '#008a20'); ?>"><?php echo (int) $r->dolu; ?> / <?php echo (int) $r->kapasite; ?></span><?php echo $tam ? ' — dolu' : ''; ?></td>
                            <td><?php echo $r->durum === 'kapali' ? '<span style="color:#646970">Kapalı</span>' : 'Açık'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </form>

        <?php else :
            $rows = $wpdb->get_results($wpdb->prepare(
                'SELECT r.*, s.baslangic, s.sure FROM ' . self::t_rnd() . ' r JOIN ' . self::t_slot() . " s ON s.id = r.slot_id
                 WHERE s.baslangic >= %s ORDER BY s.baslangic ASC, r.id ASC LIMIT 1500",
                $from
            ));
            $ileri = 0;
            foreach ($rows as $r) if ($r->durum === 'aktif' && $r->baslangic > $now_s) $ileri++;
            ?>
            <h2>Randevular <span style="font-weight:400;color:#646970">— ileride <?php echo (int) $ileri; ?> aktif randevu</span></h2>
            <p>Sayfaya eklemek için kısa kod <code>[mst_randevu]</code> ya da sayfa şablonu <strong>MST Randevu (Tam Sayfa)</strong>.</p>
            <form method="post" action="<?php echo $post; ?>">
                <?php wp_nonce_field('mst_randevu_randevu_islem'); ?>
                <input type="hidden" name="action" value="mst_randevu_randevu_islem">
                <div class="tablenav top">
                    <select name="islem">
                        <option value="">Toplu işlem…</option>
                        <option value="iptal">İptal et (yeri tekrar açılır)</option>
                        <option value="bildir">Bildirimi yeniden gönder</option>
                    </select>
                    <button class="button">Uygula</button>
                </div>
                <table class="widefat striped">
                    <thead><tr>
                        <td class="check-column"><input type="checkbox" onclick="this.closest('table').querySelectorAll('.mst-cb').forEach(c=>c.checked=this.checked)"></td>
                        <th>Tarih / Saat</th><th>Aday</th><th>Telefon</th><th>Not</th><th>Durum</th><th>Bildirim</th>
                    </tr></thead>
                    <tbody>
                    <?php if (!$rows) : ?>
                        <tr><td colspan="7">Henüz randevu yok.</td></tr>
                    <?php endif; foreach ($rows as $r) :
                        $soluk = $r->baslangic <= $now_s || $r->durum !== 'aktif'; ?>
                        <tr style="<?php echo $soluk ? 'opacity:.5' : ''; ?>">
                            <th class="check-column"><input class="mst-cb" type="checkbox" name="ids[]" value="<?php echo (int) $r->id; ?>"></th>
                            <td><strong><?php echo esc_html(self::fmt($r->baslangic, 'j M Y D')); ?></strong> <?php echo esc_html(self::fmt($r->baslangic, 'H:i')); ?></td>
                            <td><?php echo esc_html($r->ad_soyad); ?></td>
                            <td><a href="tel:+<?php echo esc_attr($r->telefon); ?>"><?php echo esc_html(self::pretty_phone($r->telefon)); ?></a></td>
                            <td style="max-width:260px"><?php echo esc_html(wp_trim_words((string) $r->not_metni, 20)); ?></td>
                            <td><?php echo $r->durum === 'aktif' ? '<span style="color:#008a20;font-weight:600">Aktif</span>' : '<span style="color:#b32d2e">İptal</span>'; ?></td>
                            <td><small><?php echo esc_html($r->bildirim_durumu); ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        <?php endif; ?>
        </div>
        <?php
    }
}

MST_Randevu::init();
