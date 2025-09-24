<?php
/**
 * Offer decline link
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/post-purchase/components/decline-link.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer)) {
    return;
}
$offer['template']['decline_text'] = !empty($offer['template']['decline_text']) ? __($offer['template']['decline_text'], 'checkout-upsell-woocommerce') : '';
?>
<a href="#" class="cuw-ppu-link cuw-ppu-decline-link"
   style="<?php echo esc_attr($offer['styles']['decline_button']); ?>"
   data-nonce="<?php echo esc_attr(wp_create_nonce('cuw_decline_offer')); ?>">
    <span class="cuw-ppu-decline-text"><?php echo wp_kses($offer['template']['decline_text'], $offer['allowed_html']); ?></span>
</a>
