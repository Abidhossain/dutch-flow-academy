<?php
/**
 * Gift Card Amount List Options
 *
 * @package YITH\GiftCards\Views
 */

$expiration_date     = get_post_meta( $product_id, '_ywgc_expiration', true );
$date_format         = apply_filters( 'yith_wcgc_date_format', 'Y-m-d' );
$expiration_date     = ! empty( $expiration_date ) ? date_i18n( $date_format, $expiration_date ) : '';
$product             = new WC_Product_Gift_Card( $product_id );
$expiration_settings = $product->get_expiration_settings_status();

?>
<div class="ywgc-form-field yith-plugin-ui" style="margin-left: 160px; margin-top: 1em;">
	<label class="ywgc-form-field__label"><?php echo esc_html__( 'Override global expiration settings', 'yith-woocommerce-gift-cards' ); ?></label>
	<div class="ywgc-form-field__content">
		<?php
		yith_plugin_fw_get_field(
			array(
				'type'  => 'onoff',
				'id'    => 'ywgc-expiration-settings-' . $product->get_id(),
				'name'  => 'ywgc-expiration-settings-' . $product->get_id(),
				'class' => 'ywgc-expiration-settings',
				'value' => 'yes' === $expiration_settings ? 'yes' : 'no',
				'data'  => array(
					'product-id' => $product->get_id(),
				),
			),
			true
		);
		?>
	</div>
	<div class="ywgc-form-field__description">
		<?php esc_html_e( 'Enable to override the expiration global settings for this gift card.', 'yith-woocommerce-gift-cards' ); ?>
	</div>
</div>
<div class="ywgc-expiration-settings-container ywgc-hidden">
	<p class="form-field expiration-date-field">
		<label for="gift-card-expiration-date"><?php esc_html_e( 'Expiration date', 'yith-woocommerce-gift-cards' ); ?></label>
		<input type="text" class="ywgc-expiration-date-picker" id="gift-card-expiration-date" name="gift-card-expiration-date" value="<?php echo esc_attr( $expiration_date ); ?>" data-min-date="<?php echo esc_attr( $expiration_date ); ?>">
		<span class="ywgc-form-field__description "><?php esc_html_e( 'Set an expiration date for this gift card.', 'yith-woocommerce-gift-cards' ); ?></span>
	</p>
</div>
<?php
