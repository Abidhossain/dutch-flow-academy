<?php
/**
 * Balance Shipping handling class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Shipping' ) ) {
	/**
	 * Handles shipping for the balance orders created
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Shipping {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_filter( 'woocommerce_cart_needs_shipping', array( self::class, 'needs_shipping' ) );
			add_filter( 'woocommerce_cart_needs_shipping_address', array( self::class, 'needs_shipping' ) );
			add_filter( 'woocommerce_cart_shipping_packages', array( self::class, 'shipping_packages' ) );
			add_action( 'woocommerce_after_calculate_totals', array( self::class, 'subtract_balance_shipping_costs' ), 10, 1 );
		}

		/**
		 * Mark cart for shipping when it contains deposits of products that needs it
		 *
		 * @param bool $needs_shipping Whether cart needs shipping or not.
		 *
		 * @return bool Whether cart needs shipping or not.
		 */
		public static function needs_shipping( $needs_shipping ) {
			if ( ! apply_filters( 'yith_wcdp_virtual_on_deposit', true, null ) ) {
				return $needs_shipping;
			}

			$cart = WC()->cart;

			if ( ! $cart ) {
				return $needs_shipping;
			}

			$cart_contents = $cart->get_cart_contents();

			foreach ( $cart_contents as $cart_item ) {
				/**
				 * Product object
				 *
				 * @var $product WC_Product
				 */
				$product = wc_get_product( $cart_item['product_id'] );

				if ( empty( $cart_item['deposit'] ) || ! $product->needs_shipping() ) {
					continue;
				}

				return true;
			}

			return $needs_shipping;
		}

		/**
		 * Filters shipping packages, to add those related to balance orders
		 *
		 * @param array $packages Array of shipping packages.
		 *
		 * @return array Array of filtered packages.
		 */
		public static function shipping_packages( $packages ) {
			if ( ! apply_filters( 'yith_wcdp_virtual_on_deposit', true, null ) ) {
				return $packages;
			}

			$balance_packages = self::get_packages();

			// create a set of items shipped via balance orders.
			$balance_shipped_items = call_user_func_array( 'array_merge', wp_list_pluck( $balance_packages, 'contents' ) );

			// cycle through packages, to remove items shipped via balance orders.
			foreach ( $packages as $key => & $package ) {
				$package_items = array_diff_key( $package['contents'], $balance_shipped_items );

				if ( empty( $package_items ) ) {
					unset( $packages[ $key ] );
					continue;
				}

				$package['contents']      = $package_items;
				$package['contents_cost'] = array_sum( wp_list_pluck( $package_items, 'line_total' ) );
			}

			return array_merge(
				array_values( $packages ),
				$balance_packages
			);
		}

		/**
		 * Subtract from cart total the amount of shipping related to balance orders
		 *
		 * @param WC_Cart $cart Cart object.
		 */
		public static function subtract_balance_shipping_costs( $cart ) {
			$balance_shipping = WC()->shipping()->calculate_shipping( $cart->get_shipping_packages() );

			$total_to_subtract = 0;
			$tax_to_subtract   = 0;

			foreach ( $balance_shipping as $key => $package ) {
				if ( empty( $package['balance_package'] ) ) {
					continue;
				}

				$chosen_method = wc_get_chosen_shipping_method_for_package( $key, $package );

				if ( empty( $chosen_method ) ) {
					continue;
				}

				$method = $package['rates'][ $chosen_method ];

				$total_to_subtract += $method->get_cost();
				$tax_to_subtract   += array_sum( $method->get_taxes() );
			}

			$cart->set_shipping_total( $cart->get_shipping_total() - $total_to_subtract );
			$cart->set_shipping_tax( $cart->get_shipping_tax() - $tax_to_subtract );
			$cart->set_total( $cart->get_total( 'edit' ) - $total_to_subtract - $tax_to_subtract );
		}

		/**
		 * Returns balance packages
		 *
		 * @return array Array of balance packages.
		 */
		public static function get_packages() {
			$cart              = WC()->cart;
			$packages          = array();
			$cart_contents     = $cart->get_cart_contents();
			$propagate_coupons = apply_filters( 'yith_wcdp_propagate_coupons', false );
			$packages_contents = array();

			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				/**
				 * Product object
				 *
				 * @var $product WC_Product
				 */
				$product_id = $cart_item['variation_id'] > 0 ? $cart_item['variation_id'] : $cart_item['product_id'];
				$product    = wc_get_product( $product_id );

				if ( empty( $cart_item['deposit'] ) || ! $product->needs_shipping() ) {
					continue;
				}

				$package_key = self::get_package_key( $cart_item_key, $cart_item );

				if ( ! isset( $packages_contents[ $package_key ] ) ) {
					$packages_contents[ $package_key ] = array();
				}
				$cart_item['data']                                   = $product;
				$packages_contents[ $package_key ][ $cart_item_key ] = $cart_item;
			}

			foreach ( $packages_contents as $key => $contents ) {
				$subtotal = array_sum( wp_list_pluck( $contents, 'deposit_balance' ) );

				$packages[] = array(
					'contents'           => $contents,
					'contents_cost'      => $subtotal,
					'applied_coupons'    => $propagate_coupons ? $cart->get_applied_coupons() : array(),
					'user'               => array(
						'ID' => get_current_user_id(),
					),
					'destination'        => array(
						'country'   => $cart->get_customer()->get_shipping_country(),
						'state'     => $cart->get_customer()->get_shipping_state(),
						'postcode'  => $cart->get_customer()->get_shipping_postcode(),
						'city'      => $cart->get_customer()->get_shipping_city(),
						'address'   => $cart->get_customer()->get_shipping_address(),
						'address_1' => $cart->get_customer()->get_shipping_address(),
						'address_2' => $cart->get_customer()->get_shipping_address_2(),
					),
					'cart_subtotal'      => $subtotal,
					'balance_package'    => $key,
					'balance_expiration' => YITH_WCDP_Products::get_expiration( wp_list_pluck( $contents, 'data' ) ),
				);
			}

			return $packages;
		}

		/**
		 * Returns key of the shipping package a specific cart item should be assigned to
		 *
		 * @param string $cart_item_key Cart item key.
		 * @param array  $cart_item Cart item.
		 *
		 * @return string|bool Package key, or false.
		 */
		protected static function get_package_key( $cart_item_key, $cart_item ) {
			if ( empty( $cart_item['deposit'] ) ) {
				return false;
			}

			$balance_type = get_option( 'yith_wcdp_balance_type', 'multiple' );

			if ( 'multiple' === $balance_type ) {
				$key = $cart_item_key;
			} else {
				$key = 'common';
			}

			return apply_filters( 'yith_wcdp_shipping_package_key', $key, $cart_item_key, $cart_item );
		}
	}
}
