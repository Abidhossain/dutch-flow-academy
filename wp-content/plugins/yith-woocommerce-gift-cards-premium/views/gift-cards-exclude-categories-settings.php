<?php
/**
 * Gift Card Amount List Options
 *
 * @package YITH\GiftCards\Views
 */

$product                     = new WC_Product_Gift_Card( $product_id );
$exclude_categories_settings = $product->get_exclude_categories_settings_status();
$excluded_categories         = $product->get_excluded_categories();
$categories_term             = get_terms( 'product_cat' );
$categories                  = array();

foreach ( $categories_term as $category ) {
	$categories[ $category->term_id ] = $category->name;
}

?>
<div class="ywgc-form-field yith-plugin-ui ywgc-excluded-categories-field" style="margin-left: 160px; margin-top: 1em;">
	<label class="ywgc-form-field__label"><?php echo esc_html__( 'Exclude product categories', 'yith-woocommerce-gift-cards' ); ?></label>
	<div class="ywgc-form-field__content ywgc-excluded-categories-field-content">
		<?php
		yith_plugin_fw_get_field(
			array(
				'type'  => 'onoff',
				'id'    => 'ywgc-excluded-categories-settings-' . $product->get_id(),
				'name'  => 'ywgc-excluded-categories-settings-' . $product->get_id(),
				'class' => 'ywgc-excluded-categories-settings',
				'value' => $exclude_categories_settings,
				'data'  => array(
					'product-id' => $product->get_id(),
				),
			),
			true
		);
		?>
	</div>
	<div class="ywgc-form-field__description">
		<?php esc_html_e( 'Enable to prevent the redeem of this gift card code for the purchase of specific products categories.', 'yith-woocommerce-gift-cards' ); ?>
		<br>
		<?php esc_html_e( '(Option not valid for gift card codes used as coupons)', 'yith-woocommerce-gift-cards' ); ?>
	</div>
	<br>
	<div class="ywgc-excluded-categories-settings-container ywgc-hidden" >
		<div class="ywgc-form-field__content ywgc-excluded-categories-field-content">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'       => 'ywgc-excluded-categories',
					'name'     => 'ywgc-excluded-categories',
					'class'    => 'wc-enhanced-select',
					'type'     => 'select-buttons',
					'multiple' => true,
					'options'  => $categories,
					'value'    => $excluded_categories,
				),
				true
			);
			?>
		</div>
		<div class="ywgc-form-field__description">
			<?php esc_html_e( 'Choose the product categories to exclude.', 'yith-woocommerce-gift-cards' ); ?>
		</div>
	</div>
</div>
<?php
