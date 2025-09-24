<?php
defined('ABSPATH') || exit;

use CUW\App\Helpers\WP;

if (!isset($campaign)) {
    return;
}
?>

<div id="cuw-campaign-info">
    <?php if (isset($campaign['created_at']) && isset($campaign['created_by'])) { ?>
        <div class="form-group mb-2">
            <h6 class="form-label font-weight-medium text-dark"><?php esc_html_e("Created", 'checkout-upsell-and-order-bumps'); ?></h6>
            <div class="ml-3">
                <span class="form-label"><?php esc_html_e("By", 'checkout-upsell-and-order-bumps'); ?>:</span>
                <span class="form-label text-dark"><?php echo esc_html(WP::getUserName($campaign['created_by'])); ?></span>
            </div>
            <div class="ml-3">
                <span class="form-label"><?php esc_html_e("At", 'checkout-upsell-and-order-bumps'); ?>:</span>
                <span class="form-label text-dark"><?php echo esc_html(WP::formatDate($campaign['created_at'], 'datetime', true)); ?></span>
            </div>
        </div>
    <?php } ?>
    <?php if (isset($campaign['updated_at']) && isset($campaign['updated_by'])) { ?>
        <div class="form-group mt-3 mb-2">
            <h6 class="form-label font-weight-medium text-dark"><?php esc_html_e("Updated", 'checkout-upsell-and-order-bumps'); ?></h6>
            <div class="ml-3">
                <span class="form-label"><?php esc_html_e("By", 'checkout-upsell-and-order-bumps'); ?>:</span>
                <span class="form-label text-dark"><?php echo esc_html(WP::getUserName($campaign['updated_by'])); ?></span>
            </div>
            <div class="ml-3">
                <span class="form-label"><?php esc_html_e("At", 'checkout-upsell-and-order-bumps'); ?>:</span>
                <span class="form-label text-dark"><?php echo esc_html(WP::formatDate($campaign['updated_at'], 'datetime', true)); ?></span>
            </div>
        </div>
    <?php } ?>
</div>
