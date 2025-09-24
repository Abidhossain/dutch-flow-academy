<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}
?>
<div>
    <div class="cuw-slider-header d-flex justify-content-between align-items-center mt-3" style="gap:8px;">
        <h4 class="cuw-slider-title"><?php esc_html_e("Choose an offer template", 'checkout-upsell-and-order-bumps'); ?></h4>
        <div class="d-flex" style="gap:8px;">
            <button type="button" id="cuw-offer-template-close" class="btn btn-outline-secondary">
                <i class="cuw-icon-close-circle inherit-color mx-1"></i>
                <?php esc_html_e("Close", 'checkout-upsell-and-order-bumps'); ?>
            </button>
        </div>
    </div>
    <div id="change-template">
        <div id="templates" class="row" style="height: auto; overflow-y: auto;"></div>
    </div>
</div>