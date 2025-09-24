<?php
defined('ABSPATH') || exit;

if (!isset($campaign)) {
    return;
}

$page_url = \CUW\App\Controllers\Admin\Page::getUrl();
$template_id = CUW()->input->get('template', $campaign['data']['page']['template'] ?? '', 'query');
?>

<div id="cuw-post-purchase-offer-page" style="<?php if (empty($show)) echo 'display: none;' ?>">
    <div id="post-purchase-offer-header"
         class="cuw-offer-header d-flex align-items-center p-3 border-bottom border-gray-light" style="gap: 12px;"
         data-uuid="<?php if (!empty($show)) echo esc_attr(CUW\App\Helpers\Functions::generateUuid()); ?>" data-action="<?php if (!empty($show)) echo 'add'; ?>"
         data-parent_uuid="" data-offer_type="">
        <div class="d-flex align-items-center" style="gap: 8px;">
            <a href="<?php echo esc_url($page_url . "&tab=campaigns"); ?>" id="back-to-campaigns-list"
               class="btn border border-gray-light p-2 <?php if (!empty($campaign['id'])) echo 'd-none'; ?>">
                <i class="cuw-icon-close"></i>
            </a>
            <button type="button" id="back-to-campaign-page"
                    class="btn border border-gray-light p-2 <?php if (empty($campaign['id'])) echo 'd-none'; ?>">
                <i class="cuw-icon-arrow-left"></i>
            </button>
            <h4 class="cuw-page-title">
                <?php esc_html_e("Edit content", 'checkout-upsell-woocommerce'); ?>
                <span class="offer-index"></span>
            </h4>
        </div>
        <div class="d-flex <?php echo CUW()->wp->isRtl() ? 'mr-auto' : 'ml-auto'; ?>" style="gap: 10px;">
            <button type="button" id="post-purchase-offer-save" class="btn btn-primary">
                <i class="cuw-icon-tick-circle text-white mx-1"></i>
                <?php esc_html_e("Save Changes", 'checkout-upsell-woocommerce'); ?>
            </button>
        </div>
    </div>
    <div class="d-flex">
        <div id="cuw-offer-tab-section" class="col-md-3 px-0 border-right border-gray-light">
            <?php CUW()->view('Pro/Admin/Campaign/Offer/OfferOptionTabs', ['campaign' => $campaign]); ?>
        </div>
        <div class="col-md-9 template-preview p-5" style="height: 70vh; overflow-y: auto;">
            <div class="cuw-offer mx-auto" style="max-width: 800px;">
                <?php echo \CUW\App\Pro\Modules\PostPurchase\Templates::getHtml($template_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>

