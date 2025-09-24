<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}
?>

<div id="cuw-view-offer">
    <div class="cuw-slider-header d-flex justify-content-between align-items-center">
        <h4 class="cuw-slider-title"><?php esc_html_e("Preview", 'checkout-upsell-woocommerce'); ?></h4>
        <div class="btn-group bg-white border border-gray-light rounded p-1 d-flex" id="cuw-view-device-preview">
            <label class="btn m-0 btn-primary">
                <i class="cuw-icon-desktop inherit-color mx-1"></i>
                <input type="radio" name="preview-device" value="desktop" class="d-none" checked><span class="mx-1"><?php esc_html_e("Desktop", 'checkout-upsell-woocommerce'); ?></span>
            </label>
            <label class="btn m-0">
                <i class="cuw-icon-mobile inherit-color mx-1"></i>
                <input type="radio" name="preview-device" value="mobile" class="d-none"> <span class="mx-1"><?php esc_html_e("Mobile", 'checkout-upsell-woocommerce'); ?></span>
            </label>
        </div>
        <div>
            <button type="button" id="cuw-view-offer-close" style="gap: 6px;" class="btn btn-outline-secondary">
                <i class="cuw-icon-close-circle inherit-color"></i>
                <?php esc_html_e("Close", 'checkout-upsell-woocommerce'); ?>
            </button>
        </div>
    </div>
    <div id="preview" class="col-md-12 border mt-4 p-3 rounded-lg" style="background: #f1f5f9; max-height:80vh; overflow-y: scroll">
        <div id="cuw-preview">
            <div class="offer-preview"></div>
        </div>
    </div>
</div>
