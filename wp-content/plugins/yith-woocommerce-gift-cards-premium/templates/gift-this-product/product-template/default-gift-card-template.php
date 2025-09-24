<?php
/**
 * Gift Card product add to cart
 *
 * @author YITH
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DO_ACTION: yith_gift_cards_template_before_add_to_cart_form
 *
 * Allow actions before the gift card add to cart form.
 */
do_action( 'yith_gift_cards_template_before_add_to_cart_form' );
do_action( 'woocommerce_before_add_to_cart_form' );

?>
<form class="gift-cards_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>">
	<input type='hidden' name='ywgc_has_custom_design' value='1'>

	<?php
	/**
	 * DO_ACTION: yith_gift_cards_template_after_form_opening
	 *
	 * Allow actions before the gift card add to cart form opening.
	 */
	do_action( 'yith_gift_cards_template_after_form_opening' );
	?>

	<?php if ( $product->is_virtual() ) : ?>
		<input type="hidden" name="ywgc-is-digital" value="1" />
	<?php endif; ?>

	<?php if ( ! ( $product instanceof WC_Product_Gift_Card ) ) : ?>
		<input type="hidden" name="ywgc-as-present-enabled" value="1">
	<?php endif; ?>

	<?php if ( ! $product->is_purchasable() ) : ?>
		<p class="gift-card-not-valid">
			<?php esc_html_e( 'This product cannot be purchased', 'yith-woocommerce-gift-cards' ); ?>
		</p>
	<?php else : ?>
		<?php
		$categories      = get_terms( YWGC_CATEGORY_TAXONOMY );
		$item_categories = array();

		foreach ( $categories as $item ) {
			$object_ids = get_objects_in_term( $item->term_id, YWGC_CATEGORY_TAXONOMY );

			foreach ( $object_ids as $object_id ) {
				$item_categories[ $object_id ] = isset( $item_categories[ $object_id ] ) ? $item_categories[ $object_id ] . ' ywgc-category-' . $item->term_id : 'ywgc-category-' . $item->term_id;
			}
		}

		wc_get_template(
			'gift-this-product/product-template/gift-card-design.php',
			array(
				'categories'      => $categories,
				'item_categories' => $item_categories,
				'product'         => $product,
			),
			'',
			trailingslashit( YITH_YWGC_TEMPLATES_DIR )
		);

		/**
		 * DO_ACTION: yith_ywgc_gift_card_before_gift_cards_list
		 *
		 * Allow actions before the gift card amount list.
		 *
		 * @param object $product the gift card product
		 */
		do_action( 'yith_ywgc_gift_card_before_gift_cards_list', $product );

		/**
		 * DO_ACTION: yith_ywgc_gift_card_delivery_info_section
		 *
		 * Display the gift card delivery info section.
		 *
		 * @param object $product the gift card product
		 */
		do_action( 'yith_ywgc_gift_card_delivery_info_section', $product );

		/**
		 * DO_ACTION: yith_ywgc_gift_this_product_shipping_cost_section
		 *
		 * Display the gift this product shipping cost section
		 *
		 * @param object $product the gift card product
		 */
		do_action( 'yith_ywgc_gift_this_product_shipping_cost_section', $product );

		?>

		<div class="ywgc-product-wrap" style="display:none;">
			<?php
			/**
			 * DO_ACTION: yith_gift_cards_template_before_gift_card
			 *
			 * Allow actions before the gift card template.
			 */
			do_action( 'yith_gift_cards_template_before_gift_card' );

			/**
			 * DO_ACTION: yith_gift_cards_template_after_gift_card
			 *
			 * Used to output the cart button and placeholder for variation data.
			 */
			do_action( 'yith_gift_cards_template_gift_card' );

			/**
			 * DO_ACTION: yith_gift_cards_template_after_gift_card
			 *
			 * Allow actions after the gift card template.
			 */
			do_action( 'yith_gift_cards_template_after_gift_card' );
			?>
		</div>

		<?php
		/**
		 * DO_ACTION: yith_gift_cards_template_after_add_to_cart_button
		 *
		 * Allow actions after the gift card add to cart button.
		 */
		do_action( 'yith_gift_cards_template_after_add_to_cart_button' );
		?>
	<?php endif; ?>

	<?php
	/**
	 * DO_ACTION: yith_gift_cards_template_after_gift_card_form
	 *
	 * Allow actions after the gift card form.
	 */
	do_action( 'yith_gift_cards_template_after_gift_card_form' );

	?>
	<div class="gift_card_template_button variations_button">
		<?php if ( ! $product->is_sold_individually() ) : ?>
			<?php woocommerce_quantity_input( array( 'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( absint( sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) ) ) : 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
		<?php endif; ?>
		<button type="submit" class="single_add_to_cart_button gift_card_add_to_cart_button button alt"><?php echo esc_html( apply_filters( 'ywgc_add_to_cart_button_text', $product->single_add_to_cart_text() ) ); ?></button>
		<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
		<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	</div>
</form>

<?php
/**
 * DO_ACTION: yith_gift_cards_template_after_add_to_cart_form
 *
 * Allow actions after the gift card add to cart form.
 */
do_action( 'yith_gift_cards_template_after_add_to_cart_form' );

/**
 * DO_ACTION: yith_ywgc_gift_card_preview_end
 *
 * End of the gift card preview section.
 *
 * @param object $product the gift card product
 */
do_action( 'yith_ywgc_gift_card_preview_end', $product );
