<?php
/**
 * Products template 3
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/products/template-3.php.
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
         style="margin: 16px 0; width: 100%; <?php echo esc_attr($data['styles']['template']); ?>">
    <?php if (!empty($heading)) { ?>
        <h2 class="cuw-heading cuw-template-title"
            style="margin-bottom: 20px; <?php echo esc_attr($data['styles']['title']); ?>">
            <?php echo wp_kses_post($heading); ?>
        </h2>
    <?php } ?>
    <form class="cuw-form" method="post">
        <div class="cuw-carousel"
             style="position: relative; display: flex; align-items: center; justify-content: center;">
            <div class="cuw-scroll-action <?php echo !empty($data['is_rtl']) ? 'cuw-next' : 'cuw-previous'; ?>"
                 style="position: absolute; z-index: 1; cursor: pointer; float: left; line-height: 1; background: inherit; opacity: 0.5; left: 0; fill: #64748b; <?php echo !empty($data['is_rtl']) ? 'opacity: 1' : 'opacity: 0.5'; ?>">
                <svg width="24px" height="24px" viewBox="0 0 1024 1024" class="icon" xmlns="http://www.w3.org/2000/svg">
                    <path d="M768 903.232l-50.432 56.768L256 512l461.568-448 50.432 56.768L364.928 512z"/>
                </svg>
            </div>
            <div class="cuw-carousel-slider"
                 style="width: 85%; display: flex; overflow-x: scroll; scroll-snap-type: x mandatory; scroll-behavior: smooth; transition: 0.2s ease-in-out; gap: 18px; padding-bottom: 10px;">
                <?php foreach ($products as $key => $product): ?>
                    <?php
                    $regular_price = !empty($product['default_variant']) ? $product['default_variant']['regular_price'] : $product['regular_price'];
                    $price = !empty($product['default_variant']) ? $product['default_variant']['price'] : $product['price'];
                    ?>
                    <div class="cuw-product cuw-column cuw-product-row cuw-carousel-slide <?php echo esc_attr(implode(' ', $product['classes'])); ?>"
                         data-id="<?php echo esc_attr($product['id']); ?>"
                         style="display: flex; flex-direction: row; width: auto; border-radius: 5px; border: 1px solid #64748b; position: relative; padding: 10px; <?php if ($key != 0) echo 'opacity: 0.8;'; ?>"
                         data-regular_price="<?php echo esc_attr($regular_price); ?>"
                         data-price="<?php echo esc_attr($price); ?>">
                        <div class="cuw-product-actions">
                            <div style="position: absolute; top: 0; left: 0; padding: 10px;">
                                <?php echo apply_filters('cuw_products_template_savings', '', $product, $data, 'dynamic'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  ?>
                            </div>
                            <div style="position: absolute; top: 0; right: 0; padding: 10px;">
                                <input class="cuw-product-checkbox" type="checkbox"
                                       name="products[<?php echo esc_attr($key); ?>][id]"
                                       value="<?php echo esc_attr($product['id']); ?>"
                                       style="float: right; margin: 4px; <?php if (!empty($is_bundle) && $product['is_main']) echo 'pointer-events: none;' ?>"
                                    <?php if ($key == 0) echo 'data-checked="1"'; ?>
                                    <?php if ($data['template']['checkbox'] != 'unchecked' || $key == 0 || (!empty($is_bundle) && $product['is_main'])) echo 'checked'; ?>>
                            </div>
                        </div>
                        <div class="cuw-carousel-slide" style="display: flex; flex-direction: row; gap: 20px;">
                            <div class="cuw-product-wrapper"
                                 style="display: flex; justify-content: center;  width: 100%; height: auto;">
                                <div style="display: flex; align-items: center; justify-content: space-around; flex-direction: column;">
                                    <div class="cuw-product-image"
                                         style="<?php echo esc_attr($data['styles']['image']); ?> <?php if (!empty($is_bundle) && $product['is_main']) echo 'pointer-events: none;' ?>">
                                        <?php if (!empty($product['default_variant']['image'])) {
                                            echo wp_kses_post($product['default_variant']['image']);
                                        } else {
                                            echo wp_kses_post($product['image']);
                                        } ?>
                                    </div>
                                </div>
                            </div>
                            <div style="width: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                <div class="cuw-product-title" style="margin-top: 14px; text-align: center;">
                                    <?php echo '<a href="' . esc_url($product['url']) . '">' . esc_html(wp_strip_all_tags($product['title'])) . '</a>'; ?>
                                </div>

                                <div style="display:flex; align-items: center; justify-content: space-between; margin-top: 7px; gap: 12px;">
                                    <?php if (!empty($product['price_html'])): ?>
                                        <div class="cuw-product-price" style="display: flex;">
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
                                    <div class="cuw-product-variants" style="margin-top: 8px; width: 100%;">
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
            </div>
            <div class="cuw-scroll-action  <?php echo !empty($data['is_rtl']) ? 'cuw-previous' : 'cuw-next'; ?>"
                 style="position: absolute; z-index: 1; cursor: pointer; float: right; line-height: 1; background: inherit; right: 0; fill: #64748b; <?php echo !empty($data['is_rtl']) ? 'opacity: 0.5' : 'opacity: 1'; ?>">
                <svg width="24px" height="24px" viewBox="0 0 1024 1024" class="icon" xmlns="http://www.w3.org/2000/svg">
                    <path d="M256 120.768L306.432 64 768 512l-461.568 448L256 903.232 659.072 512z"/>
                </svg>
            </div>
        </div>
        <div class="cuw-column cuw-buy-section" style="width:100%; margin-top: 20px;">
            <div class="cuw-actions"
                 style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 24px;">
                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 5px;">
                    <div style="display: flex; flex-wrap: wrap; gap: 4px; justify-content: center;">
                        <span><?php esc_html_e("Added products", 'checkout-upsell-woocommerce'); ?>:</span>
                        <span class="cuw-total-items" style="font-weight: bold; font-size: 110%;"></span>
                    </div>
                    <div class="cuw-total-price-section" style="display: flex; flex-wrap: wrap; gap: 4px; justify-content: center;">
                        <span><?php esc_html_e("Total price", 'checkout-upsell-woocommerce'); ?>:</span>
                        <span class="cuw-total-price" style="font-weight: bold; font-size: 110%;"></span>
                    </div>
                </div>
                <?php echo apply_filters('cuw_products_template_savings', '', null, $data, 'dynamic'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <input type="hidden" name="cuw_add_to_cart" value="<?php echo esc_attr($campaign['type']); ?>"/>
                    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign['id']); ?>">
                    <button type="button"
                            class="cuw-add-to-cart cuw-template-cta-text cuw-template-cta-section single_add_to_cart_button button alt"
                            data-buy_now="1"
                            style="text-transform: initial; margin: 0; <?php echo esc_attr($data['styles']['cta']); ?>">
                        <?php esc_html_e($cta_text); ?>
                    </button>
                </div>
            </div>
            <div class="cuw-message" style="display: none;">
                <p style="margin: 0;width: 100%; display: flex; justify-content: center; align-items: center;">
                    <?php esc_html_e("Choose items to buy.", 'checkout-upsell-woocommerce'); ?>
                </p>
            </div>
        </div>
    </form>
</section>