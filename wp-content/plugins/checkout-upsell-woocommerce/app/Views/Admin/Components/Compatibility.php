<?php
defined('ABSPATH') || exit;
if (!isset($packages)) {
    return;
}

foreach ($packages as $package) { ?>
    <div class="mt-3 row align-items-center">
        <div class="col-md-5">
            <label class="font-weight-semibold text-dark form-label"><?php echo esc_html($package['name']); ?></label>
            <p class="form-text">by <?php echo esc_html($package['author']); ?></p>
        </div>
        <div class="col-md-5 d-flex" style="gap: 16px;">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" class="custom-control-input"
                       id="compat-<?php echo esc_attr($package['key']); ?>-active"
                       name="compatibilities[<?php echo esc_attr($package['key']); ?>]"
                       value="1"
                    <?php if (!empty($package['active'])) echo 'checked'; ?>>
                <label class="custom-control-label"
                       for="compat-<?php echo esc_attr($package['key']); ?>-active">
                    <?php esc_html_e("Active", 'checkout-upsell-woocommerce'); ?>
                </label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" class="custom-control-input"
                       id="compat-<?php echo esc_attr($package['key']); ?>-disable"
                       name="compatibilities[<?php echo esc_attr($package['key']); ?>]"
                       value="0"
                    <?php if (empty($package['active'])) echo 'checked'; ?>>
                <label class="custom-control-label"
                       for="compat-<?php echo esc_attr($package['key']); ?>-disable">
                    <?php esc_html_e("Disable", 'checkout-upsell-woocommerce'); ?>
                </label>
            </div>
        </div>
    </div>
<?php }
