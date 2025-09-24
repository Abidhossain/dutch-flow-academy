<?php
/**
 * Cart handling class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Cart' ) ) {
	/**
	 * Alters cart, to add deposit info
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Cart {

		/**
		 * Grand totals to show on cart and checkout for current cart/deposit configuration
		 *
		 * @var array
		 */
		protected static $grand_totals;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			// add to cart process.
			add_filter( 'yith_wcdp_virtual_on_deposit', array( self::class, 'set_virtual_on_deposit' ) );
			add_filter( 'woocommerce_add_cart_item', array( self::class, 'update_cart_item' ), 30, 3 );
			add_filter( 'woocommerce_add_cart_item_data', array( self::class, 'update_cart_item_data' ), 30, 3 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( self::class, 'update_cart_item_from_session' ), 110, 2 );
			add_filter( 'woocommerce_add_to_cart_sold_individually_found_in_cart', array( self::class, 'deposit_found_in_cart' ), 10, 4 );

			// update frontend to show deposit/balance amount.
			add_filter( 'woocommerce_get_item_data', array( self::class, 'add_deposit_item_data' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_name', array( self::class, 'filter_cart_deposit_product_name' ), 10, 2 );
			add_filter( 'woocommerce_order_item_name', array( self::class, 'filter_order_deposit_product_name' ), 10, 2 );
			add_filter( 'woocommerce_cart_totals_order_total_html', array( self::class, 'filter_cart_total' ), 10, 2 );
			add_filter( 'woocommerce_cart_totals_order_total_html', array( self::class, 'add_expiration_note' ), 10, 2 );
			add_action( 'woocommerce_review_order_before_order_total', array( self::class, 'print_balance_totals' ) );
		}

		/* === ADD DEPOSIT TO CART === */

		/**
		 * Set virtual on deposit depending on backend option
		 *
		 * @param bool $virtual_on_deposit Whether deposits product should be virtual or not.
		 *
		 * @return bool Whether deposits product should be virtual or not
		 *
		 * @since 1.2.5
		 */
		public static function set_virtual_on_deposit( $virtual_on_deposit ) {
			$virtual_on_deposit = 'yes' === get_option( 'yith_wcdp_general_deposit_virtual', 'yes' );

			return $virtual_on_deposit;
		}

		/**
		 * Update cart item when deposit is selected
		 *
		 * @param array $cart_item Current cart item.
		 *
		 * @return mixed Filtered cart item
		 * @since 1.0.0
		 */
		public static function update_cart_item( $cart_item ) {
			/**
			 * Product object stored within cart item.
			 *
			 * @var $product \WC_Product
			 */
			$product = $cart_item['data'];

			if ( ! $product instanceof WC_Product ) {
				return $cart_item;
			}

			$product_id = $product->get_id();
			$parent_id  = $product->get_parent_id();
			$parent_id  = $parent_id ? $parent_id : $product_id;

			if (
				YITH_WCDP_Deposits::is_enabled( $product_id ) &&
				! YITH_WCDP_Deposits::has_expired( $product_id ) &&
				! $product->get_meta( 'yith_wcdp_deposit' ) &&
				/**
				 * APPLY_FILTERS: yith_wcdp_skip_cart_item_processing
				 *
				 * Filters if should skip cart item processing.
				 *
				 * @param bool             Default value: false.
				 * @param array $cart_item Current cart item.
				 *
				 * @return bool
				 */

				! apply_filters( 'yith_wcdp_skip_cart_item_processing', false, $cart_item )
			) {
				$deposit_forced = YITH_WCDP_Deposits::is_mandatory( $product_id );

				/**
				 * APPLY_FILTERS: yith_wcdp_deposit_value
				 *
				 * Filters the deposit value.
				 *
				 * @param double              Deposit amount for specified product and variation
				 * @param int   $product_id   The product ID.
				 * @param int   $variation_id The variation ID.
				 * @param array $cart_item    Current cart item.
				 *
				 * @return double
				 */
				$deposit_value = apply_filters( 'yith_wcdp_deposit_value', YITH_WCDP_Deposits::get_deposit( $product_id, false, $product->get_price() ), $parent_id, $product_id, $cart_item );

				/**
				 * APPLY_FILTERS: yith_wcdp_deposit_balance
				 *
				 * Filters the balance value.
				 *
				 * @param double              Balance amount for specified product and variation
				 * @param int   $product_id   The product ID.
				 * @param int   $variation_id The variation ID.
				 * @param array $cart_item    Current cart item.
				 *
				 * @return double
				 */
				$deposit_balance = apply_filters( 'yith_wcdp_deposit_balance', max( $product->get_price() - $deposit_value, 0 ), $parent_id, $product_id, $cart_item );

				// phpcs:disable WordPress.Security.NonceVerification
				if (
					/**
					 * APPLY_FILTERS: yith_wcdp_process_cart_item_product_change
					 *
					 * Filters if should process cart item product change.
					 *
					 * @param bool             Default value: true.
					 * @param array $cart_item Current cart item.
					 *
					 * @return bool
					 */
					apply_filters( 'yith_wcdp_process_cart_item_product_change', true, $cart_item ) &&
					isset( $_REQUEST['add-to-cart'] ) &&
					(
						( $deposit_forced && ! defined( 'YITH_WCDP_PROCESS_SUBORDERS' ) ) ||
						( isset( $_REQUEST['payment_type'] ) && 'deposit' === $_REQUEST['payment_type'] )
					)
				) {
					$product->set_price( $deposit_value );
					$product->update_meta_data( 'yith_wcdp_deposit', true );

					if ( apply_filters( 'yith_wcdp_virtual_on_deposit', true, null ) ) {
						$product->set_virtual( true );
					}

					$cart_item['deposit_value']   = $deposit_value;
					$cart_item['deposit_balance'] = $deposit_balance;
				}
				// phpcs:enable WordPress.Security.NonceVerification
			}

			return $cart_item;
		}

		/**
		 * Add cart item data when deposit is selected, to store info to save with order
		 *
		 * @param array $cart_item_data Currently saved cart item data.
		 * @param int   $product_id     Product id.
		 * @param int   $variation_id   Variation id.
		 *
		 * @return mixed Filtered cart item data
		 * @since 1.0.0
		 */
		public static function update_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
			$product_id = ! empty( $variation_id ) ? $variation_id : $product_id;
			$product    = wc_get_product( $product_id );

			if (
				YITH_WCDP_Deposits::is_enabled( $product_id ) &&
				! YITH_WCDP_Deposits::has_expired( $product_id ) &&
				/**
				 * APPLY_FILTERS: yith_wcdp_skip_cart_item_data_processing
				 *
				 * Filters if should skip cart item data processing.
				 *
				 * @param bool                       Default value: false.
				 * @param array      $cart_item_data Currently saved cart item data.
				 * @param WC_Product $product        The product.
				 *
				 * @return bool
				 */
				! apply_filters( 'yith_wcdp_skip_cart_item_data_processing', false, $cart_item_data, $product )
			) {
				$deposit_forced  = YITH_WCDP_Deposits::is_mandatory( $product_id );
				$deposit_type    = YITH_WCDP_Deposits::get_type( $product_id );
				$deposit_amount  = YITH_WCDP_Deposits::get_amount( $product_id );
				$deposit_rate    = YITH_WCDP_Deposits::get_rate( $product_id );
				$deposit_value   = YITH_WCDP_Deposits::get_deposit( $product_id );
				$deposit_balance = max( (float) $product->get_price() - (float) $deposit_value, 0 );

				// phpcs:disable WordPress.Security.NonceVerification
				$process_deposit = ( $deposit_forced && ! defined( 'YITH_WCDP_PROCESS_SUBORDERS' ) ) || ( isset( $_REQUEST['payment_type'] ) && 'deposit' === $_REQUEST['payment_type'] );

				/**
				 * APPLY_FILTERS: yith_wcdp_process_deposit
				 *
				 * Filters if should process deposit.
				 *
				 * @param bool                       Default value.
				 * @param array $cart_item_data Currently saved cart item data.
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_wcdp_process_deposit', $process_deposit, $cart_item_data ) ) {
					$cart_item_data['deposit']         = true;
					$cart_item_data['deposit_type']    = $deposit_type;
					$cart_item_data['deposit_amount']  = $deposit_amount;
					$cart_item_data['deposit_rate']    = $deposit_rate;
					$cart_item_data['deposit_value']   = $deposit_value;
					$cart_item_data['deposit_balance'] = $deposit_balance;
				}
				// phpcs:enable WordPress.Security.NonceVerification
			}

			return $cart_item_data;
		}

		/**
		 * Update cart item when retrieving cart from session
		 *
		 * @param array $session_data Session data to add to cart.
		 * @param array $values       Values stored in session.
		 *
		 * @return mixed Session data
		 * @since 1.0.0
		 */
		public static function update_cart_item_from_session( $session_data, $values ) {
			if ( empty( $values['deposit'] ) ) {
				return $session_data;
			}

			/**
			 * Product object stored into session
			 *
			 * @var $product WC_Product
			 */
			$product      = $session_data['data'];
			$product_id   = $session_data['product_id'];
			$variation_id = $session_data['variation_id'];

			$session_data = apply_filters(
				'yith_wcdp_cart_item_from_session',
				array_merge(
					$session_data,
					array(
						'deposit'         => true,
						'deposit_type'    => $values['deposit_type'] ?? '',
						'deposit_amount'  => $values['deposit_amount'] ?? '',
						'deposit_rate'    => $values['deposit_rate'] ?? '',
						'deposit_value'   => isset( $values['deposit_value'] ) ? apply_filters( 'yith_wcdp_deposit_value', $values['deposit_value'], $product_id, $variation_id, $session_data ) : '',
						'deposit_balance' => isset( $values['deposit_balance'] ) ? apply_filters( 'yith_wcdp_deposit_balance', $values['deposit_balance'], $product_id, $variation_id, $session_data ) : '',
					)
				)
			);

			if (
				apply_filters( 'yith_wcdp_process_cart_item_product_change', true, $session_data ) &&
				isset( $values['deposit_value'] )
			) {
				$product->set_price( $session_data['deposit_value'] );
				$product->update_meta_data( 'yith_wcdp_deposit', true );

				if ( apply_filters( 'yith_wcdp_virtual_on_deposit', true, null ) ) {
					$product->set_virtual( true );
				}
			}

			return $session_data;
		}

		/**
		 * When product is sold individually, before adding it to cart, checks whether there isn't any other item with a different cart_id
		 * that is just a deposit (non-deposit) version of the simple product (deposit product) being added to cart
		 *
		 * @param bool  $found_in_cart  Whether item is already in cart.
		 * @param int   $product_id     Id of the product being added to cart.
		 * @param int   $variation_id   Id of the variation being added to cart.
		 * @param array $cart_item_data Array of cart item data for item being added to cart.
		 *
		 * @return bool Whether item is already in cart
		 * @since 1.1.2
		 */
		public static function deposit_found_in_cart( $found_in_cart, $product_id, $variation_id, $cart_item_data ) {
			$cart               = WC()->cart;
			$new_cart_item_data = $cart_item_data;

			if ( isset( $cart_item_data['deposit'] ) ) {
				unset( $new_cart_item_data['deposit'] );
				unset( $new_cart_item_data['deposit_type'] );
				unset( $new_cart_item_data['deposit_amount'] );
				unset( $new_cart_item_data['deposit_rate'] );
				unset( $new_cart_item_data['deposit_value'] );
				unset( $new_cart_item_data['deposit_balance'] );
			} else {
				// force deposit item handling.
				add_filter( 'yith_wcdp_process_deposit', '__return_true' );

				$new_cart_item_data = self::update_cart_item_data( $cart_item_data, $product_id, $variation_id );

				// remove forced deposit handling.
				remove_filter( 'yith_wcdp_process_deposit', '__return_true' );
			}

			if ( isset( $new_cart_item_data['variation'] ) ) {
				$new_cart_id = $cart->generate_cart_id( $product_id, $variation_id, $new_cart_item_data['variation'], $new_cart_item_data );
			} else {
				$new_cart_id = 0;
			}

			$related_found = $cart->find_product_in_cart( $new_cart_id );

			return $found_in_cart || $related_found;
		}

		/* === CART/CHECKOUT FRONTEND METHODS === */

		/**
		 * Adds item data to be shown on cart/checkout pages (this data won't be stored anywhere)
		 *
		 * @param array $data      Data to show on templates.
		 * @param array $cart_item Current cart item.
		 *
		 * @return mixed Filtered array of cart item data
		 * @since 1.0.0
		 */
		public static function add_deposit_item_data( $data, $cart_item ) {

			if ( isset( $cart_item['deposit'] ) && $cart_item['deposit'] ) {

				$full_amount_item = array(
					'display' => wc_price(
						yith_wcdp_get_price_to_display(
							$cart_item['data'],
							array(
								'qty'   => intval( $cart_item['quantity'] ),
								'price' => $cart_item['deposit_value'] + $cart_item['deposit_balance'],
							)
						)
					),
					'name'    => YITH_WCDP_Labels::get_full_price_label(),
					'value'   => $cart_item['deposit_value'] + $cart_item['deposit_balance'] * intval( $cart_item['quantity'] ),
				);

				/**
				 * APPLY_FILTERS: yith_wcdp_full_amount_item
				 *
				 * Filters full price array of values.
				 *
				 * @param array $full_amount_item Default array of display, name and value.
				 * @param array $cart_item        Current cart item.
				 * @param array $data             Data to show on templates.
				 *
				 * @return array
				 */
				$full_amount_item = apply_filters( 'yith_wcdp_full_amount_item', $full_amount_item, $cart_item, $data );

				$balance_item = array(
					'display' => wc_price(
						yith_wcdp_get_price_to_display(
							$cart_item['data'],
							array(
								'qty'   => intval( $cart_item['quantity'] ),
								'price' => $cart_item['deposit_balance'],
							)
						)
					),
					'name'    => YITH_WCDP_Labels::get_balance_label(),
					'value'   => $cart_item['deposit_balance'] * intval( $cart_item['quantity'] ),
				);

				/**
				 * APPLY_FILTERS: yith_wcdp_balance_item
				 *
				 * Filters balance item array of values.
				 *
				 * @param array $balance_item Default array of display, name and value.
				 * @param array $cart_item    Current cart item.
				 * @param array $data         Data to show on templates.
				 *
				 * @return array
				 */
				$balance_item = apply_filters( 'yith_wcdp_balance_item', $balance_item, $cart_item, $data );

				if ( $full_amount_item && ! in_array( $full_amount_item, $data, true ) ) {
					$data[] = $full_amount_item;
				}

				if ( $balance_item && ! in_array( $balance_item, $data, true ) ) {
					$data[] = $balance_item;
				}
			}

			return $data;
		}

		/**
		 * Filter product name on cart and checkout views, to show deposit label
		 *
		 * @param string $product_name Original product name.
		 * @param array  $cart_item    Cart item object.
		 *
		 * @return string Filtered product name
		 * @since 1.0.0
		 */
		public static function filter_cart_deposit_product_name( $product_name, $cart_item ) {
			if ( isset( $cart_item['deposit'] ) && $cart_item['deposit'] ) {
				$deposit_label = YITH_WCDP_Labels::get_deposit_label();
				$product_name  = str_replace( $deposit_label . ': ', '', $product_name );

				if ( is_cart() ) {
					$product_name = preg_replace( '^(<a.*>)(.*)(<\/a>)$^', sprintf( '$1%s: $2$3', $deposit_label ), $product_name );
				} elseif ( is_checkout() ) {
					$product_name = "$deposit_label: $product_name";
				}
			}

			return $product_name;
		}

		/**
		 * Filter product name on review-order view, to show deposit label
		 *
		 * @param string $product_name  Original product name.
		 * @param array  $order_item    Order item object.
		 *
		 * @return string Filtered product name
		 * @since 1.0.0
		 */
		public static function filter_order_deposit_product_name( $product_name, $order_item ) {
			$deposit_label      = YITH_WCDP_Labels::get_deposit_label();
			$full_payment_label = YITH_WCDP_Labels::get_full_payment_label();

			$product_name = str_replace( $deposit_label . ': ', '', $product_name );
			$product_name = str_replace( $full_payment_label . ': ', '', $product_name );

			if ( is_array( $order_item ) ) {
				if ( isset( $order_item['deposit'] ) && $order_item['deposit'] ) {
					$product_name = preg_replace( '^(<a.*>)(.*)(<\/a>)$^', sprintf( '$1%s: $2$3', $deposit_label ), $product_name );
				} elseif ( isset( $order_item['full_payment'] ) && $order_item['full_payment'] ) {
					$product_name = preg_replace( '^(<a.*>)(.*)(<\/a>)$^', sprintf( '$1%s: $2$3', $full_payment_label ), $product_name );
				}
			}

			return $product_name;
		}

		/**
		 * Update cart total in the case deposits are added to cart
		 *
		 * @param string $total_html Cart total.
		 *
		 * @return string Filtered cart total
		 * @since 1.1.3
		 */
		public static function filter_cart_total( $total_html ) {
			$totals = self::get_grand_totals();

			// extract variables needed.
			list( $total, $has_deposits, $packages ) = yith_plugin_fw_extract( $totals, 'total', 'has_deposits', 'packages' );

			if ( $total && $has_deposits ) {
				/**
				 * APPLY_FILTERS: yith_wcdp_show_cart_total_html
				 *
				 * Filters cart total HTML.
				 *
				 * @param string              Default HTML.
				 * @param WC_Cart $cart       The cart.
				 * @param string  $total_html Cart total.
				 * @param string  $total      Price total.
				 * @param array   $packages   The packages.
				 *
				 * @return string
				 */
				$total_html .= apply_filters( 'yith_wcdp_show_cart_total_html', sprintf( ' (%s <strong>%s</strong>)', __( 'of', 'yith-woocommerce-deposits-and-down-payments' ), wc_price( $total ) ), WC()->cart, $total_html, $total, $packages );
			}

			return $total_html;
		}

		/**
		 * Append a note about balances expiration to the cart/checkout total
		 * If current order generates multiple orders, a modal will be shown instead
		 *
		 * @param string $total_html HTML string representing the order total.
		 * @return string Filtered HTML
		 */
		public static function add_expiration_note( $total_html = '' ) {
			$cart = WC()->cart;

			if ( ! $cart ) {
				return $total_html;
			}

			$contents = $cart->get_cart_contents();

			if ( ! $contents ) {
				return $total_html;
			}

			$balance_type     = get_option( 'yith_wcdp_balance_type', 'multiple' );
			$grouped_contents = array();

			foreach ( $contents as $cart_item_key => $cart_item ) {
				if ( empty( $cart_item['deposit'] ) ) {
					continue;
				}

				if ( 'multiple' === $balance_type ) {
					$grouped_contents[ $cart_item_key ] = array(
						$cart_item_key => $cart_item['data'],
					);
				} else {
					if ( ! isset( $grouped_contents['common'] ) ) {
						$grouped_contents['common'] = array();
					}

					$grouped_contents['common'][ $cart_item_key ] = $cart_item['data'];
				}
			}

			if ( ! $grouped_contents ) {
				return $total_html;
			}

			$expected_balances = array();

			foreach ( $grouped_contents as $key => $balance_contents ) {
				$expected_balances[ $key ] = array(
					'contents'           => $balance_contents,
					'balance_expiration' => YITH_WCDP_Products::get_expiration( $balance_contents ),
				);
			}

			$needs_modal = 1 < count( $expected_balances );
			$does_expire = false;

			foreach ( wp_list_pluck( $expected_balances, 'balance_expiration' ) as $expiration ) {
				if ( ! $expiration ) {
					continue;
				}

				$does_expire = true;
				break;
			}

			if ( ! $does_expire ) {
				return $total_html;
			}

			if ( ! $needs_modal ) {
				$balance    = array_shift( $expected_balances );
				$products   = $balance['contents'];
				$expiration = $balance['balance_expiration'];

				if ( ! $expiration ) {
					return $total_html;
				}

				$formatted_expiration = date_i18n( wc_date_format(), strtotime( $expiration ) );

				// translators: 1. Formatted expiration date for the deposit.
				$expiration_message = apply_filters( 'yith_wcdp_expiration_notice', __( 'Balance payment will be required on %s', 'yith-woocommerce-deposits-and-down-payments' ), $products );
				$expiration_message = sprintf( $expiration_message, $formatted_expiration );

				$total_html .= '<small class="deposit-expiration-label">' . esc_html( $expiration_message ) . '</small>';
			} else {
				$args = array(
					'balances' => $expected_balances,
				);

				ob_start();
				yith_wcdp_get_template( 'deposits-expirations-modal.php', $args );

				$details_modal = ob_get_clean();
				$total_html   .= $details_modal;
			}

			return $total_html;
		}

		/**
		 * Print balance lines at checkout
		 *
		 * @param string $context Context for the template (checkout/email).
		 *
		 * @return void
		 * @since 1.3.6
		 */
		public static function print_balance_totals( $context = '' ) {
			$totals = self::get_grand_totals();

			if ( ! $totals['has_deposits'] || ! $totals['total'] ) {
				return;
			}

			$inc_taxes = WC()->cart->display_prices_including_tax();

			?>
			<tr class="balance-shipping-total">
				<th <?php echo 'email' === $context ? 'class="td" colspan="2"' : ''; ?>>
					<?php
					/**
					 * APPLY_FILTERS: yith_wcdp_balance_subtotal
					 *
					 * Filters balance subtotal text.
					 *
					 * @param string Default value: 'Balance subtotal'.
					 *
					 * @return string
					 */
					// translators: 1. Balance.
					echo esc_html( apply_filters( 'yith_wcdp_balance_subtotal', sprintf( __( '%s subtotal', 'yith-woocommerce-deposits' ), YITH_WCDP_Labels::get_balance_label() ) ) );
					?>
				</th>
				<td <?php echo 'email' === $context ? 'class="td"' : ''; ?> >
					<?php echo wc_price( $totals['balance'] ); // phpcs:ignore ?>
				</td>
			</tr>

			<?php if ( ! empty( $totals['balance_shipping'] ) ) : ?>
				<tr class="balance-shipping-total">
					<th <?php echo 'email' === $context ? 'class="td" colspan="2"' : ''; ?>>
						<?php
						// translators: 1. Balance.
						echo esc_html( sprintf( __( '%s shipping', 'yith-woocommerce-deposits' ), YITH_WCDP_Labels::get_balance_label() ) );
						?>
					</th>
					<td <?php echo 'email' === $context ? 'class="td"' : ''; ?> >
						<?php echo wc_price( $totals['balance_shipping'] ); // phpcs:ignore
						?>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( wc_tax_enabled() && ! $inc_taxes && isset( $totals['balance_totals'], $totals['balance_totals']['total_tax'] ) ) : ?>
				<tr class="balance-tax-total">
					<th <?php echo 'email' === $context ? 'class="td" colspan="2"' : ''; ?>>
					<?php
					/**
					 * APPLY_FILTERS: yith_wcdp_balance_tax_subtotal
					 *
					 * Filters balance subtotal text.
					 *
					 * @param string Default value: 'Balance subtotal'.
					 *
					 * @return string
					 */
					// translators: 1. Balance.
					echo esc_html( apply_filters( 'yith_wcdp_balance_tax_subtotal', sprintf( __( '%s tax', 'yith-woocommerce-deposits' ), YITH_WCDP_Labels::get_balance_label() ) ) );
					?>
					</th>
					<td <?php echo 'email' === $context ? 'class="td"' : ''; ?> >
						<?php echo wc_price( $totals['balance_totals']['total_tax'] ); // phpcs:ignore ?>
					</td>
				</tr>
			<?php endif; ?>

			<?php
		}

		/* === UTILITY METHODS === */

		/**
		 * Checks if current cart contains deposit
		 *
		 * @return bool Whether the cart contains a deposit.
		 */
		public static function has_deposit() {
			$main_cart     = WC()->cart;
			$cart_contents = $main_cart->cart_contents;
			$has_deposit   = false;

			foreach ( $cart_contents as $content ) {
				if ( empty( $content['deposit'] ) ) {
					continue;
				}

				$has_deposit = true;
				break;
			}

			return $has_deposit;
		}

		/**
		 * Retrieve totals for current cart/deposit configuration
		 *
		 * @return array Array of totals for current cart
		 * @since 1.3.6
		 */
		public static function get_grand_totals() {
			if ( ! empty( self::$grand_totals ) ) {
				return self::$grand_totals;
			}

			$cart_hash          = WC()->cart->get_cart_hash();
			$shipping_hash      = md5( wp_json_encode( WC()->session->get( 'chosen_shipping_methods' ) ) );
			$totals_hash        = md5( $cart_hash . $shipping_hash );
			self::$grand_totals = WC()->session->get( 'yith_wcdp_grand_totals_' . $totals_hash );

			if ( ! empty( self::$grand_totals ) ) {
				return self::$grand_totals;
			}

			$main_cart       = WC()->cart;
			$cart_contents   = $main_cart->cart_contents;
			$main_totals     = $main_cart->get_totals();
			$applied_coupons = apply_filters( 'yith_wcdp_propagate_coupons', false ) ? $main_cart->applied_coupons : array();
			$support_cart    = YITH_WCDP()->get_support_cart();

			/**
			 * In order to improve calculation for methods that uses actual cart,
			 * switch WC cart with our support cart
			 *
			 * @since  1.3.6
			 */
			WC()->cart = $support_cart;

			remove_action( 'woocommerce_after_calculate_totals', array( 'YITH_WCDP_Shipping', 'subtract_balance_shipping_costs' ) );

			$support_cart->populate( $cart_contents, $applied_coupons );

			/**
			 * If we're not applying the same coupons to both deposit and balance,
			 * make at least sure that grand total accounts for the discount applied on
			 * the deposit only.
			 */
			if ( ! apply_filters( 'yith_wcdp_propagate_coupons', false ) ) {
				$support_cart->set_discount_total( $main_totals['discount_total'] );
				$support_cart->set_discount_tax( $main_totals['discount_tax'] );
				$support_cart->set_total( $support_cart->get_total( 'edit' ) - $main_totals['discount_total'] - $main_totals['discount_tax'] );
			}

			$grand_totals = $support_cart->get_totals();

			add_action( 'woocommerce_after_calculate_totals', array( 'YITH_WCDP_Shipping', 'subtract_balance_shipping_costs' ), 10, 1 );

			/**
			 * Switch back to default cart
			 *
			 * @since 1.3.6
			 */
			WC()->cart = $main_cart;
			$main_cart->calculate_totals();

			// calculate balance totals.
			$balance_totals = array();

			foreach ( $grand_totals as $key => $grand_total ) {
				if ( ! isset( $main_totals[ $key ] ) ) {
					continue;
				}

				$main_total = $main_totals[ $key ];

				if ( ! is_scalar( $main_total ) || ! is_scalar( $grand_total ) ) {
					continue;
				}

				$balance_totals[ $key ] = max( 0, $grand_total - $main_total );
			}

			$inc_taxes = WC()->cart->display_prices_including_tax();

			// format output.
			self::$grand_totals = array(
				'total'            => $grand_totals['total'],
				'balance'          => $balance_totals['subtotal'] + ( $inc_taxes ? $balance_totals['subtotal_tax'] : 0 ),
				'coupons'          => isset( $applied_coupons ) ? $applied_coupons : 0,
				'fees'             => $grand_totals['fee_total'],
				'balance_shipping' => $balance_totals['shipping_total'] + ( $inc_taxes ? $balance_totals['shipping_tax'] : 0 ),
				'shipping_total'   => $grand_totals['shipping_total'] + ( $inc_taxes ? $grand_totals['shipping_tax'] : 0 ),
				'has_deposits'     => self::has_deposit(),
				'packages'         => WC()->cart->get_shipping_packages(),
				'grand_totals'     => $grand_totals,
				'deposit_totals'   => $main_totals,
				'balance_totals'   => $balance_totals,
			);

			WC()->session->set( 'yith_wcdp_grand_totals_' . $totals_hash, self::$grand_totals );

			return self::$grand_totals;
		}
	}
}
