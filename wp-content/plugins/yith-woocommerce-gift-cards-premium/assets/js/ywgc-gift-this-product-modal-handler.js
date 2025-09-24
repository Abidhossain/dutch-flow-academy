/**
 * Handle the Gift this Product modal
 *
 */
/* global jQuery, ywgc_gift_this_product_data */
(function ($) {

	if (typeof ywgc_gift_this_product_data === "undefined") {
		return;
	}

	// Open the Gift this Product modal automatically when click the shop button
	$(function() {
		if (window.location.href.indexOf("yith-gift-this-product-form=yes") > -1) {
			setTimeout(function() {
				$( '#give-as-present' ).click();
				var url= document.location.href;
				window.history.pushState({}, "", url.split("?")[0]);
			}, 100);
		}
	});

	function animateInElem( elem, animation, callback ) {
		elem.show().addClass( 'animated ' + animation );
		elem.one( 'animationend', function() {
			elem.removeClass( 'animated ' + animation );
			if( typeof callback != 'undefined' ) {
				callback();
			}
		});
	}

	var YITHGiftThisProductModal = function( item ) {
		if( ! item.length ) {
			return;
		}

		this.self               = item;
		this.wrap               = item.find( '.yith-ywgc-gift-this-product-modal-wrapper' );
		this.popup              = item.find( '.yith-ywgc-gift-this-product-modal' );
		this.content            = item.find( '.yith-ywgc-gift-this-product-modal-content-wrapper' );
		this.overlay            = item.find( '.yith-ywgc-gift-this-product-modal-overlay' );
		this.blocked            = false;
		this.opened             = false;
		this.additional         = false;
		this.animationIn        = this.popup.attr( 'data-animation-in' );

		// position first
		this.position( null );

		// prevent propagation on popup click
		$( this.popup ).on( 'click', function(ev){
			ev.stopPropagation();
		})

		// attach event
		$( window ).on( 'resize', { obj: this }, this.position );
		// open
		$( document ).on( 'click', '#give-as-present', { obj: this, additional: false }, this.open );

		//close the popup on overlay click
		$(document).on( 'click', '.yith-ywgc-gift-this-product-modal-overlay.close-on-click', function (e) {
			e.preventDefault();
			$('.yith-ywgc-gift-this-product-modal-wrapper .yith-ywgc-gift-this-product-modal-close').click();
		});

		//close the popup on X button click
		this.popup.on( 'click', '.yith-ywgc-gift-this-product-modal-close', { obj: this }, this.close);
	};

	/** UTILS **/
	YITHGiftThisProductModal.prototype.position           = function( event ) {
		let popup    = event == null ? this.popup : event.data.obj.popup,
			window_w = $(window).width(),
			window_h = $(window).height(),
			margin   = ( ( window_w - 40 ) > ywgc_gift_this_product_data.popupWidth ) ? window_h/10 + 'px' : '0',
			width    = ( ( window_w - 40 ) > ywgc_gift_this_product_data.popupWidth ) ? ywgc_gift_this_product_data.popupWidth + 'px' : 'auto';

		popup.css({
			'margin-top'    : margin,
			'margin-bottom' : margin,
			'width'         : width,
		});
	};

	YITHGiftThisProductModal.prototype.block              = function() {
			if( ! this.blocked ) {
				this.popup.block({
					message   : null,
					overlayCSS: {
						background: '#fff url(' + ywgc_gift_this_product_data.loader + ') no-repeat center',
						opacity   : 0.5,
						cursor    : 'none'
					}
				});
				this.blocked = true;
			}
	};

	YITHGiftThisProductModal.prototype.unblock            = function() {
		if( this.blocked ) {
			this.popup.unblock();
			this.blocked = false;
		}
	};


	/** EVENT **/
	YITHGiftThisProductModal.prototype.open               = function( event ) {
		event.preventDefault();

		let object = event.data.obj;
		// if already opened, return
		if( object.opened ) {
			return;
		}

		object.opened = true;

		// add template
		object.loadTemplate( 'gift-this-product-template', {
			title: ''
		} );
		// animate
		object.self.fadeIn("slow");
		animateInElem( object.overlay, 'fadeIn' );
		animateInElem( object.popup, object.animationIn );
		// add html and body class
		$('html, body').addClass( 'yith-ywgc-gift-this-product-modal-opened' );

		object.wrap.css('position', 'fixed');
		object.overlay.css('position', 'fixed');
		object.overlay.css('z-index', '1');

		object.wrap.find( '.ywgc-choose-design-preview .ywgc-design-list li.default-image-li .ywgc-preset-image img' ).click();


		// trigger event
		$(document).trigger( 'yith_ywgc_gift_this_product_modal_opened', [ object.popup, object ] );
	};

	YITHGiftThisProductModal.prototype.loadTemplate       = function( id, data ) {
		var template            = wp.template( id );
		this.showTemplate( template( data ) );
	};

	YITHGiftThisProductModal.prototype.showTemplate       = function( section ) {
		this.content.hide().html( section ).fadeIn("slow");
		$(document).trigger( 'yith_ywgc_gift_this_product_modal_template_loaded', [ this.popup, this ] );
	};

	YITHGiftThisProductModal.prototype.close              = function( event ) {
		event.preventDefault();

		var object = event.data.obj;

		object.additional    = false;
		object.opened        = false;
		object.self.fadeOut("slow");

		// remove body class
		$('html, body').removeClass( 'yith-ywgc-gift-this-product-modal-opened' );
		// trigger event
		$(document).trigger( 'yith_ywgc_gift_this_product_modal_template_closed', [ object.popup, object ] );

		// If bookable product, reload page to take new values if necessary
		if ( $( '.type-product' ).hasClass( 'product-type-booking' ) ){
			location.reload();
		}

		// If variation, reload page - need future revision
		// if ( $( 'form.cart').hasClass( 'variations_form' ) ){
		// 	location.reload();
		// }

	};

	// START
	$( function(){
		new YITHGiftThisProductModal( $( document ).find( '#yith-ywgc-gift-this-product-modal-container' ) );
	});

})( jQuery );
