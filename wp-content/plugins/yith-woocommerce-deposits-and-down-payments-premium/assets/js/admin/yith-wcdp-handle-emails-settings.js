jQuery(function($) {

    $( document ).ready(function() {
        $( '#yith-wcdp-table-emails .yith-plugin-fw-textarea-editor-field-wrapper iframe' ).css( 'min-height', '250px' );
    });

    $( document ).on( 'click', '.toggle-settings', function( e ){
        e.preventDefault();
        $( this ).closest( '.yith-wcdp-row' ).toggleClass( 'active' );
        const target = $( this ).data( 'target' );
        $( '#'+target ).slideToggle();

    } )

    $( document ).on( 'click', '.yith-wcdp-save-settings', function( e ){
        e.preventDefault();
        $( this ).closest( 'form' ).find( '.wp-switch-editor.switch-html' ).trigger('click');
        const email_key = $( this.closest( '.email-settings' ) ).attr( 'id' );
        const data = {
            'action': 'yith_wcdp_save_email_settings',
            'params': $( this ).closest( 'form' ).serialize(),
            'email_key': email_key,
            'security': wcdp_data.save_email_settings_nonce,
        }
        $.ajax( {
            type    : "POST",
            data    : data,
            url     : ajaxurl,
            success : function ( response ) {
                const row_active = $( '.yith-wcdp-row.active' );
                row_active.find( '.email-settings' ).slideToggle();
                row_active.toggleClass( 'active' );
            },
        });
    } );

    $( document ).on( 'change', '#yith-wcdp-email-status', function(){
        const data = {
            'action'    : 'yith_wcdp_save_mail_status',
            'enabled'   : $(this).val(),
            'email_key' : $(this).closest('.yith-plugin-fw-onoff-container ').data('email_key'),
            'security': wcdp_data.save_email_status_nonce,
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
