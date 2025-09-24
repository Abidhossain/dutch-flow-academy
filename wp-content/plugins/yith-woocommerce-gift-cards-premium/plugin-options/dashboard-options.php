<?php
/**
 * Dashboard options
 *
 * @package YITH\GiftCards\PluginOptions
 */

$dashboard_tab = array(
	'dashboard' => array(
		'custom-post-type_list_table' => array(
			'type'                  => 'post_type',
			'post_type'             => 'gift_card',
			'wp-list-style'         => 'classic',
			'wp-list-auto-h-scroll' => true,
		),
	),
);

return $dashboard_tab;
