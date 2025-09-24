<?php
/**
 * Frequently bought together template 2
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/fbt/template-2.php.
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

<section class="cuw-fbt-products cuw-products cuw-template cuw-mobile-responsive"
         data-campaign_id="<?php echo esc_attr($campaign['id']); ?>" data-change_image="only_row"
         style="margin: 16px 0; <?php echo esc_attr($data['styles']['template']); ?>">
    <?php if (!empty($heading)) { ?>
        <h2 class="cuw-heading cuw-template-title"
            style="padding: 24px 24px 0 24px; margin: 0;<?php echo esc_attr($data['styles']['title']); ?>">
            <?php echo wp_kses_post($heading); ?>
        </h2>
    <?php } ?>

    <form class="cuw-form" method="post" style="margin: 0;">
        <div class="cuw-gird" style="display: flex; flex-wrap: wrap; padding: 0 24px; margin-top: 24px;">
            <?php foreach ($products as $key => $product): ?>
                <div class="cuw-column cuw-product <?php echo esc_attr(implode(' ', $product['classes'])); ?>"
                     style="margin-bottom: 20px; <?php if ($product['is_main']) echo 'pointer-events: none;' ?>"
                     data-id="<?php echo esc_attr($product['id']); ?>"
                     data-regular_price="<?php echo esc_attr($product['regular_price']); ?>"
                     data-price="<?php echo esc_attr($product['price']); ?>">
                    <div class="cuw-product-wrapper" style="display: flex;">
                        <div class="cuw-product-card" style="<?php echo esc_attr($data['styles']['card']); ?>">
                            <div class="cuw-product-actions" style="position: relative;">
                                <div style="position: absolute; top: 0; left: 0;">
                                    <?php echo apply_filters('cuw_fbt_template_savings', '', $product, $data, 'static'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                                <div style="position: absolute; top: 0; right: 0;">
                                    <?php $checkbox_style = '';
                                    if ($data['template']['checkbox'] == 'hidden' || $product['is_main']) {
                                        $checkbox_style .= 'display: none;';
                                    } elseif ($data['template']['checkbox'] == 'uncheckable') {
                                        $checkbox_style .= 'pointer-events: none; opacity: 0.8;';
                                    } ?>
                                    <input class="cuw-product-checkbox" type="checkbox"
                                           name="products[<?php echo esc_attr($key); ?>][id]"
                                           value="<?php echo esc_attr($product['id']); ?>"
                                           style="float: right; margin: 4px; <?php echo esc_attr($checkbox_style); ?>"
                                        <?php if ($product['is_main']) echo 'data-hidden="1" data-checked="1"'; ?>
                                        <?php if ($data['template']['checkbox'] != 'unchecked' || $product['is_main']) echo 'checked'; ?>>
                                    <?php if ($product['is_variable']) { ?>
                                        <input class="cuw-product-variation-id" type="hidden"
                                               name="products[<?php echo esc_attr($key); ?>][variation_id]" value="0">
                                        <textarea class="cuw-product-variation-attributes-json" style="display: none;"
                                                  name="products[<?php echo esc_attr($key); ?>][variation_attributes_json]">
                                        </textarea>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php $image_style = $data['styles']['image'];
                            if (in_array($data['template']['checkbox'], ['hidden', 'uncheckable']) || $product['is_main']) {
                                $image_style .= 'pointer-events: none;';
                            } ?>
                            <div class="cuw-product-image" style="<?php echo esc_attr($image_style); ?>">
                                <?php echo wp_kses_post($product['image']); ?>
                            </div>
                            <div class="cuw-product-title" style="margin-top: 10px; text-align: center;">
                                <?php echo !empty($product['is_main']) ? esc_html(wp_strip_all_tags($product['title'])) : wp_kses_post($product['title']); ?>
                            </div>
                            <?php if (!empty($product['price_html'])): ?>
                                <div class="cuw-product-price" style="text-align: center;">
                                    <?php echo wp_kses_post($product['price_html']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (next($products)) { ?>
                            <div class="cuw-product-separator"
                                 style="display: flex; margin: 0 8px; align-items: center; font-weight: bold; font-size: 200%; color: #888888; <?php echo 'height: ' . esc_attr($data['template']['styles']['image']['size']) . 'px;'; ?>">
                                +
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="border-top: 1px solid #f0f0f0; width: 100%;">
            <div class="cuw-buy-section"
                 style="display: flex; gap: 16px; padding: 18px 24px; align-items: center; justify-content: space-between;">
                <div class="cuw-prices" style="display: flex; gap: 24px; align-items: center;">
                    <div>
                        <div style="opacity: 0.8;">
                            <span class="cuw-main-item"></span>
                            <?php esc_html_e("Item", 'checkout-upsell-and-order-bumps'); ?>
                        </div>
                        <div class="cuw-main-price" style="font-weight: bold; font-size: 110%;"></div>
                    </div>
                    <div style="font-size: 150%; color: #aaaaaa;">+</div>
                    <div>
                        <div style="opacity: 0.8;">
                            <span class="cuw-addon-items"></span>
                            <?php esc_html_e("Add-Ons", 'checkout-upsell-and-order-bumps'); ?>
                        </div>
                        <div class="cuw-addons-price" style="font-weight: bold; font-size: 110%;"></div>
                    </div>
                    <div style="font-size: 150%; color: #aaaaaa;">=</div>
                    <div class="cuw-total-price-section">
                        <div style="opacity: 0.8;"><?php esc_html_e("Total", 'checkout-upsell-and-order-bumps'); ?></div>
                        <div class="cuw-total-price" style="font-weight: bold; font-size: 110%;"></div>
                    </div>
                </div>
                <div class="cuw-message" style="display: flex; align-items: center;">
                    <p style="margin: 0;">
                        <?php esc_html_e("Please add at least 1 add-on item to proceed", 'checkout-upsell-and-order-bumps'); ?>
                    </p>
                </div>
                <div class="cuw-actions" data-inactive="disable" style="display: none;">
                    <input type="hidden" name="cuw_add_to_cart" value="<?php echo esc_attr($campaign['type']); ?>">
                    <input type="hidden" name="main_product_id"
                           value="<?php echo !empty($main_product_id) ? esc_attr($main_product_id) : ''; ?>">
                    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign['id']); ?>">
                    <input type="hidden" name="displayed_product_ids"
                           value="<?php echo esc_attr(implode(',', $product_ids)); ?>">
                    <?php echo apply_filters('cuw_fbt_template_savings', '', null, $data, 'static'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <button type="button"
                            class="cuw-add-to-cart cuw-template-cta-button single_add_to_cart_button button alt"
                            data-text="<?php echo esc_attr($cta_text); ?>"
                            data-at_least_items="2"
                            data-choose_variants="1"
                            style="width: 100%; text-transform: initial; white-space: normal; <?php echo esc_attr($data['styles']['cta']); ?>">
                        <?php esc_html_e("Add to cart", 'checkout-upsell-and-order-bumps'); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <?php if ($has_variable) {
        echo apply_filters('cuw_fbt_template_choose_variants_modal', '', $products, $data); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    } ?>
</section>