<?php
/**
 * Plugin dashboard
 *
 * @author  YITH
 * @package YITH WooCommerce Customize My Account Page
 * @version 3.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return apply_filters(
	'yith_wcmv_dashboard_settings',
	array(
		'dashboard' => array(
			'dashboard-report' => array(
				'type'        => 'custom_tab',
				'action'      => 'yith_wcmv_admin_dashboard_report',
				'description' => _x( 'An overview of sales and commissions generated in your marketplace.', '[Admin]Panel tab description', 'yith-woocommerce-product-vendors' ),
			),
		),
	)
);
