<?php
/**
 * Product Multi Currency Options
 *
 * @var int|false             $loop                  Index used in variable product variations.
 * @var bool                  $custom_prices_enabled Custom prices enabled.
 * @var YITH_WCMCS_Currency[] $currencies            Currencies.
 * @var array                 $prices                Prices.
 *
 * @package YITH\GiftCards\Views
 */

global $thepostid;

$fields       = array(
	'onoff' => array(
		'type'    => 'onoff',
		'name'    => 'yith_wcmcs_prices_option',
		'id'      => 'yith_wcmcs_prices_option',
		'class'   => 'yith-wcmcs-multi-currency-enabled',
		'default' => 'no',
		'value'   => wc_bool_to_string( $custom_prices_enabled ),
	),
);
$placeholders = array(
	// translators: %s is the date format [YYYY-MM-DD].
	'from' => sprintf( __( 'From... %s', 'yith-woocommerce-gift-cards' ), 'YYYY-MM-DD' ),
	// translators: %s is the date format [YYYY-MM-DD].
	'to'   => sprintf( __( 'To... %s', 'yith-woocommerce-gift-cards' ), 'YYYY-MM-DD' ),
);

?>
<div class="yith-wcmcs-multi-currency yith-wcmcs-multi-currency-gift-cards yith-plugin-ui">
	<div class="yith-wcmcs-multi-currency-enabled">
		<span class="yith-wcmcs-multi-currency-enabled--label">
			<?php echo esc_html__( 'Set amounts in all currencies', 'yith-woocommerce-gift-cards' ); ?>
		</span>
		<?php yith_plugin_fw_get_field( $fields['onoff'], true, false ); ?>
	</div>
	<div class="yith-wcmcs-currencies-prices <?php echo $custom_prices_enabled ? '' : 'yith-wcmcs-hidden'; ?>">
		<?php foreach ( $currencies as $currency_id => $currency ) : ?>
			<?php
			$currency_options = array(
				'rate'              => $currency->get_rate(),
				'commission'        => ! ! apply_filters( 'yith_wcmcs_add_commission_in_rates_shortcode', true, $currency_id ) ? $currency->get_commission() : 0,
				'decimals'          => $currency->get_decimals(),
				'round'             => $currency->get_decimal_round(),
				'decimal_separator' => $currency->get_decimal_separator(),
			);

			?>
			<div class="yith-wcmcs-currencies-prices--row" data-currency-options="<?php echo esc_attr( wp_json_encode( $currency_options ) ); ?>" >
				<div class="yith-wcmcs-currency-gift-card-price">
					<label for="gift_card-amount"><?php echo wp_kses_post( __( 'Gift card amounts', 'yith-woocommerce-gift-cards' ) . '<br> (' . $currency_id . '-' . get_woocommerce_currency_symbol( $currency_id ) . ')' ); ?></label>
					<div class="ywgc-amounts-list">
						<?php YITH_YWGC_Backend_Premium::get_instance()->show_gift_card_amount_list_by_currency( $thepostid, $currency_id, $currency ); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
