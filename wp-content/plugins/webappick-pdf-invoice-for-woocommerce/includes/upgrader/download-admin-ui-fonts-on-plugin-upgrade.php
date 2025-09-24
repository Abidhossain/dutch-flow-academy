<?php
/**
 * Scripts for plugin auto updater / upgrade hook. To download fonts for admin ui.
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/hooks
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined('ABSPATH') ) {
    exit;
}


if ( ! function_exists("challan_download_admin_ui_fonts_on_plugin_update_or_upgrade") ) {

    /**
     * register hook for plugin update / auto-update / upgrade.
     */
    add_action('upgrader_process_complete', 'challan_download_admin_ui_fonts_on_plugin_update_or_upgrade', 10, 2);

    /**
     * Download font files for admin ui on update plugin.
     *
     * @param   $upgrader_object
     * @param   $options
     * @return  void
     * @since   3.3.25
     */
    function challan_download_admin_ui_fonts_on_plugin_update_or_upgrade( $upgrader_object, $options ) {
        if ( ! class_exists("Challan_AdminUIFontDownloader") ) {
            require_once CHALLAN_FREE_ROOT_FILE_PATH . "includes/classes/class-admin-ui-font-downloader.php";
        }
        $current_plugin_path_name = plugin_basename(CHALLAN_FREE_ROOT_FILE);
        if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
            foreach ( $options['plugins'] as $each_plugin ) {
                if ( $each_plugin == $current_plugin_path_name ) {
                    //download data files
                    Challan_AdminUIFontDownloader::downloadFont();
                    flush_rewrite_rules();
                }
            }
        }
    }
}

//phpcs:ignore
//manually trigger a hook:upgrader_process_complete
//do_action( 'upgrader_process_complete', [], ['action' => "update", "type" => "plugin", "plugins" => ["webappick-pdf-invoice-for-woocommerce/woo-invoice.php"]]);


if ( ! function_exists('challan_maybe_require_admin_ui_fonts_download_double_checker') ) {

    /**
     * Double font for admin ui has been downloaded or not, plugin on install or update.
     *
     * @return bool
     * @since  3.3.25
     */
    function challan_maybe_require_admin_ui_fonts_download_double_checker() {
        if ( ! function_exists('get_plugin_data') ) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        if ( ! class_exists("Challan_AdminUIFontDownloader") ) {
            require_once CHALLAN_FREE_ROOT_FILE_PATH . "includes/classes/class-admin-ui-font-downloader.php";
        }
        $plugin_version = get_plugin_data(CHALLAN_FREE_ROOT_FILE)["Version"];
        if ( version_compare($plugin_version, '3.3.24') >= 0 ) {
            if ( ! get_option("challan_admin_ui_font_downloaded", false) ) {
                //download lib
                Challan_AdminUIFontDownloader::downloadFont();
                update_option("challan_admin_ui_font_downloaded", true);
                flush_rewrite_rules();
                return true;
            }
        }
        return false;
    }

    /**
     * Call MPDF lib double checker
     */
    if ( is_admin() ) {
        challan_maybe_require_admin_ui_fonts_download_double_checker();
    }
}


