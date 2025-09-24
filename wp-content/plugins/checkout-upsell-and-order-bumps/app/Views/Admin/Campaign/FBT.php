<?php
defined('ABSPATH') || exit;
if (!isset($action)) {
    return;
}
$campaign_type = 'fbt';
$display_locations = \CUW\App\Modules\Campaigns\FBT::getDisplayLocations();
?>

<?php if ($action == 'product_edit' && isset($post_id) && isset($product_ids)): ?>
    <?php
    $campaign = !empty($matched_campaign) ? (array)$matched_campaign : [];
    ?>
    <div class="options_group cuw-fbt-products" style="display: flex; margin-top: 14px;">
        <style>
            #cuw-fbt-suggestions table {
                border-collapse: collapse;
            }

            #cuw-fbt-suggestions td, #cuw-fbt-suggestions th {
                border: 1px solid #ddd;
                padding: 4px 8px;
            }

            #cuw-fbt-suggestions th {
                padding: 10px 8px;
                text-align: left;
                background: #2271b1;
                color: white;
                border: none;
            }

            #cuw-fbt-suggestions .fbt-product-image {
                padding: 0
            }

            #cuw-fbt-suggestions .fbt-product-image img {
                height: 100%;
                width: 100%;
                vertical-align: middle;
            }

            #cuw-fbt-suggestions .fbt-no-products {
                text-align: center;
                padding: 8px !important;
            }

            #cuw-fbt-suggestions tr:nth-child(odd) {
                background: #ffffff;
            }

            #cuw-fbt-suggestions tr:nth-child(even) {
                background: #f8f8f8;
            }

            #cuw-fbt-suggestions tr:hover {
                background: #eee;
            }
        </style>

        <p class="form-field">
            <label for="cuw-fbt-products"><?php esc_html_e('Frequently Bought Together Products', 'checkout-upsell-and-order-bumps'); ?></label>
        </p>
        <div style="display: flex; flex-direction: column; width: 100%">
            <select class="wc-product-search" multiple="multiple" id="cuw-fbt-products"
                    name="cuw_fbt_product_ids[]" style="width: 50%;"
                    data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'checkout-upsell-and-order-bumps'); ?>"
                    data-action="woocommerce_json_search_products_and_variations"
                    data-exclude="<?php echo intval($post_id); ?>">
                <?php foreach ($product_ids as $product_id) {
                    if (is_object($product = \CUW\App\Helpers\WC::getProduct($product_id))) {
                        echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . esc_html(wp_strip_all_tags($product->get_formatted_name())) . '</option>';
                    }
                } ?>
            </select>
            <div class="options_group cuw-fbt-campaign">
                <div style="margin: 8px 0;">
                    <span style="display: none">
                        <?php esc_html_e("Linked campaign", 'checkout-upsell-and-order-bumps'); ?>:
                        <a target="_blank"
                           href="<?php echo esc_url(\CUW\App\Helpers\Campaign::getEditUrl($campaign)); ?>"
                           style="text-decoration: none; font-weight: bold;">
                            <span class="dashicons dashicons-admin-links"
                                  style="vertical-align: text-top; font-size: 14px;"></span>
                            <?php echo esc_html(\CUW\App\Helpers\Campaign::getTitle($campaign, true)); ?>
                        </a>
                    </span>
                    <span>
                        <a id="cuw-toggle-fbt-suggestions" style="text-decoration: none; font-weight: bold;"
                           data-show_text="<?php esc_html_e('Show suggestions', 'checkout-upsell-and-order-bumps'); ?>"
                           data-hide_text="<?php esc_html_e('Hide suggestions', 'checkout-upsell-and-order-bumps'); ?>">
                            <span class="dashicons dashicons-arrow-down"></span>
                            <span class="cuw-suggestion-text"><?php esc_html_e('Show suggestions', 'checkout-upsell-and-order-bumps'); ?></span>
                        </a>
                    </span>
                </div>
                <div id="cuw-fbt-suggestions" style="display: none;">
                    <table style="width: 60%; margin: 0;">
                        <thead>
                        <tr>
                            <th style="width: 24px;"></th>
                            <th><?php esc_html_e("Product", 'checkout-upsell-and-order-bumps'); ?></th>
                            <th style="max-width: 100px; text-align: center;"><?php esc_html_e("Price", 'checkout-upsell-and-order-bumps'); ?></th>
                            <th style="max-width: 80px; text-align: center;"><?php esc_html_e("Purchase count", 'checkout-upsell-and-order-bumps'); ?></th>
                            <th style="width: 24px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($products_from_orders)) {
                            $count = 0;
                            foreach ($products_from_orders as $product_id => $purchase_count) {
                                if (!in_array($product_id, $product_ids)) {
                                    $product = \CUW\App\Helpers\WC::getProduct($product_id);
                                    if (is_object($product)) { ?>
                                        <tr class="fbt-product" data-id="<?php echo esc_attr($product_id); ?>">
                                            <td class="fbt-product-image"><?php echo wp_kses_post($product->get_image()); ?></td>
                                            <td class="fbt-product-title"><?php echo esc_html(wp_strip_all_tags($product->get_formatted_name())); ?>
                                            <td class="fbt-product-price"
                                                style="text-align: right;"><?php echo wp_kses_post(wc_price($product->get_price())); ?></td>
                                            <td style="text-align: right;"><?php echo intval($purchase_count); ?></td>
                                            <td class="fbt-add-product"
                                                style="text-align: center; font-weight: bold; font-size: 150%; color: #2271b1;">
                                                +
                                            </td>
                                        </tr>
                                    <?php }
                                    $count++;
                                }
                            }
                            if ($count == 0) {
                                echo '<tr><td class="fbt-no-products" colspan="5">' . esc_html__("No more products.", 'checkout-upsell-and-order-bumps') . '</td></tr>';
                            }
                        } else {
                            echo '<tr><td class="fbt-no-products" colspan="5">' . esc_html__("No products found.", 'checkout-upsell-and-order-bumps') . '</td></tr>';
                        } ?>
                        </tbody>
                    </table>
                    <?php if (!empty($products_from_orders)) { ?>
                        <div style="width: 60%; margin-top: 8px; margin-bottom: 12px;">
                            <?php esc_html_e('NOTE: These suggestions are based on past orders. You can use these suggestions or you can search and add other products to the campaign.', 'checkout-upsell-and-order-bumps'); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(function ($) {
            $(document).ready(function () {
                $("#cuw-toggle-fbt-suggestions").click(function () {
                    let suggestions = $("#cuw-fbt-suggestions");
                    $(this).find('.dashicons').toggleClass("dashicons-arrow-up dashicons-arrow-down");
                    $(this).find('.cuw-suggestion-text').html(suggestions.is(':hidden') ? $(this).data('hide_text') : $(this).data('show_text'));
                    suggestions.slideToggle();
                });

                $("#cuw-fbt-suggestions .fbt-add-product").click(function () {
                    let id = $(this).closest('tr').data('id');
                    let title = $(this).closest('tr').find('.fbt-product-title').html();
                    $("#cuw-fbt-products").prepend('<option value="' + id + '" selected>' + title + '</option>').trigger('change');
                    $(this).closest('tr').fadeOut(300, function () {
                        $(this).remove();
                    });
                });
            });
        });
    </script>
<?php elseif ($action == 'campaign_edit' && isset($campaign)): ?>
    <?php
    CUW()->view('Admin/Components/Accordion', [
        'id' => 'use_products',
        'title' => 'Products',
        'icon' => 'product',
        'view' => 'Admin/Campaign/Components/Products',
        'data' => [
            'campaign' => $campaign,
            'use_options' => ['related', 'cross_sell', 'upsell', 'custom', 'specific', 'engine'],
            'default_use' => 'related',
            'allow_bundle' => true,
            'products_text' => __('Frequently Bought Together', 'checkout-upsell-and-order-bumps'),
        ],
    ]);
    CUW()->view('Admin/Components/Accordion', [
        'id' => 'discount',
        'title' => 'Discount',
        'icon' => 'discount',
        'view' => 'Admin/Campaign/Components/DiscountBundle',
        'data' => ['campaign' => $campaign],
    ]);

    CUW()->view('Admin/Components/Accordion', [
        'id' => 'template',
        'title' => 'Template',
        'icon' => 'campaigns',
        'view' => 'Admin/Campaign/Components/Template',
        'data' => [
            'campaign' => $campaign,
            'display_locations' => $display_locations,
            'display_location_text' => __('Display location on Product page', 'checkout-upsell-and-order-bumps'),
        ],
    ]);
    ?>
<?php elseif ($action == 'deprecated_product_edit' && isset($post_id) && isset($product_ids)): ?>
    <?php
    $campaign = !empty($matched_campaign) ? (array)$matched_campaign : [];
    $discount_apply_to = !empty($products_discount['apply_to']) ? $products_discount['apply_to'] : 'no_products';
    $discount_type = !empty($products_discount['type']) ? $products_discount['type'] : 'percentage';
    $discount_value = !empty($products_discount['value']) ? $products_discount['value'] : '';
    ?>
    <div id="cuw_fbt_product_data" class="panel woocommerce_options_panel hidden">
        <div class="inline notice notice-warning woocommerce-message" style="margin: 9px 12px;">
            <p style="margin: 0; padding: 6px 0; font-size: 13px;">
                <?php esc_html_e('This section was deprecated since v2.0.0. Please use "Upsell Products" tab instead of this tab.', 'checkout-upsell-and-order-bumps'); ?>
            </p>
        </div>

        <style>
            #cuw-fbt-product-suggestions table {
                margin: 6px 12px 12px 12px;
                border-collapse: collapse;
            }

            #cuw-fbt-product-suggestions td, #cuw-fbt-product-suggestions th {
                border: 1px solid #ddd;
                padding: 4px 8px;
            }

            #cuw-fbt-product-suggestions th {
                padding: 10px 8px;
                text-align: left;
                background: #2271b1;
                color: white;
                border: none;
            }

            #cuw-fbt-product-suggestions .fbt-product-image {
                padding: 0
            }

            #cuw-fbt-product-suggestions .fbt-product-image img {
                height: 100%;
                width: 100%;
                vertical-align: middle;
            }

            #cuw-fbt-product-suggestions .fbt-no-products {
                text-align: center;
                padding: 8px !important;
            }

            #cuw-fbt-product-suggestions tr:nth-child(odd) {
                background: #ffffff;
            }

            #cuw-fbt-product-suggestions tr:nth-child(even) {
                background: #f8f8f8;
            }

            #cuw-fbt-product-suggestions tr:hover {
                background: #eee;
            }
        </style>

        <div class="options_group cuw-fbt-products">
            <p class="form-field">
                <label for="cuw-fbt-products-list"><?php esc_html_e('Frequently Bought Together Products', 'checkout-upsell-and-order-bumps'); ?></label>
                <select class="wc-product-search" multiple="multiple" id="cuw-fbt-products-list"
                        name="cuw_fbt_product_ids[]" style="width: 50%;"
                        data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'checkout-upsell-and-order-bumps'); ?>"
                        data-action="woocommerce_json_search_products_and_variations"
                        data-exclude="<?php echo intval($post_id); ?>">
                    <?php foreach ($product_ids as $product_id) {
                        if (is_object($product = CUW()->wc->getProduct($product_id))) {
                            echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . esc_html(wp_strip_all_tags($product->get_formatted_name())) . '</option>';
                        }
                    } ?>
                </select>
            </p>

            <p class="form-field">
                <label style="width: calc(50% + 86px); margin-right: 10px;"><?php esc_html_e('See a list of products that you can show as Frequently Bought Together based on past orders in the store.', 'checkout-upsell-and-order-bumps'); ?></label>
                <button id="cuw-fbt-toggle-product-suggestions" type="button"
                        class="button-primary button-small cuw-show" style="min-width: 54px; text-align: center;"
                        data-i18n_show="<?php esc_html_e('Show', 'checkout-upsell-and-order-bumps'); ?>"
                        data-i18n_hide="<?php esc_html_e('Hide', 'checkout-upsell-and-order-bumps'); ?>">
                    <?php esc_html_e('Show', 'checkout-upsell-and-order-bumps'); ?>
                </button>
            </p>
            <div id="cuw-fbt-product-suggestions" style="display: none;">
                <table style="width: calc(50% + 60px)">
                    <thead>
                    <tr>
                        <th style="width: 24px;"></th>
                        <th><?php esc_html_e("Product", 'checkout-upsell-and-order-bumps'); ?></th>
                        <th style="max-width: 100px; text-align: center;"><?php esc_html_e("Price", 'checkout-upsell-and-order-bumps'); ?></th>
                        <th style="max-width: 100px; text-align: center;"><?php esc_html_e("Purchase count", 'checkout-upsell-and-order-bumps'); ?></th>
                        <th style="width: 24px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($products_from_orders)) {
                        $count = 0;
                        foreach ($products_from_orders as $product_id => $purchase_count) {
                            if (!in_array($product_id, $product_ids)) {
                                $product = \CUW\App\Helpers\WC::getProduct($product_id);
                                if (is_object($product)) { ?>
                                    <tr class="fbt-product" data-id="<?php echo esc_attr($product_id); ?>">
                                        <td class="fbt-product-image"><?php echo wp_kses_post($product->get_image()); ?></td>
                                        <td class="fbt-product-title"><?php echo esc_html(wp_strip_all_tags($product->get_formatted_name())); ?>
                                        <td class="fbt-product-price"
                                            style="text-align: right;"><?php echo wp_kses_post(wc_price($product->get_price())); ?></td>
                                        <td style="text-align: right;"><?php echo intval($purchase_count); ?></td>
                                        <td class="fbt-add-product"
                                            style="text-align: center; font-weight: bold; font-size: 150%; color: #2271b1;">
                                            +
                                        </td>
                                    </tr>
                                <?php }
                                $count++;
                            }
                        }

                        if ($count == 0) {
                            echo '<tr><td class="fbt-no-products" colspan="5">' . esc_html__("No more products.", 'checkout-upsell-and-order-bumps') . '</td></tr>';
                        }
                    } else {
                        echo '<tr><td class="fbt-no-products" colspan="5">' . esc_html__("No products found.", 'checkout-upsell-and-order-bumps') . '</td></tr>';
                    } ?>
                    </tbody>
                </table>
                <?php if (!empty($products_from_orders)) { ?>
                    <div style="width: calc(50% + 60px); margin: 12px;">
                        <?php esc_html_e('NOTE: These suggestions are based on past orders. You can use these suggestions or you can search and add other products to the campaign.', 'checkout-upsell-and-order-bumps'); ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="options_group cuw-fbt-campaign">
            <p class="form-field cuw-linked-campaign">
                <label><?php esc_html_e("Linked campaign", 'checkout-upsell-and-order-bumps'); ?></label>
                <a target="_blank" href="<?php echo esc_url(\CUW\App\Helpers\Campaign::getEditUrl($campaign)); ?>"
                   style="text-decoration: none; font-weight: bold;">
                    <span class="dashicons dashicons-admin-links"
                          style="vertical-align: bottom; font-size: 14px;"></span>
                    <?php echo esc_html(\CUW\App\Helpers\Campaign::getTitle($campaign, true)); ?>
                </a>
            </p>
            <p class="form-field cuw-products-override">
                <label><?php esc_html_e("Override products", 'checkout-upsell-and-order-bumps'); ?></label>
                <input type="checkbox" name="cuw_fbt_products_override"
                       value="1" <?php checked(true, !empty($products_override)); ?>>
                <span class="description"><?php esc_html_e("Always use the above custom products as frequently bought together products.", 'checkout-upsell-and-order-bumps'); ?></span>
            </p>

            <p class="form-field cuw-discount-override">
                <label><?php esc_html_e("Override discount", 'checkout-upsell-and-order-bumps'); ?></label>
                <input type="checkbox" name="cuw_fbt_discount_override"
                       value="1" <?php checked(true, !empty($products_discount)); ?>>
                <span class="description"><?php esc_html_e("Set a custom discount for this product.", 'checkout-upsell-and-order-bumps'); ?></span>
            </p>
            <div class="cuw-discount" style="<?php if (empty($products_discount)) echo 'display:none;' ?>">
                <p class="form-field cuw-discount-apply-to m-0">
                    <label for="cuw-discount-apply-to"><?php esc_html_e("Discount apply to", 'checkout-upsell-and-order-bumps'); ?></label>
                    <select id="cuw-discount-apply-to" name="cuw_fbt_products_discount[apply_to]" class="select short">
                        <option value="no_products" <?php selected('no_products', $discount_apply_to); ?>><?php esc_html_e("No products", 'checkout-upsell-and-order-bumps'); ?></option>
                        <option value="only_upsells" <?php selected('only_upsells', $discount_apply_to); ?>><?php esc_html_e("Only Upsell products", 'checkout-upsell-and-order-bumps'); ?></option>
                        <option value="all_products" <?php selected('all_products', $discount_apply_to); ?>><?php esc_html_e("All Products (Main + Upsell products)", 'checkout-upsell-and-order-bumps'); ?></option>
                    </select>
                </p>
                <div class="cuw-discount-details"
                     style="<?php if ($discount_apply_to == 'no_products') echo 'display: none;' ?>">
                    <p class="form-field cuw-discount-type">
                        <label for="cuw-discount-type"><?php esc_html_e("Discount type", 'checkout-upsell-and-order-bumps'); ?></label>
                        <select id="cuw-discount-type" name="cuw_fbt_products_discount[type]"
                                class="select short" <?php if ($discount_apply_to == 'no_products') echo 'disabled' ?>>
                            <option value="percentage" <?php selected('percentage', $discount_type); ?>><?php esc_html_e("Percentage discount", 'checkout-upsell-and-order-bumps'); ?></option>
                            <option value="fixed_price" <?php selected('fixed_price', $discount_type); ?>><?php esc_html_e("Fixed discount", 'checkout-upsell-and-order-bumps'); ?></option>
                        </select>
                    </p>
                    <p class="form-field cuw-discount-value">
                        <label for="cuw-discount-value"><?php esc_html_e("Discount value", 'checkout-upsell-and-order-bumps'); ?></label>
                        <input class="short" type="number" id="cuw-discount-value"
                               name="cuw_fbt_products_discount[value]" min="0"
                               value="<?php echo esc_attr($discount_value); ?>"
                               placeholder="<?php esc_attr_e("Value", 'checkout-upsell-and-order-bumps'); ?>"
                            <?php echo $discount_apply_to == 'no_products' ? 'disabled' : 'required'; ?>>
                    </p>
                </div>
            </div>
        </div>

        <script>
            jQuery(function ($) {
                $(document).ready(function () {
                    $("#cuw-fbt-toggle-product-suggestions").click(function () {
                        $(this).toggleClass("cuw-show cuw-hide").toggleClass("button-primary button-secondary");
                        $(this).html($(this).hasClass('cuw-show') ? $(this).data('i18n_show') : $(this).data('i18n_hide'));
                        $("#cuw-fbt-product-suggestions").slideToggle();
                    });

                    $("#cuw-fbt-product-suggestions .fbt-add-product").click(function () {
                        let id = $(this).closest('tr').data('id');
                        let title = $(this).closest('tr').find('.fbt-product-title').html();
                        $("#cuw-fbt-products-list").prepend('<option value="' + id + '" selected>' + title + '</option>').trigger('change');
                        $(this).closest('tr').fadeOut(300, function () {
                            $(this).remove()
                        });
                    });

                    $(".cuw-fbt-campaign .cuw-discount-override input").change(function () {
                        let discount_section = $(".cuw-fbt-campaign .cuw-discount");
                        discount_section.slideToggle();
                        discount_section.find('.cuw-discount-apply-to select').prop('disabled', !$(this).is(':checked')).trigger('change');
                    });

                    $(".cuw-fbt-campaign .cuw-discount .cuw-discount-apply-to select").change(function () {
                        let discount_details = $(".cuw-fbt-campaign .cuw-discount .cuw-discount-details");
                        if ($(this).val() !== 'no_products' && $(".cuw-fbt-campaign .cuw-discount-override input").is(':checked')) {
                            discount_details.find(':input').prop('disabled', false).prop('required', true);
                            discount_details.slideDown();
                        } else {
                            discount_details.slideUp();
                            discount_details.find(':input').prop('disabled', true).prop('required', false);
                        }
                    });

                    $(".cuw-fbt-campaign .cuw-discount .cuw-discount-type select").change(function () {
                        let discount_value = $(this).closest(".cuw-discount").find(".cuw-discount-value");
                        if ($(this).val() === 'free' || $(this).val() === 'no_discount') {
                            discount_value.slideUp();
                            discount_value.find('input').val(0).prop('required', false);
                        } else {
                            discount_value.find('input').val('').prop('required', true);
                            discount_value.slideDown();
                        }
                    });
                });
            });
        </script>
    </div>
<?php endif; ?>