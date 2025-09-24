<?php
/**
 * Vendors Announcements subtab options array
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

return array(
	'announcements' => array(
		'announcement_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => 'announcement',
			'wp-list-style' => 'classic',
		),
	),
);
