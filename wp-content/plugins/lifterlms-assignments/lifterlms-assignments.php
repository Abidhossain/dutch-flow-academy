<?php
/**
 * LifterLMS Assignments Plugin
 *
 * @package LifterLMS_Assignments/Main
 *
 * @since 1.0.0-beta.1
 * @version 2.1.0
 *
 * @wordpress-plugin
 * Plugin Name: LifterLMS Assignments
 * Plugin URI: https://lifterlms.com/product/lifterlms-assignments/
 * Description: Get your learners taking action with tasks to be completed, uploads to submit, and more.
 * Version: 2.3.3
 * Author: LifterLMS
 * Author URI: https://lifterlms.com/
 * Text Domain: lifterlms-assignments
 * Domain Path: /i18n
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.6
 * Tested up to: 6.0
 * Requires PHP: 7.4
 * LLMS requires at least: 7.2.0
 * LLMS tested up to: 7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main LifterLMS Plugin File.
 *
 * @since 1.0.0-beta.1
 * @since 1.1.4 Simplify setting of the `LLMS_ASSIGNMENTS_PLUGIN_DIR` constant.
 *              Fix duplicate slashes when including main plugin class.
 */

if ( ! defined( 'LLMS_ASSIGNMENTS_PLUGIN_FILE' ) ) {
	define( 'LLMS_ASSIGNMENTS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'LLMS_ASSIGNMENTS_PLUGIN_DIR' ) ) {
	define( 'LLMS_ASSIGNMENTS_PLUGIN_DIR', __DIR__ . '/' );
}

// Load LifterLMS Assignments.
if ( ! class_exists( 'LifterLMS_Assignments' ) ) {
	require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'class-lifterlms-assignments.php';
}

/**
 * Main Plugin Instance.
 *
 * @since 1.0.0-beta.1
 *
 * @return LifterLMS_Assignments
 */
function LLMS_Assignments() { // phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return LifterLMS_Assignments::instance();
}

// Load i18n functions. This must be loaded before the add-on's current init.
require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/functions-llms-assignments-i18n.php';

/*
 * We want to verify permalinks on plugin activation and on core LifterLMS updates,
 * depending on the order things are upgraded.
 */
register_activation_hook( LLMS_ASSIGNMENTS_PLUGIN_FILE, 'llms_assignments_verify_permalinks' );
add_action( 'lifterlms_after_install', 'llms_assignments_verify_permalinks' );

return LLMS_Assignments();
