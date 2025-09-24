<?php
/**
 * YITH WooCommerce Request a Quote module
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Request_Quote' ) && function_exists( 'YITH_Request_Quote' ) ) {
	/**
	 * Handle YITH WooCommerce Request a Quote compatibility.
	 */
	class YITH_Vendors_Request_Quote {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Order quote status
		 *
		 * @var array
		 */
		public $quote_status = array();

		/**
		 * Construct
		 */
		private function __construct() {

			$this->quote_status = $this->get_quote_status();
			// Check if quote have commissions.
			add_filter( 'ywraq_order_cart_item_data', array( $this, 'order_cart_item_data' ), 10, 3 );
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'checkout_create_order_line_item_object' ), 10, 4 );
			add_action( 'woocommerce_saved_order_items', array( $this, 'manage_order_changing' ), 10, 2 );
			add_action( 'woocommerce_checkout_create_order', array( $this, 'force_created_via' ), 10, 1 );
			// Recreate commissions if is a quote with deposit.
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'recreate_commissions_if_the_quote_has_deposit' ), 10, 1 );
			// Disable order sync.
			add_action( 'woocommerce_order_status_changed', array( $this, 'disable_sync_quote' ), 1, 3 );
		}

		/**
		 * Disable parent/child sync for quote
		 *
		 * @since  4.0.0
		 * @param string|integer $order_id   The order id.
		 * @param string         $old_status (Optional) The old status. Default empty string.
		 * @param string         $new_status (Optional) The new status. Default empty string.
		 * @return void
		 */
		public function disable_sync_quote( $order_id, $old_status = '', $new_status = '' ) {
			if ( function_exists( 'YITH_YWRAQ_Order_Request' ) && YITH_YWRAQ_Order_Request()->is_quote( $order_id ) ) {
				remove_action( 'woocommerce_order_status_changed', array( YITH_Vendors()->orders->sync, 'suborder_status_synchronization' ), 30 );
				remove_action( 'woocommerce_order_status_changed', array( YITH_Vendors()->orders->sync, 'parent_order_status_synchronization' ), 35 );
			}
		}

		/**
		 * If the order is a quote with deposits the commissions will be recreated
		 *
		 * @since 3.3.2
		 * @param integer $order_id The order ID.
		 * @return void
		 */
		public function recreate_commissions_if_the_quote_has_deposit( $order_id ) {
			if ( $this->is_quote_with_deposit( $order_id ) ) {
				$this->recreate_commissions( $order_id );
			}
		}

		/**
		 * Force the created_via value if the order was already created
		 * by multi vendor as suborder on checkout processing
		 *
		 * @since 3.3.2
		 * @param WC_Order $order The order object.
		 */
		public function force_created_via( $order ) {
			$old_order = wc_get_order( $order->get_id() );
			if ( $order && $old_order ) {
				if ( YITH_Vendors_Orders::CREATED_VIA === $old_order->get_created_via() ) {
					$order->set_created_via( YITH_Vendors_Orders::CREATED_VIA );
				}
			}
		}

		/**
		 * Add custom meta data to order item on order creation.
		 *
		 * @param WC_Order_Item_Product $item          The order item instance.
		 * @param string                $cart_item_key The cart item key.
		 * @param array                 $values        An array of item meta values.
		 * @param WC_Order              $order         The order instance.
		 * @return void
		 */
		public function checkout_create_order_line_item_object( $item, $cart_item_key, $values, $order ) {
			$item_meta_to_add = apply_filters(
				'yith_wcmv_order_cart_item_data_for_quote',
				array(
					'_parent_line_item_id',
					'_commission_id',
					'_commission_included_tax',
					'_commission_included_coupon',
				)
			);

			foreach ( $item_meta_to_add as $meta_key ) {
				if ( ! empty( $values[ $meta_key ] ) ) {
					$item->add_meta_data( $meta_key, $values[ $meta_key ], true );
					$to_add[ $meta_key ] = $values[ $meta_key ];
				}
			}

			$item->save_meta_data();
		}

		/**
		 * Filter cart item data on quote creation process.
		 *
		 * @param array                 $cart_item_data The cart item data array.
		 * @param WC_Order_Item_Product $item           The order item instance.
		 * @param WC_Order              $order          The order instance.
		 * @return array
		 */
		public function order_cart_item_data( $cart_item_data, $item, $order ) {

			$to_retreive = apply_filters(
				'yith_wcmv_order_cart_item_data_for_quote',
				array(
					'_parent_line_item_id',
					'_commission_id',
					'_commission_included_tax',
					'_commission_included_coupon',
				)
			);

			foreach ( $to_retreive as $key ) {
				$value = wc_get_order_item_meta( $item->get_id(), $key, true );
				if ( ! empty( $value ) ) {
					$cart_item_data[ $key ] = $value;
				}
			}

			return $cart_item_data;
		}

		/**
		 * Get quote available statuses.
		 *
		 * @param boolean $filtered True to filter status, false otherwise.
		 * @return mixed
		 */
		public function get_quote_status( $filtered = true ) {
			$raq_status = YITH_YWRAQ_Order_Request()->raq_order_status;
			if ( $filtered ) {
				array_walk( $raq_status, 'self::filter_status', 'wc-' );
			}

			return $raq_status;
		}

		/**
		 * Is this a quote?
		 *
		 * @since 3.3.2
		 * @param integer $order_id The order id.
		 * @return boolean
		 * @uses  YITH_YWRAQ_Order_Request::is_quote()
		 */
		public function is_quote( $order_id ) {
			return YITH_YWRAQ_Order_Request()->is_quote( $order_id );
		}

		/**
		 * Is this a quote with deposit?
		 *
		 * @since 3.3.2
		 * @param integer $order_id The order id.
		 * @return bool
		 */
		public function is_quote_with_deposit( $order_id ) {
			$order = wc_get_order( $order_id );

			return $order && $this->is_quote( $order->get_id() ) && ( yith_plugin_fw_is_true( $order->get_meta( '_has_deposit' ) || yith_plugin_fw_is_true( $order->get_meta( '_ywraq_deposit_enable' ) ) ) );
		}

		/**
		 * Has the order a valid quote status?
		 *
		 * @since 3.3.2
		 * @param integer|WC_Order|WP_Post $order The order object or the order ID.
		 * @return boolean
		 */
		public function has_valid_quote_status( $order ) {
			$is_quote = false;

			if ( ! is_object( $order ) ) {
				$order_id = $order;
				$order    = wc_get_order( $order_id );
			}

			if ( $order instanceof WC_Order && $order->has_status( $this->quote_status ) ) {
				$is_quote = true;
			} elseif ( $order instanceof WP_POST && 'shop_order' === $order->post_type ) {
				$_post    = $order;
				$order    = wc_get_order( $_post->ID );
				$is_quote = in_array( $order->get_status(), $this->quote_status, true );
			}

			return $is_quote;
		}

		/** Filter quote status callback
		 *
		 * @param string $status Quote status.
		 * @param string $key    Quote status key.
		 * @param string $prefix Quote status prefix.
		 */
		public static function filter_status( &$status, $key, $prefix ) {
			$status = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
		}

		/**
		 * Check if quote items have commissions associated.
		 *
		 * @since 3.3.2
		 */
		public function check_if_quote_have_commissions() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! empty( $_POST['order_id'] ) ) {
				$order = wc_get_order( absint( $_POST['order_id'] ) );
				if ( $order && $this->has_valid_quote_status( $order ) && $order->get_parent_id() ) {
					$items = $order->get_items();
					if ( ! empty( $items ) ) {
						foreach ( $items as $item_id => $item ) {
							$commission_id = wc_get_order_item_meta( $item_id, '_commission_id', true );
							if ( empty( $commission_id ) ) {
								$order->delete_meta_data( '_commissions_processed' );
								$order->save_meta_data();
								YITH_Vendors()->commissions->register_commissions( $order->get_id() );
							}
						}
					}
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * When order is saved, removes old commissions and update them
		 *
		 * @since  3.6.0
		 * @param integer $order_id The order id.
		 * @return void
		 */
		public function manage_order_changing( $order_id ) {
			if ( $this->has_valid_quote_status( $order_id ) ) {
				$this->recreate_commissions( $order_id );
			}
		}

		/**
		 * Recreate commissions for a specific order
		 *
		 * @since 3.3.2
		 * @param integer $order_id The order id.
		 */
		public function recreate_commissions( $order_id ) {
			$order     = wc_get_order( $order_id );
			$processed = $order->get_meta( '_commissions_processed', true );
			$items     = $order->get_items();
			if ( ! $order ) {
				return;
			}

			if ( 'yes' === $processed ) {

				if ( ! empty( $items ) ) {
					foreach ( $items as $item_id => $item ) {
						$commission_id = wc_get_order_item_meta( $item_id, '_commission_id', true );
						if ( $commission_id ) {
							YITH_Vendors_Commissions_Factory::delete( $commission_id );
						}
					}
				}
				$order->add_meta_data( '_commissions_processed', 'no', true );
				$order->save_meta_data();
			}

			YITH_Vendors()->commissions->register_commissions( $order_id );
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  1.9
 * @return YITH_Vendors_Request_Quote
 */
if ( ! function_exists( 'YITH_Vendors_Request_Quote' ) ) {
	function YITH_Vendors_Request_Quote() { // phpcs:ignore
		return YITH_Vendors_Request_Quote::instance();
	}
}

if ( empty( YITH_Vendors()->quote ) ) {
	YITH_Vendors()->quote = YITH_Vendors_Request_Quote();
}
