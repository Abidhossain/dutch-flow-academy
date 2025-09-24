<?php
/**
 * Main class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP' ) ) {
	/**
	 * WooCommerce Deposits / Down Payments
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP {

		/**
		 * Plugin version
		 *
		 * @const string
		 * @since 2.0.0
		 */
		const VERSION = '2.28.0';

		/**
		 * Database version
		 *
		 * @const string
		 * @since 2.0.0
		 */
		const DB_VERSION = '2.1.0';

		/**
		 * Plugin version
		 *
		 * @deprecated
		 * @const string
		 * @since 1.0.0
		 */
		const YITH_WCDP_VERSION = self::VERSION;

		/**
		 * Single instance of support cart
		 *
		 * @var YITH_WCDP_Support_Cart
		 */
		protected $support_cart = null;

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCDP
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			/**
			 * DO_ACTION: yith_wcdp_startup
			 *
			 * Action triggered when loading main plugin class YITH_WCDP.
			 */
			do_action( 'yith_wcdp_startup' );

			// init plugin processing.
			add_action( 'init', array( $this, 'init' ), 5 );

			// register plugin to licence/update system.
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );

			add_action( 'before_woocommerce_init', array( $this, 'declare_wc_features_support' ) );
		}

		/**
		 * Install plugin
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function init() {
			// do startup operations.
			YITH_WCDP_Install::init();
			YITH_WCDP_Products::init();
			YITH_WCDP_Shortcode::init();
			YITH_WCDP_Blocks::init();
			YITH_WCDP_Emails::init();
			YITH_WCDP_Crons::init();
			YITH_WCDP_Compatibility::init();

			// init required objects.
			YITH_WCDP_Suborders::get_instance();

			if ( is_admin() ) {
				YITH_WCDP_Admin::get_instance();
			} else {
				YITH_WCDP_Frontend::get_instance();
			}
		}

		/* === LICENCE HANDLING METHODS === */

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_WCDP_DIR . 'plugin-fw/licence/lib/yit-licence.php';
				require_once YITH_WCDP_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}

			YIT_Plugin_Licence()->register( YITH_WCDP_INIT, YITH_WCDP_SECRET_KEY, YITH_WCDP_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_WCDP_DIR . 'plugin-fw/lib/yit-upgrade.php';
			}

			YIT_Upgrade()->register( YITH_WCDP_SLUG, YITH_WCDP_INIT );
		}

		/**
		 * Declare support for WooCommerce features.
		 */
		public function declare_wc_features_support() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YITH_WCDP_INIT, true );
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', YITH_WCDP_INIT, true );
			}
		}

		/* === SUPPORT CART METHODS === */

		/**
		 * Creates single instance of support cart
		 *
		 * @param bool $empty Whether to empty support cart before returning it (default to true).
		 *
		 * @return YITH_WCDP_Support_Cart
		 * @since 1.3.0
		 */
		public function get_support_cart( $empty = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.emptyFound
			if ( is_null( $this->support_cart ) ) {
				$this->support_cart = new YITH_WCDP_Support_Cart();
			}

			if ( $empty ) {
				$this->support_cart->empty_cart();
			}

			return $this->support_cart;
		}

		/* === HELPER METHODS === */

		/**
		 * Return true if deposit is default option; false otherwise
		 *
		 * @param int|bool $product_id   Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 * @param int|bool $variation_id Variation id, if specified; false otherwise.
		 *
		 * @return bool Whether deposit is default option for product
		 * @since 1.0.0
		 */
		public function is_deposit_default( $product_id = false, $variation_id = false ) {
			_deprecated_function( __METHOD__, '2.0.0', 'YITH_WCDP_Deposits::is_default' );

			$product_id = $variation_id ? $variation_id : $product_id;
			return YITH_WCDP_Deposits::is_default( $product_id );
		}

		/**
		 * Return true if deposit is enabled on product
		 *
		 * @param int|bool $product_id   Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 * @param int|bool $variation_id Variation id, if specified; false otherwise.
		 *
		 * @return bool Whether deposit is enabled for product
		 * @since 1.0.0
		 */
		public function is_deposit_enabled_on_product( $product_id = false, $variation_id = false ) {
			_deprecated_function( __METHOD__, '2.0.0', 'YITH_WCDP_Deposits::is_enabled' );

			$product_id = $variation_id ? $variation_id : $product_id;
			return YITH_WCDP_Deposits::is_enabled( $product_id );
		}

		/**
		 * Return true in deposit is mandatory for a product
		 *
		 * @param int|bool $product_id   Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 * @param int|bool $variation_id Variation id, if specified.
		 *
		 * @return bool Whether deposit is enabled for product
		 * @since 1.0.0
		 */
		public function is_deposit_mandatory( $product_id = false, $variation_id = false ) {
			_deprecated_function( __METHOD__, '2.0.0', 'YITH_WCDP_Deposits::is_mandatory' );

			$product_id = $variation_id ? $variation_id : $product_id;
			return YITH_WCDP_Deposits::is_mandatory( $product_id );
		}

		/**
		 * Return tru if deposit has expired on product
		 *
		 * @param int|bool $product_id   Product id, if specified; false otherwise. If no product id is provided, global $product will be used.
		 * @param int|bool $variation_id Variation id, if specified; false otherwise..
		 *
		 * @return bool Whether or not deposit has expired for the product
		 */
		public function is_deposit_expired_for_product( $product_id = false, $variation_id = false ) {
			_deprecated_function( __METHOD__, '2.0.0', 'YITH_WCDP_Deposits::has_expired' );

			$product_id = $variation_id ? $variation_id : $product_id;
			return YITH_WCDP_Deposits::has_expired( $product_id );
		}

		/**
		 * Returns best matching deposit rule for passed parameters
		 *
		 * @param int $product_id   Product id.
		 * @param int $customer_id  Customer id.
		 * @param int $variation_id Variation id.
		 *
		 * @return YITH_WCDP_Deposit_Rule|bool Returns best matching rule, or false if none was found.
		 */
		public function get_best_deposit_rule_matching( $product_id = false, $customer_id = false, $variation_id = false ) {
			_deprecated_function( __METHOD__, '2.0.0', 'YITH_WCDP_Deposits::get_best_rule_matching' );

			$product_id = $variation_id ? $variation_id : $product_id;
			return YITH_WCDP_Deposits::get_best_rule_matching( $product_id, $customer_id );
		}

		/**
		 * Retrieve deposit type
		 *
		 * @param int|bool $product_id Product id.
		 * @param int|bool $customer_id Customer id.
		 * @param int|bool $variation_id Variation id.
		 *
		 * @return string Deposit type (rate, amount)
		 * @since 1.0.0
		 */
		public function get_deposit_type( $product_id = false, $customer_id = false, $variation_id = false ) {
			_deprecated_function( __METHOD__, '2.0.0', 'YITH_WCDP_Deposits::get_type' );

			$product_id = $variation_id ? $variation_id : $product_id;
			return YITH_WCDP_Deposits::get_type( $product_id, $customer_id );
		}

		/**
		 * Retrieve deposit amount (needed on amount deposit type)
		 *
		 * @param int|bool $product_id Product id.
		 * @param int|bool $customer_id Customer id.
		 * @param int|bool $variation_id Variation id.
		 *
		 * @return string Amount
		 * @since 1.0.0
		 */
		public function get_deposit_amount( $product_id = false, $customer_id = false, $variation_id = false ) {
			_deprecated_function( __METHOD__, '2.0.0', 'YITH_WCDP_Deposits::get_amount' );

			$product_id = $variation_id ? $variation_id : $product_id;
			return YITH_WCDP_Deposits::get_amount( $product_id, $customer_id );
		}

		/**
		 * Retrieve deposit rate (needed on rate deposit type)
		 *
		 * @param int|bool $product_id Product id.
		 * @param int|bool $customer_id Customer id.
		 * @param int|bool $variation_id Variation id.
		 *
		 * @return string Amount
		 * @since 1.0.0
		 */
		public function get_deposit_rate( $product_id = false, $customer_id = false, $variation_id = false ) {
			_deprecated_function( __METHOD__, '2.0.0', 'YITH_WCDP_Deposits::get_rate' );

			$product_id = $variation_id ? $variation_id : $product_id;
			return YITH_WCDP_Deposits::get_rate( $product_id, $customer_id );
		}

		/**
		 * Calculate deposit for product and variation passed as param
		 *
		 * @param int        $product_id   Product id.
		 * @param float|bool $price        Current product price (often third party plugin changes cart item price); false to use price from product object.
		 * @param string     $context      Context of the operator.
		 * @param int|bool   $customer_id  Customer id; default to false, to consider current user.
		 * @param int|bool   $variation_id Variation id; default to false, to consider main product.
		 *
		 * @return double Deposit amount for specified product and variation
		 * @since 1.0.4
		 */
		public function get_deposit( $product_id, $price = false, $context = 'edit', $customer_id = false, $variation_id = false ) {
			_deprecated_function( __METHOD__, '2.0.0', 'YITH_WCDP_Deposits::get_deposit' );

			$product_id = $variation_id ? $variation_id : $product_id;
			return YITH_WCDP_Deposits::get_deposit( $product_id, $customer_id, $price, $context );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCDP
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

/**
 * Unique access to instance of YITH_WCDP class
 *
 * @return \YITH_WCDP
 * @since 1.0.0
 */
function YITH_WCDP() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid, Universal.Files.SeparateFunctionsFromOO
	return YITH_WCDP::get_instance();
}

/**
 * Legacy function, left just for backward compatibility
 *
 * @return \YITH_WCDP
 */
function YITH_WCDP_Premium() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	_deprecated_function( 'YITH_WCDP_Premium', '2.0.0', 'YITH_WCDP' );

	return YITH_WCDP();
}

// create legacy class alias.
class_alias( 'YITH_WCDP', 'YITH_WCDP_Premium' );
