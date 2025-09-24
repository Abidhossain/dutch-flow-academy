<?php
/**
 * YITH_WordPress_Yoast_SEO_Support class
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

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WordPress_Yoast_SEO_Support' ) ) {
	/**
	 * Yoast SEO plugin support
	 */
	class YITH_WordPress_Yoast_SEO_Support {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Construct
		 */
		private function __construct() {
			add_action( 'wpseo_register_extra_replacements', array( $this, 'register_plugin_replacements' ) );
		}

		/**
		 * Register a var replacement for vendor name
		 *
		 * @return void
		 */
		public function register_plugin_replacements() {
			wpseo_register_var_replacement( '%%vendor_name%%', 'YITH_WordPress_Yoast_SEO_Support::retrieve_vendor_name', 'basic', __( 'This is the name of the vendor product', 'yith-woocommerce-product-vendors' ) );
		}

		/**
		 * Get the vendor name
		 *
		 * @return string Store name
		 */
		public static function retrieve_vendor_name( $var, $post ) {
			if ( isset( $post->ID ) ) {
				$vendor = yith_wcmv_get_vendor( $post->ID, 'product' );
				$var    = ( $vendor && $vendor->is_valid() ) ? $vendor->get_name() : $var;
			}

			return $var;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  1.11.4
 * @return /YITH_WordPress_Yoast_SEO_Support
 */
if ( ! function_exists( 'YITH_WordPress_Yoast_SEO_Support' ) ) {
	function YITH_WordPress_Yoast_SEO_Support() { // phpcs:ignore
		return YITH_WordPress_Yoast_SEO_Support::instance();
	}
}

YITH_WordPress_Yoast_SEO_Support();
