<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-and-order-bumps
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

class Plugin
{
    /**
     * Active plugins.
     *
     * @var array
     */
    private static $active_plugins;

    /**
     * Well known properties.
     *
     * @var string|bool
     */
    public $name, $version, $debug, $prefix, $slug, $url, $support_url, $has_pro;

    /**
     * To load properties.
     */
    public function __construct()
    {
        foreach (Config::get('plugin') as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $this->debug = (bool)Config::get('debug');
        $this->has_pro = self::hasPro();
    }

    /**
     * Returns plugin url.
     *
     * @param string $utm_medium
     * @return string
     */
    public function getUrl($utm_medium = '')
    {
        if (!empty($utm_medium)) {
            return $this->url . '?' . http_build_query([
                    'utm_campaign' => 'upsellwp_plugin',
                    'utm_source' => $this->has_pro ? 'upsellwp_pro' : 'upsellwp_free',
                    'utm_medium' => $utm_medium,
                ]);
        }
        return $this->url;
    }

    /**
     * Returns plugin support url.
     *
     * @return string
     */
    public function getSupportUrl()
    {
        return $this->support_url . '?' . http_build_query([
                'utm_campaign' => 'upsellwp_plugin',
                'utm_source' => $this->has_pro ? 'upsellwp_pro' : 'upsellwp_free',
                'utm_medium' => 'help',
            ]);
    }

    /**
     * Check if this plugin has pro plugin files.
     *
     * @return bool
     */
    public static function hasPro()
    {
        return class_exists('\CUW\App\Pro\Route');
    }

    /**
     * Check dependencies
     *
     * @return bool
     */
    public static function checkDependencies()
    {
        $requires = Config::get('requires', []);
        $plugin_name = !self::hasPro() ? 'UpsellWP' : 'UpsellWP PRO';
        $error_message = self::getDependenciesError($requires, $plugin_name);
        if (!empty($error_message)) {
            WP::adminNotice($error_message, 'error');
            return false;
        }
        return true;
    }

    /**
     * Get all active plugins
     *
     * @return array
     */
    public static function activePlugins()
    {
        if (!isset(self::$active_plugins)) {
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins', []));
            if (function_exists('is_multisite') && is_multisite()) {
                $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', []));
            }
            self::$active_plugins = $active_plugins;
        }
        return self::$active_plugins;
    }

    /**
     * Check if the plugin is active or not
     *
     * @param string $file
     * @return bool
     */
    public static function isActive($file)
    {
        $active_plugins = self::activePlugins();
        return in_array($file, $active_plugins) || array_key_exists($file, $active_plugins);
    }

    /**
     * Get plugin data
     *
     * @param string $file
     * @return array
     */
    public static function getData($file)
    {
        $plugin_file = ABSPATH . 'wp-content/plugins/' . $file;
        if (file_exists($plugin_file) && function_exists('get_plugin_data')) {
            return get_plugin_data($plugin_file, true, false);
        }
        return [];
    }

    /**
     * Get plugin version
     *
     * @param string $file
     * @return string|null
     */
    public static function getVersion($file)
    {
        $data = self::getData($file);
        return $data['Version'] ?? null;
    }

    /**
     * Returns error message if requirement not satisfied.
     *
     * @param array $requires
     * @param string $plugin_name
     * @return string|false
     */
    public static function getDependenciesError($requires, $plugin_name = '')
    {
        /* translators: %s value. */
        $package_requirement_short = 'Requires %s plugin.';
        /* translators: 1: plugin name, 2: plugin name */
        $package_requirement = '%1$s requires %2$s plugin to be installed and active.';
        /* translators: 1: plugin name, 2: version */
        $version_requirement_short = 'Requires %1$s version %2$s or above.' ;
        /* translators: 1: plugin name, 2: version, 3:  */
        $version_requirement = '%1$s requires %2$s version %3$s or above.';

        if (!empty($requires['php'])) {
            if (!Functions::checkVersion(PHP_VERSION, $requires['php'])) {
                return empty($plugin_name) ? sprintf($version_requirement_short, 'PHP', $requires['php'])
                    : sprintf($version_requirement, $plugin_name, 'PHP', $requires['php']);
            }
        }

        global $wp_version;
        if (!empty($requires['wordpress'])) {
            if (!Functions::checkVersion($wp_version, $requires['wordpress'])) {
                $wordpress = 'WordPress';
                return empty($plugin_name) ? sprintf($version_requirement_short, $wordpress, $requires['wordpress'])
                    : sprintf($version_requirement, $plugin_name, $wordpress, $requires['wordpress']);
            }
        }

        if (!empty($requires['woocommerce'])) {
            $woocommerce_name = 'WooCommerce';
            $woocommerce_url = 'https://wordpress.org/plugins/woocommerce';
            $woocommerce = '<a href="' . esc_url($woocommerce_url) . '" target="_blank">' . esc_html($woocommerce_name) . '</a>';
            if (!defined('WC_VERSION')) {
                return empty($plugin_name) ? sprintf($package_requirement_short, $woocommerce)
                    : sprintf($package_requirement, $plugin_name, $woocommerce);
            }
            if (!Functions::checkVersion(WC_VERSION, $requires['woocommerce'])) {
                return empty($plugin_name) ? sprintf($version_requirement_short, $woocommerce, $requires['woocommerce'])
                    : sprintf($version_requirement, $plugin_name, $woocommerce, $requires['woocommerce']);
            }
        }

        if (!empty($requires['upsellwp']) && defined('CUW_VERSION')) {
            if (!Functions::checkVersion(CUW_VERSION, $requires['upsellwp'])) {
                return empty($plugin_name) ? sprintf($version_requirement_short, __('UpsellWP', 'checkout-upsell-and-order-bumps'), $requires['upsellwp'])
                    : sprintf($version_requirement, $plugin_name, __('UpsellWP', 'checkout-upsell-and-order-bumps'), $requires['upsellwp']);
            }
        }

        if (!empty($requires['upsellwp_pro']) && defined('CUW_VERSION')) {
            $upsellwp_pro_name = 'UpsellWP PRO';
            $upsellwp_pro_url = 'https://upsellwp.com?utm_campaign=upsellwp_plugin&utm_source=upsellwp_free&utm_medium=upgrade';
            $upsellwp_pro = '<a href="' . esc_url($upsellwp_pro_url) . '" target="_blank">' . esc_html($upsellwp_pro_name) . '</a>';
            if (!self::hasPro()) {
                return empty($plugin_name) ? sprintf($package_requirement_short, $upsellwp_pro)
                    : sprintf($package_requirement, $plugin_name, $upsellwp_pro);
            }
            if (!Functions::checkVersion(CUW_VERSION, $requires['upsellwp_pro'])) {
                return empty($plugin_name) ? sprintf($version_requirement_short, $upsellwp_pro, $requires['upsellwp_pro'])
                    : sprintf($version_requirement, $plugin_name, $upsellwp_pro, $requires['upsellwp_pro']);
            }
        }

        foreach ($requires['plugins'] ?? [] as $plugin) {
            if (!isset($plugin['name']) || !isset($plugin['file'])) {
                continue;
            }

            $formatted_name = $plugin['name'];
            if (isset($plugin['url'])) {
                $formatted_name = '<a href="' . esc_url($plugin['url']) . '" target="_blank">' . esc_html($formatted_name) . '</a>';
            }

            if (!self::isActive($plugin['file'])) {
                return empty($plugin_name) ? sprintf($package_requirement_short, $formatted_name)
                    : sprintf($package_requirement, $plugin_name, $formatted_name);
            }

            if (!empty($plugin['version'])) {
                $plugin_version = self::getVersion($plugin['file']);
                if (!empty($plugin_version) && !Functions::checkVersion($plugin_version, $plugin['version'])) {
                    return empty($plugin_name) ? sprintf($version_requirement_short, $formatted_name, $plugin['version'])
                        : sprintf($version_requirement, $plugin_name, $formatted_name, $plugin['version']);
                }
            }
        }

        return false;
    }

    /**
     * Check elementor plugin v3 is active.
     */
    public static function isElementorActive()
    {
        return defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.0', '>=');
    }
}
