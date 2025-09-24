<?php defined('ABSPATH') || exit; ?>

<div id="modal-delete" class="modal fade">
    <div class="modal-dialog mt-5">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e("Delete", 'checkout-upsell-and-order-bumps'); ?></h5>
                <button type="button" class="close ml-2" data-dismiss="modal">
                    <i class="cuw-icon-close-circle text-dark"></i>
                </button>
            </div>
            <div class="modal-body">
                <?php esc_html_e("Are you sure, you want to delete the following campaigns?", 'checkout-upsell-and-order-bumps'); ?>
                <span class="campaign-title font-weight-bold"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="campaign-delete btn btn-danger" data-ids="" data-bulk="">
                    <?php esc_html_e("Yes", 'checkout-upsell-and-order-bumps'); ?>
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?php esc_html_e("No", 'checkout-upsell-and-order-bumps'); ?>
                </button>
            </div>
        </div>
    </div>
</div>