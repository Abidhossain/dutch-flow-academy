<?php
/**
 * Product variants
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/post-purchase/components/product-variants.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer)) return;
?>

<div class="cuw-ppu-product-variants">
    <?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo apply_filters('cuw_offer_template_product_variants', '', $offer, [
        'attribute_select_name' => 'variation_attributes',
    ]); ?>
</div>
