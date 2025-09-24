<?php
/**
 * Handle products.
 *
 * @since      5.0.0
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

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Products' ) ) {
	/**
	 * Products handler class
	 */
	class YITH_Vendors_Products {

		/**
		 * YITH_Vendor_Coupons constructor.
		 *
		 * @since 5.0.0
		 */
		public function __construct() {
			add_action( 'woocommerce_is_purchasable', array( $this, 'filter_purchasable' ), 99, 2 );
			add_filter( 'woocommerce_product_query_tax_query', array( $this, 'filter_tax_query_products' ), 20 );
		}

		/**
		 * Get a list of enabled product types for vendor
		 *
		 * @since 5.0.0
		 * @return array
		 */
		public function get_enabled_products_types() {
			$permission_types = get_option( 'yith_wpv_vendors_can_sell', array() );
			return array_keys(
				array_filter(
					$permission_types,
					function ( $value ) {
						return 'yes' === $value;
					}
				)
			);
		}

		/**
		 * Get a list of available product types for vendor
		 *
		 * @since 5.0.0
		 * @return array
		 */
		public function get_disabled_products_types() {
			$permission_types = get_option( 'yith_wpv_vendors_can_sell', array() );
			return array_keys(
				array_filter(
					$permission_types,
					function ( $value ) {
						return 'no' === $value;
					}
				)
			);
		}

		/**
		 * Filter product is_purchasable
		 *
		 * @since 5.0.0
		 * @param boolean    $purchasable True if product is purchasable, false otherwise.
		 * @param WC_Product $product The product object.
		 * @return boolean
		 */
		public function filter_purchasable( $purchasable, $product ) {
			if ( $this->is_vendor_product( $product ) ) {
				return ! in_array( $product->get_type(), $this->get_disabled_products_types(), true );
			}

			return $purchasable;
		}

		/**
		 * Filter WC tax query products
		 *
		 * @since 5.0.0
		 * @param array $tax_query The current tax query from WC.
		 * @return array
		 */
		public function filter_tax_query_products( $tax_query ) {
			$disabled = $this->get_disabled_products_types();
			if ( empty( $disabled ) ) {
				return $tax_query;
			}

			$tax_query[] = array(
				'relation' => 'OR',
				array(
					'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
					'operator' => 'NOT EXISTS',
				),
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => $disabled,
					'operator' => 'NOT IN',
				),
			);

			return $tax_query;
		}

		/**
		 * Is a product from vendor?
		 *
		 * @since 5.0.0
		 * @param WC_Product $product The product object.
		 * @return boolean
		 */
		protected function is_vendor_product( $product ) {
			$vendor = yith_wcmv_get_vendor( $product, 'product' );
			return $vendor && $vendor->is_valid();
		}
	}
}
