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
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Load all the scripts those require on plugin update or upgrade
 */
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/upgrader/download-mpdf-lib-on-plugin-upgrade.php';
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/upgrader/download-default-fonts-for-mpdf-on-plugin-upgrade.php';
