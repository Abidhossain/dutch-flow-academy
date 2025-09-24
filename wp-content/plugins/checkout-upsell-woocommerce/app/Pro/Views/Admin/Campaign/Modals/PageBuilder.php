<?php
defined('ABSPATH') || exit;

$page_url = \CUW\App\Controllers\Admin\Page::getUrl();
$is_elementor_active = \CUW\App\Helpers\Plugin::isElementorActive();
?>

<div id="modal-page-builder" class="modal fade">
    <div class="modal-dialog mt-5" style="max-width: 600px">
        <div class="modal-content" style="border-radius: 0.75rem;">
            <div class="modal-header p-4">
                <h5 class="modal-title"><?php esc_html_e("Choose a page builder", 'checkout-upsell-woocommerce'); ?></h5>
                <button type="button" class="close ml-2" data-dismiss="modal">
                    <i class="cuw-icon-close-circle text-dark"></i>
                </button>
            </div>
            <div class="modal-body d-flex flex-column p-4" style="gap: 12px;">
                <div class="page-builder-text">
                    <?php esc_html_e("Select your preferred page builder", 'checkout-upsell-woocommerce'); ?>
                </div>
                <div class="d-flex" style="gap: 12px;">
                    <div class="builder-section d-flex justify-content-between border border-gray-extra-light rounded w-100"
                         data-href="<?php echo esc_url($page_url . "&tab=campaigns&create=new&type=post_purchase_upsells&page_builder=wordpress"); ?>"
                         style="padding: 12px;">
                        <div class="d-flex" style="gap: 10px;">
                            <img src="<?php echo esc_url(\CUW\App\Helpers\Assets::getUrl('img-pro/page-builder/wordpress.png')); ?>"
                                 alt="" height="28px" width="28px">
                            <label><?php esc_html_e("WordPress (Gutenberg)", 'checkout-upsell-woocommerce'); ?></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="wordpress"
                                   value="wordpress">
                            <label class="form-check-label" for="wordpress">
                            </label>
                        </div>
                    </div>
                    <div class="builder-section d-flex justify-content-between border border-gray-extra-light rounded w-100"
                         data-href="<?php echo esc_url($page_url . "&tab=campaigns&create=new&type=post_purchase_upsells&page_builder=elementor"); ?>"
                         style="padding: 12px; <?php if (empty($is_elementor_active)) echo 'opacity: 0.6; pointer-events: none;' ?>">
                        <div class="d-flex" style="gap: 10px;">
                            <img src="<?php echo esc_url(\CUW\App\Helpers\Assets::getUrl('img-pro/page-builder/elementor.png')); ?>"
                                 alt="" height="28px" width="28px">
                            <label><?php esc_html_e("Elementor", 'checkout-upsell-woocommerce'); ?></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="elementor"
                                   value="elementor"
                                <?php if (empty($is_elementor_active)) echo 'disabled' ?>>
                            <label class="form-check-label" for="elementor">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-3 px-4">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <?php esc_html_e("Cancel", 'checkout-upsell-woocommerce'); ?>
                </button>
                <a href="<?php echo esc_url($page_url . "&tab=campaigns&create=new&type=campaign_type&page_builder=") ?>"
                   class="campaign-create-url btn btn-primary" style="pointer-events: none; opacity: 0.6;">
                    <?php esc_html_e("Continue", 'checkout-upsell-woocommerce'); ?>
                </a>
            </div>
        </div>
    </div>
</div>