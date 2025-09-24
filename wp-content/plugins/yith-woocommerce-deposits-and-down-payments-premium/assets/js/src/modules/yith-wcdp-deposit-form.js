'use strict';

/* globals yith_wcdp, yith, accounting */

import { $, $document, block, unblock } from '../globals';

export default class YITH_WCDP_Deposit_Form {

	xhr = null;

	$form = null;
	$depositContainer = null;
	$depositOptions = null;
	$variationAddToCart = null;

	constructor() {
		const form = $( 'form.cart' );

		if ( ! form.length ) {
			return;
		}

		this.init();
	}

	getDepositPreferences() {
		if ( ! this.$depositOptions.length ) {
			this.initDom();
		}

		if ( ! this.$depositOptions.length ) {
			return false;
		}

		return this.$depositOptions.data();
	}

	getDepositValue( price ) {
		const depositPreferences = this.getDepositPreferences();

		if ( ! depositPreferences ) {
			return price;
		}

		let depositPrice;

		if ( 'amount' === depositPreferences.depositType && !! depositPreferences.depositAmount ) {
			depositPrice = Math.min( price, depositPreferences.depositAmount );
		} else if ( 'rate' === depositPreferences.depositType && !! depositPreferences.depositRate ) {
			depositPrice = price * parseFloat( depositPreferences.depositRate ) / 100;
			depositPrice = Math.min( price, depositPrice );
		} else {
			depositPrice = price;
		}

		return depositPrice;
	}

	formatPrice( price ) {
		return accounting.formatMoney( price, {
			symbol:    yith_wcdp.currency_format.symbol,
			decimal:   yith_wcdp.currency_format.decimal,
			thousand:  yith_wcdp.currency_format.thousand,
			precision: yith_wcdp.currency_format.precision,
			format:    yith_wcdp.currency_format.format
		} )
	}

	init() {
		this.initDom();
		this.initVariations();
		this.initActions();
	}

	initDom() {
		this.$form = $( 'form.cart' );
		this.$depositContainer = this.$form?.find( '#yith-wcdp-add-deposit-to-cart' );
		this.$depositOptions = this.$depositContainer?.find( '.yith-wcdp-single-add-to-cart-fields' );
		this.$variationAddToCart = this.$form?.find( '.woocommerce-variation-add-to-cart' );
	}

	initVariations() {
		if ( ! this.$form.length || ! this.$form.hasClass( 'variations_form' ) ) {
			return;
		}

		this.$form
			.on( 'found_variation', ( ev, variation ) => this.onFoundVariation( variation ) )
			.on( 'reset_data', () => this.removeTemplate() );
	}

	initActions() {
		// Event Tickets, Product Addons, Composite compatibilities
		$document.on(
			'yith_wcevti_price_refreshed yith_wapo_product_price_updated yith_wcp_price_updated',
			( ev, fullPrice ) => this.updateTotals( fullPrice )
		);

		// Dynamic compatibility
		$document.on(
			'ywdpd_price_html_updated',
			( ev, formattedPrice, fullPrice ) => {
				if ( ! fullPrice ) {
					return;
				}

				this.updateTotals( fullPrice );
			}
		)

		// Bundle compatibility
		$document.on(
			'yith_wcpb_ajax_update_price_request',
			( ev, response ) => {
				if ( ! response || ! response?.price ) {
					return;
				}

				this.updateTotals( response.price );
			}
		);
	}

	onFoundVariation( variation ) {
		this
			.doTemplateUpdate( variation )
			.then( () => {
				$document.trigger( 'yith_wcdp_updated_deposit_form', this.$depositOptions );
			} );
	}

	doTemplateUpdate( variation ) {
		if ( yith_wcdp.ajax_variations ) {
			return this.updateTemplateViaAjax( variation );
		}

		return new Promise( ( resolve ) => {
			if ( 'undefined' !== typeof variation.add_deposit_to_cart ){
				this.updateTemplateViaVariation( variation )
			} else if( deposit_options.length ) {
				this.updateTotals( variation.display_price );
			}

			resolve();
		} );
	}

	updateTemplateViaAjax( variation ) {
		this.xhr = $.ajax( {
			beforeSend: () => {
				if( this.xhr != null ) {
					this.xhr.abort();
				}

				this.hideTemplate();
				block( this.$form );
			},
			complete: () => unblock( this.$form ),
			data: {
				variation_id: variation?.variation_id,
				variation_attr: this.$form
					.find( '.variations select' )
					.serializeArray()
					.reduce( ( a, v ) => {
						a[ v.name ] = v.value;
						return a;
					}, {} ),
				action: yith_wcdp?.actions?.get_add_deposit?.name,
				_wpnonce: yith_wcdp?.actions?.get_add_deposit?.nonce,
			},
			dataType: 'html',
			method: 'POST',
			success: ( template ) => this.updateTemplate( template ),
			url: yith_wcdp.ajax_url
		} );

		return this.xhr;
	}

	updateTemplateViaVariation( variation ) {
		this.updateTemplate( variation.add_deposit_to_cart );
	}

	hideTemplate() {
		if( ! this.$depositContainer.length ){
			return;
		}

		this.$depositContainer.hide();
	}

	updateTemplate( newTemplate ) {
		this.removeTemplate();
		this.$variationAddToCart.before( newTemplate );
		this.initDom();
	}

	removeTemplate() {
		if( ! this.$depositContainer.length ){
			return;
		}

		this.$depositContainer.remove();
	}

	updateTotals( fullPrice ) {
		const formattedFullPrice = this.formatPrice( fullPrice ),
			depositPrice = this.getDepositValue( fullPrice ),
			formattedDepositPrice = this.formatPrice( depositPrice );

		this.$depositOptions.find( '.full-price' ).html( formattedFullPrice );
		this.$depositOptions.find( '.deposit-price' ).html( formattedDepositPrice );
	}
}
