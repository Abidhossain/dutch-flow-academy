<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}
?>

<div id="cuw-action">
    <?php
    $discount = isset($campaign['data']['discount']) ? (array)$campaign['data']['discount'] : [];
    $cta_text = isset($template['cta_text']) ? $template['cta_text'] : '';
    $discount_type = isset($discount['type']) ? $discount['type'] : 'percentage';
    $discount_value = isset($discount['value']) ? $discount['value'] : '';
    $discount_label = isset($discount['label']) ? $discount['label'] : 'Discount';
    ?>
    <div>
        <div class="row mb-0 cuw-discount">
            <div class="col-md-6 cuw-discount-type form-group mb-0">
                <label for="action-discount-type" class="form-label"><?php esc_html_e("Discount type", 'checkout-upsell-woocommerce'); ?></label>
                <select class="reload-preview form-control" id="action-discount-type" name="data[discount][type]">
                    <option value="percentage" <?php selected('percentage', $discount_type); ?>><?php esc_html_e("Percentage discount", 'checkout-upsell-woocommerce'); ?></option>
                    <option value="fixed_price" <?php selected('fixed_price', $discount_type); ?>><?php esc_html_e("Fixed cart discount", 'checkout-upsell-woocommerce'); ?></option>
                </select>
            </div>
            <div class="col-md-6 cuw-discount-value form-group mb-0" style="<?php if (in_array($discount_type, ['free', 'no_discount'])) echo 'display: none;' ?>">
                <label for="action-discount-value" class="form-label"><?php esc_html_e("Discount value", 'checkout-upsell-woocommerce'); ?></label>
                <input class="reload-preview form-control" type="number" id="action-discount-value" name="data[discount][value]" min="0" value="<?php echo esc_attr($discount_value); ?>"
                       placeholder="<?php esc_attr_e("Value", 'checkout-upsell-woocommerce'); ?>">
            </div>
        </div>
        <div class="row mt-2 mb-0 cuw-discount-coupon">
            <div class="col-md-6 cuw-discount-label form-group mb-0" style="<?php if (in_array($discount_type, ['free', 'no_discount'])) echo 'display: none;' ?>">
                <label for="action-discount-value" class="form-label"><?php esc_html_e("Discount coupon label", 'checkout-upsell-woocommerce'); ?></label>
                <input class="reload-preview form-control" type="text" id="action-discount-label" name="data[discount][label]" value="<?php echo esc_attr($discount_label); ?>"
                       placeholder="<?php esc_attr_e("Coupon", 'checkout-upsell-woocommerce'); ?>">
            </div>
        </div>
        <input type="hidden" name="data[discount][is_bundle]" value="1">
        <input type="hidden" name="data[discount][bundle_by]" value="campaign_id">
        <input type="hidden" name="data[discount][apply_as]" value="dynamic_coupon">
    </div>

</div>
