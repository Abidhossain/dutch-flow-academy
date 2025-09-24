<?php
/**
 * Create Gift Card Code modal content
 *
 * @package YITH\GiftCards\Views
 */

$desc           = esc_html__( 'Create and share gift card codes from backend, without proceeding to checkout or creating an order in your shop.', 'yith-woocommerce-gift-cards' );
$radio_field    = array(
	'id'      => 'gift-card-generator-radio-modal',
	'name'    => 'gift-card-generator-radio-modal',
	'type'    => 'radio',
	'options' => array(
		'one_code'  => esc_html__( 'Generate a single gift card code', 'yith-woocommerce-gift-cards' ),
		'generator' => esc_html__( 'Generate multiple gift card codes with same amount', 'yith-woocommerce-gift-cards' ),
	),
	'value'   => 'one_code',
);
$quantity_field = array(
	'id'    => 'ywgc-gift-card-generator-quantity',
	'name'  => 'ywgc-gift-card-generator-quantity',
	'type'  => 'number',
	'min'   => 0,
	'value' => 0,
);
$amount_field   = array(
	'id'    => 'ywgc-gift-card-generator-amount',
	'name'  => 'ywgc-gift-card-generator-amount',
	'type'  => 'number',
	'min'   => 0,
	'value' => 0,
);

?>
<div class="yith-gift-card-generator-modal-content">
	<p class="yith-gift-card-generator-modal-description"><?php echo esc_html( $desc ); ?></p>
	<?php yith_plugin_fw_get_field( $radio_field, true ); ?>
	<div id="ywgc-gift-card-generator-container" class="yith-plugin-fw-metabox-field-row hidden">
		<label for="ywgc-gift-card-generator-quantity-label" style="line-height: 35px;"><?php echo esc_html_x( 'Create', 'Label for the create gift card codes button', 'yith-woocommerce-gift-cards' ); ?></label>
		<?php yith_plugin_fw_get_field( $quantity_field, true ); ?>
		<label for="ywgc-gift-card-generator-amount-label" style="line-height: 35px;"><?php echo wp_kses_post( _x( 'codes with amount', 'Gift Card generator label, where the customer can select a number of codes to be generated with an specific value, like 15 codes with amount 50€', 'yith-woocommerce-gift-cards' ) . ' ' . get_woocommerce_currency_symbol() ); ?></label>
		<?php yith_plugin_fw_get_field( $amount_field, true ); ?>
	</div>
</div>
