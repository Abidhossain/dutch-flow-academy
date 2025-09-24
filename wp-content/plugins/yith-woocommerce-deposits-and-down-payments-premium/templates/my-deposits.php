<?php
/**
 * My deposit template
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates
 * @version 1.0.1
 */

/**
 * Template variables:
 *
 * @var WC_Order $order    Deposit order
 * @var int      $order_id Deposit order id
 * @var array    $deposits (Check balances)
 * @var array    $balances Array of balances records, formatted to include all data required by this template
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

?>

<div id="yith_wcdp_deposits_details" class="yith-wcdp-my-deposits">
	<h2>
		<?php
		// translators: 1. Deposit label.
		echo esc_html( apply_filters( 'yith_wcdp_my_deposit_title', sprintf( __( '%s details', 'yith-woocommerce-deposits-and-down-payments' ), YITH_WCDP_Labels::get_balance_label() ) ) );
		?>
	</h2>
	<p>
		<?php
		// translators: 1. Deposit label.
		echo esc_html( apply_filters( 'yith_wcdp_my_deposit_text', sprintf( __( 'Some products in this order have been bought with %s. In order to complete the transaction and ship the products, all remaining amounts have to be paid. Below is the list of items with pending balance:', 'yith-woocommerce-deposits-and-down-payments' ), strtolower( YITH_WCDP_Labels::get_deposit_label() ) ) ) );
		?>
	</p>
	<?php do_action( 'yith_wcdp_before_my_deposits_table', $order_id ); ?>
	<table class="shop_table shop_table_responsive order_details">
		<thead>
		<tr>
			<th class="order-id"><?php esc_html_e( 'Order', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
			<th class="product-name"><?php esc_html_e( 'Product', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
			<th class="order-paid"><?php esc_html_e( 'Totals', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
			<th class="order-status"><?php esc_html_e( 'Status', 'yith-woocommerce-deposits-and-down-payments' ); ?></th>
			<th class="order-actions"></th>
		</tr>
		</thead>
		<tbody>
		<?php if ( ! empty( $balances ) ) : ?>
			<?php foreach ( $balances as $balance ) : ?>
				<tr>
					<td data-title="<?php esc_html_e( 'Order', 'yith-woocommerce-deposits-and-down-payments' ); ?>">
						<a href="<?php echo esc_url( $balance['suborder_view_url'] ); ?>">#<?php echo esc_html( $balance['suborder_id'] ); ?></a>
					</td>
					<td class="product-name" data-title="<?php esc_html_e( 'Product', 'yith-woocommerce-deposits-and-down-payments' ); ?>">
						<?php if ( ! empty( $balance['order_items'] ) ) : ?>
						<ul>
							<?php foreach ( $balance['order_items'] as $item ) : ?>
							<li>
								<?php
								$product = $item->get_product();

								if ( empty( $product ) ) {
									continue;
								}
								?>
								<?php echo wp_kses_post( $product->get_image( 'thumb' ) ); ?>
								<a href="<?php echo esc_attr( $product->get_permalink() ); ?>">
									<?php echo esc_html( $item->get_name() ); ?>
								</a>
							</li>
							<?php endforeach; ?>
						</ul>
						<?php endif; ?>
					</td>
					<td class="order-paid" data-title="<?php esc_html_e( 'Totals', 'yith-woocommerce-deposits-and-down-payments' ); ?>">
						<?php echo wc_price( $balance['order_total'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php if ( apply_filters( 'yith_wcdp_print_paid_details', true, $order_id ) ) : ?>
							<div class="details">
								<small><?php esc_html_e( 'Subtotal: ', 'yith-woocommerce-deposits-and-down-payments' ); ?> <?php echo wc_price( $balance['order_subtotal'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></small>

								<?php if ( $balance['order_discount'] ) : ?>
									<br>
									<small><?php esc_html_e( 'Discount: ', 'yith-woocommerce-deposits-and-down-payments' ); ?> <?php echo wc_price( -1 * $balance['order_discount'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></small>
								<?php endif; ?>
								<?php if ( $balance['order_shipping'] ) : ?>
									<br>
									<small><?php esc_html_e( 'Shipping: ', 'yith-woocommerce-deposits-and-down-payments' ); ?> <?php echo wc_price( $balance['order_shipping'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></small>
								<?php endif; ?>
								<?php if ( $balance['order_taxes'] ) : ?>
									<br>
									<small><?php esc_html_e( 'Taxes: ', 'yith-woocommerce-deposits-and-down-payments' ); ?> <?php echo wc_price( $balance['order_taxes'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></small>
								<?php endif; ?>
							</div>
					<?php endif; ?>
					</td>
					<td class="order-status" data-title="<?php esc_html_e( 'Status', 'yith-woocommerce-deposits-and-down-payments' ); ?>">
						<?php echo esc_html( wc_get_order_status_name( $balance['order_status'] ) ); ?>

						<?php if ( $balance['expiration_date'] ) : ?>
							<small>
								<?php echo esc_html( $balance['expiration_date'] ); ?>
							</small>
						<?php endif; ?>
					</td>
					<td class="order-actions" data-title="<?php esc_html_e( 'Actions', 'yith-woocommerce-deposits-and-down-payments' ); ?>">
						<?php if ( $balance['actions'] ) : ?>
							<div class="button-with-submenu">
								<a role="button" class="submenu-opener"><i class="yith-icon yith-icon-more"></i></a>
								<ul class="submenu">
									<?php
									foreach ( $balance['actions'] as $key => $deposit_action ) {
										echo '<li><a rel="nofollow" href="' . esc_url( $deposit_action['url'] ) . '" class="' . sanitize_html_class( $key ) . '">' . esc_html( $deposit_action['name'] ) . '</a></li>';
									}
									?>
								</ul>
							</div>
					<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
	<?php do_action( 'yith_wcdp_after_my_deposits_table', $order_id ); ?>
</div>
