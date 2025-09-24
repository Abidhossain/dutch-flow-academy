<?php
/**
 * YITH Vendors Staff Tab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'staff' => array(
		'staff-tab' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wcmv_vendor_dashboard_staff_tab',
		),
	),
);
