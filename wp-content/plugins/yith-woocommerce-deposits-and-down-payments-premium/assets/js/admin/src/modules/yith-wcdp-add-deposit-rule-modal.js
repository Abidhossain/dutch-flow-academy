'use strict';

/* globals yith_wcdp, yith */

import { $, $body, labels } from '../../../src/globals';
import { initFields } from './fields';

class YITH_WCDP_Add_Deposit_Rule_Modal {
	// dom elements that open this modal.
	$openers = null;

	// $opener that triggered open.
	$target = null;

	// modal object
	modal = null;

	constructor( $openers ) {
		if ( ! $openers.length ) {
			return;
		}

		this.$openers = $openers;

		this.init();
	}

	init() {
		const self = this;

		this.$openers.on( 'click', function( ev ) {
			ev.preventDefault();

			self.$target = $( this );
			self.onOpen();
		} );
	}

	onOpen() {
		const $item = this?.$target?.closest( '[data-item]' ),
			item = $item?.data( 'item' ) || {},
			args = {
				title: item?.id ? labels.edit_rule_title : labels.add_rule_title,
				content: wp.template( 'yith-wcdp-add-deposit-rule-modal' )( item ),
				footer: false,
				showClose: true,
				width: 350,
				classes: {
					wrap: 'yith-wcdp-modal',
				},
			};

		// open modal passing item object.
		this.modal = yith.ui.modal( args );

		// init modal fields
		initFields( this.modal.elements.content );
	}
}

export default YITH_WCDP_Add_Deposit_Rule_Modal;