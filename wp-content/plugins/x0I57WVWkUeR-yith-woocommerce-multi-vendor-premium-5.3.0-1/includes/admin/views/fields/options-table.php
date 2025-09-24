<?php
/**
 * Template for displaying the options-table field
 * Note: the stored value is an array if WooCommerce >= 3.0; string otherwise
 *
 * @var array $field The field.
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

list ( $field_id, $name, $options, $std, $custom_attributes ) = yith_plugin_fw_extract( $field, 'id', 'name', 'value', 'default', 'custom_attributes', 'data' );

?>
<table class="form-table options-table" id="<?php echo esc_attr( $field_id ); ?>" data-name="<?php echo esc_attr( $name ); ?>" <?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>>
	<thead>
	<tr>
		<th class="column-label"><?php echo esc_html_x( 'Label', '[Admin]Options table column label', 'yith-woocommerce-product-vendors' ); ?></th>
		<th class="column-value"><?php echo esc_html_x( 'Value', '[Admin]Options table column label', 'yith-woocommerce-product-vendors' ); ?></th>
		<th class="column-actions"></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if ( ! empty( $options ) ) :
		$index = 0;
		foreach ( $options as $key => $label ) :
			?>
			<tr data-index="<?php echo absint( $index ); ?>">
				<td class="column-label">
					<input type="text" name="<?php echo esc_attr( $name ); ?>[<?php echo absint( $index ); ?>][label]" id="options_<?php echo absint( $index ); ?>_label" value="<?php echo esc_attr( $key ); ?>">
				</td>
				<td class="column-value">
					<input type="text" name="<?php echo esc_attr( $name ); ?>[<?php echo absint( $index ); ?>][value]" id="options_<?php echo absint( $index ); ?>_value" value="<?php echo esc_attr( $label ); ?>">
				</td>
				<td class="column-actions">
					<span class="drag yith-icon yith-icon-drag ui-sortable-handle"></span>
					<a href="#" role="button" class="delete yith-icon yith-icon-trash"></a>
				</td>
			</tr>
			<?php
			$index++;
		endforeach;
	endif;
	?>
	</tbody>
</table>
<a href="#" role="button" id="add_new_option"><?php echo esc_html_x( '+ Add new option', '[Admin]Options table add row label', 'yith-woocommerce-product-vendors' ); ?></a>
