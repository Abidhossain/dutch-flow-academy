<?php
/**
 * Offer page form
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/page/offer-form.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer) || !isset($nonce) || !isset($allowed_html)) {
    return;
}
$disable_cta = !empty($offer['product']['is_variable']) && empty($offer['product']['default_variant']);
?>

<form class="cuw-page-offer-form" action="" method="post">
    <div class="cuw-page-inputs">
        <div class="cuw-page-product-quantity">
            <?php echo apply_filters('cuw_offer_template_product_quantity', '', $offer); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <div class="cuw-page-product-variants">
            <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo apply_filters('cuw_offer_template_product_variants', '', $offer, [
                'attribute_select_name' => 'variation_attributes',
            ]); ?>
        </div>
        <input type="hidden" name="cuw_action" value="accept_offer">
        <input type="hidden" name="cuw_nonce" value="<?php echo esc_attr($nonce); ?>">
    </div>
    <div class="cuw-page-actions">
        <div class="cuw-page-offer-accept">
            <button type="submit" class="cuw-page-button cuw-offer-accept-button" <?php if ($disable_cta) echo 'disabled'; ?>>
                <span class="cuw-offer-page-cta-text"><?php echo wp_kses($offer['template']['cta_text'], $allowed_html); ?></span>
            </button>
        </div>
        <div class="cuw-page-offer-decline">
            <a class="cuw-page-link cuw-offer-decline-link"
               href="<?php echo esc_url(do_shortcode('[cuw_offer_decline_url]')); ?>">
                <?php esc_html_e("No thanks", 'checkout-upsell-woocommerce'); ?>
            </a>
        </div>
    </div>
</form>
