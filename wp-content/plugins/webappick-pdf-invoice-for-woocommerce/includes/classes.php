<?php
/**
 * Scripts for plugin activation hook
 *
 * @since      3.3.27
 * @package    Challan_Free
 * @subpackage Challan_Free/classes
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



/**
 * Load all the classes those require to autoload.
 */

require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/classes/class-mpdf-lib-downloader.php';
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/classes/class-dropbox-font-downloader.php';
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/classes/class-plugin-default-font-downloader.php';
