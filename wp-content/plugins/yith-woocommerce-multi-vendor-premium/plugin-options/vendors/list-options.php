<?php
/**
 * YITH Vendors Vendors List Table subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'vendors-list' => array(
		'vendors-list-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'yith_wcmv_vendors_admin_list_table',
			'show_container' => false,
		),
	),
);
