<?php defined('ABSPATH') || exit ?>

<?php
$key = isset($key) ? (int) $key : '{key}';
$operator = isset($amplifier['operator']) && !empty($amplifier['operator']) ? $amplifier['operator'] : '';
?>

<div class="flex-fill p-3 cuw-amplifier-wrapper">
    <select class="form-control" name="engine_amplifiers[<?php echo esc_attr($key); ?>][operator]">
        <option value="asc" <?php if ($operator == 'asc') echo "selected"; ?>><?php esc_html_e("Ascending [A-Z]", 'checkout-upsell-woocommerce'); ?></option>
        <option value="desc" <?php if ($operator == 'desc') echo "selected"; ?>><?php esc_html_e("Descending [Z-A]", 'checkout-upsell-woocommerce'); ?></option>
    </select>
</div>


