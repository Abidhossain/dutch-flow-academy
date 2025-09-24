<?php
defined('ABSPATH') || exit;

$type = isset($type) ? (string)$type : '';
$campaign_type = isset($campaign_type) ? $campaign_type : '';
$filters = \CUW\App\Helpers\Filter::get($campaign_type);
$grouped_filters = [];
foreach ($filters as $key => $filter) {
    if (isset($filter['group']) && $group = $filter['group']) {
        $grouped_filters[$group][$key] = $filter;
    }
}
?>

<div id="filter-type">
    <select class="form-control">
        <option value="" selected disabled>
            <?php esc_html_e("Choose a filter", 'checkout-upsell-and-order-bumps'); ?>
        </option>
        <?php foreach ($grouped_filters as $group => $filters) { ?>
            <optgroup label="<?php echo esc_attr($group); ?>">
                <?php foreach ($filters as $key => $filter) { ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php if ($type == $key) echo "selected"; ?> <?php if (empty($filter['handler'])) echo "disabled" ?>>
                        <?php echo esc_html($filter['name']); ?>
                        <?php if (empty($filter['handler']) && !CUW()->plugin->has_pro) echo esc_html(" – " . __("PRO", 'checkout-upsell-and-order-bumps')); ?>
                    </option>
                <?php } ?>
            </optgroup>
        <?php } ?>
    </select>
</div>