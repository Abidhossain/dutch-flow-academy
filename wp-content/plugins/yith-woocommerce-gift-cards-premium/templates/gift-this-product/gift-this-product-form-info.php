<?php
/**
 * Gift this product form template
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fixed_shipping_cost = get_option( 'ywgc_gift_this_product_fixed_shipping_value', '0' );

if ( 'yes' === get_option( 'ywgc_gift_this_product_include_shipping', 'no' ) ) : ?>
	<?php
	/**
	 * APPLY_FILTERS: ywgc_include_shipping_label
	 *
	 * Filter the include shipping label on the "Gift this product" section.
	 *
	 * @param string the label text
	 *
	 * @return string
	 */
	?>

	<div class="ywgc-include-shipping-container">
		<input type="checkbox" id="ywgc-include-shipping-checkbox" name="ywgc-include-shipping-checkbox">
		<label for="ywgc-include-shipping-checkbox"><?php echo esc_html( apply_filters( 'ywgc_include_shipping_label', __( 'Pay also the shipping cost for this item', 'yith-woocommerce-gift-cards' ) ) ); ?></label>
	</div>
	<div class="ywgc-country-select-main-container ywgc-hidden">
		<div class="ywgc-country-select-container">
			<div class="ywgc-country-select-title">
				<p><?php echo esc_html__( 'Ship to:', 'yith-woocommerce-gift-cards' ); ?></p>
			</div>
			<div class="ywgc-country-select-div">
				<p class="form-row form-row-wide" id="ywgc-country-select_field">
					<select name="ywgc-country-select" id="ywgc-country-select" class="country_to_state country_select" rel="ywgc-country-select">
						<option value="default"><?php esc_html_e( 'Select a country / region&hellip;', 'woocommerce' ); ?></option>

						<?php
						foreach ( WC()->countries->get_shipping_countries() as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '"' . selected( WC()->customer->get_shipping_country(), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
						}
						?>
					</select>
				</p>
			</div>
			<div class="ywgc-postal-code-select">
				<input type="text" id="ywgc-postal-code-input" placeholder="<?php echo esc_html__( 'Postal code', 'yith-woocommerce-gift-cards' ); ?>">
			</div>
		</div>
		<div class="ywgc-gift-this-product-totals clearfix"  data-currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>"  data-decimal-separator="<?php echo esc_attr( wc_get_price_decimal_separator() ); ?>" >
			<p class="ywgc-gift-card-product-total"><?php echo esc_html__( 'Product value: ', 'yith-woocommerce-gift-cards' ); ?>
				<span class="ywgc-gift-card-product-total-value"></span>
			</p>

			<?php
			$frontend_class        = YITH_YWGC_Frontend_Premium::get_instance();
			$default_shipping_cost = ywgc_string_to_float( $frontend_class->ywgc_get_shipping_cost_by_country( WC()->customer->get_shipping_country() ) );
			?>

			<p class="ywgc-gift-card-shipping-total"><?php echo esc_html__( 'Shipping cost: ', 'yith-woocommerce-gift-cards' ); ?>
				<span class="ywgc-gift-card-shipping-total-value" data-shipping-cost="<?php echo esc_attr( $default_shipping_cost ); ?>"><?php echo wp_kses_post( wc_price( $default_shipping_cost ) ); ?></span>
			</p>
			<p class="ywgc-gift-card-total"><?php echo esc_html__( 'Gift Card total: ', 'yith-woocommerce-gift-cards' ); ?>
				<span class="ywgc-gift-card-total-value"></span>
			</p>
			<input type="hidden" name="ywgc-gift-this-product-total-value" class="ywgc-gift-this-product-total-value" value="">
		</div>
	</div>

	<?php if ( 'yes' === get_option( 'ywgc_gift_this_product_include_shipping_fixed', 'no' ) ) : ?>
		<div class="ywgc-include-fixed-shipping-container ywgc-hidden">
			<p class="ywgc-gift-card-fixed-shipping-total"><?php echo esc_html__( 'Shipping cost: ', 'yith-woocommerce-gift-cards' ); ?>
				<span class="ywgc-gift-card-fixed-shipping-total-value" data-shipping-cost="<?php echo esc_attr( $fixed_shipping_cost ); ?>"><?php echo wp_kses_post( wc_price( $fixed_shipping_cost ) ); ?></span>
			</p>
			<p class="ywgc-gift-card-total"><?php echo esc_html__( 'Gift Card total: ', 'yith-woocommerce-gift-cards' ); ?>
				<span class="ywgc-gift-card-total-value"></span>
			</p>
			<input type="hidden" name="ywgc-gift-this-product-total-value" class="ywgc-gift-this-product-total-value" value="">
		</div>
	<?php endif; ?>
<?php endif; ?>
