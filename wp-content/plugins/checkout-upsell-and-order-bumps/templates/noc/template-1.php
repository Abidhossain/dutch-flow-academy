<?php
/**
 * Next order coupon template 1
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/noc/template-1.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($data)) return;
?>

<div class="cuw-noc cuw-template" data-campaign_id="<?php echo esc_attr($data['campaign_id']); ?>"
     style="margin: 12px auto; border-radius: 5px; max-width: 512px; overflow: hidden; <?php echo esc_attr($data['styles']['template']); ?>">
    <div class="cuw-container" style="padding: 12px 12%;">
        <?php if (!empty($data['template']['title'])) : ?>
            <h3 class="cuw-template-title"
                style="margin: 0; padding: 8px 2px; text-align: center; <?php echo esc_attr($data['styles']['title']); ?>">
                <?php echo wp_kses($data['template']['title'], $data['allowed_html']); ?>
            </h3>
        <?php endif; ?>

        <?php if (!empty($data['template']['description'])) : ?>
            <p class="cuw-template-description"
               style="text-align: center; margin: 0; padding: 6px 4px; <?php echo esc_attr($data['styles']['description']); ?>">
                <?php echo wp_kses($data['template']['description'], $data['allowed_html']); ?>
            </p>
        <?php endif; ?>

        <div class="cuw-template-coupon"
             style="text-transform: uppercase; text-align:center; border-radius: 2px; font-weight: bold; margin: 8px auto; padding: 10px; <?php echo esc_attr($data['styles']['coupon']); ?>">
            <?php echo !empty($data['coupon']['code']) ? esc_html($data['coupon']['code']) : 'NOC-ABCDEF'; ?>
        </div>

        <div class="cuw-template-cta-section"
             style="text-align: center; margin-bottom: 4px; <?php echo esc_attr($data['styles']['cta']); ?>">
            <a href="<?php echo !empty($data['coupon']['url']) ? esc_url($data['coupon']['url']) : '#'; ?>"
               style="text-decoration: none; color: inherit; background: inherit; font-size: inherit;">
                <button type="button" class="cuw-button"
                        style="padding: 10px; width: 100%; border: none; border-radius: 2px; color: inherit; background: inherit; font-size: inherit; margin: 0;">
                    <span class="cuw-template-cta-text"><?php echo wp_kses($data['template']['cta_text'], $data['allowed_html']); ?></span>
                </button>
            </a>
        </div>
        <div class="cuw-template-coupon-message"
             style="<?php if ($data['template']['message'] == 'hide') echo 'display: none;' ?> margin: 0; padding: 2px; text-align: center; font-size: 90%; <?php echo esc_attr($data['styles']['description']); ?>">
            <?php echo !empty($data['coupon']['message']) ? wp_kses_post($data['coupon']['message']) : '{coupon_message}'; ?>
        </div>
    </div>
</div>