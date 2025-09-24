<?php defined('ABSPATH') || exit; ?>

<?php
$filters = isset($engine['filters']) ? $engine['filters'] : [];
$available_filters = \CUW\App\Pro\Helpers\Engine::getFilters(!empty($engine['type']) ? $engine['type'] : '');
$added_filters = [];
?>

<div id="cuw-engine-filter-container" class="p-3"
     style="<?php echo empty($filters) ? 'max-height: 54vh; overflow-y: scroll;' : 'height: auto;' ?> margin: 4px;">
    <label class="form-label font-weight-medium mt-n2 mb-2">
        <?php esc_html_e("Define filters and conditions to customize your product recommendations", 'checkout-upsell-woocommerce'); ?>
    </label>
    <div id="no-engine-filters"
         class="text-center my-2 text-secondary d-none">
        <?php esc_html_e("Add any filters to generate a list of products based on the category, tags etc...", 'checkout-upsell-woocommerce'); ?>
    </div>
    <?php if (!empty($filters)) : ?>
        <div id="added-engine-filters-list" class="d-flex flex-column" style="gap: 10px;">
            <?php foreach ($filters as $key => $filter) {
                if (empty($filter['type'])) {
                    continue;
                }
                $type = $filter['type'];
                $name = isset($available_filters[$type]['name']) ? $available_filters[$type]['name'] : '';
                if (empty($name) || !isset($available_filters[$type]['handler'])) {
                    continue;
                }
                $added_filters[] = $type;
                CUW()->view('Pro/Admin/Engine/Filters/SelectedFilters', ['key' => $key, 'name' => $name, 'type' => $type, 'filter' => $filter, 'filters' => $available_filters]);
            } ?>
        </div>
    <?php endif; ?>
    <?php if (empty($filters)) : ?>
        <?php CUW()->view('Pro/Admin/Engine/Filters/List', ['engine_type' => $engine['type'], 'added_filters' => $added_filters]); ?>
    <?php endif; ?>
</div>

<div class="form-separator m-0"></div>

<div class="input-group flex-row justify-content-between p-3" style="gap: 8px;">
    <div>
        <button type="button" id="add-new-filter"
                class="btn btn-outline-primary <?php if (empty($filters)) echo 'd-none'; ?>">
            <i class="cuw-icon-add-circle inherit-color px-1"></i>
            <?php esc_html_e("Add Filters", 'checkout-upsell-woocommerce'); ?>
        </button>
    </div>
    <button type="button"
            class="btn btn-outline-primary move-to-amplifier <?php if (!empty($filters)) echo 'd-none'; ?>" disabled>
        <?php esc_html_e("Next", 'checkout-upsell-woocommerce'); ?>
        <i class="cuw-icon-chevron-right inherit-color mx-1"></i>
    </button>
</div>

