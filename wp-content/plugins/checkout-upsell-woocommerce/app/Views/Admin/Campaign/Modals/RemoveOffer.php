<?php
defined('ABSPATH') || exit;
?>

<div id="modal-remove" class="modal fade">
    <div class="modal-dialog mt-5">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e("Remove", 'checkout-upsell-woocommerce'); ?></h5>
                <button type="button" class="close ml-2" data-dismiss="modal">
                    <i class="cuw-icon-close-circle text-dark"></i>
                </button>
            </div>
            <div class="modal-body">
                <?php echo sprintf(esc_html__("Are you sure, you want to remove %s?", 'checkout-upsell-woocommerce'),
                    '<span class="offer-title font-weight-bold"></span>');
                ?>
                <div class="mt-2 text-info cuw-child-offer-warning"
                     style="display: none; width: fit-content;">
                    <?php echo esc_html__("NOTE: It also delete the child offers.", 'checkout-upsell-woocommerce') ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="offer-delete btn btn-danger" data-id="" data-index="" data-offer_type="">
                    <?php esc_html_e("Yes", 'checkout-upsell-woocommerce'); ?>
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?php esc_html_e("No", 'checkout-upsell-woocommerce'); ?>
                </button>
            </div>
        </div>
    </div>
</div>