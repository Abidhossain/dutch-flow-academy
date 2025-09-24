<?php
/**
 * Show a section with a product suggestion if the gift card was purchased as a gift for a product in the shop
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( 'yes' === get_option( 'ywgc_gift_this_product_apply_gift_card', 'yes' ) )  {
	if ( 'no' === get_option( 'ywgc_gift_this_product_add_to_cart', 'yes' ) ) {
		$args = array(
			YWGC_ACTION_ADD_DISCOUNT_TO_CART => $gift_card->gift_card_number,
			YWGC_ACTION_VERIFY_CODE          => YITH_YWGC()->hash_gift_card( $gift_card ),
		);
	} else {
		$args = array(
			YWGC_ACTION_ADD_DISCOUNT_TO_CART => $gift_card->gift_card_number,
			YWGC_ACTION_VERIFY_CODE          => YITH_YWGC()->hash_gift_card( $gift_card ),
			YWGC_ACTION_PRODUCT_ID           => is_object( $product ) ? $product->get_id() : '',
			YWGC_ACTION_GIFT_THIS_PRODUCT    => 'yes',
		);
	}
} else {
	$args = array(
		YWGC_ACTION_PRODUCT_ID        => is_object( $product ) ? $product->get_id() : '',
		YWGC_ACTION_GIFT_THIS_PRODUCT => 'yes',
	);
}

if ( 'to_customize_page' === get_option( 'ywgc_gift_this_product_button_redirect', 'to_product_page' ) ) {
	$product_link = esc_url( add_query_arg( $args, get_page_link( get_option( 'ywgc_gift_this_product_redirected_page' ) ) ) );
} else {
	$product_link = esc_url( add_query_arg( $args, get_permalink( $product->get_id() ) ) );
}

?>
<tr class="ywgc-suggested-text ywgc-product-suggested">
	<td colspan="2">
		<?php
		/**
		 * APPLY_FILTERS: yith_ywgc_product_suggested_message
		 *
		 * Filter the suggestion message for the "Gift this product" section on the gift card sent to the customer.
		 *
		 * @param string the suggestion message
		 *
		 * @return string
		 */
		echo esc_html( apply_filters( 'yith_ywgc_product_suggested_message', __( 'Maybe you can use the gift card for this item:', 'yith-woocommerce-gift-cards' ), $product ) );
		?>
	</td>
</tr>

<?php if ( is_object( $product ) ) : ?>
	<tr class="ywgc-product-suggested">
		<td class="ywgc-product-title-td">
			<p class="ywgc-product-title"><?php echo wp_kses_post( $product->get_name() ); ?></p>

			<?php
			/**
			 * APPLY_FILTERS: ywgc_display_price_template_suggestion
			 *
			 * Filter the condition to display the product price in the product suggestion.
			 *
			 * @param bool true to display it, false to not. Default: true
			 *
			 * @return bool
			 */
			if ( 'yes' === get_option( 'ywgc_display_price', 'yes' ) && apply_filters( 'ywgc_display_price_template_suggestion', true ) ) :
				?>
				<p class="ywgc-product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
			<?php endif; ?>

			<?php if ( 'email' === $context ) : ?>
				<a class="ywgc-product-link" href="<?php echo esc_url( $product_link ); ?>"><?php echo wp_kses_post( get_option( 'ywgc_gift_this_product_email_button_label', __( 'Go to the product', 'yith-woocommerce-gift-cards' ) ) ); ?></a>
			<?php endif; ?>
		</td>
		<td class="ywgc-product-image-td">
			<?php
			/**
			 * APPLY_FILTERS: ywgc_custom_product_suggestion_image_url
			 *
			 * Filter the URL of the product suggestion image.
			 *
			 * @param string the image URL
			 *
			 * @return string
			 */
			?>
			<img class="ywgc-product-image" src="<?php echo esc_url( apply_filters( 'ywgc_custom_product_suggestion_image_url', $product->get_image_id() ? current( wp_get_attachment_image_src( $product->get_image_id(), 'thumbnail' ) ) : wc_placeholder_img_src(), $context ) ); ?>" />
		</td>
	</tr>
<?php endif; ?>
