<?php
/**
 * Module Vendor Registration Fee main class
 *
 * @since      5.0.0
 * @author     YITH
 * @package    YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Registration_Fee' ) ) {
	/**
	 * Module Vendors Registration Fee main class.
	 */
	class YITH_Vendors_Registration_Fee {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Process fee payment key.
		 *
		 * @const string
		 * @since 5.0.0
		 */
		private const PROCESS_KEY = '980886cbd4cbea41e7248a0b40899ec5';

		/**
		 * Process cookie name
		 *
		 * @const string
		 * @since 5.0.0
		 */
		const COOKIE_NAME = 'yith_wcmv_registration_fee_process';

		/**
		 * Vendor new status
		 *
		 * @const string
		 * @since 5.0.0
		 */
		const VENDOR_STATUS = 'pending-fee';

		/**
		 * Construct
		 *
		 * @since  4.1.0
		 * @author Francesco Licandro
		 * @return void
		 */
		private function __construct() {

			// Register new vendor status.
			add_filter( 'yith_wcmv_get_vendor_statuses', array( $this, 'register_vendor_status' ), 10, 1 );

			add_action( 'init', array( $this, 'maybe_start_process' ), 10 );
			add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'add_fee_to_cart' ), 10, 1 );
			add_action( 'woocommerce_cart_item_removed', array( $this, 'remove_fee_from_cart' ), 10, 1 );
			add_filter( 'woocommerce_coupons_enabled', array( $this, 'disable_coupons_for_fee' ), 10, 1 );

			add_filter( 'yith_wcmv_vendor_approved_status', array( $this, 'filter_status_on_approved' ) );
			add_filter( 'yith_wcmv_create_vendor_frontend_data', array( $this, 'filter_vendor_create_data' ), 99, 1 );
			add_filter( 'woocommerce_registration_redirect', array( $this, 'filter_registration_redirect' ), 99, 1 );

			// Add email CTA.
			add_filter( 'yith_wcmv_email_approved_vendor', array( $this, 'output_email_cta' ), 10, 2 );

			// Once order is created, end process if any.
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'handle_order_created' ), 10, 1 );
			add_action( 'woocommerce_order_status_completed', array( $this, 'handle_order_completed' ), 10, 1 );

			add_filter( 'yith_wcmv_myaccount_vendor_status_args', array( $this, 'filter_myaccount_dashboard_args' ), 10, 1 );
		}

		/**
		 * Register new vendor status
		 *
		 * @since  5.0.0
		 * @param array $statuses An array of registered vendor status.
		 * @return array
		 */
		public function register_vendor_status( $statuses ) {
			$statuses[ self::VENDOR_STATUS ] = _x( 'Pending fee', 'Vendor status', 'yith-woocommerce-product-vendors' );
			return $statuses;
		}

		/**
		 * Maybe start fee payment process.
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function maybe_start_process() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_GET['yith_wcmv_registration_fee_process'] ) ) {
				return;
			}

			if ( ! is_user_logged_in() ) {
				wc_add_notice( __( 'Please login to complete the vendor registration process.', 'yith-woocommerce-product-vendors' ), 'error' );
				$redirect_url = add_query_arg( 'redirect_to', rawurlencode( $this->get_process_url() ), wc_get_page_permalink( 'myaccount' ) );
			} elseif ( $this->vendor_must_pay_fee() ) {
				$this->start_process();

				wc_add_notice( __( 'Pay the registration fee now to start setting up your store and selling your products!', 'yith-woocommerce-product-vendors' ) );
				$redirect_url = wc_get_checkout_url();
			}

			wp_safe_redirect( $redirect_url ?? remove_query_arg( 'yith_wcmv_registration_fee_process' ) );
			exit;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Add registration fee if we are in the registration vendor process
		 *
		 * @since 5.0.0
		 * @param WC_Cart $cart The cart object.
		 * @return void
		 */
		public function add_fee_to_cart( $cart ) {
			if ( ! $this->process_running() ) {
				return;
			}

			// Cart must be empty.
			$cart->empty_cart();

			$product = $this->get_fee_product();
			// Double check if the product have a valid price set, otherwise skip the add to cart process.
			if ( empty( $product->get_price() ) ) {
				return;
			}

			// Generate a cart item key.
			$cart_item_key                         = $this->get_fee_cart_item_key();
			$cart->cart_contents[ $cart_item_key ] = array(
				'key'          => $cart_item_key,
				'data'         => $product,
				'product_id'   => 0,
				'variation_id' => 0,
				'variation'    => array(),
				'quantity'     => 1,
			);
		}

		/**
		 * Stop registration process if fee is removed from cart
		 *
		 * @since  5.0.0
		 * @param string $cart_item_key The cart item key removed.
		 * @return void
		 */
		public function remove_fee_from_cart( $cart_item_key ) {
			if ( $this->get_fee_cart_item_key() === $cart_item_key ) {
				$this->end_process();
			}
		}

		/**
		 * Disable coupon for registration fee
		 *
		 * @since  5.0.0
		 * @param string $enabled True if coupon are enabled, false otherwise.
		 */
		public function disable_coupons_for_fee( $enabled ) {
			if ( $enabled && $this->process_running() ) {
				return false;
			}
			return $enabled;
		}

		/**
		 * Handle order created for registration fee process
		 *
		 * @since 5.0.0
		 * @param WC_Order $order_id The order ID created.
		 * @return void
		 */
		public function handle_order_created( $order_id ) {
			// TODO check also the cart items?
			if ( ! $this->process_running() ) {
				return;
			}

			$order = wc_get_order( $order_id );
			if ( $order && 'checkout' === $order->get_created_via() ) {

				$this->end_process();

				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				$order->add_meta_data( '_registration_fee_vendor', $vendor->get_id() );
				$order->save();
			}
		}

		/**
		 * Handle order completed for registration fee process
		 *
		 * @since 5.0.0
		 * @param WC_Order $order_id The order ID created.
		 * @return void
		 */
		public function handle_order_completed( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			$vendor_id = $order->get_meta( '_registration_fee_vendor' );
			$vendor    = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;
			if ( ! $vendor || ! $vendor->is_valid() ) {
				return;
			}

			$vendor->set_status( 'enabled' );
			$vendor->save();
		}

		/**
		 * Filter myaccount dashboard args.
		 *
		 * @since 5.0.0
		 * @param array $args   The section args.
		 * @return array
		 */
		public function filter_myaccount_dashboard_args( $args ) {

			$args[ self::VENDOR_STATUS ] = array(
				'title'   => __( 'You\'re just one step away from creating your store...', 'yith-woocommerce-product-vendors' ),
				'message' => sprintf(
					// translators: %s is the fee amount.
					__( 'Your application has been accepted. Pay the %s registration fee now to start setting up your store and selling your products!', 'yith-woocommerce-product-vendors' ),
					$this->get_fee_product_price_formatted()
				),
				'cta'     => array(
					'label' => __( 'Pay now', 'yith-woocommerce-product-vendors' ),
					'url'   => $this->get_process_url(),
				),
			);

			return $args;
		}

		/**
		 * Filter status on approved
		 *
		 * @since 5.0.0
		 * @return string
		 */
		public function filter_status_on_approved() {
			return self::VENDOR_STATUS;
		}

		/**
		 * Filter status on frontend creation if auto-approve is enabled.
		 *
		 * @since 5.0.0
		 * @param array $data An array of vendor data.
		 * @return array
		 */
		public function filter_vendor_create_data( $data ) {
			if ( ! empty( $data['status'] ) && 'enabled' === $data['status'] ) {
				$data['status'] = self::VENDOR_STATUS;
			}

			return $data;
		}

		/**
		 * Filter registration redirect on vendor creation. If vendor is pending-fee, start process.
		 *
		 * @since 5.0.0
		 * @param string $redirect_url Current redirect URL.
		 * @return string
		 */
		public function filter_registration_redirect( $redirect_url ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->has_status( self::VENDOR_STATUS ) ) {
				$redirect_url = $this->get_process_url();
			}

			return $redirect_url;
		}

		/**
		 * Output vendor registration fee CTA
		 *
		 * @since 5.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @param boolean     $plain_text True if email type is plain text, false otherwise.
		 * @return void
		 */
		public function output_email_cta( $vendor, $plain_text = false ) {
			if ( ! $vendor->has_status( self::VENDOR_STATUS ) ) {
				return;
			}

			$method = $plain_text ? 'output_email_cta_plain' : 'output_email_cta_html';
			$this->$method();
		}

		/**
		 * Output vendor registration fee CTA HTML
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected function output_email_cta_html() {
			?>
			<p><?php echo esc_html( __( 'You\'re just one step away from selling like a pro!', 'yith-woocommerce-product-vendors' ) ); ?></p>
			<p><?php echo esc_html( __( 'Click on the button below to pay the fee:', 'yith-woocommerce-product-vendors' ) ); ?></p>
			<a href="<?php echo esc_url( $this->get_process_url() ); ?>" class="yith-vendor-button-cta">
				<?php
				// translators: $s is the fee amount.
				echo esc_html( sprintf( __( '%s - Pay now', 'yith-woocommerce-product-vendors' ), $this->get_fee_product_price_formatted() ) );
				?>
			</a>
			<p><?php echo esc_html( __( 'After that, you will be able to customize your store and upload your products.', 'yith-woocommerce-product-vendors' ) ); ?></p>
			<?php
		}

		/**
		 * Output vendor registration fee CTA HTML
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected function output_email_cta_plain() {
			echo esc_html__( 'You\'re just one step away from selling like a pro!', 'yith-woocommerce-product-vendors' ) . "\n\n";
			echo esc_html__( 'Click on the link below to pay the fee:', 'yith-woocommerce-product-vendors' ) . "\n\n";
			echo esc_url( $this->get_process_url() ) . "\n\n";
			echo esc_html__( 'After that, you will be able to customize your store and upload your products.', 'yith-woocommerce-product-vendors' ) . "\n\n";
		}

		/**
		 * Create fee product on the fly and fill with the fee options data.
		 *
		 * @since 5.0.0
		 * @return WC_Product
		 */
		protected function get_fee_product() {

			$product = new WC_Product();
			$data    = apply_filters(
				'yith_wcmv_get_registration_fee_product_data',
				array(
					// translators: %s is the singular vendor tax label.
					'name'              => get_option( 'yith_wcmv_registration_fee_product_title', sprintf( _x( '%s registration fee', '[Admin]Option default. %s is the singular vendor tax label', 'yith-woocommerce-product-vendors' ), YITH_Vendors_Taxonomy::get_singular_label( 'ucfirst' ) ) ),
					// Set price.
					'price'             => $this->get_fee_product_price(),
					'regular_price'     => $this->get_fee_product_price(),
					// Set tax options.
					'tax_status'        => $this->get_fee_product_tax_status(),
					'tax_class'         => $this->get_fee_product_tax_class(),
					// Set additional metas.
					'status'            => 'private',
					'virtual'           => true,
					'sold_individually' => true,
				)
			);

			foreach ( $data as $key => $value ) {
				if ( method_exists( $product, "set_{$key}" ) ) {
					$product->{"set_$key"}( $value );
				} else {
					$product->update_meta_data( $key, $value );
				}
			}

			return $product;
		}

		/**
		 * Get fee product price
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected function get_fee_product_price() {
			return wc_format_decimal( get_option( 'yith_wcmv_registration_fee_product_price', 100 ) );
		}

		/**
		 * Get fee product price
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected function get_fee_product_price_formatted() {
			return sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $this->get_fee_product_price() );
		}

		/**
		 * Get fee product tax status
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected function get_fee_product_tax_status() {
			return get_option( 'yith_wcmv_registration_fee_product_tax_status', 'taxable' );
		}

		/**
		 * Get fee product tax class
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected function get_fee_product_tax_class() {
			return get_option( 'yith_wcmv_registration_fee_product_tax_class', '' );
		}

		/**
		 * Return the cart item fee key.
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected function get_fee_cart_item_key() {
			return md5( self::COOKIE_NAME );
		}

		/**
		 * Get process url.
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected function get_process_url() {
			return add_query_arg(
				'yith_wcmv_registration_fee_process',
				'yes',
				home_url(),
			);
		}

		/**
		 * Check if current logged in vendor must pay the registration fee.
		 *
		 * @since 5.0.0
		 * @return bool
		 */
		protected function vendor_must_pay_fee() {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			return $vendor && $vendor->is_valid() && $vendor->has_status( self::VENDOR_STATUS );
		}

		/**
		 * Create and return a unique vendor process key to sign the process.
		 *
		 * @since 5.0.0
		 * @return string.
		 */
		protected function get_vendor_process_key() {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			return md5( $vendor->get_id() . '|' . self::PROCESS_KEY );
		}

		/**
		 * Set payment fee cookie.
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected function start_process() {
			wc_setcookie( self::COOKIE_NAME, $this->get_vendor_process_key(), 0 );
		}

		/**
		 * Check if registration fee process is running and validate with current vendor.
		 *
		 * @since 5.0.0
		 * @return bool
		 */
		protected function process_running() {
			return ! empty( $_COOKIE[ self::COOKIE_NAME ] ) && $this->get_vendor_process_key() === sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) ) && $this->vendor_must_pay_fee();
		}

		/**
		 * End payment fee process for vendor
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected function end_process() {
			wc_setcookie( self::COOKIE_NAME, '', time() - HOUR_IN_SECONDS );
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  5.0.0
 * @return YITH_Vendors_Registration_Fee
 */
if ( ! function_exists( 'YITH_Vendors_Registration_Fee' ) ) {
	function YITH_Vendors_Registration_Fee() { // phpcs:ignore
		return YITH_Vendors_Registration_Fee::instance();
	}
}

YITH_Vendors_Registration_Fee();
