/**
 * External dependencies
 */
import classNames from 'classnames';
import { _x } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { withInstanceId } from '@wordpress/compose';
import { CART_STORE_KEY } from '@woocommerce/block-data';
import {
	TotalsItem,
	TotalsWrapper,
} from '@woocommerce/blocks-checkout';

const Block = ( {
	instanceId,
	className
} ) => {
	const extensionData = useSelect( ( select ) => {
			const
				store = select( CART_STORE_KEY ),
				cartData = store.getCartData(),
				extensionData = cartData?.extensions;

			return extensionData?.['yith\\deposits'];
		} ),
		{
			has_deposits: hasDeposits,
			grand_totals: grandTotals,
			expiration_note: description,
		} = extensionData ?? {};

	if ( ! hasDeposits || ! grandTotals ) {
		return;
	}

	const { registerCheckoutFilters } = window.wc.blocksCheckout,
		{ getCurrencyFromPriceResponse } = window.wc.priceFormat,
		{
			total: grandTotal,
			balance: balanceTotal,
			balance_shipping: balanceShippingTotal,
		} = grandTotals,
		currency = getCurrencyFromPriceResponse( grandTotal ),
		depositLabel = applyFilters(
			'yith_wcdp_deposit_total_label',
			_x(
				'Deposit due today',
				'[FRONTEND] Grand total block',
				'yith-woocommerce-deposits-and-down-payments'
			)
		),
		balanceLabel = applyFilters(
			'yith_wcdp_balance_total_label',
			_x(
				'Balance subtotal',
				'[FRONTEND] Grand total block',
				'yith-woocommerce-deposits-and-down-payments'
			)
		),
		balanceShippingLabel = applyFilters(
			'yith_wcdp_balance_total_label',
			_x(
				'Balance shipping',
				'[FRONTEND] Grand total block',
				'yith-woocommerce-deposits-and-down-payments'
			)
		),
		grandTotalLabel = applyFilters(
			'yith_wcdp_grand_total_label',
			_x(
				'Grand total',
				'[FRONTEND] Grand total block',
				'yith-woocommerce-deposits-and-down-payments'
			)
		);

	registerCheckoutFilters( 'yith\\deposits', {
		totalLabel: ( label ) => {
			return hasDeposits ? depositLabel : label;
		},
	} );

	return (
		<TotalsWrapper className={ classNames(
			'yith-wcdp-grand-total-block__wrapper',
			className
		) }>
			<div className="yith-wcdp-grand-total-block__items">
				<TotalsItem
					className={ classNames(
						'wc-block-components-totals-footer-item',
						'yith-wcdp-grand-total-block__items__subtotal',
						'yith-wcdp-grand-total-block-line-item'
					) }
					currency={ currency }
					label={ balanceLabel }
					value={  parseInt( balanceTotal.price, 10 ) }
				/>
				{
					! ! parseInt( balanceShippingTotal.price, 10 ) ? <TotalsItem
						className={ classNames(
							'wc-block-components-totals-footer-item',
							'yith-wcdp-grand-total-block__items__shipping',
							'yith-wcdp-grand-total-block-line-item'
						) }
						currency={ currency }
						label={ balanceShippingLabel }
						value={  parseInt( balanceShippingTotal.price, 10 ) }
					/> : null
				}
			</div>
			<TotalsItem
				className={ classNames(
					'wc-block-components-totals-footer-item',
					'yith-wcdp-grand-total-block__total'
				) }
				currency={ currency }
				label={ grandTotalLabel }
				value={  parseInt( grandTotal.price, 10 ) }
			/>
			{
				description ? <div
					className={ classNames(
						'wc-block-components-panel',
						'wc-block-components-totals-item__description',
						'yith-wcdp-grand-total-block__description'
					) }
					dangerouslySetInnerHTML={ { __html: description } }
				/> : null
			}
		</TotalsWrapper>
	);
};

export default withInstanceId( Block );
