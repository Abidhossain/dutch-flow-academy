<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class YITH_YWGC_Admin_Extended
 *
 * @package YITH\GiftCards\Includes\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Admin_Extended' ) ) {
	/**
	 * YITH_YWGC_Admin_Extended class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Admin_Extended extends YITH_YWGC_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Admin_Extended
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 */
		public function __construct() {
			parent::__construct();

			add_action( 'init', array( $this, 'ywgc_upload_default_image_to_media_library' ) );
			add_action( 'init', array( $this, 'create_gift_card_product_in_draft' ) );
			add_filter( 'removable_query_args', array( __CLASS__, 'removable_query_args' ), 10, 2 );

			add_action( 'woocommerce_admin_field_update-cron', array( $this, 'show_update_cron_field' ) );
		}

		/**
		 * Retrieve the admin panel tabs.
		 *
		 * @return array
		 */
		protected function get_admin_panel_tabs(): array {
			return apply_filters(
				'yith_ywgc_admin_panel_tabs',
				array(
					'dashboard' => array(
						'title' => _x( 'Dashboard', 'Settings tab name', 'yith-woocommerce-gift-cards' ),
						'icon'  => 'dashboard',
					),
					'settings'  => array(
						'title' => _x( 'Settings', 'Settings tab name', 'yith-woocommerce-gift-cards' ),
						'icon'  => 'settings',
					),
					'email'     => array(
						'title' => __( 'Email Settings', 'yith-woocommerce-gift-cards' ),
						'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>',
					),
				)
			);
		}

		/**
		 * Retrieve the premium tab content.
		 *
		 * @return array
		 */
		protected function get_premium_tab(): array {
			return array(
				'features' => array(
					array(
						'title'       => __( 'Sell physical gift cards to ship to customers', 'yith-woocommerce-gift-cards' ),
						'description' => __( 'Create unlimited physical gift cards with one or multiple fixed amounts. Physical gift cards can be printed and shipped to customers.', 'yith-woocommerce-gift-cards' ),
					),
					array(
						'title'       => __( 'Advanced user options', 'yith-woocommerce-gift-cards' ),
						'description' => __( 'Allow users to enter a custom card amount and choose a delivery date and time for the gift card.', 'yith-woocommerce-gift-cards' ),
					),
					array(
						'title'       => __( 'Custom images support', 'yith-woocommerce-gift-cards' ),
						'description' => __( 'Users can upload or drag and drop a custom image or photo to customize the gift card and make it more special.', 'yith-woocommerce-gift-cards' ),
					),
					array(
						'title'       => __( 'Notifications to improve user experience ', 'yith-woocommerce-gift-cards' ),
						'description' => __( 'Notify the sender via e-mail when the gift card is delivered to the recipient and when it is used to purchase in your shop.', 'yith-woocommerce-gift-cards' ),
					),
					array(
						'title'       => __( 'Gift card redemption options', 'yith-woocommerce-gift-cards' ),
						'description' => __( 'Set a minimum requested amount in the cart to allow users to apply a gift card code, exclude specific product categories from redemption, etc.', 'yith-woocommerce-gift-cards' ),
					),
					array(
						'title'       => __( '"Gift this product” options to sell more gift cards ', 'yith-woocommerce-gift-cards' ),
						'description' => __( 'Suggest the purchase of a gift card on the product pages: the gift cards will be automatically generated with the same value as the products and they will be highlighted in the e-mail. ', 'yith-woocommerce-gift-cards' ),
					),
				),
			);
		}

		/**
		 * Retrieve the help tab content.
		 *
		 * @return array
		 */
		protected function get_help_tab(): array {
			return array(
				'doc_url' => $this->get_doc_url(),
			);
		}

		/**
		 * Retrieve the content for the welcome modals.
		 *
		 * @return array
		 */
		protected function get_welcome_modals(): array {
			return array(
				'show_in'  => 'panel',
				'on_close' => function () {
					update_option( 'yith-ywgc-welcome-modal', 'no' );
				},
				'modals'   => array(
					'welcome' => array(
						'type'        => 'welcome',
						'description' => __( 'With this plugin you can create different gift card products and allow your customers to send it to a friend or a loved one.', 'yith-woocommerce-gift-cards' ),
						'show'        => get_option( 'yith-ywgc-welcome-modal', 'welcome' ) === 'welcome',
						'items'       => array(
							'documentation'  => array(
								'url' => $this->get_doc_url(),
							),
							'create-product' => array(
								'title'       => __( 'Are you ready? Create your first <mark>gift card product</mark>', 'yith-woocommerce-gift-cards' ),
								'description' => __( '...and start the adventure!', 'yith-woocommerce-gift-cards' ),
								'url'         => add_query_arg(
									array(
										'yith-ywgc-new-gift-card-product' => 1,
									)
								),
							),
						),
					),
				),
			);
		}

		/**
		 * Show the custom woocommerce field
		 *
		 * @since 3.1.12
		 *
		 * @param array $option Option.
		 */
		public function show_update_cron_field( $option ) {
			$option['option'] = $option;

			wc_get_template( '/admin/update-cron.php', $option, '', YITH_YWGC_TEMPLATES_DIR );
		}

		/**
		 * Retrieve the documentation URL.
		 *
		 * @return string
		 */
		protected function get_doc_url(): string {
			return 'https://www.bluehost.com/help/article/yith-woocommerce-gift-cards';
		}

		/**
		 * Generate a Draft Gift Card product and redirect the customer to the edit page
		 *
		 * @return void
		 */
		public static function ywgc_upload_default_image_to_media_library() {

			$default_image_id = get_option( 'ywgc_default_image_id' );

			if ( ! $default_image_id || ! get_post_status( $default_image_id ) ){
				// upload the default gift card image to the Media Library and use it as the product image.
				$file     = YITH_YWGC_ASSETS_IMAGES_URL . 'default-giftcard-main-image.jpg';
				$filename = basename( $file );

				$upload_file = wp_upload_bits( $filename, null, file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

				if ( ! $upload_file['error'] ) {
					$wp_filetype   = wp_check_filetype( $filename, null );
					$attachment    = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					);
					$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'] );

					if ( ! is_wp_error( $attachment_id ) ) {
						require_once ABSPATH . 'wp-admin/includes/image.php';

						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
						wp_update_attachment_metadata( $attachment_id, $attachment_data );

						update_option( 'ywgc_default_image_id', $attachment_id );
					}
				}
			}
		}

		/**
		 * Generate a Draft Gift Card product and redirect the customer to the edit page
		 *
		 * @return void
		 */
		public static function create_gift_card_product_in_draft() {
			if ( isset( $_GET['yith-ywgc-new-gift-card-product'] ) ) {//phpcs:ignore WordPress.Security.NonceVerification
				if ( class_exists( 'WC_Product_Gift_Card' ) ) {
					$args = array(
						'post_title'     => esc_html__( 'Gift Card', 'yith-woocommerce-gift-cards' ),
						'post_name'      => 'gift_card',
						'post_content'   => esc_html__( 'This product has been automatically created by the plugin YITH Gift Cards. It is in draft mode, so you can just edit this text, upload a custom image (if you have one) and then publish the product to start to sell your gift card.', 'yith-woocommerce-gift-cards' ),
						'post_status'    => 'draft',
						'post_date'      => gmdate( 'Y-m-d H:i:s' ),
						'post_author'    => 0,
						'post_type'      => 'product',
						'comment_status' => 'closed',
					);

					$draft_gift_card_id = wp_insert_post( $args );

					$product = new WC_Product_Gift_Card( $draft_gift_card_id );

					if ( $product ) {
						$product->set_tax_status( 'none' );
						$product->set_virtual( true );
						$product->save_amounts( array( 10, 25, 50 ) );

						$default_image_id = get_option( 'ywgc_default_image_id' );
						$product->set_image_id( $default_image_id );

					}

						$product->save();

						wp_safe_redirect( get_edit_post_link( $product->get_id(), 'edit' ) );
						exit();
				}
			}
		}

		/**
		 * Handle removable query args.
		 *
		 * @param array $args Query args to be removed.
		 *
		 * @return array
		 * @since 4.0.0
		 */
		public static function removable_query_args( $args ) {
			$args[] = 'yith-ywgc-new-gift-card-product';

			return $args;
		}
	}
}
