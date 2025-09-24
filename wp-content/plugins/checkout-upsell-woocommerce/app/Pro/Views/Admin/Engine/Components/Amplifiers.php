<?php defined('ABSPATH') || exit; ?>

<?php

use CUW\App\Pro\Helpers\Engine;

$amplifiers = isset($engine['amplifiers']) ? $engine['amplifiers'] : [];
$available_amplifiers = Engine::getAmplifiers();
?>

<div id="cuw-engine-amplifiers" class="p-3" style="max-height: 40vh; overflow-y: scroll; margin: 4px;">
    <label class="form-label font-weight-medium mt-n2 mb-2">
        <?php esc_html_e("Specify the sorting order for product recommendations", 'checkout-upsell-woocommerce'); ?>
    </label>

    <?php foreach ($amplifiers as $key => $amplifier) {
        if (empty($amplifier['type'])) {
            continue;
        }
        $type = $amplifier['type'];
        $name = isset($available_amplifiers[$type]['name']) ? $available_amplifiers[$type]['name'] : '';
        if (empty($name) || !isset($available_amplifiers[$type]['handler'])) {
            continue;
        }
        CUW()->view('Pro/Admin/Engine/Amplifiers/List', ['key' => $key, 'name' => $name, 'type' => $type, 'amplifier' => $amplifier]);
    } ?>
</div>

<div class="form-separator m-0"></div>

<div class="input-group flex-row justify-content-between p-3" style="gap: 8px;">
    <div>
        <button type="button" id="change-amplifier" class="btn btn-outline-primary">
            <i class="cuw-icon-rotate-right inherit-color px-1"></i>
            <?php esc_html_e("Change Sorting Option", 'checkout-upsell-woocommerce'); ?>
        </button>
    </div>
    <div>
        <button type="button" id="engine-save" class="btn btn-outline-primary px-3 d-none align-items-start">
            <?php esc_html_e("Save", 'checkout-upsell-woocommerce'); ?>
            <i class="cuw-icon-tick-circle inherit-color mx-1"></i>
        </button>
        <button type="button" class="btn btn-outline-primary move-to-template d-none">
            <?php esc_html_e("Next", 'checkout-upsell-woocommerce'); ?>
            <i class="cuw-icon-chevron-right inherit-color mx-1"></i>
        </button>
    </div>
</div>


