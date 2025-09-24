<?php
/**
 * YITH Vendors Emails Panel Tab
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 5.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'emails' => array(
		'emails-tab' => array(
			'type'           => 'custom_tab',
			'action'         => 'yith_wcmv_emails_panel_tab',
			'description'    => __( 'Manage and configure the email notifications for your vendors.', 'yith-woocommerce-product-vendors' ),
			'show_container' => true,
		),
	),
);
