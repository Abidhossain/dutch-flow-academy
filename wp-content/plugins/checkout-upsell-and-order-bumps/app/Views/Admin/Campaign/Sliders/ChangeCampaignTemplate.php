<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$campaign_type = isset($campaign['type']) ? $campaign['type'] : '';
$campaign_type = !empty($campaign_type) ? $campaign_type : '';
?>

<div class="cuw-slider-header d-flex justify-content-between align-items-center mt-3" style="gap:8px;">
    <h4 class="cuw-slider-title"><?php esc_html_e("Choose Template", 'checkout-upsell-and-order-bumps'); ?></h4>
    <div>
        <button type="button" id="cuw-change-template-close" class="btn btn-outline-secondary" style="gap:6px;">
            <i class="cuw-icon-close-circle inherit-color"></i>
            <?php esc_html_e("Close", 'checkout-upsell-and-order-bumps'); ?>
        </button>
    </div>
</div>
<div id="templates" data-available="" class="row d-flex flex-column justify-content-around p-3 mb-3" style="gap: 12px;">
    <?php $templates_count = 0; ?>
    <?php foreach (\CUW\App\Helpers\Template::get($campaign_type) as $template) {
        echo '<div class="col-md-12 template-preview-card">';
        echo '<div class="template-preview" data-template="' . esc_attr($template['template']) . '">';
        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        echo \CUW\App\Helpers\Template::getPreviewHtml(['type' => $campaign_type, 'data' => [
            'template' => ['name' => $template['template']],
        ]]);
        // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';
        echo '<div class="template-name">' . esc_html($template['template']) . '</div>';
        echo '</div>';
    } ?>
</div>