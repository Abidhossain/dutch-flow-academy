<?php
/**
 * YITH Vendors Vendors Tab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'payments' => array(
		'payments-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'yith_wcmv_vendor_dashboard_payments_tab',
			'show_container' => false,
		),
	),
);
