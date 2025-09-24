jQuery(function ($) {

    $( '#yith_ywgc_transform_smart_coupons' ).on( 'click', function () {
        yith_ywgc_transform_smart_coupons();
    });

    function yith_ywgc_transform_smart_coupons( limit,offset ) {
        var ajax_zone = $('#ywgc_ajax_zone_transform_smart_coupons');

        if (typeof(offset) === 'undefined') offset = 0;
        if (typeof(limit) === 'undefined') limit = 0;

        var post_data = {
            'limit': limit,
            'offset': offset,
            action: 'yith_convert_smart_coupons_button'
        };
        if (offset == 0)
            ajax_zone.block({message: null, overlayCSS: {background: "#f1f1f1", opacity: .7}});
        $.ajax({
            type: "POST",
            data: post_data,
            url: ywgc_data.ajax_url,
            success: function (response) {
                console.log('Processing, do not cancel');
                if (response.loop == 1)
                    yith_ywgc_transform_smart_coupons(response.limit, response.offset);
                if (response.loop == 0)
                    ajax_zone.unblock();
            },
            error: function (response) {
                console.log("ERROR");
                console.log(response);
                ajax_zone.unblock();
                return false;
            }
        });
    }
    
	/**
	 * Dependencies on Cart & Checkout tab
	 * */
	$(function() {
		if ($('input#ywgc_gift_card_form_on_cart').prop('checked') || $('input#ywgc_gift_card_form_on_checkout').prop('checked')) {
			$('#ywgc_text_before_gc_form').parent().parent().parent().parent().parent().show();
		}});


		$('input#ywgc_gift_card_form_on_cart').change(function() {

			if ( ! $( this ).hasClass( 'onoffchecked') && ! $(this).prop('checked') && ! $('input#ywgc_gift_card_form_on_checkout').prop('checked') ){
				$('#ywgc_text_before_gc_form').parent().parent().parent().parent().parent().hide();
			}
			else{
				$('#ywgc_text_before_gc_form').parent().parent().parent().parent().parent().show();
			}
		});

		$('input#ywgc_gift_card_form_on_checkout').change(function() {
			if ( ! $( this ).hasClass( 'onoffchecked') && ! $(this).prop('checked') && ! $('input#ywgc_gift_card_form_on_cart').prop('checked') ){
                $('#ywgc_text_before_gc_form').parent().parent().parent().parent().parent().hide();
            }
			else{
                $('#ywgc_text_before_gc_form').parent().parent().parent().parent().parent().show();
            }
		});


  /**
   * Dependencies on Gift this product options
   * */

  $(function() {

    if ( ! $('input#ywgc_gift_this_product_include_shipping').prop('checked') ) {
      $('#ywgc_gift_this_product_include_shipping_fixed, #ywgc_gift_this_product_fixed_shipping_value').closest('.forminp').parent().addClass('ywgc-disabled-option');
    };


    if ( ! $('input#ywgc_gift_this_product_include_shipping_fixed').prop('checked') ) {
      $('#ywgc_gift_this_product_fixed_shipping_value').closest('.forminp').parent().addClass('ywgc-disabled-option');
    };

  });

  $('input#ywgc_gift_this_product_include_shipping').change(function() {

    if ( ! $( this ).hasClass( 'onoffchecked') && ! $(this).prop('checked') && ! $('input#ywgc_gift_this_product_include_shipping').prop('checked') ){
      $('#ywgc_gift_this_product_include_shipping_fixed, #ywgc_gift_this_product_fixed_shipping_value').closest('.forminp').parent().addClass('ywgc-disabled-option');
    }
    else{
      $('#ywgc_gift_this_product_include_shipping_fixed, #ywgc_gift_this_product_fixed_shipping_value').closest('.forminp').parent().removeClass('ywgc-disabled-option');
    }

  });

  $('input#ywgc_gift_this_product_include_shipping_fixed').change(function() {

    if ( ! $( this ).hasClass( 'onoffchecked') && ! $(this).prop('checked') && ! $('input#ywgc_gift_this_product_include_shipping_fixed').prop('checked') ){
      $('#ywgc_gift_this_product_fixed_shipping_value').closest('.forminp').parent().addClass('ywgc-disabled-option');
    }
    else{
      $('#ywgc_gift_this_product_fixed_shipping_value').closest('.forminp').parent().removeClass('ywgc-disabled-option');
    }

  });

});
