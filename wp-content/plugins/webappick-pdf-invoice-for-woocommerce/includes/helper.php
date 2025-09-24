<?php
/**
 * Filter to change invoice strings.
 */
if ( ! function_exists('woo_invoice_free_filter_label') ) {
    function woo_invoice_free_filter_label( $label, $order, $template ) {
        woo_invoice_free_reload_text_domain();
        return apply_filters(
            'woo_invoice_filter_template_label',
            __($label, 'webappick-pdf-invoice-for-woocommerce'), //phpcs:ignore
            $order,
            $template
        );
    }
}

/**
 * Function to switch language according to order language.
 * @param $language_code
 * @param bool $cookie_lang
 */
function woo_invoice_free_switch_language_callback( $language_code, $cookie_lang = true ) {
    if ( ! empty($language_code) ) {
        if ( class_exists('SitePress', false) ) {
            // WPML Switch Language.
            global $sitepress;
            if ( $sitepress->get_current_language() !== $language_code ) {
                $sitepress->switch_lang($language_code, $cookie_lang);
            }
        }
        // when polylang plugin is activated
        if ( defined('POLYLANG_BASENAME') || function_exists('PLL') ) {
            if ( pll_current_language() !== $language_code ) {
                PLL()->curlang = PLL()->model->get_language($language_code);
            }
        }
    }
}

/**
 * Function to restore language.
 */
function woo_invoice_free_restore_language_callback() {
    $language_code = '';
    if ( class_exists('SitePress', false) ) {
        // WPML restore Language.
        global $sitepress;
        $language_code = $sitepress->get_default_language();
    }

    // when polylang plugin is activated
    if ( defined('POLYLANG_BASENAME') || function_exists('PLL') ) {
        $language_code = pll_default_language();
    }
    /**
     * Filter to hijack Default Language code before restore
     *
     * @param string $language_code
     */

    if ( ! empty($language_code) ) {
        woo_invoice_free_switch_language_callback($language_code);
    }
}

/**
 * Reload text domain after switch language.
 */
function woo_invoice_free_reload_text_domain() {
    load_plugin_textdomain(
        'webappick-pdf-invoice-for-woocommerce',
        false,
        dirname(CHALLAN_FREE_PLUGIN_BASE_NAME) . '/languages/'
    );

}

if ( ! function_exists('challan_order_meta_query') ) {
    /**
     * @return array|object|null
     */
    function challan_order_meta_query(){
        global $wpdb;
        // Check if HPOS (High-Performance Order Storage) is enabled
        if ( woo_invoice_hpos_enabled() ) {
            // HPOS is enabled, query the wc_orders_meta table
            $order_meta_query_arr = $wpdb->get_results("SELECT DISTINCT meta_key FROM {$wpdb->prefix}wc_orders_meta"); // phpcs:ignore
        } else {
            // Traditional CPT-based orders are in use, query the postmeta table
            $order_meta_query_arr = $wpdb->get_results("SELECT $wpdb->postmeta.meta_key FROM $wpdb->postmeta LEFT JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.id WHERE $wpdb->posts.post_type = 'shop_order' GROUP BY $wpdb->postmeta.meta_key");//phpcs:ignore;
        }
        $order_meta_query = array();
        $order_meta_query = $order_meta_query_arr;

        return $order_meta_query;
    }
}

if ( ! function_exists('challan_product_meta_query') ) {
    /**
     * @return array|object|null
     */
    function challan_product_meta_query(){
        global $wpdb;

        $product_meta_query = $wpdb->get_results("SELECT $wpdb->postmeta.meta_key, $wpdb->postmeta.meta_id FROM $wpdb->postmeta LEFT JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.id WHERE $wpdb->posts.post_type = 'product' GROUP BY $wpdb->postmeta.meta_key" );//phpcs:ignore;

        return $product_meta_query;
    }
}

if ( ! function_exists('challan_item_meta_query') ) {
    /**
     * @return array|object|null
     */
    function challan_item_meta_query(){
        global $wpdb;

		if ( class_exists( 'WooCommerce' ) ) {
			$item_meta = $wpdb->get_results("SELECT DISTINCT $wpdb->order_itemmeta.meta_key  FROM $wpdb->order_itemmeta");//phpcs:ignore
		} else {
			$item_meta = array();
		}

        return $item_meta;
    }
}

