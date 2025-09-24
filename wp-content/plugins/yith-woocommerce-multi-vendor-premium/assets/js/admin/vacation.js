( function ( $ ) {

	$(document).on('change', '.yith-plugin-fw-datepicker.yith-plugin-fw-datepicker--initialized', function() {
		let date = $(this).val();

		if ( 'vacation_schedule_to' === $(this).attr('id') ) {
			$( '#vacation_schedule_from' ).datepicker( 'option', { maxDate: date } );
		} else {
			$( '#vacation_schedule_to' ).datepicker( 'option', { minDate: date } );
		}
	});

	// short fix for multiple deps on Vacation tab
	$(document).on('change', 'input[name="vacation_enabled"]', function() {
		if ( ! $(this).is(':checked') ) {
			$('input[name="vacation_schedule_enabled"]:checked' )?.click();
		}
	})

} )( jQuery );
