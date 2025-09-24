<?php
/**
 * YITH_Reports_Analytics Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
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

if ( ! class_exists( 'YITH_Reports_Analytics' ) ) {
	/**
	 * YITH_Reports_Analytics Class
	 */
	class YITH_Reports_Analytics {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Class construct
		 *
		 * @since  1.0.0
		 * @return void
		 */
		private function __construct() {
			// WooCommerce Admin Support.
			add_action( 'admin_init', array( $this, 'block_analytics_report_page' ) );
			// Remove WooCommerce Admin for Vendors.
			add_action( 'woocommerce_analytics_menu_capability', array( $this, 'remove_analytics_menu_for_vendors' ) );
			// Orders Report.
			add_filter( 'woocommerce_analytics_clauses_where', array( $this, 'analytics_clauses_where' ), 10, 2 );
		}

		/**
		 * Block Analytics Report Page
		 *
		 * @since  3.11.0
		 * @return void
		 */
		public function block_analytics_report_page() {
			global $pagenow;
			$vendor                   = yith_wcmv_get_vendor( 'current', 'user' );
			$is_new_analytics_section = ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'wc-admin' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() && $is_new_analytics_section ) {
				// translators: %1$s stand for the open anchor html to admin dashboard, %2$s stand for the close anchor tag.
				wp_die( wp_kses_post( sprintf( __( 'You do not have sufficient permissions to access this page. %1$sClick here to return to your dashboard%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( admin_url() ) . '">', '</a>' ) ) );
			}
		}

		/**
		 * Remove the WooCommerce admin bar for vendors
		 *
		 * @param string $capability User capability used to show the WooCommerce admin bar.
		 * @return string the allowed capability
		 */
		public function remove_analytics_menu_for_vendors( $capability ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$capability = false;
			}
			return $capability;
		}

		/**
		 * Filter the WooCommerce admin report to remove vendor's information
		 *
		 * @param array  $clauses The original arguments for the request.
		 * @param string $context The data store context.
		 * @return array the filtered SQL clauses
		 */
		public function analytics_clauses_where( $clauses, $context ) {
			global $wpdb;
			$clauses[] = "AND {$wpdb->prefix}wc_order_stats.parent_id = 0 AND {$wpdb->prefix}wc_order_stats.parent_id NOT IN( SELECT {$wpdb->postmeta}.post_id FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = '_created_via' AND {$wpdb->postmeta}.meta_value = 'yith_wcmv_vendor_suborder' )";
			return $clauses;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  1.0
 * @return YITH_Reports_Analytics
 */
if ( ! function_exists( 'YITH_Reports_Analytics' ) ) {
	/**
	 * Return single instance of the class YITH_Reports_Analytics
	 *
	 * @return YITH_Reports_Analytics
	 */
	function YITH_Reports_Analytics() { // phpcs:ignore
		return YITH_Reports_Analytics::instance();
	}
}

YITH_Reports_Analytics();
