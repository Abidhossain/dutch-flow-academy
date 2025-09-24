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

namespace CUW\App\Controllers\Store;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Controllers\Store\Blocks\Offers;
use CUW\App\Helpers\WC;

class Blocks extends Controller
{
    /**
     * Register endpoint data.
     */
    public static function register()
    {
        if (!WC::requiredVersion('8.3')) {
            return;
        }

        if (function_exists('woocommerce_store_api_register_endpoint_data')) {
            if (class_exists('\Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema')) {
                if (interface_exists('Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface')) {
                    $offers = new Blocks\Offers();

                    // register endpoint data
                    woocommerce_store_api_register_endpoint_data([
                        'endpoint' => \Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema::IDENTIFIER,
                        'namespace' => str_replace('-', '_', $offers->get_name()),
                        'data_callback' => [$offers, 'extendData'],
                        'schema_callback' => [$offers, 'extendDataSchema'],
                        'schema_type' => ARRAY_A,
                    ]);

                    // to load offer blocks
                    add_action('woocommerce_blocks_cart_block_registration', [__CLASS__, 'registerCartBlocks']);
                    add_action('woocommerce_blocks_checkout_block_registration', [__CLASS__, 'registerCheckoutBlocks']);
                }
            }
        }
    }

    /**
     * Register cart blocks.
     */
    public static function registerCartBlocks($integration_registry)
    {
        $integration_registry->register(new Offers());
    }

    /**
     * Register checkout blocks.
     */
    public static function registerCheckoutBlocks($integration_registry)
    {
        $integration_registry->register(new Offers());
    }
}