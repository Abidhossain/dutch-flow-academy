<?php defined('ABSPATH') || exit ?>

<?php
    $engine_type = isset($engine_type) ? $engine_type : '';
    $added_filters = isset($added_filters) ? $added_filters : [];
    $filters = \CUW\App\Pro\Helpers\Engine::getFilters($engine_type);
?>

<div id="filter-type" class="d-flex flex-column" style="gap: 10px;">
    <?php foreach ($filters as $key => $filter) { ?>
    <div id="<?php echo 'engine-filter-' . esc_attr($key); ?>" class="cuw-engine-filter-section flex-column text-decoration-none p-0 bg-white border border-gray-light mt-0 <?php echo !empty($added_filters) && in_array($key, $added_filters) ? 'd-none' : 'd-flex'; ?>"
         data-id="<?php echo esc_attr($key); ?>" style="border-radius: 8px;">
        <div class="filter-action-section d-flex justify-content-center align-items-center w-100 p-3">
            <div class="text-center">
                <img src="<?php echo esc_url(\CUW\App\Helpers\Assets::getUrl('img-pro/' . esc_attr($filter['icon']) . '.svg')); ?>" alt="<?php echo esc_attr($key) ?>" >
            </div>
            <div class="card-body py-1 mx-3 d-flex flex-column" style="gap: 4px;">
                <h4>
                    <?php echo esc_html($filter['name']); ?>
                </h4>
                <p class="card-text text-custom-secondary" style="font-size: 14px;">
                    <?php echo esc_html($filter['description']); ?>
                </p>
            </div>
            <div>
                <div id="add-engine-filter" class="text-primary" data-value="<?php echo esc_attr($key); ?>">
                    <i class="cuw-icon-add-circle inherit-color px-1"></i>
                </div>
                <div id="remove-engine-filter" class="text-primary d-none" data-value="<?php echo esc_attr($key); ?>">
                    <i class="cuw-icon-tick-filled-circle inherit-color px-1"></i>
                </div>
                <div id="remove-existing-engine-filter" class="text-danger d-none" data-value="<?php echo esc_attr($key); ?>">
                    <i class="cuw-icon-close-circle inherit-color px-1"></i>
                </div>
            </div>
        </div>
        <div id="filter-section"></div>
    </div>
    <?php } ?>
</div>