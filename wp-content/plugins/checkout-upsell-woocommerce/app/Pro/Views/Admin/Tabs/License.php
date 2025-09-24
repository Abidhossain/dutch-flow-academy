<?php
defined('ABSPATH') || exit;

$license_key = \CUW\App\Pro\Helpers\License::getLicenseKey();
$license_status = \CUW\App\Pro\Helpers\License::getLicenseStatus();
$license_status_formatted = \CUW\App\Pro\Helpers\License::getLicenseStatus(true);
$license_url = \CUW\App\Pro\Helpers\License::getAccountUrl();
$default_tab = apply_filters('cuw_settings_default_tab', 'license');
$premium_addons_data = apply_filters('cuw_premium_addons_data', []);
?>

<?php if (current_action() == 'cuw_before_settings_tabs'): ?>
    <li class="nav-item">
        <a class="nav-link <?php if ($default_tab == 'license') echo 'active'; ?>" data-toggle="tab"
           href="#settings-license">
            <?php esc_html_e("License", 'checkout-upsell-woocommerce'); ?>
        </a>
    </li>
<?php elseif (current_action() == 'cuw_before_settings_tab_contents'): ?>
    <div class="tab-pane fade <?php if ($default_tab == 'license') echo 'show active'; ?>" id="settings-license">
        <div class="row align-items-center">
            <div class="col-md-5 mb-4">
                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("License", 'checkout-upsell-woocommerce'); ?></label>
                <p class="form-text">
                    <?php echo sprintf(
                        esc_html__("Obtain your license key from your %s account and verify here.", 'checkout-upsell-woocommerce'),
                        '<a class="link" href="' . esc_url($license_url) . '" target="_blank">UpsellWP</a>'
                    ); ?>
                </p>
            </div>
            <div class="col-md-5">
                <div class="input-group">
                    <input type="text" id="license-key" placeholder="Enter your license key here..."
                           value="<?php echo esc_attr($license_key); ?>"
                           class="form-control <?php echo $license_status == 'active' ? 'border-success' : 'border-danger'; ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="activate-license"
                                style="<?php if ($license_status == 'active') echo 'display: none;'; ?>">
                            <i class="cuw-icon-tick-circle text-white mx-1"></i>
                            <?php esc_html_e("Activate", 'checkout-upsell-woocommerce'); ?>
                        </button>
                        <button class="btn btn-secondary" type="button" id="deactivate-license"
                                style="<?php if ($license_status != 'active') echo 'display: none;'; ?>">
                            <i class="cuw-icon-close-circle text-white mx-1"></i>
                            <?php esc_html_e("Deactivate", 'checkout-upsell-woocommerce'); ?>
                        </button>
                    </div>
                </div>
                <div class="text-dark small mt-1">
                    <?php esc_html_e("Status", 'checkout-upsell-woocommerce'); ?>:
                    <span id="license-status" class="text-primary">
                        <?php echo esc_html($license_status_formatted); ?>
                    </span>
                    <span>
                        <i id="check-license-status" class="cuw-icon-reset text-primary mx-1"
                           style="font-size: 12px; font-weight: 600; vertical-align: middle;"></i>
                    </span>
                </div>
            </div>
        </div>
        <?php if (!empty($premium_addons_data)) { ?>
            <div class="cuw-addons-license-section">
                <h5 class="mb-2 text-primary"><?php esc_html_e("Addons", 'checkout-upsell-woocommerce'); ?></h5>
                <?php foreach ($premium_addons_data as $slug => $data) {
                    echo ($data['view'] ?? ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } ?>
            </div>
        <?php } ?>
    </div>
<?php endif; ?>
