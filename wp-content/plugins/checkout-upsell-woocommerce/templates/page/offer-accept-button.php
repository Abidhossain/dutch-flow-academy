<?php
/**
 * Offer accept button
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/page/offer-accept-button.php.
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

<form class="cuw-page-offer-accept" action="" method="post">
    <button type="submit" class="cuw-page-button cuw-offer-accept-button" <?php if ($disable_cta) echo 'disabled'; ?>>
        <span class="cuw-offer-page-cta-text"><?php echo wp_kses($offer['template']['cta_text'], $allowed_html); ?></span>
    </button>
    <input type="hidden" name="cuw_action" value="accept_offer">
    <input type="hidden" class="cuw-hidden-product-quantity" name="quantity"
           value="<?php echo !empty($offer['product']['qty']) ? esc_attr($offer['product']['qty']) : ''; ?>">
    <input type="hidden" class="cuw-hidden-product-variant" name="variation_id" value="">
    <input type="hidden" name="cuw_nonce" value="<?php echo esc_attr($nonce); ?>">
</form>
