<?php
/**
 * Products template 2
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/products/template-1.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($data) || !isset($products) || !isset($campaign)) {
    return;
}

$heading = !empty($data['template']['title']) ? $data['template']['title'] : __('You may also like…', 'checkout-upsell-woocommerce');
$heading = apply_filters('cuw_products_heading', $heading);
$cta_text = !empty($data['template']['cta_text']) ? $data['template']['cta_text'] : __('Buy now', 'checkout-upsell-woocommerce');
?>

<section class="cuw-upsell-products cuw-products cuw-template cuw-mobile-responsive"
         data-camapign_id="<?php echo esc_attr($campaign['id']); ?>"
         style="margin: 16px 0;<?php echo esc_attr($data['styles']['template']); ?>">
    <?php if (!empty($heading)) { ?>
        <h2 class="cuw-heading cuw-template-title"
            style="margin-bottom: 20px;<?php echo esc_attr($data['styles']['title']); ?>">
            <?php echo wp_kses_post($heading); ?>
        </h2>
    <?php } ?>
    <form class="cuw-form" style="display: flex; gap: 8px; margin: 0;" method="post">
        <div class="cuw-gird" style="display:flex; flex-wrap: wrap; row-gap: 24px; column-gap: 32px;">
            <?php foreach ($products as $key => $product): ?>
                <?php
                $regular_price = !empty($product['default_variant']) ? $product['default_variant']['regular_price'] : $product['regular_price'];
                $price = !empty($product['default_variant']) ? $product['default_variant']['price'] : $product['price'];
                ?>
                <div class="cuw-product cuw-column cuw-product-row <?php echo esc_attr(implode(' ', $product['classes'])); ?>"
                     style="margin-bottom: 20px;"
                     data-id="<?php echo esc_attr($product['id']); ?>"
                     data-regular_price="<?php echo esc_attr($regular_price); ?>"
                     data-price="<?php echo esc_attr($price); ?>">
                    <div class="cuw-product-wrapper" style="display: flex;">
                        <div class="cuw-product-card"
                             style="min-width: 140px; <?php echo esc_attr($data['styles']['card']); ?>">
                            <div class="cuw-product-actions" style="position: relative;">
                                <div style="position: absolute; top: 0; left: 0;">
                                    <?php echo apply_filters('cuw_products_template_savings', '', $product, $data, 'dynamic'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  ?>
                                </div>
                                <div style="position: absolute; top: 0; right: 0;">
                                    <input class="cuw-product-checkbox" type="checkbox"
                                           name="products[<?php echo esc_attr($key); ?>][id]"
                                           value="<?php echo esc_attr($product['id']); ?>"
                                           style="float: right; margin: 4px; <?php if (!empty($is_bundle) && $product['is_main']) echo 'pointer-events: none;' ?>"
                                        <?php if ($data['template']['checkbox'] != 'unchecked' || (!empty($is_bundle) && $product['is_main'])) echo 'checked'; ?>>
                                </div>
                            </div>
                            <div class="cuw-product-image"
                                 style="min-width: 140px; min-height: 140px; <?php echo esc_attr($data['styles']['image']); ?> <?php if (!empty($is_bundle) && $product['is_main']) echo 'pointer-events: none;' ?>">
                                <?php if (!empty($product['default_variant']['image'])) {
                                    echo wp_kses_post($product['default_variant']['image']);
                                } else {
                                    echo wp_kses_post($product['image']);
                                } ?>
                            </div>
                            <div class="cuw-product-title" style="margin-top: 14px; text-align: center;">
                                <?php echo '<a href="' . esc_url($product['url']) . '">' . esc_html(wp_strip_all_tags($product['title'])) . '</a>'; ?>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-around; gap: 4px; margin-top: 8px;">
                                <?php if (!empty($product['price_html'])): ?>
                                    <div class="cuw-product-price" style="text-align: center;">
                                        <?php if (!empty($product['default_variant']['price_html'])) {
                                            echo wp_kses_post($product['default_variant']['price_html']);
                                        } else {
                                            echo wp_kses_post($product['price_html']);
                                        } ?>
                                    </div>
                                <?php endif; ?>

                                <div class="cuw-product-quantity"
                                     style="display: flex; align-items: center; justify-content: center; margin-top: 4px;">
                                    <?php echo apply_filters('cuw_product_template_quantity', '', $product, ['name' => 'products[' . $key . '][qty]']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                            </div>

                            <?php if (isset($product['variants']) && !empty($product['variants'])) { ?>
                                <div class="cuw-product-variants" style="margin-top: 8px;">
                                    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    echo apply_filters('cuw_product_template_variants', '', $product, [
                                        'variant_select_name' => 'products[' . esc_attr($key) . '][variation_id]',
                                        'attribute_select_name' => 'products[' . esc_attr($key) . '][variation_attributes]',
                                    ]);  ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="cuw-column cuw-buy-section" style="width: 200px;">
                <div class="cuw-actions" style="display: none; margin-top: 32px; margin-bottom: 24px;">
                    <div class="cuw-total-price-section" style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">
                        <span><?php esc_html_e("Total price", 'checkout-upsell-woocommerce'); ?>:</span>
                        <span class="cuw-total-price" style="font-weight: bold; font-size: 110%;"></span>
                    </div>
                    <?php echo apply_filters('cuw_products_template_savings', '', null, $data, 'dynamic'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <div style="margin-top: 8px;">
                        <input type="hidden" name="cuw_add_to_cart" value="<?php echo esc_attr($campaign['type']); ?>"/>
                        <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign['id']); ?>">
                        <button type="button"
                                class="cuw-add-to-cart cuw-template-cta-text cuw-template-cta-button single_add_to_cart_button button alt"
                                data-buy_now="1"
                                style="width: 100%; text-transform: initial; <?php echo esc_attr($data['styles']['cta']); ?>">
                            <?php esc_html_e($cta_text); ?>
                        </button>
                    </div>
                </div>
                <div class="cuw-message" style="display: none;">
                    <p style="padding: 32px 0; margin: 0;">
                        <?php esc_html_e("Choose items to buy.", 'checkout-upsell-woocommerce'); ?>
                    </p>
                </div>
            </div>
        </div>
    </form>
</section>

