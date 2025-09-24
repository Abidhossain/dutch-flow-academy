<?php
/**
 * Action template 1
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/action/simple-action-1.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($data)) return;
?>

<div class="cuw-action cuw-template" data-campaign_id="<?php echo esc_attr($data['campaign_id']); ?>"
     style="margin: 12px 0; <?php echo esc_attr($data['styles']['template']); ?>">
    <div class="cuw-template-cta-section" style="padding: 12px; <?php echo esc_attr($data['styles']['cta']); ?>">
        <label style="margin: 0; padding: 0; cursor: pointer; font-size: inherit; color: inherit;">
            <input type="checkbox" class="cuw-checkbox" <?php if (!empty($data['is_active'])) echo 'checked'; ?>>
            <span class="cuw-template-cta-text"
                  style="font-size: inherit; margin-left: 2px;"><?php echo wp_kses($data['template']['cta_text'], $data['allowed_html']); ?></span>
        </label>
    </div>
</div>