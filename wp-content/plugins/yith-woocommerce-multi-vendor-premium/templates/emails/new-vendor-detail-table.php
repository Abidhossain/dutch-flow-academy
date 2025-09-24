<?php
/**
 * Admin new vendor details table
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var YITH_Vendor $vendor Vendor object.
 * @var WP_User $owner User object.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>
<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Owner:', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
		<?php
		if ( ! empty( $owner ) ) :
			$owner_info = '';
			$href       = add_query_arg( array( 'user_id' => $owner->ID ), admin_url( 'user-edit.php' ) );
			if ( ! empty( $owner->user_firstname ) || ! empty( $owner->user_lastname ) ) {
				$owner_info = $owner->user_firstname . ' ' . $owner->user_lastname;
			} else {
				$owner_info = $owner->user_email;
			}
			?>
			<a href="<?php echo esc_url( $href ); ?>"><?php echo esc_html( $owner_info ); ?></a>
			<?php
		else :
			echo '-';
		endif;
		?>
	</td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( apply_filters( 'yith_wcmv_vendor_admin_settings_store_name_label', __( 'Store name', 'yith-woocommerce-product-vendors' ) ) ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( $vendor->get_name() ); ?></td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Location', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( $vendor->get_formatted_address() ); ?></td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( apply_filters( 'yith_wcmv_vendor_admin_settings_store_email_label', __( 'Store email', 'yith-woocommerce-product-vendors' ) ) ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( ! empty( $vendor->get_meta( 'store_email' ) ) ? $vendor->get_meta( 'store_email' ) : '-' ); ?></td>
</tr>

<tr>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php esc_html_e( 'Phone', 'yith-woocommerce-product-vendors' ); ?></td>
	<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo esc_html( ! empty( $vendor->get_meta( 'telephone' ) ) ? $vendor->get_meta( 'telephone' ) : '-' ); ?></td>
</tr>
