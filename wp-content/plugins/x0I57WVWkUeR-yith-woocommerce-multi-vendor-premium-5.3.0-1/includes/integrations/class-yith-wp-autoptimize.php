<?php
/**
 * Autoptimize compatibility
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

if ( ! function_exists( 'yith_autoptimize_filter_js_exclude' ) ) {
	/**
	 * Add support for Autoptimize. Show Google maps in vendor's page
	 *
	 * @param string $js_exclude JS excluded from autoptimize.
	 * @param mixed  $content    .
	 */
	function yith_autoptimize_filter_js_exclude( $js_exclude, $content ) {
		if ( function_exists( 'yith_wcmv_is_vendor_page' ) && yith_wcmv_is_vendor_page() ) {
			$js_exclude .= ' gmaps-api, gmap3.min.js, jquery';
		}
		return $js_exclude;
	}
}

add_filter( 'autoptimize_filter_js_exclude', 'yith_autoptimize_filter_js_exclude', 10, 2 );
