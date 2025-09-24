<?php
/**
 * YITH Vendors Modules Panel Tab
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'modules' => array(
		'modules-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'yith_wcmv_modules_panel_tab',
			'description'    => _x( 'Modules help you add advanced features to your marketplace. We have included some powerful modules FREE of charge, as well as more advanced plugins that we have developed to fully integrate with our Multi Vendor plugin.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
			'show_container' => true,
		),
	),
);
