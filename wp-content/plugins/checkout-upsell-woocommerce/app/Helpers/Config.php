<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

class Config
{
    /**
     * To hold local config data.
     *
     * @var array
     */
    private static $config;

    /**
     * To hold settings data.
     *
     * @var array
     */
    private static $settings;

    /**
     * Get config from local or options table.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = false)
    {
        if (empty($key)) {
            return false;
        }

        if (!isset(self::$config)) {
            self::$config = require CUW_PLUGIN_PATH . '/config.php';
        }

        if (array_key_exists($key, self::$config)) {
            return self::$config[$key];
        } else if (strpos($key, '.') !== false) {
            $config = self::$config;
            foreach (explode('.', $key) as $index) {
                if (!is_array($config) || !array_key_exists($index, $config)) {
                    return $default;
                }
                $config = &$config[$index];
            }
            return $config;
        } else {
            $key = sanitize_key($key);
            if (empty($key)) {
                return false;
            }
            $key = self::get('plugin.prefix', 'cuw_') . $key;
            return get_option($key, $default);
        }
    }

    /**
     * Set config to options table.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set($key, $value)
    {
        $key = sanitize_key($key);
        if (empty($key)) {
            return false;
        }

        $key = self::get('plugin.prefix', 'cuw_') . $key;
        return update_option($key, $value);
    }

    /**
     * Get setting.
     *
     * @param string $key
     * @return mixed
     */
    public static function getSetting($key)
    {
        return self::getSettings()[$key] ?? false;
    }

    /**
     * Get settings.
     *
     * @return array
     */
    public static function getSettings()
    {
        if (isset(self::$settings)) {
            return self::$settings;
        }

        self::$settings = self::get('settings', []);
        foreach (self::getDefaultSettings() as $key => $default_setting) {
            if (!isset(self::$settings[$key])) {
                self::$settings[$key] = $default_setting;
            }
        }
        return self::$settings;
    }

    /**
     * To get email settings.
     *
     * @param string $email_id
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getEmailSettings($email_id, $key, $default = false)
    {
        $data = get_option('woocommerce_cuw_' . $email_id . '_settings', []);
        return $data[$key] ?? $default;
    }

    /**
     * To update email settings.
     *
     * @param string $email_id
     * @param array $values
     * @return bool
     */
    public static function updateEmailSettings($email_id, $values)
    {
        if (!empty($email_id) && is_array($values) && !empty($values)) {
            $option_key = 'woocommerce_cuw_' . $email_id . '_settings';
            return update_option($option_key, array_merge(get_option($option_key, []), $values));
        }
        return false;
    }

    /**
     * Get default settings.
     */
    public static function getDefaultSettings()
    {
        // translatable default texts
        __("Offer applied successfully.", 'checkout-upsell-woocommerce');

        return apply_filters('cuw_default_settings', [
            'show_product_details' => 'disable',
            'calculate_discount_from' => 'regular_price',
            'exclude_coupon_discounts' => '',
            'smart_products_display' => '',
            'dynamic_offer_display' => '',
            'offer_add_limit' => '',
            'offer_display_mode' => 'first_matched',
            'offer_added_notice_message' => 'Offer applied successfully.',
            'offer_notice_display_location' => 'default',
            'always_display_offer' => '',
            'fbt_products_display_limit' => '2',
            'variant_select_template' => 'variant-select',
        ]);
    }
}