<?php
/**
 * Template for displaying the ajax-vendors field
 * Note: the stored value is an array if WooCommerce >= 3.0; string otherwise
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

yith_plugin_fw_enqueue_enhanced_select();

$field = wp_parse_args( $field, array(
	'id'       => '',
	'name'     => '',
	'class'    => 'yith-wcmv-ajax-search',
	'no_value' => false,
	'multiple' => false,
	'data'     => array(),
	'style'    => 'width:400px',
	'value'    => '',
) );

list ( $field_id, $class, $multiple, $data, $name, $style, $value ) = yith_plugin_fw_extract( $field, 'id', 'class', 'multiple', 'data', 'name', 'style', 'value' );

$value = is_array( $value ) ? implode( ',', $value ) : $value;
$data  = wp_parse_args( $data, array(
	'value'       => $value,
	'action'      => 'search_for_vendors',
	'placeholder' => _x( 'Search for a vendor', '[Admin] Search vendor field placeholder', 'yith-woocommerce-product-vendors' ),
	'allow_clear' => false,
) );

// Separate select2 needed data and other data.
$select2_custom_attributes = array();
$select2_data              = array();
$select2_data_keys         = array( 'placeholder', 'allow_clear', 'action' );
foreach ( $data as $d_key => $d_value ) {
	if ( in_array( $d_key, $select2_data_keys, true ) ) {
		$select2_data[ $d_key ] = $d_value;
	} else {
		$select2_custom_attributes[ 'data-' . $d_key ] = $d_value;
	}
}

// Populate data-selected by value.
$data_selected = array();
if ( ! empty( $value ) ) {
	if ( $multiple ) {
		foreach ( explode( ',', $value ) as $vendor_id ) {
			$vendor = yith_wcmv_get_vendor( $vendor_id );
			if ( $vendor && $vendor->is_valid() ) {
				$data_selected[ $vendor_id ] = $vendor->get_name();
			}
		}
	} else {
		$vendor_id = absint( $value );
		$vendor    = yith_wcmv_get_vendor( $vendor_id );
		if ( $vendor && $vendor->is_valid() ) {
			$data_selected[ $vendor_id ] = $vendor->get_name();
		}
	}
}

?>
<div class="yith-plugin-fw-select2-wrapper">
	<?php
	if ( function_exists( 'yit_add_select2_fields' ) ) {
		yit_add_select2_fields(
			array(
				'id'                => $field_id,
				'name'              => $name,
				'class'             => $class,
				'data-multiple'     => $multiple,
				'data-placeholder'  => $select2_data['placeholder'],
				'data-allow_clear'  => $select2_data['allow_clear'],
				'data-action'       => $select2_data['action'],
				'custom-attributes' => $select2_custom_attributes,
				'style'             => $style,
				'value'             => empty( $data_selected ) ? '' : $value,
				'data-selected'     => $data_selected,
			)
		);
	}
	?>
</div>
