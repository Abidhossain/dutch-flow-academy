<?php
defined('ABSPATH') || exit;

$key = isset($key) ? (int) $key : '{key}';
$method = isset($filter['method']) && !empty($filter['method']) ? $filter['method'] : '';
$values = isset($filter['values']) && !empty($filter['values']) ? $filter['values'] : [];
?>

<div class="filter-method flex-fill">
	<select class="form-control" name="filters[<?php echo esc_attr($key); ?>][method]">
		<option value="in_list" <?php if ($method == 'in_list') echo "selected"; ?>><?php esc_html_e("In list", 'checkout-upsell-woocommerce'); ?></option>
		<option value="not_in_list" <?php if ($method == 'not_in_list') echo "selected"; ?>><?php esc_html_e("Not in list", 'checkout-upsell-woocommerce'); ?></option>
	</select>
</div>

<div class="filter-values">
	<select multiple class="select2-list" name="filters[<?php echo esc_attr($key); ?>][values][]" data-list="skus"
            data-placeholder=" <?php esc_html_e("Search SKUs ", 'checkout-upsell-woocommerce'); ?>">
		<?php foreach ($values as $sku) { ?>
			<option value="<?php echo esc_attr($sku); ?>" selected><?php echo esc_html($sku); ?></option>
		<?php } ?>
	</select>
</div>