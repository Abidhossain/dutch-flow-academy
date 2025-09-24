<?php
/**
 * Blocks registration class
 * This is a wrapper clas that will register all blocks specifically built for this plugin
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 2.0.0
 */

use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Blocks' ) ) {
	/**
	 * Affiliate Blocks
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Blocks {

		/**
		 * Array of available blocks
		 *
		 * @var array
		 */
		protected static $blocks = array();

		/**
		 * Performs all required add_shortcode
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function init() {
			// register blocks.
			self::init_blocks();

			// extend default Stores API.
			self::register_endpoint_data();

			// make sure to render "Grand Total" block in WC cart/checkout layout,
			// if customer did not manually add it.
			add_filter( 'render_block_woocommerce/checkout-order-summary-block', array( self::class, 'append_grand_total_block' ), 10, 2 );
			add_filter( 'render_block_woocommerce/cart-order-summary-block', array( self::class, 'append_grand_total_block' ), 10, 2 );
		}

		/**
		 * Init shortcodes available and register them
		 *
		 * @return void
		 */
		public static function init_blocks() {
			$blocks = static::get_blocks();

			if ( empty( $blocks ) ) {
				return;
			}

			foreach ( $blocks as $block ) {
				$block_name  = "yith_wcdp_$block";
				$block_class = "{$block_name}_block";

				if (
					! class_exists( $block_class ) ||
					! method_exists( $block_class, 'register' )
				) {
					continue;
				}

				$block_class::register();
			}
		}

		/**
		 * Returns list of registered blocks
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return array
		 */
		public static function get_blocks( $context = 'view' ) {
			// init blocks for the plugin.
			if ( empty( self::$blocks ) ) {
				self::$blocks = array(
					'grand_total',
				);
			}

			if ( 'view' === $context ) {
				/**
				 * APPLY_FILTERS: yith_wcaf_blocks
				 *
				 * Filters the available blocks.
				 *
				 * @param array $blocks Available blocks.
				 */
				return apply_filters( 'yith_wcdp_blocks', self::$blocks );
			}

			return self::$blocks;
		}

		/**
		 * Register extension data to send with WooCommerce Store API responses
		 */
		public static function register_endpoint_data() {
			woocommerce_store_api_register_endpoint_data(
				array(
					'endpoint'        => CartSchema::IDENTIFIER,
					'namespace'       => 'yith\deposits',
					'data_callback'   => array( self::class, 'get_deposit_data_for_blocks' ),
					'schema_callback' => array( self::class, 'get_deposit_schema_for_blocks' ),
					'schema_type'     => ARRAY_A,
				)
			);
		}

		/**
		 * Returns extension data to send with Cart API response
		 *
		 * @return array Custom API response data.
		 */
		public static function get_deposit_data_for_blocks() {
			$extend = StoreApi::container()->get( ExtendSchema::class );

			$decimals       = wc_get_price_decimals();
			$grand_totals   = \YITH_WCDP_Cart::get_grand_totals();
			$data_to_export = array(
				'total',
				'balance',
				'balance_shipping',
			);
			$formatted_data = array(
				'has_deposits'    => $grand_totals['has_deposits'],
				'grand_totals'    => array(),
				'expiration_note' => $extend->get_formatter( 'html' )->format( YITH_WCDP_Cart::add_expiration_note() ),
			);

			foreach ( $data_to_export as $total ) {
				if ( ! isset( $grand_totals[ $total ] ) ) {
					continue;
				}

				$formatted_total = $extend->get_formatter( 'money' )->format(
					$grand_totals[ $total ],
					array(
						'rounding_mode' => PHP_ROUND_HALF_DOWN,
						'decimals'      => $decimals,
					)
				);

				$formatted_data['grand_totals'][ $total ] = (object) $extend->get_formatter( 'currency' )->format(
					array(
						'price' => $formatted_total,
					)
				);
			}

			return $formatted_data;
		}

		/**
		 * Returns schema for extension data sent with Cart API response
		 *
		 * @return array Custom data schema.
		 */
		public static function get_deposit_schema_for_blocks() {
			return array(
				'has_deposits'    => array(
					'description' => __( 'Flag set when cart contains any deposit product', 'yith-woocomerce-deposits-and-down-payments' ),
					'type'        => 'bool',
					'readonly'    => true,
				),
				'grand_totals'    => array(
					'description' => __( 'Totals for deposit and balance orders', 'yith-woocomerce-deposits-and-down-payments' ),
					'type'        => 'object',
					'readonly'    => true,
					'properties'  => array(
						'total'            => array(
							'description' => __( 'Total amount for deposit and balances combined', 'yith-woocomerce-deposits-and-down-payments' ),
							'type'        => 'string',
							'readonly'    => true,
						),
						'balance'          => array(
							'description' => __( 'Combined total amount for balances orders', 'yith-woocomerce-deposits-and-down-payments' ),
							'type'        => 'string',
							'readonly'    => true,
						),
						'balance_shipping' => array(
							'description' => __( 'Combined shipping total for balances orders', 'yith-woocomerce-deposits-and-down-payments' ),
							'type'        => 'string',
							'readonly'    => true,
						),
					),
				),
				'expiration_note' => array(
					'description' => __( 'Note added to grand total when balance paymnent is due to expire', 'yith-woocomerce-deposits-and-down-payments' ),
					'type'        => 'string',
					'readonly'    => true,
				),
			);
		}

		/**
		 * Automatically append Grand Total block to Cart's and Checkout's totals section, when not manually added
		 * to the page by the admin
		 *
		 * @param string $content      Rendered content of {cart/checkout}-order-summary block.
		 * @param array  $parsed_block Parsed block.
		 *
		 * @return string Filtered version of the rendered block.
		 */
		public static function append_grand_total_block( $content, $parsed_block ) {
			preg_match( '/(?P<template>cart|checkout)/', $parsed_block['blockName'], $matches );

			if ( ! isset( $matches['template'] ) ) {
				return $content;
			}

			$template_name = $matches['template'];

			if ( yith_plugin_fw_wc_is_using_block_template_in( $template_name ) ) {
				$templates = get_block_templates(
					array(
						'slug' => $template_name,
					)
				);

				if ( empty( $templates ) ) {
					return $content;
				}

				$content_to_test = $templates[0]->content;
			} else {
				$page = get_post( wc_get_page_id( $template_name ) );

				if ( ! $page ) {
					return $content;
				}

				$content_to_test = $page->post_content;
			}

			if ( ! has_block( 'yith/yith-wcdp-grand-total', $content_to_test ) ) {
				$content .= do_blocks(
					<<<EOB
						<!-- wp:yith/yith-wcdp-grand-total -->
						<div class="wp-block-yith-yith-wcdp-grand-total"></div>
						<!-- /wp:yith/yith-wcdp-grand-total -->
					EOB
				);
			}

			return $content;
		}
	}
}
