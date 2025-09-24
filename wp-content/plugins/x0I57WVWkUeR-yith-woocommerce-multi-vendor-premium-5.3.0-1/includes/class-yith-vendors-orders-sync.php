<?php
/**
 * YITH_Vendors_Orders_Sync class
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
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

if ( ! class_exists( 'YITH_Vendors_Orders_Sync' ) ) {
	/**
	 * This class handle order and its suborders sync
	 */
	class YITH_Vendors_Orders_Sync {

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->register_actions();
		}

		/**
		 * Register class actions
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function register_actions() {
			// Parent to suborder sync.
			if ( $this->order_sync_enabled() ) {
				add_filter( 'yith_wcmv_force_to_trigger_new_order_email_action', '__return_false', 20 );
				// SubOrder Sync.
				add_action( 'woocommerce_order_status_changed', array( $this, 'suborder_status_synchronization' ), 30, 3 );
				// Order Meta Synchronization.
				add_action( 'woocommerce_process_shop_order_meta', array( $this, 'suborder_meta_synchronization' ), 65, 2 );
				add_filter( 'woocommerce_can_restore_order_stock', array( $this, 'can_restore_order_stock' ), 99, 2 );
				// Process order item.
				add_action( 'woocommerce_new_order_item', array( $this, 'sync_new_order_item' ), 10, 3 );
				add_action( 'woocommerce_update_order_item', array( $this, 'sync_update_order_item' ), 10, 3 );
				add_action( 'woocommerce_before_delete_order_item', array( $this, 'sync_delete_order_item' ), 10 );
				// Order Note Synchronization.
				add_action( 'woocommerce_order_note_added', array( $this, 'sync_add_order_note' ), 10, 2 );
				add_action( 'wp_ajax_woocommerce_delete_order_note', array( $this, 'sync_delete_order_note' ), 5 );

				// SenangPay Payment Gateway for WooCommerce by senangPay Support.
				if ( class_exists( 'senangpay' ) ) {
					add_action( 'woocommerce_payment_complete', array( $this, 'suborder_status_synchronization' ) );
				}

				if ( defined( 'YITH_YWSBS_PREMIUM' ) ) {
					add_action( 'yith_suborder_renew_created', array( $this, 'suborder_status_synchronization' ) );
				}
			}

			// Suborder - parent order status synchronization.
			if ( $this->suborder_sync_enabled() ) {
				add_action( 'woocommerce_order_status_changed', array( $this, 'parent_order_status_synchronization' ), 35, 3 );
			}
		}

		/**
		 * Check if order sync is enabled.
		 *
		 * @since 4.26.0
		 * @return bool
		 */
		protected function order_sync_enabled() {
			return 'yes' === get_option( 'yith_wpv_vendors_option_order_management', 'no' ) &&
				'yes' === get_option( 'yith_wpv_vendors_option_order_synchronization', 'no' );
		}

		/**
		 * Check if suborder sync is enabled.
		 *
		 * @since 4.26.0
		 * @return bool
		 */
		protected function suborder_sync_enabled() {
			return 'yes' === get_option( 'yith_wpv_vendors_option_order_management', 'no' ) &&
				'yes' === get_option( 'yith_wpv_vendors_option_suborder_synchronization', 'no' );
		}

		/**
		 * Parent to child synchronization
		 *
		 * @since  1.6
		 * @param string|integer $parent_order_id The parent id orders.
		 * @param string         $old_status      (Optional) The old status. Default empty string.
		 * @param string         $new_status      (Optional) The new status. Default empty string.
		 * @return void
		 */
		public function suborder_status_synchronization( $parent_order_id, $old_status = '', $new_status = '' ) {
			$suborder_ids = YITH_Vendors_Orders::get_suborders( $parent_order_id );
			if ( ! empty( $suborder_ids ) ) {

				// Avoid to process customer meta for suborders.
				remove_action( 'woocommerce_order_status_completed', 'wc_paying_customer' );

				if ( empty( $new_status ) ) {
					$parent_order = wc_get_order( $parent_order_id );
					$new_status   = $parent_order ? $parent_order->get_status( 'edit' ) : '';
				}

				if ( $new_status && 'refunded' !== $new_status ) {
					foreach ( $suborder_ids as $suborder_id ) {
						$suborder = wc_get_order( $suborder_id );
						if ( $suborder ) {
							$suborder->update_status( $new_status, _x( 'Updated by admin: ', 'Order note', 'yith-woocommerce-product-vendors' ) );
						}
					}
				}

				add_action( 'woocommerce_order_status_completed', 'wc_paying_customer' );
			}
		}

		/**
		 * Parent to Child synchronization
		 *
		 * @since    1.6
		 * @param string|integer $order_id The order ID.
		 * @param WP_Post        $post     The post object.
		 * @return void
		 */
		public function suborder_meta_synchronization( $order_id, $post ) {
			// Check if order is a sub-order.
			$parent_order = wc_get_order( $order_id );
			if ( ! $parent_order || $parent_order->get_parent_id() ) {
				return;
			}

			$suborder_ids = YITH_Vendors_Orders::get_suborders( $order_id );
			if ( ! empty( $suborder_ids ) ) {
				foreach ( $suborder_ids as $suborder_id ) {
					$suborder               = wc_get_order( $suborder_id );
					$child_items            = array_keys( $suborder->get_items() );
					$_post                  = $_POST; // phpcs:ignore
					$_post['order_item_id'] = $child_items;
					$suborder_line_total    = 0;

					foreach ( $child_items as $child_items_id ) {
						$parent_item_id = wc_get_order_item_meta( $child_items_id, '_parent_line_item_id', true );
						$parent_item_id = absint( is_array( $parent_item_id ) ? array_shift( $parent_item_id ) : $parent_item_id );

						foreach ( $_post as $meta_key => $meta_value ) {
							switch ( $meta_key ) {
								case 'line_total':
								case 'line_subtotal':
								case 'order_item_tax_class':
								case 'order_item_qty':
								case 'refund_line_total':
								case 'refund_order_item_qty':
								case 'line_tax':
								case 'line_subtotal_tax':
								case 'line_tax_data':
								case 'refund_line_tax':
									if ( isset( $_post[ $meta_key ][ $parent_item_id ] ) ) {
										$_post[ $meta_key ][ $child_items_id ] = $_post[ $meta_key ][ $parent_item_id ];
										unset( $_post[ $meta_key ][ $parent_item_id ] );
									}
									break;

								case 'shipping_cost':
									if ( isset( $_post[ $meta_key ][ $parent_item_id ] ) ) {
										$_post[ $meta_key ][ $child_items_id ] = 0;
										unset( $_post[ $meta_key ][ $parent_item_id ] );
									}
									break;
								default: // nothing to do.
									break;
							}
						}

						// Calculate Order Total.
						if ( isset( $_post['line_total'][ $child_items_id ] ) ) {
							$suborder_line_total += (float) wc_format_decimal( $_post['line_total'][ $child_items_id ] );
						}
					}

					// New Order Total.
					$_post['_order_total'] = wc_format_decimal( $suborder_line_total );

					/**
					 * Don't use save method by WC_Meta_Box_Order_Items class because I need to filter the POST information
					 * use wc_save_order_items( $order_id, $items ) function directly.
					 *
					 * @see WC_Meta_Box_Order_Items::save( $suborder_id, $suborder ); in woocommerce\includes\admin\meta-boxes\class-wc-meta-box-order-items.php:45
					 * @see wc_save_order_items( $order_id, $items ); in woocommerce\includes\admin\wc-admin-functions.php:176
					 */
					wc_save_order_items( $suborder_id, $_post );
					WC_Meta_Box_Order_Downloads::save( $suborder_id, $suborder );
					WC_Meta_Box_Order_Data::save( $suborder_id );
					WC_Meta_Box_Order_Actions::save( $suborder_id, $suborder );
				}
			}
		}

		/**
		 * Check if the current order need to be restocked or not
		 *
		 * @param boolean  $can   True if the current order need to restock, false otherwise.
		 * @param WC_Order $order The order object.
		 * @return bool   True if the current order need to restock, false otherwise
		 */
		public function can_restore_order_stock( $can, $order ) {
			return $can && YITH_Vendors_Orders::CREATED_VIA !== $order->get_created_via();
		}

		/**
		 * Check if given order item can be editable for suborder
		 *
		 * @since  4.0.0
		 * @param integer $item_id The parent order item_id.
		 * @return boolean
		 */
		protected function is_order_item_editable( $item_id ) {
			$child_item_id = YITH_Vendors_Orders::get_child_item_id( absint( $item_id ) );
			if ( $child_item_id ) {
				$child_item = WC_Order_Factory::get_order_item( absint( $child_item_id ) );
				$suborder   = $child_item ? $child_item->get_order() : false;
				if ( $suborder && ! $suborder->is_editable() ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Check suborder status before update items
		 *
		 * @since  4.0.0
		 * @param int   $order_id Order ID.
		 * @param array $items    Order items to save.
		 * @return void
		 * @throws Exception Exception fired when trying to modify in item not editable.
		 */
		public function check_suborder_status_before_sync( $order_id, $items ) {
			try {

				foreach ( $items as $items_type => $items_id ) {
					if ( 'order_item_id' !== $items_type && 'shipping_method_id' !== $items_type ) {
						continue;
					}

					foreach ( $items_id as $item_id ) {
						if ( ! $this->is_order_item_editable( $item_id ) ) {
							throw new Exception();
						}
					}
				}
			} catch ( Exception $e ) {
				$message = _x( 'You cannot modify/delete an order item that\'s in a non-editable suborder!', '[Admin]Error message', 'yith-woocommerce-product-vendors' );
				if ( wp_doing_ajax() ) {
					wp_send_json_error( array( 'error' => $message ) );
				} else {
					wp_die( wp_kses_post( $message ) );
				}
			}
		}

		/**
		 * Sync new order item for suborder.
		 *
		 * @since  4.0.0
		 * @param integer       $item_id  The new item ID.
		 * @param WC_Order_Item $item     The new item object.
		 * @param integer       $order_id The order ID.
		 * @return void
		 */
		public function sync_new_order_item( $item_id, $item, $order_id ) {
		}

		/**
		 * Sync update order item for suborder.
		 *
		 * @since  4.0.0
		 * @param integer             $item_id  Item ID.
		 * @param array|WC_Order_Item $item     Either `order_item_type` or `order_item_name`. Can be also a WC_Order_Item instance.
		 * @param integer             $order_id Optional. The item order ID.
		 * @return void
		 * @throws Exception Error syncing order item.
		 */
		public function sync_update_order_item( $item_id, $item, $order_id = 0 ) {

			// Compatibility with wc_update_order_item function.
			if ( empty( $order_id ) || ! ( $item instanceof WC_Order_Item ) ) {
				$item     = WC_Order_Factory::get_order_item( absint( $item_id ) );
				$order_id = $item->get_order_id();
			}

			// First of all check if order has suborders. If not, skip.
			if ( ! $order_id || empty( YITH_Vendors_Orders::get_suborders( $order_id ) ) ) {
				return;
			}

			// Then check if item as a linked suborder item.
			$child_item_id = YITH_Vendors_Orders::get_child_item_id( absint( $item_id ) );
			if ( empty( $child_item_id ) ) {
				return;
			}

			try {

				$child_item = WC_Order_Factory::get_order_item( absint( $child_item_id ) );
				$suborder   = $child_item ? $child_item->get_order() : false;

				if ( ! $suborder || ! $child_item ) {
					throw new Exception( "An error occurred updating order item #{$child_item_id}. Order item or associated order not valid." );
				}

				switch ( $item->get_type() ) {
					case 'shipping':
						$this->update_suborder_shipping_item( $child_item, $item );
						break;
					case 'coupon':
						$this->update_suborder_coupon_item( $child_item, $item );
						break;
					case 'tax':
						break;
					case 'line_item':
						$this->update_suborder_line_item( $child_item, $item );
						break;
				}

				$suborder->calculate_totals();

			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );
			}
		}

		/**
		 * Sync delete order item for suborder.
		 *
		 * @since  4.0.0
		 * @param integer $item_id The new item ID.
		 * @return void
		 */
		public function sync_delete_order_item( $item_id ) {
			// Check if there is a child item ID.
			$child_item_id = YITH_Vendors_Orders::get_child_item_id( absint( $item_id ) );
			if ( ! empty( $child_item_id ) ) {
				// Remove itself for avoid multiple call.
				remove_action( 'woocommerce_before_delete_order_item', array( $this, 'sync_delete_order_item' ) );

				$child_item = WC_Order_Factory::get_order_item( absint( $child_item_id ) );
				if ( $child_item ) {
					$suborder      = $child_item->get_order();
					$commission_id = $child_item->get_meta( '_commission_id', true );
					// First delete the item.
					wc_delete_order_item( $child_item_id );
					$suborder->calculate_totals();
					// Then, if there is a commission associated, cancel it!
					$commission = $commission_id ? yith_wcmv_get_commission( $commission_id ) : false;
					$commission && $commission->update_status( 'cancelled' );
				}

				add_action( 'woocommerce_before_delete_order_item', array( $this, 'sync_delete_order_item' ) );
			}
		}

		/**
		 * Update a suborder shipping item
		 *
		 * @param WC_Order_Item_Shipping $shipping        The shipping to update.
		 * @param WC_Order_Item_Shipping $parent_shipping The parent shipping source.
		 */
		protected function update_suborder_shipping_item( $shipping, $parent_shipping ) {

			$shipping->set_props(
				array(
					'method_title' => $parent_shipping->get_method_title(),
					'method_id'    => $parent_shipping->get_method_id(),
					'instance_id'  => $parent_shipping->get_instance_id(),
					'total'        => wc_format_decimal( $parent_shipping->get_total() ),
					'taxes'        => array(
						'total' => $parent_shipping->get_taxes(),
					),
				)
			);

			foreach ( $parent_shipping->get_meta_data() as $meta ) {
				$meta_data = $meta->get_data();
				$shipping->update_meta_data( $meta_data['key'], $meta_data['value'] );
			}

			$shipping->save();
		}

		/**
		 * Update a suborder coupon item
		 *
		 * @param WC_Order_Item_Coupon $coupon        The coupon to update.
		 * @param WC_Order_Item_Coupon $parent_coupon The parent coupon source.
		 */
		protected function update_suborder_coupon_item( $coupon, $parent_coupon ) {

			$coupon->set_props(
				array(
					'code'         => $parent_coupon->get_code(),
					'discount'     => $parent_coupon->get_discount(),
					'discount_tax' => $parent_coupon->get_discount_tax(),
				)
			);

			$coupon_data = $parent_coupon->get_data();
			unset( $coupon_data['used_by'] );
			$coupon->add_meta_data( 'coupon_data', $coupon_data );

			$coupon->save();
		}

		/**
		 * Update a suborder line item
		 *
		 * @param WC_Order_Item_Product $item        The item to update.
		 * @param WC_Order_Item_Product $parent_item The parent item source.
		 */
		protected function update_suborder_line_item( $item, $parent_item ) {

			$item->set_props(
				array(
					'name'         => $parent_item->get_name(),
					'tax_class'    => $parent_item->get_tax_class(),
					'product_id'   => $parent_item->get_product_id(),
					'variation_id' => $parent_item->get_variation_id(),
					'quantity'     => $parent_item->get_quantity(),
					'subtotal'     => $parent_item->get_subtotal(),
					'total'        => $parent_item->get_total(),
					'subtotal_tax' => $parent_item->get_subtotal_tax(),
					'total_tax'    => $parent_item->get_total_tax(),
					'taxes'        => $parent_item->get_taxes(),
				)
			);

			$parent_meta_data = $parent_item->get_meta_data();
			foreach ( $parent_meta_data as $item_obj ) {
				$line_item_data = $item_obj->get_data();

				if ( ! in_array( $line_item_data['key'], YITH_Vendors_Orders::get_excluded_item_meta(), true ) ) {
					$item->update_meta_data( $line_item_data['key'], $line_item_data['value'] );
				}
			}

			$item->save();
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
			$parent_order    = wc_get_order( $order_id );
			$parent_order_id = $parent_order ? $parent_order->get_parent_id() : false;

			if ( $parent_order_id ) {

				remove_action( 'woocommerce_order_status_changed', array( $this, 'suborder_status_synchronization' ), 30 );

				$suborder_ids      = YITH_Vendors_Orders::get_suborders( $parent_order_id );
				$new_status_count  = 0;
				$suborder_count    = count( $suborder_ids );
				$suborder_statuses = array();

				foreach ( $suborder_ids as $suborder_id ) {
					$suborder = wc_get_order( $suborder_id );
					if ( ! $suborder ) {
						continue;
					}

					$suborder_status = $suborder->get_status( 'edit' );
					if ( $new_status === $suborder_status ) {
						++$new_status_count;
					}

					if ( ! isset( $suborder_statuses[ $suborder_status ] ) ) {
						$suborder_statuses[ $suborder_status ] = 1;
					} else {
						++$suborder_statuses[ $suborder_status ];
					}
				}

				$parent_order = wc_get_order( $parent_order_id );

				if ( $suborder_count === $new_status_count || 1 === $suborder_count ) {
					if ( 'refunded' !== $new_status ) {
						$parent_order->update_status( $new_status, _x( "Sync with vendor's suborders: ", 'Order note', 'yith-woocommerce-product-vendors' ) );
					}
				}

				add_action( 'woocommerce_order_status_changed', array( $this, 'suborder_status_synchronization' ), 30, 3 );
			}
		}

		/**
		 * Sync add order note from parent => suborders
		 *
		 * @since  4.0.0
		 * @param integer  $comment_id The note comment ID.
		 * @param WC_Order $order      The order object.
		 * @return void
		 */
		public function sync_add_order_note( $comment_id, $order ) {
			// Check if this action is triggered by the AJAX action. It is the only way to do that, thanks' Woo!
			if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'add-order-note' ) ) { // phpcs:ignore
				return;
			}

			// Check if order is a parent order.
			$suborders = YITH_Vendors_Orders::get_suborders( $order->get_id() );
			if ( ! empty( $suborders ) ) {
				// Remove itself to prevent multiple execution. Added it again is not mandatory since this is an AJAX request that ends with a die.
				remove_action( 'woocommerce_order_note_added', array( $this, 'sync_add_order_note' ), 5 );

				$comment          = get_comment( $comment_id );
				$is_customer_note = get_comment_meta( $comment_id, 'is_customer_note', true );
				foreach ( $suborders as $suborder_id ) {
					$suborder = wc_get_order( $suborder_id );
					$note_id  = $suborder->add_order_note( _x( 'Updated by admin: ', 'Order note', 'yith-woocommerce-product-vendors' ) . $comment->comment_content, $is_customer_note, true );
					add_comment_meta( $note_id, 'parent_note_id', $comment_id );
				}
			}
		}

		/**
		 * Sync delete order note.
		 * Need use the AJAX action since there is no other way.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function sync_delete_order_note() {
			global $wpdb;

			check_ajax_referer( 'delete-order-note', 'security' );

			if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST['note_id'] ) ) {
				wp_die( -1 );
			}

			$parent_note_id = absint( $_POST['note_id'] );
			$note_ids       = $wpdb->get_col( $wpdb->prepare( "SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %d", 'parent_note_id', $parent_note_id ) ); // phpcs:ignore

			if ( ! empty( $note_ids ) ) {
				foreach ( $note_ids as $note_id ) {
					wc_delete_order_note( $note_id );
				}
			}
		}
	}
}
