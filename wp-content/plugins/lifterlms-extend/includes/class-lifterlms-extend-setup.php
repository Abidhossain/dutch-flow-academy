<?php
/**
 * Handle plugin activation and deactivation
 */
class LifterLMS_Extend_Setup {

    /**
     * Check dependencies on activation
     *
     * @return void
     */
    public static function activate() {
        if (!function_exists('llms')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                __('This plugin requires LifterLMS to be installed and activated.', 'lifterlms-extend'),
                'Plugin Activation Error',
                array('back_link' => true)
            );
        }
    }

    /**
     * Cleanup on deactivation
     *
     * @return void
     */
    public static function deactivate() {
        // Add any cleanup code here
    }
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('LifterLMS_Extend_Setup', 'activate'));
register_deactivation_hook(__FILE__, array('LifterLMS_Extend_Setup', 'deactivate'));