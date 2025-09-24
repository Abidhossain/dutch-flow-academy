<?php
/**
 * My gift cards
 *
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user = wp_get_current_user();

/**
 * APPLY_FILTERS: yith_ywgc_my_gift_cards_columns
 *
 * Filter the columns to display in the gift card table in "My account".
 *
 * @param array the columns
 *
 * @return array
 */
$gift_card_columns = apply_filters(
	'yith_ywgc_my_gift_cards_columns',
	array(
		'code'        => esc_html__( 'Code', 'yith-woocommerce-gift-cards' ),
		'balance'     => esc_html__( 'Balance', 'yith-woocommerce-gift-cards' ),
		'usage'       => esc_html__( 'Usage', 'yith-woocommerce-gift-cards' ),
		'status'      => esc_html__( 'Status', 'yith-woocommerce-gift-cards' ),
		'direct_link' => '',
	)
);

/**
 * APPLY_FILTERS: yith_ywgc_woocommerce_my_account_my_orders_query
 *
 * Filter the query to display in the gift cards table in "My account".
 *
 * @param array the gift cards query
 *
 * @return array
 */
$gift_cards_args = apply_filters(
	'yith_ywgc_woocommerce_my_account_my_orders_query',
	array(
		'numberposts' => - 1,
		'fields'      => 'ids',
		'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'OR',
			array(
				'key'   => YWGC_META_GIFT_CARD_CUSTOMER_USER,
				'value' => get_current_user_id(),
			),
			array(
				'key'   => '_ywgc_recipient',
				'value' => $user->user_email,
			),
		),
		'post_type'   => YWGC_CUSTOM_POST_TYPE_NAME,
		'post_status' => 'any',
	)
);

// Retrieve the gift cards matching the criteria.
$ids = get_posts( $gift_cards_args );

/**
 * APPLY_FILTERS: yith_ywgc_my_account_my_giftcards
 *
 * Filter the title to display in the gift card table in "My account".
 *
 * @param string the table title
 *
 * @return string
 */
/**
 * APPLY_FILTERS: yith_ywgc_my_account_add_new_text
 *
 * Filter the text to add a new gift card in the gift card table in "My account".
 *
 * @param string the text
 *
 * @return string
 */
?>
<div class="gift-card-panel-title-container">
	<h2 class="gift-card-panel-title"><?php echo esc_html( apply_filters( 'yith_ywgc_my_account_my_giftcards', __( 'My Gift Cards', 'yith-woocommerce-gift-cards' ) ) ); ?></h2>
	<button id="ywgc-add-new-gift-card" class="yith-add-new-gc-my-account-button"><?php echo esc_html( apply_filters( 'yith_ywgc_my_account_add_new_text', __( 'Add new', 'yith-woocommerce-gift-cards' ) ) ); ?></button>
</div>

<?php if ( $ids ) : ?>
	<table class="shop_table shop_table_responsive my_account_giftcards" id="ywgc-my-acount-table">
		<thead>
			<tr>
				<?php foreach ( $gift_card_columns as $column_id => $column_name ) : ?>
					<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $ids as $gift_card_id ) :
				$gift_card = new YITH_YWGC_Gift_Card_Extended( array( 'ID' => $gift_card_id ) );

				if ( ! $gift_card->exists() ) {
					continue;
				}

				if ( $gift_card->is_virtual() && '' === $gift_card->delivery_send_date  ) {
					continue;
				}

				?>
				<tr class="ywgc-gift-card status-<?php echo esc_attr( $gift_card->status ); ?>">
					<?php foreach ( $gift_card_columns as $column_id => $column_name ) : ?>
						<td class="<?php echo esc_attr( $column_id ); ?> " data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php
							$value = '';

							switch ( $column_id ) {
								case 'code':
									$value = $gift_card->get_code();
									break;

								case 'balance':
									$value = wc_price( apply_filters( 'yith_ywgc_get_gift_card_price', $gift_card->get_balance(), $gift_card ) );
									break;

								case 'status':
									$value       = $gift_card->is_expired() ? '' : ywgc_get_status_label( $gift_card );
									$date_format = apply_filters( 'yith_wcgc_date_format', 'Y-m-d' );

									if ( $gift_card->expiration ) {
										$value .= $gift_card->is_expired() ? '' : '<br>';

										/**
										 * APPLY_FILTERS: yith_ywgc_gift_card_custom_expiration_message
										 *
										 * Filter the expiration message in the gift card table in "My account".
										 *
										 * @param string the expiration message
										 *
										 * @return string
										 */
										// translators: %s is the gift card expiration date.
										$value .= $gift_card->is_expired() ? sprintf( _x( 'Expired on: %s', 'gift card expiration date', 'yith-woocommerce-gift-cards' ), date_i18n( $date_format, $gift_card->expiration ) ) : apply_filters(
											'yith_ywgc_gift_card_custom_expiration_message',
											// translators: %s is the gift card expiration date.
											sprintf( _x( 'Expires on: %s', 'gift card expiration date', 'yith-woocommerce-gift-cards' ), date_i18n( $date_format, $gift_card->expiration ) ),
											$gift_card
										);
									}
									break;

								case 'usage':
									$orders = $gift_card->get_registered_orders();

									if ( $orders ) {
										foreach ( $orders as $order_id ) {
											$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

											if ( is_object( $order ) && is_object( $user ) && $user->ID === $order->get_customer_id() ) {
												?>
												<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-order', $order_id ) ); ?>" class="ywgc-view-order">
													<?php
													// translators: %s is the order ID in which the gift card was used.
													echo esc_html( sprintf( __( 'Order %s', 'yith-woocommerce-gift-cards' ), $order_id ) );
													?>
												</a><br>
												<?php
											}
										}
									} else {
										esc_html_e( 'The code has not been used yet', 'yith-woocommerce-gift-cards' );
									}
									break;

								case 'direct_link':
									$shop_page_url = apply_filters( 'yith_ywgc_shop_page_url', get_permalink( wc_get_page_id( 'shop' ) ) ? get_permalink( wc_get_page_id( 'shop' ) ) : site_url(), $gift_card );
									$args          = array(
										YWGC_ACTION_ADD_DISCOUNT_TO_CART => $gift_card->gift_card_number,
										YWGC_ACTION_VERIFY_CODE          => YITH_YWGC()->hash_gift_card( $gift_card ),
									);
									$direct_link   = esc_url( add_query_arg( $args, $shop_page_url ) );
									$link_text     = __( 'Apply to cart', 'yith-woocommerce-gift-cards' );

									$target = apply_filters( 'yith_ywgc_apply_gift_card_to_cart_target', '_blank' );

									echo '<a class="button" href="' . esc_url( $direct_link ) . '" target="' . esc_attr( $target ) . '">' . esc_html( $link_text ) . '</a>';
									break;

								default:
									/**
									 * APPLY_FILTERS: yith_ywgc_my_account_column
									 *
									 * Filter the default column in the gift card table in "My account".
									 *
									 * @param string the column data
									 * @param int $column_id the column ID
									 * @param object $gift_card the gift card object
									 *
									 * @return string
									 */
									$value = apply_filters( 'yith_ywgc_my_account_column', '', $column_id, $gift_card );
							}

							if ( $value ) {
								echo '<span>' . wp_kses_post( $value ) . '</span>';
							}

							?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php else : ?>
	<div style="margin-top: 5em">
		<?php
		/**
		 * DO_ACTION: ywgc_empty_table_state_action_customer
		 *
		 * Trigger the empty table state.
		 */
		do_action( 'ywgc_empty_table_state_action_customer' );
		?>
	</div>
<?php endif; ?>
