'use strict';

/* globals yith_wcdp, yith */

import { $ } from '../globals';

export default class YITH_WCDP_Buttons_With_Submenu {
	$container = null;
	$buttons = null;
	$openers = null;

	constructor( $container ) {
		if ( ! $container.length ) {
			return;
		}

		this.$container = $container
		this.init();
	}

	init() {
		this.$buttons = this.$container.find( '.button-with-submenu' );
		this.$openers = this.$buttons.find( 'a.submenu-opener' );

		this.initOpeners();
		this.initBackdrop();
	}

	initOpeners() {
		const self = this;

		this.$openers.not( '.initialized' ).each( function() {
			const $opener = $( this );

			$opener
				.on( 'click', ( ev ) => {
					ev.stopPropagation();
					self.toggleMenu.call( self, $opener );
				} )
				.addClass( 'initialized' );
		} );
	}

	initBackdrop() {
		$( document ).on( 'click', this.closeAll.bind( this ) );
	}

	toggleMenu( $opener ) {
		const $button = $opener.parent( '.button-with-submenu' ),
			opened = $button.hasClass( 'opened' );

		if ( opened ) {
			this.close( $button );
		} else {
			this.closeAll();
			this.open( $button );
		}
	}

	open( $button ) {
		$button.addClass( 'opened' );
	}

	close( $button ) {
		$button.removeClass( 'opened' );
	}

	closeAll() {
		this.$buttons.removeClass( 'opened' );
	}
}
