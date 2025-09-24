<?php
/**
 * Gift this product modal class
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_YWGC_Gift_This_Product' ) ) {
	/**
	 * Handle the modal for the Gift this Product feature
	 *
	 * @since 1.0.0
	 */
	class YITH_YWGC_Gift_This_Product {

		/**
		 * Constructor
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function __construct() {
			if ( is_product() ) {
				global $post;

				$product = wc_get_product( $post->ID );

				// Not load the modal in the Gift Card products.
				if ( ! $product instanceof WC_Product_Gift_Card ) {
					// add main popup.
					add_action( 'wp_footer', array( $this, 'ywgc_add_modal' ), 10 );
					add_action( 'wp_footer', array( $this, 'ywgc_append_modal_content' ), 10 );
				}
			}
		}

		/**
		 * Output the popup
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function ywgc_add_modal() {
			wc_get_template(
				'gift-this-product/ywgc-modal.php',
				array(),
				'',
				YITH_YWGC_TEMPLATES_DIR
			);
		}

		/**
		 * Append the design preset to the gift card preview (modal)
		 */
		public function ywgc_append_modal_content() {
			global $product;

			if ( ! is_object( $product ) || ! $product instanceof WC_Product ) {
				return;
			}

			if ( ! YITH_YWGC()->allow_product_as_present() ) {
				return;
			}

			if ( $product instanceof YWGC_Gift_Card_Premium ) {
				return;
			}

			wc_get_template(
				'gift-this-product/gift-this-product-template.php',
				array(
					'gifted_product' => $product,
				),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR )
			);
		}
	}
}
