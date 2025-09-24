<?php
defined('ABSPATH') || exit;
if (!isset($campaign) || !isset($is_single)) {
    return;
}
?>

<div>
    <?php if (!$is_single) { ?>
        <div class="form-group mb-2">
            <label for="priority" class="form-label">
                <?php esc_html_e("Priority", 'checkout-upsell-woocommerce'); ?>
                <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
            </label>
            <input type="number" class="form-control" id="priority" name="priority"
                   value="<?php echo esc_attr($campaign['priority']); ?>" placeholder="10">
        </div>
    <?php } ?>
    <div id="cuw-schedule">
        <?php
        $date_from = !empty($campaign['start_on']) ? CUW()->wp->formatDate($campaign['start_on'], 'Y-m-d', true) : '';
        $date_to = !empty($campaign['end_on']) ? CUW()->wp->formatDate($campaign['end_on'], 'Y-m-d', true) : '';
        ?>
        <div class="form-group">
            <label for="date-from" class="form-label">
                <?php esc_html_e("Start date", 'checkout-upsell-woocommerce'); ?>
                <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
            </label>
            <input type="date" class="form-control" id="date-from" name="date_from"
                   value="<?php echo esc_attr($date_from); ?>">
        </div>
        <div class="custom-control custom-checkbox d-inline-block">
            <input type="checkbox" class="custom-control-input"
                   id="toggle-end-date" <?php echo !empty($date_to) ? 'checked' : '' ?>>
            <label class="custom-control-label" for="toggle-end-date">Set end date</label>
        </div>
        <div class="form-group mb-2 mt-1" id="end-date" style="display: <?php echo empty($date_to) ? 'none' : ''; ?>">
            <label for="date-to" class="form-label">
                <?php esc_html_e("End date", 'checkout-upsell-woocommerce'); ?>
                <?php esc_html_e("(optional)", 'checkout-upsell-woocommerce'); ?>
            </label>
            <input type="date" class="form-control" id="date-to" name="date_to"
                   value="<?php echo esc_attr($date_to); ?>">
        </div>
    </div>
</div>
