<?php
/**
 * Vendors Report Abuse list options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 5.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'report-abuse-list' => array(
		'reported_abuse_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => 'reported_abuse',
			'wp-list-style' => 'classic',
		),
	),
);
