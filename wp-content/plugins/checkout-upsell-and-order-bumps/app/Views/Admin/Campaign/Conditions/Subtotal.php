<?php
defined('ABSPATH') || exit;

$key = isset($key) ? (int)$key : '{key}';
$operator = isset($condition['operator']) && !empty($condition['operator']) ? $condition['operator'] : '';
$value = isset($condition['value']) && !empty($condition['value']) ? $condition['value'] : '';
?>

<div class="condition-operator flex-fill">
    <select class="form-control" name="conditions[<?php echo esc_attr($key); ?>][operator]">
        <option value="ge" <?php if ($operator == 'ge') echo "selected"; ?>><?php esc_html_e("Greater than or equal to (>=)", 'checkout-upsell-and-order-bumps'); ?></option>
        <option value="gt" <?php if ($operator == 'gt') echo "selected"; ?>><?php esc_html_e("Greater than (>)", 'checkout-upsell-and-order-bumps'); ?></option>
        <option value="le" <?php if ($operator == 'le') echo "selected"; ?>><?php esc_html_e("Less than or equal to (<=)", 'checkout-upsell-and-order-bumps'); ?></option>
        <option value="lt" <?php if ($operator == 'lt') echo "selected"; ?>><?php esc_html_e("Less than (<)", 'checkout-upsell-and-order-bumps'); ?></option>
        <option value="eq" <?php if ($operator == 'eq') echo "selected"; ?>><?php esc_html_e("Equal to (=)", 'checkout-upsell-and-order-bumps'); ?></option>
        <option value="range" <?php if ($operator == 'range') echo "selected"; ?>><?php esc_html_e("Range (from-to)", 'checkout-upsell-and-order-bumps'); ?></option>
    </select>
</div>

<div class="condition-value">
    <input class="form-control" type="text" name="conditions[<?php echo esc_attr($key); ?>][value]"
           value="<?php echo esc_attr($value); ?>"
           placeholder="<?php esc_attr_e("Value", 'checkout-upsell-and-order-bumps'); ?>">
</div>
