<?php
/**
 * Became a vendor shortcode template.
 *
 * @since      Version 1.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 * @var string $become_a_vendor_label True if is become a vendor page, false otherwise.
 * @var array  $fields Array of registration form fields.
 */

/*
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<div id="yith-become-a-vendor" class="woocommerce shortcodes">
	<?php do_action( 'yith_wcmv_before_became_a_vendor_form' ); ?>

	<form method="post" class="register">

		<?php
		foreach ( $fields as $key => $field ) :
			woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, ( isset( $field['default'] ) ? $field['default'] : '' ) ) );
		endforeach;
		?>

		<p class="form-row submit-wrapper">
			<?php wp_nonce_field( 'woocommerce-register' ); ?>
			<button type="submit" id="yith-become-a-vendor-submit" class="<?php esc_attr_e( apply_filters( 'yith_wpv_become_a_vendor_button_class', 'button' ) ); ?>" name="register">
				<?php echo esc_html( $become_a_vendor_label ); ?>
			</button>
			<input type="hidden" id="yith-vendor-register" name="vendor-register" value="1">
			<input type="hidden" id="vendor-antispam" name="vendor-antispam" value="">
		</p>
	</form>

	<?php do_action( 'yith_wcmv_after_became_a_vendor_form' ); ?>
</div>
