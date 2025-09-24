<?php
/**
 * Plugin constant and variable declarations
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/configuration
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CHALLAN_FREE_VERSION', '3.7.71' );

if ( ! defined( 'CHALLAN_FREE_FONTS_COUNT' ) ) {
    /**
     * Count mPDF Fonts
     *
     * @var string dirname( __FILE__ )
     */
    define( 'CHALLAN_FREE_FONTS_COUNT', '40' );
}

if ( ! defined( 'CHALLAN_FREE_LIBS_PATH' ) ) {
    /**
     * Admin File Path with trailing slash
     *
     * @var string
     */
    define( 'CHALLAN_FREE_LIBS_PATH', CHALLAN_FREE_ROOT_FILE_PATH . 'libs/' );
}

if ( ! defined( 'CHALLAN_FREE_PLUGIN_URL' ) ) {
    /**
     * Plugin Directory URL
     *
     * @var string
     * @since 1.2.2
     */
    define( 'CHALLAN_FREE_PLUGIN_URL', trailingslashit( plugin_dir_url( CHALLAN_FREE_ROOT_FILE ) ) );
}

if ( ! defined( 'CHALLAN_FREE_ADMIN_PATH' ) ) {
    /**
     * Admin File Path with trailing slash.
     *
     * @var string
     */
    define( 'CHALLAN_FREE_ADMIN_PATH', CHALLAN_FREE_ROOT_FILE_PATH . 'admin/' );
}

if ( ! defined( 'CHALLAN_FREE_ADMIN_URL' ) ) {
    /**
     * Admin url.
     *
     * @var string
     */
    define( 'CHALLAN_FREE_ADMIN_URL', CHALLAN_FREE_PLUGIN_URL . 'admin/' );
}

if ( ! defined( 'CHALLAN_FREE_PLUGIN_BASE_NAME' ) ) {
    /**
     * Plugin Base name.
     *
     * @var string
     * @since 1.2.2
     */
    define( 'CHALLAN_FREE_PLUGIN_BASE_NAME', plugin_basename( CHALLAN_FREE_ROOT_FILE ) );
}

if ( ! defined( 'CHALLAN_FREE_EXTENSIONS' ) ) {

    define( 'CHALLAN_FREE_EXTENSIONS', [
        'ar'    => 'Arabic',
        'he_IL' => 'Hebrew',
    ] );
}

if ( ! defined( 'CHALLAN_FREENONCE') ) {
    define( 'CHALLAN_FREENONCE', 'wpifw_pdf_nonce' );
}

if ( ! defined( 'CHALLAN_FREE_FONT_DIR' ) ) {
    /**
     * Custom Font Directory..
     *
     * @var string
     * @since 2.3.1
     */
    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'];
    $wpifw_invoice_font_dir = $base_dir . "/WOO-INVOICE/WOO-INVOICE-FONTS";
    define('CHALLAN_FREE_FONT_DIR', trailingslashit($wpifw_invoice_font_dir));
}

if ( ! defined( 'CHALLAN_FREETEMP_DIR' ) ) {
    /**
     * Custom tempDir Directory for MPDF
     *
     * @var string
     * @since 2.3.1
     */
    //e.g dir like /WOO-INVOICE/WOO-INVOICE/TEMP
    define( 'CHALLAN_FREETEMP_DIR', wp_upload_dir()['basedir'] ."/WOO-INVOICE/TEMP/" );

}

if ( ! defined( 'CHALLAN_ADMIN_UI_FONTS_DIR' ) ) {
    /**
     * Custom fonts' directory for admin ui related fonts
     *
     * @var string
     * @since 2.3.1
     */
    //e.g dir like /uploads/WOO-INVOICE-ADMIN-UI-FONTS/
    define( 'CHALLAN_ADMIN_UI_FONTS_DIR', wp_upload_dir()['basedir'] ."/WOO-INVOICE-ADMIN-UI-FONTS/" );

}

// end of constants.php