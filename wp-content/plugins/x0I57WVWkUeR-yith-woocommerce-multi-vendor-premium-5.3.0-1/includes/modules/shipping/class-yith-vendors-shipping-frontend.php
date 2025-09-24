<?php
/**
 * YITH_Vendors_Shipping_Frontend class
 *
 * @since      1.11.4
 * @author     YITH
 * @package YITH\MultiVendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

use Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher;

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Shipping_Frontend' ) ) {
	/**
	 * YITH_Vendors_Shipping_Frontend class
	 */
	class YITH_Vendors_Shipping_Frontend {

		/**
		 * An array of vendor cart elements
		 *
		 * @var array
		 */
		private $vendor_cart_elements = array();

		/**
		 * Constructor
		 *
		 */
		public function __construct() {
			add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'woocommerce_cart_shipping_packages' ) );
			add_filter( 'woocommerce_shipping_packages', array( $this, 'woocommerce_shipping_packages' ) );
			add_filter( 'woocommerce_shipping_package_name', array( $this, 'woocommerce_shipping_package_name' ), 10, 3 );

			add_filter( 'woocommerce_package_rates', array( $this, 'woocommerce_package_rates' ), 10, 2 );

			// Single product page tab.
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_shipping_vendor_tab' ), 15 );
		}

		/**
		 * Allow packages to be reorganized after calculating the shipping.
		 * Remove empty packages.
		 *
		 * @since  1.9.17
		 * @param array $packages The array of packages after shipping costs are calculated.
		 * @return array
		 */
		public function woocommerce_shipping_packages( $packages ) {
			foreach ( $packages as $key => $package ) {
				if ( empty( $package['contents'] ) ) {
					unset( $packages[ $key ] );
				}
			}

			return $packages;
		}

		/**
		 * Let's filter packages to calculate shipping for.
		 *
		 * @since  1.9.17
		 * @param array $packages An array of available packages.
		 * @return array
		 */
		public function woocommerce_cart_shipping_packages( $packages ) {
			// Reset vendor cart elements.
			$this->vendor_cart_elements = array();
			$vendors                    = $this->get_vendors_in_cart();

			if ( empty( $vendors ) ) {
				return $packages;
			}

			foreach ( $vendors as $vendor ) {
				if ( YITH_Vendors_Shipping::is_single_vendor_shipping_enabled( $vendor ) ) {
					// First set vendor elements.
					$this->set_vendor_cart_elements( $vendor );
					// Get the vendor package.
					$packages[] = $this->get_package( $vendor );
				}
			}

			// Remove vendor products from WooCommerce shipping packages.
			foreach ( $packages as &$package ) {
				if ( ! isset( $package['yith-vendor'] ) ) {

					if ( empty( $package['contents_cost'] ) ) {
						$package['contents_cost'] = 0;
					}

					if ( empty( $package['contents_taxes_cost'] ) ) {
						$package['contents_taxes_cost'] = 0;
					}

					if ( count( $this->vendor_cart_elements ) ) {
						foreach ( $this->vendor_cart_elements as $product_vendor_cart_key ) {
							if ( apply_filters( 'yith_wcmv_vendor_cart_elements_package', true, $product_vendor_cart_key, $package ) ) {
								unset( $package['contents'][ $product_vendor_cart_key ] );
							}
						}
						$package = $this->calculate_package_cost( $package );
					}
				}
			}

			return $packages;
		}

		/**
		 * Calculate package content cost
		 *
		 * @since  4.0.0
		 * @param array $package The package to process.
		 * @return array
		 */
		protected function calculate_package_cost( $package ) {

			foreach ( $package['contents'] as $item ) {
				$product = $item['data'];
				// Double check if is product.
				if ( ! $product instanceof WC_Product ) {
					continue;
				}
				if ( $product->needs_shipping() ) {
					if ( isset( $item['line_total'] ) ) {
						$package['contents_cost'] += $item['line_total'];
					}

					if ( isset( $item['line_tax'] ) ) {
						$package['contents_taxes_cost'] += $item['line_tax'];
					}
				}
			}

			return $package;
		}

		/**
		 * Build vendor shipping package
		 *
		 * @since 1.9.17
		 * @param YITH_Vendor $vendor   The current processed vendor.
		 * @return array
		 */
		protected function get_package( $vendor ) {
			return $this->calculate_package_cost(
				array(
					'contents'            => $this->get_vendors_cart_contens( $vendor ), // Items in the package.
					'contents_cost'       => 0, // Cost of items in the package, set below.
					'contents_taxes_cost' => 0, // Cost of items taxes in the package, set below.
					'applied_coupons'     => array(),
					'user'                => array(
						'ID' => get_current_user_id(),
					),
					'destination'         => array(
						'country'   => WC()->customer->get_shipping_country(),
						'state'     => WC()->customer->get_shipping_state(),
						'postcode'  => WC()->customer->get_shipping_postcode(),
						'city'      => WC()->customer->get_shipping_city(),
						'address'   => WC()->customer->get_shipping_address(),
						'address_2' => WC()->customer->get_shipping_address_2(),
					),
					'yith-vendor'         => $vendor,
				)
			);
		}

		/**
		 * Get vendors for current cart content
		 *
		 * @since  1.9.17
		 * @return array
		 */
		protected function get_vendors_in_cart() {
			$vendors = array();

			foreach ( WC()->cart->get_cart() as $cart_item ) {
				if ( isset( $cart_item['data'] ) ) {
					$product = $cart_item['data'];
					// Double check if is product.
					if ( ! $product instanceof WC_Product ) {
						continue;
					}
					$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
					$vendor     = yith_wcmv_get_vendor( $product_id, 'product' );
					if ( $vendor && $vendor->is_valid() && YITH_Vendors_Shipping::is_single_vendor_shipping_enabled( $vendor ) && ! in_array( $vendor, $vendors ) ) {
						$vendors[] = $vendor;
					}
				}
			}

			return $vendors;
		}

		/**
		 * Set vendor cart elements
		 *
		 * @since 4.19.0
		 * @param YITH_Vendor $vendor Get vendor products in cart.
		 * @return void
		 */
		protected function set_vendor_cart_elements( $vendor ) {
			if ( ! YITH_Vendors_Shipping::is_single_vendor_shipping_enabled( $vendor ) ) {
				return;
			}

			foreach ( WC()->cart->get_cart() as $key => $cart_item ) {

				if ( isset( $cart_item['data'] ) ) {
					$product = $cart_item['data'];
					// Double check if is product.
					if ( ! $product instanceof WC_Product ) {
						continue;
					}

					if ( ! $product->is_virtual() && ! $product->is_downloadable() ) {

						$product_id     = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
						$current_vendor = yith_wcmv_get_vendor( $product_id, 'product' );

						if ( $current_vendor && absint( $current_vendor->get_id() ) === absint( $vendor->get_id() ) ) {
							$this->vendor_cart_elements[] = $key;
						}
					}
				}
			}
		}

		/**
		 * Get cart contents by given vendor
		 *
		 * @since  1.9.17
		 * @param YITH_Vendor|false $vendor Get vendor products in cart.
		 * @return array
		 */
		protected function get_vendors_cart_contens( $vendor = false ) {
			$cart_contents = array();
			foreach ( $this->vendor_cart_elements as $item_key ) {
				$cart_item = WC()->cart->get_cart_item( $item_key );
				if ( ! empty( $vendor ) && $vendor instanceof YITH_Vendor ) {
					$prod_vendor = yith_wcmv_get_vendor( $cart_item['product_id'], 'product' );
					if ( ! empty( $prod_vendor ) && $vendor->get_id() !== $prod_vendor->get_id() ) {
						continue;
					}
				}
				$cart_contents[ $item_key ] = $cart_item;
			}

			return $cart_contents;
		}

		/**
		 * Check from defined vendor zone a match with current cart
		 *
		 * @since  1.9.17
		 * @param YITH_Vendor $vendor Current processing cart vendor.
		 * @return bool|integer The zone index if found, false otherwise
		 */
		protected function get_matched_zone( $vendor ) {

			// Check if vendor has defined zone data.
			$zone_data = maybe_unserialize( $vendor->get_meta( 'zone_data' ) );
			if ( empty( $zone_data ) ) {
				return false;
			}

			$destination_country   = strtoupper( WC()->customer->get_shipping_country() );
			$destination_continent = strtoupper( wc_clean( WC()->countries->get_continent_code_for_country( $destination_country ) ) );
			$destination_state     = strtoupper( wc_clean( WC()->customer->get_shipping_state() ) );
			$destination_postcode  = strtoupper( wc_clean( WC()->customer->get_shipping_postcode() ) );

			$search_destination_continent = 'continent:' . $destination_continent;
			$search_destination_country   = 'country:' . $destination_country;
			$search_destination_state     = 'state:' . $destination_country . ':' . $destination_state;
			$matched                      = false;

			foreach ( $zone_data as $key => $zone ) {

				if ( $matched ) {
					break;
				}

				// Regions must be set, if not skip!
				if ( empty( $zone['zone_regions'] ) ) {
					continue;
				}

				// Start check regions.
				if ( false !== array_search( 'continent:all', $zone['zone_regions'], true ) ) {
					$matched = $key;
				} else {
					foreach ( $zone['zone_regions'] as $region ) {
						if ( $region === $search_destination_continent || $region === $search_destination_country || $region === $search_destination_state ) {
							$matched = $key;
							break;
						}
					}
				}

				// If regions match failed, break!
				if ( false === $matched ) {
					continue;
				}

				// If postcode is set, check it!
				if ( ! empty( $zone['zone_post_code'] ) ) {

					// Reset match!
					$matched = false;
					// Backward compatibility with postcode saved as plain string.
					if ( ! is_array( $zone['zone_post_code'] ) ) {
						// Make sure to include also space like PHP_EOL to fix a sleeping time moment.
						$postcodes = explode( PHP_EOL, str_replace( ' ', PHP_EOL, $zone['zone_post_code'] ) );
					} else {
						$postcodes = $zone['zone_post_code'];
					}

					foreach ( $postcodes as $postcode ) {
						$postcode    = trim( $postcode );
						$is_postcode = ! empty( $postcode ) ? WC_Validation::is_postcode( $postcode, $destination_country ) : false;

						if ( $is_postcode ) {
							if ( $postcode === $destination_postcode ) {
								$matched = $key;
								break;
							}
						} else {
							// Check for range or wildcard postalcode.
							$is_range     = strrpos( $postcode, '...' );
							$is_wildcards = strrpos( $postcode, '*' );

							if ( $is_range ) {
								$postcode_range = explode( '...', $postcode );
								$min            = min( $postcode_range );
								$max            = max( $postcode_range );

								if ( $destination_postcode >= $min && $destination_postcode <= $max ) {
									$matched = $key;
									break;
								}
							} elseif ( $is_wildcards ) {
								$postcode = str_replace( '*', '', $postcode );
								$regex    = "/^{$postcode}/";

								if ( preg_match( $regex, $destination_postcode ) ) {
									$matched = $key;
									break;
								}
							}
						}
					}
				}
			}

			return $matched;
		}

		/**
		 * Filter package name.
		 *
		 * @since  1.9.17
		 * @param string  $title   Current package name.
		 * @param integer $i       Package index.
		 * @param array   $package Current package.
		 * @return string
		 */
		public function woocommerce_shipping_package_name( $title, $i, $package ) {
			if ( isset( $package['yith-vendor'] ) ) {
				// translators: %s is the vendor name.
				$title = sprintf( _x( '%s shipping', '%s stand for the vendor name', 'yith-woocommerce-product-vendors' ), $package['yith-vendor']->get_name() );
				$title = apply_filters( 'yith_wcmv_shipping_package_name', $title, $package['yith-vendor'], $i );
			}

			return $title;
		}

		/**
		 * Filter the calculated rates.
		 *
		 * @since  1.9.17
		 * @param array $rates   Calculated package rates.
		 * @param array $package Package of cart items.
		 * @return array
		 */
		public function woocommerce_package_rates( $rates, $package ) {

			if ( ! isset( $package['yith-vendor'] ) || ! YITH_Vendors_Shipping::is_single_vendor_shipping_enabled( $package['yith-vendor'] ) ) {
				return $rates;
			}

			$zone_key = $this->get_matched_zone( $package['yith-vendor'] );
			// If no zone key was found, return empty rates.
			if ( false === $zone_key ) {
				return array();
			}

			$rates     = array();
			$zone_data = maybe_unserialize( $package['yith-vendor']->get_meta( 'zone_data' ) );

			if ( is_array( $zone_data ) && isset( $zone_data[ $zone_key ] ) ) {

				$zone = $zone_data[ $zone_key ];

				if ( isset( $zone['zone_shipping_methods'] ) ) {

					$zone_shipping_methods = $zone['zone_shipping_methods'];

					if ( is_array( $zone_shipping_methods ) ) {
						foreach ( $zone_shipping_methods as $key => $shipping_method ) {
							$this->calculate_shipping_method_rate( $package, $key, $shipping_method, $rates );
						}
					}
				}
			}

			return $rates;
		}

		/**
		 * Calculate rate for package based on given shipping method
		 *
		 * @since  4.0.0
		 * @param array   $package         The shipping package.
		 * @param integer $key             The shipping method key.
		 * @param array   $shipping_method The shipping method.
		 * @param array   $rates           The array of rated to modify.
		 * @return void
		 */
		protected function calculate_shipping_method_rate( $package, $key, $shipping_method, &$rates ) {

			// Check first if is free shipping!
			if ( ! $this->is_shipping_method_available( $package, $shipping_method ) ) {
				return;
			}

			$total_qty  = 0;
			$total_cost = 0;
			foreach ( $package['contents'] as $sc_id => $sc_args ) {
				$total_qty  += isset( $sc_args['quantity'] ) ? $sc_args['quantity'] : 1;
				$total_cost += $sc_args['line_total'];
			}

			if ( empty( $shipping_method['method_cost'] ) ) {
				$shipping_method['method_cost'] = 0;
			} else {
				$shipping_method['method_cost'] = $this->evaluate_cost( $shipping_method['method_cost'], $total_qty, $total_cost );
			}

			$total_cost = $shipping_method['method_cost'] ? floatval( wc_format_decimal( $shipping_method['method_cost'] ) ) : 0;
			if ( ! in_array( $shipping_method['type_id'], apply_filters( 'yith_wcmv_shipping_method_without_extra_cost', array( 'free_shipping' ) ), true ) ) {
				$total_cost += $this->get_extra_cost( $package, $shipping_method );
			}

			$total_cost    = $this->maybe_convert_amount( $total_cost );
			$calculate_tax = isset( $shipping_method['method_tax_status'] ) && 'none' !== $shipping_method['method_tax_status'];

			// Create rate object.
			$rate_key           = $shipping_method['type_id'] . '_' . $key;
			$rate               = new WC_Shipping_Rate( $rate_key, $shipping_method['method_title'], $total_cost, '', $shipping_method['type_id'] );
			$rates[ $rate_key ] = $rate;

			if ( $calculate_tax && $total_cost > 0 ) {
				$rates[ $rate_key ]->taxes = WC_Tax::calc_shipping_tax( $total_cost, WC_Tax::get_shipping_tax_rates() );
			}
		}

		/**
		 * Evaluate a cost from a sum/string.
		 *
		 * @since 4.1.1
		 * @param string $sum  Sum of shipping to evaluate.
		 * @param array  $qty  (Optional) Quantity amount. Default is 1.
		 * @param array  $cost (Optional) Cost amount. Default is 1.
		 * @return string
		 */
		protected function evaluate_cost( $sum, $qty = 1, $cost = 1 ) {

			if ( ! class_exists( 'WC_Eval_Math' ) ) {
				include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';
			}

			$locale   = localeconv();
			$decimals = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' );

			if ( false !== strpos( $sum, '[fee' ) ) {
				$matches = array();
				$fee     = 0;
				preg_match( '/\[fee+(.*)]/', $sum, $matches );
				if ( ! empty( $matches[1] ) ) {
					$atts = shortcode_parse_atts( $matches[1] );

					if ( $atts['percent'] ) {
						$fee = $cost * ( floatval( $atts['percent'] ) / 100 );
					}

					if ( $atts['min_fee'] && $fee < $atts['min_fee'] ) {
						$fee = $atts['min_fee'];
					}
					if ( $atts['max_fee'] && $fee > $atts['max_fee'] ) {
						$fee = $atts['max_fee'];
					}
				}

				$sum = preg_replace( '/\[fee+(.*)]/', $fee, $sum );
			}

			$sum = str_replace(
				array(
					'[qty]',
					'[cost]',
				),
				array(
					$qty,
					$cost,
				),
				$sum
			);
			// Remove whitespace from string.
			$sum = preg_replace( '/\s+/', '', $sum );
			// Remove locale from string.
			$sum = str_replace( $decimals, '.', $sum );
			// Trim invalid start/end characters.
			$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

			// Do the math.
			return ( class_exists( 'WC_Eval_Math' ) && $sum ) ? WC_Eval_Math::evaluate( $sum ) : 0;
		}

		/**
		 * Check if the shipping method is available for given package.
		 *
		 * @since  4.0.0
		 * @param array $package         The shipping package.
		 * @param array $shipping_method The shipping method.
		 * @return boolean True if free shipping is available, false otherwise.
		 */
		protected function is_shipping_method_available( $package, $shipping_method ) {

			$type_id = isset( $shipping_method['type_id'] ) ? $shipping_method['type_id'] : '';
			if ( ! $type_id ) {
				return false;
			}

			$available = true;
			if ( 'free_shipping' === $type_id ) {
				$requires = isset( $shipping_method['method_requires'] ) ? $shipping_method['method_requires'] : '';
				$vendor   = $package['yith-vendor'];

				if ( $requires ) {
					$min_amount   = $this->get_method_min_amount( $shipping_method );
					$package_cost = floatval( wc_format_decimal( $package['contents_cost'] + $package['contents_taxes_cost'] ) );

					switch ( $requires ) {
						case 'coupon':
							$available = $this->is_coupon_free_shipping( $vendor );
							break;

						case 'min_amount':
							$available = $package_cost >= $min_amount;
							break;

						case 'either':
							$available = $this->is_coupon_free_shipping( $vendor ) || $package_cost >= $min_amount;
							break;

						case 'both':
							$available = $this->is_coupon_free_shipping( $vendor ) && $package_cost >= $min_amount;
							break;
					}
				}
			}

			return apply_filters( 'yith_wcmv_is_shipping_method_available', $available, $shipping_method, $package );
		}

		/**
		 * Check if there is in cart a coupon for free shipping based on given vendor.
		 *
		 * @since  1.9.17
		 * @param YITH_Vendor $vendor Coupon associated vendor.
		 * @return boolean
		 */
		protected function is_coupon_free_shipping( $vendor ) {
			foreach ( WC()->cart->get_applied_coupons() as $code ) {
				$coupon = new WC_Coupon( $code );
				if ( ! $coupon->get_data_store() ) {
					continue;
				}

				$coupon_author = get_post_field( 'post_author', $coupon->get_id() );
				if ( apply_filters( 'yith_wcmv_is_free_shipping_coupon', in_array( $coupon_author, $vendor->get_admins() ) && $coupon->get_free_shipping(), $coupon, $coupon_author, $vendor ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Get shipping method min amount value
		 *
		 * @since  4.0.0
		 * @param array $shipping_method The shipping method data.
		 * @return float
		 */
		protected function get_method_min_amount( $shipping_method ) {
			if ( empty( $shipping_method['min_amount'] ) ) {
				return 0;
			}

			return $this->maybe_convert_amount( floatval( wc_format_decimal( $shipping_method['min_amount'] ) ) );
		}

		/**
		 * Maybe convert given amount. Compatibility with Aelia Currency Switcher & WooCommerce Currency Switcher.
		 *
		 * @since  4.0.0
		 * @param float $amount The amount to convert.
		 * @return float
		 */
		protected function maybe_convert_amount( $amount ) {

			global $WOOCS;

			// Support for WooCommerce Currency Switcher.
			if ( ! empty( $WOOCS ) ) {
				if ( $WOOCS->current_currency != $WOOCS->default_currency && get_option( 'woocs_is_multiple_allowed', 0 ) ) {
					$amount = $WOOCS->woocs_exchange_value( $amount );
				}
			} elseif ( class_exists( 'WC_Aelia_CurrencySwitcher' ) ) { // Aelia Currency Switcher Support.
				$aelia_obj       = $GLOBALS[ WC_Aelia_CurrencySwitcher::$plugin_slug ];
				$base_currency   = is_callable( array( $aelia_obj, 'base_currency' ) ) ? $aelia_obj->base_currency() : get_woocommerce_currency();
				$current_country = is_callable( array( $aelia_obj, 'get_selected_currency' ) ) ? $aelia_obj->get_selected_currency() : get_woocommerce_currency();
				$amount          = apply_filters( 'wc_aelia_cs_convert', $amount, $base_currency, $current_country );
			}

			return floatval( $amount );
		}

		/**
		 * Get extra cost for given package based on vendor settings
		 *
		 * @since  1.9.17
		 * @param array $package         The package to process.
		 * @param array $shipping_method Shipping method for package.
		 * @return float
		 */
		protected function get_extra_cost( $package, $shipping_method ) {
			$vendor = $package['yith-vendor'];
			$items  = $package['contents'];

			$extra_cost         = 0;
			$extra_cost_enabled = 'yes' === $vendor->get_meta( 'enable_shipping_extra_cost' );

			// The default shipping price for each product in the cart.
			$shipping_default_price = $vendor->get_meta( 'shipping_default_price' );
			if ( ! empty( $shipping_default_price ) ) {
				$extra_cost += (float) wc_format_decimal( $shipping_default_price );
			}

			// Additional price for each item.
			$extra_cost += $extra_cost_enabled ? $this->get_extra_cost_rule( $vendor->get_meta( 'shipping_extra_cost_items' ), count( $items ) ) : 0;

			$shipping_class_costs = array();
			foreach ( $items as $item ) {

				if ( empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
					continue;
				}

				$product = $item['data'];
				// Additional price for each product.
				$extra_cost += $extra_cost_enabled ? $this->get_extra_cost_rule( $vendor->get_meta( 'shipping_extra_cost_products' ), $item['quantity'] ) : 0;

				if ( $product->is_type( 'variation' ) ) {
					$parent_data               = $product->get_parent_data();
					$product_shipping_class_id = isset( $parent_data['shipping_class_id'] ) ? $parent_data['shipping_class_id'] : '';
				} else {
					$product_shipping_class_id = $product->get_shipping_class_id( 'edit' );
				}

				if ( empty( $product_shipping_class_id ) ) {
					$product_shipping_class_id = 'no_class_cost';
				} elseif ( null === term_exists( $product_shipping_class_id, 'product_shipping_class' ) || is_numeric( $product_shipping_class_id ) ) {
					$product_shipping_class_id = 'class_cost_' . $product_shipping_class_id;
				}

				if ( ! empty( $product_shipping_class_id ) && isset( $shipping_method[ $product_shipping_class_id ] ) ) {
					$shipping_class_costs[ $product_shipping_class_id ]['cost']       = $shipping_method[ $product_shipping_class_id ];
					$shipping_class_costs[ $product_shipping_class_id ]['qty']        = isset( $shipping_class_costs[ $product_shipping_class_id ]['qty'] ) ? $shipping_class_costs[ $product_shipping_class_id ]['qty'] + $item['quantity'] : $item['quantity'];
					$shipping_class_costs[ $product_shipping_class_id ]['line_total'] = isset( $shipping_class_costs[ $product_shipping_class_id ]['line_total'] ) ? $shipping_class_costs[ $product_shipping_class_id ]['line_total'] + $item['line_total'] : $item['line_total'];
				}
			}

			$shipping_class_type = ! empty( $shipping_method['type'] ) ? $shipping_method['type'] : 'class';
			$shipping_class_cost = 0;
			foreach ( $shipping_class_costs as $sc_id => $sc_args ) {
				$cost = $this->evaluate_cost( $sc_args['cost'], $sc_args['qty'], $sc_args['line_total'] );
				if ( 'class' === $shipping_class_type ) {
					$shipping_class_cost += (float) $cost;
				} elseif ( 'order' === $shipping_class_type ) {
					$shipping_class_cost = ( (float) $cost > $shipping_class_cost ) ? (float) $cost : $shipping_class_cost;
				}
			}

			$extra_cost += $shipping_class_cost;

			return (float) wc_format_decimal( $extra_cost );
		}

		/**
		 * Get extra cost single rule
		 *
		 * @since  4.0.0
		 * @param array   $rule The rule key to check.
		 * @param integer $qty  The quantity to check.
		 * @return float
		 */
		protected function get_extra_cost_rule( $rule, $qty = 1 ) {

			$extra_cost = 0;

			if ( ! empty( $rule ) ) {
				$applied_how = isset( $rule['applied_how'] ) ? $rule['applied_how'] : '';
				$cost        = isset( $rule['cost'] ) ? (float) wc_format_decimal( $rule['cost'] ) : 0;
				$items       = isset( $rule['items'] ) ? absint( $rule['items'] ) : 1;

				if ( ! empty( $cost ) && $qty > $items ) {
					switch ( $applied_how ) {
						case 'fixed':
							$extra_cost = $cost;
							break;

						case 'per_product':
							$extra_cost = $cost * ( $qty - $items );
							break;
					}
				}
			}

			return $extra_cost;
		}

		/**
		 * Add single product tab with shipping info
		 *
		 * @since  4.0.0
		 * @param array $tabs The product tabs array.
		 * @return   array
		 */
		public function add_shipping_vendor_tab( $tabs ) {
			global $product;

			$vendor = yith_wcmv_get_vendor( $product, 'product' );
			if ( empty( $vendor ) || ! $vendor->is_valid() ) {
				return $tabs;
			}

			if ( ! empty( $vendor->get_meta( 'shipping_processing_time' ) ) || ! empty( $vendor->get_meta( 'shipping_policy' ) )
				|| ! empty( $vendor->get_meta( 'shipping_refund_policy' ) ) || ! empty( $vendor->get_meta( 'shipping_location_from' ) ) ) {

				$default_tab_title = _x( 'Shipping info', '[Single Product Page]: Tab name for shipping information', 'yith-woocommerce-product-vendors' );
				$tab_title         = get_option( 'yith_wpv_shipping_tab_text_text', $default_tab_title );

				$args = array(
					'title'    => $tab_title,
					'priority' => 99,
					'callback' => array( $this, 'get_shipping_tab' ),
				);

				// Use yith_wc_vendor as array key. Not use vendor to prevent conflict with WC vendor extension.
				$tabs['yith_wc_vendor_shipping'] = apply_filters( 'yith_woocommerce_product_shipping_tab', $args );
			}

			return $tabs;
		}

		/**
		 * Callback for product shipping tab content
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function get_shipping_tab() {
			global $product;

			$vendor = yith_wcmv_get_vendor( $product, 'product' );
			if ( empty( $vendor ) || ! $vendor->is_valid() ) {
				return;
			}

			$shipping_processing_time = $vendor->get_meta( 'shipping_processing_time' );
			if ( ! empty( $shipping_processing_time ) ) {
				$processing_time_string   = YITH_Vendors_Shipping::get_shipping_processing_times();
				$shipping_processing_time = $processing_time_string[ $shipping_processing_time ];
			}

			$shipping_location_from = $vendor->get_meta( 'shipping_location_from' );
			if ( ! empty( $shipping_location_from ) ) {
				$wc_country_obj = new WC_Countries();
				$wc_countries   = $wc_country_obj->get_countries();

				$shipping_location_from = $wc_countries[ $shipping_location_from ];
			}

			$args = array(
				'shipping_processing_time'        => $shipping_processing_time,
				'shipping_location_from'          => $shipping_location_from,
				'shipping_policy'                 => call_user_func( '__', $vendor->get_meta( 'shipping_policy' ), 'yith-woocommerce-product-vendors' ),
				'shipping_refund_policy'          => call_user_func( '__', $vendor->get_meta( 'shipping_refund_policy' ), 'yith-woocommerce-product-vendors' ),
				'processing_time_title'           => _x( 'Processing time', '[single Product Page]: Shipping tab subtitle', 'yith-woocommerce-product-vendors' ),
				'shipping_processing_time_prefix' => _x( 'Ready to ship in', '[part of]: Ready to ship in x business day', 'yith-woocommerce-product-vendors' ),
				'shipping_location_from_prefix'   => _x( 'from', '[part of]: Ready to ship in x business day From Italy', 'yith-woocommerce-product-vendors' ),
				'shipping_location_from_title'    => _x( 'Shipping from', '[part of]: Shipping from Italy', 'yith-woocommerce-product-vendors' ),
				'shipping_policy_title'           => _x( 'Shipping policy', '[single Product Page]: Shipping tab subtitle', 'yith-woocommerce-product-vendors' ),
				'refund_policy_title'             => _x( 'Refund policy', '[single Product Page]: Shipping tab subtitle', 'yith-woocommerce-product-vendors' ),
			);

			$args = apply_filters( 'yith_woocommerce_product_vendor_tab_template', $args );

			yith_wcmv_get_template( 'shipping-tab', $args, 'woocommerce/single-product' );
		}
	}
}
