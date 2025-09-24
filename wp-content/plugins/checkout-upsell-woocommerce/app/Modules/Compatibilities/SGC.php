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

namespace CUW\App\Modules\Compatibilities;

defined('ABSPATH') || exit;

class SGC extends Base
{
    /**
     * To run compatibility script.
     */
    public function run()
    {
        add_filter('cuw_get_product_data', [__CLASS__, 'getImage'], 10, 3);
    }

    /**
     * To replace product image.
     */
    public static function getImage($data, $product, $args)
    {
        if (is_object($product) && method_exists($product, 'get_image_id')) {
            if (function_exists('wp_get_attachment_image_url') && $product->get_image_id()) {
                $data['image'] = '<img src="' . wp_get_attachment_image_url($product->get_image_id()) . '" alt=""/>';
            }
        }
        return $data;
    }
}