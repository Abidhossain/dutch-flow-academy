<?php
/**
 * Offer template 5 (wide)
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/offer/template-5-wide.php.
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
     data-cart_item_key="<?php echo esc_attr($offer['cart_item_key'] ?? ''); ?>">
    <div class="cuw-container"
         style="margin: 12px 0; padding: 10px; <?php echo esc_attr($offer['styles']['template']); ?>">
        <?php if (!empty($offer['template']['title'])) : ?>
            <div class="cuw-banner">
                <h3 class="cuw-offer-title"
                    style="padding: 8px 12px; margin: 0 0 8px 0; text-align: center; <?php echo esc_attr($offer['styles']['title']); ?>">
                    <?php echo wp_kses($offer['template']['title'], $offer['allowed_html']); ?>
                </h3>
            </div>
        <?php endif; ?>

        <div class="cuw-product-section" style="display: flex; gap: 10px; align-items: center;">
            <div class="cuw-product-image" style="flex: 1; height: 100%;">
                <?php if (!empty($offer['product']['default_variant']['image'])) {
                    echo wp_kses_post($offer['product']['default_variant']['image']);
                } else {
                    echo wp_kses_post($offer['product']['image']);
                } ?>
            </div>
            <div style="flex: 2;">
                <h4 class="cuw-product-title" style="color: #454d55; margin: 0; padding: 0">
                    <?php echo wp_kses_post($offer['product']['title']); ?>
                </h4>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                    <p class="cuw-product-price" style="margin: 0;">
                        <?php if (!empty($offer['product']['default_variant']['price_html'])) {
                            echo wp_kses_post($offer['product']['default_variant']['price_html']);
                        } else {
                            echo wp_kses_post($offer['product']['price_html']);
                        } ?>
                    </p>
                    <div class="cuw-product-quantity"
                         style="color: gray; <?php if (!empty($offer['cart_item_key'])) echo 'pointer-events: none; opacity: 0.8;'; ?>">
                        <?php echo apply_filters('cuw_offer_template_product_quantity', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                </div>

                <div class="cuw-product-variants"
                     style="margin-top: 8px; <?php if (!empty($offer['cart_item_key'])) echo 'pointer-events: none; opacity: 0.8;'; ?>">
                    <?php echo apply_filters('cuw_offer_template_product_variants', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>

                <?php if (!empty($offer['template']['description'])) : ?>
                    <p class="cuw-offer-description"
                       style="text-align: justify; padding: 8px; margin-top: 8px; margin-bottom: 0; <?php echo esc_attr($offer['styles']['description']); ?>">
                        <?php echo wp_kses($offer['template']['description'], $offer['allowed_html']); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="cuw-offer-cta-section"
             style="background: #e2e6ea; text-align: center; padding: 10px; margin-top: 8px; <?php echo esc_attr($offer['styles']['cta']); ?>">
            <label style="margin: 0; padding: 0; cursor: pointer; font-size: inherit; color: inherit;">
                <input type="checkbox"
                       class="cuw-checkbox" <?php if (!empty($offer['cart_item_key'])) echo 'checked'; ?>
                    <?php if ($disable_cta) echo 'disabled'; ?>>
                <span class="cuw-offer-cta-text"
                      style="font-size: inherit; margin-left: 2px; <?php if (!empty($offer['cart_item_key'])) echo 'display: none;' ?>">
                    <?php echo wp_kses($offer['template']['cta_text'], $offer['allowed_html']); ?>
                </span>
                <span class="cuw-offer-added-text"
                      style="font-size: inherit; margin-left: 2px; <?php if (empty($offer['cart_item_key'])) echo 'display: none;' ?>">
                    <?php esc_html_e('Added', 'checkout-upsell-and-order-bumps'); ?>
                </span>
            </label>
        </div>
    </div>
</div>