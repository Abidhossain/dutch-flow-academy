<?php
/**
 * Gift Card Categories options
 *
 * @package YITH\GiftCards\PluginOptions
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

$gift_card_categories_tab = array(
	'settings-gift-card-categories' => array(
		'configuration-gift-card-categories-list' => array(
			'type'          => 'taxonomy',
			'taxonomy'      => YWGC_CATEGORY_TAXONOMY,
			'wp-list-style' => 'classic',
		),
	),
);

return $gift_card_categories_tab;
