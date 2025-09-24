<?php defined('ABSPATH') || exit ?>

<?php

use CUW\App\Pro\Models\Engine;
use CUW\App\Pro\Helpers\Engine as EngineHelper;

if (!isset($page)) {
    return;
}

$id = 0;
$linked_ids = [];
if (CUW()->input->get('create', '', 'query') == 'new') {
    $form = 'create';
    $type = CUW()->input->get('type', '', 'query');
    $engine = [
        'id' => $id,
        'type' => $type,
        'title' => '',
        'enabled' => 1,
        'filters' => [],
        'amplifiers' => [floor(microtime(true) * 1000) => ['type' => 'default']],
    ];
} elseif (is_numeric($id = CUW()->input->get('edit', '', 'query'))) {
    $form = 'edit';
    $engine = Engine::get($id, null, true);
    $status = EngineHelper::getStatus($engine, true);
    $linked_ids = \CUW\App\Pro\Models\CampaignEngine::getCampaignIds($engine['id']);
} else {
    echo '<p class="text-center mt-5">Unable to perform this action!</p>';
    return;
}

$engine_type = $engine['type'] ?? '';
$engine_text = EngineHelper::getTypes($engine['type']);
?>

<div id="cuw-engine" data-type="<?php echo esc_attr($engine_type); ?>" data-action="<?php echo esc_attr($form); ?>">
    <form id="engine-form" action="" method="POST" enctype="multipart/form-data">
        <div id="edit-engine-name-block" class="title-container" style="display: none">
            <div class="d-flex flex-row justify-content-between" style="gap:8px;">
                <input type="text" class="form-control" id="title" name="title"
                       value="<?php echo esc_attr($engine['title']); ?>"
                       placeholder="<?php esc_attr_e("Engine name", 'checkout-upsell-woocommerce'); ?>"
                       maxlength="255"
                       style="font-size: 16px;">
                <button type="button" id="engine-name-save" class="btn btn-primary px-3">
                    <i class="cuw-icon-tick-circle text-white mx-1"></i>
                    <?php esc_html_e("Save", 'checkout-upsell-woocommerce'); ?>
                </button>
                <button type="button" id="engine-name-close" class="btn btn-outline-secondary px-3">
                    <i class="cuw-icon-close-circle inherit-color mx-1"></i>
                </button>
            </div>
        </div>
        <div id="header">
            <div class="row title-container m-0">
                <div id="engine-header" class="col-md-12 p-0 d-flex align-items-center justify-content-between">
                    <div class="cuw-title-container">
                        <button type="button" id="engine-close" class="btn border border-gray-light">
                            <i class="cuw-icon-close"></i>
                        </button>
                        <?php if ($form !== 'create') { ?>
                            <div id="engine-name-settings" class="d-flex align-items-center">
                                <h5 class="" id="engine-name">
                                    <?php echo $form == 'create'
                                        ? esc_html__("New Engine", 'checkout-upsell-woocommerce')
                                        : esc_html($engine['title']);
                                    ?>
                                </h5>
                                <i id="edit-engine-name" class="cuw-icon-edit-simple  mx-1"></i>
                                <span class=" mx-2 badge-pill-blue-primary px-2 py-1 rounded engine-badge"><?php echo esc_html($engine_text); ?></span>
                            </div>
                        <?php } else { ?>
                            <div class="d-flex align-items-center flex-fill">
                                <input type="text" class="form-control w-100" id="title" name="title"
                                       value="<?php echo !empty($engine['title']) ? esc_attr($engine['title']) : ''; ?>"
                                       placeholder="<?php esc_attr_e("Engine name", 'checkout-upsell-woocommerce'); ?>"
                                       maxlength="255"
                                       style="font-size: 16px;">
                            </div>
                            <span class=" mx-2 badge-pill-blue-primary px-2 py-1 rounded engine-badge"><?php echo esc_html($engine_text); ?></span>
                        <?php } ?>
                    </div>
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <div class="d-flex align-items-center"
                             style="gap:8px; <?php echo(count($linked_ids) > 0 ? 'opacity: 0.6;' : ''); ?>">
                            <div class="custom-control custom-switch custom-switch-md mb-1">
                                <input type="checkbox" name="enabled" value="1"
                                       class="engine-enable custom-control-input" data-id="engine-enabled"
                                       id="switch-engine-enable" <?php if ($engine['enabled'] == 1) echo "checked"; ?> <?php echo(count($linked_ids) > 0 ? 'disabled' : ''); ?>>
                                <label class="custom-control-label pl-2" for="switch-engine-enable"></label>
                                <?php if (count($linked_ids)) { ?>
                                    <input type="hidden" name="enabled"
                                           value="<?php echo $engine['enabled'] ? '1' : '0'; ?>"/>
                                <?php } ?>
                            </div>
                            <?php if (isset($status['code'])) { ?>
                                <div class="px-2">
                                    <span class="text-dark status-<?php echo esc_attr($status['code']); ?>"><?php echo esc_html($status['text']); ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <button type="button" id="engine-save" class="btn btn-outline-primary px-3">
                            <i class="cuw-icon-tick-circle inherit-color mx-1"></i>
                            <?php esc_html_e("Save", 'checkout-upsell-woocommerce'); ?>
                        </button>
                        <button type="button" id="engine-save-close" class="btn btn-primary px-3">
                            <i class="cuw-icon-save text-white mx-1"></i>
                            <?php esc_html_e("Save & Close", 'checkout-upsell-woocommerce'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="engine-id" name="id" value="<?php echo esc_attr($id); ?>">
        <input type="hidden" id="engine-type" name="type" value="<?php echo esc_attr($engine['type']); ?>">
        <div class=" row p-3">
            <div class="col-md-8">
                <div id="filter-accordion">
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'engine_filter',
                        'title' => __('Filters & Conditions', 'checkout-upsell-woocommerce'),
                        'icon' => 'filter',
                        'body' => CUW()->view('Pro/Admin/Engine/Components/Filters', ['engine' => $engine], false)
                    ]);
                    ?>
                </div>
                <div id="amplifier-accordion">
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'engine_amplifier',
                        'title' => __('Sorting Options', 'checkout-upsell-woocommerce'),
                        'icon' => 'sorting',
                        'body' => CUW()->view('Pro/Admin/Engine/Components/Amplifiers', ['engine' => $engine], false),
                        'expand' => $form == 'edit',
                    ]);
                    ?>
                </div>
            </div>
            <div class="col-md-4">
                <?php if ($form == 'edit') { ?>
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'link_campaign',
                        'title' => __('Linked Campaigns', 'checkout-upsell-woocommerce'),
                        'icon' => 'campaigns',
                        'view' => 'Pro/Admin/Engine/Components/LinkedCampaigns',
                        'data' => ['linked_ids' => $linked_ids, 'page' => $page],
                    ]);
                    ?>
                    <?php
                    CUW()->view('Admin/Components/Accordion', [
                        'id' => 'info',
                        'title' => __('Information', 'checkout-upsell-woocommerce'),
                        'icon' => 'info-circle',
                        'view' => 'Pro/Admin/Engine/Components/Information',
                        'data' => ['engine' => $engine],
                        'expand' => false,
                    ]);
                    ?>
                <?php } ?>
            </div>
        </div>
    </form>
    <?php
    CUW()->view('Admin/Components/Slider', [
        'id' => 'engine-filter',
        'width' => '50%',
        'body' => CUW()->view('Pro/Admin/Engine/Slider/AddFilter', compact('engine'), false)
    ]);
    ?>
</div>
