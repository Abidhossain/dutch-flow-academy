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

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

class Product
{
    /**
     * Get product data
     *
     * @param \WC_Product|int $product_or_id
     * @param array $args
     * @retun array|false
     */
    public static function getData($product_or_id, $args = [])
    {
        $data = [];
        $product = WC::getProduct($product_or_id);
        if (!is_object($product)) {
            return false;
        }

        $args = wp_parse_args($args, [
            'quantity' => 1,
            'discount' => [],
            'to_display' => false,
            'is_preview' => false,
            'display_in' => 'shop',
            'format_title' => false,
            'format_image' => false,
            'include_variants' => false,
            'filter_purchasable' => false,
            'load_tax' => false,
            'check_cart_item_existing_qty' => true,
        ]);

        $check_cart_item_existing_qty = $args['is_preview'] ? false : $args['check_cart_item_existing_qty'];
        if ($args['filter_purchasable'] && !WC::isPurchasableProduct($product, $args['quantity'], $check_cart_item_existing_qty)) {
            return false;
        }

        $data['id'] = $product->get_id();
        $data['type'] = $product->get_type();
        $data['title'] = self::getTitle($product);
        $data['parent_id'] = $product->get_parent_id();

        if ($args['to_display']) {
            $data['url'] = $product->get_permalink();
            $image_size_type = apply_filters('cuw_product_image_size', 'medium');
            $image_attributes = apply_filters('cuw_product_image_attributes', [], $product);
            $data['image'] = $product->get_image($image_size_type, $image_attributes);
            $data['regular_price'] = WC::getPriceToDisplay($product, self::getPrice($product, 'regular_price'), $args['quantity'], $args['display_in']);
            $data['price'] = WC::getPriceToDisplay($product, '', $args['quantity'], $args['display_in']);

            if ($args['format_title']) {
                $data['title'] = self::formatTitle($product, $args['quantity']);
            }
            if ($args['format_image']) {
                $data['image'] = self::formatImage($product, 0, $image_size_type, $image_attributes);
            }
        } else {
            $data['regular_price'] = $product->get_regular_price();
            $data['price'] = $product->get_price();
        }

        $data['regular_price'] = apply_filters('cuw_product_regular_price', $data['regular_price'], $product, $args);
        $data['price'] = apply_filters('cuw_product_sale_price', $data['price'], $product, $args);

        if (!empty($args['discount']) && isset($args['discount']['type']) && $args['discount']['type'] != 'no_discount') {
            $data['regular_price'] = Discount::getProductPrice($product, $args['discount']);
            $data['price'] = Discount::getPrice($product, $args['discount'], $data['regular_price']);
            if ($args['to_display']) {
                if ($args['discount']['type'] == 'fixed_price' && !empty($args['discount']['is_bundle'])) {
                    $data['price_html'] = WC::formatPrice(WC::getPriceToDisplay($product, $data['regular_price'], $args['quantity'], $args['display_in']));
                } else {
                    $regular_price = $data['regular_price'] * $args['quantity'];
                    $price = $data['price'] * $args['quantity'];
                    $data['price_html'] = Discount::getPriceHtml($product, $args['discount'], $args['display_in'], $args['display_in'], $regular_price, $price);
                }
                $data['regular_price'] = WC::getPriceToDisplay($product, $data['regular_price'], $args['quantity'], $args['display_in']);
                $data['price'] = WC::getPriceToDisplay($product, $data['price'], $args['quantity'], $args['display_in']);
            }
        } else {
            if ($args['to_display']) {
                $data['price_html'] = self::getPriceHtml($product, $args['display_in'], $data['regular_price'], $data['price']);
            }
        }
        if (in_array($product->get_type(), ['subscription', 'variable-subscription', 'subscription_variation'])) {
            $data['regular_price'] = (float)$data['regular_price'] + Product::getPrice($product, 'sign_up_fee');
            $data['price'] = (float)$data['price'] + Product::getPrice($product, 'sign_up_fee');
        }

        $data['is_sale'] = ($data['regular_price'] > $data['price']);
        if ($data['is_sale']) {
            $data['discount'] = $data['regular_price'] - $data['price'];
            $data['discount_amount'] = WC::formatPriceRaw($data['discount'], ['trim_zeros' => true]);
            $data['discount_percentage'] = round(($data['regular_price'] - $data['price']) / $data['regular_price'] * 100, 2);
        }

        if (!empty($args['load_tax'])) {
            $data['tax'] = self::getTax($product, $data['price'], $args['display_in']);
        }

        if (!WC::isBackordersAllowedProduct($product)) {
            $data['stock_qty'] = (string)WC::getProductStockQty($product);
        } else {
            $data['stock_qty'] = '';
        }

        $data['is_variable'] = false;
        if ($args['include_variants'] && WC::isVariableProduct($product)) {
            $data['is_variable'] = true;
            $data['variants'] = [];
            $data['available_attributes'] = WC::getVariationAttributes($product);
            $variant_ids = WC::getProductChildrenIds($product);
            foreach ($variant_ids as $variant_id) {
                $variation = WC::getProduct($variant_id);
                if (is_object($variation) && WC::isVariationVisible($variation) && $variation_data = self::getData($variation, $args)) {
                    $info = wp_strip_all_tags(WC::getFormattedVariationInfo($variation));
                    $variation_data['info'] = !empty($info) ? $info : '';
                    $variation_data['attributes'] = WC::getVariationAttributes($variation);
                    $variation_data['is_active'] = WC::isVariationActive($variation);
                    unset($variation_data['is_variable']);
                    $data['variants'][] = $variation_data;
                }
            }

            if (!empty($data['variants'])) {
                $prices = array_column($data['variants'], 'price');
                $min_price = min(array_column($data['variants'], 'price'));
                $max_price = max(array_column($data['variants'], 'price'));
                $min_price_variant_key = array_search($min_price, $prices);
                $max_price_variant_key = array_search($max_price, $prices);
                $min_price_variant = $data['variants'][$min_price_variant_key];
                $max_price_variant = $data['variants'][$max_price_variant_key];

                $data['regular_price'] = $min_price_variant['regular_price'];
                $data['price'] = $min_price_variant['price'];
                $data['is_sale'] = $min_price_variant['is_sale'];
                if ($data['is_sale']) {
                    $data['discount'] = $min_price_variant['discount'];
                    $data['discount_amount'] = $min_price_variant['discount_amount'];
                }

                if (Config::getSetting('variant_select_template') != 'attributes-select') {
                    $default_variant = $min_price_variant;
                    if ($default_variant_id = WC::getDefaultVariationId($product)) {
                        foreach ($data['variants'] as $variant) {
                            if ($variant['id'] == $default_variant_id) {
                                $default_variant = $variant;
                                break;
                            }
                        }
                    }
                    $data['default_variant'] = apply_filters('cuw_default_product_variant', $default_variant, $data['variants'], $product);
                }

                if (!empty($args['discount']) && $args['discount']['type'] == 'fixed_price' && !empty($args['discount']['is_bundle'])) {
                    $min_regular_price = min(array_column($data['variants'], 'regular_price'));
                    $max_regular_price = max(array_column($data['variants'], 'regular_price'));
                    if ($min_regular_price !== $max_regular_price) {
                        $data['price_html'] = WC::formatPriceRange($min_regular_price, $max_regular_price);
                    } else {
                        $data['price_html'] = WC::formatPrice($data['regular_price']);
                    }
                } else {
                    if ($min_price !== $max_price) {
                        $data['price_html'] = WC::formatPriceRange($min_price, $max_price);
                    } elseif ($data['is_sale'] && $min_price_variant['regular_price'] === $max_price_variant['regular_price']) {
                        $data['price_html'] = WC::formatSalePrice($max_price_variant['regular_price'], $min_price);
                    } else {
                        $data['price_html'] = WC::formatPrice($data['price']);
                    }
                }
                if ($args['display_in'] == 'shop') {
                    $data['price_html'] .= $product->get_price_suffix($min_price);
                }

                if (!empty($data['default_variant']['stock_qty'])) {
                    $data['stock_qty'] = $data['default_variant']['stock_qty'];
                }

            } elseif ($args['filter_purchasable']) {
                return false;
            }
        }

        return apply_filters('cuw_get_product_data', $data, $product, $args);
    }

