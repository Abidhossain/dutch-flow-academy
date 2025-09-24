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
 * APPLY_FILTERS: ywgc_add_to_cart_button_text
 *
 * Filter the "Add to cart" button text for the gift card products.
 *
 * @param string the button text
 *
 * @return string
 */

?>
<div class="gift_card_template_button variations_button">
	<?php if ( ! $sold_individually ) : ?>
		<?php woocommerce_quantity_input( array( 'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( absint( sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) ) ) : 1 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
	<?php endif; ?>
	<button type="submit" class="single_add_to_cart_button gift_card_add_to_cart_button button alt"><?php echo esc_html( apply_filters( 'ywgc_add_to_cart_button_text', $add_to_card_text ) ); ?></button>
	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product_id ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product_id ); ?>" />
</div>
