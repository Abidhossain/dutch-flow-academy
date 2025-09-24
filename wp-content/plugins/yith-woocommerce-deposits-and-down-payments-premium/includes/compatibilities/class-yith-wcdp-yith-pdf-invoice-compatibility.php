<?php
/**
 * Compatibility class with PDF Invoince
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_YITH_PDF_Invoice_Compatibility' ) ) {
	/**
	 * Deposit - PDF Invoice compatibility
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_YITH_PDF_Invoice_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_YITH_PDF_Invoice_Compatibility
		 * @since 1.0.5
		 */
		protected static $instance;

		/**
		 * Constructor method
		 */
		public function __construct() {
			add_filter( 'yith_ywpi_invoice_subtotal', array( $this, 'set_order_subtotal_in_invoice' ), 10, 2 );
			add_filter( 'yith_ywpi_line_discount', array( $this, 'set_order_total_discount_in_invoice' ), 10, 2 );
		}

		/**
		 * Set correct order total
		 *
		 * @param float    $order_subtotal Order subtotal.
		 * @param WC_Order $order          Order object.
		 *
		 * @return float
		 */
		public function set_order_subtotal_in_invoice( $order_subtotal, $order ) {

			$order_id     = $order->get_id();
			$new_subtotal = 0;
			$items        = $order->get_items();

			foreach ( $items as $item ) {
				$new_subtotal += $item['line_subtotal'];
			}

			return $new_subtotal > 0 ? $new_subtotal : $order_subtotal;
		}

		/**
		 * Set correct discount for the invoice
		 *
		 * @param float         $discount   Discount amount.
		 * @param WC_Order_Item $order_item Order item.
		 */
		public function set_order_total_discount_in_invoice( $discount, $order_item ) {

			if ( ( isset( $order_item['deposit'] ) && ! ! $order_item['deposit'] ) || ( ! empty( $order_item['deposit_id'] ) ) ) {
				$discount = 0;
			}

			return $discount;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_YITH_PDF_Invoice_Compatibility
		 * @since 1.0.5
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