    /**
     * Get product IDs.
     *
     * @param \WC_Product|int $product_or_id
     * @param string $from
     * @return array
     */
    public static function getIds($product_or_id, $from = '')
    {
        $product = WC::getProduct($product_or_id);
        if (!empty($product)) {
            if ($from == 'related' && function_exists('wc_get_related_products')) {
                return wc_get_related_products($product->get_id());
            } elseif ($from == 'cross_sell' && method_exists($product, 'get_cross_sell_ids')) {
                if ($product->get_parent_id() && $parent_product = WC::getProduct($product->get_parent_id())) {
                    return $parent_product->get_cross_sell_ids();
                }
                return $product->get_cross_sell_ids();
            } elseif ($from == 'upsell' && method_exists($product, 'get_upsell_ids')) {
                if ($product->get_parent_id() && $parent_product = WC::getProduct($product->get_parent_id())) {
                    return $parent_product->get_upsell_ids();
                }
                return $product->get_upsell_ids();
            }
        }
        return [];
    }

    /**
     * Get product price.
     *
     * @param \WC_Product $product
     * @param string|null $from
     * @return int|float
     */
    public static function getPrice($product, $from = null)
    {
        $price = null;
        switch ($from) {
            case 'regular_price':
                $regular_price = $product->get_regular_price();
                if ($regular_price !== '' && $regular_price > 0) {
                    $price = $regular_price;
                }
                break;
            case 'sale_price':
                $sale_price = $product->get_sale_price();
                if ($sale_price !== '' && $sale_price > 0) {
                    $price = $sale_price;
                }
                break;
            case 'sign_up_fee':
                $sign_up_fee = $product->get_sign_up_fee();
                if ($sign_up_fee !== ''&& $sign_up_fee > 0) {
                    $price = $sign_up_fee;
                }
                break;
        }

        if ($price === null) {
            $price = $product->get_price();
        }
        return apply_filters('cuw_product_price', (float)$price, $product, $from);
    }

