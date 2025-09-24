'use strict';

/* global jQuery yith_wcdp yith */

// these constants will be wrapped inside webpack closure, to prevent collisions

const $ = jQuery,
	$document = $( document ),
	$body = $( 'body' ),
	block = ( $el ) => {
		if ( 'undefined' === typeof $.fn.block ) {
			return false;
		}

		try {
			$el.block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6,
				},
			} );

			return $el;
		} catch ( e ) {
			return false;
		}
	},
	unblock = ( $el ) => {
		if ( 'undefined' === typeof $.fn.unblock ) {
			return false;
		}

		try {
			$el.unblock();
		} catch ( e ) {
			return false;
		}
	},
	labels = yith_wcdp?.labels;

export {
	$,
	$document,
	$body,
	block,
	unblock,
	labels
};
