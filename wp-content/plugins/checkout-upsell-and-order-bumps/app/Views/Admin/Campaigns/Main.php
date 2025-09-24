<?php
defined('ABSPATH') || exit;

use CUW\App\Helpers\Campaign;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Controllers\Admin\Page;

$rtl = CUW()->wp->isRtl();

$default_args = Page::defaultQueryArgs();
$status = CUW()->input->get('status', $default_args['status'], 'query');
$type = CUW()->input->get('type', $default_args['type'], 'query');
$search = CUW()->input->get('search', $default_args['search'], 'query');
$like_args = ($search != $default_args['search']) ? ['title' => $search] : null;

$campaigns_ids = CampaignModel::all(['status' => '', 'type' => $type, 'columns' => ['id'], 'like' => $like_args]);
$active_campaign_ids = CampaignModel::all(['status' => 'active', 'type' => $type, 'columns' => ['id']]);
$drafted_campaign_ids = CampaignModel::all(['status' => 'draft', 'type' => $type, 'columns' => ['id']]);
$published_campaign_ids = CampaignModel::all(['status' => 'publish', 'type' => $type, 'columns' => ['id']]);

$campaigns_count = !empty($campaigns_ids) && is_array($campaigns_ids) ? count($campaigns_ids) : 0;
$active_campaigns_count = !empty($active_campaign_ids) && is_array($active_campaign_ids) ? count($active_campaign_ids) : 0;
$drafted_campaigns_count = !empty($drafted_campaign_ids) && is_array($drafted_campaign_ids) ? count($drafted_campaign_ids) : 0;
$published_campaigns_count = !empty($published_campaign_ids) && is_array($published_campaign_ids) ? count($published_campaign_ids) : 0;

$campaign_types = Campaign::getTypes('', true);

$page_url = Page::getUrl();
?>

<div id="cuw-campaigns">
    <div id="campaigns-list">

        <div class="d-flex  title-container align-items-center justify-content-between">
            <h5><?php esc_html_e("Campaigns", 'checkout-upsell-and-order-bumps'); ?>
            </h5>
            <div>
                <button class="create-campaign btn btn-primary d-flex align-items-center px-3">
                    <i class="cuw-icon-add-circle text-white mx-1"></i>
                    <?php esc_html_e("Create New Campaign", 'checkout-upsell-and-order-bumps'); ?>
                </button>
            </div>
        </div>

        <div class="d-flex p-3 flex-wrap align-items-center justify-content-between" id="basic-toolbar">
            <div class="d-flex" style="gap:8px;">
                <a class="dropdown-item  border-light campaign-sort <?php if ($status == '') echo 'active'; ?>"
                   href="<?php echo esc_url(Page::getUrl(['status' => ''], true)); ?>">
                    <?php
                        // translators: %s count.
                        echo esc_html(sprintf(__('All (%s)', 'checkout-upsell-and-order-bumps'), $campaigns_count))
                    ?>
                </a>
                <a class="dropdown-item campaign-sort <?php if ($status == 'active') echo 'active'; ?>"
                   href="<?php echo esc_url(Page::getUrl(['status' => 'active'], true)); ?>">
                    <?php
                        // translators: %s active count.
                        echo esc_html(sprintf(__('Active (%s)', 'checkout-upsell-and-order-bumps'), $active_campaigns_count))
                    ?>
                </a>
                <a class="dropdown-item campaign-sort <?php if ($status == 'draft') echo 'active'; ?>"
                   href="<?php echo esc_url(Page::getUrl(['status' => 'draft'], true)); ?>">
                    <?php
                        // translators: %s draft count.
                        echo esc_html(sprintf(__('Draft (%s)', 'checkout-upsell-and-order-bumps'), $drafted_campaigns_count))
                    ?>
                </a>
                <a class="dropdown-item campaign-sort <?php if ($status == 'publish') echo 'active'; ?>"
                   href="<?php echo esc_url(Page::getUrl(['status' => 'publish'], true)); ?>">
                    <?php
                        // translators: %s published campaign count.
                        echo esc_html(sprintf(__('Published (%s)', 'checkout-upsell-and-order-bumps'), $published_campaigns_count))
                    ?>
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
                                $filters[] = esc_html(__("Type", 'checkout-upsell-and-order-bumps') . ": " . Campaign::getTypes($type));
                            }
                            echo esc_html(implode(", ", $filters));
                            echo '</span>';
                        } else {
                            echo esc_html__("Filter", 'checkout-upsell-and-order-bumps');
                        } ?>
                    </button>
                    <div class="dropdown-menu">
                        <span class="dropdown-item text-dark font-weight-bold"><?php esc_html_e("Type", 'checkout-upsell-and-order-bumps'); ?></span>
                        <a href="<?php echo esc_url(Page::getUrl(['type' => ''], true)); ?>"
                           class="dropdown-item <?php if ($type == '') echo 'active'; ?>"><?php esc_attr_e("All", 'checkout-upsell-and-order-bumps'); ?></a>
                        <?php foreach ($campaign_types as $campaign_type => $text) { ?>
                            <a href="<?php echo esc_url(Page::getUrl(['type' => $campaign_type], true)); ?>"
                               class="dropdown-item <?php if ($type == $campaign_type) echo 'active'; ?>"><?php echo esc_html($text); ?></a>
                        <?php } ?>
                    </div>
                </div>
                <form class="cuw-search" method="get" action="">
                    <i class="cuw-icon-search mx-1"></i>
                    <input type="hidden" name="page" value="<?php echo esc_attr(CUW()->plugin->slug); ?>">
                    <input type="hidden" name="tab" value="<?php echo esc_attr(Page::getCurrentTab()); ?>">
                    <input type="text" id="search-campaign" name="search" value="<?php echo esc_attr($search); ?>"
                           class="form-control <?php if ($search) echo 'border-primary' ?>"
                           placeholder="<?php esc_attr_e("Search campaign", 'checkout-upsell-and-order-bumps'); ?>">
                </form>
            </div>
        </div>
        <div class="d-none justify-content-between p-3 align-items-center" id="bulk-toolbar">
            <p class=""><span id="checks-count">0</span> <?php esc_html_e("selected", 'checkout-upsell-and-order-bumps'); ?>
            </p>
            <div>
                <button class="btn btn-outline-danger px-3" data-toggle="modal" data-target="#modal-delete"
                        data-bulk="1">
                    <i class="cuw-icon-delete inherit-color mx-1"></i> <?php esc_html_e("Delete All", 'checkout-upsell-and-order-bumps'); ?>
                </button>
            </div>
        </div>
        <?php CUW()->view('Admin/Campaigns/List'); ?>
    </div>
    <?php
    CUW()->view('Admin/Campaigns/Create');
    CUW()->view('Admin/Campaigns/Delete');
    CUW()->view('Pro/Admin/Campaign/Modals/PageBuilder');
    ?>
</div>
