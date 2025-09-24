<?php
/**
 * Add deposit to cart (single product)
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $product         WC_Product
 * @var $deposit_enabled bool
 * @var $deposit_forced  bool
 * @var $default_deposit bool
 * @var $deposit_type    string
 * @var $deposit_amount  float
 * @var $deposit_rate    float
 * @var $deposit_value   float
 * @var $classes         string
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly
?>

<div id="yith-wcdp-add-deposit-to-cart" class="yith-wcdp <?php echo esc_attr( $classes ); ?>">

	<?php do_action( 'yith_wcdp_before_add_deposit_to_cart', $product ); ?>

	<div class="yith-wcdp-single-add-to-cart-fields" data-deposit-type="<?php echo esc_attr( $deposit_type ); ?>" data-deposit-amount="<?php echo esc_attr( $deposit_amount ); ?>" data-deposit-rate="<?php echo esc_attr( $deposit_rate ); ?>">
		<?php if ( ! $deposit_forced ) : ?>
			<label class="full">
				<input type="radio" name="payment_type" value="full" <?php checked( ! $default_deposit ); ?> />
				<span class="label">
					<?php echo esc_html( YITH_WCDP_Labels::get_pay_full_amount_label() ); ?>
					<span class="price-label full-price">
						<?php echo wp_kses_post( $product->get_price_html() ); ?>
					</span>
				</span>
			</label>
			<label class="deposit">
				<input type="radio" name="payment_type" value="deposit" <?php checked( $default_deposit ); ?> />
				<span class="label">
					<?php echo esc_html( YITH_WCDP_Labels::get_pay_deposit_label() ); ?>
					<span class="price-label deposit-price">
						<?php echo wp_kses_post( apply_filters( 'yith_wcdp_single_deposit_price', wc_price( $deposit_value ), $deposit_value ) ); ?>
					</span>
					<?php do_action( 'yith_wcdp_after_deposit_price_label', $product ); ?>
				</span>
			</label>
		<?php else : ?>
			<p class="yith-wcdp-deposit-mandatory">
				<span class="label">
					<?php echo wp_kses_post( apply_filters( 'yith_wcdp_deposit_only_message', __( 'Pay a deposit', 'yith-woocommerce-deposits-and-down-payments' ), $deposit_value ) ); ?>
					<span class="price-label deposit-price">
						<?php echo wp_kses_post( apply_filters( 'yith_wcdp_single_deposit_price', wc_price( $deposit_value ), $deposit_value ) ); ?>
					</span>
					<?php do_action( 'yith_wcdp_after_deposit_price_label', $product ); ?>
				</span>
			</p>
		<?php endif; ?>
	</div>

	<?php do_action( 'yith_wcdp_after_add_deposit_to_cart', $product ); ?>

</div>
