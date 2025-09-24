<?php
/**
 * New Staff Member email
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WC_Email_New_Staff_Member', false ) ) {
	/**
	 * New Vendor Staff Member email
	 * This email is sent to new staff member.
	 *
	 * @class      YITH_WC_Email_New_Staff_Member
	 * @extends    WC_Email
	 * @package YITH\MultiVendor
	 * @version    4.0.0
	 */
	class YITH_WC_Email_New_Staff_Member extends WC_Email {

		/**
		 * User login name.
		 *
		 * @var string
		 */
		public $user_login;

		/**
		 * User email.
		 *
		 * @var string
		 */
		public $user_email;

		/**
		 * User display name.
		 *
		 * @var string
		 */
		public $user_display_name;

		/**
		 * Magic link to set initial password.
		 *
		 * @var string
		 */
		public $set_password_url;

		/**
		 * Vendor store name.
		 *
		 * @var string
		 */
		public $vendor_name;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'new_vendor_staff_member';
			$this->title          = _x( 'New staff member', '[Admin]Email title', 'yith-woocommerce-product-vendors' );
			$this->description    = _x( 'This email is sent to new vendor staff members.', '[Admin]Email description', 'yith-woocommerce-product-vendors' );
			$this->template_base  = YITH_WPV_TEMPLATE_PATH;
			$this->template_html  = 'emails/new-vendor-staff-member.php';
			$this->template_plain = 'emails/plain/new-vendor-staff-member.php';

			// Set placeholders key.
			$this->placeholders = array(
				'{store_name}' => '',
			);

			// Sent trigger listen.
			add_action( 'yith_wcmv_new_vendor_staff_member', array( $this, 'trigger' ), 10, 3 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_default_subject() {
			return _x( 'You are a new {store_name} staff member!', 'Email default subject', 'yith-woocommerce-product-vendors' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return _x( 'Welcome to {store_name}', 'Email default heading', 'yith-woocommerce-product-vendors' );
		}

		/**
		 * Trigger.
		 *
		 * @since  4.0.0
		 * @param integer     $user_id     User ID.
		 * @param YITH_Vendor $vendor      Vendor object associated with the user.
		 * @param boolean     $new_account (Optional) Whether the WP user is an existing one or ia a new one. Default false.
		 */
		public function trigger( $user_id, $vendor, $new_account = false ) {

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->setup_locale();

			if ( $user_id ) {
				$this->object            = new WP_User( $user_id );
				$this->user_login        = stripslashes( $this->object->user_login );
				$this->user_display_name = stripslashes( $this->object->display_name );
				$this->user_email        = stripslashes( $this->object->user_email );
				$this->vendor_name       = $vendor->get_name();
				$this->recipient         = $this->user_email;
				$this->set_password_url  = $new_account ? $this->generate_set_password_url() : '';
				// Set placeholder value based on Vendor name.
				$this->placeholders['{store_name}'] = $vendor->get_name();
			}

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_content_html() {
			ob_start();
			yith_wcmv_get_template(
				$this->template_html,
				array(
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'user_login'         => $this->user_login,
					'user_display_name'  => $this->user_display_name,
					'blogname'           => $this->get_blogname(),
					'vendor_name'        => $this->vendor_name,
					'set_password_url'   => $this->set_password_url,
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
				)
			);

			return ob_get_clean();
		}

		/**
		 * Get content plain.
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_content_plain() {
			ob_start();
			yith_wcmv_get_template(
				$this->template_plain,
				array(
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'user_login'         => $this->user_login,
					'user_display_name'  => $this->user_display_name,
					'blogname'           => $this->get_blogname(),
					'vendor_name'        => $this->vendor_name,
					'set_password_url'   => $this->set_password_url,
					'sent_to_admin'      => false,
					'plain_text'         => true,
					'email'              => $this,
				)
			);

			return ob_get_clean();
		}

		/**
		 * Generate set password URL link for a new user.
		 *
		 * @since 4.0.0
		 * @return string
		 */
		protected function generate_set_password_url() {
			// Generate a magic link so user can set initial password.
			$key = get_password_reset_key( $this->object );
			if ( ! is_wp_error( $key ) ) {
				$action = 'newaccount';
				return wc_get_account_endpoint_url( 'lost-password' ) . "?action=$action&key=$key&login=" . rawurlencode( $this->object->user_login );
			} else {
				// Something went wrong while getting the key for new password URL, send customer to the generic password reset.
				return wc_get_account_endpoint_url( 'lost-password' );
			}
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
					'label'   => __( 'Enable notification for this email', 'yith-woocommerce-product-vendors' ),
					'default' => 'yes',
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

return new YITH_WC_Email_New_Staff_Member();
