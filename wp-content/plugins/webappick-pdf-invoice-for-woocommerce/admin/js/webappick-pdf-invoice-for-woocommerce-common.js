(function ( $ ) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     */
    $(
        function () {
            $("#doaction, #doaction2").click(
                function (event) {
                    var actionselected = $(this).attr("id").substr(2);
                    var getAction = $('select[name="' + actionselected + '"]').val();
                    if(getAction==="wpifw_bulk_invoice") {
                        event.preventDefault();
                        var wpifwOrderIds = [];
                        $('tbody th.check-column input[type="checkbox"]:checked').each(
                            function () {
                                wpifwOrderIds.push($(this).val());
                            }
                        );

                        if (!wpifwOrderIds.length) {
                            alert('You have to select orders first!');
                            return false;
                        }

                        var order_ids=wpifwOrderIds.join(',');
                        var URL;
                        if (woo_invoice_ajax_obj_2.woo_invoice_ajax_url_2.indexOf("?") != -1) {
                            URL = woo_invoice_ajax_obj_2.woo_invoice_ajax_url_2+'&action=wpifw_generate_invoice&order_ids='+order_ids+'&_wpnonce='+woo_invoice_ajax_obj_2.nonce;
                        } else {
                            URL = woo_invoice_ajax_obj_2.woo_invoice_ajax_url_2+'?action=wpifw_generate_invoice&order_ids='+order_ids+'&_wpnonce='+woo_invoice_ajax_obj_2.nonce;
                        }

                        window.open(URL,'_blank');

                        return false;
                    }else if(getAction==="wpifw_bulk_invoice_packing_slip") {

                        event.preventDefault();
                        var wpifwOrderIds = [];
                        $('tbody th.check-column input[type="checkbox"]:checked').each(
                            function () {
                                wpifwOrderIds.push($(this).val());
                            }
                        );

                        if (!wpifwOrderIds.length) {
                            alert('You have to select orders first!');
                            return false;
                        }

                        var order_ids=wpifwOrderIds.join(',');
                        var URL;
                        if (woo_invoice_ajax_obj_2.woo_invoice_ajax_url_2.indexOf("?") != -1) {
                            URL = woo_invoice_ajax_obj_2.woo_invoice_ajax_url_2+'&action=wpifw_generate_invoice_packing_slip&order_ids='+order_ids+'&_wpnonce='+woo_invoice_ajax_obj_2.nonce;
                        } else {
                            URL = woo_invoice_ajax_obj_2.woo_invoice_ajax_url_2+'?action=wpifw_generate_invoice_packing_slip&order_ids='+order_ids+'&_wpnonce='+woo_invoice_ajax_obj_2.nonce;
                        }

                        window.open(URL,'_blank');

                        return false;
                    }

                }
            );
        }
    );
    /**
     * When the window is loaded:
     */

    /**
     * Added postbox toggle
     */
    $('._winvoice_docs .toggle-indicator').on('click', function (){
        $(this).closest('.postbox').toggleClass('closed');
    });

    $('#atttoorder').change(
        function () {
            if(this.checked !== true) {
                $('#emailAttechedData').css('display','none');
            }
            else{
                $('#emailAttechedData').css('display','block');
            }
        }
    );

    $('#wpifw_download').change(
        function () {
            if(this.checked !== true) {
                $('#downloadAttechedData').css('display','none');
            }
            else{
                $('#downloadAttechedData').css('display','block');
            }
        }
    );





})(jQuery);