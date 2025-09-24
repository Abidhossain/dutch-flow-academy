<?php
/**
 * LLMS_PDFS_Exportable_Certificates class file.
 *
 * @package LifterLMS_PDFS/Classes
 *
 * @since 2.0.0
 * @version 2.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Certificates client-side PDF exporter.
 *
 * @since 2.0.0
 */
class LLMS_PDFS_Exportable_Certificates extends LLMS_PDFS_Abstract_Client_Side_Exportable_Content {

	/**
	 * Unique content type ID.
	 *
	 * @var string
	 */
	protected $id = 'certificates';

	/**
	 * Registration priority.
	 *
	 * Used to order the content type on the settings page.
	 *
	 * @var integer
	 */
	protected $priority = 10;

	/**
	 * Retrieve certificate-specific settings.
	 *
	 * @since 2.0.0
	 *
	 * @param LLMS_User_Certificate $cert Certificate object.
	 * @return array
	 */
	private function get_certificate_settings( $cert ) {

		$is_legacy  = ( 1 === $cert->get_template_version() );
		$dimensions = $is_legacy ? $cert->get_background_image() : $cert->get_dimensions_for_display( false );

		return array(
			'orientation'     => $is_legacy ? $this::get_orientation_from_dimensions( $dimensions['width'], $dimensions['height'] ) : $cert->get_orientation(),
			'backgroundColor' => $cert->get_background(),
			'unit'            => $is_legacy ? 'px' : $cert->get_unit(),
			'width'           => $dimensions['width'],
			'height'          => $dimensions['height'],
		);

	}

	/**
	 * Determines orientation based on supplied dimensions.
	 *
	 * This is used by legacy certificates which determine orientation based on the dimensions
	 * of the background image. Since there's no orientation value saved we need to calculate it.
	 *
	 * @since 2.0.0
	 *
	 * @param int $width  The certificate's width.
	 * @param int $height The certificate's height.
	 * @return string Either "landscape" or "portrait".
	 */
	private function get_orientation_from_dimensions( $width, $height ) {
		return $width >= $height ? 'landscape' : 'portrait';
	}

	/**
	 * Retrieves script localization data.
	 *
	 * @since 2.0.0
	 * @since 2.1.0 Include proxy URL from parent method.
	 *
	 * @param string $handle The script handle.
	 * @return array {
	 *     Localization data to pass to `wp_localize_script()`.
	 *
	 *     @type string $0 The localization variable name.
	 *     @type array  $1 Array of localization data.
	 * }
	 */
	protected function get_localization_data( $handle ) {

		$parent      = parent::get_localization_data( $handle );
		$parent_data = array();
		if ( ! empty( $parent ) ) {
			list( , $parent_data ) = $parent;
		}

		$cert     = llms_get_certificate( get_the_ID(), true );
		$filename = sanitize_title(
			$cert->get( 'title' ),
			_x( 'certificate', 'certificate export filename fallback', 'lifterlms' )
		);

		/**
		 * Filters client-side options used for JS PDF generation.
		 *
		 * @since 2.0.0
		 *
		 * @param array $options {
		 *     Array of options.
		 *
		 *     @type string $imageFormat Image format embedded into the generated PDF. Available
		 *                               formats listed under `format` at {@link http://raw.githack.com/MrRio/jsPDF/master/docs/module-addImage.html}.
		 *     @type int    $scale       Scale factor. Higher number improves quality while increasing generation
		 *                               time. Must be >= 1 and <= 5.
		 *     @type string $filename    Filename of the generated PDF.
		 * }
		 * @param string $handle The handle of the script being localized.
		 */
		$options = apply_filters(
			'llms_pdfs_certificate_options',
			array(
				'imageFormat' => 'WEBP',
				'scale'       => 3,
				'filename'    => $filename . '.pdf',
			),
			$handle
		);

		return array( 'llmsPdfsCertificates', array_merge( $parent_data, $options, $this->get_certificate_settings( $cert ) ) );
	}

	/**
	 * Determines if the content type's assets should be enqueued.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	protected function should_enqueue() {
		return in_array( get_post_type(), array( 'llms_certificate', 'llms_my_certificate' ), true );
	}

	/**
	 * Sets the content type's description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function set_description() {
		return __( 'Replaces the default certificate download format with a PDF.', 'lifterlms-pdfs' );
	}

	/**
	 * Sets the content type's title.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function set_title() {
		return __( 'Certificates', 'lifterlms-pdfs' );
	}

}

return new LLMS_PDFS_Exportable_Certificates();
