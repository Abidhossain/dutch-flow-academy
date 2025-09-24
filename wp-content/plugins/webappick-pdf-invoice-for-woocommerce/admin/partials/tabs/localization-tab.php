<!--START LOCALIZATION TAB-->
<li>
    <div class="woo-invoice-row">
        <div class="woo-invoice-col-8">
            <div class="woo-invoice-card woo-invoice-mr-0">
                <div class="woo-invoice-card-body">
                    <form action="" method="post">
                        <?php wp_nonce_field( 'localization_form_nonce' ); ?>
                        <h3><?php esc_html_e( 'Invoice block', 'webappick-pdf-invoice-for-woocommerce' ); ?></h3>

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="invoice">Invoice</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="invoice" name="wpifw_INVOICE_TITLE"
                                   value="<?php echo ! empty( get_option( 'wpifw_INVOICE_TITLE' ) ) ? esc_attr( get_option( 'wpifw_INVOICE_TITLE' ) ) : 'Invoice'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="payment_method">Payment
                                Method</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="payment_method" name="wpifw_payment_method_text"
                                   value="<?php echo ! empty( get_option( 'wpifw_payment_method_text' ) ) ? esc_attr( get_option( 'wpifw_payment_method_text' ) ) : 'Payment Method'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="Invoice-number">Invoice
                                Number</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="Invoice-number" name="wpifw_INVOICE_NUMBER_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_INVOICE_NUMBER_TEXT' ) ) ? esc_attr( get_option( 'wpifw_INVOICE_NUMBER_TEXT' ) ) : 'Invoice Number'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="Invoice-date">Invoice Date</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="Invoice-date" name="wpifw_INVOICE_DATE_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_INVOICE_DATE_TEXT' ) ) ? esc_attr( get_option( 'wpifw_INVOICE_DATE_TEXT' ) ) : 'Invoice Date'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="order_number">Order Number</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="order_number" name="wpifw_ORDER_NUMBER_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_ORDER_NUMBER_TEXT' ) ) ? esc_attr( get_option( 'wpifw_ORDER_NUMBER_TEXT' ) ) : 'Order Number'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="order_date">Order Date</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="order_date" name="wpifw_ORDER_DATE_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_ORDER_DATE_TEXT' ) ) ? esc_attr( get_option( 'wpifw_ORDER_DATE_TEXT' ) ) : 'Order Date'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="sl">SL</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="sl" name="wpifw_SL"
                                   value="<?php echo ! empty( get_option( 'wpifw_SL' ) ) ? esc_attr( get_option( 'wpifw_SL' ) ) : 'SL'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="product">Product</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="product" name="wpifw_PRODUCT"
                                   value="<?php echo ! empty( get_option( 'wpifw_PRODUCT' ) ) ? esc_attr( get_option( 'wpifw_PRODUCT' ) ) : 'Product'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="price">Price</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="price" name="wpifw_PRICE"
                                   value="<?php echo ! empty( get_option( 'wpifw_PRICE' ) ) ? esc_attr( get_option( 'wpifw_PRICE' ) ) : 'Price'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="quantity">Quantity</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="quantity" name="wpifw_QUANTITY"
                                   value="<?php echo ! empty( get_option( 'wpifw_QUANTITY' ) ) ? esc_attr( get_option( 'wpifw_QUANTITY' ) ) : 'Quantity'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="total">Total</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="total" name="wpifw_ROW_TOTAL"
                                   value="<?php echo ! empty( get_option( 'wpifw_ROW_TOTAL' ) ) ? esc_attr( get_option( 'wpifw_ROW_TOTAL' ) ) : 'Total'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="su-total">Sub Total</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="su-total" name="wpifw_SUBTOTAL_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_SUBTOTAL_TEXT' ) ) ? esc_attr( get_option( 'wpifw_SUBTOTAL_TEXT' ) ) : 'Sub Total'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="Tax1">Tax</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="Tax1" name="wpifw_TAX_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_TAX_TEXT' ) ) ? esc_attr( get_option( 'wpifw_TAX_TEXT' ) ) : 'Tax'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="discount">Discount</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="discount" name="wpifw_DISCOUNT_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_DISCOUNT_TEXT' ) ) ? esc_attr( get_option( 'wpifw_DISCOUNT_TEXT' ) ) : 'Discount'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="grand-total-tax">REFUNDED</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="refund-tax" name="wpifw_REFUNDED_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_REFUNDED_TEXT' ) ) ? esc_attr( get_option( 'wpifw_REFUNDED_TEXT' ) ) : 'Refunded'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="shipping">SHIPPING</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="shipping" name="wpifw_SHIPPING_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_SHIPPING_TEXT' ) ) ? esc_attr( get_option( 'wpifw_SHIPPING_TEXT' ) ) : 'Shipping'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="grand-total-tax">Grand
                                Total</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="grand-total-tax" name="wpifw_GRAND_TOTAL_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_GRAND_TOTAL_TEXT' ) ) ? esc_attr( get_option( 'wpifw_GRAND_TOTAL_TEXT' ) ) : 'Grand Total'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label woo-invoice-download-invoice"
                                   for="grand-total-tax">Download Invoice<br><span
                                    style="font-size: xx-small">(For Email template)</span></label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="grand-total-tax" name="wpifw_DOWNLOAD_INVOICE_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_DOWNLOAD_INVOICE_TEXT' ) ) ? esc_attr( get_option( 'wpifw_DOWNLOAD_INVOICE_TEXT' ) ) : 'Download Invoice'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <h3><?php esc_html_e( 'Packing Slip block', 'webappick-pdf-invoice-for-woocommerce' ); ?></h3>


                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="packing_slip">Packing Slip</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="packing_slip" name="wpifw_PACKING_SLIP_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_PACKING_SLIP_TEXT' ) ) ? esc_attr( get_option( 'wpifw_PACKING_SLIP_TEXT' ) ) : 'Packing Slip'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="Invoice-number_slip">Order
                                Number</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="Invoice-number_slip" name="wpifw_PACKING_SLIP_ORDER_NUMBER_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_PACKING_SLIP_ORDER_NUMBER_TEXT' ) ) ? esc_attr( get_option( 'wpifw_PACKING_SLIP_ORDER_NUMBER_TEXT' ) ) : 'Order Number'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="Invoice-date">Order Date</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="Invoice-date" name="wpifw_PACKING_SLIP_ORDER_DATE_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_PACKING_SLIP_ORDER_DATE_TEXT' ) ) ? esc_attr( get_option( 'wpifw_PACKING_SLIP_ORDER_DATE_TEXT' ) ) : 'Order Date'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="shipping_method">Shipping
                                method</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="shipping_method" name="wpifw_PACKING_SLIP_ORDER_METHOD_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_PACKING_SLIP_ORDER_METHOD_TEXT' ) ) ? esc_attr( get_option( 'wpifw_PACKING_SLIP_ORDER_METHOD_TEXT' ) ) : 'Shipping method'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="product">Product</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="product" name="wpifw_PACKING_SLIP_PRODUCT_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_PACKING_SLIP_PRODUCT_TEXT' ) ) ? esc_attr( get_option( 'wpifw_PACKING_SLIP_PRODUCT_TEXT' ) ) : 'Product'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="weight">Weight</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="weight" name="wpifw_PACKING_SLIP_WEIGHT_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_PACKING_SLIP_WEIGHT_TEXT' ) ) ? esc_attr( get_option( 'wpifw_PACKING_SLIP_WEIGHT_TEXT' ) ) : 'Weight'; ?>">
                        </div><!-- end .woo-invoice-form-group -->

                        <div class="woo-invoice-form-group">
                            <label class="woo-invoice-custom-label" for="quantity">Quantity</label>
                            <input class="woo-invoice-fixed-width woo-invoice-form-control" type="text"
                                   id="quantity" name="wpifw_PACKING_SLIP_QUANTITY_TEXT"
                                   value="<?php echo ! empty( get_option( 'wpifw_PACKING_SLIP_QUANTITY_TEXT' ) ) ? esc_attr( get_option( 'wpifw_PACKING_SLIP_QUANTITY_TEXT' ) ) : 'Quantity'; ?>">
                        </div><!-- end .woo-invoice-form-group -->


                        <div class="woo-invoice-card-footer woo-invoice-save-changes-selector">
                            <input type="submit" style="float:right;" name="wpifw_submit_localization"
                                   value="<?php esc_html_e( 'Save Changes', 'webappick-pdf-invoice-for-woocommerce' ); ?>"
                                   class="woo-invoice-btn woo-invoice-btn-primary"/>
                        </div><!-- end .woo-invoice-card-footer -->

                    </form>
                </div><!-- end .woo-invoice-card-body -->
            </div><!-- end .woo-invoice-card -->
        </div><!-- end .woo-invoice-col-8 -->
    </div><!-- end .woo-invoice-row -->
</li>
<!--END LOCALIZATION TAB-->
