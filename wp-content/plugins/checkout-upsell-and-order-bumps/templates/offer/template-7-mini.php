<?php
/**
 * Offer template 7 (mini)
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/offer/template-7-mini.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer)) return;
$disable_cta = empty($offer['cart_item_key']) && !empty($offer['product']['is_variable']) && empty($offer['product']['default_variant']);
?>

<div class="cuw-offer <?php echo !empty($offer['cart_item_key']) ? 'cuw-offer-added' : ''; ?>"
     data-id="<?php echo esc_attr($offer['id']); ?>"
     data-discount="<?php echo esc_attr($offer['discount']['text']); ?>"
     data-cart_item_key="<?php echo esc_attr($offer['cart_item_key'] ?? ''); ?>"
     style="margin: 6px 0;">
    <div class="cuw-container"
         style="display: flex; flex-direction: column; justify-content: center; max-width: 640px; margin: 0 auto; <?php echo esc_attr($offer['styles']['template']); ?>">
        <div class="cuw-product-section"
             style="display: flex; gap: 10px; margin: 8px; align-items: center; line-height: 1.4;">
            <div class="cuw-product-image" style="min-width: 32px; width: 72px; margin-bottom: auto;">
                <?php if (!empty($offer['product']['default_variant']['image'])) {
                    echo wp_kses_post($offer['product']['default_variant']['image']);
                } else {
                    echo wp_kses_post($offer['product']['image']);
                } ?>
            </div>
            <div style="display: flex; flex-direction: column;">
                <div class="cuw-product-title" style="color: #454d55;">
                    <?php echo wp_kses_post($offer['product']['title']); ?>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="cuw-product-price" style="margin: 8px 0; font-size: 14px;">
                        <?php if (!empty($offer['product']['default_variant']['price_html'])) {
                            echo wp_kses_post($offer['product']['default_variant']['price_html']);
                        } else {
                            echo wp_kses_post($offer['product']['price_html']);
                        } ?>
                    </div>
                    <div class="cuw-offer-title"
                         style="padding: 2px 8px; text-align: center; border-radius: 12px; <?php echo esc_attr($offer['styles']['title']); ?>">
                        <?php echo wp_kses($offer['template']['title'], $offer['allowed_html']); ?>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 4px;">
                    <div class="cuw-product-quantity"
                         style="display: flex; color: gray; zoom: 90%; <?php if (!empty($offer['cart_item_key'])) echo 'pointer-events: none; opacity: 0.8;'; ?>">
                        <?php echo apply_filters('cuw_offer_template_product_quantity', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($offer['product']['variants'])) { ?>
            <div class="cuw-product-variants"
                 style="zoom: 90%; margin: 0 8px 8px 8px; <?php if (!empty($offer['cart_item_key'])) echo 'pointer-events: none; opacity: 0.8;'; ?>">
                <?php echo apply_filters('cuw_offer_template_product_variants', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        <?php } ?>
        <?php if (!empty($offer['template']['description'])) : ?>
            <div class="cuw-offer-description"
                 style="text-align: justify; padding: 8px; margin-bottom: 0; display: none; <?php echo esc_attr($offer['styles']['description']); ?>">
                <?php echo wp_kses($offer['template']['description'], $offer['allowed_html']); ?>
            </div>
        <?php endif; ?>
        <div class="cuw-offer-cta-section"
             style="<?php echo esc_attr($offer['styles']['cta']); ?>">
            <label style="margin: 0; padding: 6px 8px; cursor: pointer; font-size: inherit; color: inherit; display: flex; align-items: center; gap: 4px; justify-content: center;">
                <input type="checkbox"
                       class="cuw-checkbox" <?php if (!empty($offer['cart_item_key'])) echo 'checked'; ?>
                       style="zoom: 1.2;"
                    <?php if ($disable_cta) echo 'disabled'; ?>>
                <span class="cuw-offer-cta-text"
                      style="font-size: inherit; <?php if (!empty($offer['cart_item_key'])) echo 'display: none;' ?>">
                    <?php echo wp_kses($offer['template']['cta_text'], $offer['allowed_html']); ?>
                </span>
                <span class="cuw-offer-added-text"
                      style="font-size: inherit; <?php if (empty($offer['cart_item_key'])) echo 'display: none;' ?>">
                    <?php esc_html_e('Added', 'checkout-upsell-and-order-bumps'); ?>
                </span>
            </label>
        </div>
    </div>
</div>
