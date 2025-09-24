<?php
/**
 * YITH_Vendors_Emails
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 5.0.0
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Emails' ) ) {
	/**
	 * Handle plugin emails.
	 */
	class YITH_Vendors_Emails {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * An array of available emails
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $emails_list = array();

		/**
		 * Constructor
		 *
		 * @since  5.0.0
		 * @return void
		 */
		private function __construct() {

			$this->emails_list = array(
				'YITH_WC_Email_Commissions_Unpaid',
				'YITH_WC_Email_Commissions_Paid',
				'YITH_WC_Email_Vendor_Commissions_Paid',
				'YITH_WC_Email_New_Vendor_Registration',
				'YITH_WC_Email_Vendor_New_Account',
				'YITH_WC_Email_Vendor_Rejected',
				'YITH_WC_Email_New_Order',
				'YITH_WC_Email_Cancelled_Order',
				'YITH_WC_Email_Vendor_Commissions_Bulk_Action',
				'YITH_WC_Email_Product_Set_In_Pending_Review',
			);

			add_filter( 'woocommerce_email_classes', array( $this, 'register_emails' ) );
			add_action( 'yith_wcmv_email_order_items_table', array( $this, 'email_order_items_table' ), 10, 8 );

			// Admin panel.
			add_filter( 'yith_wcmv_admin_panel_tabs', array( $this, 'emails_tab' ), 30 );
			add_action( 'yith_wcmv_emails_panel_tab', array( $this, 'emails_tab_content' ), 99 );

			// Admin actions.
			add_action( 'yith_wcmv_admin_ajax_email_active_toggle', array( $this, 'handle_email_active_toggle' ) );
			add_action( 'yith_wcmv_admin_ajax_email_save_settings', array( $this, 'handle_email_save_settings' ) );

			// Load custom email styles.
			add_filter( 'woocommerce_email_styles', array( $this, 'add_email_styles' ), 10, 2 );
		}

		/**
		 * Get plugin emails
		 *
		 * @since 5.0.0
		 * @return array
		 */
		public function get_emails_list() {
			return apply_filters( 'yith_wcmv_plugin_emails_list', $this->emails_list );
		}

		/**
		 * Register emails
		 *
		 * @since  5.0.0
		 * @param array $emails An array of registered emails.
		 * @return array
		 */
		public function register_emails( $emails ) {
			foreach ( $this->get_emails_list() as $email ) {
				$file = YITH_WPV_PATH . 'includes/emails/class-' . str_replace( '_', '-', strtolower( $email ) ) . '.php';
				if ( file_exists( $file ) ) {
					$emails[ $email ] = include $file;
				}
			}

			return $emails;
		}

		/**
		 * Get plugin emails
		 *
		 * @since 5.0.0
		 * @return WC_Email[]
		 */
		public function get_emails() {
			static $emails;

			if ( is_null( $emails ) ) {
				$mailer = WC()->mailer();
				$emails = array_filter(
					$mailer->get_emails(),
					function ( $value, $key ) {
						return in_array( $key, $this->get_emails_list(), true );
					},
					ARRAY_FILTER_USE_BOTH
				);
			}

			return $emails;
		}

		/**
		 * Get plugin email
		 *
		 * @since 5.0.0
		 * @param string $email Email class name.
		 * @return WC_Email|null
		 */
		protected function get_email( $email ) {
			$emails = $this->get_emails();
			return isset( $emails[ $email ] ) ? $emails[ $email ] : null;
		}

		/**
		 * Get the email vendor order table
		 *
		 * @param YITH_Vendor $vendor              Vendor object.
		 * @param WC_Order    $order               Order object.
		 * @param boolean     $show_download_links (Optional) True to show item download link, false otherwise. Default false.
		 * @param boolean     $show_sku            (Optional) True to show item sku, false otherwise. Default false.
		 * @param boolean     $show_purchase_note  (Optional) True to show purchase note, false otherwise. Default false.
		 * @param boolean     $show_image          (Optional) True to show item image, false otherwise. Default false.
		 * @param array       $image_size          (Optional) The item image size. Default array(32,32).
		 * @param boolean     $plain_text          (Optional) True if is a plain email, false otherwise. Default false.
		 * @return void
		 */
		public function email_order_items_table( $vendor, $order, $show_download_links = false, $show_sku = false, $show_purchase_note = false, $show_image = false, $image_size = array( 32, 32 ), $plain_text = false ) {

			$template = ! empty( $plain_text ) ? 'emails/plain/vendor-email-order-items.php' : 'emails/vendor-email-order-items.php';

			yith_wcmv_get_template(
				$template,
				array(
					'order'                  => $order,
					'vendor'                 => $vendor,
					'items'                  => $order->get_items(),
					'show_download_links'    => $show_download_links,
					'show_sku'               => $show_sku,
					'show_purchase_note'     => $show_purchase_note,
					'show_image'             => $show_image,
					'image_size'             => $image_size,
					'plain_text'             => $plain_text,
					'tax_credited_to_vendor' => 'vendor' === get_option( 'yith_wpv_commissions_tax_management', 'website' ),
				)
			);
		}

		/**
		 * Add emails tab
		 *
		 * @since  5.0.0
		 * @param array $tabs An array of panel tabs.
		 * @return array
		 */
		public function emails_tab( $tabs ) {
			return array_merge(
				$tabs,
				array(
					'emails' => _x( 'Emails', '[Admin]Panel tab name', 'yith-woocommerce-product-vendors' ),
				)
			);
		}

		/**
		 * Emails tab content
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function emails_tab_content() {
			yith_wcmv_include_admin_template( 'emails-list', array( 'emails' => $this->get_emails() ) );
		}

		/**
		 * Handle AJAX email active toggle
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function handle_email_active_toggle() {
			// phpcs:disable WordPress.Security.NonceVerification
			$email = isset( $_POST['email'] ) ? $this->get_email( sanitize_text_field( wp_unslash( $_POST['email'] ) ) ) : null;
			if ( empty( $email ) ) {
				wp_send_json_error();
			}

			$status = isset( $_POST['status'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['status'] ) ) ) : '';
			if ( ! in_array( $status, array( 'yes', 'no' ), true ) ) {
				wp_send_json_error();
			}

			$email->update_option( 'enabled', $status );

			wp_send_json_success();
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle AJAX email settings update
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function handle_email_save_settings() {
			// phpcs:disable WordPress.Security.NonceVerification
			$email = isset( $_POST['email'] ) ? $this->get_email( sanitize_text_field( wp_unslash( $_POST['email'] ) ) ) : null;
			if ( empty( $email ) ) {
				wp_send_json_error();
			}

			$email->process_admin_options();

			wp_send_json_success();
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Add email style for CTA
		 *
		 * @since 5.0.0
		 * @param string   $css Current email styles.
		 * @param WC_Email $email Email class instance.
		 * @return string
		 */
		public function add_email_styles( $css, $email ) {

			// Add style only to plugin registered emails.
			$valid = array_filter(
				$this->get_emails_list(),
				function ( $email_class ) use ( $email ) {
					return $email instanceof $email_class;
				}
			);

			if ( ! empty( $valid ) ) {
				ob_start();
				yith_wcmv_get_template( 'email-styles', array(), 'emails' );
				$css .= ob_get_clean();
			}

			return $css;
		}
	}
}
