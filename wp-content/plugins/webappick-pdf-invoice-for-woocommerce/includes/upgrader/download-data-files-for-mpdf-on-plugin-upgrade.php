<?php
/**
 * Scripts for plugin auto updater / upgrade hook.
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
if ( ! function_exists("challan_download_data_dat_files_on_plugin_update_or_upgrade") ) {

    /**
     * register hook for plugin update / auto-update / upgrade.
     */
//    add_action('upgrader_process_complete', 'challan_download_data_dat_files_on_plugin_update_or_upgrade', 10, 2);

    /**
     * Download data files on update plugin.
     *
     * @param   $upgrader_object
     * @param   $options
     * @return  void
     * @since   3.3.25
     */
    function challan_download_data_dat_files_on_plugin_update_or_upgrade( $upgrader_object, $options ) {
        if ( ! class_exists("Challan_PluginDataDatDownloader") ) {
            require_once CHALLAN_FREE_ROOT_FILE_PATH . "includes/classes/class-plugin-font-data-dat-downloader.php";
        }
        $current_plugin_path_name = plugin_basename(CHALLAN_FREE_ROOT_FILE);
        if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
            foreach ( $options['plugins'] as $each_plugin ) {
                if ( $each_plugin == $current_plugin_path_name ) {
                    //download data files
                    Challan_PluginDataDatDownloader::downloadDataFiles();
                    flush_rewrite_rules();
                }
            }
        }
    }
}

//phpcs:ignore
//manually trigger a hook:upgrader_process_complete
//do_action( 'upgrader_process_complete', [], ['action' => "update", "type" => "plugin", "plugins" => ["webappick-pdf-invoice-for-woocommerce/woo-invoice.php"]]);


if ( ! function_exists("challan_maybe_require_plugin_default_data_dat_download_double_checker") ) {

    /**
     * Double check data files for MPDF has been downloaded, plugin on install or update.
     *
     * @return bool
     * @since  3.3.25
     */
    function challan_maybe_require_plugin_default_data_dat_download_double_checker() {
        if ( ! function_exists('get_plugin_data') ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( ! class_exists("Challan_PluginDataDatDownloader") ) {
            require_once CHALLAN_FREE_ROOT_FILE_PATH . "includes/classes/class-plugin-font-data-dat-downloader.php";
        }
        $plugin_data = get_plugin_data(CHALLAN_FREE_ROOT_FILE);
        $version = explode('.', $plugin_data['Version']);
        //check plugin version >= 3.3.24
        if (
            (int)$version[0] > 3
            || ( 3 === (int)$version[0] && (int)$version[1] >= 3 && (int)$version[2] >= 24)
        ) {
            //call font downloader
            $default_data_dat_downloaded = get_option("challan_default_data_dat_downloaded", false);
            if ( ! $default_data_dat_downloaded ) {
                //download data files
                Challan_PluginDataDatDownloader::downloadDataFiles();
                update_option("challan_default_data_dat_downloaded", true);
                flush_rewrite_rules();
                return true;
            }
        }
        return false;
    }

    /**
     * Call MPDF data download double checker
     */
    if ( is_admin() ) {
//        challan_maybe_require_plugin_default_data_dat_download_double_checker();
    }
}
