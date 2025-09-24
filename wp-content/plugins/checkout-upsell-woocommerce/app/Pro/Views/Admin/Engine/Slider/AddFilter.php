<?php defined('ABSPATH') || exit ?>

<?php
if (isset($campaign)) {
    $engine = [];
    if (!empty($campaign['data']['engine_id'])) {
        $engine = \CUW\App\Pro\Models\Engine::get($campaign['data']['engine_id']);
    }
}

if (empty($engine)) {
    return;
}
$added_filters = [];
foreach ($engine['filters'] as $key => $filter) {
    $added_filters[] = $filter['type'];
}
?>
<div class="mt-3 d-flex flex-column">
    <div class="d-flex justify-content-between align-items-center my-3 mx-2">
        <h4><?php esc_html_e("Filters", 'checkout-upsell-woocommerce'); ?></h4>
        <div>
            <button type="button" id="cuw-filter-close" class="btn btn-outline-secondary" style="gap: 6px;">
                <i class="cuw-icon-close-circle inherit-color"></i>
                <?php esc_html_e("Close", 'checkout-upsell-woocommerce'); ?>
            </button>
        </div>
    </div>
    <div id="cuw-engine-filters-list" style="overflow-y: scroll; height: 70vh; padding: 4px;">
        <?php CUW()->view('Pro/Admin/Engine/Filters/List', ['engine_type' => $engine['type'], 'added_filters' => $added_filters]); ?>
    </div>
    <div class="d-flex flex-row-reverse">
        <div class="mt-3">
            <button type="button" id="save-filter" class="btn btn-primary" disabled>
                <i class="cuw-icon-add-circle inherit-color px-1"></i>
                <?php esc_html_e("Save", 'checkout-upsell-woocommerce'); ?>
            </button>
        </div>
    </div>
</div>
