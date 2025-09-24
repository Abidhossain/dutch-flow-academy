<?php
/**
 * Vendor create modal list table
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 * @var YITH_Vendors_Vendors_List_Table $vendors_table YITH_Vendors_Vendors_List_Table class instance.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$steps          = YITH_Vendors_Factory::get_modal_steps();
$first_step     = current( array_keys( $steps ) ); // Make sure to het the first step key.
$singular_label = YITH_Vendors_Taxonomy::get_singular_label( 'strtolower' );

?>
<script type="text/template" id="tmpl-yith-wcmv-modal-vendor-modal-header">
	<# if( data.modalType == 'edit' ) { #>
	<?php
	// translators: %s stand for the singular label for vendor taxonomy. Default is vendor.
	echo esc_html( sprintf( _x( 'Edit %s', '[Admin]Create vendor modal title', 'yith-woocommerce-product-vendors' ), $singular_label ) );
	?>
	<# } else { #>
	<?php
	// translators: %s stand for the singular label for vendor taxonomy. Default is vendor.
	echo esc_html( sprintf( _x( 'Add %s', '[Admin]Create vendor modal title', 'yith-woocommerce-product-vendors' ), $singular_label ) );
	?>
	<# } #>
	<div class="form-header">
		<ul class="steps-list">
			<?php foreach ( $steps as $step_id => $step ) : ?>
				<li data-step="<?php echo esc_attr( $step_id ); ?>" class="<?php echo( $step_id === $first_step ? 'current' : '' ); ?>">
					<a href="#<?php echo esc_attr( $step_id ); ?>">
						<span class="step-number"></span>
						<span class="step-label"><?php echo esc_html( $step['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</script>
<script type="text/template" id="tmpl-yith-wcmv-modal-vendor-modal-content">
	<form method="POST" id="vendor-modal-form">
		<?php foreach ( $steps as $step_id => $step ) : ?>
			<fieldset data-step="<?php echo esc_attr( $step_id ); ?>" class="step-content vendor-fields-container" <?php echo ( $step_id !== $first_step ) ? 'style="display:none;"' : ''; ?>>
				<?php
				foreach ( $step['fields'] as $field_id => $field ) :
					if ( ! isset( $field['type'] ) || ! in_array( $field['type'], array( 'text', 'number', 'textarea', 'textarea-editor' ), true ) ) {
						$field['custom_attributes']['data-value'] = "{{data.$field_id}}";
					} else {
						$field['value'] = "{{data.$field_id}}";
					}
					yith_wcmv_print_vendor_admin_fields( $field_id, $field );
				endforeach;
				?>
			</fieldset>
		<?php endforeach; ?>
		<# if( data.modalType == 'edit' ) { #>
		<input type="hidden" name="vendor_id" id="vendor_id" value="{{data.vendor_id}}">
		<?php wp_nonce_field( 'yith_wcmv_edit_vendor', '_vendor_modal_nonce' ); ?>
		<# } else { #>
		<?php wp_nonce_field( 'yith_wcmv_create_vendor', '_vendor_modal_nonce' ); ?>
		<# } #>
	</form>
</script>
<script type="text/template" id="tmpl-yith-wcmv-modal-vendor-modal-footer">
	<div class="form-actions">
		<a href="#" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl vendor-next-step"><?php echo esc_html_x( 'Next >', '[Admin] Vendor modal button label', 'yith-woocommerce-product-vendors' ); ?></a>
		<input type="button" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl vendor-modal-submit" value="<?php echo esc_html_x( 'Save', '[Admin] Vendor modal button label', 'yith-woocommerce-product-vendors' ); ?>" style="display: none;">
	</div>
</script>
