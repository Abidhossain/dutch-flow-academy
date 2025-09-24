'use strict';

/* global yith_wcaf */

import { $, $document } from '../../../src/globals';

class YITH_WCDP_Dependencies_Handler {
	// container
	$container;

	// fields;
	$fields;

	// dependencies tree.
	dependencies = {};

	constructor( $container ) {
		this.$container = $container;

		if ( ! this.$container?.length ) {
			return;
		}

		this.init();
	}

	init() {
		this.initFields();

		if ( ! this.$fields?.length ) {
			return false;
		}

		this.initDependencies();
		return true;
	}

	reInit() {
		this.$fields.off( 'change', this.applyDependencies );
		return this.init();
	}

	initFields() {
		this.$fields = this.$container.find( ':input' );
	}

	initDependencies() {
		this.buildDependenciesTree();

		if ( ! Object.keys( this.dependencies ).length ) {
			return;
		}

		this.handleDependencies();
	}

	buildDependenciesTree() {
		const self = this;

		this.$fields.closest( '[data-dependencies]' ).each( function () {
			const $field = $( this ),
				id = $field.attr( 'id' );

			if ( ! id ) {
				return;
			}

			let newBranch = {
				[ id ]: $field.data( 'dependencies' ),
			};

			self.dependencies = $.extend( self.dependencies, newBranch );
		} );

		// backward compatibility with plugin-fw
		this.$container.find( '[data-dep-target]' ).each( function () {
			const $container = $( this ),
				id = $container.data( 'dep-id' ),
				target = $container.data( 'dep-target' ),
				value = $container.data( 'dep-value' );

			if ( ! id || ! target || ! value ) {
				return;
			}

			let newBranch = {
				[ target ]: {
					[ id ]: value.toString().split( ',' ),
				},
			};

			self.dependencies = $.extend( self.dependencies, newBranch );
		} );
	}

	handleDependencies() {
		this.$fields.on( 'change', this.applyDependencies.bind( this ) );

		this.applyDependencies();
	}

	applyDependencies() {
		$.each( this.dependencies, ( field, conditions ) => {
			const $container = this.findFieldContainer( field ),
				show = this.checkConditions( conditions );

			if ( ! $container.length ) {
				return;
			}

			if ( show ) {
				$container?.fadeIn();
			} else {
				$container?.hide();
			}
		} );
	}

	findField( field ) {
		const $field = this.$container.find( `#${ field }` );

		if ( ! $field.length ) {
			return false;
		}

		return $field;
	}

	findFieldContainer( field ) {
		const $field = this.findField( field );

		if ( ! $field?.length ) {
			return false;
		}

		// maybe an inline-field
		let $container = $field.closest( '.option-element' );

		// maybe in a settings table
		if ( ! $container.length ) {
			$container = $field.closest( '.yith-plugin-fw__panel__option' );
		}

		// maybe inside a form
		if ( ! $container.length ) {
			$container = $field.closest( '.form-row' );
		}

		if ( ! $container.length ) {
			return false;
		}

		return $container;
	}

	checkConditions( conditions ) {
		let result = true;

		$.each( conditions, ( field, condition ) => {
			let $field = this.findField( field ),
				fieldValue;

			if ( ! result || ! $field?.length ) {
				return;
			}

			if ( $field.first().is( 'input[type="radio"]' ) ) {
				fieldValue = $field.filter( ':checked' ).val().toString();
			} else {
				fieldValue = $field?.val()?.toString();
			}

			if ( Array.isArray( condition ) ) {
				result = condition.includes( fieldValue );
			} else if ( typeof condition === 'function' ) {
				result = condition( fieldValue );
			} else if ( 0 === condition.indexOf( ':' ) ) {
				result = $field.is( condition );
			} else if ( 0 === condition.indexOf( '!:' ) ) {
				result = ! $field.is( condition.toString().substring( 1 ) );
			} else if ( 0 === condition.indexOf( '!' ) ) {
				result = condition.toString().substring( 1 ) !== fieldValue;
			} else {
				result = condition.toString() === fieldValue;
			}

			if ( typeof this.dependencies[ field ] !== 'undefined' ) {
				result =
					result &&
					this.checkConditions( this.dependencies[ field ] );
			}
		} );

		return result;
	}
}

function initDependencies( $container ) {
	// init container
	if ( ! $container?.length ) {
		$container = $document;
	}

	let handler = $container.data( 'dependencies-handler' );

	if ( handler ) {
		return handler;
	}

	handler = new YITH_WCDP_Dependencies_Handler( $container );
	$container.data( 'dependencies-handler', handler );

	return handler;
}

function reInitDependencies( $container ) {
	// init container
	if ( ! $container?.length ) {
		$container = $document;
	}

	let handler = $container.data( 'dependencies-handler' );

	if ( ! handler ) {
		handler = initDependencies( $container );
	}

	handler.reInit();

	return handler;
}

export default initDependencies;

export {
	initDependencies,
	reInitDependencies,
}
