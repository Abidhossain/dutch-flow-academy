<?php
/**
 * My account vendor registration template.
 *
 * @since      Version 1.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 * @var boolean $is_become_a_vendor_page True if is become a vendor page, false otherwise.
 * @var array $fields Array of registration form fields.
 */

/*
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$is_checked = ! empty( $_POST['vendor-register'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing


if ( ! apply_filters( 'yith_wcmv_force_display_vendor_registration_form', false ) && ! $is_become_a_vendor_page ) : ?>
	<p class="form-row">
		<label for="vendor-register" class="inline vendor-register-label">
			<input name="vendor-register" type="checkbox" id="vendor-register" value="yes" <?php checked( $is_checked ); ?> />
			<?php echo esc_html( apply_filters( 'yith_wcmv_register_as_vendor_text', sprintf( '%s %s', _x( 'Register as a', '[Part of]: Register as a vendor', 'yith-woocommerce-product-vendors' ), strtolower( YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ) ) ) ) ); ?>
		</label>
	</p>
<?php else : ?>
	<input name="vendor-register" type="hidden" id="vendor-register" value="yes" />
<?php endif; ?>

<div id="yith-vendor-registration" style="display: <?php echo esc_attr( ( $is_checked || $is_become_a_vendor_page ) ? 'block' : 'none' ); ?>;">

	<?php
	foreach ( $fields as $key => $field ) :
		woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key ) );
	endforeach;
	?>
	<input type="hidden" id="vendor-antispam" name="vendor-antispam" value="">
	<?php if ( $is_become_a_vendor_page ) : ?>
	<input type="hidden" id="redirect" name="redirect" value="<?php echo esc_attr( wc_get_page_permalink( 'myaccount' ) ); ?>">
	<?php endif; ?>
</div>
