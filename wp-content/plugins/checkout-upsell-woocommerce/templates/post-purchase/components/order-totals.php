<?php
/**
 * Order totals
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/post-purchase/components/order-totals.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer) || !function_exists('wc_price')) return;

$price = $offer['product']['price'] ?? 0;
$tax = $offer['product']['tax'] ?? 0;
$quantity = !empty($offer['product']['fixed_qty']) ? $offer['product']['fixed_qty'] : 1;
$subtotal = $price * $quantity;
$discount = $offer['discount']['text'] ?? '';
$order_total = $offer['order']['total'] ?? 0;
$hide_section = empty($offer['template']['order_totals']['enabled']);
$hide_table = !empty($offer['product']['is_variable']) && empty($offer['product']['default_variant']);
?>

<div class="cuw-ppu-order-totals"
     style="<?php echo $hide_section ? 'display: none;' : ''; ?>"
     data-qty="<?php echo esc_attr($quantity); ?>"
     data-price="<?php echo esc_attr($price); ?>"
     data-tax="<?php echo esc_attr($tax); ?>"
     data-order_total="<?php echo esc_attr($order_total); ?>">
    <table class="cuw-ppu-order-totals-table" style="width: 100%; <?php echo $hide_table ? 'display: none;' : ''; ?>">
        <tbody>
        <tr>
            <th><?php echo esc_html__('Subtotal', 'checkout-upsell-woocommerce'); ?></th>
            <td class="cuw-ppu-subtotal" style="text-align: end;"><?php echo wp_kses_post(wc_price($subtotal)); ?></td>
        </tr>
        <tr>
            <th><?php echo esc_html__('Discount', 'checkout-upsell-woocommerce'); ?></th>
            <td class="cuw-ppu-discount" style="text-align: end;"><?php echo wp_kses_post($discount); ?></td>
        </tr>
        <tr style="<?php if (empty($tax)) echo 'display: none;' ?>">
            <th><?php echo esc_html__('Tax', 'checkout-upsell-woocommerce'); ?></th>
            <td class="cuw-ppu-tax" style="text-align: end;"><?php echo wp_kses_post(wc_price($tax)); ?></td>
        </tr>
        <tr style="border-top: 1px solid #E8EAED;">
            <th><?php echo esc_html__('Order Total', 'checkout-upsell-woocommerce'); ?></th>
            <td class="cuw-ppu-total" style="text-align: end;">
                <?php echo wp_kses_post(wc_price($order_total + $subtotal + $tax)); ?></td>
        </tr>
        </tbody>
    </table>
</div>
