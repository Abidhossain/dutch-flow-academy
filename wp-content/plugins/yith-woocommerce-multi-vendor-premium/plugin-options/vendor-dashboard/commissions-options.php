<?php
/**
 * YITH Vendors Commissions Tab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'commissions' => array(
		'commissions-options' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wcmv_commissions_admin_list_table',
		),
	),
);
