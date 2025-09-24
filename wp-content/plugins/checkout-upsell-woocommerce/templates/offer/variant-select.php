<?php
/**
 * Offer variant (variation product) select
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/offer/variant-select.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;

if (!empty($offer['product']['variants']) && !empty($offer['product']['default_variant'])) { ?>
    <select class="variant-select" name="variation_id">
        <?php foreach ($offer['product']['variants'] as $variant) { ?>
            <option value="<?php echo esc_attr($variant['id']); ?>"
                    data-regular_price="<?php echo esc_attr($variant['regular_price']); ?>"
                    data-price="<?php echo esc_attr($variant['price']); ?>"
                    data-price_html="<?php echo esc_attr($variant['price_html']); ?>"
                    data-stock_qty="<?php echo esc_attr($variant['stock_qty']); ?>"
                    data-tax="<?php echo isset($variant['tax']) ? esc_attr($variant['tax']) : 0; ?>"
                    data-image="<?php echo empty($offer['product']['fixed_image']) ? esc_attr($variant['image']) : ''; ?>"
                <?php if ($offer['product']['default_variant']['id'] == $variant['id']) echo 'selected'; ?>>
                <?php echo esc_html($variant['info']); ?>
            </option>
        <?php } ?>
    </select>
    <?php
}
