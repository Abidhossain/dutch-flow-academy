<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}
?>
<div>
    <?php if (!empty($redirect_options)) {
        $redirect_url = !empty($campaign['data']['options']['redirect_url']) ? $campaign['data']['options']['redirect_url'] : '';
        $custom_redirect_url = !empty($campaign['data']['options']['custom_redirect_url']) ? $campaign['data']['options']['custom_redirect_url'] : \CUW\App\Helpers\WC::getPageUrl();
        ?>
        <div id="redirect-url" class="row mb-2">
            <div class="url-field col-md-12">
                <label for="url-field" class="form-label">
                    <?php esc_html_e("CTA redirect URL", 'checkout-upsell-and-order-bumps'); ?>
                </label>
                <select class="form-control" id="url-field" name="data[options][redirect_url]">
                    <?php if (in_array('default', $redirect_options)) { ?>
                        <option value="" <?php if (empty($redirect_url)) echo "selected"; ?>><?php esc_html_e('Default', 'checkout-upsell-and-order-bumps'); ?></option>
                    <?php } ?>
                    <?php if (in_array('home', $redirect_options)) { ?>
                        <option value="home" <?php if ($redirect_url == "home") echo "selected"; ?>><?php esc_html_e('Home page', 'checkout-upsell-and-order-bumps'); ?></option>
                    <?php } ?>
                    <?php if (in_array('shop', $redirect_options)) { ?>
                        <option value="shop" <?php if ($redirect_url == "shop") echo "selected"; ?>><?php esc_html_e('Shop page', 'checkout-upsell-and-order-bumps'); ?></option>
                    <?php } ?>
                    <?php if (in_array('cart', $redirect_options)) { ?>
                        <option value="cart" <?php if ($redirect_url == "cart") echo "selected"; ?>><?php esc_html_e('Cart page', 'checkout-upsell-and-order-bumps'); ?></option>
                    <?php } ?>
                    <?php if (in_array('checkout', $redirect_options)) { ?>
                        <option value="checkout" <?php if ($redirect_url == "checkout") echo "selected"; ?>><?php esc_html_e('Checkout page', 'checkout-upsell-and-order-bumps'); ?></option>
                    <?php } ?>
                    <?php if (in_array('custom', $redirect_options)) { ?>
                        <option value="custom" <?php if ($redirect_url == "custom") echo "selected"; ?>><?php esc_html_e('Custom URL', 'checkout-upsell-and-order-bumps'); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div id="url-value" class="url-value col-md-12 mt-2"
                 style="display: <?php echo ($redirect_url == 'custom') ? 'block' : 'none'; ?>">
                <label for="url-value" class="form-label">
                    <?php esc_html_e("Custom redirect URL", 'checkout-upsell-and-order-bumps'); ?>
                </label>
                <input type="url" class="form-control" name="data[options][custom_redirect_url]"
                       value="<?php echo esc_attr($custom_redirect_url); ?>" <?php if ($redirect_url != 'custom') echo 'disabled' ?> />
            </div>
        </div>
    <?php } ?>
</div>
