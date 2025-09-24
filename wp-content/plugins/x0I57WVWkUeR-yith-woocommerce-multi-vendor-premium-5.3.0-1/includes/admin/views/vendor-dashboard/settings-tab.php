<?php
/**
 * Vendor dashboard settings tab template
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 * @var array   $fields    The form fields.
 * @var integer $vendor_id The current vendor id.
 * @var string  $section   The current section shown.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>
<form id="plugin-fw-wc" method="POST">
	<div class="yith-plugin-fw__panel__secondary-notices"></div>
	<div class="vendor-fields-container">
		<?php
		foreach ( $fields as $field_id => $field ) :
			yith_wcmv_print_vendor_admin_fields( $field_id, $field );
		endforeach;
		?>
		<input type="hidden" id="vendor_id" name="vendor_id" value="<?php echo absint( $vendor_id ); ?>">
	</div>

	<input type="hidden" id="action" name="action" value="<?php echo esc_attr( YITH_Vendors_Admin_Vendor_Dashboard_Panel::FORM_ACTION ); ?>">
	<input type="hidden" id="section" name="section" value="settings">
	<?php wp_nonce_field( YITH_Vendors_Admin_Vendor_Dashboard_Panel::FORM_ACTION ); ?>
</form>

<div class="yith-plugin-fw__panel__content__actions">
	<button class="yith-plugin-fw__button yith-plugin-fw__button--primary" id="yith-plugin-fw__panel__content__save">
		<?php echo esc_html_x( 'Save Options', '[Admin]Button Label', 'yith-woocommerce-product-vendors' ); ?>
	</button>
</div>