<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$use_options = !empty($use_options) ? $use_options : [];
$products_text = !empty($products_text) ? $products_text : '';
$campaign_type = !empty($campaign['type']) ? $campaign['type'] : '';

$products_data = \CUW\App\Helpers\Campaign::getProductsData($campaign);
$use_products = !empty($products_data['use']) ? $products_data['use'] : '';
if (empty($use_products)) {
    $use_products = !empty($default_use) ? $default_use : '';
}

$specific_products = [];
$specific_product_ids = !empty($products_data['ids']) ? $products_data['ids'] : [];
foreach ($specific_product_ids as $id) {
    $specific_products[$id] = CUW()->wc->getProductTitle($id, true);
}

$quantity_field = !empty($products_data['quantity_field']) ? $products_data['quantity_field'] : 'sync';
$quantity_value = !empty($products_data['quantity_value']) ? $products_data['quantity_value'] : '1';

$recommendation_engine_name = '';
$recommendation_engine_id = !empty($products_data['engine_id']) ? $products_data['engine_id'] : '';
if (!empty($recommendation_engine_id)) {
    $recommendation_engine = apply_filters('cuw_get_engine', false, $recommendation_engine_id, ['title']);
    if (!empty($recommendation_engine) && isset($recommendation_engine['title'])) {
        $recommendation_engine_name = $recommendation_engine['title'];
    } else {
        $recommendation_engine_name = __("(Deleted)", 'checkout-upsell-and-order-bumps');
    }
}
$has_pro = CUW()->plugin->has_pro;
?>

