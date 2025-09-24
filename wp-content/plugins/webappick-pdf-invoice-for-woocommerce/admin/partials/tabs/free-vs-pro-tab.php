<!--START FREE VS PREMIUM TAB-->
<li>
    <div class="woo-invoice-row">
        <div class="woo-invoice-col-sm-9">
            <div class="woo-invoice-card woo-invoice-mr-0">
                <div class="woo-invoice-card-body">
                    <table width="100%">
                        <tr>
                            <th style="padding: 20px;font-size:18px" width="50%">Features</th>
                            <th width="25%" style="text-align: center;font-size:18px">Free</th>
                            <th width="25%" style="text-align: center;font-size:18px">Premium</th>
                        </tr>
                        <?php
                        $woo_invoice_comparetable = array(

                            array(
                                'title' => __('Automatic Invoicing', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Attach to Order Email', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Invoice Download From My Account', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Custom Date Format', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Display ID/SKU', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Display Currency Code', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Display Payment Method', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Packing Slip', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Footer Info Section', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Bulk Invoice/Packing Slip Download', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Invoice Template Translation', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Display Shipping Cost With Tax / Without Tax', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Total Tax', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),
                            array(
                                'title' => __('Invoice/Packing Slip Print', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => true,
                            ),

                            array(
                                'title' => __('Bar Code', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('QR Code', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('ZATCA QR code', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('GST Invoice', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Invoice Backup To Dropbox', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Write Custom CSS', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Custom Invoice Numbering Options', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('WPML Compatibility', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Polylang Compatibility', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Individual Product Tax & Tax %', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Total Without Tax', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Paid Stamp', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Authorized Signature', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Custom Background', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Display Product Image', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Display Product Category', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Display Product Short Description', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Display Discounted Price', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Proforma Invoice', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),

                            array(
                                'title' => __('WooCommerce Subscription Compatibility', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Order Delivery Address Print', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Download Bulk Delivery Address Print', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Custom Paper Size', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Pagination Style', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Display refund details', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Display fee details', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),
                            array(
                                'title' => __('Generate credit note template', 'webappick-pdf-invoice-for-woocommerce'),
                                'free'  => false,
                            ),

                        );
                        foreach ($woo_invoice_comparetable as $invoice_feature) {
                        ?>
                            <tr>
                                <td class="woo-invoice-proFree-feature"><?php printf(esc_html__('%1$s', 'webappick-pdf-invoice-for-woocommerce'), $invoice_feature['title']); //phpcs:ignore 
                                                                        ?></td>
                                <?php if (false === $invoice_feature['free']) { ?>
                                    <td class="woo-invoice-proFree-free"><span class="dashicons dashicons-no"></span></td>
                                <?php } else { ?>
                                    <td class="woo-invoice-proFree-pro"><span class="dashicons dashicons-yes"></span></td>
                                <?php } ?>
                                <td class="woo-invoice-proFree-pro"><span class="dashicons dashicons-yes"></span></td>
                            </tr>
                        <?php } ?>
                        </tfoot>
                    </table>
                </div><!-- end .woo-invoice-card -->
            </div><!-- end .woo-invoice-card -->
        </div><!-- end .woo-invoice-col-8 -->
        <div class="woo-invoice-col-sm-3 woo-invoice-col-12 ">
            <div class="woo-invoice-banner-sidebar">
                <div class="woo-invoice-card">

                </div>

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
<!--END FREE VS PREMIUM TAB-->