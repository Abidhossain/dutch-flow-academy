<?php
/**
 * YITH_Vendors_Shipping class.
 *
 * @since      1.9.17
 * @author     YITH
 * @package YITH\MultiVendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Shipping' ) ) {
	/**
	 * Main class for shipping module.
	 */
	class YITH_Vendors_Shipping extends YITH_Vendors_Shipping_Legacy {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Admin Instance
		 *
		 * @since 1.9.17
		 * @var null| YITH_Vendors_Shipping_Admin
		 */
		public $admin = null;

		/**
		 * Frontpage Instance
		 *
		 * @since 1.9.17
		 * @var null | YITH_Vendors_Shipping_Frontend
		 */
		public $frontend = null;

		/**
		 * Construct
		 *
		 * @since  1.9.17
		 */
		private function __construct() {
			$this->init();
			$this->register_actions();
			$this->define_class_aliases();
		}

		/**
		 * Define plugin class aliases for backward compatibility
		 *
		 * @since  4.0.0
		 */
		protected function define_class_aliases() {
			class_alias( 'YITH_Vendors_Shipping', 'YITH_Vendor_Shipping' );
			class_alias( 'YITH_Vendors_Shipping_Admin', 'YITH_Vendor_Shipping_Admin' );
			class_alias( 'YITH_Vendors_Shipping_Frontend', 'YITH_Vendor_Shipping_Frontend' );
		}

		/**
		 * Class Init
		 * Instance the admin or frontend classes
		 *
		 * @since  1.9.17
		 * @return void
		 * @access protected
		 */
		protected function init() {
			// Handle admin request.
			if ( yith_wcmv_is_admin_request() ) {
				$this->admin = new YITH_Vendors_Shipping_Admin();
			}
			// Handle frontend request.
			if ( yith_wcmv_is_frontend_request() ) {
				$this->frontend = new YITH_Vendors_Shipping_Frontend();
			}
		}

		/**
		 * Register class hooks and filters
		 *
		 * @since  1.9.17
		 * @return void
		 * @access protected
		 */
		protected function register_actions() {
			add_filter( 'yith_wcmv_register_commissions_status', array( $this, 'register_shipping_fee_status' ), 15, 3 );
			// Register shipping fee commissions.
			add_action( 'yith_wcmv_checkout_order_processed', array( $this, 'register_commissions' ), 15, 1 );
			// Register shipping commissions.
			add_action( 'yith_wcmv_suborder_created', array( $this, 'register_commissions' ), 15, 1 );
			// Filter vendor meta value for backward compatibility.
			add_filter( 'yith_wcmv_get_vendor_value', array( $this, 'filter_meta_value' ), 10, 3 );
		}

		/**
		 * Get the shipping processing time array.
		 *
		 * @since  1.9.17
		 * @return mixed
		 */
		public static function get_shipping_processing_times() {
			$times = array(
				'1' => __( '1 business day', 'yith-woocommerce-product-vendors' ),
				'2' => __( '1-2 business days', 'yith-woocommerce-product-vendors' ),
				'3' => __( '1-3 business days', 'yith-woocommerce-product-vendors' ),
				'4' => __( '3-5 business days', 'yith-woocommerce-product-vendors' ),
				'5' => __( '1-2 weeks', 'yith-woocommerce-product-vendors' ),
				'6' => __( '2-3 weeks', 'yith-woocommerce-product-vendors' ),
				'7' => __( '3-4 weeks', 'yith-woocommerce-product-vendors' ),
				'8' => __( '4-6 weeks', 'yith-woocommerce-product-vendors' ),
				'9' => __( '6-8 weeks', 'yith-woocommerce-product-vendors' ),
			);

			return apply_filters( 'yith_wcmv_shipping_processing_times', $times );
		}

		/**
		 * Get an array of shipping country
		 *
		 * @since  4.0.0
		 * @param boolean $add_empty (Optional) True to add an empty field, false otherwise.
		 * @return mixed
		 */
		public static function get_shipping_countries( $add_empty = false ) {
			$countries_class = WC()->countries;
			if ( empty( $countries_class ) ) {
				$countries_class = new WC_Countries();
			}
			$countries = $countries_class->get_countries();
			if ( $add_empty ) {
				$countries = array_merge( array( '' => __( '- Select a location -', 'yith-woocommerce-product-vendors' ) ), $countries );
			}

			return apply_filters( 'yith_wcmv_shipping_countries', $countries );
		}

		/**
		 * Check if shipping is enabled for given vendor
		 *
		 * @since  1.9.17
		 * @param YITH_Vendor $vendor The vendor instance.
		 * @return bool
		 */
		public static function is_single_vendor_shipping_enabled( $vendor ) {
			return 'yes' === $vendor->get_meta( 'enable_shipping' );
		}

		/**
		 * Get vendor from shipping method ID.
		 *
		 * @since  1.9.17
		 * @param string       $method_id The method ID.
		 * @param null | array $packages  (Optional) The packages array, or null to get the one from WC()->shipping.
		 * @return YITH_Vendor
		 */
		public function get_vendor_from_method_id( $method_id, $packages = null ) {
			$vendor   = false;
			$packages = empty( $packages ) ? WC()->shipping->get_packages() : $packages;

			foreach ( $packages as $id => $package ) {
				if ( ! empty( $package['rates'][ $method_id ] ) && ! empty( $package['yith-vendor'] ) ) {
					$vendor = $package['yith-vendor'];
					break;
				}
			}

			return $vendor;
		}

		/**
		 * Register the shipping commission linked to order
		 *
		 * @since 1.9.17
		 * @param integer $order_id The order ID.
		 */
		public static function register_commissions( $order_id ) {

			$order     = wc_get_order( $order_id );
			$processed = $order ? $order->get_meta( '_shipping_commissions_processed' ) : 'yes';
			if ( 'yes' === $processed ) {
				return;
			}

			$vendor_id = yith_wcmv_get_vendor_id_for_order( $order );
			$vendor    = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;
			if ( ! $vendor || ! $vendor->is_valid() ) {
				return;
			}
			$shipping_methods = apply_filters( 'yith_wcmv_get_shipping_methods', $order->get_shipping_methods(), $order, $vendor );
			$commission_ids   = array();

			if ( ! empty( $shipping_methods ) ) {
				$parent_order_id = $order->get_parent_id();
				$parent_order    = wc_get_order( $parent_order_id );

				foreach ( $shipping_methods as $shipping_id => $shipping ) {

					$line_total = apply_filters( 'yith_wcmv_shipping_line_total', (float) $shipping->get_total( 'edit' ) + (float) $shipping->get_total_tax( 'edit' ), $shipping, $vendor, $order );
					$epsylon    = (float) pow( 10, wc_get_price_decimals() * -1 );
					if ( apply_filters( 'yith_wcmv_skip_shipping_commission', $line_total < $epsylon, $shipping, $order, $vendor ) ) {
						continue;
					}

					$args = array(
						'line_item_id' => $shipping_id,
						'order_id'     => $order_id,
						'user_id'      => $vendor->get_owner(),
						'vendor_id'    => $vendor->get_id(),
						'amount'       => $line_total,
						'line_total'   => $line_total,
						'rate'         => apply_filters( 'yith_wcmv_vendor_shipping_rate', 1, $vendor ),
						'type'         => 'shipping',
					);

					$commission_id = yith_wcmv_create_commission( $args );
					if ( is_wp_error( $commission_id ) ) {
						continue;
					}

					$method_id                    = $shipping->get_method_id();
					$commission_ids[ $method_id ] = $commission_id;

					$shipping->add_meta_data( '_commission_id', $commission_id, true );
					$shipping->save();

					$vendor_package_id = $shipping->get_meta( '_vendor_package_id', true, 'edit' );
					$vendor_id         = (int) $shipping->get_meta( 'vendor_id', true, 'edit' );

					if ( ! empty( $parent_order ) && $parent_order instanceof WC_Order ) {
						foreach ( $parent_order->get_items( 'shipping' ) as $parent_shipping_item ) {
							$vendor_parent_package_id = $parent_shipping_item->get_meta( '_vendor_package_id', true, 'edit' );
							$vendor_parent_id         = (int) $parent_shipping_item->get_meta( 'vendor_id', true, 'edit' );
							if ( empty( $vendor_parent_package_id ) || empty( $vendor_parent_id ) ) {
								continue;
							}

							if ( $vendor_package_id === $vendor_parent_package_id && $vendor_parent_id === $vendor_id ) {
								$parent_shipping_item->add_meta_data( '_commission_id', $commission_id, true );
								$parent_shipping_item->save();
							}
						}
					}
				}
			}

			// Mark shipping fee as processed.
			$order->add_meta_data( '_shipping_commissions_processed', 'yes', true );
			$order->save_meta_data();
		}

		/**
		 * Get the shipping fee ids
		 *
		 * @param array   $commission_ids An array of commissions id.
		 * @param integer $order_id       The order ID.
		 * @param string  $status         The order status.
		 * @return array
		 */
		public function register_shipping_fee_status( $commission_ids, $order_id, $status ) {
			$args = array(
				'order_id' => $order_id,
				'type'     => 'shipping',
				'status'   => 'all',
			);

			$shipping_fee_ids = yith_wcmv_get_commissions( $args );

			return ! empty( $shipping_fee_ids ) ? array_merge( $commission_ids, $shipping_fee_ids ) : $commission_ids;
		}

		/**
		 * Filter specific vendor meta value for shipping module backward compatibility
		 *
		 * @since  4.0.0
		 * @param mixed       $value  The current meta value.
		 * @param YITH_Vendor $vendor The vendor instance.
		 * @param string      $key    The meta key.
		 * @return mixed
		 */
		public function filter_meta_value( $value, $vendor, $key ) {

			if ( empty( $value ) && in_array( $key, array( 'shipping_extra_cost_items', 'shipping_extra_cost_products' ), true ) ) {

				$value = array(
					'items'       => 1,
					'cost'        => 0,
					'applied_how' => 'per_product',
				);

				switch ( $key ) {
					case 'shipping_extra_cost_items':
						$old_value = $vendor->shipping_product_additional_price;
						if ( $old_value ) {
							$value = array_merge( $value, array( 'cost' => floatval( $old_value ) ) );
						}
						break;

					case 'shipping_extra_cost_products':
						$old_value = $vendor->shipping_product_qty_price;
						if ( $old_value ) {
							$value = array_merge( $value, array( 'cost' => floatval( $old_value ) ) );
						}
						break;
				}
			}

			return $value;
		}
	}
}

if ( ! function_exists( 'YITH_Vendors_Shipping' ) ) {
	/**
	 * Get main instance of class YITH_Vendors_Shipping
	 *
	 * @since  1.9.17
	 * @return YITH_Vendors_Shipping
	 */
	function YITH_Vendors_Shipping() { // phpcs:ignore
		return YITH_Vendors_Shipping::instance();
	}
}

YITH_Vendors_Shipping();
