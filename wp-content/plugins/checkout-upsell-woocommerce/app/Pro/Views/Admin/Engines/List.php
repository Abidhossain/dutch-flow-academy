<?php defined('ABSPATH') || exit ?>

<?php

use CUW\App\Pro\Models\Engine as EngineModel;
use CUW\App\Pro\Models\CampaignEngine;
use CUW\App\Pro\Helpers\Engine;

isset($page) || exit;
$rtl = \CUW\App\Helpers\WP::isRtl();
$default_args = $page->defaultQueryArgs();
$enabled = CUW()->input->get('status', $default_args['status'], 'query');
$sort = CUW()->input->get('sort', $default_args['sort'], 'query');
$type = CUW()->input->get('type', $default_args['type'], 'query');
$page_no = (int)CUW()->input->get('page_no', $default_args['page_no'], 'query');
$order_by = CUW()->input->get('order_by', $default_args['order_by'], 'query');
$search = CUW()->input->get('search', $default_args['search'], 'query');
$like_args = ($search != $default_args['search']) ? ['title' => $search] : null;

$engines_per_page = CUW()->config->get('engines_per_page', '5');
$engines_per_page = (int)apply_filters('cuw_engines_per_page', $engines_per_page);
$total_engines_count = EngineModel::getCount(true);

$engines = EngineModel::all([
    'status' => $enabled,
    'type' => $type,
    'columns' => ['id', 'type', 'title', 'enabled', 'created_at'],
    'like' => $like_args,
    'limit' => $engines_per_page,
    'offset' => $page_no > 1 ? ($page_no - 1) * $engines_per_page : 0,
    'order_by' => $order_by,
    'sort' => $sort,
    'hidden' => 0,
]);
$page_url = $page->getUrl();
$engines_ids = EngineModel::all(['enabled' => $enabled, 'columns' => ['id'], 'hidden' => 0]);
$engines_count = !empty($engines_ids) && is_array($engines_ids) ? count($engines_ids) : 0;

$id_sort = ($order_by == 'id' && $sort == 'asc') ? 'desc' : 'asc';
?>


