<?php
/**
 * Cancelled order email
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WC_Email_Cancelled_Order' ) ) {
	/**
	 * Cancelled order (to vendor) Email
	 * Cancelled order emails are sent when orders have been marked as cancelled (if they were previously set as pending or on-hold).
	 *
	 * @class      YITH_WC_Email_Cancelled_Order
	 * @extends    WC_Email
	 * @package YITH\MultiVendor
	 * @version    4.0.0
	 */
	class YITH_WC_Email_Cancelled_Order extends WC_Email {

		/**
		 * Order number
		 *
		 * @var string
		 */
		public $order_number = '';

		/**
		 * Current vendor
		 *
		 * @var YITH_Vendor|null
		 */
		public $vendor = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->id          = 'cancelled_order_to_vendor';
			$this->title       = __( 'Canceled order (to vendor)', 'yith-woocommerce-product-vendors' );
			$this->description = __( 'Canceled order emails are sent when orders are marked as canceled.', 'yith-woocommerce-product-vendors' );

			$this->heading = __( 'Canceled order', 'yith-woocommerce-product-vendors' );
			$this->subject = __( '[{site_title}] Canceled order ({order_number})', 'yith-woocommerce-product-vendors' );

			$this->template_base  = YITH_WPV_TEMPLATE_PATH;
			$this->template_html  = 'emails/vendor-cancelled-order.php';
			$this->template_plain = 'emails/plain/vendor-cancelled-order.php';

			// Set placeholders key.
			$this->placeholders = array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Triggers for this email.
			add_action( 'woocommerce_order_status_pending_to_cancelled_notification', array( $this, 'trigger' ) );
			add_action( 'woocommerce_order_status_on-hold_to_cancelled_notification', array( $this, 'trigger' ) );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Trigger function.
		 *
		 * @access public
		 * @param string|integer $order_id The order ID.
		 * @return void
		 */
		public function trigger( $order_id ) {

			if ( ! $this->is_enabled() || empty( $order_id ) ) {
				return;
			}

			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			// Is a parent order?
			if ( ! $order->get_parent_id() ) {
				$suborder_ids = 'woocommerce_order_action_cancelled_order_to_vendor' === current_action() ? YITH_Vendors_Orders::get_suborders( $order_id ) : array();
			} else {
				$suborder_ids = array( $order_id );
			}

			if ( empty( $suborder_ids ) ) {
				return;
			}

			$this->setup_locale();

			foreach ( $suborder_ids as $suborder_id ) {
				$this->object = wc_get_order( $suborder_id );
				$vendor_id    = $this->object ? yith_wcmv_get_vendor_id_for_order( $this->object ) : false;
				$this->vendor = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;

				if ( ! $this->vendor || ! $this->vendor->is_valid() ) {
					continue;
				}

				$this->order_number = yith_wcmv_get_email_order_number( $this->object, 'yes' === $this->get_option( 'show_parent_order_id', 'no' ) );

				// Set placeholder values.
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->order_number;

				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get email headers.
		 *
		 * @return string
		 */
		public function get_headers() {
			$headers = parent::get_headers();

			if ( 'yes' === $this->get_option( 'send_cc_to_admin', 'no' ) ) {
				$admin_name  = get_option( 'woocommerce_email_from_name' );
				$admin_email = get_option( 'woocommerce_email_from_address' );
				if ( $admin_email && $admin_name ) {
					$headers .= "Cc: {$admin_name} <{$admin_email}>";
				}
			}

			return $headers;
		}

		/**
		 * Get email recipients
		 *
		 * @return string
		 */
		public function get_recipient() {
			if ( empty( $this->vendor ) ) {
				return '';
			}

			$vendor_email = $this->vendor->get_meta( 'store_email' );
			if ( empty( $vendor_email ) ) {
				$vendor_owner = get_user_by( 'id', absint( $this->vendor->get_owner() ) );
				$vendor_email = $vendor_owner instanceof WP_User ? $vendor_owner->user_email : false;
			}

			return apply_filters( 'yith_wcmv_email_address_recipients_cancelled_order_vendor_email', $vendor_email, $this->vendor, $this );
		}

		/**
		 * Get the email content in HTML format.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_html() {
			ob_start();
			yith_wcmv_get_template(
				$this->template_html,
				array(
					'order'         => $this->object,
					'order_number'  => $this->order_number,
					'vendor'        => $this->vendor,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				)
			);

			return ob_get_clean();
		}

		/**
		 * Get the email content in plain text format.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_plain() {
			ob_start();
			yith_wcmv_get_template(
				$this->template_plain,
				array(
					'order'         => $this->object,
					'order_number'  => $this->order_number,
					'vendor'        => $this->vendor,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => true,
					'email'         => $this,
				)
			);

			return ob_get_clean();
		}

		/**
		 * Initialise Settings Form Fields
		 *
		 * @access public
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'              => array(
					'title'   => __( 'Enable/Disable', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'yith-woocommerce-product-vendors' ),
					'default' => 'yes',
				),
				'subject'              => array(
					'title'       => __( 'Subject', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s is a list of available placeholder for email.
					'description' => sprintf( __( 'Available placeholders: %s', 'yith-woocommerce-product-vendors' ), '<code>' . implode( '</code><code>', array_keys( $this->placeholders ) ) . '</code>' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'              => array(
					'title'       => __( 'Email heading', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s is a list of available placeholder for email.
					'description' => sprintf( __( 'Available placeholders: %s', 'yith-woocommerce-product-vendors' ), '<code>' . implode( '</code><code>', array_keys( $this->placeholders ) ) . '</code>' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type'           => array(
					'title'       => __( 'Email type', 'yith-woocommerce-product-vendors' ),
					'type'        => 'select',
					'description' => __( 'Choose email format.', 'yith-woocommerce-product-vendors' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
				),
				'show_parent_order_id' => array(
					'title'   => __( 'Show order ID', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Show the parent order ID instead of the vendor suborder ID.', 'yith-woocommerce-product-vendors' ),
					'default' => 'no',
				),
				'send_cc_to_admin'     => array(
					'title'   => __( 'Send a copy of this email to administrator', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Send a copy of this email to the website admin.', 'yith-woocommerce-product-vendors' ),
					'default' => 'no',
				),
			);
		}
	}
}

return new YITH_WC_Email_Cancelled_Order();
