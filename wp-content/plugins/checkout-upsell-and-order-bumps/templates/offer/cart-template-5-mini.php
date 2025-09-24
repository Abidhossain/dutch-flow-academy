<?php
/**
 * Cart offer template 5 (mini)
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/offer/cart-template-5-mini.php.
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
         style="margin: 6px 0; display: flex; flex-wrap: wrap; padding: 4px 6px; gap: 8px; <?php echo esc_attr($offer['styles']['template']); ?>">
        <div class="cuw-product-section"
             style="display: flex; align-items: center; flex: 2;">
            <div class="cuw-offer-cta-section"
                 style="text-align: center; padding: 0; margin: 0 12px 0; <?php echo esc_attr($offer['styles']['cta']); ?>">
                <label style="display: flex; margin: 0; cursor: pointer; font-size: inherit; color: inherit;">
                    <input type="checkbox"
                           class="cuw-checkbox" <?php if (!empty($offer['cart_item_key'])) echo 'checked'; ?>
                           style="zoom: 1.2"
                        <?php if ($disable_cta) echo 'disabled'; ?>>
                </label>
            </div>
            <div style="display: flex; padding: 8px 0; min-width: 120px; align-items: center; gap: 8px;">
                <p class="cuw-product-title" style="color: #343638; margin: 0; padding: 0;">
                    <?php echo wp_kses_post($offer['product']['title']); ?>
                </p>
                <div style="display: inline;">
                    <h6 class="cuw-offer-title"
                        style="padding: 2px 8px; line-height: 1.5; border-radius: 12px; margin: 0; <?php echo esc_attr($offer['styles']['title']); ?>">
                        <?php echo wp_kses($offer['template']['title'], $offer['allowed_html']); ?>
                    </h6>
                </div>
            </div>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; flex: 3; gap: 6px;">
            <p class="cuw-product-price" style="margin: 0; text-align: center;">
                <?php if (!empty($offer['product']['default_variant']['price_html'])) {
                    echo wp_kses_post($offer['product']['default_variant']['price_html']);
                } else {
                    echo wp_kses_post($offer['product']['price_html']);
                } ?>
            </p>
            <div class="cuw-product-variants" style="zoom: 90%; min-width: 64px;">
                <?php echo apply_filters('cuw_offer_template_product_variants', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="cuw-product-quantity" style="display: flex; text-align: center; color: gray; zoom: 90%;">
                <?php echo apply_filters('cuw_offer_template_product_quantity', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>