<?php if ($total_engines_count == 0) { ?>
    <div class="engine-create text-center d-flex justify-content-center vmh-50 align-items-center">
        <div class="my-5 py-5">
            <div class="mb-4">
                <img src="<?php echo esc_url(CUW()->assets->getUrl("img/start-create-campaign.png")); ?>"/>
            </div>
            <h5 class="mb-3"><?php esc_html_e("Start creating Engines!", 'checkout-upsell-woocommerce'); ?></h5>
            <div class="w-50 mx-auto">
                <p class="text-secondary mb-3"><?php esc_html_e("Create a recommendation engine to generate upsell products. Get started in a few clicks.", 'checkout-upsell-woocommerce'); ?></p>
                <button class="create-engine d-flex-center btn btn-primary text-center px-3 mx-auto">
                    <i class="cuw-icon-add-circle px-1 text-white"></i>
                    <?php esc_html_e("Create New Engine", 'checkout-upsell-woocommerce'); ?>
                </button>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="overflow-auto px-3">
        <table class="table table-hover table-borderless">
            <thead>
            <tr class="text-uppercase text-dark">
                <th style="width: 5%">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check-all" name="">
                        <label class="custom-control-label" for="check-all"></label>
                    </div>
                </th>
                <th style="width: 25%;" class="text-uppercase">
                    <a class="text-decoration-none d-flex align-items-center"
                       href="<?php echo esc_url($page->getUrl(['order_by' => 'id', 'sort' => $id_sort], true)); ?>">
                        <?php esc_html_e("Engines", 'checkout-upsell-woocommerce'); ?>
                        <?php if ($order_by == 'id') echo '<span class="cuw-icon-' . esc_attr($sort) . '"><i class="path1"></i><i class="path2"></i></span>'; ?>
                    </a>
                </th>
                <th style="width: 15%"
                    class="text-uppercase"><?php esc_html_e("Created on", 'checkout-upsell-woocommerce'); ?></th>
                <th style="width: 20%"
                    class="text-uppercase"><?php esc_html_e("Linked Campaigns", 'checkout-upsell-woocommerce'); ?></th>
                <th style="width: 15%"
                    class="text-uppercase cuw-action-status"><?php esc_html_e("Status", 'checkout-upsell-woocommerce'); ?></th>
                <th class="text-uppercase cuw-action-header"><?php esc_html_e("Actions", 'checkout-upsell-woocommerce'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($engines)) {
                foreach ($engines as $engine) {
                    $linked_ids = CampaignEngine::getCampaignIds($engine['id']);
                    ?>
                    <tr class="engine engine-<?php echo esc_attr($engine['id']); ?>"
                        data-title="<?php echo esc_attr($engine['title']); ?>"
                        data-linked_campaigns="<?php echo esc_attr(count($linked_ids)); ?>">
                        <td class="align-middle">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input check-single"
                                       id="check-<?php echo esc_attr($engine['id']); ?>"
                                       value="<?php echo esc_attr($engine['id']); ?>">
                                <label class="custom-control-label"
                                       for="check-<?php echo esc_attr($engine['id']); ?>"></label>
                            </div>
                        </td>
                        <td class="d-flex flex-column">
                            <a href="<?php echo esc_url($page_url . "&tab=engines&edit=" . $engine['id']); ?>"
                               class="d-block text-decoration-none text-dark ">
                                <?php echo esc_html($engine['title']); ?>
                            </a>
                            <?php $type = Engine::getType($engine, true);
                            if (!empty($type) && is_array($type)) { ?>
                                <span style="font-size: 12px;"
                                      class="badge-pill-blue-primary mt-2 px-2 w-max engine-type-badge"><?php echo esc_html($type['text']); ?></span>
                            <?php } ?>
                        </td>

                        <td class="align-middle">
                            <?php echo esc_html(\CUW\App\Helpers\WP::formatDate($engine['created_at'], 'date', true)); ?>
                        </td>
                        <td class="align-middle cursor-pointer"
                            title="<?php echo esc_attr(implode(', ', array_map(function ($id) {
                                return '#' . $id;
                            }, $linked_ids))); ?>">
                            <?php echo esc_html(count($linked_ids)); ?>
                        </td>
                        <td class="align-middle" style="<?php echo(count($linked_ids) > 0 ? 'opacity: 0.6;' : ''); ?>">
                            <div class="custom-control d-flex custom-switch custom-switch-md">
                                <input type="checkbox" name="enabled" value="1"
                                       class="engine-enable custom-control-input"
                                       data-id="<?php echo esc_attr($engine['id']); ?>"
                                       id="switch-<?php echo esc_attr($engine['id']); ?>" <?php if ($engine['enabled']) echo "checked"; ?> <?php echo(count($linked_ids) > 0 ? 'disabled' : ''); ?>>
                                <label class="custom-control-label pl-2"
                                       style="position: relative; top: -4px;"
                                       for="switch-<?php echo esc_attr($engine['id']); ?>"></label>
                                <div class="engine-status mx-1"
                                     style="<?php echo $rtl ? 'position:relative; left:-40px; top:4px' : ''; ?>"
                                     style="display: inline-block">
                                    <?php $status = Engine::getStatus($engine, true);
                                    if (!empty($status) && is_array($status)) { ?>
                                        <span class="p-2 status-<?php echo esc_attr($status['code']); ?>"><?php echo esc_html($status['text']); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="engine-action-block d-flex align-items-center" style="gap:8px;">
                                <?php $edit_url = $page_url . "&tab=engines" . ($page_no > 1 ? "&page_no=" . $page_no : '') . "&edit=" . $engine['id']; ?>
                                <a href="<?php echo esc_url($edit_url); ?>"
                                   class="btn btn-outline-secondary border border-gray-light py-2 px-3">
                                    <i class="cuw-icon-edit-note inherit-color mx-1"></i>
                                    <?php esc_html_e("Edit", 'checkout-upsell-woocommerce'); ?>
                                </a>
                                <a class="btn engine-list-delete py-2 px-3 border border-gray-light"
                                   data-id="<?php echo esc_attr($engine['id']); ?>" data-toggle="modal"
                                   data-target="#modal-delete">
                                    <i class="cuw-icon-delete inherit-color mx-1"></i>
                                    <?php esc_html_e("Delete", 'checkout-upsell-woocommerce'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr class="engine engine-empty">
                    <td colspan="8"
                        class="text-center p-4"><?php esc_html_e("No engines found", 'checkout-upsell-woocommerce'); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php if (!empty($engines) || $page_no > 1) { ?>
            <div class="pagination-block px-3 pb-3 d-flex align-items-center" style="justify-content: space-between;">
                <?php
                $pages_count = ceil($engines_count / $engines_per_page);
                $showing_from = (($page_no - 1) * $engines_per_page) + 1;
                $showing_to = (($page_no - 1) * $engines_per_page) + count($engines);
                ?>
                <?php if (!empty($engines)) { ?>
                    <p class="my-2">
                        <?php esc_html_e("Showing", 'checkout-upsell-woocommerce'); ?>
                        <strong>
                            <?php echo esc_html($showing_from == $showing_to ? $showing_to : $showing_from . ' ' . __("to", 'checkout-upsell-woocommerce') . ' ' . $showing_to); ?>
                        </strong>
                        <?php esc_html_e("of", 'checkout-upsell-woocommerce'); ?>
                        <strong><?php echo esc_html($engines_count); ?></strong>
                    </p>
                <?php } ?>
                <div class="d-flex align-items-center" style="gap:8px;" id="engine-list-block">
                    <select style="width: 60px;" class="form-control" id="engines-per-page" name="engines_per_page">
                        <option value="5" <?php if ($engines_per_page == '5') echo "selected"; ?>>5</option>
                        <option value="10" <?php if ($engines_per_page == '10') echo "selected"; ?>>10</option>
                        <option value="20" <?php if ($engines_per_page == '20') echo "selected"; ?>>20</option>
                        <option value="100" <?php if ($engines_per_page == '100') echo "selected"; ?>>100</option>
                    </select>
                    <ul class="pagination">
                        <li class="page-item <?php if ($page_no == 1) echo 'disabled'; ?>">
                            <a class="page-link"
                               href="<?php echo esc_url($page->getUrl(['page_no' => $page_no - 1], true)); ?>">
                                <i class="cuw-icon-<?php echo $rtl ? 'chevron-right' : 'chevron-left'; ?> text-dark"></i>
                            </a>
                        </li>
                        <?php for ($page_i = 1; $page_i <= $pages_count; $page_i++) {
                            if ($page_no - 3 < $page_i && $page_no + 3 > $page_i) { ?>
                                <li class="page-item <?php if ($page_i == $page_no) echo 'active'; ?>">
                                    <a class="page-link"
                                       href="<?php echo esc_url($page->getUrl(['page_no' => $page_i], true)); ?>">
                                        <?php echo esc_html($page_i); ?>
                                    </a>
                                </li>
                            <?php }
                        } ?>
                        <li class="page-item <?php if ($page_no == $pages_count) echo 'disabled'; ?>">
                            <a class="page-link"
                               href="<?php echo esc_url($page->getUrl(['page_no' => $page_no + 1], true)); ?>">
                                <i class="cuw-icon-<?php echo $rtl ? 'chevron-left' : 'chevron-right'; ?> text-dark"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>

<?php } ?>
