<?php
/**
 * Deposits class
 * Provides tools for deposit/balance calculation
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Deposits' ) ) {
	/**
	 * Deposits class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Deposits {

		/* === GETTER METHODS === */

		/**
		 * Return true if deposit is enabled on product
		 *
		 * @param int|bool $product_id Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 *
		 * @return bool Whether deposit is enabled for product
		 * @since 1.0.0
		 */
		public static function is_enabled( $product_id = false ) {
			global $product;

			if ( ! $product_id && $product instanceof WC_Product ) {
				$product_id      = $product->get_id();
				$current_product = $product;
			} else {
				$current_product = wc_get_product( $product_id );
			}

			// if product isn't purchasable, deposit isn't available too.
			if ( ! $current_product->is_purchasable() ) {
				return false;
			}

			$is_enabled = YITH_WCDP_Options::get( 'enable_deposit', $product_id );

			// check for expiration.
			if (
				$is_enabled &&
				self::has_expired( $product_id ) &&
				'disable_deposit' === self::get_expiration_fallback( $product_id )
				&& apply_filters( 'yith_wcdp_completely_remove_deposit_after_expiration', false, $product_id )
			) {
				$is_enabled = false;
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_is_deposit_enabled_on_product
			 *
			 * Filters if the deposit is enabled.
			 *
			 * @param bool                   Default value.
			 * @param int|bool $product_id   Product ID if exists.
			 * @param int|bool $variation_id Variation ID if exists.
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_wcdp_is_deposit_enabled_on_product', yith_plugin_fw_is_true( $is_enabled ), $product_id );
		}

		/**
		 * Return true if deposit is default option; false otherwise
		 *
		 * @param int|bool $product_id Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 *
		 * @return bool Whether deposit is default option for product
		 * @since 1.0.0
		 */
		public static function is_default( $product_id = false ) {
			global $product;

			if ( ! $product_id && $product instanceof WC_Product ) {
				$product_id = $product->get_id();
			}

			$is_default = YITH_WCDP_Options::get( 'deposit_default', $product_id );

			return apply_filters( 'yith_wcdp_is_deposit_default', yith_plugin_fw_is_true( $is_default ), $product_id );
		}

		/**
		 * Return true in deposit is mandatory for a product
		 *
		 * @param int|bool $product_id Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 *
		 * @return bool Whether deposit is enabled for product
		 * @since 1.0.0
		 */
		public static function is_mandatory( $product_id = false ) {
			global $product;

			if ( ! $product_id && $product instanceof WC_Product ) {
				$product_id = $product->get_id();
			}

			$is_mandatory = YITH_WCDP_Options::get( 'force_deposit', $product_id );

			/**
			 * APPLY_FILTERS: yith_wcdp_is_deposit_mandatory
			 *
			 * Filters if the deposit is mandatory.
			 *
			 * @param bool                   Default value.
			 * @param int|bool $product_id   Product ID if exists.
			 * @param int|bool $variation_id Variation ID if exists.
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_wcdp_is_deposit_mandatory', yith_plugin_fw_is_true( $is_mandatory ), $product_id );
		}

		/**
		 * Retrieve deposit type
		 *
		 * @param int|bool $product_id Product id.
		 * @param int|bool $customer_id Customer id.
		 *
		 * @return string Deposit type (rate, amount)
		 * @since 1.0.0
		 */
		public static function get_type( $product_id = false, $customer_id = false ) {
			$rule_matching   = self::get_best_rule_matching( $product_id, $customer_id );
			$default_deposit = get_option( 'yith_wcdp_general_deposit_amount', array() );

			if ( $rule_matching ) {
				$deposit_type = $rule_matching->is_fixed() ? 'amount' : 'rate';
			} else {
				$deposit_type = isset( $default_deposit['type'] ) ? $default_deposit['type'] : 'amount';
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_deposit_type
			 *
			 * Filters the deposit type.
			 *
			 * @param string                 Default value.
			 * @param int|bool $product_id   Product ID if exists.
			 * @param int|bool $variation_id Variation ID if exists.
			 * @param int|bool $customer_id  Customer ID if exists.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_deposit_type', $deposit_type, $product_id, $customer_id );
		}

		/**
		 * Retrieve deposit amount (needed on amount deposit type)
		 *
		 * @param int|bool $product_id Product id.
		 * @param int|bool $customer_id Customer id.
		 *
		 * @return string Amount
		 * @since 1.0.0
		 */
		public static function get_amount( $product_id = false, $customer_id = false ) {
			$rule_matching   = self::get_best_rule_matching( $product_id, $customer_id );
			$default_deposit = get_option( 'yith_wcdp_general_deposit_amount', array() );

			if ( $rule_matching && $rule_matching->is_fixed() ) {
				$deposit_amount = $rule_matching->get_amount();
			} else {
				$deposit_amount = isset( $default_deposit['amount'] ) ? $default_deposit['amount'] : 0;
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_deposit_amount
			 *
			 * Filters the deposit amount.
			 *
			 * @param string                 Default value.
			 * @param int|bool $product_id   Product ID if exists.
			 * @param int|bool $variation_id Variation ID if exists.
			 * @param int|bool $customer_id  Customer ID if exists.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_deposit_amount', $deposit_amount, $product_id, $customer_id );
		}

		/**
		 * Retrieve deposit rate (needed on rate deposit type)
		 *
		 * @param int|bool $product_id Product id.
		 * @param int|bool $customer_id Customer id.
		 *
		 * @return string Amount
		 * @since 1.0.0
		 */
		public static function get_rate( $product_id = false, $customer_id = false ) {
			$rule_matching   = self::get_best_rule_matching( $product_id, $customer_id );
			$default_deposit = get_option( 'yith_wcdp_general_deposit_amount', array() );

			if ( $rule_matching && ! $rule_matching->is_fixed() ) {
				$deposit_rate = $rule_matching->get_rate();
			} else {
				$deposit_rate = isset( $default_deposit['rate'] ) ? $default_deposit['rate'] : 10;
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_deposit_rate
			 *
			 * Filters the deposit type.
			 *
			 * @param string                 Default value.
			 * @param int|bool $product_id   Product ID if exists.
			 * @param int|bool $variation_id Variation ID if exists.
			 * @param int|bool $customer_id  Customer ID if exists.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcdp_deposit_rate', $deposit_rate, $product_id, $customer_id );
		}

		/* ==== EXPIRATION METHODS === */

		/**
		 * Returns the date of deposit expiration for the product
		 *
		 * @param int|bool $product_id Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 * @param string   $context    Context of the operation (changes the result returned).
		 * @return bool|string|WC_DateTime False if no expiration is set; formatted textual date if context is view, DateTime object otherwise.
		 */
		public static function get_expiration_date( $product_id = false, $context = 'view' ) {
			$should_expire = YITH_WCDP_Options::get( 'enable_expiration', $product_id );

			if ( ! yith_plugin_fw_is_true( $should_expire ) ) {
				return false;
			}

			$expiration_type = YITH_WCDP_Options::get( 'expiration_type', $product_id );
			$expiration_ts   = false;

			if ( 'num_of_days' === $expiration_type ) {
				$expiration_duration = YITH_WCDP_Options::get( 'expiration_duration', $product_id );
				$expiration_duration = is_array( $expiration_duration ) ? $expiration_duration['amount'] : $expiration_duration;
				$expiration_ts       = time() + $expiration_duration * DAY_IN_SECONDS;
			} elseif ( 'specific_date' === $expiration_type ) {
				$expiration_date = YITH_WCDP_Options::get( 'expiration_date', $product_id );
				$expiration_ts   = strtotime( $expiration_date );
			}

			$expiration_ts = apply_filters( 'yith_wcdp_deposit_expiration_timestamp', $expiration_ts, $expiration_type, $product_id, $context );

			if ( ! $expiration_ts ) {
				return false;
			}

			try {
				$expiration_dt = new WC_DateTime( "@{$expiration_ts}", new DateTimeZone( 'UTC' ) );

				if ( 'view' === $context ) {
					$expiration_dt->setTimezone( new DateTimeZone( wc_timezone_string() ) );
					return $expiration_dt->format( 'Y-m-d' );
				}

				return $expiration_dt;
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Return true if deposit has expired for the product
		 * If deposit shouldn't expire at all, or expiration type isn't related to the product object, method will always
		 * return false
		 *
		 * @param int|bool $product_id Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 *
		 * @return bool Whether or not deposit has expired for the product
		 */
		public static function has_expired( $product_id = false ) {
			global $product;

			if ( ! $product_id && $product instanceof WC_Product ) {
				$product_id = $product->get_id();
			}

			$should_expire = YITH_WCDP_Options::get( 'enable_expiration', $product_id );

			if ( ! yith_plugin_fw_is_true( $should_expire ) ) {
				return false;
			}

			$deposit_expiration = self::get_expiration_date( $product_id, 'edit' );

			if ( ! $deposit_expiration ) {
				return false;
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_is_deposit_expired_for_product
			 *
			 * Filters if the deposit is expired for the product.
			 *
			 * @param bool                   Default value.
			 * @param int|bool $product_id   Product ID if exists.
			 * @param int|bool $variation_id Variation ID if exists.
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_wcdp_is_deposit_expired_for_product', $deposit_expiration->getTimestamp() < time(), $product_id );
		}

		/**
		 * Return fallback for deposit expiration on the product
		 *
		 * @param int|bool $product_id Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 *
		 * @return string Expiration fallback
		 * @since 1.0.0
		 */
		public static function get_expiration_fallback( $product_id = false ) {
			global $product;

			if ( ! $product_id && $product instanceof WC_Product ) {
				$product_id = $product->get_id();
			}

			$fallback = YITH_WCDP_Options::get( 'expiration_product_fallback', $product_id );

			return apply_filters( 'yith_wcdp_deposit_expiration_fallback', $fallback, $product_id );
		}

		/* === CALCULATION METHODS === */

		/**
		 * Returns best matching deposit rule for passed parameters
		 *
		 * @param int $product_id  Product id.
		 * @param int $customer_id Customer id.
		 *
		 * @return YITH_WCDP_Deposit_Rule|bool Returns best matching rule, or false if none was found.
		 */
		public static function get_best_rule_matching( $product_id = false, $customer_id = false ) {
			global $product;

			$product_obj       = ! $product_id ? $product : wc_get_product( $product_id );
			$product_parent_id = $product_obj ? $product_obj->get_parent_id() : false;
			$product_parent    = $product_parent_id ? wc_get_product( $product_parent_id ) : false;
			$customer_obj      = ! $customer_id ? wp_get_current_user() : get_user_by( 'id', $customer_id );

			if ( ! $product_obj && ! $customer_obj ) {
				return false;
			}

			$conditions_sets = array(
				// search for role-specific rules.
				array(
					'user_role' => $customer_obj ? $customer_obj->roles : false,
				),

				// search for product-specific rules.
				array(
					'product_id' => $product_obj ? $product_obj->get_id() : false,
				),

				// search for product parent rules.
				array(
					'product_id' => $product_obj ? $product_obj->get_parent_id() : false,
				),

				// search for rules matching Product Categories.
				array(
					'product_cat' => $product_parent ? $product_parent->get_category_ids() : ( $product_obj ? $product_obj->get_category_ids() : false ),
				),
			);

			// remove conditions that cannot be applied.
			$conditions_sets = array_values(
				array_filter(
					$conditions_sets,
					function ( $item ) {
						$res = true;

						// if any of the condition is empty, remove it.
						foreach ( $item as $value ) {
							if ( empty( $value ) ) {
								$res = false;
								break;
							}
						}

						return $res;
					}
				)
			);

			// if conditions are empty, return.
			if ( empty( $conditions_sets ) ) {
				return false;
			}

			// cycle conditions until it matches a set of rules.
			$index = 0;

			do {
				$conditions = $conditions_sets[ $index ];
				$rules      = YITH_WCDP_Deposit_Rule_Factory::get_rules(
					array_merge(
						$conditions
					)
				);

				++$index;
			} while ( ! $rules && isset( $conditions_sets[ $index ] ) );

			$match = false;

			if ( $rules ) {
				$match = current( $rules );
			}

			return apply_filters( 'yith_wcaf_matched_deposit_rule', $match, $product_id, $customer_id );
		}

		/**
		 * Calculate deposit for product and variation passed as param
		 *
		 * @param int        $product_id   Product id.
		 * @param int|bool   $customer_id  Customer id; default to false, to consider current user.
		 * @param float|bool $price        Current product price (often third party plugin changes cart item price); false to use price from product object.
		 * @param string     $context      Context of the operator.
		 *
		 * @return double Deposit amount for specified product and variation
		 * @since 1.0.4
		 */
		public static function get_deposit( $product_id = false, $customer_id = false, $price = false, $context = 'edit' ) {
			global $product;

			if ( $product_id ) {
				$current_product = wc_get_product( $product_id );
			} else {
				$current_product = $product;
			}

			if ( ! $current_product ) {
				return 0;
			}

			if ( 'view' === $context ) {
				$price = yith_wcdp_get_price_to_display( $current_product, array_merge( array( 'qty' => 1 ), $price ? array( 'price' => $price ) : array() ) );
			} else {
				$price = $price ? $price : $current_product->get_price();
			}

			/**
			 * APPLY_FILTERS: yith_wcdp_product_price_for_deposit_operation
			 *
			 * Filters the deposit type.
			 *
			 * @param float|bool $price   Current product price (often third party plugin changes cart item price); false to use price from product object.
			 * @param WC_Product $product Product ID if exists.
			 *
			 * @return float|bool
			 */
			$price = floatval( apply_filters( 'yith_wcdp_product_price_for_deposit_operation', $price, $current_product ) );

			$deposit_type   = self::get_type( $product_id, $customer_id );
			$deposit_amount = self::get_amount( $product_id, $customer_id );
			$deposit_rate   = self::get_rate( $product_id, $customer_id );
			$deposit_value  = 0;

			if ( 'rate' === $deposit_type ) {
				$deposit_value = $price * (float) $deposit_rate / 100;
			} elseif ( 'amount' === $deposit_type ) {
				$deposit_value = 'view' === $context ? yith_wcdp_get_price_to_display(
					$current_product,
					array(
						'qty'   => 1,
						'price' => $deposit_amount,
					)
				) : $deposit_amount;
			}

			$deposit_value = min( $deposit_value, $price );

			return $deposit_value;
		}
	}
}
