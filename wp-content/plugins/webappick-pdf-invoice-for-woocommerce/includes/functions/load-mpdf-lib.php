<?php
/**
 * Global Functions
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/functions
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! function_exists('challan_load_mpdf_lib') ) :
/**
 * Load MPDF library
 *
 * @return bool
 */
function challan_load_mpdf_lib() {
    if ( class_exists('\Mpdf\Mpdf') || class_exists(\Mpdf\Mpdf::class) ) {
        return true;
    }
    if ( file_exists(CHALLAN_FREE_INVOICE_DIR . "mpdf/vendor/autoload.php") ) {
        require_once CHALLAN_FREE_INVOICE_DIR . "mpdf/vendor/autoload.php";
        return true;
    }
    return false;
}
challan_load_mpdf_lib();
endif;