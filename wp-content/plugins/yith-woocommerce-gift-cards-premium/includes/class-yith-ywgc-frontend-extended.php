<?php
/**
 * Frontend class
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Frontend_Extended' ) ) {
	/**
	 * YITH_YWGC_Frontend_Extended class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Frontend_Extended extends YITH_YWGC_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Frontend_Extended
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 */
		protected function __construct() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
			parent::__construct();
		}

		/**
		 * Initiate the frontend
		 *
		 * @since 2.0.2
		 */
		public function frontend_init() {
			if ( get_option( 'ywgc_gift_card_form_on_cart', 'yes' ) === 'yes' ) {
				$get_option_ywgc_gift_card_form_on_cart_place = get_option( 'ywgc_gift_card_form_on_cart_place', 'woocommerce_before_cart' );

				/**
				 * APPLY_FILTERS: ywgc_gift_card_code_form_cart_hook
				 *
				 * Filter the cart hook where we want to display the fields to add the gift card codes.
				 *
				 * @param string the hook name. Default: woocommerce_before_cart
				 *
				 * @return string
				 */
				$ywgc_cart_hook = apply_filters( 'ywgc_gift_card_code_form_cart_hook', ( empty( $get_option_ywgc_gift_card_form_on_cart_place ) ? 'woocommerce_before_cart' : get_option( 'ywgc_gift_card_form_on_cart_place' ) ) );

				/**
				 * Show the gift card section for entering the discount code in the cart page
				 */
				add_action( $ywgc_cart_hook, array( $this, 'show_field_for_gift_code' ) );
			}

			if ( get_option( 'ywgc_gift_card_form_on_checkout', 'yes' ) === 'yes' ) {
				$get_option_ywgc_gift_card_form_on_checkout_place = get_option( 'ywgc_gift_card_form_on_checkout_place', 'woocommerce_before_checkout_form' );

				/**
				 * APPLY_FILTERS: ywgc_gift_card_code_form_checkout_hook
				 *
				 * Filter the checkout hook where we want to display the fields to add the gift card codes.
				 *
				 * @param string the hook name. Default: woocommerce_before_checkout_form
				 *
				 * @return string
				 */
				$ywgc_checkout_hook = apply_filters( 'ywgc_gift_card_code_form_checkout_hook', empty( $get_option_ywgc_gift_card_form_on_checkout_place ) ? 'woocommerce_before_checkout_form' : get_option( 'ywgc_gift_card_form_on_checkout_place' ) );
				/**
				 * Show the gift card section for entering the discount code in the cart page
				 */
				add_action( $ywgc_checkout_hook, array( $this, 'show_field_for_gift_code' ) );
			}
		}

		/**
		 * Show Gift Cards details
		 *
		 * @param WC_Product $product Product object.
		 */
		public function show_gift_card_details( $product ) {
			if ( ( $product instanceof WC_Product_Gift_Card ) && $product->is_virtual() ) {
				wc_get_template(
					'yith-gift-cards/gift-card-details.php',
					array(
						'mandatory_recipient' => YITH_YWGC()->mandatory_recipient(),
						'date_format'         => apply_filters( 'yith_wcgc_date_format', 'Y-m-d' ),
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			} else {
				wc_get_template(
					'yith-gift-cards/physical-gift-card-details.php',
					array(
						'date_format' => apply_filters( 'yith_wcgc_date_format', 'Y-m-d' ),
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);
			}
		}
	}
}
