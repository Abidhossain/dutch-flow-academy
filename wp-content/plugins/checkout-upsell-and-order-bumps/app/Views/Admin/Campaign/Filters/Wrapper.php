<?php
defined('ABSPATH') || exit;

$key = isset($key) ? (int)$key : '{key}';
$type = isset($type) ? $type : '{type}';
$name = isset($name) ? $name : '{name}';
$rtl = cuw()->wp->isRtl();
$relation = $relation ?? 'and';
?>

<div class="cuw-filter mt-3" data-id="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($type); ?>">
    <div class="filter-inputs" style="display: none;">
        <div class="filter-name text-secondary font-weight-medium mb-1 <?php echo $rtl ? 'ml-4' : ''; ?>"><?php echo esc_html($name); ?></div>
        <div class="filter-data w-100 d-flex flex-column" style="gap: 8px;">
            <input type="hidden" name="filters[<?php echo esc_attr($key); ?>][type]"
                   value="<?php echo esc_attr($type); ?>">
            <?php if (is_numeric($key) && !empty($filter) && !empty($filters) && !empty($filters[$type]['handler'])) {
                $filters[$type]['handler']->template(['key' => $key, 'filter' => $filter], true);
            } else {
                echo '{data}';
            } ?>
        </div>
    </div>
    <span class="filter-relation-wrapper">
        <span class="cuw-relation filter-relation relation-<?php echo esc_attr($relation) ?>"><?php echo !empty($show_relation) ? esc_html($relation) : '' ?></span>
        <span class="filter-count cuw-count text-uppercase"><?php echo esc_html__('Filter', 'checkout-upsell-and-order-bumps') . ' ' . esc_html(!empty($count) ? $count : ''); ?></span>
    </span>
    <div class="filter-row">
        <div class="d-flex align-items-center justify-content-between" style="gap:8px;">
            <div class="filter-text-wrapper">
                <i class="cuw-icon-box cuw-filter-icon text-dark"></i>
                <div class="filter-text"><span class="spinner-border spinner-border-sm"></span></div>
            </div>
            <div class="d-flex" style="gap: 8px">
                <div class="filter-edit"
                     style="<?php echo ($type != 'all_products') ? 'display: flex;' : 'display: none;' ?>">
                    <i class="cuw-icon-edit-note" title="Edit"></i>
                </div>
                <div class="filter-remove">
                    <i class="cuw-icon-delete inherit-color" title="Remove"></i>
                </div>
            </div>
        </div>
    </div>
</div>