<div id="cuw-products">
    <div class="use-products p-3 border-bottom border-gray-light">
        <label class="form-label font-weight-medium mb-2">
            <?php esc_html_e("Choose the product suggestion method for the campaign", 'checkout-upsell-and-order-bumps'); ?>
        </label>
        <?php if (in_array('related', $use_options)) { ?>
            <div class="custom-control use-product custom-radio custom-control mb-2 common-border <?php echo ($use_products == 'related') ? 'selected-border' : ''; ?>">
                <input type="radio" class="use-products-radio position-relative custom-control-input" id="use-related"
                       name="data[products][use]" value="related" <?php checked('related', $use_products); ?>>
                <label class="custom-control-label font-weight-medium"
                       for="use-related"><?php esc_html_e("Use Related products", 'checkout-upsell-and-order-bumps'); ?></label>
                <span class="d-block secondary small cuw-px-20px">
                    <?php
                        // translators: %s product text.
                        echo esc_html(sprintf(__("This will use WooCommerce Related Products as %s products.", 'checkout-upsell-and-order-bumps'), $products_text));
                    ?>
                </span>
                <span class="d-block secondary small cuw-px-20px">
                    <?php esc_html_e("WooCommerce decides the relation based on products having the same tags or categories.", 'checkout-upsell-and-order-bumps'); ?>
                </span>
            </div>
        <?php } ?>

        <?php if (in_array('cross_sell', $use_options)) { ?>
            <div class="custom-control use-product common-border custom-radio custom-control mb-2 <?php echo ($use_products == 'cross_sell') ? 'selected-border' : ''; ?>">
                <input type="radio" class="use-products-radio position-relative custom-control-input"
                       id="use-cross-sell" name="data[products][use]"
                       value="cross_sell" <?php checked('cross_sell', $use_products); ?>>
                <label class="custom-control-label font-weight-medium"
                       for="use-cross-sell"><?php esc_html_e("Use Cross-sell products", 'checkout-upsell-and-order-bumps'); ?></label>
                <span class="d-block secondary small cuw-px-20px">
                    <?php esc_html_e('This will use the "Cross-sell products" selected under the "Linked Products" section in the product creation page.', 'checkout-upsell-and-order-bumps'); ?>
                </span>
            </div>
        <?php } ?>

        <?php if (in_array('upsell', $use_options)) { ?>
            <div class="custom-control use-product common-border custom-radio custom-control mb-2 <?php echo ($use_products == 'upsell') ? 'selected-border' : ''; ?>">
                <input type="radio" class="use-products-radio position-relative custom-control-input" id="use-upsell"
                       name="data[products][use]" value="upsell" <?php checked('upsell', $use_products);
                echo ($use_products == 'upsell') ? 'selected-border' : '' ?>>
                <label class="custom-control-label font-weight-medium"
                       for="use-upsell"><?php esc_html_e("Use Upsell products", 'checkout-upsell-and-order-bumps'); ?></label>
                <span class="d-block secondary small cuw-px-20px">
                    <?php esc_html_e('This will use the "Upsell products" selected under the "Linked Products" section in the product creation page.', 'checkout-upsell-and-order-bumps'); ?>
                </span>
            </div>
        <?php } ?>

        <?php if (in_array('custom', $use_options)) { ?>
            <div class="custom-control <?php echo $has_pro ? 'use-product' : ''; ?> common-border custom-radio custom-control mb-2 <?php echo ($use_products == 'custom') ? 'selected-border' : ''; ?>">
                <input type="radio" class="use-products-radio position-relative custom-control-input"
                       id="use-frequently"
                       name="data[products][use]" value="custom" <?php checked('custom', $use_products); ?>
                    <?php if (!CUW()->plugin->has_pro) echo 'disabled'; ?>>
                <label class="custom-control-label font-weight-medium" for="use-frequently">
                    <?php esc_html_e("Custom products", 'checkout-upsell-and-order-bumps'); ?>
                    <?php if (!CUW()->plugin->has_pro) { ?>
                        <span class="text-dark"
                              style="font-weight: 400;">[<?php esc_html_e("Unlock this feature by", 'checkout-upsell-and-order-bumps'); ?>
                            <a class="text-decoration-none"
                               href="<?php echo esc_url(CUW()->plugin->getUrl($campaign_type)); ?>"
                               target="_blank"><?php esc_html_e("Upgrading to PRO", 'checkout-upsell-and-order-bumps'); ?></a>]
                        </span>
                    <?php } ?>
                </label>
                <span class="d-block secondary small cuw-px-20px" <?php if (!CUW()->plugin->has_pro) echo 'style="opacity: 0.8;"'; ?>>
                    <?php echo esc_html(__('This will use the products selected under the "Upsell Products" section in the product creation page.', 'checkout-upsell-and-order-bumps')); ?>
                </span>
                <span class="d-block text-dark secondary small cuw-px-20px">
                    <?php
                        // translators: %s product text.
                        echo esc_html(sprintf(__("NOTE: You need to choose the %s products manually when you create or edit the products.", 'checkout-upsell-and-order-bumps'), $products_text));
                    ?>
                </span>
            </div>
        <?php } ?>

        <?php if (in_array('specific', $use_options)) { ?>
            <div class="custom-control use-product common-border custom-radio custom-control mb-2 <?php echo ($use_products == 'specific') ? 'selected-border' : ''; ?>">
                <input type="radio" class="use-products-radio position-relative custom-control-input" id="use-specific"
                       name="data[products][use]" value="specific" <?php checked('specific', $use_products); ?>>
                <label class="custom-control-label font-weight-medium"
                       for="use-specific"><?php esc_html_e("Specific products", 'checkout-upsell-and-order-bumps'); ?></label>
                <span class="d-block secondary small cuw-px-20px">
                    <?php
                        // translators: %s product text.
                        echo esc_html(sprintf(__('This will use the following set of products as %s products.', 'checkout-upsell-and-order-bumps'), $products_text));
                    ?>
                </span>
                <div class="mt-2" id="specific-products"
                     style="margin: 0 24px; display: <?php echo !empty($use_products == 'specific') ? 'block' : 'none'; ?>">
                    <select multiple class="select2-list" name="data[products][ids][]" data-list="products"
                            data-placeholder="<?php esc_html_e("Choose products", 'checkout-upsell-and-order-bumps'); ?>">
                        <?php foreach ($specific_products as $id => $name) { ?>
                            <option value="<?php echo esc_attr($id); ?>"
                                    selected><?php echo esc_html($name); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php } ?>

        <?php if (in_array('single', $use_options)) { ?>
            <div class="custom-control use-product common-border custom-radio custom-control mb-2 <?php echo ($use_products == 'single') ? 'selected-border' : ''; ?>">
                <input type="radio" class="use-products-radio position-relative custom-control-input" id="use-single"
                       name="data[products][use]" value="single" <?php checked('single', $use_products); ?>>
                <label class="custom-control-label font-weight-medium"
                       for="use-single"><?php esc_html_e("Choose a Product", 'checkout-upsell-and-order-bumps'); ?></label>
                <span class="d-block secondary small cuw-px-20px">
                    <?php echo esc_html('This will use the below product to display in mentioned location.'); ?>
                </span>
                <div class="mt-2" id="single-product"
                     style="margin: 0 24px; display: <?php echo !empty($use_products == 'single') ? 'block' : 'none'; ?>">
                    <select class="select2-list" name="data[products][ids][]" data-list="products"
                            data-placeholder="<?php esc_html_e("Choose product", 'checkout-upsell-and-order-bumps'); ?>">
                        <?php foreach ($specific_products as $id => $name) { ?>
                            <option value="<?php echo esc_attr($id); ?>"
                                    selected><?php echo esc_html($name); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php } ?>

        <?php if (in_array('engine', $use_options)) { ?>
            <div class="custom-control <?php echo $has_pro ? 'use-product' : ''; ?> custom-radio common-border custom-control mb-2 <?php echo ($use_products == 'engine') ? 'selected-border' : ''; ?>">
                <input type="radio" class="use-products-radio position-relative custom-control-input" id="use-engine"
                       name="data[products][use]" value="engine" <?php checked('engine', $use_products); ?>
                    <?php if (!CUW()->plugin->has_pro) echo 'disabled'; ?>>
                <label class="custom-control-label font-weight-medium" for="use-engine">
                    <?php esc_html_e("Use Recommendation engine", 'checkout-upsell-and-order-bumps'); ?>
                    <?php if (!CUW()->plugin->has_pro) { ?>
                        <span class="text-dark"
                              style="font-weight: 400;">[<?php esc_html_e("Unlock this feature by", 'checkout-upsell-and-order-bumps'); ?>
                            <a class="text-decoration-none"
                               href="<?php echo esc_url(CUW()->plugin->getUrl($campaign_type)); ?>"
                               target="_blank"><?php esc_html_e("Upgrading to PRO", 'checkout-upsell-and-order-bumps'); ?></a>]
                        </span>
                    <?php } ?>
                </label>
                <span class="d-block secondary small cuw-px-20px">
                    <?php
                        // translators: %s product text.
                        echo esc_html(sprintf(__('This will use the following set of products prepared by engine as %s products.', 'checkout-upsell-and-order-bumps'), $products_text));
                    ?>
                </span>
                <div class="mt-2" id="recommendation-engines"
                     style="margin: 0 24px; display: <?php echo !empty($use_products == 'engine') ? 'block' : 'none'; ?>">
                    <select class="select2-list" name="data[products][engine_id]" data-list="engines"
                            data-campaign_type="<?php echo esc_attr($campaign_type); ?>"
                            data-placeholder="<?php esc_html_e("Choose engine", 'checkout-upsell-and-order-bumps'); ?>">
                        <?php if (!empty($recommendation_engine_id)) { ?>
                            <option value="<?php echo esc_attr($recommendation_engine_id); ?>" selected>
                                <?php echo esc_html($recommendation_engine_name); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php if (!empty($allow_bundle)) { ?>
        <!--        <div class="form-separator mt-2"></div>-->
        <div class="products-bundle p-3">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="is-bundle" name="data[products][bundle]"
                       value="1" <?php if (!empty($products_data['bundle'])) echo 'checked' ?>
                    <?php if (!CUW()->plugin->has_pro) echo 'disabled'; ?>>
                <label class="custom-control-label font-weight-medium" for="is-bundle">
                    <?php esc_html_e("Bundle products", 'checkout-upsell-and-order-bumps'); ?>
                    <?php if (!CUW()->plugin->has_pro) { ?>
                        <span class="text-dark"
                              style="font-weight: 400;">[<?php esc_html_e("Unlock this feature by", 'checkout-upsell-and-order-bumps'); ?>
                            <a class="text-decoration-none"
                               href="<?php echo esc_url(CUW()->plugin->getUrl($campaign_type)); ?>"
                               target="_blank"><?php esc_html_e("Upgrading to PRO", 'checkout-upsell-and-order-bumps'); ?></a>]
                        </span>
                    <?php } ?>
                </label>
                <span class="d-block secondary small" <?php if (!CUW()->plugin->has_pro) echo 'style="opacity: 0.8;"'; ?>>
                    <?php esc_html_e('This will group the selected products as a bundle in the cart.', 'checkout-upsell-and-order-bumps'); ?>
                </span>
                <span class="d-block text-dark secondary small" <?php if (!CUW()->plugin->has_pro) echo 'style="opacity: 0.8;"'; ?>>
                    <?php esc_html_e("NOTE: If the customer removes the main product from the cart, upsell products are also removed.", 'checkout-upsell-and-order-bumps'); ?>
                </span>
            </div>
        </div>
        <div id="bundle-item-quantity" class="row mb-3"
             style="margin: 0 9px; <?php if (empty($products_data['bundle'])) echo 'display: none;' ?>">
            <div class="quantity-field col-md-6 mx-3">
                <label for="quantity-field" class="form-label">
                    <?php esc_html_e("Cart item quantity", 'checkout-upsell-and-order-bumps'); ?>
                </label>
                <select class="form-control" id="quantity-field" name="data[products][quantity_field]"
                    <?php if (empty($products_data['bundle'])) echo 'disabled' ?>>
                    <option value="sync" <?php if ($quantity_field == 'sync') echo "selected"; ?>><?php esc_html_e('Sync quantity with main product quantity', 'checkout-upsell-and-order-bumps'); ?></option>
                    <option value="custom" <?php if ($quantity_field == 'custom') echo "selected"; ?>><?php esc_html_e('Allow customer to change quantity', 'checkout-upsell-and-order-bumps'); ?></option>
                </select>
            </div>
        </div>
    <?php } ?>

    <?php if (!empty($allow_remove)) { ?>
        <!--        <div class="form-separator mt-2"></div>-->
        <div class="px-3 pt-3">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="allow_remove"
                       name="data[products][allow_remove]"
                       value="1" <?php if (!empty($products_data['allow_remove'])) echo 'checked' ?>>
                <label class="custom-control-label font-weight-medium" for="allow_remove">
                    <?php esc_html_e("Allow customers to remove Add-ons from the cart?", 'checkout-upsell-and-order-bumps'); ?>
                </label>
            </div>
        </div>
    <?php } ?>

    <?php if (!empty($change_quantity)) { ?>
        <!--        --><?php //if (empty($allow_remove)) echo '<div class="form-separator mt-2"></div>'; ?>

        <div id="item-quantity" class="row p-3">
            <div class="quantity-field col-md-6">
                <label for="quantity-field" class="form-label">
                    <?php esc_html_e("Cart item quantity", 'checkout-upsell-and-order-bumps'); ?>
                </label>
                <select class="form-control" id="quantity-field" name="data[products][quantity_field]">
                    <option value="sync" <?php if ($quantity_field == 'sync') echo "selected"; ?>><?php esc_html_e('Sync quantity with main product quantity', 'checkout-upsell-and-order-bumps'); ?></option>
                    <option value="custom" <?php if ($quantity_field == 'custom') echo "selected"; ?>><?php esc_html_e('Allow customer to change quantity', 'checkout-upsell-and-order-bumps'); ?></option>
                    <option value="fixed" <?php if ($quantity_field == 'fixed') echo "selected"; ?>><?php esc_html_e('Fixed quantity', 'checkout-upsell-and-order-bumps'); ?></option>
                </select>
            </div>
            <div id="quantity-value" class="quantity-value col-md-6"
                 style="display: <?php echo ($quantity_field == 'fixed') ? 'block' : 'none'; ?>">
                <label for="quantity-value" class="form-label">
                    <?php esc_html_e("Value", 'checkout-upsell-and-order-bumps'); ?>
                </label>
                <input type="number" class="form-control" name="data[products][quantity_value]"
                       value="<?php echo esc_attr($quantity_value); ?>" <?php if ($quantity_field != 'fixed') echo 'disabled' ?>/>
            </div>
        </div>
    <?php } ?>
    <?php do_action('cuw_campaign_after_change_quantity_section', $campaign, $products_data); ?>

    <?php if (!empty($change_variant)) { ?>
        <input type="hidden" name="data[products][change_variant]" value="1">
    <?php } ?>
</div>