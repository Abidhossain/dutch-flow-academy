<?php
    defined('ABSPATH') || exit;
    if (!isset($campaign)) {
        return;
    }

    $template = !empty($campaign['data']['template']) ? $campaign['data']['template'] : [];
    $discount = !empty($campaign['data']['discount']) ? $campaign['data']['discount'] : [];
    $campaign_type = isset($campaign['type']) ? $campaign['type'] : '';
?>
<div id="cuw-view-template" class="mt-3">
    <div class="cuw-slider-header d-flex justify-content-between align-items-center flex-wrap">
        <h4 class="cuw-slider-title"><?php esc_html_e("Preview", 'checkout-upsell-and-order-bumps'); ?></h4>
        <div class="btn-group bg-white border border-gray-light rounded p-1 d-flex" id="cuw-view-device-preview">
            <label class="btn m-0 btn-primary">
                <i class="cuw-icon-desktop inherit-color mx-1"></i>
                <input type="radio" name="preview-device" value="desktop" class="d-none" checked><span class="mx-1"><?php esc_html_e("Desktop", 'checkout-upsell-and-order-bumps'); ?></span>
            </label>
            <label class="btn m-0">
                <i class="cuw-icon-mobile inherit-color mx-1"></i>
                <input type="radio" name="preview-device" value="mobile" class="d-none"> <span class="mx-1"><?php esc_html_e("Mobile", 'checkout-upsell-and-order-bumps'); ?></span>
            </label>
        </div>
        <div>
            <button type="button" id="cuw-view-template-close" class="align-items-center btn btn-outline-secondary">
                <i class="cuw-icon-close-circle inherit-color mx-1"></i>
               <?php esc_html_e("Close", 'checkout-upsell-and-order-bumps'); ?>
            </button>
        </div>
    </div>
    <div id="preview" class="border mt-3 p-3 rounded-lg" style="background: #f1f5f9; max-height:80vh; overflow-y: scroll">
        <div id="cuw-view-template-preview">
            <div class="cuw-template-preview">
                <?php
                // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                echo \CUW\App\Helpers\Template::getPreviewHtml(['type' => $campaign_type,
                    'data' => [
                        'discount' => $discount,
                        'template' => $template,
                    ]
                ]);
                // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </div>
        </div>
    </div>
</div>