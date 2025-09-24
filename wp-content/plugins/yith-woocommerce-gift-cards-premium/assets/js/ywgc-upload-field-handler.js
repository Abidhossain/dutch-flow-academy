/* globals ywgc_field_data, wp, yith */
( function ( $ ) {

	/**
	 * File field.
	 */
	( function () {
		/**
		 * Get the wrapper of the file field.
		 * @param element JQuery element or dom element.
		 * @returns {*}
		 */
		function getWrapper( element ) {
			return $( element ).closest( '.yith-plugin-fw-file' );
		}

		function onDragOver() {
			getWrapper( this ).addClass( 'yith-plugin-fw--is-dragging' );
		}

		function onDragLeave() {
			getWrapper( this ).addClass( 'yith-plugin-fw--is-dragging' );
		}

		function onChange() {
			var wrapper = getWrapper( this ),
				name    = wrapper.find( '.yith-plugin-fw-file__preview__name' ),
				file    = this.files.length ? this.files[ 0 ] : false;

			wrapper.removeClass( 'yith-plugin-fw--is-dragging' );

			var ext = file.name.split( '.' ).pop().toLowerCase();

			if ($.inArray( ext, ['png', 'jpg', 'jpeg'] ) == -1 ) {
				$( "div.yith-plugin-fw-file__message" ).append(
					'<br><span class="ywgc-picture-error">' +
					ywgc_data.invalid_image_extension + '</span>'
				);
				return;
			}

			if ( file.size > ywgc_data.custom_image_max_size * 1024 * 1024 && ywgc_data.custom_image_max_size > 0) {
				$( "div.yith-plugin-fw-file__message" ).append(
					'<br><span class="ywgc-picture-error">' +
					ywgc_data.invalid_image_size + '</span>'
				);
				return;
			}

			if ( file ) {
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

					$( '.yith-ywgc-preview-image' ).html( html_miniature );
				};

				name.html( file.name );
				$( '.yith-ywgc-drag-drop-icon-modal' ).hide();
				wrapper.addClass( 'yith-plugin-fw--filled' );
			} else {
				wrapper.removeClass( 'yith-plugin-fw--filled' );
			}
		}

			$( document )
			.on( 'dragover', '.yith-plugin-fw-file', onDragOver )
			.on( 'dragleave', '.yith-plugin-fw-file', onDragLeave )
			.on( 'change', '.yith-plugin-fw-file__field', onChange );

	} )();


} )( jQuery );
