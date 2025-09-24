<?php
/**
 * Class YITH_Vendors_REST_Install
 *
 * @since      4.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_REST_Install' ) ) {
	/**
	 * Init class.
	 */
	class YITH_Vendors_REST_Install {

		/**
		 * Boostrap REST API.
		 *
		 * @since  4.0.0
		 */
		public static function init() {
			// REST API extensions init.
			add_action( 'rest_api_init', array( __CLASS__, 'rest_api_init' ) );
		}

		/**
		 * Init REST API.
		 *
		 * @since  4.0.0
		 */
		public static function rest_api_init() {
			$controllers = apply_filters(
				'yith_wcmv_rest_controllers',
				array(
					'YITH_Vendors_REST_Vendors_Controller',
					'YITH_Vendors_REST_Commissions_Controller',
					'YITH_Vendors_REST_Reports_Controller',
					'YITH_Vendors_REST_Reports_Vendors_Controller',
					'YITH_Vendors_REST_Reports_Products_Controller',
					'YITH_Vendors_REST_Reports_Vendors_Stats_Controller',
				)
			);

			foreach ( $controllers as $controller ) {
				if ( ! class_exists( $controller ) ) {
					continue;
				}

				$class = new $controller();
				$class->register_routes();
			}
		}
	}
}
