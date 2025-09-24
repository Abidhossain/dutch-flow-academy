<?php
defined('ABSPATH') || exit;
if (!isset($action)) {
    return;
}

$campaign_type = isset($campaign_type) ? $campaign_type : 'noc';
$display_locations = \CUW\App\Modules\Campaigns\NOC::getDisplayLocations();
?>

<?php if ($action == 'cuw_campaign_contents' && isset($campaign)): ?>
    <?php
    CUW()->view('Admin/Components/Accordion', [
        'id' => 'coupon',
        'title' => __('Coupon Settings', 'checkout-upsell-woocommerce'),
        'icon' => 'next-order-coupon',
        'view' => 'Admin/Campaign/Components/Coupon',
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
            'display_location_text' => __('Display location on Thank you page', 'checkout-upsell-woocommerce'),
        ],
    ]);
    ?>
<?php endif; ?>
