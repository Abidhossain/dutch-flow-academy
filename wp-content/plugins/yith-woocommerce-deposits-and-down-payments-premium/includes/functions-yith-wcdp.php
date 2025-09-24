<?php
/**
 * Utility functions
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'yith_wcdp_get_order_subtotal' ) ) {
	/**
	 * Return order subtotal, considering full items prices if some items are deposits
	 *
	 * @param int $order_id Order id.
	 *
	 * @return float Order subtotal
	 * @since 1.0.3
	 */
	function yith_wcdp_get_order_subtotal( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return 0;
		}

		$has_deposit = $order->get_meta( '_has_deposit' );

		if ( ! $has_deposit ) {
			return $order->get_subtotal();
		}

		$items = $order->get_items();
		$total = 0;

		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				if ( ! isset( $item['deposit'] ) || ! $item['deposit'] ) {
					$total += $order->get_item_total( $item ) * $item['qty'];
				} else {
					$total += ( $item['deposit_value'] + $item['deposit_balance'] ) * $item['qty'];
				}
			}
		}

		return $total;
	}
}

if ( ! function_exists( 'yith_wcdp_get_cart_subtotal' ) ) {
	/**
	 * Return cart subtotal, considering full items prices if some items are deposits
	 *
	 * @return float Order subtotal
	 * @since 1.0.3
	 */
	function yith_wcdp_get_cart_subtotal() {
		$items = WC()->cart->get_cart();
		$total = 0;

		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				if ( ! isset( $item['deposit'] ) || ! $item['deposit'] ) {
					$total += $item['line_subtotal'];
				} else {
					$total += ( $item['deposit_value'] + $item['deposit_balance'] ) * $item['quantity'];
				}
			}
		}

		return $total;
	}
}

if ( ! function_exists( 'yith_wcdp_get_price_to_display' ) ) {
	/**
	 * Wraps functionality of wc_get_price_to_display, for older versions of WooCommerce
	 *
	 * @param WC_Product $product Product to use during calculation.
	 * @param array      $args    Array of arguments used in function; use the same logic of second param of wc_get_price_to_display.
	 *
	 * @since 1.1.2
	 */
	function yith_wcdp_get_price_to_display( $product, $args = array() ) {
		if ( ! $product || ! $product instanceof WC_Product ) {
			return;
		}

		$args = wp_parse_args(
			$args,
			array(
				'qty'   => 1,
				'price' => $product->get_price(),
				'order' => null,
			)
		);

		$price = $args['price'];
		$qty   = $args['qty'];
		$order = $args['order'];

		$show_including_tax = ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ( is_shop() || is_product() || is_product_taxonomy() || wp_doing_ajax() ) ) || ( 'incl' === get_option( 'woocommerce_tax_display_cart' ) && ( is_cart() || is_checkout() || $order ) );

		if ( $show_including_tax ) {
			return wc_get_price_including_tax(
				$product,
				array(
					'qty'   => $qty,
					'price' => $price,
				)
			);
		} else {
			return wc_get_price_excluding_tax(
				$product,
				array(
					'qty'   => $qty,
					'price' => $price,
				)
			);
		}
	}
}

if ( ! function_exists( 'yith_wcdp_locate_template' ) ) {
	/**
	 * Locate template for Deposit plugin
	 *
	 * @param string $filename Template name (with or without extension).
	 * @param string $section  Subdirectory where to search.
	 *
	 * @return string Found template
	 */
	function yith_wcdp_locate_template( $filename, $section = '' ) {
		$ext = strpos( $filename, '.php' ) === false ? '.php' : '';

		$template_name = $section . '/' . $filename . $ext;
		$template_path = WC()->template_path() . 'yith-wcdp/';
		$default_path  = YITH_WCDP_DIR . 'templates/';

		if ( defined( 'YITH_WCDP_PREMIUM_INIT' ) ) {
			$premium_template = str_replace( '.php', '-premium.php', $template_name );
			$located_premium  = wc_locate_template( $premium_template, $template_path, $default_path );
			$template_name    = file_exists( $located_premium ) ? $premium_template : $template_name;
		}

		return wc_locate_template( $template_name, $template_path, $default_path );
	}
}

