<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class to manage the gift card product type
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Product_Gift_Card' ) ) {

	/**
	 * WC_Product_Gift_Card class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class WC_Product_Gift_Card extends WC_Product {

		const YWGC_AMOUNTS                      = '_gift_card_amounts';
		const YWGC_PRODUCT_TEMPLATE_DESIGN      = '_ywgc_show_product_template_design';
		const YWGC_MANUAL_AMOUNT_MODE           = '_ywgc_manual_amount_mode';
		const YWGC_OVERRIDE_GLOBAL_SETTINGS     = '_ywgc_override_global_settings';
		const YWGC_ADD_DISCOUNT_SETTINGS        = '_ywgc_add_discount_settings';
		const YWGC_EXPIRATION_SETTINGS          = '_ywgc_expiration_settings';
		const YWGC_EXCLUDED_CATEGORIES_SETTINGS = '_ywgc_exclude_categories_settings';
		const YWGC_EXCLUDED_CATEGORIES          = '_ywgc_excluded_categories';

		/**
		 * Gift card amounts
		 *
		 * @var array|null
		 */
		public $amounts = null;

		/**
		 * Initialize a gift card product.
		 *
		 * @param mixed $product Product object.
		 */
		public function __construct( $product ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
			try {
				parent::__construct( $product );
			} catch ( Exception $e ) {
				
			}
		}

		/**
		 * Get_type
		 *
		 * @return string
		 */
		public function get_type() {
			return YWGC_GIFT_CARD_PRODUCT_TYPE;
		}

		/**
		 * Is_downloadable
		 *
		 * @return bool
		 */
		public function is_downloadable() {
			return false;
		}

		/**
		 * Retrieve the number of current amounts for this product
		 *
		 * @return int
		 * @since  1.0.0
		 */
		public function get_amounts_count() {
			$amounts = $this->get_product_amounts();

			return count( $amounts );
		}

		/**
		 * Retrieve the amounts set for the product
		 *
		 * @return array
		 */
		public function get_product_amounts() {
			if ( ! is_array( $this->amounts ) ) {
				if ( $this->id ) {
					$result        = get_post_meta( $this->get_id(), self::YWGC_AMOUNTS, true );
					$this->amounts = is_array( $result ) ? $result : array();
				}
			}

			/**
			 * APPLY_FILTERS: yith_ywgc_gift_card_amounts
			 *
			 * Filter the gift card amounts.
			 *
			 * @param string $amounts_to_show gift card amounts
			 * @param object $this  gift card object
			 *
			 * @return array
			 */
			return apply_filters( 'yith_ywgc_gift_card_amounts', $this->amounts, $this );
		}

		/**
		 * Returns false if the product cannot be bought.
		 *
		 * @return bool
		 */
		public function is_purchasable() {
			$purchasable = $this->get_amounts_count() > 0 || $this->is_manual_amount_enabled() ? true : false;

			return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
		}

		/**
		 * Save current gift card amounts
		 *
		 * @param array $amounts amounts.
		 */
		public function set_amounts( $amounts = array() ) {
			$this->amounts = $amounts;
		}

		/**
		 * Save current gift card amounts
		 *
		 * @param array  $amounts     Gift card amounts.
		 * @param string $currency_id Currency ID.
		 */
		public function save_amounts( $amounts = array(), $currency_id = '' ) {
			if ( '' !== $currency_id ) {
				$amounts_by_currency_meta = '_gift_card_amounts_' . $currency_id;
				update_post_meta( $this->get_id(), $amounts_by_currency_meta, $amounts );
			} else {
				update_post_meta( $this->get_id(), self::YWGC_AMOUNTS, $amounts );
			}
		}

		/**
		 * Update the design status for the gift card
		 *
		 * @param string $status Design status.
		 */
		public function set_design_status( $status ) {
			$this->update_meta_data( self::YWGC_PRODUCT_TEMPLATE_DESIGN, $status );
			$this->save_meta_data();
		}

		/**
		 * Retrieve the design status
		 *
		 * @return mixed
		 */
		public function get_design_status() {
			return $this->get_product_instance()->get_meta( self::YWGC_PRODUCT_TEMPLATE_DESIGN );
		}

		/**
		 * Process the current product instance in order to let third party plugin
		 * change the reference(Useful for WPML and similar plugins)
		 *
		 * @return WC_Product
		 */
		protected function get_product_instance() {
			/**
			 * APPLY_FILTERS: yith_ywgc_get_product_instance
			 *
			 * Filter the gift card product instance.
			 *
			 * @param object $this gift card product instance
			 *
			 * @return object
			 */
			return apply_filters( 'yith_ywgc_get_product_instance', $this );
		}

		/**
		 * Get the specific product settings to override the globals
		 */
		public function get_override_global_settings_status() {
			return $this->get_product_instance()->get_meta( self::YWGC_OVERRIDE_GLOBAL_SETTINGS );
		}

		/**
		 * Get the discount settings status
		 */
		public function get_add_discount_settings_status() {
			return $this->get_product_instance()->get_meta( self::YWGC_ADD_DISCOUNT_SETTINGS );
		}

		/**
		 * Get the expiration settings status
		 */
		public function get_expiration_settings_status() {
			return $this->get_product_instance()->get_meta( self::YWGC_EXPIRATION_SETTINGS );
		}

		/**
		 * Get the expiration settings status
		 */
		public function get_exclude_categories_settings_status() {
			return $this->get_product_instance()->get_meta( self::YWGC_EXCLUDED_CATEGORIES_SETTINGS );
		}

		/**
		 * Get the expiration settings status
		 */
		public function get_excluded_categories() {
			return $this->get_product_instance()->get_meta( self::YWGC_EXCLUDED_CATEGORIES );
		}

		/**
		 * Get the manual settings status
		 */
		public function get_manual_amount_status() {
			return $this->get_product_instance()->get_meta( self::YWGC_MANUAL_AMOUNT_MODE );
		}

		/**
		 * Update the specific product settings to override the globals
		 *
		 * @param string $status the status.
		 *
		 * @return void
		 */
		public function update_override_global_settings_status( $status ) {
			$this->update_meta_data( self::YWGC_OVERRIDE_GLOBAL_SETTINGS, $status );
			$this->save_meta_data();
		}

		/**
		 * Update the manual amount settings status.
		 *
		 * @param string $status Available values are "global", "accept" and "reject".
		 */
		public function update_manual_amount_status( $status ) {
			$this->update_meta_data( self::YWGC_MANUAL_AMOUNT_MODE, $status );
			$this->save_meta_data();
		}

		/**
		 * Update the discount settings status.
		 *
		 * @param string $status Available values are "global", "accept" and "reject".
		 */
		public function update_add_discount_settings_status( $status ) {
			$this->update_meta_data( self::YWGC_ADD_DISCOUNT_SETTINGS, $status );
			$this->save_meta_data();
		}

		/**
		 * Update the expiration settings status.
		 *
		 * @param string $status Available values are "global", "accept" and "reject".
		 */
		public function update_expiration_settings_status( $status ) {
			$this->update_meta_data( self::YWGC_EXPIRATION_SETTINGS, $status );
			$this->save_meta_data();
		}

		/**
		 * Update the excluded categories settings status.
		 *
		 * @param string $status the setting status.
		 */
		public function update_excluded_categories_settings_status( $status ) {
			$this->update_meta_data( self::YWGC_EXCLUDED_CATEGORIES_SETTINGS, $status );
			$this->save_meta_data();
		}

		/**
		 * Update the excluded categories settings status.
		 *
		 * @param string $status the excluded categories.
		 */
		public function update_excluded_categories( $status ) {
			$this->update_meta_data( self::YWGC_EXCLUDED_CATEGORIES, $status );
			$this->save_meta_data();
		}

		/**
		 * Returns the price in html format
		 *
		 * @access public
		 *
		 * @param string $price Price (default: '').
		 *
		 * @return string
		 */
		public function get_price_html( $price = '' ) {
			$amounts = $this->get_amounts_to_be_shown();

			// No price for current gift card.
			if ( ! count( $amounts ) ) {
				/**
				 * APPLY_FILTERS: yith_woocommerce_gift_cards_empty_price_html
				 *
				 * Filter the empty price HTML for the gift cards.
				 *
				 * @param string empty string
				 * @param object $this gift card product instance
				 *
				 * @return string
				 */
				$price = apply_filters( 'yith_woocommerce_gift_cards_empty_price_html', '', $this );
			} else {
				ksort( $amounts, SORT_NUMERIC );

				$min_price = current( $amounts );
				$min_price = wc_price( $min_price['price'] );
				$max_price = end( $amounts );
				$max_price = wc_price( $max_price['price'] );

				/**
				 * APPLY_FILTERS: yith_woocommerce_gift_cards_amount_range
				 *
				 * Filter the price range of a gift card product.
				 *
				 * @param string $price price range of the gift card
				 * @param object $this gift card product instance
				 * @param string $min_price minimum amount of the gift card
				 * @param string $max_price maximum amount of the gift card
				 *
				 * @return string
				 */
				$price = apply_filters( 'yith_woocommerce_gift_cards_amount_range', $min_price !== $max_price ? wc_format_price_range( $min_price, $max_price ) : $min_price, $this, $min_price, $max_price );
			}

			return apply_filters( 'woocommerce_get_price_html', $price, $this );
		}

		/**
		 * Retrieve an array of gift cards amounts with the corrected value to be shown(inclusive or not inclusive taxes)
		 *
		 * @return array
		 * @since  1.0.0
		 */
		public function get_amounts_to_be_shown() {
			$customer_currency_id  = class_exists( 'YITH_WCMCS_Currency_Handler' ) ? YITH_WCMCS_Currency_Handler::get_instance()->get_client_currency_id() : get_woocommerce_currency();
			$default_currency_id   = function_exists( 'yith_wcmcs_get_wc_currency_options' ) ? yith_wcmcs_get_wc_currency_options( 'currency' ) : get_woocommerce_currency();
			$gift_card_product_id  = $this->id;
			$amounts_to_show       = array();
			$tax_display_mode      = get_option( 'woocommerce_tax_display_shop' );
			$custom_prices_enabled = 'yes' === get_post_meta( $gift_card_product_id, '_ywgc_wcmcs_custom_prices', true );

			if ( $customer_currency_id !== $default_currency_id && $custom_prices_enabled ) {
				$display_amounts = $this->get_product_amounts_by_currency( $customer_currency_id );
				$product_amounts = $this->get_product_amounts_by_currency( $customer_currency_id );
			} else {
				$display_amounts = $this->get_product_amounts();
				$product_amounts = $this->get_product_amounts();
			}

			$index = 0;

			foreach ( $display_amounts as $amount ) {
				$amount = wc_format_decimal( $amount );

				if ( 'incl' === $tax_display_mode ) {
					$price = yit_get_price_including_tax( $this, 1, $amount );
				} else {
					$price = yit_get_price_excluding_tax( $this, 1, $amount );
				}

				$original_amount = $product_amounts[ $index ] ?? 0;
				$negative        = $price < 0;
				$price_format    = get_woocommerce_price_format();
				$formatted_price = ( $negative ? '-' : '' ) . sprintf( $price_format, get_woocommerce_currency_symbol(), $price );
				
				$amounts_to_show[ $original_amount ] = array(
					'amount'   => $amount,
					'price'    => $price,
					'wc-price' => $formatted_price,
					'title'    => wc_price( $price ),
				);

				++$index;
			}

			/**
			 * APPLY_FILTERS: yith_ywgc_gift_cards_amounts
			 *
			 * Filter the gift card amounts to be shown.
			 *
			 * @param string $amounts_to_show gift card amounts to show
			 * @param string $gift_card_product_id  gift card product id
			 *
			 * @return array
			 */
			return apply_filters( 'yith_ywgc_gift_cards_amounts', $amounts_to_show, $gift_card_product_id );
		}

		/**
		 * Retrieve the amounts set for the product in the different currencies
		 *
		 * @param int $currency_id the currency ID.
		 *
		 * @return array
		 */
		public function get_product_amounts_by_currency( $currency_id ) {
			$amounts_by_currency_meta = '_gift_card_amounts_' . $currency_id;

			if ( $this->id ) {
				$result           = get_post_meta( $this->get_id(), $amounts_by_currency_meta, true );
				$currency_amounts = is_array( $result ) ? $result : $this->get_product_amounts_converted_to_currency( $currency_id );
				$currency_amounts = array_values( $currency_amounts );
			} else {
				$currency_amounts = array();
			}

			/**
			 * APPLY_FILTERS: yith_ywgc_gift_card_amounts_by_currency
			 *
			 * Filter the gift card amounts by currency.
			 *
			 * @param string $amounts_to_show gift card amounts
			 * @param object $this  gift card object
			 * @param int $currency_id  currency ID
			 *
			 * @return array
			 */
			return apply_filters( 'yith_ywgc_gift_card_amounts_by_currency', array_values( $currency_amounts ), $this, $currency_id );
		}

		/**
		 * Retrieve the amounts set for the product converted to the different currencies
		 *
		 * @param int $currency_id the currency ID.
		 *
		 * @return array
		 */
		public function get_product_amounts_converted_to_currency( $currency_id ) {
			$converted_amounts = array();
			$product_amounts   = $this->get_product_amounts();

			foreach ( $product_amounts as $amount ) {
				$converted_amounts[] = apply_filters(
					'yith_wcmcs_convert_price',
					$amount,
					array(
						'from' => yith_wcmcs_get_current_currency_id(),
						'to'   => $currency_id,
					)
				);
			}

			return $converted_amounts;
		}

		/**
		 * Retrieve if manual amount is enabled for this gift card
		 */
		public function is_manual_amount_enabled() {
			$override_globals = $this->get_override_global_settings_status();
			$status           = $this->get_manual_amount_status();

			if ( 'yes' !== $override_globals ) {
				$status = get_option( 'ywgc_permit_free_amount', 'no' );
			}

			if ( 'yes' === $status || '1' === $status ) {
				$status = 'yes';
			}

			/**
			 * APPLY_FILTERS: yith_gift_cards_is_manual_amount_enabled
			 *
			 * Filter the manual amount status for the gift card product.
			 *
			 * @param bool bool to check if the manual amount is enabled
			 * @param bool $status manual amount status
			 * @param object $this gift card product instance
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_gift_cards_is_manual_amount_enabled', 'yes' === $status, $status, $this );
		}

		/**
		 * Get the add to cart button text
		 *
		 * @return string
		 */
		public function add_to_cart_text() {
			/**
			 * APPLY_FILTERS: yith_ywgc_select_amount_text
			 *
			 * Filter the "Select amount" text for the gift card products on the catalog pages.
			 *
			 * @param string $text Select amount text
			 *
			 * @return string
			 */
			$text = $this->is_purchasable() && $this->is_in_stock() ? apply_filters( 'yith_ywgc_select_amount_text', esc_html__( 'Select amount', 'yith-woocommerce-gift-cards' ) ) : esc_html__( 'Read more', 'yith-woocommerce-gift-cards' );

			/**
			 * APPLY_FILTERS: woocommerce_product_add_to_cart_text
			 *
			 * Filter the WooCommerce add to cart button text for the gift card products, using the gift card object on the second param.
			 *
			 * @param string $text add to cart text
			 * @param string $this gift card object
			 *
			 * @return string
			 */
			return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
		}
	}
}
