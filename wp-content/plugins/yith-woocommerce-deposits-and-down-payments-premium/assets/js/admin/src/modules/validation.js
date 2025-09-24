'use strict';

/* global yith_wcdp */

import { $, $document, labels } from '../../../src/globals';

class YITH_WCDP_Validation_Handler {
	// container
	$container;

	// error class to add/remove to fields wrapper
	ERROR_CLASS = 'woocommerce-invalid';

	constructor( $container ) {
		this.$container = $container;

		if ( ! this.$container?.length ) {
			return;
		}

		this.initValidation();
	}

	// init validation.

	initValidation() {
		this.initForm();
		this.initFields();
	}

	initForm() {
		const $forms = this.$container.is( 'form' )
			? this.$container
			: this.$container.find( 'form' );

		if ( ! $forms.length ) {
			return;
		}

		const self = this;

		$forms.on( 'submit yith_wcaf_validate_fields', function ( ev ) {
			const $form = $( this ),
				res = self.validateForm( $form );

			if ( ! res ) {
				ev.stopImmediatePropagation();

				return false;
			}

			return true;
		} );
	}

	initFields() {
		const $fields = this.getFields( this.$container );

		if ( ! $fields.length ) {
			return;
		}

		const self = this;

		$fields.on( 'keyup change', function () {
			const $field = $( this );

			self.validateField( $field );
		} );
	}

	// fields handling.

	getFieldWrapper( $field ) {
		return $field.closest( '.form-row, .yith-plugin-fw-panel-wc-row' );
	}

	getFields( $container ) {
		const $fields = $( 'input, select, textarea', $container );

		return $fields
			.not( 'input[type="submit"]' )
			.not( 'input[type="hidden"]' )
			.not( '.select2-search__field' );
	}

	getVisibleFields( $container ) {
		const $fields = this.getFields( $container );

		return $fields.filter( ( index, field ) => {
			const $field = $( field ),
				$fieldWrapper = this.getFieldWrapper( $field );

			return $fieldWrapper.is( ':visible' );
		} );
	}

	isFieldValid( $field ) {
		const $wrapper = this.getFieldWrapper( $field ),
			fieldType = $field.attr( 'type' ),
			value = $field.val(),
			alwaysRequiredFields = [
				'reg_username',
				'reg_email',
				'reg_password',
			];

		// check for required fields
		if (
			$field.prop( 'required' ) ||
			$wrapper.hasClass( 'required' ) ||
			$wrapper.hasClass( 'validate-required' ) ||
			$wrapper.hasClass( 'yith-plugin-fw--required' ) ||
			alwaysRequiredFields.includes( $field.get( 0 ).id )
		) {
			if ( 'checkbox' === fieldType && ! $field.is( ':checked' ) ) {
				throw 'missing';
			} else if ( ! value || ! value?.length ) {
				throw 'missing';
			}
		}

		// check for patterns
		const pattern = $wrapper.data( 'pattern' );

		if ( pattern ) {
			const regex = new RegExp( pattern );

			if ( ! regex.test( value ) ) {
				throw 'malformed';
			}
		}

		// check for min length
		const minLength = $wrapper.data( 'min_length' );

		if ( minLength && value.length < minLength ) {
			throw 'short';
		}

		// check for max length
		const maxLength = $wrapper.data( 'max_length' );

		if ( maxLength && value.length > maxLength ) {
			throw 'long';
		}

		// check for number
		if ( 'number' === fieldType ) {
			const min = parseFloat( $field.attr( 'min' ) ),
				max = parseFloat( $field.attr( 'max' ) ),
				numVal = parseFloat( value );

			if ( ( min && min > numVal ) || ( max && max < numVal ) ) {
				throw 'overflow';
			}
		}

		// all validation passed; we can return true.
		return true;
	}

	validateField( $field ) {
		try {
			this.isFieldValid( $field );
		} catch ( e ) {
			this.reportError( $field, e );

			return false;
		}

		this.removeError( $field );

		return true;
	}

	validateForm( $form ) {
		const $visibleFields = this.getVisibleFields( $form );

		if ( ! $visibleFields.length ) {
			return true;
		}

		const self = this;
		let valid = true;

		$visibleFields.each( function () {
			const $field = $( this );

			if ( ! self.validateField( $field ) ) {
				valid = false;
			}
		} );

		if ( ! valid ) {
			// scroll top.
			this.scrollToFirstError( $form );

			// stop form submitting.
			return false;
		}

		return true;
	}

	// error handling.

	getErrorMsg( $field, errorType ) {
		// check if we have a field-specific error message.
		let msg = $field.data( 'error' );

		if ( msg ) {
			return msg;
		}

		// check if message is added to wrapper.
		const $wrapper = this.getFieldWrapper( $field );

		msg = $wrapper.data( 'error' );

		if ( msg ) {
			return msg;
		}

		// check if message is added to label.
		const $label = $wrapper.find( 'label' );

		msg = $label.data( 'error' );

		if ( msg ) {
			return msg;
		}

		if ( ! labels?.errors ) {
			return false;
		}

		switch ( errorType ) {
			case 'missing':
				const fieldType = $field.attr( 'type' );

				msg =
					'checkbox' === fieldType
						? labels.errors?.accept_check
						: labels.errors?.compile_field;

				if ( msg ) {
					return msg;
				}

			// fallthrough if we didn't find a proper message yet.
			default:
				msg = labels.errors?.[ errorType ]
					? labels.errors?.[ errorType ]
					: labels.errors?.general_error;
				break;
		}

		return msg;
	}

	reportError( $field, errorType ) {
		const $wrapper = this.getFieldWrapper( $field ),
			errorMsg = this.getErrorMsg( $field, errorType );

		$wrapper.addClass( this.ERROR_CLASS );

		if ( ! errorMsg ) {
			return;
		}

		// remove existing errors.
		$wrapper.find( '.error-msg' ).remove();

		// generate and append new error message.
		const $errorMsg = $( '<span/>', {
			class: 'error-msg',
			text: errorMsg,
		} );

		$wrapper.append( $errorMsg );
	}

	removeError( $field ) {
		const $wrapper = this.getFieldWrapper( $field ),
			$errorMsg = $wrapper.find( '.error-msg' );

		$wrapper.removeClass( this.ERROR_CLASS );
		$errorMsg.remove();
	}

	scrollToFirstError( $form ) {
		const $firstError = $form.find( `.${ this.ERROR_CLASS }` ).first();
		let $target = this.findScrollableParent( $form );

		if ( ! $target || ! $target.length ) {
			$target = $( 'html, body' );
		}

		const scrollDiff = $firstError.offset().top - $target.offset().top;
		let scrollValue = scrollDiff;

		if ( ! $target.is( 'html, body' ) ) {
			scrollValue = $target.get( 0 ).scrollTop + scrollDiff;
		}

		$target.animate( {
			scrollTop: scrollValue,
		} );
	}

	findScrollableParent( $node ) {
		let node = $node.get( 0 );

		if ( ! node ) {
			return null;
		}

		let overflowY, isScrollable;

		do {
			if ( document === node ) {
				return null;
			}

			overflowY = window.getComputedStyle( node ).overflowY;
			isScrollable = overflowY !== 'visible' && overflowY !== 'hidden';
		} while (
			! ( isScrollable && node.scrollHeight > node.clientHeight ) &&
			( node = node.parentNode )
		);

		return $( node );
	}
}

export default function initValidation( $container ) {
	// init container
	if ( ! $container?.length ) {
		$container = $document;
	}

	return new YITH_WCDP_Validation_Handler( $container );
}
