<?php
/**
 * Scripts for plugin settings
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

if ( ! function_exists("challan_admin_ui_fonts_dir_setting") ) {
    /**
     * Temp directory checker for MPDF.
     *
     * @return void
     */
    function challan_admin_ui_fonts_dir_setting() {
        if ( defined('CHALLAN_ADMIN_UI_FONTS_DIR') ) {
            /**
             * Setup fonts directory for admin ui
             *
             * @var string.
             * @since 2.3.1
             */
            //Make dir like /uploads/WOO-INVOICE-ADMIN-UI-FONTS/
            if ( ! file_exists(rtrim(CHALLAN_ADMIN_UI_FONTS_DIR, "/")) ) {
                mkdir(rtrim(CHALLAN_ADMIN_UI_FONTS_DIR, "/"), 0777, true);
            }
        }
    }

    //call immediate
    challan_admin_ui_fonts_dir_setting();
}

