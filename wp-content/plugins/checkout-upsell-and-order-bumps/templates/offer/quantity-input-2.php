<?php
/**
 * Offer quantity input or text - 2
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/offer/quantity-input-2.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer)) return;

if (!empty($offer['product']['fixed_qty'])) {
    echo esc_html__('Quantity', 'checkout-upsell-and-order-bumps') . ': ' . esc_html($offer['product']['fixed_qty']);
} else {
    $stock_quantity = !empty($offer['product']['stock_qty']) ? $offer['product']['stock_qty'] : '';
    ?>
    <div class="quantity-input quantity-input-2" style="">
        <span class="cuw-minus" style="opacity: 0.6;"></span>
        <input type="number" class="cuw-qty" name="quantity"
               value="<?php echo esc_attr($offer['product']['qty'] ?? 1); ?>" min="1" step="1"
               max="<?php echo esc_attr($stock_quantity) ?>" placeholder="1" style="margin: 0;">
        <span class="cuw-plus"></span>
    </div>
    <?php
}