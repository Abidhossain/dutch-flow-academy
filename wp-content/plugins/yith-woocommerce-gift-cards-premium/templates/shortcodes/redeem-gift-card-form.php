<?php
/**
 * Template to render the form to redeem a gift card
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

$code_text_input   = array(
	'type'  => 'text',
	'id'    => 'ywgc-gift-card-code',
	'name'  => 'ywgc-gift-card-code',
	'class' => 'ywgc-shortcode-text-input',
);
$amount_text_input = array(
	'type'  => 'text',
	'id'    => 'ywgc-used-amount',
	'name'  => 'ywgc-used-amount',
	'class' => 'ywgc-shortcode-text-input',
);

/**
 * APPLY_FILTERS: ywgc_redeem_shortcode_access_condition
 *
 * Filter the condition to access to the redeem shortcode.
 *
 * @param bool true to give access, false to not. Default: false
 *
 * @return bool
 */
if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) || apply_filters( 'ywgc_redeem_shortcode_access_condition', false ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown ?>
	<form method="post" class="form-redeem-gift-card ywgc-shortcode-form" name="form-redeem-gift-card">
		<p class="ywgc-shortcode-form-title"><?php echo esc_html_x( 'Redeem a gift card', 'shortcode title', 'yith-woocommerce-gift-cards' ); ?></p>
		<span class="ywgc-shortcode-form-label"><?php esc_html_e( 'Gift Card code', 'yith-woocommerce-gift-cards' ); ?></span>
		<?php yith_plugin_fw_get_field( $code_text_input, true ); ?>
		<span class="ywgc-shortcode-form-label"><?php esc_html_e( 'Used amount', 'yith-woocommerce-gift-cards' ); ?></span>
		<?php yith_plugin_fw_get_field( $amount_text_input, true ); ?>
		<button class="yith-plugin-fw__button--primary ywgc-shortcode-submit-button" type="submit"><?php esc_html_e( 'Submit', 'yith-woocommerce-gift-cards' ); ?></button>
		<span class="ywgc-shortcode-message">
			<?php
			
			if ( ! empty( $_POST['ywgc-gift-card-code'] ) && ! empty( $_POST['ywgc-used-amount'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$code        = sanitize_text_field( wp_unslash( $_POST['ywgc-gift-card-code'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$used_amount = sanitize_text_field( wp_unslash( $_POST['ywgc-used-amount'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$args        = array(
					'gift_card_number' => $code,
				);
				$gift_card   = new YWGC_Gift_Card_Premium( $args );

				if ( ! is_object( $gift_card ) || ! $gift_card->exists() ) {
					echo '<div style="color: red; margin-top: 10px;">' . esc_html__( 'The code added is not associated to any existing gift card.', 'yith-woocommerce-gift-cards' ) . '</div>';
					echo '<br>';
				} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
					if ( is_object( $gift_card ) && 0 !== $gift_card->ID && $gift_card->has_sufficient_credit( $used_amount ) ) {
						if ( ! $gift_card->is_expired() ) {
							$new_balance = apply_filters( 'yith_ywgc_gift_card_coupon_amount', $gift_card->get_balance(), $gift_card ) - (float) $used_amount;

							/**
							 * DO_ACTION: yith_ywgc_before_register_redemption_shortcode
							 *
							 * Adds an action just before register the redemption of the amount via shortcode.
							 *
							 * @param object $gift_card  Object of the gift card.
							 * @param string $code       Code introduced in the shortcode.
							 * @param float $used_amount Amount introduced in the shortcode.
							 */
							do_action( 'yith_ywgc_before_register_redemption_shortcode', $gift_card, $code, $used_amount );

							$gift_card->update_balance( apply_filters( 'yith_ywgc_gift_card_amount_before_deduct_redeem_shortcode', $new_balance, $gift_card ) );
							$gift_card->register_redemption( $used_amount );

							echo '<div style="background-color: #edf8c0; padding: 15px; width: fit-content; margin-top: 10px;">' . esc_html__( 'The gift card has been redeemed successfully.', 'yith-woocommerce-gift-cards' ) . '<br><br>' . esc_html__( 'New gift card balance: ', 'yith-woocommerce-gift-cards' ) . '<span style="font-weight: bold">' . wp_kses_post( wc_price( apply_filters( 'yith_ywgc_gift_card_coupon_amount', $gift_card->get_balance(), $gift_card ) ) ) . '</span></div>';
							echo '<br>';
						} else {
							echo '<div style="color: red; margin-top: 10px;">' . esc_html__( 'Your gift card expired on ', 'yith-woocommerce-gift-cards' ) . wp_kses_post( gmdate( 'Y-m-d', $gift_card->expiration ) ) . '</div><br>';
							echo '<div style="color: red;">' . esc_html__( 'Gift card balance was: ', 'yith-woocommerce-gift-cards' ) . '<span style="font-weight: bold;">' . wp_kses_post( wc_price( apply_filters( 'yith_ywgc_gift_card_coupon_amount', $gift_card->get_balance(), $gift_card ) ) ) . '</span></div>';
							echo '<br>';
						}
					} else {
						echo '<div style="color: red; margin-top: 10px;">' . esc_html__( 'The gift card balance is not enough to cover this order amount.', 'yith-woocommerce-gift-cards' ) . '</div><br>';
						echo '<div style="color: red;">' . esc_html__( 'Gift card balance: ', 'yith-woocommerce-gift-cards' ) . '<span style="font-weight: bold;">' . wp_kses_post( wc_price( apply_filters( 'yith_ywgc_gift_card_coupon_amount', $gift_card->get_balance(), $gift_card ) ) ) . '</span></div>';
						echo '<br>';
					}
				}
			}
			?>
		</span>
	</form>
	<?php
}
