<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Export functions
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'ywgc_get_fields_to_export' ) ) {
	/**
	 * Get fields to be exported in the modal
	 *
	 * @return array
	 */
	function ywgc_get_fields_to_export() {
		$export_fields = array(
			'ywgc_export_option_order_id'          => array(
				'label' => esc_html__( 'Order ID', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_order_id',
				'name'  => 'ywgc_export_option_order_id',
				'value' => 'yes',
			),
			'ywgc_export_option_gift_card_id'      => array(
				'label' => esc_html__( 'Gift card ID', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_gift_card_id',
				'name'  => 'ywgc_export_option_gift_card_id',
				'value' => 'yes',
			),
			'ywgc_export_option_gift_card_code'    => array(
				'label' => esc_html__( 'Code', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_gift_card_code',
				'name'  => 'ywgc_export_option_gift_card_code',
				'value' => 'yes',
			),
			'ywgc_export_option_gift_card_amount'  => array(
				'label' => esc_html__( 'Amount', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_gift_card_amount',
				'name'  => 'ywgc_export_option_gift_card_amount',
				'value' => 'yes',
			),
			'ywgc_export_option_gift_card_balance' => array(
				'label' => esc_html__( 'Current balance', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_gift_card_balance',
				'name'  => 'ywgc_export_option_gift_card_balance',
				'value' => 'yes',
			),
			'ywgc_export_option_sender_name'       => array(
				'label' => esc_html__( 'Sender\'s name', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_sender_name',
				'name'  => 'ywgc_export_option_sender_name',
				'value' => 'yes',
			),
			'ywgc_export_option_recipient_name'    => array(
				'label' => esc_html__( 'Recipient\'s name', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_recipient_name',
				'name'  => 'ywgc_export_option_recipient_name',
				'value' => 'yes',
			),
			'ywgc_export_option_recipient_email'   => array(
				'label' => esc_html__( 'Recipient\'s email', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_recipient_email',
				'name'  => 'ywgc_export_option_recipient_email',
				'value' => 'yes',
			),
			'ywgc_export_option_message'           => array(
				'label' => esc_html__( 'Message', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_message',
				'name'  => 'ywgc_export_option_message',
				'value' => 'yes',
			),
			'ywgc_export_option_expiration_date'   => array(
				'label' => esc_html__( 'Expiration date', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_expiration_date',
				'name'  => 'ywgc_export_option_expiration_date',
				'value' => 'yes',
			),
			'ywgc_export_option_delivery_date'     => array(
				'label' => esc_html__( 'Delivery date', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_delivery_date',
				'name'  => 'ywgc_export_option_delivery_date',
				'value' => 'yes',
			),
			'ywgc_export_option_internal_note'     => array(
				'label' => esc_html__( 'Internal note', 'yith-woocommerce-gift-cards' ),
				'type'  => 'checkbox',
				'id'    => 'ywgc_export_option_internal_note',
				'name'  => 'ywgc_export_option_internal_note',
				'value' => 'yes',
			),
		);

		return $export_fields;
	}
}

if ( ! function_exists( 'ywgc_get_date_fields_in_export' ) ) {
	/**
	 * Get date fields to be exported in the modal
	 *
	 * @return string
	 */
	function ywgc_get_date_fields_in_export() {
		$date_fields = yith_ywgc_get_view_html( 'modals/export-import/date-from-to.php' );

		return $date_fields;
	}
}

if ( ! function_exists( 'ywgc_output_csv' ) ) {
	/**
	 * Send the CSV data to Ajax
	 *
	 * @param array  $data      Data to export.
	 * @param string $delimiter Delimiter.
	 */
	function ywgc_output_csv( $data, $delimiter ) {
		if ( ! empty( $data ) ) :
			$fp = fopen( 'php://output', 'w' );

			fputcsv( $fp, array_keys( reset( $data ) ) );

			foreach ( $data as $row ) :
				fputcsv( $fp, $row, $delimiter );
			endforeach;

			fclose( $fp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		endif;

		exit();
	}
}
