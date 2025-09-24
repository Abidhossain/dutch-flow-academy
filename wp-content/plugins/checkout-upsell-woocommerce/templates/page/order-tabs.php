<?php
/**
 * Order-received page tabs
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/page/order-tabs.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($main_order) || !isset($offer_order) || !isset($current_page)) {
    return;
}
?>

<div class="woocommerce-tabs wc-tabs-wrapper" id="cuw-order-tabs" style="padding: 1.5rem 0;">
    <ul class="tabs wc-tabs" role="tablist" style="width: 100%; margin: 0;">
        <li class="main_order_tab <?php if ($current_page == 'main_order') echo 'active' ?>" id="tab-main-order"
            role="tab" aria-controls="tab-main_order">
            <a href="<?php echo esc_url($main_order->get_checkout_order_received_url()); ?>">
                <?php echo '#' . esc_html($main_order->get_order_number()) . ' ' . esc_html__("Main order", 'checkout-upsell-woocommerce'); ?>
            </a>
        </li>
        <li class="offer_order_tab <?php if ($current_page == 'offer_order') echo 'active' ?>" id="tab-offer-order"
            role="tab" aria-controls="tab-offer_order">
            <a href="<?php echo esc_url($offer_order->get_checkout_order_received_url()); ?>">
                <?php echo '#' . esc_html($offer_order->get_order_number()) . ' ' . esc_html__("Offer order", 'checkout-upsell-woocommerce'); ?>
            </a>
        </li>
    </ul>
</div>