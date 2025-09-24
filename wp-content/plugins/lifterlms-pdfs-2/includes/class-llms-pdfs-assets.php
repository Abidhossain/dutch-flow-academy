<?php
/**
 * LLMS_PDFS_Assets class.
 *
 * @package LifterLMS_Custom_Fields/Classes
 *
 * @since 2.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register & enqueue Scripts & Styles
 *
 * @since 2.0.0
 */
class LLMS_PDFS_Assets {

	use LLMS_Trait_Singleton;

	/**
	 * Instance of `LLMS_Assets`
	 *
	 * @var LLMS_Assets
	 */
	public $assets;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {

		// Load an instance of the LLMS_Assets class.
		$this->assets = new LLMS_Assets(
			'llms-pdfs',
			array(
				// Base defaults shared by all asset types.
				'base'   => array(
					'base_file' => LLMS_PDFS_PLUGIN_FILE,
					'base_url'  => LLMS_PDFS_PLUGIN_URL,
					'version'   => LLMS_PDFS_VERSION,
					'suffix'    => '', // Only minified files are distributed.
				),
				// Script specific defaults.
				'script' => array(
					'translate'  => true,
					'asset_file' => true,
				),
			)
		);

	}

}

return LLMS_PDFS_Assets::instance();
