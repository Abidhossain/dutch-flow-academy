<?php
/**
 * Scripts for add_filter | remove_filter | apply_filter
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
 * Load all the scripts those require for filtering
 */
//require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/filters/add-temp-dir-for-mpdf-using-add-filter.php';
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/filters/fix-font-name-for-mpdf-using-add-filter.php';