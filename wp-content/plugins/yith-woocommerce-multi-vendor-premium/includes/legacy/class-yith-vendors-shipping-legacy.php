<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Shipping_Legacy' ) ) {

	abstract class YITH_Vendors_Shipping_Legacy {

		/**
		 * @since  1.9.17
		 * @return mixed
		 */
		public static function yith_wcmv_get_shipping_processing_times() {
			_deprecated_function( __METHOD__, '4.0.0', 'get_shipping_processing_times' );
			return self::get_shipping_processing_times();
		}

		/**
		 * Get the shipping processing time array.
		 *
		 * @since  1.9.17
		 * @return mixed
		 */
		public static function get_shipping_processing_times() {
			return array();
		}
	}
}


if ( ! function_exists( 'YITH_Vendor_Shipping' ) ) {
	/**
	 * Get main instance of class YITH_Vendors_Shipping
	 *
	 * @since  1.9.17
	 * @return YITH_Vendors_Shipping
	 * @deprecated
	 */
	function YITH_Vendor_Shipping() { // phpcs:ignore
		_deprecated_function( 'YITH_Vendor_Shipping', '4.0.0', 'YITH_Vendors_Shipping' );
		return YITH_Vendors_Shipping::instance();
	}
}
