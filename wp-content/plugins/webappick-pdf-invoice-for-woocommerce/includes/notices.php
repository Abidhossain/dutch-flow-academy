<?php
/**
 * Scripts for showing notices
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/notices
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * Load all the scripts those require for showing notice
 */
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/notices/admin-notice-for-review.php';
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/notices/admin-notice-woocommerce-is-not-installed-error.php';
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/notices/admin-notice-wp-content-dir-is-not-writeable-error.php';
require_once CHALLAN_FREE_ROOT_FILE_PATH . 'includes/notices/admin-notice-invoice-dir-is-not-writeable-error.php';
