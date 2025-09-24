<?php
/**
 * Thankyou upsells total savings amount label
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/products/savings.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;

if (!isset($data) || empty($display)) {
    return;
}

$badge_on = $data['template']['save_badge'] ?? 'do_not_display';
$badge_text = $data['template']['save_badge_text'] ?? '-{price}';
$total_text = apply_filters('cuw_total_saving_text', __('You can save %s', 'checkout-upsell-woocommerce'));
$display_product_badge = in_array($badge_on, ['only_products', 'both_products_and_total']);
$display_total_savings = in_array($badge_on, ['only_total', 'both_products_and_total']);
if (!empty($product)) {
    $discount_in_price = $discount_in_percentage = 0;
    if ($display == 'static' || ($display == 'dynamic' && !$product['is_variable'])) {
        if ($product['is_variable']) {
            $discount_in_price = !empty(array_column($product['variants'], 'discount')) ? max(array_column($product['variants'], 'discount')) : '';
            $discount_in_percentage = !empty(array_column($product['variants'], 'discount_percentage')) ? max(array_column($product['variants'], 'discount_percentage')) : '';
        } else {
            $discount_in_price = $product['discount'] ?? '';
            $discount_in_percentage = $product['discount_percentage'] ?? '';
        }
    } elseif ($display == 'dynamic') {
        $discount_in_price = $product['is_variable'] ? ($product['default_variant']['discount'] ?? '') : ($product['discount'] ?? '');
        $discount_in_percentage = $product['is_variable'] ? ($product['default_variant']['discount_percentage'] ?? '') : ($product['discount_percentage'] ?? '');
    }
    $save_text = '';
    if (!empty($discount_in_price) && !empty($discount_in_percentage)) {
        $save_text = str_replace(['{price}', '{percentage}'], [wp_strip_all_tags(wc_price($discount_in_price)), $discount_in_percentage . '%'], $badge_text);
    }
    ?>
    <span class="cuw-badge"
          data-price="<?php echo esc_attr($discount_in_price); ?>"
          data-percentage="<?php echo esc_attr($discount_in_percentage); ?>"
          data-save_text="<?php echo esc_attr($badge_text); ?>"
          data-hidden="<?php echo !$display_product_badge ? '1' : '0'; ?>"
          style="background: limegreen; color: white; line-height: 1; padding: 3px 6px; font-size: 90%; <?php echo !$display_product_badge || empty($save_text) ? 'display: none;' : ''; ?>">
        <span class="cuw-badge-text"><?php echo wp_kses_post($save_text) ?></span>
    </span>
<?php } else { ?>
    <div class="cuw-total-savings" data-hidden="<?php echo !$display_total_savings ? '1' : '0'; ?>"
         style="font-size: 14px; font-weight: 500; border-radius: 50px; padding: 4px; <?php echo !$display_total_savings ? 'display: none;' : ''; ?>">
        <div style="display: flex; gap: 4px; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 28 28" fill="currentColor">
                <path d="M10.418 3.7625L12.3211 7H12.25H8.3125C7.10391 7 6.125 6.02109 6.125 4.8125C6.125 3.60391 7.10391 2.625 8.3125 2.625H8.43281C9.24766 2.625 10.0078 3.05703 10.418 3.7625ZM3.5 4.8125C3.5 5.6 3.69141 6.34375 4.025 7H1.75C0.782031 7 0 7.78203 0 8.75V12.25C0 13.218 0.782031 14 1.75 14H26.25C27.218 14 28 13.218 28 12.25V8.75C28 7.78203 27.218 7 26.25 7H23.975C24.3086 6.34375 24.5 5.6 24.5 4.8125C24.5 2.15469 22.3453 0 19.6875 0H19.5672C17.8227 0 16.2039 0.924219 15.318 2.42813L14 4.67578L12.682 2.43359C11.7961 0.924219 10.1773 0 8.43281 0H8.3125C5.65469 0 3.5 2.15469 3.5 4.8125ZM21.875 4.8125C21.875 6.02109 20.8961 7 19.6875 7H15.75H15.6789L17.582 3.7625C17.9977 3.05703 18.7523 2.625 19.5672 2.625H19.6875C20.8961 2.625 21.875 3.60391 21.875 4.8125ZM1.75 15.75V25.375C1.75 26.8242 2.92578 28 4.375 28H12.25V15.75H1.75ZM15.75 28H23.625C25.0742 28 26.25 26.8242 26.25 25.375V15.75H15.75V28Z"/>
            </svg>
            <span><?php echo wp_kses_post(sprintf($total_text, '<span class="cuw-saved-amount" style="font-weight: 700;"></span>')) ?></span>
        </div>
    </div>
<?php }
