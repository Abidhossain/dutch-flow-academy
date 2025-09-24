<?php
/**
 * This class is a collection to methods to improve the compatibility with Hello Elementor theme.
 *
 * @class      YITH_Vendors_Theme_Hello_Elementor
 * @since      4.1.0
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

if ( ! class_exists( 'YITH_Vendors_Theme_Hello_Elementor', false ) ) {
	/**
	 * This class is a collection to methods to improve the compatibility with Hello Elementor theme.
	 *
	 * @class      YITH_Vendors_Theme_Hello_Elementor
	 * @since      4.0.0
	 * @package YITH\MultiVendor
	 */
	class YITH_Vendors_Theme_Hello_Elementor {

		/**
		 * Init Hello Elementor compatibilities hooks
		 *
		 * @since  4.0.0
		 */
		public static function init() {
			add_filter( 'yith_wcmv_do_404_redirect', '__return_false' );
			add_action( 'yith_wcmv_404_redirect', array( __CLASS__, 'the_redirect' ), 10, 1 );
		}

		/**
		 * Custom redirect
		 *
		 * @param YITH_Vendor $vendor Current vendor.
		 * @return void
		 */
		public static function the_redirect( $vendor ) {
			if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {
				get_header();
				get_template_part( 'template-parts/404' );
				get_footer();
				exit;
			}
		}
	}
}
