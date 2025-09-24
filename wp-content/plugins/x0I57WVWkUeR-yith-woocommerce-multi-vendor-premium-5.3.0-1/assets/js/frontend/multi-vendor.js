/*
 * Multi Vendors Frontend Script
 *
 * @version 4.0.0
 */

( function ( $ ) {

	const registrationForm = {

		form: $( '#yith-vendor-registration, #yith-become-a-vendor' ),
		registerCheckbox: $( 'input#vendor-register' ),

		toggleVisibility: function() {
			let fields = this.form.find('.validate-required').find( 'input, select' );

			this.form.fadeToggle( 'slow', 'linear' );
			// Set required fields.
			if ( this.registerCheckbox.is(':checked') ) {
				fields.attr('required', true);
			} else {
				fields.attr('required', false);
			}
		},

		fieldError: function() {
			let input = $( this ),
				field = $( this ).closest( '.form-row' );

			if ( ! field.hasClass( 'validate-required' ) ) {
				return false;
			}

			if ( ! input.val() ) {
				field.addClass( 'woocommerce-invalid' ).removeClass('woocommerce-validated');
			} else {
				field.removeClass( 'woocommerce-invalid' ).addClass('woocommerce-validated');
			}
		},

		init: function() {
			this.registerCheckbox.on( 'click', this.toggleVisibility.bind(this) );
			this.form.find( '.input-text' ).on( 'blur', this.fieldError );

			this.form.find( '.state_select' ).attr( 'id', 'billing_state' );
			this.form.find( '.country_select' ).attr( 'id', 'billing_country' ).change();
		}
	};

	$( document ).ready( function() {
		registrationForm.init();
	});

} )( jQuery );