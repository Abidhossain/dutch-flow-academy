<?php
/**
 * Compatibility class with Stripe
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_YITH_Stripe_Compatibility' ) ) {
	/**
	 * Deposit - Stripe compatibility
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_YITH_Stripe_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_YITH_Stripe_Compatibility
		 * @since 2.0.0
		 */
		protected static $instance;

		/**
		 * Landing uri for Stripe plugin.
		 *
		 * @var string
		 */
		protected static $landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-stripe/';

		/**
		 * Constructor method
		 */
		public function __construct() {
			// add stripe-dedicated options.
			add_filter( 'yith_wcdp_balances_settings', array( $this, 'add_options' ) );

			if ( $this->is_enabled() ) {
				// frontend changes.
				add_filter( 'yith_wcdp_expiration_notice', array( $this, 'filter_deposit_expiration_notice' ) );
				add_action( 'woocommerce_credit_card_form_end', array( $this, 'add_automatic_payment_notice' ), 10, 1 );

				// pay for expired balances, when possible.
				add_filter( 'yith_wcdp_should_cancel_balance_after_expiration', array( $this, 'pay_for_balance' ), 10, 2 );

				// dedicated emails.
				add_filter( 'woocommerce_email_classes', array( $this, 'register_email_classes' ), 5 );
			}
		}

		/* === GETTERS === */

		/**
		 * Checks if integration is active (YITH WooCommerce Stripe must be active)
		 *
		 * @return bool Whether integration is active
		 */
		public function is_active() {
			return defined( 'YITH_WCSTRIPE' ) && apply_filters( 'yith_wcdp_enable_stripe_compatibility', true );
		}

		/**
		 * Checks whether integration is enabled (integration is active and dedicated option is ON)
		 *
		 * @return bool Returns true when integration is enabled
		 */
		public function is_enabled() {
			return $this->is_active() && yith_plugin_fw_is_true( get_option( 'yith_wcdp_enable_stripe_payment', 'no' ) );
		}

		/**
		 * Checks whether current customer has a valid card registered for payments
		 *
		 * @param int|bool $customer_id Customer id; false to use current user.
		 * @return bool Whether customer has valid card.
		 */
		public function customer_has_valid_card( $customer_id = false ) {
			return ! ! $this->get_customer_valid_card( $customer_id );
		}

		/**
		 * Retrieves card that should be used to automatically pay balances for current customer
		 *
		 * @param int|bool $customer_id Customer id; false to use current user.
		 * @return WC_Payment_Token|bool Payment token, or false if none found.
		 */
		public function get_customer_valid_card( $customer_id = false ) {
			if ( ! $customer_id && is_user_logged_in() ) {
				$customer_id = get_current_user_id();
			}

			if ( ! $customer_id ) {
				return false;
			}

			$gateway = YITH_WCStripe()->get_gateway();

			if ( ! $gateway || ! $gateway->is_available() ) {
				return false;
			}

			// if default token is Stripe's one, return that.
			$default_token = WC_Payment_Tokens::get_customer_default_token( $customer_id );

			if ( $default_token && $gateway->id === $default_token->get_gateway_id() ) {
				return $default_token;
			}

			$customer_tokens = WC_Payment_Tokens::get_customer_tokens( $customer_id, $gateway->id );

			if ( empty( $customer_tokens ) ) {
				return false;
			}

			// if no stripe token is default, return last one (last entered).
			return array_pop( $customer_tokens );
		}

		/**
		 * Checks whether a specific balance matches all requirements to be automatically paid with Stripe
		 *
		 * @param int $balance_id Balance id.
		 * @return bool Whether balance order can be paid with Stripe.
		 */
		public function can_balance_be_paid( $balance_id ) {
			$balance = wc_get_order( $balance_id );

			if ( ! $this->is_enabled() || ! $balance ) {
				return false;
			}

			$customer_id = $balance->get_customer_id();
			$gateway     = YITH_WCStripe()->get_gateway();

			if ( ! $gateway || ! $customer_id || ! $this->customer_has_valid_card( $customer_id ) ) {
				return false;
			}

			$deposit_id = YITH_WCDP_Suborders()->get_parent_order( $balance_id );
			$deposit    = wc_get_order( $deposit_id );

			if ( ! $deposit ) {
				return false;
			}

			$deposit_payment_method_policy = get_option( 'yith_wcdp_deposit_payment_method_for_stripe', 'stripe' );

			if ( 'stripe' === $deposit_payment_method_policy && $gateway->id !== $deposit->get_payment_method() ) {
				return false;
			}

			return true;
		}

		/* === ADMIN METHODS === */

		/**
		 * Add options to balance tab
		 *
		 * @param array $options Array of available options.
		 * @return array Filtered array of options.
		 */
		public function add_options( $options ) {
			$settings = $options['balances'];

			$additional_options = array_merge(
				array(
					'stripe-options'        => array(
						'title' => _x( 'Stripe Options', '[ADMIN] Affiliate dashboard settings page', 'yith-woocommerce-deposits-and-down-payments' ),
						// translators: 1. Stripe landing uri. 2. YITH WooCommerce Stripe (plugin name shouldn't be translated).
						'desc'  => $this->is_active() ? '' : sprintf( __( 'To enable this option you need to install our <a href="%1$s" target="_blank">%2$2s</a> plugin.', 'yith-auctions-for-woocommerce' ), self::$landing, 'YITH WooCommerce Stripe Premium' ),
						'type'  => 'title',
						'id'    => 'yith_wcdp_stripe',
					),
					'enable-stripe-payment' => array(
						'title'           => __( 'Automatically charge balance on customer\'s credit card', 'yith-woocommerce-deposits-and-down-payments' ),
						'type'            => 'yith-field',
						'yith-type'       => 'onoff',
						'desc'            => __( 'Enable to automatically charge the balance amount on your customer\'s credit card.', 'yith-woocommerce-deposits-and-down-payments' ),
						'id'              => 'yith_wcdp_enable_stripe_payment',
						'extra_row_class' => $this->is_active() ? '' : 'yith-disabled',
						'default'         => 'no',
					),
				),
				$this->is_active() ? array(
					'deposit-payment-method-for-stripe' => array(
						'title'     => __( 'Charge balance on credit card', 'yith-woocommerce-deposits-and-down-payments' ),
						'type'      => 'yith-field',
						'yith-type' => 'radio',
						'desc'      => __( 'Choose how to manage the automatic charge of the balance, either charge it only to customers who paid deposits with a credit card, or to all customers, no matter which deposit payment method they used.', 'yith-woocommerce-deposits-and-down-payments' ),
						'default'   => 'stripe',
						'options'   => array(
							'stripe' => __( 'Only if the customers paid the deposit with a credit card', 'yith-woocommerce-deposits-and-down-payments' ),
							'any'    => __( 'Forced for all customers, regardless of deposit\'s payment method', 'yith-woocommerce-deposits-and-down-payments' ),
						),
						'id'        => 'yith_wcdp_deposit_payment_method_for_stripe',
						'deps'      => array(
							'id'    => 'yith_wcdp_enable_stripe_payment',
							'value' => 'yes',
						),
					),
					'stripe-payment-note'               => array(
						'title'         => __( 'Notice of automatic charge to show in "Payment Method" section and at checkout', 'yith-woocommerce-deposits-and-down-payments' ),
						'type'          => 'yith-field',
						'yith-type'     => 'textarea-editor',
						'desc'          => __( 'Enter a text to alert your customers that their credit cards will be used to automatically pay for the balances on the due date.', 'yith-woocommerce-deposits-and-down-payments' ),
						'default'       => esc_html__( 'By adding a credit card, you authorize us to automatically charge it for the costs of the balances created for your account.', 'yith-auctions-for-woocommerce' ),
						'id'            => 'yith_wcdp_stripe_payment_note',
						'textarea_rows' => 5,
						'deps'          => array(
							'id'    => 'yith_wcdp_enable_stripe_payment',
							'value' => 'yes',
						),
					),
				) : array(),
				array(
					'stripe-options-end' => array(
						'type' => 'sectionend',
						'id'   => 'yith_wcdp_stripe',
						'desc' => '',
					),
				)
			);

			$settings = array_merge(
				$settings,
				$additional_options
			);

			$options['balances'] = $settings;

			return $options;
		}

		/* === FRONTEND CHANGES === */

		/**
		 * Filter deposit expiration notice
		 *
		 * @param string $notice Notice to filter.
		 * @retur string Filtered notice.
		 */
		public function filter_deposit_expiration_notice( $notice ) {
			if ( ! $this->is_enabled() || ! $this->customer_has_valid_card() ) {
				return $notice;
			}

			// translators: 1. Balance due date.
			return __( 'Balance will be automatically charged on %s', 'yith-woocommerce-deposits-and-down-payments' );
		}

		/**
		 * Add a notice on Checkout and on My Account pages, to specifcy that newly entered CC will be used to pay for balances automatically.
		 *
		 * @param string $gateway_id Gateway id.
		 *
		 * @return void;
		 */
		public function add_automatic_payment_notice( $gateway_id ) {
			$gateway = YITH_WCStripe()->get_gateway();

			if ( ! $gateway || ! $this->is_enabled() || $gateway_id !== $gateway->id ) {
				return;
			}

			$notice = get_option( 'yith_wcdp_stripe_payment_note' );

			if ( apply_filters( 'yith_wcdp_show_automatic_payment_notice', empty( $notice ), $notice ) ) {
				return;
			}

			$message = '<p id="yith_wcdp_automatic_payment_notice">' . $notice . '</p>';

			echo wp_kses_post( $message );
		}

		/* ==== EMAILS === */

		/**
		 * Register custom emails for the integration
		 *
		 * @param array $classes Array of available classes.
		 * @return array Array of filtered email classes.
		 */
		public function register_email_classes( $classes ) {
			include YITH_WCDP_INC . 'compatibilities/stripe/emails/class-yith-wcdp-customer-deposit-automatic-payment-email.php';

			$classes['YITH_WCDP_Customer_Deposit_Automatic_Payment_Email'] = new YITH_WCDP_Customer_Deposit_Automatic_Payment_Email();

			return $classes;
		}

		/* === CRON METHODS === */

		/**
		 * Tries to pay for expired balances; if this doesn't work, default fallback will apply
		 *
		 * @param bool     $should_cancel Whether balance order should be cancelled.
		 * @param WC_Order $order         Order object.
		 *
		 * @return bool Whether balance order should be cancelled.
		 */
		public function pay_for_balance( $should_cancel, $order ) {
			if ( ! $this->is_enabled() || ! $order ) {
				return $should_cancel;
			}

			$user_id = $order->get_customer_id();
			$gateway = YITH_WCStripe()->get_gateway();

			if ( ! $user_id || ! $gateway ) {
				return $should_cancel;
			}

			$customer_id         = $gateway->get_customer_id( $user_id );
			$customer_valid_card = $this->get_customer_valid_card( $user_id );

			if ( ! $customer_id || ! $customer_valid_card ) {
				return $should_cancel;
			}

			// translators: 1. Blog name. 2. Order number.
			$description = apply_filters( 'yith_wcstripe_charge_description', sprintf( __( '%1$s - Order %2$s', 'yith-woocommerce-deposits-and-down-payments' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() );
			$currency    = $order->get_currency();
			$metadata    = array(
				'order_id'    => $order->get_id(),
				'order_email' => $order->get_billing_email(),
				'cart_hash'   => '',
			);

			$args = apply_filters(
				'yith_wcstripe_create_payment_intent',
				array_merge(
					array(
						'amount'              => YITH_WCStripe::get_amount( $order->get_total(), $currency, $order ),
						'currency'            => $currency,
						'description'         => $description,
						'metadata'            => apply_filters(
							'yith_wcstripe_metadata',
							array_merge(
								array(
									'instance' => $gateway->instance,
								),
								$metadata
							),
							'create_payment_intent'
						),
						'payment_method'      => $customer_valid_card->get_token(),
						'setup_future_usage'  => 'off_session',
						'capture_method'      => $gateway->capture ? 'automatic' : 'manual',
						'confirmation_method' => 'manual',
					),
					$customer_id ? array(
						'customer' => $customer_id,
					) : array()
				)
			);

			// init stripe api.
			$gateway->init_stripe_sdk();

			try {
				$intent = $gateway->api->create_intent( $args );
				$intent->confirm(
					array(
						'return_url' => $gateway->get_verification_url( $order ),
					)
				);
			} catch ( Exception $e ) {
				// translators: 1. Error code. 2. Error message.
				$order->add_order_note( sprintf( __( 'Stripe Error: %s', 'yith-woocommerce-stripe' ), $e->getMessage() ) );

				return $should_cancel;
			}

			if ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ), true ) ) {
				return $should_cancel;
			}

			// register intent for the order.
			$order->update_meta_data( 'intent_id', $intent->id );

			// retrieve charge to use for next steps.
			$charge = $intent->latest_charge;
			$charge = is_object( $charge ) ? $charge : $gateway->api->get_charge( $charge );

			// payment complete.
			$order->payment_complete( $charge->id );

			// add order note.
			// translators: 1. Stripe charge id.
			$order->add_order_note( sprintf( __( 'Stripe payment approved (ID: %s)', 'yith-woocommerce-deposits-and-down-payments' ), $charge->id ) );

			// update order meta.
			$order->update_meta_data( '_captured', $charge->captured ? 'yes' : 'no' );
			$order->update_meta_data( '_stripe_customer_id', $customer_id );
			$order->save();

			do_action( 'yith_wcdp_after_stripe_automatic_charge', $order );

			// if everything went fine, return false to skip order cancellation.
			return false;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_YITH_Stripe_Compatibility
		 * @since 1.0.5
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
