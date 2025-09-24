<?php
/**
 * LLMS_PDFS_Abstract_Client_Side_Exportable_Content abstract class file.
 *
 * @package LifterLMS/Abstracts
 *
 * @since 2.0.0
 * @version 2.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class for client-side exportable content.
 *
 * @since 2.0.0
 */
abstract class LLMS_PDFS_Abstract_Client_Side_Exportable_Content extends LLMS_PDFS_Abstract_Exportable_Content {

	/**
	 * Determines if the content type's assets should be enqueued.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	abstract protected function should_enqueue();

	/**
	 * Initialize the content type.
	 *
	 * If the content type should load, registers assets and add hooks.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean Returns `true` if the export type is enabled and should load and `false` otherwise.
	 */
	public function init() {

		$should_load = parent::init();
		if ( $should_load ) {
			LLMS_PDFS_Assets::instance()->assets->define( 'scripts', $this->get_scripts() );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		}

		return $should_load;

	}

	/**
	 * Enqueues the content type's assets.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean Returns `false` when assets should not be enqueued, otherwise `true`.
	 */
	public function enqueue() {

		if ( ! $this->should_enqueue() ) {
			return false;
		}

		foreach ( array_keys( $this->get_scripts() ) as $handle ) {

			LLMS_PDFS_Assets::instance()->assets->enqueue_script( $handle );

			$localization_data = $this->get_localization_data( $handle );
			if ( $localization_data ) {
				list( $var, $data ) = $localization_data;
				wp_localize_script( $handle, $var, $data );
			}
		}

		return true;

	}

	/**
	 * Retrieves a list of the content type's scripts.
	 *
	 * @since 2.0.0
	 *
	 * @return array[] An array suitable to pass on to {@see LLMS_Assets::define()}.
	 */
	protected function get_scripts() {
		return array(
			"llms-pdfs-{$this->id}" => array(),
		);
	}

	/**
	 * Retrieves script localization data.
	 *
	 * @since 2.0.0
	 * @since 2.1.0 Add the `proxyUrl` when enabled.
	 *
	 * @param string $handle The script handle.
	 * @return array {
	 *     Localization data to pass to `wp_localize_script()`. If there's no localization data supplied the
	 *     array will be empty.
	 *
	 *     @type string $0 The localization variable name.
	 *     @type array  $1 Array of localization data.
	 * }
	 */
	protected function get_localization_data( $handle ) {

		$opts = array();
		if ( LLMS_PDFS_Image_Proxy::is_enabled() ) {
			$opts['proxyUrl'] = LLMS_PDFS_Image_Proxy::get_url();
		}

		/**
		 * Filters the localization default localization data for a client-side export.
		 *
		 * @since 2.1.0
		 *
		 * @param array  $opts   Localization data array.
		 * @param string $handle The script handle.
		 */
		$opts = apply_filters( 'llms_pdfs_client_side_export_options', $opts, $handle );

		if ( ! empty( $opts ) ) {
			return array( 'llmsPdfsClientSideExport', $opts );
		}

		return array();
	}

}
