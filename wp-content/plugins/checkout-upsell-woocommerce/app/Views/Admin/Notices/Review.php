<?php
defined('ABSPATH') || exit;
if (!isset($data)) {
    return;
}
?>
<div class="notice notice-info is-dismissible">
    <div style="display: flex; gap: 12px; align-items: center; padding: 12px 0;">
        <img src="<?php echo esc_url(CUW()->assets->getUrl("img/logo.png")); ?>" style="width: 78px; height: 78px;"/>
        <div>
            <p style="margin: 0; padding: 0; font-size: 14px;">
                <?php echo wp_kses_post(sprintf(__('Fantastic! You\'ve earned %1$s in the last %2$s days in upsells using our UpsellWP plugin! &#128640;', 'checkout-upsell-woocommerce'),
                    '<strong>' . $data['revenue'] . '</strong>', $data['days']
                )); ?>
                <br>
                <?php esc_html_e("Could you share the joy by leaving a 5-star review on the WordPress repository? Your story could inspire and guide fellow entrepreneurs towards their own success journey!", 'checkout-upsell-woocommerce'); ?>
            </p>
            <div style="display: flex; gap: 8px; margin-top: 6px;">
                <a class="button-primary" href="<?php echo esc_url($data['review_url']); ?>" target="_blank">
                    <span class="dashicons dashicons-yes-alt" style="vertical-align: sub; font-size: 16px;"></span>
                    <?php echo esc_html__("Ok, you deserve it", 'checkout-upsell-woocommerce'); ?>
                </a>
                <a class="button-secondary" style="border-color: #ddd; opacity: 0.9;"
                   href="<?php echo esc_url($data['later_url']); ?>">
                    <span class="dashicons dashicons-clock" style="vertical-align: sub; font-size: 16px;"></span>
                    <?php echo esc_html__("Nope, Maybe later", 'checkout-upsell-woocommerce'); ?>
                </a>
                <a class="button-secondary" style="border-color: #ddd; opacity: 0.9;"
                   href="<?php echo esc_url($data['done_url']); ?>">
                    <span class="dashicons dashicons-thumbs-up" style="vertical-align: sub; font-size: 16px;"></span>
                    <?php echo esc_html__("I already did", 'checkout-upsell-woocommerce'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
