<?php
/**
 * Offer decline link
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/page/offer-decline-link.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer)) return;
?>

<div class="cuw-page-offer-decline">
    <a class="cuw-page-link cuw-offer-decline-link"
       href="<?php echo esc_url(do_shortcode('[cuw_offer_decline_url]')); ?>">
        <?php esc_html_e("No thanks", 'checkout-upsell-woocommerce'); ?>
    </a>
</div>
