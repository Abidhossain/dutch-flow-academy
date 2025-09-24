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

if ( ! class_exists( 'YITH_Vendors_Commission' ) ) {
	/**
	 * Main class for the commission
	 */
	class YITH_Vendors_Commission extends YITH_Vendors_Commission_Legacy {

		/**
		 * Vendor associated to this commission
		 *
		 * @var null|YITH_Vendor
		 */
		protected $vendor = null;

		/**
		 * User associated to this commission
		 *
		 * @var null|WP_User
		 */
		protected $user = null;

		/**
		 * Order associated to this commission
		 *
		 * @var null|boolean|WC_Order
		 */
		protected $order = null;

		/**
		 * Product associated to this commission
		 *
		 * @var null|boolean|WC_Product
		 */
		protected $product = null;

		/**
		 * Order item associated to this commission
		 *
		 * @var null|boolean|WC_Order_Item
		 */
		protected $item = null;

		/**
		 * An array of refund associated to this commission
		 *
		 * @var null|array
		 */
		protected $refunds = null;

		/**
		 * Stores data about status changes so relevant hooks can be fired.
		 *
		 * @var bool|array
		 */
		protected $status_transition = false;

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 * @access public
		 * @param integer $commission_id The commission ID.
		 * @return YITH_Vendors_Commission|false The current object, false otherwise.
		 */
		public function __construct( $commission_id = 0 ) {
			$this->set_id( $commission_id );
			if ( $this->get_id() ) {
				$this->read();
			}

			return $this;
		}

		/**
		 * Save data to the database.
		 *
		 * @since 3.0.0
		 * @return int order ID
		 */
		public function save() {
			parent::save();
			$this->status_transition();

			return $this->get_id();
		}

		/**
		 * Get commission order ID
		 *
		 * @since  4.0.0
		 * @param string $context What the value is for. Valid values are view and edit.
		 * @return integer
		 */
		public function get_order_id( $context = 'view' ) {
			return absint( $this->get_data( 'order_id', $context ) );
		}

		/**
		 * Get order object of this commission
		 *
		 * @since  4.0.0
		 * @return WC_Order|boolean
		 */
		public function get_order() {
			if ( is_null( $this->order ) ) {
				$order_id    = $this->get_order_id();
				$this->order = ! empty( $order_id ) ? wc_get_order( $order_id ) : false;
			}

			return $this->order;
		}

		/**
		 * Get commission order line item ID
		 *
		 * @since  4.0.0
		 * @param string $context What the value is for. Valid values are view and edit.
		 * @return integer
		 */
		public function get_line_item_id( $context = 'view' ) {
			return absint( $this->get_data( 'line_item_id', $context ) );
		}

		/**
		 * Get item of order of this commission
		 *
		 * @since  4.0.0
		 * @return boolean|WC_Order_Item
		 */
		public function get_item() {
			if ( is_null( $this->item ) ) {
				$item_id    = $this->get_data( 'line_item_id' );
				$this->item = ! empty( $item_id ) ? WC_Order_Factory::get_order_item( $item_id ) : false;
			}

			return $this->item;
		}

		/**
		 * Returns total of the line that was used to generate current commission
		 *
		 * @since  4.0.0
		 * @param string $context What the value is for. Valid values are view and edit.
		 * @return float Line total.
		 */
		public function get_line_total( $context = 'view' ) {
			return (float) $this->get_data( 'line_total', $context );
		}

		/**
		 * Return product id for current commission
		 *
		 * @since  4.0.0
		 * @param string $context What the value is for. Valid values are view and edit.
		 * @return integer Product id.
		 */
		public function get_product_id( $context = 'view' ) {
			return (int) $this->get_data( 'product_id', $context );
		}

		/**
		 * Get the product object of this commission
		 *
		 * @since  4.0.0
		 * @return boolean|WC_Product
		 */
		public function get_product() {
			if ( is_null( $this->product ) ) {
				$product_id = $this->get_product_id();
				if ( $product_id ) {
					$this->product = wc_get_product( $product_id );
				} else { // Backward compatibility.
					$item          = $this->get_item();
					$this->product = ( $item && is_callable( array( $item, 'get_product' ) ) ) ? $item->get_product() : false;
				}
			}

			return $this->product;
		}

		/**
		 * Return product name for current commission
		 *
		 * @since  4.0.0
		 * @return string Product name.
		 */
		public function get_product_name() {
			$product = $this->get_product();
			return $product instanceof WC_Product ? $product->get_title() : '';
		}

		/**
		 * Get commission vendor ID
		 *
		 * @since  4.0.0
		 * @param string $context What the value is for. Valid values are view and edit.
		 * @return integer
		 */
		public function get_vendor_id( $context = 'view' ) {
			return absint( $this->get_data( 'vendor_id', $context ) );
		}

		/**
		 * Get vendor object of this commission
		 *
		 * @since  4.0.0
		 * @return boolean|YITH_Vendor
		 */
		public function get_vendor() {
			if ( is_null( $this->vendor ) ) {
				$vendor_id    = $this->get_vendor_id();
				$this->vendor = ! empty( $vendor_id ) ? yith_wcmv_get_vendor( $vendor_id ) : false;
			}

			return $this->vendor;
		}

		/**
		 * Get commission user ID
		 *
		 * @since  4.0.0
		 * @param string $context What the value is for. Valid values are view and edit.
		 * @return integer
		 */
		public function get_user_id( $context = 'view' ) {
			return absint( $this->get_data( 'user_id', $context ) );
		}

		/**
		 * Get user object of this commission
		 *
		 * @since  4.0.0
		 * @return boolean|WP_User
		 */
		public function get_user() {
			if ( is_null( $this->user ) ) {
				$user_id    = $this->get_user_id();
				$this->user = ! empty( $user_id ) ? get_user_by( 'id', $user_id ) : false;
			}

			return $this->user;
		}

		/**
		 * Get commission status
		 *
		 * @since  4.0.0
		 * @param string $context The context where retrieve status.
		 * @return string
		 */
		public function get_status( $context = '' ) {
			$status = $this->get_data( 'status' );
			if ( 'display' === $context ) {
				$all_status = yith_wcmv_get_commission_statuses( true );

				return isset( $all_status[ $status ] ) ? $all_status[ $status ] : '';
			} else {
				return $status;
			}
		}

		/**
		 * Get commission type
		 *
		 * @since  4.0.0
		 * @param string $context What the value is for. Valid values are view and edit.
		 * @return string
		 */
		public function get_type( $context = 'view' ) {
			return $this->get_data( 'type', $context );
		}

		/**
		 * Get commission currency
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_currency() {
			$order = $this->get_order();
			return $order ? $order->get_currency() : get_woocommerce_currency();
		}

		/**
		 * Get amount of commission
		 *
		 * @since  4.0.0
		 * @param string $context The context where retrieve date.
		 * @param array  $args    An array of arguments to use on wc_price.
		 * @return mixed
		 */
		public function get_amount( $context = '', $args = array() ) {
			$amount = $this->get_data( 'amount' );

			return 'display' === $context ? $this->format_price( $amount, $args ) : $amount;
		}

		/**
		 * Get amount refunded of commission
		 *
		 * @since  4.0.0
		 * @param string $context The context where retrieve date.
		 * @param array  $args    An array of arguments to use on wc_price.
		 * @return mixed
		 */
		public function get_amount_refunded( $context = '', $args = array() ) {
			$amount_refunded = $this->get_data( 'amount_refunded' );
			if ( 'display' === $context ) {
				return $amount_refunded ? $this->format_price( $amount_refunded, $args ) : '';
			}

			return $amount_refunded;
		}

		/**
		 * Get amount to pay of commission
		 *
		 * @since  4.0.0
		 * @param string $context (Optional) The context where retrieve date.
		 * @param array  $args    (Optional) An array of arguments to use on wc_price.
		 * @return mixed
		 */
		public function get_amount_to_pay( $context = '', $args = array() ) {

			$commission_amount          = $this->get_amount( 'edit' );
			$commission_amount_refunded = $this->get_amount_refunded( 'edit' );
			$amount_to_pay              = $commission_amount + $commission_amount_refunded;

			return 'display' === $context ? $this->format_price( $amount_to_pay, $args ) : $amount_to_pay;
		}

		/**
		 * Get the commission rate
		 *
		 * @since  4.0.0
		 * @param string $context (Optional) The context where retrieve date.
		 * @return mixed
		 */
		public function get_rate( $context = '' ) {
			$rate = $this->get_data( 'rate' );
			return 'display' === $context ? ( $rate * 100 ) . '%' : $rate;
		}

		/**
		 * Get the commission last edit date
		 *
		 * @since  4.0.0
		 * @param boolean $gmt (Optional) True to get GMT date, false otherwise. Default false.
		 * @return string
		 */
		public function get_last_edit( $gmt = false ) {
			return $gmt ? $this->get_data( 'last_edit_gmt' ) : $this->get_data( 'last_edit' );
		}

		/**
		 * Get the commission last edit GMT date
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_last_edit_gmt() {
			return $this->get_last_edit( true );
		}

		/**
		 * Get the commission created date
		 *
		 * @since  4.0.0
		 * @param boolean $gmt (Optional) True to get GMT date, false otherwise. Default false.
		 * @return string
		 */
		public function get_created_date( $gmt = false ) {
			return $gmt ? $this->get_data( 'created_date_gmt' ) : $this->get_data( 'created_date' );
		}

		/**
		 * Get the commission created GMT date
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_created_date_gmt() {
			return $this->get_created_date( true );
		}

		/**
		 * Get the date of commission, corresponding to order date
		 *
		 * @since  4.0.0
		 * @param string $context (Optional) The context where retrieve date. Default mysql.
		 * @return string
		 */
		public function get_date( $context = 'mysql' ) {

			$gmt = apply_filters( 'yith_wcmv_get_date_gmt', false );

			// Check first if commission has date_created set.
			$created_date = $this->get_created_date( $gmt );
			$date         = strtotime( $created_date );
			if ( empty( $date ) || '0000-00-00 00:00:00' === $created_date ) {
				$order = $this->get_order();
				if ( $order ) {
					$date = $order->get_date_created();
					$date = ( class_exists( 'WC_DateTime' ) && $date instanceof WC_DateTime ) ? $date->getTimestamp() : strtotime( $date );
				}
			}

			if ( 'display' === $context ) {
				$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
				$date   = date_i18n( $format, $date );
			} else {
				$date = date( 'Y-m-d H:i:s', $date ); // phpcs:ignore
			}

			return $date;
		}

		/**
		 * Retrieve the refunds from the order meta
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_refunds() {

			if ( is_null( $this->refunds ) ) {
				$this->refunds = array();
				$order         = $this->get_order();
				$item_id       = $this->get_line_item_id();

				if ( $order && $item_id ) {
					foreach ( $order->get_refunds() as $refund ) {
						$refund_items = $refund ? $refund->get_items( array( 'line_item', 'shipping' ) ) : array();

						foreach ( $refund_items as $refund_item ) {
							if ( $item_id === $refund_item->get_meta( '_refunded_item_id' ) ) {
								$this->refunds[ $refund->get_id() ] = $refund_item->get_meta( '_refund_commission_amount', true );
								break;
							}
						}
					}
				}
			}

			return (array) $this->refunds;
		}

		/**
		 * Set commission associated order ID
		 *
		 * @since  4.0.0
		 * @param integer $order_id The order ID to set.
		 * @return void
		 */
		public function set_order_id( $order_id ) {
			$order_id = absint( $order_id );
			$order    = $order_id ? wc_get_order( $order_id ) : false;
			if ( $order ) {
				$this->set_data( 'order_id', $order_id );
			}
		}

		/**
		 * Set commission associated user ID
		 *
		 * @since  4.0.0
		 * @param integer $user_id The user ID to set.
		 * @return void
		 */
		public function set_user_id( $user_id ) {
			$user_id = absint( $user_id );
			if ( $user_id ) {
				$user = get_user_by( 'id', $user_id );
				if ( ! $user || ! $user->exists() ) {
					return;
				}
			}

			$this->set_data( 'user_id', $user_id );
		}

		/**
		 * Set commission associated vendor ID
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor ID to set.
		 * @return void
		 */
		public function set_vendor_id( $vendor_id ) {
			$vendor_id = absint( $vendor_id );
			$vendor    = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;
			if ( $vendor && $vendor->is_valid() ) {
				$this->set_data( 'vendor_id', $vendor_id );
			}
		}

		/**
		 * Set commission associated order line item ID
		 *
		 * @since  4.0.0
		 * @param integer $line_item_id The line item ID to set.
		 * @return void
		 */
		public function set_line_item_id( $line_item_id ) {
			$line_item_id = absint( $line_item_id );
			$line_item    = $line_item_id ? WC_Order_Factory::get_order_item( $line_item_id ) : false;
			if ( $line_item ) {
				$this->set_data( 'line_item_id', $line_item_id );
			}
		}

		/**
		 * Set line total for current commissions
		 *
		 * @since  4.0.0
		 * @param float $line_total Line total used to calculate current commission.
		 */
		public function set_line_total( $line_total ) {
			$line_total = (float) $line_total;

			if ( $line_total < 0 ) {
				$line_total = 0;
			}

			$this->set_data( 'line_total', $line_total );
		}

		/**
		 * Set product id for the commission
		 *
		 * @param int $product_id Product id.
		 */
		public function set_product_id( $product_id ) {
			$this->set_data( 'product_id', absint( $product_id ) );
		}

		/**
		 * Set commission rate
		 *
		 * @since  4.0.0
		 * @param float $rate (Optional) The commission rate to set. If empty use the global value.
		 * @return void
		 */
		public function set_rate( $rate = 0 ) {
			$rate = $this->format_rate( $rate );
			// Make sure rate is correctly set.
			if ( empty( $rate ) ) {
				// Set commission by vendor if isset.
				$vendor = $this->get_vendor();
				if ( $vendor && $vendor->is_valid() ) {
					$rate = $vendor->get_commission();
				} else {
					$rate = yith_wcmv_get_base_commission();
					$rate = $rate / 100;
				}
				// Pass to format to make sure it is correctly set.
				$rate = $this->format_rate( $rate );
			}

			if ( ! empty( $rate ) ) {
				$this->set_data( 'rate', $rate );
			}
		}

		/**
		 * Set commission amount
		 *
		 * @since  4.0.0
		 * @param float $amount The commission amount to set.
		 * @return void
		 */
		public function set_amount( $amount ) {
			$amount = round( floatval( $amount ), 4 );
			$this->set_data( 'amount', $amount );
		}

		/**
		 * Set commission amount refunded
		 *
		 * @since  4.0.0
		 * @param float $amount_refunded The commission amount refund to set.
		 * @return void
		 */
		public function set_amount_refunded( $amount_refunded ) {
			$amount_refunded = round( floatval( $amount_refunded ), 4 );
			$this->set_data( 'amount_refunded', $amount_refunded );
		}

		/**
		 * Set commission status
		 *
		 * @since  4.0.0
		 * @param string  $new_status The commission status to set.
		 * @param string  $note       A note to add to commission.
		 * @param boolean $force      True to force status update, false otherwise.
		 * @return boolean True if new status is set, false otherwise
		 */
		public function set_status( $new_status, $note = '', $force = false ) {

			$all_status = yith_wcmv_get_commission_statuses( true );

			if ( array_key_exists( $new_status, $all_status ) ) {
				$old_status = $this->get_status();

				// If the new status is the same of the old one and force param is false, just return true.
				if ( $new_status === $old_status && ! $force ) {
					return true;
				}
				// If a commission have no status yet or force is set or status change is permitted, do it!
				if ( empty( $old_status ) || $force || YITH_Vendors()->commissions->is_status_changing_permitted( $new_status, $old_status ) ) {
					$this->set_data( 'status', $new_status );

					if ( $old_status ) {
						// translators: %1$s stand for the current commission status, %2$s is the new commission status.
						$note .= ' ' . sprintf( __( 'Commission status changed from %1$s to %2$s.', 'yith-woocommerce-product-vendors' ), $all_status[ $old_status ], $all_status[ $new_status ] );
						$this->store_status_transition( $old_status, $new_status, $note );
					}

					return true;
				}
			}

			return false;
		}

		/**
		 * Set date commission was created
		 *
		 * @since  4.0.0
		 * @param integer|string|WC_DateTime $created_date Date of creation.
		 */
		public function set_created_date( $created_date ) {
			$this->set_date_prop( 'created_date', $created_date );
		}

		/**
		 * Set date commission was created
		 *
		 * @since  4.0.0
		 * @param integer|string|WC_DateTime $created_date Date of creation.
		 */
		public function set_created_date_gmt( $created_date ) {
			$this->set_date_prop( 'created_date_gmt', $created_date, true );
		}

		/**
		 * Set date commission was last edited
		 *
		 * @since  4.0.0
		 * @param integer|string|WC_DateTime $last_edit Date of last edit.
		 */
		public function set_last_edit( $last_edit ) {
			$this->set_date_prop( 'last_edit', $last_edit );
		}

		/**
		 * Set date commission was last edited
		 *
		 * @since  4.0.0
		 * @param integer|string|WC_DateTime $last_edit Date of last edit (timestamp or date).
		 */
		public function set_last_edit_gmt( $last_edit ) {
			$this->set_date_prop( 'last_edit_gmt', $last_edit, true );
		}

		/**
		 * Change status of commissions immediately
		 * WC Order Status  ->  YITH Commissions Status
		 * pending          ->  pending
		 * processing       ->  pending
		 * on-hold          ->  unpaid
		 * completed        ->  paid
		 * cancelled        ->  cancelled
		 * failed           ->  cancelled
		 * refunded         ->  refunded
		 *
		 * @since  4.0.0
		 * @param string  $new_status The new commission status.
		 * @param string  $note       A note to add to commission.
		 * @param boolean $force      (Optional) True to force change, false otherwise.
		 * @return bool
		 */
		public function update_status( $new_status, $note = '', $force = false ) {
			if ( $this->exists() && $this->set_status( $new_status, $note, $force ) ) {
				$this->save();
				return true;
			}

			return false;
		}

		/**
		 * Store the status transition.
		 *
		 * @since  4.0.0
		 * @param string $from The starting status.
		 * @param string $to   The new status.
		 * @param string $note (Optional) The transition status note.
		 */
		protected function store_status_transition( $from, $to, $note = '' ) {
			$this->status_transition = array(
				'from' => ! empty( $this->status_transition['from'] ) ? $this->status_transition['from'] : $from,
				'to'   => $to,
				'note' => $note,
			);
		}

		/**
		 * Handle the status transition.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function status_transition() {
			$status_transition = $this->status_transition;
			// Reset status transition variable.
			$this->status_transition = false;

			if ( $status_transition ) {

				if ( ! empty( $status_transition['note'] ) ) {
					$this->add_note( $status_transition['note'] );
				};

				// Status was changed.
				// TODO add deprecated for this actions
				do_action( 'yith_commission_status_' . $status_transition['to'], $this->get_id() );
				do_action( 'yith_commission_status_' . $status_transition['from'] . '_to_' . $status_transition['to'], $this->get_id() );
				do_action( 'yith_commission_status_changed', $this->get_id(), $status_transition['from'], $status_transition['to'] );

				if ( 'paid' === $status_transition['to'] && apply_filters( 'yith_wcmv_send_commission_paid_email', true, $this ) ) {
					WC()->mailer();
					// TODO add deprecated for this actions
					do_action( 'yith_vendors_commissions_paid', $this );
				}
			}
		}

		/**
		 * Set commission type
		 *
		 * @since  4.0.0
		 * @param string $type The commission type to set.
		 * @return void
		 */
		public function set_type( $type ) {
			$this->set_data( 'type', $type );
		}

		/**
		 * Checks the commission status against a passed in status.
		 *
		 * @since  4.0.0
		 * @param string|array $status Status to check.
		 * @return boolean
		 */
		public function has_status( $status ) {
			return apply_filters( 'yith_wcmv_commission_has_status', ( ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status ), $this, $status );
		}

		/**
		 * Format a currency price
		 *
		 * @since  4.0.0
		 * @param float|integer $price The price to format.
		 * @param array         $args  An array of args to use in wc price.
		 * @return string
		 */
		protected function format_price( $price, $args ) {
			// Merge with default args.
			$args = array_merge( array( 'currency' => $this->get_currency() ), $args );

			return wc_price( $price, $args );
		}

		/**
		 * Format commission rate to prevent value error
		 *
		 * @since  4.0.0
		 * @param float $rate The rate to format.
		 * @return float
		 */
		protected function format_rate( $rate ) {
			$rate = round( floatval( $rate ), 4 );
			if ( $rate > 1 ) {
				$rate = 1;
			} elseif ( $rate < 0 ) {
				$rate = 0;
			}

			return $rate;
		}

		/**
		 * Retrieve the URL for viewing the commission details
		 *
		 * @since  1.0.0
		 * @param string $context (Deprecated) The context where retrieve date. Default admin.
		 * @return string
		 */
		public function get_view_url( $context = 'admin' ) {
			$url = '';
			if ( 'admin' === $context && class_exists( 'YITH_Vendors_Admin_Commissions' ) ) {
				$url = add_query_arg(
					array(
						's'                 => $this->get_id(),
						'commission_status' => 'all',
					),
					YITH_Vendors_Admin_Commissions::get_commissions_list_table_url()
				);
			}

			return apply_filters( 'yith_wcmv_commission_get_view_url', $url, $context, $this );
		}

		/**
		 * Get formatted order uri
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_formatted_order_uri() {
			$order = $this->get_order();

			if ( ! $order ) {
				$return = '<span class="order-deleted">' . esc_html__( 'Order Deleted', 'yith-woocommerce-product-vendors' ) . '</span>';
			} else {
				$order_id = $order->get_id();
				$return   = '<strong>#' . esc_attr( $order->get_order_number() ) . '</strong>';
				if ( current_user_can( 'manage_woocommerce' ) || apply_filters( 'yith_wcmv_commission_list_table_show_order_edit', false ) ) {
					$order_uri = apply_filters( 'yith_wcmv_commissions_list_table_order_url', admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' ), $this, $order );
					$return    = '<a href="' . $order_uri . '">' . $return . '</a>';
				}
			}

			return apply_filters( 'yith_wcmv_commission_get_formatted_order_uri', $return, $this );
		}
	}
}
