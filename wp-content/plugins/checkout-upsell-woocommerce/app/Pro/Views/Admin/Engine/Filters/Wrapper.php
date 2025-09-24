<?php defined('ABSPATH') || exit ?>

<?php
$key = isset($key) ? (int)$key : '{key}';
$type = isset($type) ? $type : '{type}';
$name = isset($name) ? $name : '{name}';
?>

<div class="cuw-engine-filter" data-id="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($type); ?>">
    <div class="filter-inputs" style="display: none;">
        <div class="filter-name d-none"><?php echo esc_html($name); ?></div>
        <div class="d-flex flex-column" style="gap: 8px;">
            <div class="filter-data w-100 d-flex flex-column" style="gap: 8px;">
                <input type="hidden" name="engine_filters[<?php echo esc_attr($key); ?>][type]"
                       value="<?php echo esc_attr($type); ?>">
                <?php if (is_numeric($key) && !empty($filter) && !empty($filters) && !empty($filters[$type]['handler'])) {
                    $filters[$type]['handler']->template(['key' => $key, 'filter' => $filter], true);
                } else {
                    echo '{data}';
                } ?>
            </div>
        </div>
    </div>
    <div class="filter-row">
        <div class="d-flex align-items-center justify-content-between" style="gap:8px;">
            <div class="d-flex align-items-center filter-text-wrapper"
                 style="background: #F2F4F7; border-radius: 8px; padding: 12px; gap:4px;">
                <i class="cuw-icon-box text-dark"></i>
                <div class="filter-description"><span class="spinner-border spinner-border-sm"></span></div>
            </div>

            <div class="d-flex" style="gap:8px;">
                <div class="filter-edit"
                     style="<?php echo ((!empty($filter) && count($filter) != 1)) ? 'display: flex;' : 'display: none;' ?>">
                    <i class="cuw-icon-edit-note inherit-color" title="Edit"></i>
                </div>
                <div class="filter-remove">
                    <i class="cuw-icon-delete text-danger" title="Remove"></i>
                </div>
            </div>
        </div>
    </div>
</div>
