<?php
/**
 * LifterLMS Assignments Main Class
 *
 * @package LifterLMS_Assignments/Classes
 *
 * @since 1.0.0-beta.1
 * @version 2.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Assignments Main Class
 *
 * @since 1.0.0-beta.1
 * @since 1.1.2 Raise minimum required core version to 3.31.0.
 * @since 1.1.4 Include LLMS_Assignments_Capabilities class.
 */
final class LifterLMS_Assignments {

	/**
	 * Current version of the plugin
	 *
	 * @var string
	 */
	public $version = '2.3.3';

	/**
	 * Singleton instance of the class
	 *
	 * @var     obj
	 */
	private static $_instance = null;

	/**
	 * Singleton Instance of the LifterLMS_Assignments class
	 *
	 * @return   obj  instance of the LifterLMS_Assignments class.
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @return   void
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.6
	 */
	private function __construct() {

		if ( ! defined( 'LLMS_ASSIGNMENTS_VERSION' ) ) {
			define( 'LLMS_ASSIGNMENTS_VERSION', $this->version );
		}

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );

		// get started.
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Include files and instantiate classes
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.1.0 Add includes
	 * @since 1.1.4 Include LLMS_Assignments_Capabilities class.
	 *
	 * @return  void
	 */
	private function includes() {

		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-ajax.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-assets.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-capabilities.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-completion.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-grades.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-install.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-l10n.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-media-protection.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-posts.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-privacy.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-assignments-student-dashboard.php';

		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/class-llms-query-assignments-submission.php';

		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/functions-llms-assignments.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/functions-llms-assignments-templates.php';

		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/models/class-llms-assignment.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/models/class-llms-assignment-submission.php';
		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/models/class-llms-assignment-task.php';

		require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/notifications/class-llms-assignments-notifications.php';

		if ( is_admin() ) {

			require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/admin/class-llms-assignments-builder.php';
			require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/admin/class-llms-assignments-data.php';
			require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/admin/class-llms-assignments-reporting.php';
			require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/admin/class-llms-assignments-metaboxes.php';
			require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/admin/class-llms-table-assignments.php';
			require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/admin/class-llms-table-assignments-submissions.php';
			require_once LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/admin/class-llms-assignments-permalinks.php';

		}
	}

	/**
	 * Include all required files and classes
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.1.2 Raise minimum required core version to 3.31.0.
	 * @since 1.1.13 Use `llms()` in favor of `LLMS()` & raise minimum required core version to 4.21.2.
	 * @since 1.2.0 Raise minimum required core version to 5.3.0.
	 * @since 1.3.0 Raised the minimum required LifterLMS version to 6.0.0.
	 * @since 2.1.0 Raised the minimum required LifterLMS version to 7.2.0.
	 *
	 * @return void
	 */
	public function init() {

		// Only load if we have the minimum LifterLMS version installed & activated.
		if ( function_exists( 'llms' ) && version_compare( '7.2.0', llms()->version, '<=' ) ) {

			$this->includes();

		}
	}

	/**
	 * Load l10n files
	 * The first loaded file takes priority
	 *
	 * Files can be found in the following order:
	 *      WP_LANG_DIR/lifterlms/lifterlms-assignments-LOCALE.mo
	 *      WP_LANG_DIR/plugins/lifterlms-assignments-LOCALE.mo
	 *
	 * @return   void
	 * @since    1.0.0-beta.1
	 * @version  1.0.0-beta.1
	 */
	public function load_textdomain() {
		if ( function_exists( 'llms_load_textdomain' ) ) {
			llms_load_textdomain( 'lifterlms-assignments', LLMS_ASSIGNMENTS_PLUGIN_DIR, 'i18n' );
		}
	}
}
