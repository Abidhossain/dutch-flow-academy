<?php
    defined('ABSPATH') || exit;
    if (!isset($campaign)) {
        return;
    }

    $timer = isset($campaign['data']['timer']) ? (array)$campaign['data']['timer'] : [];
    $minutes = isset($timer['minutes']) ? $timer['minutes'] : '5';
    $seconds = isset($timer['seconds']) ? $timer['seconds'] : '0';
    $message = !empty($timer['message']) ? $timer['message'] : "Offer expires in: <strong>{minutes}:{seconds}</strong>";
?>

<div id="cuw-timer">
    <div>
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="timer-enabled" name="data[timer][enabled]" value="1" <?php if (!empty($timer['enabled'])) echo 'checked' ?>>
            <label class="custom-control-label font-weight-medium" for="timer-enabled">
                <?php esc_html_e("Enable the offer expiration countdown timer notice?", 'checkout-upsell-woocommerce'); ?>
            </label>
            <span class="d-block secondary small">
                    <?php esc_html_e('If the timer has expired, the offer will be skipped and the order processed further.', 'checkout-upsell-woocommerce'); ?>
                </span>
        </div>
    </div>
    <div id="timer-section" class="mt-2 mx-4" style="<?php if (empty($timer['enabled'])) echo 'display: none;'; ?>">
        <div class="form-group">
            <label for="timer-expires" class="form-label"><?php esc_html_e("Duration", 'checkout-upsell-woocommerce'); ?> (MM:SS)</label>
            <div style="width: 128px;">
                <div class="input-group">
                    <select class="form-control" name="data[timer][minutes]">
                        <?php for ($i = 0; $i < 60; $i++) {
                            echo '<option value="' . esc_attr($i) . '" ' . ($i == $minutes ? 'selected' : '') . '>' . esc_html($i < 10 ? '0' . $i : $i) . '</option>';
                        } ?>
                    </select>
                    <select class="form-control" name="data[timer][seconds]">
                        <?php for ($i = 0; $i < 60; $i++) {
                            echo '<option value="' . esc_attr($i) . '" ' . ($i == $seconds ? 'selected' : '') . '>' . esc_html($i < 10 ? '0' . $i : $i) . '</option>';
                        } ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group mb-0">
            <label for="timer-message" class="form-label"><?php esc_html_e("Message", 'checkout-upsell-woocommerce'); ?></label>
            <input type="text" class="form-control" id="timer-message" name="data[timer][message]" value="<?php echo esc_attr($message); ?>" placeholder="Offer expires in: <strong>{minutes}:{seconds}</strong>">
            <span class="d-block small mt-1" style="opacity: 0.8;">
                <?php esc_html_e('Available shortcodes', 'checkout-upsell-woocommerce'); ?>: {minutes}, {seconds}
            </span>
            <span class="d-block text-dark small mt-1 px-3 py-2 bg-yellow-secondary rounded">
                <?php echo sprintf(esc_html__('NOTE: You might need to use the %s shortcode on the offer page to show notice.', 'checkout-upsell-woocommerce'), '<strong class="cuw-copy">[cuw_offer_expire_notice]</strong>'); ?>
            </span>
        </div>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            $("#timer-section :input").prop('disabled', !$("#timer-enabled").is(':checked'));
            $("#timer-enabled").change(function () {
                $("#timer-section").slideToggle();
                $("#timer-section :input").prop('disabled', !$(this).is(':checked'))
            });
        });
    </script>
</div>
