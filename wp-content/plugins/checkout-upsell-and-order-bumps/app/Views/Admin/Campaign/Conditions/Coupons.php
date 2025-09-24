<?php
defined('ABSPATH') || exit;

$key = isset($key) ? (int)$key : '{key}';
$method = isset($condition['method']) && !empty($condition['method']) ? $condition['method'] : '';
$values = isset($condition['values']) && !empty($condition['values']) ? $condition['values'] : [];
$hide_values = in_array($method, ['empty', 'not_empty']);
?>

<div class="condition-method flex-fill">
    <select class="form-control coupon-condition" name="conditions[<?php echo esc_attr($key); ?>][method]">
        <option value="in_list" <?php if ($method == 'in_list') echo "selected"; ?>><?php esc_html_e("In list", 'checkout-upsell-and-order-bumps'); ?></option>
        <option value="not_in_list" <?php if ($method == 'not_in_list') echo "selected"; ?>><?php esc_html_e("Not in list", 'checkout-upsell-and-order-bumps'); ?></option>
        <option value="not_empty" <?php if ($method == 'not_empty') echo "selected"; ?>><?php esc_html_e("Any coupon", 'checkout-upsell-and-order-bumps'); ?></option>
        <option value="empty" <?php if ($method == 'empty') echo "selected"; ?>><?php esc_html_e("No coupons", 'checkout-upsell-and-order-bumps'); ?></option>
    </select>
</div>

<div class="condition-values" <?php if ($hide_values) echo 'style="display: none;"'; ?>>
    <select multiple class="select2-list" name="conditions[<?php echo esc_attr($key); ?>][values][]" data-list="coupons"
            data-placeholder=" <?php esc_html_e("Choose coupons", 'checkout-upsell-and-order-bumps'); ?>"
        <?php if ($hide_values) echo 'disabled'; ?>>
        <?php foreach ($values as $value) { ?>
            <option value="<?php echo esc_attr($value); ?>" selected><?php echo esc_html($value); ?></option>
        <?php } ?>
    </select>
</div>
