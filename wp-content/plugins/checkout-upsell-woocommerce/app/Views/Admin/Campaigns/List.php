<?php
defined('ABSPATH') || exit;

use CUW\App\Helpers\Campaign;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Controllers\Admin\Page;

$rtl = CUW()->wp->isRtl();
$default_args = Page::defaultQueryArgs();
$page_no = (int)CUW()->input->get('page_no', $default_args['page_no'], 'query');
$order_by = CUW()->input->get('order_by', $default_args['order_by'], 'query');
$sort = CUW()->input->get('sort', $default_args['sort'], 'query');
$page_url = Page::getUrl();

$campaigns_per_page = CUW()->plugin->has_pro ? CUW()->config->get('campaigns_per_page', '5') : 5;
$campaigns_per_page = (int)apply_filters('cuw_campaigns_per_page', $campaigns_per_page);
$total_campaigns_count = CampaignModel::getCount();

$status = CUW()->input->get('status', $default_args['status'], 'query');
$type = CUW()->input->get('type', $default_args['type'], 'query');
$search = CUW()->input->get('search', $default_args['search'], 'query');
$like_args = ($search != $default_args['search']) ? ['title' => $search] : null;
$campaigns = CampaignModel::all([
    'status' => $status,
    'type' => $type,
    'columns' => ['id', 'type', 'title', 'enabled', 'priority', 'created_at', 'start_on', 'end_on'],
    'like' => $like_args,
    'limit' => $campaigns_per_page,
    'offset' => $page_no > 1 ? ($page_no - 1) * $campaigns_per_page : 0,
    'order_by' => $order_by,
    'sort' => $sort,
]);
$campaigns_ids = CampaignModel::all(['status' => $status, 'type' => $type, 'columns' => ['id'], 'like' => $like_args]);
$campaigns_count = !empty($campaigns_ids) && is_array($campaigns_ids) ? count($campaigns_ids) : 0;

$future_id_sort = ($order_by == 'id' && $sort == 'asc') ? 'desc' : 'asc';
$future_priority_sort = ($order_by == 'priority' && $sort == 'asc') ? 'desc' : 'asc';
?>

