<?php
/**
 * YITH Gateway Account Funds
 * Define methods and properties for class that manages payments via Account Funds
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Gateway_Account_Funds' ) ) {

	class YITH_Vendors_Gateway_Account_Funds extends YITH_Vendors_Gateway {

		/**
		 * The gateway slug/
		 *
		 * @var string
		 */
		protected $id = 'account-funds';

		/**
		 * The gateway name.
		 *
		 * @var string
		 */
		protected $method_title = 'Account Funds';

		/**
		 * YITH_Vendors_Gateway_Account_Funds constructor.
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public function __construct() {

			$this->register_gateway_options();
			$this->set_is_external( true );
			$this->set_is_available_on_checkout( true );

			$this->set_external_args(
				array(
					'check_method'   => 'class_exists',
					'check_for'      => 'YITH_YWF_Customer',
					'plugin_url'     => '//yithemes.com/themes/plugins/yith-woocommerce-account-funds/',
					'plugin_name'    => 'YITH WooCommerce Account Funds',
					'min_version'    => '1.3.0',
					'plugin_version' => defined( 'YITH_FUNDS_VERSION' ) ? YITH_FUNDS_VERSION : 0,
				)
			);

			parent::__construct();
		}

		/**
		 * Init class hooks and filters
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function init() {
			parent::init();

			if ( ! $this->is_enabled() ) {
				return;
			}

			if ( $this->is_enabled_for_checkout() ) {
				add_action( 'woocommerce_order_status_changed', array( $this, 'process_credit' ), 30, 3 );
			}

			if ( $this->is_commission_refund_enabled() ) {
				add_action( 'woocommerce_order_refunded', array( $this, 'process_refund' ), 25, 2 );
				add_action( 'yith_wcmv_delete_commission_refund', array( $this, 'remove_refund' ), 20, 3 );
			}
		}

		/**
		 * Register gateway options array
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function register_gateway_options() {
			$this->options = array(
				'account_funds_enable_refunds' => array(
					'id'      => 'yith_wcmv_enable_account_funds_refund',
					'type'    => 'onoff',
					'title'   => __( 'Refund commissions', 'yith-woocommerce-product-vendors' ),
					'desc'    => __( 'If enabled, the commission amount related to refunded orders will be removed from the vendor\'s balance. Please, note: the vendor\'s balance can be a negative value.', 'yith-woocommerce-product-vendors' ),
					'default' => 'no',
				),
			);
		}

		/**
		 * Process the commission payment
		 *
		 * @param array $payment_detail An array of payments data to use for payment.
		 * @return array Single or multiple response array [ status, message ].
		 */
		public function pay( $payment_detail ) {

			// Collect commission ids where payment fail.
			$commissions_failed = array();

			foreach ( $payment_detail as $vendor_id => $pay_data ) {
				$user_id  = $payment_detail[ $vendor_id ]['user_id'];
				$amounts  = $payment_detail[ $vendor_id ]['amount'];
				$customer = new YITH_YWF_Customer( $user_id );

				foreach ( $amounts as $currency => $amount ) {

					$commission_ids = $payment_detail[ $vendor_id ]['commission_ids'][ $currency ];
					$commission     = yith_wcmv_get_commission( $commission_ids[0] );
					$order          = $commission ? $commission->get_order() : false;
					if ( ! $order ) {
						continue;
					}

					$amount = round( $amount, 2 );

					$payment_id = $this->register_payment(
						array(
							'payment'        => array(
								'vendor_id'        => $vendor_id,
								'user_id'          => $user_id,
								'amount'           => $amount,
								'currency'         => $currency,
								'payment_date'     => $payment_detail[ $vendor_id ]['payment_date'],
								'payment_date_gmt' => $payment_detail[ $vendor_id ]['payment_date_gmt'],
							),
							'commission_ids' => $commission_ids,
						)
					);

					// Add funds in the vendor's account.
					$funds_to_add = apply_filters( 'yith_admin_deposit_funds', $amount, $order->get_id() );
					$funds_to_add = round( $funds_to_add, 2 );

					if ( $customer->add_funds( $funds_to_add ) ) {
						$status              = 'paid';
						$commissions_message = '';

						// Update commission status.
						foreach ( $commission_ids as $commission_id ) {
							$commission = yith_wcmv_get_commission( $commission_id );
							// Double check commission.
							if ( ! $commission ) {
								continue;
							}

							$commission->update_status( $status, '', true );
							$this->set_payment_post_meta( $commission );
							$gateway_payment_message = sprintf(
								'%s. %s %s',
								__( 'Payment correctly issued through the selected gateway', 'yith-woocommerce-product-vendors' ),
								_x( 'Paid via', '[Note]: Paid through gateway X', 'yith-woocommerce-product-vendors' ),
								$this->get_method_title()
							);

							$commission->add_note( urldecode( $gateway_payment_message ) );
							$commissions_message .= sprintf( '#%s,', $commission_id );
						}

						$commissions_message = trim( $commissions_message, ',' );
						// translators: %1$s stand for the commissions amount added, %2$s is the list of the commissions' id.
						$message = esc_html( _n( 'Added %1$s to funds for commission %2$s.', 'Added %1$s to funds for commissions %2$s.', count( $commission_ids ), 'yith-woocommerce-product-vendors' ) );

						$fund_log_args = array(
							'user_id'        => $user_id,
							'fund_user'      => $funds_to_add,
							'type_operation' => 'commission',
							'description'    => sprintf( $message, wc_price( $amount, array( 'currency' => $currency ) ), $commissions_message ),
							'order_id'       => $order->get_id(),
						);

						do_action( 'ywf_add_user_log', $fund_log_args );

					} else {
						$commissions_failed = array_merge( $commissions_failed, $commission_ids );
						$status             = 'failed';
					}

					$this->update_payment_status( $payment_id, $status );
				}
			}

			return array(
				'status'             => empty( $commissions_failed ),
				'commissions_failed' => $commissions_failed,
				'message'            => '',
			);
		}

		/**
		 * Process credit method.
		 *
		 * @param integer $order_id   The order ID.
		 * @param string  $old_status The order status.
		 * @param string  $new_status The new order status.
		 * @return void
		 */
		public function process_credit( $order_id, $old_status, $new_status ) {

			$order = wc_get_order( $order_id );
			$allowed_status = $this->get_order_status_allowed();
			if ( empty( $order ) || empty( $order->get_parent_id() ) || ! $order->has_status( $allowed_status ) ) { // Skip main order.
				return;
			}

			$commission_ids = yith_wcmv_get_commissions(
				array(
					'order_id' => $order_id,
					'status'   => 'all',
				)
			);
			if ( empty( $commission_ids ) ) {
				return;
			}

			$pay_data = $this->get_pay_data( array( 'commission_ids' => $commission_ids ) );
			$this->pay( $pay_data );
		}

		/**
		 * Check if is possible refund commissions.
		 *
		 * @return boolean
		 */
		public function is_commission_refund_enabled() {
			return 'yes' === get_option( 'yith_wcmv_enable_account_funds_refund', 'no' );
		}

		/**
		 * Process the commission refund
		 *
		 * @param integer $order_id  The order ID refunded.
		 * @param integer $refund_id THe refund ID created.
		 * @return void
		 */
		public function process_refund( $order_id, $refund_id ) {

			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			$parent_id = $order->get_parent_id();
			if ( empty( $parent_id ) || YITH_Vendors_Orders::CREATED_VIA !== $order->get_created_via() ) {
				return;
			}

			$refund = wc_get_order( $refund_id );
			if ( ! $refund ) {
				return;
			}

			// Process items.
			foreach ( $refund->get_items( array( 'line_item', 'shipping' ) ) as $refund_item ) {

				$refund_amount       = (float) $refund_item->get_meta( '_refund_commission_amount', true, 'edit' );
				$commission_refunded = $refund_item->get_meta( '_funds_refunded', true, 'edit' );
				$commission_refunded = ! empty( $commission_refunded ) || 'yes' === $commission_refunded;

				if ( ! empty( $refund_amount ) && ! $commission_refunded ) {
					$item_id       = $refund_item->get_meta( '_refunded_item_id' );
					$line_item     = $order->get_item( $item_id );
					$commission_id = $line_item->get_meta( '_commission_id' );

					$payments = YITH_Vendors()->payments->get_payments_details_by_commission_ids(
						$commission_id,
						array(
							'ID',
							'vendor_id',
							'user_id',
						),
						array(
							'status'     => 'paid',
							'gateway_id' => $this->get_id(),
						)
					);

					if ( $payments ) {
						foreach ( $payments as $payment ) {

							$commission = yith_wcmv_get_commission( $commission_id );
							if ( ! $commission ) {
								continue;
							}

							$order           = $commission->get_order();
							$currency        = $order->get_currency();
							$customer        = new YITH_YWF_Customer( $payment['user_id'] );
							$funds_to_remove = (float) apply_filters( 'yith_admin_deposit_funds', $refund_amount, $order->get_id() );
							$funds_to_remove = round( $funds_to_remove, 2 );

							if ( $customer->add_funds( $funds_to_remove ) ) {
								// Update payment status.
								$this->update_payment_status( $payment['ID'], 'refunded' );
								// translators: %s stand for the refund amount to remove.
								$message = sprintf( __( 'Removed %s from vendor funds\' balance.', 'yith-woocommerce-product-vendors' ), wc_price( $funds_to_remove, array( 'currency' => $currency ) ) );
								$commission->add_note( $message );
								$refund_item->add_meta_data( '_funds_refunded', 'yes', true );
								$refund_item->save();

								$commissions_message = sprintf( '#%s', $commission_id );
								$fund_log_args       = array(
									'user_id'        => $payment['user_id'],
									'fund_user'      => $funds_to_remove,
									'type_operation' => 'commission_refund',
									// translators: %1$s stand for the refund amount, %2$s is the commission id.
									'description'    => sprintf( __( 'Removed %1$s from funds for commission %2$s.', 'yith-woocommerce-product-vendors' ), wc_price( $refund_amount, array( 'currency' => $currency ) ), $commissions_message ),
									'order_id'       => $refund_item->get_id(),
								);

								do_action( 'ywf_add_user_log', $fund_log_args );
							}
						}
					}
				}
			}
		}

		/**
		 * Handle delete commission refund
		 *
		 * @param WC_Order_Refund         $refund      The order refund object.
		 * @param WC_Order_Item           $refund_item The order refund item.
		 * @param YITH_Vendors_Commission $commission  The commission object.
		 * @return void
		 */
		public function remove_refund( $refund, $refund_item, $commission ) {

			$refund_amount       = $refund_item->get_meta( '_refund_commission_amount', true, 'edit' );
			$commission_refunded = $refund_item->get_meta( '_funds_refunded', true, 'edit' );
			$commission_refunded = ! empty( $commission_refunded ) || 'yes' === $commission_refunded;

			if ( ! empty( (float) $refund_amount ) && $commission_refunded ) {

				$refund_amount = abs( (float) $refund_amount );
				$user          = $commission->get_user();
				$user_id       = $user->ID;
				$order         = $commission->get_order();
				$currency      = $order->get_currency();
				$commission_id = $commission->get_id();

				$funds_to_restore = apply_filters( 'yith_admin_deposit_funds', $refund_amount, $order->get_id() );
				$customer         = new YITH_YWF_Customer( $user_id );

				if ( $customer->add_funds( $funds_to_restore ) ) {
					// translators: %s stand for the refund amount.
					$message = sprintf( __( ' %s funds added to vendor funds\' balance.', 'yith-woocommerce-product-vendors' ), wc_price( $refund_amount, array( 'currency' => $currency ) ) );
					$commission->add_note( $message );

					$fund_log_args = array(
						'user_id'        => $user_id,
						'fund_user'      => $funds_to_restore,
						'type_operation' => 'commission',
						// translators: %1$s stand for the refund amount, %2$s is the commission id.
						'description'    => sprintf( __( 'Added %1$s funds for commission %2$s.', 'yith-woocommerce-product-vendors' ), wc_price( $refund_amount, array( 'currency' => $currency ) ), $commission_id ),
						'order_id'       => $refund_item->get_id(),
					);

					do_action( 'ywf_add_user_log', $fund_log_args );
				}
			}
		}
	}
}
