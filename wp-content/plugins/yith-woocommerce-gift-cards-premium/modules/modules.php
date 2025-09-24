<?php
/**
 * Modules list.
 *
 * @package YITH\GiftCards\Modules
 */

defined( 'ABSPATH' ) || exit;

return array(
	'gift-this-product' => array(
		'name'         => _x( 'Gift this product', 'Module name', 'yith-woocommerce-gift-cards' ),
		'description'  => __( 'Enable this module to be able to generate gift cards from your products. With the "Gift this product" feature, your customers can buy gift cards with the same value as the products they like suggesting the recipient to get them.', 'yith-woocommerce-gift-cards' ),
		'needs_reload' => true,
	),
);
