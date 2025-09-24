<?php
/**
 * Gift Card Amount List Options
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
				<input type="text" class="gift_card-amount" data-amount="<?php echo esc_attr( $amount ); ?>" value="<?php echo esc_attr( $amount ); ?>">
				<input type="hidden" class="yith_wcgc_multi_currency" name="<?php echo esc_attr( 'gift-card-amounts[' . $index . ']' ); ?>" value="<?php echo esc_attr( $amount ); ?>">
				<a class="remove-amount" href=""><span class="dashicons dashicons-dismiss"></span></a>
			</span>
			<?php
			++$index;
		endforeach;
		?>
	<?php endif; ?>

	<span class="variation-amount-aux ywgc-hidden" data-amount="">
		<input type="text" class="gift_card-amount" data-amount="" value="">
		<input type="hidden" class="yith_wcgc_multi_currency" name="" value="">
		<a class="remove-amount" href=""><span class="dashicons dashicons-dismiss"></span></a>
	</span>
</span>
