<?php
defined('ABSPATH') || exit;
if (!isset($settings)) {
    return;
}

use CUW\App\Pro\Modules\Campaigns\PostPurchase;

?>

<?php if (current_action() == 'cuw_after_campaigns_settings'): ?>
    <?php
    $before_payment_supported_methods = implode(", ", PostPurchase::supportedPaymentMethods('before_payment'));
    $after_payment_supported_methods = implode(", ", PostPurchase::supportedPaymentMethods('after_payment'));
    ?>
    <h5 class="mt-3 mb-n2 text-primary text"><?php esc_html_e("Post-purchase Upsells", 'checkout-upsell-woocommerce'); ?></h5>
    <div class="mt-3 row align-items-center">
        <div class="col-md-5">
            <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Choose when the offer will be displayed", 'checkout-upsell-woocommerce'); ?></label>
            <p class="form-text mb-0">
                <?php esc_html_e(
                    'Choose the "Before payment" option if you would like to display the offer before the order is created. So that when customer accepts the offer, it gets added to the same order. If you choose the "After payment", the offer will be created as a separate order.',
                    'checkout-upsell-woocommerce'
                ); ?>
            </p>
            <p class="form-text text-dark"><?php esc_html_e('NOTE: Post-purchase offers will NOT display for unsupported gateways.', 'checkout-upsell-woocommerce'); ?></p>
        </div>
        <div class="col-md-5">
            <select class="form-control" name="process_post_purchase" id="post-purchase-process-type">
                <option value="before_payment"
                        data-methods="<?php echo esc_attr($before_payment_supported_methods) ?>" <?php if ($settings['process_post_purchase'] == 'before_payment') echo "selected"; ?>>
                    <?php esc_html_e("Before payment (Adds offer to the same order)", 'checkout-upsell-woocommerce'); ?>
                </option>
                <option value="after_payment"
                        data-methods="<?php echo esc_attr($after_payment_supported_methods) ?>" <?php if ($settings['process_post_purchase'] == 'after_payment') echo "selected"; ?>>
                    <?php esc_html_e("After payment is completed (Creates a new order for the offer)", 'checkout-upsell-woocommerce'); ?>
                </option>
            </select>
            <div class="text-dark small mt-1">
                <?php esc_html_e("Supported payment methods", 'checkout-upsell-woocommerce'); ?>:
                <span id="supported-payment-methods" class="text-primary">
                <?php
                if ($settings['process_post_purchase'] == 'before_payment') echo esc_html($before_payment_supported_methods);
                elseif ($settings['process_post_purchase'] == 'after_payment') echo esc_html($after_payment_supported_methods);
                ?>
                </span>
            </div>
        </div>
    </div>
    <div class="mt-3 mb-4 row align-items-center">
        <div class="col-md-5">
            <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Display order status notice to customers", 'checkout-upsell-woocommerce'); ?></label>
            <p class="form-text"><?php esc_html_e("Useful to inform the customer about the status of the order on the offer page.", 'checkout-upsell-woocommerce'); ?></p>
        </div>
        <div class="col-md-5">
            <div class="custom-control custom-switch custom-switch-md mb-2">
                <input type="checkbox" name="show_order_info_notice" value="1" class="custom-control-input"
                       id="order-status-notice" <?php if ($settings['show_order_info_notice']) echo "checked"; ?>>
                <label class="custom-control-label pl-2" for="order-status-notice"></label>
            </div>
        </div>
    </div>
    <div class="mt-3 row align-items-center">
        <div class="col-md-5">
            <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Offer page title", 'checkout-upsell-woocommerce'); ?></label>
            <p class="form-text"><?php esc_html_e("Useful to change post-purchase upsell offer template title.", 'checkout-upsell-woocommerce'); ?></p>
        </div>
        <div class="col-md-5">
            <input type="text" class="form-control" name="ppu_page_title"
                   value="<?php echo esc_attr($settings['ppu_page_title']) ?>"/>
        </div>
    </div>


    <h5 class="mt-3 mb-n2 text-primary text"><?php esc_html_e("Product & Cart Add-Ons", 'checkout-upsell-woocommerce'); ?></h5>
    <div class="mt-3 row align-items-center">
        <div class="col-md-5">
            <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Add-on badge text", 'checkout-upsell-woocommerce'); ?></label>
            <p class="form-text"><?php esc_html_e("Useful to change add-on badge text that is displayed after cart item name.", 'checkout-upsell-woocommerce'); ?></p>
        </div>
        <div class="col-md-5">
            <input type="text" class="form-control" name="addon_badge_text"
                   value="<?php echo esc_attr($settings['addon_badge_text']) ?>"/>
        </div>
    </div>

    <h5 class="mt-3 mb-n2 text-primary text"><?php esc_html_e("Product Add-Ons", 'checkout-upsell-woocommerce'); ?></h5>
    <div class="mt-3 row align-items-center">
        <div class="col-md-5">
            <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Maximum number of add-ons to display", 'checkout-upsell-woocommerce'); ?></label>
            <p class="form-text"><?php esc_html_e("Useful to limit add-on products that are display on a product page."); ?></p>
        </div>
        <div class="col-md-5">
            <select class="form-control" name="product_addon_products_display_limit">
                <?php CUW()->view('Admin/Components/LimitOptions', ['selected_limit' => $settings['product_addon_products_display_limit']]); ?>
            </select>
        </div>
    </div>

    <h5 class="mt-3 mb-n2 text-primary text"><?php esc_html_e("Cart Add-Ons", 'checkout-upsell-woocommerce'); ?></h5>
    <div class="mt-3 row align-items-center">
        <div class="col-md-5">
            <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Maximum number of add-ons to display", 'checkout-upsell-woocommerce'); ?></label>
            <p class="form-text"><?php esc_html_e("Useful to limit add-on products that are display in the cart page."); ?></p>
        </div>
        <div class="col-md-5">
            <select class="form-control" name="cart_addon_products_display_limit">
                <?php CUW()->view('Admin/Components/LimitOptions', ['selected_limit' => $settings['cart_addon_products_display_limit']]); ?>
            </select>
        </div>
    </div>

    <h5 class="mt-3 mb-n2 text-primary text"><?php esc_html_e("Thankyou Upsells", 'checkout-upsell-woocommerce'); ?></h5>
    <div class="mt-3 row align-items-center">
        <div class="col-md-5">
            <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Maximum number of products to display", 'checkout-upsell-woocommerce'); ?></label>
            <p class="form-text"><?php esc_html_e("Useful to limit products that are display on a Thankyou page"); ?></p>
        </div>
        <div class="col-md-5">
            <select class="form-control" name="thankyou_upsell_products_display_limit">
                <?php CUW()->view('Admin/Components/LimitOptions', ['selected_limit' => $settings['thankyou_upsell_products_display_limit']]); ?>
            </select>
        </div>
    </div>

    <h5 class="mt-3 mb-n2 text-primary text"><?php esc_html_e("Upsell Popups", 'checkout-upsell-woocommerce'); ?></h5>
    <div class="mt-3 row align-items-center">
        <div class="col-md-5">
            <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Maximum number of products to display", 'checkout-upsell-woocommerce'); ?></label>
            <p class="form-text"><?php esc_html_e("Useful to limit products that are display on a popup."); ?></p>
        </div>
        <div class="col-md-5">
            <select class="form-control" name="upsell_popup_products_display_limit">
                <?php CUW()->view('Admin/Components/LimitOptions', ['selected_limit' => $settings['upsell_popup_products_display_limit']]); ?>
            </select>
        </div>
    </div>
