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


/**
 * Download MPDF library
 */
register_activation_hook(CHALLAN_FREE_ROOT_FILE, function () {
    Challan_MpdfLibDownloader::downloadLib();
});
