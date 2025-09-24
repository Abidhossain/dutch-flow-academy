<?php
/**
 * Cart variant (variation product) select
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/common/cart-item-variant-select.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($product) || !isset($cart_item)) {
    return;
}

$current_variant_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : '';

if (!empty($product['variants'])) { ?>
    <div class="cuw-cart-item-variants"
         data-item_key="<?php echo !empty($cart_item_key) ? esc_attr($cart_item_key) : ''; ?>" style="margin-top: 8px;">
        <select class="variant-select" name="variation_id" style="width: 100%;">
            <?php foreach ($product['variants'] as $variant) { ?>
                <option value="<?php echo esc_attr($variant['id']); ?>"
                    <?php echo ($variant['id'] == $current_variant_id) ? 'selected' : ''; ?>>
                    <?php echo esc_html($variant['info']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <?php
}
