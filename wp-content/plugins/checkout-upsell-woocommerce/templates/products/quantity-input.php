<?php
/**
 * Product quantity input or text
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/products/quantity-input.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */
defined('ABSPATH') || exit;
if (!isset($product)) return;

$stock_quantity = !empty($product['stock_qty']) ? $product['stock_qty'] : '';
?>

<div class="quantity-input">
    <span class="cuw-plus"></span>
    <input type="number" class="cuw-qty"
           name="<?php echo !empty($attributes['name']) ? esc_attr($attributes['name']) : 'quantity'; ?>" value="1"
           min="1" step="1" max="<?php echo esc_attr($stock_quantity); ?>" placeholder="1" style="margin: 0;">
    <span class="cuw-minus" style="opacity: 0.6;"></span>
</div>
