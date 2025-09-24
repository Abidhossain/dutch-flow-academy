<?php
defined('ABSPATH') || exit;

$key = isset($key) ? (int)$key : '{key}';
$value = isset($condition['value']) && !empty($condition['value']) ? $condition['value'] : [];
?>

<div class="condition-method flex-fill">
    <select class="form-control optional trigger-change" name="conditions[<?php echo esc_attr($key); ?>][value]">
        <option value="yes" <?php if ($value == 'yes') echo "selected"; ?>><?php esc_html_e("Yes", 'checkout-upsell-woocommerce'); ?></option>
        <option value="no" <?php if ($value == 'no') echo "selected"; ?>><?php esc_html_e("No", 'checkout-upsell-woocommerce'); ?></option>
    </select>
</div>