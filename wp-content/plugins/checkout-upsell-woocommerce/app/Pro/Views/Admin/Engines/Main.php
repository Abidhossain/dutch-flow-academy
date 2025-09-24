<?php defined('ABSPATH') || exit ?>

<?php

use CUW\App\Pro\Models\Engine as EngineModel;
use CUW\App\Pro\Helpers\Engine;

isset($page) || exit;
$rtl = \CUW\App\Helpers\WP::isRtl();
$page_url = $page->getUrl();

$default_args = $page->defaultQueryArgs();
$status = CUW()->input->get('status', $default_args['status'], 'query');
$type = CUW()->input->get('type', $default_args['type'], 'query');
$search = CUW()->input->get('search', $default_args['search'], 'query');
$like_args = ($search != $default_args['search']) ? ['title' => $search] : null;

$engines_ids = EngineModel::all(['status' => '', 'type' => $type, 'columns' => ['id'], 'hidden' => 0]);
$drafted_engine_ids = EngineModel::all(['status' => 'draft', 'type' => $type, 'columns' => ['id'], 'hidden' => 0]);
$active_engine_ids = EngineModel::all(['status' => 'active', 'type' => $type, 'columns' => ['id'], 'hidden' => 0]);

$engines_count = !empty($engines_ids) && is_array($engines_ids) ? count($engines_ids) : 0;
$drafted_engines_count = !empty($drafted_engine_ids) && is_array($drafted_engine_ids) ? count($drafted_engine_ids) : 0;
$active_engines_count = !empty($active_engine_ids) && is_array($active_engine_ids) ? count($active_engine_ids) : 0;

$engine_types = Engine::getTypes();
?>


<div id="cuw-engines">
    <div id="engines-list">
        <div class="d-flex flex-wrap title-container align-items-center justify-content-between">
            <h5><?php esc_html_e("Recommendation Engines", 'checkout-upsell-woocommerce'); ?></h5>
            <div>
                <button class="create-engine btn btn-primary px-3">
                    <i class="cuw-icon-add-circle text-white mx-1"></i>
                    <?php esc_html_e("Create New Engine", 'checkout-upsell-woocommerce'); ?>
                </button>
            </div>
        </div>
        <div class="d-flex p-3 flex-wrap align-items-center justify-content-between" id="basic-toolbar">
            <div class="d-flex" style="gap:8px;">
                <a class="dropdown-item campaign-sort <?php if ($status == '') echo 'active'; ?>"
                   href="<?php echo esc_url($page->getUrl(['status' => ''], true)); ?>">
                    <?php echo esc_html__(sprintf(__('All (%s)', 'checkout-upsell-woocommerce'), $engines_count)) ?>
                </a>
                <a class="dropdown-item campaign-sort <?php if ($status == 'active') echo 'active'; ?>"
                   href="<?php echo esc_url($page->getUrl(['status' => 'active'], true)); ?>">
                    <?php echo esc_html__(sprintf(__('Active (%s)', 'checkout-upsell-woocommerce'), $active_engines_count)) ?>
                </a>
                <a class="dropdown-item campaign-sort <?php if ($status == 'draft') echo 'active'; ?>"
                   href="<?php echo esc_url($page->getUrl(['status' => 'draft'], true)); ?>">
                    <?php echo esc_html__(sprintf(__('Draft (%s)', 'checkout-upsell-woocommerce'), $drafted_engines_count)) ?>
                </a>
            </div>
            <div class="d-flex flex-wrap" style="gap: 8px;">
                <div class="cuw-filter dropdown dropdown-right">
                    <button type="button" class="btn btn-data-toggle <?php if ($type != '') echo 'border-primary' ?>"
                            data-toggle="dropdown">
                        <i class="cuw-icon-filter px-1"></i>
                        <?php if ($type) {
                            $filters = [];
                            echo '<span class="text-dark">';
                            if ($type) {
                                $filters[] = esc_html(__("Type", 'checkout-upsell-woocommerce') . ": " . Engine::getTypes($type));
                            }
                            echo esc_html(implode(", ", $filters));
                            echo '</span>';
                        } else {
                            echo esc_html__("Filter", 'checkout-upsell-woocommerce');
                        } ?>
                    </button>
                    <div class="dropdown-menu" style="height: auto;">
                        <span class="dropdown-item text-dark font-weight-bold"><?php esc_html_e("Type", 'checkout-upsell-woocommerce'); ?></span>
                        <a href="<?php echo esc_url($page->getUrl(['type' => ''], true)); ?>"
                           class="dropdown-item <?php if ($type == '') echo 'active'; ?>"><?php esc_attr_e("All"); ?></a>
                        <?php foreach ($engine_types as $engine_type => $text) { ?>
                            <a href="<?php echo esc_url($page->getUrl(['type' => $engine_type], true)); ?>"
                               class="dropdown-item <?php if ($type == $engine_type) echo 'active'; ?>"><?php echo esc_html($text); ?></a>
                        <?php } ?>
                    </div>
                </div>
                <form class="cuw-search" method="get" action="">
                    <i class="cuw-icon-search mx-1"></i>
                    <input type="hidden" name="page" value="<?php echo esc_attr(CUW()->plugin->slug); ?>">
                    <input type="hidden" name="tab" value="<?php echo esc_attr($page->getCurrentTab()); ?>">
                    <input type="text" id="search-engine" name="search" value="<?php echo esc_attr($search); ?>"
                           class="form-control <?php if ($search) echo 'border-primary' ?>"
                           placeholder="<?php esc_attr_e("Search engine", 'checkout-upsell-woocommerce'); ?>">
                </form>
            </div>
        </div>
        <div class="d-none justify-content-between p-3 align-items-center" id="bulk-toolbar">
            <p class="">
                <span id="checks-count">0</span> <?php esc_html_e("selected", 'checkout-upsell-woocommerce'); ?>
            </p>
            <div>
                <button class="btn btn-outline-danger px-3" data-toggle="modal" data-target="#modal-delete"
                        data-bulk="1">
                    <i class="cuw-icon-delete inherit-color mx-1"></i> <?php esc_html_e("Delete All", 'checkout-upsell-woocommerce'); ?>
                </button>
            </div>
        </div>
        <?php CUW()->view('Pro/Admin/Engines/List', ['page' => $page]); ?>
    </div>
    <?php
    CUW()->view('Pro/Admin/Engines/Create', ['page' => $page]);
    CUW()->view('Pro/Admin/Engines/Delete', ['page' => $page]);
    ?>
</div>
