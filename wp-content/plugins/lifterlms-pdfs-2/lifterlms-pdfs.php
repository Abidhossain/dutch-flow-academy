<?php
/**
 * LifterLMS PDFs Plugin
 *
 * @package  LifterLMS_PDF/Main
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 * Plugin Name: LifterLMS PDFs
 * Plugin URI: https://lifterlms.com/
 * Description: Generate PDFs for LifterLMS Certificates
 * Version: 2.1.0
 * Author: LifterLMS
 * Author URI: https://lifterlms.com/
 * Text Domain: lifterlms-pdfs
 * Domain Path: /i18n
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * LifterLMS Minimum: 6.0.0
 */

defined( 'ABSPATH' ) || exit;

// Define Constants.
if ( ! defined( 'LLMS_PDFS_PLUGIN_FILE' ) ) {
	define( 'LLMS_PDFS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'LLMS_PDFS_PLUGIN_DIR' ) ) {
	define( 'LLMS_PDFS_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
}

if ( ! defined( 'LLMS_PDFS_PLUGIN_URL' ) ) {
	define( 'LLMS_PDFS_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

// Load Plugin.
if ( ! class_exists( 'LifterLMS_PDFs' ) ) {
	require_once LLMS_PDFS_PLUGIN_DIR . 'class-lifterlms-pdfs.php';
}

/**
 * Main Plugin Instance
 *
 * @since 1.0.0
 *
 * @return LLMS_PDF
 */
function llms_pdfs() {
	return LifterLMS_PDFs::instance();
}
return llms_pdfs();
