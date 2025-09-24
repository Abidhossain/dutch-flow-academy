<?php
/**
 * Product data commission tab content.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 * @var array $field_args An array of field arguments.
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

do_action( 'yith_wcmv_before_product_data_commission_tab' );

?>
	<div id="yith_wpv_single_commission" class="panel woocommerce_options_panel">
		<div class="options_group">
			<?php woocommerce_wp_text_input( $field_args ); ?>
		</div>
	</div>
<?php
do_action( 'yith_wcmv_after_product_data_commission_tab' );