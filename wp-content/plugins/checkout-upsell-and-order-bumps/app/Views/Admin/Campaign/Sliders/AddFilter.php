<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$campaign_type = isset($campaign['type']) ? $campaign['type'] : '';
$campaign_type = !empty($campaign_type) ? $campaign_type : '';
?>

<div class="cuw-slider-header d-flex justify-content-between align-items-center mt-3">
    <h4 class="cuw-slider-title"><?php esc_html_e("Filter", 'checkout-upsell-and-order-bumps'); ?></h4>
    <div>
        <button type="button" id="cuw-filter-close" class="btn btn-outline-secondary" style="gap: 6px;">
            <i class="cuw-icon-close-circle inherit-color"></i>
            <?php esc_html_e("Close", 'checkout-upsell-and-order-bumps'); ?>
        </button>
    </div>
</div>
<div>
    <div class="input-group mt-3 d-flex flex-column" style="gap: 8px;">
        <?php CUW()->view('Admin/Campaign/Filters/List', ['campaign_type' => $campaign_type]); ?>
    </div>
    <div id="filter-section"></div>
    <div class="d-flex justify-content-end mt-3">
        <button type="button" id="slider-filter-add" class="btn btn-primary" disabled>
            <i class="cuw-icon-add-circle inherit-color mx-1"></i>
            <?php esc_html_e("Save", 'checkout-upsell-and-order-bumps'); ?>
        </button>
    </div>
</div>