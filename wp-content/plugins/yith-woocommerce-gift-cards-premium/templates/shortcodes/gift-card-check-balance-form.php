<?php
/**
 * Template to render the form to check the gift card balance
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

$text_input = array(
	'type'  => 'text',
	'id'    => 'ywgc-check-code',
	'name'  => 'ywgc-check-code',
	'class' => 'ywgc-shortcode-text-input',
);

?>
<form method="post" class="form-check-gift-card-balance ywgc-shortcode-form" name="form-check-gift-card-balance">
	<p class="ywgc-shortcode-form-title"><?php echo esc_html_x( 'Check your balance', 'shortcode title', 'yith-woocommerce-gift-cards' ); ?></p>
	<span class="ywgc-shortcode-form-label"> <?php esc_html_e( 'Gift Card code', 'yith-woocommerce-gift-cards' ); ?></span>
	<div>
		<?php yith_plugin_fw_get_field( $text_input, true ); ?>
		<button class="yith-plugin-fw__button--primary ywgc-shortcode-submit-button" type="submit"><?php esc_html_e( 'Submit', 'yith-woocommerce-gift-cards' ); ?></button>
		<span class="ywgc-shortcode-message">
			<?php
			if ( isset( $_POST['ywgc-check-code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$code      = sanitize_text_field( wp_unslash( $_POST['ywgc-check-code'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$args      = array(
					'gift_card_number' => $code,
				);
				$gift_card = new YITH_YWGC_Gift_Card( $args );

				if ( is_object( $gift_card ) && 0 !== $gift_card->ID ) {
					if ( $gift_card->is_expired() ) {
						// translators: %s is the date when the gift card expired.
						echo '<div style="color: red; margin-top: 10px;">' . esc_html( sprintf( __( 'Your gift card expired on %s with the following balance: ', 'yith-woocommerce-gift-cards' ), gmdate( 'Y-m-d', $gift_card->expiration ) ) ) . '<span style="font-weight: bold">' . wp_kses_post( wc_price( apply_filters( 'yith_ywgc_gift_card_coupon_amount', $gift_card->get_balance(), $gift_card ) ) ) . '</span></div>';
						echo '<br>';
					} else {
						echo '<div class="ywgc-check-code-gift-card-balance" style="background-color: #edf8c0; padding: 15px; width: fit-content; margin-top: 10px;">' . esc_html__( 'Your gift card balance: ', 'yith-woocommerce-gift-cards' ) . '<span style="font-weight: bold">' . wp_kses_post( wc_price( apply_filters( 'yith_ywgc_gift_card_coupon_amount', $gift_card->get_balance(), $gift_card ) ) ) . '</span></div>';
						echo '<br>';
					}
				} else {
					echo '<div style="color: red; margin-top: 10px;">' . esc_html__( 'This code is not associated to any existing gift card. Try again.', 'yith-woocommerce-gift-cards' ) . '</div>';
					echo '<br>';
				}
			}
			?>
		</span>
	</div>
</form>
