<?php
/**
 * Template for displaying a single shipping zone
 *
 * @since   4.0.0
 * @var integer $zone_id The zone ID.
 * @var array   $zone    The zone data.
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>
<div class="yith-wcmv-shipping-zone boxed-style" data-zone_id="<?php echo esc_attr( $zone_id ); ?>">
	<div class="yith-wcmv-shipping-zone-title">
		<h4>
			<?php echo esc_html( $zone['zone_name'] ); ?>
		</h4>
		<div class="yith-wcmv-shipping-zone__actions">
			<?php
			foreach ( array( 'edit', 'trash', 'drag' ) as $component ) :
				yith_plugin_fw_get_component(
					array(
						'type'   => 'action-button',
						'action' => $component,
						'icon'   => $component,
						'class'  => $component . '-zone',
						'data'   => array(
							'zone_id' => $zone_id,
						),
						'url'    => '#',
					)
				);
			endforeach;
			?>
		</div>
	</div>
	<div class="yith-wcmv-shipping-zone-settings" style="display: none;">
		<div class="yith-wcmv-shipping-zone-settings__field-wrapper">
			<label for="zone_data_<?php echo esc_attr( $zone_id ); ?>_zone_name"><?php echo esc_html_x( 'Zone Name', '[Admin]Shipping zone field label', 'yith-woocommerce-product-vendors' ); ?></label>
			<span>
				<input type="text" id="zone_data_<?php echo esc_attr( $zone_id ); ?>_zone_name" name="zone_data[<?php echo esc_attr( $zone_id ); ?>][zone_name]" value="<?php echo ! empty( $zone['zone_name'] ) ? esc_attr( $zone['zone_name'] ) : ''; ?>"/>
			</span>
		</div>

		<div class="yith-wcmv-shipping-zone-settings__field-wrapper">
			<label for="zone_data_<?php echo esc_attr( $zone_id ); ?>_zone_regions"><?php echo esc_html_x( 'Regions', '[Admin]Shipping zone field label', 'yith-woocommerce-product-vendors' ); ?></label>
			<span class="yith-wcmv-shipping-zone-settings__field">
				<select multiple="multiple" id="zone_data_<?php echo esc_attr( $zone_id ); ?>_zone_regions" name="zone_data[<?php echo esc_attr( $zone_id ); ?>][zone_regions][]"
					data-value="<?php echo esc_attr( wp_json_encode( ! empty( $zone['zone_regions'] ) ? $zone['zone_regions'] : array() ) ); ?>" class="yith-wcmv-shipping-zone-region-select">
				</select>
				<br>
				<button class="yith-wcmv-shipping-zone-select-action button-primary" data-action="select-all"><?php esc_html_e( 'Select All', 'yith-woocommerce-product-vendors' ); ?></button>
				<button class="yith-wcmv-shipping-zone-select-action button-secondary" data-action="remove-all"><?php esc_html_e( 'Remove All', 'yith-woocommerce-product-vendors' ); ?></button>
				<br>
				<a class="yith-wcmv-shipping-zone-postcodes-toggle" href="#"><?php esc_html_e( 'Limit to specific ZIP/postcodes', 'yith-woocommerce-product-vendors' ); ?></a>
				<br>
				<span class="yith-wcmv-shipping-zone-postcodes" style="<?php echo empty( $zone['zone_post_code'] ) ? 'display:none;' : ''; ?>">
					<textarea name="zone_data[<?php echo esc_attr( $zone_id ); ?>][zone_post_code]" placeholder="<?php esc_attr_e( 'List 1 postcode per line', 'yith-woocommerce-product-vendors' ); ?>" cols="25" rows="5"><?php echo esc_html( ! empty( $zone['zone_post_code'] ) ? $zone['zone_post_code'] : '' ); ?></textarea>
					<span class="description">
						<?php echo wp_kses_post( apply_filters( 'yith_wcmv_postcodes_description', __( 'Postcodes containing wildcards (e.g. CB23*) and fully numeric ranges (e.g. <code>90210...99000</code>) are also supported.', 'yith-woocommerce-product-vendors' ) ) ); ?>
					</span>
				</span>
			</span>
		</div>

		<div class="yith-wcmv-shipping-zone-settings__methods-wrapper">
			<span class="label"><?php echo esc_html_x( 'Shipping methods', '[Admin]Shipping zone field label', 'yith-woocommerce-product-vendors' ); ?></span>
			<div class="yith-wcmv-shipping-zone-settings__methods">
				<ul>
					<?php
					foreach ( $zone['zone_shipping_methods'] as $method_id => $method ) :
						/**
						 * Print a single shipping method
						 *
						 * @var integer $zone_id   The zone ID.
						 * @var integer $method_id The shipping method ID.
						 * @var array   $method    The shipping method data.
						 */
						do_action( 'yith_wcmv_admin_shipping_methods_single', $zone_id, $method_id, $method );
					endforeach;
					?>
					<li class="yith-wcmv-shipping-zone-settings__method boxed-style">
						<a href="#" class="yith-wcmv-shipping-zone-settings__method-add" data-zone_id="<?php echo esc_attr( $zone_id ); ?>"><?php echo esc_html_x( '+ Add method', '[Admin]Shipping zone field label', 'yith-woocommerce-product-vendors' ); ?></a>
					</li>
				</ul>
			</div>
		</div>

		<div class="yith-toggle-content-buttons">
			<button class="yith-save-button save-zone"><?php echo esc_html_x( 'Save', '[Admin]Shipping zone button label', 'yith-woocommerce-product-vendors' ); ?></button>
		</div>
	</div>
</div>
