<?php
defined('ABSPATH') || exit;

$key = isset($key) ? (int)$key : '{key}';
$method = isset($condition['method']) && !empty($condition['method']) ? $condition['method'] : '';
$values = isset($condition['values']) && !empty($condition['values']) ? $condition['values'] : [];
?>

<div class="condition-method flex-fill">
    <select class="form-control" name="conditions[<?php echo esc_attr($key); ?>][method]">
        <option value="in" <?php if ($method == 'in') echo "selected"; ?>><?php esc_html_e("In", 'checkout-upsell-woocommerce'); ?></option>
        <option value="not_in" <?php if ($method == 'not_in') echo "selected"; ?>><?php esc_html_e("Not In", 'checkout-upsell-woocommerce'); ?></option>
    </select>
</div>

<div class="condition-values d-flex flex-column">
    <div style="margin-top: 10px;"><?php esc_html_e("From", 'checkout-upsell-woocommerce'); ?></div>
    <div class="condition-value">
        <input type='time' class="form-control"  name="conditions[<?php echo esc_attr($key); ?>][values][from]" value="<?php echo !empty($values['from']) ? esc_attr($values['from']) : ''; ?>">
    </div>
    <div style="margin-top: 10px;"><?php esc_html_e("To", 'checkout-upsell-woocommerce'); ?></div>
    <div class="condition-value">
        <input type="time" class="form-control" name="conditions[<?php echo esc_attr($key); ?>][values][to]" value="<?php echo !empty($values['to']) ? esc_attr($values['to']) : ''; ?>">
    </div>
</div>