<?php
defined('ABSPATH') || exit;

$roles = CUW()->wp->getUserRoles();
$roles = array_merge($roles, ['cuw_guest' => ['name' => esc_html__("Guest", 'checkout-upsell-woocommerce')]]);

$key = isset($key) ? (int)$key : '{key}';
$method = isset($condition['method']) && !empty($condition['method']) ? $condition['method'] : '';
$values = isset($condition['values']) && !empty($condition['values']) ? array_flip($condition['values']) : [];
foreach ($values as $slug => $role) {
    if (isset($roles[$slug])) {
        $values[$slug] = $roles[$slug]['name'];
    }
}
?>

<div class="condition-method flex-fill">
    <select class="form-control" name="conditions[<?php echo esc_attr($key); ?>][method]">
        <option value="in_list" <?php if ($method == 'in_list') echo "selected"; ?>><?php esc_html_e("In list", 'checkout-upsell-woocommerce'); ?></option>
        <option value="not_in_list" <?php if ($method == 'not_in_list') echo "selected"; ?>><?php esc_html_e("Not in list", 'checkout-upsell-woocommerce'); ?></option>
    </select>
</div>

<div class="condition-values">
    <select multiple class="select2-local" name="conditions[<?php echo esc_attr($key); ?>][values][]" data-list="roles"
            data-placeholder=" <?php esc_html_e("Choose roles", 'checkout-upsell-woocommerce'); ?>">
        <?php foreach ($roles as $slug => $role) { ?>
            <option value="<?php echo esc_attr($slug); ?>" <?php if (isset($values[$slug])) echo "selected"; ?>><?php echo esc_html($role['name']); ?></option>
        <?php } ?>
    </select>
</div>
