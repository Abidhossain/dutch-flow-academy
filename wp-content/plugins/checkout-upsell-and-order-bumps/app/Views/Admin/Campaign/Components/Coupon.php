<?php
defined('ABSPATH') || exit;
if (!isset($campaign)) {
    return;
}

$discount = isset($campaign['data']['discount']) ? (array)$campaign['data']['discount'] : [];
$coupon = isset($campaign['data']['coupon']) ? (array)$campaign['data']['coupon'] : [];
$list_statuses = CUW()->wc->getOrderStatuses();
$order_statuses = array_flip(isset($campaign['data']['order_statuses']) ? $campaign['data']['order_statuses'] : ['wc-processing', 'wc-completed']);
foreach ($order_statuses as $slug => $status) {
    if (isset($list_statuses[$slug])) {
        $order_statuses[$slug] = $list_statuses[$slug];
    }
}
$failed_order_statuses = array_flip(isset($campaign['data']['failed_order_statuses']) ? $campaign['data']['failed_order_statuses'] : ['wc-cancelled', 'wc-refunded']);
$failed_order_statuses_list = array_intersect_key($list_statuses, array_flip(CUW()->wc->getOrderStatuses('failed')));
?>

<div id="cuw-action">
    <?php
    $discount_type = isset($discount['type']) ? $discount['type'] : 'percentage';
    $discount_value = isset($discount['value']) ? $discount['value'] : '';

    $coupon_prefix = isset($coupon['prefix']) ? $coupon['prefix'] : 'NOC-';
    $coupon_length = isset($coupon['length']) ? $coupon['length'] : '6';

    $minimum_spent = isset($coupon['minimum_amount']) ? $coupon['minimum_amount'] : '';
    $maximum_spent = isset($coupon['maximum_amount']) ? $coupon['maximum_amount'] : '';

    $individual_use = isset($coupon['individual_use']);
    $exclude_sale_items = isset($coupon['exclude_sale_items']);
    $free_shipping = isset($coupon['free_shipping']);
    $allow_sharing = isset($coupon['allow_sharing']);

    $expire_after_x_days = \CUW\App\Modules\Campaigns\NOC::getExpireDays($coupon);
    $limit_usage_to_x_items = isset($coupon['limit_usage_to_x_items']) ? $coupon['limit_usage_to_x_items'] : '';

    $product_ids = !empty($coupon['product_ids']) ? $coupon['product_ids'] : [];
    $exclude_product_ids = !empty($coupon['exclude_product_ids']) ? $coupon['exclude_product_ids'] : [];

    $product_categories = !empty($coupon['product_categories']) ? $coupon['product_categories'] : [];
    $exclude_product_categories = !empty($coupon['exclude_product_categories']) ? $coupon['exclude_product_categories'] : [];
    ?>
    <div class="p-3">
        <div class="row mb-0 cuw-discount">
            <div class="col-md-6 cuw-discount-type form-group mb-0">
                <label for="action-discount-type"
                       class="form-label"><?php esc_html_e("Discount type", 'checkout-upsell-and-order-bumps'); ?></label>
                <select class="form-control" id="action-discount-type" name="data[discount][type]">
                    <option value="percentage" <?php selected('percentage', $discount_type); ?>><?php esc_html_e("Percentage discount", 'checkout-upsell-and-order-bumps'); ?></option>
                    <option value="fixed_price" <?php selected('fixed_price', $discount_type); ?>><?php esc_html_e("Fixed cart discount", 'checkout-upsell-and-order-bumps'); ?></option>
                </select>
            </div>
            <div class="col-md-6 cuw-discount-value form-group mb-0"
                 style="<?php if (in_array($discount_type, ['free', 'no_discount'])) echo 'display: none;' ?>">
                <label for="action-discount-value"
                       class="form-label"><?php esc_html_e("Discount value", 'checkout-upsell-and-order-bumps'); ?></label>
                <input class="form-control" type="number" id="action-discount-value" name="data[discount][value]"
                       min="0" value="<?php echo esc_attr($discount_value); ?>"
                       placeholder="<?php esc_attr_e("Value", 'checkout-upsell-and-order-bumps'); ?>">
            </div>
        </div>
        <div class="row mt-2 mb-0 cuw-coupon">
            <div class="col-md-6 form-group mb-0">
                <label for="action-coupon-prefix"
                       class="form-label"><?php esc_html_e("Coupon prefix", 'checkout-upsell-and-order-bumps'); ?></label>
                <input class="form-control" type="text" id="action-coupon-prefix" name="data[coupon][prefix]"
                       value="<?php echo esc_attr($coupon_prefix); ?>"
                       placeholder="<?php esc_attr_e("Coupon prefix", 'checkout-upsell-and-order-bumps'); ?>">
            </div>
            <div class="col-md-6 cuw-discount-label form-group mb-0">
                <label for="action-coupon-length"
                       class="form-label"><?php esc_html_e("Coupon length (without prefix)", 'checkout-upsell-and-order-bumps'); ?></label>
                <input class="form-control" type="number" id="action-coupon-length" name="data[coupon][length]"
                       value="<?php echo esc_attr($coupon_length); ?>" min="6" max="16">
            </div>
        </div>
        <div id="action-config" style="display: none;">
            <div class="row mt-2 mb-0 cuw-coupon">
                <div class="col-md-6 form-group mb-0">
                    <label for="action-coupon-minimum-spent"
                           class="form-label"><?php esc_html_e("Minimum spent", 'checkout-upsell-and-order-bumps'); ?></label>
                    <input class="form-control" type="number" id="action-coupon-minimum-spent"
                           name="data[coupon][minimum_amount]" value="<?php echo esc_attr($minimum_spent); ?>" min="0"
                           placeholder="<?php esc_attr_e("No minimum", 'checkout-upsell-and-order-bumps'); ?>">
                </div>
                <div class="col-md-6 form-group mb-0">
                    <label for="action-coupon-maximum-spent"
                           class="form-label"><?php esc_html_e("Maximum spent", 'checkout-upsell-and-order-bumps'); ?></label>
                    <input class="form-control" type="number" id="action-coupon-maximum-spent"
                           name="data[coupon][maximum_amount]" value="<?php echo esc_attr($maximum_spent); ?>" min="0"
                           placeholder="<?php esc_attr_e("No maximum", 'checkout-upsell-and-order-bumps'); ?>">
                </div>

                <div class="col-md-6 form-group mb-0">
                    <div class="custom-control mt-3 custom-checkbox custom-control">
                        <input type="checkbox" class="custom-control-input" id="individual-use"
                               name="data[coupon][individual_use]" value="1" <?php checked(true, $individual_use); ?>>
                        <label class="custom-control-label"
                               for="individual-use"><?php esc_html_e("Individual use only", 'checkout-upsell-and-order-bumps'); ?></label>
                    </div>
                </div>
                <div class="col-md-6 form-group mb-0">
                    <div class="custom-control mt-3 custom-checkbox custom-control">
                        <input type="checkbox" class="custom-control-input" id="exclude-sale-items"
                               name="data[coupon][exclude_sale_items]"
                               value="1" <?php checked(true, $exclude_sale_items); ?>>
                        <label class="custom-control-label"
                               for="exclude-sale-items"><?php esc_html_e("Exclude sale items", 'checkout-upsell-and-order-bumps'); ?></label>
                    </div>
                </div>
                <div class="col-md-6 form-group mb-0">
                    <div class="custom-control mt-3 custom-checkbox custom-control">
                        <input type="checkbox" class="custom-control-input" id="allow-free-shipping"
                               name="data[coupon][free_shipping]" value="1" <?php checked(true, $free_shipping); ?>>
                        <label class="custom-control-label"
                               for="allow-free-shipping"><?php esc_html_e("Allow free shipping", 'checkout-upsell-and-order-bumps'); ?></label>
                    </div>
                </div>
                <div class="col-md-6 form-group mb-0">
                    <div class="custom-control mt-3 custom-checkbox custom-control">
                        <input type="checkbox" class="custom-control-input" id="allow-sharing"
                               name="data[coupon][allow_sharing]" value="1" <?php checked(true, $allow_sharing); ?>>
                        <label class="custom-control-label"
                               for="allow-sharing"><?php esc_html_e("Allow sharing", 'checkout-upsell-and-order-bumps'); ?></label>
                    </div>
                </div>

                <div class="col-md-6 form-group mt-2 mb-0">
                    <label class="form-label"><?php esc_html_e("Products", 'checkout-upsell-and-order-bumps'); ?></label>
                    <select multiple class="select2-list" name="data[coupon][product_ids][]" data-list="products"
                            data-placeholder=" <?php esc_html_e("Choose products", 'checkout-upsell-and-order-bumps'); ?>">
                        <?php foreach ($product_ids as $id) { ?>
                            <option value="<?php echo esc_attr($id); ?>" selected>
                                <?php echo esc_html(CUW()->wc->getProductTitle($id, true)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6 form-group mt-2 mb-0">
                    <label class="form-label"><?php esc_html_e("Exclude products", 'checkout-upsell-and-order-bumps'); ?></label>
                    <select multiple class="select2-list" name="data[coupon][exclude_product_ids][]"
                            data-list="products"
                            data-placeholder=" <?php esc_html_e("Choose products", 'checkout-upsell-and-order-bumps'); ?>">
                        <?php foreach ($exclude_product_ids as $id) { ?>
                            <option value="<?php echo esc_attr($id); ?>" selected>
                                <?php echo esc_html(CUW()->wc->getProductTitle($id, true)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-6 form-group mt-2 mb-0">
                    <label class="form-label"><?php esc_html_e("Categories", 'checkout-upsell-and-order-bumps'); ?></label>
                    <select multiple class="select2-list" name="data[coupon][product_categories][]"
                            data-list="taxonomies" data-taxonomy="product_cat"
                            data-placeholder=" <?php esc_html_e("Choose categories", 'checkout-upsell-and-order-bumps'); ?>">
                        <?php foreach ($product_categories as $id) { ?>
                            <option value="<?php echo esc_attr($id); ?>" selected>
                                <?php echo esc_html(CUW()->wc->getTaxonomyName($id, true)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6 form-group mt-2 mb-0">
                    <label class="form-label"><?php esc_html_e("Exclude categories", 'checkout-upsell-and-order-bumps'); ?></label>
                    <select multiple class="select2-list" name="data[coupon][exclude_product_categories][]"
                            data-list="taxonomies" data-taxonomy="product_cat"
                            data-placeholder=" <?php esc_html_e("Choose categories", 'checkout-upsell-and-order-bumps'); ?>">
                        <?php foreach ($exclude_product_categories as $id) { ?>
                            <option value="<?php echo esc_attr($id); ?>" selected>
                                <?php echo esc_html(CUW()->wc->getTaxonomyName($id, true)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="action-toggle-config" class="mt-3"
             data-show="<?php esc_attr_e("Show advanced settings", 'checkout-upsell-and-order-bumps'); ?>"
             data-hide="<?php esc_html_e("Hide advanced settings", 'checkout-upsell-and-order-bumps'); ?>">
            <a class="text-decoration-none d-flex align-items-center small"
               style="font-weight: 500; cursor: pointer; gap: 6px;">
                <i class="cuw-icon-down inherit-color"></i>
                <span><?php esc_html_e("Show advanced settings", 'checkout-upsell-and-order-bumps'); ?></span>
            </a>
        </div>
    </div>

    <div class="form-separator m-0"></div>

    <div class="row p-3">
        <div class="col-md-6 when-generate-coupon">
            <label for="display-location"
                   class="form-label"><?php esc_html_e("Generate and show coupons for the following order status", 'checkout-upsell-and-order-bumps'); ?></label>
            <select multiple class="select2-local" name="data[order_statuses][]"
                    data-placeholder=" <?php esc_html_e("Choose order statuses", 'checkout-upsell-and-order-bumps'); ?>">
                <?php foreach ($list_statuses as $slug => $name) { ?>
                    <option value="<?php echo esc_attr($slug); ?>" <?php if (isset($order_statuses[$slug])) echo "selected"; ?>><?php echo esc_html($name); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-6 form-group my-0">
            <label for="action-coupon-length"
                   class="form-label"><?php esc_html_e("Generated coupon expires after X days", 'checkout-upsell-and-order-bumps'); ?></label>
            <input type="number" class="form-control" name="data[coupon][expire_after_x_days]"
                   value="<?php echo esc_attr($expire_after_x_days); ?>"
                   placeholder="<?php esc_html_e("Never expire", 'checkout-upsell-and-order-bumps'); ?>">
        </div>
    </div>

    <div class="row px-3 pb-3">
        <div class="col-md-6 when-generate-coupon">
            <label for="display-location"
                   class="form-label"><?php esc_html_e("Coupons should be invalidated for the following order statuses.", 'checkout-upsell-and-order-bumps'); ?></label>
            <select multiple class="select2-local" name="data[failed_order_statuses][]"
                    data-placeholder=" <?php esc_html_e("Choose order statuses", 'checkout-upsell-and-order-bumps'); ?>">
                <?php foreach ($failed_order_statuses_list as $slug => $name) { ?>
                    <option value="<?php echo esc_attr($slug); ?>" <?php if (isset($failed_order_statuses[$slug])) echo "selected"; ?>><?php echo esc_html($name); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
</div>
