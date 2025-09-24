<?php
/**
 * Deposit expirations
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates
 * @version 1.0.1
 */

/**
 * Template variables:
 *
 * @var array $balances Array of balances that system is expecting to create at checkout
 */

?>
<a role="button" class="yith-wcdp-modal-opener deposit-expiration-modal-opener" data-modal="deposit_expiration_modal">
	<?php esc_html_e( 'View details', 'yith-woocommerce-deposits-and-down-payments' ); ?>
</a>

<div id="deposit_expiration_modal">
	<ul class="balances-details">
		<?php foreach ( $balances as $balance ) : ?>
			<?php
			$products           = $balance['contents'];
			$first_product      = current( $products );
			$balance_expiration = $balance['balance_expiration'];
			$product_names      = array();

			foreach ( $products as $product ) {
				$product_names[] = sprintf( '<a href="%1$s">%2$s</a>', $product->get_permalink(), $product->get_name() );
			}
			?>
			<li class="single-balance">
				<?php echo wp_kses_post( $first_product->get_image() ); ?>
				<div class="balance-details">
					<div class="balance-products">
						<h3><?php echo wp_kses_post( implode( ', ', $product_names ) ); ?></h3>
					</div>
					<?php if ( $balance_expiration ) : ?>
						<?php
							// translators: 1. Formatted expiration date for the deposit.
							$expiration_message   = apply_filters( 'yith_wcdp_expiration_notice', __( 'Balance payment will be required on %s', 'yith-woocommerce-deposits-and-down-payments' ), $products );
							$formatted_expiration = gmdate( wc_date_format(), strtotime( $balance_expiration ) );
							$expiration_message   = sprintf( $expiration_message, "<small>$formatted_expiration</small>" );
						?>
						<div class="balance-expiration">
							<?php echo wp_kses_post( $expiration_message ); ?>
						</div>
					<?php else : ?>
						<div class="balance-expiration">
							<?php esc_html_e( 'Pay balance when you\'re ready to complete your purchase', 'yith-woocommerce-deposits-and-down-payments' ); ?>
						</div>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
