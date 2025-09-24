<?php
/**
 * LLMS_Integration_PDFS class.
 *
 * @package LifterLMS_Groups/Classes
 *
 * @since 2.0.0
 * @version 2.0.05
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS PDFs Integration Class
 *
 * @since 2.0.0
 */
class LLMS_Integration_PDFS extends LLMS_Abstract_Integration {

	/**
	 * Integration ID.
	 *
	 * @var string
	 */
	public $id = 'pdfs';

	/**
	 * Options data abstract version
	 *
	 * Autoload default values from integration settings when calling `get_option()`.
	 *
	 * @var int
	 */
	protected $version = 2;

	/**
	 * Integration Constructor
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function configure() {

		$this->title           = __( 'LifterLMS PDFs', 'lifterlms-pdfs' );
		$this->description     = __( 'Enables students to download PDF exports of content like certificates, grade and progress reports, transaction receipts, and more.', 'lifterlms-groups' );
		$this->plugin_basename = plugin_basename( LLMS_PDFS_PLUGIN_FILE );

		add_filter( 'llms_integration_pdfs_get_settings', array( $this, 'mod_default_settings' ), 1 );

	}

	/**
	 * Determines if PDF downloads are enabled for a given content type.
	 *
	 * The content type must be able to be enabled based on add-on requirements and
	 * the option must be explicitly enabled through the add-on settings page.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id The content type ID.
	 * @return boolean
	 */
	public function is_content_type_enabled( $id ) {

		$opt = $this->get_option( 'content_types', $this->get_default_content_type_option() );
		return ! empty( $opt[ $id ] ) ? llms_parse_bool( $opt[ $id ] ) : false;

	}

	/**
	 * Retrieves the default option value for the `content_type` option.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	private function get_default_content_type_option() {

		$settings = array_filter(
			$this->get_integration_settings(),
			// Remove non content-type settings.
			function( $setting ) {
				return ! empty( $setting['_x_content_type_id'] );
			}
		);

		// Return an array of id => yes/no value.
		return wp_list_pluck( $settings, 'default', '_x_content_type_id' );

	}

	/**
	 * Retrieves a list of the available exportable content types.
	 *
	 * @since 2.0.0
	 *
	 * @return LLMS_PDFS_Abstract_Exportable_Content[]
	 */
	public function get_exportable_content_types() {

		/**
		 * Filters the list of available exportable content types.
		 *
		 * @since 2.0.0
		 *
		 * @param LLMS_PDFS_Abstract_Exportable_Content[] $types List of content types.
		 */
		return apply_filters( 'llms_pdfs_exportable_content_types', array() );

	}

	/**
	 * Get integration settings
	 *
	 * @since 2.0.0
	 *
	 * @return array[]
	 */
	public function get_integration_settings() {
		$ret = include LLMS_PDFS_PLUGIN_DIR . '/includes/admin/integration-settings.php';
		return $ret;
	}

	/**
	 * This integration is always enabled.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return true;
	}

	/**
	 * Modify the default settings to remove the "Enabled" option.
	 *
	 * Since this integration is always enabled the option is not necessary.
	 *
	 * @since 2.0.0
	 *
	 * @param  array[] $settings Default settings array.
	 * @return array[]
	 */
	public function mod_default_settings( $settings ) {

		$ids   = wp_list_pluck( $settings, 'id' );
		$index = array_search( 'llms_integration_pdfs_enabled', $ids, true );
		if ( false !== $index ) {
			unset( $settings[ $index ] );
		}

		return array_values( $settings );

	}

}
