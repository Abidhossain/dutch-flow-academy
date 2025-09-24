<?php
defined('ABSPATH') || exit;
isset($page) || exit;
?>

<div id="modal-delete" class="modal fade">
    <div class="modal-dialog mt-5">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e("Delete", 'checkout-upsell-woocommerce'); ?></h5>
                <button type="button" class="close ml-2" data-dismiss="modal">
                    <i class="cuw-icon-close-circle text-dark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="engine-delete-text">
                    <?php esc_html_e("Are you sure, you want to delete the following engines?", 'checkout-upsell-woocommerce'); ?>
                </div>
                <div class="engine-delete-warning text-info" style="display: none;">
                    <?php echo sprintf(esc_html__("The following engines are linked to %s campaigns. Before proceeding to delete, you should unlink the engines from campaigns.", 'checkout-upsell-woocommerce'),
                        '<span class="engine-count font-weight-medium"></span>'); ?>
                </div>
                <span class="engine-title font-weight-bold"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="engine-delete btn btn-danger engine-delete-yes" data-ids="" data-bulk="">
                    <?php esc_html_e("Yes", 'checkout-upsell-woocommerce'); ?>
                </button>
                <button type="button" class="btn btn-secondary engine-delete-no" data-dismiss="modal">
                    <?php esc_html_e("No", 'checkout-upsell-woocommerce'); ?>
                </button>
                <button type="button" class="btn btn-secondary engine-delete-close" data-dismiss="modal"
                        style="display: none">
                    <?php esc_html_e("Close", 'checkout-upsell-woocommerce'); ?>
                </button>
            </div>
        </div>
    </div>
</div>