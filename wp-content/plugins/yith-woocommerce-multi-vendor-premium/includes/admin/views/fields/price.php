<?php
/**
 * Template for displaying the price field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

list ( $field_id, $name, $value, $std, $custom_attributes, $data ) = yith_plugin_fw_extract( $field, 'id', 'name', 'value', 'std', 'custom_attributes', 'data' );

$value = wc_format_localized_price( $value );
?>
<input type="text"
	id="<?php echo esc_attr( $field_id ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	class="wc_input_price"
	value="<?php echo esc_attr( $value ); ?>"

	<?php if ( isset( $std ) ) : ?>
		data-std="<?php echo esc_attr( $std ); ?>"
	<?php endif; ?>

	<?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>
	<?php yith_plugin_fw_html_data_to_string( $data, true ); ?>
/>


