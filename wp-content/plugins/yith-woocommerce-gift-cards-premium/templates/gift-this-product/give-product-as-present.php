<?php
/**
 * Variable product add to cart
 *
 * @author YITH <plugins@yithemes.com>
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

$currency_pos = get_option( 'woocommerce_currency_pos' );

if ( 'left' === $currency_pos || 'left_space' === $currency_pos ) {
	$price_html = get_woocommerce_currency_symbol() . number_format( (float) wc_get_price_to_display($product), wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );

} else {
	$price_html = number_format( (float) wc_get_price_to_display($product), wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ) . get_woocommerce_currency_symbol();
}



$button_data = 'data-product-id="' . $product->get_id() . '" data-price="' .  wc_get_price_to_display($product) . '" data-price-html="' . $price_html . '" data-product-name="' . esc_html( $product->get_name() ) . '" data-image-url="' . esc_html( $product->get_image_id() ? current( wp_get_attachment_image_src( $product->get_image_id() ) ) : wc_placeholder_img_src() ) . '"';
?>
<div id="gift-this-product" class="gift-this-product-main-container">
	<?php if ( 'yes' === $icon ) { ?>
		<img src="<?php echo esc_url( YITH_YWGC_ASSETS_IMAGES_URL . 'card_giftcard_icon.svg' ); ?>" class="material-icons ywgc_woocommerce_message_icon">
	<?php } ?>

	<div class="gift-this-product-description-container">
		<span class="gift-this-product-title-message"><?php echo wp_kses_post( get_option( 'ywgc_gift_this_product_label_description', __( 'Do you feel this product is perfect for a friend or a loved one? You can buy a gift card for this item!', 'yith-woocommerce-gift-cards' ) ), 'yith-woocommerce-gift-cards' ); ?></span>
		<?php
		// Gift this product as button.
		if ( get_option( 'ywgc_gift_this_product_button_style', 'ywgc_gift_this_product_button_style_text' ) === 'ywgc_gift_this_product_button_style_button' ) {
			if ( $product->get_type() === 'variable' ) {
				?>
				<button id="give-as-present" class="btn btn-ghost give-as-present variable-gift-this-product" <?php echo wp_kses_post( $button_data ); ?>><?php echo wp_kses_post( get_option( 'ywgc_gift_this_product_label', __( 'Gift this product', 'yith-woocommerce-gift-cards' ) ) ); ?></button>
				<?php
			} else {
				?>
				<button id="give-as-present" class="btn btn-ghost give-as-present" <?php echo wp_kses_post( $button_data ); ?>><?php echo wp_kses_post( get_option( 'ywgc_gift_this_product_label', __( 'Gift this product', 'yith-woocommerce-gift-cards' ) ) ); ?></button>
				<?php
			}
		} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
			if ( $product->get_type() === 'variable' ) {
				?>
				<a id="give-as-present" class="btn btn-ghost give-as-present variable-gift-this-product" <?php echo wp_kses_post( $button_data ); ?>><?php echo wp_kses_post( get_option( 'ywgc_gift_this_product_label', __( 'Gift this product', 'yith-woocommerce-gift-cards' ) ) ); ?></a>
				<?php
			} else {
				?>
				<a id="give-as-present" class="btn btn-ghost give-as-present" <?php echo wp_kses_post( $button_data ); ?>><?php echo wp_kses_data( get_option( 'ywgc_gift_this_product_label', __( 'Gift this product', 'yith-woocommerce-gift-cards' ) ) ); ?></a>
				<?php
			}
		}
		?>
	</div>
</div>
