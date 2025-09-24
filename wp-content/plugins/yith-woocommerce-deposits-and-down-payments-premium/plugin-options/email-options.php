<?php
/**
 * Email options
 *
 * @package YITH\Deposits\Options
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'email' => array(
		'yith_wcdp_email_settings' => array(
			'type'        => 'custom_tab',
			'action'      => 'yith_wcdp_email_settings',
			'title'       => __( 'Emails', 'yith-woocommerce-deposits-and-down-payments' ),
			'description' => __( 'Manage and customize the emails sent by the plugin.', 'yith-woocommerce-deposits-and-down-payments' ),
		),
	),
);
