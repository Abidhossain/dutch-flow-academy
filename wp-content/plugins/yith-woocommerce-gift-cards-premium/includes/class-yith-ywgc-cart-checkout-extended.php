<?php
/**
 * Class to manage the cart and checkout features
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Cart_Checkout_Extended' ) ) {
	/**
	 * YITH_YWGC_Cart_Checkout_Extended class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Cart_Checkout_Extended extends YITH_YWGC_Cart_Checkout {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Cart_Checkout_Extended
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 */
		protected function __construct() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
			parent::__construct();
		}

		/**
		 * Build cart item meta to pass to add_to_cart when adding a gift card to the cart
		 *
		 * @since 1.5.0
		 */
		public function build_cart_item_data() {
			$cart_item_data = array();

			/**
			 * Check if the current gift card has a prefixed amount set
			 */
			$ywgc_is_preset_amount = isset( $_REQUEST['gift_amounts'] ) && ( floatval( $_REQUEST['gift_amounts'] ) > 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$ywgc_is_preset_amount = wc_format_decimal( $ywgc_is_preset_amount );

			/**
			 * Neither manual or fixed? Something wrong happened!
			 */
			if ( ! $ywgc_is_preset_amount ) {
				wp_die( esc_html__( 'The gift card has an invalid amount', 'yith-woocommerce-gift-cards' ) );
			}

			/**
			 * Check if it is a physical gift card
			 */
			$ywgc_is_physical = isset( $_REQUEST['ywgc-is-physical'] ) && boolval( $_REQUEST['ywgc-is-physical'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $ywgc_is_physical ) {
				/**
				 * Retrieve sender name
				 */
				$sender_name = isset( $_REQUEST['ywgc-sender-name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywgc-sender-name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Recipient name
				 */
				$recipient_name = isset( $_REQUEST['ywgc-recipient-name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywgc-recipient-name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Retrieve the sender message
				 */
				$sender_message = isset( $_REQUEST['ywgc-edit-message'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['ywgc-edit-message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			/**
			 * Check if it is a digital gift card
			 */
			$ywgc_is_digital = isset( $_REQUEST['ywgc-is-digital'] ) && boolval( $_REQUEST['ywgc-is-digital'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $ywgc_is_digital ) {
				/**
				 * Retrieve gift card recipient
				 */
				$recipients = isset( $_REQUEST['ywgc-recipient-email'] ) ? array_map( 'sanitize_email', wp_unslash( $_REQUEST['ywgc-recipient-email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Retrieve sender name
				 */
				$sender_name = isset( $_REQUEST['ywgc-sender-name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywgc-sender-name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Recipient name
				 */
				$recipient_name = isset( $_REQUEST['ywgc-recipient-name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['ywgc-recipient-name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Retrieve the sender message
				 */
				$sender_message = isset( $_REQUEST['ywgc-edit-message'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['ywgc-edit-message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Gift card should be delivered on a specific date?
				 */
				$delivery_date = isset( $_REQUEST['ywgc-delivery-date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywgc-delivery-date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( '' !== $delivery_date && is_string( $delivery_date ) && ! is_bool( $delivery_date ) ) {
					$saved_format = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );

					if ( 'MM d, yy' === $saved_format ) {
						$aux           = wp_timezone_string() . ' ' . $delivery_date . ' 00:00';
						$delivery_date = strtotime( $aux );
					} else {
						$search  = array( '.', ', ', '/', ' ', ',', 'MM', 'yy', 'mm', 'dd' );
						$replace = array( '-', '-', '-', '-', '-', 'M', 'y', 'm', 'd' );

						$date_formatted         = str_replace( $search, $replace, $delivery_date );
						$saved_format_formatted = str_replace( $search, $replace, $saved_format );
						$delivery_date          = 'mm/dd/yy' !== $saved_format ? gmdate( $saved_format_formatted, strtotime( $date_formatted ) ) : gmdate( $saved_format_formatted, strtotime( $delivery_date ) );
						$delivery_date_object   = DateTime::createFromFormat( $saved_format_formatted, $delivery_date );

						if ( $delivery_date_object ) {
							$delivery_date_aux = $delivery_date_object->format( 'Y-m-d' );
							$aux               = $delivery_date_aux . ' 00:00';
							$delivery_date     = strtotime( $aux ) - wc_timezone_offset();
						}
					}
				}

				$postdated        = '' !== $delivery_date ? true : false;
				$gift_card_design = - 1;
				$design_type      = isset( $_POST['ywgc-design-type'] ) ? sanitize_text_field( wp_unslash( $_POST['ywgc-design-type'] ) ) : 'default'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( 'template' === $design_type ) {
					if ( isset( $_POST['ywgc-template-design'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
						$gift_card_design = sanitize_text_field( wp_unslash( $_POST['ywgc-template-design'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
					}
				}
			}

			if ( isset( $_POST['add-to-cart'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$cart_item_data['ywgc_product_id'] = absint( $_POST['add-to-cart'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			} elseif ( isset( $_REQUEST['ywgc_product_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$cart_item_data['ywgc_product_id'] = absint( $_REQUEST['ywgc_product_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			/**
			 * Set the gift card amount
			 */
			$product     = wc_get_product( $cart_item_data['ywgc_product_id'] );
			$ywgc_amount = sanitize_text_field( wp_unslash( $_REQUEST['gift_amounts'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$ywgc_amount = apply_filters( 'yith_ywgc_submitting_select_amount', $ywgc_amount, $product );

			$cart_item_data['ywgc_amount']      = $ywgc_amount;
			$cart_item_data['ywgc_is_digital']  = $ywgc_is_digital;
			$cart_item_data['ywgc_is_physical'] = $ywgc_is_physical;

			/**
			 * Retrieve the gift card recipient, if digital
			 */
			if ( $ywgc_is_digital ) {
				$cart_item_data['ywgc_recipients']     = $recipients;
				$cart_item_data['ywgc_sender_name']    = $sender_name;
				$cart_item_data['ywgc_recipient_name'] = $recipient_name;
				$cart_item_data['ywgc_message']        = $sender_message;
				$cart_item_data['ywgc_postdated']      = $postdated;

				$cart_item_data['ywgc_design_type']       = $design_type;
				$cart_item_data['ywgc_has_custom_design'] = '-1' !== $gift_card_design;

				if ( $gift_card_design ) {
					$cart_item_data['ywgc_design'] = $gift_card_design;
				}

				if ( $postdated ) {
					/**
					 * APPLY_FILTERS: ywgc_save_delivery_date_cart_item_data
					 *
					 * Filter the delivery date in the cart item data.
					 *
					 * @param string the delivery date including the gmt_offset
					 * @param string $delivery_date the delivery date
					 *
					 * @return string
					 */
					$delivery_date                        = apply_filters( 'ywgc_save_delivery_date_cart_item_data', $delivery_date + ( 3600 * get_option( 'gmt_offset' ) ), $delivery_date );
					$cart_item_data['ywgc_delivery_date'] = $delivery_date;
				}
			}

			if ( $ywgc_is_physical ) {
				$cart_item_data['ywgc_recipient_name'] = $recipient_name;
				$cart_item_data['ywgc_sender_name']    = $sender_name;
				$cart_item_data['ywgc_message']        = $sender_message;
			}

			return $cart_item_data;
		}
	}
}
