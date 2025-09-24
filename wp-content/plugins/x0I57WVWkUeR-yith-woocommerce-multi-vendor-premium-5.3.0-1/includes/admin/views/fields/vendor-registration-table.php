<?php
/**
 * Template for displaying the ajax-vendors field
 * Note: the stored value is an array if WooCommerce >= 3.0; string otherwise
 *
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$columns = apply_filters(
	'yith_wcmv_vendor_registration_table_columns',
	array(
		'name'         => _x( 'Name', '[Admin]Vendor registration table column name', 'yith-woocommerce-product-vendors' ),
		'type'         => _x( 'Type', '[Admin]Vendor registration table column name', 'yith-woocommerce-product-vendors' ),
		'label'        => _x( 'Label', '[Admin]Vendor registration table column name', 'yith-woocommerce-product-vendors' ),
		'connected_to' => _x( 'Connected to', '[Admin]Vendor registration table column name', 'yith-woocommerce-product-vendors' ),
		'required'     => _x( 'Required', '[Admin]Vendor registration table column name', 'yith-woocommerce-product-vendors' ),
		'active'       => _x( 'Active', '[Admin]Vendor registration table column name', 'yith-woocommerce-product-vendors' ),
		'actions'      => '',
	)
);

$fields = YITH_Vendors_Registration_Form::get_fields();

?>

<div class="yith-vendor-registration-table-wrapper">
	<div class="yith-vendor-registration-table__actions">
		<button id="yith-vendor-registration-table__add-fields"
			class="yith-vendor-registration-table__add-fields button-primary"><?php echo esc_html_x( 'Add field', '[Admin]Vendor registration table button label', 'yith-woocommerce-product-vendors' ); ?></button>
		<button id="yith-vendor-registration-table__restore-default"
			class="yith-vendor-registration-table__restore-default button-secondary yith-button-ghost"><?php echo esc_html_x( 'Restore Default', '[Admin]Vendor registration table button label', 'yith-woocommerce-product-vendors' ); ?></button>
	</div>

	<?php do_action( 'yith_wcmv_vendor_registration_form_before_table' ); ?>

	<table class="yith-vendor-registration-table">
		<thead>
		<tr>
			<?php
			foreach ( $columns as $key => $label ) :
				?>
				<th class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( ! empty( $fields ) ) :
			foreach ( $fields as $field_id => $field_options ) :
				if ( empty( $field_options['name'] ) ) {
					continue;
				}

				if ( ! empty( $field_options['class'] ) ) {
					$field_options['class'] = implode( ',', $field_options['class'] );
				}
				?>
				<tr data-id="<?php echo esc_attr( $field_id ); ?>" data-name="<?php echo esc_attr( $field_options['name'] ); ?>"
					data-options="<?php echo esc_attr( wp_json_encode( $field_options ) ); ?>">
					<?php
					foreach ( $columns as $key => $label ) :
						$current_value = ! empty( $field_options[ $key ] ) ? $field_options[ $key ] : '-';
						?>
						<td class="<?php echo esc_attr( $key ); ?>">
							<?php
							if ( 'required' === $key ) :
								if ( 'yes' === $current_value ) :
									?>
									<span class="is_required">
										<img src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>icons/tick.svg" alt>
									</span>
								<?php endif; ?>
							<?php
							elseif ( 'active' === $key ) :
								if ( 'vendor-name' !== $field_id ) {
									yith_plugin_fw_get_field(
										array(
											'id'      => $key,
											'name'    => $key,
											'type'    => 'onoff',
											'default' => 'no',
											'value'   => $current_value,
										),
										true,
										false
									);
								} else {
									echo '-';
								}

							elseif ( 'actions' === $key ) :
								yith_plugin_fw_get_component(
									array(
										'type'  => 'action-button',
										'title' => __( 'Edit field', 'yith-woocommerce-product-vendors' ),
										'class' => 'yith-vendor-registration-table__edit-field',
										'icon'  => 'edit',
										'url'   => '#',
									)
								);

								if ( ! YITH_Vendors_Registration_Form::is_default_field( $field_id ) ) {
									yith_plugin_fw_get_component(
										array(
											'type'   => 'action-button',
											'title'  => __( 'Delete field', 'yith-woocommerce-product-vendors' ),
											'class'  => 'yith-vendor-registration-table__delete-field',
											'action' => 'trash',
											'icon'   => 'trash',
											'url'    => '#',
										)
									);
								}

								yith_plugin_fw_get_component(
									array(
										'type'  => 'action-button',
										'class' => 'yith-vendor-registration-table__drag-field',
										'icon'  => 'drag',
										'url'   => '#',
									)
								);
							else :
								echo esc_html( $current_value );
							endif;
							?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		<tfoot>

		</tfoot>
	</table>

	<?php do_action( 'yith_wcmv_vendor_registration_form_after_table' ); ?>

	<script type="text/template" id="tmpl-yith-wcmv-modal-registration-form">
		<form method="POST" id="vendor-registration-field-form">
			<table class="form-table">
				<tbody>
				<?php
				foreach ( YITH_Vendors_Registration_Form::get_admin_modal_fields() as $key => $field ) {
					$field['id']                              = $key;
					$field['name']                            = 'registration_form[' . $key . ']';
					$field['custom_attributes']['data-value'] = '{{data.' . $key . '}}';
					yith_wcmv_print_panel_field( $field );
				}
				?>
				</tbody>
			</table>
			<input type="hidden" name="field_id" id="field_id" value="{{data.field_id}}">
		</form>
	</script>
	<script type="text/template" id="tmpl-yith-wcmv-modal-registration-form-footer">
		<div class="form-actions">
			<button class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl vendor-registration-field-form-submit">
				<?php echo esc_html_x( 'Save', '[Admin] Vendor modal button label', 'yith-woocommerce-product-vendors' ); ?>
			</button>
		</div>
	</script>
</div>
