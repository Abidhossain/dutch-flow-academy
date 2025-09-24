<?php
/**
 * Email options
 *
 * @package YITH\GiftCards\PluginOptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'email' => array(
		'yith_ywgc_email_settings' => array(
			'type'        => 'custom_tab',
			'action'      => 'yith_ywgc_email_settings',
			'title'       => __( 'Emails', 'yith-woocommerce-gift-cards' ),
			'description' => __( 'Manage and customize the emails sent to users about gift cards.', 'yith-woocommerce-gift-cards' ),
		),
	),
);
