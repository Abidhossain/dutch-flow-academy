<?php
/**
 * YITH Vendors Report Abuse Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
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

if ( ! class_exists( 'YITH_Vendors_Report_Abuse' ) ) {
	/**
	 * Handle report abuse module feature
	 */
	class YITH_Vendors_Report_Abuse {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Post type name
		 *
		 * @since 4.0.0
		 * @const string
		 */
		const POST_TYPE = 'reported_abuse';

		/**
		 * YITH_Vendors_Report_Abuse constructor.
		 *
		 * @since  4.0.0
		 */
		private function __construct() {
			$this->register_post_type();
			$this->init_hooks();
		}

		/**
		 * Register report_abuse post type
		 *
		 * @since  4.0.0
		 * @return void
		 */
		private function register_post_type() {
			if ( ! is_blog_installed() || post_type_exists( self::POST_TYPE ) ) {
				return;
			}

			register_post_type(
				self::POST_TYPE,
				apply_filters(
					'yith_wcmc_register_post_type_report_abuse',
					array(
						'label'               => __( 'Abuse reports', 'yith-woocommerce-product-vendors' ),
						'description'         => __( 'Monitor abuse reports submitted by users on your marketplace.', 'yith-woocommerce-product-vendors' ),
						'public'              => false,
						'show_ui'             => true,
						'capability_type'     => 'post',
						'capabilities'        => array(
							'create_posts' => is_multisite() ? 'do_not_allow' : false,
						),
						'map_meta_cap'        => true,
						'publicly_queryable'  => false,
						'exclude_from_search' => true,
						'show_in_menu'        => false,
						'hierarchical'        => false,
						'show_in_nav_menus'   => false,
						'rewrite'             => false,
						'query_var'           => false,
						'supports'            => false,
						'has_archive'         => false,
					)
				)
			);
		}

		/**
		 * Init class hooks and filters
		 *
		 * @since  4.0.0
		 * @return void
		 */
		private function init_hooks() {

			if ( is_admin() ) {
				// Maybe load post type announcement deps.
				add_action( 'admin_init', array( $this, 'maybe_load_list_table_class' ) );
				// For publish status on item restore.
				add_filter( 'wp_untrash_post_status', array( $this, 'force_publish_status_on_untrash' ), 10, 3 );
			}

			add_action( 'woocommerce_single_product_summary', array( $this, 'add_report_abuse_link' ), 60 );
			// Register module scripts and style for module.
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_styles' ), 1 );
			add_filter( 'yith_wcmv_get_inline_custom_css_rules', array( $this, 'add_custom_css_rules' ), 10, 1 );
			// AJAX submit!
			add_action( 'wp_ajax_send_report_abuse', array( $this, 'report_an_abuse' ) );
			add_action( 'wp_ajax_nopriv_send_report_abuse', array( $this, 'report_an_abuse' ) );
			add_action( 'wc_ajax_send_report_abuse', array( $this, 'report_an_abuse' ) );
		}

		/**
		 * Maybe load post type announcement handler and deps
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function maybe_load_list_table_class() {
			if ( isset( $_GET['post_type'] ) && self::POST_TYPE === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				YITH_Vendors_Reported_Abuse_List_Table::instance();
			}
		}

		/**
		 * Force post to publish status once restored from trash.
		 *
		 * @param string $new_status      The new status of the post being restored.
		 * @param int    $post_id         The ID of the post being restored.
		 * @param string $previous_status The status of the post at the point where it was trashed.
		 * @return mixed|string
		 */
		public function force_publish_status_on_untrash( $new_status, $post_id, $previous_status ) {
			if ( self::POST_TYPE === get_post_type( $post_id ) ) {
				return 'publish';
			}

			return $new_status;
		}

		/**
		 * Register module scripts and styles
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function register_scripts_styles() {
			wp_register_script( 'yith-wcmv-report-abuse', YITH_WPV_ASSETS_URL . 'js/frontend/' . yit_load_js_file( 'report-abuse.js' ), array( 'jquery', 'wp-util' ), YITH_WPV_VERSION, true );
		}

		/**
		 * Add custom CSS rules
		 *
		 * @since  4.0.0
		 * @param array $rules An array of defined rules.
		 * @return array
		 */
		public function add_custom_css_rules( $rules ) {

			$colors = get_option(
				'yith_wpv_report_abuse_link_color',
				array(
					'normal' => '#af2323',
					'hover'  => '#af2323',
				)
			);

			$rules['report-abuse-color']       = $colors['normal'];
			$rules['report-abuse-color-hover'] = $colors['hover'];

			return $rules;
		}

		/**
		 * Add a report abuse link in single product page
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function add_report_abuse_link() {
			global $product;

			$show = get_option( 'yith_wpv_report_abuse_link', 'none' );
			if ( 'none' === $show || ! ( $product instanceof WC_Product ) ) {
				return;
			}

			$vendor = yith_wcmv_get_vendor( 'current', 'product' );
			if ( 'vendor' === $show && ( ! $vendor || ! $vendor->is_valid() ) ) {
				return;
			}

			$args = array(
				'product_id' => $product->get_id(),
				'vendor_id'  => 0,
				'vendor'     => $vendor, // Leave for backward compatibility.
			);

			// translators: %s stand for the product title.
			$args['title'] = apply_filters( 'yith_wcmv_report_abuse_modal_title', sprintf( __( 'Report an issue on "%s"', 'yith-woocommerce-product-vendors' ), $product->get_title() ) );
			if ( $vendor && $vendor->is_valid() ) {
				$args['vendor_id'] = $vendor->get_id();
				// translators: %s stand for the vendor shop name.
				$args['subtitle'] = sprintf( __( 'Sold by %s', 'yith-woocommerce-product-vendors' ), $vendor->get_name() );
			}

			$this->print_report_abuse_link( $args );
		}

		/**
		 * Print report abuse link
		 *
		 * @since  4.0.0
		 * @param array $args An array of template arguments.
		 * @return void
		 */
		protected function print_report_abuse_link( $args = array() ) {

			$current_user = null;
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
			}

			$args = wp_parse_args(
				$args,
				array(
					'abuse_text'   => get_option( 'yith_wpv_report_abuse_link_text', _x( 'Report abuse', '[Single Product Page]: link label', 'yith-woocommerce-product-vendors' ) ),
					'button_class' => apply_filters( 'yith_wpv_report_abuse_button_class', 'submit' ),
					'submit_label' => apply_filters( 'yith_wpv_report_submit_button_label', __( 'Send report', 'yith-woocommerce-product-vendors' ) ),
					'current_user' => array(
						'display_name' => ! empty( $current_user ) ? $current_user->display_name : '',
						'user_email'   => ! empty( $current_user ) ? $current_user->user_email : '',
					),
				)
			);

			// Enqueue scripts.
			wp_enqueue_script( 'yith-wcmv-report-abuse' );
			yith_wcmv_get_template( 'abuse', $args, 'woocommerce/single-product' );
		}

		/**
		 * Create a new abuse
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Report a new abuse errors.
		 */
		public function report_an_abuse() {
			// phpcs:disable WordPress.Security.NonceVerification
			try {

				if ( empty( $_POST['report_abuse'] ) || ! empty( $_POST['report_abuse']['spam'] ) ) {
					throw new Exception();
				}

				$name         = isset( $_POST['report_abuse']['name'] ) ? sanitize_text_field( wp_unslash( $_POST['report_abuse']['name'] ) ) : '';
				$from_email   = isset( $_POST['report_abuse']['email'] ) ? sanitize_email( wp_unslash( $_POST['report_abuse']['email'] ) ) : '';
				$user_message = isset( $_POST['report_abuse']['message'] ) ? sanitize_textarea_field( $_POST['report_abuse']['message'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

				if ( empty( $name ) || empty( $from_email ) || empty( $user_message ) ) {
					throw new Exception( _x( 'Please, fill in all of the mandatory form fields!', 'Error on report abuse form submit', 'yith-woocommerce-product-vendors' ) );
				}

				$product = isset( $_POST['report_abuse']['product_id'] ) ? wc_get_product( absint( $_POST['report_abuse']['product_id'] ) ) : false;
				$vendor  = isset( $_POST['report_abuse']['vendor_id'] ) ? yith_wcmv_get_vendor( absint( $_POST['report_abuse']['vendor_id'] ) ) : false;

				if ( ( empty( $vendor ) || ! $vendor->is_valid() ) && empty( $product ) ) {
					throw new Exception();
				}

				// Create post data.
				$data = array(
					'post_type'    => self::POST_TYPE,
					'post_status'  => 'publish',
					'post_author'  => get_current_user_id(),
					// translators: %1$s: Username of whi reported the abuse, %2$s: Abuse date.
					'post_title'   => sprintf( __( 'Abuse reported by %1$s &ndash; %2$s', 'yith-woocommerce-product-vendors' ), $name, strftime( _x( '%1$b %2$d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'yith-woocommerce-product-vendors' ) ) ),
					'post_content' => $user_message,
					'meta_input'   => array_filter(
						array(
							'_vendor_id'  => ( $vendor && $vendor->is_valid() ) ? $vendor->get_id() : '',
							'_product_id' => $product ? $product->get_id() : '',
							'_from_name'  => $name,
							'_from_email' => $from_email,
						)
					),
				);

				$abuse_id = wp_insert_post( apply_filters( 'yith_wcmv_new_reported_abuse_data', $data ), true );
				if ( empty( $abuse_id ) || is_wp_error( $abuse_id ) ) {
					throw new Exception();
				}

				if ( apply_filters( 'yith_wcmv_send_vendor_report_abuse_email', true ) ) {
					$this->send_report_abuse_email(
						array(
							'name'         => $name,
							'from_email'   => $from_email,
							'user_message' => $user_message,
							'product'      => $product,
							'vendor'       => $vendor,
						)
					);
				}

				wp_send_json_success( _x( 'Abuse reported correctly!', 'Success on report abuse form submit', 'yith-woocommerce-product-vendors' ) );

			} catch ( Exception $e ) {

				$message = $e->getMessage();
				if ( empty( $message ) ) {
					$message = _x( 'Your request could not be processed. Please, try again!', 'Error on report abuse form submit', 'yith-woocommerce-product-vendors' );
				}

				wp_send_json_error( $message );
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Send a report to abuse
		 *
		 * @since  4.0.0
		 * @param array $data An array of email data.
		 * @return void
		 * @throws Exception Error on send report abuse email.
		 */
		private function send_report_abuse_email( $data ) {

			extract( $data ); // phpcs:ignore

			$to = sanitize_email( apply_filters( 'yith_wcmv_report_abuse_email', get_option( 'woocommerce_email_from_address' ) ) );
			// translators: %s could be either vendor name or product name.
			$subject = sprintf( _x( 'A user has reported abuse on %s.', 'Report abuse email subject', 'yith-woocommerce-product-vendors' ), ( ( $vendor && $vendor->is_valid() ) ? $vendor->get_name() : $product->get_title() ) );
			$headers = array( "From: {$name} <{$from_email}>" );

			if ( $product ) {
				$product_name = sprintf( '<a href="%s" target="_blank">%s</a>', $product->get_permalink(), $product->get_title() );
				// translators: %1$s: the customer name, %2$s the customer email, %3$s the product name, %4$s the product ID.
				$message = sprintf( __( 'User %1$s (%2$s) is reporting abuse on the following product: %3$s (ID: #%4$s).', 'yith-woocommerce-product-vendors' ), $name, $from_email, $product_name, $product->get_id() );
				if ( $vendor && $vendor->is_valid() ) {
					$vendor_name = sprintf( '<a href="%s" target="_blank">%s</a>', $vendor->get_url(), $vendor->get_name() );
					// translators: %1$s: the vendor name, %2$s the vendor ID.
					$message .= "\n" . sprintf( __( 'Vendor: %1$s (ID: #%2$s).', 'yith-woocommerce-product-vendors' ), $vendor_name, $vendor->get_id() );
				}
			} else {
				$vendor_name = sprintf( '<a href="%s" target="_blank">%s</a>', $vendor->get_url(), $vendor->get_name() );
				// translators: %1$s: the customer name, %2$s the customer email, %3$s the product name, %4$s the product ID.
				$message = sprintf( __( 'User %1$s (%2$s) is reporting abuse on the following store: %3$s (ID: #%4$s).', 'yith-woocommerce-product-vendors' ), $name, $from_email, $vendor_name, $vendor->get_id() );
			}

			// translators: %s is the user message.
			$message .= "\n\n" . sprintf( __( 'Message: %s', 'yith-woocommerce-product-vendors' ), $user_message ) . "\n\n";

			add_filter(
				'wp_mail_content_type',
				function () {
					return 'text/html';
				}
			);

			if ( ! wp_mail( $to, $subject, nl2br( $message ), implode( "\r\n", $headers ), array() ) ) {
				throw new Exception();
			}
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  4.0.0
 * @return YITH_Vendors_Report_Abuse
 */
if ( ! function_exists( 'YITH_Vendors_Report_Abuse' ) ) {
	function YITH_Vendors_Report_Abuse() { // phpcs:ignore
		return YITH_Vendors_Report_Abuse::instance();
	}
}

YITH_Vendors_Report_Abuse();
