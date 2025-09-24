<?php
/**
 * Offer accept button
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/post-purchase/components/accept-button.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer)) {
    return;
}
$disable_cta = !empty($offer['product']['is_variable']) && empty($offer['product']['default_variant']);
$offer['template']['accept_text'] = !empty($offer['template']['accept_text']) ? __($offer['template']['accept_text'], 'checkout-upsell-woocommerce') : '';
?>
<button type="button" class="cuw-ppu-button cuw-ppu-accept-button"
        style="<?php echo esc_attr($offer['styles']['accept_button']); ?>"
        data-nonce="<?php echo esc_attr(wp_create_nonce('cuw_accept_offer')); ?>"
    <?php if ($disable_cta) echo 'disabled'; ?>>
    <span class="cuw-ppu-accept-text"><?php echo wp_kses($offer['template']['accept_text'], $offer['allowed_html']); ?></span>
</button>
