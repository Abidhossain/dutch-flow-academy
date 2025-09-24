<?php
/**
 * Vendor name product template.
 *
 * @author     YITH
 * @package YITH\MultiVendor
 * @var YITH_Vendor $vendor The vendor.
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

<div class="vendor-name-wrapper">
	<span class="vendor-name">
		<?php echo esc_html( apply_filters( 'yith_frontend_vendor_name_prefix', __( 'by', 'yith-woocommerce-product-vendors' ) ) ); ?>
		<a class="vendor-name-link" href="<?php echo esc_url( $vendor->get_url() ); ?>"><?php echo esc_html( $vendor->get_name() ); ?></a>
	</span>
	<?php if ( isset( $sales_info ) ) : ?>
		<span class="vendor-item-sales">
			<?php echo esc_html( $sales_info ); ?>
		</span>
	<?php endif; ?>
</div>
