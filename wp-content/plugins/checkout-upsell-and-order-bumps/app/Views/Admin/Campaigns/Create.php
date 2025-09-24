<?php
defined('ABSPATH') || exit;

use CUW\App\Helpers\Campaign;
use CUW\App\Controllers\Admin\Page;

$page_url = Page::getUrl();
?>

<div id="campaigns-create" class="collapse">
    <div class="d-flex title-container align-items-center" style="gap: 8px;">
        <button type="button" id="back-to-campaigns" class="btn  border border-gray-light p-2">
            <i class="cuw-icon-arrow-left mx-1"></i>
        </button>
        <h5 class=""><?php esc_html_e("Choose campaign type...", 'checkout-upsell-and-order-bumps'); ?></h5>
    </div>
    <div class="row m-0">
        <div class="col-md-2 campaign-filters p-0">
            <ul class="nav nav-tabs tabs-v mb-3">
                <li style="border-left:3px solid transparent;"
                    class="campaign-filter-type cursor-pointer nav-item active-tab-container " id="all">
                    <h6 class="campaign-filter-name p-3 text-primary" id="all">
                        <?php esc_html_e("All campaigns", 'checkout-upsell-and-order-bumps'); ?>
                    </h6>
                </li>
                <?php foreach (Campaign::getCategories() as $slug => $name) { ?>
                    <li style="border-left:3px solid transparent;"
                        class="campaign-filter-type cursor-pointer nav-item bg-white "
                        id="<?php echo esc_attr($slug) ?>">
                        <h6 class="campaign-filter-name p-3 text-secondary"
                            id="<?php echo esc_attr($slug) ?>"><?php echo esc_html($name) ?></h6>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="col-md-10 p-3">
            <h5 class="mt-1" id="campaign-sort-name">
                <?php esc_html_e("All Campaigns", 'checkout-upsell-and-order-bumps'); ?>
            </h5>
            <div class="my-4 d-flex flex-wrap justify-content-center w-100 " id="available-campaigns"
                 style="gap: 20px;">
                <?php foreach (Campaign::get() as $type => $campaign) {
                    $badge = Campaign::getBadge($type, true);
                    if ($campaign['is_single'] && Campaign::isCreated($type)) {
                        $badge = ['badge' => 'created', 'class' => 'success', 'text' => __("Created", 'checkout-upsell-and-order-bumps')];
                    }
                    $page_builder_available = !empty($campaign['page_builder']) && !empty($campaign['handler']);
                    ?>
                    <a class="card create-campaign-card text-decoration-none p-3 bg-white border border-gray-light mt-0 <?php if (!empty($page_builder_available)) echo 'trigger-ppu-modal'; ?> <?php echo !empty($campaign['categories']) ? esc_attr(implode(' ', $campaign['categories'])) : ''; ?> <?php if (empty($campaign['handler'])) echo 'cuw-pro'; ?>"
                       href="<?php echo esc_url(!empty($campaign['handler']) ? $page_url . "&tab=campaigns&create=new&type=" . $type : CUW()->plugin->getUrl($type)); ?>"
                        <?php if (!empty($page_builder_available)) echo 'data-toggle="modal" data-target="#modal-page-builder" data-type="' . esc_attr($type) . '"' ?>
                       style="max-width: 344px;display: flex;align-items: center;flex-direction: column <?php if (!empty($badge['badge']) && $badge['badge'] == 'created') echo 'pointer-events: none; opacity: 0.8'; ?>"
                       target="<?php if (empty($campaign['handler'])) echo '_blank'; ?>">
                        <?php if ($badge) {
                            echo '<div class="d-flex align-items-center cuw-badge position-absolute badge badge-' . esc_attr($badge['color']) . '" style="top:-12px; right:4px; gap: 4px;"> <i class="cuw-icon-'. esc_attr($badge['icon']) .' inherit-color" style="font-size:16px;"></i><span class=" ' . esc_attr($badge['class']) . '">' . esc_html($badge['text']) . '</span> </div>';
                        } ?>
                        <div class="text-center">
                            <img height="35px" width="35px" class="my-2"
                                 src="<?php echo esc_url(CUW()->assets->getUrl("img/" . str_replace('_', '-', $type) . ".svg")); ?>"/>
                            <h4><?php echo esc_html($campaign['title']); ?></h4>
                        </div>
                        <div class="card-body py-1 px-4 text-center">
                            <p class="card-text text-custom-secondary"
                               style="font-size: 14px;"><?php esc_html_e($campaign['description'], 'checkout-upsell-and-order-bumps'); //phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?></p>
                        </div>
                        <button type="button" id="create-campaign-button"
                                class="btn mt-2 btn-outline-primary px-4 py-2">
                            <?php echo (!empty($campaign['is_pro']) && !CUW()->plugin->has_pro) ? esc_html__("Unlock with PRO", 'checkout-upsell-and-order-bumps') : esc_html__("Create Campaign", 'checkout-upsell-and-order-bumps'); ?>
                        </button>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
