<?php
defined('ABSPATH') || exit;
if (!isset($action)) {
    return;
}

$campaign_type = $campaign_type ?? 'double_order';
$display_locations = \CUW\App\Pro\Modules\Campaigns\DoubleOrder::getDisplayLocations();
?>

<?php if ($action == 'cuw_campaign_contents' && isset($campaign)): ?>

    <?php
    CUW()->view('Admin/Components/Accordion', [
        'id' => 'discount',
        'title' => __('Discount', 'checkout-upsell-woocommerce'),
        'icon' => 'discount',
        'view' => 'Pro/Admin/Campaign/Components/Action',
        'data' => ['campaign' => $campaign],
    ]);
    CUW()->view('Admin/Components/Accordion', [
        'id' => 'template',
        'title' => __('Template', 'checkout-upsell-woocommerce'),
        'icon' => 'campaigns',
        'view' => 'Admin/Campaign/Components/Template',
        'data' => [
            'campaign' => $campaign,
            'display_locations' => $display_locations,
            'display_location_text' => __('Display location on Checkout page', 'checkout-upsell-woocommerce'),
        ],
    ]);
    ?>

<?php endif; ?>
