<?php
/**
 * YITH Vendors Gateways settings array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'commissions-gateways' => array(
		'commissions-gateways-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'yith_wcmv_commissions_admin_gateways',
			'show_container' => true,
		),
	),
);
