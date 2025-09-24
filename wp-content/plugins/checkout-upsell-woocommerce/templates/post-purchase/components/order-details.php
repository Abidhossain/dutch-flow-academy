<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/post-purchase/components/order-details.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer) || empty($offer['template']['order_details']['enabled'])) return;


$message = $cta_text = '';
if (!empty($offer['process_type'])) {
    $message = $offer['process_type'] == 'after_payment'
        ? __('Your order has been received.', 'checkout-upsell-woocommerce')
        : __('Your order is not processed yet.', 'checkout-upsell-woocommerce');
    $cta_text = __('Skip offers', 'checkout-upsell-woocommerce');
}
?>

<?php
if ($offer['is_preview'] || $offer['template']['order_details']['notice_type'] == 'custom') {
    $offer['styles']['order_details'] .= ($offer['template']['order_details']['notice_type'] != 'custom') ? 'display: none;' : '';
    ?>
    <div class="cuw-ppu-order-details cuw-built-in"
         style="display: flex; justify-content: space-between; align-items: center; padding: 12px; margin: 12px 0; border-radius: 8px; <?php echo esc_attr($offer['styles']['order_details']); ?>">
        <div style="display: inline-flex; align-items: center; gap: 12px;">
            <?php if ($offer['process_type'] == 'after_payment') { ?>
                <svg width="24px" height="24px" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 34C7.62636 34 0 26.3736 0 17C0 7.62636 7.62636 0 17 0C26.3736 0 34 7.62636 34 17C34 26.3736 26.3736 34 17 34ZM17 2.125C8.79777 2.125 2.125 8.79777 2.125 17C2.125 25.2022 8.79777 31.875 17 31.875C25.2022 31.875 31.875 25.2022 31.875 17C31.875 8.79777 25.2022 2.125 17 2.125ZM14.5637 23.0637L25.1887 12.4387C25.6039 12.0235 25.6039 11.3512 25.1887 10.9363C24.7735 10.5214 24.1012 10.5211 23.6863 10.9363L13.8125 20.8101L10.3137 17.3113C9.89852 16.8961 9.22622 16.8961 8.81131 17.3113C8.39641 17.7265 8.39614 18.3988 8.81131 18.8137L13.0613 23.0637C13.2688 23.2711 13.5408 23.375 13.8125 23.375C14.0842 23.375 14.3562 23.2711 14.5637 23.0637Z"
                          fill="#0DB145"/>
                </svg>
            <?php } else { ?>
                <svg width="24px" height="24px" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_3155_64554)">
                        <path d="M18.1933 15.4118C18.1933 15.0954 18.0676 14.7919 17.8438 14.5682C17.6201 14.3444 17.3166 14.2188 17.0002 14.2188C16.6838 14.2188 16.3803 14.3444 16.1566 14.5682C15.9328 14.7919 15.8071 15.0954 15.8071 15.4118V24.9564C15.8071 25.2728 15.9328 25.5763 16.1566 25.8C16.3803 26.0237 16.6838 26.1494 17.0002 26.1494C17.3166 26.1494 17.6201 26.0237 17.8438 25.8C18.0676 25.5763 18.1933 25.2728 18.1933 24.9564V15.4118Z"
                              fill="#0D62B1"/>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M-0.000244141 17.002C-0.000244141 7.61324 7.61104 0.00195312 16.9998 0.00195312C26.3886 0.00195312 33.9999 7.61324 33.9999 17.002C33.9999 26.3908 26.3886 34.0021 16.9998 34.0021C7.61104 34.0021 -0.000244141 26.3908 -0.000244141 17.002ZM6.52396 6.52615C3.74559 9.30453 2.18471 13.0728 2.18471 17.002C2.18471 20.9312 3.74559 24.6995 6.52396 27.4779C9.30233 30.2562 13.0706 31.8171 16.9998 31.8171C20.929 31.8171 24.6973 30.2562 27.4757 27.4779C30.254 24.6995 31.8149 20.9312 31.8149 17.002C31.8149 13.0728 30.254 9.30453 27.4757 6.52615C24.6973 3.74778 20.929 2.18691 16.9998 2.18691C13.0706 2.18691 9.30233 3.74778 6.52396 6.52615Z"
                              fill="#0D62B1"/>
                        <path d="M18.5909 10.6396C18.5909 11.0615 18.4233 11.4661 18.125 11.7644C17.8267 12.0627 17.4221 12.2303 17.0002 12.2303C16.5783 12.2303 16.1737 12.0627 15.8753 11.7644C15.577 11.4661 15.4094 11.0615 15.4094 10.6396C15.4094 10.2177 15.577 9.81308 15.8753 9.51475C16.1737 9.21643 16.5783 9.04883 17.0002 9.04883C17.4221 9.04883 17.8267 9.21643 18.125 9.51475C18.4233 9.81308 18.5909 10.2177 18.5909 10.6396Z"
                              fill="#0D62B1"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_3155_64554">
                            <rect width="34" height="34" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
            <?php } ?>
            <span class="cuw-ppu-order-message"
                  style="display: flex; flex-direction: column; margin: 0; font-weight: 600; font-size: inherit; color: inherit; background-color: inherit;">
                <span><?php echo esc_html($message); ?></span>
                <small style="opacity: 0.6;">
                    <?php echo esc_html__('Order', 'checkout-upsell-woocommerce') . ' #' . esc_attr($offer['order_id'] ?? ''); ?>
                </small>
            </span>
        </div>
        <a href="#" tabindex="1" class="cuw-ppu-ignore-offers"
           style="text-decoration: underline; background-color: inherit; color: inherit; font-weight: 600; border: none; padding: 0; margin: 0;">
            <?php echo esc_html($cta_text); ?>
        </a>
    </div>
    <?php
}

if ($offer['is_preview'] || $offer['template']['order_details']['notice_type'] == 'wc_notice') {
    $order_details_style = ($offer['template']['order_details']['notice_type'] != 'wc_notice') ? 'display: none;' : '';
    ?>
    <div class="cuw-ppu-order-details cuw-wc-notice" style="<?php echo esc_attr($order_details_style) ?>">
        <?php
        $inner_html = sprintf('%s <a href="#" tabindex="1" class="cuw-ppu-ignore-offers button wc-forward">%s</a>', $message, $cta_text);
        if (function_exists('wc_print_notice')) {
            echo '<p class="cuw-ppu-order-message">';
            wc_print_notice($inner_html, 'notice');
            echo '</p>';
        } else if ($offer['is_preview']) {
            echo '<p class="cuw-ppu-order-message cuw-wc-notice-message">';
            echo wp_kses_post($inner_html);
            echo '</p>';
        }
        ?>
    </div>
<?php } ?>
