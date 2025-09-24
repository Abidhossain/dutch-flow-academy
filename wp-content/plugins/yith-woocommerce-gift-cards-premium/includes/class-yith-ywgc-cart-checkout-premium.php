<?php
/**
 * Class to manage the cart and checkout features
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Cart_Checkout_Premium' ) ) {
	/**
	 * YITH_YWGC_Cart_Checkout_Premium class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Cart_Checkout_Premium extends YITH_YWGC_Cart_Checkout_Extended {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Cart_Checkout_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 */
		protected function __construct() {
			parent::__construct();

			/**
			 * Add "gift this product as" on WC thank you page.
			 */
			add_filter( 'woocommerce_order_item_name', array( $this, 'add_gift_this_product_as_on_wc_thankyou_page' ), 10, 2 );

			add_filter( 'woocommerce_cart_item_removed_title', array( $this, 'add_gift_this_product_product_removed_from_cart' ), 10, 2 );

			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'prevent_gift_card_and_physical_products_in_the_same_cart' ), 10, 6 );

			/**
			 * Show gift card details in cart page
			 */
			if ( 'yes' === get_option( 'ywgc_show_recipient_on_cart', 'no' ) ) {
				add_filter( 'woocommerce_get_item_data', array( $this, 'show_gift_card_details_in_cart' ), 10, 2 );
			}

			add_action( 'template_redirect', array( $this, 'check_email_discount' ) );

			add_filter( 'woocommerce_is_purchasable', array( $this, 'gift_card_is_purchasable' ), 10, 2 );

			/* Cart and Checkout */
			add_filter( 'woocommerce_cart_item_name', array( $this, 'show_this_product_as_a_gift_card' ), 10, 3 );
			add_filter( 'woocommerce_order_item_name', array( $this, 'show_this_product_as_a_gift_card' ), 10, 3 );

			add_filter( 'woocommerce_paypal_args', array( $this, 'ywgc_modify_paypal_args' ), 11, 2 );

			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'ywgc_remove_order_again_button_gift_this_product' ), 5, 1 );

			add_filter( 'woocommerce_cart_item_price', array( $this, 'gift_cards_cart_item_price' ), 99, 3 );

			/**
			 * Compatibility with Ajax add to Cart feature
			 */
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'yith_ywgc_ajax_add_to_cart_compatibility' ), 10, 2 );
		}

		/**
		 * Add label when the product is purchased as a gift card with the "Gift this product"
		 *
		 * @param string $product_title The product title HTML.
		 * @param array  $cart_item     The cart item array.
		 * @param bool   $cart_item_key The cart item key.
		 *
		 * @since    2.0.1
		 * @return  string  The product title HTML
		 * @use     woocommerce_cart_item_name hook
		 */
		public function show_this_product_as_a_gift_card( $product_title, $cart_item, $cart_item_key = false ) {
			if ( is_array( $cart_item ) && ! empty( $cart_item['ywgc_product_as_present'] ) ) {
				$product_id = ( $cart_item['ywgc_present_variation_id'] ? $cart_item['ywgc_present_variation_id'] : $cart_item['ywgc_present_product_id'] );

				$product_title = "<a href='" . get_permalink( $product_id ) . "' >" . wc_get_product( $product_id )->get_name() . '</a> ' . apply_filters( 'yith_wc_gift_card_as_a_gift_card', esc_html__( 'as a Gift Card', 'yith-woocommerce-gift-cards' ) );
			}

			/**
			 * APPLY_FILTERS: yith_ywgc_cart_product_title
			 *
			 * Filter the gift card product title in the cart.
			 *
			 * @param string $product_title the product title
			 * @param array $cart_item the cart item
			 * @param string $cart_item_key the cart item key
			 *
			 * @return string
			 */
			return apply_filters( 'yith_ywgc_cart_product_title', $product_title, $cart_item, $cart_item_key );
		}

		/**
		 * Allow gift card as a present always, no matter if the gift card is set as purchasable or not
		 *
		 * @param bool       $purchasable Whether the product is purchasable.
		 * @param WC_Product $product     Product object.
		 *
		 * @return bool
		 */
		public function gift_card_is_purchasable( $purchasable, $product ) {
			if ( ( $product instanceof WC_Product_Gift_Card ) && $product->get_id() === intval( YITH_YWGC()->default_gift_card_id ) ) {
				return true;
			}

			if ( function_exists( 'yith_wapo_product_has_blocks' ) && yith_wapo_product_has_blocks( $product->get_id() ) ) {
				return true;
			}

			return $purchasable;
		}

		/**
		 * Verify the gift card value
		 *
		 * @param array  $return_val the returning value.
		 * @param string $code       the gift card code.
		 *
		 * @return array
		 * @since  2.0.4
		 */
		public function verify_coupon_code( $return_val, $code ) {
			$gift_card = YITH_YWGC()->get_gift_card_by_code( $code );

			if ( ! is_object( $gift_card ) ) {
				return $return_val;
			}

			/**
			 * APPLY_FILTERS: ywgc_verify_coupon_code_condition
			 *
			 * Filter the condition to verify the coupon code.
			 *
			 * @param bool true to add a condition, false for not. Default: false
			 * @param array $return_val the returning value of the coupon
			 * @param string $code the gift card code applied as coupon
			 *
			 * @return bool
			 */
			if ( apply_filters( 'ywgc_verify_coupon_code_condition', false, $return_val, $code ) ) {
				return $return_val;
			}

			if ( $gift_card->exists() && get_option( 'ywgc_apply_gc_code_on_gc_product', 'no' ) === 'yes' && is_cart() ) {
				$items = WC()->cart->get_cart();

				foreach ( $items as $cart_item_key => $values ) {
					$product = $values['data'];

					if ( $product->get_type() === 'gift-card' ) {
						wc_add_notice( esc_html__( 'It is not possible to add a gift card code when the cart contains a gift card product', 'yith-woocommerce-gift-cards' ), 'error' );

						return $return_val;
					}
				}
			}

			if ( ! $gift_card instanceof YWGC_Gift_Card_Premium ) {
				return $return_val;
			}

			/**
			 * APPLY_FILTERS: ywgc_get_gift_card_balance_amount_for_coupon
			 *
			 * Filter the gift card balance to apply it as a coupon.
			 *
			 * @param float the gift card balance
			 * @param object $gift_card the gift card object
			 *
			 * @return float
			 */
			$amount = apply_filters( 'ywgc_get_gift_card_balance_amount_for_coupon', $gift_card->get_balance(), $gift_card );

			global $woocommerce_wpml;

			if ( $woocommerce_wpml && $woocommerce_wpml->multi_currency ) {
				$amount = apply_filters( 'wcml_raw_price_amount', $amount );
			}

			if ( $gift_card->ID && $gift_card->get_balance() > 0 && $gift_card->is_enabled() & ! $gift_card->is_expired() ) {
				/**
				 * APPLY_FILTERS: ywgc_temp_coupon_array
				 *
				 * Filter the temporal coupon data, generated with the gift card data.
				 *
				 * @param array the temporal coupon data
				 * @param object $gift_card the gift card object
				 *
				 * @return array
				 */
				return apply_filters(
					'ywgc_temp_coupon_array',
					array(
						'discount_type' => 'fixed_cart',
						'coupon_amount' => $amount,
						'amount'        => $amount,
						'id'            => true,
					),
					$gift_card
				);
			}

			return $return_val;
		}

		/**
		 * Show gift card details in cart page
		 *
		 * @param array $item_data Item data.
		 * @param array $cart_item Cart item.
		 *
		 * @return array
		 */
		public function show_gift_card_details_in_cart( $item_data, $cart_item ) {
			if ( is_cart() || is_checkout() && isset( $cart_item['ywgc_product_id'] ) ) {
				if ( isset( $cart_item['ywgc_recipient_name'] ) && ! empty( $cart_item['ywgc_recipient_name'] ) ) {
					echo wp_kses_post( '<div class="ywgc-details-in-cart-recipient-name" style="margin-top: 5px">' . __( 'Recipient\'s name: ', 'yith-woocommerce-gift-cards' ) . wp_strip_all_tags( preg_replace( '#<script(.*?)>(.*?)</script>#', '', $cart_item['ywgc_recipient_name'] ) ) . '</div>' );
				}

				if ( isset( $cart_item['ywgc_recipients'] ) && ! empty( $cart_item['ywgc_recipients'] ) ) {
					$value = is_array( $cart_item['ywgc_recipients'] ) ? implode( ', ', $cart_item['ywgc_recipients'] ) : $cart_item['ywgc_recipients'];

					if ( ! $value ) {
						$value = esc_html__( 'Your billing email address', 'yith-woocommerce-gift-cards' );
					}

					echo wp_kses_post( '<div class="ywgc-details-in-cart-recipient-email">' . __( 'Recipient\'s email: ', 'yith-woocommerce-gift-cards' ) . wp_strip_all_tags( preg_replace( '#<script(.*?)>(.*?)</script>#', '', $value ) ) . '</div>' );
				}

				if ( isset( $cart_item['ywgc_sender_name'] ) && ! empty( $cart_item['ywgc_sender_name'] ) ) {
					echo wp_kses_post( '<div class="ywgc-details-in-cart-sender-name">' . __( 'Your name: ', 'yith-woocommerce-gift-cards' ) . wp_strip_all_tags( preg_replace( '#<script(.*?)>(.*?)</script>#', '', $cart_item['ywgc_sender_name'] ) ) . '</div>' );
				}

				if ( isset( $cart_item['ywgc_message'] ) && ! empty( $cart_item['ywgc_message'] ) ) {
					echo wp_kses_post( '<div class="ywgc-details-in-cart-message">' . __( 'Message: ', 'yith-woocommerce-gift-cards' ) . nl2br( stripslashes( wp_strip_all_tags( preg_replace( '#<script(.*?)>(.*?)</script>#', '', $cart_item['ywgc_message'] ) ) ) ) . '</div>' );
				}

				if ( isset( $cart_item['ywgc_delivery_date'] ) && ! empty( $cart_item['ywgc_delivery_date'] ) ) {
					$date_format = apply_filters( 'yith_wcgc_date_format', 'Y-m-d' );
					$date        = date_i18n( $date_format, $cart_item['ywgc_delivery_date'] );

					echo wp_kses_post( '<div class="ywgc-details-in-cart-delivery-date">' . __( 'Delivery date: ', 'yith-woocommerce-gift-cards' ) . $date . '</div>' );
				}
			}

			return $item_data;
		}

		/**
		 * Modify the PayPal arguments to avoid issues when a gift card is applied
		 *
		 * @param array    $args  Array of arguments.
		 * @param WC_Order $order Order object.
		 *
		 * @return array
		 */
		public function ywgc_modify_paypal_args( $args, $order ) {
			if ( $order->get_total() > $order->get_shipping_total() ) {
				return $args;
			}

			$gift_cards_applied       = $order->get_meta( '_ywgc_applied_gift_cards' );
			$total_coupons_amount     = 0;
			$total_coupons_amount_tax = 0;

			$order_coupons = $order->get_coupons();

			foreach ( $order_coupons as $coupon ) {
				$coupon_code = $coupon->get_code();

				$coupon_codes_array[] = $coupon_code;

				$gift = YITH_YWGC()->get_gift_card_by_code( $coupon_code );

				if ( is_object( $gift ) && $gift->exists() ) {
					$total_coupons_amount     += $coupon->get_discount();
					$total_coupons_amount_tax += $coupon->get_discount_tax();
				} else {
					continue;
				}
			}

			$total_applied_as_default_coupon = $total_coupons_amount + $total_coupons_amount_tax;

			if ( ! $gift_cards_applied && ! $total_applied_as_default_coupon > 0 ) {
				return $args;
			}

			if ( $total_applied_as_default_coupon > 0 ) {
				$discount_amount_cart = $total_applied_as_default_coupon;
			}

			if ( $gift_cards_applied ) {
				$discount_amount_cart = $order->get_meta( '_ywgc_applied_gift_cards' );
			}

			if ( empty( $discount_amount_cart ) ) {
				return $args;
			}

			$item_total = 0;

			foreach ( $args as $key => $value ) {
				if ( strpos( $key, 'amount_' ) === 0 ) {
					$index       = str_replace( 'amount_', '', $key );
					$qty         = ( ! empty( $args[ 'quantity_' . $index ] ) ) ? $args[ 'quantity_' . $index ] : 1;
					$item_total += ( $value * $qty );

					$args[ 'shipping_' . $index ] += $item_total;
					$args[ 'amount_' . $index ]   -= $item_total;
				}
			}

			return $args;
		}

		/**
		 * Prevent physical and virtual gift cards to be added in the same cart
		 *
		 * @param bool $allow        Whether to allow physical and virtual gift cards in the same cart.
		 * @param int  $product_id   Product ID.
		 * @param int  $quantity     Product quantity.
		 * @param int  $variation_id Variation ID.
		 *
		 * @return bool
		 */
		public function prevent_gift_card_and_physical_products_in_the_same_cart( $allow, $product_id, $quantity, $variation_id = '' ) {
			if ( 'yes' === get_option( 'ywgc_prevent_virtual_gift_card_and_physical_products_in_same_order', false ) ) {
				$product_id = '' !== $variation_id ? $variation_id : $product_id;

				$added_product = wc_get_product( $product_id );

				$contents = WC()->cart->cart_contents;

				if ( ! empty( $contents ) ) {
					foreach ( $contents as $item_key => $cart_item ) {
						if ( ( isset( $cart_item['ywgc_is_digital'] ) && true === $cart_item['ywgc_is_digital'] && ! $added_product->is_virtual() ) ) {
							$allow = false;
							break;
						}
					}
				}

				if ( false === $allow ) {
					wc_add_notice( esc_html__( 'You can\'t purchase a physical product and a digital gift card with the same order', 'yith-woocommerce-gift-cards' ), 'error' );
				}
			}

			return $allow;
		}

		/**
		 * Move an uploaded file into a persistent folder with a unique name
		 *
		 * @param string $image uploaded image.
		 *
		 * @return string|void real path of the uploaded image
		 */
		public function save_uploaded_file( $image ) {
			// Create folders for storing documents.
			$date     = getdate();
			$folder   = sprintf( '%s/%s', $date['year'], $date['mon'] );
			$filename = $image['name'];

			while ( true ) {
				$relative_path = sprintf( '%s/%s', $folder, $filename );
				$dir_path      = sprintf( '%s/%s', YITH_YWGC_SAVE_DIR, $folder );
				$full_path     = sprintf( '%s/%s', YITH_YWGC_SAVE_DIR, $relative_path );

				if ( ! file_exists( $full_path ) ) {
					if ( ! file_exists( $dir_path ) ) {
						wp_mkdir_p( $dir_path );
					}

					move_uploaded_file( $image['tmp_name'], $full_path );

					return $relative_path;
				} else {
					$name_without_ext = pathinfo( $filename, PATHINFO_FILENAME );
					$ext              = pathinfo( $filename, PATHINFO_EXTENSION );
					$filename         = $name_without_ext . wp_rand() . '.' . $ext;
				}
			}
		}

		/**
		 * Manage the request from an email for a gift card code to be applied to the cart
		 */
		public function check_email_discount() {
			$actions = array();

			/**
			 *
			 * Old version support from version 1.8.6 on
			 * after some versions we could remove the following way of retrieving the
			 * 'add-discount' and 'verify-code'
			 */

			/*************** START OLD VERSION VAR RETRIEVES */

			if ( isset( $_GET['add-discount'] ) && isset( $_GET['verify-code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$actions = array(
					'add_discount'      => sanitize_text_field( wp_unslash( $_GET['add-discount'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'verify_code'       => sanitize_text_field( wp_unslash( $_GET['verify-code'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'product_id'        => ( isset( $_GET[ YWGC_ACTION_PRODUCT_ID ] ) ? intval( $_GET[ YWGC_ACTION_PRODUCT_ID ] ) : get_option( YWGC_PRODUCT_PLACEHOLDER ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'gift_this_product' => 'yes',
				);
			}

			/*************** END OLD VERSION VAR RETRIEVES */

			if ( isset( $_GET[ YWGC_ACTION_ADD_DISCOUNT_TO_CART ] ) && isset( $_GET[ YWGC_ACTION_VERIFY_CODE ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$actions = array(
					'add_discount'      => sanitize_text_field( wp_unslash( $_GET[ YWGC_ACTION_ADD_DISCOUNT_TO_CART ] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'verify_code'       => sanitize_text_field( wp_unslash( $_GET[ YWGC_ACTION_VERIFY_CODE ] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'product_id'        => ( isset( $_GET[ YWGC_ACTION_PRODUCT_ID ] ) ? intval( $_GET[ YWGC_ACTION_PRODUCT_ID ] ) : get_option( YWGC_PRODUCT_PLACEHOLDER ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'gift_this_product' => ( isset( $_GET[ YWGC_ACTION_GIFT_THIS_PRODUCT ] ) ? sanitize_text_field( wp_unslash( $_GET[ YWGC_ACTION_GIFT_THIS_PRODUCT ] ) ) : 'no' ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				);
			}

			if ( is_array( $actions ) && ! empty( $actions ) && apply_filters( 'yith_ywgc_actions_from_email_url', true, $actions['product_id'] ) ) {
				/**
				 *
				 * We add the product to the cart directly so in case the browser of the user never has used the site
				 * we create the cart session. If the admin does not want to add the product directly to the cart
				 * we remove it
				 */
				if ( 'yes' === get_option( 'ywgc_gift_this_product_add_to_cart', 'yes' ) && get_option( YWGC_PRODUCT_PLACEHOLDER ) !== $actions['product_id'] && '' !== $actions['product_id'] ) {
					WC()->cart->add_to_cart( $actions['product_id'] );
				}

				if ( 'yes' !== get_option( 'ywgc_gift_this_product_add_to_cart', 'yes' ) || 'no' === $actions['gift_this_product'] ) {
					$items = WC()->cart->get_cart();

					foreach ( $items as $cart_item_key => $values ) {
						if ( $values['product_id'] === $actions['product_id'] ) {
							WC()->cart->remove_cart_item( $cart_item_key );
						}
					}
				}

				if ( 'no' !== ( get_option( 'ywgc_auto_discount' ) && 'no' === $actions['gift_this_product'] ) || ( 'no' !== get_option( 'ywgc_gift_this_product_apply_gift_card', 'yes' ) && 'yes' === $actions['gift_this_product'] ) ) {
					$gift = YITH_YWGC()->get_gift_card_by_code( $actions['add_discount'] );

					if ( is_object( $gift ) && $gift->can_be_used() ) {

						$applied_gift_cards = array();

						if ( isset( WC()->session ) ) {
							$applied_gift_cards = WC()->session->get( 'applied_gift_cards', array() );
						}

						$key = array_search( $gift->get_code(), $applied_gift_cards, true );

						// If the gift card code is already applied, redirect to the page without args.
						if ( false !== $key ) {
							return;
						}

						// Check the hash value and compare with the one provided.
						$hash_value = YITH_YWGC()->hash_gift_card( $gift );

						if ( $hash_value === $actions['verify_code'] ) {
							// can add the discount to the cart.
							if ( YITH_YWGC()->check_gift_card( $gift ) ) {
								if ( ! WC()->session->has_session() ) {
									WC()->session->set_customer_session_cookie( true );
								}

								$this->add_gift_card_code_to_session( $gift->get_code() );
								wc_add_notice( $gift->get_gift_card_message( YITH_YWGC_Gift_Card::GIFT_CARD_SUCCESS ) );
							}
						}
					}
				}
			}

			unset( $_GET['add-discount'] );
			unset( $_GET[ YWGC_ACTION_ADD_DISCOUNT_TO_CART ] );
		}

		/**
		 * Remove the "Order again" button if the order contains gift cards
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @since 3.1.14
		 *
		 * @return void
		 */
		public function ywgc_remove_order_again_button_gift_this_product( $order ) {
			if ( ! $order instanceof WC_Order ) {
				return;
			}

			$order_items         = $order->get_items();
			$product_types_array = array();

			foreach ( $order_items as $item_id => $item ) {
				$product = wc_get_product( $item['product_id'] );

				if ( $product && $product instanceof WC_Product ) {
					$product_types_array[] = $product->get_type();
				}
			}

			if ( in_array( 'gift-card', $product_types_array, true ) ) {
				remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );
			}
		}

		/**
		 * Add_gift_this_product_as_on_wc_thankyou_page
		 *
		 * @param  mixed $order_item_name Order item name.
		 * @param  mixed $order_item order_item.
		 * @return string
		 */
		public function add_gift_this_product_as_on_wc_thankyou_page( $order_item_name, $order_item ) {
			$product_id = ( $order_item['ywgc_present_variation_id'] ? $order_item['ywgc_present_variation_id'] : $order_item['ywgc_present_product_id'] );

			$order_item_name = ! empty( $product_id ) ? wc_get_product( $product_id )->get_name() . esc_html__( ' as a Gift Card', 'yith-woocommerce-gift-cards' ) : $order_item_name;

			return $order_item_name;
		}

		/**
		 * Add the product purchased as a gift cart tag when the product is removed from the cart.
		 *
		 * @param string $cart_item_name Cart item name.
		 * @param array  $cart_item Cart item.
		 *
		 * @return string
		 */
		public function add_gift_this_product_product_removed_from_cart( $cart_item_name, $cart_item ) {
			if ( ! isset( $cart_item['ywgc_present_variation_id'] ) || ! isset( $cart_item['ywgc_present_product_id'] ) ) {
				return $cart_item_name;
			}

			$product_id = ( $cart_item['ywgc_present_variation_id'] ? $cart_item['ywgc_present_variation_id'] : $cart_item['ywgc_present_product_id'] );

			// translators: %s is the name of the product purchased as a gift card removed from the cart.
			$cart_item_name = ! empty( $product_id ) ? sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'yith-woocommerce-gift-cards' ), wc_get_product( $product_id )->get_name() . esc_html__( ' as a Gift Card', 'yith-woocommerce-gift-cards' ) ) : $cart_item_name;

			return $cart_item_name;
		}

		/**
		 * Deduct an amount from the gift card balance
		 *
		 * @param int    $id                  the order id.
		 * @param int    $item_id             the item id.
		 * @param string $code                the gift card code.
		 * @param float  $discount_amount     the amount to deduct.
		 * @param float  $discount_amount_tax the tax amount to deduct.
		 *
		 * @since  2.0.4
		 */
		public function deduct_amount_from_gift_card( $id, $item_id, $code, $discount_amount, $discount_amount_tax ) {
			$gift = YITH_YWGC()->get_gift_card_by_code( $code );

			if ( ! is_object( $gift ) ) {
				return;
			}

			if ( get_option( 'woocommerce_tax_display_cart' ) === 'excl' ) {
				$total_discount_amount_aux = $discount_amount;
			} else {
				$total_discount_amount_aux = $discount_amount + $discount_amount_tax;
			}

			/**
			 * APPLY_FILTERS: ywgc_amount_to_deduct_in_gift_card_from_coupon
			 *
			 * Filter the amount to deduct in the gift cards when it's applied as coupon.
			 *
			 * @param float $total_discount_amount_aux the discount amount
			 * @param object $gift the gift card object
			 *
			 * @return float
			 */
			$total_discount_amount = apply_filters( 'ywgc_amount_to_deduct_in_gift_card_from_coupon', $total_discount_amount_aux, $gift );

			global $woocommerce_wpml;

			if ( $woocommerce_wpml && $woocommerce_wpml->multi_currency && class_exists( 'YWGC_WPML' ) ) {
				$total_discount_amount = YWGC_WPML::get_instance()->convert_to_base_currency( $total_discount_amount );
			}

			if ( $gift instanceof YWGC_Gift_Card_Premium ) {
				$new_balance = $gift->get_balance() - $total_discount_amount;

				$new_balance = $new_balance < 0 ? 0 : $new_balance;

				/**
				 * APPLY_FILTERS: yith_ywgc_new_balance_before_update_balance
				 *
				 * Filter the new balance of the gift card applied as coupon.
				 *
				 * @param float the new balance
				 * @param object $gift the gift card object
				 * @param float $total_discount_amount the discount amount
				 *
				 * @return float
				 */
				$new_balance = apply_filters( 'yith_ywgc_new_balance_before_update_balance', max( 0.00, $new_balance ), $gift, $total_discount_amount );

				$gift->update_balance( $new_balance );
				$gift->register_order( $id );
				$this->notify_customer_if_gift_cards_used( $gift );
			}
		}

		/**
		 * Notify the customer if a gift cards he bought is used
		 *
		 * @param YITH_YWGC_Gift_Card $gift_card Gift card object.
		 *
		 * @since  1.0.0
		 */
		public function notify_customer_if_gift_cards_used( $gift_card ) {
			$notify_customer_email_settings = get_option( 'woocommerce_ywgc-email-notify-customer_settings' );

			if ( isset( $notify_customer_email_settings['enabled'] ) && 'yes' === $notify_customer_email_settings['enabled'] ) {
				if ( $gift_card->exists() ) {
					WC()->mailer();

					/**
					 * DO_ACTION: ywgc_email_notify_customer_notification
					 *
					 * Trigger the customer notification email.
					 *
					 * @param object $gift_card the gift card object
					 */
					do_action( 'ywgc_email_notify_customer_notification', $gift_card );
				}
			}
		}

		/**
		 * Build cart item meta to pass to add_to_cart when adding a gift card to the cart
		 *
		 * @since 1.5.0
		 */
		public function build_cart_item_data() {

			$cart_item_data = array();

			$product_as_present = isset( $_POST['ywgc-as-present'] ) && ( 'yes' === $_POST['ywgc-as-present'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			/**
			 * Check if the current gift card has a manually entered amount set
			 */
			$ywgc_is_manual_amount = isset( $_REQUEST['ywgc-manual-amount'] ) && ! isset( $_REQUEST['gift_amounts'] ) && ( floatval( $_REQUEST['ywgc-manual-amount'] ) > 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$ywgc_is_manual_amount = wc_format_decimal( $ywgc_is_manual_amount );

			/**
			 * Check if the current gift card has a prefixed amount set
			 */
			$ywgc_is_preset_amount = ! $ywgc_is_manual_amount && isset( $_REQUEST['gift_amounts'] ) && ( floatval( $_REQUEST['gift_amounts'] ) > 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$ywgc_is_preset_amount = wc_format_decimal( $ywgc_is_preset_amount );

			$has_addons = false;

			if ( function_exists( 'yith_wapo_product_has_blocks' ) && isset( $_REQUEST['product_id'] ) && yith_wapo_product_has_blocks( absint( $_REQUEST['product_id'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$has_addons = true;
			}

			/**
			 * Neither manual or fixed? Something wrong happened!
			 */

			/**
			 * APPLY_FILTERS: yith_ywgc_allow_zero_gift_cards
			 *
			 * Filter the condition to allow to purchase gift cards with zero value.
			 *
			 * @param bool true to not allow it and show an error message, false to allow it. Default: true
			 *
			 * @return bool
			 */
			if ( ! $product_as_present && ! $ywgc_is_manual_amount && ! $ywgc_is_preset_amount && apply_filters( 'yith_ywgc_allow_zero_gift_cards', true ) && ! $has_addons ) {
				wp_die( esc_html__( 'The gift card has invalid amount', 'yith-woocommerce-gift-cards' ) );
			}

			/**
			 * Manage the design selected
			 */
			$gift_card_design = - 1;
			$design_type      = isset( $_POST['ywgc-design-type'] ) ? sanitize_text_field( wp_unslash( $_POST['ywgc-design-type'] ) ) : 'default'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( 'custom' === $design_type ) {
				/**
				 * The user has uploaded a file
				 */
				if ( isset( $_FILES['ywgc-upload-picture'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$custom_image = $_FILES['ywgc-upload-picture']; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

					// check uploaded file to see if ti matches requirements.
					if ( empty( $custom_image['name'] ) ) {
						wc_add_notice( _x( 'There was an error with the image file you uploaded; please, try again', 'upload image error message', 'yith-woocommerce-gift-cards' ), 'error' );
						return false;
					}

					if ( ! empty( $custom_image['error'] ) ) {
						// translators: %s is the error produced when trying to upload a custom image.
						wc_add_notice( sprintf( _x( 'There was an error with upload: %s', 'upload image error message', 'yith-woocommerce-gift-cards' ), $custom_image['error'] ), 'error' );
						return false;
					}

					$file_name = sanitize_file_name( strtolower( $custom_image['name'] ) );

					/**
					 * APPLY_FILTERS: yith_ywgc_upload_allowed_extensions
					 *
					 * Filter the allowed upload extensions for the custom images.
					 *
					 * @param array the allowed extensions. Default: 'jpeg', 'jpg', 'png'
					 *
					 * @return array
					 */
					$allowed_ext_array = apply_filters( 'yith_ywgc_upload_allowed_extensions', array( 'jpeg', 'jpg', 'png' ) );
					$file_ext          = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );

					if ( ! empty( $allowed_ext_array ) && ( ! in_array( $file_ext, $allowed_ext_array, true ) ) ) {
						wc_add_notice( _x( 'The image file you selected has an invalid extension; please, choose another file', 'upload image error message', 'yith-woocommerce-gift-cards' ), 'error' );
						return false;
					}

					if ( isset( $custom_image['type'] ) && substr( $custom_image['type'], 0, 6 ) !== 'image/' ) {
						wc_add_notice( _x( 'The file type you have  selected is not valid; please, choose another file', 'upload image error message', 'yith-woocommerce-gift-cards' ), 'error' );
						return false;
					}

					$max_size_byte = empty( get_option( 'ywgc_custom_image_max_size', 1 ) ) ? false : get_option( 'ywgc_custom_image_max_size', 1 ) * 1024 * 1024;

					if ( $max_size_byte && $custom_image['size'] > $max_size_byte ) {
						wc_add_notice( _x( 'The image file you selected is too big; please, choose another file', 'upload image error message', 'yith-woocommerce-gift-cards' ), 'error' );
						return false;
					}

					if ( isset( $custom_image['name'] ) ) {
						$temp = explode( '.', $custom_image['name'] );

						if ( isset( $temp['1'] ) && ( in_array( strtolower( $temp['1'] ), $allowed_ext_array, true ) ) ) {
							$custom_image['name'] = round( microtime( true ) ) . '.' . end( $temp );
						} else {
							wc_add_notice( _x( 'The image file you selected has an invalid extension; please, choose another file', 'upload image error message', 'yith-woocommerce-gift-cards' ), 'error' );
							return false;
						}
					}

					if ( isset( $custom_image['tmp_name'] ) && ( 0 === $custom_image['error'] ) ) {
						$gift_card_design = $this->save_uploaded_file( $custom_image );
					}
				}
			} elseif ( 'template' === $design_type ) {
				if ( isset( $_POST['ywgc-template-design'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$gift_card_design = sanitize_text_field( wp_unslash( $_POST['ywgc-template-design'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				}
			} elseif ( 'custom-modal' === $design_type ) {
				if ( isset( $_POST['ywgc-custom-modal-design'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$gift_card_design = $_POST['ywgc-custom-modal-design']; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				}
			}

			/**
			 * Check if it is a physical gift card
			 */
			$ywgc_is_physical = isset( $_REQUEST['ywgc-is-physical'] ) && boolval( $_REQUEST['ywgc-is-physical'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $ywgc_is_physical ) {
				/**
				 * Retrieve sender name
				 */
				$sender_name = isset( $_REQUEST['ywgc-sender-name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywgc-sender-name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Recipient name
				 */
				$recipient_name = isset( $_REQUEST['ywgc-recipient-name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywgc-recipient-name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Retrieve the sender message
				 */
				$sender_message = isset( $_REQUEST['ywgc-edit-message'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['ywgc-edit-message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			/**
			 * Check if it is a digital gift card
			 */
			$ywgc_is_digital = isset( $_REQUEST['ywgc-is-digital'] ) && boolval( $_REQUEST['ywgc-is-digital'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $ywgc_is_digital ) {
				/**
				 * Retrieve gift card recipient
				 */
				$recipients = isset( $_REQUEST['ywgc-recipient-email'] ) ? array_map( 'sanitize_email', wp_unslash( $_REQUEST['ywgc-recipient-email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Retrieve sender name
				 */
				$sender_name = isset( $_REQUEST['ywgc-sender-name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywgc-sender-name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Recipient name
				 */
				$recipient_name = isset( $_REQUEST['ywgc-recipient-name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['ywgc-recipient-name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Retrieve the sender message
				 */
				$sender_message = isset( $_REQUEST['ywgc-edit-message'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['ywgc-edit-message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/**
				 * Gift card should be delivered on a specific date?
				 */
				$delivery_date = isset( $_REQUEST['ywgc-delivery-date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywgc-delivery-date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( '' !== $delivery_date && is_string( $delivery_date ) && ! is_bool( $delivery_date ) ) {
					$saved_format = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );

					if ( 'MM d, yy' === $saved_format ) {
						$delivery_date = strtotime( $delivery_date );
					} else {
						$search  = array( '.', ', ', '/', ',', 'MM', 'yy', 'mm', 'dd' );
						$replace = array( '-', '-', '-', '-', 'M', 'Y', 'm', 'd' );

						$date_formatted         = str_replace( $search, $replace, $delivery_date );
						$saved_format_formatted = str_replace( $search, $replace, $saved_format ) . ' H:i P';

						$delivery_date_from_format = DateTime::createFromFormat( $saved_format_formatted, $date_formatted );

						if ( $delivery_date_from_format ) {
							$delivery_date = $delivery_date_from_format->getTimestamp();
						}
					}
				}

				$postdated = '' !== $delivery_date ? true : false;

				$delivery_notification_enabled = isset( $_POST['ywgc-delivery-notification-checkbox'] ) ? sanitize_text_field( wp_unslash( $_POST['ywgc-delivery-notification-checkbox'] ) ) : 'off'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( 'on' === $delivery_notification_enabled ) {
					$cart_item_data['ywgc_delivery_notification_checkbox'] = $delivery_notification_enabled;
				}
			}

			if ( $product_as_present ) {
				$cart_item_data['ywgc_product_id'] = YITH_YWGC()->default_gift_card_id;

				$present_product_id   = absint( $_POST['ywgc-gifted-product-id'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$present_variation_id = 0;

				$product = wc_get_product( $present_product_id );

				if ( isset( $_POST['ywgc-gift-this-product-total-value'] ) && isset( $_POST['ywgc-include-shipping-checkbox'] ) && 'on' === $_POST['ywgc-include-shipping-checkbox'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$ywgc_amount = sanitize_text_field( wp_unslash( $_POST['ywgc-gift-this-product-total-value'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
					if ( $product && $product->is_type( 'booking' ) &&
						class_exists( 'YITH_WCBK_Cart' ) && is_callable( 'YITH_WCBK_Cart::get_booking_data_from_request' )
						&& is_callable( array( $product, 'calculate_price' ) )
					) {
						// TODO Use the booking data to include it in the gift card.
						$booking_data = YITH_WCBK_Cart::get_booking_data_from_request();
						$ywgc_amount  = isset( $_POST['ywgc-manual-amount'] ) ? sanitize_text_field( wp_unslash( $_POST['ywgc-manual-amount'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					} else {
						$ywgc_amount = isset( $_POST['ywgc-manual-amount'] ) ? sanitize_text_field( wp_unslash( $_POST['ywgc-manual-amount'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
					}
				}

				/**
				 * APPLY_FILTERS: yith_ywgc_submitting_as_present_amount
				 *
				 * Filter the gift card amount created with the "Gift this product".
				 *
				 * @param string $ywgc_amount the amount to create the gift card
				 * @param object $product the product object
				 *
				 * @return string
				 */
				$ywgc_amount = apply_filters( 'yith_ywgc_submitting_as_present_amount', $ywgc_amount, $product );

				$cart_item_data['ywgc_product_as_present']   = $product_as_present;
				$cart_item_data['ywgc_present_product_id']   = $present_product_id;
				$cart_item_data['ywgc_present_variation_id'] = $present_variation_id;
			} else {
				if ( isset( $_POST['add-to-cart'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$cart_item_data['ywgc_product_id'] = absint( $_POST['add-to-cart'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				} elseif ( isset( $_REQUEST['ywgc_product_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$cart_item_data['ywgc_product_id'] = absint( $_REQUEST['ywgc_product_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}

				/**
				 * Set the gift card amount
				 */
				$product = wc_get_product( $cart_item_data['ywgc_product_id'] );

				$on_sale_value = get_post_meta( $cart_item_data['ywgc_product_id'], '_ywgc_sale_discount_value', true );
				$on_sale_value = str_replace( ',', '.', $on_sale_value );
				$on_sale       = is_object( $product ) ? $product->get_add_discount_settings_status() : false;

				if ( $ywgc_is_manual_amount ) {
					$ywgc_amount = sanitize_text_field( wp_unslash( str_replace( ',', '.', sanitize_text_field( wp_unslash( $_REQUEST['ywgc-manual-amount'] ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					/**
					 * APPLY_FILTERS: yith_ywgc_submitting_manual_amount_with_discount
					 *
					 * Filter the condition to allow discounts on the custom amount added by the customer.
					 *
					 * @param bool true to allow it, false for not. Default: true
					 *
					 * @return bool
					 */
					if ( apply_filters( 'yith_ywgc_submitting_manual_amount_with_discount', true ) && $on_sale && $on_sale_value ) {
						/**
						 * APPLY_FILTERS: ywgc_amount_without_discount_value
						 *
						 * Filter the gift card amount without discount saved in the gift card data.
						 *
						 * @param float $ywgc_amount the amount to create the gift card
						 *
						 * @return float
						 */
						$cart_item_data['ywgc_amount_without_discount'] = apply_filters( 'ywgc_amount_without_discount_value', $ywgc_amount );

						/**
						 * APPLY_FILTERS: yith_ywgc_discount_value
						 *
						 * Filter the gift card discount value.
						 *
						 * @param float the discount amount
						 * @param float $ywgc_amount the default amount
						 * @param float $on_sale_value the percentage discount value
						 *
						 * @return float
						 */
						$discount = apply_filters( 'yith_ywgc_discount_value', ( $ywgc_amount * (float) $on_sale_value ) / 100, $ywgc_amount, $on_sale_value );

						/**
						 * APPLY_FILTERS: yith_ywgc_submitting_manual_amount
						 *
						 * Filter the final manual amount to create the gift card on the cart.
						 *
						 * @param float the gift card amount
						 *
						 * @return float
						 */
						$ywgc_amount = apply_filters( 'yith_ywgc_submitting_manual_amount', $ywgc_amount - $discount );
					} else {
						$ywgc_amount = apply_filters( 'yith_ywgc_submitting_manual_amount', $ywgc_amount );
					}
				} else {
					$ywgc_amount = sanitize_text_field( wp_unslash( $_REQUEST['gift_amounts'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					if ( $on_sale && $on_sale_value ) {
						$cart_item_data['ywgc_amount_without_discount'] = apply_filters( 'ywgc_amount_without_discount_value', $ywgc_amount );

						$discount    = apply_filters( 'yith_ywgc_discount_value', ( $ywgc_amount * (float) $on_sale_value ) / 100, $ywgc_amount, $on_sale_value );
						$ywgc_amount = $ywgc_amount - $discount;
					} else {
						/**
						 * APPLY_FILTERS: yith_ywgc_submitting_select_amount
						 *
						 * Filter the final selected amount to create the gift card on the cart.
						 *
						 * @param float $ywgc_amount the gift card amount
						 * @param object $product the gift card amount
						 *
						 * @return float
						 */
						$ywgc_amount = apply_filters( 'yith_ywgc_submitting_select_amount', $ywgc_amount, $product );
					}
				}
			}

			$cart_item_data['ywgc_amount']           = $ywgc_amount;
			$cart_item_data['ywgc_is_manual_amount'] = $ywgc_is_manual_amount;
			$cart_item_data['ywgc_is_digital']       = $ywgc_is_digital;
			$cart_item_data['ywgc_is_physical']      = $ywgc_is_physical;

			/**
			 * Retrieve the gift card recipient, if digital
			 */
			if ( $ywgc_is_digital ) {
				$cart_item_data['ywgc_recipients']     = $recipients;
				$cart_item_data['ywgc_sender_name']    = $sender_name;
				$cart_item_data['ywgc_recipient_name'] = $recipient_name;
				$cart_item_data['ywgc_message']        = apply_filters( 'ywgc_message_cart_item_data', $sender_message );
				$cart_item_data['ywgc_postdated']      = $postdated;

				if ( $postdated ) {
					/**
					 * APPLY_FILTERS: ywgc_save_delivery_date_cart_item_data
					 *
					 * Filter the delivery date in the cart item data.
					 *
					 * @param string the delivery date including the gmt_offset
					 * @param string $delivery_date the delivery date
					 *
					 * @return string
					 */

					$cart_item_data['ywgc_delivery_date'] = apply_filters( 'ywgc_save_delivery_date_cart_item_data', $delivery_date, $delivery_date );
				}
			}

			if ( $ywgc_is_physical ) {
				$cart_item_data['ywgc_recipient_name'] = $recipient_name;
				$cart_item_data['ywgc_sender_name']    = $sender_name;
				$cart_item_data['ywgc_message']        = apply_filters( 'ywgc_message_cart_item_data', $sender_message );
			}

			$cart_item_data['ywgc_design_type']       = $design_type;
			$cart_item_data['ywgc_has_custom_design'] = '-1' !== $gift_card_design;

			if ( $gift_card_design ) {
				$cart_item_data['ywgc_design'] = $gift_card_design;
			}

			global $woocommerce_wpml;

			if ( $woocommerce_wpml && $woocommerce_wpml->multi_currency ) {
				$currency = $woocommerce_wpml->multi_currency->get_client_currency();

				$cart_item_data['ywgc_currency'] = $currency;

				$cart_item_data['ywgc_default_currency_amount'] = $woocommerce_wpml->multi_currency->prices->unconvert_price_amount( $ywgc_amount );
			}

			if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) ) {

				$currency = get_woocommerce_currency();

				$cart_item_data['ywgc_currency'] = $currency;
			}

			if ( defined( 'WOOCOMMERCE_MULTICURRENCY_VERSION' ) ) {
				$currency_detector = new \WOOMC\Currency\Detector();

				$conversion_api = new \WOOMC\API();

				$from = $currency_detector->currency();
				$to   = $currency_detector->getDefaultCurrency();

				$cart_item_data['ywgc_currency'] = $from;

				$cart_item_data['ywgc_default_currency_amount'] = $conversion_api->convert( $ywgc_amount, $to, $from );
			}

			if ( defined( 'YITH_WCMCS_INIT' ) && 'gift-card' === $product->get_type() ) {
				$currency_id         = isset( $_POST['yith_wcmcs_amounts_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['yith_wcmcs_amounts_currency'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$default_currency_id = function_exists( 'yith_wcmcs_get_wc_currency_options' ) ? yith_wcmcs_get_wc_currency_options( 'currency' ) : get_woocommerce_currency();

				if ( $currency_id === $default_currency_id || '' === $currency_id ) {
					$amounts_in_currency                 = $product->get_product_amounts();
					$amount_formatted                    = str_replace( '.', ',', $ywgc_amount );
					$cart_item_data['ywgc_amount_index'] = array_search( $amount_formatted, $amounts_in_currency, true );
				} else {
					$amounts_in_currency                 = $product->get_product_amounts_by_currency( $currency_id );
					$amount_formatted                    = str_replace( '.', ',', $ywgc_amount );
					$cart_item_data['ywgc_amount_index'] = array_search( $amount_formatted, $amounts_in_currency, true );
				}
			}

			return $cart_item_data;
		}

		/**
		 * Custom add_to_cart handler for gift card product type
		 */
		public function add_to_cart_handler() {
			$item_data  = $this->build_cart_item_data();
			$product_id = $item_data['ywgc_product_id'];

			$adding_to_cart = wc_get_product( $product_id );

			if ( ! $product_id ) {
				wc_add_notice( esc_html__( 'An error occurred while adding the product to the cart.', 'yith-woocommerce-gift-cards' ), 'error' );

				return false;
			}

			$added_to_cart = false;

			if ( $item_data['ywgc_is_digital'] ) {
				$recipients = $item_data['ywgc_recipients'];

				/**
				 * Check if all mandatory fields are filled or throw an error
				 */
				if ( YITH_YWGC()->mandatory_recipient() && is_array( $recipients ) && ! count( $recipients ) ) {
					wc_add_notice( esc_html__( 'Add a valid email address for the recipient', 'yith-woocommerce-gift-cards' ), 'error' );

					return false;
				}

				/**
				 * Validate all email addresses submitted
				 */
				$email_error = '';

				if ( YITH_YWGC()->mandatory_recipient() && $recipients ) {
					foreach ( $recipients as $recipient ) {
						if ( YITH_YWGC()->mandatory_recipient() && empty( $recipient ) ) {
							wc_add_notice( esc_html__( 'The recipient(s) email address is mandatory', 'yith-woocommerce-gift-cards' ), 'error' );

							return false;
						}

						if ( $recipient && ! sanitize_email( filter_var( $recipient, FILTER_VALIDATE_EMAIL ) ) ) {
							$email_error .= '<br>' . sanitize_email( filter_var( $recipient, FILTER_VALIDATE_EMAIL ) );
						}
					}

					if ( $email_error ) {
						wc_add_notice( esc_html__( 'Email address not valid, please check the following: ', 'yith-woocommerce-gift-cards' ) . $email_error, 'error' );

						return false;
					}
				}

				/** The user can purchase 1 gift card with multiple recipient emails or [quantity] gift card for the same user.
				 * It's not possible to mix both, purchasing multiple instance of gift card with multiple recipients
				 * */
				$recipient_count = is_array( $item_data['ywgc_recipients'] ) ? count( $item_data['ywgc_recipients'] ) : 0;
				$quantity        = ( $recipient_count > 1 ) ? $recipient_count : ( isset( $_REQUEST['quantity'] ) ? intval( $_REQUEST['quantity'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( $recipient_count > 1 ) {
					$item_data_to_card = $item_data;

					for ( $i = 0; $i < $recipient_count; $i++ ) {
						$item_data_to_card['ywgc_recipients']     = array( $item_data['ywgc_recipients'][ $i ] );
						$item_data_to_card['ywgc_recipient_name'] = $item_data['ywgc_recipient_name'][ $i ];

						$added_to_cart = WC()->cart->add_to_cart( $product_id, 1, 0, array(), $item_data_to_card );
					}
				} else {
					$item_data['ywgc_recipient_name'] = is_array( $item_data['ywgc_recipient_name'] ) ? $item_data['ywgc_recipient_name'][0] : $item_data['ywgc_recipient_name'];
					$added_to_cart                    = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $item_data );
				}
			} elseif ( $item_data['ywgc_is_physical'] ) {
				/** The user can purchase 1 gift card with multiple recipient names or [quantity] gift card for the same user.
				 * It's not possible to mix both, purchasing multiple instance of gift card with multiple recipients
				 * */

				$recipient_name_count = is_array( $item_data['ywgc_recipient_name'] ) ? count( $item_data['ywgc_recipient_name'] ) : 0;
				$quantity             = ( $recipient_name_count > 1 ) ? $recipient_name_count : ( isset( $_REQUEST['quantity'] ) ? intval( $_REQUEST['quantity'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( $recipient_name_count > 1 ) {
					$item_data_to_card = $item_data;

					for ( $i = 0; $i < $recipient_name_count; $i++ ) {
						$item_data_to_card['ywgc_recipient_name'] = $item_data['ywgc_recipient_name'][ $i ];

						$added_to_cart = WC()->cart->add_to_cart( $product_id, 1, 0, array(), $item_data_to_card );
					}
				} else {
					$item_data['ywgc_recipient_name'] = is_array( $item_data['ywgc_recipient_name'] ) ? $item_data['ywgc_recipient_name'][0] : $item_data['ywgc_recipient_name'];
					$added_to_cart                    = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $item_data );
				}
			} else {
				$quantity      = isset( $_REQUEST['quantity'] ) ? intval( $_REQUEST['quantity'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$added_to_cart = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $item_data );
			}

			if ( $added_to_cart ) {
				if ( get_option( YWGC_PRODUCT_PLACEHOLDER ) === $product_id && isset( $item_data['ywgc_present_product_id'] ) ) {
					$product_id = $item_data['ywgc_present_product_id'];
				}

				if ( ! isset( $item_data['ywgc_present_product_id'] ) && isset( $item_data['ywgc_product_id'] ) ) {
					$product_id = $item_data['ywgc_product_id'];
				}

				$this->show_cart_message_on_added_product( $product_id, $quantity );
			}

			// If we added the product to the cart we can now optionally do a redirect.
			if ( wc_notice_count( 'error' ) === 0 ) {
				$url = '';

				// If has custom URL redirect there.
				$url = apply_filters( 'woocommerce_add_to_cart_redirect', $url, $adding_to_cart );

				if ( $url ) {
					wp_safe_redirect( $url );
					exit;
				} elseif ( get_option( 'woocommerce_cart_redirect_after_add' ) === 'yes' ) {
					if ( function_exists( 'wc_get_cart_url' ) ) {
						wp_safe_redirect( wc_get_cart_url() );
					} else {
						wp_safe_redirect( WC()->cart->get_cart_url() );
					}
					exit;
				}
			}
		}

		/**
		 * Updates cart item price for discounted gift cards
		 *
		 * @param string $price_html Html for the cart item price.
		 * @param array  $cart_item Cart item.
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return string Filtered html for the price.
		 * */
		public function gift_cards_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
			if ( isset( $cart_item['ywgc_product_id'] ) && isset( $cart_item['ywgc_amount_without_discount'] ) && ! empty( $cart_item['ywgc_amount_without_discount'] ) && apply_filters( 'ywgc_show_discounted_gift_card_price_in_cart', true ) ) {
				$price_html = wc_format_sale_price( $cart_item['ywgc_amount_without_discount'], $cart_item['ywgc_amount'] );
			}

			return $price_html;
		}

		/**
		 * Update the balance for all gift cards applied to an order
		 *
		 * @param int|WC_Order $order Order ID or order object.
		 */
		public function register_gift_cards_usage( $order ) {

			if ( is_numeric( $order ) ) {
				$order = wc_get_order( $order );
			}

			$applied_gift_cards = $this->get_gift_cards_from_session() ?? $order->get_meta( '_ywgc_applied_gift_cards' );

			if ( null === $applied_gift_cards ) {
				return;
			}

			/**
			 * Adding two race condition fields to the order
			 */
			if ( apply_filters( 'ywgc_apply_race_condition', false ) ) {
				$order->update_meta_data( YWGC_RACE_CONDITION_BLOCKED, 'no' );
				$order->update_meta_data( YWGC_RACE_CONDITION_UNIQUID, 'none' );
			}

			$created_via = get_post_meta( $order->get_id(), '_created_via', true );

			$applied_gift_cards_aux = array();
			$applied_discount       = 0.00;

			foreach ( $applied_gift_cards as $key => $code ) {

				$gift_card = YITH_YWGC()->get_gift_card_by_code( $code );

				if ( is_object( $gift_card ) && $gift_card->exists() ) {

					$amount = isset( WC()->cart->applied_gift_cards_amounts[ $code ] ) ? - WC()->cart->applied_gift_cards_amounts[ $code ] : 0;

					/**
					 * APPLY_FILTERS: yith_ywgc_gift_card_amount_before_deduct
					 *
					 * Filter the gift card amount before deduct it from the gift card balance.
					 *
					 * @param string $amount the amount to be deducted
					 * @param object $gift the gift card object
					 * @param object $order the order object
					 *
					 * @return string
					 */
					$amount = apply_filters( 'yith_ywgc_gift_card_amount_before_deduct', $amount, $gift_card, $order );

					$applied_gift_cards_aux[ $code ] = $amount * -1;
					$applied_discount               += $amount * -1;

					// Avoid charging twice if there is a YITH Multi Vendor suborder.
					if ( 'yith_wcmv_vendor_suborder' !== $created_via ) {

						$order->update_meta_data( '_ywgc_applied_gift_cards', $applied_gift_cards_aux );
						$order->update_meta_data( '_ywgc_applied_gift_cards_totals', $applied_discount );
						/**
						 * APPLY_FILTERS: yith_ywgc_new_balance_before_update_balance
						 *
						 * Filter the gift card new balance before update it.
						 *
						 * @param string the new gift card balance
						 * @param object $gift the gift card object
						 * @param string $amount the amount to be deducted
						 *
						 * @return string
						 */
						$new_balance = apply_filters( 'yith_ywgc_new_balance_before_update_balance', max( 0.00, $gift_card->get_balance() + $amount ), $gift_card, $amount );

						$gift_card->update_balance( $new_balance );
						$gift_card->register_order( $order->get_id() );
						$this->notify_customer_if_gift_cards_used( $gift_card );
					}

					if ( 'yith_wcmv_vendor_suborder' !== $created_via ) {
						// translators: first %s is the gift card amount used, second %s is the gift card code.
						$order->add_order_note( sprintf( esc_html__( 'Applied %s from gift card %s.', 'yith-woocommerce-gift-cards' ), wc_price( $amount ), $code ) );

						$order->save();
					} elseif ( 'yith_wcmv_vendor_suborder' === $created_via ) { // If is a MV suborder.

						// translators: first %s is the gift card amount used, second %s is the gift card code.
						$order->add_order_note( sprintf( esc_html__( 'Applied %s from gift card %s.', 'yith-woocommerce-gift-cards' ), wc_price( $amount ), $code ) );

						$order->save();
					}
				}
			}

			$this->empty_gift_cards_session();
		}

		/**
		 * Updates cart item price for discounted gift cards
		 *
		 * @param array $cart_item_data The cart item data.
		 * @param int   $product_id     The product ID.
		 *
		 * @return array The cart item data.
		 * */
		public function yith_ywgc_ajax_add_to_cart_compatibility( $cart_item_data, $product_id ) {
			if ( ! apply_filters( 'yith_ywgc_enable_ajax_add_to_cart_compatibility', false ) ) {
				return $cart_item_data;
			}

			$item_data = $this->build_cart_item_data();

			if ( ! isset( $item_data['ywgc_product_id'] ) ) {
				$item_data['ywgc_product_id'] = $product_id;
			}

			if ( isset( $item_data['ywgc_recipient_name'] ) && is_array( $item_data['ywgc_recipient_name'] ) ) {
				$item_data['ywgc_recipient_name'] = implode( ', ', $item_data['ywgc_recipient_name'] );
			}

			return $item_data;
		}
	}
}
