<?php
/**
 * Product addons template 2
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/addon/template-2.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($products) || !isset($campaign) || !isset($data)) {
    return;
}

$heading = !empty($data['template']['title']) ? $data['template']['title'] : '';
$addons_price_text = apply_filters('cuw_product_addons_price_text', '');
$total_price_text = apply_filters('cuw_product_addons_total_price_text', esc_html__('Total price', 'checkout-upsell-woocommerce'));
$show_custom_quantity = !empty($campaign['data']['products']['quantity_field']) && $campaign['data']['products']['quantity_field'] == 'custom';
?>

<section class="cuw-product-addons cuw-products cuw-template cuw-mobile-responsive"
         data-campaign_id="<?php echo esc_attr($campaign['id']); ?>"
         data-main_product_regular_price="<?php echo isset($campaign['main_product_regular_price']) ? esc_attr($campaign['main_product_regular_price']) : ''; ?>"
         data-main_product_price="<?php echo isset($campaign['main_product_price']) ? esc_attr($campaign['main_product_price']) : ''; ?>"
         style="margin: 16px 0; <?php echo esc_attr($data['styles']['template']); ?>">
    <input type="hidden" name="cuw_add_to_cart" value="<?php echo esc_attr($campaign['type']); ?>">
    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign['id']); ?>">
    <?php if (isset($heading)) { ?>
        <h4 class="cuw-heading cuw-template-title"
            style="margin-bottom: 10px; <?php echo esc_attr($data['styles']['title']); ?>">
            <?php echo wp_kses_post($heading); ?>
        </h4>
    <?php } ?>
    <div class="cuw-gird" style="display: flex; flex-direction: column; flex-wrap: wrap; gap: 10px;">
        <?php foreach ($products as $key => $product): ?>
            <?php
            $regular_price = !empty($product['default_variant']) ? $product['default_variant']['regular_price'] : $product['regular_price'];
            $price = !empty($product['default_variant']) ? $product['default_variant']['price'] : $product['price'];
            ?>
            <div class="cuw-product cuw-product-row" style="display: flex; gap: 4px; flex-wrap: wrap;"
                 data-id="<?php echo esc_attr($product['id']); ?>"
                 data-regular_price="<?php echo esc_attr($product['regular_price']); ?>"
                 data-price="<?php echo esc_attr($product['price']); ?>">
                <div style="flex: 1; display: flex; flex-wrap: wrap; gap: 12px;">
                    <div class="cuw-product-actions" style="position: relative;">
                        <div class="cuw-product-image" style="<?php echo esc_attr($data['styles']['image']); ?>  ">
                            <?php echo wp_kses_post($product['image']); ?>
                        </div>
                        <div style="position: absolute; top: 0; left: 0;">
                            <input class="cuw-product-checkbox" type="checkbox"
                                   name="products[<?php echo esc_attr($key); ?>][id]"
                                   value="<?php echo esc_attr($product['id']); ?>"
                                   style="margin: 4px;">
                        </div>
                    </div>

                    <div style="flex: 2; font-size: 15px; display: flex; justify-content: center; gap: 4px; flex-direction:column;">
                        <div style="display: flex; justify-content: space-between;">
                            <div style="display: flex; flex-direction: column; justify-content: space-between; gap: 8px;">
                                <div class="cuw-product-title">
                                    <?php echo wp_kses_post($product['title']); ?>
                                </div>
                                <?php if (!empty($product['price_html'])): ?>
                                    <div class="cuw-product-price">
                                        <?php if (!empty($product['default_variant'])) {
                                            echo wp_kses_post($product['default_variant']['price_html']);
                                        } else {
                                            echo wp_kses_post($product['price_html']);
                                        } ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($show_custom_quantity) { ?>
                                <div class="cuw-product-quantity" style="zoom: 90%; display: none;">
                                    <?php echo apply_filters('cuw_product_template_quantity', '', $product, ['name' => 'products[' . $key . '][qty]']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if (isset($product['variants']) && !empty($product['variants'])) { ?>
                            <div class="cuw-product-variants"
                                 style="zoom: 90%; width: 80%; justify-content: start; display: none;">
                                <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                echo apply_filters('cuw_product_addons_template_product_variants', '', $product, [
                                    'variant_select_name' => 'products[' . esc_attr($key) . '][variation_id]',
                                    'attribute_select_name' => 'products[' . esc_attr($key) . '][variation_attributes]',
                                ]); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cuw-product-addons-pricing-section"
         style="display: none; flex-wrap: wrap; flex-direction: column; gap: 4px; margin-top: 12px;">
        <?php if (!empty($addons_price_text)) { ?>
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight: bold; font-size: 110%;"><?php echo esc_html($addons_price_text); ?></span>
                <span class="cuw-addons-price" style="font-weight: bold; font-size: 110%;"></span>
            </div>
        <?php } ?>
        <?php if (!empty($total_price_text)) { ?>
            <div class="cuw-total-price-section" style="display: flex; justify-content: space-between;">
                <span style="font-weight: bold; font-size: 110%;"><?php echo esc_html($total_price_text); ?></span>
                <span class="cuw-total-price" style="font-weight: bold; font-size: 110%;"></span>
            </div>
        <?php } ?>
    </div>
</section>
