<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$is_rtl = \CUW\App\Helpers\WP::isRtl();
$campaign_data = !empty($campaign['data']) ? $campaign['data'] : [];

$triggers = \CUW\App\Pro\Modules\Campaigns\UpsellPopups::getTriggers();
$use_trigger = !empty($campaign_data['triggers']) ? $campaign_data['triggers'] : [];
?>
<div id="cuw-triggers">
    <label class="form-label font-weight-medium"><?php esc_html_e("Choose when to trigger the Upsell Popup", 'checkout-upsell-woocommerce'); ?></label>
    <div class="cuw-trigger-message text-center mt-3 text-danger" style="display: none">
        <?php esc_html_e('At least one trigger is required', 'checkout-upsell-woocommerce'); ?>
    </div>
    <?php foreach ($triggers as $key => $trigger) {
        $product_suggestion = !empty($campaign_data['trigger_options'][$key]['suggestion_method']) ? $campaign_data['trigger_options'][$key]['suggestion_method'] : 'cart_products';
        $filter_type = !empty($campaign_data['trigger_options'][$key]['filter']['type']) ? $campaign_data['trigger_options'][$key]['filter']['type'] : 'all_products';
        $filter_method = !empty($campaign_data['trigger_options'][$key]['filter']['method']) ? $campaign_data['trigger_options'][$key]['filter']['method'] : '';
        $filter_values = !empty($campaign_data['trigger_options'][$key]['filter']['values']) ? $campaign_data['trigger_options'][$key]['filter']['values'] : [];
        ?>
        <div class="custom-control custom-checkbox custom-control mb-2 cuw-trigger">
            <input type="checkbox" class="custom-control-input" id="<?php echo esc_attr(str_replace('_', '-', $key)) ?>"
                   name="data[triggers][]"
                   value="<?php echo esc_attr($key) ?>" <?php if (in_array($key, $use_trigger)) echo 'checked'; ?>>
            <label class="custom-control-label font-weight-medium"
                   for="<?php echo esc_attr(str_replace('_', '-', $key)) ?>"><?php echo esc_html($trigger['title']); ?></label>
            <span class="d-block secondary small">
                <?php echo wp_kses_post($trigger['description']); ?>
            </span>
            <?php if ($key == 'added_to_cart') { ?>
                <div>
                    <div id="trigger-advanced-settings" class="trigger-options <?php echo $is_rtl ? 'ml-4' : 'mr-4' ?>"
                         style="display: <?php echo (!in_array('added_to_cart', $use_trigger) || $product_suggestion == 'cart_products' && $filter_type == 'all_products') ? 'none' : ''; ?>;">
                        <div class="mt-2 py-1 border-top border-gray-light">
                            <div id="triggers-filter">
                                <label class="form-label"><?php esc_html_e("Popup shows to", 'checkout-upsell-woocommerce'); ?></label>
                                <div style="display: flex; gap: 24px;">
                                    <div class="custom-control custom-radio custom-control mb-2">
                                        <input type="radio" class="custom-control-input" id="all-products"
                                               name="data[trigger_options][<?php echo esc_attr($key); ?>][filter][type]"
                                               value="all_products" <?php if ($filter_type == 'all_products') echo 'checked'; ?>
                                               checked>
                                        <label class="custom-control-label font-weight-medium"
                                               for="all-products"><?php esc_html_e('All products', 'checkout-upsell-woocommerce'); ?></label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control mb-2">
                                        <input type="radio" class="custom-control-input" id="specific-products"
                                               name="data[trigger_options][<?php echo esc_attr($key); ?>][filter][type]"
                                               value="products" <?php if ($filter_type == 'products') echo 'checked'; ?>>
                                        <label class="custom-control-label font-weight-medium"
                                               for="specific-products"><?php esc_html_e('Specific products', 'checkout-upsell-woocommerce'); ?></label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control mb-2">
                                        <input type="radio" class="custom-control-input" id="specific-categories"
                                               name="data[trigger_options][<?php echo esc_attr($key); ?>][filter][type]"
                                               value="categories" <?php if ($filter_type == 'categories') echo 'checked'; ?>>
                                        <label class="custom-control-label font-weight-medium"
                                               for="specific-categories"><?php esc_html_e('Specific categories', 'checkout-upsell-woocommerce'); ?></label>
                                    </div>
                                </div>
                                <div id="choose-specific-products" class="form-group my-1"
                                     style="display:<?php echo ($filter_type == 'products') ? '' : 'none'; ?>">
                                    <div style="display: flex; gap:10px;">
                                        <div class="filter-method w-25">
                                            <select class="form-control"
                                                    name="data[trigger_options][<?php echo esc_attr($key); ?>][filter][method]" <?php echo $filter_type == 'products' ? '' : 'disabled' ?>>
                                                <option value="in_list" <?php if ($filter_method == 'in_list') echo "selected"; ?>
                                                        selected><?php esc_html_e("In list", 'checkout-upsell-woocommerce'); ?></option>
                                                <option value="not_in_list" <?php if ($filter_method == 'not_in_list') echo "selected"; ?>><?php esc_html_e("Not in list", 'checkout-upsell-woocommerce'); ?></option>
                                            </select>
                                        </div>
                                        <div class="w-50">
                                            <select multiple class="select2-list"
                                                    name="data[trigger_options][<?php echo esc_attr($key); ?>][filter][values][]"
                                                    data-list="products"
                                                    data-placeholder=" <?php esc_html_e("Choose products", 'checkout-upsell-woocommerce'); ?>" <?php echo $filter_type == 'products' ? '' : 'disabled' ?>>
                                                <?php foreach ($filter_values as $id) { ?>
                                                    <option value="<?php echo esc_attr($id); ?>" selected>
                                                        <?php echo esc_html(CUW()->wc->getProductTitle($id, true)); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <p class="cuw-trigger-filter-message d-none text-danger"><?php esc_html_e("This field is required", 'checkout-upsell-woocommerce'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div id="choose-specific-categories" class="form-group my-1"
                                     style="display:<?php echo $filter_type == 'categories' ? '' : 'none'; ?>">
                                    <div style="display: flex; gap:10px;">
                                        <div class="filter-method w-25">
                                            <select class="form-control"
                                                    name="data[trigger_options][<?php echo esc_attr($key); ?>][filter][method]" <?php echo $filter_type == 'categories' ? '' : 'disabled' ?>>
                                                <option value="in_list" <?php if ($filter_method == 'in_list') echo "selected"; ?>><?php esc_html_e("In list", 'checkout-upsell-woocommerce'); ?></option>
                                                <option value="not_in_list" <?php if ($filter_method == 'not_in_list') echo "selected"; ?>><?php esc_html_e("Not in list", 'checkout-upsell-woocommerce'); ?></option>
                                            </select>
                                        </div>
                                        <div class="w-50">
                                            <select multiple class="select2-list"
                                                    name="data[trigger_options][<?php echo esc_attr($key); ?>][filter][values][]"
                                                    data-list="taxonomies" data-taxonomy="product_cat"
                                                    data-placeholder="<?php esc_html_e("Choose categories", 'checkout-upsell-woocommerce'); ?>" <?php echo $filter_type == 'categories' ? '' : 'disabled' ?>>
                                                <?php foreach ($filter_values as $id) { ?>
                                                    <option value="<?php echo esc_attr($id); ?>" selected>
                                                        <?php echo esc_html(CUW()->wc->getTaxonomyName($id, true)); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <p class="cuw-trigger-filter-message d-none text-danger"><?php esc_html_e("This field is required", 'checkout-upsell-woocommerce'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="my-1 pt-2 border-top border-gray-light">
                            <label class="form-label"><?php esc_html_e("Product suggestion method works based on", 'checkout-upsell-woocommerce'); ?></label>
                            <div style="display: flex; gap: 24px;">
                                <div class="custom-control custom-radio custom-control mb-2">
                                    <input type="radio" class="custom-control-input" id="cart-product-suggestion"
                                           name="data[trigger_options][<?php echo esc_attr($key); ?>][suggestion_method]"
                                           value="cart_products"
                                        <?php if ($product_suggestion == 'cart_products') echo 'checked'; ?> checked>
                                    <label class="custom-control-label font-weight-medium"
                                           for="cart-product-suggestion"><?php esc_html_e('Cart products', 'checkout-upsell-woocommerce'); ?></label>
                                </div>
                                <div class="custom-control custom-radio custom-control mb-2">
                                    <input type="radio" class="custom-control-input" id="current-product-suggestion"
                                           name="data[trigger_options][<?php echo esc_attr($key); ?>][suggestion_method]"
                                           value="current_product"
                                        <?php if ($product_suggestion == 'current_product') echo 'checked'; ?>>
                                    <label class="custom-control-label font-weight-medium"
                                           for="current-product-suggestion"><?php esc_html_e('Added product (recently)', 'checkout-upsell-woocommerce'); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="action-toggle-config" class="mb-2"
                         style="display: <?php echo in_array($key, $use_trigger) ? '' : 'none'; ?>"
                         data-show="<?php esc_attr_e("Show advanced configurations", 'checkout-upsell-woocommerce'); ?>"
                         data-hide="<?php esc_html_e("Hide advanced configurations", 'checkout-upsell-woocommerce'); ?>">
                        <a class="text-decoration-none d-flex align-items-center small"
                           style="font-weight: 500; cursor: pointer; gap: 6px;">
                            <?php if ($product_suggestion == 'cart_products' && $filter_type == 'all_products') {
                                $text = __('Show advanced configurations', 'checkout-upsell-woocommerce');
                                $class = 'down';
                            } else {
                                $text = __('Hide advanced configurations', 'checkout-upsell-woocommerce');
                                $class = 'up';
                            } ?>
                            <i class="cuw-icon-<?php echo esc_attr($class); ?> inherit-color"></i>
                            <span><?php echo esc_html($text); ?></span>
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
