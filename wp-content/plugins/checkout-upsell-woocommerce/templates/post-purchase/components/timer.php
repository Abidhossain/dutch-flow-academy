<?php
/**
 * Offer timer
 *
 * This template can be overridden by copying it to yourtheme/checkout-upsell-woocommerce/post-purchase/components/timer.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer) will need to copy the new files
 * to your theme to maintain compatibility. We try to do this as little as possible, but it does happen.
 */

defined('ABSPATH') || exit;
if (!isset($offer) || empty($offer['timer']['enabled'])) {
    return;
}

$minutes = $offer['timer']['minutes'];
$seconds = $offer['timer']['seconds'];
$message = __($offer['timer']['message'], 'checkout-upsell-woocommerce');

$formatted_minutes = ($minutes < 10 ? '0' . $minutes : $minutes);
$formatted_seconds = ($seconds < 10 ? '0' . $seconds : $seconds);

// restore the timer from a cookie when we refresh the page
if (!empty($offer['id'])) {
    $cookie_key = 'cuw_timer_' . $offer['id'] . '_' . ($offer['order_id'] ?? '');
    $duration = sanitize_text_field(wp_unslash($_COOKIE[$cookie_key] ?? ''));
    if (!empty($duration)) {
        $formatted_minutes = gmdate('i', (int)$duration);
        $formatted_seconds = gmdate('s', (int)$duration);
    }
}

$text = str_replace(['{minutes}', '{seconds}'], [$formatted_minutes, $formatted_seconds], $message);
if ($offer['is_preview'] || $offer['timer']['notice_type'] != 'wc_notice') {
    $offer['styles']['timer'] .= ($offer['timer']['notice_type'] == 'wc_notice') ? 'display: none;' : '';
    ?>
    <div class="cuw-ppu-offer-timer cuw-built-in"
         style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; <?php echo esc_attr($offer['styles']['timer']); ?>">
        <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2C17.52 2 22 6.48 22 12Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15.7099 15.1798L12.6099 13.3298C12.0699 13.0098 11.6299 12.2398 11.6299 11.6098V7.50977"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <?php
        echo wp_kses_post(sprintf(
            '<span class="cuw-ppu-timer-message cuw-page-timer" data-minutes="%s" data-seconds="%s" data-message="%s">%s</span>',
            $minutes, $seconds, esc_html($message), $text
        ));
        ?>
    </div>
    <?php
}

if ($offer['is_preview'] || $offer['timer']['notice_type'] == 'wc_notice') {
    $timer_style = ($offer['timer']['notice_type'] != 'wc_notice') ? 'display: none;' : '';
    echo '<div class="cuw-ppu-offer-timer cuw-wc-notice" style="' . esc_attr($timer_style) . '">';
    if (function_exists('wc_print_notice')) {
        wc_print_notice(sprintf(
            '<span class="cuw-ppu-timer-message cuw-page-timer" data-minutes="%s" data-seconds="%s" data-message="%s">%s</span>',
            $minutes, $seconds, esc_html($message), $text
        ), 'notice');
    } else if ($offer['is_preview']) {
        echo wp_kses_post(sprintf(
            '<span class="cuw-ppu-timer-message cuw-wc-notice-message" data-minutes="%s" data-seconds="%s" data-message="%s">%s</span>',
            $minutes, $seconds, esc_html($message), $text
        ));
    }
    echo '</div>';
}