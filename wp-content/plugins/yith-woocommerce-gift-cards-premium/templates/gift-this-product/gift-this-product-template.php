<?php
/**
 * Gift this product template
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );
$fixed_shipping_cost  = get_option( 'ywgc_gift_this_product_fixed_shipping_value', '0' );

do_action( 'yith_ywgc_gift_card_preview_end', $default_gift_product ); // Load the modal content.

?>
<script type="text/template" id="tmpl-gift-this-product-template">
	<div id="ywgc-gift-this-product-template clearfix">
		<h3 class="ywgc-gift-this-product-modal-title"><?php echo esc_html_x( 'Send a gift card for this product', 'Gift this product modal title', 'yith-woocommerce-gift-cards' ); ?></h3>
		<?php
		wc_get_template(
			'gift-this-product/product-template/content-gift-this-product.php',
			array(),
			'',
			trailingslashit( YITH_YWGC_TEMPLATES_DIR )
		);
		?>
	</div>
</script>
