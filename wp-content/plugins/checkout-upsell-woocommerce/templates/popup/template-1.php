<?php
/**
 * Upsell popup template 1
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/popup/template-1.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($data) || !isset($products) || !isset($campaign)) {
    return;
}

$heading = !empty($data['template']['title']) ? $data['template']['title'] : __("Wait! Don't miss our special deals", 'checkout-upsell-woocommerce');
$heading = apply_filters('cuw_products_popup_heading', $heading);
$cta_text = !empty($data['template']['cta_text']) ? $data['template']['cta_text'] : __('Add', 'checkout-upsell-woocommerce');
$cart_subtotal = !empty($cart_subtotal) ? $cart_subtotal : (function_exists('wc_price') ? wc_price(0) : '');
?>

<div id="cuw-modal-<?php echo esc_attr($campaign['id']); ?>" class="cuw-modal cuw-template"
     data-camapign_id="<?php echo esc_attr($campaign['id']); ?>"
     data-page="<?php echo !empty($page) ? esc_attr($page) : ''; ?>">
    <div class="cuw-modal-content cuw-animate-fade"
         style="margin: 0 auto; box-shadow: 0 0 4px rgb(0, 0, 0, 0.1); <?php echo esc_attr($data['styles']['content']); ?>">
        <div class="cuw-modal-header"
             style="display: flex; align-items: center; padding: 12px 16px; <?php echo esc_attr($data['styles']['header']); ?>">
            <div class="cuw-template-title"
                 style="flex: 1; text-align: center; font-family: sans-serif; font-size: inherit; color: inherit;">
                <?php echo wp_kses_post($heading); ?>
            </div>
            <span class="cuw-modal-close" style="font-size: 32px; line-height: 1;">&times;</span>
        </div>
        <div class="cuw-modal-subheader"
             style="display: flex; justify-content: space-between; align-items: center; gap: 6px; padding: 10px 28px; <?php echo esc_attr($data['styles']['subheader']); ?>">
            <div style="display: flex; gap: 8px; align-items: center; justify-content: space-between; color: inherit;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 55 55" fill="currentColor">
                    <path d="M16.4306 50.3595C15.2928 50.3595 14.3229 49.9544 13.5208 49.1443C12.7188 48.334 12.3177 47.3601 12.3177 46.2224C12.3177 45.0847 12.7228 44.1147 13.533 43.3127C14.3432 42.5106 15.3172 42.1095 16.4549 42.1095C17.5926 42.1095 18.5625 42.5146 19.3646 43.3248C20.1667 44.135 20.5677 45.109 20.5677 46.2467C20.5677 47.3844 20.1626 48.3543 19.3524 49.1564C18.5422 49.9585 17.5683 50.3595 16.4306 50.3595ZM39.3472 50.3595C38.2095 50.3595 37.2396 49.9544 36.4375 49.1443C35.6354 48.334 35.2344 47.3601 35.2344 46.2224C35.2344 45.0847 35.6395 44.1147 36.4496 43.3127C37.2599 42.5106 38.2338 42.1095 39.3715 42.1095C40.5093 42.1095 41.4792 42.5146 42.2812 43.3248C43.0833 44.135 43.4844 45.109 43.4844 46.2467C43.4844 47.3844 43.0793 48.3543 42.2691 49.1564C41.4589 49.9585 40.4849 50.3595 39.3472 50.3595ZM13.4635 12.547L19.7656 25.6095H36.2656L43.4271 12.547H13.4635ZM11.7448 9.10954H45.4938C46.3709 9.10954 47.0383 9.51058 47.4959 10.3127C47.9535 11.1147 47.9531 11.9168 47.4948 12.7189L39.7604 26.6408C39.3403 27.3665 38.7949 27.9489 38.1242 28.3882C37.4535 28.8274 36.7194 29.047 35.9219 29.047H18.5625L15.3542 35.0054H43.4844V38.4429H15.8698C14.2656 38.4429 13.1102 37.9081 12.4036 36.8387C11.697 35.7693 11.7066 34.5661 12.4323 33.2293L16.099 26.4689L7.39062 8.021H2.92188V4.5835H9.625L11.7448 9.10954Z"/>
                </svg>
                <span class="cuw-cart-title"
                      style="font-family: sans-serif; font-size: inherit;">
                    <?php esc_html_e("Your cart", 'checkout-upsell-woocommerce'); ?>
                </span>
            </div>
            <div>
                <div class="cuw-cart-subtotal"
                     style="font-family: sans-serif; font-size: inherit;">
                    <?php echo wp_kses_post($cart_subtotal); ?>
                </div>
            </div>
        </div>

        <div class="cuw-modal-body"
             style="overflow-x: auto; overflow-y: auto; max-height: 480px; <?php echo esc_attr($data['styles']['body']); ?>">
            <?php if (!empty($campaign) && !empty($products)) { ?>
                <div class="cuw-popup-products cuw-products cuw-mobile-responsive"
                     data-campaign_id="<?php echo esc_attr($campaign['id']); ?>" style="margin: 24px 32px;">
                    <?php foreach ($products as $key => $product): ?>
                        <?php
                        $regular_price = !empty($product['default_variant']) ? $product['default_variant']['regular_price'] : $product['regular_price'];
                        $price = !empty($product['default_variant']) ? $product['default_variant']['price'] : $product['price'];
                        $disable_cta = !empty($product['is_variable']) && empty($product['default_variant']);
                        ?>
                        <div class="cuw-product cuw-product-row"
                             style="margin-bottom: 20px; display: flex; flex-direction: row; flex-wrap: wrap; gap:20px;"
                             data-id="<?php echo esc_attr($product['id']); ?>"
                             data-regular_price="<?php echo esc_attr($regular_price); ?>"
                             data-price="<?php echo esc_attr($price); ?>">
                            <div class="cuw-product-image-wrapper"
                                 style="position: relative; <?php echo esc_attr($data['styles']['image']); ?>">
                                <div class="cuw-product-image"
                                     style="<?php echo esc_attr($data['styles']['image']); ?>">
                                    <?php if (!empty($product['default_variant']['image'])) {
                                        echo wp_kses_post($product['default_variant']['image']);
                                    } else {
                                        echo wp_kses_post($product['image']);
                                    } ?>
                                </div>
                                <span class="cuw-added-icon"
                                      style="display:none; position: absolute; right: -10px; top: -8px;">
                                    <svg style="background-color: white; border-radius: 25px;"
                                         xmlns="http://www.w3.org/2000/svg" fill="#178a0c" width="25px" height="25px"
                                         viewBox="0 0 24.00 24.00" stroke="#178a0c"
                                         stroke-width="0.00024000000000000003">
                                        <path d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm5.676,8.237-6,5.5a1,1,0,0,1-1.383-.03l-3-3a1,1,0,1,1,1.414-1.414l2.323,2.323,5.294-4.853a1,1,0,1,1,1.352,1.474Z"/>
                                    </svg>
                                </span>
                            </div>
                            <div style="flex: 2; display: flex; flex-direction: column; justify-content: center; gap: 2px;">
                                <div class="cuw-product-title" style="font-size: 18px; color: #000000;">
                                    <?php echo esc_html(wp_strip_all_tags($product['title'])); ?>
                                </div>
                                <?php if (!empty($product['variants'])) { ?>
                                    <div class="cuw-product-variants" style="max-width: 320px;">
                                        <?php echo apply_filters('cuw_product_template_variants', '', $product, []); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
                                <?php do_action('cuw_popup_template_after_product_details', $product, $data, $campaign); ?>
                            </div>
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <div class="cuw-product-quantity">
                                    <?php echo apply_filters('cuw_product_template_quantity', '', $product, []); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                            </div>
                            <div class="cuw-product-actions"
                                 style="flex: 1; display: flex; flex-wrap: wrap; justify-content: end; align-items: center; gap: 10px;">
                                <div class="cuw-add-product-to-cart cuw-template-cta-button"
                                     style="border-radius: 5px; padding: 8px 24px; line-height: 1; font-weight: bold; cursor: pointer; <?php echo esc_attr($data['styles']['cta']); ?>
                                                <?php echo $disable_cta ? 'pointer-events: none; opacity: 0.8;' : ''; ?>">
                                    + <span class="cuw-template-cta-text"><?php echo wp_kses_post($cta_text); ?></span>
                                </div>
                                <div class="cuw-added-text"
                                     style="display:none; border-radius: 5px; padding: 8px 16px; line-height: 1; <?php echo esc_attr($data['styles']['cta']); ?>">
                                    <?php esc_html_e("Added", 'checkout-upsell-woocommerce'); ?>
                                </div>
                                <div class="cuw-remove-item-from-cart"
                                     style="display: none; cursor: pointer; margin-top: 4px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px"
                                         viewBox="0 0 32 32" fill="#0000000" stroke="#0000000">
                                        <line stroke="#868686" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2px" x1="7" x2="25" y1="7" y2="25"/>
                                        <line stroke="#868686" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2px" x1="7" x2="25" y1="25" y2="7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php } ?>
        </div>

        <div class="cuw-modal-footer"
             style="padding: 12px 28px; gap: 10px; flex-wrap: wrap; <?php echo esc_attr($data['styles']['footer']); ?>">
            <div class="cuw-total-savings"
                 style="display:none; font-weight: 500; border-radius: 50px; padding: 4px 12px; opacity: 0.8; <?php echo esc_attr($data['styles']['cta']); ?>">
                <div style="display: flex; gap: 6px; align-items: center;">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 28 28"
                             fill="currentColor">
                            <path d="M10.418 3.7625L12.3211 7H12.25H8.3125C7.10391 7 6.125 6.02109 6.125 4.8125C6.125 3.60391 7.10391 2.625 8.3125 2.625H8.43281C9.24766 2.625 10.0078 3.05703 10.418 3.7625ZM3.5 4.8125C3.5 5.6 3.69141 6.34375 4.025 7H1.75C0.782031 7 0 7.78203 0 8.75V12.25C0 13.218 0.782031 14 1.75 14H26.25C27.218 14 28 13.218 28 12.25V8.75C28 7.78203 27.218 7 26.25 7H23.975C24.3086 6.34375 24.5 5.6 24.5 4.8125C24.5 2.15469 22.3453 0 19.6875 0H19.5672C17.8227 0 16.2039 0.924219 15.318 2.42813L14 4.67578L12.682 2.43359C11.7961 0.924219 10.1773 0 8.43281 0H8.3125C5.65469 0 3.5 2.15469 3.5 4.8125ZM21.875 4.8125C21.875 6.02109 20.8961 7 19.6875 7H15.75H15.6789L17.582 3.7625C17.9977 3.05703 18.7523 2.625 19.5672 2.625H19.6875C20.8961 2.625 21.875 3.60391 21.875 4.8125ZM1.75 15.75V25.375C1.75 26.8242 2.92578 28 4.375 28H12.25V15.75H1.75ZM15.75 28H23.625C25.0742 28 26.25 26.8242 26.25 25.375V15.75H15.75V28Z"/>
                        </svg>
                        <?php esc_html_e("You have saved", 'checkout-upsell-woocommerce'); ?>
                    </div>
                    <span class="cuw-saved-amount" style="font-weight: 700;"></span>
                </div>
            </div>
            <div class="cuw-modal-actions"
                 style="display: flex; justify-content: end; align-items: center; gap: 6px; <?php echo !empty($data['is_rtl']) ? 'margin-right: auto;' : 'margin-left: auto;' ?>">
                <?php foreach ($trigger['popup_actions'] ?? [] as $key => $action) {
                    if (empty($action['url'])) { ?>
                        <button type="button"
                                class="cuw-<?php echo esc_attr(str_replace('_', '-', $key)); ?>-button cuw-template-action button alt cuw-custom-trigger"
                                style="border-radius: 8px; line-height: 1.2; margin: 0; padding: 8px 24px; <?php echo esc_attr($data['styles']['action']); ?>"
                                data-event="<?php echo !empty($trigger['event']) ? esc_attr($trigger['event']) : ''; ?>"
                                data-target="<?php echo !empty($trigger['target']) ? esc_attr($trigger['target']) : ''; ?>">
                            <?php echo !empty($action['text']) ? esc_html($action['text']) : ''; ?>
                        </button>
                    <?php } else { ?>
                        <a class="cuw-<?php echo esc_attr(str_replace('_', '-', $key)); ?>-link cuw-template-action"
                           href="<?php echo esc_url($action['url']); ?>"
                           style="text-transform: initial; border-radius:8px; <?php echo esc_attr($data['styles']['action']); ?>">
                            <button type="button" class="button alt"
                                    style="font-size: inherit; background: inherit; color: inherit; border-radius: 8px; line-height: 1.2; margin: 0; padding: 8px 24px;">
                                <?php echo !empty($action['text']) ? esc_html($action['text']) : ''; ?>
                            </button>
                        </a>
                    <?php }
                } ?>
            </div>
        </div>
    </div>
</div>