<?php
/**
 * Frequently bought together template 7
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/fbt/template-7.php.
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
$has_variable = (bool)array_sum(array_column($products, 'is_variable'));
$product_ids = array_column($products, 'id');
?>

<section class="cuw-fbt-products cuw-products cuw-template"
         data-campaign_id="<?php echo esc_attr($campaign['id']); ?>"
         style="margin: 16px 0; <?php echo esc_attr($data['styles']['template']); ?>">
    <?php if (!empty($heading)) { ?>
        <h2 class="cuw-heading cuw-template-title"
            style="margin-bottom: 20px; <?php echo esc_attr($data['styles']['title']); ?>">
            <?php echo esc_html($heading); ?>
        </h2>
    <?php } ?>

    <form class="cuw-form" style="display: flex; gap: 8px; margin: 0;" method="post">
        <div class="cuw-gird" style="display: flex; flex-wrap: wrap;">
            <?php foreach ($products as $key => $product): ?>
                <div class="cuw-product cuw-product-row <?php echo esc_attr(implode(' ', $product['classes'])); ?>"
                     style="margin-bottom: 20px;"
                     data-id="<?php echo esc_attr($product['id']); ?>"
                     data-regular_price="<?php echo esc_attr($product['regular_price']); ?>"
                     data-price="<?php echo esc_attr($product['price']); ?>">
                    <div class="cuw-product-wrapper" style="display: flex;">
                        <div class="cuw-product-container">
                            <div class="cuw-product-card" style="margin: 16px; <?php echo esc_attr($data['styles']['card']); ?>">
                                <div class="cuw-product-actions" style="position: relative;">
                                    <div style="position: absolute; top: 0; left: 0;">
                                        <?php echo apply_filters('cuw_fbt_template_savings', '', $product, $data, 'static'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </div>
                                </div>
                                <?php $image_style = $data['styles']['image'];
                                if (in_array($data['template']['checkbox'], ['hidden', 'uncheckable']) || (!empty($is_bundle) && $product['is_main'])) {
                                    $image_style .= 'pointer-events: none;';
                                } ?>
                                <div class="cuw-product-image cuw-product-curve-image"
                                     style="box-shadow: none; border-radius: 12px; <?php echo esc_attr($image_style); ?>">
                                    <?php if (!empty($product['default_variant']['image'])) {
                                        echo wp_kses_post($product['default_variant']['image']);
                                    } else {
                                        echo wp_kses_post($product['image']);
                                    } ?>
                                </div>
                                <div style="display: flex; margin-top: 10px; align-items: flex-start; gap: 8px;">
                                    <div>
                                        <?php $checkbox_style = '';
                                        if ($data['template']['checkbox'] == 'hidden') {
                                            $checkbox_style .= 'display: none;';
                                        } elseif ($data['template']['checkbox'] == 'uncheckable' || (!empty($is_bundle) && $product['is_main'])) {
                                            $checkbox_style .= 'pointer-events: none; opacity: 0.8;';
                                        } ?>
                                        <input class="cuw-product-checkbox cuw-custom-checkbox" type="checkbox"
                                               name="products[<?php echo esc_attr($key); ?>][id]"
                                               value="<?php echo esc_attr($product['id']); ?>"
                                               style="<?php echo esc_attr($checkbox_style); ?>"
                                            <?php if ($data['template']['checkbox'] != 'unchecked' || (!empty($is_bundle) && $product['is_main'])) echo 'checked'; ?>>
                                        <?php if (!empty($product['is_variable']) && !empty($product['variants'])) { ?>
                                            <input class="cuw-product-variation-id" type="hidden"
                                                   name="products[<?php echo esc_attr($key); ?>][variation_id]"
                                                   value="<?php echo esc_attr(current($product['variants'])['id']); ?>">
                                        <?php } ?>
                                    </div>
                                    <div class="cuw-product-title">
                                        <?php echo !empty($product['is_main']) ? esc_html(wp_strip_all_tags($product['title'])) : wp_kses_post($product['title']); ?>
                                    </div>
                                </div>
                                <?php if (!empty($product['price_html'])): ?>
                                    <div class="cuw-product-price">
                                        <?php echo wp_kses_post($product['price_html']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($product['variants']) && !empty($product['variants'])) { ?>
                                <div class="" style="<?php echo esc_attr($data['styles']['card']); ?> margin: 16px;">
                                    <div class="cuw-product-variants" >
                                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        echo apply_filters('cuw_fbt_template_product_variants', '', $product, [
                                            'variant_select_name' => 'products[' . esc_attr($key) . '][variation_id]',
                                            'attribute_select_name' => 'products[' . esc_attr($key) . '][variation_attributes]',
                                        ]); ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if (next($products)) { ?>
                            <div class="cuw-product-separator"
                                 style="display: flex; margin: 0 8px; align-items: center; font-size: 24px; color: #888888; <?php echo 'height: ' . esc_attr($data['template']['styles']['image']['size']) . 'px;'; ?>">
                                +
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="cuw-column cuw-buy-section" style="max-width: 256px; padding: 26px;">
                <div class="cuw-actions" style="display: none;">
                    <div class="cuw-total-price-section" style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center; margin-top: 24px;">
                        <span><?php esc_html_e("Total price", 'checkout-upsell-and-order-bumps'); ?>:</span>
                        <span class="cuw-total-price" style="font-weight: bold; font-size: 110%;"></span>
                    </div>
                    <?php echo apply_filters('cuw_fbt_template_savings', '', null, $data, 'static'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
                                data-at_least_items="<?php echo !empty($is_bundle) ? 2 : 1; ?>"
                                style="width: 100%; text-transform: initial; white-space: normal; border-radius: 12px; <?php echo esc_attr($data['styles']['cta']); ?>">
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
    </form>
</section>