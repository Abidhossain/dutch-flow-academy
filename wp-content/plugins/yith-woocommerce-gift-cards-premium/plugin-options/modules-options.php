<?php
/**
 * Modules options
 *
 * @package YITH\GiftCards\PluginOptions
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

return array(
	'modules' => array(
		'modules-tab' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_ywgc_print_modules_tab',
		),
	),
);
