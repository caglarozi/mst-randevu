<?php
/**
 * MST Yayıncılık Yazar Kariyer Akademisi: tanıtım sayfası şablonu, Yazar Kariyer Analizi
 * başvuru formu (kayıt + aşamaya göre program önerisi + bildirim) ve yönetim ekranı.
 * Üst çubuk, renkler ve ortak bileşenler randevu / Yazar Paneli sayfalarıyla aynıdır.
 */
if (!defined('ABSPATH')) {
    exit;
}

class MST_Akademi
{
    const SABLON = 'mst-akademi-tam-sayfa';
    const OPT    = 'mst_akademi_ayarlar';
    const DB     = 1;

    /** Programlar: anahtar => [ad, kısa ad] */
    const PROGRAMLAR = [
        'temel'      => ['Yazar Akademisi Temel Programı', 'Temel Program'],
        'marka'      => ['Yazar Marka ve Görünürlük Programı', 'Marka ve Görünürlük'],
        'mentorluk'  => ['Yazar Kariyer Mentorluk Programı', 'Kariyer Mentorluk'],
    ];

    const DURUMLAR = [
        'yeni' => 'Yeni', 'incelendi' => 'İncelendi', 'gorusme' => 'Ön görüşme', 'onerildi' => 'Program önerildi',
        'kayit' => 'Kayıt oldu', 'uygun_degil' => 'Uygun değil',
    ];

