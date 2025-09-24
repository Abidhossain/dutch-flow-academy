<?php
defined('ABSPATH') || exit;

use CUW\App\Helpers\WP;

if (!isset($engine)) {
    return;
}
?>

<div id="cuw-engine-info">
    <?php if (isset($engine['created_at']) && isset($engine['created_by'])) { ?>
        <div class="form-group mb-2">
            <h6 class="form-label font-weight-medium text-dark"><?php esc_html_e("Created", 'checkout-upsell-woocommerce'); ?></h6>
            <div class="ml-3">
                <span class="form-label"><?php esc_html_e("By", 'checkout-upsell-woocommerce'); ?>:</span>
                <span class="form-label text-dark"><?php echo esc_html(WP::getUserName($engine['created_by'])); ?></span>
            </div>
            <div class="ml-3">
                <span class="form-label"><?php esc_html_e("At", 'checkout-upsell-woocommerce'); ?>:</span>
                <span class="form-label text-dark"><?php echo esc_html(WP::formatDate($engine['created_at'], 'datetime', true)); ?></span>
            </div>
        </div>
    <?php } ?>
    <?php if (isset($engine['updated_at']) && isset($engine['updated_by'])) { ?>
        <div class="form-group mt-3 mb-2">
            <h6 class="form-label font-weight-medium text-dark"><?php esc_html_e("Updated", 'checkout-upsell-woocommerce'); ?></h6>
            <div class="ml-3">
                <span class="form-label"><?php esc_html_e("By", 'checkout-upsell-woocommerce'); ?>:</span>
                <span class="form-label text-dark"><?php echo esc_html(WP::getUserName($engine['updated_by'])); ?></span>
            </div>
            <div class="ml-3">
                <span class="form-label"><?php esc_html_e("At", 'checkout-upsell-woocommerce'); ?>:</span>
                <span class="form-label text-dark"><?php echo esc_html(WP::formatDate($engine['updated_at'], 'datetime', true)); ?></span>
            </div>
        </div>
    <?php } ?>
</div>
