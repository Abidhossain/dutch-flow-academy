<?php defined('ABSPATH') || exit ?>

<?php
if (empty($filters)) {
    return;
}

$key = isset($key) ? (int)$key : '{key}';
$type = isset($type) ? $type : '{type}';
?>

<div class="cuw-added-engine-filter d-flex flex-column text-decoration-none p-0 bg-white border border-gray-light mt-0" data-id="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($type); ?>" style="border-radius: 8px;">
    <div class="d-flex justify-content-center align-items-center w-100 p-3">
        <div>
            <img src="<?php echo esc_url(\CUW\App\Helpers\Assets::getUrl('img-pro/' . esc_attr($filters[$type]['icon']) . '.svg')); ?>" alt="<?php echo esc_attr($type) ?>" >
        </div>
        <div class="card-body py-1 mx-3 d-flex flex-column" style="gap: 4px;">
            <h4>
                <?php echo esc_html($filters[$type]['name']); ?>
            </h4>
            <p class="card-text text-custom-secondary" style="font-size: 14px;">
                <?php echo esc_html($filters[$type]['description']); ?>
            </p>
        </div>
        <div>
            <div id="remove-existing-engine-filter" class="text-danger" data-value="<?php echo esc_attr($key); ?>">
                <i class="cuw-icon-close-circle inherit-color px-1"></i>
            </div>
        </div>
    </div>
    <div id="filter-section" class="d-flex flex-column" style="gap: 8px;">
        <div class="filter-data w-100 d-flex flex-column" style="gap: 8px;">
            <input type="hidden" name="engine_filters[<?php echo esc_attr($key); ?>][type]"
                   value="<?php echo esc_attr($type); ?>">
            <?php if (is_numeric($key) && !empty($filter) && !empty($filters[$type]['handler'])) {
                $filters[$type]['handler']->template(['key' => $key, 'filter' => $filter], true);
            } ?>
        </div>
    </div>
</div>