<?php
/**
 * Popup class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Classes
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_YWGC_Popup' ) ) {
	/**
	 * YITH_YWGC_Popup class.
	 *
	 * @since 1.0.0
	 */
	class YITH_YWGC_Popup {

		/**
		 * Constructor
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function __construct() {
			// add main popup.
			add_action( 'wp_footer', array( $this, 'ywgc_add_popup' ), 10 );

			// add popup template parts.
			add_action( 'yith_ywgc_gift_card_preview_end', array( $this, 'ywgc_append_design_presets' ) );
		}

		/**
		 * Output the popup
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function ywgc_add_popup() {
			/**
			 * APPLY_FILTERS: yith_ywgc_popup_template_args
			 *
			 * Filter the arguments to pass to the popup template.
			 *
			 * @param array the array of arguments
			 *
			 * @return array
			 */
			wc_get_template(
				'yith-gift-cards/ywgc-popup.php',
				apply_filters( 'yith_ywgc_popup_template_args', array() ),
				'',
				YITH_YWGC_TEMPLATES_DIR
			);
		}

		/**
		 * Append the design preset to the gift card preview (modal)
		 *
		 * @param WC_Product $product Product object.
		 */
		public function ywgc_append_design_presets( $product ) {
			if ( 'yes' !== get_option( 'ywgc_template_design', 'yes' ) ) {
				return;
			}

			if ( ! is_object( $product ) ) {
				return;
			}

			/**
			 * APPLY_FILTERS: yith_wcgc_design_presets_args
			 *
			 * Filter the arguments to pass to the design presets template.
			 *
			 * @param array the array of arguments
			 *
			 * @return array
			 */
			$args            = apply_filters( 'yith_wcgc_design_presets_args', array( 'hide_empty' => 1 ) );
			$args            = array_merge( array( 'taxonomy' => YWGC_CATEGORY_TAXONOMY ), $args );
			$categories      = get_terms( $args );
			$item_categories = array();

			foreach ( $categories as $item ) {
				$object_ids = get_objects_in_term( $item->term_id, YWGC_CATEGORY_TAXONOMY );

				foreach ( $object_ids as $object_id ) {
					$item_categories[ $object_id ] = isset( $item_categories[ $object_id ] ) ? $item_categories[ $object_id ] . ' ywgc-category-' . $item->term_id : 'ywgc-category-' . $item->term_id;
				}
			}

			$allow_customer_images            = get_option( 'ywgc_custom_design', 'no' );
			$selected_categories              = get_post_meta( $product->get_id(), 'selected_images_categories', true );
			$selected_categories_unserialized = json_decode( $selected_categories );
			$selected_categories_unserialized = isset( $selected_categories_unserialized ) && is_array( $selected_categories_unserialized ) ? $selected_categories_unserialized : array();
			$default_gift_product             = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

			if ( is_object( $product ) && is_object( $default_gift_product ) && $product->get_id() === $default_gift_product->get_id() ) {
				$selected_categories_unserialized = array(
					'0' => '0',
				);
			}

			wc_get_template(
				'yith-gift-cards/gift-card-presets.php',
				array(
					'categories'                       => $categories,
					'item_categories'                  => $item_categories,
					'product'                          => $product,
					'selected_categories_unserialized' => array_map( 'intval', $selected_categories_unserialized ),
					'allow_customer_images'            => $allow_customer_images,
				),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR )
			);
		}
	}
}
