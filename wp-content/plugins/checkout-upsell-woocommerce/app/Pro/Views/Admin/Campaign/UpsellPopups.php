<?php
defined('ABSPATH') || exit;
if (!isset($action)) {
    return;
}
?>

<?php if ($action == 'campaign_edit' && isset($campaign)): ?>
    <?php
    CUW()->view('Admin/Components/Accordion', [
        'id' => 'select_triggers',
        'title' => __('Triggers', 'checkout-upsell-woocommerce'),
        'icon' => 'options-2',
        'view' => 'Pro/Admin/Campaign/Components/SelectTriggers',
        'data' => ['campaign' => $campaign],
    ]);

    CUW()->view('Admin/Components/Accordion', [
        'id' => 'use_products',
        'title' => __('Products', 'checkout-upsell-woocommerce'),
        'icon' => 'product',
        'view' => 'Admin/Campaign/Components/Products',
        'data' => [
            'campaign' => $campaign,
            'use_options' => ['related', 'cross_sell', 'upsell', 'specific', 'engine'],
            'default_use' => 'related',
            'allow_bundle' => false,
            'products_text' => __('Upsell', 'checkout-upsell-woocommerce'),
        ],
    ]);

    CUW()->view('Admin/Components/Accordion', [
        'id' => 'discount',
        'title' => __('Discount', 'checkout-upsell-woocommerce'),
        'icon' => 'discount',
        'view' => 'Admin/Campaign/Components/Discount',
        'data' => ['campaign' => $campaign],
    ]);

    CUW()->view('Admin/Components/Accordion', [
        'id' => 'template',
        'title' => __('Template', 'checkout-upsell-woocommerce'),
        'icon' => 'campaigns',
        'view' => 'Admin/Campaign/Components/Template',
        'data' => ['campaign' => $campaign],
    ]);
    ?>
<?php endif; ?>
