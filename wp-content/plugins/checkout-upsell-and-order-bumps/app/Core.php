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

namespace CUW\App;

use CUW\App\Helpers\Assets;
use CUW\App\Helpers\Config;
use CUW\App\Helpers\Functions;
use CUW\App\Helpers\Input;
use CUW\App\Helpers\Plugin;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;

defined('ABSPATH') || exit;

class Core
{
    /**
     * Primary instance.
     *
     * @var Core
     */
    private static $app;

    /**
     * Functions helper instance.
     *
     * @var Functions
     */
    public $fn;

    /**
     * WordPress helper instance.
     *
     * @var WP
     */
    public $wp;

    /**
     * WooCommerce helper instance.
     *
     * @var WC
     */
    public $wc;

    /**
     * Input helper instance.
     *
     * @var Input
     */
    public $input;

    /**
     * Configuration helper instance.
     *
     * @var Config
     */
    public $config;

    /**
     * Plugin helper instance.
     *
     * @var Plugin
     */
    public $plugin;

    /**
     * Assets helper instance.
     *
     * @var Assets
     */
    public $assets;

    /**
     * To load secondary instances.
     */
    private function __construct()
    {
        $this->fn = new Functions();
        $this->wp = new WP();
        $this->wc = new WC();
        $this->input = new Input();
        $this->config = new Config();
        $this->plugin = new Plugin();
        $this->assets = new Assets($this->plugin);
    }

    /**
     * Return app (primary) instance.
     *
     * @return Core
     */
    public static function instance()
    {
        if (!isset(self::$app)) {
            self::$app = new Core();
        }
        return self::$app;
    }

    /**
     * View file.
     *
     * @param string $path
     * @param array $data
     * @param bool $print
     * @return false|string
     */
    public function view($path, $data = [], $print = true)
    {
        if (strpos($path, 'Pro/') !== false) {
            $file = CUW_PLUGIN_PATH . '/app/Pro/Views/' . ltrim($path, 'Pro/') . '.php';
        } else {
            $file = CUW_PLUGIN_PATH . '/app/Views/' . $path . '.php';
        }

        $output = $this->fn->renderTemplate($file, $data);
        if ($print) {
            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        return $output;
    }

    /**
     * Template file.
     *
     * @param string $file_or_path
     * @param array $data
     * @param bool $print
     * @return false|string
     */
    public function template($file_or_path, $data = [], $print = true)
    {
        $filepath = $file_or_path;
        if (strpos($filepath, '.php') === false && strpos($filepath, '.html') === false) {
            $filepath .= '.php';
        }

        $file = CUW_PLUGIN_PATH . '/templates/' . $filepath;
        if (function_exists('get_theme_file_path')) {
            $override_file_in_theme = get_theme_file_path($this->plugin->slug . '/' . $filepath);
            if (file_exists($override_file_in_theme)) {
                $file = $override_file_in_theme;
            }
        }

        $output = apply_filters('cuw_template', $this->fn->renderTemplate($file, $data), $file_or_path, $data);
        if ($print) {
            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        return $output;
    }

    /**
     * Bootstrap plugin.
     *
     * @return void
     * @deprecated
     */
    public function bootstrap()
    {
        // Silence is golden
    }
}