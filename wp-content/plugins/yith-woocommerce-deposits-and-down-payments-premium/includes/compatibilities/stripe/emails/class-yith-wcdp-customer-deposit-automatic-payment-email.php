<?php
/**
 * Deposit automatic payment reminder email
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Customer_Deposit_Automatic_Payment_Email' ) ) {
	/**
	 * New deposit created email email
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Customer_Deposit_Automatic_Payment_Email extends YITH_WCDP_Customer_Deposit_Expiring_Email {

		/**
		 * Email method ID.
		 *
		 * @var String
		 */
		public $id = 'automatic_deposit_payment';

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			parent::__construct();

			// reset options to use new ID.
			$this->settings = array();

			// init subclass specific properties.
			$this->title        = __( 'YITH WooCommerce Deposits / Down Payments - Balance automatic payment', 'yith-woocommerce-deposits-and-down-payments' );
			$this->description  = __( 'This email is sent to customers when the balance automatic payment date is approaching.', 'yith-woocommerce-deposits-and-down-payments' );
			$this->heading      = __( 'The pending balance will be charged to your credit card soon', 'yith-woocommerce-deposits-and-down-payments' );
			$this->subject      = __( 'The balance will be automatically paid soon', 'yith-woocommerce-deposits-and-down-payments' );
			$this->content_html = $this->get_option( 'content_html', $this->get_default_content() );
			$this->content_text = $this->get_option( 'content_text', $this->get_default_content( true ) );

			$this->template_html  = 'emails/customer-deposit-automatic-payment-email.php';
			$this->template_plain = 'emails/plain/customer-deposit-automatic-payment-email.php';

			$this->customer_email = true;

			// Init settings.
			$this->init_form_fields();
			$this->init_settings();
		}

		/**
		 * Method triggered to send email
		 *
		 * @param int  $order_id    Deposit id.
		 * @param int  $suborder_id Balance order id.
		 * @param bool $force       Whether mail should be sent even if notification was already sent.
		 */
		public function trigger( $order_id, $suborder_id = false, $force = false ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return;
			}

			$instance    = YITH_WCDP_YITH_Stripe_Compatibility::get_instance();
			$customer_id = $order->get_customer_id();

			if ( ! $instance || ! $customer_id || ! $instance->customer_has_valid_card( $customer_id ) || ( $suborder_id && ! $instance->can_balance_be_paid( $suborder_id ) ) ) {
				return;
			}

			if ( $suborder_id && $instance->can_balance_be_paid( $suborder_id ) ) {
				$this->disable_default_expiration_email();
			}

			$valid_card = $instance->get_customer_valid_card( $customer_id );

			$placeholders = array(
				'{card_type}'         => strtoupper( $valid_card->get_meta( 'card_type' ) ),
				'{card_last4}'        => $valid_card->get_meta( 'last4' ),
				'{card_expiry_year}'  => $valid_card->get_meta( 'expiry_year' ),
				'{card_expiry_month}' => $valid_card->get_meta( 'expiry_month' ),
			);

			$this->placeholders = array_merge(
				$this->placeholders,
				$placeholders,
			);

			parent::trigger( $order_id, $suborder_id, $force );
		}

		/**
		 * Returns deposit list template (plain/html)
		 *
		 * @param bool  $plain Whether to use plain template or HTML one.
		 * @param array $args  Additional arguments for the template.
		 * @return string Deposit list template
		 * @since 1.0.0
		 */
		public function get_deposit_list( $plain = false, $args = array() ) {
			$args = array_merge(
				array(
					'hide_pay_button' => true,
				),
				$args
			);

			return parent::get_deposit_list( $plain, $args );
		}

		/**
		 * Disable default expiration notification, that will be replaced by this email
		 *
		 * @return void
		 */
		protected function disable_default_expiration_email() {
			add_filter( 'yith_wcdp_is_expiring_deposit_email_enable', '__return_false' );

			add_action(
				'yith_wcdp_deposits_expiring_notification',
				function () {
					remove_filter( 'yith_wcdp_is_expiring_deposit_email_enable', '__return_false' );
				},
				20
			);
		}

		/**
		 * Returns default content for this email
		 *
		 * @param bool $plain Whether method should return plain content or HTML one.
		 *
		 * @return string Default content.
		 */
		protected function get_default_content( $plain = false ) {
			$default = __(
				'<p>Hi {customer_name},</p>
					<p>We remind you that the pending balances listed below will be automatically charged to your credit card (<b>{card_type}</b> ending in <b>{card_last4}</b>) in {days_left}.</p>
					{deposit_list}
					<p>Please, be sure to double-check the saved payment method, to correctly complete the payment.</p>
					<p>Best regards,</p>
					<p>{site_title} staff</p>',
				'yith-woocommerce-deposits-and-down-payments'
			);

			if ( $plain ) {
				$default = __(
					"Hi {customer_name},\n
					We remind you that the pending balances listed below will be automatically charged to your credit card ({card_type} ending in {card_last4}) in {days_left}.\n
					{deposit_list}\n
					Please, be sure to double-check the saved payment method, to correctly complete your balance payments.\n
					Best regards,\n
					{site_title} staff",
					'yith-woocommerce-deposits-and-down-payments'
				);
			}

			return $default;
		}
	}
}
