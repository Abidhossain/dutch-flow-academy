<?php
/**
 * Class YITH_YWGC_Backend_Premium
 *
 * @package YITH\GiftCards\Includes\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Backend_Premium' ) ) {
	/**
	 * YITH_YWGC_Backend class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Backend_Premium extends YITH_YWGC_Backend_Extended {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Backend_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Admin notices
		 *
		 * @var array
		 */
		public $admin_notices = array();

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
		protected function __construct() {
			parent::__construct();

			/**
			 * Show icon that prompt the admin for a pre-printed gift cards bought and whose code is not entered
			 */
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'show_warning_for_pre_printed_gift_cards' ) );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'show_warning_for_pre_printed_gift_cards' ) );

			add_action( 'yith_ywgc_gift_card_email_sent', array( $this, 'manage_bcc' ) );

			add_action( 'woocommerce_order_status_changed', array( $this, 'update_gift_card_as_coupon_amount_on_order_status_change' ), 10, 4 );

			add_action( 'woocommerce_admin_field_yith_ywgc_transform_smart_coupons_html', array( $this, 'yith_ywgc_transform_smart_coupons_buttons' ) );

			add_filter( 'yith_ywgc_general_options_array', array( $this, 'yith_ywgc_general_options_array_custom' ), 10, 1 );

			add_action( 'wp_ajax_yith_convert_smart_coupons_button', array( $this, 'ywgc_convert_smart_coupons_to_gift_cards' ) );

			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'ywgc_hidden_order_item_meta' ), 10, 1 );

			/**
			 * APPLY_FILTERS: ywgc_display_default_shipping_value_condition
			 *
			 * Filter the condition to show the default shipping in the order summary, not the discounted one.
			 *
			 * @param bool true to display the default shipping, false to show the discounted shipping. Default: false
			 *
			 * @return bool
			 */
			if ( apply_filters( 'ywgc_display_default_shipping_value_condition', false ) ) {
				add_action( 'woocommerce_order_shipping_to_display', array( $this, 'ywgc_display_default_shipping_value' ), 10, 3 );
			}

			add_action( 'wp_ajax_yith_ywgc_run_exporter', array( $this, 'yith_ywgc_run_exporter' ) );

			add_action( 'wp_ajax_yith_ywgc_run_importer', array( $this, 'yith_ywgc_run_importer' ) );

			/**
			 * Commented due to bad performance when the site have a lot of gift cards to search
			 *
			 * add_filter( 'get_search_query', array( $this, 'gift_card_search_label' ) );
			 * add_action( 'parse_query', array( $this, 'gift_card_search' ) );
			 * add_filter( 'query_vars', array( $this, 'add_custom_query_var_gift_cards' ) );
			 */

			add_action( 'admin_notices', array( $this, 'ywgc_show_upload_error' ) );

			add_action( 'giftcard-category_edit_form', array( $this, 'ywgc_include_upload_image_categories' ), 10, 2 );

			add_action( 'admin_init', array( $this, 'ywgc_upload_image_actions_from_settings_panel' ), 9 );

			add_action( 'wp_ajax_ywgc_delete_image_from_category', array( $this, 'ywgc_delete_image_from_category' ) );

			add_action( 'giftcard-category_add_form_fields', array( $this, 'ywgc_add_form_fields_category_creation' ), 10, 1 );
			add_action( 'created_term', array( $this, 'ywgc_save_images_on_category_creation' ), 10, 3 );
			add_action( 'edit_term', array( $this, 'ywgc_save_images_on_category_creation' ), 10, 3 );

			add_action( 'edit_form_after_title', array( $this, 'ywgc_generate_automatic_code_in_post' ) );

			/**
			 * Compatibility with YITH WooCommerce Subscription Premium
			 */
			if ( defined( 'YITH_YWSBS_PREMIUM' ) ) {
				add_action( 'ywsbs_renew_subscription', array( $this, 'apply_gift_cards_on_subscription_renew' ), 10, 2 );
			}

			/**
			 * Save gift card data when a product of type "gift card" is saved
			 */
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_pre_printed_gift_card_code' ), 1, 2 );

			/**
			 * Ajax method for the Gift Card generator
			 */
			add_action( 'wp_ajax_ywgc_gift_card_generator', array( $this, 'ywgc_gift_card_generator' ) );

			/**
			 * Send the individual gift card postponed in date and time
			 */
			add_action( 'ywgc_send_postponed_gift_card', array( $this, 'ywgc_send_postponed_gift_card' ) );

			add_action( 'woocommerce_before_order_itemmeta', array( $this, 'show_selected_design_link_in_physical_gift_cards_order_itemmeta' ), 15, 3 );

			add_filter( 'woocommerce_product_data_tabs', array( $this, 'hide_product_tabs_gift_this_product' ) );
		}

		/**
		 * Enqueue scripts on administration comment page
		 */
		public function enqueue_backend_files() {
			$screen = get_current_screen();

			if ( ( isset( $_REQUEST['page'] ) && 'yith_woocommerce_gift_cards_panel' === $_REQUEST['page'] ) || 'edit-giftcard-category' === $screen->id || 'product' === $screen->id || 'edit-gift_card' === $screen->id ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_enqueue_style(
					'ywgc_gift_cards_admin_panel_css',
					YITH_YWGC_ASSETS_URL . '/css/ywgc-gift-cards-admin-panel.css',
					array(),
					YITH_YWGC_VERSION
				);

				wp_register_script(
					'ywgc_gift_cards_admin_panel',
					YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-gift-cards-admin-panel.js' ),
					array(
						'jquery',
						'jquery-blockui',
					),
					YITH_YWGC_VERSION,
					true
				);

				/**
				 * APPLY_FILTERS: yith_gift_cards_loader
				 *
				 * Filter the URL of the ajax loader gif for the plugin.
				 *
				 * @param string the gif URL
				 *
				 * @return string
				 */
				wp_localize_script(
					'ywgc_gift_cards_admin_panel',
					'ywgc_data_panel',
					array()
				);

				wp_enqueue_script( 'ywgc_gift_cards_admin_panel' );
			}

			if ( 'product' === $screen->id ) {
				wp_enqueue_style( 'yith-plugin-fw-fields' );
				wp_enqueue_script( 'yith-plugin-fw-fields' );
			}

			$if_shop_order = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) === $screen->id : 'shop-order' === $screen->id;

			if ( is_admin() && ( in_array( $screen->id, array( 'product', 'edit-product' ), true ) ) || ( 'gift_card' === $screen->id ) || $if_shop_order || isset( $_REQUEST['page'] ) && 'yith_woocommerce_gift_cards_panel' === $_REQUEST['page'] || 'edit-gift_card' === $screen->id ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( apply_filters( 'yith_ywgc_enqueue_jquery_ui_css', true ) ) {
					wp_enqueue_style(
						'jquery-ui-css',
						'//code.jquery.com/ui/1.13.1/themes/smoothness/jquery-ui.css',
						'1.0',
						true
					);
				}

				wp_enqueue_style(
					'ywgc-backend-css',
					YITH_YWGC_ASSETS_URL . '/css/ywgc-backend.css',
					array(),
					YITH_YWGC_VERSION
				);

				wp_register_script(
					'ywgc-backend',
					YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-backend.js' ),
					array(
						'jquery',
						'jquery-blockui',
						'jquery-ui-datepicker',
					),
					YITH_YWGC_VERSION,
					true
				);

				$date_format = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );

				wp_localize_script(
					'ywgc-backend',
					'ywgc_data',
					array(
						'ajax_url'                  => admin_url( 'admin-ajax.php' ),
						'nonce'                     => wp_create_nonce( 'ajax-nonce' ),
						'date_format'               => $date_format,
						'decimal_separator'         => wc_get_price_decimal_separator(),
						'create_code_url'           => esc_url( admin_url( 'post-new.php?post_type=gift_card' ) ),
						'code_modal'                => array(
							'title'   => esc_html__( 'Create gift card code', 'yith-woocommerce-gift-cards' ),
							'content' => yith_ywgc_get_view_html( 'modals/create-code/content.php' ),
							'footer'  => yith_ywgc_get_view_html( 'modals/create-code/footer.php' ),
						),
						'export_import_modal'       => array(
							'content' => yith_ywgc_get_view_html( 'modals/export-import/content.php' ),
						),
						'save_email_settings_nonce' => wp_create_nonce( 'yith_ywgc_save_email_settings' ),
						'save_email_status_nonce'   => wp_create_nonce( 'yith_ywgc_save_email_status' ),
						'generate_gift_cards_nonce' => wp_create_nonce( 'yith_ywgc_generate_gift_cards' ),
					)
				);

				wp_enqueue_script( 'ywgc-backend' );
			}

			if ( 'upload' === $screen->id ) {
				wp_register_script(
					'ywgc-categories',
					YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-categories.js' ),
					array(
						'jquery',
						'jquery-blockui',
					),
					YITH_YWGC_VERSION,
					true
				);

				$categories1_id = 'categories1_id';
				$categories2_id = 'categories2_id';

				wp_localize_script(
					'ywgc-categories',
					'ywgc_data',
					array(
						'loader'                => apply_filters( 'yith_gift_cards_loader', YITH_YWGC_ASSETS_URL . '/images/loading.gif' ),
						'ajax_url'              => admin_url( 'admin-ajax.php' ),
						'set_category_action'   => esc_html__( 'Set gift card category', 'yith-woocommerce-gift-cards' ),
						'unset_category_action' => esc_html__( 'Unset gift card category', 'yith-woocommerce-gift-cards' ),
						'categories1'           => $this->get_category_select( $categories1_id ),
						'categories1_id'        => $categories1_id,
						'categories2'           => $this->get_category_select( $categories2_id ),
						'categories2_id'        => $categories2_id,
					)
				);

				wp_enqueue_script( 'ywgc-categories' );
			}

			if ( 'edit-giftcard-category' === $screen->id ) {
				wp_enqueue_media();
				wp_register_script(
					'ywgc-media-button',
					YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-media-button.js' ),
					array(
						'jquery',
					),
					YITH_YWGC_VERSION,
					true
				);

				wp_localize_script(
					'ywgc-media-button',
					'ywgc_data',
					array(
						'upload_file_frame_title'  => esc_html__( 'Manage the Media library', 'yith-woocommerce-gift-cards' ),
						'upload_file_frame_button' => esc_html__( 'Done', 'yith-woocommerce-gift-cards' ),
						'loader'                   => apply_filters( 'yith_gift_cards_loader', YITH_YWGC_ASSETS_URL . '/images/loading.gif' ),
						'ajax_url'                 => admin_url( 'admin-ajax.php' ),
						'delete_image_nonce'       => wp_create_nonce( 'delete_image' ),
					)
				);

				wp_enqueue_script( 'ywgc-media-button' );
			}

			$this->add_generate_gift_card_code_button();

			// Enqueue the modules scripts.
			if ( ( isset( $_REQUEST['page'] ) && 'yith_woocommerce_gift_cards_panel' === $_REQUEST['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_register_script(
					'ywgc-modules',
					YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-modules.js' ),
					array(
						'jquery',
					),
					YITH_YWGC_VERSION,
					true
				);

				wp_localize_script(
					'ywgc-modules',
					'ywgc_modules_data',
					array(
						'ajaxurl'     => admin_url( 'admin-ajax.php' ),
						'nonces'      => array(
							'modulesAction' => wp_create_nonce( YITH_YWGC_Modules::AJAX_ACTION ),
						),
						'blockParams' => array(
							'message'         => '',
							'blockMsgClass'   => 'yith-ywgc-block-ui-element',
							'css'             => array(
								'border'     => 'none',
								'background' => 'transparent',
							),
							'overlayCSS'      => array(
								'background' => '#ffffff',
								'opacity'    => '0.7',
							),
							'ignoreIfBlocked' => false,
						),
					)
				);

				wp_enqueue_script( 'ywgc-modules' );
			}
		}

		/**
		 * Show advanced product settings
		 *
		 * @param int $thepostid Post ID.
		 */
		public function show_advanced_product_settings( $thepostid ) {
			$this->show_manual_amount_settings( $thepostid );
			$this->show_sale_discount_settings( $thepostid );

			parent::show_advanced_product_settings( $thepostid );

			$this->show_gift_card_exclude_categories_settings( $thepostid );
		}

		/**
		 * Show checkbox enabling the product to avoid use of free amount
		 *
		 * @param int $product_id the product ID.
		 */
		public function show_manual_amount_settings( $product_id ) {
			yith_ywgc_get_view( 'gift-cards-manual-amount-settings.php', compact( 'product_id' ) );
		}

		/**
		 * Show input to enter a discount for the gift card
		 *
		 * @param int $product_id the product ID.
		 */
		public function show_sale_discount_settings( $product_id ) {
			yith_ywgc_get_view( 'gift-cards-discount-settings.php', compact( 'product_id' ) );
		}

		/**
		 * Show option to exclude product categories to use the gift card
		 *
		 * @param int $product_id the product ID.
		 */
		public function show_gift_card_exclude_categories_settings( $product_id ) {
			yith_ywgc_get_view( 'gift-cards-exclude-categories-settings.php', compact( 'product_id' ) );
		}

		/**
		 * Create the gift cards for the order
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @throws Exception Thrown when there are issues to set the gift card expiration date.
		 */
		public function create_gift_cards_for_order( $order ) {
			/**
			 * APPLY_FILTERS: ywgc_custom_condition_to_create_gift_card
			 *
			 * Filter the condition to generate the gift card. Useful to third-party plugins.
			 *
			 * @param bool true to generate the gift card in the order, false for not
			 * @param object $order the order object
			 *
			 * @return bool
			 */
			if ( ! apply_filters( 'ywgc_custom_condition_to_create_gift_card', true, $order ) ) {
				return;
			}

			/**
			 * APPLY_FILTERS: ywgc_apply_race_condition
			 *
			 * Filter the condition to apply a race condition when generating the gift cards in the order. Useful when having issues with duplicated gift card codes.
			 *
			 * @param bool true to apply it, false for not
			 *
			 * @return bool
			 */
			if ( apply_filters( 'ywgc_apply_race_condition', false ) ) {
				if ( ! $this->start_race_condition( $order->get_id() ) ) {
					return;
				}
			}

			$line_items = $order->get_items( 'line_item' );

			foreach ( $line_items as $order_item_id => $order_item_data ) {
				$product_id_alternative = wc_get_order_item_meta( $order_item_id, '_ywgc_product_id' );

				$product_id = '' !== $order_item_data['product_id'] ? $order_item_data['product_id'] : $product_id_alternative;
				$product    = wc_get_product( $product_id );

				if ( ! $product instanceof WC_Product_Gift_Card ) {
					continue;
				}

				$gift_ids = ywgc_get_order_item_giftcards( $order_item_id );

				if ( $gift_ids ) {
					continue;
				}

				/**
				 * APPLY_FILTERS: yith_ywgc_create_gift_card_for_order_item
				 *
				 * Filter the condition to generate the gift card by order item.
				 *
				 * @param bool true to generate it, false for not
				 * @param object $order the order object
				 * @param int $order_item_id the order item ID
				 * @param array $order_item_data the order item data
				 *
				 * @return bool
				 */
				if ( ! apply_filters( 'yith_ywgc_create_gift_card_for_order_item', true, $order, $order_item_id, $order_item_data ) ) {
					continue;
				}

				$is_product_as_present = wc_get_order_item_meta( $order_item_id, '_ywgc_product_as_present', true );
				$present_product_id    = 0;
				$present_variation_id  = 0;

				if ( $is_product_as_present ) {
					$present_product_id   = wc_get_order_item_meta( $order_item_id, '_ywgc_present_product_id', true );
					$present_variation_id = wc_get_order_item_meta( $order_item_id, '_ywgc_present_variation_id', true );
				}

				$order_id = $order->get_id();

				/**
				 * APPLY_FILTERS: yith_ywgc_line_subtotal
				 *
				 * Filter the line subtotal when generating a gift card code.
				 *
				 * @param float the line subtotal
				 * @param array $order_item_data the order item data
				 * @param int $order_id the order ID
				 * @param int $order_item_id the order item ID
				 *
				 * @return bool
				 */
				$line_subtotal = apply_filters( 'yith_ywgc_line_subtotal', $order_item_data['line_subtotal'], $order_item_data, $order_id, $order_item_id );

				/**
				 * APPLY_FILTERS: yith_ywgc_line_subtotal_tax
				 *
				 * Filter the line subtotal tax when generating a gift card code.
				 *
				 * @param float the line subtotal tax
				 * @param array $order_item_data the order item data
				 * @param int $order_id the order ID
				 * @param int $order_item_id the order item ID
				 *
				 * @return bool
				 */
				$line_subtotal_tax = apply_filters( 'yith_ywgc_line_subtotal_tax', $order_item_data['line_subtotal_tax'], $order_item_data, $order_id, $order_item_id );
				$quantity          = $order_item_data['qty'];
				$single_amount     = (float) ( $line_subtotal / $quantity );
				$single_tax        = (float) ( $line_subtotal_tax / $quantity );

				$new_ids = array();

				$order_currency = $order->get_currency();

				$product_id       = wc_get_order_item_meta( $order_item_id, '_ywgc_product_id' );
				$is_manual_amount = wc_get_order_item_meta( $order_item_id, '_ywgc_is_manual_amount' );
				$is_digital       = wc_get_order_item_meta( $order_item_id, '_ywgc_is_digital' );

				$has_custom_design = wc_get_order_item_meta( $order_item_id, '_ywgc_has_custom_design' );
				$design_type       = wc_get_order_item_meta( $order_item_id, '_ywgc_design_type' );

				$is_postdated = false;

				if ( $is_digital ) {
					/**
					 * APPLY_FILTERS: ywgc_recipients_array_on_create_gift_cards_for_order
					 *
					 * Filter the gift card recipients on gift card creation.
					 *
					 * @param array the recipients
					 *
					 * @return array
					 */
					$recipients      = apply_filters( 'ywgc_recipients_array_on_create_gift_cards_for_order', wc_get_order_item_meta( $order_item_id, '_ywgc_recipients' ) );
					$recipients      = is_array( $recipients ) ? $recipients : array();
					$recipient_count = count( $recipients );
					$sender          = wc_get_order_item_meta( $order_item_id, '_ywgc_sender_name' );
					$recipient_name  = wc_get_order_item_meta( $order_item_id, '_ywgc_recipient_name' );
					$message         = wc_get_order_item_meta( $order_item_id, '_ywgc_message' );

					/**
					 * APPLY_FILTERS: ywgc_is_postdated_delivery_date_by_default
					 *
					 * Filter the if the gift card is postdated.
					 *
					 * @param bool true if is postdated, false if not
					 *
					 * @return bool
					 */
					$is_postdated = apply_filters( 'ywgc_is_postdated_delivery_date_by_default', wc_get_order_item_meta( $order_item_id, '_ywgc_postdated', true ) );

					if ( $is_postdated ) {
						$delivery_date = wc_get_order_item_meta( $order_item_id, '_ywgc_delivery_date', true );
					}

					$ywgc_delivery_notification_checkbox = wc_get_order_item_meta( $order_item_id, '_ywgc_delivery_notification_checkbox', true );
				}

				for ( $i = 0; $i < $quantity; $i++ ) {
					// Generate a gift card post type and save it.
					$gift_card = new YWGC_Gift_Card_Premium();

					$gift_card->product_id        = $product_id;
					$gift_card->order_id          = $order_id;
					$gift_card->order_item_id     = $order_item_id;
					$gift_card->is_digital        = $is_digital;
					$gift_card->is_manual_amount  = $is_manual_amount;
					$gift_card->has_custom_design = $has_custom_design;
					$gift_card->design_type       = $design_type;

					if ( $has_custom_design ) {
						$gift_card->design = wc_get_order_item_meta( $order_item_id, '_ywgc_design' );
					}

					$gift_card->product_as_present = $is_product_as_present;

					if ( $is_product_as_present ) {
						$gift_card->present_product_id   = $present_product_id;
						$gift_card->present_variation_id = $present_variation_id;
					}

					if ( $gift_card->is_digital ) {
						$gift_card->sender_name        = $sender;
						$gift_card->recipient_name     = $recipient_name;
						$gift_card->message            = $message;
						$gift_card->postdated_delivery = $is_postdated;

						if ( $is_postdated ) {
							$gift_card->delivery_date = $delivery_date;
						}

						/**
						 * If the user entered several recipient email addresses, one gift card
						 * for every recipient will be created and it will be the unique recipient for
						 * that email. If only one, or none if allowed, recipient email address was entered
						 * then create '$quantity' specular gift cards
						 */
						if ( ( 1 === $recipient_count ) && ! empty( $recipients[0] ) ) {
							$gift_card->recipient = $recipients[0];
						} elseif ( ( $recipient_count > 1 ) && ! empty( $recipients[ $i ] ) ) {
							$gift_card->recipient = $recipients[ $i ];
						} else {
							/**
							 * APPLY_FILTERS: ywgc_is_postdated_delivery_date_by_default
							 *
							 * Filter the customer as the gift card recipient using the billing email.
							 *
							 * @param string the recipient email
							 *
							 * @return string
							 */
							$gift_card->recipient = apply_filters( 'yith_ywgc_set_default_gift_card_recipient', $order->get_billing_email() );
						}

						$gift_card->delivery_notification = $ywgc_delivery_notification_checkbox;
					}

					if ( $gift_card->is_virtual() && 'yes' === get_option( 'ywgc_enable_pre_printed_virtual', 'no' ) || apply_filters( 'yith_ywgc_custom_condition_set_gift_card_as_preprinted', false, $gift_card ) ) {
						$gift_card->set_as_pre_printed();
					} elseif ( ! $gift_card->is_virtual() && 'yes' === get_option( 'ywgc_enable_pre_printed_physical', 'no' ) || apply_filters( 'yith_ywgc_custom_condition_set_gift_card_as_preprinted', false, $gift_card ) ) {
						$gift_card->set_as_pre_printed();
					} else {
						$attempts = 100;

						do {
							/**
							 * APPLY_FILTERS: yith_wcgc_generated_code
							 *
							 * Filter the generated gift card code.
							 *
							 * @param string the gift card code
							 * @param object $order the order object
							 * @param object $gift_card the gift card object
							 *
							 * @return string
							 */
							$code       = apply_filters( 'yith_wcgc_generated_code', YITH_YWGC()->generate_gift_card_code(), $order, $gift_card );
							$check_code = YITH_YWGC()->get_gift_card_by_code( $code );

							if ( ! $check_code || is_object( $check_code ) && ! $check_code->ID ) {
								$gift_card->gift_card_number = $code;
								break;
							}

							--$attempts;
						} while ( $attempts > 0 );

						if ( ! $attempts ) {
							// Unable to find a unique code, the gift card need a manual code entered.
							$gift_card->set_as_code_not_valid();
						}
					}

					$gift_card->total_amount = $single_amount + $single_tax;

					// Add the default amount and not the converted one by WPML.
					global $woocommerce_wpml;

					$default_currency_amount = wc_get_order_item_meta( $order_item_id, '_ywgc_default_currency_amount' );

					if ( $woocommerce_wpml && $woocommerce_wpml->multi_currency && ! empty( $default_currency_amount ) ) {
						$gift_card->total_amount = $default_currency_amount;
					}

					if ( defined( 'WOOCOMMERCE_MULTICURRENCY_VERSION' ) ) {
						$gift_card->total_amount = $default_currency_amount;
					}

					$on_sale       = $product->get_add_discount_settings_status();
					$on_sale_value = get_post_meta( $product->get_id(), '_ywgc_sale_discount_value', true );

					if ( $on_sale && $on_sale_value ) {
						$gift_card->total_amount = wc_get_order_item_meta( $order_item_id, '_ywgc_amount_without_discount', true );
					}

					$gift_card->update_balance( $gift_card->total_amount );
					$gift_card->version  = YITH_YWGC_VERSION;
					$gift_card->currency = $order_currency;

					$expiration_date        = get_post_meta( $product_id, '_ywgc_expiration', true );
					$expiration_date_status = $product->get_expiration_settings_status();

					if ( '' !== $expiration_date && $expiration_date_status ) {
						if ( 0 === intval( $expiration_date ) ) {
							$gift_card->expiration = 0;
						} else {
							$gift_card->expiration = $expiration_date;
						}
					} else {
						try {
							/**
							 * APPLY_FILTERS: ywgc_usage_expiration_in_months
							 *
							 * Filter the gift card usage expiration in months.
							 *
							 * @param string the usage expiration in months
							 * @param object $gift_card the gift card object
							 * @param int $product_id the product ID
							 *
							 * @return string
							 */
							$usage_expiration      = apply_filters( 'ywgc_usage_expiration_in_months', get_option( 'ywgc_usage_expiration', '' ), $gift_card, $product_id );
							$start_usage_date      = $gift_card->delivery_date ? $gift_card->delivery_date : current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
							$gift_card->expiration = '0' !== $usage_expiration ? strtotime( "+$usage_expiration month", (int) $start_usage_date ) : '0';
						} catch ( Exception $e ) {
							error_log( 'An error occurred setting the expiration date for gift card: ' . $gift_card->gift_card_number ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
						}
					}

					/**
					 * DO_ACTION: yith_ywgc_before_gift_card_generation_save
					 *
					 * Allow actions before the gift card generated is save.
					 *
					 * @param object $gift_card the gift card object
					 */
					do_action( 'yith_ywgc_before_gift_card_generation_save', $gift_card );

					$gift_card->save();

					if ( $gift_card->delivery_date ) {
						update_post_meta( $gift_card->ID, '_ywgc_delivery_date_toggle', 'yes' );
					}

					if ( $gift_card->expiration ) {
						update_post_meta( $gift_card->ID, '_ywgc_expiration_date_toggle', 'yes' );
					}

					if ( apply_filters( 'yith_ywgc_register_gift_card_purchase_customer', false ) ) {
						$user_id = $order->get_customer_id();

						update_post_meta( $gift_card->ID, YWGC_META_GIFT_CARD_CUSTOMER_USER, $user_id );
					}

					update_post_meta( $gift_card->ID, '_ywgc_default_currency_amount', $default_currency_amount );
					update_post_meta( $gift_card->ID, '_ywgc_order_item_id', $order_item_id );

					/**
					 * DO_ACTION: yith_ywgc_after_gift_card_generation_save
					 *
					 * Allow actions after the gift card generated is save.
					 *
					 * @param object $gift_card the gift card object
					 */
					do_action( 'yith_ywgc_after_gift_card_generation_save', $gift_card );

					$new_ids[] = $gift_card->ID;

					/**
					 * APPLY_FILTERS: ywgc_send_gift_card_code_by_default
					 *
					 * Filter the condition to send the gift card code.
					 *
					 * @param bool true to send it, false for not. Default: true
					 * @param object $gift_card the gift card object
					 *
					 * @return bool
					 */
					/**
					 * APPLY_FILTERS: yith_wcgc_send_now_gift_card_to_custom_recipient
					 *
					 * Filter the condition to send the gift card code to a custom recipient.
					 *
					 * @param bool true to send it, false for not. Default: false
					 * @param object $gift_card the gift card object
					 *
					 * @return bool
					 */
					if ( ( ! $is_postdated && apply_filters( 'ywgc_send_gift_card_code_by_default', true, $gift_card ) ) && $gift_card->get_code() !== '' || apply_filters( 'yith_wcgc_send_now_gift_card_to_custom_recipient', false, $gift_card ) ) {
						YITH_YWGC_Emails_Premium::get_instance()->send_gift_card_email( $gift_card );
					}

					if ( $is_postdated ) {
						// Create a single cron, to send the scheduled gift card.
						wp_schedule_single_event( apply_filters( 'ywgc_scheduled_gift_card_delivery_date_timestamp', $delivery_date ), 'ywgc_send_postponed_gift_card', array( $gift_card->ID ) );
					}
				}

				// save gift card Post ids on order item.
				ywgc_set_order_item_giftcards( $order_item_id, $new_ids );
			}

			if ( apply_filters( 'ywgc_apply_race_condition', false ) ) {
				$this->end_race_condition( $order->get_id() );
			}
		}

		/**
		 * Save_gift_card_product
		 * Save additional product attribute when a gift card product is saved
		 *
		 * @param int $post_id current product id.
		 *
		 * @return void
		 */
		public function save_gift_card_product( $post_id ) {
			$product = new WC_Product_Gift_Card( $post_id );

			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST[ 'ywgc-override-product-settings-' . $product->get_id() ] ) ) {
				$product->update_override_global_settings_status( sanitize_text_field( wp_unslash( $_POST[ 'ywgc-override-product-settings-' . $product->get_id() ] ) ) );
			} else {
				$product->update_override_global_settings_status( false );
			}

			// Save the flag for manual amounts when the product is saved.
			if ( isset( $_POST[ 'manual_amount_mode-' . $product->get_id() ] ) ) {
				$product->update_manual_amount_status( sanitize_text_field( wp_unslash( $_POST[ 'manual_amount_mode-' . $product->get_id() ] ) ) );
			} else {
				$product->update_manual_amount_status( 'disabled-product-level' );
			}

			// Discount settings update.
			if ( isset( $_POST[ 'ywgc-add-discount-settings-' . $product->get_id() ] ) ) {
				$product->update_add_discount_settings_status( sanitize_text_field( wp_unslash( $_POST[ 'ywgc-add-discount-settings-' . $product->get_id() ] ) ) );
			} else {
				$product->update_add_discount_settings_status( false );
			}

			if ( isset( $_POST['gift_card-sale-discount'] ) ) {
				update_post_meta( $post_id, '_ywgc_sale_discount_value', sanitize_text_field( wp_unslash( $_POST['gift_card-sale-discount'] ) ) );
			}

			if ( isset( $_POST['gift_card-sale-discount-text'] ) ) {
				update_post_meta( $post_id, '_ywgc_sale_discount_text', sanitize_text_field( wp_unslash( $_POST['gift_card-sale-discount-text'] ) ) );
			}

			// Expiration settings update.
			if ( isset( $_POST[ 'ywgc-expiration-settings-' . $product->get_id() ] ) ) {
				$product->update_expiration_settings_status( sanitize_text_field( wp_unslash( $_POST[ 'ywgc-expiration-settings-' . $product->get_id() ] ) ) );
			} else {
				$product->update_expiration_settings_status( false );
			}

			if ( isset( $_POST['gift-card-expiration-date'] ) ) {
				$date_format               = apply_filters( 'yith_wcgc_date_format', 'Y-m-d' );
				$date                      = 'd/m/Y' === $date_format ? str_replace( '/', '-', sanitize_text_field( wp_unslash( $_POST['gift-card-expiration-date'] ) ) ) : sanitize_text_field( wp_unslash( $_POST['gift-card-expiration-date'] ) );
				$expiration_date           = is_string( $date ) ? strtotime( $date ) : $date;
				$expiration_date_formatted = ! empty( $expiration_date ) ? date_i18n( $date_format, $expiration_date ) : '';

				update_post_meta( $post_id, '_ywgc_expiration', $expiration_date );
				update_post_meta( $post_id, '_ywgc_expiration_date', $expiration_date_formatted );
			}

			if ( isset( $_POST['ywgc-minimal-amount'] ) ) {
				update_post_meta( $post_id, '_ywgc_minimal_manual_amount', sanitize_text_field( wp_unslash( $_POST['ywgc-minimal-amount'] ) ) );
			}
			if ( isset( $_POST['ywgc-maximum-amount'] ) ) {
				update_post_meta( $post_id, '_ywgc_maximum_manual_amount', sanitize_text_field( wp_unslash( $_POST['ywgc-maximum-amount'] ) ) );
			}

			if ( isset( $_POST[ 'ywgc-excluded-categories-settings-' . $product->get_id() ] ) ) {
				$product->update_excluded_categories_settings_status( true );

				if ( isset( $_POST['ywgc-excluded-categories'] ) ) {
					$product->update_excluded_categories( array_map( 'intval', $_POST['ywgc-excluded-categories'] ) );
				}
			} else {
				$product->update_excluded_categories_settings_status( false );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Show the gift card code under the order item, in the order admin page
		 *
		 * @param int        $item_id  Item ID.
		 * @param array      $item     Order item.
		 * @param WC_product $_product Product object.
		 *
		 * @since  1.0.0
		 */
		public function show_gift_card_code_on_order_item( $item_id, $item, $_product ) {
			global $theorder;

			if ( wc_get_order_item_meta( $item_id, '_ywgc_product_as_present', true ) ) {
				$product_id   = wc_get_order_item_meta( $item_id, '_ywgc_present_product_id', true );
				$product      = wc_get_product( $product_id );
				$product_link = $product_id ? admin_url( 'post.php?post=' . $product_id . '&action=edit' ) : '';

				if ( $product && $product instanceof WC_Product ) {
					/**
					 * APPLY_FILTERS: yith_wc_gift_card_as_a_gift_card
					 *
					 * Filter the "as a Gift Card" string in the order item when a product is gifted.
					 *
					 * @param string "as a Gift Card" string in the order item
					 *
					 * @return string
					 */
					$product_title = "<a href='" . $product_link . "' >" . $product->get_name() . '</a> ' . apply_filters( 'yith_wc_gift_card_as_a_gift_card', esc_html__( 'as a Gift Card', 'yith-woocommerce-gift-cards' ) );

					?>
					<div class="ywgc_order_sold_as_gift_card">
						<?php echo wp_kses_post( $product_title ); ?>
					</div>
					<?php
				}
			}

			$gift_ids = ywgc_get_order_item_giftcards( $item_id );

			if ( empty( $gift_ids ) ) {
				return;
			}

			foreach ( $gift_ids as $gift_id ) {
				$gc = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_id ) );

				/**
				 * APPLY_FILTERS: yith_ywgc_enter_pre_printed_gift_card_code
				 *
				 * Filter the condition to show or not the pre-printed gift card code field in the order.
				 *
				 * @param bool true to show it, false for not. Default: true
				 * @param object $theorder the order object
				 * @param object $_product the gift card product object
				 *
				 * @return bool
				 */

				if ( ! $gc->is_pre_printed() ) :
					?>
					<div>
						<span class="ywgc-gift-code-label"><?php echo esc_html__( 'Gift card code: ', 'yith-woocommerce-gift-cards' ); ?></span>
						<?php if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown ?>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=gift_card&s=' . $gc->get_code() ) ); ?>" class="ywgc-card-code"><?php echo wp_kses_post( $gc->get_code() ); ?></a>
						<?php } else { ?>
							<span class="ywgc-card-code"><?php echo wp_kses_post( $gc->get_code() ); ?></span>
						<?php } ?>
					</div>
					<?php elseif ( apply_filters( 'yith_ywgc_enter_pre_printed_gift_card_code', true, $theorder, $_product ) ) : ?>
						<div>
							<span class="ywgc-gift-code-label"><?php echo esc_html__( 'Enter the pre-printed code: ', 'yith-woocommerce-gift-cards' ); ?></span>
							<input type="text" name="ywgc-pre-printed-code[<?php echo esc_attr( $gc->ID ); ?>]" class="ywgc-pre-printed-code">
						</div>
						<?php
				endif;
			}
		}

		/**
		 * Show the design download link in the order item meta for the physical gift cards
		 *
		 * @param int        $item_id  Order item ID.
		 * @param array      $item     Order item.
		 * @param WC_product $_product Product object.
		 *
		 * @since  1.0.0
		 */
		public function show_selected_design_link_in_physical_gift_cards_order_itemmeta( $item_id, $item, $_product ) {

			/**
			 * APPLY_FILTERS: yith_ywgc_show_download_link_in_physical_gift_cards_order_itemmeta
			 *
			 * Filter the condition to show or not a download link in the order item meta for the design selected in the physical gift cards.
			 *
			 * @param bool true to show it, false for not. Default: true
			 * @param int $item_id the item ID
			 *
			 * @return bool
			 */
			if ( ! apply_filters( 'yith_ywgc_show_download_link_in_physical_gift_cards_order_itemmeta', true, $item_id ) ) {
				return;
			}

			$gift_ids = ywgc_get_order_item_giftcards( $item_id );

			if ( empty( $gift_ids ) ) {
				return;
			}

			foreach ( $gift_ids as $gift_id ) {
				$gc = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_id ) );

				if ( ! $gc->is_virtual() && $gc->has_custom_design ) :
					if ( 'default' === $gc->design_type ) {
						$design_url = wp_get_attachment_url( get_post_thumbnail_id( $gc->product_id ) );
					} elseif ( 'custom' === $gc->design_type ) {
						$design_url = YITH_YWGC_SAVE_URL . $gc->design;
					} else {
						$design_url = wp_get_attachment_url( $gc->design );
					}
					?>

					<div>
						<a target="_blank"  href="<?php echo esc_url( $design_url ); ?>" class="ywgc-card-design"><?php echo esc_html_x( 'Download selected design', 'Link in the order data to download the design selected by the customer for the gift card', 'yith-woocommerce-gift-cards' ); ?></a>
					</div>
					<?php
				endif;
			}
		}

		/**
		 * Hide the product tabs for the Gift this product
		 *
		 * @param array $tabs Tabs.
		 *
		 * @return mixed
		 */
		public function hide_product_tabs_gift_this_product( $tabs ) {
			global $post;

			if ( get_option( YWGC_PRODUCT_PLACEHOLDER, -1 ) === (string) $post->ID ) {
				$tabs = array( 'general' => $tabs['general'] );
			}

			return $tabs;
		}

		/**
		 * Send a copy of gift card email to additional recipients, if set
		 *
		 * @param object $gift_card Gift card object.
		 */
		public function manage_bcc( $gift_card ) {
			$this->notify_customer_if_gift_cards_is_delivered( $gift_card );

			$recipients = array();

			$notify_delivered_email_settings = get_option( 'woocommerce_ywgc-email-send-gift-card_settings' );

			// Check if the option is set to add the admin email.
			if ( isset( $notify_delivered_email_settings['ywgc_blind_carbon_copy'] ) && 'yes' === $notify_delivered_email_settings['ywgc_blind_carbon_copy'] ) {
				$recipients[] = get_option( 'admin_email' );
			}

			if ( isset( $notify_delivered_email_settings['ywgc_blind_carbon_copy_additionals'] ) ) {
				$emails_array = explode( ',', $notify_delivered_email_settings['ywgc_blind_carbon_copy_additionals'] );

				foreach ( $emails_array as $email ) {
					$recipients[] = $email;
				}
			}

			$order = wc_get_order( $gift_card->order_id );

			// Check if the option is set to add the gift card buyer email.
			if ( isset( $notify_delivered_email_settings['ywgc_blind_carbon_copy_to_buyer'] ) && 'yes' === $notify_delivered_email_settings['ywgc_blind_carbon_copy_to_buyer'] && $order instanceof WC_Order && $gift_card->recipient !== $order->get_billing_email() ) {
				$recipients[] = $order->get_billing_email();
			}

			/**
			 * APPLY_FILTERS: yith_ywgc_bcc_additional_recipients
			 *
			 * Filter the additional gift card recipients, to send them a BCC email with the gift card.
			 *
			 * @param array $recipients the additional recipients
			 * @param object $gift_card the gift card object
			 *
			 * @return array
			 */
			$recipients = apply_filters( 'yith_ywgc_bcc_additional_recipients', array_filter( $recipients, 'strlen' ), $gift_card );

			if ( empty( $recipients ) ) {
				return;
			}

			WC()->mailer();

			foreach ( $recipients as $recipient ) {
				// Send a copy of the gift card to the recipient.
				$gift_card->recipient = $recipient;

				/**
				 * DO_ACTION: yith_ywgc_after_gift_card_generation_save
				 *
				 * Trigger the send gift card notification.
				 *
				 * @param object $gift_card the gift card object
				 * @param string the case, for the "Recipient" or in "BCC"
				 */
				do_action( 'ywgc_email_send_gift_card_notification', $gift_card, 'BCC' );
			}
		}

		/**
		 * Show the gift card code under the order item, in the order admin page
		 *
		 * @param object $gift_card Gift card object.
		 *
		 * @since  1.0.0
		 */
		public function notify_customer_if_gift_cards_is_delivered( $gift_card ) {
			$notify_delivered_email_settings = get_option( 'woocommerce_ywgc-email-delivered-gift-card_settings' );

			if ( $gift_card->exists() && isset( $notify_delivered_email_settings['enabled'] ) && 'yes' === $notify_delivered_email_settings['enabled'] ) {
				if ( isset( $notify_delivered_email_settings['ywgc_delivery_notify_customer_checkbox'] ) && 'yes' === $notify_delivered_email_settings['ywgc_delivery_notify_customer_checkbox'] && 'on' === $gift_card->delivery_notification ) {
					WC()->mailer();

					/**
					 * DO_ACTION: ywgc_email_delivered_gift_card
					 *
					 * Trigger the send gift card delivered email.
					 *
					 * @param object $gift_card the gift card object
					 */
					do_action( 'ywgc_email_delivered_gift_card', $gift_card );
				} elseif ( isset( $notify_delivered_email_settings['ywgc_delivery_notify_customer_checkbox'] ) && 'yes' === $notify_delivered_email_settings['ywgc_delivery_notify_customer_checkbox'] && 'off' === $gift_card->delivery_notification ) {
					return;
				} else {
					WC()->mailer();
					do_action( 'ywgc_email_delivered_gift_card', $gift_card );
				}
			}
		}

		/**
		 * Show icon on backend page "orders" for order where there is file uploaded and waiting to be confirmed.
		 *
		 * @param string $column current column being shown.
		 */
		public function show_warning_for_pre_printed_gift_cards( $column ) {
			// If column is not of type order_status, skip it.
			if ( 'order_status' !== $column ) {
				return;
			}

			global $the_order;

			if ( ! empty( $the_order ) && ( $the_order instanceof WC_Order ) ) {
				$count = $this->pre_printed_cards_waiting_count( $the_order );

				if ( $count ) {
					// translators: %d is the number of pre-printed gift cards in the order.
					$message = sprintf( _n( 'This order contains %d pre-printed gift card that needs to be filled', 'This order contains %d pre-printed gift cards that needs to be filled', $count, 'yith-woocommerce-gift-cards' ), $count );

					?>
						<img class="ywgc-pre-printed-waiting" src="<?php echo esc_url( YITH_YWGC_ASSETS_IMAGES_URL . 'waiting.png' ); ?>" title="<?php echo esc_attr( $message ); ?>" />
					<?php
				}
			}
		}

		/**
		 * Retrieve the number of pre-printed gift cards that are not filled
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @since  1.0.0
		 * @return int
		 */
		private function pre_printed_cards_waiting_count( $order ) {
			$order_items = $order->get_items( 'line_item' );
			$count       = 0;

			foreach ( $order_items as $order_item_id => $order_data ) {
				$gift_ids = ywgc_get_order_item_giftcards( $order_item_id );

				if ( empty( $gift_ids ) ) {
					return;
				}

				foreach ( $gift_ids as $gift_id ) {
					$gc = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_id ) );

					if ( $gc->is_pre_printed() ) {
						++$count;
					}
				}
			}

			return $count;
		}

		/**
		 * Gift Card Search
		 *
		 * @param WP_Query $wp Query.
		 */
		public function gift_card_search( $wp ) {
			global $pagenow, $wpdb;

			if ( 'edit.php' !== $pagenow || empty( $wp->query_vars['s'] ) || 'gift_card' !== $wp->query_vars['post_type'] || ! isset( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			// Query to check if the search value is a postmeta included in the gift cards, name, email, etc.
			$query_by_meta_value = "SELECT DISTINCT post_id FROM {$wpdb->posts} wposts, {$wpdb->postmeta} wpostmeta WHERE meta_value LIKE '%%%s%%'";

			// Query to get the gift cards by the code/title.
			$query_by_code = "SELECT DISTINCT ID FROM {$wpdb->posts} wposts WHERE wposts.post_type = 'gift_card' AND wposts.post_title LIKE '%%%s%%' ";

			$gift_by_meta_array = $wpdb->get_col( $wpdb->prepare( $query_by_meta_value, sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.Security.NonceVerification.Recommended
			$gift_by_code_array = $wpdb->get_col( $wpdb->prepare( $query_by_code, sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.Security.NonceVerification.Recommended

			$gift_array = array_unique( array_merge( $gift_by_meta_array, $gift_by_code_array ) );

			if ( is_array( $gift_array ) ) {
				unset( $wp->query_vars['s'] );

				$wp->query_vars['gift_card_search'] = true;

				$wp->query_vars['post__in'] = array_merge( $gift_array, array( 0 ) );
			}
		}

		/**
		 * Gift card search label
		 *
		 * @param WP_Query $query Query.
		 *
		 * @return array|string|string[]
		 */
		public function gift_card_search_label( $query ) {
			global $pagenow, $typenow;

			if ( 'edit.php' !== $pagenow ) {
				return $query;
			}

			if ( 'gift_card' !== $typenow ) {
				return $query;
			}

			if ( ! isset( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return $query;
			}

			if ( ! get_query_var( 'gift_card_search' ) ) {
				return $query;
			}

			return sanitize_text_field( wp_unslash( $_GET['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}


		/**
		 * Query vars for custom searches.
		 *
		 * @param mixed $public_query_vars Query vars.
		 *
		 * @return array
		 */
		public function add_custom_query_var_gift_cards( $public_query_vars ) {
			$public_query_vars[] = 'gift_card_search';

			return $public_query_vars;
		}

		/**
		 * Hide item meta from the orders.
		 *
		 * @param array $meta_array Meta array.
		 */
		public function ywgc_hidden_order_item_meta( $meta_array ) {
			$meta_array[] = '_ywgc_design';

			/**
			 * APPLY_FILTERS: yith_ywgc_order_item_meta
			 *
			 * Filter the order item meta values to be hidden in the order details.
			 *
			 * @param array $meta_array the order item meta values to be hidden
			 *
			 * @return array
			 */
			return apply_filters( 'yith_ywgc_order_item_meta', $meta_array );
		}

		/**
		 * The order is set to completed
		 *
		 * @param WC_Order $order  Order object.
		 * @param string   $action Action to take.
		 *
		 * @since  1.0.0
		 */
		public function change_gift_cards_status_on_order( $order, $action ) {
			if ( 'nothing' === $action ) {
				return;
			}

			$order_items = $order->get_items();

			foreach ( $order_items as $item_id => $item ) {
				$ids = ywgc_get_order_item_giftcards( $item_id );

				if ( $ids ) {
					foreach ( $ids as $gift_id ) {
						$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_id ) );

						if ( ! $gift_card->exists() ) {
							continue;
						}

						if ( 'dismiss' === $action ) {
							$gift_card->set_dismissed_status();
						} elseif ( 'disable' === $action ) {
							$gift_card->set_enabled_status( false );
						}
					}
				}
			}
		}

		/**
		 * Update the gift card balance when it is used as a coupon
		 *
		 * @param int            $order_id Order ID.
		 * @param string         $from_status Old order status.
		 * @param string         $to_status New order status.
		 * @param WC_Order|false $order Order object.
		 */
		public function update_gift_card_as_coupon_amount_on_order_status_change( $order_id, $from_status, $to_status, $order = false ) {
			if ( 'cancelled' === $to_status || 'refunded' === $to_status || 'failed' === $to_status ) {
				$order   = wc_get_order( $order_id );
				$coupons = $order->get_coupons();

				foreach ( $coupons as $coupon ) {
					$args = array(
						'gift_card_number' => $coupon->get_code(),
					);

					$gift_card = new YITH_YWGC_Gift_Card( $args );

					if ( ! $gift_card->exists() ) {
						continue;
					}

					$new_amount = (float) $gift_card->get_balance() + (float) $coupon->get_discount() + (float) $coupon->get_discount_tax();

					if ( $new_amount > $gift_card->total_amount ) {
						$new_amount = $gift_card->total_amount;
					}

					if ( apply_filters( 'yith_ywgc_restore_gift_card_balance', true, $gift_card ) ) {
						$gift_card->update_balance( $new_amount );
					}
				}
			}
		}

		/**
		 * Convert Smart Coupons to Gift Cards
		 */
		public function ywgc_convert_smart_coupons_to_gift_cards() {
			global $wpdb;

			$date_format = apply_filters( 'yith_wcgc_date_format', 'Y-m-d' );

			$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$limit  = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( 0 == $limit ) {
				$limit = 50;
			}

			$coupons_array        = $wpdb->get_results( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key= 'discount_type' AND meta_value= 'smart_coupon'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$total_coupons_number = count( $coupons_array );

			if ( $limit > $total_coupons_number ) {
				$counter = $total_coupons_number;
			} else {
				$counter = $offset + $limit;
			}

			foreach ( $coupons_array as $coupons ) {
				$coupon_id   = $coupons->post_id;
				$coupon_code = get_the_title( $coupon_id );

				if ( ! $coupon_code ) {
					continue;
				}

				for ( $i = $offset; $i < $counter; $i++ ) {
					$coupon_amount          = get_post_meta( $coupon_id, 'coupon_amount', true );
					$recipient_emails_array = get_post_meta( $coupon_id, 'customer_email', true );
					$expiration_timestamp   = get_post_meta( $coupon_id, 'date_expires', true );
					$expiration_formatted   = '0' !== $expiration_timestamp ? date_i18n( $date_format, $expiration_timestamp ) : '';
				}

				$duplicated_post_array = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_title = '{$coupon_code}' AND post_type = 'gift_card' " ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				foreach ( $duplicated_post_array as $duplicated_post ) {
					$duplicated_post_id = $duplicated_post->ID;
				}

				if ( get_the_title( $duplicated_post_id ) === $coupon_code ) {
					continue;
				} else {
					$new_draft_post = array(
						'post_title'  => $coupon_code,
						'post_status' => 'draft',
						'post_type'   => 'gift_card',
					);

					$post_id = wp_insert_post( $new_draft_post );

					$updated_post = array(
						'ID'          => $post_id,
						'post_title'  => $coupon_code,
						'post_status' => 'publish',
						'post_type'   => 'gift_card',
					);

					wp_update_post( $updated_post );
					update_post_meta( $post_id, '_ywgc_amount_total', $coupon_amount );
					update_post_meta( $post_id, '_ywgc_balance_total', $coupon_amount );
					update_post_meta( $post_id, '_ywgc_is_digital', '1' );
					update_post_meta( $post_id, '_ywgc_expiration', $expiration_timestamp );
					update_post_meta( $post_id, '_ywgc_expiration_date_formatted', $expiration_formatted );
					update_post_meta( $post_id, '_ywgc_recipient', $recipient_emails_array['0'] );
				}
			}

			$new_offset = $offset + $limit;

			if ( ( $total_coupons_number - $new_offset ) < $limit ) {
				$limit = $total_coupons_number - $new_offset;
			}

			if ( $new_offset < $total_coupons_number ) {
				$data = array(
					'limit'  => "$limit",
					'offset' => "$new_offset",
					'loop'   => '1',
				);

				wp_send_json( $data );
			} else {
				$data = array(
					'limit'  => "$limit",
					'offset' => "$new_offset",
					'loop'   => '0',
				);
				wp_send_json( $data );
			}
		}

		/**
		 * Render the import cost buttons.
		 */
		public function yith_ywgc_transform_smart_coupons_buttons() {
			?>
				<div id="ywgc_ajax_zone_transform_smart_coupons" class="yith-plugin-fw__panel__section__content">
					<button type="button" class="ywgc_transform_smart_coupons_class button button-primary" id="yith_ywgc_transform_smart_coupons" ><?php echo esc_html__( 'Transfer', 'yith-woocommerce-gift-cards' ); ?></button>
					<span class="yith-plugin-fw__panel__option__description" style="margin-top: 15px;"><?php echo esc_html__( 'Note: this action cannot be undone.', 'yith-woocommerce-gift-cards' ); ?></span>
				</div>
			<?php
		}

		/**
		 * Manage CSS class for the gift cards table rows
		 *
		 * @param array  $classes Array of classes.
		 * @param string $class   Class.
		 * @param int    $post_id Post ID.
		 *
		 * @return array|mixed|void
		 * @since  1.0.0
		 */
		public function add_cpt_table_class( $classes, $class, $post_id ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound
			if ( YWGC_CUSTOM_POST_TYPE_NAME !== get_post_type( $post_id ) ) {
				return $classes;
			}

			$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $post_id ) );

			if ( ! $gift_card->exists() ) {
				return $class;
			}

			$classes[] = $gift_card->status;

			/**
			 * APPLY_FILTERS: yith_gift_cards_table_class
			 *
			 * Filter the gift card table classes.
			 *
			 * @param array $classes the table classes
			 * @param int $post_id the post ID
			 *
			 * @return array
			 */
			return apply_filters( 'yith_gift_cards_table_class', $classes, $post_id );
		}

		/**
		 * Currency Switchers options
		 *
		 * @param array $general_options Array with general options.
		 */
		public function yith_ywgc_general_options_array_custom( $general_options ) {
			if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) ) {
				$aux = array(
					'aelia_currency_switchers_tab_start' => array(
						'type' => 'sectionstart',
						'id'   => 'yith_aelia_currency_switchers_settings_tab_start',
					),
					'aelia_currency_switchers_tab_title' => array(
						'type' => 'title',
						'name' => esc_html__( 'Aelia Currency Switcher integration', 'yith-woocommerce-gift-cards' ),
						'desc' => '',
						'id'   => 'yith_aelia_currency_switchers_tab',
					),
					'enable_aelia_option'                => array(
						'name'      => esc_html__( 'Enable Aelia integration', 'yith-woocommerce-gift-cards' ),
						'type'      => 'yith-field',
						'yith-type' => 'onoff',
						'id'        => 'ywgc_aelia_integration_option',
						'default'   => 'yes',
					),

					'aelia_currency_switchers_tab_end'   => array(
						'type' => 'sectionend',
						'id'   => 'yith_aelia_currency_switchers_settings_tab_end',
					),
				);

				$general_options['settings-general'] = array_merge( $general_options['settings-general'], $aux );
			}

			global $woocommerce_wpml;

			if ( $woocommerce_wpml ) {
				$aux = array(
					'wpml_currency_switchers_tab_start' => array(
						'type' => 'sectionstart',
						'id'   => 'yith_wpml_currency_switchers_settings_tab_start',
					),
					'wpml_currency_switchers_tab_title' => array(
						'type' => 'title',
						'name' => esc_html__( 'WPML Currency Switcher integration', 'yith-woocommerce-gift-cards' ),
						'desc' => '',
						'id'   => 'yith_wpml_currency_switchers_tab',
					),
					'enable_wpml_option'                => array(
						'name'      => esc_html__( 'Enable WPML integration', 'yith-woocommerce-gift-cards' ),
						'type'      => 'yith-field',
						'yith-type' => 'onoff',
						'id'        => 'ywgc_wpml_integration_option',
						'default'   => 'yes',
					),
					'wpml_currency_switchers_tab_end'   => array(
						'type' => 'sectionend',
						'id'   => 'yith_wpml_currency_switchers_settings_tab_end',
					),
				);

				$general_options['settings-general'] = array_merge( $general_options['settings-general'], $aux );
			}

			if ( function_exists( 'YITH_Vendors' ) ) {
				$vendor_options = array(
					'yith_ywgc_wpv_tab_start'           => array(
						'type' => 'sectionstart',
						'id'   => 'yith_ywgc_wpv_tab_start',
					),
					'wpml_currency_switchers_tab_title' => array(
						'type' => 'title',
						'name' => esc_html__( 'YITH WooCommerce Multi Vendor / Marketplace', 'yith-woocommerce-gift-cards' ),
						'desc' => '',
						'id'   => 'yith_ywgc_wpv__tab',
					),
					'enable_wpml_option'                => array(
						'name'      => esc_html__( 'Gift cards vendor restrictions', 'yith-woocommerce-gift-cards' ),
						'desc'      => esc_html__( 'Enable to make sure customers can redeem a gift card only on the products of the associated vendor. E.g. If the customer buys a gift card from vendor X, it can only be redeemed on products by vendor X.', 'yith-woocommerce-gift-cards' ),
						'type'      => 'yith-field',
						'yith-type' => 'onoff',
						'id'        => 'yith_ywgc_wpv_vendor_restriction_option',
						'default'   => 'no',
					),
					'yith_ywgc_wpv_tab_end'             => array(
						'type' => 'sectionend',
						'id'   => 'yith_ywgc_wpv_tab_end',
					),
				);

				$general_options['settings-general'] = array_merge( $general_options['settings-general'], $vendor_options );
			}

			if ( class_exists( 'WC_Smart_Coupons' ) ) {
				$smart_coupons_options = array(
					array(
						'name' => __( 'WooCommerce Smart Coupons integration', 'yith-woocommerce-gift-cards' ),
						'type' => 'title',
					),
					'convert_smart_coupons_tab_button' => array(
						'type'  => 'yith_ywgc_transform_smart_coupons_html',
						'title' => esc_html__( 'Transfer "Store Credit / Gift Certificate" coupons', 'yith-woocommerce-gift-cards' ),
					),
					array(
						'type' => 'sectionend',
					),
				);

				$general_options['settings-general'] = array_merge( $general_options['settings-general'], $smart_coupons_options );
			}

			return $general_options;
		}

		/**
		 * Show the default shipping in the order summary, not the discounted one
		 *
		 * @param float    $shipping    Shipping total.
		 * @param WC_Order $order       Order object.
		 * @param string   $tax_display Excl or incl tax display mode.
		 */
		public function ywgc_display_default_shipping_value( $shipping, $order, $tax_display ) {
			$order_gift_cards = $order->get_meta( '_ywgc_applied_gift_cards' );
			$shipping         = 0;
			$shipping_items   = $order->get_items( 'shipping' );

			foreach ( $shipping_items as $item_id => $item_shipping ) {
				$shipping_data = $item_shipping->get_data();
				$shipping     += $shipping_data['total'] + $shipping_data['total_tax'];
			}

			if ( $order_gift_cards ) {
				$shipping  = wc_price( $shipping, array( 'currency' => $order->get_currency() ) );
				$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>', $order, $tax_display );
				// translators: %s is the order shipping method.
				$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_shipped_via', '&nbsp;<small class="shipped_via">' . sprintf( __( 'via %s', 'woocommerce' ), $order->get_shipping_method() ) . '</small>', $order );
			}

			return $shipping;
		}

		/**
		 * Export gift cards in CSV
		 */
		public function yith_ywgc_run_exporter() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) || ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				die( 'Error' );
			}

			global $wpdb;

			$n_posts = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = %s",
					'gift_card'
				)
			);

			// If we need to export in a specific date range.
			if ( isset( $_POST['date_option'] ) && 'by_date' === $_POST['date_option'] && isset( $_POST['from'] ) && isset( $_POST['to'] ) ) {
				$start_filter = sanitize_text_field( wp_unslash( $_POST['from'] ) );
				$end_filter   = sanitize_text_field( wp_unslash( $_POST['to'] ) );
				$saved_format = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );

				if ( 'MM d, yy' === $saved_format ) {
					$start_filter_formatted = strtotime( $start_filter );
					$end_filter_formatted   = strtotime( $end_filter );
					$start_filter_formatted = date_i18n( 'Y-m-d', $start_filter_formatted );
					$end_filter_formatted   = date_i18n( 'Y-m-d', $end_filter_formatted );
				} else {
					$search                 = array( '.', ', ', '/', ' ', ',', 'MM', 'yy', 'mm', 'dd' );
					$replace                = array( '-', '-', '-', '-', '-', 'M', 'y', 'm', 'd' );
					$saved_format_formatted = str_replace( $search, $replace, $saved_format );
					$start_filter_formatted = str_replace( $search, $replace, $start_filter );
					$end_filter_formatted   = str_replace( $search, $replace, $end_filter );
					$start_filter           = '' !== $start_filter_formatted ? 'mm/dd/yy' !== $saved_format ? gmdate( $saved_format_formatted, strtotime( $start_filter_formatted ) ) : gmdate( $saved_format_formatted, strtotime( $start_filter ) ) : '';
					$end_filter             = '' !== $end_filter_formatted ? 'mm/dd/yy' !== $saved_format ? gmdate( $saved_format_formatted, strtotime( $end_filter_formatted ) ) : gmdate( $saved_format_formatted, strtotime( $end_filter ) ) : '';
					$start_filter           = ! empty( $start_filter ) ? DateTime::createFromFormat( $saved_format_formatted, $start_filter ) : '';

					if ( $start_filter ) {
						$start_filter_formatted = $start_filter->getTimestamp();
						$start_filter_formatted = date_i18n( 'Y-m-d', $start_filter_formatted );
					}

					$end_filter = ! empty( $end_filter ) ? DateTime::createFromFormat( $saved_format_formatted, $end_filter ) : '';

					if ( $end_filter ) {
						$end_filter_formatted = $end_filter->getTimestamp();
						$end_filter_formatted = date_i18n( 'Y-m-d', $end_filter_formatted );
					}
				}
			} else {
				$start_filter_formatted = '';
				$end_filter_formatted   = '';
			}

			$offset     = 0;
			$gift_cards = array();

			while ( $offset < $n_posts ) {
				$args               = array(
					'posts_per_page' => 100,
					'orderby'        => 'title',
					'order'          => 'asc',
					'date_query'     => array(
						array(
							'after'     => $start_filter_formatted,
							'before'    => $end_filter_formatted,
							'inclusive' => true,
						),
					),
					'post_type'      => 'gift_card',
					'post_status'    => apply_filters( 'yith_ywgc_gift_card_status_export', 'publish' ),
					'offset'         => $offset,
				);
				$gift_cards_to_push = get_posts( $args );

				foreach ( $gift_cards_to_push as $gift_card ) {
					$gift_cards[] = $gift_card;
				}

				$offset = $offset + 100;
			}

			$gift_cards_columns_labels = array();

			$order_id          = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			$gift_card_id      = isset( $_POST['gift_card_id'] ) ? sanitize_text_field( wp_unslash( $_POST['gift_card_id'] ) ) : '';
			$gift_card_code    = isset( $_POST['gift_card_code'] ) ? sanitize_text_field( wp_unslash( $_POST['gift_card_code'] ) ) : '';
			$gift_card_amount  = isset( $_POST['gift_card_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['gift_card_amount'] ) ) : '';
			$gift_card_balance = isset( $_POST['gift_card_balance'] ) ? sanitize_text_field( wp_unslash( $_POST['gift_card_balance'] ) ) : '';
			$sender_name       = isset( $_POST['sender_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sender_name'] ) ) : '';
			$recipient_name    = isset( $_POST['recipient_name'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_name'] ) ) : '';
			$recipient_email   = isset( $_POST['recipient_email'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_email'] ) ) : '';
			$message           = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
			$expiration_date   = isset( $_POST['expiration_date'] ) ? sanitize_text_field( wp_unslash( $_POST['expiration_date'] ) ) : '';
			$delivery_date     = isset( $_POST['delivery_date'] ) ? sanitize_text_field( wp_unslash( $_POST['delivery_date'] ) ) : '';
			$internal_note     = isset( $_POST['internal_note'] ) ? sanitize_text_field( wp_unslash( $_POST['internal_note'] ) ) : '';

			if ( 'null' !== $order_id ) {
				$gift_cards_columns_labels['0'][] = __( 'Order ID', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $gift_card_id ) {
				$gift_cards_columns_labels['0'][] = __( 'Gift Card ID', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $gift_card_code ) {
				$gift_cards_columns_labels['0'][] = __( 'Code', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $gift_card_amount ) {
				$gift_cards_columns_labels['0'][] = __( 'Amount', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $gift_card_balance ) {
				$gift_cards_columns_labels['0'][] = __( 'Current balance', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $sender_name ) {
				$gift_cards_columns_labels['0'][] = __( 'Sender\'s name', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $recipient_name ) {
				$gift_cards_columns_labels['0'][] = __( 'Recipient\'s name', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $recipient_email ) {
				$gift_cards_columns_labels['0'][] = __( 'Recipient\'s email', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $message ) {
				$gift_cards_columns_labels['0'][] = __( 'Message', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $expiration_date ) {
				$gift_cards_columns_labels['0'][] = __( 'Expiration date', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $delivery_date ) {
				$gift_cards_columns_labels['0'][] = __( 'Delivery date', 'yith-woocommerce-gift-cards' );
			}

			if ( 'null' !== $internal_note ) {
				$gift_cards_columns_labels['0'][] = __( 'Internal note', 'yith-woocommerce-gift-cards' );
			}

			$date_format              = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );
			$search                   = array( 'MM', 'yy', 'mm', 'dd' );
			$replace                  = array( 'M', 'y', 'm', 'd' );
			$saved_format_formatted   = str_replace( $search, $replace, $date_format );
			$counter                  = 0;
			$total_balance            = 0;
			$formatted_gift_card_data = array();

			foreach ( $gift_cards as $gift_card ) {
				$gift_card_object = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_card->ID ) );

				if ( 'null' !== $order_id ) {
					$formatted_gift_card_data[ $counter ][] = apply_filters( 'yith_ywgc_gift_card_purchase_order_id', $gift_card_object->order_id );
				}

				if ( 'null' !== $gift_card_id ) {
					$formatted_gift_card_data[ $counter ][] = $gift_card->ID;
				}

				if ( 'null' !== $gift_card_code ) {
					$formatted_gift_card_data[ $counter ][] = $gift_card_object->get_code();
				}

				if ( 'null' !== $gift_card_amount ) {
					$formatted_gift_card_data[ $counter ][] = $gift_card_object->total_amount;
				}

				if ( 'null' !== $gift_card_balance ) {
					$formatted_gift_card_data[ $counter ][] = $gift_card_object->get_balance();
					$total_balance                         += $gift_card_object->get_balance();
				}

				if ( 'null' !== $sender_name ) {
					$formatted_gift_card_data[ $counter ][] = $gift_card_object->sender_name;
				}

				if ( 'null' !== $recipient_name ) {
					$formatted_gift_card_data[ $counter ][] = $gift_card_object->recipient_name;
				}

				if ( 'null' !== $recipient_email ) {
					$formatted_gift_card_data[ $counter ][] = $gift_card_object->recipient;
				}

				if ( 'null' !== $message ) {
					$formatted_gift_card_data[ $counter ][] = $gift_card_object->message;
				}

				if ( 'null' !== $expiration_date ) {
					if ( $gift_card_object->expiration > 0 ) {
						$formatted_gift_card_data[ $counter ][] = date_i18n( $saved_format_formatted, $gift_card_object->expiration );
					} else {
						$formatted_gift_card_data[ $counter ][] = '';
					}
				}

				if ( 'null' !== $delivery_date ) {
					if ( $gift_card_object->delivery_date > 0 ) {
						$formatted_gift_card_data[ $counter ][] = date_i18n( $saved_format_formatted, $gift_card_object->delivery_date );
					} elseif ( $gift_card_object->delivery_send_date > 0 ) {
						$formatted_gift_card_data[ $counter ][] = date_i18n( $saved_format_formatted, $gift_card_object->delivery_send_date );
					} else {
						$formatted_gift_card_data[ $counter ][] = '';
					}
				}

				if ( 'null' !== $internal_note ) {
					$formatted_gift_card_data[ $counter ][] = $gift_card_object->internal_notes;
				}

				++$counter;
			}

			/**
			 * APPLY_FILTERS: ywgc_export_gift_cards_data
			 *
			 * Filter the gift card data to be exported in CSV.
			 *
			 * @param array merged array with the gift card data
			 * @param array $gift_cards_columns_labels array with the columns name
			 * @param array $formatted_gift_card_data array with the gift card data
			 * @param array $gift_cards the gift cards to be exported
			 *
			 * @return array
			 */
			$formatted_gift_card_data = apply_filters(
				'ywgc_export_gift_cards_data',
				array_merge( $gift_cards_columns_labels, $formatted_gift_card_data ),
				$gift_cards_columns_labels,
				$formatted_gift_card_data,
				$gift_cards
			);

			if ( apply_filters( 'yith_ywgc_display_total_balance_row_csv', false ) ) {
				$total_balance_array      = array(
					array(
						0 => esc_html__( 'Total active balance:', 'yith-woocommerce-gift-cards' ),
						1 => $total_balance,
					),
				);
				$formatted_gift_card_data = array_merge( $formatted_gift_card_data, $total_balance_array );
			}

			$delimiter = isset( $_POST['csv_delimitier'] ) ? sanitize_text_field( wp_unslash( $_POST['csv_delimitier'] ) ) : ';';

			return ywgc_output_csv( $formatted_gift_card_data, $delimiter );
		}

		/**
		 * Manage import actions
		 */
		public function yith_ywgc_run_importer() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) || ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				die( 'Error' );
			}

			$file_url       = isset( $_POST['file_url'] ) ? sanitize_text_field( wp_unslash( $_POST['file_url'] ) ) : '';
			$file           = wp_check_filetype( $file_url );
			$file_extension = $file['ext'] ?? '';
			$file_type      = $file['type'] ?? '';
			$delimiter      = isset( $_POST['csv_delimitier'] ) ? sanitize_text_field( wp_unslash( $_POST['csv_delimitier'] ) ) : ';';

			if ( 'text/csv' !== $file_type || 'csv' !== $file_extension ) {
				wp_send_json(
					array(
						'code'  => 1,
						'value' => esc_html_x( 'The uploaded file is not a valid CSV file.', 'Import failed message', 'yith-woocommerce-gift-cards' ),
						'image' => YITH_YWGC_ASSETS_URL . '/images/import-failed.svg',
					)
				);
				return;
			}

			$this->import_from_csv( $file_url, $delimiter );
		}

		/**
		 * Import points from a csv file
		 *
		 * @param string $file      File.
		 * @param string $delimiter Delimiter.
		 *
		 * @return mixed|void
		 */
		public function import_from_csv( $file, $delimiter ) {
			$response = '';
			$this->import_start();

			$loop = 0;

			$handle = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

			if ( false !== $handle ) {
				$header = fgetcsv( $handle, 0, $delimiter );

				if ( count( $header ) === 12 ) {
					while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
						if ( ! is_array( $row ) || count( $row ) < 12 ) {
							continue;
						}

						$order_id         = sanitize_text_field( $row[0] );
						$gift_card_id     = sanitize_text_field( $row[1] );
						$gift_card_code   = sanitize_text_field( $row[2] );
						$purchased_amount = sanitize_text_field( $row[3] );
						$current_balance  = sanitize_text_field( $row[4] );
						$sender_name      = sanitize_text_field( $row[5] );
						$recipient_name   = sanitize_text_field( $row[6] );
						$recipient_email  = sanitize_email( $row[7] );
						$message          = sanitize_text_field( $row[8] );
						$expiration_date  = sanitize_text_field( $row[9] );
						$delivery_date    = sanitize_text_field( $row[10] );
						$internal_note    = sanitize_text_field( $row[11] );

						if ( '' === $gift_card_id ) {
							$gift_card = new YWGC_Gift_Card_Premium();

							// For the new imported gift cards, we check if the code exist.
							$check_code = YITH_YWGC()->get_gift_card_by_code( $gift_card_code );

							if ( ! $check_code || is_object( $check_code ) && ! $check_code->ID || apply_filters( 'yith_ywgc_replace_gift_card_code', false ) ) {
								$gift_card->gift_card_number = $gift_card_code;
							} else {
								$gift_card->gift_card_number = YITH_YWGC()->generate_gift_card_code();
							}
						} else {
							$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_card_id ) );

							if ( is_object( $gift_card ) ) {
								$gift_card->gift_card_number = $gift_card_code;
							} else {
								$gift_card->gift_card_number = YITH_YWGC()->generate_gift_card_code();
							}
						}

						if ( '' !== $order_id ) {
							$order = wc_get_order( $order_id );

							if ( is_object( $order ) && $order instanceof WC_Order ) {
								$gift_card->order_id = $order_id;
							}
						}

						$saved_format = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );

						$expiration_date_timestamp = '';
						$delivery_date_timestamp   = '';

						$search                 = array( '.', ', ', '/', ' ', ',', 'MM', 'yy', 'mm', 'dd' );
						$replace                = array( '-', '-', '-', '-', '-', 'M', 'y', 'm', 'd' );
						$saved_format_formatted = str_replace( $search, $replace, $saved_format );

						if ( $delivery_date ) {
							// Delivery date.
							$date_formatted = str_replace( $search, $replace, $delivery_date );
							$delivery_date  = ! empty( $delivery_date ) ? DateTime::createFromFormat( $saved_format_formatted, $date_formatted ) : '';

							if ( $delivery_date ) {
								$delivery_date_timestamp = $delivery_date->getTimestamp();
							}
						}

						if ( $expiration_date ) {
							// Expiration date.
							$date_formatted  = str_replace( $search, $replace, $expiration_date );
							$expiration_date = ! empty( $expiration_date ) ? DateTime::createFromFormat( $saved_format_formatted, $date_formatted ) : '';

							if ( $expiration_date ) {
								$expiration_date_timestamp = $expiration_date->getTimestamp();
							}
						}

						$gift_card->total_amount = $purchased_amount;
						$gift_card->update_balance( $current_balance );
						$gift_card->sender_name    = $sender_name;
						$gift_card->recipient_name = $recipient_name;
						$gift_card->recipient      = $recipient_email;

						if ( $gift_card->recipient ) {
							$gift_card->is_digital = true;
						}

						$gift_card->message       = $message;
						$gift_card->expiration    = $expiration_date_timestamp;
						$gift_card->delivery_date = $delivery_date_timestamp;

						if ( $delivery_date_timestamp ) {
							$gift_card->postdated_delivery = $delivery_date_timestamp;
						}

						$gift_card->internal_notes = $internal_note;

						$gift_card->save();

						++$loop;
					}

					$response = $loop;

					wp_send_json(
						array(
							'code'    => 1,
							'value'   => esc_html_x( 'Import done', 'Import success message', 'yith-woocommerce-gift-cards' ),
							'image'   => YITH_YWGC_ASSETS_URL . '/images/import-done.svg',
							'success' => true,
						)
					);
				} else {
					wp_send_json(
						array(
							'code'  => 1,
							'value' => esc_html_x( 'The CSV is invalid. Check the sample file.', 'Import failed message', 'yith-woocommerce-gift-cards' ),
							'image' => YITH_YWGC_ASSETS_URL . '/images/import-failed.svg',
						)
					);
				}

				fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			}

			return $response;
		}

		/**
		 * Start import
		 *
		 * @return void
		 * @since 1.0.0
		 */
		private function import_start() {
			if ( function_exists( 'gc_enable' ) ) {
				gc_enable();
			}

			// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
			@set_time_limit( 0 );
			@ob_flush();
			@flush();
			@ini_set( 'auto_detect_line_endings', '1' );
			// phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged
		}

		/**
		 * Shows messages if there are update errors
		 */
		public function ywgc_show_upload_error() {
			$admin_notices = $this->admin_notices;

			if ( ! $admin_notices ) {
				return;
			}

			foreach ( $admin_notices as $admin_notice ) {
				printf( '<div class="ywgc_notices %s"><p>%s</p></div>', esc_attr( $admin_notice['class'] ), wp_kses_post( $admin_notice['message'] ) );
			}
		}

		/**
		 * Shows the category images and upload button in the category edit pages
		 *
		 * @param WP_Term $tag      Current taxonomy term object.
		 * @param string  $taxonomy Current taxonomy slug.
		 */
		public function ywgc_include_upload_image_categories( $tag, $taxonomy ) {
			if ( ! isset( $_REQUEST['tag_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			$object_ids = get_objects_in_term( $tag->term_id, YWGC_CATEGORY_TAXONOMY );
			$term       = sanitize_text_field( wp_unslash( $_REQUEST['tag_ID'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$term_obj   = get_term( $term );

			if ( 'all' === $term_obj->slug || 'none' === $term_obj->slug ) {
				return;
			}

			?>
			<label class="ywgc-category-image-title"><?php esc_html_e( 'Images in this category', 'yith-woocommerce-gift-cards' ); ?></label>
			<div class="ywgc-category-images-main-container">
				<?php
				$object_ids_aux = array_reverse( $object_ids );

				foreach ( $object_ids_aux as $item_id ) {
					?>
					<div class="ywgc-category-image" data-design-id="<?php echo esc_attr( $item_id ); ?>" data-design-cat="<?php echo esc_attr( $term ); ?>">
						<span class="dashicons dashicons-no ywgc-category-image-delete" title="<?php esc_html_e( 'Remove image from this category', 'yith-woocommerce-gift-cards' ); ?>"></span>
						<?php
						/**
						 * APPLY_FILTERS: yith_ywgc_preset_image_size
						 *
						 * Filter the size of the gift card images in the categories.
						 *
						 * @param string Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order).
						 *
						 * @return string
						 */
						echo wp_get_attachment_image( intval( $item_id ), apply_filters( 'yith_ywgc_preset_image_size', 'thumbnail' ) );
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php

			$field = array(
				'id'   => 'ywgc-upload-images-cat-edit',
				'type' => 'image-gallery',
				'name' => 'ywgc-edit-images-ids',
			);

			yith_plugin_fw_get_field( $field, true );

			?>
			<p class="ywgc-category-image-description description"><?php esc_html_e( 'Select the images to be included in this category.', 'yith-woocommerce-gift-cards' ); ?></p>
			<?php
		}

		/**
		 * Manage the image upload in the category edit pages
		 */
		public function ywgc_upload_image_actions_from_settings_panel() {
			if ( isset( $_REQUEST['action'], $_POST['_wpnonce'], $_REQUEST['tag_ID'] ) && 'editedtag' === $_REQUEST['action'] && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-tag_' . sanitize_text_field( wp_unslash( $_REQUEST['tag_ID'] ) ) ) ) {
				if ( isset( $_REQUEST['ywgc-edit-images-ids'] ) ) {
					$images_ids       = sanitize_text_field( wp_unslash( $_REQUEST['ywgc-edit-images-ids'] ) );
					$images_ids_array = explode( ',', $images_ids );
					$term_id          = array( sanitize_text_field( wp_unslash( $_REQUEST['tag_ID'] ) ) );

					foreach ( $images_ids_array as $image_id ) {
						wp_set_post_terms( $image_id, $term_id, YWGC_CATEGORY_TAXONOMY );
					}
				}
			}
		}

		/**
		 * Ajax method to delete images from categories in the category edit pages
		 */
		public function ywgc_delete_image_from_category() {
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'delete_image' ) ) {
				wp_send_json( array( 'result' => false ) );
			}

			$image_id = isset( $_POST['image_id'] ) ? intval( $_POST['image_id'] ) : '';
			$cat_id   = isset( $_POST['cat_id'] ) ? intval( $_POST['cat_id'] ) : '';

			if ( '' === $image_id || '' === $cat_id ) {
				return;
			}

			wp_remove_object_terms( (int) $image_id, (int) $cat_id, YWGC_CATEGORY_TAXONOMY );

			wp_send_json( array( 'code' => 1 ) );
		}

		/**
		 * Shows the category images and upload button in the category creation
		 *
		 * @param string $taxonomy Taxonomy slug.
		 */
		public function ywgc_add_form_fields_category_creation( $taxonomy ) {
			$field = array(
				'id'   => 'ywgc-upload-images-cat-creation',
				'type' => 'image-gallery',
				'name' => 'ywgc-uploaded-images-ids',
			);

			?>
			<div class="form-field term-image-wrap">
				<label for="parent"><?php esc_html_e( 'Images in this category', 'yith-woocommerce-gift-cards' ); ?></label>
				<?php yith_plugin_fw_get_field( $field, true ); ?>
				<p><?php esc_html_e( 'Select the images to be included in this category.', 'yith-woocommerce-gift-cards' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Save the category images on category creation
		 *
		 * @param int    $term_id  Term ID.
		 * @param int    $tt_id    Term taxonomy ID.
		 * @param string $taxonomy Taxonomy slug.
		 */
		public function ywgc_save_images_on_category_creation( $term_id, $tt_id = '', $taxonomy = '' ) {
			if ( isset( $_POST['ywgc-uploaded-images-ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$images_ids       = sanitize_text_field( wp_unslash( $_POST['ywgc-uploaded-images-ids'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$images_ids_array = explode( ',', $images_ids );

				foreach ( $images_ids_array as $image_id ) {
					wp_set_post_terms( $image_id, array( $term_id ), YWGC_CATEGORY_TAXONOMY );
				}
			}
		}

		/**
		 * Specify custom bulk actions messages for gift card post type.
		 *
		 * @param WP_Post $post Post object.
		 */
		public function ywgc_generate_automatic_code_in_post( $post ) {
			if ( get_option( 'ywgc_generate_automatic_code', 'no' ) === 'no' ) {
				return;
			}

			if ( get_post_type() !== 'gift_card' ) {
				return;
			}

			if ( '' !== $post->post_title ) {
				return;
			}

			$title = YITH_WooCommerce_Gift_Cards_Premium::get_instance()->generate_gift_card_code();

			?>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$("#title").val("<?php echo wp_kses_post( $title ); ?>");
				});
			</script>
			<?php
		}

		/**
		 * Apply the gift card in the subscription renew
		 *
		 * @param int $order_id        Order ID.
		 * @param int $subscription_id Subscription ID.
		 */
		public function apply_gift_cards_on_subscription_renew( $order_id, $subscription_id ) {
			$renew_order             = wc_get_order( $order_id );
			$subscription            = ywsbs_get_subscription( $subscription_id );
			$parent_order            = $subscription->get_order();
			$parent_order_gift_cards = $parent_order->get_meta( '_ywgc_applied_gift_cards' );
			$parent_coupons          = $parent_order->get_coupons();

			foreach ( $parent_coupons as $coupon ) {
				$args      = array(
					'gift_card_number' => $coupon->get_code(),
				);
				$gift_card = new YITH_YWGC_Gift_Card( $args );

				if ( $gift_card->exists() ) {
					if ( is_array( $parent_order_gift_cards ) ) {
						$parent_order_gift_cards[ $coupon->get_code() ] = (float) $coupon->get_discount() + (float) $coupon->get_discount_tax();
					} else {
						$parent_order_gift_cards                        = array();
						$parent_order_gift_cards[ $coupon->get_code() ] = (float) $coupon->get_discount() + (float) $coupon->get_discount_tax();
					}
				}
			}

			if ( $parent_order_gift_cards ) {
				foreach ( $parent_order_gift_cards as $code => $amount ) {
					$gift_card         = YITH_YWGC()->get_gift_card_by_code( $code );
					$gift_card_balance = $gift_card->get_balance();
					$renew_order_total = $renew_order->get_total();

					if ( $gift_card_balance >= $renew_order_total ) {
						$amount_to_substract_to_gift_card = $renew_order_total;
						$new_balance                      = $gift_card_balance - $amount_to_substract_to_gift_card;

						// translators: %1$s is the gift card code. %2$s is the amount to substract from the gift card. %3$s is the remaining gift card balance.
						$order_note = sprintf( __( 'Renew order paid with the gift card "%1$s" applied to the main subscription order with a value of %2$s. Remaining balance in the gift card: %3$s' ), $code, wc_price( $amount_to_substract_to_gift_card ), wc_price( $new_balance ) );

						$gift_card->update_balance( $new_balance );

						$renew_order->set_total( $renew_order->get_total() - $amount_to_substract_to_gift_card );
						$renew_order->add_order_note( $order_note );
						$renew_order->save();
						$renew_order->payment_complete();
					}
				}
			}
		}

		/**
		 * Show the gift card amounts list
		 *
		 * @param int    $product_id  Product ID.
		 * @param string $currency_id Currency ID.
		 */
		public function show_gift_card_amount_list_by_currency( $product_id, $currency_id ) {
			$gift_card = new WC_Product_Gift_Card( $product_id );

			if ( ! $gift_card->exists() || ! method_exists( $gift_card, 'get_product_amounts_by_currency' ) ) {
				return;
			}

			$amounts = $gift_card->get_product_amounts_by_currency( $currency_id );

			yith_ywgc_get_view( 'gift-cards-show-amount-list-by-currency.php', compact( 'amounts', 'currency_id' ) );
		}

		/**
		 * When the order is completed, generate a card number for every gift card product
		 *
		 * @param int|WC_Order $order      The order which status is changing.
		 * @param string       $old_status Current order status.
		 * @param string       $new_status New order status.
		 */
		public function order_status_changed( $order, $old_status, $new_status ) {
			if ( is_numeric( $order ) ) {
				$order = wc_get_order( $order );
			}

			if ( ! $order || in_array( $order->get_id(), $this->processing_order, true ) ) {
				return;
			}

			$this->processing_order[] = $order->get_id();

			/**
			 * APPLY_FILTERS: yith_ywgc_generate_gift_card_on_order_status
			 *
			 * Filter the order statuses where the gift card will be generated.
			 *
			 * @param array the order statuses. Default: 'completed', 'processing'
			 *
			 * @return array
			 */
			$allowed_status = apply_filters(
				'yith_ywgc_generate_gift_card_on_order_status',
				array( 'completed', 'processing' )
			);

			if ( in_array( $new_status, $allowed_status, true ) ) {
				$this->generate_gift_card_for_order( $order );

				$used_gift_cards = $order->get_meta( '_ywgc_applied_gift_cards' );

				if ( isset( $used_gift_cards ) && ! empty( $used_gift_cards ) ) {
					$checkout_instance = YITH_YWGC_Cart_Checkout_Premium::get_instance();

					foreach ( $used_gift_cards as $gift_card_code => $value ) {
						$gift_card = YITH_YWGC()->get_gift_card_by_code( $gift_card_code );
						$checkout_instance->notify_customer_if_gift_cards_used( $gift_card );
					}
				}
			} elseif ( 'refunded' === $new_status ) {
				$this->change_gift_cards_status_on_order( $order, YITH_YWGC()->order_refunded_action() );
			} elseif ( 'cancelled' === $new_status ) {
				$this->change_gift_cards_status_on_order( $order, YITH_YWGC()->order_cancelled_action() );
			}
		}

		/**
		 * Check if there are pre-printed gift cards that were filled and need to be updated
		 *
		 * @param int      $order_id Order ID.
		 * @param WC_Order $order    Order object.
		 *
		 * @since  1.0.0
		 */
		public function save_pre_printed_gift_card_code( $order_id, $order ) {
			if ( ! isset( $_POST['ywgc-pre-printed-code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				return;
			}

			$codes = array_map( 'sanitize_text_field', wp_unslash( $_POST['ywgc-pre-printed-code'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			foreach ( $codes as $gift_id => $gift_code ) {
				if ( ! empty( $gift_code ) ) {
					$gc = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_id ) );

					$gc->gift_card_number = $gift_code;
					$gc->set_enabled_status( true );
					$gc->save();

					YITH_YWGC_Emails_Premium::get_instance()->send_gift_card_email( $gc );
				}
			}
		}

		/**
		 * Make some redirect based on the current action being performed
		 *
		 * @since  1.0.0
		 */
		public function redirect_gift_cards_link() {
			/**
			 * Check if the user ask for downloading the gift pdf file
			 */
			if ( isset( $_GET[ YWGC_ACTION_DOWNLOAD_PDF ] ) && isset( $_GET['gift-card-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['gift-card-nonce'] ) ), 'gift-card-nonce' ) ) {
				$gift_id   = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
				$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_id ) );

				$new_file = YITH_YWGC()->create_gift_card_pdf_file( $gift_card );

				header( 'Content-type:  application/pdf' );
				header( 'Content-Length: ' . filesize( $new_file ) );
				header( 'Content-Disposition: attachment; filename="' . basename( $new_file ) . '"' );
				readfile( $new_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile

				ignore_user_abort( true );
				( connection_aborted() ? wp_delete_file( $new_file ) : wp_delete_file( $new_file ) );

				exit;
			}

			/**
			 * Check if the user ask for retrying sending the gift card email that are not shipped yet
			 */
			if ( isset( $_GET[ YWGC_ACTION_RETRY_SENDING ] ) ) {
				$gift_card_id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';

				YITH_YWGC_Emails_Premium::get_instance()->send_gift_card_email( $gift_card_id, false );
				$redirect_url = remove_query_arg( array( YWGC_ACTION_RETRY_SENDING, 'id' ) );

				wp_safe_redirect( $redirect_url );
				exit;
			}

			/**
			 * Check if the user ask for enabling/disabling a specific gift cards
			 */
			if ( isset( $_GET[ YWGC_ACTION_ENABLE_CARD ] ) || isset( $_GET[ YWGC_ACTION_DISABLE_CARD ] ) ) {
				$gift_card_id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
				$enabled      = isset( $_GET[ YWGC_ACTION_ENABLE_CARD ] );

				$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_card_id ) );

				if ( ! $gift_card->is_dismissed() ) {
					$current_status = $gift_card->is_enabled();

					if ( $current_status !== $enabled ) {
						$gift_card->set_enabled_status( $enabled );

						/**
						 * DO_ACTION: yith_gift_cards_status_changed
						 *
						 * Trigger when the gift card status is changed.
						 *
						 * @param object $gift_card the gift card object
						 * @param bool $enabled the status of the gift card
						 */
						do_action( 'yith_gift_cards_status_changed', $gift_card, $enabled );
					}

					wp_safe_redirect(
						remove_query_arg(
							array(
								YWGC_ACTION_ENABLE_CARD,
								YWGC_ACTION_DISABLE_CARD,
								'id',
							)
						)
					);
					die();
				}
			}

			if ( ! isset( $_GET['post_type'] ) || ! isset( $_GET['s'] ) ) {
				return;
			}

			if ( 'shop_coupon' !== $_GET['post_type'] ) {
				return;
			}

			if ( preg_match( '/(\w{4}-\w{4}-\w{4}-\w{4})(.*)/i', sanitize_text_field( wp_unslash( $_GET['s'] ) ), $matches ) ) {
				wp_safe_redirect( admin_url( 'edit.php?s=' . $matches[1] . '&post_type=gift_card' ) );
				die();
			}
		}

		/**
		 * Ajax method for the gift card generator
		 */
		public function ywgc_gift_card_generator() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'yith_ywgc_generate_gift_cards' ) || ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				die( 'Error' );
			}

			$quantity = isset( $_POST['quantity'] ) ? floatval( $_POST['quantity'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$amount   = isset( $_POST['amount'] ) ? floatval( $_POST['amount'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			for ( $i = 1; $i <= $quantity; $i++ ) {
				$gift_card = new YWGC_Gift_Card_Premium();

				$gift_card->is_manual_amount = true;

				$attempts = 100;

				do {
					$code       = YITH_YWGC()->generate_gift_card_code();
					$check_code = YITH_YWGC()->get_gift_card_by_code( $code );

					if ( ! $check_code || is_object( $check_code ) && ! $check_code->ID ) {
						$gift_card->gift_card_number = $code;
						break;
					}

					--$attempts;
				} while ( $attempts > 0 );

				if ( ! $attempts ) {
					$gift_card->set_as_code_not_valid();
				}

				$gift_card->total_amount = $amount;
				$gift_card->update_balance( $amount );
				$gift_card->version = YITH_YWGC_VERSION;

				try {
					$usage_expiration      = get_option( 'ywgc_usage_expiration', '' );
					$start_usage_date      = $gift_card->delivery_date ? $gift_card->delivery_date : current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
					$gift_card->expiration = '0' !== $usage_expiration ? strtotime( "+$usage_expiration month", (int) $start_usage_date ) : '0';
				} catch ( Exception $e ) {
					error_log( 'An error occurred setting the expiration date for gift card: ' . $gift_card->gift_card_number ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}

				$gift_card->save();

				update_post_meta( $gift_card->ID, 'generated_in_bulk', '1' );
			}
		}

		/**
		 * Send the individual gift card postponed in date and time
		 *
		 * @param int $gift_card_id Gift card ID.
		 */
		public function ywgc_send_postponed_gift_card( $gift_card_id ) {
			YITH_YWGC_Emails_Premium::get_instance()->send_gift_card_email( $gift_card_id );
		}
	}
}