    /**
     * Get product price html.
     *
     * @param \WC_Product $product
     * @return int|float
     */
    public static function getPriceHtml($product, $tax_based_on = 'shop', $regular_price = null, $price = null)
    {
        if ($regular_price === null || $price === null) {
            $regular_price = $product->get_regular_price();
            $price = $product->get_price();
        }
        if ($regular_price > $price) {
            $price_html = WC::formatSalePrice($regular_price, $price);
        } else {
            $price_html = WC::formatPrice($price);
        }
        if ($tax_based_on == 'shop') {
            $price_html .= $product->get_price_suffix($price);
        }
        return apply_filters('cuw_product_price_html', $price_html, $product, $tax_based_on, $regular_price, $price);
    }

    /**
     * Get product price based on settings.
     *
     * @param $product \WC_Product
     * @return int|float
     */
    public static function getPriceBasedOnConfig($product)
    {
        return self::getPrice($product, Config::getSetting('calculate_discount_from'));
    }

    /**
     * To prepare cart item data.
     *
     * @param array $campaign
     * @param \WC_Product $product
     * @param int|float $quantity
     * @param int $variation_id
     * @param array $variation_attributes
     * @return array|false
     */
    public static function prepareMetaData($campaign, $product, $quantity = 1, $variation_id = 0, $variation_attributes = [])
    {
        $variation = [];
        $product_id = $product->get_id();
        if (WC::isVariableProduct($product) && !empty($variation_id) && $variation_product = WC::getProduct($variation_id)) {
            $variation = !empty($variation_attributes) ? $variation_attributes : self::getVariationAttributes($product, $variation_product);
            $product = $variation_product;
        }

        $discount = $campaign['data']['discount'] ?? [];
        $discount_text = Discount::getText($product, $discount, 'cart');
        $product_price = Discount::getProductPrice($product, $discount);
        $price = Discount::getPrice($product, $discount, $product_price);
        $meta_data = [
            'type' => 'product',
            'price' => $price,
            'product' => [
                'id' => $product_id,
                'qty' => $quantity,
                'price' => $product_price,
                'variation_id' => $variation_id,
                'variation' => $variation,
            ],
            'discount' => [
                'text' => $discount_text,
                'type' => $discount['type'] ?? 'no_discount',
                'value' => $discount['value'] ?? 0,
                'price' => $product_price - $price,
                'is_bundle' => !empty($discount['is_bundle']),
                'bundle_by' => !empty($discount['bundle_by']) ? $discount['bundle_by'] : '',
            ],
            'campaign_id' => $campaign['id'],
            'campaign_type' => $campaign['type'],
        ];
        return apply_filters('cuw_product_item_data', $meta_data, $product, $campaign);
    }

