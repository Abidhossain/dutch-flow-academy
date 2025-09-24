<?php
/**
 * Product details popup.
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/common/product-details-popup.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($product_object)) {
    return;
}

global $post;
global $product;
$product = $product_object;
$post = get_post($product->get_id());

$show_description = apply_filters('cuw_show_product_description_in_popup', true);
?>
<div id="cuw-modal-product-details-<?php echo esc_attr($product->get_id()); ?>" class="cuw-modal">
    <div class="cuw-modal-content cuw-animate-fade" style="max-width: 800px;">
        <div class="cuw-modal-header" style="display: block; padding: 0;">
            <div class="cuw-modal-header-primary" style="display: flex; align-items: center; padding: 14px 16px;">
                <div style="flex: 1; text-align: center; font-family: sans-serif; font-size: 24px;">
                    <a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo wp_kses_post($product->get_title()); ?></a>
                </div>
                <span class="cuw-modal-close" style="font-size: 32px; line-height: 1;">&times;</span>
            </div>
        </div>
        <div class="cuw-modal-body" style="overflow-x: auto; overflow-y: auto; max-height: 480px;">
            <div class="cuw-product-details" style="display: flex; flex-direction: column; padding: 10px; gap: 16px;">
                <div class="cuw-product-layout" style="display: flex; gap: 20px; flex-wrap: wrap; align-items: center;">
                    <div class="cuw-product-image"
                         style="flex: 2; display: flex; justify-content: center; max-width: 200px; margin-bottom: auto;">
                        <?php echo wp_kses_post($product->get_image()); ?>
                    </div>
                    <div style="display: flex; flex: 3; flex-direction: column; gap: 6px; flex-wrap: wrap; align-items: initial; width: 100%;">
                        <?php if (!empty($product->get_review_count())) { ?>
                            <div class="cuw-product-rating">
                                <?php wc_get_template('single-product/rating.php'); ?>
                            </div>
                        <?php } ?>
                        <div class="cuw-product-short-description">
                            <?php wc_get_template('single-product/short-description.php'); ?>
                        </div>
                        <div class="cuw-product-meta">
                            <?php wc_get_template('single-product/meta.php'); ?>
                        </div>
                    </div>
                </div>
                <?php if ($show_description) { ?>
                    <div class="cuw-product-description">
                        <?php the_content(); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
