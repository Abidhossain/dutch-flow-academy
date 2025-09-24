<?php
/**
 * YITH Vendors Admin Dashboard class. Handle plugin report dashboard methods.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Dashboard' ) ) {
	/**
	 * YITH Vendors Admin Dashboard class
	 */
	class YITH_Vendors_Admin_Dashboard {

		/**
		 * Class construct
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function __construct() {
			add_filter( 'yith_wcmv_get_admin_css', array( $this, 'add_style_deps' ), 10, 1 );
			add_filter( 'admin_body_class', array( $this, 'add_body_classes' ), 10, 1 );

			add_action( 'yith_wcmv_admin_dashboard_report', array( $this, 'output_admin_dashboard_report' ) );
		}

		/**
		 * Add style deps to be included in dashboard tab
		 *
		 * @since  4.0.0
		 * @param array $css An array of CSS to register.
		 * @return array
		 */
		public function add_style_deps( $css ) {
			if ( isset( $css['admin'] ) ) {
				array_push( $css['admin']['deps'], 'wp-components', 'wc-components' );
			}

			return $css;
		}

		/**
		 * Adds required body class for dashboard section
		 *
		 * @since  4.0.0
		 * @param string $classes List of default body classes.
		 * @return string Filtered body classes.
		 */
		public function add_body_classes( $classes ) {
			$classes .= ' woocommerce-page';
			return $classes;
		}

		/**
		 * Output admin dashboard report
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function output_admin_dashboard_report() {
			yith_wcmv_include_admin_template( 'dashboard-report' );
		}
	}
}
