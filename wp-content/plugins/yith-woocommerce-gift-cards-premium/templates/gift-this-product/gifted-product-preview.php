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

?>
<div class="ywgc-gifted-product-container">
	<div  class="ywgc-suggested-text">
		<?php echo esc_html( apply_filters( 'yith_ywgc_product_suggested_message', __( 'Maybe you can use the gift card for this item:', 'yith-woocommerce-gift-cards' ) ) ); ?>
	</div>
	<div class="ywgc-product-suggested">
		<div class="ywgc-product-image">
			<img class="ywgc-product-image" src=""/>
		</div>
		<div class="ywgc-product-info">
			<p class="ywgc-product-title"></p>
			<p class="ywgc-product-price"></p>
		</div>
	</div>
</div>
