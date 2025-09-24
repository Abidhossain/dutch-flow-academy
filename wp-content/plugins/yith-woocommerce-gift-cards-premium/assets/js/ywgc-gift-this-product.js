(function ($) {

	if (typeof ywgc_data === "undefined") {
		return;
	}

	var give_as_present_button = $( '#give-as-present' );

	/**
	 * Manage the Gift this Product modal events
	 * */
	$( document ).on(
		'yith_ywgc_gift_this_product_modal_template_loaded',
		function( popup, item ) {

			var product_price      = give_as_present_button.attr( 'data-price'),
				product_price_html = give_as_present_button.attr( 'data-price-html'),
				product_id         = give_as_present_button.data( 'product-id'),
				product_name       = give_as_present_button.data( 'product-name'),
				image_url          = give_as_present_button.data( 'image-url'),
				shipping_cost	   = $( '.ywgc-gift-card-shipping-total-value').data( 'shipping-cost' );

			$( "form.gift-cards_form" ).append( '<input type="hidden" class="ywgc-as-present" name="ywgc-as-present" value="yes">' );

			$( "form.gift-cards_form" ).append( '<input type="hidden" class="ywgc-gifted-product-id" name="ywgc-gifted-product-id" value="' + product_id + '">' );

			$( "form.gift-cards_form" ).append( '<input type="hidden" class="ywgc-manual-amount" name="ywgc-manual-amount" value="' + product_price + '">' );

			$( '.ywgc-form-preview-amount' ).html( product_price_html );
			$( '.ywgc-product-price' ).html( product_price_html );
			$( '.ywgc-product-title' ).html( product_name );
			$( '.ywgc-product-image' ).attr( 'src', image_url );

			// remove the layout 1 styles and use only the version 2 in the modal
			$('head').find('link#ywgc-product-layout-1-css').remove();

			if ( 'yes' === ywgc_data.shipping_in_gift_this_product ){

				var shipping_cost = ywgc_data.fixed_shipping_value;

				var shipping_cost_formatted = shipping_cost.toString().replace( '"', '' );

				reload_shipping_cost_data( shipping_cost, product_price );
			}

			// Manage the include shipping checkbox
			$( 'input#ywgc-include-shipping-checkbox' ).on('click', function() {
					if ( ywgc_data.fixed_shipping === 'no' ) {
						if ($( 'input#ywgc-include-shipping-checkbox' ).prop( 'checked' )) {
							$( 'div.ywgc-country-select-main-container ' ).removeClass( 'ywgc-hidden' );

							$( 'button.ywgc-add-gift-product-to-cart' ).css( 'margin', '8em 0 0 auto' );
						} else {
							$( 'div.ywgc-country-select-main-container ' ).addClass( 'ywgc-hidden' );
							$( 'button.ywgc-add-gift-product-to-cart' ).css( 'margin', '3em auto 0 auto' );
						}
					} else {
						if ($( 'input#ywgc-include-shipping-checkbox' ).prop( 'checked' )) {
							$( 'div.ywgc-include-fixed-shipping-container ' ).removeClass( 'ywgc-hidden' );

							$( 'button.ywgc-add-gift-product-to-cart' ).css( 'margin', '8em 0 0 auto' );
						} else {
							$( 'div.ywgc-include-fixed-shipping-container ' ).addClass( 'ywgc-hidden' );
						}
					}
			});

			// Manage the country select
			$( '#ywgc-country-select' ).on('change', function() {

					var ajax_zone = $( '.ywgc-gift-this-product-totals' );
					var country_code = $( this ).val();
					var postal_code  = $( '#ywgc-postal-code-input' ).val();
					var data = {
						country_code: country_code,
						postal_code: postal_code,
						action: 'ywgc_get_shipping_for_gift_this_product'
					};

					ajax_zone.block( { message: null, overlayCSS: { background: "#f1f1f1", opacity: .5 } } );

					$.ajax({
							type: 'POST',
							url: ywgc_data.ajax_url,
							data: data,
							dataType: 'html',
							success: function(response) {

								var formatted_price = response.replace( '"', '' );

								formatted_price = parseFloat( formatted_price, 10 ).toString();

								reload_shipping_cost_data( formatted_price, product_price );

								ajax_zone.unblock();

							},
							error: function(response) {
								console.log( "ERROR" );
								console.log( response );
								ajax_zone.unblock();
								return false;
							}
					});
				});

			function reload_shipping_cost_data( formatted_price, product_price  ){

				$( 'span.ywgc-gift-card-shipping-total-value' ).text( formatted_price.replace( '.', ywgc_data.currency_format_decimal_sep ) + ' ' + ywgc_data.currency_format_symbol );

				$( 'span.ywgc-gift-card-product-total-value' ).text( product_price + ' ' + ywgc_data.currency_format_symbol );

				var new_total = parseFloat( formatted_price ) + parseFloat( product_price );

				if( "left"== ywgc_data.currency_position ){
                    $( 'span.ywgc-gift-card-total-value' ).text( ywgc_data.currency_format_symbol + new_total.toFixed( 2 ).toString().replace( '.', ywgc_data.currency_format_decimal_sep ) );
                }else if( "right"== ywgc_data.currency_position ){
                    $( 'span.ywgc-gift-card-total-value' ).text( new_total.toFixed( 2 ).toString().replace( '.', ywgc_data.currency_format_decimal_sep ) + ywgc_data.currency_format_symbol );
                }else if("right_space"== ywgc_data.currency_position){
                    $( 'span.ywgc-gift-card-total-value' ).text( new_total.toFixed( 2 ).toString().replace( '.', ywgc_data.currency_format_decimal_sep ) + ' ' + ywgc_data.currency_format_symbol );
                }else{
                    $( 'span.ywgc-gift-card-total-value' ).text( ywgc_data.currency_format_symbol + ' ' + new_total.toFixed( 2 ).toString().replace( '.', ywgc_data.currency_format_decimal_sep ) );
                }
				//include the total value in the hidden input
				$( 'input.ywgc-gift-this-product-total-value' ).val( new_total );
			}

			$( '#ywgc-postal-code-input' ).on(
				'input',
				function() {
					$( '#ywgc-country-select' ).change();
				}
			);

			/**
			 * manage recipient and sender fields to display them automatically in the preview
			 */
			var recipient_name_input = $( '.ywgc-recipient-name input' );
			recipient_name_input.on(
				'change keyup',
				function(e) {
					e.preventDefault();
					var recipient_name = recipient_name_input.val();

					recipient_name = $.parseHTML( recipient_name.replace( /(<([^>]+)>)/gi, "" ) );

					$( '.ywgc-form-preview-to-content' ).html( recipient_name );
				}
			);

			var sender_name_input = $( '.ywgc-sender-name input' );
			sender_name_input.on(
				'change keyup',
				function(e) {
					e.preventDefault();
					var sender_name = sender_name_input.val();
					sender_name     = $.parseHTML( sender_name.replace( /(<([^>]+)>)/gi, "" ) );

					$( '.ywgc-form-preview-from-content' ).html( sender_name );
				}
			);

			var message_input = $( '.ywgc-message textarea' );
			message_input.on(
				'change keyup',
				function(e) {
					e.preventDefault();
					var message = message_input.val();
					message     = $.parseHTML( message.replace( /(<([^>]+)>)/gi, "" ).replace( /\n/g, '<br/>' ) );

					$( '.ywgc-form-preview-message' ).html( message );
				}
			);

			// Date and time picker handler
			$(function() {
				
				$('#ywgc-delivery-date').datetimepicker({
					minDate: ywgc_data.min_date,
					maxDate: ywgc_data.max_date,
					dateFormat: ywgc_data.date_format,
					timeFormat: ywgc_data.time_format,
					timezone: ywgc_data.timezone,
					showSecond: false,
					showMillisec: false,
					showMicrosec: false,
					showTimezone: false,
					hourText: ywgc_data.hour_text,
					currentText: ywgc_data.current_text,
					closeText: ywgc_data.close_text,
					controlType: 'select',
					beforeShow: function( inst, elem ) {
						$('#ui-datepicker-div').addClass( 'ywgc-date-picker');
						setTimeout(function () {
							$('#ywgc-delivery-date').datepicker("widget").find(".ui-timepicker-div").hide();
							if( $( '.ui-datepicker-calendar td' ).hasClass( 'ui-datepicker-current-day')){
								$('#ywgc-delivery-date').datepicker("widget").find(".ui-timepicker-div").show();
							}else{
								$( ywgc_data.today_selected_message_div).insertAfter( '.ui-timepicker-div');
							}
							$( '.yith-ywgc-gift-this-product-modal-wrapper' ).css( 'overflow', 'hidden' );
							inst.dpDiv.css({
								top: $(".datepicker").offset().top,
								left: $(".datepicker").offset().left
							});
						}, 1 );
					},
					onSelect: function(dateText, inst) {
						setTimeout(function () {
							if ( $( 'td.ui-datepicker-today' ).hasClass( 'ui-datepicker-current-day') ) {
								$('#ywgc-delivery-date').datepicker("widget").find(".ui-timepicker-div").hide();
								$( ywgc_data.today_selected_message_div).insertAfter( '.ui-timepicker-div');
								$( 'input#ywgc-delivery-date' ).val('');
							} else{
								$('#ywgc-delivery-date').datepicker("widget").find(".ui-timepicker-div").show();
							}
						}, 1 );
					},
					onClose: function(dateText, inst) {
						$( '.yith-ywgc-gift-this-product-modal-wrapper' ).css( 'overflow', 'auto' );
					}
				});
			});

			/**
			 * Manage the selected design images
			 */
			var wc_gallery_image             = $( '.product-type-gift-card .woocommerce-product-gallery__image a' );
			var wc_gallery_image_placeholder = $( '.product-type-gift-card .woocommerce-product-gallery__image--placeholder' );

			$( '.ywgc-preset-image.ywgc-default-product-image img' ).addClass( 'selected_design_image' );

			$( item ).on(
				'click',
				'.product-type-gift-card form.gift-cards_form.cart .ywgc-preset-image img:not(.ywgc_upload_plus_icon)',
				function(e) {
					e.preventDefault();

					var id = $( this ).closest( '.ywgc-preset-image' ).data( 'design-id' );

					$( document ).trigger( 'ywgc-picture-changed', ['template', id] );

					$( 'a.lightbox-added' ).remove();

					if ($( '.product-type-gift-card .woocommerce-product-gallery__wrapper' ).children().length != 0) {
						$( '.product-type-gift-card .woocommerce-product-gallery__image' ).remove();

						var image_url = $( this ).closest( '.ywgc-preset-image' ).data( 'design-url' );
						var srcset    = $( this ).attr( 'srcset' );
						var src       = $( this ).attr( 'src' );

						if ($( this ).hasClass( 'custom-selected-image' ) || $( this ).hasClass( 'custom-modal-selected-image' )) {
							image_url = src;
						}

						if (wc_gallery_image_placeholder.length != 0) {
							wc_gallery_image_placeholder.remove();
						}

						$( '<div data-thumb="' + src + '" data-thumb-alt class="woocommerce-product-gallery__image"><a href="' + image_url + '"><img src="' + image_url + '" class="wp-post-image size-full" alt="" data-caption="" data-src="' + image_url + '" data-large_image="' + image_url + '" data-large_image_width="1024" data-large_image_height="1024" sizes="(max-width: 600px) 100vw, 600px"' + srcset + ' width="600" height="600"></a></div>' ).insertBefore( '.ywgc-main-form-preview-container' );
					}
				}
			);

			$( item ).on(
				'click',
				'.ywgc-preset-image img:not(.ywgc_upload_plus_icon)',
				function(e) {
					e.preventDefault();

					var id = $( this ).closest( '.ywgc-preset-image' ).data( 'design-id' );

					$( '.ywgc-preset-image img' ).removeClass( 'selected_design_image' );
					$( '.ywgc-preset-image' ).removeClass( 'selected_image_parent' );

					$( this ).addClass( 'selected_design_image' );
					$( this ).closest( '.ywgc-preset-image' ).addClass( 'selected_image_parent' );

					$( document ).trigger( 'ywgc-picture-changed', ['template', id] );
				});

			/**
			 * Show the custom file choosed by the user as the image used on the gift card editor on product page
			 * */
			$( '#ywgc-upload-picture' ).on(
				'change',
				function() {

					$( '.ywgc-preset-image img' ).removeClass( 'selected_design_image' );

					var preview_image = function(file) {
						var oFReader = new FileReader();
						oFReader.readAsDataURL( file );

						oFReader.onload = function(oFREvent) {

							var image_base64 = oFREvent.target.result;

							var html_miniature = '<img src="' + image_base64 + '" class="attachment-thumbnail size-thumbnail  custom-selected-image selected_design_image" ' +
								'alt="" ' +
								'srcset="' + image_base64 + ' 150w, ' +
								'' + image_base64 + ' 250w, ' +
								'' + image_base64 + ' 100w" ' +
								'sizes="(max-width: 150px) 85vw, 150px" width="150" height="150">';

							var html_content = '<img src="' + image_base64 + '" class="wp-post-image size-full" alt="" width="600" height="600">';

							//Here we add the upload image in the design list and select it
							if ($( '.ywgc-design-list li.default-image-li .ywgc-preset-image ' ).hasClass( 'ywgc-default-product-image' )) {
								$( '.ywgc-design-list li.default-image-li .ywgc-preset-image' ).html( html_miniature );
								wc_gallery_image.html( html_content );
							} else {
								$( '.ywgc-design-list li.default-image-li .ywgc-preset-image' ).html( html_miniature );
							}

							$( '.ywgc-design-list .ywgc-preset-image img.custom-selected-image' ).parent().attr( 'data-design-url', image_base64 );
							$( '.ywgc-design-list .ywgc-preset-image img.custom-selected-image' ).parent().attr( 'data-design-id', 'custom' );

							$( '.custom-selected-image ' ).click();

							$ ( '.yith-plugin-fw-file.yith-ywgc-upload-file-field' ).removeClass( 'yith-plugin-fw--is-dragging' );

							$( document ).trigger( 'ywgc-picture-changed', ['custom', 'custom'] );
						}
					};

					//  Manage the image errors and remove previous errors shown
					$( ".ywgc-picture-error" ).remove();

					var ext = $( this ).val().split( '.' ).pop().toLowerCase();

					if ($.inArray( ext, ['png', 'jpg', 'jpeg'] ) == -1) {
						$( "div.gift-card-content-editor.step-appearance" ).append(
							'<span class="ywgc-picture-error">' +
							ywgc_data.invalid_image_extension + '</span>'
						);
						return;
					}

					if ($( this )[0].files[0].size > ywgc_data.custom_image_max_size * 1024 * 1024 && ywgc_data.custom_image_max_size > 0) {
						$( "div.gift-card-content-editor.step-appearance" ).append(
							'<span class="ywgc-picture-error">' +
							ywgc_data.invalid_image_size + '</span>'
						);
						return;
					}

					preview_image( $( this )[0].files[0] );
				}
			);
		});

})( jQuery );
