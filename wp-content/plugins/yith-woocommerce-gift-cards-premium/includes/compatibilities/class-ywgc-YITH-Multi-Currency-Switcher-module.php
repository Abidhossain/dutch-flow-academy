<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase, WordPress.Files.FileName.InvalidClassFileName
/**
 * YWGC_YITH_WCMCS_Module class
 *
 * @package YITH\GiftCards\Classes\Compatibilities
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWGC_YITH_WCMCS_Module' ) ) {
	/**
	 * Class to manage the integration with Multi Currency Switcher
	 *
	 * @class   YWGC_YITH_WCMCS_Module
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YWGC_YITH_WCMCS_Module {

		/**
		 * Single instance of the class
		 *
		 * @var YWGC_YITH_WCMCS_Module
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
		 */
		public function __construct() {
			add_filter( 'yith_ywgc_gift_card_coupon_amount', array( $this, 'yith_convert_from_gift_card_currency_to_selected_currency' ), 10, 2 );

			add_filter( 'ywgc_get_gift_card_balance_amount_for_coupon', array( $this, 'yith_convert_from_gift_card_currency_to_selected_currency' ), 10, 2 );

			add_filter( 'ywgc_amount_to_deduct_in_gift_card_from_coupon', array( $this, 'yith_convert_from_gift_card_currency_to_selected_currency' ), 10, 2 );

			add_filter( 'yith_ywgc_new_balance_before_update_balance', array( $this, 'yith_convert_new_balance' ), 10, 3 );

			add_filter( 'yith_ywgc_get_gift_card_price', array( $this, 'yith_convert_from_gift_card_currency_to_selected_currency' ), 10, 2 );

			add_filter( 'yith_ywgc_gift_card_amounts', array( $this, 'convert_amounts_automatically_on_product_page' ), 10, 2 );

			add_action( 'yith_ywgc_multi_currency_settings', array( $this, 'print_multi_currency_prices_for_gift_cards' ), 10, 0 );

			add_action( 'yith_gift_cards_after_product_save', array( $this, 'save_gift_card_product_manual_prices_handler' ) );

			add_filter( 'yith_wcmcs_pre_product_regular_price', array( $this, 'ywgc_block_convert_if_gift_card' ), 10, 3 );
			add_filter( 'yith_wcmcs_pre_product_sale_price', array( $this, 'ywgc_block_convert_if_gift_card' ), 10, 3 );
			add_filter( 'yith_wcmcs_pre_product_price', array( $this, 'ywgc_convert_if_gift_card' ), 10, 3 );

			add_filter( 'yith_ywgc_submitting_manual_amount', array( $this, 'ywgc_convert_manual_amount' ), 10, 1 );
			add_filter( 'yith_ywgc_submitting_select_amount', array( $this, 'ywgc_convert_selected_amount' ), 10, 2 );

			add_filter( 'ywgc_amount_selection_hidden_amount', array( $this, 'ywgc_amount_selection_hidden_convert_to_base' ), 10, 2 );
			add_filter( 'ywgc_amount_selection_hidden_price', array( $this, 'ywgc_amount_selection_hidden_convert_to_base' ), 10, 2 );

			add_filter( 'yith_wcmcs_product_prices_options_group_classes', array( $this, 'yith_wcmcs_product_prices_options_group_classes_for_gift_cards' ), 10, 1 );

			add_action( 'yith_gift_cards_template_after_amounts', array( $this, 'yith_gift_cards_template_after_amounts_add_currency' ) );

			add_filter( 'ywgc_save_total_balance', array( $this, 'ywgc_format_amount_based_in_currency' ), 10, 3 );
			add_filter( 'ywgc_save_total_amount', array( $this, 'ywgc_format_amount_based_in_currency' ), 10, 3 );
			add_filter( 'ywgc_get_total_balance', array( $this, 'ywgc_round_amounts_based_in_currency' ), 10, 3 );

			add_filter( 'yith_wcgc_table_get_balance', array( $this, 'yith_wcgc_table_get_balance_in_currency_format' ), 10, 2 );

			add_filter( 'yith_wcmcs_manual_coupon_amount', array( $this, 'yith_ywgc_block_coupon_convert_if_gift_card' ), 10, 3 );
		}

		/**
		 * Filter the new balance of the gift card in the correct currency
		 *
		 * @param float  $new_balance The new gift card balance.
		 * @param object $gift_card   The gift card object.
		 * @param float  $amount      The gift card amount.
		 */
		public function yith_convert_new_balance( $new_balance, $gift_card, $amount ) {
			$currency_id = function_exists( 'yith_wcmcs_get_current_currency_id' ) ? yith_wcmcs_get_current_currency_id() : get_woocommerce_currency();

			if ( $currency_id === $gift_card->currency ) {
				return $new_balance;
			} else {
				$amount = apply_filters(
					'yith_wcmcs_convert_price',
					$amount,
					array(
						'from' => $currency_id,
						'to'   => $gift_card->currency,
					)
				);

				$new_balance = $gift_card->get_balance() - $amount;
			}

			return $new_balance;
		}

		/**
		 * Convert the gift card amount in the gift card currency to the customer selected currency
		 *
		 * @param float  $gift_card_balance The gift card balance.
		 * @param object $gift_card         The gift card object.
		 */
		public function yith_convert_from_gift_card_currency_to_selected_currency( $gift_card_balance, $gift_card ) {
			$currency_id = function_exists( 'yith_wcmcs_get_current_currency_id' ) ? yith_wcmcs_get_current_currency_id() : get_woocommerce_currency();

			if ( $currency_id === $gift_card->currency ) {
				return $gift_card_balance;
			} else {
				$gift_card_balance = apply_filters(
					'yith_wcmcs_convert_price',
					$gift_card_balance,
					array(
						'from' => $gift_card->currency,
						'to'   => $currency_id,
					)
				);
			}

			return $gift_card_balance;
		}

		/**
		 * Convert the gift card amount in the selected currency to the gift card currency
		 *
		 * @param float  $gift_card_balance The gift card balance.
		 * @param object $gift_card         The gift card object.
		 */
		public function yith_convert_from_selected_currency_to_gift_card_currency( $gift_card_balance, $gift_card ) {
			$currency_id = function_exists( 'yith_wcmcs_get_current_currency_id' ) ? yith_wcmcs_get_current_currency_id() : get_woocommerce_currency();

			if ( $currency_id === $gift_card->currency ) {
				return $gift_card_balance;
			} else {
				$gift_card_balance = apply_filters(
					'yith_wcmcs_convert_price',
					$gift_card_balance,
					array(
						'from' => $currency_id,
						'to'   => $gift_card->currency,
					)
				);
			}

			return $gift_card_balance;
		}

		/**
		 * Convert the gift card products amounts automatically in the product page if not custom prices are defined for the different currencies
		 *
		 * @param array  $amounts   Gift card amounts.
		 * @param object $gift_card Gift card object.
		 */
		public function convert_amounts_automatically_on_product_page( $amounts, $gift_card ) {
			$custom_prices_enabled = 'yes' === get_post_meta( $gift_card->get_id(), '_ywgc_wcmcs_custom_prices', true );

			if ( $custom_prices_enabled ) {
				return $amounts;
			}

			$amount_aux = array();

			foreach ( $amounts as $amount ) {
				$amount_converted = apply_filters( 'yith_wcmcs_convert_price', $amount );
				$amount_aux[]     = $amount_converted;
			}

			return $amount_aux;
		}

		/**
		 * Manage the gift card prices conversion
		 *
		 * @param null       $converted_price The converted price value.
		 * @param string     $price           Price.
		 * @param WC_Product $product         Product.
		 */
		public function ywgc_convert_if_gift_card( $converted_price, $price, $product ) {
			if ( $product->get_type() === 'gift-card' && method_exists( $product, 'get_product_amounts_by_currency' ) ) {
				$custom_prices_enabled = 'yes' === get_post_meta( $product->get_id(), '_ywgc_wcmcs_custom_prices', true );
				$currency_id           = function_exists( 'yith_wcmcs_get_current_currency_id' ) ? yith_wcmcs_get_current_currency_id() : get_woocommerce_currency();
				$default_currency_id   = function_exists( 'yith_wcmcs_get_wc_currency_options' ) ? yith_wcmcs_get_wc_currency_options( 'currency' ) : get_woocommerce_currency();
				$amounts_in_currency   = $product->get_product_amounts_by_currency( $currency_id );
				$default_amounts       = $product->get_product_amounts();

				if ( $custom_prices_enabled && $currency_id !== $default_currency_id ) {
					$amount_index            = array_search( $price, $default_amounts, true );
					$is_manual_amount        = in_array( $price, $default_amounts, true ) ? 'no' : 'yes';
					$linked_gift_card_amount = 'no' === $is_manual_amount ? $amounts_in_currency[ $amount_index ] : round( apply_filters( 'yith_wcmcs_convert_price', (float) $price ), 0 );
					$amount_converted        = str_replace( ',', '.', $linked_gift_card_amount );
				} else {
					$amount_converted = apply_filters( 'yith_wcmcs_convert_price', (float) $price );
				}

				return $amount_converted;
			} else {
				return $converted_price;
			}
		}

		/**
		 * Manage the gift card manual amount conversion
		 *
		 * @param float $amount Gift card amount.
		 */
		public function ywgc_convert_manual_amount( $amount ) {
			$currency_id         = function_exists( 'yith_wcmcs_get_current_currency_id' ) ? yith_wcmcs_get_current_currency_id() : get_woocommerce_currency();
			$default_currency_id = function_exists( 'yith_wcmcs_get_wc_currency_options' ) ? yith_wcmcs_get_wc_currency_options( 'currency' ) : get_woocommerce_currency();
			$amount              = apply_filters(
				'yith_wcmcs_convert_price',
				$amount,
				array(
					'from' => $currency_id,
					'to'   => $default_currency_id,
				)
			);

			return $amount;
		}

		/**
		 * Manage the gift card predefined amount conversion
		 *
		 * @param float      $amount  Gift card amount.
		 * @param WC_Product $product Product object.
		 */
		public function ywgc_convert_selected_amount( $amount, $product ) {
			$custom_prices_enabled = 'yes' === get_post_meta( $product->get_id(), '_ywgc_wcmcs_custom_prices', true );
			$currency_id           = function_exists( 'yith_wcmcs_get_current_currency_id' ) ? yith_wcmcs_get_current_currency_id() : get_woocommerce_currency();

			if ( $custom_prices_enabled ) {
				$amounts_in_currency = $product->get_product_amounts_by_currency( $currency_id );
				$default_amounts     = $product->get_product_amounts();
				$amount_index        = array_search( $amount, $amounts_in_currency, true );
				$amount              = $default_amounts[ $amount_index ];
			}

			return $amount;
		}

		/**
		 * Don't convert prices for gift card products
		 *
		 * @param null       $converted_price The converted price value.
		 * @param string     $price           Price.
		 * @param WC_Product $product         Product.
		 */
		public function ywgc_block_convert_if_gift_card( $converted_price, $price, $product ) {
			$custom_prices_enabled = 'yes' === get_post_meta( $product->get_id(), '_ywgc_wcmcs_custom_prices', true );

			if ( $product->get_type() === 'gift-card' && $custom_prices_enabled ) {
				return $price;
			}

			return $converted_price;
		}

		/**
		 * Hide the default YITH Multi Currency Options for gift cards
		 *
		 * @param array $classes Array of classes.
		 */
		public function yith_wcmcs_product_prices_options_group_classes_for_gift_cards( $classes ) {
			$classes[] = 'hide_if_gift-card';

			return $classes;
		}

		/**
		 * Print multi currency prices for Gift Card products
		 *
		 * @param int|false     $loop           Loop index used in variable product variation.
		 * @param array|false   $variation_data Variation Data.
		 * @param WP_Post|false $variation      Product Variation.
		 */
		public function print_multi_currency_prices_for_gift_cards( $loop = false, $variation_data = false, $variation = false ) {
			global $post;

			$product               = wc_get_product( false === $variation ? $post : $variation );
			$default_currency_id   = function_exists( 'yith_wcmcs_get_wc_currency_options' ) ? yith_wcmcs_get_wc_currency_options( 'currency' ) : get_woocommerce_currency();
			$custom_prices_enabled = 'yes' === $product->get_meta( '_ywgc_wcmcs_custom_prices', true );
			$currencies            = function_exists( 'yith_wcmcs_get_currencies' ) ? yith_wcmcs_get_currencies() : array();

			unset( $currencies[ $default_currency_id ] );

			$prices = function_exists( 'yith_wcmcs_get_product_prices' ) ? yith_wcmcs_get_product_prices( $product ) : array();

			yith_ywgc_get_view( 'gift-cards-product-multi-currency-options.php', compact( 'currencies', 'custom_prices_enabled', 'prices', 'loop' ) );

			wp_enqueue_script( 'yith-plugin-fw-fields' );
			wp_enqueue_style( 'yith-plugin-fw-fields' );
		}

		/**
		 * Save product manual prices
		 *
		 * @param int $product_id Product ID.
		 */
		public function save_gift_card_product_manual_prices_handler( $product_id ) {
			$product = new WC_Product_Gift_Card( $product_id );

			if ( $product ) {
				if ( isset( $_POST['yith_wcmcs_prices_option'] ) && wc_string_to_bool( sanitize_text_field( wp_unslash( $_POST['yith_wcmcs_prices_option'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					update_post_meta( $product_id, '_ywgc_wcmcs_custom_prices', 'yes' );
				} else {
					update_post_meta( $product_id, '_ywgc_wcmcs_custom_prices', 'no' );
				}

				if ( isset( $_POST['yith_wcgc_multi_currency']['gift-card-amounts'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$gift_card_amounts = $_POST['yith_wcgc_multi_currency']['gift-card-amounts']; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

					foreach ( $gift_card_amounts as $currency_id  => $amounts_by_currency ) {
						if ( ! is_array( $amounts_by_currency ) ) {
							$amounts_by_currency = array( $amounts_by_currency );
						}

						$product->save_amounts( $amounts_by_currency, $currency_id );
					}
				}
			}
		}

		/**
		 * Add the default base currency amounts in the hidden inputs if the custom prices are disabled
		 *
		 * @param float      $amount  Gift card amount.
		 * @param WC_Product $product Product object.
		 */
		public function ywgc_amount_selection_hidden_convert_to_base( $amount, $product ) {
			$currency_id           = function_exists( 'yith_wcmcs_get_current_currency_id' ) ? yith_wcmcs_get_current_currency_id() : get_woocommerce_currency();
			$default_currency_id   = function_exists( 'yith_wcmcs_get_wc_currency_options' ) ? yith_wcmcs_get_wc_currency_options( 'currency' ) : get_woocommerce_currency();
			$custom_prices_enabled = 'yes' === get_post_meta( $product->get_id(), '_ywgc_wcmcs_custom_prices', true );

			if ( ! $custom_prices_enabled ) {
				$amount = apply_filters(
					'yith_wcmcs_convert_price',
					$amount,
					array(
						'from' => $currency_id,
						'to'   => $default_currency_id,
					)
				);
			}

			return $amount;
		}

		/**
		 * Add hidden input with the currency ID
		 */
		public function yith_gift_cards_template_after_amounts_add_currency() {
			$currency_id = function_exists( 'yith_wcmcs_get_current_currency_id' ) ? yith_wcmcs_get_current_currency_id() : get_woocommerce_currency();

			?>
			<input type="hidden" name="yith_wcmcs_amounts_currency" value="<?php echo esc_attr( $currency_id ); ?>">
			<?php
		}

		/**
		 * Don't save the amounts rounded
		 *
		 * @param float  $rounded_amount Rounded amount.
		 * @param float  $amount         Amount.
		 * @param object $gift_card      Gift card object.
		 */
		public function ywgc_round_amounts_based_in_currency( $rounded_amount, $amount, $gift_card ) {
			$default_currency_id = function_exists( 'yith_wcmcs_get_wc_currency_options' ) ? yith_wcmcs_get_wc_currency_options( 'currency' ) : get_woocommerce_currency();
			$order               = wc_get_order( $gift_card->order_id );
			$order_currency_id   = is_object( $order ) ? $order->get_currency() : $default_currency_id;
			$currency            = yith_wcmcs_get_currency( $order_currency_id );

			return round( floatval( $amount ), $currency->get_decimals() );
		}

		/**
		 * Don't save the amounts rounded
		 *
		 * @param float  $rounded_amount Rounded amount.
		 * @param float  $amount         Amount.
		 * @param object $gift_card      Gift card object.
		 */
		public function ywgc_format_amount_based_in_currency( $rounded_amount, $amount, $gift_card ) {
			$default_currency_id = function_exists( 'yith_wcmcs_get_wc_currency_options' ) ? yith_wcmcs_get_wc_currency_options( 'currency' ) : get_woocommerce_currency();
			$order               = wc_get_order( $gift_card->order_id );
			$order_currency_id   = is_object( $order ) ? $order->get_currency() : $default_currency_id;
			$currency            = yith_wcmcs_get_currency( $order_currency_id );

			if ( is_string( $amount ) ) {
				$replacements = array(
					''  => $currency->get_thousand_separator(),
					'.' => $currency->get_decimal_separator(),
				);
				$amount       = floatval( str_replace( array_values( $replacements ), array_keys( $replacements ), $amount ) );
			}

			return $amount;
		}

		/**
		 * Get table balance in currency format
		 *
		 * @param string              $balance    Gift card balance.
		 * @param YITH_YWGC_Gift_Card $gift_card Gift card object.
		 */
		public function yith_wcgc_table_get_balance_in_currency_format( $balance, $gift_card ) {
			$default_currency_id = function_exists( 'yith_wcmcs_get_wc_currency_options' ) ? yith_wcmcs_get_wc_currency_options( 'currency' ) : get_woocommerce_currency();
			$order               = wc_get_order( $gift_card->order_id );
			$order_currency_id   = is_object( $order ) ? $order->get_currency() : $default_currency_id;
			$currency            = yith_wcmcs_get_currency( $order_currency_id );

			return wc_price(
				$gift_card->get_balance(),
				array(
					'currency' => $currency->get_id(),
					'decimals' => $currency->get_decimals(),
				)
			);
		}

		/**
		 * Get table balance in currency format
		 *
		 * @param float     $manual_amount Manual amount.
		 * @param float     $amount        Amount.
		 * @param WC_Coupon $coupon        Coupon object.
		 */
		public function yith_ywgc_block_coupon_convert_if_gift_card( $manual_amount, $amount, $coupon ) {
			if ( method_exists( $coupon, 'get_code' ) && function_exists( 'YITH_YWGC' ) && method_exists( YITH_YWGC(), 'get_gift_card_by_code' ) && YITH_YWGC()->get_gift_card_by_code( $coupon->get_code() ) ) {
				$manual_amount = $amount;
			}

			return $manual_amount;
		}
	}
}

YWGC_YITH_WCMCS_Module::get_instance();
