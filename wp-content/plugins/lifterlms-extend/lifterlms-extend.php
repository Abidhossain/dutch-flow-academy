<?php
/**
 * Plugin Name: LifterLMS Extend
 * Plugin URI: https://yourwebsite.com/lifterlms-extend
 * Description: Extends LifterLMS functionality with additional features
 * Version: 1.1.0
 * Author: Rejown Ahmed Zisan
 * Author URI: https://rejownahmed.me
 * Text Domain: lifterlms-extend
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires LifterLMS: 7.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

// Include dependencies
require_once plugin_dir_path(__FILE__) . 'includes/class-lifterlms-extend-setup.php';
require_once plugin_dir_path(__FILE__) . 'includes/api/class-llms-extend-rest-api.php';

// Define plugin constants
define('LLMS_EXTEND_VERSION', '1.0.0');
define('LLMS_EXTEND_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LLMS_EXTEND_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LLMS_EXTEND_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class LifterLMS_Extend {

    /**
     * Singleton instance
     *
     * @var LifterLMS_Extend
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return LifterLMS_Extend
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * REST API instance
     *
     * @var LLMS_Extend_REST_API
     */
    private $rest_api = null;

    /**
     * Initialize plugin
     *
     * @return void
     */
    public function init() {
        // Check if LifterLMS is active
        if (!$this->check_lifterlms_dependency()) {
            return;
        }

        // Initialize REST API
        $this->rest_api = new LLMS_Extend_REST_API();

        // Initialize your plugin features here
    }

    /**
     * Check if LifterLMS is installed and active
     *
     * @return bool
     */
    private function check_lifterlms_dependency() {
        if (!class_exists('LifterLMS')) {
            add_action('admin_notices', array($this, 'lifterlms_missing_notice'));
            return false;
        }
        return true;
    }

    /**
     * Admin notice for missing LifterLMS
     *
     * @return void
     */
    public function lifterlms_missing_notice() {
        if (current_user_can('activate_plugins')) {
            $lifterlms_url = 'https://wordpress.org/plugins/lifterlms/';
            /* translators: %s: URL to LifterLMS plugin */
            $message = sprintf(
                __('LifterLMS Extend requires LifterLMS to be installed and activated. Please %1$sinstall LifterLMS%2$s to continue.', 'lifterlms-extend'),
                '<a href="' . esc_url($lifterlms_url) . '" target="_blank">',
                '</a>'
            );
            echo '<div class="notice notice-error"><p>' . wp_kses_post($message) . '</p></div>';
        }
    }
}

// Initialize the plugin
function lifterlms_extend() {
    return LifterLMS_Extend::instance();
}

// Start the plugin
lifterlms_extend();