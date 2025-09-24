<?php
/**
 * Commissions status changed (bulk action) email
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WC_Email_Vendor_Commissions_Bulk_Action' ) ) {
	/**
	 * Commissions status changed (bulk action) Email
	 * An email sent when commissions have been updated.
	 *
	 * @class      YITH_WC_Email_Vendor_Commissions_Bulk_Action
	 * @extends    WC_Email
	 * @package YITH\MultiVendor
	 * @version    4.0.0
	 */
	class YITH_WC_Email_Vendor_Commissions_Bulk_Action extends WC_Email {

		/**
		 * New commission status
		 *
		 * @var string
		 */
		public $new_commission_status;

		/**
		 * Current vendor object
		 *
		 * @var YITH_Vendor
		 */
		public $current_vendor = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->id          = 'vendor_commissions_bulk_action';
			$this->title       = __( 'Commissions status changed (bulk action)', 'yith-woocommerce-product-vendors' );
			$this->description = __( 'Commissions have been updated.', 'yith-woocommerce-product-vendors' );

			$this->heading = __( 'Commissions updated', 'yith-woocommerce-product-vendors' );
			$this->subject = __( '[{site_title}] - Commissions updated', 'yith-woocommerce-product-vendors' );

			$this->template_base  = YITH_WPV_TEMPLATE_PATH;
			$this->template_html  = 'emails/commissions-bulk.php';
			$this->template_plain = 'emails/plain/commissions-bulk.php';

			// Triggers for this email.
			add_action( 'yith_vendors_commissions_bulk_action', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Trigger function
		 *
		 * @access public
		 * @param YITH_Vendors_Commission $commissions           Commissions paid.
		 * @param integer                 $vendor_id             The vendor ID.
		 * @param string                  $new_commission_status The new commissions' status.
		 * @return void
		 */
		public function trigger( $commissions, $vendor_id, $new_commission_status ) {
			$this->object                = $commissions;
			$this->new_commission_status = $new_commission_status;

			if ( empty( $commissions ) || ! $this->is_enabled() ) {
				return;
			}

			$this->setup_locale();

			$vendor = yith_wcmv_get_vendor( $vendor_id, 'vendor' );
			if ( $vendor && $vendor->is_valid() ) {
				$this->current_vendor = $vendor;
				$vendor_email         = $vendor->get_meta( 'store_email' );

				if ( empty( $vendor_email ) ) {
					$vendor_owner = get_user_by( 'id', absint( $vendor->get_owner() ) );
					$vendor_email = $vendor_owner instanceof WP_User ? $vendor_owner->user_email : false;
				}

				$this->recipient = $vendor_email;

				if ( $this->sent_to_admin() ) {
					$admin_email      = sanitize_email( get_option( 'admin_email' ) );
					$this->recipient .= ",{$admin_email}";
				}

				if ( $this->recipient ) {
					$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
				}
			}

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
					'commissions'           => $this->object,
					'new_commission_status' => $this->new_commission_status,
					'current_vendor'        => $this->current_vendor,
					'show_note'             => $this->show_note(),
					'email_heading'         => $this->get_heading(),
					'sent_to_admin'         => $this->sent_to_admin(),
					'plain_text'            => false,
					'email'                 => $this,
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
					'commissions'           => $this->object,
					'new_commission_status' => $this->new_commission_status,
					'current_vendor'        => $this->current_vendor,
					'show_note'             => $this->show_note(),
					'email_heading'         => $this->get_heading(),
					'sent_to_admin'         => $this->sent_to_admin(),
					'plain_text'            => true,
					'email'                 => $this,
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
				'enabled'       => array(
					'title'   => __( 'Enable/Disable', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable notification for this email', 'yith-woocommerce-product-vendors' ),
					'default' => 'yes',
				),
				'subject'       => array(
					'title'       => __( 'Subject', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s is a list of available placeholder for email.
					'description' => sprintf( __( 'Available placeholders: %s', 'yith-woocommerce-product-vendors' ), '<code>' . implode( '</code><code>', array_keys( $this->placeholders ) ) . '</code>' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'       => array(
					'title'       => __( 'Email heading', 'yith-woocommerce-product-vendors' ),
					'type'        => 'text',
					// translators: %s is a list of available placeholder for email.
					'description' => sprintf( __( 'Available placeholders: %s', 'yith-woocommerce-product-vendors' ), '<code>' . implode( '</code><code>', array_keys( $this->placeholders ) ) . '</code>' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type'    => array(
					'title'       => __( 'Email type', 'yith-woocommerce-product-vendors' ),
					'type'        => 'select',
					'description' => __( 'Choose email format.', 'yith-woocommerce-product-vendors' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
				),
				'show_note'     => array(
					'title'   => __( 'Show commission note', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable commission note column for this email', 'yith-woocommerce-product-vendors' ),
					'default' => 'no',
				),
				'sent_to_admin' => array(
					'title'   => __( 'Send a copy of this email to administrator', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'Send a copy of this email to the website admin.', 'yith-woocommerce-product-vendors' ),
					'default' => 'no',
				),
			);
		}

		/**
		 * Retrieve the table for commission details
		 *
		 * @param YITH_Vendors_Commission $commissions           Commissions paid.
		 * @param string                  $new_commission_status The new commissions status.
		 * @param boolean                 $show_note             Show note or not.
		 * @param boolean                 $plain_text            Plain text or not.
		 * @return string
		 */
		public function email_commission_bulk_table( $commissions, $new_commission_status, $show_note, $plain_text = false ) {

			add_filter( 'yith_wcmv_commission_have_been_calculated_text', '__return_empty_string' );

			ob_start();

			$template = $plain_text ? 'plain/commissions-bulk-table' : 'commissions-bulk-table';

			yith_wcmv_get_template(
				$template,
				array(
					'commissions'           => $commissions,
					'new_commission_status' => $new_commission_status,
					'show_note'             => $show_note,
				),
				'emails'
			);

			$return = apply_filters( 'woocommerce_email_commission_detail_table', ob_get_clean(), $this );

			remove_filter( 'yith_wcmv_commission_have_been_calculated_text', '__return_empty_string' );

			return $return;
		}

		/**
		 * Checks if this email is enabled and will be sent.
		 *
		 * @return bool
		 */
		public function show_note() {
			return apply_filters( 'woocommerce_email_show_note_' . $this->id, 'yes' === $this->get_option( 'show_note' ), $this->object );
		}

		/**
		 * Checks if this email is enabled and will be sent.
		 *
		 * @return bool
		 */
		public function sent_to_admin() {
			return apply_filters( 'woocommerce_email_sent_to_admin_' . $this->id, 'yes' === $this->get_option( 'sent_to_admin' ), $this->object );
		}
	}
}

return new YITH_WC_Email_Vendor_Commissions_Bulk_Action();
