<?php
/**
 * Product set in pending review (to admin) email
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WC_Email_Product_Set_In_Pending_Review' ) ) {
	/**
	 * Product set in pending review (to admin)
	 * Email sent to administrator when a product is edited by vendor and need of admin approval.
	 *
	 * @class      YITH_WC_Email_Product_Set_In_Pending_Review
	 * @extends    WC_Email
	 * @package    YITH\MultiVendor
	 * @version    4.0.0
	 */
	class YITH_WC_Email_Product_Set_In_Pending_Review extends WC_Email {

		/**
		 * The product object
		 *
		 * @var null|WC_Product
		 */
		public $product = null;

		/**
		 * The product vendor
		 *
		 * @var null
		 */
		public $vendor = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->id          = 'product_set_in_pending_review';
			$this->title       = __( 'Product set in pending review (to admin)', 'yith-woocommerce-product-vendors' );
			$this->description = __( 'Email sent to the administrator when a product is edited by a vendor and needs admin approval.', 'yith-woocommerce-product-vendors' );

			$this->heading = __( 'A vendor product needs review', 'yith-woocommerce-product-vendors' );
			$this->subject = __( '[{site_title}] Product Edited', 'yith-woocommerce-product-vendors' );

			$this->template_base  = YITH_WPV_TEMPLATE_PATH;
			$this->template_html  = 'emails/product-set-in-pending-review.php';
			$this->template_plain = 'emails/plain/product-set-in-pending-review.php';

			$this->recipient = $this->get_option( 'recipient' );
			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}

			$this->placeholders = array(
				'{product_name}'      => '',
				'{product_edit_link}' => '',
				'{vendor}'            => '',
			);

			// Triggers for this email.
			add_action( 'yith_wcmv_product_set_in_pending_review_after_edit', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();

			$this->vendor = null;
		}

		/**
		 * Trigger function.
		 *
		 * @access public
		 * @param integer     $product_id     The product ID.
		 * @param WC_Product  $product        The WC_Product object.
		 * @param YITH_Vendor $current_vendor The YITH_Vendor object.
		 * @return void
		 */
		public function trigger( $product_id, $product, $current_vendor ) {

			if ( ! $this->is_enabled() ) {
				return;
			}

			$this->product = $product;
			if ( ! $this->product instanceof WC_Product || 'pending' !== $this->product->get_status() ) {
				return;
			}

			$this->setup_locale();

			// Set placeholders value.
			$this->placeholders = array_merge(
				$this->placeholders,
				array(
					'{product_name}'      => $this->product->get_title(),
					'{product_edit_link}' => get_edit_post_link( $this->product->get_id() ),
					'{vendor}'            => $current_vendor->get_name(),
					// Deprecated.
					'{post_link}'         => get_edit_post_link( $this->product->get_id() ),
				)
			);

			$this->send( $this->recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

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
					'product'       => $this->product,
					'vendor'        => $this->vendor,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				)
			);

			return $this->format_string( ob_get_clean() );
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

return new YITH_WC_Email_Product_Set_In_Pending_Review();
