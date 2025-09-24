<?php
/**
 * New deposit created email
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Customer_Deposit_Created_Email' ) ) {
	/**
	 * New deposit created email email
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Customer_Deposit_Created_Email extends YITH_WCDP_Email {

		/**
		 * Email method ID.
		 *
		 * @var String
		 */
		public $id = 'new_deposit';

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->title       = __( 'YITH WooCommerce Deposits / Down Payments - New deposit for customer', 'yith-woocommerce-deposits-and-down-payments' );
			$this->description = __( 'This email is sent to customers when they create an order with one or more deposits.', 'yith-woocommerce-deposits-and-down-payments' );

			$this->heading = __( 'Pay your order\'s balance now', 'yith-woocommerce-deposits-and-down-payments' );
			$this->subject = __( 'Pay your order\'s balance now', 'yith-woocommerce-deposits-and-down-payments' );

			$this->content_html = $this->get_option(
				'content_html',
				__(
					'<p>Hi {customer_name},</p>
					<p>Your order #{order_number} is currently being processed, and its actual status is {order_status}.</p>
					<p>You can now pay the balances of the following product(s):</p>
					{deposit_list}
					<p>Best regards,</p>
					<p>{site_title} staff</p>',
					'yith-woocommerce-deposits-and-down-payments'
				)
			);
			$this->content_text = $this->get_option(
				'content_html',
				__(
					"Hi {customer_name},\n
					Your order #{order_number} is currently being processed, and its actual status is {order_status}.\n
					You can now pay the balances of the following product(s):\n
					{deposit_list_plain}\n
					Best regards,
					{site_title} staff"
				)
			);

			$this->template_html  = 'emails/customer-deposit-created-email.php';
			$this->template_plain = 'emails/plain/customer-deposit-created-email.php';

			$this->customer_email = true;

			// Triggers for this email.
			add_action( 'woocommerce_order_status_completed_notification', array( $this, 'trigger' ), 10, 1 );
			add_action( 'woocommerce_order_status_processing_notification', array( $this, 'trigger' ), 10, 1 );
			add_action( 'yith_wcdp_deposits_created_notification', array( $this, 'trigger' ), 10, 1 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Method triggered to send email
		 *
		 * @param int $order_id Order id.
		 *
		 * @return void
		 */
		public function trigger( $order_id ) {
			$this->object    = wc_get_order( $order_id );
			$this->recipient = $this->object->get_billing_email();
			$this->customer  = $this->object->get_user();
			$this->suborders = YITH_WCDP_Suborders()->get_suborders( $order_id );

			if ( ! $this->is_enabled() || ! $this->get_recipient() || ! $this->suborders ) {
				return;
			}

			$this->set_replaces();

			$this->placeholders = array_merge(
				$this->placeholders,
				array(
					'{content_html}' => $this->format_string( $this->content_html ),
					'{content_text}' => $this->format_string( $this->content_text ),
				)
			);

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * Check if mail is enabled
		 *
		 * @return bool Whether email notification is enabled or not
		 * @since 1.0.0
		 */
		public function is_enabled() {
			$notify_customer = get_option( 'woocommerce_new_deposit_settings' );

			/**
			 * APPLY_FILTERS: yith_wcdp_customer_deposit_created_email_enabled
			 *
			 * Filters if customer deposit created email is enabled.
			 *
			 * @param bool Default value.
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_wcdp_customer_deposit_created_email_enabled', isset( $notify_customer['enabled'] ) && 'yes' === $notify_customer['enabled'] );
		}

		/**
		 * Init form fields to display in WC admin pages
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'subject'      => array(
					'title'                => __( 'Subject', 'yith-woocommerce-deposits-and-down-payments' ),
					'type'                 => 'yith_wcdp_field',
					'yith_wcdp_field_type' => 'text',
					// translators: 1. Default subject.
					'description'          => sprintf( __( 'This controls the email subject line. Leave it blank to use the default subject: <code>%s</code>.', 'yith-woocommerce-deposits-and-down-payments' ), $this->subject ),
					'placeholder'          => '',
					'default'              => '',
				),
				'heading'      => array(
					'title'                => __( 'Email heading', 'yith-woocommerce-deposits-and-down-payments' ),
					'type'                 => 'yith_wcdp_field',
					'yith_wcdp_field_type' => 'text',
					// translators: 1. Default heading.
					'description'          => sprintf( __( 'This controls the main heading in the notification email. Leave it blank to use the default heading: <code>%s</code>.', 'yith-woocommerce-deposits-and-down-payments' ), $this->heading ),
					'placeholder'          => '',
					'default'              => '',
				),
				'email_type'   => array(
					'title'                => __( 'Email type', 'yith-woocommerce-deposits-and-down-payments' ),
					'type'                 => 'yith_wcdp_field',
					'yith_wcdp_field_type' => 'select',
					'description'          => __( 'Choose the email format.', 'yith-woocommerce-deposits-and-down-payments' ),
					'default'              => 'html',
					'class'                => 'email_type wc-enhanced-select',
					'options'              => $this->get_email_type_options(),
				),

				'content_html' => array(
					'title'                => __( 'Email Content', 'yith-woocommerce-deposits-and-down-payments' ),
					'type'                 => 'yith_wcdp_field',
					'yith_wcdp_field_type' => 'textarea-editor',
					'wpautop'              => true,
					'description'          => __( 'This field lets you modify the main content of the email. You can use the following placeholders: <code>{order_id}</code> <code>{order_date}</code> <code>{order_state}</code> <code>{customer_name}</code> <code>{customer_login}</code> <code>{customer_email}</code> <code>{suborder_list}</code> <code>{suborder_table}</code>', 'yith-woocommerce-deposits-and-down-payments' ),
					'default'              => __(
						'<p>Hi {customer_name},</p>
						<p>Your order #{order_number} is currently being processed, and its actual status is {order_status}.</p>
						<p>You can now pay the balances of the following product(s):</p>
						{deposit_list}',
						'yith-woocommerce-deposits-and-down-payments'
					),
				),
			);
		}
	}
}
