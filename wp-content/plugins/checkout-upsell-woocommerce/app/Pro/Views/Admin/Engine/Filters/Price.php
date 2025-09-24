<?php defined('ABSPATH') || exit ?>

<?php
$key = isset($key) ? (int)$key : '{key}';
$operator = !empty($filter['operator']) ? html_entity_decode($filter['operator']) : '';
$value = !empty($filter['value']) ? $filter['value'] : '';
?>

<div class="d-flex p-3 cuw-filter-wrapper" style="gap: 10px;">
    <div class="filter-operator" style="min-width: 212px;">
        <select class="form-control" name="engine_filters[<?php echo esc_attr($key); ?>][operator]">
            <option value=">=" <?php if ($operator == '>=') echo "selected"; ?>><?php esc_html_e("Greater than or equal to (>=)", 'checkout-upsell-woocommerce'); ?></option>
            <option value=">" <?php if ($operator == '>') echo "selected"; ?>><?php esc_html_e("Greater than (>)", 'checkout-upsell-woocommerce'); ?></option>
            <option value="<=" <?php if ($operator == '<=') echo "selected"; ?>><?php esc_html_e("Less than or equal to (<=)", 'checkout-upsell-woocommerce'); ?></option>
            <option value="<" <?php if ($operator == '<') echo "selected"; ?>><?php esc_html_e("Less than (<)", 'checkout-upsell-woocommerce'); ?></option>
            <option value="=" <?php if ($operator == '=') echo "selected"; ?>><?php esc_html_e("Equal to (=)", 'checkout-upsell-woocommerce'); ?></option>
        </select>
    </div>

    <div class="filter-value flex-fill">
        <input class="form-control" type="number" name="engine_filters[<?php echo esc_attr($key); ?>][value]"
               value="<?php echo esc_attr($value); ?>"
               placeholder="<?php esc_attr_e("Value", 'checkout-upsell-woocommerce'); ?>">
    </div>
</div>