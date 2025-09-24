<?php
defined('ABSPATH') || exit;
if (!isset($post_id)) {
    return;
}

$created_campaign_id = get_post_meta($post_id, 'cuw_created_campaign_id', true);
$created_order_id = get_post_meta($post_id, 'cuw_created_order_id', true);
$created_for = get_post_meta($post_id, 'cuw_created_for', true);
$created_order = CUW\App\Helpers\WC::getOrder($created_order_id);
$campaign = \CUW\App\Models\Campaign::get($created_campaign_id);
if (is_numeric($created_for)) {
    $created_for = CUW()->wp->getUserName($created_for);
}

$used_order_id = get_post_meta($post_id, 'cuw_used_order_id', true);
$used_order = CUW\App\Helpers\WC::getOrder($used_order_id);
$used_by = get_post_meta($post_id, 'cuw_used_by', true);
if (is_numeric($used_by)) {
    $used_by = CUW()->wp->getUserName($used_by);
}
?>

<div style="padding: 6px 2px;">
    <?php esc_html_e('Created order', 'checkout-upsell-woocommerce'); ?>:
    <?php if ($created_order) { ?>
        <a target="_blank" href="<?php echo esc_url($created_order->get_edit_order_url()); ?>"
           style="text-decoration: none; font-weight: bold;">
            <?php echo esc_html('#' . $created_order->get_order_number() . ' ' . $created_order->get_formatted_billing_full_name()); ?>
        </a>
    <?php } else {
        echo '-';
    } ?>
</div>
<div style="padding: 6px 2px;">
    <?php esc_html_e('Created via', 'checkout-upsell-woocommerce'); ?>:
    <?php if ($campaign) { ?>
        <a target="_blank" href="<?php echo esc_url(\CUW\App\Helpers\Campaign::getEditUrl($campaign)); ?>"
           style="text-decoration: none; font-weight: bold;">
            <?php echo esc_html(\CUW\App\Helpers\Campaign::getTitle($campaign, true)); ?>
        </a>
    <?php } else {
        echo '-';
    } ?>
</div>
<div style="padding: 6px 2px;">
    <?php esc_html_e('Created for', 'checkout-upsell-woocommerce'); ?>:
    <?php if ($created_for) { ?>
        <?php echo '<span style="font-weight: bold;">' . esc_html($created_for) . '</span>'; ?>
    <?php } else {
        echo '-';
    } ?>
</div>

<div style="padding: 6px 2px;">
    <?php esc_html_e('Used order', 'checkout-upsell-woocommerce'); ?>:
    <?php if ($used_order) { ?>
        <a target="_blank" href="<?php echo esc_url($used_order->get_edit_order_url()); ?>"
           style="text-decoration: none; font-weight: bold;">
            <?php echo esc_html('#' . $used_order->get_order_number() . ' ' . $used_order->get_formatted_billing_full_name()); ?>
        </a>
    <?php } else {
        echo '-';
    } ?>
</div>
<div style="padding: 6px 8px 4px 2px;">
    <?php esc_html_e('Used by', 'checkout-upsell-woocommerce'); ?>:
    <?php if ($used_by) { ?>
        <?php echo '<span style="font-weight: bold;">' . esc_html($used_by) . '</span>'; ?>
    <?php } else {
        echo '-';
    } ?>
</div>
