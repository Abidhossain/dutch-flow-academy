<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Commissions' ) ) {
	/**
	 * Class YITH_Vendors_Commissions
	 */
	class YITH_Vendors_Commissions extends YITH_Vendors_Commissions_Legacy {

		/**
		 * Include coupon on commission calculation
		 *
		 * @since 4.0.0
		 * @var string
		 */
		protected $include_coupon = 'yes';

		/**
		 * Tax management on commission calculation
		 *
		 * @since 4.0.0
		 * @var string
		 */
		protected $tax_management = 'website';

		/**
		 * An array of processing order.
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $processed_order = array();

		/**
		 * Constructor
		 *
		 * @since  4.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			$this->tax_management = get_option( 'yith_wpv_commissions_tax_management', 'website' );
			$this->include_coupon = get_option( 'yith_wpv_include_coupon', 'yes' );

			add_action( 'yith_wcmv_checkout_order_processed', array( $this, 'register_commissions' ), 10, 1 );
			add_action( 'woocommerce_order_status_changed', array( $this, 'manage_status_changing' ), 10, 3 );
			add_action( 'woocommerce_refund_created', array( $this, 'register_commission_refund' ), 10, 2 );

			add_action( 'yith_wcmv_email_commission_details_table', array( $this, 'email_commission_details_table' ), 10, 2 );

			// Order trash sync.
			add_action( 'trashed_post', array( $this, 'cancel_commissions_on_trash_order' ), 10, 1 );
			add_action( 'deleted_post', array( $this, 'delete_commissions_on_delete_order' ), 10, 1 );
		}

		/**
		 * Get status capabilities map
		 *
		 * @since  2.5.0
		 * @return array capabilities allowed change list
		 */
		public function get_status_capabilities() {
			return apply_filters(
				'yith_wcmv_get_commissions_status_capabilities_map',
				array(
					'pending'    => array( 'unpaid', 'paid', 'cancelled' ),
					'unpaid'     => array( 'pending', 'paid', 'cancelled', 'processing' ),
					'paid'       => array( 'refunded' ),
					'cancelled'  => array(),
					'refunded'   => array(),
					'processing' => array( 'paid', 'unpaid' ),
				)
			);
		}

		/**
		 * Check if status changing is permitted
		 *
		 * @param string $new_status The new commission status.
		 * @param string $old_status The old commission status.
		 * @return boolean
		 */
		public function is_status_changing_permitted( $new_status, $old_status ) {
			$status_capabilities = $this->get_status_capabilities();

			return $new_status !== $old_status && in_array( $new_status, $status_capabilities[ $old_status ], true );
		}

		/**
		 * Register the commission linked to order
		 *
		 * @since 1.0
		 * @param integer|string $order_id The order ID.
		 * @return void
		 */
		public function register_commissions( $order_id ) {
			// Only process commissions once.
			$order = wc_get_order( $order_id );

			if ( ! $order || 'yes' === $order->get_meta( '_commissions_processed' ) || in_array( $order->get_id(), $this->processed_order, true ) ) {
				return;
			}

			// Store processing order to avoid multiple process for same execution.
			$this->processed_order[] = $order->get_id();

			// Check all items of order to know if there is some vendor to credit and what are the products to process.
			foreach ( $order->get_items() as $item_id => $item ) {

				$product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;
				if ( empty( $product ) ) {
					continue;
				}

				if ( $product->is_type( 'variation' ) ) {
					$product = wc_get_product( $product->get_parent_id() );
				}

				$vendor = yith_wcmv_get_vendor( $product, 'product' );
				if ( ! $vendor || ! $vendor->is_valid() ) {
					continue;
				}

				// Get percentage for commission.
				$rate = (float) $vendor->get_commission_rate( $product->get_id() );
				$rate = apply_filters( 'yith_wcmv_product_commission', $rate, $vendor, $order, $item, $item_id );

				$line_total = $this->get_line_item_total( $item, $order );
				// Calculate commission amount.
				$amount = $this->calculate_commission_amount( $vendor, $order, $item, $rate );
				// No amount to apply.
				if ( apply_filters( 'yith_wcmv_register_commissions', empty( $amount ), $vendor, $order, $item, $amount ) ) {
					continue;
				}

				// Add commission in pending.
				$commission_id = yith_wcmv_create_commission(
					apply_filters(
						'yith_wcmv_register_commission_for_order_item',
						array(
							'line_item_id' => $item_id,
							'line_total'   => $line_total,
							'order_id'     => $order_id,
							'user_id'      => $vendor->get_owner(),
							'vendor_id'    => $vendor->get_id(),
							'product_id'   => $product->get_id(),
							'rate'         => $rate,
							'amount'       => $amount,
						),
						$item,
						$order,
						$vendor
					)
				);

				if ( is_wp_error( $commission_id ) ) {
					continue;
				}

				$commission = yith_wcmv_get_commission( $commission_id );
				if ( ! $commission ) {
					continue;
				}
				// Add commission note.
				$commission->add_note( apply_filters( 'yith_wcmv_new_commission_note', self::get_tax_and_coupon_management_message( $this->tax_management, $this->include_coupon ) ) );
				// Register commission to order item.
				$this->register_commission_to_order_item( $item, $commission_id );

				// Add commission to parent order item.
				$parent_item_id = $item->get_meta( '_parent_line_item_id', true );
				$parent_item_id = is_array( $parent_item_id ) ? array_shift( $parent_item_id ) : $parent_item_id;
				if ( $parent_item_id ) {
					$this->register_commission_to_order_item( absint( $parent_item_id ), $commission_id );
				}

				do_action( 'yith_wcmv_after_single_register_commission', $commission_id, $item_id, '_commission_id', $order );
			}

			// Mark commissions as processed.
			$order->add_meta_data( '_commissions_processed', 'yes', true );
			$order->save_meta_data();

			do_action( 'yith_wcmv_order_commissions_processed', $order_id );

			if ( apply_filters( 'yith_wcmv_force_to_trigger_new_order_email_action', false ) ) {
				WC()->mailer();
				do_action( 'yith_wcmv_new_order_email', $order_id );
			}
		}

		/**
		 * Register a commission to an order item.
		 *
		 * @since  4.0.0
		 * @param integer|WC_Order_Item $item                       The order item, or its ID.
		 * @param integer               $commission_id              The commission id.
		 * @param string                $tax_management             The commission tax management option.
		 * @param boolean|null          $commission_included_coupon True if commission include coupon, false otherwise.
		 * @return void
		 */
		protected function register_commission_to_order_item( $item, $commission_id, $tax_management = '', $commission_included_coupon = null ) {
			if ( ! ( $item instanceof WC_Order_Item ) ) {
				$item = WC_Order_Factory::get_order_item( absint( $item ) );
			}

			if ( $item instanceof WC_Order_Item ) {

				$tax_management             = ! empty( $tax_management ) ? $tax_management : $this->tax_management;
				$commission_included_coupon = ! is_null( $commission_included_coupon ) ? $commission_included_coupon : $this->include_coupon;

				$item->update_meta_data( '_commission_id', $commission_id );
				$item->update_meta_data( '_commission_included_tax', $tax_management );
				$item->update_meta_data( '_commission_included_coupon', $commission_included_coupon );
				$item->save_meta_data();

				do_action( 'yith_wcmv_add_extra_commission_order_item_meta', $item->get_id() );
			}
		}

		/**
		 * Get line item total
		 *
		 * @since  4.0.0
		 * @param WC_Order_Item $item  The item order related to the commission.
		 * @param WC_Order      $order The order related to the commission.
		 * @return float
		 */
		public function get_line_item_total( $item, $order ) {
			// Check if coupon is included and get line total or subtotal.
			$get_item_amount = 'yes' === $this->include_coupon ? 'get_line_total' : 'get_line_subtotal';
			// Retrieve the real amount of single item, with right discounts applied and without taxes.
			$line_total = $order->$get_item_amount( $item, 'split' === $this->tax_management, false );
			return (float) apply_filters( 'yith_wcmv_get_line_total_amount_for_commission', $line_total, $order, $item, $item->get_id() );
		}

		/**
		 * Calculate commission for an order, vendor and item
		 *
		 * @param YITH_Vendor   $vendor Item vendor.
		 * @param WC_Order      $order  The order related to the commission.
		 * @param WC_Order_Item $item   The item order related to the commission.
		 * @param float         $rate   The commission rate to use for calculate amount.
		 * @return mixed
		 */
		protected function calculate_commission_amount( $vendor, $order, $item, $rate ) {

			// If commission rate is 0% then go no further.
			if ( empty( $rate ) ) {
				return 0;
			}

			$line_total = $this->get_line_item_total( $item, $order );
			// If total is 0 after discounts then go no further.
			if ( ! $line_total ) {
				return 0;
			}

			// Get total amount for commission.
			$amount = (float) $line_total * $rate;
			// If commission amount is 0 then go no further.
			if ( ! $amount ) {
				return 0;
			}

			if ( 'vendor' === $this->tax_management ) {
				$vendor_item_tax = wc_round_tax_total( $item->get_total_tax() );
				if ( ! empty( $vendor_item_tax ) ) {
					$amount = (float) $amount + $vendor_item_tax;
				}
			}

			return apply_filters( 'yith_wcmv_calculate_commission_amount', $amount, $vendor, $order, $item, $item->get_id() );
		}

		/**
		 * Manage the status changing
		 *
		 * @since   1.0
		 * @param integer $order_id   The order ID.
		 * @param string  $old_status Old order status.
		 * @param string  $new_status New order status.
		 * @reuturn boolean
		 */
		public function manage_status_changing( $order_id, $old_status, $new_status ) {

			switch ( $new_status ) {

				case 'completed':
				case 'processing':
					$this->register_commissions_unpaid( $order_id );
					break;

				case 'refunded':
					$this->register_commissions_refunded( $order_id );
					break;

				case 'cancelled':
				case 'failed':
					$this->register_commissions_cancelled( $order_id );
					break;

				case 'pending':
				case 'on-hold':
					$this->register_commissions_pending( $order_id );
					break;

			}
		}

		/**
		 * Register commissions status change based on order status
		 *
		 * @since  4.0.0
		 * @param integer|string $order_id The order ID.
		 * @param string         $status   The order status.
		 * @return boolean
		 */
		protected function register_commissions_status( $order_id, $status ) {
			// Ensure the order have commissions processed.
			$order = wc_get_order( $order_id );
			if ( ! $order || 'yes' !== $order->get_meta( '_commissions_processed' ) ) {
				return false;
			}

			$commission_ids = array();

			foreach ( $order->get_items() as $item ) {
				$commission_ids[] = $item->get_meta( '_commission_id', true );
			}

			$commission_ids = array_filter( $commission_ids ); // Remove empty values.
			$commission_ids = apply_filters( 'yith_wcmv_register_commissions_status', $commission_ids, $order_id, $status );

			if ( empty( $commission_ids ) ) {
				return false;
			}

			foreach ( $commission_ids as $commission_id ) {
				// Retrieve commission.
				$commission = yith_wcmv_get_commission( absint( $commission_id ) );
				if ( ! $commission ) {
					continue;
				}

				// Update the commission status. This will also save the commission.
				$commission->update_status( $status );
			}

			return true;
		}

		/**
		 * Register the commission as unpaid when the order is completed
		 *
		 * @since  4.0.0
		 * @param integer|string $order_id The order ID.
		 * @return void
		 */
		protected function register_commissions_unpaid( $order_id ) {
			$this->register_commissions_status( $order_id, 'unpaid' );
		}

		/**
		 * Register the commission as refunded when there was a refund in the order
		 *
		 * @since  4.0.0
		 * @param integer|string $order_id The order ID.
		 * @return void
		 */
		protected function register_commissions_refunded( $order_id ) {
			$this->register_commissions_status( $order_id, 'refunded' );
		}

		/**
		 * Register the commission as unpaid when the order is completed
		 *
		 * @since  4.0.0
		 * @param integer|string $order_id The order ID.
		 * @return void
		 */
		protected function register_commissions_cancelled( $order_id ) {
			$this->register_commissions_status( $order_id, 'cancelled' );
		}

		/**
		 * Register the commission as pending when the order is on-hold
		 *
		 * @since 1.0
		 * @param integer $order_id The order ID.
		 */
		protected function register_commissions_pending( $order_id ) {
			$this->register_commissions_status( $order_id, 'pending' );
		}

		/**
		 * Trash order -> change commission status
		 *
		 * @since  4.2.1
		 * @param integer $order_id The order ID trashed/untrashed.
		 * @return void
		 */
		public function cancel_commissions_on_trash_order( $order_id ) {
			$this->register_commissions_status( $order_id, 'cancelled' );
		}

		/**
		 * Delete commission
		 *
		 * @since  4.2.1
		 * @param integer $order_id The order ID deleted.
		 * @return void
		 */
		public function delete_commissions_on_delete_order( $order_id ) {
			$status = yith_wcmv_get_commission_statuses();
			unset( $status['paid'], $status['processing'], $status['refunded'] ); // Avoid delete commissions paid/refunded or in payment processing status.

			$commissions = yith_wcmv_get_commissions(
				array(
					'order_id' => $order_id,
					'number'   => -1,
					'status'   => array_keys( $status ),
					'fields'   => 'all',
				)
			);

			foreach ( $commissions as $commission ) {
				$commission && $commission->delete();
			}
		}

		/**
		 * Recalculate commissions after an order refund is created
		 *
		 * @since  1.0.0
		 * @param integer|string $refund_id The refund ID.
		 * @param array          $args      An Array of refund arguments.
		 * @return void
		 */
		public function register_commission_refund( $refund_id, $args ) {

			$suborder_id = $args['order_id'];
			// Is vendor suborder ?
			$suborder = wc_get_order( absint( $suborder_id ) );
			if ( ! $suborder || ! $suborder->get_parent_id() || YITH_Vendors_Orders::CREATED_VIA !== $suborder->get_created_via() ) {
				return;
			}

			// This is a vendor suborder. Get the suborder refund.
			$refund = wc_get_order( $refund_id );
			$items  = $refund ? $refund->get_items( array( 'line_item', 'shipping' ) ) : array();

			foreach ( $items as $refund_item ) {
				$item_id   = $refund_item->get_meta( '_refunded_item_id' );
				$line_item = $suborder->get_item( $item_id );

				if ( ! $line_item ) {
					continue;
				}

				// Taxes and Coupons Management for Vendor's commissions.
				$vendor_tax_management       = '';
				$commission_included_coupons = false;

				$commission_id = $line_item->get_meta( '_commission_id', true );
				$commission    = yith_wcmv_get_commission( absint( $commission_id ) );
				if ( ! $commission ) {
					continue;
				}

				if ( 'product' === $commission->get_type() ) {
					// Tax Management for vendors.
					$vendor_tax_management = strtolower( $line_item->get_meta( '_commission_included_tax', true, 'edit' ) );
					// Coupon Management for vendors.
					$commission_included_coupons = 'no' !== strtolower( $line_item->get_meta( '_commission_included_coupon', true, 'edit' ) );

					if ( 'website' === $vendor_tax_management ) {
						$refund_amount = abs( $refund_item->get_total() );
					} else {
						$refund_amount = abs( $refund_item->get_total() + $refund_item->get_total_tax() );
					}
				} else {
					$refund_amount = abs( $refund_item->get_total() + $refund_item->get_total_tax() );
				}

				// Is line item full refunded?
				if ( $refund_amount > 0 ) {

					if ( 'website' === $vendor_tax_management ) {
						$is_full_refunded = abs( floatval( $refund_amount ) - floatval( $line_item->get_total() ) ) < 0.01;
					} else {
						$is_full_refunded = abs( floatval( $refund_amount ) - ( floatval( $line_item->get_total() ) + floatval( $line_item->get_total_tax() ) ) ) < 0.01;
					}

					if ( $is_full_refunded ) { // Is a full refund.
						$commission->set_status( 'refunded', '', true );
						$refund_commission_amount   = -$commission->get_amount( 'edit' );
						$commission_amount_refunded = -$commission->get_amount( 'edit' );
					} else {
						$tax_refund_amount = 0;
						if ( 'product' === $commission->get_type() ) {
							if ( 'vendor' === $vendor_tax_management ) {
								$refund_amount     = abs( $refund_item->get_total() );
								$tax_refund_amount = abs( $refund_item->get_total_tax() );
							}

							if ( false === $commission_included_coupons ) {
								$refund_amount = ( ( $refund_amount * $line_item->get_subtotal() ) / $line_item->get_total() );
							}
						} else {
							$refund_amount     = abs( $refund_item->get_total() );
							$tax_refund_amount = abs( $refund_item->get_total_tax() );
						}

						// Partial Refund.
						$refund_commission_amount   = round( $refund_amount, 2, PHP_ROUND_HALF_ODD ) * $commission->get_rate( 'edit' ) * -1 + ( round( $tax_refund_amount, 2, PHP_ROUND_HALF_ODD ) * -1 );
						$commission_amount_refunded = floatval( $refund_commission_amount ) + floatval( $commission->get_amount_refunded( 'edit' ) );
					}

					$refund_item->add_meta_data( '_refund_commission_amount', $refund_commission_amount, true );
					$refund_item->save();

					$commission->set_amount_refunded( $commission_amount_refunded );
					// translators: %s stand for the commission amount refunded.
					$message = sprintf( _x( 'Refunded: %s', 'Commission note', 'yith-woocommerce-product-vendors' ), wc_price( abs( $commission_amount_refunded ), array( 'currency' => $suborder->get_currency() ) ) );
					$commission->add_note( $message );
					$commission->save();

					do_action( 'yith_wcmv_register_commission_refund', $refund, $refund_item, $commission );
				}
			}
		}

		/**
		 * Delete a commission refund
		 *
		 * @since  4.0.0
		 * @param integer|string $refund_id       The refund ID.
		 * @param integer|string $suborder_id     The suborder ID.
		 * @param integer|string $parent_order_id The parent order ID.
		 * @return boolean
		 */
		public function delete_commission_refund( $refund_id, $suborder_id, $parent_order_id ) {

			// Is vendor suborder ?
			$suborder = wc_get_order( absint( $suborder_id ) );
			if ( ! $suborder || ! $suborder->get_parent_id() || YITH_Vendors_Orders::CREATED_VIA !== $suborder->get_created_via() ) {
				return false;
			}

			$refund = wc_get_order( $refund_id );
			$items  = $refund ? $refund->get_items( array( 'line_item', 'shipping' ) ) : array();

			foreach ( $items as $refund_item ) {
				$item_id       = $refund_item->get_meta( '_refunded_item_id', true );
				$refund_amount = abs( $refund->get_total() );
				$line_item     = $suborder->get_item( $item_id );

				if ( ! $line_item || $refund_amount <= 0 ) {
					continue;
				}

				$commission_id = $line_item->get_meta( '_commission_id' );
				$commission    = yith_wcmv_get_commission( $commission_id );
				if ( ! $commission ) {
					continue;
				}

				$commission_amount_refunded = (float) $commission->get_amount_refunded() - (float) $refund_item->get_meta( '_refund_commission_amount', true, 'edit' );
				$commission->set_amount_refunded( $commission_amount_refunded );
				// translators: %s stand for the commission amount credited.
				$message = sprintf( _x( 'Credited: %s', 'Commission note', 'yith-woocommerce-product-vendors' ), wc_price( abs( $commission_amount_refunded ), array( 'currency' => $suborder->get_currency() ) ) );
				$commission->add_note( $message );
				$commission->save();

				do_action( 'yith_wcmv_delete_commission_refund', $refund, $refund_item, $commission );
			}

			return true;
		}

		/**
		 * Get the message for tax and coupon management system for commission
		 *
		 * @param null|string  $commission_included_tax    (Optional) Who takes care of taxes.
		 * @param null|boolean $commission_included_coupon (Optional) Are commission calculated including coupons.
		 * @return string The message to show.
		 */
		public static function get_tax_and_coupon_management_message( $commission_included_tax = null, $commission_included_coupon = null ) {

			$commission_included_tax    = is_null( $commission_included_tax ) ? get_option( 'yith_wpv_commissions_tax_management', 'website' ) : $commission_included_tax;
			$commission_included_coupon = is_null( $commission_included_coupon ) ? 'yes' === get_option( 'yith_wpv_include_coupon', 'no' ) : $commission_included_coupon;
			$tax_string                 = array(
				'website' => _x( 'credited to the website admin', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
				'split'   => _x( 'split between vendor and admin', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
				'vendor'  => _x( 'credited to the vendor', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ),
			);

			// Add note to commission to know if the commission has been calculated included or excluded tax and coupon.
			$coupon = 'yes' === $commission_included_coupon ? _x( 'included', 'means: Vendor commission have been calculated: coupon included', 'yith-woocommerce-product-vendors' ) : _x( 'excluded', 'means: Vendor commission have been calculated: tax excluded', 'yith-woocommerce-product-vendors' );

			$tax_message = sprintf(
				'<br>* %s: <em>%s</em>',
				_x( 'taxes', 'part of: tax included or tax excluded', 'yith-woocommerce-product-vendors' ),
				strtolower( $tax_string[ $commission_included_tax ] )
			);

			$tax_message = apply_filters( 'yith_wcmv_commission_tax_message', $tax_message );

			$commission_have_been_calculated_text = _x( 'Vendor commission has been calculated', 'part of: Vendor commission have been calculated: tax included', 'yith-woocommerce-product-vendors' ) . ':';
			$commission_have_been_calculated_text = apply_filters( 'yith_wcmv_commission_have_been_calculated_text', $commission_have_been_calculated_text );

			$msg = sprintf(
				'%s<br>* %s <em>%s</em>%s',
				$commission_have_been_calculated_text,
				_x( 'coupon', 'part of: coupon included or coupon excluded', 'yith-woocommerce-product-vendors' ),
				$coupon,
				$tax_message
			);

			return $msg;
		}

		/**
		 * Retrieve the table for commission details
		 *
		 * @since  4.0.0
		 * @param YITH_Vendors_Commission $commission The commission instance.
		 * @param boolean                 $plain_text (Optional) True for plain text email, false otherwise. Default is false.
		 * @return void
		 */
		public function email_commission_details_table( $commission, $plain_text = false ) {
			ob_start();

			$template = $plain_text ? 'plain/commission-detail-table' : 'commission-detail-table';

			yith_wcmv_get_template(
				$template,
				array(
					'commission' => $commission,
					'order'      => $commission->get_order(),
					'vendor'     => $commission->get_vendor(),
					'item'       => $commission->get_item(),
					'product'    => $commission->get_product(),
				),
				'emails'
			);

			echo apply_filters( 'woocommerce_email_commission_detail_table', ob_get_clean(), $commission ); // phpcs:ignore
		}

		/**
		 * Get amount of commission unpaid for given vendor
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor ID.
		 * @return float
		 */
		public function get_unpaid_commissions_amount( $vendor_id ) {
			global $wpdb;

			$amount = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(amount) FROM $wpdb->commissions WHERE status = %s AND vendor_id = %d", 'unpaid', $vendor_id ) ); // phpcs:ignore
			return (float) $amount;
		}
	}
}
