<?php
/**
 * Gift Card Amount List Options
 *
 * @package YITH\GiftCards\Views
 */

$sale_discount      = get_post_meta( $product_id, '_ywgc_sale_discount_value', true );
$sale_discount_text = get_post_meta( $product_id, '_ywgc_sale_discount_text', true );
$product            = new WC_Product_Gift_Card( $product_id );
$discount_settings  = $product->get_add_discount_settings_status();

?>
<div class="ywgc-form-field yith-plugin-ui" style="margin-left: 160px; margin-top: 1em;">
	<label class="ywgc-form-field__label"><?php echo esc_html__( 'Add a discount', 'yith-woocommerce-gift-cards' ); ?></label>
	<div class="ywgc-form-field__content">
		<?php
		yith_plugin_fw_get_field(
			array(
				'type'  => 'onoff',
				'id'    => 'ywgc-add-discount-settings-' . $product->get_id(),
				'name'  => 'ywgc-add-discount-settings-' . $product->get_id(),
				'class' => 'ywgc-add-discount-settings',
				'value' => 'yes' === $discount_settings ? 'yes' : 'no',
				'data'  => array(
					'product-id' => $product->get_id(),
				),
			),
			true
		);
		?>
	</div>
	<div class="ywgc-form-field__description">
		<?php esc_html_e( 'Enable to include a discount for this gift card.', 'yith-woocommerce-gift-cards' ); ?>
	</div>
</div>
<div class="ywgc-add-discount-settings-container ywgc-hidden">
	<p class="form-field sale-discount">
		<label for="gift_card-sale-discount"><?php esc_html_e( 'Sale discount (%)', 'yith-woocommerce-gift-cards' ); ?></label>
		<input type="number" min="-100" max="100" id="gift_card-sale-discount" name="gift_card-sale-discount" value="<?php echo esc_attr( $sale_discount ); ?>">
		<span class="ywgc-form-field__description "><?php esc_html_e( 'Enter an optional discount for this gift card.', 'yith-woocommerce-gift-cards' ); ?></span>
	</p>
	<p class="form-field sale-discount-text">
		<label for="gift_card-sale-discount-text"><?php esc_html_e( 'Sale discount text', 'yith-woocommerce-gift-cards' ); ?></label>
		<input type="text" id="gift_card-sale-discount-text" name="gift_card-sale-discount-text" value="<?php echo esc_attr( $sale_discount_text ); ?>">
		<span class="ywgc-form-field__description "><?php esc_html_e( 'Enter a text  to describe your discount.', 'yith-woocommerce-gift-cards' ); ?></span>
	</p>
</div>
<?php
