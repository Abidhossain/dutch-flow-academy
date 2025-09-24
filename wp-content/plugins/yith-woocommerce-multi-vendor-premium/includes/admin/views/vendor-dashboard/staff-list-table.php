<?php
/**
 * Admin commissions list table
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 * @var YITH_Vendors_Staff_List_Table $staff_table        YITH_Vendors_Staff_List_Table class instance.
 * @var array                         $add_staff_fields   An array of fields for the add staff form.
 * @var array                         $permissions_fields An array of fields for the permissions form.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>
<div class="custom-list-table">
	<?php if ( ! $staff_table->has_items() ) : ?>
		<div class="yith-plugin-fw__list-table-blank-state">
			<img class="yith-plugin-fw__list-table-blank-state__icon" src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>icons/staff.svg" width="65" alt=""/>
			<div class="yith-plugin-fw__list-table-blank-state__message"><?php echo esc_html_x( 'No staff member added to this store.', '[Admin]Commissions table empty message', 'yith-woocommerce-product-vendors' ); ?></div>
		</div>
	<?php
	else :
		?>
		<form id="vendors-list-table" method="GET">
			<input type="hidden" name="page" value="<?php echo ! empty( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : ''; ?>"/>
			<input type="hidden" name="tab" value="<?php echo ! empty( $_GET['tab'] ) ? esc_attr( wp_unslash( $_GET['tab'] ) ) : ''; ?>"/>
			<?php $staff_table->display(); ?>
		</form>
	<?php
	endif;
	?>
</div>
<script type="text/template" id="tmpl-yith-wcmv-modal-new-staff">
	<form method="POST" id="add-staff-form">
		<table class="form-table">
			<?php
			foreach ( $add_staff_fields as $key => $field ) :
				$field['id']   = $key;
				$field['name'] = $key;
				yith_wcmv_print_panel_field( $field );
			endforeach;
			?>
		</table>
		<p class="submit">
			<input type="hidden" name="action" id="action" value="<?php echo esc_attr( YITH_Vendors_Staff_Admin::ADMIN_STAFF_ACTION ); ?>"/>
			<input type="hidden" name="request" id="request" value="add"/>
			<?php wp_nonce_field( YITH_Vendors_Staff_Admin::ADMIN_STAFF_ACTION ); ?>
			<button class="yith-plugin-fw__button--primary"><?php echo esc_html_x( 'Add', '[Admin]Modal button label', 'yith-woocommerce-product-vendors' ); ?></button>
		</p>
	</form>
</script>
<script type="text/template" id="tmpl-yith-wcmv-modal-edit-staff-permissions">
	<form method="POST" id="edit-staff-permissions-form">
		<table class="form-table">
			<tr>
				<th scope="row" class="titledesc"><?php echo esc_html_x( 'This staff member can:', '[Admin]Modal button label', 'yith-woocommerce-product-vendors' ); ?></th>
				<td class="forminp forminp-checkbox">
					<?php foreach ( $permissions_fields as $key => $label ) : ?>
						<fieldset>
							<label for="permission_manage_<?php echo esc_attr( $key ); ?>">
								<input name="<?php echo esc_attr( $key ); ?>" id="permission_manage_<?php echo esc_attr( $key ); ?>" type="checkbox" value="yes" checked="checked" data-value="{{data.permissions.<?php echo esc_attr( $key ); ?>}}">
								<?php echo esc_html( $label ); ?>
							</label>
						</fieldset>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="hidden" name="action" id="action" value="<?php echo esc_attr( YITH_Vendors_Staff_Admin::ADMIN_STAFF_ACTION ); ?>"/>
			<input type="hidden" name="request" id="request" value="edit_permissions"/>
			<?php wp_nonce_field( YITH_Vendors_Staff_Admin::ADMIN_STAFF_ACTION ); ?>
			<input type="hidden" name="id" id="id" value="{{data.id}}"/>
			<button class="yith-plugin-fw__button--primary"><?php echo esc_html_x( 'Save Permissions', '[Admin]Modal button label', 'yith-woocommerce-product-vendors' ); ?></button>
		</p>
	</form>
</script>