<?php endif; ?>

<?php if (current_action() == 'cuw_after_campaigns_setting_tab'): ?>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab"
           href="#settings-engines">
            <?php esc_html_e("Engines", 'checkout-upsell-woocommerce'); ?>
        </a>
    </li>
<?php endif; ?>

<?php if (current_action() == 'cuw_after_settings_tab_contents'): ?>
    <div class="tab-pane fade pb-3" id="settings-engines">
        <h5 class="mb-n2 text-primary"><?php esc_html_e("Listing", 'checkout-upsell-woocommerce'); ?></h5>
        <div class="mt-3 row align-items-center">
            <div class="col-md-5">
                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Maximum number of products to list", 'checkout-upsell-woocommerce'); ?></label>
                <p class="form-text"><?php esc_html_e("Useful to limit the products that engines will process.", 'checkout-upsell-woocommerce'); ?></p>
            </div>
            <div class="col-md-5">
                <input type="number" class="form-control cuw-format-numeric-input" name="engine_products_fetch_limit"
                       min="1" max="10000"
                       value="<?php echo esc_attr($settings['engine_products_fetch_limit']) ?>"/>
            </div>
        </div>

        <h5 class="mt-3 mb-n2 text-primary"><?php esc_html_e("Caching", 'checkout-upsell-woocommerce'); ?></h5>
        <div class="mt-3 row align-items-center">
            <div class="col-md-5">
                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Enable caching", 'checkout-upsell-woocommerce'); ?></label>
                <p class="form-text"><?php esc_html_e("Useful to cache engine data. It will increase site performance while using recommendation engine.", 'checkout-upsell-woocommerce'); ?></p>
            </div>
            <div class="col-md-5">
                <div class="custom-control custom-switch custom-switch-md mb-2">
                    <input type="checkbox" name="engine_cache_enabled" value="1" class="custom-control-input"
                           id="engine-cache-enabled" <?php if ($settings['engine_cache_enabled']) echo "checked"; ?>>
                    <label class="custom-control-label pl-2" for="engine-cache-enabled"></label>
                </div>
            </div>
        </div>
        <div class="mt-3 row align-items-center cuw-engine-cache-expiration-block"
             style="<?php if (empty($settings['engine_cache_enabled'])) echo 'display: none;'; ?>">
            <div class="col-md-5">
                <label class="font-weight-semibold text-dark form-label"><?php esc_html_e("Cache expiration time in hours", 'checkout-upsell-woocommerce'); ?></label>
                <p class="form-text"><?php esc_html_e("Useful to control caching duration.", 'checkout-upsell-woocommerce'); ?></p>
            </div>
            <div class="col-md-5">
                <input type="number" class="form-control cuw-format-numeric-input" name="engine_cache_expiration"
                       min="1" max="8760"
                       value="<?php echo esc_attr($settings['engine_cache_expiration']) ?>">
            </div>
        </div>
    </div>
<?php endif; ?>
