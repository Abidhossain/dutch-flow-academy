/* global woocommerce_admin_meta_boxes_coupon */
jQuery(function( $ ) {

	/**
	 * Coupon actions
	 */
	var yith_wcgc_meta_boxes_gift_card_actions = {

		/**
		 * Initialize variations actions
		 */
		init: function() {
            this.insert_generate_code_button();
			$( '.button.generate-gift-card-code' ).on( 'click', this.generate_gift_card_code );
		},

        /**
         * Insert generate code buttom HTML.
         */
		insert_generate_code_button: function() {
			$( '.post-type-gift_card' ).find( '#title' ).after(
				'<a href="#" class="button generate-gift-card-code">' + yith_wcgc_admin_meta_boxes_gift_card.generate_button_text + '</a>'
			);
        },

		/**
		 * Generate a random coupon code
		 */
		generate_gift_card_code: function( e ) {
			e.preventDefault();
			var $gift_card_code_field = $( '#title' ),
				$gift_code_label = $( '#title-prompt-text' ),
			    $result = '';
			$.ajax({
				type   : 'POST',
				url    : ajaxurl,
				data   : {
					"action" : "generate_gift_card_code",
				},
				beforeSend: function(){
					$gift_card_code_field.attr( 'disabled', true );
				},
				success: function (response) {
					if( response.success === true ){
						$gift_card_code_field.trigger( 'focus' ).val( response.data ).attr( 'disabled', false );
						$gift_code_label.addClass( 'screen-reader-text' );
					}
				}
			});
		}
	};

	yith_wcgc_meta_boxes_gift_card_actions.init();
	if ( typeof adminpage !== 'undefined' && ['post-php', 'post-new-php'].indexOf( adminpage ) >= 0 ) {
		var postTypeSaving = {
			dom                      : {
				actions  : $( '#yith-wcgc-post-type__actions' ),
				save     : $( '#yith-wcgc-post-type__save' ),
				floatSave: $( '#yith-wcgc-post-type__float-save' )
			},
			init                     : function () {
				var self = postTypeSaving;
				if ( self.dom.save.length ) {
					self.dom.save.on( 'click', self.onSaveClick );
					self.dom.floatSave.on( 'click', self.onFloatSaveClick );
					document.addEventListener( 'scroll', self.handleFloatSaveVisibility, { passive: true } );
					$( window ).on( 'resize', self.handleFloatSaveVisibility );
					self.handleFloatSaveVisibility();
				}
			},
			isInViewport             : function ( el ) {
				var rect     = el.get( 0 ).getBoundingClientRect(),
					viewport = {
						width : window.innerWidth || document.documentElement.clientWidth,
						height: window.innerHeight || document.documentElement.clientHeight
					};
				return (
					rect.top >= 0 &&
					rect.left >= 0 &&
					rect.top <= viewport.height &&
					rect.left <= viewport.width
				);
			},
			handleFloatSaveVisibility: function () {
				if ( postTypeSaving.isInViewport( postTypeSaving.dom.save ) ) {
					postTypeSaving.dom.floatSave.removeClass( 'visible' );
				} else {
					postTypeSaving.dom.floatSave.addClass( 'visible' );
				}
			},
			onSaveClick              : function () {
				$( window ).off( 'beforeunload.edit-post' );
				$( this ).block(
					{
						message   : null,
						overlayCSS: {
							background: 'transparent',
							opacity   : 0.6
						}
					}
				);
			},
			onFloatSaveClick         : function () {
				postTypeSaving.dom.save.trigger( 'click' );
			}
		};
		postTypeSaving.init();
	}

	/**
	 * Dont save the post without a gift card code
	 */
	var post_title_code = $( '.yith-plugin-ui--gift_card-post_type input#title' );

	post_title_code.prop('required',true);

	post_title_code.on("invalid", function(event) {
		$( this ).css( 'border-color', 'red' );
	});

	post_title_code.on("change", function(event) {
		$( this ).css( 'border-color', 'unset' );
	});


});
