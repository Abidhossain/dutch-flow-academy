<?php
/**
 * LifterLMS_PDFs class file.
 *
 * @package LifterLMS_PDF/Classes
 *
 * @since 1.0.0
 * @version 2.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS_PDFs main class
 *
 * @since 1.0.0
 */
final class LifterLMS_PDFs {

	/**
	 * Current version of the plugin
	 *
	 * @var string
	 */
	public $version = '2.1.0';

	/**
	 * Singleton instance of the class
	 *
	 * @var obj
	 */
	private static $instance = null;

	/**
	 * Singleton Instance of the LifterLMS_PDF class
	 *
	 * @since 1.0.0
	 *
	 * @return obj  instance of the LifterLMS_PDF class
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Remove text domain loading in favor of loading in the `init()` method.
	 *
	 * @return void
	 */
	private function __construct() {

		if ( ! defined( 'LLMS_PDFS_VERSION' ) ) {
			define( 'LLMS_PDFS_VERSION', $this->version );
		}

		add_action( 'plugins_loaded', array( $this, 'init' ) );

	}

	/**
	 * Determines if the plugin's dependencies are met.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	private function can_load() {
		return function_exists( 'llms' ) && version_compare( '6.0.0-alpha.2', llms()->version, '<=' );
	}

	/**
	 * Retrieves the main integration class instance.
	 *
	 * @since 2.0.0
	 *
	 * @return LLMS_Integration_PDFS
	 */
	public function get_integration() {
		return llms()->integrations()->get_integration( 'pdfs' );
	}

	/**
	 * Include files and instantiate classes
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Removed inclusion of deprecated `LLMS_PDFS_Pdflayer` class.
	 *              Added `LLMS_PDFS_Assets` and `LLMS_PDFS_Generator_JS`.
	 * @since 2.1.0 Added `LLMS_PDFS_Image_Proxy`.
	 *
	 * @return void
	 */
	public function includes() {

		require_once 'vendor/autoload.php';

		require_once LLMS_PDFS_PLUGIN_DIR . 'includes/class-llms-pdfs-assets.php';
		require_once LLMS_PDFS_PLUGIN_DIR . 'includes/class-llms-pdfs-generator-server-side.php';
		require_once LLMS_PDFS_PLUGIN_DIR . 'includes/class-llms-pdfs-image-proxy.php';
		require_once LLMS_PDFS_PLUGIN_DIR . 'includes/class-llms-pdfs-install.php';

		require_once LLMS_PDFS_PLUGIN_DIR . 'includes/abstracts/class-llms-pdfs-abstract-exportable-content.php';
		require_once LLMS_PDFS_PLUGIN_DIR . 'includes/abstracts/class-llms-pdfs-abstract-client-side-exportable-content.php';
		require_once LLMS_PDFS_PLUGIN_DIR . 'includes/abstracts/class-llms-pdfs-abstract-server-side-exportable-content.php';
		foreach ( glob( LLMS_PDFS_PLUGIN_DIR . 'includes/content-types/*.php' ) as $file ) {
			require_once $file;
		}

	}

	/**
	 * Include all required files and classes
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Use `can_load()` to determine if plugin requirements are met.
	 *              Load textdomain.
	 *
	 * @return void
	 */
	public function init() {

		if ( $this->can_load() ) {

			require_once LLMS_PDFS_PLUGIN_DIR . 'includes/class-llms-integration-pdfs.php';

			add_action( 'init', array( $this, 'load_textdomain' ), 0 );
			add_action( 'plugins_loaded', array( $this, 'includes' ), 100 );

			add_filter( 'lifterlms_integrations', array( $this, 'register_integration' ), 10 );

		}

	}

	/**
	 * Load l10n files
	 *
	 * Language files can be found in the following locations (The first loaded file takes priority):
	 *
	 *   1. wp-content/languages/lifterlms/lifterlms-pdfs-{LOCALE}.mo
	 *
	 *      This is recommended "safe" location where custom language files can be stored. A file
	 *      stored in this directory will never be automatically overwritten.
	 *
	 *   2. wp-content/languages/plugins/lifterlms-pdfs-{LOCALE}.mo
	 *
	 *      This is the default directory where WordPress will download language files from the
	 *      WordPress GlotPress server during updates. If you store a custom language file in this
	 *      directory it will be overwritten during updates.
	 *
	 *   3. wp-content/plugins/lifterlms/languages/lifterlms-pdfs-{LOCALE}.mo
	 *
	 *      This is the the LifterLMS plugin directory. A language file stored in this directory will
	 *      be removed from the server during a LifterLMS plugin update.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Use `llms_load_textdomain()`.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		llms_load_textdomain( 'lifterlms-pdfs', LLMS_PDFS_PLUGIN_DIR, 'i18n' );
	}

	/**
	 * Registers the integration with the LifterLMS Core plugin.
	 *
	 * @since 2.0.0
	 *
	 * @param string[] $integrations Array of existing integration class names.
	 * @return string[]
	 */
	public function register_integration( $integrations ) {
		$integrations[] = 'LLMS_Integration_PDFS';
		return $integrations;

	}

}
