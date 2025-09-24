<?php
/**
 * Next order coupon template 3
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/noc/template-3-classic.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($data)) return;
?>

<div class="cuw-noc" data-campaign_id="<?php echo esc_attr($data['campaign_id']); ?>"
     style="max-width: 512px; overflow: hidden; margin: 12px auto;">
    <table>
        <tr>
            <td class="cuw-template" style="<?php echo esc_attr($data['styles']['template']); ?>">
                <table class="cuw-container" cellpadding="20" cellspacing="0"
                       style="width: 100%; border: 0 !important; margin: 0;">
                    <?php if (!empty($data['template']['title'])) : ?>
                        <tr>
                            <td valign="middle" class="cuw-template-title"
                                style="margin: 0; text-align: center; line-height: 1.2; padding: 8px !important; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; <?php echo esc_attr($data['styles']['title']); ?>">
                                <?php echo wp_kses($data['template']['title'], $data['allowed_html']); ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php if (!empty($data['template']['description'])) : ?>
                        <tr>
                            <td class="cuw-template-description"
                                style="text-align: center; margin: 0; line-height: 1.2; padding: 8px 8px 16px 8px !important; <?php echo esc_attr($data['styles']['description']); ?>">
                                <?php echo wp_kses($data['template']['description'], $data['allowed_html']); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="cuw-template-coupon"
                            style="text-transform: uppercase; text-align:center; border-radius: 2px; font-weight: bold; margin: 0; padding: 6px 12px !important; <?php echo esc_attr($data['styles']['coupon']); ?>">
                            <?php echo !empty($data['coupon']['code']) ? esc_html($data['coupon']['code']) : 'NOC-ABCDEF'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="cuw-template-cta-section"
                            style="width: 100% !important; text-align: center; margin: 0; padding: 6px 12px !important; <?php echo esc_attr($data['styles']['cta']); ?>">
                            <a href="<?php echo !empty($data['coupon']['url']) ? esc_url($data['coupon']['url']) : '#'; ?>"
                               style="text-decoration: none; width: 100% !important;">
                                <button type="button" class="cuw-button cuw-template-cta-element"
                                        style="width: 100%; border: none; border-radius: 2px; margin: 0; padding: 0 !important; min-height: 26px; <?php echo esc_attr($data['styles']['cta']); ?>">
                                    <span class="cuw-template-cta-text cuw-template-cta-element"
                                          style="<?php echo esc_attr($data['styles']['cta']); ?>">
                                        <?php echo wp_kses($data['template']['cta_text'], $data['allowed_html']); ?>
                                    </span>
                                </button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="cuw-template-coupon-message"
                            style="<?php if ($data['template']['message'] == 'hide') echo 'display: none;' ?> margin: 0; padding: 2px; text-align: center; font-size: 90%; <?php echo esc_attr($data['styles']['description']); ?>">
                            <?php echo !empty($data['coupon']['message']) ? wp_kses_post($data['coupon']['message']) : '{coupon_message}'; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
</div>