if ( ! function_exists('challan_order_meta_data_position') ) {
    /**
     * @return string[].
     */
    function challan_order_meta_data_position(){
        $order_meta_position_arr = array(
            'after_order_data'        => 'After order data',
            'before_order_data'       => 'Before order data',
            'before_billing_address'  => 'Before billing address',
            'after_billing_address'   => 'After billing address',
            'before_shipping_address' => 'Before shipping address',
            'after_shipping_address'  => 'After shipping address',
        );

        return $order_meta_position_arr;
    }
}


function woo_invoice_get_default_languages(){
    return array(
        'af'             => 'Afrikaans',
        'ar'             => 'العربية',
        'ary'            => 'العربية المغربية',
        'as'             => 'অসমীয়া',
        'azb'            => 'گؤنئی آذربایجان',
        'az'             => 'Azərbaycan dili',
        'bel'            => 'Беларуская мова',
        'bg_BG'          => 'Български',
        'bn_BD'          => 'বাংলা',
        'bo'             => 'བོད་ཡིག',
        'bs_BA'          => 'Bosanski',
        'ca'             => 'Català',
        'ceb'            => 'Cebuano',
        'cs_CZ'          => 'Čeština',
        'cy'             => 'Cymraeg',
        'da_DK'          => 'Dansk',
        'de_DE_formal'   => 'Deutsch (Sie)',
        'de_DE'          => 'Deutsch',
        'de_CH_informal' => 'Deutsch (Schweiz, Du)',
        'de_CH'          => 'Deutsch (Schweiz)',
        'de_AT'          => 'Deutsch (Österreich)',
        'dsb'            => 'Dolnoserbšćina',
        'dzo'            => 'རྫོང་ཁ',
        'el'             => 'Ελληνικά',
        'en_CA'          => 'English (Canada)',
        'en_NZ'          => 'English (New Zealand)',
        'en_ZA'          => 'English (South Africa)',
        'en_GB'          => 'English (UK)',
        'en_AU'          => 'English (Australia)',
        'eo'             => 'Esperanto',
        'es_DO'          => 'Español de República Dominicana',
        'es_CR'          => 'Español de Costa Rica',
        'es_VE'          => 'Español de Venezuela',
        'es_CO'          => 'Español de Colombia',
        'es_CL'          => 'Español de Chile',
        'es_UY'          => 'Español de Uruguay',
        'es_PR'          => 'Español de Puerto Rico',
        'es_ES'          => 'Español',
        'es_GT'          => 'Español de Guatemala',
        'es_PE'          => 'Español de Perú',
        'es_MX'          => 'Español de México',
        'es_EC'          => 'Español de Ecuador',
        'es_AR'          => 'Español de Argentina',
        'et'             => 'Eesti',
        'eu'             => 'Euskara',
        'fa_AF'          => '(فارسی (افغانستان',
        'fa_IR'          => 'فارسی',
        'fi'             => 'Suomi',
        'fr_FR'          => 'Français',
        'fr_CA'          => 'Français du Canada',
        'fr_BE'          => 'Français de Belgique',
        'fur'            => 'Friulian',
        'gd'             => 'Gàidhlig',
        'gl_ES'          => 'Galego',
        'gu'             => 'ગુજરાતી',
        'haz'            => 'هزاره گی',
        'he_IL'          => 'עִבְרִית',
        'hi_IN'          => 'हिन्दी',
        'hr'             => 'Hrvatski',
        'hsb'            => 'Hornjoserbšćina',
        'hu_HU'          => 'Magyar',
        'hy'             => 'Հայերեն',
        'id_ID'          => 'Bahasa Indonesia',
        'is_IS'          => 'Íslenska',
        'it_IT'          => 'Italiano',
        'ja'             => '日本語',
        'jv_ID'          => 'Basa Jawa',
        'ka_GE'          => 'ქართული',
        'kab'            => 'Taqbaylit',
        'kk'             => 'Қазақ тілі',
        'km'             => 'ភាសាខ្មែរ',
        'kn'             => 'ಕನ್ನಡ',
        'ko_KR'          => '한국어',
        'ckb'            => 'كوردی‎',
        'lo'             => 'ພາສາລາວ',
        'lt_LT'          => 'Lietuvių kalba',
        'lv'             => 'Latviešu valoda',
        'mk_MK'          => 'Македонски јазик',
        'ml_IN'          => 'മലയാളം',
        'mn'             => 'Монгол',
        'mr'             => 'मराठी',
        'ms_MY'          => 'Bahasa Melayu',
        'my_MM'          => 'ဗမာစာ',
        'nb_NO'          => 'Norsk bokmål',
        'ne_NP'          => 'नेपाली',
        'nl_NL_formal'   => 'Nederlands (Formeel)',
        'nl_BE'          => 'Nederlands (België)',
        'nl_NL'          => 'Nederlands',
        'nn_NO'          => 'Norsk nynorsk',
        'oci'            => 'Occitan',
        'pa_IN'          => 'ਪੰਜਾਬੀ',
        'pl_PL'          => 'Polski',
        'ps'             => 'پښتو',
        'pt_PT'          => 'Português',
        'pt_PT_ao90'     => 'Português (AO90)',
        'pt_AO'          => 'Português de Angola',
        'pt_BR'          => 'Português do Brasil',
        'rhg'            => 'Ruáinga',
        'ro_RO'          => 'Română',
        'ru_RU'          => 'Русский',
        'sah'            => 'Сахалыы',
        'snd'            => 'سنڌي',
        'si_LK'          => 'සිංහල',
        'sk_SK'          => 'Slovenčina',
        'skr'            => 'سرائیکی',
        'sl_SI'          => 'Slovenščina',
        'sq'             => 'Shqip',
        'sr_RS'          => 'Српски језик',
        'sv_SE'          => 'Svenska',
        'sw'             => 'Kiswahili',
        'szl'            => 'Ślōnskŏ gŏdka',
        'ta_IN'          => 'தமிழ்',
        'ta_LK'          => 'தமிழ்',
        'te'             => 'తెలుగు',
        'th'             => 'ไทย',
        'tl'             => 'Tagalog',
        'tr_TR'          => 'Türkçe',
        'tt_RU'          => 'Татар теле',
        'tah'            => 'Reo Tahiti',
        'ug_CN'          => 'ئۇيغۇرچە',
        'uk'             => 'Українська',
        'ur'             => 'اردو',
        'uz_UZ'          => 'O‘zbekcha',
        'vi'             => 'Tiếng Việt',
        'zh_TW'          => '繁體中文',
        'zh_HK'          => '香港中文版	',
        'zh_CN'          => '简体中文',
    );
}
// Define rtl
function woo_invoice_is_rtl() {
    global $locale;
    if ( false !== strpos( $locale, 'ar' )
        || false !== strpos( $locale, 'he' )
        || false !== strpos( $locale, 'he_IL' )
        || false !== strpos( $locale, 'ur' )
    ) {
        $rtl = true;
    } else {
        $rtl = false;
    }

    return $rtl;
}

