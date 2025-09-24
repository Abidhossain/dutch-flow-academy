'use strict';

/* globals yith_wcdp, yith */

import { $ } from '../../src/globals';
import { initFields } from './modules/fields';
import { reInitDependencies } from './modules/dependencies';
import YITH_WCDP_Add_Deposit_Rule_Modal from './modules/yith-wcdp-add-deposit-rule-modal';

class YITH_WCDP_Admin {
	constructor() {
		this.maybeInitDepositModal();
		this.maybeInitSettings();
		this.maybeInitVariationPanel();
	}

	maybeInitDepositModal() {
		const $container = $( '#yith_wcdp_panel_settings-rules' );

		if ( ! $container.length ) {
			return;
		}

		this.initDepositModal( $container );
	}

	maybeInitSettings() {
		const $containers = $( '#yith_wcdp_panel_settings-deposits' )
			.add( '#yith_wcdp_panel_balances' )
			.add( '#yith_wcdp_panel_customizations' )
			.add( '#yith_wcdp_deposit_tab' );

		if ( ! $containers.length ) {
			return;
		}

		this.initSettings( $containers );
	}

	maybeInitVariationPanel() {
		const $container = $( '#woocommerce-product-data' );

		if ( ! $container.length ) {
			return;
		}

		$container.on( 'woocommerce_variations_loaded', () => {
			this.initSettings( $container );
		} );

		$container.on( 'woocommerce_variations_loaded woocommerce_variations_added woocommerce_variations_removed', () => {
			reInitDependencies( $container );
		} );
	}

	initDepositModal( $container ) {
		const self = this,
			$addButtons = $( '.yith-wcdp-add-rule-button', $container ),
			$editButtons = $( '.edit-deposit-rule', $container );

		if ( ! $addButtons.length ) {
			return;
		}

		new YITH_WCDP_Add_Deposit_Rule_Modal(
			$addButtons.add( $editButtons )
		);
	}

	initSettings( $container ) {
		initFields( $container );
	}
}

jQuery( () => {
	new YITH_WCDP_Admin();
} );