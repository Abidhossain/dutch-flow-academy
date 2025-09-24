<?php
/**
 * Deposit expiring email
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Customer_Deposit_Expiring_Email' ) ) {
	/**
	 * New deposit created email email
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Customer_Deposit_Expiring_Email extends YITH_WCDP_Email {

		/**
		 * Email method ID.
		 *
		 * @var String
		 */
		public $id = 'expiring_deposit';

		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->title        = __( 'YITH WooCommerce Deposits / Down Payments - Balance expiration reminder', 'yith-woocommerce-deposits-and-down-payments' );
			$this->description  = __( 'This email is sent to customers when the balance due date is approaching.', 'yith-woocommerce-deposits-and-down-payments' );
			$this->heading      = __( 'Don\'t forget to pay for your products', 'yith-woocommerce-deposits-and-down-payments' );
			$this->subject      = __( 'Don\'t forget to pay for your products', 'yith-woocommerce-deposits-and-down-payments' );
			$this->content_html = $this->get_option( 'content_html', $this->get_default_content() );
			$this->content_text = $this->get_option( 'content_html', $this->get_default_content( true ) );

			$this->template_html  = 'emails/customer-deposit-expiring-email.php';
			$this->template_plain = 'emails/plain/customer-deposit-expiring-email.php';

			$this->customer_email = true;

			// Triggers for this email.
			add_action( 'yith_wcdp_deposits_expiring_notification', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Method triggered to send email
		 *
		 * @param int  $order_id    Deposit id.
		 * @param int  $suborder_id Balance order id.
		 * @param bool $force       Whether mail should be sent even if notification was already sent.
		 *
		 * @return void
		 */
		public function trigger( $order_id, $suborder_id = false, $force = false ) {
			$this->object      = wc_get_order( $order_id );
			$this->recipient   = $this->object->get_billing_email();
			$this->customer    = $this->object->get_user();
			$this->suborders   = YITH_WCDP_Suborders()->get_suborders( $order_id );
			$this->suborder_id = $suborder_id;

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->set_replaces();

			$deposit_expiration_days = get_option( 'yith_wcdp_deposits_expiration_duration', 30 );

			if ( ! $suborder_id ) {
				$suborders = YITH_WCDP_Suborders()->get_suborders( $order_id );

				if ( ! empty( $suborders ) ) {
					foreach ( $suborders as $sub_id ) {
						$sub = wc_get_order( $sub_id );
						/**
						 * APPLY_FILTERS: yith_wcdp_will_suborder_expire
						 *
						 * Filters if suborder will expire.
						 *
						 * @param bool     $will_expire Default value.
						 * @param WC_Order $suborder    The suborder.
						 *
						 * @return bool
						 */
						if ( apply_filters( 'yith_wcdp_will_suborder_expire', false ) || 'yes' === $sub->get_meta( '_will_suborder_expire' ) ) {
							$this->trigger( $order_id, $sub_id, $force );
						}
					}

					return;
				}
			}

			$suborder = wc_get_order( $suborder_id );

			if ( ! $suborder || ! ( $suborder instanceof WC_Order ) ) {
				return;
			}

			$email_sent = $suborder->get_meta( '_expiring_deposit_notification_sent', true );

			if ( ! $force && 'yes' === $email_sent ) {
				return;
			}

			$expiration_date         = $suborder->get_meta( '_suborder_expiration', true );
			$deposit_expiration_time = strtotime( $expiration_date );

			/**
			 * APPLY_FILTERS: yith_wcdp_has_no_expiration_date
			 *
			 * Filters if deposit has no expiration date.
			 *
			 * @param bool Default value: True.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcdp_has_no_expiration_date', true ) && ! $expiration_date ) {
				$order_date              = $this->object->get_date_completed();
				$deposit_expiration_time = $order_date->getTimestamp() + $deposit_expiration_days * DAY_IN_SECONDS;
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_deposit_lower_than_time
			 *
			 * Filters if deposit expiration is lower than current time.
			 *
			 * @param bool Default value: True.
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcdp_deposit_lower_than_time', true ) && $deposit_expiration_time < time() ) {
				return;
			}

			$days_before_expiration = max( 0, floor( ( $deposit_expiration_time - time() ) / DAY_IN_SECONDS ) );

			$placeholders = array(
				// translators: 1. Days left before suborder expiration.
				'{days_left}'          => sprintf( _n( '%d day', '%d days', $days_before_expiration, 'yith-woocommerce-deposits-and-down-payments' ), $days_before_expiration ),
				'{days_before_expire}' => $days_before_expiration,
				'{expiration_date}'    => date_i18n( wc_date_format(), $deposit_expiration_time ),
				'{content_html}'       => $this->content_html,
				'{content_text}'       => $this->content_text,
			);

			$this->placeholders = array_merge(
				$this->placeholders,
				$placeholders
			);

			$this->placeholders['{content_html}'] = $this->format_string( $this->placeholders['{content_html}'] );
			$this->placeholders['{content_text}'] = $this->format_string( $this->placeholders['{content_text}'] );

			/**
			 * APPLY_FILTERS: yith_wcdp_deposit_expiring_email_recipient
			 *
			 * Filters deposit expiring email recipient.
			 *
			 * @param string $recipient Default recipient.
			 *
			 * @return string
			 */
			$this->send( apply_filters( 'yith_wcdp_deposit_expiring_email_recipient', $this->get_recipient() ), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * Check if mail is enabled
		 *
		 * @return bool Whether email notification is enabled or not
		 * @since 1.0.0
		 */
		public function is_enabled() {
			$notify_customer = get_option( 'woocommerce_expiring_deposit_settings' );

			/**
			 * APPLY_FILTERS: yith_wcdp_is_email_enable
			 *
			 * Filters if mail notification is enabled.
			 *
			 * @param bool   $bool            Default value.
			 * @param string $deposit_expire  Deposit expiration setting in plugin settings.
			 * @param string $notify_customer Notify customer setting in plugin settings.
			 *
			 * @return bool
			 */
			return apply_filters( "yith_wcdp_is_{$this->id}_email_enable", isset( $notify_customer['enabled'] ) && 'yes' === $notify_customer['enabled'], $notify_customer, $this );
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
					'description'          => sprintf( __( 'This controls the main heading contained in the notification email. Leave it blank to use the default heading: <code>%s</code>.', 'yith-woocommerce-deposits-and-down-payments' ), $this->heading ),
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
					'title'                => __( 'Email HTML Content', 'yith-woocommerce-deposits-and-down-payments' ),
					'type'                 => 'yith_wcdp_field',
					'yith_wcdp_field_type' => 'textarea-editor',
					'wpautop'              => true,
					'description'          => __( 'This field lets you modify the main content of the email. You can use the following placeholders: <code>{order_id}</code> <code>{order_date}</code> <code>{order_state}</code> <code>{customer_name}</code> <code>{customer_login}</code> <code>{customer_email}</code> <code>{suborder_list}</code> <code>{suborder_table}</code> <code>{expiration_date}</code> <code>{days_before_expiration}</code>', 'yith-woocommerce-deposits-and-down-payments' ),
					'default'              => $this->get_default_content(),
				),
			);
		}

		/**
		 * Returns default content for this email
		 *
		 * @param bool $plain Whether method should return plain content or HTML one.
		 * @return string Default content.
		 */
		protected function get_default_content( $plain = false ) {
			$default = __(
				'<p>Hi {customer_name},</p>
					<p>We noticed that you still have balance payments that have to be paid in our shop.</p>
					<p>Hurry up, you only have <strong>{days_left}</strong> left!</p>
					{deposit_list}
					<p>Best regards,</p>
					<p>{site_title} staff</p>',
				'yith-woocommerce-deposits-and-down-payments'
			);

			if ( $plain ) {
				$default = __(
					"Hi {customer_name},\n
					We noticed that you still have balance payments that have to be paid in our shop.\n
					Hurry up, you only have {days_left} left!\n\n
					{deposit_list}\n
					Best regards,\n
					{site_title} staff",
					'yith-woocommerce-deposits-and-down-payments'
				);
			}

			return $default;
		}
	}
}
