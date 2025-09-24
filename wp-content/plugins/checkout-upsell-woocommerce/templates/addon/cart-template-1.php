<?php
/**
 * Cart addon template 1
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/addon/cart-template-1.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($data) || !isset($products) || !isset($campaign)) {
    return;
}

$heading = !empty($data['template']['title']) ? $data['template']['title'] : __('Add-Ons:', 'checkout-upsell-woocommerce');
$heading = apply_filters('cuw_cart_addons_heading', $heading);
?>
<section class="cuw-cart-addon-products cuw-template" data-campaign_id="<?php echo esc_attr($campaign['id']); ?>"
         style="margin-top: 8px; <?php echo esc_attr($data['styles']['template']); ?>">
    <?php if (!empty($heading)) { ?>
        <p class="cuw-heading cuw-template-title"
           style="margin: 0; text-align: justify; <?php echo esc_attr($data['styles']['title']); ?>"><?php echo wp_kses_post($heading); ?></p>
    <?php } ?>
    <?php if (!empty($campaign) && !empty($products)) { ?>
        <div class="cuw-cart-addon-products cuw-products cuw-mobile-responsive"
             data-campaign_id="<?php echo esc_attr($campaign['id']); ?>"
             data-main_item_key="<?php echo !empty($cart_item_key) ? esc_attr($cart_item_key) : ''; ?>"
             data-quantity="1">
            <?php foreach ($products as $key => $product): ?>
                <?php
                $regular_price = !empty($product['default_variant']) ? $product['default_variant']['regular_price'] : $product['regular_price'];
                $price = !empty($product['default_variant']) ? $product['default_variant']['price'] : $product['price'];
                ?>
                <div class="cuw-product cuw-product-row" style="margin-top: 8px;"
                     data-id="<?php echo esc_attr($product['id']); ?>"
                     data-variant_id="<?php echo isset($product['variants']) ? esc_attr($product['default_variant']['id'] ?? current($product['variants'])['id']) : ''; ?>"
                     data-item_key="<?php echo isset($product['cart_item_key']) ? esc_attr($product['cart_item_key']) : ''; ?>">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <div class="cuw-product-actions" style="display: flex; align-items: center; margin-top: 4px;">
                            <input type="checkbox" class="cuw-addon-checkbox"
                                   style="margin: 0;" <?php echo !empty($product['cart_item_key']) ? 'checked' : ''; ?>>
                        </div>
                        <div style="width: 100%; display: flex; flex-wrap: wrap; align-items: center; gap: 4px;">
                            <div class="cuw-product-title"
                                 style="text-align: <?php echo !empty($data['is_rtl']) ? 'right' : 'left' ?>;">
                                <?php echo wp_kses_post($product['title']); ?>
                            </div>
                            <?php if (!empty($product['price_html'])): ?>
                                <div class="cuw-product-price">
                                    <?php echo wp_kses_post($product['price_html']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php } ?>
</section>