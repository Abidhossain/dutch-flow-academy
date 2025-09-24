<?php
defined('ABSPATH') || exit;

$key = isset($key) ? (int)$key : '{key}';
$operator = isset($condition['operator']) && !empty($condition['operator']) ? $condition['operator'] : '';
$value = isset($condition['value']) && !empty($condition['value']) ? $condition['value'] : '';
$order_date = isset($condition['order_date']) && !empty($condition['order_date']) ? $condition['order_date'] : [];
$order_statuses = isset($condition['order_statuses']) && !empty($condition['order_statuses']) ? array_flip($condition['order_statuses']) : [];
$list_statuses = CUW()->wc->getOrderStatuses();
foreach ($order_statuses as $slug => $status) {
    if (isset($list_statuses[$slug])) {
        $order_statuses[$slug] = $list_statuses[$slug];
    }
}
?>

<div class="condition-operator flex-fill">
    <select class="form-control optional" name="conditions[<?php echo esc_attr($key); ?>][order_date]">
        <option value="" <?php if ($order_date == '') echo "selected"; ?>><?php esc_html_e("All time", 'checkout-upsell-woocommerce'); ?></option>
        <optgroup label="<?php esc_attr_e('Current', 'checkout-upsell-woocommerce') ?>">
            <option value="now" <?php if ($order_date == 'now') echo "selected"; ?>><?php esc_html_e("Current day", 'checkout-upsell-woocommerce'); ?></option>
            <option value="this week" <?php if ($order_date == 'this week') echo "selected"; ?>><?php esc_html_e("Current week", 'checkout-upsell-woocommerce'); ?></option>
            <option value="first day of this month" <?php if ($order_date == 'first day of this month') echo "selected"; ?>><?php esc_html_e("Current month", 'checkout-upsell-woocommerce'); ?></option>
            <option value="first day of january this year" <?php if ($order_date == 'first day of january this year') echo "selected"; ?>><?php esc_html_e("Current year", 'checkout-upsell-woocommerce'); ?></option>
        </optgroup>
        <optgroup label="<?php esc_attr_e('Days', 'checkout-upsell-woocommerce') ?>">
            <option value="-1 day" <?php if ($order_date == '-1 day') echo "selected"; ?>>
                1 <?php esc_html_e("day", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-2 days" <?php if ($order_date == '-2 days') echo "selected"; ?>>
                2 <?php esc_html_e("days", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-3 days" <?php if ($order_date == '-3 days') echo "selected"; ?>>
                3 <?php esc_html_e("days", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-4 days" <?php if ($order_date == '-4 days') echo "selected"; ?>>
                4 <?php esc_html_e("days", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-5 days" <?php if ($order_date == '-5 days') echo "selected"; ?>>
                5 <?php esc_html_e("days", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-6 days" <?php if ($order_date == '-6 days') echo "selected"; ?>>
                6 <?php esc_html_e("days", 'checkout-upsell-woocommerce'); ?></option>
        </optgroup>
        <optgroup label="<?php esc_attr_e('Weeks', 'checkout-upsell-woocommerce') ?>">
            <option value="-1 week" <?php if ($order_date == '-1 week') echo "selected"; ?>>
                1 <?php esc_html_e("week", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-2 weeks" <?php if ($order_date == '-2 weeks') echo "selected"; ?>>
                2 <?php esc_html_e("weeks", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-3 weeks" <?php if ($order_date == '-3 weeks') echo "selected"; ?>>
                3 <?php esc_html_e("weeks", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-4 weeks" <?php if ($order_date == '-4 weeks') echo "selected"; ?>>
                4 <?php esc_html_e("weeks", 'checkout-upsell-woocommerce'); ?></option>
        </optgroup>
        <optgroup label="<?php esc_attr_e('Months', 'checkout-upsell-woocommerce') ?>">
            <option value="-1 month" <?php if ($order_date == '-1 month') echo "selected"; ?>>
                1 <?php esc_html_e("month", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-2 months" <?php if ($order_date == '-2 months') echo "selected"; ?>>
                2 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-3 months" <?php if ($order_date == '-3 months') echo "selected"; ?>>
                3 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-4 months" <?php if ($order_date == '-4 months') echo "selected"; ?>>
                4 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-5 months" <?php if ($order_date == '-5 months') echo "selected"; ?>>
                5 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-6 months" <?php if ($order_date == '-6 months') echo "selected"; ?>>
                6 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-7 months" <?php if ($order_date == '-7 months') echo "selected"; ?>>
                7 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-8 months" <?php if ($order_date == '-8 months') echo "selected"; ?>>
                8 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-9 months" <?php if ($order_date == '-9 months') echo "selected"; ?>>
                9 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-10 months" <?php if ($order_date == '-10 months') echo "selected"; ?>>
                10 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-11 months" <?php if ($order_date == '-11 months') echo "selected"; ?>>
                11 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-12 months" <?php if ($order_date == '-12 months') echo "selected"; ?>>
                12 <?php esc_html_e("months", 'checkout-upsell-woocommerce'); ?></option>
        </optgroup>
        <optgroup label="<?php esc_attr_e('Years', 'checkout-upsell-woocommerce') ?>">
            <option value="-2 years" <?php if ($order_date == '-2 years') echo "selected"; ?>>
                2 <?php esc_html_e("years", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-3 years" <?php if ($order_date == '-3 years') echo "selected"; ?>>
                3 <?php esc_html_e("years", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-4 years" <?php if ($order_date == '-4 years') echo "selected"; ?>>
                4 <?php esc_html_e("years", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-5 years" <?php if ($order_date == '-5 years') echo "selected"; ?>>
                5 <?php esc_html_e("years", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-6 years" <?php if ($order_date == '-6 years') echo "selected"; ?>>
                6 <?php esc_html_e("years", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-7 years" <?php if ($order_date == '-7 years') echo "selected"; ?>>
                7 <?php esc_html_e("years", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-8 years" <?php if ($order_date == '-8 years') echo "selected"; ?>>
                8 <?php esc_html_e("years", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-9 years" <?php if ($order_date == '-9 years') echo "selected"; ?>>
                9 <?php esc_html_e("years", 'checkout-upsell-woocommerce'); ?></option>
            <option value="-10 years" <?php if ($order_date == '-10 years') echo "selected"; ?>>
                10 <?php esc_html_e("years", 'checkout-upsell-woocommerce'); ?></option>
        </optgroup>
    </select>
</div>

<div class="condition-operator flex-fill">
    <select class="form-control" name="conditions[<?php echo esc_attr($key); ?>][operator]">
        <option value="ge" <?php if ($operator == 'ge') echo "selected"; ?>><?php esc_html_e("Greater than or equal to (>=)", 'checkout-upsell-woocommerce'); ?></option>
        <option value="gt" <?php if ($operator == 'gt') echo "selected"; ?>><?php esc_html_e("Greater than (>)", 'checkout-upsell-woocommerce'); ?></option>
        <option value="le" <?php if ($operator == 'le') echo "selected"; ?>><?php esc_html_e("Less than or equal to (<=)", 'checkout-upsell-woocommerce'); ?></option>
        <option value="lt" <?php if ($operator == 'lt') echo "selected"; ?>><?php esc_html_e("Less than (<)", 'checkout-upsell-woocommerce'); ?></option>
        <option value="eq" <?php if ($operator == 'eq') echo "selected"; ?>><?php esc_html_e("Equal to (=)", 'checkout-upsell-woocommerce'); ?></option>
        <option value="range" <?php if ($operator == 'range') echo "selected"; ?>><?php esc_html_e("Range (from-to)", 'checkout-upsell-woocommerce'); ?></option>
    </select>
</div>

<div class="condition-value">
    <input class="form-control" type="text" name="conditions[<?php echo esc_attr($key); ?>][value]"
           value="<?php echo esc_attr($value); ?>"
           placeholder="<?php esc_attr_e("Total amount", 'checkout-upsell-woocommerce'); ?>">
</div>

<div class="condition-values">
    <select multiple class="select2-local" name="conditions[<?php echo esc_attr($key); ?>][order_statuses][]"
            data-placeholder=" <?php esc_html_e("Choose order statuses", 'checkout-upsell-woocommerce'); ?>">
        <?php foreach ($list_statuses as $slug => $name) { ?>
            <option value="<?php echo esc_attr($slug); ?>" <?php if (isset($order_statuses[$slug])) echo "selected"; ?>><?php echo esc_html($name); ?></option>
        <?php } ?>
    </select>
</div>