<?php if ($total_campaigns_count == 0) { ?>
    <div class="campaign-create text-center d-flex justify-content-center vmh-50 align-items-center">
        <div class="my-5 py-5">
            <div class="mb-4">
                <img src="<?php echo esc_url(CUW()->assets->getUrl("img/start-create-campaign.png")); ?>"/>
            </div>
            <h5 class="mb-3"><?php esc_html_e("Start creating campaigns!", 'checkout-upsell-woocommerce'); ?></h5>
            <div class="w-50 mx-auto">
                <p class="text-secondary mb-3"><?php esc_html_e("Create an upsell campaign to boost the average order value. Get started in a few clicks.", 'checkout-upsell-woocommerce'); ?></p>
                <button class="create-campaign btn btn-primary mx-auto">
                    <i class="cuw-icon-add-circle px-1 text-white"></i>
                    <span class="mx-auto"><?php esc_html_e("Create New Campaign", 'checkout-upsell-woocommerce'); ?></span>
                </button>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="overflow-auto px-3 ">
        <table class="table table-hover m-0 table-borderless">
            <thead>
            <tr class="text-uppercase text-dark">
                <th style="width: 2%; vertical-align: middle;">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check-all" name="">
                        <label class="custom-control-label" for="check-all"></label>
                    </div>
                </th>
                <th style="width: 25%;" class="text-uppercase">
                    <a class="text-decoration-none d-flex align-items-center"
                       href="<?php echo esc_url(Page::getUrl(['order_by' => 'id', 'sort' => $future_id_sort], true)); ?>">
                        <?php esc_html_e("Campaigns", 'checkout-upsell-woocommerce'); ?>
                        <?php if ($order_by == 'id') echo '<span class="cuw-icon-' . esc_attr($sort) . '"><i class="path1"></i><i class="path2" style="vertical-align: inherit"></i></span>'; ?>
                    </a>
                </th>
                <th style="width: 10%; vertical-align: middle"
                    class="text-uppercase"><?php esc_html_e("Views", 'checkout-upsell-woocommerce'); ?></th>
                <th style="width: 10%; vertical-align: middle"
                    class="text-uppercase"><?php esc_html_e("Revenue", 'checkout-upsell-woocommerce'); ?></th>
                <th style="width: 15%; vertical-align: middle"
                    class="text-uppercase"><?php esc_html_e("Created on", 'checkout-upsell-woocommerce'); ?></th>
                <th style="width: 10%; vertical-align: middle" class="text-uppercase">
                    <a class="text-decoration-none d-flex align-items-center"
                       href="<?php echo esc_url(Page::getUrl(['order_by' => 'priority', 'sort' => $future_priority_sort], true)); ?>">
                        <?php esc_html_e("Priority", 'checkout-upsell-woocommerce'); ?>
                        <?php if ($order_by == 'priority') echo '<span class="cuw-icon-' . esc_attr($sort) . '"><i class="path1"></i><i class="path2"></i></span>'; ?>
                    </a>
                </th>
                <th style="width: 10%; vertical-align: middle"
                    class="text-uppercase cuw-action-status"><?php esc_html_e("Status", 'checkout-upsell-woocommerce'); ?></th>
                <th class="text-uppercase cuw-action-header"><?php esc_html_e("Actions", 'checkout-upsell-woocommerce'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($campaigns)) {
                foreach ($campaigns as $campaign) { ?>
                    <tr class="campaign campaign-<?php echo esc_attr($campaign['id']); ?>"
                        data-title="<?php echo esc_attr($campaign['title']); ?>">
                        <td class="align-middle">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input check-single  " style=""
                                       id="check-<?php echo esc_attr($campaign['id']); ?>"
                                       value="<?php echo esc_attr($campaign['id']); ?>">
                                <label class="custom-control-label"
                                       for="check-<?php echo esc_attr($campaign['id']); ?>"></label>
                            </div>
                        </td>
                        <td class="d-flex flex-column">
                            <a href="<?php echo esc_url($page_url . "&tab=campaigns&edit=" . $campaign['id']); ?>"
                               class="d-block text-decoration-none text-dark">
                                <?php echo esc_html($campaign['title']); ?>
                            </a>
                            <?php $type = Campaign::getType($campaign, true);
                            if (!empty($type) && is_array($type)) { ?>
                                <span style="font-size: 12px;"
                                      class="badge-pill-blue-primary w-max px-2 mt-2 campaign-type-badge"><?php echo esc_html($type['text']); ?></span>
                            <?php } ?>
                        </td>

                        <td class="align-middle"><?php echo in_array($campaign['type'], ['checkout_upsells', 'cart_upsells', 'post_purchase'])
                                ? esc_html(CampaignModel::getTotalViews($campaign['id']))
                                : esc_html__("N/A", 'checkout-upsell-woocommerce'); ?>
                        </td>
                        <td class="align-middle"><?php echo CUW()->wc->formatPrice(CampaignModel::getRevenue($campaign['id'])); // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                        <td class="align-middle">
                            <?php echo esc_html(CUW()->wp->formatDate($campaign['created_at'], 'date', true)); ?>
                        </td>
                        <td class="align-middle"><?php echo esc_html($campaign['priority']); ?></td>
                        <td class="align-middle">
                            <div class="custom-control d-flex custom-switch custom-switch-md">
                                <input type="checkbox" style="" name="enabled" value="1"
                                       class="campaign-enable custom-control-input"
                                       data-id="<?php echo esc_attr($campaign['id']); ?>"
                                       id="switch-<?php echo esc_attr($campaign['id']); ?>" <?php if ($campaign['enabled']) echo "checked"; ?>>
                                <label style="position: relative; top: -4px;" class="custom-control-label pl-2"
                                       for="switch-<?php echo esc_attr($campaign['id']); ?>"></label>
                                <div class="campaign-status mx-1"
                                     style="<?php echo $rtl ? 'position:relative; left:-40px; top:4px' : ''; ?>"
                                     style="display: inline-block">
                                    <?php $status = Campaign::getStatus($campaign, true);
                                    if (!empty($status) && is_array($status)) { ?>
                                        <span class="p-2 status-<?php echo esc_attr($status['code']); ?>"><?php echo esc_html($status['text']); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="campaign-action-block d-flex align-items-center" style="gap:8px;">
                                <?php $edit_url = $page_url . "&tab=campaigns" . ($page_no > 1 ? "&page_no=" . $page_no : '') . "&edit=" . $campaign['id']; ?>
                                <a href="<?php echo esc_url($edit_url); ?>"
                                   class="btn border border-gray-light btn-outline-secondary p-2">
                                    <i class="cuw-icon-edit-note inherit-color px-1"></i>
                                    <?php esc_html_e("Edit", 'checkout-upsell-woocommerce'); ?>
                                </a>
                                <a href="<?php echo esc_url($page_url . "&tab=reports&campaign_id=" . $campaign['id']); ?>"
                                   class="btn btn-outline-secondary border border-gray-light p-2 "
                                   title="<?php echo esc_attr__('Reports', 'checkout-upsell-woocommerce'); ?>">
                                    <i class="cuw-icon-analytics inherit-color px-1"></i>
                                </a>
                                <div class="dropdown d-inline-block">
                                    <button type="button" class="btn btn-data-toggle border border-gray-light p-2"
                                            data-toggle="dropdown">
                                        <i class="cuw-icon-more px-1"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <?php if (CUW()->plugin->has_pro || $campaigns_count < 5) { ?>
                                            <a class="dropdown-item campaign-duplicate d-flex align-items-center"
                                               data-id="<?php echo esc_attr($campaign['id']); ?>">
                                                <i class="cuw-icon-copy px-1"></i>
                                                <?php esc_html_e("Duplicate", 'checkout-upsell-woocommerce'); ?>
                                            </a>
                                        <?php } ?>
                                        <a class="dropdown-item delete-icon-container d-flex align-items-center"
                                           data-id="<?php echo esc_attr($campaign['id']); ?>"
                                           data-toggle="modal" data-target="#modal-delete">
                                            <i class="cuw-icon-delete  px-1"></i>
                                            <?php esc_html_e("Delete", 'checkout-upsell-woocommerce'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr class="campaign campaign-empty">
                    <td colspan="8"
                        class="text-center p-4"><?php esc_html_e("No campaigns found", 'checkout-upsell-woocommerce'); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if (!empty($campaigns) || $page_no > 1) { ?>
        <div class="pagination-block px-3 pb-3 d-flex align-items-center" style="justify-content: space-between;">
            <?php if (CUW()->plugin->has_pro) {
                $pages_count = ceil($campaigns_count / $campaigns_per_page);
                $showing_from = (($page_no - 1) * $campaigns_per_page) + 1;
                $showing_to = (($page_no - 1) * $campaigns_per_page) + count($campaigns);
                ?>
                <?php if (!empty($campaigns)) { ?>
                    <p class="my-2">
                        <?php esc_html_e("Showing", 'checkout-upsell-woocommerce'); ?>
                        <strong>
                            <?php echo esc_html($showing_from == $showing_to ? $showing_to : $showing_from . ' ' . __("to", 'checkout-upsell-woocommerce') . ' ' . $showing_to); ?>
                        </strong>
                        <?php esc_html_e("of", 'checkout-upsell-woocommerce'); ?>
                        <strong><?php echo esc_html($campaigns_count); ?></strong>
                    </p>
                <?php } ?>
                <div class="d-flex align-items-center" style="gap:8px;" id="campaign-list-block">
                    <select style="width: 60px;" class="form-control" id="campaigns-per-page" name="campaigns_per_page">
                        <option value="5" <?php if ($campaigns_per_page == '5') echo "selected"; ?>>5</option>
                        <option value="10" <?php if ($campaigns_per_page == '10') echo "selected"; ?>>10</option>
                        <option value="20" <?php if ($campaigns_per_page == '20') echo "selected"; ?>>20</option>
                        <option value="100" <?php if ($campaigns_per_page == '100') echo "selected"; ?>>100</option>
                    </select>
                    <ul class="pagination">
                        <li class="page-item <?php if ($page_no == 1) echo 'disabled'; ?>">
                            <a class="page-link"
                               href="<?php echo esc_url(Page::getUrl(['page_no' => $page_no - 1], true)); ?>">
                                <i class="cuw-icon-<?php echo $rtl ? 'chevron-right' : 'chevron-left'; ?> text-dark"></i>
                            </a>
                        </li>
                        <?php for ($page_i = 1; $page_i <= $pages_count; $page_i++) {
                            if ($page_no - 3 < $page_i && $page_no + 3 > $page_i) { ?>
                                <li class="page-item <?php if ($page_i == $page_no) echo 'active'; ?>">
                                    <a class="page-link"
                                       href="<?php echo esc_url(Page::getUrl(['page_no' => $page_i], true)); ?>">
                                        <?php echo esc_html($page_i); ?>
                                    </a>
                                </li>
                            <?php }
                        } ?>
                        <li class="page-item <?php if ($page_no == $pages_count) echo 'disabled'; ?>">
                            <a class="page-link"
                               href="<?php echo esc_url(Page::getUrl(['page_no' => $page_no + 1], true)); ?>">
                                <i class="cuw-icon-<?php echo $rtl ? 'chevron-left' : 'chevron-right'; ?> text-dark"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php } else { ?>
                <p class="my-2">
                    <strong><?php echo esc_html(count($campaigns)); ?> </strong>
                    <?php esc_html_e("out of", 'checkout-upsell-woocommerce'); ?>
                    <strong>5</strong>
                </p>
                <p class="my-2 ml-auto">
                    <span><?php esc_html_e("To create more campaigns", 'checkout-upsell-woocommerce'); ?></span> –
                    <a class="text-decoration-none font-weight-bold"
                       href="<?php echo esc_url(CUW()->plugin->getUrl('upgrade')); ?>" target="_blank">
                        <?php esc_html_e("Upgrade to PRO", 'checkout-upsell-woocommerce'); ?>
                    </a>
                </p>
            <?php } ?>
        </div>
    <?php } ?>
<?php } ?>
