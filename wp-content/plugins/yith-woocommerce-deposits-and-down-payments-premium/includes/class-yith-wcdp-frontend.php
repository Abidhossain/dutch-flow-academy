<?php
/**
 * Frontend class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Frontend' ) ) {
	/**
	 * Contains all frontend handling of the plugin
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_Frontend
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Template of single product page "Add deposit to cart"
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $single_product_add_deposit;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			// init frontend handling.
			add_action( 'init', array( $this, 'init' ) );

			// update add to cart for deposit.
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'print_single_add_deposit_to_cart' ) );
			add_action( 'woocommerce_available_variation', array( $this, 'add_deposit_variation_data' ), 10, 3 );
			add_action( 'yith_wcdp_after_deposit_price_label', array( $this, 'expiration_notice' ) );

			if ( ! yith_plugin_fw_is_true( get_option( 'yith_wcdp_hide_loop_button', 'no' ) ) ) {
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'print_loop_add_deposit_to_cart' ), 15 );
				add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'remove_loop_add_to_cart' ), 10, 2 );
			}

			// additional product notes.
			add_action( 'init', array( $this, 'add_additional_product_note' ), 15 );
			add_action( 'yith_wcdp_before_add_deposit_to_cart', array( $this, 'print_additional_variation_note' ) );

			// expiration fallbacks for the product.
			add_filter( 'woocommerce_is_purchasable', array( $this, 'is_deposit_product_purchasable' ), 10, 2 );
			add_filter( 'woocommerce_product_is_visible', array( $this, 'is_deposit_product_visible' ), 10, 2 );

			// enqueue required assets.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Init frontend functionalities
		 *
		 * @return void.
		 */
		public function init() {
			YITH_WCDP_Cart::init();
			YITH_WCDP_Shipping::init();
			YITH_WCDP_Checkout::init();
			YITH_WCDP_My_Account::init();
		}

		/* === SINGLE PRODUCT ADD DEPOSIT TO CART === */

		/**
		 * Returns "Add deposit to cart" template, to be used for a specific product/variation
		 *
		 * @param int|bool $product_id Product id; if false, system will use global product.
		 * @param bool     $echo       Whether to echo the template.
		 *
		 * @return string|bool Template HTML, or false on failure.
		 */
		public function single_add_deposit_to_cart( $product_id = false, $echo = false ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.echoFound
			global $product;

			// retrieve product.
			if ( $product_id ) {
				$current_product = wc_get_product( $product_id );
			} elseif ( $product instanceof WC_Product ) {
				$product_id      = $product->get_id();
				$current_product = $product;
			} else {
				return false;
			}

			// can't find the product.
			if ( ! $product_id || ! $current_product ) {
				return false;
			}

			// product can't be purchased.
			/**
			 * APPLY_FILTERS: yith_wcdp_add_single_deposit_button
			 *
			 * Filters if should add the add single deposit button.
			 *
			 * @param bool                Default value: true.
			 * @param WC_Product $product The product.
			 *
			 * @return string
			 */
			if ( ! apply_filters( 'yith_wcdp_add_single_deposit_button', $current_product->is_purchasable() && $current_product->is_in_stock(), $product_id, $current_product ) ) {
				return false;
			}

			if ( $current_product->is_type( array( 'simple', 'variation' ) ) ) {
				ob_start();
				$this->print_single_add_deposit_to_cart_template( $product_id );

				$template = ob_get_clean();
			} else {
				// retrieve template for product types different product types.
				ob_start();

				/**
				 * DO_ACTION: yith_wcdp_{$product->get_type()}_add_to_cart
				 *
				 * Action triggered when adding "Add deposit" option to the product page of a non-simple type product.
				 *
				 * @param WC_Product $product The product object.
				 */
				do_action( "yith_wcdp_{$current_product->get_type()}_add_to_cart", $current_product );

				$template = ob_get_clean();
			}

			if ( $echo ) {
				// template is already escaped.
				echo $template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return $template;
		}

		/**
		 * A simple wrapper for {@see \YITH_WCDP_Frontend::single_add_deposit_to_cart}
		 * Prints Add deposit to cart for single product
		 *
		 * @param int|bool $product_id Product id; if false, system will use global product.
		 */
		public function print_single_add_deposit_to_cart( $product_id = false ) {
			$this->single_add_deposit_to_cart( $product_id, true );
		}

		/**
		 * A simple wrapper for {@see \YITH_WCDP_Frontend::single_add_deposit_to_cart}
		 * Prints Add deposit to cart for single product
		 *
		 * @param int|bool $product_id Product id; if false, system will use global product.
		 */
		public function print_single_add_deposit_to_cart_template( $product_id = false ) {
			global $product;

			// retrieve product.
			if ( $product_id ) {
				$current_product = wc_get_product( $product_id );
			} elseif ( $product instanceof WC_Product ) {
				$product_id      = $product->get_id();
				$current_product = $product;
			} else {
				return;
			}

			// check if deposit is enabled.
			$deposit_enabled = YITH_WCDP_Deposits::is_enabled( $product_id );

			if ( ! $deposit_enabled ) {
				return;
			}

			// product options.
			$parent_id       = $current_product->get_parent_id();
			$parent_id       = $parent_id ? $parent_id : $product_id;
			$deposit_forced  = YITH_WCDP_Deposits::is_mandatory( $product_id );
			$default_deposit = YITH_WCDP_Deposits::is_default( $product_id );
			$deposit_type    = YITH_WCDP_Deposits::get_type( $product_id );
			$deposit_amount  = YITH_WCDP_Deposits::get_amount( $product_id );
			$deposit_rate    = YITH_WCDP_Deposits::get_rate( $product_id );
			$classes         = array();

			if ( YITH_WCDP_Deposits::has_expired( $product_id ) && 'disable_deposit' === YITH_WCDP_Deposits::get_expiration_fallback( $product_id ) ) {
				$classes[] = 'deposit-disabled';
			}

			$classes = implode( ' ', apply_filters( 'yith_wcdp_add_deposit_to_cart_classes', $classes, $product_id ) );

			// calculate deposit for current product.
			$deposit_value = apply_filters( 'yith_wcdp_deposit_value', min( YITH_WCDP_Deposits::get_deposit( $product_id, false, false, 'view' ), $current_product->get_price() ), $parent_id, $product_id, array() );

			// format prices for frontend.
			$deposit_amount = yith_wcdp_get_price_to_display( $current_product, array( 'price' => $deposit_amount ) );

			// compact template args.
			$template_args = array_merge(
				compact( 'deposit_enabled', 'default_deposit', 'deposit_forced', 'deposit_type', 'deposit_amount', 'deposit_rate', 'deposit_value', 'classes' ),
				array(
					'product' => $current_product,
				)
			);

			// retrieve template.
			yith_wcdp_get_template( 'single-add-deposit-to-cart.php', $template_args );
		}

		/**
		 * Adds "Deposit form" to each variation data
		 *
		 * @param array                $variation_data Variation data to filter.
		 * @param WC_Product_Variable  $product        Variable product.
		 * @param WC_Product_Variation $variation      Current variation.
		 *
		 * @return mixed Filtered variation data
		 * @since 1.04
		 */
		public function add_deposit_variation_data( $variation_data, $product, $variation ) {
			$product_id   = $product->get_id();
			$variation_id = $variation->get_id();
			$enable_ajax  = yith_plugin_fw_is_true( get_option( 'yith_wcdp_general_enable_ajax_variation', 'no' ) );

			/**
			 * APPLY_FILTERS: yith_wcdp_generate_add_deposit_to_cart_variations_field
			 *
			 * Filters if should generate the deposit add to cart to the variation.
			 *
			 * @param bool               Default value: true.
			 * @param int  $variation_id Variation id.
			 *
			 * @return bool
			 */
			if ( $enable_ajax || ! apply_filters( 'yith_wcdp_generate_add_deposit_to_cart_variations_field', true, $product_id, $variation_id ) ) {
				return $variation_data;
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_disable_deposit_variation_option
			 *
			 * Filters if should disable the deposit settings to the variations
			 *
			 * @param bool               Default value: false.
			 * @param int  $variation_id Variation id.
			 *
			 * @return bool
			 */
			$variation_specific = ! apply_filters( 'yith_wcdp_disable_deposit_variation_option', false, $product_id );
			$form_template      = $this->single_add_deposit_to_cart( $variation_specific ? $variation_id : $product_id );

			$variation_data = array_merge(
				$variation_data,
				array(
					'add_deposit_to_cart' => $form_template,
				)
			);

			return $variation_data;
		}

		/* === LOOP ADD DEPOSIT TO CART === */

		/**
		 * Prints template of loop add deposit to cart
		 *
		 * @param int|bool $product_id Product id; if false, system will use global product.
		 * @param bool     $echo       Whether to echo the template.
		 *
		 * @return string Template HTML, or false on failure.
		 */
		public function loop_add_deposit_to_cart( $product_id = false, $echo = false ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.echoFound
			global $product;

			if ( $product_id ) {
				$current_product = wc_get_product( $product_id );
			} elseif ( $product instanceof WC_Product ) {
				$product_id      = $product->get_id();
				$current_product = $product;
			} else {
				return false;
			}

			if ( ! $product_id || ! $current_product ) {
				return false;
			}

			// check if deposit is enabled.
			$deposit_enabled = YITH_WCDP_Deposits::is_enabled( $product_id );

			if ( ! $deposit_enabled ) {
				return false;
			}

			$args = apply_filters(
				'yith_wcdp_loop_add_deposit_to_cart_args',
				array(
					'product_url' => esc_url( $current_product->get_permalink() . '#yith-wcdp-add-deposit-to-cart' ),
				),
				$current_product
			);

			ob_start();

			yith_wcdp_get_template( 'loop-add-deposit-to-cart', $args );

			$template = ob_get_clean();

			if ( $echo ) {
				// template is already escaped.
				echo $template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return $template;
		}

		/**
		 *  A simple wrapper for {@see \YITH_WCDP_Frontend::loop_add_deposit_to_cart}
		 * Prints Add deposit to cart for product loops
		 *
		 * @param int|bool $product_id Product id; if false, system will use global product.
		 */
		public function print_loop_add_deposit_to_cart( $product_id = false ) {
			/**
			 * APPLY_FILTERS: yith_wcdp_show_add_to_cart_in_loop
			 *
			 * Filters if should show the deposit add to cart button in the loop.
			 *
			 * @param bool Default value coming from if it's purchasable and in stock.
			 * @param int  Current product id.
			 *
			 * @return bool
			 */
			if ( ! apply_filters( 'yith_wcdp_show_add_to_cart_in_loop', true, $product_id ) ) {
				return;
			}

			$this->loop_add_deposit_to_cart( $product_id, true );
		}

		/**
		 * Removes loop Add to Cart anchor, when deposit is enabled and mandatory on product
		 *
		 * @param string     $anchor  HTML for the Add to Cart anchor.
		 * @param WC_Product $product Current product object.
		 *
		 * @return string Filtered anchor (empty when shouldn't be shown)
		 */
		public function remove_loop_add_to_cart( $anchor, $product ) {
			$product_id = $product->get_id();

			$enabled   = YITH_WCDP_Deposits::is_enabled( $product_id );
			$mandatory = YITH_WCDP_Deposits::is_mandatory( $product_id );

			/**
			 * APPLY_FILTERS: yith_wcdp_remove_loop_add_to_cart
			 *
			 * Filters whether to remove the add to cart link from the product when deposit is enabled and it is mandatory
			 *
			 * @param bool $remove_add_to_cart Whether to remove the add to cart or not
			 *
			 * @return bool
			 */
			if ( apply_filters( 'yith_wcdp_remove_loop_add_to_cart', $enabled && $mandatory ) ) {
				return '';
			}

			return $anchor;
		}

		/* === DEPOSIT EXPIRATION NOTICE === */

		/**
		 * Prints a custom message under deposit option, to show balance expiration date
		 *
		 * @param WC_Product $product Product object.
		 * @return void
		 */
		public function expiration_notice( $product ) {
			$expiration_date = YITH_WCDP_Deposits::get_expiration_date( $product->get_id(), 'edit' );

			if ( ! $expiration_date ) {
				return;
			}

			// translators: 1. Formatted expiration date for the deposit.
			$expiration_message   = apply_filters( 'yith_wcdp_expiration_notice', __( 'Balance payment will be required on %s', 'yith-woocommerce-deposits-and-down-payments' ), $product );
			$formatted_expiration = $expiration_date->date_i18n( wc_date_format() );
			$expiration_message   = sprintf( $expiration_message, '<span class="expiration-date">' . $formatted_expiration . '</span>' );

			?>
			<small class="yith-wcdp-expiration-notice">
				<?php echo wp_kses_post( $expiration_message ); ?>
			</small>
			<?php
		}

		/* === PRODUCT NOTES === */

		/**
		 * Adds additional product deposit note to correct action
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_additional_product_note() {
			$position = get_option( 'yith_wcdp_deposit_labels_product_note_position', 'woocommerce_template_single_meta' );
			$action   = 'woocommerce_product_meta_end';
			$priority = 10;

			switch ( $position ) {
				case 'none':
					return;
				case 'woocommerce_template_single_title':
					$action   = 'woocommerce_single_product_summary';
					$priority = 7;
					break;
				case 'woocommerce_template_single_price':
					$action   = 'woocommerce_single_product_summary';
					$priority = 15;
					break;
				case 'woocommerce_template_single_excerpt':
					$action   = 'woocommerce_single_product_summary';
					$priority = 25;
					break;
				case 'woocommerce_template_single_add_to_cart':
					$action   = 'woocommerce_single_product_summary';
					$priority = 35;
					break;
				case 'woocommerce_template_single_sharing':
					$action   = 'woocommerce_single_product_summary';
					$priority = 55;
					break;
				case 'woocommerce_product_meta_end':
				default:
					break;
			}

			add_action( $action, array( $this, 'print_additional_product_note' ), $priority );
		}

		/**
		 * Print additional product deposit note
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function print_additional_product_note() {
			global $product;

			if ( ! is_product() || ! $product ) {
				return;
			}

			$product_id = $product->get_id();
			$enabled    = YITH_WCDP_Deposits::is_enabled( $product_id );
			$show_notes = yith_plugin_fw_is_true( YITH_WCDP_Options::get( 'show_product_notes', $product_id ) );

			/**
			 * APPLY_FILTERS: yith_wcdp_print_deposit_product_notice
			 *
			 * Filters whether to show the notice on the product page.
			 *
			 * @param bool $show_notes Whether to show the notice or not.
			 * @param int  $product_id Product ID.
			 *
			 * @return bool
			 */
			if ( ! $enabled || ! apply_filters( 'yith_wcdp_print_deposit_product_notice', $show_notes, $product_id ) ) {
				return;
			}

			$note = YITH_WCDP_Options::read_product_property( $product_id, 'product_note' );

			if ( empty( $note ) ) {
				$create_balance = YITH_WCDP_Options::get( 'create_balance', $product_id );

				if ( 'on-hold' === $create_balance ) {
					$note = get_option( 'yith_wcdp_deposit_labels_pay_in_loco' );
				} elseif ( 'pending' === $create_balance ) {
					$note = get_option( 'yith_wcdp_deposit_labels_product_note' );
				}
			}

			if ( empty( $note ) ) {
				return;
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_deposit_print_product_note
			 *
			 * Filters the deposit product note HTML.
			 *
			 * @param string                   The HTML.
			 * @param WC_Product $product      The product.
			 * @param string     $general_note The general note.
			 *
			 * @return string
			 */
			echo wp_kses_post( apply_filters( 'yith_wcdp_deposit_print_product_note', "<div class='yith-wcdp-product-note'>{$note}</div>", $product, $note ) );
		}

		/**
		 * Add additional notes for variation
		 *
		 * @param WC_Product_variable $product Current product.
		 */
		public function print_additional_variation_note( $product ) {
			if ( ! $product || ! $product->is_type( 'variation' ) ) {
				return;
			}

			$override  = yith_plugin_fw_is_true( $product->get_meta( '_override_deposit_options' ) );
			$show_note = yith_plugin_fw_is_true( $product->get_meta( '_show_product_notes' ) );
			$note      = $product->get_meta( '_product_note' );

			if ( ! $override || ! $show_note || ! $note ) {
				return;
			}

			?>
			<span class="variation-notes">
				<?php echo wp_kses_post( $note ); ?>
			</span>
			<?php
		}

		/* === EXPIRATION HANDLING === */

		/**
		 * Filters is purchasable for a specific product, to make it no longer available after expiration
		 *
		 * @param bool       $is_purchasable Whether current product is purchasable.
		 * @param WC_Product $product        Current product.
		 *
		 * @return bool Filtered value
		 */
		public function is_deposit_product_purchasable( $is_purchasable, $product ) {
			$product_id = $product->get_id();

			$expiration_fallback = YITH_WCDP_Deposits::get_expiration_fallback( $product_id );

			if ( 'item_not_purchasable' === $expiration_fallback ) {
				$deposit_expired = YITH_WCDP_Deposits::has_expired( $product_id );

				return ! $deposit_expired ? $is_purchasable : false;
			}

			return $is_purchasable;
		}

		/**
		 * Filters is visible for a specific product, to make it no longer available after expiration
		 *
		 * @param bool $is_visible Whether current product is purchasable.
		 * @param int  $product_id Current product id.
		 *
		 * @return bool Filtered value
		 */
		public function is_deposit_product_visible( $is_visible, $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return $is_visible;
			}

			$expiration_fallback = YITH_WCDP_Deposits::get_expiration_fallback( $product_id );

			if ( 'hide_item' === $expiration_fallback ) {
				$deposit_expired = YITH_WCDP_Deposits::has_expired( $product_id );

				return ! $deposit_expired ? $is_visible : 'hidden';
			}

			return $is_visible;
		}

		/* === GENERAL FRONTEND METHODS === */

		/**
		 * Enqueue frontend assets
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			global $post;

			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			// include js required.
			$template_name = 'deposit-and-down-payments.js';
			$locations     = array(
				trailingslashit( WC()->template_path() ) . 'yith-wcdp/' . $template_name,
				trailingslashit( WC()->template_path() ) . $template_name,
				'yith-wcdp/' . $template_name,
				$template_name,
			);

			$template_js = locate_template( $locations );

			if ( ! $template_js ) {
				$template_js = YITH_WCDP_URL . 'assets/js/yith-wcdp.bundle' . $suffix . '.js';
			} else {
				$search      = array( get_stylesheet_directory(), get_template_directory() );
				$replace     = array( get_stylesheet_directory_uri(), get_template_directory_uri() );
				$template_js = str_replace( $search, $replace, $template_js );
			}

			wp_register_script(
				'yith-wcdp',
				/**
				 * APPLY_FILTERS: yith_wcdp_enqueue_frontend_script_template_js
				 *
				 * Filters the 'yith-wcdp' script path.
				 *
				 * @param string $template_js The path to the script file.
				 *
				 * @return string
				 */
				apply_filters( 'yith_wcdp_enqueue_frontend_script_template_js', $template_js ),
				array(
					'jquery',
					'wc-add-to-cart-variation',
					'jquery-blockui',
					'selectWoo',
					'wc-country-select',
					'wc-address-i18n',
					'accounting',
				),
				YITH_WCDP::YITH_WCDP_VERSION,
				true
			);

			$template_name = 'deposit-and-down-payments.css';
			$locations     = array(
				trailingslashit( WC()->template_path() ) . 'yith-wcdp/' . $template_name,
				trailingslashit( WC()->template_path() ) . $template_name,
				'yith-wcdp/' . $template_name,
				$template_name,
			);

			$template_css = locate_template( $locations );

			if ( ! $template_css ) {
				$template_css = YITH_WCDP_URL . 'assets/css/yith-wcdp.css';
			} else {
				$search       = array( get_stylesheet_directory(), get_template_directory() );
				$replace      = array( get_stylesheet_directory_uri(), get_template_directory_uri() );
				$template_css = str_replace( $search, $replace, $template_css );
			}

			wp_register_style( 'yith-wcdp', $template_css, array( 'select2', 'yith-plugin-fw-icon-font' ), YITH_WCDP::YITH_WCDP_VERSION );

			$should_enqueue = is_product() || is_cart() || is_checkout() || is_account_page() || has_shortcode( $post instanceof WP_Post ? $post->post_content : '', 'product_page' ) || has_shortcode( $post instanceof WP_Post ? $post->post_content : '', 'booking_form' );

			if ( ! $should_enqueue ) {
				return;
			}

			/**
			 * DO_ACTION: yith_wcdp_enqueue_frontend_script
			 *
			 * Action triggered before enqueueing 'yith-wcdp' frontend script.
			 */
			do_action( 'yith_wcdp_enqueue_frontend_script' );

			wp_enqueue_script( 'yith-wcdp' );
			wp_localize_script(
				'yith-wcdp',
				'yith_wcdp',
				array(
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'actions'         => array(
						'calculate_shipping' => array(
							'name'  => 'yith_wcdp_calculate_shipping',
							'nonce' => wp_create_nonce( 'calculate-shipping' ),
						),
						'change_location'    => array(
							'name'  => 'yith_wcdp_change_location',
							'nonce' => wp_create_nonce( 'change-location' ),
						),
						'get_add_deposit'    => array(
							'name'  => 'get_add_deposit_to_cart_template',
							'nonce' => wp_create_nonce( 'get_add_deposit_to_cart_template' ),
						),
					),
					'currency_format' => array(
						'symbol'    => get_woocommerce_currency_symbol(),
						'decimal'   => esc_attr( wc_get_price_decimal_separator() ),
						'thousand'  => esc_attr( wc_get_price_thousand_separator() ),
						'precision' => wc_get_price_decimals(),
						'format'    => esc_attr(
							str_replace(
								array( '%1$s', '%2$s' ),
								array(
									'%s',
									'%v',
								),
								get_woocommerce_price_format()
							)
						),
					),
					'labels'          => array(
						'deposit_expiration_modal_title' => __( 'Details of balance payments', 'yith-woocommerce-deposits-and-down-payments' ),
					),
					'ajax_variations' => 'yes' === get_option( 'yith_wcdp_general_enable_ajax_variation', 'no' ),
				)
			);

			/**
			 * DO_ACTION: yith_wcdp_enqueue_frontend_style
			 *
			 * Action triggered before enqueueing 'yith-wcdp' frontend style.
			 */
			do_action( 'yith_wcdp_enqueue_frontend_style' );

			wp_enqueue_style( 'yith-wcdp' );
		}

		/* === UTILITY METHODS === */

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_Frontend
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

/**
 * Unique access to instance of YITH_WCDP_Frontend class
 *
 * @return \YITH_WCDP_Frontend
 * @since 1.0.0
 */
function YITH_WCDP_Frontend() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid, Universal.Files.SeparateFunctionsFromOO
	return YITH_WCDP_Frontend::get_instance();
}

/**
 * Legacy function, left just for backward compatibility
 *
 * @return \YITH_WCDP_Frontend
 */
function YITH_WCDP_Frontend_Premium() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	_deprecated_function( 'YITH_WCDP_Frontend_Premium', '2.0.0', 'YITH_WCDP_Frontend' );

	return YITH_WCDP_Frontend();
}

// create legacy class alias.
class_alias( 'YITH_WCDP_Frontend', 'YITH_WCDP_Frontend_Premium' );
