<?php
/**
 * Frontend class
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Frontend_Premium' ) ) {
	/**
	 * YITH_YWGC_Frontend_Premium class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Frontend_Premium extends YITH_YWGC_Frontend_Extended {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Frontend_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Popup init
		 *
		 * @var $popup
		 */
		public $popup = null;

		/**
		 * Gift this product modal init
		 *
		 * @var $gift_product_modal
		 */
		public $gift_product_modal = null;

		/**
		 * Add new gift card modal init
		 *
		 * @var $add_new_gift_card_modal
		 */
		public $add_new_gift_card_modal = null;

		/**
		 * Apply the gift cards in the coupon form
		 *
		 * @var $apply_gift_card_on_coupon_form
		 */
		public $apply_gift_card_on_coupon_form;

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
			 * Show the product info under the preview in the Gift this product modal
			 */
			add_action( 'ywgc_form_preview_end_gift_this_product', array( $this, 'show_product_info_under_preview_in_modal' ) );

			/**
			 * Include the hidden info in the default Gift Card product
			 */
			add_action( 'yith_ywgc_gift_this_product_shipping_cost_section', array( $this, 'add_gift_this_product_info_to_default_product' ) );

			/**
			 * Add the input hidden to set if gift this product automatically
			 */
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_give_as_present_link_simple' ) );

			/**
			 * Let the customer choose if disable the gift this product option
			 */
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'yith_wcgc_display_input_hidden_disable_gift_this_product' ), 99 );

			/**
			 * Let the customer to use a product of type WC_Product_Variable  as source for a gift card
			 */
			add_action( 'woocommerce_after_variations_form', array( $this, 'show_give_as_present_link_variable' ), 99 );

			/**
			 * Integration with YITH Product Bundle
			 * Let the customer to use a product of type WC_Product_Yith_Bundle  as source for a gift card
			 */
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_give_as_present_link_product_bundle_product' ), 99 );

			/**
			 * Integration with YITH Bookings
			 * Let the customer to use a product of type WC_Product_Booking  as source for a gift card
			 */
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_give_as_present_link_product_booking_product' ), 100 );

			add_action( 'yith_ywgc_gift_card_delivery_info_section', array( $this, 'show_gift_card_details' ), 15 );

			/**
			 * Enqueue frontend scripts
			 */
			add_action( 'woocommerce_product_query', array( $this, 'hide_from_shop_page' ) );

			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'ywgc_show_gift_this_product_in_shop' ), 15 );

			add_filter( 'woocommerce_checkout_coupon_message', array( $this, 'yith_ywgc_rename_coupon_label' ), 10, 1 );

			add_action( 'template_redirect', array( $this, 'ywgc_init_popup' ), 0 );

			add_filter( 'yith_ywgc_check_gift_card_return', array( $this, 'yith_ywgc_check_gift_card_return_callback' ), 0 );

			add_filter( 'yith_ywgc_check_gift_card_return', array( $this, 'yith_ywgc_check_excluded_product_categories' ), 10, 2 );

			add_action( 'template_redirect', array( $this, 'yith_ywgc_rename_coupon_field_on_cart_template_redirect' ) );

			add_action( 'wp_ajax_ywgc_get_shipping_for_gift_this_product', array( $this, 'ywgc_calculate_shipping_for_gift_this_product' ) );
			add_action( 'wp_ajax_nopriv_ywgc_get_shipping_for_gift_this_product', array( $this, 'ywgc_calculate_shipping_for_gift_this_product' ) );

			add_action( 'wp_ajax_ywgc_add_new_gift_card_my_account', array( $this, 'ywgc_add_new_gift_card_my_account' ) );
			add_action( 'wp_ajax_nopriv_ywgc_add_new_gift_card_my_account', array( $this, 'ywgc_add_new_gift_card_my_account' ) );

			add_action( 'woocommerce_thankyou', array( $this, 'yith_wcgc_woocommerce_auto_complete_order' ) );

			/**
			 * If the product being added to the cart should be used as a gift card, avoid adding it
			 * on cart and use a gift card instead.
			 */
			add_filter( 'woocommerce_add_to_cart_handler', array( $this, 'set_product_type_before_add_to_cart' ), 10, 2 );

			add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'add_gift_this_product_button_for_blocks' ), 10, 3 );

			add_action( 'template_redirect', array( $this, 'wc_blocks_hooks' ), 10 );
		}

		/**
		 * Init the popup for the gallery.
		 */
		public function ywgc_init_popup() {
			if ( 'yes' === get_option( ' ywgc_template_design', 'yes' ) && is_product() ) {
				include 'class-yith-ywgc-popup.php';
				$this->popup = new YITH_YWGC_Popup();
			}

			if ( YITH_YWGC()->allow_product_as_present() && is_product() ) {
				include 'class-yith-ywgc-gift-this-product.php';
				$this->gift_product_modal = new YITH_YWGC_Gift_This_Product();
			}

			if ( is_account_page() ) {
				include 'class-yith-ywgc-add-new-giftcard-my-account.php';
				$this->add_new_gift_card_modal = new YITH_YWGC_Add_New_Gift_Card();
			}
		}

		/**
		 * Display the input hidden to set if disable gift this product
		 */
		public function yith_wcgc_display_input_hidden_disable_gift_this_product() {
			global $product;

			if ( ! $product || ! YITH_YWGC()->allow_product_as_present() ) {
				return;
			}

			$disable_gift_this_product = get_post_meta( $product->get_id(), '_yith_wcgc_disable_gift_this_product', true );

			echo "<input type='hidden' id='yith_wcgc_disable_gift_this_product' value='" . esc_attr( $disable_gift_this_product ) . "'>";
		}

		/**
		 * Gift this product button on the shop loop.
		 */
		public function ywgc_show_gift_this_product_in_shop() {
			/**
			 * APPLY_FILTERS: yith_wc_gift_this_product_shop_page_product_filter
			 *
			 * Filter the product object in the shop page to manage the "Gift this product" button.
			 *
			 * @param object the product object
			 *
			 * @return object
			 */
			$product = apply_filters( 'yith_wc_gift_this_product_shop_page_product_filter', wc_get_product() );

			/**
			 * APPLY_FILTERS: yith_ywgc_give_product_as_present
			 *
			 * Filter the condition to display the "Gift this product" feature in the products.
			 *
			 * @param bool true to display it, false to not. Default: true
			 * @param object the product object
			 *
			 * @return bool
			 */
			if ( $product && apply_filters( 'yith_ywgc_give_product_as_present', true, $product ) && ( $product->is_in_stock() && $product->get_type() !== 'gift-card' ) && ( get_option( 'ywgc_permit_its_a_present_shop_page' ) === 'yes' ) && YITH_YWGC()->allow_product_as_present() && ( get_post_meta( $product->get_id(), '_yith_wcgc_disable_gift_this_product', true ) !== 'yes' ) && ( $product->is_type( array( 'simple', 'variable', 'yith_bundle' ) ) || ( defined( 'YITH_WCBK' ) && $product->is_type( 'booking' ) ) ) ) {
				/**
				 * APPLY_FILTERS: yith_wc_gift_this_product_shop_page_class_filter
				 *
				 * Filter the "Gift this product" button class on the shop page.
				 *
				 * @param string the "Gift this product" button class
				 *
				 * @return string
				 */
				?>
				<div class="ywgc-gift-this-product-shop-container">
					<a href="<?php echo esc_html( get_permalink( $product->get_id() ) . '?yith-gift-this-product-form=yes' ); ?>" style="text-align: center" class="<?php echo esc_html( apply_filters( 'yith_wc_gift_this_product_shop_page_class_filter', 'button yith_wc_gift_this_product_shop_page_class' ) ); ?>" rel="nofollow"><?php echo esc_html( YITH_YWGC()->ywgc_gift_this_product_label() ); ?></a>
				</div>
				<?php
			}
		}

		/**
		 * Hide the temporary gift card product from being shown on shop page.
		 *
		 * @param WP_Query $query The current query.
		 *
		 * @since  1.0.0
		 */
		public function hide_from_shop_page( $query ) {
			if ( $query instanceof WP_Query && YITH_YWGC()->default_gift_card_id ) {
				$query->set( 'post__not_in', array( YITH_YWGC()->default_gift_card_id ) );
			}
		}

		/**
		 * Show the gift card product frontend template
		 */
		public function show_gift_card_product_template() {
			global $product;

			if ( 'gift-card' === $product->get_type() ) {
				$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

				$template_path = 'v2' === get_option( 'ywgc_page_layout_version', 'v1' ) || is_object( $default_gift_product ) && $product->get_id() === $default_gift_product->get_id() ? 'yith-gift-cards/layouts/2.0/gift-card.php' : 'single-product/add-to-cart/gift-card.php';

				wc_get_template(
					$template_path,
					array(
						'product' => $product,
					),
					'',
					trailingslashit(
						YITH_YWGC_TEMPLATES_DIR
					)
				);
			}
		}

		/**
		 * Show_amount_selection
		 *
		 * @param  mixed $product product.
		 * @return void
		 */
		public function show_amount_selection( $product ) {
			if ( $this->is_manual_amount_allowed( $product ) === 'disabled-product-level' && $this->is_manual_amount_allowed( $product ) !== '1' || $this->is_manual_amount_allowed( $product ) === '' || $this->is_manual_amount_allowed( $product ) === 'no' ) {
				$manual_amount = false;
			} else {
				$manual_amount = true;
			}

			$template_path = 'v2' === get_option( 'ywgc_page_layout_version', 'v1' ) ? 'yith-gift-cards/layouts/2.0/gift-card-amount-selection.php' : 'single-product/add-to-cart/gift-card-amount-selection.php';

			wc_get_template(
				$template_path,
				array(
					'product'       => $product,
					'amounts'       => $product->get_amounts_to_be_shown(),
					'manual_amount' => $manual_amount,
				),
				'',
				trailingslashit(
					YITH_YWGC_TEMPLATES_DIR
				)
			);
		}

		/**
		 * Add frontend style to gift card product page
		 *
		 * @since  1.0
		 */
		public function enqueue_frontend_script() {
			/**
			 * APPLY_FILTERS: yith_ywgc_do_eneuque_frontend_scripts
			 *
			 * Filter the condition to enqueue the frontend scripts.
			 *
			 * @param bool true to load it anywhere, false to only load in the specific pages. Default: false
			 *
			 * @return bool
			 */
			if ( is_product() || is_cart() || is_checkout() || is_account_page() || apply_filters( 'yith_ywgc_do_eneuque_frontend_scripts', false ) ) {
				global $post;

				if ( is_product() ) {
					$product = wc_get_product( $post->ID );

					if ( ( $product->get_type() !== 'gift-card' && ! YITH_YWGC()->allow_product_as_present() ) && ! apply_filters( 'yith_ywgc_do_eneuque_frontend_scripts', false ) ) {
						return;
					}
				}

				wp_register_script( 'yith_ywgc_timepicker', YITH_YWGC_SCRIPT_URL . 'jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-datepicker' ), YITH_YWGC_ENQUEUE_VERSION, true );

				wp_register_script( 'accounting', WC()->plugin_url() . yit_load_js_file( '/assets/js/accounting/accounting.js' ), array( 'jquery' ), '0.4.2', true );

				$frontend_deps = array(
					'jquery',
					'woocommerce',
					'jquery-ui-datepicker',
					'accounting',
					'wp-util',
				);

				if ( is_product() ) {
					$frontend_deps[] = 'yith_ywgc_timepicker';
				}

				if ( is_cart() || is_checkout() ) {
					$frontend_deps = array();
				}

				/**
				 * APPLY_FILTERS: yith_ywgc_enqueue_script_source_path
				 *
				 * Filter the frontend.js script source path.
				 *
				 * @param string the source path
				 *
				 * @return string
				 */
				// register and enqueue ajax calls related script file.
				wp_register_script(
					'ywgc-frontend-script',
					apply_filters( 'yith_ywgc_enqueue_script_source_path', YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-frontend.js' ) ),
					$frontend_deps,
					YITH_YWGC_ENQUEUE_VERSION,
					true
				);

				wp_register_script(
					'ywgc-upload-field-handler',
					YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-upload-field-handler.js' ),
					$frontend_deps,
					YITH_YWGC_ENQUEUE_VERSION,
					true
				);

				if ( is_product() ) {
					$product = new WC_Product_Gift_Card( $post->ID );

					$override_settings = $product->get_override_global_settings_status();

					if ( 'yes' === $override_settings ) {
						$manual_minimal_amount = get_post_meta( $post->ID, '_ywgc_minimal_manual_amount', true );
						$manual_maximum_amount = get_post_meta( $post->ID, '_ywgc_maximum_manual_amount', true );
					} else {
						$manual_minimal_amount = get_option( 'ywgc_minimal_amount_gift_card', '' );
						$manual_maximum_amount = get_option( 'ywgc_maximum_amount_gift_card', '' );
					}

					if ( is_numeric( $manual_minimal_amount ) ) {
						/**
						 * APPLY_FILTERS: ywgc_minimal_amount_error_text
						 *
						 * Filter the minimal amount error text on the manual amount field.
						 *
						 * @param string the error text
						 *
						 * @return string
						 */
						$manual_minimal_amount_error = apply_filters( 'ywgc_minimal_amount_error_text', esc_html__( 'The minimum amount is', 'yith-woocommerce-gift-cards' ) . ' ' . wc_price( $manual_minimal_amount ), $manual_minimal_amount );
					} else {
						$manual_minimal_amount_error = '';
					}

					if ( is_numeric( $manual_maximum_amount ) ) {
						/**
						 * APPLY_FILTERS: ywgc_maximum_amount_error_text
						 *
						 * Filter the maximum amount error text on the manual amount field.
						 *
						 * @param string the error text
						 *
						 * @return string
						 */
						$manual_maximum_amount_error = apply_filters( 'ywgc_maximum_amount_error_text', esc_html__( 'The maximum amount is', 'yith-woocommerce-gift-cards' ) . ' ' . wc_price( $manual_maximum_amount ), $manual_maximum_amount );
					} else {
						$manual_maximum_amount_error = '';
					}
				} else {
					$manual_minimal_amount       = '';
					$manual_minimal_amount_error = '';
					$manual_maximum_amount       = '';
					$manual_maximum_amount_error = '';
				}

				$default_color     = defined( 'YITH_PROTEO_VERSION' ) ? get_theme_mod( 'yith_proteo_main_color_shade', '#448a85' ) : '#000000';
				$plugin_main_color = get_option( 'ywgc_plugin_main_color', $default_color );
				$date_format       = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );
				$timezone          = null;

				$yith_show_gift_this_product_form = sanitize_text_field( wp_unslash( $_REQUEST['yith-gift-this-product-form'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$enfold = 'Enfold' === wp_get_theme()->get( 'Name' ) || 'Enfold Child' === wp_get_theme()->get( 'Name' ) ? 'yes' : 'no';

				$frontend_data = apply_filters(
					'yith_ywgc_frontend_script_data',
					array(
						'loader'                          => apply_filters( 'yith_gift_cards_loader', YITH_YWGC_ASSETS_URL . '/images/loading.gif' ),
						'ajax_url'                        => admin_url( 'admin-ajax.php' ),
						'currency'                        => get_woocommerce_currency_symbol(),
						'currency_position'               => get_option( 'woocommerce_currency_pos' ),
						'custom_image_max_size'           => get_option( 'ywgc_custom_image_max_size', 1 ),
						'invalid_image_extension'         => esc_html__( 'File format is not valid, select a jpg, jpeg or png file', 'yith-woocommerce-gift-cards' ),
						'invalid_image_size'              => esc_html__( 'The size for the uploaded file exceeds the maximum allowed', 'yith-woocommerce-gift-cards' ) . ' (' . get_option( 'ywgc_custom_image_max_size', 1 ) . ' MB)',
						'multiple_recipient'              => esc_html__( "<b>Note</b>: You added more than one recipient, so <i class='ywgc-darkred-text'>you will buy %number_gift_cards% gift cards</i> and each recipient will receive a different gift card.", 'yith-woocommerce-gift-cards' ),
						'wc_ajax_url'                     => WC_AJAX::get_endpoint( '%%endpoint%%' ),
						'gift_card_nonce'                 => wp_create_nonce( 'apply-gift-card' ),
						// For accounting JS.
						'currency_format'                 => esc_attr(
							str_replace(
								array( '%1$s', '%2$s' ),
								array(
									'%s',
									'%v',
								),
								get_woocommerce_price_format()
							)
						),
						'mon_decimal_point'               => wc_get_price_decimal_separator(),
						'currency_format_num_decimals'    => wc_get_price_decimals(),
						'currency_format_symbol'          => get_woocommerce_currency_symbol(),
						'currency_format_decimal_sep'     => esc_attr( wc_get_price_decimal_separator() ),
						'currency_format_thousand_sep'    => esc_attr( wc_get_price_thousand_separator() ),
						'manual_amount_wrong_format'      => sprintf(
							apply_filters(
								'yith_ywgc_manual_amount_wrong_format_text',
								esc_html__( "Please use only digits and the decimal separator '%1\$s'. Valid examples are '123', '123%1\$s9 and '123%1\$s99'.", 'yith-woocommerce-gift-cards' )
							),
							wc_get_price_decimal_separator(),
							wc_get_price_decimal_separator(),
							wc_get_price_decimal_separator()
						),
						'manual_minimal_amount'           => $manual_minimal_amount,
						'manual_minimal_amount_error'     => $manual_minimal_amount_error,
						'manual_maximum_amount'           => $manual_maximum_amount,
						'manual_maximum_amount_error'     => $manual_maximum_amount_error,
						'email_bad_format'                => esc_html__( 'Please enter a valid email address', 'yith-woocommerce-gift-cards' ),
						'mandatory_email'                 => YITH_YWGC()->mandatory_recipient(),
						'label_name'                      => apply_filters( 'ywgc_recipient_name_label', esc_html__( 'Name', 'yith-woocommerce-gift-cards' ) ),
						'label_email'                     => apply_filters( 'ywgc_recipient_email_label', esc_html__( 'Email', 'yith-woocommerce-gift-cards' ) ),
						'notice_target'                   => apply_filters( 'yith_ywgc_gift_card_notice_target', 'div.ywgc_enter_code' ),
						'add_gift_text'                   => apply_filters( 'yith_gift_card_layout_add_gift_button_text', esc_html__( 'Buy gift card', 'yith-woocommerce-gift-cards' ) ),
						'min_date'                        => apply_filters( 'ywgc_calendar_min_date', 0 ),
						'max_date'                        => apply_filters( 'ywgc_calendar_max_date', null ),
						'date_format'                     => $date_format,
						'time_format'                     => apply_filters( 'ywgc_calendar_time_format', 'HH:mm Z' ),
						'timezone'                        => ! is_null( apply_filters( 'ywgc_calendar_timezone_offset', $timezone ) ) ? $timezone * 60 : null,
						'hour_text'                       => apply_filters( 'ywgc_timepicker_set_time_text', esc_html__( 'Set time', 'yith-woocommerce-gift-cards' ) ),
						'close_text'                      => apply_filters( 'ywgc_timepicker_save_text', esc_html__( 'Save', 'yith-woocommerce-gift-cards' ) ),
						'current_text'                    => apply_filters( 'ywgc_calendar_current_text', esc_html__( 'Now', 'yith-woocommerce-gift-cards' ) ),
						'default_hour'                    => apply_filters( 'ywgc_calendar_default_hour', '0' ),
						'default_minutes'                 => apply_filters( 'ywgc_calendar_default_minutes', '0' ),
						'today_selected_message_div'      => '<div class="ywgc-today-date-selected-message">' . sprintf( apply_filters( 'ywgc_today_selected_message', esc_html__( 'The gift card will be delivered %s, immediately after the payment.', 'yith-woocommerce-gift-cards' ) ),'<strong>' . esc_html__( 'today', 'yith-woocommerce-gift-cards' ) . '</strong>' ) . '</div>',
						'clear_button_text'               => apply_filters( 'ywgc_send_now_text_in_calendar', esc_html__( 'Send now', 'yith-woocommerce-gift-cards' ) ),
						'plugin_main_color'               => $plugin_main_color,
						'gift_this_product_automatically' => $yith_show_gift_this_product_form,
						'enfold'                          => $enfold,
						'select2'                         => apply_filters( 'yith_ywgc_frontend_select2_load', 'yes' ),
						'shipping_in_gift_this_product'   => get_option( 'ywgc_gift_this_product_include_shipping', 'no' ),
						'fixed_shipping'                  => get_option( 'ywgc_gift_this_product_include_shipping_fixed', 'no' ),
						'fixed_shipping_value'            => get_option( 'ywgc_gift_this_product_fixed_shipping_value', '0' ),
						'v2_layout'                       => get_option( 'ywgc_page_layout_version', 'v1' ),
						'is_product'                      => is_product(),
					)
				);

				/**
				 * APPLY_FILTERS: yith_ywgc_manual_amount_wrong_format_text
				 *
				 * Filter the manual amount wrong format error text.
				 *
				 * @param string the error text
				 *
				 * @return string
				 */
				wp_localize_script(
					'ywgc-frontend-script',
					'ywgc_data',
					$frontend_data
				);

				wp_enqueue_script( 'ywgc-frontend-script' );

				if ( is_product() ) {
					/**
					 * Script to manage the Gift this product Modal events
					 */
					wp_register_script(
						'ywgc-gift-this-product',
						YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-gift-this-product.js' ),
						$frontend_deps,
						YITH_YWGC_ENQUEUE_VERSION,
						true
					);

					wp_localize_script(
						'ywgc-gift-this-product',
						'ywgc_data',
						$frontend_data
					);

					wp_enqueue_script( 'ywgc-gift-this-product' );
				}

				if ( $this->gift_product_modal ) {
					wp_register_script(
						'ywgc-gift-this-product-modal-handler',
						YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-gift-this-product-modal-handler.js' ),
						$frontend_deps,
						YITH_YWGC_ENQUEUE_VERSION,
						true
					);

					wp_localize_script(
						'ywgc-gift-this-product-modal-handler',
						'ywgc_gift_this_product_data',
						array(
							'loader'     => apply_filters( 'yith_gift_cards_loader', YITH_YWGC_ASSETS_URL . '/images/loading.gif' ),
							'ajax_url'   => admin_url( 'admin-ajax.php' ),
							'popupWidth' => '100%',
						)
					);

					wp_enqueue_script( 'ywgc-gift-this-product-modal-handler' );
				}

				/**
				 * POPUP script
				 */
				if ( $this->popup ) {
					wp_register_script(
						'ywgc_popup_handler_js',
						YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-popup-handler.js' ),
						array( 'jquery', 'wp-util', 'jquery-blockui' ),
						YITH_YWGC_ENQUEUE_VERSION,
						true
					);

					wp_enqueue_script( 'ywgc_popup_handler_js' );

					/**
					 * APPLY_FILTERS: ywgc_popup_handler_js_script_data
					 *
					 * Filter the popup handler script data.
					 *
					 * @param array the script data
					 *
					 * @return array
					 */
					wp_localize_script(
						'ywgc_popup_handler_js',
						'ywgc_popup_data',
						apply_filters(
							'ywgc_popup_handler_js_script_data',
							array(
								'popupWidth' => '100%',
								'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
								'loader'     => YITH_YWGC_ASSETS_URL . '/images/loading.gif',
							)
						)
					);

					wp_localize_script(
						'ywgc-upload-field-handler',
						'ywgc_field_data',
						array(
							'admin_url' => admin_url( 'admin.php' ),
							'ajax_url'  => admin_url( 'admin-ajax.php' ),
						)
					);

					if ( is_product() ) {
						wp_enqueue_script( 'yith_ywgc_timepicker' );
						wp_enqueue_script( 'ywgc-upload-field-handler' );
					}
				}

				if ( $this->add_new_gift_card_modal ) {
					wp_register_script(
						'ywgc-add-new-gift-card-modal-handler',
						YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-add-new-gift-card-modal-handler.js' ),
						array( 'jquery', 'wp-util', 'jquery-blockui' ),
						YITH_YWGC_ENQUEUE_VERSION,
						true
					);

					wp_localize_script(
						'ywgc-add-new-gift-card-modal-handler',
						'ywgc_add_new_gift_card_data',
						array(
							'loader'     => apply_filters( 'yith_gift_cards_loader', YITH_YWGC_ASSETS_URL . '/images/loading.gif' ),
							'ajax_url'   => admin_url( 'admin-ajax.php' ),
							'popupWidth' => '100%',
						)
					);

					wp_enqueue_script( 'ywgc-add-new-gift-card-modal-handler' );
				}
			}
		}

		/**
		 * Add frontend style to gift card product page
		 *
		 * @since  1.0
		 */
		public function enqueue_frontend_style() {
			global $post;

			if ( is_product() || is_cart() || is_checkout() || is_shop() || is_account_page() || apply_filters( 'yith_ywgc_do_eneuque_frontend_scripts', false ) ) {
				if ( is_product() ) {
					$product = wc_get_product( $post->ID );

					if ( ( $product instanceof WC_Product && $product->get_type() !== 'gift-card' && ! YITH_YWGC()->allow_product_as_present() ) && ! apply_filters( 'yith_ywgc_do_eneuque_frontend_scripts', false ) ) {
						return;
					}
				}

				if ( is_cart() ) {
					if ( get_option( 'ywgc_gift_card_form_on_cart', 'yes' ) === 'no' ) {
						return;
					}
				}

				if ( is_checkout() ) {
					if ( get_option( 'ywgc_gift_card_form_on_checkout', 'yes' ) === 'no' ) {
						return;
					}
				}

				/**
				 * APPLY_FILTERS: ywgc_enqueue_frontend_styles
				 *
				 * Filter the condition to enqueue the frontend styles.
				 *
				 * @param bool true to load the styles, false to not. Default: true
				 *
				 * @return bool
				 */
				if ( apply_filters( 'ywgc_enqueue_frontend_styles', true ) ) {
					wp_enqueue_style(
						'ywgc-frontend',
						YITH_YWGC_ASSETS_URL . '/css/ywgc-frontend.css',
						array(),
						YITH_YWGC_ENQUEUE_VERSION
					);

					/**
					 * APPLY_FILTERS: yith_ywgc_enqueue_jquery_ui_css
					 *
					 * Filter the condition to enqueue the jQuery UI styles.
					 *
					 * @param bool true to load the styles, false to not. Default: true
					 *
					 * @return bool
					 */
					if ( apply_filters( 'yith_ywgc_enqueue_jquery_ui_css', true ) ) {
						wp_enqueue_style(
							'jquery-ui-css',
							'//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css',
							array(),
							'1.11.4'
						);
					}

					if ( $this->popup ) {
						wp_register_style( 'ywgc_popup_style_css', YITH_YWGC_ASSETS_URL . '/css/ywgc-popup-style.css', array(), YITH_YWGC_ENQUEUE_VERSION, 'all' );

						wp_enqueue_style( 'ywgc_popup_style_css' );
					}

					if ( $this->gift_product_modal ) {
						wp_register_style( 'ywgc_gift_this_product_modal_style_css', YITH_YWGC_ASSETS_URL . '/css/ywgc-gift-product-modal-style.css', array(), YITH_YWGC_ENQUEUE_VERSION, 'all' );

						wp_enqueue_style( 'ywgc_gift_this_product_modal_style_css' );
					}

					if ( $this->add_new_gift_card_modal ) {
						wp_register_style( 'ywgc_add_new_gift_card_modal_style_css', YITH_YWGC_ASSETS_URL . '/css/ywgc-add-new-gift-card-modal-style.css', array(), YITH_YWGC_ENQUEUE_VERSION, 'all' );

						wp_enqueue_style( 'ywgc_add_new_gift_card_modal_style_css' );
					}

					wp_add_inline_style( 'ywgc-frontend', $this->get_custom_css() );
				}

				if ( is_product() ) {
					$disable_gift_this_product = get_post_meta( $post->ID, '_yith_wcgc_disable_gift_this_product', true );

					// Enqueue the styles for the 2.0 layout.
					if ( 'v2' === get_option( 'ywgc_page_layout_version', 'v1' ) ) {
						wp_enqueue_style(
							'ywgc-product-layout-2',
							YITH_YWGC_ASSETS_URL . '/css/layouts/ywgc-product-layout-2.css',
							array(),
							YITH_YWGC_ENQUEUE_VERSION
						);
					} else {
						wp_enqueue_style(
							'ywgc-product-layout-1',
							YITH_YWGC_ASSETS_URL . '/css/layouts/ywgc-product-layout-1.css',
							array(),
							YITH_YWGC_ENQUEUE_VERSION
						);
					}
				}
			}

			if ( $post && ( has_shortcode( $post->post_content, 'yith_gift_card_check_balance_form' ) || has_shortcode( $post->post_content, 'yith_redeem_gift_card_form' ) ) ) {
				wp_enqueue_style(
					'ywgc-shortcodes',
					YITH_YWGC_ASSETS_URL . '/css/ywgc-shortcodes.css',
					array(),
					YITH_YWGC_ENQUEUE_VERSION
				);
			}
		}

		/**
		 * Add custom styles in frontend
		 */
		public function get_custom_css() {
			$custom_css        = '';
			$default_color     = defined( 'YITH_PROTEO_VERSION' ) ? get_theme_mod( 'yith_proteo_main_color_shade', '#448a85' ) : '#000000';
			$plugin_main_color = get_option( 'ywgc_plugin_main_color', $default_color );

			list($r, $g, $b) = sscanf( $plugin_main_color, '#%02x%02x%02x' );

			$gift_this_product_button_colors_default = array(
				'default'      => '#ffffff',
				'hover'        => '#ffffff',
				'default_text' => '#448A85',
				'hover_text'   => '#1A4E43',
			);

			$gift_this_product_button_colors_array = get_option( 'ywgc_gift_this_product_colors', $gift_this_product_button_colors_default );

			$form_button_colors_default = array(
				'default'      => '#448a85',
				'hover'        => '#4ac4aa',
				'default_text' => '#ffffff',
				'hover_text'   => '#ffffff',
			);

			$form_colors_default = array(
				'default'      => '#ffffff',
				'hover'        => '#ffffff',
				'default_text' => '#000000',
				'hover_text'   => '#000000',
			);

			$form_button_colors_array = get_option( 'ywgc_apply_gift_cards_button_colors', $form_button_colors_default );
			$form_colors_array        = get_option( 'ywgc_apply_gift_cards_colors', $form_colors_default );

			if ( is_array( $gift_this_product_button_colors_array ) ) {
				$custom_css .= "
					#give-as-present,
					.yith_wc_gift_this_product_shop_page_class {
						background-color: {$gift_this_product_button_colors_array['default']} !important;
						color:{$gift_this_product_button_colors_array['default_text']} !important;
					}
					#give-as-present:hover,
					.yith_wc_gift_this_product_shop_page_class:hover {
						background-color:{$gift_this_product_button_colors_array['hover']} !important;
						color:{$gift_this_product_button_colors_array['hover_text']} !important;
					}
					#ywgc-cancel-gift-card {
						background-color:{$gift_this_product_button_colors_array['default']};
						color:{$gift_this_product_button_colors_array['default_text']};
					}
					#ywgc-cancel-gift-card:hover {
						background-color:{$gift_this_product_button_colors_array['hover']};
						color:{$gift_this_product_button_colors_array['hover_text']};
					}
				";
			}

			if ( is_array( $form_button_colors_array ) ) {
				$custom_css .= "
					.ywgc_apply_gift_card_button{
						background-color:{$form_button_colors_array['default']} !important;
						color:{$form_button_colors_array['default_text']}!important;
					}
					.ywgc_apply_gift_card_button:hover{
						background-color:{$form_button_colors_array['hover']}!important;
						color:{$form_button_colors_array['hover_text']}!important;
					}
				";
			}

			if ( is_array( $form_colors_array ) ) {
				$custom_css .= "
					.ywgc_enter_code{
						background-color:{$form_colors_array['default']};
						color:{$form_colors_array['default_text']};
					}
					.ywgc_enter_code:hover{
						background-color:{$form_colors_array['default']};
						color: {$form_colors_array['default_text']};
					}
				";
			}

			$custom_css .= "
					.gift-cards-list button{
						border: 1px solid {$plugin_main_color};
					}
					.selected_image_parent{
						outline: 2px dashed {$plugin_main_color} !important;
						outline-offset: -2px;
					}
					.ywgc-preset-image.selected_image_parent:after{
						background-color: {$plugin_main_color};
					}
					.selected_button{
						background-color: {$plugin_main_color} !important;
						color: white !important;
					}
					.selected_button .ywgc-manual-currency-symbol{
						background-color: {$plugin_main_color} !important;
						color: white !important;
					}
					.selected_button #ywgc-manual-amount{
						background-color: {$plugin_main_color} !important;
						color: white !important;
					}
					.ywgc-on-sale-text{
						color:{$plugin_main_color};
					}
					.ywgc-choose-image.ywgc-choose-template:hover{
						background: rgba({$r}, {$g}, {$b}, 0.9);
					}
					.ywgc-choose-image.ywgc-choose-template{
						background: rgba({$r}, {$g}, {$b}, 0.8);
					}
					.ywgc-form-preview-separator{
						background-color: {$plugin_main_color};
					}
					.ywgc-form-preview-amount{
						color: {$plugin_main_color};
					}
					.ywgc-manual-amount-container, .ywgc-normal-border{
						border: 1px solid {$plugin_main_color};
					}
					.ywgc-template-categories a:hover,
					.ywgc-template-categories a.ywgc-category-selected{
						color: {$plugin_main_color};
					}
					.ywgc-design-list-modal .ywgc-preset-image:before {
						background-color: {$plugin_main_color};
					}
		   	";

			if ( class_exists( 'Storefront' ) ) {
				$custom_css .= '
					#ywgc-choose-design-preview .ywgc-design-list > ul{
						display: contents;
					}
				';
			}

			if ( 'Enfold' === wp_get_theme()->get( 'Name' ) || 'Enfold Child' === wp_get_theme()->get( 'Name' ) ) {
				$custom_css .= '
					.ywgc-amount-buttons{
						width: 8em;
						height: 3em;
					}
					.gift-card-content-editor.step-content input {
						width: 70% !important;
					}
					.gift-card-content-editor.step-content textarea {
						width: 70% !important;
					}

					.ywgc-sender-info-title{
						margin-top: 8em !important;
					}
				';
			}

			if ( 'Twenty Twenty' === wp_get_theme()->get( 'Name' ) || 'Twenty Twenty Child' === wp_get_theme()->get( 'Name' ) || 'WooPress' === wp_get_theme()->get( 'Name' ) || 'WooPress Child' === wp_get_theme()->get( 'Name' ) ) {
				$custom_css .= '
					.ywgc-currency-symbol {
						margin-left: 0;
						position: relative;
						top: -32px;
						left: 7px;
					}
				';
			}

			if ( 'Avada' === wp_get_theme()->get( 'Name' ) || 'Avada Child' === wp_get_theme()->get( 'Name' ) ) {
				$custom_css .= '
					.ywgc-currency-symbol {
						margin-left: -120px;
					}
				';
			}

			$currency_pos = get_option( 'woocommerce_currency_pos' );

			if ( 'v2' === get_option( 'ywgc_page_layout_version', 'v1' ) ) {
				if ( 'left' === $currency_pos || 'left_space' === $currency_pos ) {
					$custom_css .= '
						#ywgc-manual-amount-div-wrapper{
					    	position: relative;
					    	height: 100%;
						}
						.ywgc-manual-currency-symbol {
							background: white;
							position: absolute;
							left: 8px;
							top: 14px;
							padding: 5px;
							pointer-events: none;
						}
						.ywgc-manual-currency-symbol.right {
							display: none;
						}
					';
				} else {
					$custom_css .= '
						#ywgc-manual-amount-div-wrapper{
							position: relative;
							height: 100%;
						}
						.ywgc-manual-currency-symbol {
							background: white;
							position: absolute;
							right: 8px;
							top: 14px;
							padding: 5px;
							pointer-events: none;
						}
						.ywgc-manual-currency-symbol.left {
							display: none;
						}
					';
				}
			} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
				if ( 'left' === $currency_pos || 'left_space' === $currency_pos ) {
					$custom_css .= '
						.ywgc-manual-currency-symbol {
							padding: 10px;
							background-color: #f6f6f6;
						}
						.ywgc-manual-currency-symbol.right {
							display: none;
						}
					';
				} else {
					$custom_css .= '
						.ywgc-manual-currency-symbol {
							padding: 10px;
							background-color: #f6f6f6;
						}
						.ywgc-manual-currency-symbol.left {
							display: none;
						}
					';
				}
			}

			$custom_css .= "
				#ui-datepicker-div.ywgc-date-picker .ui-datepicker-buttonpane.ui-widget-content button{
					background-color: {$plugin_main_color} !important;
				}
		   	";

			if ( 'yes' !== get_option( 'ywgc_display_price', 'no' ) ) {
				$custom_css .= '
               		.product-type-gift-card .summary .price {
                    	display: none;
                	}
                ';
			}

			$image_size = yith_plugin_fw_get_dimensions_by_option( 'ywgc_template_design_size' );

			$image_size_width  = isset( $image_size['width'] ) ? $image_size['width'] : '150px';
			$image_size_height = isset( $image_size['height'] ) ? $image_size['height'] : '150px';

			$custom_css .= "
                .ywgc-design-list .ywgc-preset-image img, .ywgc-preset-image.ywgc-preset-image-view-all, .ywgc-preset-image.ywgc-custom-picture {
                    width: {$image_size_width};
                    height: {$image_size_height};
                }
            ";

			$modal_width  = ( 70 / 100 ) * (int) $image_size_width;
			$modal_height = ( 70 / 100 ) * (int) $image_size_height;

			$custom_css .= "
                .yith-ywgc-gift-this-product-modal-content-wrapper .ywgc-design-list .ywgc-preset-image img,.yith-ywgc-gift-this-product-modal-content-wrapper .ywgc-preset-image.ywgc-preset-image-view-all, .yith-ywgc-gift-this-product-modal-content-wrapper .ywgc-preset-image.ywgc-custom-picture {
                    width: {$modal_width}px;
                    height: {$modal_height}px;
                }
            ";

			if ( is_rtl() ){
				$custom_css .= "
                .ywgc-design-list .ywgc-preset-image:not(.ywgc-custom-picture) img,
				.ywgc-preset-image.ywgc-preset-image-view-all,
				.ywgc-preset-image.ywgc-custom-picture {
				    object-fit: cover;
				}
				.ywgc-design-list-modal {
				    left: 0!important;
				    right: unset!important;
				}
				.ywgc-custom-upload-container-modal {
				    float:left!important;
				}
				.ywgc-design-list-menu {
				    right: 0;
				}
				ul.ywgc-template-categories {
				    float: right!important;
				}
				.ywgc-template-categories {
				    padding-left: unset!important;
				    padding-right: 40px;
				}
				.ywgc-design-categories-title {
				    padding-left: unset!important;
				    padding-right: 40px;
				}
				.gift-cards-list {
				    direction: ltr;
				    text-align: left;
				}
				.ywgc_select_amount_title {
				    direction: rtl;
				    text-align: right;
				}
				.ywgc-template-categories {
				    padding-left: 40px;
				}
				.ywgc-manual-currency-symbol.left {
				    right: unset !important;
				}
				.ywgc-manual-currency-symbol {
				    padding: unset !important;
				}
            ";
			}

			/**
			 * APPLY_FILTERS: yith_ywgc_custom_css
			 *
			 * Filter the custom CSS added in the frontend.
			 *
			 * @param string the custom CSS
			 *
			 * @return string
			 */
			return apply_filters( 'yith_ywgc_custom_css', $custom_css );
		}

		/**
		 * Show custom design area for the product
		 *
		 * @param WC_Product $product Product object.
		 */
		public function show_design_section( $product ) {
			if ( ! $product || ! $product instanceof WC_Product ) {
				return;
			}

			if ( is_product() && ! $product->is_virtual() && 'no' === get_option( 'ywgc_gallery_in_physical', 'no' ) ) {
				return;
			}

			/**
			 * APPLY_FILTERS: yith_wcgc_design_presets_args
			 *
			 * Filter the arguments to pass to the design presets template.
			 *
			 * @param array the array of arguments
			 *
			 * @return array
			 */
			$args            = apply_filters( 'yith_wcgc_design_presets_args', array( 'hide_empty' => 1 ) );
			$args            = array_merge( array( 'taxonomy' => YWGC_CATEGORY_TAXONOMY ), $args );
			$categories      = get_terms( $args );
			$item_categories = array();

			foreach ( $categories as $item ) {
				$object_ids = get_objects_in_term( $item->term_id, YWGC_CATEGORY_TAXONOMY );

				foreach ( $object_ids as $object_id ) {
					$item_categories[ $object_id ] = isset( $item_categories[ $object_id ] ) ? $item_categories[ $object_id ] . ' ywgc-category-' . $item->term_id : 'ywgc-category-' . $item->term_id;
				}
			}

			$desings_to_show   = get_option( 'ywgc_template_design_number_to_show', '3' );
			$categories_number = count( $item_categories );

			if ( $desings_to_show > $categories_number ) {
				$desings_to_show = $categories_number;
			}

			if ( 0 === intval( $desings_to_show ) ) {
				$desings_to_show = 1;
			}

			$allow_templates       = get_option( 'ywgc_template_design', 'yes' );
			$allow_customer_images = get_option( 'ywgc_custom_design', 'no' );

			if ( 'yes' === $allow_templates ) {
				$display = '';
			} else {
				$display = 'display: none';
			}

			$selected_categories              = get_post_meta( $product->get_id(), 'selected_images_categories', true );
			$selected_categories_string       = is_string( $selected_categories ) ? $selected_categories : '';
			$selected_categories_unserialized = json_decode( $selected_categories_string );
			$default_gift_product             = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

			if ( is_object( $product ) && is_object( $default_gift_product ) && $product->get_id() === $default_gift_product->get_id() ) {
				$selected_categories_unserialized = array( '0' => '0' );
			} elseif ( is_null( $selected_categories_unserialized ) ) {
				$selected_categories_unserialized = array();
			}

			$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

			$template_path = 'v2' === get_option( 'ywgc_page_layout_version', 'v1' ) || is_object( $default_gift_product ) && $product->get_id() === $default_gift_product->get_id() ? 'yith-gift-cards/layouts/2.0/gift-card-design.php' : 'yith-gift-cards/gift-card-design.php';

			wc_get_template(
				$template_path,
				array(
					'categories'                       => $categories,
					'item_categories'                  => $item_categories,
					'product'                          => $product,
					'desings_to_show'                  => $desings_to_show,
					'allow_templates'                  => $allow_templates,
					'display'                          => $display,
					'allow_customer_images'            => $allow_customer_images,
					'selected_categories_unserialized' => array_map( 'intval', $selected_categories_unserialized ),
				),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR )
			);
		}

		/**
		 * Show Gift Cards details
		 *
		 * @param WC_Product $product Product object.
		 */
		public function show_gift_card_details( $product ) {
			if ( ( $product instanceof WC_Product_Gift_Card ) && $product->is_virtual() ) {
				$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

				$template_path = 'v2' === get_option( 'ywgc_page_layout_version', 'v1' ) || is_object( $default_gift_product ) && $product->get_id() === $default_gift_product->get_id() ? 'yith-gift-cards/layouts/2.0/gift-card-details.php' : 'yith-gift-cards/gift-card-details.php';

				wc_get_template(
					$template_path,
					array(
						'allow_multiple_recipients' => YITH_YWGC()->allow_multiple_recipients() && ( $product instanceof WC_Product_Gift_Card ),
						'mandatory_recipient'       => YITH_YWGC()->mandatory_recipient(),
						'allow_send_later'          => ( 'yes' === get_option( 'ywgc_enable_send_later', 'no' ) ),
						'date_format'               => apply_filters( 'yith_wcgc_date_format', 'Y-m-d' ),
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			} else {
				$template_path = 'v2' === get_option( 'ywgc_page_layout_version', 'v1' ) ? 'yith-gift-cards/layouts/2.0/physical-gift-card-details.php' : 'yith-gift-cards/physical-gift-card-details.php';

				wc_get_template(
					$template_path,
					array(
						'allow_multiple_recipients'       => YITH_YWGC()->allow_multiple_recipients() && ( $product instanceof WC_Product_Gift_Card ),
						'gift_this_product'               => ! ( $product instanceof WC_Product_Gift_Card ),
						'allow_send_later'                => ( 'yes' === get_option( 'ywgc_enable_send_later', 'no' ) ),
						'date_format'                     => apply_filters( 'yith_wcgc_date_format', 'Y-m-d' ),
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			}
		}

		/**
		 * Let the customer to use a product of type WC_Product_Simple  as source for a gift card
		 *
		 * @param WC_Product $product Product object.
		 */
		public function show_product_info_under_preview_in_modal( $product ) {
			$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

			if ( is_object( $default_gift_product ) && $product->get_id() === $default_gift_product->get_id() ) {
				wc_get_template(
					'gift-this-product/gifted-product-preview.php',
					array(),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			}
		}

		/**
		 * Include the hidden info in the default Gift Card product
		 *
		 * @param WC_Product $product Product object.
		 */
		public function add_gift_this_product_info_to_default_product( $product ) {
			if ( is_object( $product ) ) {
				wc_get_template(
					'gift-this-product/gift-this-product-form-info.php',
					array(
						'product' => $product,
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			}
		}

		/**
		 * Let the customer to use a product of type WC_Product_Simple  as source for a gift card
		 */
		public function show_give_as_present_link_simple() {
			global $product;

			$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

			if ( ! $default_gift_product ) {
				return;
			}

			if ( ! $product ) {
				return;
			}

			if ( ! $product->is_type( array( 'simple', 'variable', 'yith_bundle', 'booking' ) ) ) {
				return;
			}

			if ( 'yes' === get_post_meta( $product->get_id(), '_yith_wcgc_disable_gift_this_product', true ) ) {
				return;
			}

			if ( ! YITH_YWGC()->allow_product_as_present() ) {
				return;
			}

			if ( $product instanceof WC_Product_Simple && apply_filters( 'yith_ywgc_give_product_as_present', true, $product ) ) {
				wc_get_template(
					'gift-this-product/give-product-as-present.php',
					array(
						'product' => $product,
						'icon'    => get_option( 'ywgc_gift_this_product_icon', 'no' ),
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			}
		}

		/**
		 * Let the customer to use a product of type WC_Product_Variable  as source for a gift card
		 */
		public function show_give_as_present_link_variable() {
			global $product;

			$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

			if ( ! $default_gift_product ) {
				return;
			}

			if ( ! $product ) {
				return;
			}

			if ( ! $product->is_type( array( 'simple', 'variable', 'yith_bundle', 'booking' ) ) ) {
				return;
			}

			if ( 'yes' === get_post_meta( $product->get_id(), '_yith_wcgc_disable_gift_this_product', true ) ) {
				return;
			}

			if ( ! YITH_YWGC()->allow_product_as_present() ) {
				return;
			}

			if ( $product instanceof WC_Product_Variable && apply_filters( 'yith_ywgc_give_product_as_present', true, $product ) ) {
				wc_get_template(
					'gift-this-product/give-product-as-present.php',
					array(
						'product' => $product,
						'icon'    => get_option( 'ywgc_gift_this_product_icon', 'no' ),
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			}
		}

		/**
		 * Integration with yith woocommerce product bundle
		 * Let the customer to use a product of type WC_Product_Yith_Bundle  as source for a gift card
		 */
		public function show_give_as_present_link_product_bundle_product() {
			global $product;

			$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

			if ( ! $default_gift_product ) {
				return;
			}

			if ( ! $product ) {
				return;
			}

			if ( 'yes' === get_post_meta( $product->get_id(), '_yith_wcgc_disable_gift_this_product', true ) ) {
				return;
			}

			if ( ! YITH_YWGC()->allow_product_as_present() ) {
				return;
			}

			if ( $product instanceof WC_Product_Yith_Bundle && apply_filters( 'yith_ywgc_give_product_as_present', true, $product ) ) {
				wc_get_template(
					'gift-this-product/give-product-as-present.php',
					array(
						'product' => $product,
						'icon'    => get_option( 'ywgc_gift_this_product_icon', 'no' ),
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			}
		}

		/**
		 * Integration with YITH Bookings
		 * Let the customer to use a product of type WC_Product_Booking  as source for a gift card
		 */
		public function show_give_as_present_link_product_booking_product() {
			global $product;

			$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

			if ( ! $default_gift_product ) {
				return;
			}

			if ( ! $product ) {
				return;
			}

			if ( 'yes' === get_post_meta( $product->get_id(), '_yith_wcgc_disable_gift_this_product', true ) ) {
				return;
			}

			if ( ! YITH_YWGC()->allow_product_as_present() ) {
				return;
			}

			if ( ! function_exists( 'is_product' ) || ! is_product() ) {
				return;
			}

			if ( defined( 'YITH_WCBK' ) && $product instanceof WC_Product_Booking && apply_filters( 'yith_ywgc_give_product_as_present', true, $product ) ) {
				wc_get_template(
					'gift-this-product/give-product-as-present.php',
					array(
						'product' => $product,
						'icon'    => get_option( 'ywgc_gift_this_product_icon', 'no' ),
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			}
		}

		/**
		 * Check if a gift card product avoid entering manual amount value
		 *
		 * @param WC_Product_Gift_Card $product Product object.
		 *
		 * @return bool
		 */
		public function is_manual_amount_allowed( $product ) {
			$manual_amount            = $product->get_manual_amount_status();
			$override_global_settings = $product->get_override_global_settings_status();

			// if the gift card have specific manual entered amount behaviour, return that.
			if ( 'yes' === $override_global_settings ) {
				return $manual_amount;
			}

			return YITH_YWGC()->allow_manual_amount();
		}

		/**
		 * Display a preview of the form under the gift card image
		 */
		public function yith_ywgc_display_gift_card_form_preview_below_image() {
			if ( is_product() ) {
				$product = wc_get_product( get_the_ID() );

				$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

				if ( is_object( $product ) && $product->is_type( 'gift-card' ) ) {
					$template_path = 'v2' === get_option( 'ywgc_page_layout_version', 'v1' ) || is_object( $default_gift_product ) && $product->get_id() === $default_gift_product->get_id() ? 'yith-gift-cards/layouts/2.0/form-preview.php' : 'single-product/form-preview.php';

					wc_get_template(
						$template_path,
						array(
							'product' => $product,
						),
						'',
						trailingslashit( YITH_YWGC_TEMPLATES_DIR )
					);
				}
			}
		}

		/**
		 * Rename the coupon field on the cart page
		 *
		 * @param string $translated_text Translated text.
		 * @param string $text            Text.
		 * @param string $text_domain     Text domain.
		 *
		 * @return mixed|void|null
		 */
		public function yith_ywgc_rename_coupon_field_on_cart( $translated_text, $text, $text_domain ) {
			if ( 'woocommerce' !== $text_domain || $this->apply_gift_card_on_coupon_form ) {
				return $translated_text;
			}

			if ( 'Apply coupon' === $text ) {
				$translated_text = get_option( 'ywgc_apply_coupon_button_text_button', esc_html__( 'Apply coupon', 'yith-woocommerce-gift-cards' ) );
			}

			if ( 'Coupon:' === $text ) {
				$translated_text = get_option( 'ywgc_apply_coupon_title_text_button', esc_html__( 'Coupon:', 'yith-woocommerce-gift-cards' ) );
			}

			if ( 'Coupon code' === $text ) {
				$translated_text = get_option( 'ywgc_apply_coupon_placeholder_text_button', esc_html__( 'Coupon code', 'yith-woocommerce-gift-cards' ) );
			}

			if ( 'If you have a coupon code, please apply it below.' === $text ) {
				$translated_text = get_option( 'ywgc_apply_coupon_checkout_text', esc_html__( 'If you have a coupon code, please apply it below.', 'yith-woocommerce-gift-cards' ) );
			}

			return $translated_text;
		}

		/**
		 * Rename the coupon field on the cart page
		 */
		public function yith_ywgc_rename_coupon_field_on_cart_template_redirect() {
			$this->apply_gift_card_on_coupon_form = get_option( 'ywgc_apply_gift_card_on_coupon_form', 'no' ) !== 'yes';

			if ( is_cart() || is_checkout() ) {
				add_filter( 'gettext', array( $this, 'yith_ywgc_rename_coupon_field_on_cart' ), 10, 3 );
			}
		}

		/**
		 * Rename the coupon label on the checkout page
		 *
		 * @param string $text Text.
		 *
		 * @return string
		 */
		public function yith_ywgc_rename_coupon_label( $text ) {
			if ( 'yes' === get_option( 'ywgc_apply_gift_card_on_coupon_form', 'no' ) ) {
				$text_option = get_option( 'ywgc_apply_coupon_label_text', esc_html__( 'Have a coupon?', 'yith-woocommerce-gift-cards' ) );

				$text = $text_option . ' <a href="#" class="showcoupon">' . esc_html__( 'Click here to enter your code', 'yith-woocommerce-gift-cards' ) . '</a>';
			}

			return $text;
		}

		/**
		 * Add condition when a gift card is applied
		 *
		 * @param bool $bool Whether to apply gift card or not.
		 *
		 * @return bool
		 */
		public function yith_ywgc_check_gift_card_return_callback( $bool ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.boolFound
			$items = WC()->cart->get_cart();

			if ( 'yes' === get_option( 'ywgc_apply_gc_code_on_gc_product', 'no' ) ) {
				foreach ( $items as $cart_item_key => $values ) {
					$product = $values['data'];

					if ( $product && 'gift-card' === $product->get_type() ) {
						wc_add_notice( esc_html__( 'It is not possible to add a gift card code when the cart contains a gift card product', 'yith-woocommerce-gift-cards' ), 'error' );

						$bool = false;
					}
				}
			}

			return $bool;
		}

		/**
		 * Add condition when a gift card is applied
		 *
		 * @param bool                   $bool      Whether to apply gift card or not.
		 * @param YWGC_Gift_Card_Premium $gift_card The gift card object.
		 *
		 * @return bool
		 */
		public function yith_ywgc_check_excluded_product_categories( $bool, $gift_card ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.boolFound
			if ( ! is_object( $gift_card ) || $gift_card->product_as_present ) {
				return $bool;
			}

			$product                    = new WC_Product_Gift_Card( $gift_card->product_id );
			$exclude_categories_setting = $product->get_exclude_categories_settings_status();

			if ( $exclude_categories_setting ) {
				$excluded_categories = $product->get_excluded_categories();

				if ( empty( $excluded_categories ) ) {
					return $bool;
				}

				$categories_names = '';

				foreach ( $excluded_categories as $category ) {
					$categories_names .= end( $excluded_categories ) === $category ? get_the_category_by_ID( $category ) . '' : get_the_category_by_ID( $category ) . ', ';
				}

				$items = WC()->cart->get_cart();

				foreach ( $items as $cart_item_key => $values ) {
					$product = $values['data'];

					if ( 'variation' === $product->get_type() ) {
						$product = wc_get_product( $product->get_parent_id() );
					}

					if ( array_intersect( $excluded_categories, $product->get_category_ids() ) ) {
						if ( count( $excluded_categories ) > 1 ) {
							wc_add_notice( esc_html_x( 'It is not possible to use this gift card code when the cart contains items of these categories: ', 'Error message when a gift card code is excluded for certain categories. After the string, we include a list of the excluded categories names', 'yith-woocommerce-gift-cards' ) . $categories_names, 'error' );
						} else {
							wc_add_notice( esc_html_x( 'It is not possible to use this gift card code when the cart contains items from this category: ', 'Error message when a gift card code is excluded for certain category. After the string, we include the name of the excluded category', 'yith-woocommerce-gift-cards' ) . $categories_names, 'error' );
						}

						$bool = false;
					}
				}
			}

			return $bool;
		}

		/**
		 * Delete_uploaded_files
		 *
		 * @param string $target Target dir.
		 */
		public function delete_uploaded_files( $target ) {
			if ( is_dir( $target ) ) {
				$files = glob( $target . '*', GLOB_MARK );

				foreach ( $files as $file ) {
					$this->delete_uploaded_files( $file );
				}

				if ( is_dir( $target ) ) {
					rmdir( $target ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
				}
			} elseif ( is_file( $target ) ) {
				wp_delete_file( $target );
			}
		}

		/**
		 * Calculate the shipping cost in the "Gift this product" feature
		 */
		public function ywgc_calculate_shipping_for_gift_this_product() {
			$country_code = isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$postal_code  = isset( $_POST['postal_code'] ) ? sanitize_text_field( wp_unslash( $_POST['postal_code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$shipping_cost = $this->ywgc_get_shipping_cost_by_country( $country_code, $postal_code );

			wp_send_json( ywgc_string_to_float( $shipping_cost ) );
		}

		/**
		 * Add a new gift card to the customer account
		 */
		public function ywgc_add_new_gift_card_my_account() {
			$gift_card_code = isset( $_POST['gift_card_code'] ) ? sanitize_text_field( wp_unslash( $_POST['gift_card_code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$user_id        = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$args = array(
				'gift_card_number' => $gift_card_code,
			);

			$gift_card = new YITH_YWGC_Gift_Card( $args );

			if (  ! is_object( $gift_card ) || 0 === $gift_card->ID || '' === $gift_card_code || apply_filters('yith_ywgc_add_new_gift_card_my_account_error_condition', false, $gift_card, $user_id ) ) {
				wp_send_json_error();

			} elseif ( is_object( $gift_card ) && 0 !== $gift_card->ID ) {
				$user = wp_get_current_user();
				$gift_card->register_user( $user->ID );

				wp_send_json_success();
			}
		}

		/**
		 * Get the shipping cost by country in the "Gift this product" feature
		 *
		 * @param string      $country_code Country code.
		 * @param string|null $postal_code  Postal code.
		 *
		 * @return float
		 */
		public function ywgc_get_shipping_cost_by_country( $country_code, $postal_code = null ) {
			global $wpdb;

			$shipping_cost = 0;

			if ( class_exists( 'WC_Shipping_Zones' ) ) {
				$all_zones = WC_Shipping_Zones::get_zones();

				if ( ! empty( $all_zones ) ) {
					foreach ( $all_zones as $zone ) {
						if ( ! empty( $zone['zone_locations'] ) ) {
							$array_post_codes    = array();
							$array_country_codes = array();

							foreach ( $zone['zone_locations'] as $code ) {
								if ( 'postcode' === $code->type ) {
									$array_post_codes[] = $code->code;
								}

								if ( 'country' === $code->type ) {
									$array_country_codes[] = $code->code;
								}
							}

							$postcode = wc_normalize_postcode( wc_clean( $postal_code ) );
							$matches  = 'false';

							if ( $postcode && ! empty( $array_post_codes ) ) {
								$postcode_locations = $wpdb->get_results( "SELECT zone_id, location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_type = 'postcode';" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
								$matches_check      = wc_postcode_location_matcher( $postcode, $postcode_locations, 'zone_id', 'location_code', $country_code );

								if ( array( $matches_check ) && ! empty( $matches_check ) ) {
									$matches = 'true';
								}
							} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
								if ( in_array( $country_code, $array_country_codes, true ) && empty( $array_post_codes ) ) {
									$matches = 'true';
								}
							}

							// If the country and postcode match with a shipping rule, calculate the shipping.
							if ( 'true' === $matches ) {
								if ( ! empty( $zone['shipping_methods'] ) ) {
									foreach ( $zone['shipping_methods'] as $flat_rate ) {
										if ( 'free_shipping' === $flat_rate->id ) {
											$shipping_cost = '0';
										} else {
											if ( ! $flat_rate->cost ) {
												continue;
											}

											if ( 'taxable' === $flat_rate->tax_status ) {
												$shipping_cost = str_replace( ' * [qty]', '', $flat_rate->cost );
												$shipping_tax  = $this->ywgc_get_tax_cost_by_country( $country_code, $shipping_cost );
												$shipping_cost = (float) $shipping_cost + (float) $shipping_tax;
											} else {
												$shipping_cost = str_replace( ' * [qty]', '', $flat_rate->cost );
											}
										}

										break;
									}
								}
								break;
							} else {
								$shipping_cost = '0';
							}
						}
					}
				}
			}

			return $shipping_cost;
		}

		/**
		 * Get the tax cost by country in the "Gift this product" feature
		 *
		 * @param string $country_code Country code.
		 * @param float  $total        Total.
		 *
		 * @return float
		 */
		public function ywgc_get_tax_cost_by_country( $country_code, $total ) {
			$tax_rate            = 0;
			$tax                 = 0;
			$available_tax_rates = WC_Tax::find_rates( array( 'country' => $country_code ) );

			if ( ! empty( $available_tax_rates ) ) {
				foreach ( $available_tax_rates as $available_tax_rate ) {
					$tax_rate = $available_tax_rate['rate'];
					break;
				}

				$tax = ( (float) $tax_rate / 100 ) * (float) $total;
				$tax = round( $tax, 2 );
			}

			return $tax;
		}

		/**
		 * Auto-complete WC orders if the order only contains virtual gift cards and the option is enabled
		 *
		 * @param int $order_id Order ID.
		 *
		 * @return void|float
		 */
		public function yith_wcgc_woocommerce_auto_complete_order( $order_id ) {
			/**
			 * APPLY_FILTERS: yith_ywgc_skip_orders_autocomplete
			 *
			 * Filter the condition to skip the autocomplete orders based on the order.
			 *
			 * @param bool true to skip it, false to not. Default: false
			 * @param int $order_id the order ID
			 *
			 * @return bool
			 */
			if ( get_option( 'ywgc_autocomplete_orders', 'no' ) === 'no' || apply_filters( 'yith_ywgc_skip_orders_autocomplete', false, $order_id ) ) {
				return;
			}

			if ( ! $order_id ) {
				return;
			}

			$order       = wc_get_order( $order_id );
			$order_items = $order->get_items();

			$product_types_array = array();

			foreach ( $order_items as $item_id => $item_data ) {
				$product               = wc_get_product( $item_data['product_id'] );
				$product_types_array[] = $product->get_type();
			}

			$fulldiff = array_merge( array_diff( $product_types_array, array( 'gift-card' ) ), array_diff( array( 'gift-card' ), $product_types_array ) );

			if ( empty( $fulldiff ) ) {
				$order->update_status( 'completed' );
			}
		}

		/**
		 * When a product is chosen as a starting point for creating a gift card, as in "give it as a present" function on
		 * product page, the product that will really go in the cart if a gift card, not the product that is
		 * currently shown.
		 *
		 * @param string     $product_type   Product type.
		 * @param WC_Product $adding_to_cart Product object.
		 *
		 * @return string
		 */
		public function set_product_type_before_add_to_cart( $product_type, $adding_to_cart ) {
			if ( ! isset( $_POST['ywgc-as-present'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				return $product_type;
			}

			return YWGC_GIFT_CARD_PRODUCT_TYPE;
		}

		/**
		 * Show the gift card code under the order item, in the order admin page
		 *
		 * @param int   $item_id Order item ID.
		 * @param array $item    Order item.
		 *
		 * @since  1.0.0
		 */
		public function show_gift_card_code_on_order_item( $item_id, $item ) {
			$gift_ids = ywgc_get_order_item_giftcards( $item_id );

			if ( empty( $gift_ids ) ) {
				return;
			}

			foreach ( $gift_ids as $gift_id ) {
				$gc = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_id ) );

				if ( ! $gc->is_pre_printed() ) :
					?>
					<div>
						<?php
						/**
						 * APPLY_FILTERS: yith_ywgc_display_code_order_details
						 *
						 * Filter the condition to display the gift card code in the order details.
						 *
						 * @param bool true to display it, false to not. Default: true
						 *
						 * @return bool
						 */
						if ( apply_filters( 'yith_ywgc_display_code_order_details', true ) ) :
							?>
							<span class="ywgc-gift-code-label"><?php echo esc_html__( 'Gift card code: ', 'yith-woocommerce-gift-cards' ); ?></span>
							<span class="ywgc-card-code"><?php echo wp_kses_post( $gc->get_code() ); ?></span>
							<?php
						endif;
						if ( $gc->is_virtual() ) {
							if ( $gc->delivery_send_date ) {
								$status_class   = 'sent';
								$formatted_date = $gc->get_formatted_date( $gc->delivery_send_date );

								// translators: %s is the date when the gift card was sent.
								$message = sprintf( esc_html__( 'Sent on %s', 'yith-woocommerce-gift-cards' ), (string) $formatted_date );
							} elseif ( $gc->delivery_date >= current_time( 'timestamp' ) ) { // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
								$status_class = 'scheduled';
								$message      = esc_html__( 'Scheduled', 'yith-woocommerce-gift-cards' );
							} elseif ( $gc->has_been_sent() === '' ) {
								$status_class = 'not-sent';
								$message      = esc_html__( 'Not yet sent', 'yith-woocommerce-gift-cards' );
							} else {
								$status_class = 'failed';
								$message      = esc_html__( 'Failed', 'yith-woocommerce-gift-cards' );
							}
							?>

							<div>
								<span>
									<?php
									// translators: %s is the gift card recipient email.
									echo wp_kses_post( sprintf( __( 'Recipient: %s', 'yith-woocommerce-gift-cards' ), $gc->recipient ) );
									?>
								</span>
							</div>
							<div>
								<?php if ( '' !== $gc->delivery_date ) : ?>
									<span>
										<?php
										// translators: %s is the gift card delivery date.
										echo esc_html( sprintf( __( 'Delivery date: %s', 'yith-woocommerce-gift-cards' ), $gc->get_formatted_date( $item['ywgc_delivery_date'] ) ) );
										?>
									</span>
									<br>
								<?php endif; ?>
								<span class="ywgc-delivery-status <?php echo esc_attr( $status_class ); ?>"><?php echo wp_kses_post( $message ); ?></span>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				endif;
			}
		}

		/**
		 * Add "Gift this product" button in the loop to Products block item
		 *
		 * @param string     $item_html HTML of the single block item.
		 * @param array      $data      Data used to render the item.
		 * @param WC_Product $product   Current product.
		 *
		 * @return string Filtered HTML.
		 */
		public function add_gift_this_product_button_for_blocks( $item_html, $data, $product ) {
			if ( $product && apply_filters( 'yith_ywgc_give_product_as_present', true, $product ) && ( $product->is_in_stock() && $product->get_type() !== 'gift-card' ) && ( get_option( 'ywgc_permit_its_a_present_shop_page' ) === 'yes' ) && YITH_YWGC()->allow_product_as_present() && ( get_post_meta( $product->get_id(), '_yith_wcgc_disable_gift_this_product', true ) !== 'yes' ) && ( $product->is_type( array( 'simple', 'variable', 'yith_bundle' ) ) || ( defined( 'YITH_WCBK' ) && $product->is_type( 'booking' ) ) ) ) {
				$index = 3;

				preg_match( '/(<li class=".*?">)[\S|\s]*?(<a .*?>[\S|\s]*?<\/a>)([\S|\s]*?)(<\/li>)/', $item_html, $parts );

				if ( ! $parts || count( $parts ) < 5 ) {
					return $item_html;
				}

				// removes first match (entire match).
				array_shift( $parts );

				// removes empty parts.
				$parts = array_filter( $parts );

				$button = '<a href="' . esc_html( get_permalink( $product->get_id() ) . '?yith-gift-this-product-form=yes' ) . '" class="button yith_wc_gift_this_product_shop_page_class" rel="nofollow">' . esc_html( YITH_YWGC()->ywgc_gift_this_product_label() ) . '</a>';

				// if index is found, stitch button in correct position.
				if ( $index ) {
					$first_set  = array_slice( $parts, 0, $index );
					$second_set = array_slice( $parts, $index );
					$parts      = array_merge(
						$first_set,
						(array) $button,
						$second_set
					);
				}

				// join all parts together.
				$item_html = implode( '', $parts );
			}

			return $item_html;
		}

		/**
		 * Check if WC is using the block to show the add to quote button
		 *
		 * @since 4.16.0
		 */
		public function wc_blocks_hooks() {
			if ( yith_plugin_fw_wc_is_using_block_template_in_product_catalogue() && is_shop() || is_product_taxonomy() || has_block( 'post-template' ) && has_block( 'woocommerce/product-button' ) ) {
				add_filter( 'render_block_woocommerce/product-button', array( $this, 'add_button_shop_block' ), 10, 2 );
			}
		}

		/**
		 * Concat the quote button to the product button
		 *
		 * @param string $content Block content.
		 * @param array  $block   Block.
		 *
		 * @since 4.16.0
		 */
		public function add_button_shop_block( $content, $block ) {
			ob_start();
			$this->ywgc_show_gift_this_product_in_shop();
			$new_content = ob_get_contents();
			ob_end_clean();

			return $content . $new_content;
		}
	}
}