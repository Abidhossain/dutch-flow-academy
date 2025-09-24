<?php
/**
 * Scripts for plugin activation hook
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

if ( ! function_exists("challan_download_default_fonts_on_plugin_activate") ) {
    /**
     * Download default fonts on activate plugin
     */
    function challan_download_default_fonts_on_plugin_activate() {
        if ( ! class_exists("Challan_PluginDefaultFontDownloader") ) {
            require_once CHALLAN_FREE_ROOT_FILE_PATH . "includes/classes/class-plugin-default-font-downloader.php";
        }
        Challan_PluginDefaultFontDownloader::downloadDefaultFonts();
        flush_rewrite_rules();
    }

    register_activation_hook(CHALLAN_FREE_ROOT_FILE, 'challan_download_default_fonts_on_plugin_activate');
}
