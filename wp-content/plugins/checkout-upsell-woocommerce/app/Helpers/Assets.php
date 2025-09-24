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

class Assets
{
    /**
     * Styles
     *
     * @var array
     */
    public $styles = [];

    /**
     * Scripts
     *
     * @var array
     */
    public $scripts = [];

    /**
     * To hold properties.
     *
     * @var string|bool
     */
    private $prefix, $version, $load_minified;

    /**
     * Location to enqueue scripts
     *
     * @var array
     */
    protected $locations = [
        'front' => 'wp_enqueue_scripts',
        'admin' => 'admin_enqueue_scripts',
        'login' => 'login_enqueue_scripts',
        'customizer' => 'customize_preview_init',
    ];

    /**
     * Init assets
     *
     * @param $plugin Plugin
     */
    public function __construct($plugin)
    {
        $this->prefix = $plugin->prefix;
        $this->version = $plugin->version;
        $this->load_minified = !(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) && !$plugin->debug;
    }

    /**
     * Get asset url
     *
     * @param string $path
     * @return string
     */
    public static function getUrl($path)
    {
        return function_exists('plugin_dir_url') ? plugin_dir_url(CUW_PLUGIN_FILE) . "assets/" . $path : false;
    }

    /**
     * Check if the file exists or not
     *
     * @param string $path
     * @return bool
     */
    public static function fileExists($path)
    {
        return file_exists(CUW_PLUGIN_PATH . "assets/" . $path);
    }

    /**
     * Enqueue style
     *
     * @param string $name
     * @param string $file
     * @param array $deps
     * @return Assets
     */
    public function addCss($name, $file, array $deps = [])
    {
        if (!filter_var($file, FILTER_VALIDATE_URL)) {
            $extension = ".css";
            if ($this->load_minified && $this->fileExists("css/" . $file . ".min.css")) {
                $extension = ".min.css";
            }
            $file = $this->getUrl("css/" . $file . $extension);
        }

        $this->styles[$this->prefix . $name] = [
            'src' => $file,
            'deps' => $deps,
        ];

        return $this;
    }

    /**
     * Dequeue style
     *
     * @param string $name
     * @return Assets
     */
    public function removeCss($name)
    {
        if (isset($this->styles[$this->prefix . $name])) {
            unset($this->styles[$this->prefix . $name]);
        }

        return $this;
    }

    /**
     * Enqueue script
     *
     * @param string $name
     * @param string $file
     * @param array $data
     * @param array $deps
     * @return Assets
     */
    public function addJs($name, $file, array $data = [], array $deps = ['jquery'])
    {
        if (!filter_var($file, FILTER_VALIDATE_URL)) {
            $extension = ".js";
            if ($this->load_minified && self::fileExists("js/" . $file . ".min.js")) {
                $extension = ".min.js";
            }
            $file = $this->getUrl("js/" . $file . $extension);
        }

        $this->scripts[$this->prefix . $name] = [
            'src' => $file,
            'data' => $data,
            'deps' => $deps,
        ];

        return $this;
    }

    /**
     * Dequeue script
     *
     * @param string $name
     * @return Assets
     */
    public function removeJs($name)
    {
        if (isset($this->scripts[$this->prefix . $name])) {
            unset($this->scripts[$this->prefix . $name]);
        }

        return $this;
    }

    /**
     * Enqueue scripts
     *
     * @param string $location
     * @param int $priority
     */
    public function enqueue($location, $priority = 10)
    {
        if (!array_key_exists($location, $this->locations)) {
            throw new \UnexpectedValueException('Expected a valid location on enqueue method');
        }

        $data = [
            'styles' => $this->styles,
            'scripts' => $this->scripts,
            'version' => $this->version,
        ];

        add_action($this->locations[$location], function () use ($data) {
            foreach ($data['styles'] as $name => $style) {
                wp_enqueue_style($name, $style['src'], $style['deps'], $data['version']);
            }
            foreach ($data['scripts'] as $name => $script) {
                wp_enqueue_script($name, $script['src'], $script['deps'], $data['version']);
                if (!empty($script['data'])) {
                    wp_localize_script($name, $name, $script['data']);
                }
            }
        }, (int)$priority);

        $this->styles = [];
        $this->scripts = [];
    }

    /**
     * Frontend assets enqueue priority.
     *
     * @return int
     */
    public static function getFrontendEnqueuePriority()
    {
        return (int)apply_filters('cuw_frontend_assets_enqueue_priority', 10);
    }
}