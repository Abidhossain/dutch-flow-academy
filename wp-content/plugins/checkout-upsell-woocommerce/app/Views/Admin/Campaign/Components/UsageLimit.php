<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}
?>

<div>
    <?php
    $campaign_type = $campaign['type'] ?? '';
    $limit = !empty($campaign['usage_limit']) ? $campaign['usage_limit'] : '';
    $used = $campaign['usage_count'] ?? '-';
    $limit_per_user = !empty($campaign['usage_limit_per_user']) ? $campaign['usage_limit_per_user'] : '';
    ?>
    <div class="form-group mb-0">
        <label for="campaign-limit" class="form-label">
            <?php if ($campaign_type == 'noc') {
                esc_html_e("Overall coupon generation limit", 'checkout-upsell-woocommerce');
            } else {
                esc_html_e("Overall usage limit", 'checkout-upsell-woocommerce');
            } ?>
            <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
        </label>
        <input class="form-control" type="number" step="1" id="campaign-limit" name="limit" min="0"
               value="<?php echo esc_attr($limit); ?>"
               placeholder="<?php esc_attr_e("Unlimited", 'checkout-upsell-woocommerce'); ?>">
        <?php if (!empty($campaign['id'])) { ?>
            <span class="mt-1 mb-0 d-block form-label">
                <?php if ($campaign_type == 'noc') {
                    esc_html_e("Generated coupons", 'checkout-upsell-woocommerce');
                } else {
                    esc_html_e("Used", 'checkout-upsell-woocommerce');
                } ?>:
                <span class="font-weight-medium text-dark"><?php echo esc_html($used); ?></span>
            </span>
        <?php } ?>
    </div>
    <div class="form-group mt-2 mb-0">
        <label for="campaign-limit-per-user" class="form-label">
            <?php if ($campaign_type == 'noc') {
                esc_html_e("Coupon generation limit per customer", 'checkout-upsell-woocommerce');
            } else {
                esc_html_e("Usage limit per customer", 'checkout-upsell-woocommerce');
            } ?>
            <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
        </label>
        <input class="form-control" type="number" step="1" id="campaign-limit-per-user"
               name="limit_per_user" min="0" value="<?php echo esc_attr($limit_per_user); ?>"
               placeholder="<?php esc_attr_e("Unlimited", 'checkout-upsell-woocommerce'); ?>">
    </div>
</div>
