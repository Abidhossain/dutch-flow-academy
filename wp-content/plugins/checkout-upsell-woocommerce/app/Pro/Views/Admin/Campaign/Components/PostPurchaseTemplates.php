<?php
defined('ABSPATH') || exit;

if (!isset($campaign) || !isset($page_builder)) {
    return;
}
$templates = \CUW\App\Pro\Modules\PostPurchase\Templates::getTemplates($page_builder, true);
?>

<div id="cuw-ppu-templates">
    <div id="header">
        <div class="row title-container m-0">
            <div id="campaign-header" class="col-md-12 p-0 d-flex  align-items-center justify-content-between">
                <div class="cuw-title-container">
                    <button type="button" id="campaign-close" class="btn border border-gray-extra-light  px-2">
                        <i class="cuw-icon-close"></i>
                    </button>
                    <div style="gap: 6px" class="d-flex align-items-center">
                        <h5><?php esc_html_e("Choose templates", 'checkout-upsell-woocommerce'); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="post-purchase-templates" class="my-4 d-flex flex-wrap justify-content-center w-100" style="gap: 24px;">
        <?php foreach ($templates as $key => $template) { ?>
            <div class="card create-ppu-card text-decoration-none p-0 border border-gray-light mt-0 h-50">
                <div class="mx-2 mb-3 mt-4 px-2 preview-layout">
                    <div class="template-preview p-3 cuw-offer border border-gray-light rounded cuw-builder-<?php echo esc_attr($template['builder']) ?>" data-template="<?php echo esc_attr($template['name']) ?>">
                    <?php
                        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo \CUW\App\Pro\Modules\PostPurchase\Templates::getHtml($template, true);
                        // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center px-3 pb-3 pt-1">
                    <div class="template-name font-weight-semibold text-dark"><?php echo esc_html($template['name']); ?></div>
                        <div class="d-flex" style="gap: 12px;">
                            <a class="btn btn-outline-secondary" href="<?php echo add_query_arg('cuw_ppu_preview', '1', $template['url']) ?>" target="_blank">
                                <?php echo esc_html__('Live preview', 'checkout-upsell-woocommerce'); ?>
                                <i class="cuw-icon-external-link inherit-color mx-1" style="font-size: 14px;"></i>
                            </a>
                            <a class="btn btn-primary" href="<?php echo \CUW\App\Controllers\Admin\Page::getUrl(['template' => $key], true) ?>">
                                <?php echo esc_html__('Use this template', 'checkout-upsell-woocommerce'); ?>
                            </a>
                        </div>
                    </div>
            </div>
        <?php } ?>
    </div>
</div>