<?php
/**
 * Template for displaying a single shipping method
 *
 * @var integer $zone_id   The shipping zone ID.
 * @var integer $method_id The shipping method ID.
 * @var array   $method    The shipping method data.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>
<li class="yith-wcmv-shipping-zone-settings__method boxed-style" data-method_id="<?php echo esc_attr( $method_id ); ?>">
	<h4><?php echo esc_html( $method['method_title'] ); ?></h4>
	<div class="yith-wcmv-shipping-zone-settings__method-actions">
		<?php
		foreach ( array( 'edit', 'trash' ) as $component ) :
			yith_plugin_fw_get_component(
				array(
					'type'   => 'action-button',
					'action' => $component,
					'icon'   => $component,
					'class'  => $component . '-method',
					'data'   => array(
						'zone_id'   => $zone_id,
						'method_id' => $method_id,
					),
					'url'    => '#',
				)
			);
		endforeach;
		?>
	</div>
	<input type="hidden" id="yith-wcmv-shipping-method-data-<?php echo esc_attr( $method_id ); ?>" name="zone_data[<?php echo esc_attr( $zone_id ); ?>][zone_shipping_methods][<?php echo esc_attr( $method_id ); ?>]" value="<?php echo esc_attr( wp_json_encode( $method ) ); ?>"/>
</li>
