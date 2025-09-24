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

global $product;

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
		/**
		 * DO_ACTION: yith_ywgc_gift_card_design_section
		 *
		 * Display the gift card design section.
		 *
		 * @param object $product the gift card product
		 */
		do_action( 'yith_ywgc_gift_card_design_section', $product );

		/**
		 * DO_ACTION: yith_ywgc_gift_card_before_gift_cards_list
		 *
		 * Allow actions before the gift card amount list.
		 *
		 * @param object $product the gift card product
		 */
		do_action( 'yith_ywgc_gift_card_before_gift_cards_list', $product );
		?>

		<div class="gift-cards-list"
		<?php

		if ( ! YITH_YWGC()->frontend->is_manual_amount_allowed( $product ) && count( $product->get_amounts_to_be_shown() ) === 1 ) {
			echo 'style="display: none"';}
		?>
		>
			<?php
			/**
			 * DO_ACTION: yith_ywgc_show_gift_card_amount_selection
			 *
			 * Display the gift card amounts section.
			 *
			 * @param object $product the gift card product
			 */
			do_action( 'yith_ywgc_show_gift_card_amount_selection', $product );
			?>
		</div>

		<?php
		/**
		 * DO_ACTION: yith_ywgc_gift_card_delivery_info_section
		 *
		 * Display the gift card delivery info section.
		 *
		 * @param object $product the gift card product
		 */
		do_action( 'yith_ywgc_gift_card_delivery_info_section', $product );
		?>

		<?php
		/**
		 * DO_ACTION: yith_gift_cards_template_before_add_to_cart_button
		 *
		 * Allow actions before the add to cart button.
		 */
		do_action( 'yith_gift_cards_template_before_add_to_cart_button' );
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
</form>

<?php
/**
 * DO_ACTION: yith_gift_cards_template_after_add_to_cart_form
 *
 * Allow actions after the gift card add to cart form.
 */
do_action( 'yith_gift_cards_template_after_add_to_cart_form' );
?>

<?php
/**
 * DO_ACTION: yith_ywgc_gift_card_preview_end
 *
 * End of the gift card preview section.
 *
 * @param object $product the gift card product
 */
do_action( 'yith_ywgc_gift_card_preview_end', $product );
?>
