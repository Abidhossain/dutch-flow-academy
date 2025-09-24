'use strict';

/* globals yith_wcdp, yith */

import { $, $body } from '../globals.js';

export default class YITH_WCDP_Modal {
	// modal opener
	opener = null;

	// target of the open event
	$target = null;

	// modal object
	$modal = null;

	// modal content
	$content = null;

	constructor( $opener, args ) {
		if ( ! $opener ) {
			return;
		}

		this.opener = $opener;
		this.args = $.extend(
			{
				title: false,
				shouldOpen: false,
				template: false,
				onOpen: false,
				onClose: false,
			},
			args || {}
		);

		this.init();
	}

	init() {
		$( document )
			.off( 'click', this.opener )
			.on( 'click', this.opener, (ev ) => {
			this.$target = $( ev.target );

			if ( ! this.shouldOpen() ) {
				return;
			}

			ev.preventDefault();
			this.onOpen();
		} );
	}

	// events handling

	shouldOpen() {
		if ( 'function' === typeof this.args?.shouldOpen ) {
			return this.args.shouldOpen.call( this );
		}

		return true;
	}

	onOpen() {
		let template = this.args?.template || '',
			$content = null;

		if ( 'function' === typeof template ) {
			template = template.call( this );
		}

		if ( ! this.$content?.length ) {
			if ( this.$target.data( 'modal' ) ) {
				$content = $( `#${ this.$target.data( 'modal' ) }` ).detach();
			} else if ( ! template ) {
				return;
			} else if ( 'string' === typeof template ) {
				$content = $( template ).detach();
			} else if ( 'function' === typeof template ) {
				$content = template().detach();
			} else if ( template?.lenght ) {
				$content = template.detach();
			}

			this.$content = $content;
		}

		this.maybeOpenModal( this.$content );
	}

	onClose() {
		this.maybeCloseModal();
	}

	maybeBuildModal() {
		if ( this.$modal?.length ) {
			return this.$modal;
		}

		const $modal = $( '<div/>', {
				class: 'yith-wcdp-modal',
			} ),
			$contentContainer = $( '<div/>', {
				class: 'content pretty-scrollbar',
			} ),
			$closeButton = $( '<a/>', {
				class: 'close-button main-close-button',
				html: '&times;',
				role: 'button',
				href: '#',
			} );

		this.$modal = $modal;

		$modal.append( $contentContainer ).append( $closeButton );

		if ( this.args?.title ) {
			const $title = $( '<div/>', {
				class: 'title',
				html: `<h3>${ this.args.title }</h3>`,
			} );

			$modal.prepend( $title );
		}

		$modal.on( 'click', '.close-button', ( ev ) => {
			ev.preventDefault();

			this.onClose();
		} );

		$body.append( $modal );

		return this.$modal;
	}

	maybeDestroyModal() {
		if ( ! this.$modal?.length ) {
			return;
		}

		this.$modal.remove();
	}

	maybeOpenModal( content ) {
		if ( ! this.$modal?.length ) {
			this.maybeBuildModal();
		}

		if ( this.$modal.hasClass( 'open' ) ) {
			return;
		}

		this.$modal
			.find( '.content' )
			.append( content )
			.end()
			.fadeIn( () => {
				this.$modal.addClass( 'open' );

				if ( 'function' === typeof this.args?.onOpen ) {
					this.args?.onOpen.call( this );
				}
			} );

		$body.addClass( 'yith-wcdp-open-modal' );
	}

	maybeCloseModal() {
		if ( ! this.$modal?.length ) {
			this.maybeBuildModal();
		}

		if ( ! this.$modal.hasClass( 'open' ) ) {
			return;
		}

		this.$modal.fadeOut( () => {
			this.$modal.removeClass( 'open' );
			$body.removeClass( 'yith-wcdp-open-modal' );

			if ( 'function' === typeof this.args?.onClose ) {
				this.args?.onClose.call( this );
			}
		} );
	}
}
