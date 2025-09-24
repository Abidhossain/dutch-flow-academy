<?php
/**
 * Cart offer template 7 (wide)
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/offer/cart-template-7-wide.php.
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
         style="margin: 12px 0; padding: 24px; border-radius: 24px; <?php echo esc_attr($offer['styles']['template']); ?>">
        <div class="cuw-product-section" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <div class="cuw-product-image cuw-product-curve-image" style="height: 72px; width: auto; min-width: 80px; border-radius: 12px;">
                <?php if (!empty($offer['product']['default_variant']['image'])) {
                    echo wp_kses_post($offer['product']['default_variant']['image']);
                } else {
                    echo wp_kses_post($offer['product']['image']);
                } ?>
            </div>
            <div style="flex: 3; padding: 0 10px; min-width:200px;">
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <h4 class="cuw-product-title" style="color: #454d55; margin: 0; padding: 0;">
                        <?php echo wp_kses_post($offer['product']['title']); ?>
                    </h4>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <p class="cuw-product-price" style="margin: 0;">
                            <?php if (!empty($offer['product']['default_variant']['price_html'])) {
                                echo wp_kses_post($offer['product']['default_variant']['price_html']);
                            } else {
                                echo wp_kses_post($offer['product']['price_html']);
                            } ?>
                        </p>
                        <h6 class="cuw-offer-title"
                            style="padding: 4px 6px; border-radius: 8px; margin: 0; <?php echo esc_attr($offer['styles']['title']); ?>">
                            <?php echo wp_kses($offer['template']['title'], $offer['allowed_html']); ?>
                        </h6>
                    </div>
                </div>
                <div class="cuw-product-variants" style="margin-top: 8px;">
                    <?php echo apply_filters('cuw_offer_template_product_variants', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            </div>

            <div class="cuw-product-quantity" style="flex: 2; text-align: center; color: gray;">
                <?php echo apply_filters('cuw_offer_template_product_quantity', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="cuw-offer-cta-section"
                 style="text-align: center; margin-left: auto; border-radius: 8px; <?php echo esc_attr($offer['styles']['cta']); ?>">
                <button type="button" class="cuw-button"
                        style="padding: 10px 16px; width: 100%; color: inherit; background: inherit; border: 0; border-radius: inherit; overflow: hidden; margin: 0;"
                    <?php if ($disable_cta) echo 'disabled'; ?>>
                    <span class="cuw-offer-cta-text" style="font-weight: bold;">
                        <?php echo wp_kses($offer['template']['cta_text'], $offer['allowed_html']); ?>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>