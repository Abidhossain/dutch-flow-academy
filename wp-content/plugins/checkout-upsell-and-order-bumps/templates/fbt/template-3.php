<?php
/**
 * Frequently bought together template 3
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/fbt/template-3.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($data) || !isset($products) || !isset($campaign)) {
    return;
}

$heading = !empty($data['template']['title']) ? $data['template']['title'] : __('Frequently bought together', 'checkout-upsell-and-order-bumps');
$heading = apply_filters('cuw_fbt_products_heading', $heading);
$cta_text = !empty($data['template']['cta_text']) ? $data['template']['cta_text'] : __('Add to cart', 'checkout-upsell-and-order-bumps');
$product_ids = array_column($products, 'id');
?>

<section class="cuw-fbt-products cuw-products cuw-template cuw-desktop-block"
         data-campaign_id="<?php echo esc_attr($campaign['id']); ?>"
         style="margin: 16px 0; <?php echo esc_attr($data['styles']['template']); ?>">
    <?php if (!empty($heading)) { ?>
        <h2 class="cuw-heading cuw-template-title"
            style="margin-bottom: 20px;<?php echo esc_attr($data['styles']['title']); ?>">
            <?php echo wp_kses_post($heading); ?>
        </h2>
    <?php } ?>

    <form class="cuw-form" style="display: flex; flex-direction: column; margin: 0;" method="post">
        <div class="cuw-gird" style="display: flex; flex-wrap: wrap;">
            <?php foreach ($products as $key => $product): ?>
                <div class="cuw-column cuw-product"
                     data-id="<?php echo esc_attr($product['id']); ?>"
                     style="margin-bottom: 20px;">
                    <div class="cuw-product-wrapper" style="display: flex;">
                        <div>
                            <?php $image_style = $data['styles']['image'];
                            if (in_array($data['template']['checkbox'], ['hidden', 'uncheckable']) || (!empty($is_bundle) && $product['is_main'])) {
                                $image_style .= 'pointer-events: none;';
                            } ?>
                            <div class="cuw-product-image"
                                 style="<?php echo esc_attr($image_style); ?>">
                                <?php if (!empty($product['default_variant']['image'])) {
                                    echo wp_kses_post($product['default_variant']['image']);
                                } else {
                                    echo wp_kses_post($product['image']);
                                } ?>
                            </div>
                        </div>
                        <?php if (next($products)) { ?>
                            <div class="cuw-column cuw-product-separator"
                                 style="display: flex; margin: 0 8px; align-items: center; font-weight: bold; font-size: 150%; color: #888888; <?php echo 'height: ' . esc_attr($data['template']['styles']['image']['size']) . 'px;'; ?>">
                                +
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="cuw-column cuw-buy-section" style="max-width: 256px; padding: 26px;">
                <div class="cuw-actions" style="display: none;">
                    <div class="cuw-total-price-section" style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 8px;">
                        <span><?php esc_html_e("Total price", 'checkout-upsell-and-order-bumps'); ?>:</span>
                        <span class="cuw-total-price" style="font-weight: bold; font-size: 110%;"></span>
                    </div>
                    <div style="margin-top: 8px;">
                        <input type="hidden" name="cuw_add_to_cart" value="<?php echo esc_attr($campaign['type']); ?>">
                        <input type="hidden" name="main_product_id"
                               value="<?php echo !empty($main_product_id) ? esc_attr($main_product_id) : ''; ?>">
                        <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign['id']); ?>">
                        <input type="hidden" name="displayed_product_ids"
                               value="<?php echo esc_attr(implode(',', $product_ids)); ?>">
                        <?php echo apply_filters('cuw_fbt_template_savings', '', null, $data, 'dynamic'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <button type="button"
                                class="cuw-add-to-cart cuw-template-cta-button single_add_to_cart_button button alt"
                                data-text="<?php echo esc_attr($cta_text); ?>"
                                style="width: 100%; text-transform: initial; border-radius: 100px; white-space: normal; <?php echo esc_attr($data['styles']['cta']); ?>">
                            <?php esc_html_e("Add to cart", 'checkout-upsell-and-order-bumps'); ?>
                        </button>
                    </div>
                </div>
                <div class="cuw-message" style="display: none;">
                    <p style="padding-top: 48px; margin: 0;">
                        <?php esc_html_e("Choose items to buy together.", 'checkout-upsell-and-order-bumps'); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="cuw-buy-section"
             style="display: flex; padding: 0 20px; flex-direction: column; align-items: start;">
            <div class="cuw-product-actions" style="display: flex; flex-direction: column; gap: 5px">
                <?php foreach ($products as $key => $product): ?>
                    <div class="cuw-product cuw-product-row <?php echo esc_attr(implode(' ', $product['classes'])); ?>"
                         style="display: flex; flex-direction: row; gap: 8px; align-items: center;"
                         data-id="<?php echo esc_attr($product['id']); ?>"
                         data-regular_price="<?php echo esc_attr($product['regular_price']); ?>"
                         data-price="<?php echo esc_attr($product['price']); ?>">
                        <?php $checkbox_style = '';
                        if ($data['template']['checkbox'] == 'hidden') {
                            $checkbox_style .= 'display: none;';
                        } elseif ($data['template']['checkbox'] == 'uncheckable' || (!empty($is_bundle) && $product['is_main'])) {
                            $checkbox_style .= 'pointer-events: none; opacity: 0.8;';
                        } ?>
                        <input class="cuw-product-checkbox" type="checkbox"
                               name="products[<?php echo esc_attr($key); ?>][id]"
                               value="<?php echo esc_attr($product['id']); ?>"
                               style="float: right; margin: 4px; <?php echo esc_attr($checkbox_style); ?>"
                            <?php if ($data['template']['checkbox'] != 'unchecked' || (!empty($is_bundle) && $product['is_main'])) echo 'checked'; ?>>
                        <?php if (!empty($product['is_variable']) && !empty($product['variants'])) { ?>
                            <input class="cuw-product-variation-id" type="hidden"
                                   name="products[<?php echo esc_attr($key); ?>][variation_id]"
                                   value="<?php echo esc_attr(current($product['variants'])['id']); ?>">
                        <?php } ?>
                        <div class="cuw-product-title">
                            <?php echo !empty($product['is_main']) ? esc_html(wp_strip_all_tags($product['title'])) : wp_kses_post($product['title']); ?>
                        </div>
                        <?php if (isset($product['variants']) && !empty($product['variants'])) { ?>
                            <div class="cuw-product-variants inline-attributes-select" style="min-width: 160px;">
                                <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                echo apply_filters('cuw_fbt_template_product_variants', '', $product, [
                                    'variant_select_name' => 'products[' . esc_attr($key) . '][variation_id]',
                                    'attribute_select_name' => 'products[' . esc_attr($key) . '][variation_attributes]',
                                ]); ?>
                            </div>
                        <?php } ?>
                        <?php if (!empty($product['price_html'])): ?>
                            <div class="cuw-product-price">
                                <?php if (!empty($product['default_variant']['price_html'])) {
                                    echo wp_kses_post($product['default_variant']['price_html']);
                                } else {
                                    echo wp_kses_post($product['price_html']);
                                } ?>
                            </div>
                        <?php endif; ?>
                        <?php echo apply_filters('cuw_fbt_template_savings', '', $product, $data, 'dynamic'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </form>
</section>

<section class="cuw-fbt-products cuw-products cuw-template cuw-mobile-block"
         data-campaign_id="<?php echo esc_attr($campaign['id']); ?>"
         style="margin: 16px 0; <?php echo esc_attr($data['styles']['template']); ?>">
    <?php if (!empty($heading)) { ?>
        <h2 class="cuw-heading cuw-template-title"
            style="margin-bottom: 20px; <?php echo esc_attr($data['styles']['title']); ?>">
            <?php echo esc_html($heading); ?>
        </h2>
    <?php } ?>

    <form class="cuw-form" style="display: flex; gap: 8px; margin: 0;" method="post">
        <div class="cuw-gird" style="width: 100%; display: flex; flex-direction: column; flex-wrap: wrap;">
            <?php foreach ($products as $key => $product): ?>
                <div class="cuw-column cuw-product cuw-product-row <?php echo esc_attr(implode(' ', $product['classes'])); ?>"
                     style="margin-bottom: 20px;"
                     data-id="<?php echo esc_attr($product['id']); ?>"
                     data-regular_price="<?php echo esc_attr($product['regular_price']); ?>"
                     data-price="<?php echo esc_attr($product['price']); ?>">
                    <div style="display: flex; gap: 4px;">
                        <?php $image_style = 'width: 30%; max-height: 120px;';
                        if (in_array($data['template']['checkbox'], ['hidden', 'uncheckable']) || (!empty($is_bundle) && $product['is_main'])) {
                            $image_style .= 'pointer-events: none;';
                        } ?>
                        <div class="cuw-product-image"
                             style="<?php echo esc_attr($image_style); ?>">
                            <?php if (!empty($product['default_variant']['image'])) {
                                echo wp_kses_post($product['default_variant']['image']);
                            } else {
                                echo wp_kses_post($product['image']);
                            } ?>
                        </div>
                        <div style="width: 70%; display: flex; gap: 4px; padding: 0 4px;">
                            <div style="width: 100%; display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; gap: 4px; align-items: start;">
                                    <?php $checkbox_style = '';
                                    if ($data['template']['checkbox'] == 'hidden') {
                                        $checkbox_style .= 'display: none;';
                                    } elseif ($data['template']['checkbox'] == 'uncheckable' || (!empty($is_bundle) && $product['is_main'])) {
                                        $checkbox_style .= 'pointer-events: none; opacity: 0.8;';
                                    } ?>
                                    <input class="cuw-product-checkbox" type="checkbox"
                                           name="products[<?php echo esc_attr($key); ?>][id]"
                                           value="<?php echo esc_attr($product['id']); ?>"
                                           style="float: right; margin: 4px; <?php echo esc_attr($checkbox_style) ?>"
                                        <?php if ($data['template']['checkbox'] != 'unchecked' || (!empty($is_bundle) && $product['is_main'])) echo 'checked'; ?>>
                                    <?php if (!empty($product['is_variable']) && !empty($product['variants'])) { ?>
                                        <input class="cuw-product-variation-id" type="hidden"
                                               name="products[<?php echo esc_attr($key); ?>][variation_id]"
                                               value="<?php echo esc_attr(current($product['variants'])['id']); ?>">
                                    <?php } ?>
                                    <div class="cuw-product-title">
                                        <?php echo !empty($product['is_main']) ? esc_html(wp_strip_all_tags($product['title'])) : wp_kses_post($product['title']); ?>
                                        <?php echo apply_filters('cuw_fbt_template_savings', '', $product, $data, 'dynamic'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </div>
                                </div>
                                <?php if (isset($product['variants']) && !empty($product['variants'])) { ?>
                                    <div class="cuw-product-variants">
                                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        echo apply_filters('cuw_fbt_template_product_variants', '', $product, [
                                            'variant_select_name' => 'products[' . esc_attr($key) . '][variation_id]',
                                            'attribute_select_name' => 'products[' . esc_attr($key) . '][variation_attributes]',
                                        ]); ?>
                                    </div>
                                <?php } ?>
                                <?php if (!empty($product['price_html'])): ?>
                                    <div class="cuw-product-price">
                                        <?php if (!empty($product['default_variant']['price_html'])) {
                                            echo wp_kses_post($product['default_variant']['price_html']);
                                        } else {
                                            echo wp_kses_post($product['price_html']);
                                        } ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="cuw-column cuw-buy-section"
                 style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 0 26px;">
                <div class="cuw-actions" style="display: none; width: 100%; margin-bottom: 8px;">
                    <div class="cuw-total-price-section" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4px; margin-top: 8px;">
                        <span><?php esc_html_e("Total price", 'checkout-upsell-and-order-bumps'); ?>:</span>
                        <span class="cuw-total-price" style="font-weight: bold; font-size: 110%;"></span>
                    </div>
                    <?php echo apply_filters('cuw_fbt_template_savings', '', null, $data, 'dynamic'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <div style="margin-top: 8px;">
                        <input type="hidden" name="cuw_add_to_cart" value="<?php echo esc_attr($campaign['type']); ?>">
                        <input type="hidden" name="main_product_id"
                               value="<?php echo !empty($main_product_id) ? esc_attr($main_product_id) : ''; ?>">
                        <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign['id']); ?>">
                        <input type="hidden" name="displayed_product_ids"
                               value="<?php echo esc_attr(implode(',', $product_ids)); ?>">
                        <button type="button"
                                class="cuw-add-to-cart cuw-template-cta-button single_add_to_cart_button button alt"
                                data-text="<?php echo esc_attr($cta_text); ?>"
                                style="width: 100%; text-transform: initial; white-space: normal; border-radius: 100px; <?php echo esc_attr($data['styles']['cta']); ?>">
                            <?php esc_html_e("Add to cart", 'checkout-upsell-and-order-bumps'); ?>
                        </button>
                    </div>
                </div>
                <div class="cuw-message" style="display: none;">
                    <p style="margin: 0;">
                        <?php esc_html_e("Choose items to buy together.", 'checkout-upsell-and-order-bumps'); ?>
                    </p>
                </div>
            </div>
        </div>
    </form>
</section>