    public static function init()
    {
        add_action('plugins_loaded', [__CLASS__, 'maybe_upgrade']);
        add_filter('theme_page_templates', [__CLASS__, 'page_templates']);
        add_filter('template_include', [__CLASS__, 'template_include'], 100);
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 20);
        add_action('wp_enqueue_scripts', [__CLASS__, 'isolate_styles'], 9999);
        add_action('wp_ajax_mst_akademi_basvuru', [__CLASS__, 'ajax_basvuru']);
        add_action('wp_ajax_nopriv_mst_akademi_basvuru', [__CLASS__, 'ajax_basvuru']);
        add_action('admin_menu', [__CLASS__, 'admin_menu'], 20);
        add_action('admin_post_mst_akademi_ayar', [__CLASS__, 'handle_ayar']);
        add_action('admin_post_mst_akademi_durum', [__CLASS__, 'handle_durum']);
        add_action('admin_post_mst_akademi_csv', [__CLASS__, 'handle_csv']);
    }

    /* ------------------------------------------------------------------ */
    /*  Kurulum ve ayarlar                                                 */
    /* ------------------------------------------------------------------ */

    public static function tablo()
    {
        global $wpdb;
        return $wpdb->prefix . 'mst_akademi_basvurular';
    }

    public static function maybe_upgrade()
    {
        if ((int) get_option('mst_akademi_db') === self::DB) return;
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta('CREATE TABLE ' . self::tablo() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            olusturma datetime NOT NULL,
            ad_soyad varchar(191) NOT NULL DEFAULT '',
            telefon varchar(32) NOT NULL DEFAULT '',
            eposta varchar(191) NOT NULL DEFAULT '',
            sehir varchar(100) NOT NULL DEFAULT '',
            asama varchar(20) NOT NULL DEFAULT '',
            oneri varchar(20) NOT NULL DEFAULT '',
            ilgi varchar(30) NOT NULL DEFAULT '',
            durum varchar(20) NOT NULL DEFAULT 'yeni',
            veri longtext NULL,
            kaynak text NULL,
            bildirim_durumu varchar(191) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY asama (asama),
            KEY durum (durum),
            KEY telefon (telefon)
        ) " . $wpdb->get_charset_collate() . ';');
        update_option('mst_akademi_db', self::DB);
    }

    public static function defaults()
    {
        return [
            'egitim_tarihi'  => '',   // ücretsiz eğitimin tarihi (Y-m-d H:i); geçmişse "yeni tarih yakında"
            'egitim_yeri'    => 'Çevrim içi, canlı',
            'egitim_suresi'  => '',
            'donem_tarihi'   => '',   // bir sonraki program dönemi başlangıcı (Y-m-d)
            'bildirim_eposta'=> '',   // boşsa randevu bildirim adresi
            'webhook_url'    => '',   // isteğe bağlı: her başvuruda JSON POST (CRM vb.)
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

    /** Site dili ne olursa olsun Türkçe tarih: "4 Ekim 2026 Pazar, 20:30". */
    public static function tr_tarih($ts, $saat = false)
    {
        $aylar = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        $gunler = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        $d = (new DateTime('@' . $ts))->setTimezone(wp_timezone());
        $m = $d->format('j') . ' ' . $aylar[(int) $d->format('n') - 1] . ' ' . $d->format('Y');
        return $saat ? $m . ' ' . $gunler[(int) $d->format('w')] . ', ' . $d->format('H:i') : $m;
    }

    public static function donem()
    {
        $o = self::opts();
        if (!$o['donem_tarihi']) return null;
        $ts = strtotime($o['donem_tarihi'] . ' 00:00:00');
        return ($ts && $ts > time() - DAY_IN_SECONDS) ? self::tr_tarih($ts) : null;
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
        wp_register_style('mst-akademi', MST_RANDEVU_URL . 'assets/akademi.css', ['mst-uygulama'], MST_RANDEVU_VER);
        wp_register_script('mst-akademi', MST_RANDEVU_URL . 'assets/akademi.js', [], MST_RANDEVU_VER, true);
        // Açılıştaki daktilo satırı için ek yazı tipi (yalnızca bu sayfada)
        wp_enqueue_style('mst-akademi-font', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&display=swap', [], null);
        wp_enqueue_style('mst-randevu-font');
        wp_enqueue_style('mst-akademi');
        wp_enqueue_script('mst-randevu');
        wp_enqueue_script('mst-uygulama');
        wp_enqueue_script('mst-akademi');
        wp_localize_script('mst-akademi', 'MST_AKADEMI', ['ajax' => admin_url('admin-ajax.php')]);
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
    /*  Başvuru formu                                                      */
    /* ------------------------------------------------------------------ */

    /** Seçenekli alanlar: şablon (form) ve doğrulama aynı listeyi kullanır. */
    public static function secenekler()
    {
        return [
            'asama'     => ['yazma' => 'Yazma aşamasındayım', 'dosya' => 'Dosyam hazır', 'yayimlandi' => 'Kitabım yayımlandı'],
            'yas'       => ['18-24' => '18–24', '25-34' => '25–34', '35-44' => '35–44', '45-54' => '45–54', '55-64' => '55–64', '65+' => '65 ve üzeri'],
            'kitap_fikri'     => ['evet' => 'Evet', 'hayir' => 'Hayır'],
            'yazmaya_basladi' => ['evet' => 'Evet', 'hayir' => 'Hayır'],
            'dosya_tamam'     => ['evet' => 'Evet', 'kismen' => 'Kısmen', 'hayir' => 'Hayır'],
            'yayimladi'       => ['evet' => 'Evet', 'hayir' => 'Hayır'],
            'kitap_turu' => ['roman' => 'Roman', 'oyku' => 'Öykü', 'siir' => 'Şiir', 'deneme' => 'Deneme', 'kisisel' => 'Kişisel gelişim',
                             'cocuk' => 'Çocuk / gençlik', 'tarih' => 'Tarih', 'ani' => 'Anı / biyografi', 'akademik' => 'Akademik / uzmanlık', 'diger' => 'Diğer'],
            'kamera'    => ['duzenli' => 'Evet, düzenli', 'ara_sira' => 'Ara sıra', 'hayir' => 'Hayır'],
            'siklik'    => ['her_gun' => 'Her gün', 'haftada' => 'Haftada birkaç kez', 'ayda' => 'Ayda birkaç kez', 'duzensiz' => 'Düzensiz', 'hic' => 'Hiç üretmiyorum'],
            'zorlandigi'=> ['yazma' => 'Yazma düzeni', 'okur' => 'Hedef okuru belirlemek', 'sosyal' => 'Sosyal medya', 'video' => 'Kamera ve video',
                            'yz' => 'Yapay zekâ', 'lansman' => 'Lansman ve PR', 'satis' => 'Satış ve tanıtım', 'diger' => 'Diğer'],
            'hedef'     => ['tamamlamak' => 'Kitabımı tamamlamak', 'yayimlatmak' => 'Kitabımı yayımlatmak', 'gorunurluk' => 'Görünürlüğümü artırmak',
                            'topluluk' => 'Okur topluluğu oluşturmak', 'kariyer' => 'Uzun vadeli yazar kariyeri kurmak', 'diger' => 'Diğer'],
            'gunler'    => ['pzt' => 'Pzt', 'sal' => 'Sal', 'car' => 'Çar', 'per' => 'Per', 'cum' => 'Cum', 'cmt' => 'Cmt', 'paz' => 'Paz'],
            'saat'      => ['sabah' => 'Sabah (09–12)', 'ogle' => 'Öğleden sonra (12–17)', 'aksam' => 'Akşam (17–20)', 'gece' => 'Gece (20–23)'],
            'ilgi'      => ['ucretsiz' => 'Ücretsiz eğitim', 'temel' => 'Temel Program', 'marka' => 'Marka ve Görünürlük Programı',
                            'mentorluk' => 'Kariyer Mentorluk Programı', 'karar' => 'Henüz karar vermedim'],
        ];
    }

    /** Alan etiketleri (e-posta ve yönetim ekranında). */
    public static function etiketler()
    {
        return [
            'asama' => 'Yazarlık aşaması', 'ad_soyad' => 'Ad soyad', 'telefon' => 'Telefon', 'eposta' => 'E-posta', 'sehir' => 'Şehir',
            'yas' => 'Yaş aralığı', 'kitap_fikri' => 'Kitap fikri var mı?', 'yazmaya_basladi' => 'Yazmaya başladı mı?', 'dosya_tamam' => 'Dosya tamamlandı mı?',
            'yayimladi' => 'Daha önce kitap yayımladı mı?', 'kitap_adi' => 'Yayımlanmış kitap adı', 'kitap_turu' => 'Kitap türü',
            'sosyal' => 'Sosyal medya hesabı', 'kamera' => 'Kamera karşısında video üretiyor mu?', 'siklik' => 'İçerik üretme sıklığı',
            'zorlandigi' => 'En fazla zorlandığı alan', 'beklenti' => 'Eğitimden beklentisi', 'hedef' => 'Hedefi', 'gunler' => 'Uygun günler',
            'saat' => 'Uygun saat aralığı', 'ilgi' => 'İlgilendiği program', 'not' => 'Ek not', 'iletisim_izni' => 'Ticari ileti izni',
        ];
    }

    /**
     * Aşamaya göre program önerisi (MST ekibi başvuruyu inceleyip son kararı verir):
     * yazma → Temel; dosya hazır → Marka ve Görünürlük (görünürlük üretimi hiç yoksa Temel);
     * yayımlanmış → Marka ve Görünürlük, uzun vadeli kariyer hedefi / mentorluk ilgisi varsa Mentorluk.
     */
    public static function sinifla(array $v)
    {
        switch ($v['asama'] ?? '') {
            case 'yazma':
                return 'temel';
            case 'dosya':
                $uretimYok = in_array($v['siklik'] ?? '', ['hic', 'duzensiz', ''], true) && ($v['kamera'] ?? '') !== 'duzenli' && empty($v['sosyal']);
                return $uretimYok ? 'temel' : 'marka';
            case 'yayimlandi':
                return (($v['hedef'] ?? '') === 'kariyer' || ($v['ilgi'] ?? '') === 'mentorluk') ? 'mentorluk' : 'marka';
        }
        return 'temel';
    }

    public static function ajax_basvuru()
    {
        global $wpdb;
        $hata = function ($mesaj, $alan = '', $kod = 200) {
            wp_send_json_error(['mesaj' => $mesaj, 'alan' => $alan], $kod);
        };
        // Bot tuzağı: gizli alan dolu ya da form 3 saniyeden kısa sürede gönderildi
        if (!empty($_POST['website'])) $hata('İstek reddedildi.', '', 400);
        $bas = isset($_POST['_t']) ? (int) $_POST['_t'] : 0;
        if ($bas && (time() - $bas) < 3) $hata('Lütfen formu yeniden gönderin.', '', 400);

        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '0';
        $rk = 'mst_akd_' . md5($ip);
        $n  = (int) get_transient($rk);
        if ($n >= 5) $hata('Çok fazla deneme yapıldı. Lütfen biraz sonra tekrar deneyin.', '', 429);
        set_transient($rk, $n + 1, HOUR_IN_SECONDS);

        $sec = self::secenekler();
        $al = function ($k, $max = 191) {
            return isset($_POST[$k]) ? mb_substr(trim(sanitize_text_field(wp_unslash($_POST[$k]))), 0, $max) : '';
        };
        $v = [];
        foreach (['ad_soyad' => 100, 'sehir' => 100, 'kitap_adi' => 191, 'sosyal' => 191] as $k => $max) $v[$k] = $al($k, $max);
        foreach (['beklenti', 'not'] as $k) {
            $v[$k] = isset($_POST[$k]) ? mb_substr(trim(sanitize_textarea_field(wp_unslash($_POST[$k]))), 0, 1500) : '';
        }
        foreach (['asama', 'yas', 'kitap_fikri', 'yazmaya_basladi', 'dosya_tamam', 'yayimladi', 'kitap_turu', 'kamera', 'siklik', 'zorlandigi', 'hedef', 'saat', 'ilgi'] as $k) {
            $x = $al($k, 30);
            $v[$k] = isset($sec[$k][$x]) ? $x : '';
        }
        $g = isset($_POST['gunler']) ? (array) wp_unslash($_POST['gunler']) : [];
        $v['gunler'] = array_values(array_intersect(array_map('sanitize_key', $g), array_keys($sec['gunler'])));
        $v['eposta'] = sanitize_email(wp_unslash($_POST['eposta'] ?? ''));
        $tel = MST_Randevu::normalize_phone(wp_unslash($_POST['telefon'] ?? ''));
        $v['telefon'] = $tel;
        $v['iletisim_izni'] = !empty($_POST['iletisim_izni']) ? 'evet' : 'hayir';

        if (!$v['asama']) $hata('Lütfen yazarlık aşamanızı seçin.', 'asama');
        if (mb_strlen($v['ad_soyad']) < 3) $hata('Lütfen adınızı ve soyadınızı yazın.', 'ad_soyad');
        if (!$tel) $hata('Geçerli bir telefon numarası girin (ör. 0532 123 45 67).', 'telefon');
        if (!is_email($v['eposta'])) $hata('Geçerli bir e-posta adresi girin.', 'eposta');
        if (empty($_POST['kvkk'])) $hata('Devam etmek için aydınlatma metni onayını işaretleyin.', 'kvkk');

        // Aynı numarayla son 10 dakikada gelen ikinci başvuru (çift tıklama) yeniden kaydedilmez
        $simdi = new DateTime('now', wp_timezone());
        $once  = (clone $simdi)->modify('-10 minutes')->format('Y-m-d H:i:s');
        $var = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . self::tablo() . ' WHERE telefon = %s AND olusturma > %s LIMIT 1', $tel, $once));

        $oneri = self::sinifla($v);
        if (!$var) {
            $kaynak = [];
            foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'gclid', 'ilk_sayfa', 'referans'] as $k) {
                $x = $al($k, 300);
                if ($x !== '') $kaynak[$k] = $x;
            }
            $ok = $wpdb->insert(self::tablo(), [
                'olusturma' => $simdi->format('Y-m-d H:i:s'),
                'ad_soyad'  => $v['ad_soyad'],
                'telefon'   => $tel,
                'eposta'    => $v['eposta'],
                'sehir'     => $v['sehir'],
                'asama'     => $v['asama'],
                'oneri'     => $oneri,
                'ilgi'      => $v['ilgi'],
                'durum'     => 'yeni',
                'veri'      => wp_json_encode($v, JSON_UNESCAPED_UNICODE),
                'kaynak'    => wp_json_encode($kaynak, JSON_UNESCAPED_UNICODE),
            ]);
            if (!$ok) $hata('Başvurunuz kaydedilemedi, lütfen tekrar deneyin.', '', 500);
            self::bildir((int) $wpdb->insert_id, $v, $oneri, $kaynak);
        }

        wp_send_json_success([
            'oneri'     => $oneri,
            'oneri_adi' => self::PROGRAMLAR[$oneri][0],
            'mesaj'     => 'Başvurunuz bize ulaştı. Ekibimiz bilgilerinizi inceleyip size uygun programı ve sonraki adımı telefonla ya da e-postayla iletecek.',
        ]);
    }

    /** E-posta + isteğe bağlı webhook. */
    public static function bildir($id, array $v, $oneri, array $kaynak)
    {
        global $wpdb;
        $o   = self::opts();
        $sec = self::secenekler();
        $et  = self::etiketler();
        $satir = [];
        foreach ($et as $k => $e) {
            $x = $v[$k] ?? '';
            if (is_array($x)) $x = implode(', ', array_map(function ($g) use ($sec) { return $sec['gunler'][$g] ?? $g; }, $x));
            elseif (isset($sec[$k][$x])) $x = $sec[$k][$x];
            if ($k === 'telefon') $x = MST_Randevu::pretty_phone($x);
            if ($x !== '' && $x !== null) $satir[] = $e . ': ' . $x;
        }
        $metin = "🎓 Yeni Yazar Kariyer Analizi başvurusu\n"
            . 'Sistem önerisi: ' . self::PROGRAMLAR[$oneri][0] . "\n\n" . implode("\n", $satir)
            . ($kaynak ? "\n\nKaynak: " . http_build_query($kaynak, '', ', ') : '')
            . "\n\nPanel: " . admin_url('admin.php?page=mst-akademi');
        $sonuc = [];
        $to = $o['bildirim_eposta'] ?: MST_Randevu::opts()['bildirim_eposta'];
        if ($to) {
            $sonuc[] = 'e-posta: ' . (wp_mail($to, 'Akademi başvurusu: ' . $v['ad_soyad'] . ' — ' . $sec['asama'][$v['asama']], $metin) ? 'ok' : 'HATA');
        }
        $p = [
            'olay' => 'akademi.basvuru', 'kaynak' => 'mst-akademi', 'site' => home_url(), 'basvuru_id' => $id,
            'ad_soyad' => $v['ad_soyad'], 'telefon' => '+' . $v['telefon'], 'telefon_goster' => MST_Randevu::pretty_phone($v['telefon']),
            'eposta' => $v['eposta'], 'asama' => $v['asama'], 'oneri' => $oneri, 'oneri_adi' => self::PROGRAMLAR[$oneri][0],
            'alanlar' => $v, 'utm' => $kaynak, 'mesaj' => $metin,
        ];
        if ($o['webhook_url']) {
            $h = ['Content-Type' => 'application/json; charset=utf-8'];
            $tok = MST_Randevu::opts()['webhook_token'];
            if ($tok) $h['Authorization'] = 'Bearer ' . $tok;
            $res = wp_remote_post($o['webhook_url'], ['timeout' => 8, 'headers' => $h, 'body' => wp_json_encode($p, JSON_UNESCAPED_UNICODE)]);
            $sonuc[] = is_wp_error($res) ? 'webhook: HATA (' . $res->get_error_message() . ')' : 'webhook: ' . wp_remote_retrieve_response_code($res);
        }
        if ($sonuc) $wpdb->update(self::tablo(), ['bildirim_durumu' => mb_substr(implode(' | ', $sonuc), 0, 190)], ['id' => $id]);
        do_action('mst_akademi_basvuru', $p);
    }

    /* ------------------------------------------------------------------ */
    /*  Yönetim                                                            */
    /* ------------------------------------------------------------------ */

    public static function admin_menu()
    {
        add_submenu_page('mst-randevu', 'Akademi Başvuruları', 'Akademi Başvuruları', 'manage_options', 'mst-akademi', [__CLASS__, 'admin_page']);
    }

    private static function geri($msg, $tab = 'basvurular')
    {
        wp_safe_redirect(add_query_arg(['page' => 'mst-akademi', 'tab' => $tab, 'mst_msg' => rawurlencode($msg)], admin_url('admin.php')));
        exit;
    }

    public static function handle_ayar()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_akademi_ayar');
        $o = self::opts();
        $t = sanitize_text_field(wp_unslash($_POST['egitim_tarihi'] ?? ''));
        $o['egitim_tarihi']   = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $t) ? str_replace('T', ' ', $t) : '';
        $d = sanitize_text_field(wp_unslash($_POST['donem_tarihi'] ?? ''));
        $o['donem_tarihi']    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : '';
        $o['egitim_yeri']     = sanitize_text_field(wp_unslash($_POST['egitim_yeri'] ?? ''));
        $o['egitim_suresi']   = sanitize_text_field(wp_unslash($_POST['egitim_suresi'] ?? ''));
        $o['bildirim_eposta'] = sanitize_email(wp_unslash($_POST['bildirim_eposta'] ?? ''));
        $o['webhook_url']     = esc_url_raw(wp_unslash($_POST['webhook_url'] ?? ''));
        update_option(self::OPT, $o);
        MST_Randevu::onbellek_temizle(); // tarih değişince sayfanın önbellekteki eski hâli kalmasın
        self::geri('Ayarlar kaydedildi.', 'ayarlar');
    }

    public static function handle_durum()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_akademi_durum');
        global $wpdb;
        $id = absint($_POST['id'] ?? 0);
        $d  = sanitize_key($_POST['durum'] ?? '');
        if ($id && isset(self::DURUMLAR[$d])) $wpdb->update(self::tablo(), ['durum' => $d], ['id' => $id]);
        self::geri('Durum güncellendi.');
    }

    public static function handle_csv()
    {
        if (!current_user_can('manage_options')) wp_die('Yetkisiz');
        check_admin_referer('mst_akademi_csv');
        global $wpdb;
        $rows = $wpdb->get_results('SELECT * FROM ' . self::tablo() . ' ORDER BY id DESC');
        $sec = self::secenekler();
        $et  = self::etiketler();
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=akademi-basvurulari-' . wp_date('Y-m-d') . '.csv');
        $f = fopen('php://output', 'w');
        fwrite($f, "\xEF\xBB\xBF"); // Excel Türkçe karakterleri doğru açsın
        fputcsv($f, array_merge(['No', 'Tarih', 'Durum', 'Sistem önerisi'], array_values($et), ['Kaynak']), ';');
        foreach ($rows as $r) {
            $v = json_decode((string) $r->veri, true) ?: [];
            $s = [$r->id, $r->olusturma, self::DURUMLAR[$r->durum] ?? $r->durum, self::PROGRAMLAR[$r->oneri][1] ?? $r->oneri];
            foreach ($et as $k => $e) {
                $x = $v[$k] ?? '';
                if (is_array($x)) $x = implode(', ', $x);
                elseif (isset($sec[$k][$x])) $x = $sec[$k][$x];
                if ($k === 'telefon') $x = MST_Randevu::pretty_phone($x);
                $s[] = $x;
            }
            $s[] = $r->kaynak;
            fputcsv($f, $s, ';');
        }
        exit;
    }

    public static function admin_page()
    {
        global $wpdb;
        $tab  = sanitize_key($_GET['tab'] ?? 'basvurular');
        $msg  = isset($_GET['mst_msg']) ? sanitize_text_field(wp_unslash($_GET['mst_msg'])) : '';
        $url  = admin_url('admin.php?page=mst-akademi');
        $post = esc_url(admin_url('admin-post.php'));
        $o    = self::opts();
        $sec  = self::secenekler();
        $et   = self::etiketler();
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
            <nav class="nav-tab-wrapper">
                <?php foreach (['basvurular' => 'Başvurular', 'ayarlar' => 'Ücretsiz eğitim & Ayarlar'] as $k => $e) : ?>
                    <a class="nav-tab <?php echo $tab === $k ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url($url . '&tab=' . $k); ?>"><?php echo esc_html($e); ?></a>
                <?php endforeach; ?>
            </nav>

        <?php if ($tab === 'ayarlar') : ?>
            <form method="post" action="<?php echo $post; ?>">
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
                <h2>Bildirim</h2>
                <table class="form-table">
                    <tr><th>Bildirim e-postası</th><td><input type="email" name="bildirim_eposta" class="regular-text" value="<?php echo esc_attr($o['bildirim_eposta']); ?>" placeholder="<?php echo esc_attr(MST_Randevu::opts()['bildirim_eposta']); ?>">
                        <p class="description">Boşsa randevu bildirimlerinin gittiği adres kullanılır.</p></td></tr>
                    <tr><th>Webhook URL (isteğe bağlı)</th><td><input type="url" name="webhook_url" class="large-text" value="<?php echo esc_attr($o['webhook_url']); ?>" placeholder="https://…">
                        <p class="description">Her başvuruda bu adrese JSON POST atılır (olay: <code>akademi.basvuru</code>); anahtar olarak randevu ayarlarındaki webhook anahtarı kullanılır.</p></td></tr>
                </table>
                <?php submit_button('Kaydet'); ?>
            </form>
        <?php else :
            $filtre = sanitize_key($_GET['asama'] ?? '');
            $where  = $filtre && isset($sec['asama'][$filtre]) ? $wpdb->prepare(' WHERE asama = %s', $filtre) : '';
            $rows   = $wpdb->get_results('SELECT * FROM ' . self::tablo() . $where . ' ORDER BY id DESC LIMIT 300');
            ?>
            <p style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <a class="button<?php echo !$filtre ? ' button-primary' : ''; ?>" href="<?php echo esc_url($url); ?>">Tümü</a>
                <?php foreach ($sec['asama'] as $k => $e) : ?>
                    <a class="button<?php echo $filtre === $k ? ' button-primary' : ''; ?>" href="<?php echo esc_url($url . '&asama=' . $k); ?>"><?php echo esc_html($e); ?></a>
                <?php endforeach; ?>
                <span style="flex:1"></span>
                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mst_akademi_csv'), 'mst_akademi_csv')); ?>">CSV indir</a>
            </p>
            <table class="widefat striped">
                <thead><tr><th>Tarih</th><th>Ad soyad</th><th>İletişim</th><th>Aşama</th><th>Sistem önerisi</th><th>İlgi</th><th>Durum</th></tr></thead>
                <tbody>
                <?php if (!$rows) : ?><tr><td colspan="7">Henüz başvuru yok.</td></tr><?php endif; ?>
                <?php foreach ($rows as $r) :
                    $v = json_decode((string) $r->veri, true) ?: [];
                    $wa = 'https://wa.me/' . $r->telefon; ?>
                    <tr>
                        <td><?php echo esc_html(wp_date('j M Y H:i', strtotime($r->olusturma))); ?></td>
                        <td><strong><?php echo esc_html($r->ad_soyad); ?></strong><?php echo $r->sehir ? '<br><small>' . esc_html($r->sehir) . '</small>' : ''; ?>
                            <details><summary>Tüm yanıtlar</summary><table class="form-table" style="margin:0">
                            <?php foreach ($et as $k => $e) :
                                $x = $v[$k] ?? '';
                                if (is_array($x)) $x = implode(', ', array_map(function ($g) use ($sec) { return $sec['gunler'][$g] ?? $g; }, $x));
                                elseif (isset($sec[$k][$x])) $x = $sec[$k][$x];
                                if ($x === '' || in_array($k, ['ad_soyad', 'telefon', 'eposta'], true)) continue; ?>
                                <tr><th style="padding:4px 8px"><?php echo esc_html($e); ?></th><td style="padding:4px 8px"><?php echo nl2br(esc_html($x)); ?></td></tr>
                            <?php endforeach; ?>
                            <?php if ($r->kaynak && $r->kaynak !== '[]') : ?><tr><th style="padding:4px 8px">Kaynak</th><td style="padding:4px 8px"><code><?php echo esc_html($r->kaynak); ?></code></td></tr><?php endif; ?>
                            </table></details></td>
                        <td><a href="tel:+<?php echo esc_attr($r->telefon); ?>"><?php echo esc_html(MST_Randevu::pretty_phone($r->telefon)); ?></a> · <a href="<?php echo esc_url($wa); ?>" target="_blank">WhatsApp</a><br><a href="mailto:<?php echo esc_attr($r->eposta); ?>"><?php echo esc_html($r->eposta); ?></a></td>
                        <td><?php echo esc_html($sec['asama'][$r->asama] ?? $r->asama); ?></td>
                        <td><?php echo esc_html(self::PROGRAMLAR[$r->oneri][1] ?? $r->oneri); ?></td>
                        <td><?php echo esc_html($sec['ilgi'][$r->ilgi] ?? '—'); ?></td>
                        <td><form method="post" action="<?php echo $post; ?>" style="display:flex;gap:4px">
                            <?php wp_nonce_field('mst_akademi_durum'); ?>
                            <input type="hidden" name="action" value="mst_akademi_durum"><input type="hidden" name="id" value="<?php echo (int) $r->id; ?>">
                            <select name="durum"><?php foreach (self::DURUMLAR as $k => $e) : ?><option value="<?php echo esc_attr($k); ?>" <?php selected($r->durum, $k); ?>><?php echo esc_html($e); ?></option><?php endforeach; ?></select>
                            <button class="button">Kaydet</button></form></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        </div>
        <?php
    }
}

MST_Akademi::init();
