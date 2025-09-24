<?php
/**
 * Frequently bought together template 1
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/fbt/template-1.php.
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
            style="margin-bottom: 20px; <?php echo esc_attr($data['styles']['title']); ?>">
            <?php echo wp_kses_post($heading); ?>
        </h2>
    <?php } ?>

    <form class="cuw-form" style="display: flex; gap: 8px; margin: 0;" method="post">
        <div class="cuw-gird" style="display: flex; flex-wrap: wrap;">
            <?php foreach ($products as $key => $product): ?>
                <div class="cuw-column cuw-product <?php echo esc_attr(implode(' ', $product['classes'])); ?>"
                     style="margin-bottom: 20px;"
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
                            if (in_array($data['template']['checkbox'], ['hidden', 'uncheckable']) || (!empty($is_bundle) && $product['is_main'])) {
                                $image_style .= 'pointer-events: none;';
                            } ?>
                            <div class="cuw-product-image"
                                 style="<?php echo esc_attr($image_style); ?>">
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
                                 style="display: flex; margin: 0 8px; align-items: center; font-weight: bold; font-size: 150%; color: #888888; <?php echo 'height: ' . esc_attr($data['template']['styles']['image']['size']) . 'px;'; ?>">
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
                                data-choose_variants="1"
                                style="width: 100%; text-transform: initial; white-space: normal; <?php echo esc_attr($data['styles']['cta']); ?>">
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

    <?php if ($has_variable) {
        echo apply_filters('cuw_fbt_template_choose_variants_modal', '', $products, $data); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    } ?>
</section>