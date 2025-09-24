'use strict';

/* global jQuery */

import { $, $body, $document } from '../../../src/globals';
import initDependencies from './dependencies';
import initValidation from './validation';

const initFields = ( $container ) => {
	// init container
	if ( ! $container?.length ) {
		$container = $document;
	}

	// data-value handling
	( () => {
		const $fields = $( ':input[data-value]', $container );

		if ( ! $fields.length ) {
			return;
		}

		$fields.each( function () {
			const $field = $( this ),
				value = $field.data( 'value' );

			if (
				$field.is( 'input[type="checkbox"]' ) ||
				$field.is( 'input[type="radio"]' )
			) {
				if ( 'boolean' === typeof value ) {
					$field.prop( 'checked', value );
				} else if ( value ) {
					$field.prop( 'checked', value === $field.val() );
				} else {
					$field.prop( 'checked', false );
				}
			} else if ( $field.is( 'select' ) && Array.isArray( value ) ) {
				$field.val( value );
			} else if ( $field.is( 'select' ) && 'object' === typeof value ) {
				for ( const i in value ) {
					if ( ! $field.find( `[value="${ i }"]` )?.length ) {
						$field.append(
							$( '<option/>', {
								value: i,
								text: value[ i ],
							} )
						);
					}
				}

				$field.val( Object.keys( value ) );
			} else if ( 'boolean' === typeof value ) {
				$field.val( value ? 1 : 0 );
			} else if ( value ) {
				$field.val( String( value ) );
			}

			$field.trigger( 'change' );
		} );
	} )();

	// init dependencies
	initDependencies( $container );

	// init validation
	initValidation( $container );

	// trigger plugin-fw fields handling
	$container.trigger( 'yith_fields_init' );
};

export {
	initFields,
	initDependencies
}