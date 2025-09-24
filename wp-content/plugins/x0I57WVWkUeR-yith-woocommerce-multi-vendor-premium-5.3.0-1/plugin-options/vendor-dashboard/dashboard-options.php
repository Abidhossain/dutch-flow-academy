<?php
/**
 * Plugin dashboard
 *
 * @author  YITH
 * @package YITH WooCommerce Customize My Account Page
 * @version 3.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'dashboard' => array(
		'dashboard-report' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wcmv_admin_dashboard_report',
		),
	),
);
