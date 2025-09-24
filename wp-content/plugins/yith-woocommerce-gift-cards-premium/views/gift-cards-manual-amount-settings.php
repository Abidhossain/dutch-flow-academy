<?php
/**
 * Gift Card Amount List Options
 *
 * @package YITH\GiftCards\Views
 */

$product           = new WC_Product_Gift_Card( $product_id );
$manual_mode       = $product->get_manual_amount_status();
$override_settings = $product->get_override_global_settings_status();
$minimal_amount    = get_post_meta( $product_id, '_ywgc_minimal_manual_amount', true );
$maximum_amount    = get_post_meta( $product_id, '_ywgc_maximum_manual_amount', true );

?>
<div class="ywgc-form-field yith-plugin-ui" style="margin-left: 160px; margin-top: 1em;">
	<label class="ywgc-form-field__label"><?php echo esc_html__( 'Override custom amount settings', 'yith-woocommerce-gift-cards' ); ?></label>
	<div class="ywgc-form-field__content">
		<?php
		yith_plugin_fw_get_field(
			array(
				'type'  => 'onoff',
				'id'    => 'ywgc-override-product-settings-' . $product->get_id(),
				'name'  => 'ywgc-override-product-settings-' . $product->get_id(),
				'class' => 'ywgc-override-product-settings',
				'value' => 'yes' === $override_settings ? 'yes' : 'no',
				'data'  => array(
					'product-id' => $product->get_id(),
				),
			),
			true
		);
		?>
	</div>
	<div class="ywgc-form-field__description">
		<?php esc_html_e( 'Enable to override the custom amount global settings for this gift card.', 'yith-woocommerce-gift-cards' ); ?>
	</div>
</div>
<div class="ywgc-form-field yith-plugin-ui ywgc-custom-amount-field ywgc-hidden" style="margin-left: 160px;margin-top: 15px;">
	<label class="ywgc-form-field__label"><?php echo esc_html__( 'Allow custom amount', 'yith-woocommerce-gift-cards' ); ?></label>
	<div class="ywgc-form-field__content">
		<?php
		yith_plugin_fw_get_field(
			array(
				'type'  => 'onoff',
				'id'    => 'manual_amount_mode-' . $product->get_id(),
				'name'  => 'manual_amount_mode-' . $product->get_id(),
				'value' => 'yes' === $manual_mode ? 'yes' : 'no',
				'data'  => array(
					'product-id' => $product->get_id(),
				),
				'deps'  => array(
					'id'    => 'ywgc-override-product-settings' . $product->get_id(),
					'value' => 'yes',
				),
			),
			true
		);
		?>
	</div>
	<div class="ywgc-form-field__description">
		<?php esc_html_e( 'If enabled, customers can choose from the available amounts or also enter a custom amount for the gift card.', 'yith-woocommerce-gift-cards' ); ?>
	</div>
</div>
<p class="form-field minimal-amount-field ywgc-hidden">
	<label for="ywgc-minimal-amount"><?php esc_html_e( 'Minimum custom amount', 'yith-woocommerce-gift-cards' ); ?></label>
	<input type="number" class="ywgc-minimal-amount" id="ywgc-minimal-amount" name="ywgc-minimal-amount" value="<?php echo esc_attr( $minimal_amount ); ?>" placeholder="<?php esc_attr_e( 'Enter a minimum amount', 'yith-woocommerce-gift-cards' ); ?>">
	<span class="ywgc-form-field__description "><?php esc_html_e( 'Set an optional minimum custom amount for this gift card.', 'yith-woocommerce-gift-cards' ); ?></span>
</p>
<p class="form-field maximum-amount-field ywgc-hidden">
	<label for="ywgc-maximum-amount"><?php esc_html_e( 'Maximum custom amount', 'yith-woocommerce-gift-cards' ); ?></label>
	<input type="number" class="ywgc-maximum-amount" id="ywgc-maximum-amount" name="ywgc-maximum-amount" value="<?php echo esc_attr( $maximum_amount ); ?>" placeholder="<?php esc_attr_e( 'Enter a maximum amount', 'yith-woocommerce-gift-cards' ); ?>">
	<span class="ywgc-form-field__description "><?php esc_html_e( 'Set an optional maximum custom amount for this gift card.', 'yith-woocommerce-gift-cards' ); ?></span>
</p>
<?php
