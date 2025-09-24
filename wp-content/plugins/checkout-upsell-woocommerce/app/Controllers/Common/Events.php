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

namespace CUW\App\Controllers\Common;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;

class Events extends Controller
{
    /**
     * To load custom events
     */
    public static function add()
    {
        add_filter('cuw_offer_template_product_quantity', [__CLASS__, 'loadQuantityInput'], 10, 2);
        add_filter('cuw_offer_template_product_variants', [__CLASS__, 'loadVariantSelect'], 10, 3);
        add_filter('cuw_product_template_quantity', [__CLASS__, 'loadProductQuantityInput'], 10, 3);
        add_filter('cuw_product_template_variants', [__CLASS__, 'loadProductVariantSelect'], 10, 3);
    }

    /**
     * To load custom quantity input on offer template
     *
     * @hooked cuw_offer_template_product_quantity
     */
    public static function loadQuantityInput($html, $offer)
    {
        return self::app()->template('offer/quantity-input', ['offer' => $offer], false);
    }

    /**
     * To load variant select input if the product is variable
     *
     * @hooked cuw_offer_template_product_variants
     */
    public static function loadVariantSelect($html, $offer, $args = [])
    {
        $template_name = self::app()->config->getSetting('variant_select_template');
        return self::app()->template('offer/' . $template_name, ['offer' => $offer, 'args' => $args], false);
    }

    /**
     * To load custom quantity input on product template
     *
     * @hooked cuw_product_template_quantity
     */
    public static function loadProductQuantityInput($html, $product, $attributes)
    {
        return self::app()->template('products/quantity-input', ['product' => $product, 'attributes' => $attributes], false);
    }

    /**
     * To load variant select input if the product is variable
     *
     * @hooked cuw_product_template_variants
     */
    public static function loadProductVariantSelect($html, $product, $args = [])
    {
        $template_name = self::app()->config->getSetting('variant_select_template');
        return self::app()->template('products/' . $template_name, ['product' => $product, 'args' => $args], false);
    }
}