if ( ! function_exists( 'yith_wcdp_get_template' ) ) {
	/**
	 * Get template for Affiliate plugin
	 *
	 * @param string $filename Template name (with or without extension).
	 * @param array  $args     Array of params to use in the template.
	 * @param string $section  Subdirectory where to search.
	 */
	function yith_wcdp_get_template( $filename, $args = array(), $section = '' ) {
		$ext = strpos( $filename, '.php' ) === false ? '.php' : '';

		$template_name = $section . '/' . $filename . $ext;
		$template_path = WC()->template_path() . 'yith-wcdp/';
		$default_path  = YITH_WCDP_DIR . 'templates/';

		if ( defined( 'YITH_WCDP_PREMIUM_INIT' ) ) {
			$premium_template = str_replace( '.php', '-premium.php', $template_name );
			$located_premium  = wc_locate_template( $premium_template, $template_path, $default_path );
			$template_name    = file_exists( $located_premium ) ? $premium_template : $template_name;
		}

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}
}

if ( ! function_exists( 'yith_wcdp_days_to_duration' ) ) {
	/**
	 * Converts a number of days to a duration array [ 'unit' => 'min', 'amount' => 5 ]
	 *
	 * @param mixed $days Number of days to convert.
	 *                    If parameter is already in duration format, function will just return it; otherwise, it will be converted to int and processed.
	 * @return array Array of duration that best approximate number of days passed as first parameter.
	 */
	function yith_wcdp_days_to_duration( $days ) {
		// if already in duration format, let's return it.
		if ( is_array( $days ) && isset( $days['unit'] ) && isset( $days['amount'] ) ) {
			return $days;
		}

		// first of all, let's convert value to integer.
		$days = (int) $days;

		// then let's try to approximate closest unit.
		$units = array(
			'day'   => 1,
			'week'  => 7,
			'month' => 30,
		);
		$keys  = array_keys( $units );
		$pivot = array_shift( $keys );

		do {
			$unit  = $pivot;
			$pivot = array_shift( $keys );
		} while ( isset( $units[ $pivot ] ) && $days > $units[ $pivot ] );

		// once we have the unit, let's calculate the integer amount of units that best fits current value.
		$amount = round( $days / $units[ $unit ] );

		// finally return duration array.
		return compact( 'unit', 'amount' );
	}
}

if ( ! function_exists( 'yith_wcdp_duration_to_days' ) ) {
	/**
	 * Converts a duration array [ 'unit' => 'min', 'amount' => 5 ] to equivalent number of days
	 *
	 * @param mixed $duration Duration to convert. If value is int, it will be returned; if not in duration format system will try to convert to duration and then process it.
	 * @return int Number of days equivalent to passed duration.
	 */
	function yith_wcdp_duration_to_days( $duration ) {
		// if already in seconds format, let's return it.
		if ( is_int( $duration ) ) {
			return $duration;
		}

		// if not in duration format, try to convert it.
		if ( ! is_array( $duration ) || ! isset( $duration['unit'] ) || ! isset( $duration['amount'] ) ) {
			$duration = yith_wcdp_days_to_duration( $duration );
		}

		// if still not in duration format, return 0.
		if ( ! is_array( $duration ) || ! isset( $duration['unit'] ) || ! isset( $duration['amount'] ) ) {
			return 0;
		}

		$units = array(
			'day'   => 1,
			'week'  => 7,
			'month' => 30,
		);
		$unit  = isset( $units[ $duration['unit'] ] ) ? $units[ $duration['unit'] ] : DAY_IN_SECONDS;

		return (int) $unit * $duration['amount'];
	}
}

if ( ! function_exists( 'yith_wcdp_append_items' ) ) {
	/**
	 * Adds items inside set array, placing them after the item with the index specified
	 *
	 * @param array  $set      Array where we need to add items.
	 * @param string $index    Index we need to search inside $set.
	 * @param mixed  $items    Items that we need to add to $set.
	 * @param string $position Where to place the additional set of items.
	 *
	 * @return array Array with new items
	 * @since 1.2.5
	 */
	function yith_wcdp_append_items( $set, $index, $items, $position = 'after' ) {
		$index_position = array_search( $index, array_keys( $set ), true );

		if ( $index_position < 0 ) {
			return $set;
		}

		if ( 'after' === $position ) {
			$pivot_position = $index_position + 1;
		} else {
			$pivot_position = $index_position;
		}

		$settings_options_chunk_1 = array_slice( $set, 0, $pivot_position );
		$settings_options_chunk_2 = array_slice( $set, $pivot_position, count( $set ) );

		return array_merge(
			$settings_options_chunk_1,
			$items,
			$settings_options_chunk_2
		);
	}
}
