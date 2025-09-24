<?php
/**
 * Template for displaying the shipping zones field
 *
 * @since   4.0.0
 * @var array $zones The shipping zones array.
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$close_button = _x( 'Close shipping zone', '[Admin]Close shipping zone button label', 'yith-woocommerce-product-vendors' );
$add_button   = _x( 'Add shipping zone', '[Admin]Add shipping zone button label', 'yith-woocommerce-product-vendors' );
$zones        = is_array( $zones ) ? $zones : array(); // Prevent meta value different from array.

?>
<div id="yith-wcmv-shipping-zones-wrapper" class="ui-sortable">
	<button class="button-primary yith-wcmv-shipping-zones-add-button yith-add-button" data-closed_label="<?php echo esc_attr( $close_button ); ?>"
		data-opened_label="<?php echo esc_attr( $add_button ); ?>"><?php echo esc_html( $add_button ); ?></button>

	<div class="yith-wcmv-shipping-zone-new boxed-style" style="display:none;"></div>

	<div class="yith-wcmv-shipping-zones-empty" style="<?php echo ! empty( $zones ) ? 'display:none;' : ''; ?>">
		<img src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>icons/globe.svg" width="52" height="52" alt=""/>
		<p><?php echo esc_html_x( 'You have no shipping zones set yet.', '[Admin]Shipping zone empty message', 'yith-woocommerce-product-vendors' ); ?></p>
	</div>

	<div class="yith-wcmv-shipping-zones">
		<?php
		foreach ( $zones as $zone_id => $zone ) :
			/**
			 * Print a single shipping zone
			 *
			 * @var integer $zone_id The zone ID.
			 * @var array   $zone    The zone data.
			 */
			do_action( 'yith_wcmv_admin_shipping_zones_single', $zone_id, $zone );
		endforeach;
		?>
	</div>
</div>
<script type="text/template" id="tmpl-yith-wcmv-shipping-zones-new">
	<div class="yith-wcmv-shipping-zone-settings">
		<div class="yith-wcmv-shipping-zone-settings__field-wrapper">
			<label for="new_zone_name"><?php echo esc_html_x( 'Zone Name', '[Admin]Shipping zone field label', 'yith-woocommerce-product-vendors' ); ?></label>
			<span>
				<input type="text" id="new_zone_name" name="zone_data[new][zone_name]" value=""/>
			</span>
		</div>
		<div class="yith-wcmv-shipping-zone-settings__field-wrapper">
			<label for="new_zone_regions"><?php echo esc_html_x( 'Regions', '[Admin]Shipping zone field label', 'yith-woocommerce-product-vendors' ); ?></label>
			<span class="yith-wcmv-shipping-zone-settings__field">
				<select multiple="multiple" id="new_zone_regions" name="zone_data[new][zone_regions][]" class="yith-wcmv-shipping-zone-region-select"></select>
				<br>
				<button class="yith-wcmv-shipping-zone-select-action button-primary" data-action="select-all"><?php esc_html_e( 'Select All', 'yith-woocommerce-product-vendors' ); ?></button>
				<button class="yith-wcmv-shipping-zone-select-action button-secondary" data-action="remove-all"><?php esc_html_e( 'Remove All', 'yith-woocommerce-product-vendors' ); ?></button>
				<br>
				<a class="yith-wcmv-shipping-zone-postcodes-toggle" href="#"><?php esc_html_e( 'Limit to specific ZIP/postcodes', 'yith-woocommerce-product-vendors' ); ?></a>
				<span class="yith-wcmv-shipping-zone-postcodes" style="display: none;">
					<textarea name="zone_data[new][zone_post_code]" placeholder="<?php esc_attr_e( 'List 1 postcode per line', 'yith-woocommerce-product-vendors' ); ?>" cols="25" rows="5"></textarea>
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
					<li class="yith-wcmv-shipping-zone-settings__method boxed-style">
						<a href="#" class="yith-wcmv-shipping-zone-settings__method-add" data-zone_id="new"><?php echo esc_html_x( '+ Add method', '[Admin]Shipping zone field label', 'yith-woocommerce-product-vendors' ); ?></a>
					</li>
				</ul>
			</div>
		</div>
		<div class="yith-toggle-content-buttons">
			<button class="yith-save-button save-zone"><?php echo esc_html_x( 'Save', '[Admin]Shipping zone button label', 'yith-woocommerce-product-vendors' ); ?></button>
		</div>
	</div>
</script>
<script type="text/template" id="tmpl-yith-wcmv-shipping-zones-new-method">
	<form action="" method="POST">
		<p><?php esc_html_e( 'Choose the shipping method you wish to add. Only shipping methods that support zones are listed.', 'yith-woocommerce-product-vendors' ); ?></p>
		<div class="yith-wcmv-shipping-zone-method-selector">
			<select name="method_id">
				<?php
				foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
					if ( ! $method->supports( 'shipping-zones' ) ) {
						continue;
					}
					echo '<option data-description="' . esc_attr( wp_kses_post( wpautop( $method->get_method_description() ) ) ) . '" value="' . esc_attr( $method->id ) . '">' . esc_html( $method->get_method_title() ) . '</li>';
				}
				?>
			</select>

			<p class="submit-wrap">
				<button class="yith-plugin-fw__button--primary yith-plugin-fw__button"><?php echo esc_html__( 'Add shipping method', 'yith-woocommerce-product-vendors' ); ?></button>
				<input type="hidden" name="request" value="<?php echo esc_attr( YITH_Vendors_Shipping_Admin::ADD_SHIPPING_METHOD ); ?>">
				<input type="hidden" name="zone_id" value="{{data.zone_id}}">
			</p>

		</div>
	</form>
</script>
<script type="text/template" id="tmpl-yith-wcmv-shipping-zones-settings-method">
	<form action="" method="POST">
		<table class="form-table">
			<tbody>
			<?php
			foreach ( YITH_Vendors_Shipping()->admin->get_shipping_methods_fields() as $method_id => $fields ) :
				echo "<# if ( data.type_id === '{$method_id}' ) { #>"; // phpcs:ignore
				foreach ( $fields as $field ) {
					yith_wcmv_print_panel_field( $field );
				}
				echo '<# } #>';
			endforeach;
			?>
			</tbody>
		</table>

		<p class="submit-wrap">
			<button class="yith-plugin-fw__button--primary yith-plugin-fw__button"><?php echo esc_html__( 'Save changes', 'yith-woocommerce-product-vendors' ); ?></button>
			<input type="hidden" name="request" value="<?php echo esc_attr( YITH_Vendors_Shipping_Admin::EDIT_SHIPPING_METHOD ); ?>">
			<input type="hidden" name="zone_id" value="{{data.zone_id}}">
			<input type="hidden" name="method_id" value="{{data.method_id}}">
		</p>
	</form>
</script>
