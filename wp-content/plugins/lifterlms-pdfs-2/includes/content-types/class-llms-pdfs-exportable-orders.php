<?php
/**
 * LLMS_PDFS_Exportable_Orders class file.
 *
 * @package LifterLMS_PDFS/Classes
 *
 * @since 2.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Order data server-side PDF exporter.
 *
 * @since 2.0.0
 */
class LLMS_PDFS_Exportable_Orders extends LLMS_PDFS_Abstract_Server_Side_Exportable_Content {

	/**
	 * Unique content type ID.
	 *
	 * @var string
	 */
	protected $id = 'orders';

	/**
	 * Registration priority.
	 *
	 * Used to order the content type on the settings page.
	 *
	 * @var integer
	 */
	protected $priority = 20;

	/**
	 * List of hooks used to output the button.
	 *
	 * @var array[]
	 */
	protected $button_hooks = array(
		array( 'llms_view_order_after_secondary' ),
		array( 'lifterlms_after_order_meta_box' ),
	);

	/**
	 * Retrieves the HTML for the export form & button.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed ...$args Arguments from the hook.
	 * @return string
	 */
	protected function get_button_html( ...$args ) {

		if ( ! is_admin() ) {
			return parent::get_button_html( ...$args );
		}

		ob_start();
		llms_form_field( $this->get_button_settings() );
		wp_nonce_field( $this->id, $this->nonce_key, false );
		return ob_get_clean();
	}

	/**
	 * Retrieves the settings used to output the download button.
	 *
	 * @since 2.0.0
	 *
	 * @return array Array of settings suitable to pass to {@see llms_form_field()}.
	 */
	protected function get_button_settings() {

		$settings = parent::get_button_settings();

		if ( is_admin() ) {
			$settings['wrapper_classes'] = 'align-right';
			$settings['classes']        .= ' auto';
			$settings['attributes']      = array(
				'style' => 'margin-top: 30px',
			);
		}

		return $settings;

	}

	/**
	 * Retrieves the ID of the current object.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed ...$args Arguments from the hook.
	 * @return int
	 */
	protected function get_current_object_id( ...$args ) {

		$order = $args[0];
		return $order->get( 'id' );

	}

	/**
	 * Retrieves the filename for the exported PDF.
	 *
	 * @since 2.0.0
	 *
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Exportable_Content::parse_object_data()}.
	 * @return string
	 */
	protected function get_filename( $object_data ) {

		// Translators: %d = Order ID.
		return sanitize_file_name( sprintf( _x( 'order-%d', 'order export filename', 'lifterlms-pdfs' ), $object_data['id'] ) );

	}

	/**
	 * Retrieves the HTML to write to the PDF.
	 *
	 * @since 2.0.0
	 *
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Exportable_Content::parse_object_data()}.
	 * @return string
	 */
	protected function get_html( $object_data ) {
		ob_start();
		$order = new LLMS_Order( $object_data['id'] );
		include_once LLMS_PDFS_PLUGIN_DIR . 'includes/views/order.php';
		return ob_get_clean();
	}

	/**
	 * Retrieves PDF initialization arguments passed to {@see LLMS_PDFS_Generator_Server_Side::get()}.
	 *
	 * @since 2.0.0
	 *
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Server_Side_Exportable_Content::parse_object_data()}.
	 * @return array Array of arguments passed to {@see LLMS_PDFS_Generator_Server_Side::get()}.
	 */
	protected function get_pdf_args( $object_data ) {

		$args = parent::get_pdf_args( $object_data );
		// Translators: %d = Order ID.
		$args['title'] = sprintf( __( 'Order #%d', 'lifterlms-pdfs' ), $object_data['id'] );

		return $args;

	}

	/**
	 * Retrieves object data from form submission.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function parse_object_data() {

		if ( is_admin() && ! empty( llms_filter_input( INPUT_POST, $this->get_button_settings()['name'] ) ) ) {
			$id = absint( llms_filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_NUMBER_INT ) );
			return $id ? compact( 'id' ) : array();
		}

		return parent::parse_object_data();

	}

	/**
	 * Sets the content type's description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function set_description() {
		return __( 'Students can download their orders and transactions.', 'lifterlms' );
	}

	/**
	 * Sets the content type's title.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function set_title() {
		return __( 'Orders and Transactions', 'lifterlms' );
	}

}

return new LLMS_PDFS_Exportable_Orders();
