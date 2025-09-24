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

$vendor  = isset( $vendor ) ? $vendor : yith_wcmv_get_vendor( 'current', 'product' );
$message = ( $vendor && $vendor->is_valid() ) ? call_user_func( '__', $vendor->get_meta( 'vacation_message' ), 'yith-woocommerce-product-vendors' ) : '';

if ( $message ) : ?>
	<div class="store-vacation">
		<?php echo wp_kses_post( $message ); ?>
	</div>
	<?php
endif;
