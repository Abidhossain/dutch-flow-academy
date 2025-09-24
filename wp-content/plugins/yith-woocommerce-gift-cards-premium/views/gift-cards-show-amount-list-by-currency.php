<?php
/**
 * Gift Card Amount List Options for YITH Multi Currency Switcher
 *
 * @package YITH\GiftCards\Views
 */

?>
<span class="variation-amount-list">
	<?php
	if ( $amounts ) :
		$index = 0;

		foreach ( $amounts as $amount ) :
			?>
			<span class="variation-amount" data-amount="<?php echo esc_attr( $amount ); ?>">
				<input type="text" class="gift_card-amount" data-currency-id="<?php echo esc_attr( $currency_id ); ?>" data-amount="<?php echo esc_attr( $amount ); ?>" value="<?php echo esc_attr( $amount ); ?>">
				<input type="hidden" class="yith_wcgc_multi_currency" name="<?php echo esc_attr( 'yith_wcgc_multi_currency[gift-card-amounts][' . $currency_id . '][' . $index . ']' ); ?>" value="<?php echo esc_attr( $amount ); ?>">
			</span>
			<?php
			++$index;
		endforeach;
		?>
	<?php endif; ?>

	<span class="variation-amount-aux-currency ywgc-hidden" data-amount="">
		<input type="text" class="gift_card-amount" data-currency-id="<?php echo esc_attr( $currency_id ); ?>" data-amount="" value="">
		<input type="hidden" class="yith_wcgc_multi_currency" name="" value="">
	</span>
</span>
