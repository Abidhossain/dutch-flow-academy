<?php
/**
 * Offer template 3
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/offer/template-3.php.
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
         style="margin: 12px 0; border-radius: 10px; overflow: hidden; <?php echo esc_attr($offer['styles']['template']); ?>">
        <?php if (!empty($offer['template']['title'])) : ?>
            <div class="cuw-banner">
                <h4 class="cuw-offer-title"
                    style="color: white; padding: 12px; text-align: center; margin: 0; <?php echo esc_attr($offer['styles']['title']); ?>">
                    <?php echo wp_kses($offer['template']['title'], $offer['allowed_html']); ?>
                </h4>
            </div>
        <?php endif; ?>

        <div class="cuw-product-section"
             style="display: flex; margin: 12px; align-items: center; justify-content: center; gap: 8px;">
            <div class="cuw-product-image" style="width: 50%; height: auto; border-radius: 8px; overflow: hidden;">
                <?php if (!empty($offer['product']['default_variant']['image'])) {
                    echo wp_kses_post($offer['product']['default_variant']['image']);
                } else {
                    echo wp_kses_post($offer['product']['image']);
                } ?>
            </div>

            <div style="width: 50%;">
                <h4 class="cuw-product-title" style="color: #454d55;">
                    <?php echo wp_kses_post($offer['product']['title']); ?>
                </h4>
                <p class="cuw-product-price" style="margin: 8px 0;">
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
        </div>

        <div class="cuw-product-variants"
             style="margin: 0 8px 8px 8px; <?php if (!empty($offer['cart_item_key'])) echo 'pointer-events: none; opacity: 0.8;'; ?>">
            <?php echo apply_filters('cuw_offer_template_product_variants', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>

        <?php if (!empty($offer['template']['description'])) : ?>
            <p class="cuw-offer-description"
               style="text-align: justify; padding: 8px; margin: 8px; border-radius: 8px; overflow: hidden; <?php echo esc_attr($offer['styles']['description']); ?>">
                <?php echo wp_kses($offer['template']['description'], $offer['allowed_html']); ?>
            </p>
        <?php endif; ?>

        <div class="cuw-offer-cta-section"
             style="background: #f3e141; text-align: center; padding: 12px; margin-top: 8px; <?php echo esc_attr($offer['styles']['cta']); ?>">
            <label style="margin: 0; padding: 0; cursor: pointer; font-size: inherit; color: inherit;">
                <input type="checkbox"
                       class="cuw-checkbox" <?php if (!empty($offer['cart_item_key'])) echo 'checked'; ?>
                    <?php if ($disable_cta) echo 'disabled'; ?>>
                <span class="cuw-offer-cta-text"
                      style="font-weight: bold; font-size: inherit; margin-left: 2px; <?php if (!empty($offer['cart_item_key'])) echo 'display: none;' ?>">
                    <?php echo wp_kses($offer['template']['cta_text'], $offer['allowed_html']); ?>
                </span>
                <span class="cuw-offer-added-text"
                      style="font-weight: bold; font-size: inherit; margin-left: 2px; <?php if (empty($offer['cart_item_key'])) echo 'display: none;' ?>">
                    <?php esc_html_e('Added', 'checkout-upsell-and-order-bumps'); ?>
                </span>
            </label>
        </div>
    </div>
</div>