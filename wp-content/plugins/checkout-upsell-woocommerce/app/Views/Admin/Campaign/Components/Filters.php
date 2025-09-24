<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$campaign_type = isset($campaign['type']) ? $campaign['type'] : '';
$filters = isset($campaign['filters']) ? $campaign['filters'] : [];
$available_filters = \CUW\App\Helpers\Filter::get($campaign_type);
$filter_relation = $campaign['filters']['relation'] ?? ''
?>

<div id="filters-match" class="align-items-center text-dark font-weight-medium mb-2"
     style="display: <?php echo empty($filters) ? 'none' : 'flex'; ?>;justify-content: space-between">
    <div style="display: flex; align-items: center; gap:4px;">
        <?php $filters_relation = isset($filters['relation']) && $filters['relation'] == 'or' ? "or" : "and"; ?>
        <?php echo sprintf("%s of the following filters",
            '<span class="filter-match-radio"><span class="custom-control custom-radio custom-control-inline">
                                <input type="radio" class="custom-control-input" id="filters-match-all" name="filters[relation]" value="and"' . ($filters_relation == "and" ? ' checked' : '') . (empty($filters) ? ' disabled' : '') . '>
                                <label class="custom-control-label" for="filters-match-all">' . esc_html__("Match All", "checkout-upsell-woocommerce") . '</label>
                        </span>
                        <span class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="filters-match-any" name="filters[relation]" value="or"' . ($filters_relation == "or" ? ' checked' : '') . (empty($filters) ? ' disabled' : '') . '>
                            <label class="custom-control-label" for="filters-match-any">' . esc_html__("Match Any", "checkout-upsell-woocommerce") . '</label>
                        </span></span>'); ?>
    </div>
    <div id="delete-all-filters" class="d-flex cursor-pointer justify-content-end align-items-center" style="gap:6px;">
        <i class="cuw-icon-delete text-danger"></i>
        <p class="text-danger font-weight-medium"><?php esc_html_e('Delete all filters', 'checkout-upsell-woocommerce'); ?></p>
    </div>
</div>

<div id="no-filters" class="text-center mt-2 text-danger" <?php if (!empty($filters)) echo 'style="display: none;"' ?>>
    <?php esc_html_e("At least one filter is required", 'checkout-upsell-woocommerce'); ?>
</div>

<div id="cuw-filters">
    <?php  $show_relation = false; $count = 0;?>
    <?php foreach ($filters as $key => $filter) {
        if (empty($filter['type'])) {
            continue;
        }
        $count ++;
        $type = $filter['type'];
        $name = isset($available_filters[$type]['name']) ? $available_filters[$type]['name'] : '';
        if (empty($name) || !isset($available_filters[$type]['handler'])) {
            continue;
        }
        CUW()->view('Admin/Campaign/Filters/Wrapper', [
            'key' => $key,
            'count' => $count,
            'name' => $name,
            'type' => $type,
            'filter' => $filter,
            'filters' => $available_filters,
            'relation' => $filter_relation,
            'show_relation' => $show_relation]);
        $show_relation = true;
    } ?>
</div>

<div class="input-group mt-3 d-flex flex-row" style="gap: 8px;">
    <button type="button" id="add-filter" class="btn btn-outline-primary">
        <i class="cuw-icon-add-circle inherit-color mx-1"></i>
        <?php esc_html_e("Add filter", 'checkout-upsell-woocommerce'); ?>
    </button>
</div>

