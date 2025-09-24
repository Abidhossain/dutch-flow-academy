<?php
/**
 * Base compatibility class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Compatibilities
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_WCDP_Compatibility' ) ) {
	/**
	 * Class that offers basic features for compatibility with other plugins
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Compatibility {

		/**
		 * Constructor method
		 * Loads compatibility classes when related plugin is active.
		 */
		public static function init() {

			if ( defined( 'YITH_YWPI_INIT' ) ) {
				require_once 'class-yith-wcdp-yith-pdf-invoice-compatibility.php';
				YITH_WCDP_YITH_PDF_Invoice_Compatibility::get_instance();
			}

			if ( defined( 'YITH_YWDPD_PREMIUM' ) ) {
				require_once 'class-yith-wcdp-yith-dynamic-pricing-and-discounts-compatibility.php';
				YITH_WCDP_YITH_Dynamic_Pricing_And_Discounts_Compatibility::get_instance();
			}

			if ( defined( 'YITH_WCEVTI_INIT' ) ) {
				require_once 'class-yith-wcdp-yith-event-tickets-compatibility.php';
				YITH_WCDP_YITH_Event_Tickets_Compatibility::get_instance();
			}

			if ( defined( 'YITH_WCPO_INIT' ) ) {
				require_once 'class-yith-wcdp-yith-pre-order-compatibility.php';
				YITH_WCDP_YITH_Pre_Order_Compatibility::get_instance();
			}

			if ( defined( 'YITH_WCP_PREMIUM' ) ) {
				require_once 'class-yith-wcdp-yith-composite-products-compatibility.php';
				YITH_WCDP_YITH_Composite_Products_Compatibility::get_instance();
			}

			if ( defined( 'YITH_WAPO_PREMIUM' ) ) {
				require_once 'class-yith-wcdp-yith-advanced-product-options-compatibility.php';
				YITH_WCDP_YITH_Advanced_Product_Options_Compatibility::get_instance();
			}

			if ( defined( 'YITH_WPV_PREMIUM' ) ) {
				require_once 'class-yith-wcdp-yith-multi-vendor-compatibility.php';
				YITH_WCDP_YITH_Multi_Vendor_Compatibility::get_instance();
			}

			if ( defined( 'YITH_YWGC_INIT' ) ) {
				require_once 'class-yith-wcdp-yith-gift-cards-compatibility.php';
				YITH_WCDP_YITH_Gift_Cards_Compatibility::get_instance();
			}

			if ( defined( 'YITH_WCPB_INIT' ) && version_compare( YITH_WCPB_VERSION, '1.3.0', '>=' ) ) {
				require_once 'class-yith-wcdp-yith-products-bundle-compatibility.php';
				YITH_WCDP_YITH_Products_Bundle_Compatibility::get_instance();
			}

			if ( defined( 'WCML_VERSION' ) ) {
				require_once 'class-yith-wcdp-wcml-compatibility.php';
				YITH_WCDP_WCML_Compatibility::get_instance();
			}

			if ( class_exists( 'Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher' ) ) {
				require_once 'class-yith-wcdp-wc-aelia-currency-switcher-compatibility.php';
				YITH_WCDP_WC_Aelia_Currency_Switcher_Compatibility::get_instance();
			}

			if ( apply_filters( 'yith_wcdp_enable_stripe_compatibility', true ) ) {
				require_once 'stripe/class-yith-wcdp-yith-stripe-compatibility.php';
				YITH_WCDP_YITH_Stripe_Compatibility::get_instance();
			}
		}
	}
}
