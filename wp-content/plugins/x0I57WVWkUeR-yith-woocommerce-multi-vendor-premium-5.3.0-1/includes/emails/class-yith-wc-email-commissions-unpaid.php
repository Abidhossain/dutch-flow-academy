<?php
/**
 * Commissions unpaid email
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WC_Email_Commissions_Unpaid' ) ) {
	/**
	 * Commissions unpaid Email
	 * New commissions are credited to vendors.
	 *
	 * @class      YITH_WC_Email_Commissions_Unpaid
	 * @extends    WC_Email
	 * @package YITH\MultiVendor
	 * @version    4.0.0
	 */
	class YITH_WC_Email_Commissions_Unpaid extends WC_Email {

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->id          = 'commissions_unpaid';
			$this->title       = __( 'Commissions unpaid', 'yith-woocommerce-product-vendors' );
			$this->description = __( 'New commissions have been credited to the vendor but the payment failed or is still pending.', 'yith-woocommerce-product-vendors' );

			$this->heading = __( 'Commissions unpaid', 'yith-woocommerce-product-vendors' );
			$this->subject = __( '[{site_title}] - Commissions unpaid', 'yith-woocommerce-product-vendors' );

			$this->template_base  = YITH_WPV_TEMPLATE_PATH;
			$this->template_html  = 'emails/commissions-unpaid.php';
			$this->template_plain = 'emails/plain/commissions-unpaid.php';

			// Triggers for this email.
			add_action( 'yith_vendors_commissions_unpaid', array( $this, 'trigger' ) );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}

		/**
		 * Trigger function.
		 *
		 * @access public
		 * @param YITH_Vendors_Commission $commission The commission object.
		 * @return void
		 */
		public function trigger( $commission ) {
			if ( ! $commission instanceof YITH_Vendors_Commission || ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->setup_locale();

			$this->object = $commission;
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			$this->restore_locale();
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
					'commission'    => $this->object,
					'order'         => $this->object,
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
					'commission'    => $this->object,
					'order'         => $this->object,
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
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'yith-woocommerce-product-vendors' ),
					'default' => 'yes',
				),
				'recipient'  => array(
					'title'       => __( 'Recipient(s)', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s stand for the default email recipients comma separated.
					'description' => sprintf( __( 'Enter recipients (comma-separated) for this email. Defaults to <code>%s</code>', 'yith-woocommerce-product-vendors' ), esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default'     => '',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s is a list of available placeholder for email.
					'description' => sprintf( __( 'Available placeholders: %s', 'yith-woocommerce-product-vendors' ), '<code>' . implode( '</code><code>', array_keys( $this->placeholders ) ) . '</code>' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s is a list of available placeholder for email.
					'description' => sprintf( __( 'Available placeholders: %s', 'yith-woocommerce-product-vendors' ), '<code>' . implode( '</code><code>', array_keys( $this->placeholders ) ) . '</code>' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'yith-woocommerce-product-vendors' ),
					'type'        => 'select',
					'description' => __( 'Choose email format.', 'yith-woocommerce-product-vendors' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
				),
			);
		}
	}
}

return new YITH_WC_Email_Commissions_Unpaid();
