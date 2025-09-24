<?php
/**
 * YITH Gateway PayPal Payouts
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Gateway_Paypal_Payouts' ) ) {
	/**
	 * Define methods and properties for class that manages payments via PayPal Payouts.
	 */
	class YITH_Vendors_Gateway_Paypal_Payouts extends YITH_Vendors_Gateway {

		/**
		 * The gateway slug.
		 *
		 * @var string
		 */
		protected $id = 'paypal-payouts';

		/**
		 * The gateway name.
		 *
		 * @var string
		 */
		protected $method_title = 'PayPal Payouts';

		/**
		 * YITH_Vendors_Gateway_PayPal_Payouts constructor.
		 *
		 * @return void
		 * @since 4.0.0
		 */
		public function __construct() {
			$this->set_is_external( true );
			$this->set_is_available_on_checkout( true );

			$this->set_external_args(
				array(
					'check_method'   => 'function_exists',
					'check_for'      => 'YITH_PayPal_Payouts',
					'plugin_url'     => '//yithemes.com/themes/plugins/yith-paypal-payouts-for-woocommerce/',
					'plugin_name'    => 'YITH PayPal Payouts for WooCommerce',
					'min_version'    => '1.0.0',
					'plugin_version' => defined( 'YITH_PAYOUTS_VERSION' ) ? YITH_PAYOUTS_VERSION : 0,
				)
			);

			parent::__construct();
		}

		/**
		 * Init gateway hooks and filters
		 *
		 * @return void
		 * @since  4.0.0
		 */
		protected function init() {
			parent::init();

			if ( ! $this->is_enabled() ) {
				return;
			}

			add_action( 'yith_paypal_payout_batch_change_status', array( $this, 'change_commissions_status' ), 10, 3 );
			add_filter( 'yith_wcmv_vendor_admin_fields', array( $this, 'add_threshold_option' ), 10, 1 );

			// Checkout Payment.
			if ( $this->is_enabled_for_checkout() ) {
				add_action( 'woocommerce_order_status_changed', array( $this, 'process_credit' ), 30, 4 );
			}

			if ( 'yes' === get_option( 'yith_payouts_exclude_vendor_commission', 'yes' ) ) {
				add_filter( 'yith_payouts_include_item', array( $this, 'remove_vendor_product_from_calculation' ), 10, 2 );
			}

			add_filter( 'yith_payout_receiver_email', array( $this, 'return_paypal_email' ), 10, 2 );
			add_filter( 'yith_payouts_payment_mode', array( $this, 'add_payouts_commission_payment_mode' ), 10, 1 );
			add_filter( 'yith_payouts_items_columns', array( $this, 'commission_column_name' ), 10, 1 );
		}

		/**
		 * Register gateway options array
		 *
		 * @return void
		 * @since  4.0.0
		 */
		public function register_gateway_options() {
			$this->options = array(
				'payment_minimum_withdrawals' => array(
					'id'                => 'yith_wcmv_paypal_payment_minimum_withdrawals',
					'type'              => 'number',
					'title'             => __( 'Minimum Withdrawal', 'yith-woocommerce-product-vendors' ) . ' ' . get_woocommerce_currency_symbol(),
					'desc'              => __( "Set the minimum value for commission withdrawals. This setting will update all vendors' accounts that still have a threshold lower than the one set.", 'yith-woocommerce-product-vendors' ),
					'custom_attributes' => array(
						'min' => 0,
					),
					'default'           => 1,
				),
			);
		}

		/**
		 * Check if the current gateway is enabled or not. Extend check to correct PayPal conf.
		 *
		 * @return boolean true if enabled, false otherwise.
		 * @since  1.0.0
		 */
		public function is_enabled() {
			return parent::is_enabled() && $this->is_external_plugin_configured();
		}

		/**
		 * Check if external plugin is configured.
		 *
		 * @return boolean
		 * @since  4.0.0
		 */
		public function is_external_plugin_configured() {
			if ( $this->is_external_plugin_enabled() ) {
				// Load Payouts core classes.
				YITH_PayPal_Payouts()->load_payouts_classes();
				$enabled = function_exists( 'YITH_PayOuts_Service' ) && YITH_PayOuts_Service()->check_service_configuration();

				return $enabled;
			}

			return false;
		}

		/**
		 * Add external plugin required message
		 *
		 * @param string $gateway_id The gateway ID.
		 * @return bool True if the external plugin is required, false otherwise.
		 * @since  1.0.0
		 */
		public function add_is_external_required_message( $gateway_id ) {
			if ( ! $this->is_external_plugin_configured() ) {
				$panel_url  = add_query_arg( 'page', 'yith_wc_paypal_payouts_panel', admin_url( 'admin.php' ) );
				$gateway_id = sprintf( '<a href="%s" class="yith-wcmv-gateway-required-external" target="_blank">%s</a>', $panel_url, __( 'Please complete the plugin configuration to enable this gateway', 'yith-woocommerce-product-vendors' ) );
			}

			return parent::add_is_external_required_message( $gateway_id );
		}

		/**
		 * Format payment data for Paypal Payouts. We should divide commission by order ID.
		 *
		 * @param array $payment_data Array of parameters to format for PayPal Payouts.
		 * @return array
		 * @since  4.0.0
		 */
		protected function format_payment_data( $payment_data ) {

			foreach ( $payment_data as $vendor_id => &$data ) {

				$commissions = $data['commission_ids'];
				// Reset current data.
				$data['amount']         = array();
				$data['commission_ids'] = array();

				foreach ( $commissions as $currency => $commission_ids ) {
					foreach ( $commission_ids as $commission_id ) {
						$commission = yith_wcmv_get_commission( $commission_id );
						if ( ! $commission ) {
							continue;
						}

						$order_id = $commission->get_order_id();
						if ( ! isset( $data['amount'][ $currency ][ $order_id ] ) ) {
							$data['amount'][ $currency ][ $order_id ] = $commission->get_amount_to_pay();
						} else {
							$data['amount'][ $currency ][ $order_id ] += $commission->get_amount_to_pay();
						}

						if ( ! isset( $data['commission_ids'][ $currency ][ $order_id ] ) ) {
							$data['commission_ids'][ $currency ][ $order_id ] = array( $commission_id );
						} else {
							$data['commission_ids'][ $currency ][ $order_id ][] = $commission_id;
						}
						$data['amount'][ $currency ][ $order_id ] = number_format( $data['amount'][ $currency ][ $order_id ], 2 );
					}
				}
			}

			return $payment_data;
		}

		/**
		 * Pay method, used to process payment requests
		 *
		 * @param array $payment_data Array of parameters for the single requests.
		 * @return array
		 */
		public function pay( $payment_data ) {

			// Collect commission ids where payment fail.
			$commissions_failed = array();

			$current_site_id = md5( ( get_site_url() ) );
			$note_to_send    = apply_filters( 'yith_wcmv_gateway_paypal_payouts_vendor_note', 'Thank you', $payment_data );

			foreach ( $this->format_payment_data( $payment_data ) as $vendor_id => $pay_data ) {
				$vendor       = yith_wcmv_get_vendor( $vendor_id );
				$paypal_email = $vendor ? $vendor->get_meta( 'paypal_email' ) : '';

				foreach ( $pay_data['amount'] as $currency => $amounts ) {
					foreach ( $amounts as $order_id => $amount ) {
						$commission_ids = $pay_data['commission_ids'][ $currency ][ $order_id ];

						if ( empty( $paypal_email ) ) {
							$error   = true;
							$message = _x( 'Error processing payment using PayPal Payouts: the vendor has not set a valid PayPal email.', 'Gateway error message', 'yith-woocommerce-product-vendors' );
						} else {
							// Create entry in Payments table.
							$payment_id = $this->register_payment(
								array(
									'payment'        => array(
										'user_id'          => $pay_data['user_id'],
										'vendor_id'        => $vendor_id,
										'amount'           => $amount,
										'currency'         => $currency,
										'payment_date'     => $pay_data['payment_date'],
										'payment_date_gmt' => $pay_data['payment_date_gmt'],
									),
									'commission_ids' => $commission_ids,
								)
							);

							$sender_items = array(
								array(
									'recipient_type' => 'EMAIL',
									'receiver'       => $paypal_email,
									'note'           => $note_to_send,
									'amount'         => array(
										'value'    => $amount,
										'currency' => $currency,
									),
								),
							);

							YITH_PayOuts_Service()->register_payouts(
								array(
									'sender_batch_id' => 'commission_' . $payment_id . '_' . $current_site_id,
									'order_id'        => $order_id,
									'items'           => $sender_items,
									'payout_mode'     => 'commission',
								)
							);

							$response = YITH_PayOuts_Service()->PayOuts(
								array(
									'sender_batch_id' => 'commission_' . $payment_id . '_' . $current_site_id,
									'sender_items'    => $sender_items,
									'commission_ids'  => $commission_ids,
									'order_id'        => $order_id,
								)
							);

							if ( empty( $response ) ) {
								$error   = true;
								$message = _x( 'Error processing the payment using the PayPal Payouts service. Check the log for more details.', 'Gateway error message', 'yith-woocommerce-product-vendors' );
							}
						}

						foreach ( $commission_ids as $commission_id ) {
							$commission = yith_wcmv_get_commission( $commission_id );
							if ( ! $commission ) {
								continue;
							}
							if ( empty( $error ) ) {
								$commission->update_status( 'processing' );
							} else {
								$commissions_failed[] = $commission_id;
								$commission->add_note( $message );
							}
						}
					}
				}
			}

			return array(
				'status'             => empty( $commissions_failed ),
				'commissions_failed' => $commissions_failed,
				'message'            => empty( $commissions_failed ) ? _x( 'Payment request sent to PayPal successfully. In a few minutes, the commission status will be changed according to PayPal\'s response.', 'Gateway success message', 'yith-woocommerce-product-vendors' ) : '',
			);
		}

		/**
		 * Change commission status based on batch payment response.
		 *
		 * @param string $payout_batch_id Payout batch ID.
		 * @param string $status Payout batch status.
		 * @param array  $batch_header Payout batch header.
		 * @return void
		 */
		public function change_commissions_status( $payout_batch_id, $status, $batch_header ) {

			// Extract payment_id from sender_batch_id.
			$payment_id     = $batch_header['sender_batch_header']['sender_batch_id'];
			$payment_id     = explode( '_', $payment_id );
			$payment_id     = isset( $payment_id[1] ) ? absint( $payment_id[1] ) : 0;
			$payment_status = false;
			// Get payment status.
			if ( 'success' === strtolower( $status ) ) {
				$payment_status = 'paid';
			} elseif ( 'denied' === strtolower( $status ) ) {
				$payment_status = 'failed';
			}

			if ( ! empty( $payment_id ) || ! empty( $payment_status ) ) {
				$commission_ids = YITH_Vendors()->payments->get_commissions_by_payment_id( $payment_id );
				foreach ( $commission_ids as $commission_id ) {
					$commission = yith_wcmv_get_commission( $commission_id );
					if ( ! $commission ) {
						continue;
					}

					if ( $payment_status ) {
						if ( 'paid' === $payment_status ) {
							// translators: %1$s is the payment method title, %2$s is the payment batch ID.
							$commission->update_status( 'paid', sprintf( __( 'Commission paid via %1$s (batch ID: %2$s)', 'yith-woocommerce-product-vendors' ), $this->get_method_title(), $payout_batch_id ) );
							$this->set_payment_post_meta( $commission );
							// Update payment status.
						} elseif ( 'failed' === $payment_status ) {
							// translators: %s is the payment method title.
							$commission->update_status( 'unpaid', sprintf( __( 'Payment via %s failed.', 'yith-woocommerce-product-vendors' ), $this->get_method_title(), $status ) );
						}
					}

					$this->update_payment_status( $payment_id, $payment_status );
				}
			}
		}

		/**
		 * Process commissions at checkout
		 *
		 * @param integer       $order_id The order ID.
		 * @param string        $old_status The old order status.
		 * @param string        $new_status The new order status.
		 * @param WC_Order|null $order (Optional) The order object instance. Default is null.
		 * @return mixed
		 */
		public function process_credit( $order_id, $old_status, $new_status, $order = null ) {

			$allowed_status = $this->get_order_status_allowed();
			if ( empty( $order ) || empty( $order->get_parent_id() ) || ! apply_filters( 'yith_wcmv_gateway_paypal_payouts_order_status', $order->has_status( $allowed_status ), $order ) ) { // Skip main order.
				return false;
			}

			$commission_ids = $this->get_commissions( $order );
			if ( empty( $commission_ids ) ) {
				return false;
			}

			$pay_data = $this->get_pay_data( array( 'commission_ids' => $commission_ids ) );

			return $this->pay( $pay_data );
		}

		/**
		 * Get commissions to pay for vendor on order status changed
		 *
		 * @param WC_Order $order The order object processed.
		 * @return array
		 */
		protected function get_commissions( $order ) {

			$args = array(
				'order_id' => $order->get_id(),
				'status'   => 'unpaid',
				'fields'   => 'ids',
			);

			if ( $this->is_threshold_enabled() ) {

				$vendor_id = yith_wcmv_get_vendor_id_for_order( $order );
				$vendor    = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;

				if ( empty( $vendor ) || ! $vendor->is_valid() ) {
					return array();
				}

				$threshold = $vendor->get_meta( 'threshold' );
				if ( empty( $threshold ) || $threshold < $this->get_default_threshold_amount() ) {
					$threshold = $this->get_default_threshold_amount();
				}

				if ( YITH_Vendors()->commissions->get_unpaid_commissions_amount( $vendor_id ) < $threshold ) {
					return array();
				}

				$args = array_merge(
					$args,
					array(
						'order_id'  => '',
						'vendor_id' => $vendor_id,
					)
				);
			}

			return yith_wcmv_get_commissions( $args );
		}

		/**
		 * Get default threshold amount
		 *
		 * @return integer
		 * @since  4.0.0
		 */
		protected function get_default_threshold_amount() {
			return get_option( 'yith_wcmv_paypal_payment_minimum_withdrawals', 1 );
		}

		/**
		 * Check if the threshold option is enabled and valid
		 *
		 * @return bool
		 */
		protected function is_threshold_enabled() {
			return $this->get_default_threshold_amount() > 0;
		}

		/**
		 * Remove vendor products from compute global payouts
		 *
		 * @param boolean       $include_product Current value. True to include item, false otherwise.
		 * @param WC_Order_Item $item CUrrent processing order item.
		 * @return bool
		 * @since  1.0.0
		 */
		public function remove_vendor_product_from_calculation( $include_product, $item ) {

			$product_id = is_callable( array( $item, 'get_product_id' ) ) ? $item->get_product_id() : 0;
			$vendor     = $product_id ? yith_wcmv_get_vendor( $product_id, 'product' ) : false;

			if ( $vendor && $vendor->is_valid() ) {
				$include_product = false;
			}

			return $include_product;
		}

		/**
		 * Filter payouts receiver email with the vendor PayPal email.
		 *
		 * @param string  $email Current email value.
		 * @param integer $user_id Current user ID.
		 * @return string
		 */
		public function return_paypal_email( $email, $user_id ) {
			$vendor = yith_wcmv_get_vendor( $user_id, 'user' );
			if ( empty( $email ) && $vendor && $vendor->is_valid() ) {
				$email = $vendor->get_meta( 'paypal_email' );
			}

			return $email;
		}

		/**
		 * Add threshold option to vendor factory fields.
		 *
		 * @param array $fields The vendor fields.
		 * @return array
		 * @since  4.0.0
		 */
		public function add_threshold_option( $fields ) {

			$threshold = $this->get_default_threshold_amount();

			if ( isset( $fields['payment'] ) && $threshold ) {
				$fields['payment']['threshold'] = array(
					'type'              => 'number',
					'label'             => _x( 'Threshold', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ) . ' ' . get_woocommerce_currency_symbol(),
					'description'       => _x( 'Minimum vendor\'s earnings before vendor commissions can be paid.', '[Admin] Vendor option description', 'yith-woocommerce-product-vendors' ),
					'default'           => $threshold,
					'custom_attributes' => array(
						'min' => $threshold,
					),
				);
			}

			return $fields;
		}

		/**
		 * Add payment mode "commission" to payout payments mode
		 *
		 * @param array $payment_mode An array of available payments mode.
		 * @return array
		 */
		public function add_payouts_commission_payment_mode( $payment_mode ) {
			if ( ! isset( $payment_mode['commission'] ) ) {
				$payment_mode['commission'] = __( 'Commission Payment', 'yith-paypal-payouts-for-woocommerce' );
			}

			return $payment_mode;
		}

		/**
		 * Change label for order column to Commission ID.
		 *
		 * @param array $columns An array of columns to show in payout details.
		 * @return array
		 */
		public function commission_column_name( $columns ) {
			$columns['order'] = __( 'Commission ID', 'yith-woocommerce-product-vendors' );

			return $columns;
		}
	}
}
