<?php
/**
 * Admin gateways list template
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 * @var array $gateways An array of available gateways.
 * @var array $columns  Table columns to show.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<div id="yith-wcmv-gateways-list">
	<div class="list-head">
		<?php foreach ( $columns as $key => $column ) : ?>
			<div class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></div>
		<?php endforeach; ?>
	</div>
	<?php
	foreach ( $gateways as $gateway_id => $gateway ) :
		$has_option = ! empty( $gateway['options'] ) && $gateway['available'];
		$item_classes = array(
			$has_option ? 'has-options' : '',
			! $gateway['available'] ? 'not-available' : '',
		);
		?>
		<div class="list-item <?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
			<?php foreach ( $columns as $key => $column ) : ?>
				<div class="<?php echo esc_attr( $key ); ?>">
					<?php
					switch ( $key ) :
						case 'name':
							if ( $has_option ) {
								echo '<i class="yith-icon yith-icon-arrow-right"></i>';
							}
							$displayed_gateway_name = apply_filters( "yith_wcmv_{$gateway_id}_gateway_display_name", $gateway['name'] );
							$displayed_gateway_name = ! empty( $displayed_gateway_name ) ? $displayed_gateway_name : _x( '(no title)', '[Admin]: means "no name". Powered by WooCommerce. Please, refer to WooCommerce .po file for more details about that.', 'yith-woocommerce-product-vendors' );
							echo '<span>' . esc_html( $displayed_gateway_name ) . '</span>';
							break;

						case 'id':
							echo wp_kses_post( apply_filters( "yith_wcmv_displayed_{$gateway_id}_id", $gateway_id ) );
							break;

						case 'status':
							?>
							<span class="yith-plugin-ui enable-gateway-trigger">
								<?php
								$custom_attributes = array( 'data-gateway_id' => $gateway_id );
								if ( ! $gateway['available'] ) {
									$custom_attributes['disabled'] = 'disabled';
								}

								yith_plugin_fw_get_field(
									array(
										'id'                => 'yith_wcmv_enabled_gateway_' . $gateway_id,
										'name'              => 'yith_wcmv_enabled_gateway_' . $gateway_id,
										'type'              => 'onoff',
										'default'           => 'no',
										'value'             => $gateway['enabled'] ? 'yes' : 'no',
										'custom_attributes' => $custom_attributes,
									),
									true,
									false
								);
								?>
							</span>
							<?php
							break;

						default:
							// Backward compatibility.
							$gateway = YITH_Vendors_Gateways::get_gateway( $gateway_id );
							do_action( 'yith_wcmv_payment_gateways_setting_column_' . $key, $gateway );
							break;
					endswitch;
					?>
				</div>
			<?php endforeach; ?>

			<?php if ( $has_option ) : ?>
				<div class="options yith-plugin-fw">
					<form class="gateway-options-form" method="POST">
						<table class="form-table">
							<?php
							foreach ( $gateway['options'] as $option ) :
								class_exists( 'YIT_Plugin_Panel_WooCommerce' ) && YIT_Plugin_Panel_WooCommerce::add_yith_field( $option );
							endforeach;
							?>
						</table>
						<div class="actions">
							<input type="hidden" name="gateway_id" id="gateway-id" value="<?php echo esc_attr( $gateway_id ); ?>"/>
							<button type="submit" class="button-primary"><?php echo esc_html_x( 'Save', '[Admin]Gateway options save button label', 'yith-woocommerce-product-vendors' ); ?></button>
						</div>
					</form>
				</div>
			<?php endif; ?>

		</div>
	<?php endforeach; ?>
</div>