/**
 * Check if HPOS (High-Performance Order Storage) is enabled.
 *
 * @return bool
 */
function woo_invoice_hpos_enabled() {
    return class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
}

/**
 * Get WC_Order object from the given value.
 *
 * @since 3.6.27
 * @param int|WC_Order $order Order id or order object
 * @return WC_Order|bool Order object or false if invalid
 */
function woo_invoice_get_order($order) {
    // Check if the order is an integer
    if (is_int($order)) {
        $order = wc_get_order($order);
    }
    // Check if the order is a string that can be converted to a positive integer
    elseif (is_string($order) && 0 < absint($order)) {
        $order = wc_get_order(absint($order));
    }

    // If the order is already a WC_Order object, return it
    if ($order instanceof WC_Order) {
        return $order;
    }

    // Return false if the order is invalid
    return false;
}

/**
 * Get order meta data depending on whether HPOS is enabled.
 *
 * @param int|WC_Order $order Order id or order object
 * @param string $meta_key
 * @param bool $is_single
 * @return mixed
 */
function woo_invoice_get_meta($order, $meta_key, $is_single = true) {
    $order = woo_invoice_get_order($order);

    if (!$order) {
        return false;
    }

    if (woo_invoice_hpos_enabled()) {
        return $order->get_meta($meta_key, $is_single);
    }
    return get_post_meta($order->get_id(), $meta_key, $is_single);
}

/**
 * Update order meta data depending on whether HPOS is enabled.
 *
 * @param int|WC_Order $order Order id or order object
 * @param string $meta_key
 * @param mixed $meta_value
 */
function woo_invoice_update_meta($order, $meta_key, $meta_value) {
    $order = woo_invoice_get_order($order);

    if (!$order) {
        return false;
    }

    if (woo_invoice_hpos_enabled()) {
        $order->update_meta_data($meta_key, $meta_value);
        $order->save();
    } else {
        update_post_meta($order->get_id(), $meta_key, $meta_value);
    }
    return true;
}