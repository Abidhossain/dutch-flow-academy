<?php
/**
 * Cart addon template 2
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/addon/cart-template-2.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($data) || !isset($products) || !isset($campaign)) {
    return;
}

$heading = !empty($data['template']['title']) ? $data['template']['title'] : __('Show Add-Ons', 'checkout-upsell-woocommerce');
$heading = apply_filters('cuw_cart_addons_heading', $heading);
$product_added = isset($product_added) ? $product_added : true;
?>
<section class="cuw-cart-addon-products cuw-template cuw-mobile-responsive"
         data-campaign_id="<?php echo esc_attr($campaign['id']); ?>"
         style="max-width: 320px; margin-top: 8px; border-radius: 4px; <?php echo esc_attr($data['styles']['template']); ?>">
    <?php if (!empty($heading)) { ?>
        <div class="cuw-toggle-addons"
             style="display: flex; align-items: center; justify-content: space-between; font-weight: bold; cursor: pointer; <?php echo esc_attr($data['styles']['title']); ?>">
            <p class="cuw-heading cuw-template-title"
               style="line-height: 1; margin: 0; font-size: inherit; color: inherit; <?php echo ($product_added) ? 'opacity: 0.6;' : 'opacity: 1;'; ?>"><?php echo wp_kses_post($heading); ?></p>
            <div>
                <svg class="cuw-addon-arrow-up" width="18px" height="18px" viewBox="0 0 1024 1024"
                     xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                     style="display: <?php echo ($product_added) ? 'block;' : 'none;'; ?>">
                    <path d="M903.232 768l56.768-50.432L512 256l-448 461.568 56.768 50.432L512 364.928z"/>
                </svg>
                <svg class="cuw-addon-arrow-down" width="18px" height="18px" viewBox="0 0 1024 1024"
                     xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                     style="display: <?php echo ($product_added) ? 'none;' : 'block;'; ?>">
                    <path d="M903.232 256l56.768 50.432L512 768 64 306.432 120.768 256 512 659.072z"/>
                </svg>
            </div>
        </div>
    <?php } ?>

    <?php if (!empty($campaign) && !empty($products)) { ?>
        <div class="cuw-products" data-campaign_id="<?php echo esc_attr($campaign['id']); ?>"
             data-main_item_key="<?php echo !empty($cart_item_key) ? esc_attr($cart_item_key) : ''; ?>"
             data-quantity="1"
             style="margin-top: 12px; display: <?php echo ($product_added) ? 'block;' : 'none;'; ?>">
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($products as $key => $product): ?>
                    <?php
                    $regular_price = !empty($product['default_variant']) ? $product['default_variant']['regular_price'] : $product['regular_price'];
                    $price = !empty($product['default_variant']) ? $product['default_variant']['price'] : $product['price'];
                    ?>
                    <div class="cuw-product cuw-product-row"
                         data-id="<?php echo esc_attr($product['id']); ?>"
                         data-variant_id="<?php echo isset($product['variants']) ? esc_attr($product['default_variant']['id'] ?? current($product['variants'])['id']) : ''; ?>"
                         data-item_key="<?php echo isset($product['cart_item_key']) ? esc_attr($product['cart_item_key']) : ''; ?>">
                        <div style="display: flex; gap: 8px;">
                            <div class="cuw-product-actions"
                                 style="display: flex; align-items: start; margin-top: 4px;">
                                <input type="checkbox" class="cuw-addon-checkbox"
                                       style="margin: 0;" <?php echo !empty($product['cart_item_key']) ? 'checked' : ''; ?>>
                            </div>
                            <div style="width: 100%; display: flex; flex-direction: column; align-items: start; flex-wrap: wrap; gap: 4px;">
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
        </div>
    <?php } ?>
</section>