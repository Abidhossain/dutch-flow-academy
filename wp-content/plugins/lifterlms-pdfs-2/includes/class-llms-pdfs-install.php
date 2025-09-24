<?php
/**
 * LLMS_PDFS_Install class file.
 *
 * @package LifterLMS_PDFS/Classes
 *
 * @since 2.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Install and update routines.
 *
 * @since 2.0.0
 */
class LLMS_PDFS_Install {

	/**
	 * Static constructor.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
	}

	/**
	 * Checks the current LLMS version and runs installer if required
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public static function check_version() {

		if ( ! defined( 'IFRAME_REQUEST' ) && self::get_version_option( 'current' ) !== llms_pdfs()->version ) {

			self::install();

			/**
			 * Action triggered after LifterLMS PDFs is updated.
			 *
			 * @since 2.0.0
			 */
			do_action( 'llms_pdfs_updated' );

		}

	}

	/**
	 * Runs the install routine.
	 *
	 * Updates version numbers and runs DB upgrades if needed.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean Returns `true` when installation runs and `false` if the blog isn't installed yet.
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return false;
		}

		/**
		 * Action triggered before the LifterLMS PDFs install routine runs.
		 *
		 * @since 2.0.0
		 */
		do_action( 'llms_pdfs_before_install' );

		self::update_db();
		self::set_version_option( 'db' );
		self::set_version_option( 'current' );

		/**
		 * Action triggered after the LifterLMS PDFs install routine runs.
		 *
		 * @since 2.0.0
		 */
		do_action( 'llms_pdfs_after_install' );

		return true;

	}

	/**
	 * Retrieves the value of a version option.
	 *
	 * @since 2.0.0
	 *
	 * @param string $option Option to retrieve, either "current" or "db".
	 * @return string|null Returns the version string or `null` if there is no saved version.
	 */
	public static function get_version_option( $option = 'current' ) {
		return get_option( self::get_version_option_name( $option ), null );
	}

	/**
	 * Retrieve the database option name for a give version option.
	 *
	 * @since 2.0.0
	 *
	 * @param string $option Option to name, either "current" or "db".
	 * @return string
	 */
	private static function get_version_option_name( $option ) {
		return "llms_pdfs_{$option}_version";
	}

	/**
	 * Update the value of a version option.
	 *
	 * @since 2.0.0
	 *
	 * @param string $option  Option to update, either "current" or "db".
	 * @param string $version Version string.
	 * @return boolean Returns `true` if the option was updated and `false` otherwise.
	 */
	public static function set_version_option( $option = 'current', $version = null ) {
		$name = self::get_version_option_name( $option );
		delete_option( $name );
		return update_option(
			$name,
			is_null( $version ) ? llms_pdfs()->version : $version,
			'current' === $option // Don't need to autoload the DB version.
		);
	}

	/**
	 * Runs database upgrade routines.
	 *
	 * This is an unsophisticate upgrade script that handles upgrading from version 1. In the future, should additional
	 * upgrades be required, this method will need to be rewritten, likely to use the `LLMS_DB_Upgrader` found in the LifterLMS
	 * core plugin.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean Returns `true` if a DB update ran and `false` otherwise.
	 */
	private static function update_db() {

		// If this option doesn't exist we don't need to perform an upgrade.
		if ( is_null( get_option( 'llms_pdfs_pdflayer_access_key', null ) ) ) {
			return false;
		}

		delete_option( 'llms_pdfs_pdflayer_access_key' );
		delete_option( 'llms_pdfs_pdflayer_access_key_status' );

		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';
		LLMS_Admin_Notices::add_notice(
			'llms-pdfs-upgrade-200',
			self::get_notice_html_200(),
			array(
				'type'             => 'success',
				'dismiss_for_days' => 0,
				'remindable'       => false,
			)
		);

		return true;

	}

	/**
	 * Retrieves the HTML for the 2.0.0 upgrade notice welcome message.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	private static function get_notice_html_200() {
		ob_start();
		?>
		<strong><?php _e( 'Welcome to LifterLMS PDFS v2.0!', 'lifterlms-pdfs' ); ?></strong>
		<p><?php _e( "This version removes the integration with the PDFLayer service in favor of a new self-hosted PDF generation method. You don't have to do anything to start using this new method, it's already setup and ready to go!", 'lifterlms-pdfs' ); ?></p>
		<p><?php _e( 'If you were previously using PDFLayer only to generate PDFs through this integration, you may wish to cancel your active subscription as it is no longer needed by LifterLMS PDFs.', 'lifterlms-pdfs' ); ?></p>
		<p>
		<?php
		printf(
			// Translators: %1$s = Opening anchor tag; %2$s = closing anchor tag.
			__( 'If you would like to read more about these changes %1$sclick here.%2$s', 'lifterlms-pdfs' ),
			'<a href="https://lifterlms.com/docs/pdflayer-sunset/" target="_blank" rel="noopener">',
			'</a>'
		);
		?>
		</p>
		<?php
		return ob_get_clean();
	}

}

return LLMS_PDFS_Install::init();
