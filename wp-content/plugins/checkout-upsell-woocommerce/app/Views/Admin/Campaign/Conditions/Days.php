<?php
defined('ABSPATH') || exit;

$days = [
    'sunday' => esc_html__("Sunday", 'checkout-upsell-woocommerce'),
    'monday' => esc_html__("Monday", 'checkout-upsell-woocommerce'),
    'tuesday' => esc_html__("Tuesday", 'checkout-upsell-woocommerce'),
    'wednesday' => esc_html__("Wednesday", 'checkout-upsell-woocommerce'),
    'thursday' => esc_html__("Thursday", 'checkout-upsell-woocommerce'),
    'friday' => esc_html__("Friday", 'checkout-upsell-woocommerce'),
    'saturday' => esc_html__("Saturday", 'checkout-upsell-woocommerce'),
];
$key = isset($key) ? (int)$key : '{key}';
$method = isset($condition['method']) && !empty($condition['method']) ? $condition['method'] : '';
$values = isset($condition['values']) && !empty($condition['values']) ? array_flip($condition['values']) : [];
foreach ($values as $slug => $day) {
    $values[$slug] = $day;
}
?>

<div class="condition-method flex-fill">
    <select class="form-control" name="conditions[<?php echo esc_attr($key); ?>][method]">
        <option value="in_list" <?php if ($method == 'in_list') echo "selected"; ?>><?php esc_html_e("In list", 'checkout-upsell-woocommerce'); ?></option>
        <option value="not_in_list" <?php if ($method == 'not_in_list') echo "selected"; ?>><?php esc_html_e("Not in list", 'checkout-upsell-woocommerce'); ?></option>
    </select>
</div>

<div class="condition-values">
    <select multiple class="select2-local" name="conditions[<?php echo esc_attr($key); ?>][values][]" data-list="days"
            data-placeholder=" <?php esc_html_e("Choose days", 'checkout-upsell-woocommerce'); ?>">
        <?php foreach ($days as $slug => $day) { ?>
            <option value="<?php echo esc_attr($slug); ?>" <?php if (isset($values[$slug])) echo "selected"; ?>><?php echo esc_html($day); ?></option>
        <?php } ?>
    </select>
</div>
