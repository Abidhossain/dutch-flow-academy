/* global jQuery, ywgc_data */
(function ($) {

	if (typeof ywgc_data === "undefined") {
		return;
	}

	$( document ).on(
		'click',
		'.ywgc-choose-image.ywgc-choose-template',
		function(e) {
			e.preventDefault();
			$( '#yith-ywgc .yith-ywgc-popup-close' ).show();
		}
	);

	//Manage the picture changed event
	$( document ).on(
		'ywgc-picture-changed',
		function(event, type, id) {

			$( '.ywgc-template-design' ).remove();
			$( '.ywgc-design-type' ).remove();

			if (id == 'custom') {
				$( '.ywgc-custom-upload-image-li' ).show();
				type = 'custom';
			}

			if (id == 'custom-modal') {
				type = 'custom-modal';
			}

			$( 'form.cart' ).append( '<input type="hidden" class="ywgc-design-type" name="ywgc-design-type" value="' + type + '">' );

			$( 'form.cart' ).append( '<input type="hidden" class="ywgc-template-design" name="ywgc-template-design" value="' + id + '">' );
		}
	);

	$(function() {

		if( ywgc_data.is_product ){
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
				hour: parseInt( ywgc_data.default_hour ),
				minute: parseInt( ywgc_data.default_minutes ),
				beforeShow: function( inst, elem ) {
					$('#ui-datepicker-div').addClass( 'ywgc-date-picker');
					setTimeout(function () {
						$('#ywgc-delivery-date').datepicker("widget").find(".ui-timepicker-div").hide();
						if( $( '.ui-datepicker-calendar td' ).hasClass( 'ui-datepicker-current-day')){
							$('#ywgc-delivery-date').datepicker("widget").find(".ui-timepicker-div").show();
						}else{
							$( ywgc_data.today_selected_message_div).insertAfter( '.ui-timepicker-div');
						}
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
				}
			});

			$( '.ywgc-choose-design-preview .ywgc-design-list li.default-image-li .ywgc-preset-image img' ).click();

			show_hide_add_to_cart_button();
		}

	});


	/**
	 * Manage the selected design images
	 */
	var wc_gallery_image             = $( '.product-type-gift-card .woocommerce-product-gallery__image a' );
	var wc_gallery_image_placeholder = $( '.product-type-gift-card .woocommerce-product-gallery__image--placeholder' );

	$( '.ywgc-preset-image.ywgc-default-product-image img' ).addClass( 'selected_design_image' );

	$( document ).on(
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

	$( document ).on(
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
	 * Manage the modal
	 * */
	$( document ).on(
		'yith_ywgc_popup_template_loaded',
		function(popup, item) {

			/**
			 * Manage the category selected on the modal
			 * */
			$( item ).on(
				'click',
				'a.ywgc-show-category',
				function(e) {

					var current_category = $( this ).data( "category-id" );

					//  highlight the selected category
					$( 'a.ywgc-custom-design-menu-title-link' ).removeClass( 'ywgc-category-selected' );
					$( 'a.ywgc-show-category' ).removeClass( 'ywgc-category-selected' );
					$( this ).addClass( 'ywgc-category-selected' );

					//  Show only the design of the selected category
					if ('all' !== current_category) {
						$( '.ywgc-design-item' ).hide();
						$( '.ywgc-design-item.' + current_category ).fadeIn( "fast" );
					} else {
						$( '.ywgc-design-item' ).fadeIn( "fast" );

					}
					return false;
				}
			);

			$( item ).on(
				'click',
				'a.ywgc-custom-design-menu-title-link',
				function(e) {
					$( 'a.ywgc-show-category' ).removeClass( 'ywgc-category-selected' );
					$( this ).addClass( 'ywgc-category-selected' );
				}
			);

			/**
			 * manage the selected image in the modal
			 * */
			$( item ).on(
				'click',
				'.ywgc-preset-image img',
				function(e) {
					e.preventDefault();

					$( '.ywgc-preset-image img' ).removeClass( 'selected_design_image' );
					$( this ).addClass( 'selected_design_image' );

					if ($( this ).hasClass( 'selected_design_image' )) {

						var image_url  = $( this ).attr( 'src' ),
							design_id  = $( this ).parent().data( 'design-id' ),
							design_url = $( this ).parent().data( 'design-url' );

						var html_content = '<a href="' + image_url + '"><img src="' + image_url + '" class="wp-post-image size-thumbnail" alt="" data-caption="" data-src="' + image_url + '" data-large_image="' + image_url + '" data-large_image_width="1024" data-large_image_height="1024" sizes="(max-width: 600px) 100vw, 600px" width="600" height="600"></a>';

						var html_miniature = '<img src="' + image_url + '" class="attachment-shop_thumbnail size-shop_thumbnail selected_design_image selected_design_image_in_modal" ' +
							'alt="" sizes="(max-width: 150px) 100vw, 150px" width="150" height="150">';

						if ($( '.ywgc-design-list li.default-image-li .ywgc-preset-image ' ).hasClass( 'ywgc-default-product-image' )) {
							wc_gallery_image.html( html_content );
							$( '.ywgc-design-list li.default-image-li .ywgc-preset-image' ).html( html_miniature );
							$( '.ywgc-design-list li.default-image-li .ywgc-preset-image' ).data( 'design-id', design_id );
							$( '.ywgc-design-list li.default-image-li .ywgc-preset-image' ).data( 'design-url', design_url );
						} else {
							$( '.ywgc-design-list li.default-image-li .ywgc-preset-image' ).html( html_miniature );
							$( '.ywgc-design-list li.default-image-li .ywgc-preset-image' ).data( 'design-id', design_id );
							$( '.ywgc-design-list li.default-image-li .ywgc-preset-image' ).data( 'design-url', design_url );
						}
					}

					$( '.yith-ywgc-popup-wrapper .yith-ywgc-popup-close' ).click();

				}
			);

			/**
			 * manage the custom upload in the modal
			 * */
			$( item ).on(
				'click',
				'.ywgc-upload-section-modal',
				function(e) {
					e.preventDefault();
					$( '.ywgc-design-list-modal' ).hide();
					$( '.ywgc-custom-upload-container-modal' ).show();
				}
			);

			$( item ).on(
				'click',
				'.ywgc-show-category',
				function(e) {
					e.preventDefault();
					$( '.ywgc-custom-upload-container-modal' ).hide();
					$( '.ywgc-design-list-modal' ).show();
				}
			);

			/**
			 * Show the custom file choosed by the user as the image used on the gift card editor on product page
			 * */
			$( '#ywgc-upload-picture-modal' ).on(
				'change',
				function() {

					$( '.ywgc-preset-image img' ).removeClass( 'selected_design_image' );

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
				});


			$( item ).on( 'click', '#accept-image', selectImage );
			$( item ).on( 'click', '#decline-image', cancelImage );

		});

	var selectImage = function() {

		var image_base64 = $( '.yith-ywgc-preview-image .custom-selected-image' ).attr('src');

		if ( image_base64 ) {

			var html_miniature = '<img src="' + image_base64 + '" class="attachment-thumbnail size-thumbnail  custom-selected-image selected_design_image" ' +
				'alt="" ' +
				'srcset="' + image_base64 + ' 150w, ' +
				'' + image_base64 + ' 250w, ' +
				'' + image_base64 + ' 100w" ' +
				'sizes="(max-width: 150px) 85vw, 150px" width="150" height="150">';

			var html_content = '<img src="' + image_base64 + '" class="wp-post-image size-full" alt="" width="600" height="600">';

			//Here we add the upload image in the design list and select it
			if ($('.ywgc-design-list li.default-image-li .ywgc-preset-image ').hasClass('ywgc-default-product-image')) {
				$('.ywgc-design-list li.default-image-li .ywgc-preset-image').html(html_miniature);
				wc_gallery_image.html(html_content);
			} else {
				$('.ywgc-design-list li.default-image-li .ywgc-preset-image').html(html_miniature);
			}

			$('.ywgc-design-list .ywgc-preset-image img.custom-selected-image').parent().attr('data-design-url', image_base64);
			$('.ywgc-design-list .ywgc-preset-image img.custom-selected-image').parent().attr('data-design-id', 'custom');

			$('.custom-selected-image').click();

			$('.yith-ywgc-popup-wrapper .yith-ywgc-popup-close').click();

			$('form.cart').append('<input type="hidden" class="ywgc-custom-modal-design" name="ywgc-custom-modal-design" value="' + image_base64 + '">');

			$(document).trigger('ywgc-picture-changed', ['custom', 'custom-modal']);
		}
	};

	var cancelImage = function() {
		$( '.yith-ywgc-drag-drop-icon-modal' ).show();
		$( '.yith-plugin-fw-file' ).removeClass( 'yith-plugin-fw--filled' );
	};

	$( document ).on(
		'yith_ywgc_popup_closed',
		function(popup, item) {
			$( '.ywgc-design-list .ywgc-preset-image img.selected_design_image_in_modal' ).click();
		}
	);

	if ( 'v1' === ywgc_data.v2_layout ) {
		/**
		 * Show the custom file choosed by the user as the image used on the gift card editor on product page
		 * */
		$( document ).on(
			'click',
			'.ywgc-custom-picture',
			function(e) {
				e.preventDefault();
				$( '#ywgc-upload-picture' ).click();
			}
		);
	}

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

	/**
	 * Display the gift card form cart/checkout
	 * */
	$( document ).on( 'click', 'a.ywgc-show-giftcard', show_gift_card_form );

	function show_gift_card_form() {
		$( '.ywgc_enter_code' ).slideToggle(
			300,
			function() {
				if ( ! $( '.yith_wc_gift_card_blank_brightness' ).length) {

					$( '.ywgc_enter_code' ).find( ':input:eq( 0 )' ).focus();

					$( ".ywgc_enter_code" ).keyup(
						function(event) {
							if (event.keyCode === 13) {
								$( "button.ywgc_apply_gift_card_button" ).click();
							}
						}
					);
				}

			}
		);
		return false;
	}

	function update_gift_card_amount(amount) {
		//copy the button value to the preview price
		$( '.ywgc-form-preview-amount' ).text( amount );

	}

	function show_gift_card_editor(val) {
		$( 'button.gift_card_add_to_cart_button' ).attr( 'disabled', ! val );
	}

	/** This code manage the amount buttons actions */
	function show_hide_add_to_cart_button() {
		var gift_this_product            = $( '#give-as-present' );
		var amount_buttons               = $( 'button.ywgc-amount-buttons' );
		var amount_buttons_hidden_inputs = $( 'input.ywgc-amount-buttons' );
		var manual_amount_element        = $( 'input.ywgc-manual-amount' );
		var first_amount_button          = $( 'button.ywgc-amount-buttons:first' );
		var manual_amount_container      = $( '.ywgc-manual-amount-container' );

		if (amount_buttons.length === 0) {
			show_gift_card_editor( false );
		}

		if ( amount_buttons.length === 1 && manual_amount_element.length === 0 ){
			amount_buttons.hide();
		}

		if ( manual_amount_element.length == '1' && typeof manual_amount_element.val() !== 'undefined' && manual_amount_element.val().length === 0 || amount_buttons.length === 1  ) {
			//Auto-select the 1st amount button
			first_amount_button.addClass( 'selected_button' );
			if (first_amount_button.hasClass( 'selected_button' )) {
				$( 'input.ywgc-amount-buttons:first' ).attr( 'name', 'gift_amounts' );
			}
			//copy the 1st button value to the preview price
			$( '.ywgc-form-preview-amount' ).text( first_amount_button.data( 'wc-price' ) );
		} else if ( 0 === manual_amount_container.length ) {
			//Auto-select the 1st amount button
			first_amount_button.addClass( 'selected_button' );
			if (first_amount_button.hasClass( 'selected_button' )) {
				$( 'input.ywgc-amount-buttons:first' ).attr( 'name', 'gift_amounts' );
			}
			//copy the 1st button value to the preview price
			$( '.ywgc-form-preview-amount' ).text( first_amount_button.data( 'wc-price' ) );
		}

		// select a button
		amount_buttons.on(
			'click',
			function(e) {
				e.preventDefault();

				amount_buttons.removeClass( 'selected_button' );
				amount_buttons_hidden_inputs.removeClass( 'selected_button' );
				amount_buttons_hidden_inputs.removeAttr( 'name' );
				manual_amount_element.removeClass( 'selected_button' );
				$( this ).addClass( 'selected_button' );
				$( this ).next().addClass( 'selected_button' );
				$( '.ywgc-manual-amount-error' ).removeClass( 'selected_button' );

				$( document ).trigger( 'ywgc-amount-changed', [$( this )] );
			}
		);

		manual_amount_element.on(
			'focusout',
			function(e) {

				if ( manual_amount_element.val().length === 0 ) {
					first_amount_button.click();
				}

			}
		);

		/**
		 * Manage the manual amount selection
		 * */
		manual_amount_element.on(
			'click change keyup',
			function(e) {
				e.preventDefault();

				$( '.ywgc-manual-amount-error' ).show();

				if ( $( this ).hasClass( 'ywgc-manual-amount') ){
					$( '.ywgc-predefined-amount-button' ).removeClass( 'selected_button' );
				} else{
					amount_buttons.removeClass( 'selected_button' );
					$( '.ywgc-manual-currency-symbol' ).removeClass( 'selected_button' );
				}

				amount_buttons_hidden_inputs.removeClass( 'selected_button' );
				amount_buttons_hidden_inputs.removeAttr( 'name' );

				//copy the button value to the preview price
				$( '.ywgc-form-preview-amount' ).text( $( this ).data( 'wc-price' ) );
				$( '.summary .price' ).text( $( this ).data( 'wc-price' ) );

				/* the user should enter a manual value as gift card amount */
				if (manual_amount_element.length) {

					manual_amount_container.removeClass( 'ywgc-red-border' );
					manual_amount_container.addClass( 'ywgc-normal-border' );

					var manual_amount = manual_amount_element.val();
					var test_amount   = new RegExp( '^[1-9]\\d*(?:' + '\\' + ywgc_data.currency_format_decimal_sep + '\\d{1,2})?$', 'g' );


					if (manual_amount.length && ! test_amount.test( manual_amount )) {

						$( '.ywgc-manual-amount-error' ).remove();

						manual_amount_container.after( '<div class="ywgc-manual-amount-error">' + ywgc_data.manual_amount_wrong_format + '</div>' );
						manual_amount_container.addClass( 'ywgc-red-border' );
						manual_amount_container.removeClass( 'ywgc-normal-border' );

						amount = accounting.formatMoney(
							"",
							{
								symbol: ywgc_data.currency_format_symbol,
								decimal: ywgc_data.currency_format_decimal_sep,
								thousand: ywgc_data.currency_format_thousand_sep,
								precision: ywgc_data.currency_format_num_decimals,
								format: ywgc_data.currency_format
							}
						);
						update_gift_card_amount( amount );
						show_gift_card_editor( false );
					} else if (parseInt( manual_amount ) < parseInt( ywgc_data.manual_minimal_amount ) && (ywgc_data.manual_minimal_amount_error.length > 0)) {

						$( '.ywgc-manual-amount-error' ).remove();

						manual_amount_container.after( '<div class="ywgc-manual-amount-error">' + ywgc_data.manual_minimal_amount_error + '</div>' );
						manual_amount_container.addClass( 'ywgc-red-border' );
						manual_amount_container.removeClass( 'ywgc-normal-border' );
						amount = accounting.formatMoney(
							manual_amount,
							{
								symbol: ywgc_data.currency_format_symbol,
								decimal: ywgc_data.currency_format_decimal_sep,
								thousand: ywgc_data.currency_format_thousand_sep,
								precision: ywgc_data.currency_format_num_decimals,
								format: ywgc_data.currency_format
							}
						);
						update_gift_card_amount( amount );
						show_gift_card_editor( false );
					} else if (parseInt( manual_amount ) > parseInt( ywgc_data.manual_maximum_amount ) && (ywgc_data.manual_maximum_amount_error.length > 0) && parseInt( ywgc_data.manual_maximum_amount ) != 0) {

						$( '.ywgc-manual-amount-error' ).remove();

						manual_amount_container.after( '<div class="ywgc-manual-amount-error">' + ywgc_data.manual_maximum_amount_error + '</div>' );
						manual_amount_container.addClass( 'ywgc-red-border' );
						manual_amount_container.removeClass( 'ywgc-normal-border' );
						amount = accounting.formatMoney(
							manual_amount,
							{
								symbol: ywgc_data.currency_format_symbol,
								decimal: ywgc_data.currency_format_decimal_sep,
								thousand: ywgc_data.currency_format_thousand_sep,
								precision: ywgc_data.currency_format_num_decimals,
								format: ywgc_data.currency_format
							}
						);
						update_gift_card_amount( amount );
						show_gift_card_editor( false );
					} else {

						/** If the user entered a valid amount, show "add to cart" button*/
						if (manual_amount) {

							$( '.ywgc-manual-amount-error' ).remove();

							// manual amount is a valid numeric value
							show_gift_card_editor( true );

							amount = accounting.unformat( manual_amount, ywgc_data.mon_decimal_point );

							if (amount <= 0) {
								show_gift_card_editor( false );
							} else {
								amount = accounting.formatMoney(
									amount,
									{
										symbol: ywgc_data.currency_format_symbol,
										decimal: ywgc_data.currency_format_decimal_sep,
										thousand: ywgc_data.currency_format_thousand_sep,
										precision: ywgc_data.currency_format_num_decimals,
										format: ywgc_data.currency_format
									}
								);

								update_gift_card_amount( amount ); //esto es para mostrarlo en la template del frontend

								show_gift_card_editor( true );
							}
						} else {
							amount = accounting.formatMoney(
								"",
								{
									symbol: ywgc_data.currency_format_symbol,
									decimal: ywgc_data.currency_format_decimal_sep,
									thousand: ywgc_data.currency_format_thousand_sep,
									precision: ywgc_data.currency_format_num_decimals,
									format: ywgc_data.currency_format
								}
							);

							update_gift_card_amount( amount );
							show_gift_card_editor( false );
						}
					}
				}

			}
		);

		var amount = first_amount_button.data( 'wc-price' );

		//Manage the amount button selection
		amount_buttons.on(
			'click',
			function(e) {
				e.preventDefault();

				amount_buttons_hidden_inputs.removeAttr( 'name' );

				if ( ! gift_this_product.length) {

					if ($( 'input.selected_button' ).data( 'price' ) < 0) {
						show_gift_card_editor( false );
					} else {
						show_gift_card_editor( true );
						amount = $( 'input.selected_button' ).data( 'wc-price' );
						$( 'input.selected_button' ).attr( 'name', 'gift_amounts' );
					}
					update_gift_card_amount( amount );
				}

				if ( manual_amount_container.hasClass('selected_button')) {
					$( '.ywgc-manual-amount-error' ).show();
					show_gift_card_editor( false );
				} else {
					$( '.ywgc-manual-amount-error' ).hide();
					show_gift_card_editor( true );
				}

			}
		);

		if ( $( '#yith-wapo-container' ).length ) {
			show_gift_card_editor( true );
		}

	}

	$( document ).on(
		'input',
		'.gift-cards-list input.ywgc-manual-amount',
		function(e) {
			show_hide_add_to_cart_button();
		}
	);

	if ( ywgc_data.v2_layout ){
		$( document ).on(
			'focus',
			'.gift-cards-list input.ywgc-manual-amount',
			function( e ){
				if ( 'left' === ywgc_data.currency_position || 'left_space' === ywgc_data.currency_position ) {
					$( '.ywgc-manual-currency-symbol.left' ).removeClass( 'ywgc-hidden' );
				} else{
					$( '.ywgc-manual-currency-symbol.right' ).removeClass( 'ywgc-hidden' );
				}
			});

		$( document ).on(
			'focusout',
			'.gift-cards-list input.ywgc-manual-amount',
			function( e ){
				if ( ! $(this).val() ){
					if ( 'left' === ywgc_data.currency_position || 'left_space' === ywgc_data.currency_position ) {
						$( '.ywgc-manual-currency-symbol.left' ).addClass( 'ywgc-hidden' );
					} else{
						$( '.ywgc-manual-currency-symbol.right' ).addClass( 'ywgc-hidden' );
					}
				}
			});
	}

	$( document ).on(
		'input',
		'#ywgc-edit-message',
		function(e) {
			$( ".ywgc-card-message" ).html( $( '#ywgc-edit-message' ).val() );
		}
	);

	$( document ).on(
		'change',
		'.gift-cards-list select',
		function(e) {
			show_hide_add_to_cart_button();
		}
	);

	//Disable the enter key in the manual amount input
	$( '#ywgc-manual-amount' ).keypress(function(event) {
		if (event.keyCode == 13) {
			event.preventDefault();
		}
	});

	$( '.ywgc-single-recipient input[name="ywgc-recipient-email[]"]' ).each(
		function(i, obj) {
			$( this ).on(
				'input',
				function() {
					$( this ).closest( '.ywgc-single-recipient' ).find( '.ywgc-bad-email-format' ).remove();
				}
			);
		}
	);

	function validateEmail(email) {
		var test_email = new RegExp( '^[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}$', 'i' );
		return test_email.test( email );
	}

	$( document ).on(
		'submit',
		'.gift-cards_form',
		function(e) {
			var can_submit = true;
			$( '.ywgc-single-recipient input[name="ywgc-recipient-email[]"]' ).each(
				function(i, obj) {

					if ($( this ).val() && ! validateEmail( $( this ).val() )) {
						$( this ).closest( '.ywgc-single-recipient' ).find( '.ywgc-bad-email-format' ).remove();
						$( this ).after( '<span class="ywgc-bad-email-format">' + ywgc_data.email_bad_format + '</span>' );
						can_submit = false;
					}
				}
			);
			if ( ! can_submit) {
				e.preventDefault();
			}
		}
	);
	/** Manage the WooCommerce 2.6 changes in the cart template
	 * with AJAX
	 * @since 1.4.0
	 */

	$( document ).on(
		'click',
		'a.ywgc-remove-gift-card ',
		remove_gift_card_code
	);

	function remove_gift_card_code(evt) {
		evt.preventDefault();
		var $table         = $( evt.currentTarget ).parents( 'table' );
		var gift_card_code = $( evt.currentTarget ).data( 'gift-card-code' );

		block( $table );

		var data = {
			security: ywgc_data.gift_card_nonce,
			code: gift_card_code,
			action: 'ywgc_remove_gift_card_code'
		};

		$.ajax(
			{
				type: 'POST',
				url: ywgc_data.ajax_url,
				data: data,
				dataType: 'html',
				success: function(response) {
					show_notice( response );
					$( document.body ).trigger( 'removed_gift_card' );
					unblock( $table );
				},
				complete: function() {
					update_cart_totals();
				}
			}
		);
	}

	/**
	 * Apply the gift card code the same way WooCommerce do for Coupon code
	 *
	 * @param {JQuery Object} $form The cart form.
	 */
	$( document ).on(
		'click',
		'button.ywgc_apply_gift_card_button',
		function(e) {
			e.preventDefault();
			var parent = $( this ).closest( 'div.ywgc_enter_code' );
			block( parent );

			var $text_field    = parent.find( 'input[ name="gift_card_code" ]' );
			var gift_card_code = $text_field.val();

			var data = {
				security: ywgc_data.gift_card_nonce,
				code: gift_card_code,
				action: 'ywgc_apply_gift_card_code'
			};

			$.ajax(
				{
					type: 'POST',
					url: ywgc_data.ajax_url,
					data: data,
					dataType: 'html',
					success: function(response) {
						show_notice( response );
						$( document.body ).trigger( 'applied_gift_card' );
					},
					complete: function() {

						unblock( parent );
						$text_field.val( '' );

						update_cart_totals();
					}
				}
			);
		}
	);

	/**
	 * Block a node visually for processing.
	 *
	 * @param {JQuery Object} $node
	 */
	var block = function($node) {
		$node.addClass( 'processing' ).block(
			{
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			}
		);
	};

	/**
	 * Unblock a node after processing is complete.
	 *
	 * @param {JQuery Object} $node
	 */
	var unblock = function($node) {
		$node.removeClass( 'processing' ).unblock();
	};

	/**
	 * Gets a url for a given AJAX endpoint.
	 *
	 * @param {String} endpoint The AJAX Endpoint
	 * @return {String} The URL to use for the request
	 */
	var get_url = function(endpoint) {
		return ywgc_data.wc_ajax_url.toString().replace(
			'%%endpoint%%',
			endpoint
		);
	};

	/**
	 * Clear previous notices and shows new one above form.
	 *
	 * @param {Object} The Notice HTML Element in string or object form.
	 */
	var show_notice = function(html_element) {
		$( '.woocommerce-error, .woocommerce-message' ).remove();
		$( ywgc_data.notice_target ).after( html_element );
		if ($( '.ywgc_have_code' ).length) {
			$( '.ywgc_enter_code' ).slideUp( '300' );
		}
	};

	/**
	 * Update the cart after something has changed.
	 */
	function update_cart_totals() {
		block( $( 'div.cart_totals' ) );

		$.ajax(
			{
				url: get_url( 'get_cart_totals' ),
				dataType: 'html',
				success: function(response) {
					$( 'div.cart_totals' ).replaceWith( response );
				}
			}
		);

		$( document.body ).trigger( 'update_checkout' );
	}

	/**
	 * Integration with YITH Quick View and some third party themes
	 */
	$( document ).on(
		'qv_loader_stop yit_quick_view_loaded flatsome_quickview',
		function() {

			show_hide_add_to_cart_button();
			hide_on_gift_as_present();
		}
	);

	var hide_on_gift_as_present = function() {
		if ($( 'input[name="ywgc-as-present-enabled"]' ).length) {
			$( '.ywgc-generator' ).hide();
			show_gift_card_editor( false );
		}
	}

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

	/**
	 * Manage the add/remove recipients
	 */
	function add_recipient(cnt) {

		var recipients_number = cnt + 2;

		var quantity_input = $( "div.gift_card_template_button input[name='quantity']" );

		var note = ywgc_data.multiple_recipient.replace( '%number_gift_cards%', recipients_number );

		var last     = $( 'div.ywgc-single-recipient' ).last();
		var required = ywgc_data.mandatory_email ? 'required' : '';
		var new_div  = '<div class="ywgc-additional-recipient">' +
			'<label for="ywgc-recipient-name' + cnt + '">' + ywgc_data.label_name + '</label>' +
			'<input type="text" id="ywgc-recipient-name' + cnt + '" name="ywgc-recipient-name[]" class="yith_wc_gift_card_input_recipient_details" placeholder="' + ywgc_data.name + '" ' + required + '/>' +
			'<br><label for="ywgc-recipient-email' + cnt + '">' + ywgc_data.label_email + '</label>' +
			'<input type="email" id="ywgc-recipient-email' + cnt + '" name="ywgc-recipient-email[]" class="ywgc-recipient yith_wc_gift_card_input_recipient_details" placeholder="' + ywgc_data.email + '" ' + required + '/>' +
			'<a href="#" class="ywgc-remove-recipient"> ' +
			'</div>';

		var new_div_v2  = '<div class="ywgc-additional-recipient"><div class="ywgc-recipient-name ywgc-label-above-input clearfix"><label for="ywgc-recipient-name' + cnt + '">' + ywgc_data.label_name + '</label><input type="text" id="ywgc-recipient-name' + cnt + '" name="ywgc-recipient-name[]" class="yith_wc_gift_card_input_recipient_details" ' + required + '/></div><div class="ywgc-recipient-email ywgc-label-above-input clearfix"><a href="#" class="ywgc-remove-recipient"></a><label for="ywgc-recipient-email' + cnt + '">' + ywgc_data.label_email + '</label><input type="email" id="ywgc-recipient-email' + cnt + '" name="ywgc-recipient-email[]" class="ywgc-recipient yith_wc_gift_card_input_recipient_details"' + required + '/></div></div>';

		if ( ywgc_data.v2_layout ){
			last.after( new_div_v2 );
		} else{
			last.after( new_div );
		}

		quantity_input.addClass( 'ywgc-remove-number-input' );
		quantity_input.attr( "onkeydown", "return false" );
		quantity_input.css( "background-color", "lightgray" );
		quantity_input.val( recipients_number );

		//  show a message for quantity disabled when multi recipients is entered
		$( ".ywgc-multi-recipients span" ).remove();
		$( "div.gift_card_template_button div.quantity" ).before( "<div class='ywgc-multi-recipients'><span>" + note + "</span></div>" );

	}

	function remove_recipient(element, cnt) {

		var quantity_input = $( "div.gift_card_template_button input[name='quantity']" );

		//update the quantity input
		quantity_input.val( cnt );

		var note = ywgc_data.multiple_recipient.replace( '%number_gift_cards%', cnt );

		//update the note message
		$( ".ywgc-multi-recipients span" ).remove();
		$( "div.gift_card_template_button div.quantity" ).before( "<div class='ywgc-multi-recipients'><span>" + note + "</span></div>" );

		//  remove the element
		$( element ).parent().parent( "div.ywgc-additional-recipient" ).remove();

		//  Avoid the deletion of all recipient
		var emails = $( 'input[name="ywgc-recipient-email[]"]' );
		if (emails.length == 1) {
			//  only one recipient is entered...
			$( "a.hide-if-alone" ).css( 'visibility', 'hidden' );
			$( "div.ywgc-multi-recipients" ).remove();
			quantity_input.removeClass( 'ywgc-remove-number-input' );
			quantity_input.removeAttr( "onkeydown" );
			quantity_input.css( "background-color", "" );
		}

	}

	$( document ).on(
		'click',
		'a.add-recipient',
		function(e) {
			e.preventDefault();

			var cnt = $( '.ywgc-additional-recipient' ).length;

			var proteo_qty_arrows = $( '.product-qty-arrows' );

			if (proteo_qty_arrows.length) {
				proteo_qty_arrows.hide();
			}

			add_recipient( cnt );
		}
	);

	$( document ).on(
		'click',
		'a.ywgc-remove-recipient',
		function(e) {
			e.preventDefault();

			var cnt = $( '.ywgc-additional-recipient' ).length;

			var proteo_qty_arrows = $( '.product-qty-arrows' );

			if (proteo_qty_arrows.length && cnt === '0') {
				proteo_qty_arrows.show();
			}

			remove_recipient( $( this ), cnt );
		}
	);

	function set_giftcard_value( price, price_html, product_id, product_name, image_url ) {
		var give_as_present = $( "#give-as-present" );

		if ( '' !== price ){
			give_as_present.attr( 'data-price', price );
		}
		if ( '' !== price_html ){
			give_as_present.attr( 'data-price-html', price_html );
		}
		if ( '' !== product_id ){
			give_as_present.attr( 'data-product-id', product_id );
		}
		if ( '' !== product_name ){
			give_as_present.attr( 'data-product-name', product_name );
		}
		if ( '' !== image_url ){
			give_as_present.attr( 'data-image-url', image_url );
		}

	}

	$( '.variations_form.cart' ).on(
		'found_variation',
		function(ev, variation) {
			if (typeof variation !== "undefined") {
				$( '#give-as-present' ).prop( 'disabled', false );
				set_giftcard_value( variation.display_price, variation.price_html , variation.variation_id, variation.name, variation.image.gallery_thumbnail_src );
			}
		}
	);

	$( document ).on(
		'reset_data',
		function() {
			$( '#give-as-present' ).prop( 'disabled', true );
			set_giftcard_value( '', '', '', '' );
		}
	);

	// Integration with YITH Booking
	( function () {
		var bookingForm = $( '.yith-wcbk-booking-form' );
		if ( bookingForm.length ) {
			// Enable the Gift this Product when the Booking form is filled
			bookingForm.on( 'yith_wcbk_booking_form_add_to_cart_enabled_status_updated', function ( event, enabled ) {
				$( '#give-as-present' ).prop( 'disabled', !enabled );
				if ( enabled ){
					$( '#gift-this-product' ).css('opacity', '1' );
				} else{
					$( '#gift-this-product' ).css('opacity', '0.5' );
				}
			} );
			// Update the Gift this Product with the Booking price
			bookingForm.on( 'yith_wcbk_form_update_response', function ( event, data ) {
				set_giftcard_value( data.raw_price, data.price , '', '', '' );
			} );
		}
	} )();

	// Integration with YITH Bundles
	if ($( '.yith-wcpb-product-bundled-items' ).length) {
		setTimeout(
			function() {
				$( '#give-as-present' ).prop( 'disabled', false );
			},
			1000
		);
	}

	$( document ).on(
		'yith_wcpb_ajax_update_price_request',
		function ( ev, response ) {

			if (!response || !(response !== null && response !== void 0 && response.price)) {
				return;
			}

			$( '#give-as-present' ).data( 'price', response.price );
			$( '#give-as-present' ).data( 'price-html', response.price_html );
		}
	);

	$(function() {
		var is_submitted = $( '#new-card-form-submitted' ).val();

		if ( is_submitted ) {
			location.reload();
		}
	});

	// Integration with Product Addons
	$( document ).on(
		'wapo-after-calculate-product-price',
		function(event, response) {

			let totalOrderPriceRaw  = response['order_price_raw'],
				totalOrderPriceHTML = response['order_price_suffix'],
				give_as_present     = $( "#give-as-present" );

			$( '.product-type-gift-card .ywgc-form-preview-amount' ).html( totalOrderPriceHTML );
			$( '.product-type-gift-card .summary .price' ).html( totalOrderPriceHTML );

			give_as_present.attr( 'data-price', totalOrderPriceRaw );
			give_as_present.attr( 'data-price-html', totalOrderPriceHTML );

		});

})( jQuery );
