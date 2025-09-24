<?php
/**
 * Cart offer template 6 (mini)
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/offer/cart-template-6-mini.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer)) return;
$disable_cta = !empty($offer['product']['is_variable']) && empty($offer['product']['default_variant']);
?>

<div class="cuw-offer" data-id="<?php echo esc_attr($offer['id']); ?>"
     data-discount="<?php echo esc_attr($offer['discount']['text']); ?>">
    <div class="cuw-container"
         style="margin: 6px 0; display: flex; flex-wrap: wrap; padding: 6px; gap: 16px; <?php echo esc_attr($offer['styles']['template']); ?>">
        <div class="cuw-product-section"
             style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; flex: 1;">
            <div style="display: flex; gap: 8px;">
                <div class="cuw-product-image" style="height: 100%; width: 48px;">
                    <?php if (!empty($offer['product']['default_variant']['image'])) {
                        echo wp_kses_post($offer['product']['default_variant']['image']);
                    } else {
                        echo wp_kses_post($offer['product']['image']);
                    } ?>
                </div>
                <div style="min-width: 120px;">
                    <p class="cuw-product-title" style="color: #454d55; margin: 0; padding: 0;">
                        <?php echo wp_kses_post($offer['product']['title']); ?>
                    </p>
                    <div style="display: flex;">
                        <div class="cuw-offer-title"
                             style="padding: 2px; line-height: 1.2; border-radius: 4px; margin: 0; <?php echo esc_attr($offer['styles']['title']); ?>">
                            <?php echo wp_kses($offer['template']['title'], $offer['allowed_html']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <p class="cuw-product-price" style="margin: 0; text-align: center; padding: 4px;">
                <?php if (!empty($offer['product']['default_variant']['price_html'])) {
                    echo wp_kses_post($offer['product']['default_variant']['price_html']);
                } else {
                    echo wp_kses_post($offer['product']['price_html']);
                } ?>
            </p>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; flex: 1; gap: 8px;">
            <div class="cuw-product-quantity" style="display: flex; text-align: center; color: gray; zoom: 90%;">
                <?php echo apply_filters('cuw_offer_template_product_quantity', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="cuw-product-variants" style="zoom: 90%; min-width: 48px;">
                <?php echo apply_filters('cuw_offer_template_product_variants', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="cuw-offer-cta-section"
                 style="text-align: center; border-radius: 6px; width: auto; min-width: 80px; <?php echo esc_attr($offer['styles']['cta']); ?>">
                <button type="button" class="cuw-button"
                        style="line-height: 1.4; padding: 8px 12px; width: 100%; color: inherit; background: inherit; border: 0; border-radius: inherit; overflow: hidden; margin: 0;"
                    <?php if ($disable_cta) echo 'disabled'; ?>>
                        <span class="cuw-offer-cta-text" style="font-weight: bold;">
                            <?php echo wp_kses($offer['template']['cta_text'], $offer['allowed_html']); ?>
                        </span>
                </button>
            </div>
        </div>
    </div>
</div>