<?php
/**
 * Frequently bought together product variant select modal
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-and-order-bumps/fbt/variant-select-modal.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($products) || !isset($data)) return;
?>

<div class="cuw-modal">
    <div class="cuw-modal-content cuw-animate-fade" style="max-width: 720px;">
        <div class="cuw-modal-header">
            <h4><?php esc_html_e("Choose variants", 'checkout-upsell-and-order-bumps'); ?></h4>
            <span class="cuw-modal-close">&times;</span>
        </div>
        <div class="cuw-modal-body" style="padding: 12px 16px; overflow-x: auto; overflow-y: auto; max-height: 480px;">
            <table>
                <thead>
                <tr>
                    <th></th>
                    <th style="text-align: center;"><?php esc_html_e("Product", 'checkout-upsell-and-order-bumps'); ?></th>
                    <th></th>
                    <th style="text-align: center;"><?php esc_html_e("Price", 'checkout-upsell-and-order-bumps'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $simple_products = array_filter($products, function ($product) {
                    return empty($product['is_variable']);
                });
                $variable_products = array_filter($products, function ($product) {
                    return !empty($product['is_variable']);
                });

                foreach ($simple_products as $key => $product) { ?>
                    <tr class="cuw-product-row" data-product_id="<?php echo esc_attr($product['id']); ?>"
                        style="display: none; opacity: 0.8;">
                        <td class="cuw-product-image" style="height: 64px; width: 64px; padding: 8px;">
                            <?php echo wp_kses_post($product['image']); ?>
                        </td>
                        <td class="cuw-product-title" style="vertical-align: middle;" colspan="2">
                            <?php echo esc_html(wp_strip_all_tags($product['title'])); ?>
                        </td>
                        <td class="cuw-product-price" style="vertical-align: middle;"></td>
                    </tr>
                <?php }

                foreach ($variable_products as $key => $product) { ?>
                    <tr class="cuw-product-row" data-product_id="<?php echo esc_attr($product['id']); ?>"
                        style="display: none;">
                        <td class="cuw-product-image" style="height: 64px; width: 64px; padding: 8px; vertical-align: middle;">
                            <?php echo wp_kses_post($product['image']); ?>
                        </td>
                        <td class="cuw-product-title" style="vertical-align: middle;">
                            <?php echo esc_html(wp_strip_all_tags($product['title'])); ?>
                        </td>
                        <td class="cuw-product-variants" style="vertical-align: middle;">
                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            echo apply_filters('cuw_fbt_template_product_variants', '', $product, [
                                'variant_select_name' => 'products[' . esc_attr($key) . '][variation_id]',
                                'attribute_select_name' => 'products[' . esc_attr($key) . '][variation_attributes]',
                            ]); ?>
                        </td>
                        <td class="cuw-product-price" style="vertical-align: middle;"></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="cuw-modal-footer" style="padding: 12px 16px; gap: 8px; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="cuw-total-price-section">
                    <?php esc_html_e("Total price", 'checkout-upsell-and-order-bumps'); ?>:
                    <span class="cuw-total-price" style="font-weight: bold; font-size: 110%;"></span>
                </div>
                <?php echo apply_filters('cuw_fbt_template_savings', '', null, $data, 'static'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <button type="button" style="text-transform: initial;"
                    class="cuw-add-to-cart single_add_to_cart_button button alt">
                <?php esc_html_e("Add to cart", 'checkout-upsell-and-order-bumps'); ?>
            </button>
        </div>
    </div>
</div>
