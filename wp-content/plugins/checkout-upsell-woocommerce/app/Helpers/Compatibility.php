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

use CUW\App\Modules\Compatibilities;

class Compatibility
{
    /**
     * To hold compatibilities data.
     *
     * @var array
     */
    private static $compatibilities;

    /**
     * To hold configurations data.
     *
     * @var array
     */
    private static $configurations;

    /**
     * Get compatibilities.
     *
     * @return array|false
     */
    public static function get($key = '')
    {
        if (!isset(self::$compatibilities)) {
            self::$compatibilities = apply_filters('cuw_compatibilities', [
                'wdr_v2' => [
                    'name' => 'Discount Rules for WooCommerce',
                    'type' => 'plugin',
                    'file' => 'woo-discount-rules/woo-discount-rules.php',
                    'author' => 'Flycart',
                    'handler' => new Compatibilities\WDRv2(),
                ],
                'wcs' => [
                    'name' => 'WooCommerce Subscriptions',
                    'type' => 'plugin',
                    'file' => 'woocommerce-subscriptions/woocommerce-subscriptions.php',
                    'author' => 'WooCommerce',
                    'handler' => new Compatibilities\WCS(),
                ],
                'woocs' => [
                    'name' => 'FOX - Currency Switcher (formally WOOCS)',
                    'type' => 'plugin',
                    'file' => 'woocommerce-currency-switcher/index.php',
                    'author' => 'realmag777',
                    'handler' => new Compatibilities\WOOCS(),
                ],
                'curcy' => [
                    'name' => 'CURCY - Multi Currency for WooCommerce',
                    'type' => 'plugin',
                    'file' => 'woo-multi-currency/woo-multi-currency.php',
                    'author' => 'VillaTheme',
                    'handler' => new Compatibilities\CURCY(),
                ],
                'cswoo' => [
                    'name' => 'Currency Switcher for WooCommerce',
                    'type' => 'plugin',
                    'file' => 'currency-switcher-woocommerce/currency-switcher-woocommerce.php',
                    'author' => 'WP Wham',
                    'handler' => new Compatibilities\CSWOO(),
                ],
                'wcml' => [
                    'name' => 'WooCommerce Multilingual & Multicurrency',
                    'type' => 'plugin',
                    'file' => 'woocommerce-multilingual/wpml-woocommerce.php',
                    'author' => 'OnTheGoSystems',
                    'handler' => new Compatibilities\WCML(),
                ],
                'sgc' => [
                    'name' => 'Speed Optimizer',
                    'type' => 'plugin',
                    'file' => 'sg-cachepress/sg-cachepress.php',
                    'author' => 'SiteGround',
                    'handler' => new Compatibilities\SGC(),
                ],
                'flatsome' => [
                    'name' => 'Flatsome',
                    'type' => 'theme',
                    'slug' => 'flatsome',
                    'author' => 'UX-Themes',
                    'handler' => new Compatibilities\Flatsome(),
                ],
                'woodmart' => [
                    'name' => 'Woodmart',
                    'type' => 'theme',
                    'slug' => 'woodmart',
                    'author' => 'XTemos',
                    'handler' => new Compatibilities\Woodmart(),
                ],
                'shoptimizer' => [
                    'name' => 'Shoptimizer',
                    'type' => 'theme',
                    'slug' => 'shoptimizer',
                    'author' => 'CommerceGurus',
                    'handler' => new Compatibilities\Shoptimizer(),
                ],
            ]);
        }
        if ($key !== '') {
            return self::$compatibilities[$key] ?? false;
        }
        return self::$compatibilities;
    }

    /**
     * Init hooks and run scripts
     */
    public static function init()
    {
        self::initHooks();
        self::runScripts();
    }

    /**
     * Init hooks
     */
    private static function initHooks()
    {
        add_action('cuw_save_settings', [__CLASS__, 'save']);
    }

    /**
     * Check if the plugin compatibility is active or not.
     *
     * @return bool
     */
    private static function isActive($key)
    {
        if (!isset(self::$configurations)) {
            self::$configurations = Config::get('compatibilities', []);
        }
        return self::$configurations[$key] ?? true;
    }

    /**
     * Display in settings.
     *
     * @return array
     */
    public static function getListToDisplay()
    {
        $compatibilities = [];
        foreach (self::get() as $key => $script) {
            if (empty($script['type']) || !self::isPackageActive($script)) {
                continue;
            }
            $compatibilities[$key] = array_merge($script, ['key' => $key, 'active' => self::isActive($key)]);
        }
        return $compatibilities;
    }

    /**
     * To check if the plugin or theme is active.
     *
     * @return boolean
     */
    public static function isPackageActive($script)
    {
        if ($script['type'] == 'plugin' && !empty($script['file'])) {
            return Plugin::isActive($script['file']);
        } else if ($script['type'] == 'theme' && !empty($script['slug'])) {
            return WP::getCurrentThemeSlug() == $script['slug'];
        }
        return false;
    }

    /**
     * Save compatibility settings.
     */
    public static function save($data)
    {
        $configurations = [];
        foreach (self::getListToDisplay() as $key => $script) {
            $configurations[$key] = !empty($data['compatibilities'][$key]);
        }
        Config::set('compatibilities', $configurations);
    }

    /**
     * Run scripts.
     */
    private static function runScripts()
    {
        foreach (self::get() as $key => $script) {
            if (!self::isActive($key) || !self::isPackageActive($script)) {
                continue;
            }

            if (!empty($script['handler']) && is_a($script['handler'], 'CUW\App\Modules\Compatibilities\Base')) {
                $script['handler']->run();
            }
        }
    }
}