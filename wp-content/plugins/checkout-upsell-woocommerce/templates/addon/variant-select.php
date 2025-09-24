<?php
/**
 * Product Addons variant select
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/addon/variant-select.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($product)) return;

if (!empty($product['variants']) && !empty($product['default_variant'])) { ?>
    <select class="variant-select" style="width: 100%;"
            name="<?php echo !empty($args['variant_select_name']) ? esc_attr($args['variant_select_name']) : 'variation_id'; ?>">
        <?php foreach ($product['variants'] as $variant) { ?>
            <option value="<?php echo esc_attr($variant['id']); ?>"
                    data-regular_price="<?php echo esc_attr($variant['regular_price']); ?>"
                    data-price="<?php echo esc_attr($variant['price']); ?>"
                    data-price_html="<?php echo esc_attr($variant['price_html']); ?>"
                    data-stock_qty="<?php echo esc_attr($variant['stock_qty']); ?>"
                    data-image="<?php echo esc_attr($variant['image']); ?>"
                <?php if ($product['default_variant']['id'] == $variant['id']) echo 'selected'; ?>>
                <?php echo esc_html($variant['info']); ?>
            </option>
        <?php } ?>
    </select>
    <?php
}