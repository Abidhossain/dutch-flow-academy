<?php
/**
 * Checkout gift cards form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-gift-cards.php.
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * APPLY_FILTERS: yith_gift_cards_show_field
 *
 * Filter the condition display the gift card form in the gift card product page.
 *
 * @param bool true to display it, false to not. Default: true
 *
 * @return bool
 */
if ( ! apply_filters( 'yith_gift_cards_show_field', true ) || is_order_received_page() ) {
	return;
}

$direct_display = get_option( 'ywgc_display_form', 'ywgc_display_form_hidden' ) === 'ywgc_display_form_visible' ? 'yes' : 'no';

if ( get_option( 'ywgc_icon_text_before_gc_form', 'no' ) === 'yes' ) {
	$icon = '<img src="' . YITH_YWGC_ASSETS_IMAGES_URL . 'card_giftcard_icon.svg" class="material-icons ywgc_woocommerce_message_icon"  style="margin-right: 6px; float: left;">';
} else {
	$icon = ''; }

if ( 'yes' !== $direct_display ) {
	echo wp_kses_post( '<div class="ywgc_have_code">' . $icon . get_option( 'ywgc_text_before_gc_form', __( 'Got a gift card from a loved one?', 'yith-woocommerce-gift-cards' ) ) . ' <a href="#" class="ywgc-show-giftcard">' . get_option( 'ywgc_link_text_before_gc_form', __( 'Use it here!', 'yith-woocommerce-gift-cards' ) ) . '</a> </div>' );
}

?>
<div class="ywgc_enter_code" method="post" style="<?php echo ( 'yes' !== $direct_display ? 'display:none' : '' ); ?>">
	<?php
	if ( get_option( 'ywgc_minimal_cart_total_option', 'no' ) === 'yes' && WC()->cart->total < get_option( 'ywgc_minimal_cart_total_value', '0' ) ) :
		?>
		<p class="woocommerce-error" role="alert">
			<?php echo wp_kses_post( _x( 'In order to apply the gift card, the total amount in the cart has to be at least', 'Apply gift card', 'yith-woocommerce-gift-cards' ) . ' ' . get_option( 'ywgc_minimal_cart_total_value' ) . get_woocommerce_currency_symbol() ); ?>
		</p>
		<?php
	endif;
	?>

	<div style="position: relative">
		<p><?php echo wp_kses_post( get_option( 'ywgc_text_in_the_form', __( 'Apply the gift card code in the following field', 'yith-woocommerce-gift-cards' ) ) ); ?></p>
		<p class="form-row form-row-first">
			<?php
			/**
			 * APPLY_FILTERS: ywgc_checkout_box_placeholder
			 *
			 * Filter the gift card field placeholder in the cart & checkout.
			 *
			 * @param string the placeholder
			 *
			 * @return string
			 */
			?>
			<input type="text" name="gift_card_code" class="input-text" placeholder="<?php echo esc_attr( apply_filters( 'ywgc_checkout_box_placeholder', _x( 'Gift card code', 'Apply gift card', 'yith-woocommerce-gift-cards' ) ) ); ?>" id="giftcard_code" value="" />
		</p>
		<p class="form-row form-row-last">
			<button type="submit" class="button ywgc_apply_gift_card_button" name="ywgc_apply_gift_card" value="<?php echo esc_attr( get_option( 'ywgc_apply_gift_card_button_text', __( 'Apply Gift Card', 'yith-woocommerce-gift-cards' ) ) ); ?>"><?php echo wp_kses_post( get_option( 'ywgc_apply_gift_card_button_text', __( 'Apply Gift Card', 'yith-woocommerce-gift-cards' ) ) ); ?></button>
			<input type="hidden" name="is_gift_card" value="1" />
		</p>
		<div class="clear"></div>

		<?php
		if ( get_option( 'ywgc_minimal_cart_total_option', 'no' ) === 'yes' && WC()->cart->total < get_option( 'ywgc_minimal_cart_total_value' ) ) :
			?>
			<div class="yith_wc_gift_card_blank_brightness"></div>
			<?php
		endif;
		?>
	</div>
</div>
