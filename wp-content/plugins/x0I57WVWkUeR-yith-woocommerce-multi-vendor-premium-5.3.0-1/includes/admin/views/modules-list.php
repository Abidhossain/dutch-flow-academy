<?php
/**
 * Admin modules list
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 * @var array $modules An array of modules.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<div id="modules-container">
	<?php foreach ( $modules as $module_id => $module ) : ?>
		<div class="module" data-module="<?php echo esc_attr( $module_id ); ?>">
			<header>
				<?php if ( isset( $module['title'] ) ) : ?>
					<h3><?php echo esc_html( $module['title'] ); ?></h3>
				<?php endif; ?>
			</header>
			<?php if ( isset( $module['description'] ) ) : ?>
				<div class="module-description"><?php echo wp_kses_post( $module['description'] ); ?></div>
			<?php endif; ?>
			<div class="module-activation">
				<label for="<?php echo esc_attr( $module_id ); ?>_active">
					<?php echo esc_html__( 'Enable module', 'yith-woocommerce-product-vendors' ); ?>
				</label>
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'                => $module_id . '_active',
						'name'              => $module_id . '_active',
						'type'              => 'onoff',
						'default'           => 'no',
						'class'             => 'on-off-module',
						'value'             => YITH_Vendors_Modules_Handler::instance()->is_module_active( $module_id ) ? 'yes' : 'no',
						'custom_attributes' => array( 'data-module' => $module_id ),
					),
					true,
					false
				);
				?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
