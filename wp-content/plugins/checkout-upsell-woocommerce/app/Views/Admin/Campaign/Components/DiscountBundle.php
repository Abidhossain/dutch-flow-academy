<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$campaign_data = !empty($campaign['data']) ? $campaign['data'] : [];
$discount = !empty($campaign_data['discount']) ? $campaign_data['discount'] : [];
$discount_apply_to = !empty($discount['apply_to']) ? $discount['apply_to'] : 'no_products';
$discount_type = !empty($discount['type']) ? $discount['type'] : 'percentage';
$discount_value = !empty($discount['value']) ? $discount['value'] : '';
?>

<div id="cuw-discount">
    <div class="d-flex" style="gap: 16px;">
        <div class="cuw-discount-apply-to form-group m-0" style="width: 50%;">
            <label for="fbt-discount-type" class="form-label"><?php esc_html_e("Discount apply to", 'checkout-upsell-woocommerce'); ?></label>
            <select class="form-control" id="fbt-discount-type" name="data[discount][apply_to]">
                <option value="no_products" <?php selected('no_products', $discount_apply_to); ?>><?php esc_html_e("No products", 'checkout-upsell-woocommerce'); ?></option>
                <option value="only_upsells" <?php selected('only_upsells', $discount_apply_to); ?>><?php esc_html_e("Only Upsell products", 'checkout-upsell-woocommerce'); ?></option>
                <option value="all_products" <?php selected('all_products', $discount_apply_to); ?>><?php esc_html_e("All Products (Main + Upsell products)", 'checkout-upsell-woocommerce'); ?></option>
            </select>
        </div>
    </div>
    <div class="cuw-discount-details mt-2" style="<?php if ($discount_apply_to == 'no_products') echo 'display: none;' ?>">
        <div class="d-flex" style="gap: 16px;">
            <div class="cuw-discount-type form-group mb-0" style="width: 50%;">
                <label for="fbt-discount-type" class="form-label"><?php esc_html_e("Discount type", 'checkout-upsell-woocommerce'); ?></label>
                <select class="form-control" id="fbt-discount-type" name="data[discount][type]" <?php if ($discount_apply_to == 'no_products') echo 'disabled' ?>>
                    <option value="percentage" <?php selected('percentage', $discount_type); ?>><?php esc_html_e("Percentage discount", 'checkout-upsell-woocommerce'); ?></option>
                    <option value="fixed_price" <?php selected('fixed_price', $discount_type); ?>><?php esc_html_e("Fixed discount", 'checkout-upsell-woocommerce'); ?></option>
                </select>
            </div>
            <div class="cuw-discount-value form-group mb-0" style="width: calc(50% - 16px);">
                <label for="fbt-discount-value" class="form-label"><?php esc_html_e("Discount value", 'checkout-upsell-woocommerce'); ?></label>
                <input class="form-control" type="number" id="fbt-discount-value" name="data[discount][value]" min="0" value="<?php echo esc_attr($discount_value); ?>"
                       placeholder="<?php esc_attr_e("Value", 'checkout-upsell-woocommerce'); ?>"
                    <?php if ($discount_apply_to == 'no_products') echo 'disabled' ?>>
            </div>
        </div>
    </div>
</div>
