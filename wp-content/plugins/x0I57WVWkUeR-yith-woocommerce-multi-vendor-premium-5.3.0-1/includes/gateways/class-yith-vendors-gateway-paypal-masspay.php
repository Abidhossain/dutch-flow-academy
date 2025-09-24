<?php
/**
 * YITH YITH_Vendors_Gateway_Manual
 * Define methods and properties for class that manages payments via paypal
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

use angelleye\PayPal\PayPal;

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Gateway_Paypal_Masspay' ) ) {

	class YITH_Vendors_Gateway_Paypal_Masspay extends YITH_Vendors_Gateway {

		/**
		 * Status for payments correctly sent
		 *
		 * @const string
		 * @since 1.0
		 */
		const PAYMENT_STATUS_OK = 'Success';

		/**
		 * Status for payments failed
		 *
		 * @const string
		 * @since 1.0
		 */
		const PAYMENT_STATUS_FAIL = 'Failure';

		/**
		 * The gateway slug.
		 *
		 * @var string
		 */
		protected $id = 'paypal-masspay';

		/**
		 * The gateway name.
		 *
		 * @var string
		 */
		protected $method_title = 'PayPal MassPay';

		/**
		 * YITH_Vendors_Gateway_Paypal_Masspay constructor.
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public function __construct() {
			$this->set_is_available_on_checkout( true );
			parent::__construct();
		}

		/**
		 * Init gateway hooks and filters
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function init() {
			// Disable table action based on PayPal Settings.
			add_filter( "yith_wcmv_add_{$this->get_id()}_table_action", array( $this, 'paypal_is_configured' ), 10, 1 );
			parent::init();

			if ( ! $this->is_enabled() ) {
				return;
			}

			// Vendor's Panel: payments tab.
			add_filter( 'yith_wcmv_vendor_admin_fields', array( $this, 'add_masspay_options' ), 10, 1 );
			// Checkout Payment.
			if ( $this->is_enabled_for_checkout() ) {
				// Hook the IPNListener.
				add_action( 'init', array( $this, 'handle_notification' ), 30 );
				add_action( 'woocommerce_order_status_changed', array( $this, 'process_credit' ), 20, 3 );
			}
		}

		/**
		 * Check if the current gateway is enabled or not
		 *
		 * @return bool TRUE if enabled, FALSE otherwise
		 */
		public function is_enabled() {
			$enabled            = 'yes' === get_option( "yith_wcmv_enable_{$this->get_id()}_gateway", apply_filters( 'yith_wcmv_is_enable_gateway_default_value', 'yes' ) );
			$is_masspay_enabled = 'masspay' === get_option( 'yith_wcmv_paypal_payment_gateway', 'masspay' );

			return $enabled && $is_masspay_enabled;
		}

		/**
		 * Check if PayPal is correctly configured
		 *
		 * @since  4.0.0
		 * @param boolean $value Current filter value.
		 * @return boolean True if it is correctly configured, false otherwise.
		 */
		public function paypal_is_configured( $value = true ) {
			// Retrieve saved options from panel and check for required API settings.
			$stored_options = $this->get_gateway_options();
			if ( empty( $stored_options['api_username'] ) || empty( $stored_options['api_password'] ) || empty( $stored_options['api_signature'] ) ) {
				return false;
			}

			return $value;
		}

		/**
		 * Register gateway options array
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function register_gateway_options() {
			$this->options = array(
				'payment_gateway'             => array(
					'id'      => 'yith_wcmv_paypal_payment_gateway',
					'type'    => 'select',
					'title'   => __( 'PayPal Service', 'yith-woocommerce-product-vendors' ),
					'desc'    => __( 'Choose PayPal service to pay the commissions to vendors (the only option currently available is MassPay).', 'yith-woocommerce-product-vendors' ),
					'options' => apply_filters(
						'yith_wcmv_payments_gateway',
						array(
							'masspay' => __( 'MassPay', 'yith-woocommerce-product-vendors' ),
						)
					),
					'default' => 'masspay',
				),
				'payment_method'              => array(
					'id'      => 'yith_wcmv_paypal_payment_method',
					'type'    => 'select',
					'title'   => __( 'Payment Method', 'yith-woocommerce-product-vendors' ),
					'desc'    => __( 'Choose how to pay the commissions to vendors.', 'yith-woocommerce-product-vendors' ),
					'options' => array(
						'manual' => __( 'Pay manually', 'yith-woocommerce-product-vendors' ),
						'choose' => __( 'Let vendors decide', 'yith-woocommerce-product-vendors' ),
					),
					'default' => 'choose',
				),
				'payment_minimum withdrawals' => array(
					'id'                => 'yith_wcmv_paypal_payment_minimum_withdrawals',
					'type'              => 'number',
					'title'             => __( 'Minimum Withdrawal', 'yith-woocommerce-product-vendors' ) . ' ' . get_woocommerce_currency_symbol(),
					'desc'              => __( "Set the minimum value for commission withdrawals. This setting will update all vendors' accounts that still have a threshold lower than the one set.", 'yith-woocommerce-product-vendors' ),
					'custom_attributes' => array(
						'min' => 1,
					),
					'default'           => 1,
				),
				'paypal_sandbox'              => array(
					'id'      => 'yith_wcmv_paypal_sandbox',
					'type'    => 'onoff',
					'title'   => __( 'Sandbox environment', 'yith-woocommerce-product-vendors' ),
					'desc'    => __( 'Enable to set a sandbox environment for testing purposes.', 'yith-woocommerce-product-vendors' ),
					'default' => 'yes',
				),
				'paypal_api_username'         => array(
					'id'    => 'yith_wcmv_paypal_api_username',
					'type'  => 'text',
					'title' => __( 'API Username', 'yith-woocommerce-product-vendors' ),
					// translators: %s is the link to WooCommerce settings page.
					'desc'  => sprintf( __( 'API username of PayPal administration account (if empty, PayPal settings in <a href="%s">WooCommmerce Settings page</a> apply).', 'yith-woocommerce-product-vendors' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_paypal' ) ),
				),
				'paypal_api_password'         => array(
					'id'    => 'yith_wcmv_paypal_api_password',
					'type'  => 'text',
					'title' => __( 'API Password', 'yith-woocommerce-product-vendors' ),
					// translators: %s is the link to WooCommerce settings page.
					'desc'  => sprintf( __( 'API password of PayPal administration account (if empty, PayPal settings in <a href="%s">WooCommmerce Settings page</a> apply).', 'yith-woocommerce-product-vendors' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_paypal' ) ),
				),
				'paypal_api_signature'        => array(
					'id'    => 'yith_wcmv_paypal_api_signature',
					'type'  => 'text',
					'title' => __( 'API Signature', 'yith-woocommerce-product-vendors' ),
					// translators: %s is the link to WooCommerce settings page.
					'desc'  => sprintf( __( 'API signature of PayPal administration account (if empty, PayPal settings in <a href="%s">WooCommmerce Settings page</a> apply).', 'yith-woocommerce-product-vendors' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_paypal' ) ),
				),
				'paypal_payment_mail_subject' => array(
					'id'    => 'yith_wcmv_paypal_payment_mail_subject',
					'type'  => 'text',
					'title' => __( 'Payment Email Subject', 'yith-woocommerce-product-vendors' ),
					'desc'  => __( 'Subject of the email sent by PayPal to customers when a payment request is registered.', 'yith-woocommerce-product-vendors' ),
				),
				'paypal_ipn_notification_url' => array(
					'id'                => 'yith_wcmv_paypal_ipn_notification_url',
					'type'              => 'text',
					'title'             => __( 'Notification URL', 'yith-woocommerce-product-vendors' ),
					'desc'              => __( 'Copy this URL and set it into PayPal admin panel to receive IPN from their server.', 'yith-woocommerce-product-vendors' ),
					'default'           => site_url() . '/?paypal_ipn_response=true',
					'css'               => 'width: 400px;',
					'custom_attributes' => array(
						'readonly' => 'readonly',
					),
				),
			);
		}

		/**
		 * Add gateway options to vendor payments field
		 *
		 * @since  4.0.0
		 * @param array $fields The vendor fields.
		 * @return array
		 */
		public function add_masspay_options( $fields ) {

			if ( 'choose' !== get_option( 'yith_wcmv_paypal_payment_method', 'choose' ) || ! isset( $fields['payment'] ) ) {
				return $fields;
			}

			$min_threshold = $this->get_default_threshold_amount();

			$fields['payment'] = array_merge(
				$fields['payment'],
				array(
					'masspay_title' => array(
						'type'  => 'title',
						'label' => _x( 'PayPal MassPay', '[Admin] Vendor options group title', 'yith-woocommerce-product-vendors' ),
					),
					'payment_type'  => array(
						'type'        => 'select',
						'label'       => _x( 'Payment type', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
						'description' => _x( 'Choose the payment method for crediting commissions.', '[Admin] Vendor option description', 'yith-woocommerce-product-vendors' ),
						'options'     => array(
							'instant'   => __( 'Instant Payment', 'yith-woocommerce-product-vendors' ),
							'threshold' => __( 'Payment threshold', 'yith-woocommerce-product-vendors' ),
						),
					),
					'threshold'     => array(
						'type'              => 'number',
						'label'             => _x( 'Threshold', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ) . ' ' . get_woocommerce_currency_symbol(),
						// translators: %s stand for the minimum threshold allowed by site administrator.
						'description'       => sprintf( _x( 'Minimum vendor\'s earnings before vendor commissions can be paid. Note: the minimum threshold allowed by the site administrator is %s.', '[Admin] Vendor option description', 'yith-woocommerce-product-vendors' ), yith_wcmv_format_price( $min_threshold ) ),
						'default'           => $min_threshold,
						'custom_attributes' => array(
							'min' => $min_threshold,
						),
					),
				)
			);

			return $fields;
		}

		/**
		 * Get default threshold amount
		 *
		 * @since  4.0.0
		 * @return integer
		 */
		protected function get_default_threshold_amount() {
			return get_option( 'yith_wcmv_paypal_payment_minimum_withdrawals', 1 );
		}

		/**
		 * Get the data for pay() method
		 *
		 * @param array $args Array argument to retreive payment data.
		 * @return array
		 */
		public function get_pay_data( $args = array() ) {

			$commission_ids = array();
			if ( ! empty( $args['commission_ids'] ) ) {
				$commission_ids = $args['commission_ids'];
			} elseif ( ! empty( $args['order_id'] ) ) {
				// Create an array of commissions to be processed.
				$order_commission_ids = yith_wcmv_get_commissions(
					array(
						'order_id' => $args['order_id'],
						'status'   => 'all',
					)
				);

				if ( ! empty( $order_commission_ids ) ) {
					foreach ( $order_commission_ids as $commission_id ) {
						$commission = yith_wcmv_get_commission( $commission_id );
						// Save the amount to pay for each commission of vendor.
						if ( $commission ) {
							$vendor = $commission->get_vendor();

							if ( $vendor && $vendor->is_valid() && $vendor->get_meta( 'paypal_email' ) ) {

								if ( 'threshold' === $vendor->get_meta( 'payment_type' ) ) {

									$threshold = $vendor->get_meta( 'threshold' );
									if ( empty( $threshold ) || $threshold < $this->get_default_threshold_amount() ) {
										$threshold = $this->get_default_threshold_amount();
									}

									if ( YITH_Vendors()->commissions->get_unpaid_commissions_amount( $vendor->get_id() ) >= $threshold ) {
										$unpaid_commission_ids = yith_wcmv_get_commissions(
											array(
												'vendor_id' => $vendor->get_id(),
												'status'    => 'unpaid',
											)
										);

										$commission_ids = array_merge( $commission_ids, $unpaid_commission_ids );
									}
								} else {
									$commission_ids[] = $commission->get_id();
								}
							}
						}
					}
				}
			}

			return $this->build_args_to_register_vendor_payments( $commission_ids );
		}

		/**
		 * Pay method, used to process payment requests
		 *
		 * @since  1.0
		 * @param array $payment_data Array of parameters for the single requests.
		 * @return array
		 */
		public function pay( $payment_data ) {

			if ( ! $this->paypal_is_configured() ) {
				return array(
					'status'  => false,
					'message' => _x( 'Missing configuration for PayPal MassPay gateway.', 'Gateway error message', 'yith-woocommerce-product-vendors' ),
				);
			}

			// Collect commission ids where payment fail.
			$commissions_failed = array();

			// Include required libraries.
			require_once dirname( dirname( __FILE__ ) ) . '/third-party/PayPal/PayPal.php';
			// Retrieve saved options from panel.
			$stored_options = $this->get_gateway_options();
			// Create new PayPal instance.
			$paypal = new PayPal(
				array(
					'Sandbox'      => 'no' !== $stored_options['sandbox'],
					'APIUsername'  => $stored_options['api_username'],
					'APIPassword'  => $stored_options['api_password'],
					'APISignature' => $stored_options['api_signature'],
					'PrintHeaders' => true,
					'LogResults'   => false,
				)
			);

			// Prepare request arrays.
			$mpfields = array(
				// The subject line of the email that PayPal sends when the transaction is completed. Same for all recipients. 255 char max.
				'emailsubject' => $stored_options['payment_mail_subject'],
				// Indicates how you identify the recipients of payments in this call to MassPay.  Must be EmailAddress or UserID.
				'receivertype' => 'EmailAddress',
			);

			// Create payment array by currencies.
			$payments_data_currencies = array();
			foreach ( $payment_data as $vendor_id => $vendor_arg ) {

				foreach ( $vendor_arg['amount'] as $currency => $amount ) {
					$vendor = yith_wcmv_get_vendor( $vendor_id, 'vendor' );
					if ( ! $vendor || ! $vendor->is_valid() || empty( $vendor->get_meta( 'paypal_email' ) ) || empty( $amount ) ) {
						continue;
					}

					$payment_id = $this->register_payment(
						array(
							'payment'        => array(
								'vendor_id'        => $vendor_id,
								'user_id'          => $vendor_arg['amount']['user_id'],
								'currency'         => $currency,
								'amount'           => $amount,
								'payment_date'     => $vendor_arg['amount']['payment_date'],
								'payment_date_gmt' => $vendor_arg['amount']['payment_date_gmt'],
							),
							'commission_ids' => $vendor_arg['commission_ids'][ $currency ],
						)
					);

					if ( $payment_id ) {
						foreach ( $vendor_arg['commission_ids'][ $currency ] as $commission_id ) {
							$commission = yith_wcmv_get_commission( $commission_id );
							if ( $commission ) {
								$commission->update_status( 'processing' );
							}
						}

						$payments_data_currencies[ $currency ][] = array(
							'paypal_email' => $vendor->get_meta( 'paypal_email' ),
							'amount'       => round( $amount, 2 ),
							'request_id'   => $payment_id,
							'commissions'  => $vendor_arg['commission_ids'][ $currency ],
						);
					}
				}
			}

			foreach ( $payments_data_currencies as $currency => $payments ) {
				$mpfields['currencycode'] = $currency;
				$mpitems                  = array();
				foreach ( $payments as $payment ) {
					$mpitems[] = array(
						// Required.  Email address of recipient.  You must specify either L_EMAIL or L_RECEIVERID but you must not mix the two.
						'l_email'    => $payment['paypal_email'],
						// Required.  Payment amount.
						'l_amt'      => $payment['amount'],
						// Transaction-specific ID number for tracking in an accounting system.
						'l_uniqueid' => $payment['request_id'],
					);
				}

				$paypalresult = $paypal->MassPay(
					array(
						'MPFields' => $mpfields,
						'MPItems'  => $mpitems,
					)
				);

				$errors = array();
				if ( self::PAYMENT_STATUS_FAIL === $paypalresult['ACK'] ) {
					foreach ( $paypalresult['ERRORS'] as $error ) {
						$errors[] = $error['L_LONGMESSAGE'];
					}
					YITH_Vendors_Logger::log( implode( "\n", $errors ) );

					foreach ( $payments as $payment ) {
						foreach ( $payment['commissions'] as $commission_id ) {
							$commission = yith_wcmv_get_commission( $commission_id );
							if ( ! $commission ) {
								continue;
							}
							// translators: %s is a list of payments error messages.
							$commission->add_note( sprintf( __( 'Payment failed: %s', 'yith-woocommerce-product-vendors' ), implode( "\n", $errors ) ) );
							$commissions_failed[] = $commission_id;
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
		 * Method used to handle notification from PayPal server
		 *
		 * @since  1.0
		 * @return void
		 */
		public function handle_notification() {

			if ( empty( $_REQUEST['paypal_ipn_response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			// Include required libraries.
			require dirname( dirname( __FILE__ ) ) . '/third-party/IPNListener/ipnlistener.php';

			// Retrieve saved options from panel.
			$stored_options = $this->get_gateway_options();

			$listener              = new IpnListener();
			$listener->use_sandbox = 'no' !== $stored_options['sandbox'];

			try {
				// Process IPN request, require validation to PayPal server.
				$listener->requirePostMethod();
				$verified = $listener->processIpn();

			} catch ( Exception $e ) {
				// Fatal error trying to process IPN.
				die();
			}

			// If PayPal says IPN is valid, process content.
			if ( $verified ) {
				$request_data = $_REQUEST; // phpcs:ignore
				if ( ! isset( $request_data['payment_status'] ) ) {
					die();
				}

				// Format payment data.
				$payment_data = array();
				for ( $i = 1; array_key_exists( 'status_' . $i, $request_data ); $i++ ) {
					$data_index = array_keys( $request_data );

					foreach ( $data_index as $index ) {
						if ( strpos( $index, '_' . $i ) !== false ) {
							$payment_data[ $i ][ str_replace( '_' . $i, '', $index ) ] = $request_data[ $index ];
							unset( $request_data[ $index ] );
						}
					}
				}

				if ( ! empty( $payment_data ) ) {
					foreach ( $payment_data as $payment ) {
						if ( ! isset( $payment['unique_id'] ) ) {
							continue;
						}

						$args                   = array();
						$args['unique_id']      = $payment['unique_id'];
						$args['gross']          = $payment['mc_gross'];
						$args['status']         = $payment['status'];
						$args['receiver_email'] = $payment['receiver_email'];
						$args['currency']       = $payment['mc_currency'];
						$args['txn_id']         = $payment['masspay_txn_id'];

						$this->handle_payment_successful( $args );

						// Call action to update request status.
						do_action( 'yith_vendors_gateway_notification', $args );
					}
				}
			}

			die();
		}

		/**
		 * Get the gateway options
		 *
		 * @return array
		 */
		public function get_gateway_options() {

			$api_username  = get_option( 'yith_wcmv_paypal_api_username', '' );
			$api_password  = get_option( 'yith_wcmv_paypal_api_password', '' );
			$api_signature = get_option( 'yith_wcmv_paypal_api_signature', '' );

			// If empty, get from woocommerce settings.
			if ( empty( $api_username ) && empty( $api_password ) && empty( $api_signature ) ) {
				$gateways = WC()->payment_gateways()->get_available_payment_gateways();
				if ( isset( $gateways['paypal'] ) ) {
					$paypal        = $gateways['paypal'];
					$api_username  = $paypal->testmode ? $paypal->get_option( 'sandbox_api_username' ) : $paypal->get_option( 'api_username' );
					$api_password  = $paypal->testmode ? $paypal->get_option( 'sandbox_api_password' ) : $paypal->get_option( 'api_password' );
					$api_signature = $paypal->testmode ? $paypal->get_option( 'sandbox_api_signature' ) : $paypal->get_option( 'api_signature' );
				}
			}

			$args = array(
				'sandbox'              => get_option( 'yith_wcmv_paypal_sandbox', 'yes' ),
				'api_username'         => $api_username,
				'api_password'         => $api_password,
				'api_signature'        => $api_signature,
				'payment_mail_subject' => get_option( 'yith_wcmv_paypal_payment_mail_subject', '' ),
				'ipn_notification_url' => site_url() . '/?paypal_ipn_response=true',
			);

			$args = wp_parse_args( $args, array() );

			return $args;
		}

		/**
		 * Process success payment
		 *
		 * @param array $args Successful payment data.
		 * @return void
		 */
		public function handle_payment_successful( $args ) {
			if ( empty( $args['unique_id'] ) ) {
				return;
			}

			$payment_id     = $args['unique_id'];
			$commission_ids = YITH_Vendors()->payments->get_commissions_by_payment_id( $payment_id );
			$status         = 'Completed' === $args['status'] ? 'paid' : 'failed';

			if ( count( $commission_ids ) > 0 ) {
				foreach ( $commission_ids as $commission_id ) {

					$commission = yith_wcmv_get_commission( absint( $commission_id ) );
					// Perform only if the commission is in progress.
					if ( ! $commission || ! $commission->has_status( 'processing' ) ) {
						continue;
					}

					// if completed, set as paid.
					if ( 'paid' === $status ) {
						// translators:  %1$s stand for the Gateway name, %2$s stand for the transaction ID.
						$commission->update_status( 'paid', sprintf( __( 'Commission paid via %1$s (txn ID: %2$s)', 'yith-woocommerce-product-vendors' ), $this->get_method_title(), $args['txn_id'] ) );
						$this->set_payment_post_meta( $commission );
					} else { // Set unpaid if failed.
						// translators:  %s stand for payment status.
						$commission->update_status( 'unpaid', sprintf( __( 'Payment %s', 'yith-woocommerce-product-vendors' ), $args['status'] ) );
					}
				}

				YITH_Vendors()->payments->update_payment_status( $payment_id, $status );
			}
		}

		/**
		 * Pay the commission to vendor
		 *
		 * @param string|integer $order_id   The order ID.
		 * @param string         $old_status Order status.
		 * @param string         $new_status New order status.
		 * @return void
		 */
		public function process_credit( $order_id, $old_status, $new_status ) {
			if ( 'completed' !== $new_status || 'manual' === get_option( 'yith_wcmv_paypal_payment_method', 'choose' ) ) {
				return;
			}
			// Pay.
			$this->pay( $this->get_pay_data( array( 'order_id' => $order_id ) ) );
		}
	}
}
