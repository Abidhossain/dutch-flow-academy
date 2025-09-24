<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$campaign_type = !empty($campaign['type']) ? $campaign['type'] : '';
$discount_type = !empty($campaign['data']['discount']['type']) ? $campaign['data']['discount']['type'] : 'no_discount';
$discount_value = !empty($campaign['data']['discount']['value']) ? $campaign['data']['discount']['value'] : 0;
$hide_free_discount = in_array($campaign_type, apply_filters('cuw_hide_free_discount_option_campaign_types', ['thankyou_upsells', 'upsell_popups']));
?>

<div id="cuw-discount" class="row">
    <div class="cuw-discount-type form-group col-md-6 m-0">
        <label for="discount-type" class="form-label"><?php esc_html_e("Discount type", 'checkout-upsell-and-order-bumps'); ?></label>
        <select class="form-control" id="discount-type" name="data[discount][type]">
            <option value="no_discount" <?php selected('no_discount', $discount_type); ?>><?php esc_html_e("No discount", 'checkout-upsell-and-order-bumps'); ?></option>
            <option value="percentage" <?php selected('percentage', $discount_type); ?>><?php esc_html_e("Percentage discount", 'checkout-upsell-and-order-bumps'); ?></option>
            <option value="fixed_price" <?php selected('fixed_price', $discount_type); ?>><?php esc_html_e("Fixed discount", 'checkout-upsell-and-order-bumps'); ?></option>
            <?php if (!$hide_free_discount) { ?>
                <option value="free" <?php selected('free', $discount_type); ?>><?php esc_html_e("Free", 'checkout-upsell-and-order-bumps'); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="cuw-discount-value form-group col-md-6" <?php if (in_array($discount_type, ['no_discount', 'free'])) echo 'style="display: none;"'?>>
        <label for="discount-value" class="form-label"><?php esc_html_e("Discount value", 'checkout-upsell-and-order-bumps'); ?></label>
        <input class="form-control" type="number" id="discount-value" name="data[discount][value]" min="0" value="<?php echo esc_attr($discount_value); ?>"
               placeholder="<?php esc_attr_e("Value", 'checkout-upsell-and-order-bumps'); ?>">
    </div>
</div>


