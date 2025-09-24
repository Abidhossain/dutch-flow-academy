<?php
/**
 * YITH_Vendors_Gateway
 * Define methods and properties for class that manages admin payments
 *
 * @class      YITH_Vendors_Gateway
 * @since      Version 4.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Gateway' ) ) {

	abstract class YITH_Vendors_Gateway extends YITH_Vendors_Gateway_Legacy {

		/**
		 * Check if this gateway is available on checkout or not.
		 *
		 * @var boolean
		 */
		protected $is_available_on_checkout = false;

		/**
		 * Check if this gateway is available now.
		 *
		 * @var boolean
		 */
		protected $is_coming_soon = false;

		/**
		 * Check if this gateway required an external plugin to works.
		 *
		 * @var boolean
		 */
		protected $is_external = false;

		/**
		 * Args for external gateways.
		 *
		 * @var array
		 */
		protected $external_args = array();

		/**
		 * The gateway slug.
		 *
		 * @var string
		 */
		protected $id = 'gateway-id';

		/**
		 * The gateway name.
		 *
		 * @var string
		 */
		protected $method_title = 'Gateway';

		/**
		 * The gateway options array.
		 *
		 * @var array
		 */
		protected $options = array();

		/**
		 * Name of the class of the actual used gateway
		 *
		 * @var string Gateway class name.
		 */
		public $gateway;

		/**
		 * Constructor Method
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function __construct() {
			$this->gateway = $this->get_id(); // Backward compatibility.
			$this->register_gateway_options();
			$this->init();
		}

		/**
		 * Init class hooks and filters
		 *
		 * @return void
		 * @since  4.0.0
		 */
		protected function init() {
			if ( $this->get_is_external() ) {
				add_filter(
					"yith_wcmv_displayed_{$this->get_id()}_id",
					array(
						$this,
						'add_is_external_required_message',
					)
				);
			}

			if ( ! $this->is_enabled() ) {
				return;
			}

			// Commissions table action.
			if ( current_user_can( 'manage_woocommerce' ) && apply_filters( "yith_wcmv_add_{$this->get_id()}_table_action", true ) ) {
				add_action( 'yith_wcmv_single_commission_row_actions', array( $this, 'add_single_action' ), 10, 2 );
				add_filter( 'yith_wcmv_commissions_list_table_bulk_actions', array( $this, 'add_bulk_action' ), 10, 1 );
				// Pay commission(s) in admin area.
				add_action(
					"yith_wcmv_commissions_table_action_pay_commission_{$this->get_id()}",
					array(
						$this,
						'handle_commissions_payment',
					),
					10,
					2
				);
			}
		}

		/**
		 * Register gateway options array
		 *
		 * @return void
		 * @since  4.0.0
		 */
		protected function register_gateway_options() {
		}

		/**
		 * Get the gateway options
		 *
		 * @return array
		 */
		public function get_gateway_options() {
			return array();
		}

		/**
		 * Get the data for pay() method
		 *
		 * @param array $args Array argument to retrieve payment data.
		 *
		 * @return array
		 * @since  1.0.0
		 */
		protected function get_pay_data( $args = array() ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$commission_ids = array();
			$extra_args     = array();

			// Leave for backward compatibility.
			if ( ! empty( $args['commission_ids'] ) ) {
				$commission_ids = $args['commission_ids'];
			} elseif ( ! empty( $args['commission_id'] ) ) {
				$commission_ids = $args['commission_id'];
			}

			$pay_data = $this->build_args_to_register_vendor_payments( $commission_ids );

			if ( ! empty( $args['extra_args'] ) ) {
				$extra_args = $args['extra_args'];
			}

			$vendor_ids = array_keys( $pay_data );
			foreach ( $vendor_ids as $vendor_id ) {
				$pay_data[ $vendor_id ] = array_merge( $pay_data[ $vendor_id ], $extra_args );
			}

			return apply_filters( "yith_wcmv_get_pay_data_args_for_{$this->get_id()}", $pay_data );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Check if the current gateway is enabled or not
		 *
		 * @return boolean true if enabled, false otherwise.
		 * @since  1.0.0
		 */
		public function is_enabled() {
			$gateway_slug = $this->get_id();
			$enabled      = 'yes' === get_option( "yith_wcmv_enable_{$gateway_slug}_gateway", 'no' );

			if ( $enabled && $this->get_is_external() ) {
				$enabled = $this->is_external_plugin_enabled();
			}

			return $enabled;
		}

		/**
		 * Switch enabled option for gateway
		 *
		 * @param boolean $enabled True if gateway is enabled, false otherwise.
		 *
		 * @return boolean
		 * @since  4.0.0
		 */
		public function switch_enabled( $enabled ) {
			$gateway_slug = $this->get_id();

			return update_option( "yith_wcmv_enable_{$gateway_slug}_gateway", $enabled ? 'yes' : 'no' );
		}

		/**
		 * Get Class Slug
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Get Class Name
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_method_title() {
			return $this->method_title;
		}

		/**
		 * Get gateway options array
		 *
		 * @return array
		 * @since  4.0.0
		 */
		public function get_options() {
			return apply_filters( "yith_wcmv_gateway_{$this->id}_options", $this->options, $this );
		}

		/**
		 * Get is_coming_soon attribute
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_is_coming_soon() {
			return $this->is_coming_soon;
		}

		/**
		 * Set is_coming_soon attribute
		 *
		 * @param boolean $is_coming_soon Is coming soon flag value.
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function set_is_coming_soon( $is_coming_soon ) {
			$this->is_coming_soon = $is_coming_soon;
		}

		/**
		 * Get is_external attribute
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_is_external() {
			return $this->is_external;
		}

		/**
		 * Set is_external attribute
		 *
		 * @param boolean $is_extenal is external flag value.
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function set_is_external( $is_extenal ) {
			$this->is_external = $is_extenal;
		}

		/**
		 * Set is_external attribute
		 *
		 * @return array
		 * @since  1.0.0
		 */
		public function get_external_args() {
			return $this->external_args;
		}

		/**
		 * Get is_available_on_checkout attribute
		 *
		 * @return boolean
		 * @since  1.0.0
		 */
		public function get_is_available_on_checkout() {
			return $this->is_available_on_checkout;
		}

		/**
		 * Set is_available_on_checkout attribute
		 *
		 * @param boolean $available Is available on checkout flag value.
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function set_is_available_on_checkout( $available ) {
			$this->is_available_on_checkout = $available;
		}

		/**
		 * Set is_external attribute
		 *
		 * @param array $args An array of arguments.
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function set_external_args( $args ) {
			$this->external_args = $args;
		}

		/**
		 * Check for external plugin
		 *
		 * @return bool True if the external plugin is required, false otherwise.
		 * @since  1.0.0
		 */
		public function is_external_plugin_enabled() {
			$external_args = $this->get_external_args();
			extract( $external_args ); // phpcs:ignore

			$check = $check_method( $check_for );

			if ( isset( $min_version ) && isset( $plugin_version ) ) {
				$is_enabled          = $check;
				$is_required_version = 1 === version_compare( $plugin_version, $min_version );
				$check               = $is_required_version && $is_enabled;
			}

			return $check;
		}

		/**
		 * Check if external plugin is configured.
		 *
		 * @return boolean
		 * @since  4.0.0
		 */
		public function is_external_plugin_configured() {
			return $this->is_external_plugin_enabled();
		}

		/**
		 * Add external plugin required message
		 *
		 * @param string $gateway_id The gateway ID.
		 *
		 * @return bool True if the external plugin is required, false otherwise.
		 * @since  1.0.0
		 */
		public function add_is_external_required_message( $gateway_id ) {
			if ( ! $this->is_external_plugin_enabled() ) {
				$min_version_message = '';
				$external_args       = $this->get_external_args();
				extract( $external_args ); // phpcs:ignore

				if ( isset( $min_version ) ) {
					// translators: %s stand for the required plugin version.
					$min_version_message = sprintf( _x( '(version %s or greater)', '[Admin] Required version x.x.x or greater', 'yith-woocommerce-product-vendors' ), $min_version );
				}

				$gateway_id = sprintf( '<a href="%s" class="yith-wcmv-gateway-required-external" target="_blank">%s %s %s</a>', $plugin_url, _x( 'Required', '[Admin]: Part of Required xxx plugin', 'yith-woocommerce-product-vendors' ), $plugin_name, $min_version_message );
			}

			return $gateway_id;
		}

		/**
		 * Change admin url
		 *
		 * @param string $admin_url Admin url.
		 *
		 * @return string Admin option url if plugin is enabled, landing page url otherwise.
		 * @since  1.0.0
		 * @deprecated
		 */
		public function change_admin_url( $admin_url ) {
			if ( ! $this->is_external_plugin_enabled() ) {
				$external_args = $this->get_external_args();
				extract( $external_args ); // phpcs:ignore
				$admin_url = $plugin_url;
			}

			return $admin_url;
		}

		/**
		 * Check if this gateway is enabled for checkout
		 *
		 * @return boolean
		 * @since  1.0.0
		 */
		public function is_enabled_for_checkout() {
			return 'yes' === get_option( 'yith_wcmv_checkout_commissions_payment', 'yes' ) && $this->get_id() === get_option( 'yith_wcmv_checkout_gateway', '' );
		}

		/**
		 * Return the status allowed to pay the commissions
		 *
		 * @return array
		 * @since 4.17.0
		 */
		public function get_order_status_allowed() {
			$status = get_option( 'yith_wcmv_checkout_commissions_order_status', array( 'completed' ) );

			return empty( $status ) || ! is_array( $status ) ? array( 'completed' ) : $status;
		}

		/**
		 * Handle commissions payment action
		 *
		 * @param integer|string|array $commission_ids An array of commission ids or a single commission to process.
		 * @param array                $redirect_args Redirect url arguments.
		 *
		 * @return void
		 * @since  4.0.0
		 */
		public function handle_commissions_payment( $commission_ids, &$redirect_args ) {

			if ( empty( $commission_ids ) ) {
				YITH_Vendors_Admin_Notices::add( __( 'Please, select at least one commission.', 'yith-woocommerce-product-vendors' ), 'error' );
			} else {
				$response = $this->process_commissions_payment( $commission_ids );
				$message  = isset( $response['message'] ) ? $response['message'] : '';
				// Check if a message is set, otherwise use a standard one.
				if ( empty( $message ) ) {
					if ( false === $response['status'] ) {

						if ( ! empty( $response['commissions_failed'] ) ) {
							// translators: %1$s stand for the commission label, %2$s is the list of the commissions id.
							$message = sprintf( _x( 'There was an error processing the payment for %1$s #%2$s. Check the %1$s log for more details.', 'Gateway error message', 'yith-woocommerce-product-vendors' ), _n( 'commission', 'commissions', count( $response['commissions_failed'] ), 'yith-woocommerce-product-vendors' ), implode( ', #', $response['commissions_failed'] ) );
						} else {
							$message = _x( 'There was an error processing the payment.', 'Gateway success message', 'yith-woocommerce-product-vendors' );
						}
					} else {
						$message = _x( 'Payment processed successfully.', 'Gateway success message', 'yith-woocommerce-product-vendors' );
					}
				}

				YITH_Vendors_Admin_Notices::add( $message, $response['status'] ? 'success' : 'error' );
			}
		}

		/**
		 * Process commissions payment for given commissions array
		 *
		 * @param integer|string|array $commission_ids An array of commission ids or a single commission to process.
		 *
		 * @return array Response array [ status, message ].
		 * @throws Exception Error on process commissions payment.
		 * @since  4.0.0
		 */
		public function process_commissions_payment( $commission_ids ) {

			try {
				if ( empty( $commission_ids ) ) {
					throw new Exception( _x( 'No commissions to pay.', 'Gateway payment error', 'yith-woocommerce-product-vendors' ) );
				}

				do_action( 'yith_wcmv_before_process_commission_payment', $commission_ids, $this );

				if ( ! is_array( $commission_ids ) ) {
					$commission_ids = explode( ',', $commission_ids );
				}

				$commission_ids = array_map( 'absint', $commission_ids );
				$data           = $this->get_pay_data( array( 'commission_ids' => $commission_ids ) );
				if ( empty( $data ) ) {
					throw new Exception( _x( 'Empty commissions payment data to process.', 'Gateway payment error', 'yith-woocommerce-product-vendors' ) );
				}
				$response = $this->pay( $data );

				do_action( 'yith_wcmv_after_process_commission_payment', $commission_ids, $this );

				return $response;
			} catch ( Exception $e ) {
				return array(
					'status'  => false,
					'message' => $e->getMessage(),
				);
			}
		}

		/**
		 * Sends payment requests to gateway specific method
		 *
		 * @param mixed $payment_detail Array used to identify payment to execute; it will be passed to gateway method, so can be anything.
		 *
		 * @return  array Single or multiple response array [ status, message ].
		 * @since  1.0.0
		 */
		public function pay( $payment_detail ) {
			return array(
				'status'  => false,
				'message' => '',
			);
		}

		/**
		 * Add commissions single action
		 *
		 * @param array                   $actions Array of single commission actions.
		 * @param YITH_Vendors_Commission $commission Commission object.
		 *
		 * @return array
		 */
		public function add_single_action( $actions, $commission ) {

			if ( ! YITH_Vendors()->commissions->is_status_changing_permitted( 'paid', $commission->get_status() ) ) {
				return $actions;
			}

			$gateway_action = array(
				'name'         => sprintf( '%s %s', _x( 'Pay with', '[Button Label]: Pay with your Account Funds', 'yith-woocommerce-product-vendors' ), $this->get_method_title() ),
				'url'          => YITH_Vendors_Admin_Commissions::get_commission_action_url( $commission->get_id(), "pay_commission_{$this->get_id()}" ),
				'confirm_data' => array(
					'title'   => _x( 'Confirm payment', '[Admin]Commission modal action title', 'yith-woocommerce-product-vendors' ),
					'message' => sprintf(
					// translators: %1$s stand for the commission ID, %2$s stand for the selected payment gateway.
						_x( 'Are you sure you want to pay commission #%1$s using #%2$s?', '[Admin]Commission modal action message', 'yith-woocommerce-product-vendors' ),
						$commission->get_id(),
						$this->get_method_title()
					),
				),
			);

			array_unshift( $actions, $gateway_action );

			return $actions;
		}

		/**
		 * Add commissions table bulk action
		 *
		 * @param array $actions Bulk actions for commissions table.
		 *
		 * @return array
		 */
		public function add_bulk_action( $actions ) {
			$actions = array_merge( array( "pay_commission_{$this->get_id()}" => sprintf( '%s %s', _x( 'Pay with', '[Button Label]: Pay with your Account Funds', 'yith-woocommerce-product-vendors' ), $this->get_method_title() ) ), $actions );

			return $actions;
		}

		/**
		 * This function return the needs args for function register_vendor_payments
		 *
		 * @param array|string $commissions An array of commissions.
		 *
		 * @return array
		 * @since  1.0.0
		 */
		public function build_args_to_register_vendor_payments( $commissions ) {

			if ( ! is_array( $commissions ) ) {
				$commissions = array( $commissions );
			}

			$args = array();

			foreach ( $commissions as $commission_id ) {
				$commission = yith_wcmv_get_commission( $commission_id );
				if ( ! $this->can_commission_being_paid( $commission ) ) {
					continue;
				}

				$vendor    = $commission->get_vendor();
				$vendor_id = $vendor->get_id();
				$currency  = $commission->get_currency();

				if ( ! isset( $args[ $vendor_id ] ) ) {
					$args[ $vendor_id ]['user_id']          = $vendor->get_owner();
					$args[ $vendor_id ]['payment_date']     = current_time( 'mysql' );
					$args[ $vendor_id ]['payment_date_gmt'] = current_time( 'mysql', 1 );
				}

				if ( ! isset( $args[ $vendor_id ]['amount'][ $currency ] ) ) {
					$args[ $vendor_id ]['amount'][ $currency ] = $commission->get_amount_to_pay();
				} else {
					$args[ $vendor_id ]['amount'][ $currency ] += $commission->get_amount_to_pay();
				}

				if ( ! isset( $args[ $vendor_id ]['commission_ids'][ $currency ] ) ) {
					$args[ $vendor_id ]['commission_ids'][ $currency ] = array( $commission_id );
				} else {

					$args[ $vendor_id ]['commission_ids'][ $currency ][] = $commission_id;
				}
			}

			return $args;
		}

		/**
		 * Check if given commission can be processed
		 *
		 * @param boolean|YITH_Vendors_Commission $commission The commission object to check or false.
		 *
		 * @return boolean
		 * @since  4.0.0
		 */
		protected function can_commission_being_paid( $commission ) {
			if ( ! $commission || ! $commission->exists() ) { // Validate given commission.
				return false;
			}

			// Order must exist, Check for orphan commissions.
			if ( ! $this->commission_order_exist( $commission ) ) {
				return false;
			}

			$can = YITH_Vendors()->commissions->is_status_changing_permitted( 'paid', $commission->get_status() );
			// Make sure there are no payments pending or paid registered for commission.
			$payments = YITH_Vendors()->payments->get_payments_details_by_commission_ids(
				$commission->get_id(),
				array(
					'ID',
					'status',
				)
			);
			foreach ( $payments as $payment ) {
				if ( 'paid' === $payment['status'] || 'pending' === $payment['status'] ) {
					$can = false;
					break;
				}
			}

			return apply_filters( 'yith_vendors_can_commission_being_paid', $can, $commission, $this );
		}

		/**
		 * Check if the commission related order exist
		 *
		 * @param YITH_Vendors_Commission $commission The commission to check.
		 *
		 * @return boolean
		 * @since  4.2.1
		 */
		protected function commission_order_exist( $commission ) {
			$order = $commission->get_order();
			if ( empty( $order ) ) {
				// set the commission status to cancelled.
				$commission->update_status( 'cancelled' );

				return false;
			}

			return true;
		}

		/**
		 * Add post meta after payment successful
		 *
		 * @param YITH_Vendors_Commission $commission The commission object.
		 *
		 * @return void
		 * @since  2.6.0
		 */
		public function set_payment_post_meta( $commission ) {
			if ( $commission instanceof YITH_Vendors_Commission ) {

				$order         = $commission->get_order();
				$commission_id = $commission->get_id();

				$order_meta_values = array(
					"_commission_{$commission_id}_paid_by_gateway" => 'yes',
					"_commission_{$commission_id}_paid_by" => $this->get_id(),
				);

				foreach ( $order_meta_values as $meta_key => $meta_value ) {
					$order->add_meta_data( $meta_key, $meta_value, true );
				}

				$order->save_meta_data();
			}
		}

		/**
		 * Register payments in payment table
		 *
		 * @param array $args An array of payment arguments.
		 *
		 * @return boolean|integer
		 * @since  4.0.0
		 */
		protected function register_payment( $args ) {
			// Merge with default value.
			$args['payment'] = array_merge(
				array(
					'status'     => 'processing',
					'gateway_id' => $this->get_id(),
				),
				$args['payment']
			);

			// Create entry in Payments table.
			return YITH_Vendors()->payments->add_payment( $args );
		}

		/**
		 * Update a payment status
		 *
		 * @param integer $payment_id The payment ID to update.
		 * @param string  $status The new payment status to set.
		 * @return boolean
		 * @since  4.0.0
		 */
		protected function update_payment_status( $payment_id, $status ) {
			return YITH_Vendors()->payments->update_payment_status( absint( $payment_id ), $status );
		}
	}
}
