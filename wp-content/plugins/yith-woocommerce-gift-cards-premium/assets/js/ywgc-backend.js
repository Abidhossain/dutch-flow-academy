jQuery(function($) {

    /**
     * Remove amount for the current gift card
     */
    $(document).on("click", "a.remove-amount", function(e) {
        e.preventDefault();
        remove_amount($(this));
    });


    function remove_amount(item) {

        var clicked_item = item.closest("span.variation-amount");
        var position_of_clicked_item = clicked_item.index();

        var currency_prices_section = $(".yith-wcmcs-currencies-prices");
        var is_currency_amount = clicked_item.parent().parent().parent().parent().hasClass('yith-wcmcs-currencies-prices--row');

        clicked_item.remove();

        if (currency_prices_section.length && !is_currency_amount) {

            $(".yith-wcmcs-currencies-prices--row .variation-amount-list").each(function() {
                $(this).children().eq(position_of_clicked_item).remove();
            });

        }

        $(document.body).trigger('removed_gift_card_amount');

    };

    /**
     * Add a new amount for the current gift card
     */
    $(document).on("click", "a.add-new-amount", function(e) {
        e.preventDefault();
        add_amount($(this));
    });

    /**
     * Add a new amount to current gift card
     * @param item
     */
    function add_amount(item) {

        var amount_input_value = item.parent().find('#gift_card-amount').val();
        var clicked_item = item.closest("span.add-new-amount-section");
        var amounts_list = item.parent().parent().find("span.variation-amount-list");
        var currency_prices_section = $(".yith-wcmcs-currencies-prices");
        var is_currency_amount = clicked_item.parent().parent().hasClass('yith-wcmcs-currencies-prices--row');
        var hidden_input_aux = $(".variation-amount-aux");
        var amount_index = amounts_list.find("span.variation-amount").length;
        var add_amount_condition = true;

        if (!amount_input_value.length || parseFloat(amount_input_value.replace(',', '.')) <= 0) {
            add_amount_condition = false;

            $('.add-new-amount-section #gift_card-amount').addClass('ywgc-wrong-amount-alert-border ywgc-wrong-amount-alert');
            $('.add-new-amount-section .ywgc-currency-symbol-enter-amount').addClass('ywgc-wrong-amount-alert');

            $('.ywgc-tooltip-container.ywgc-invalid-amount').removeClass('ywgc-hidden');
        }

        amounts_list.find("span.variation-amount .yith_wcgc_multi_currency").each(function() {

            if (parseFloat($(this).val().replace(',', '.')) == parseFloat(amount_input_value.replace(',', '.'))) {
                add_amount_condition = false;

                $('.add-new-amount-section #gift_card-amount').addClass('ywgc-wrong-amount-alert-border ywgc-wrong-amount-alert');
                $('.add-new-amount-section .ywgc-currency-symbol-enter-amount').addClass('ywgc-wrong-amount-alert');

                $('.ywgc-tooltip-container.ywgc-amount-already-added').removeClass('ywgc-hidden');

            }

        });

        if (add_amount_condition) {

            hidden_input_aux.clone().insertBefore(hidden_input_aux).removeClass('ywgc-hidden variation-amount-aux').addClass('variation-amount new-amount');

            var hidden_input = $('.variation-amount.new-amount input.yith_wcgc_multi_currency');
            var visible_input = $('.variation-amount.new-amount input.gift_card-amount');

            hidden_input.attr('name', 'gift-card-amounts[' + amount_index + ']');
            hidden_input.val(amount_input_value);

            hidden_input.parent().data("amount", amount_input_value);

            visible_input.val(amount_input_value);
            visible_input.data("amount", amount_input_value);

            $('.variation-amount.new-amount').data("amount", amount_input_value);
            $('.variation-amount.new-amount').removeClass('new-amount');


            // sort the amount list
            amounts_list.find("span.variation-amount ").sort(sort_amount_list).appendTo(amounts_list);

            // clear the add amount input
            $('#gift_card-amount').val('');
            $('#gift_card-amount').selectionStart = 0;
            $('#gift_card-amount').selectionEnd = 0;

            // if there is additional currencies
            if (currency_prices_section.length && !is_currency_amount) {

                $(".yith-wcmcs-currencies-prices--row .variation-amount-list").each(function() {

                    var hidden_input_currency_aux = $(this).find(".variation-amount-aux-currency");

                    hidden_input_currency_aux.clone().insertBefore(hidden_input_currency_aux).removeClass('ywgc-hidden variation-amount-aux-currency').addClass('variation-amount new-amount-currency');

                    var currency_hidden_input = $('.variation-amount.new-amount-currency input.yith_wcgc_multi_currency');
                    var currency_visible_input = $('.variation-amount.new-amount-currency input.gift_card-amount');

                    var currency_id = currency_visible_input.data('currency-id');
                    var currency_options = $(this).parent().parent().parent().data('currency-options');

                    var fromPrice = parseFloat(amount_input_value.replace(',', '.')),
                        fromRate = 1,
                        toRate = currency_options['rate'],
                        toDecimals = currency_options['decimals'],
                        toRound = currency_options['round'],
                        decimalSeparator = currency_options['decimal_separator'];

                    var converted_amount = convert_amount(fromPrice, fromRate, toRate, toDecimals, toRound, decimalSeparator);

                    currency_hidden_input.attr('name', 'yith_wcgc_multi_currency[gift-card-amounts][' + currency_id + '][' + amount_index + ']');
                    currency_hidden_input.val(converted_amount);

                    currency_hidden_input.parent().data("amount", converted_amount);


                    currency_visible_input.val(converted_amount);
                    currency_visible_input.data("amount", converted_amount);

                    $('.variation-amount.new-amount-currency').data("amount", converted_amount).removeClass('new-amount-currency');

                    $(this).find("span.variation-amount").sort(sort_amount_list).appendTo($(this));

                });

            }

            $(document.body).trigger('added_gift_card_amount');
        }

    }

    $(document).on('input', '.add-new-amount-section #gift_card-amount', function() {
        $(this).removeClass('ywgc-wrong-amount-alert-border ywgc-wrong-amount-alert');
        $('.add-new-amount-section .ywgc-currency-symbol-enter-amount').removeClass('ywgc-wrong-amount-alert');

        $('.ywgc-tooltip-container').addClass('ywgc-hidden');
    });

    /**
     * Add a new amount for the current gift card on "enter"
     */
    $(document).on('keypress', 'input#gift_card-amount', function(e) {
        if (event.which === 13) {
            e.preventDefault();

            $(this).parent().find('a.add-new-amount').click();

        }
    });


    /**
     * Update amount for the current gift card
     */
    $(document).on('focusout', 'input.gift_card-amount', function(e) {
        e.preventDefault();

        var amount_input = $(this).parent().find('.gift_card-amount').val();
        $(this).parent().find('.yith_wcgc_multi_currency').val(amount_input);
    });

    function sort_amount_list(a, b) {

        return parseFloat($(b).data('amount').toString().replace(',', '.')) < parseFloat($(a).data('amount').toString().replace(',', '.')) ? 1 : -1;
    }

    /**
     * Convert amount to specific currency
     */
    function convert_amount(fromPrice, fromRate, toRate, toDecimals, toRound, decimalSeparator) {

        var price = parseFloat(fromPrice) / parseFloat(fromRate) * parseFloat(toRate);

        switch (toRound) {
            case 'round-up':
                var pow = Math.pow(10, toDecimals);
                price = -Math.round((price * (-1) * pow - 0.5)) / pow;
                break;
            case 'round-down':
                price -= 0.5 / Math.pow(10, toDecimals);
                break;
            case 'round-int-up':
                price = Math.ceil(price);
                break;
            case 'round-int-down':
                price = Math.floor(price);
                break;
        }
        price = price.toFixed(toDecimals);

        price = Math.max(parseFloat(price.replace(decimalSeparator, '.')), 0).toString().replace('.', decimalSeparator);

        return price < 0 ? 0 : price;

    }


    $(document).on('change', 'input[name="ywgc_physical_gift_card"]', function(e) {
        var status = $(this).prop("checked");
        $('input[name="_virtual"]').prop("checked", !status);
    });

    $('body .ywgc_order_sold_as_gift_card').each(function() {
        $(this).parent('td').find('.wc-order-item-name').hide();
    });

    //show the manage stock in the inventory tab
    $('._manage_stock_field').addClass('show_if_gift-card').show();

    /* Manage date when gift card is created manually */
    if (typeof jQuery.fn.datepicker !== "undefined") {

        $(".ywgc-expiration-date-picker").datepicker({ dateFormat: ywgc_data.date_format, minDate: +1 });
    }


    var default_button_text = $('button.ywgc-actions:first').text();


    $(document).on('click', 'button.ywgc-actions', function(e) {
        e.preventDefault();

        var button      = $(this),
            link        = button.prev('#ywgc_direct_link').text(),
            copied_text = $('#ywgc_copied_to_clipboard').text();

        if ( navigator.clipboard && window.isSecureContext ) {
            navigator.clipboard.writeText( link ).then(
                () => {
                    button.text(copied_text);
                },
                () => {
                    console.log( 'Copy to clipboard failed' );
                }
            );
        }

        setTimeout(function() {
            button.text(default_button_text);
        }, 1000);
    });

    $(document).on('change', '.ywgc-toggle-enabled input', function() {

        var enabled = $(this).val() === 'yes' ? 'yes' : 'no',
            container = $(this).closest('.ywgc-toggle-enabled'),
            gift_card_ID = container.data('gift-card-id');

        $.ajax({
            type: 'POST',
            data: {
                action: 'ywgc_toggle_enabled_action',
                id: gift_card_ID,
                enabled: enabled
            },
            url: ajaxurl,
            success: function(response) {
                if (typeof response.error !== 'undefined') {
                    alert(response.error);
                }
            },
        });
    });


    if ($('.ywgc-override-product-settings input').val() === 'yes') {

        $('.ywgc-custom-amount-field').removeClass('ywgc-hidden');
        $('.minimal-amount-field').removeClass('ywgc-hidden');
        $('.maximum-amount-field').removeClass('ywgc-hidden');

    }

    $(document).on('change', '.ywgc-override-product-settings > input', function() {

        var enabled = $(this).val() === 'yes' ? 'yes' : 'no';

        if (enabled == 'yes') {
            $('.ywgc-custom-amount-field').show();

            if ($('.ywgc-custom-amount-field input').val() === 'yes') {
                $('.minimal-amount-field').show();
                $('.maximum-amount-field').show();
            }


        } else {
            $('.ywgc-custom-amount-field').hide();
            if ($('.ywgc-custom-amount-field input').val() === 'yes') {
                $('.minimal-amount-field').hide();
                $('.maximum-amount-field').hide();
            }


        }
    });


    if ($('.ywgc-custom-amount-field input').val() === 'yes' && $('.ywgc-override-product-settings input').val() == 'yes') {
        $('.minimal-amount-field').removeClass('ywgc-hidden');
        $('.maximum-amount-field').removeClass('ywgc-hidden');

    } else {
        $('.minimal-amount-field').addClass('ywgc-hidden');
        $('.maximum-amount-field').addClass('ywgc-hidden');

    }


    $(document).on('change', '.ywgc-custom-amount-field input', function() {
        var enabled = $(this).val() === 'yes' ? 'yes' : 'no';

        if (enabled == 'yes') {
            $('.minimal-amount-field').show();
            $('.maximum-amount-field').show();

        } else {
            $('.minimal-amount-field').hide();
            $('.maximum-amount-field').hide();

        }
    });


    if ($('.ywgc-add-discount-settings input').val() === 'yes') {
        $('.ywgc-add-discount-settings-container').removeClass('ywgc-hidden');
    }

    $(document).on('change', '.ywgc-add-discount-settings input', function() {
        var enabled = $(this).val() === 'yes' ? 'yes' : 'no';

        if (enabled == 'yes') {
            $('.ywgc-add-discount-settings-container').show();
        } else {
            $('.ywgc-add-discount-settings-container').hide();
        }

    });


    if ($('.ywgc-expiration-settings input').val() === 'yes') {
        $('.ywgc-expiration-settings-container').removeClass('ywgc-hidden');
    }

    $(document).on('change', '.ywgc-expiration-settings input', function() {
        var enabled = $(this).val() === 'yes' ? 'yes' : 'no';

        if (enabled == 'yes') {
            $('.ywgc-expiration-settings-container').show();
        } else {
            $('.ywgc-expiration-settings-container').hide();
        }

    });

    if ( $('.ywgc-excluded-categories-settings input').val() ) {
        $('.ywgc-excluded-categories-settings-container').removeClass('ywgc-hidden');
    }

    $(document).on('change', '.ywgc-excluded-categories-settings input', function() {
        var enabled = $(this).val() === 'yes' ? 'yes' : 'no';

        if (enabled == 'yes') {
            $('.ywgc-excluded-categories-settings-container').show();
        } else {
            $('.ywgc-excluded-categories-settings-container').hide();
        }

    });

    $(document).ready(function() {

        var amounts = $('.ywgc-product-edit-page-amount-options .variation-amount-list').has('span.variation-amount').length;

        if (!amounts) {
            $('.ywgc-product-edit-page-multi-currency-options').hide();
        }

    });


    $(document).on('added_gift_card_amount', function(event) {

        $('.ywgc-product-edit-page-multi-currency-options').show();


        var amounts = $('.ywgc-product-edit-page-amount-options .variation-amount-list span.variation-amount').length;

        $(".ywgc-product-edit-page-multi-currency-options .yith-wcmcs-currencies-prices .yith-wcmcs-currencies-prices--row").each(function() {

            var currency_amounts = $(this).find('.variation-amount-list span.variation-amount').length;

            if (amounts != currency_amounts) {
                var difference = Math.abs(amounts - currency_amounts);
                $(this).find('.variation-amount-list span.variation-amount:nth-last-child(-n+' + difference + ')').remove();
            }
        });

    });

    $(document).on('removed_gift_card_amount', function(event) {

        var amounts = $('.ywgc-product-edit-page-amount-options .variation-amount-list').has('span').length;

        if (!amounts) {
            $('.ywgc-product-edit-page-multi-currency-options').hide();
        }

        var amounts = $('.ywgc-product-edit-page-amount-options .variation-amount-list span.variation-amount').length;

        $(".ywgc-product-edit-page-multi-currency-options .yith-wcmcs-currencies-prices .yith-wcmcs-currencies-prices--row").each(function() {

            var currency_amounts = $(this).find('.variation-amount-list span.variation-amount').length;

            if (amounts != currency_amounts) {
                var difference = Math.abs(amounts - currency_amounts) + 1;
                $(this).find('.variation-amount-list span.variation-amount:nth-last-child(-n+' + difference + ')').remove();
            }
        });

    });


    // Code Generator Modal Handling
    $(document).on( 'click', '.create-code-custom-button', function(ev){
        ev.preventDefault();

        yith.ui.modal( {
            title  : ywgc_data.code_modal.title,
            content: ywgc_data.code_modal.content,
            footer: ywgc_data.code_modal.footer,
            width: 600,
            allowWpMenu: false,
            closeWhenClickingOnOverlay: true,
            allowClosingWithEsc: true,
            classes: {
                title: 'yith-gift-card-generator-modal-title',
                content: 'yith-gift-card-generator-modal-content',
                footer: 'yith-gift-card-generator-modal-footer'
            }
        });
    });

    var last_row = $( '#gift-card-generator-radio-modal-generator').parent();
    last_row.append('#ywgc-gift-card-generator-container');


    $(document).on('click', '#gift-card-generator-radio-modal-one_code', function(){
        $( '#yith-gift-card-generator-modal-button' ).attr("href", ywgc_data.create_code_url ).removeClass( 'multiple-generation' );
        $( '#ywgc-gift-card-generator-container' ).css( 'display', 'none');
    });

    $(document).on('click', '#gift-card-generator-radio-modal-generator', function(){

        var last_row = $( 'input#gift-card-generator-radio-modal-generator').parent(),
            container = $('#ywgc-gift-card-generator-container');
        last_row.append(container);

        $( '#ywgc-gift-card-generator-container' ).css( 'display', 'inline-flex');

        $( '#yith-gift-card-generator-modal-button' ).attr("href", "" ).addClass( 'multiple-generation' );
    });


    // Export/Import Modal Handling
    $(document).on( 'click', '.export-import-custom-button', function(ev){
        ev.preventDefault();

        yith.ui.modal( {
            title  : ywgc_data.export_import_modal.title,
            content: ywgc_data.export_import_modal.content,
            width: 600,
            allowWpMenu: false,
            closeWhenClickingOnOverlay: true,
            allowClosingWithEsc: true,
            classes: {
                title: 'yith-gift-card-export-import-modal-title',
                content: 'yith-gift-card-export-import-modal-content',
                footer: 'yith-gift-card-export-import-modal-footer'
            },
            onCreate: function () {
                $('#ywgc_export_option_date_from').datepicker({
                    dateFormat: ywgc_data.date_format
                });
                $('#ywgc_export_option_date_to').datepicker({
                    dateFormat: ywgc_data.date_format
                });
            }
        });
    });

    // Export Import steps handling
    const changeStep = function( stepTo ){
        $( '.single-step.active' ).removeClass( 'active' ).hide();
        $( '.single-step[data-step="'+ stepTo +'"]' ).show().addClass( 'active' );
        $( '.step-label.active' ).removeClass( 'active' );
        $( '.step-label[data-step="'+ stepTo +'"]' ).addClass( 'active' );
    };

    $( document ).on( 'click', '.move-step.yith-gift-card-export-import-modal-button', function(e){
        e.preventDefault();
        showConfiguration( $( '#gift-card-export-import-radio-modal' ).val() );
        changeStep( $( this ).data( 'step-to' ) );
    } );

    $( document ).on( 'click', '.previous', function(e){
        e.preventDefault();
        changeStep( $( this ).data( 'step-to' ) );
    } );

    $( document ).on( 'click', 'button.try-again', function(e){
        e.preventDefault();
        changeStep( $( this ).data( 'step-to' ) );
    } );

    const showConfiguration = function( value ){
        if ( value === 'import' ){
            $( '.yith-gift-card-export-import-modal-title.choose-action' ).hide();
            $( '.yith-gift-card-export-import-modal-title.export' ).hide();
            $( '.yith-gift-card-export-import-modal-title.import' ).show();
            $( '.export-configuration' ).hide();
            $( '.ywgc-export-button' ).hide();
            $( '.ywgc-export-done' ).hide();
            $( '.import-configuration' ).show();
            $( '.ywgc-import-button' ).show();
            $( '.ywgc-import-done' ).show();
            $( 'input#ywgc_file_import_csv' ).prop('required',true);
        }
        else{
            $( '.yith-gift-card-export-import-modal-title.choose-action' ).hide();
            $( '.yith-gift-card-export-import-modal-title.import' ).hide();
            $( '.yith-gift-card-export-import-modal-title.export' ).show();
            $( '.export-configuration' ).show();
            $( '.ywgc-export-button' ).show();
            $( '.ywgc-export-done' ).show();
            $( '.import-configuration' ).hide();
            $( '.ywgc-import-button' ).hide();
            $( '.ywgc-import-done' ).hide();
            $( 'input#ywgc_file_import_csv' ).prop('required',false);
        }
    };

    // dependencies
    $( document ).on( 'change', '#ywgc_export_option_date', function(e){
        e.preventDefault();

        if ( $(this).val() === 'by_date' ){
            $('div.ywgc-date-from-to-date-selectors').show();
        }
        else{
            $('div.ywgc-date-from-to-date-selectors').hide();

            $('input#ywgc_export_option_date_from').datepicker('setDate', null);
            $('input#ywgc_export_option_date_to').datepicker('setDate', null);
        }
    } );

    // form handler
    $( document ).on( 'submit', '#yith-ywgc-export-import-form', function( e ){
        e.preventDefault();

        let originalFormData = new FormData( document.getElementById("yith-ywgc-export-import-form") ),
            action            = originalFormData.get( 'gift-card-export-import-radio-modal' ), // export or import
            date_option       = originalFormData.get( 'ywgc_export_option_date' ), //all or by_date
            from              = originalFormData.get( 'ywgc_export_option_date_from' ),
            to                = originalFormData.get( 'ywgc_export_option_date_to' ),
            order_id          = originalFormData.get( 'ywgc_export_option_order_id' ),
            gift_card_id      = originalFormData.get( 'ywgc_export_option_gift_card_code' ),
            gift_card_code    = originalFormData.get( 'ywgc_export_option_gift_card_code' ),
            gift_card_amount  = originalFormData.get( 'ywgc_export_option_gift_card_amount' ),
            gift_card_balance = originalFormData.get( 'ywgc_export_option_gift_card_balance' ),
            sender_name       = originalFormData.get( 'ywgc_export_option_sender_name' ),
            recipient_name    = originalFormData.get( 'ywgc_export_option_recipient_name' ),
            recipient_email   = originalFormData.get( 'ywgc_export_option_recipient_email' ),
            message           = originalFormData.get( 'ywgc_export_option_message' ),
            expiration_date    = originalFormData.get( 'ywgc_export_option_expiration_date' ),
            delivery_date     = originalFormData.get( 'ywgc_export_option_delivery_date' ),
            internal_note     = originalFormData.get( 'ywgc_export_option_internal_note' ),
            csv_delimitier    = originalFormData.get( 'ywgc_csv_delimiter' ),
            file              = originalFormData.get( 'ywgc_file_import_csv' ),
            nonce             = originalFormData.get( '_wpnonce' );

        formDataToSend   = new FormData();

        if ( action === 'export' ){
            formDataToSend.set( 'action', 'yith_ywgc_run_exporter' );
            formDataToSend.set( 'date_option', date_option );
            formDataToSend.set( 'from', from );
            formDataToSend.set( 'to', to );
            formDataToSend.set( 'order_id', order_id );
            formDataToSend.set( 'gift_card_id', gift_card_id );
            formDataToSend.set( 'gift_card_code', gift_card_code );
            formDataToSend.set( 'gift_card_amount', gift_card_amount );
            formDataToSend.set( 'gift_card_balance', gift_card_balance );
            formDataToSend.set( 'sender_name', sender_name );
            formDataToSend.set( 'recipient_name', recipient_name );
            formDataToSend.set( 'recipient_email', recipient_email );
            formDataToSend.set( 'message', message );
            formDataToSend.set( 'expiration_date', expiration_date );
            formDataToSend.set( 'delivery_date', delivery_date );
            formDataToSend.set( 'internal_note', internal_note );
            formDataToSend.set( 'csv_delimitier', csv_delimitier );
            formDataToSend.set( 'to', to );
            formDataToSend.set( 'nonce', ywgc_data.nonce );

            $.ajax( {
                type       : "POST",
                url        : ajaxurl,
                data       : formDataToSend,
                processData: false,
                contentType: false,
                cache: false,
                success    : function ( response ) {
                    var downloadLink = document.createElement("a");
                    var fileData = ['\ufeff'+response];

                    var blobObject = new Blob(fileData,{
                        type: "text/csv;charset=utf-8;"
                    });

                    var url = URL.createObjectURL(blobObject);
                    var d = new Date();
                    var date_output = d.getDate()+"-"+(d.getMonth() + 1)+"-"+d.getFullYear()+"-"+d.getHours()+"-"+d.getMinutes()+"-"+d.getSeconds();

                    downloadLink.href = url;
                    downloadLink.download = "gift-card-export_" + date_output +".csv";

                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);

                    $( '#step-configuration' ).hide();
                    $( '#ywgc-gift-card-export-import-container' ).addClass( 'completed' );
                    $( '#step-completed button.try-again' ).hide();
                },
                error: function (response) {
                    $( '#step-completed button.yith-plugin-fw__button--close' ).hide();
                    console.log("ERROR");
                    console.log(response);
                }
            } );

        } else{
            formDataToSend.set( 'action', 'yith_ywgc_run_importer' );
            formDataToSend.set( 'file_url', file );
            formDataToSend.set( 'csv_delimitier', csv_delimitier );
            formDataToSend.set( 'nonce', ywgc_data.nonce );

            if ( file ) {
                $.ajax( {
                    type       : "POST",
                    url        : ajaxurl,
                    data       : formDataToSend,
                    processData: false,
                    contentType: false,
                    cache: false,
                    success    : function ( response ) {
                        $( '#step-configuration' ).hide();
                        if ( 1 === response.code || 2 === response.code ) {
                            $( '.ywgc-import-done' ).text( response.value );
                            $( '#step-completed img' ).attr( 'src', response.image );
                            $( '#ywgc-gift-card-export-import-container' ).addClass( 'completed' );
                            if ( response.success ){
                                $( '#step-completed button.try-again' ).hide();
                            }
                        } else {
                            $( '#ywgc-gift-card-export-import-container' ).addClass( 'completed' );
                            $( '#step-completed button.try-again' ).hide();
                        }
                    },
                    error: function (response) {
                        console.log("ERROR");
                        console.log(response);
                    },
                    complete: function ( response ) {
                        $('#posts-filter').load(document.URL +  ' #posts-filter');
                    }
                } );
            }
        }

        changeStep( 4 );

    } );

    const closeModal = function(){
        $( '.yith-plugin-fw__modal__close' ).trigger( 'click' );
        location.reload();
    };

    $( document ).on( 'click', '#close-modal', closeModal );

    // Gift Card Generator Ajax
    $(document).on('click', '#yith-gift-card-generator-modal-button.multiple-generation', function (e) {
        e.preventDefault();

        var quantity = $( '#ywgc-gift-card-generator-quantity' ).val();
        var amount   = $( '#ywgc-gift-card-generator-amount' ).val();

        if ( quantity == 0 || quantity < 0  ){
            $( '#ywgc-gift-card-generator-quantity' ).css( 'border-color', 'red' );
            return;
        }

        if ( amount == 0 ){
            $( '#ywgc-gift-card-generator-amount' ).css( 'border-color', 'red' );
            return;

        }

        var block_zone = $( this ).parent().parent().parent();

        block_zone.block({message: null, overlayCSS: {background: "#f1f1f1", opacity: .7}});

        var data = {
            quantity: quantity,
            amount: amount,
            action: 'ywgc_gift_card_generator',
            nonce: ywgc_data.generate_gift_cards_nonce,
        };

        $.ajax({
            type: 'POST',
            url: ywgc_data.ajax_url,
            data: data,
            dataType: 'html',
            success: function (response) {
                setTimeout(function() {
                    block_zone.unblock();
                    $( '.yith-plugin-fw__modal__close' ).click();
                }, 1500);

            },
            error: function (response) {
                block_zone.unblock();
                console.log("ERROR");
                console.log(response);
            },
            complete: function (response) {
                location.reload();
                /*$('#posts-filter').load(document.URL +  ' #posts-filter');

                setTimeout(function() {
                    $( '#the-list tr' ).slice(0, quantity ).css( 'background-color', '#e7eccc' );
                }, 1500);

                setTimeout(function() {
                    $('#posts-filter').load(document.URL +  ' #posts-filter');
                }, 7000);*/
            }
        });

    });

    $(document).on('change', '#ywgc-gift-card-generator-quantity', function (e) {
        e.preventDefault();

        var val = $(this).val();

        if ( val == 0 || val < 0 ){
            $(this).css( 'border-color', 'red' );
        } else{
            $(this).css( 'border-color', '#cbd5e1' );
        }
    });

    $(document).on('change', '#ywgc-gift-card-generator-amount', function (e) {
        e.preventDefault();

        var val = $(this).val();
        if ( val == 0 || val < 0 ){
            $(this).css( 'border-color', 'red' );
        } else{
            $(this).css( 'border-color', '#cbd5e1' );
        }
    });

    // Email settings actions

    $( document ).on( 'click', '.toggle-settings', function( e ){
        e.preventDefault();
        $( this ).closest( '.yith-ywgc-row' ).toggleClass( 'active' );
        const target = $( this ).data( 'target' );
        $( '#'+target ).slideToggle();

    } )

    $( document ).on( 'click', '.yith-ywgc-save-settings', function( e ){
        e.preventDefault();
        $( this ).closest( 'form' ).find( '.wp-switch-editor.switch-html' ).trigger('click');
        const email_key = $( this.closest( '.email-settings' ) ).attr( 'id' );
        const data = {
            'action': 'yith_ywgc_save_email_settings',
            'params': $( this ).closest( 'form' ).serialize(),
            'email_key': email_key,
            'security': ywgc_data.save_email_settings_nonce,
        }
        $.ajax( {
            type    : "POST",
            data    : data,
            url     : ajaxurl,
            success : function ( response ) {
                const row_active = $( '.yith-ywgc-row.active' );
                row_active.find( '.email-settings' ).slideToggle();
                row_active.toggleClass( 'active' );
            },
        });
    } );

    $( document ).on( 'change', '#yith-ywgc-email-status', function(){
        const data = {
            'action'    : 'yith_ywgc_save_mail_status',
            'enabled'   : $(this).val(),
            'email_key' : $(this).closest('.yith-plugin-fw-onoff-container ').data('email_key'),
            'security': ywgc_data.save_email_status_nonce,
        }

        $.ajax( {
            type    : "POST",
            data    : data,
            url     : ajaxurl,
            success : function ( response ) {
                console.log('Email status updated');
            }
        });
    } );
});
