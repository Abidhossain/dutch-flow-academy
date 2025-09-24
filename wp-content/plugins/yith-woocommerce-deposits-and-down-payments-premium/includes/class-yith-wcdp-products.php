<?php
/**
 * Products handling class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Products' ) ) {
	/**
	 * Alters cart, to add deposit info
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Products {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'woocommerce_check_cart_items', array( self::class, 'add_held_stock_fix' ), 0 );
			add_filter( 'woocommerce_product_get_stock_quantity', array( self::class, 'remove_held_stock_fix' ), 15 );
		}

		/**
		 * Returns expected expiration for a set of products
		 *
		 * @param array $products Array of products, as product id or product objects.
		 * @return string|bool Expiration date, or false if product set don't expire.
		 */
		public static function get_expiration( $products = array() ) {
			$expiration = false;

			foreach ( $products as $product ) {
				$product = $product instanceof WC_Product ? $product : wc_get_product( $product );

				if ( ! $product ) {
					continue;
				}

				$product_id         = $product->get_id();
				$expiration_enabled = YITH_WCDP_Options::get( 'enable_expiration', $product_id );

				if ( ! yith_plugin_fw_is_true( $expiration_enabled ) ) {
					continue;
				}

				$item_expiration = YITH_WCDP_Deposits::get_expiration_date( $product_id );

				if ( $item_expiration && ( ! $expiration || $item_expiration < $expiration ) ) {
					$expiration = $item_expiration;
				}
			}

			$expiration = $expiration ? max( gmdate( 'Y-m-d' ), $expiration ) : false;

			return apply_filters( 'yith_wcdp_balance_expiration_date', $expiration, $products );
		}

		/**
		 * Enqueue stock fix, just before retrieving stock quantity
		 *
		 * @return void
		 */
		public static function add_held_stock_fix() {
			add_filter( 'woocommerce_product_get_stock_quantity', array( self::class, 'increase_stock_of_held_amount' ), 10, 2 );
		}

		/**
		 * Dequeue stock fix, just after retrieving stock quantity
		 *
		 * @param int $stock_qty Original sotck quantity.
		 *
		 * @return int Stock quantity
		 */
		public static function remove_held_stock_fix( $stock_qty ) {
			remove_filter( 'woocommerce_product_get_stock_quantity', array( self::class, 'increase_stock_of_held_amount' ) );

			return $stock_qty;
		}

		/**
		 * Increase stock quantity just before checking cart items
		 * This allows us to stock back  balance items, that normally would be considered as held items and removed from current stock
		 * This happens only during cart items check, and for this reason this fix is limited to that specific execution
		 *
		 * @param int        $stock_qty Original stock quantity.
		 * @param WC_Product $product   Current product.
		 *
		 * @return int Filtered stock quantity
		 */
		public static function increase_stock_of_held_amount( $stock_qty, $product ) {
			global $wpdb;

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$prepared_query = $wpdb->prepare(
					"
					 SELECT SUM( order_item_meta.meta_value ) AS held_qty
					 FROM {$wpdb->prefix}wc_orders AS orders
					 LEFT JOIN {$wpdb->prefix}wc_order_operational_data AS operationaldata ON orders.id = operationaldata.order_id
					 LEFT JOIN {$wpdb->prefix}woocommerce_order_items as order_items ON orders.id = order_items.order_id
					 LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
					 LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta2 ON order_items.order_item_id = order_item_meta2.order_item_id
					 WHERE 	order_item_meta.meta_key    = '_qty'
					 AND 	order_item_meta2.meta_key   = %s
					 AND 	order_item_meta2.meta_value = %d
					 AND 	orders.type             IN ( '" . implode( "','", array_map( 'esc_sql', wc_get_order_types() ) ) . "' ) " . // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					"AND 	orders.status           = 'wc-pending'
					 AND    orders.parent_order_id != 0
					 AND    operationaldata.created_via = %s",
					'variation' === get_post_type( $product->get_stock_managed_by_id() ) ? '_variation_id' : '_product_id',
					$product->get_stock_managed_by_id(),
					'yith_wcdp_balance_order'
				);
			} else {
				$prepared_query = $wpdb->prepare(
					"
					 SELECT SUM( order_item_meta.meta_value ) AS held_qty
					 FROM {$wpdb->posts} AS posts
					 LEFT JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
					 LEFT JOIN {$wpdb->prefix}woocommerce_order_items as order_items ON posts.ID = order_items.order_id
					 LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
					 LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta2 ON order_items.order_item_id = order_item_meta2.order_item_id
					 WHERE 	order_item_meta.meta_key    = '_qty'
					 AND 	order_item_meta2.meta_key   = %s
					 AND 	order_item_meta2.meta_value = %d
					 AND 	posts.post_type             IN ( '" . implode( "','", array_map( 'esc_sql', wc_get_order_types() ) ) . "' ) " . // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					"AND 	posts.post_status           = 'wc-pending'
					 AND    posts.post_parent           != 0
					 AND    postmeta.meta_key = %s
					 AND    postmeta.meta_value = %s",
					'variation' === get_post_type( $product->get_stock_managed_by_id() ) ? '_variation_id' : '_product_id',
					$product->get_stock_managed_by_id(),
					'_created_via',
					'yith_wcdp_balance_order'
				);
			}

			// count stock of balances.
			$balance_pending_stock = $wpdb->get_var( $prepared_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			return $stock_qty + (int) $balance_pending_stock;
		}
	}
}
