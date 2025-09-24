<?php
defined('ABSPATH') || exit;

$days = [
    'sunday' => esc_html__("Sunday", 'checkout-upsell-and-order-bumps'),
    'monday' => esc_html__("Monday", 'checkout-upsell-and-order-bumps'),
    'tuesday' => esc_html__("Tuesday", 'checkout-upsell-and-order-bumps'),
    'wednesday' => esc_html__("Wednesday", 'checkout-upsell-and-order-bumps'),
    'thursday' => esc_html__("Thursday", 'checkout-upsell-and-order-bumps'),
    'friday' => esc_html__("Friday", 'checkout-upsell-and-order-bumps'),
    'saturday' => esc_html__("Saturday", 'checkout-upsell-and-order-bumps'),
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
        <option value="in_list" <?php if ($method == 'in_list') echo "selected"; ?>><?php esc_html_e("In list", 'checkout-upsell-and-order-bumps'); ?></option>
        <option value="not_in_list" <?php if ($method == 'not_in_list') echo "selected"; ?>><?php esc_html_e("Not in list", 'checkout-upsell-and-order-bumps'); ?></option>
    </select>
</div>

<div class="condition-values">
    <select multiple class="select2-local" name="conditions[<?php echo esc_attr($key); ?>][values][]" data-list="days"
            data-placeholder=" <?php esc_html_e("Choose days", 'checkout-upsell-and-order-bumps'); ?>">
        <?php foreach ($days as $slug => $day) { ?>
            <option value="<?php echo esc_attr($slug); ?>" <?php if (isset($values[$slug])) echo "selected"; ?>><?php echo esc_html($day); ?></option>
        <?php } ?>
    </select>
</div>
