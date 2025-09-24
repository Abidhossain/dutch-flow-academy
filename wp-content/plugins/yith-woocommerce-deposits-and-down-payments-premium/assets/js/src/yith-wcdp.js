'use strict';

/* globals yith_wcdp, yith, accounting */

import { $, $document, block, unblock } from './globals';
import YITH_WCDP_Deposit_Form from './modules/yith-wcdp-deposit-form';
import YITH_WCDP_Modal from './modules/yith-wcdp-modal';
import YITH_WCDP_Buttons_With_Submenu from './modules/yith-wcdp-buttons-with-submenu';

jQuery( () => {
	// add deposit to cart from.
	new YITH_WCDP_Deposit_Form();

	// deposits details form.
	const $depositsDetails = $( '#yith_wcdp_deposits_details' )

	if ( $depositsDetails.length ) {
		new YITH_WCDP_Buttons_With_Submenu( $depositsDetails );
	}

	// balances details modal
	const initBalanceDetailsModal = () => {
		new YITH_WCDP_Modal( '.deposit-expiration-modal-opener', {
			title: yith_wcdp.labels.deposit_expiration_modal_title
		} );
	};

	$document.on( 'updated_checkout', initBalanceDetailsModal );
	initBalanceDetailsModal();
} );