<?php
/**
 * Scripts for plugin functions
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/settings
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * Load all the scripts those require as global functions
 */
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/functions/load-mpdf-lib.php';
