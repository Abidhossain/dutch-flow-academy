<!--START BULK DOWNLOAD TAB-->
<li>
    <div class="woo-invoice-row">
        <div class="woo-invoice-col-sm-9">
            <div class="woo-invoice-card woo-invoice-mr-0">
                <div class="woo-invoice-card-body">
                    <form action="" method="post" target="_blank">
                        <?php wp_nonce_field('bulk_download_form_nonce'); ?>
                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="disid"> <?php esc_html_e('Bulk Type', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <select class="woo-invoice-fixed-width woo-invoice-select-control" id="wpifw_bulk_type" name="wpifw_bulk_type">
                                <option value="WPIFW_INVOICE_DOWNLOAD" <?php selected(get_option('wpifw_bulk_type'), 'WPIFW_INVOICE_DOWNLOAD', true); ?>><?php esc_html_e('Invoice', 'webappick-pdf-invoice-for-woocommerce'); ?></option>
                                <option value="WPIFW_PACKING_SLIP" <?php selected(get_option('wpifw_bulk_type'), 'WPIFW_PACKING_SLIP', true); ?>><?php esc_html_e('Packing Slip', 'webappick-pdf-invoice-for-woocommerce'); ?></option>
                                <option value="WPIFW_SHIPPING_LABEL" <?php selected(get_option('wpifw_bulk_type'), 'WPIFW_SHIPPING_LABEL', true); ?>><?php esc_html_e('Shipping Label', 'webappick-pdf-invoice-for-woocommerce'); ?></option>
                                <option value="WPIFW_CSV" <?php selected(get_option('wpifw_bulk_type'), 'WPIFW_CSV', true); ?>><?php esc_html_e('CSV File', 'webappick-pdf-invoice-for-woocommerce'); ?></option>
                            </select>
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="Date-from"> <?php esc_html_e('Date From', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <input class="woo-invoice-fixed-width woo-invoice-datepicker woo-invoice-form-control" id="Date-from" name="wpifw_date_from" placeholder="<?php esc_html_e('Date From', 'webappick-pdf-invoice-for-woocommerce'); ?>" max="<?php echo esc_attr(gmdate('Y-m-d')); ?>" required autocomplete="off">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="Date-to"> <?php esc_html_e('Date To', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <input class="woo-invoice-fixed-width woo-invoice-datepicker woo-invoice-form-control" id="Date-to" name="wpifw_date_to" placeholder="<?php esc_html_e('Date To', 'webappick-pdf-invoice-for-woocommerce'); ?>" max="<?php echo esc_attr(gmdate('Y-m-d')); ?>" required autocomplete="off">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="wpifw_bulk_download_order_status"> <?php esc_html_e('Order Status', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <select id="wpifw_bulk_download_order_status" name="wpifw_bulk_download_order_status" class="woo-invoice-fixed-width woo-invoice-select-control">
                                <option value="all"><?php esc_html_e('All', 'webappick-pdf-invoice-for-woocommerce'); ?></option>
                                <?php
                                $order_statuses = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : '';

                                if (!empty($order_statuses)) {
                                    foreach ($order_statuses as $key => $value) {
                                        echo '<option value="' . $key . '"  >' . esc_html__($value, 'webappick-pdf-invoice-for-woocommerce') . '</option>'; //phpcs:ignore
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="woo-invoice-form-group woo-invoice-add-csv-fields" tooltip="" flow="right">
                            <label class="woo-invoice-custom-label" for="wpifw_add_fields_csv"> <?php esc_html_e('Add Fields', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <select id="wpifw_add_fields_csv" name="wpifw_add_fields_csv[]" class="wpifw_attr wpifw_attributes woo-invoice-fixed-width " multiple>
                                <?php
                                $fields = [
                                    //'get_address', // Returns the requested address in raw, non-formatted way.
                                    //'get_base_data', //Get basic order data in array format.
                                    'get_billing_address_1', //Get billing address line 1.
                                    'get_billing_address_2', //Get billing address line 2.
                                    'get_billing_city', //Get billing city.
                                    'get_billing_company', //Get billing company.
                                    'get_billing_country', //Get billing country.
                                    'get_billing_email', //Get billing email.
                                    'get_billing_first_name', //Get billing first name.
                                    'get_billing_last_name', //Get billing last name.
                                    'get_billing_phone', //Get billing phone.
                                    'get_billing_postcode', //Get billing postcode.
                                    'get_billing_state', //Get billing state.
                                    'get_cancel_endpoint', //Helper method to return the cancel endpoint.
                                    'get_cancel_order_url', //Generates a URL so that a customer can cancel their (unpaid - pending) order.
                                    'get_cancel_order_url_raw', //Generates a raw (unescaped) cancel-order URL for use by payment gateways.
                                    'get_cart_hash', //Get cart hash.
                                    'get_cart_tax', //Gets cart tax amount.
                                    'get_changes', //Expands the shipping and billing information in the changes array.
                                    'get_checkout_order_received_url', //Generates a URL for the thanks page (order received).
                                    'get_checkout_payment_url', //Generates a URL so that a customer can pay for their (unpaid - pending) order. Pass 'true' for the checkout version which doesn't offer gateway choices.
                                    'get_coupon_codes', //Get used coupon codes only.
                                    'get_coupons', //Return an array of coupons within this order.
                                    'get_created_via', //Get created via.
                                    'get_currency', //Gets order currency.
                                    'get_customer_id', //Get customer_id.
                                    'get_customer_ip_address', //Get customer ip address.
                                    'get_customer_note', //Get customer note.
                                    'get_customer_order_notes', //List order notes (public) for the customer.
                                    'get_customer_user_agent', //Get customer user agent.
                                    //'get_data',//Get all class data in array format.
                                    //'get_data_keys',//Returns array of expected data keys for this object.
                                    'get_data_store', //Get the data store.
                                    'get_date_completed', //Get date completed.
                                    'get_date_created', //Get date_created.
                                    'get_date_modified', //Get date_modified.
                                    'get_date_paid', //Get date paid.
                                    'get_discount_tax', //Get discount_tax.
                                    'get_discount_to_display', //Get the discount amount (formatted).
                                    'get_discount_total', //Get discount_total.
                                    'get_download_url', //Get the Download URL.
                                    //'get_downloadable_items',//Get downloads from all line items for this order.
                                    'get_edit_order_url', //Get's the URL to edit the order in the backend.
                                    //'get_extra_data_keys',//Returns all "extra" data keys for an object (for sub objects like product types).
                                    //'get_fees',//Return an array of fees within this order.
                                    'get_formatted_billing_address', //Get a formatted billing address for the order.
                                    'get_formatted_billing_full_name', //Get a formatted billing full name.
                                    'get_formatted_line_subtotal', //Gets line subtotal - formatted for display.
                                    'get_formatted_order_total', //Gets order total - formatted for display.
                                    'get_formatted_shipping_address', //Get a formatted shipping address for the order.
                                    'get_formatted_shipping_full_name', //Get a formatted shipping full name.
                                    'get_id', //Returns the unique ID for this object.
                                    //'get_item',//Get an order item object, based on its type.
                                    'get_item_count', //Gets the count of order items of a certain type.
                                    'get_item_count_refunded', //Gets the count of order items of a certain type that have been refunded.
                                    //'get_item_downloads',//Get the downloadable files for an item in this order.
                                    'get_item_meta', //Get order item meta.
                                    //'get_item_meta_array',//Get all item meta data in array format in the order it was saved. Does not group meta by key like get_item_meta().
                                    'get_item_subtotal', //Get item subtotal - this is the cost before discount.
                                    'get_item_tax', //Get item tax - useful for gateways.
                                    'get_item_total', //Calculate item cost - useful for gateways.
                                    //'get_items',//Return an array of items/products within this order.
                                    //'get_items_tax_classes',//Get all tax classes for items in the order.
                                    'get_line_subtotal', //Get line subtotal - this is the cost before discount.
                                    'get_line_tax', //Get line tax - useful for gateways.
                                    'get_line_total', //Calculate line total - useful for gateways.
                                    'get_meta', //Get Meta Data by Key.
                                    'get_meta_cache_key', //Helper method to compute meta cache key. Different from WP Meta cache key in that meta data cached using this key also contains meta_id column.
                                    'get_meta_data', //Get All Meta Data.
                                    'get_object_read', //Get object read property.
                                    'get_order', //Gets an order from the database.
                                    'get_order_currency', //Get currency.
                                    'get_order_item_totals', //Get totals for display on pages and in emails.
                                    'get_order_key', //Get order key.
                                    'get_order_number', //Gets the order number for display (by default, order ID).
                                    'get_parent_id', //Get parent order ID.
                                    'get_payment_method', //Get the payment method.
                                    'get_payment_method_title', //Get payment method title.
                                    //'get_payment_tokens',//Returns a list of all payment tokens associated with the current order
                                    'get_prices_include_tax', //Get prices_include_tax.
                                    'get_product_from_item', //Get a product (either product or variation).
                                    'get_qty_refunded_for_item', //Get the refunded amount for a line item.
                                    //'get_refunds',//Get order refunds.
                                    'get_remaining_refund_amount', //How much money is left to refund?
                                    //'get_remaining_refund_items',//How many items are left to refund?
                                    'get_rounded_items_total', //Return rounded total based on settings. Will be used by Cart and Orders.
                                    'get_shipping_address_1', //Get shipping address line 1.
                                    'get_shipping_address_2', //Get shipping address line 2.
                                    'get_shipping_address_map_url', //Get a formatted shipping address for the order.
                                    'get_shipping_city', //Get shipping city.
                                    'get_shipping_company', //Get shipping company.
                                    'get_shipping_country', //Get shipping country.
                                    'get_shipping_first_name', //Get shipping first name.
                                    'get_shipping_last_name', //Get shipping_last_name.
                                    'get_shipping_method', //Gets formatted shipping method title.
                                    'get_shipping_methods', //Return an array of shipping costs within this order.
                                    'get_shipping_phone', //Get shipping phone.
                                    'get_shipping_postcode', //Get shipping postcode.
                                    'get_shipping_state', //Get shipping state.
                                    'get_shipping_tax', //Get shipping_tax.
                                    'get_shipping_to_display', //Gets shipping (formatted).
                                    'get_shipping_total', //Get shipping_total.
                                    'get_status', //Return the order statuses without wc- internal prefix.
                                    'get_subtotal', //Gets order subtotal.
                                    'get_subtotal_to_display', //Gets subtotal - subtotal is shown before discounts, but with localised taxes.
                                    'get_tax_refunded_for_item', //Get the refunded tax amount for a line item.
                                    //'get_tax_totals',//Get taxes, merged by code, formatted ready for output.
                                    //'get_taxes',//Return an array of taxes within this order.
                                    'get_total', //Gets order grand total. incl. taxes. Used in gateways.
                                    'get_total_discount', //Gets the total discount amount.
                                    //'get_total_fees',//Calculate fees for all line items.
                                    'get_total_qty_refunded', //Get the total number of items refunded.
                                    'get_total_refunded', //Get amount already refunded.
                                    'get_total_refunded_for_item', //Get the refunded amount for a line item.
                                    'get_total_shipping', //Gets shipping total. Alias of WC_Order::get_shipping_total().
                                    'get_total_shipping_refunded', //Get the total shipping refunded.
                                    'get_total_tax', //Get total tax amount. Alias for get_order_tax().
                                    'get_total_tax_refunded', //Get the total tax refunded.
                                    'get_total_tax_refunded_by_rate_id', //Get total tax refunded by rate ID.
                                    'get_transaction_id', //Get transaction d.
                                    'get_type', //Get internal type.
                                    'get_used_coupons', //Get coupon codes only.
                                    'get_user', //Get the user associated with the order. False for guests.
                                    'get_user_id', //Alias for get_customer_id().
                                    'get_version', //Get order_version.
                                    'get_view_order_url', // Generates a URL to view an order from the my account page.
                                ];
                                if (count($fields)) {
                                    foreach ($fields as $field) {
                                        echo '<option value=' . $field . '  >' . $field . '</option>'; //phpcs:ignore
                                    }
                                }
                                ?>
                            </select>
                            <p class="woo_invoice_meta_html woo_invoice_col_3 woo_invoice_pro_notice">Notice: Challan Pro only.</p>

                        </div>
                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="wpifw_bulk_download_order_email"><?php esc_html_e('Order Email', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <input type="text" class="woo-invoice-fixed-width" id="wpifw_bulk_download_order_email" placeholder="Challan Pro only" disabled name="wpifw_bulk_download_order_email">
                        </div>
                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="wpifw_bulk_download_order_user_id"><?php esc_html_e('Order User ID', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <input type="text" class="woo-invoice-fixed-width" id="wpifw_bulk_download_order_user_id" placeholder="Challan Pro only" disabled name="wpifw_bulk_download_order_user_id">
                        </div>
                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="wpifw_bulk_download_order_payment_method"> <?php esc_html_e('Order Payment Method', 'webappick-pdf-invoice-for-woocommerce'); ?></label>
                            <select id="wpifw_bulk_download_order_payment_method" name="wpifw_bulk_download_order_payment_method" class="woo-invoice-fixed-width woo-invoice-select-control">
                                <option value="all"><?php esc_html_e('All', 'webappick-pdf-invoice-for-woocommerce'); ?></option>
                                <?php
                                $gateways         = WC()->payment_gateways->get_available_payment_gateways();
                                $enabled_gateways = array();

                                if ($gateways) {
                                    foreach ($gateways as $gateway) {

                                        if ('yes' == $gateway->enabled) {
                                            $enabled_gateways[$gateway->id] = $gateway->title;
                                        }
                                    }
                                }
                                foreach ($enabled_gateways as $key => $value) {
                                    echo '<option value="' . $key . '">' . esc_html__($value, 'webappick-pdf-invoice-for-woocommerce') . '</option>'; //phpcs:ignore
                                }
                                ?>
                            </select>

                        </div>

                        <div class="woo-invoice-card-footer">
                            <input type="submit" style="float: right;" id="wpifw_submit_bulk_download" name="wpifw_submit_bulk_download" value="<?php esc_html_e('Download', 'webappick-pdf-invoice-for-woocommerce'); ?>" class="woo-invoice-btn woo-invoice-btn-primary" />
                        </div><!-- end .woo-invoice-card-footer -->
                    </form>
                    <!-- Fetch error message if not found table -->
                    <?php if (isset($_GET['message'])) { //phpcs:ignore 
                    ?>
                        <p style="margin:0; color:red; margin-top: -15px"> <?php echo esc_html(sanitize_text_field(wp_unslash($_GET['message']))); //phpcs:ignore 
                                                                            ?> </p>
                    <?php } ?>
                </div><!-- end .woo-invoice-card-body -->
            </div><!-- end .woo-invoice-card -->
        </div><!-- end .woo-invoice-col-8 -->
        <div class="woo-invoice-col-sm-3 woo-invoice-col-12 ">
            <div class="woo-invoice-banner-sidebar">


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
