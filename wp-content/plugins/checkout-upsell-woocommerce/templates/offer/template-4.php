<?php
/**
 * Offer template 4
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/offer/template-4.php.
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
         style="margin: 12px 0; border-radius: 10px; overflow: hidden; position: relative; padding: 12px; <?php echo esc_attr($offer['styles']['template']); ?>">
        <?php if (!empty($offer['template']['title'])) : ?>
            <div class="cuw-badge"
                 style="position: absolute; top: 6px; <?php echo $offer['is_rtl'] ? 'right: 0;' : 'left: 0;'; ?>">
                <?php $border_radius = $offer['is_rtl'] ? 'border-top-right-radius: 10px; border-bottom-left-radius: 10px;' : 'border-top-left-radius: 10px; border-bottom-right-radius: 10px;'; ?>
                <span class="cuw-offer-title"
                      style="font-weight: bold; padding:8px; <?php echo esc_attr($border_radius); ?> <?php echo esc_attr($offer['styles']['title']); ?>">
                    <?php echo wp_kses($offer['template']['title'], $offer['allowed_html']); ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="cuw-product-section">
            <div style="vertical-align: bottom;">
                <div class="cuw-product-image cuw-fit-image"
                     style="width: auto; height: 240px; border-radius: 10px; overflow: hidden;">
                    <?php if (!empty($offer['product']['default_variant']['image'])) {
                        echo wp_kses_post($offer['product']['default_variant']['image']);
                    } else {
                        echo wp_kses_post($offer['product']['image']);
                    } ?>
                </div>
            </div>

            <div>
                <h4 class="cuw-product-title" style="color: #3a3b3d; margin-top: 12px; margin-bottom: 0;">
                    <?php echo wp_kses_post($offer['product']['title']); ?>
                </h4>
                <div class="cuw-product-variants"
                     style="margin: 2px 0; <?php if (!empty($offer['cart_item_key'])) echo 'pointer-events: none; opacity: 0.8;'; ?>">
                    <?php echo apply_filters('cuw_offer_template_product_variants', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <div>
                        <p class="cuw-product-price" style="margin-top: 10px; margin-bottom: 6px;">
                            <?php if (!empty($offer['product']['default_variant']['price_html'])) {
                                echo wp_kses_post($offer['product']['default_variant']['price_html']);
                            } else {
                                echo wp_kses_post($offer['product']['price_html']);
                            } ?>
                        </p>
                        <div class="cuw-product-quantity"
                             style="margin-top: 0; margin-bottom: 10px; color: gray; <?php if (!empty($offer['cart_item_key'])) echo 'pointer-events: none; opacity: 0.8;'; ?>">
                            <?php echo apply_filters('cuw_offer_template_product_quantity', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    </div>
                    <div class="cuw-offer-cta-section"
                         style="display: flex; flex: 1 1 auto; text-align: center; margin-left: auto; border-radius: 8px; <?php echo esc_attr($offer['styles']['cta']); ?>">
                        <button type="button" class="cuw-button"
                                style="padding: 10px 16px; margin: 0; width: 100%; color: inherit; background: inherit; border: 0; border-radius: inherit; overflow: hidden;"
                            <?php if ($disable_cta) echo 'disabled'; ?>>
                            <span class="cuw-offer-cta-text"
                                  style="font-weight: bold; <?php if (!empty($offer['cart_item_key'])) echo 'display: none;' ?>">
                                <?php echo wp_kses($offer['template']['cta_text'], $offer['allowed_html']); ?>
                            </span>
                            <span class="cuw-offer-added-text"
                                  style="font-weight: bold; <?php if (empty($offer['cart_item_key'])) echo 'display: none;' ?>">
                                <?php esc_html_e('Added', 'checkout-upsell-woocommerce'); ?>&emsp;&times;
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($offer['template']['description'])) : ?>
            <p class="cuw-offer-description"
               style="text-align: justify; padding: 8px; margin-bottom: 0; border-radius: 8px; overflow: hidden; <?php echo esc_attr($offer['styles']['description']); ?>">
                <?php echo wp_kses($offer['template']['description'], $offer['allowed_html']); ?>
            </p>
        <?php endif; ?>
    </div>
</div>