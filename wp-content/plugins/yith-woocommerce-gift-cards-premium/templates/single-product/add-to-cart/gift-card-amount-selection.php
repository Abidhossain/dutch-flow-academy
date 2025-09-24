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

$on_sale       = $product->get_add_discount_settings_status();
$on_sale_value = get_post_meta( $product->get_id(), '_ywgc_sale_discount_value', true );
$on_sale_value = str_replace( ',', '.', $on_sale_value );

?>
<?php if ( 1 !== count( $amounts ) || $manual_amount ) : ?>
	<h3 class="ywgc_select_amount_title"><?php echo wp_kses_post( get_option( 'ywgc_select_amount_title', __( 'Set an amount', 'yith-woocommerce-gift-cards' ) ) ); ?></h3>
<?php endif; ?>

<?php if ( $amounts ) : ?>
	<?php
	/**
	 * DO_ACTION: yith_gift_cards_template_before_amounts
	 *
	 * Allow to apply changes before the amount selection on the gift card page.
	 *
	 * @param WC_Product $product the gift card product
	 */
	do_action( 'yith_gift_cards_template_before_amounts', $product );

	foreach ( $amounts as $value => $item ) :
		if ( $on_sale && $on_sale_value && apply_filters( 'ywgc_show_discounted_gift_card_amounts_buttons', true ) ) {
			$discounted_price = $item['price'] - ( ( $item['price'] * (float) $on_sale_value ) / 100 );
			$value_to_display = wc_format_sale_price( $item['price'], $discounted_price );
		} else {
			$value_to_display = $item['title'];
		}

		?>
		<button type="button" class="ywgc-predefined-amount-button ywgc-amount-buttons" value="<?php echo esc_attr( $item['amount'] ); ?>" data-price="<?php echo esc_attr( $item['price'] ); ?>" data-wc-price="<?php echo esc_attr( wp_strip_all_tags( wc_price( $item['price'] ) ) ); ?>">
			<?php echo wp_kses_post( apply_filters( 'yith_gift_card_select_amount_values', $value_to_display, $item, $product ) ); ?>
		</button>
		<input type="hidden" class="ywgc-predefined-amount-button ywgc-amount-buttons" value="<?php echo esc_attr( apply_filters( 'ywgc_amount_selection_hidden_amount', $item['amount'], $product ) ); ?>" data-price="<?php echo esc_attr( apply_filters( 'ywgc_amount_selection_hidden_price', $item['price'], $product ) ); ?>" data-wc-price="<?php echo esc_attr( wp_strip_all_tags( wc_price( apply_filters( 'ywgc_amount_selection_hidden_price', $item['price'], $product ) ) ) ); ?>" >
	<?php endforeach; ?>
	<?php
endif;

/**
 * APPLY_FILTERS: yith_wcgc_manual_amount_option_text
 *
 * Filter the custom amount field placeholder on the gift card product page.
 *
 * @param string the custom amount field placeholder
 *
 * @return string
 */
if ( $manual_amount ) :
	?>
	<div class="ywgc-manual-amount-container ywgc-manual-amount ywgc-amount-buttons"><span class="ywgc-manual-currency-symbol left"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span><input id="ywgc-manual-amount" name="ywgc-manual-amount" class="ywgc-manual-amount" type="text" placeholder="<?php echo esc_html( apply_filters( 'yith_wcgc_manual_amount_option_text', esc_html__( 'Enter amount', 'yith-woocommerce-gift-cards' ) ) ); ?>"><span class="ywgc-manual-currency-symbol right"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span></div>
	<?php
endif;

/**
 * DO_ACTION: yith_gift_cards_template_after_amounts
 *
 * Allow to apply changes after the amount selection on the gift card page.
 *
 * @param object $product the gift card product
 */
do_action( 'yith_gift_cards_template_after_amounts', $product );
