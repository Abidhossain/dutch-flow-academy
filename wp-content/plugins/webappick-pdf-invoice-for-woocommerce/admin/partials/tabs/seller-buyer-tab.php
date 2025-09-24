<!--START SELLER & BUYER  TAB-->
<li>
    <div class="woo-invoice-row">
        <div class="woo-invoice-col-sm-9 ">
            <div class="woo-invoice-card woo-invoice-mr-0">
                <div class="woo-invoice-card-body">
                    <form action="" method="post">
                        <?php wp_nonce_field('seller_form_nonce'); ?>
                        <div class="woo-invoice-header-title woo-invoice-mt-0">
                            <h4><?php esc_html_e('Seller Block', 'webappick-pdf-invoice-for-woocommerce'); ?></h4>
                        </div>
                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="logo"><?php esc_html_e('Logo Image', 'webappick-pdf-invoice-for-woocommerce'); ?></label>

                            <div style="display:inline-block;">
                                <?php wp_enqueue_media(); ?>
                                <input id="wpifw_upload_logo_button" type="button" class="button" value="<?php esc_html_e('Upload Logo', 'webappick-pdf-invoice-for-woocommerce'); ?>" />
                                <input id="wpifw_remove_logo_button" type="button" class="button" value="<?php esc_html_e('Remove Logo', 'webappick-pdf-invoice-for-woocommerce'); ?>" />
                                <input type='hidden'  name='wpifw_logo_attachment_id' id='wpifw_logo_attachment_id' value='<?php echo esc_attr(get_option('wpifw_logo_attachment_image_id')); ?>'>
                            </div>
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <?php $wpifw_logo_width = '' != get_option('wpifw_logo_width') ? get_option('wpifw_logo_width') : '20%'; ?>
                            <label class="woo-invoice-custom-label" for="logo-height-width"><?php esc_html_e('Logo Size', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <input class="woo-invoice-uploaded-logo-width woo-invoice-fixed-width woo-invoice-form-control" type="text" name="wpifw_logo_width" placeholder="<?php echo esc_html('20%'); ?>" value='<?php echo esc_attr($wpifw_logo_width); ?>'>
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="bltitle"><?php esc_html_e('Seller Title', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text" id="bltitle" name="wpifw_block_title_from" value="<?php echo esc_attr(get_option('wpifw_block_title_from')); ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="cname"><?php esc_html_e('Company Name', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text" id="cname" name="wpifw_cname" value="<?php echo esc_attr(get_option('wpifw_cname')); ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-tinymce-label" for="cdetails"><?php esc_html_e('Company Details', 'webappick-pdf-invoice-for-woocommerce'); ?></label>

                            <div class="woo-invoice-tinymce-textarea">
                                <textarea style="height:150px;" class="woo-invoice-form-control" id="cdetails" name="wpifw_cdetails" value=""><?php echo esc_attr(get_option('wpifw_cdetails')); ?></textarea>
                            </div><!-- end .woo-invoice-tinymce-textarea -->
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-tinymce-label" for="terms-and-condition"><?php esc_html_e('Footer 1', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <div class="woo-invoice-tinymce-textarea">
                                <textarea style="height:150px;" class="woo-invoice-form-control" id="terms-and-condition" name="wpifw_terms_and_condition" value=""><?php echo esc_textarea(get_option('wpifw_terms_and_condition')); ?></textarea>
                            </div><!-- end .woo-invoice-tinymce-textarea -->

                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-tinymce-label" for="other-information"><?php esc_html_e('Footer 2', 'webappick-pdf-invoice-for-woocommerce'); ?></label>

                            <div class="woo-invoice-tinymce-textarea">
                                <textarea style="height:150px;" class="woo-invoice-form-control" id="other-information" name="wpifw_other_information" value=""><?php echo esc_textarea(get_option('wpifw_other_information')); ?></textarea>
                            </div>
                        </div><!-- end .woo-invoice-form-group -->
                        <div class="woo-invoice-header-title woo-invoice-mt-0">
                            <h4><?php esc_html_e('Buyer Block', 'webappick-pdf-invoice-for-woocommerce'); ?></h4>
                        </div>

                        <div class="woo-invoice-form-group">
                            <div class="woo-invoice-custom-control woo-invoice-custom-switch" style="padding-left:0!important;">
                                <div class="woo-invoice-toggle-label">
                                    <span class="woo-invoice-checkbox-label"><?php esc_html_e('Disable Phone Number', 'webappick-pdf-invoice-for-woocommerce'); ?></span>
                                </div>
                                <div class="woo-invoice-toggle-container" tooltip="Disable Phone Number." flow="right">
                                    <input type="hidden" name="wpifw_display_phone" value="0">
                                    <input type="checkbox" class="woo-invoice-custom-control-input" id="wpifw_display_phone" name="wpifw_display_phone" value="1" <?php checked(get_option('wpifw_display_phone'), $woo_invoice_current, true); ?>>
                                    <label class="woo-invoice-custom-control-label" for="wpifw_display_phone"></label>
                                </div>
                            </div>
                        </div><!-- end .woo-invoice-form-group -->


                        <div class="woo-invoice-form-group">
                            <div class="woo-invoice-custom-control woo-invoice-custom-switch" style="padding-left:0!important;">
                                <div class="woo-invoice-toggle-label">
                                    <span class="woo-invoice-checkbox-label"><?php esc_html_e('Disable Email Address', 'webappick-pdf-invoice-for-woocommerce'); ?></span>
                                </div>
                                <div class="woo-invoice-toggle-container" tooltip="Disable Email Address." flow="right">
                                    <input type="hidden" name="wpifw_display_email" value="0">
                                    <input type="checkbox" class="woo-invoice-custom-control-input" id="wpifw_display_email" name="wpifw_display_email" value="1" <?php checked(get_option('wpifw_display_email'), $woo_invoice_current, true); ?>>
                                    <label class="woo-invoice-custom-control-label" for="wpifw_display_email"></label>
                                </div>
                            </div>
                        </div><!-- end .woo-invoice-form-group -->


                        <div class="woo-invoice-card-footer woo-invoice-save-changes-selector">
                            <input type="submit" style="float:right;" name="wpifw_submit_seller&buyer" value="<?php esc_html_e('Save Changes', 'webappick-pdf-invoice-for-woocommerce'); ?>" class="woo-invoice-btn woo-invoice-btn-primary" />
                        </div><!-- end .woo-invoice-card-footer -->
                    </form>

                </div><!-- end .woo-invoice-card-body -->
            </div><!-- end .woo-invoice-card -->
        </div><!-- end .woo-invoice-col-sm-8 -->
        <div class="woo-invoice-col-sm-3 ">
            <div class="woo-invoice-banner-sidebar">
                <div class="woo-invoice-card">
                    <div class="woo-invoice-card-header">
                        <div class="woo-invoice-card-header-title">
                            <b><?php esc_html_e('Logo Preview', 'webappick-pdf-invoice-for-woocommerce'); ?></b>
                        </div>
                    </div>
                    <div class="woo-invoice-card-body">
                        <div class='wpifw_logo-preview-wrapper'>
                            <?php
                            if (get_option('wpifw_logo_attachment_image_id') && !empty(get_option('wpifw_logo_attachment_image_id'))) {
                                $woo_invoice_url = wp_get_attachment_url(get_option('wpifw_logo_attachment_image_id'));
                            ?>
                                <img style="max-width:90px;display: block;margin:0 auto;" id='wpifw_logo-preview' src='<?php echo esc_url($woo_invoice_url); ?>'>
                            <?php
                            } else {
                            ?>
                                <img style="max-width:90px;display: block;margin:0 auto;" id='wpifw_logo-preview' src=''>
                            <?php
                            }

                            ?>
                        </div>
                    </div>
                </div>

                <!-- <div class="woo_invoice_template_free_vs_pro">
                <div class="woo-invoice-card-body" style="text-align: center">
                    <a class="invoice_template_preiview_btn" target="_blank" href="<?php echo esc_url(admin_url() . 'admin.php?page=webappick-woo-pro-vs-free'); ?>"><?php echo esc_html_e('Free VS Pro', 'webappick-pdf-invoice-for-woocommerce') ?></a>
                </div>< end .woo-invoice-card-body -->
                <!-- </div>end .woo-invoice-card -->

                <div class="woo-invoice-banner-container">
                    <div class="woo-invoice-banner-logo">
                        <a class="wapk-woo-invoice-banner-logo" href="<?php echo esc_url('https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free'); ?>" target="_blank"><img src="<?php echo esc_url($woo_invoice_banner_logo_dir); ?>" alt="Woo Invoice"></a>
                    </div>
                    <div class="woo-invoice-banner-title">
                        <h2>Why Upgrade to the Challan Pro?</h2>
                        <ul>
                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                Customizable Readymade Templates
                            </li>

                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                Paid Stamp, Signature, Watermark
                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                Multilingual & Multicurrency Invoice
                            </li>
                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                Multiple Tax Classes Support
                            </li>
                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                Add Image, QR & BAR Code
                            </li>
                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                ZATCA-compliant (for Saudi Arabia)
                            </li>
                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                GST Invoice (for India)
                            </li>
                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                Compatible with a Variety of Plugins
                            </li>
                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                Dedicated Customer Support and Live Chat
                            </li>
                            <li>
                                <span>
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" class="ctx-flex-shrink-0 ctx-w-4 ctx-h-4 ctx-text-blue-600 dark:text-blue-500" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                                30-Day Money Back Guarantee
                            </li>



                        </ul>

                    </div>
                    <button class="woo-invoice-banner-btn" onclick=" window.open('https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips', '_blank'); return false;">
                        Get Challan pro Now
                        <svg width="20" height="20" fill="#fff" viewBox="0 0 24 24" version="1.2" baseProfile="tiny" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.396 18.433 17 12l-6.604-6.433A2 2 0 0 0 7 7v10a2 2 0 0 0 3.396 1.433z" />
                        </svg>
                    </button>


                </div>
            </div>

        </div><!-- end .woo-invoice-col-sm-4 -->
    </div><!-- end .woo-invoice-row -->
</li>
<!--END SELLER & BUYER  TAB-->
