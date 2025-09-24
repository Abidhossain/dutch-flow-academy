<?php
/**
 * Scripts for plugin deactivation hook
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/hooks
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function wapifw_deactivate() {
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
    wp_cache_flush();
}
register_deactivation_hook( CHALLAN_FREE_ROOT_FILE, 'wapifw_deactivate' );