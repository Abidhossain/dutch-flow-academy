<?php
/**
 * Compatibility class with Dynamic Pricing and Discounts
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_YITH_Dynamic_Pricing_And_Discounts_Compatibility' ) ) {
	/**
	 * Deposit - Dynamic Pricing compatibility
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_YITH_Dynamic_Pricing_And_Discounts_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCDP_YITH_Dynamic_Pricing_And_Discounts_Compatibility
		 * @since 1.0.5
		 */
		protected static $instance;

		/**
		 * Constructor.
		 *
		 * @since 1.0.5
		 */
		public function __construct() {
			add_filter( 'yith_wcdp_process_cart_item_product_change', array( $this, 'cart_item_product_change' ), 10, 2 );
			if ( version_compare( YITH_YWDPD_VERSION, '2.4.0', '>' ) ) {
				add_action( 'init', array( $this, 'init_integration' ), 99 );
			} else {
				add_filter( 'yith_wcdp_product_price_for_deposit_operation', array( $this, 'change_base_product_price' ), 20, 2 );
			}

			add_filter( 'woocommerce_cart_item_price', array( $this, 'replace_cart_item_price' ), 300, 3 );
			add_action( 'woocommerce_add_to_cart', array( $this, 'process_cart_item_product_change' ), 300 );
			add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'process_cart_item_product_change' ), 300 );
			add_action( 'woocommerce_before_calculate_totals', array( $this, 'process_cart_item_product_change' ), 300 );
		}

		/**
		 * Change cart item.
		 *
		 * @param bool  $bool      Change cart item.
		 * @param array $cart_item Cart item.
		 *
		 * @return bool
		 */
		public function cart_item_product_change( $bool, $cart_item ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.boolFound
			$product = $cart_item['data'];

			if ( $this->is_enabled_for_product( $product->get_id() ) ) {
				$bool = false;
			}

			return $bool;
		}

		/**
		 * Init the integration with Dyanmic 3.0
		 */
		public function init_integration() {
			add_filter( 'ywdpd_skip_cart_check', array( $this, 'skip_cart_check' ), 20, 2 );
			add_action( 'yith_wcdp_after_add_to_support_cart', array( $this, 'fix_product_price' ), 20, 6 );
			add_filter( 'yith_wcdp_deposist_value', array( $this, 'change_deposit_value' ), 10, 2 );
		}

		/**
		 * Filter deposit value
		 *
		 * @param float      $deposit_amount Deposit amount.
		 * @param WC_Product $product Product object.
		 *
		 * @return float Filtered deposit value.
		 */
		public function change_deposit_value( $deposit_amount, $product ) {
			$price         = $product->get_price();
			$dynamic_price = ywdpd_dynamic_pricing_discounts()->get_frontend_manager()->get_dynamic_price( $price, $product );

			return min( YITH_WCDP_Deposits::get_deposit( $product->get_id(), false, $dynamic_price, 'view' ), $price );
		}

		/**
		 * Reset roduct price to original
		 *
		 * @param int   $product_id         Product id.
		 * @param int   $quantity           Quantity in cart.
		 * @param int   $variation_id       Variation id.
		 * @param array $variation          Variation attributes.
		 * @param array $old_cart_item_data Cart item.
		 * @param array $new_cart_item      Cart item.
		 */
		public function fix_product_price( $product_id, $quantity, $variation_id, $variation, $old_cart_item_data, $new_cart_item ) {
			if ( isset( $old_cart_item_data['ywdpd_discounts']['price_adjusted'], $new_cart_item['data'] ) && $new_cart_item['data'] instanceof WC_Product ) {
				$new_cart_item['data']->set_price( $old_cart_item_data['ywdpd_discounts']['price_adjusted'] );
			}
		}

		/**
		 * Skip the check if is a Support cart
		 *
		 * @param bool    $skip Skip the check.
		 * @param WC_Cart $cart The cart object.
		 */
		public function skip_cart_check( $skip, $cart ) {
			$skip = $cart instanceof YITH_WCDP_Support_Cart;

			return $skip;
		}


		/**
		 * Execute deposit calculations after Dynamic discounts
		 *
		 * TODO: this method needs to be reviewed, to execute only for products that are affected by Dynamic rules
		 * (currently it replaces entirely the plugin Add to Cart behaviour)
		 *
		 * @return void
		 * @since 1.0.5
		 */
		public function process_cart_item_product_change() {

			$cart = WC()->cart;

			if ( $cart instanceof YITH_WCDP_Support_Cart ) {
				return;
			}

			if ( $cart->is_empty() ) {
				return;
			}

			foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( empty( $cart_item['deposit'] ) ) {
					continue;
				}

				/**
				 * Product object for current cart item
				 *
				 * @var $product \WC_Product
				 */
				$product = $cart_item['data'];

				if ( ! $this->is_enabled_for_product( $product->get_id() ) ) {
					continue;
				}

				$price = wc_get_product( $product->get_id() )->get_price();

				if ( ! empty( $cart_item['ywdpd_discounts'] ) ) {
					if ( version_compare( YITH_YWDPD_VERSION, '2.4.0', '<=' ) ) {
						foreach ( $cart_item['ywdpd_discounts'] as $discount ) {
							if ( isset( $discount['status'] ) && 'applied' === $discount['status'] ) {

								$price = $discount['current_price'];
							}

							if ( 'bulk' === $discount['discount_mode'] ) {
								break;
							}
						}
					} else {
						$price = $cart_item['ywdpd_discounts']['price_adjusted'];
					}
				}

				$product_id      = $product->get_id();
				$variation_id    = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : false;
				$deposit_value   = apply_filters( 'yith_wcdp_deposit_value', YITH_WCDP_Deposits::get_deposit( $product_id, false, $price ), $product_id, $variation_id, $cart_item );
				$deposit_balance = apply_filters( 'yith_wcdp_deposit_balance', max( $price - $deposit_value, 0 ), $product_id, $variation_id, $cart_item );

				$product->set_price( $deposit_value );
				$product->update_meta_data( 'yith_wcdp_deposit', true );

				if ( apply_filters( 'yith_wcdp_virtual_on_deposit', true, null ) ) {
					$product->set_virtual( true );
				}

				$cart->cart_contents[ $cart_item_key ]['deposit_value']   = $deposit_value;
				$cart->cart_contents[ $cart_item_key ]['deposit_balance'] = $deposit_balance;

			}
		}

		/**
		 * Updates cart item price, according to both Deposit and Dynamic rules
		 *
		 * @param string $price_html Html for the cart item price.
		 * @param array  $cart_item Cart item.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return string Filtered html for the price.
		 */
		public function replace_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
			$cart = WC()->cart;

			if ( $cart instanceof YITH_WCDP_Support_Cart ) {
				return $price_html;
			}

			if ( empty( $cart_item['deposit_value'] ) || empty( $cart_item['ywdpd_discounts'] ) ) {
				return $price_html;
			}

			$product = $cart_item['data'];
			$price   = $product->get_price();

			if ( version_compare( YITH_YWDPD_VERSION, '2.4.0', '<=' ) ) {
				if ( YITH_WCDP_Deposits::is_enabled( $product->get_id() ) ) {
					$price = YITH_WCDP_Deposits::get_deposit( $product->get_id(), false, $price );
					WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $price );
				}

				$old_price = $price;

				if ( isset( $cart_item['ywdpd_discounts'] ) ) {

					foreach ( $cart_item['ywdpd_discounts'] as $discount ) {
						if ( isset( $discount['status'] ) && 'applied' === $discount['status'] ) {

							$old_price = $discount['current_price'];
						}

						if ( 'bulk' === $discount['discount_mode'] ) {
							break;
						}
					}
				}
			} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
				if ( isset( $cart_item['ywdpd_discounts'] ) ) {
					$old_price = $cart_item['ywdpd_discounts']['price_adjusted'];

					if ( YITH_WCDP_Deposits::is_enabled( $product->get_id() ) ) {
						$price = YITH_WCDP_Deposits::get_deposit( $product->get_id(), false, $old_price );
						WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $price );
					}
				}
			}

			if ( $old_price !== $price ) {
				$price_html = '<del>' . wc_price( $old_price ) . '</del> ' . wc_price( $price );
			}

			return $price_html;
		}

		/**
		 * Filters price used as a base for deposit calculation
		 *
		 * @param float      $price Price used for calculation.
		 * @param WC_Product $product Product object.
		 *
		 * @return float Filtered price.
		 */
		public function change_base_product_price( $price, $product ) {
			if ( function_exists( 'YITH_WC_Dynamic_Pricing' ) && is_product() ) {
				$price = YITH_WC_Dynamic_Pricing()->get_discount_price( $price, $product );
			}

			return $price;
		}

		/**
		 * Whether the integration is enabled for products
		 *
		 * @param int $product_id Product ID.
		 *
		 * @return bool
		 */
		public function is_enabled_for_product( $product_id ) {
			return apply_filters( 'yith_wcdp_is_enabled_for_product', true, $product_id );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP_YITH_Dynamic_Pricing_And_Discounts_Compatibility
		 * @since 1.0.5
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
