<?php
defined('ABSPATH') || exit;
if (!isset($action)) {
    return;
}
?>

<?php if ($action == 'load_popup_script' && isset($trigger) && isset($page)) { ?>
    <?php $campaign_id = !empty($campaign['id']) ? $campaign['id'] : 0; ?>
    <script>
        jQuery(document.body).on(<?php
            echo !empty($trigger['event']) ? '"' . esc_js($trigger['event']) . '", ' : '';
            echo !empty($trigger['target']) ? '"' . esc_js($trigger['target']) . '", ' : '';
            ?>function (event, fragments, cart_hash, product) {
                <?php if (!empty($trigger['show_once']) && !empty($campaign_id)) { ?>
                if (jQuery('#cuw-modal-<?php echo esc_js($campaign_id); ?>').data('shown')) {
                    return;
                }
                <?php } ?>

                <?php if (!empty($trigger['show_once'])) { ?>
                if (!jQuery(this).data('modal_shown')) {
                    event.preventDefault();
                    jQuery(this).data('modal_shown', true);
                } else {
                    return;
                }
                <?php } ?>

                let data = {
                    campaign_id: <?php echo esc_js($campaign_id); ?>,
                    event: '<?php echo !empty($trigger['event']) ? esc_js($trigger['event']) : ''; ?>',
                    target: '<?php echo !empty($trigger['target']) ? esc_js($trigger['target']) : ''; ?>',
                    trigger: '<?php echo !empty($trigger['key']) ? esc_js($trigger['key']) : ''; ?>',
                    page: '<?php echo esc_js($page); ?>',
                    dynamic_content: <?php echo !empty($trigger['dynamic_content']) ? 'true' : 'false'; ?>
                };
                if (product && product.data('product_id')) {
                    data.product_id = product.data('product_id');
                }
                jQuery(document.body).trigger("cuw_show_upsell_popup", [data, jQuery(this)]);
            });
    </script>
    <?php
} ?>
