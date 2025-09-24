<?php defined('ABSPATH') || exit ?>

<?php
    $key = isset($key) ? (int)$key : '{key}';
    $type = isset($type) ? $type : '{type}';
    $name = isset($name) ? $name : '{name}';
    $selected_amplifier = isset($amplifier) ? $amplifier : [];
    $amplifiers = \CUW\App\Pro\Helpers\Engine::getAmplifiers();
?>

<div id="amplifier-type" class="d-flex flex-column" style="gap: 10px;">
    <?php foreach ($amplifiers as $slug => $amplifier) { ?>
        <div id="<?php echo 'engine-amplifier-' . esc_attr($slug); ?>" class="cuw-engine-amplifier-section flex-column w-100 text-decoration-none p-0 bg-white border border-gray-light mt-0 mx-1 <?php if ($slug != $type) echo 'd-none'; ?>"
             data-id="<?php echo esc_attr($slug); ?>" style="border-radius: 8px;">
            <div class="d-flex justify-content-center align-items-center w-100 p-3">
                <div class="text-center">
                    <img src="<?php echo esc_url(\CUW\App\Helpers\Assets::getUrl('img-pro/' . esc_attr($amplifier['icon']) . '.svg')); ?>" alt="<?php echo esc_attr($slug) ?>" >
                </div>
                <div class="card-body py-1 mx-3 d-flex flex-column" style="gap: 4px;">
                    <h4>
                        <?php echo esc_html($amplifier['name']); ?>
                    </h4>
                    <p class="card-text text-custom-secondary" style="font-size: 14px;">
                        <?php echo esc_html($amplifier['description']); ?>
                    </p>
                </div>
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="add-engine-amplifier" <?php if ($slug == $type) echo 'checked'; ?>>
                        <label class="form-check-label" for="add-engine-amplifier">
                        </label>
                    </div>
                </div>
            </div>
            <div id="amplifier-section">
                <?php
                if ($slug == $type) {
                    CUW()->view('Pro/Admin/Engine/Amplifiers/Wrapper', ['key' => $key, 'name' => $name, 'type' => $type, 'amplifier' => $selected_amplifier, 'amplifiers' => $amplifiers]);
                }
                ?>
            </div>
        </div>
    <?php } ?>
</div>