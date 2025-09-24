<?php
/**
 * Shipping polices subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'zones' => array(
		'zones-options' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wcmv_vendor_dashboard_shipping_zones_tab',
		),
	),
);
