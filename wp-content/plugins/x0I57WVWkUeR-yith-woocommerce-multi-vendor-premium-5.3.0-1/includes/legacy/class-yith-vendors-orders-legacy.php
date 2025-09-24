<?php
/*
 * Legacy class for YITH Vendors. This class includes all deprecated methods and arguments that are going to be removed on future release.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Orders_Legacy' ) ) {
	/**
	 * Class YITH_Vendors
	 */
	abstract class YITH_Vendors_Orders_Legacy {

		/**
		 * Get suborders from parent_order_id
		 *
		 * @since    4.0.0
		 * @param boolean|integer $parent_order_id The parent order ID. Default false.
		 * @return array
		 */
		public static function get_suborder( $parent_order_id = false ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Orders::get_suborders' );

			return YITH_Vendors_Orders::get_suborders( $parent_order_id );
		}

		/**
		 * Get line item id from parent item id
		 *
		 * @since    1.6.0
		 * @param integer $order_item_id The parent order_item_id.
		 * @return   int|bool The child item id if exist, false otherwise
		 */
		public static function get_line_item_id_from_parent( $order_item_id ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Orders::get_child_item_id' );

			return YITH_Vendors_Orders::get_child_item_id( $order_item_id );
		}

		/**
		 * Get parent item id from child item id
		 *
		 * @since    1.6.0
		 * @param WC_Order $suborder      The suborder object.
		 * @param integer  $child_item_id The child item id.
		 * @return integer|bool The parent item id if exist, false otherwise
		 * @throws Exception
		 */
		public static function get_parent_item_id( $suborder = false, $child_item_id = 0 ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			return $child_item_id ? wc_get_order_item_meta( $child_item_id, '_parent_line_item_id', true ) : false;
		}

		/**
		 * Check if the current page is an order details page for vendor
		 *
		 * @since    1.6.0
		 * @param mixed $vendor The vendor object.
		 * @return   bool
		 * @deprecated
		 */
		public function is_vendor_order_page( $vendor = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			if ( ! $vendor ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			}
			$is_ajax          = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$current_screen   = get_current_screen();
			$is_order_details = is_admin() && ! is_null( $current_screen ) && 'edit-shop_order' === $current_screen->id;

			return apply_filters( 'yith_wcmv_is_vendor_order_page', $vendor->is_valid() && $vendor->has_limited_access() && $is_order_details && ! $is_ajax );
		}

		/**
		 * Check if the current page is an order details page for vendor
		 *
		 * @since    1.6.0
		 * @param mixed $vendor The vendor object.
		 * @return   bool
		 * @deprecated
		 */
		public function is_vendor_order_details_page( $vendor = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			if ( ! $vendor ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			}
			$is_ajax          = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$is_order_details = is_admin() && 'shop_order' === get_current_screen()->id;

			return apply_filters( 'yith_wcmv_is_vendor_order_details_page', $vendor->is_valid() && $vendor->has_limited_access() && $is_order_details && ! $is_ajax );
		}

		/**
		 * Add input hidden with customer id
		 *
		 * @since  1.9.18
		 * @param WC_Order $order Order object.
		 * @return void
		 * @deprecated
		 */
		public function hide_customer_info( $order ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( $order instanceof WC_Order ) {
				ob_start(); ?>
				<input type="hidden" name="customer_user" value="<?php echo absint( $order->get_user_id() ); ?>"/>
				<?php
				echo ob_get_clean(); // phpcs:ignore
			}
		}

		/**
		 * Filtered the order preview data
		 *
		 * @since  3.4.1
		 * @param array    $data  The order preview data.
		 * @param WC_Order $order Current order object.
		 *
		 * @return mixed|array Filtered preview data
		 * @deprecated
		 */
		public function order_preview_get_order_details( $data, $order ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			if ( $this->is_vendor_order_page() ) {
				if ( 'yes' === get_option( 'yith_wpv_vendors_option_order_hide_customer', 'no' ) ) {
					$data['data']['billing']['phone'] = '';
					$data['data']['billing']['email'] = '';
				}

				if ( 'yes' === get_option( 'yith_wpv_vendors_option_order_hide_payment', 'no' ) ) {
					$data['payment_via'] = '';
				}

				if ( 'yes' === get_option( 'yith_wpv_vendors_option_order_hide_shipping_billing', 'no' ) ) {
					$data['formatted_shipping_address'] = '';
					$data['formatted_billing_address']  = '';
				}
			}

			return $data;
		}

		/**
		 * Hidden default Meta-Boxes.
		 *
		 * @param array  $hidden An array of hidden meta-boxes.
		 * @param object $screen Current screen object.
		 * @return array
		 * @deprecated
		 */
		public function hidden_meta_boxes( $hidden, $screen ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( 'no' === get_option( 'yith_wpv_vendors_option_order_edit_custom_fields', 'no' ) && 'shop_order' === $screen->post_type && 'post' === $screen->base ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
					$hidden = array_merge( $hidden, array( 'postcustom' ) );
				}
			}

			return $hidden;
		}

		/**
		 * Add the commission information to order line item
		 *
		 * @since  1.9.12
		 * @param integer         $item_id The item ID.
		 * @param WC_Order_Item   $item    The order item object.
		 * @param WC_Product|null $product The line item associated product. Null otherwise.
		 * @deprecated
		 */
		public function commission_info_in_order_line_item( $item_id, $item, $product ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}


		/**
		 * Change commission label value
		 *
		 * @param string             $attribute_label The label value.
		 * @param string             $meta_key        The meta key.
		 * @param boolean|WC_Product $product         The product object.
		 * @return string
		 * @deprecated
		 */
		public function commissions_attribute_label( $attribute_label, $meta_key, $product = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $attribute_label;
		}

		/**
		 * Output custom columns for coupons
		 *
		 * @param string $column The column to render.
		 * @param mixed  $order  the current order.
		 * @return void
		 * @deprecated
		 */
		public function render_shop_order_columns( $column, $order = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			if ( ! empty( YITH_Vendors()->admin ) ) {
				YITH_Vendors()->admin
					->get_orders_handler()
					->render_shop_order_columns( $column, $order );
			}
		}

		/**
		 * Parent to Child synchronization
		 *
		 * @since    2.0.8
		 * @param integer $order_id   The parent id order.
		 * @param string  $old_status Old order status.
		 * @param string  $new_status New order status.
		 * @return void
		 */
		public function parent_order_status_synchronization( $order_id, $old_status, $new_status ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors()->orders->sync->parent_order_status_synchronization' );
			YITH_Vendors()->orders->sync->parent_order_status_synchronization( $order_id, $old_status, $new_status );
		}

		/**
		 * Trash suborder sync
		 *
		 * @since  4.2.1
		 * @param integer $order_id The order ID trashed/untrashed.
		 * @return void
		 */
		public function sync_trash_suborder( $order_id ) {
		}

		/**
		 * Trashed parent order sync
		 *
		 * @since  1.0.0
		 * @param integer $order_id The order ID trashed.
		 * @return void
		 * @deprecated
		 */
		public function trash_suborder( $order_id ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors()->orders->sync_trash_suborder' );
			$this->sync_trash_suborder( $order_id );
		}

		/**
		 * Set post author for new suborder.
		 *
		 * @since  4.0.0
		 * @param integer $suborder_id The suborder ID.
		 * @param integer $owner_id    The user owner ID.
		 * @return void
		 */
		protected function set_post_author( $suborder_id, $owner_id ) {
			_deprecated_function( __METHOD__, '4.0.0', 'Use filter woocommerce_new_order_data' );
		}
	}
}
