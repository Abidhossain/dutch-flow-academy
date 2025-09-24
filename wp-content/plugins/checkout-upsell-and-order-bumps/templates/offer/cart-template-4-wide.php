<?php
/**
 * Cart offer template 4 (wide)
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/offer/cart-template-4-wide.php.
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
         style="margin: 12px 0; padding: 10px; <?php echo esc_attr($offer['styles']['template']); ?>">
        <div class="cuw-product-section"
             style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-around;">
            <div class="cuw-product-image"
                 style="flex: 1; height: 100%; width: 100%; min-width: 80px; max-width: 100px;">
                <?php if (!empty($offer['product']['default_variant']['image'])) {
                    echo wp_kses_post($offer['product']['default_variant']['image']);
                } else {
                    echo wp_kses_post($offer['product']['image']);
                } ?>
            </div>
            <div style="flex: 3; padding: 0; display: flex; flex-direction: column; gap: 2px; min-width: 180px">
                <div>
                    <h4 class="cuw-product-title" style="display: inline; color: #454d55; margin: 0 2px; padding: 0;">
                        <?php echo wp_kses_post($offer['product']['title']); ?>
                    </h4>
                    <h4 class="cuw-offer-title"
                        style="display: inline; text-align: center; margin: 0 2px; font-weight: bold; padding: 2px 8px; border-radius: 20px; white-space: nowrap; <?php echo esc_attr($offer['styles']['title']); ?>">
                        <?php echo wp_kses($offer['template']['title'], $offer['allowed_html']); ?>
                    </h4>
                </div>
                <div style="display: flex; gap: 10px; justify-content: space-between; width: 100%; align-items: center;">
                    <div class="cuw-product-price" style="height: fit-content; padding: 0 4px; border-radius: 20px;">
                        <?php if (!empty($offer['product']['default_variant']['price_html'])) {
                            echo wp_kses_post($offer['product']['default_variant']['price_html']);
                        } else {
                            echo wp_kses_post($offer['product']['price_html']);
                        } ?>
                    </div>
                    <div class="cuw-product-quantity" style="color: gray;">
                        <?php echo apply_filters('cuw_offer_template_product_quantity', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                </div>
                <div class="cuw-product-variants" style="margin-top: 8px; width: 100%;">
                    <?php echo apply_filters('cuw_offer_template_product_variants', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            </div>
            <div style="text-align: center; display: flex; flex-direction: column; gap: 5px; align-items: center;">
                <div class="cuw-offer-cta-section"
                     style="text-align: center; border-radius: 50px; <?php echo esc_attr($offer['styles']['cta']); ?>">
                    <button type="button" class="cuw-button"
                            style="padding: 5px 15px; width: 100%; color: inherit; background: inherit; border: 0; border-radius: inherit; overflow: hidden; margin: 0;"
                        <?php if ($disable_cta) echo 'disabled'; ?>>
                        <span class="cuw-offer-cta-text" style="font-weight: bold;">
                            <?php echo wp_kses($offer['template']['cta_text'], $offer['allowed_html']); ?>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
