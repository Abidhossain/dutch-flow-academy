<?php
defined('ABSPATH') || exit;
if (CUW()->plugin->has_pro) {
    return;
}
?>

<div class="cuw-upgrade">
    <div class="mx-auto my-3 d-flex flex-column align-items-center" style="width: 200px; gap: 8px;">
        <p>
            <?php esc_html_e("To unlock this feature", 'checkout-upsell-woocommerce'); ?>
        </p>
        <a class="btn btn-primary text-center"
           href="<?php echo esc_url(CUW()->plugin->getUrl($medium ?? 'unlock_feature')); ?>"
           target="_blank">
            <?php esc_html_e("Upgrade to PRO", 'checkout-upsell-woocommerce'); ?>
        </a>
    </div>
</div>
