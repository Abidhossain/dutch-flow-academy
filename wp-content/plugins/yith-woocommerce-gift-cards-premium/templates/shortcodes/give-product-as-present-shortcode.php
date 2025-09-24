<?php
/**
 * Variable product add to cart
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_get_template(
	'gift-this-product/give-product-as-present.php',
	array(
		'product' => $product,
		'icon'    => $icon,
	),
	'',
	trailingslashit( YITH_YWGC_TEMPLATES_DIR )
);
