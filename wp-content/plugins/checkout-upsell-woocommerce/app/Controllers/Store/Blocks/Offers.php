<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Controllers\Store\Blocks;

defined('ABSPATH') || exit;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use CUW\App\Helpers\WC;
use CUW\App\Modules\Campaigns\CartUpsells;
use CUW\App\Modules\Campaigns\CheckoutUpsells;

class Offers implements IntegrationInterface
{
    /**
     * The name of the integration.
     *
     * @return string
     */
    public function get_name()
    {
        return 'cuw-upsell-offers';
    }

    /**
     * When called invokes any initialization/setup for the integration.
     */
    public function initialize()
    {
        $script_asset_path = CUW_PLUGIN_PATH . 'blocks/build/index.asset.php';
        $script_asset = file_exists($script_asset_path)
            ? require $script_asset_path
            : [
                'dependencies' => [],
                'version' => CUW_VERSION,
            ];

        wp_register_script(
            $this->get_name(),
            plugin_dir_url(CUW_PLUGIN_FILE) . 'blocks/build/index.js',
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );
    }

    /**
     * Extend data.
     *
     * @return array
     */
    public function extendData()
    {
        $extend_data = [];

        $is_cart = WC::is('cart', true);
        $is_checkout = WC::is('checkout', true);
        $is_store_api = WC::isStoreApi();
        if ($is_store_api) {
            add_filter('cuw_stop_cart_upsells_offer_count_increasing', '__return_true', 1000);
            add_filter('cuw_stop_checkout_upsells_offer_count_increasing', '__return_true', 1000);
        }

        if ($is_cart || $is_store_api) {
            $extend_data['cart_upsells'] = [
                'order_meta' => CartUpsells::getOffersHtml('blocks/woocommerce/cart/order_meta'),
                'coupon' => CartUpsells::getOffersHtml('blocks/woocommerce/cart/coupon'),
                'shipping' => CartUpsells::getOffersHtml('blocks/woocommerce/cart/shipping'),
            ];
        }
        if ($is_checkout || $is_store_api) {
            $extend_data['checkout_upsells'] = [
                'order_meta' => CheckoutUpsells::getOffersHtml('blocks/woocommerce/checkout/order_meta'),
                'coupon' => CheckoutUpsells::getOffersHtml('blocks/woocommerce/checkout/coupon'),
                'shipping' => CheckoutUpsells::getOffersHtml('blocks/woocommerce/checkout/shipping'),
            ];
        }
        return $extend_data;
    }

    /**
     * Extend data schema.
     *
     * @return array
     */
    public function extendDataSchema()
    {
        return [
            'properties' => [
                'upsell_offers' => [
                    'description' => __('Upsell offers', 'checkout-upsell-woocommerce'),
                    'type' => 'object',
                    'context' => [
                        'view', //'edit'
                    ],
                    'readonly' => true,
                ],
            ]
        ];
    }

    /**
     * Returns an array of script handles to enqueue in the frontend context.
     *
     * @return string[]
     */
    public function get_script_handles()
    {
        return [$this->get_name()];
    }

    /**
     * Returns an array of script handles to enqueue in the editor context.
     *
     * @return string[]
     */
    public function get_editor_script_handles()
    {
        return [];
    }

    /**
     * An array of key, value pairs of data made available to the block on the client side.
     *
     * @return array
     */
    public function get_script_data()
    {
        return apply_filters('cuw_upsell_offers_block_script_data', []);
    }
}