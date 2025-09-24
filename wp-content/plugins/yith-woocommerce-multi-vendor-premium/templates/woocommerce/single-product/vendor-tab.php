<?php
/**
 * Vendor single product tab template.
 *
 * @author     YITH
 * @package YITH\MultiVendor
 * @var string $vendor_description The vendor description.
 * @var string $vendor_name The vendor name.
 * @var string $vendor_url Vendor store url.
 * @var YITH_Vendor $vendor Vendor instance.
 */

/*
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$vendor_description = do_shortcode( $vendor_description );
$vendor_description = call_user_func( '__', $vendor_description, 'yith-woocommerce-product-vendors' );
$vendor_name        = call_user_func( '__', $vendor_name, 'yith-woocommerce-product-vendors' );
?>

<h2>
	<a href="<?php echo esc_url( $vendor_url ); ?>">
		<?php echo wp_kses_post( $vendor_name ); ?>
	</a>
</h2>

<div class="vendor-description">
	<?php echo wp_kses_post( wpautop( $vendor_description ) ); ?>
</div>

<?php
do_action( 'yith_wcmv_vendor_tab_information', $vendor );
