<?php
defined('ABSPATH') || exit;

$type = isset($type) ? (string)$type : '';
$campaign_type = isset($campaign_type) ? $campaign_type : '';
$conditions = \CUW\App\Helpers\Condition::get($campaign_type);
$grouped_conditions = [];
foreach ($conditions as $key => $condition) {
    if (isset($condition['group']) && $group = $condition['group']) {
        $grouped_conditions[$group][$key] = $condition;
    }
}
?>

<div id="condition-type">
    <select class="form-control">
        <option value="" selected disabled><?php esc_html_e("Choose a condition", 'checkout-upsell-and-order-bumps'); ?></option>
        <?php foreach ($grouped_conditions as $group => $conditions) { ?>
            <optgroup label="<?php echo esc_attr($group); ?>">
            <?php foreach ($conditions as $key => $condition) { ?>
                <option value="<?php echo esc_attr($key); ?>" <?php if ($type == $key) echo "selected"; ?> <?php if (empty($condition['handler'])) echo "disabled" ?>>
                    <?php echo esc_html($condition['name']); ?>
                    <?php if (empty($condition['handler']) && !CUW()->plugin->has_pro) echo esc_html(" – " . __("PRO", 'checkout-upsell-and-order-bumps')); ?>
                </option>
            <?php } ?>
            </optgroup>
        <?php } ?>
    </select>
</div>