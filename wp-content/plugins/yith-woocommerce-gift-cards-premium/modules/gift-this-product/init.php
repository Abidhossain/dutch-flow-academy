<?php
/**
 * Gift this product module init.
 *
 * @package YITH\GiftCards\Modules\GiftThisProduct
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/class-yith-ywgc-gift-this-product-module.php';

return YITH_YWGC_Gift_This_Product_Module::get_instance();
