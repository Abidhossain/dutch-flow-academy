<?php
defined('ABSPATH') || exit;
if (!isset($action)) {
    return;
}

$campaign_type = 'product_addons';
$display_locations = \CUW\App\Pro\Modules\Campaigns\ProductAddons::getDisplayLocations();
?>

<?php if ($action == 'product_edit' && isset($post_id) && isset($product_ids)): ?>
    <?php
    $campaign = !empty($matched_campaign) ? (array)$matched_campaign : [];
    ?>
    <div class="options_group cuw-product-addon-products" style="display: flex; margin-top: 14px;">
        <p class="form-field">
            <label for="cuw-product-addons-products-list"><?php esc_html_e('Product Add-Ons', 'checkout-upsell-woocommerce'); ?></label>
        </p>
        <div style="display: flex; flex-direction: column; width: 100%; margin-bottom: 14px;">
            <select class="wc-product-search" multiple="multiple" id="cuw-product-addons-products-list"
                    name="cuw_product_addons_product_ids[]" style="width: 50%;"
                    data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'woocommerce'); ?>"
                    data-action="woocommerce_json_search_products_and_variations"
                    data-exclude="<?php echo intval($post_id); ?>">
                <?php foreach ($product_ids as $product_id) {
                    if (is_object($product = \CUW\App\Helpers\WC::getProduct($product_id))) {
                        echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . esc_html(wp_strip_all_tags($product->get_formatted_name())) . '</option>';
                    }
                } ?>
            </select>
            <div class="options_group cuw-product-addons-campaign" style="display: none">
                <div style="margin: 8px 0;">
                <span>
                    <?php esc_html_e("Linked campaign", 'checkout-upsell-woocommerce'); ?>:
                    <a target="_blank"
                       href="<?php echo esc_url(\CUW\App\Helpers\Campaign::getEditUrl($campaign)); ?>"
                       style="text-decoration: none; font-weight: bold;">
                       <span class="dashicons dashicons-admin-links"
                             style="vertical-align: text-top; font-size: 14px;">
                       </span>
                        <?php echo esc_html(\CUW\App\Helpers\Campaign::getTitle($campaign, true)); ?>
                    </a>
                </span>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($action == 'campaign_edit' && isset($campaign)): ?>
    <?php

    CUW()->view('Admin/Components/Accordion', [
        'id' => 'use_products',
        'title' => __('Products', 'checkout-upsell-woocommerce'),
        'icon' => 'product',
        'view' => 'Admin/Campaign/Components/Products',
        'data' => [
            'campaign' => $campaign,
            'use_options' => ['related', 'cross_sell', 'upsell', 'custom', 'specific', 'engine'],
            'default_use' => 'related',
            'allow_bundle' => false,
            'products_text' => __('Add-On', 'checkout-upsell-woocommerce'),
            'allow_remove' => true,
            'change_quantity' => true,
        ],
    ]);

    CUW()->view('Admin/Components/Accordion', [
        'id' => 'discount',
        'title' => __('Discount', 'checkout-upsell-woocommerce'),
        'icon' => 'discount',
        'view' => 'Admin/Campaign/Components/Discount',
        'data' => ['campaign' => $campaign],
    ]);

    CUW()->view('Admin/Components/Accordion', [
        'id' => 'template',
        'title' => __('Template', 'checkout-upsell-woocommerce'),
        'icon' => 'campaigns',
        'view' => 'Admin/Campaign/Components/Template',
        'data' => [
            'campaign' => $campaign,
            'display_locations' => $display_locations,
            'display_location_text' => __('Display location on Product page', 'checkout-upsell-woocommerce'),
        ],
    ]);
    ?>
<?php endif; ?>