    /**
     * To get variation data.
     *
     * @param \WC_Product_Variable|object $product
     * @param \WC_Product_Variation|object $variation_product
     * @return array
     */
    public static function getVariationAttributes($product, $variation_product)
    {
        $variation = [];
        $product_attributes = WC::getVariationAttributes($product);
        foreach (WC::getProductAttributes($variation_product) as $key => $value) {
            if (empty($value) && !empty($product_attributes[$key])) {
                $value = current($product_attributes[$key]);
            }
            $variation['attribute_' . $key] = $value;
        }
        return apply_filters('cuw_product_variation_attributes', $variation, $variation_product);
    }

    /**
     * Get product title.
     *
     * @param int|\WC_Product $product_or_id
     * @return string
     */
    public static function getTitle($product_or_id)
    {
        $product = WC::getProduct($product_or_id);
        if (!empty($product)) {
            if (is_a($product, '\WC_Product_Variation') && function_exists('wc_get_formatted_variation')) {
                $variation_separator = apply_filters('woocommerce_product_variation_title_attributes_separator', ' - ', $product);
                $variation_attributes = wc_get_formatted_variation($product, true, false);
                return get_the_title($product->get_parent_id()) . $variation_separator . $variation_attributes;
            }
            return $product->get_title();
        }
        return '';
    }

    /**
     * Format product title.
     *
     * @param int|\WC_Product $product_or_id
     * @param int|float $quantity
     * @return string
     */
    public static function formatTitle($product_or_id, $quantity = 1)
    {
        $title = '';
        $product = WC::getProduct($product_or_id);
        if (!empty($product)) {
            $title = self::getTitle($product);
            $title = self::mayLoadWrapper($product, $title);
            if ($quantity > 1) {
                $title .= ' <strong class="product-quantity">&times;&nbsp;' . esc_html($quantity) . '</strong>';
            }
        }
        return $title;
    }

    /**
     * Format image.
     *
     * @param int|\WC_Product $product_or_id
     * @param int $image_id
     * @param string $size
     * @param array $attr
     * @return string
     */
    public static function formatImage($product_or_id, $image_id = 0, $size = 'medium', $attr = [])
    {
        $image = '';
        $product = WC::getProduct($product_or_id);
        if (!empty($product)) {
            if (!empty($image_id) && $image_html = WP::getImage($image_id, $size, false, $attr)) {
                $image = $image_html;
            } else {
                $image = $product->get_image($size, $attr);
            }
            $image = self::mayLoadWrapper($product, $image);
        }
        return $image;
    }

    /**
     * Load extra wrapper.
     *
     * @param \WC_Product $product
     * @param string $content
     * @return string
     */
    private static function mayLoadWrapper($product, $content)
    {
        $attrs = '';
        $show_product_details = Config::getSetting('show_product_details');
        if ($show_product_details == 'in_popup') {
            $attrs = 'class="cuw-modal-product-detail" data-id="' . ($product->get_parent_id() ? $product->get_parent_id() : $product->get_id()) . '"';
        } elseif (in_array($show_product_details, ['in_current_tab', 'in_new_tab'])) {
            $attrs = $product->get_permalink() ? 'href="' . $product->get_permalink() . '"' : '';
            $attrs .= ($show_product_details == 'in_new_tab') ? ' target="_blank"' : '';
        }
        if (!empty($attrs)) {
            $content = '<a ' . $attrs . ' style="text-decoration: none; cursor: pointer;">' . wp_kses_post($content) . '</a>';
        }
        return $content;
    }

    /**
     * To get product tax (for single quantity)
     *
     * @param object|int $object_or_id
     * @param int $product_price
     * @param string $display_in
     * @return float|int
     */
    private static function getTax($object_or_id, $product_price, $display_in = 'cart')
    {
        $tax = 0;
        if (!WC::displayPricesIncludingTax($display_in) && !empty($object_or_id) && !empty($product_price)) {
            $incl_tax = function_exists('wc_prices_include_tax') && wc_prices_include_tax();
            if ($incl_tax) {
                add_filter('woocommerce_prices_include_tax', '__return_false', 1000);
            }
            $product_price_incl_tax = WC::getPriceToDisplay($object_or_id, $product_price, 1, 'incl');
            if ($incl_tax) {
                remove_filter('woocommerce_prices_include_tax', '__return_false', 1000);
            }
            if (!empty($product_price_incl_tax)) {
                $tax = round($product_price_incl_tax - $product_price, 4);
            }
        }
        return $tax;
    }
}