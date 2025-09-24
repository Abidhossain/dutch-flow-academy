<?php
/**
 * YITH Vendors Vacation module class
 *
 * @since      Version 1.0.0
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


if ( ! class_exists( 'YITH_Vendors_Vacation' ) ) {
	/**
	 * YITH Vendors Vacation module class
	 */
	class YITH_Vendors_Vacation {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Admin Instance
		 *
		 * @since 1.9.17
		 * @var null| YITH_Vendors_Vacation_Admin
		 */
		public $admin = null;

		/**
		 * Construct
		 *
		 * @since  4.0.0
		 * @return void
		 */
		private function __construct() {
			if ( yith_wcmv_is_admin_request() ) {
				$this->admin = new YITH_Vendors_Vacation_Admin();
			}
			$this->register_frontend_actions();

			add_filter( 'yith_wcmv_get_vendors_query_args', array( $this, 'query_by_vacation_meta' ), 10, 2 );
		}

		/**
		 * Register admin hooks and filters
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function register_frontend_actions() {
			// Filter Product in loop.
			if ( 'no' !== get_option( 'yith_wpv_hide_vendors_products_on_vacation', 'no' ) ) {
				add_filter( 'yith_wcmv_to_exclude_terms_in_loop', array( $this, 'check_vendors_in_vacation' ), 20, 1 );
			}

			// Add vacation message on store header.
			add_action( 'yith_wcmv_after_vendor_header', array( $this, 'add_vacation_message' ), 10, 2 );

			// Hide add to cart button.
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'hide_loop_add_to_cart' ), 10, 2 );
			add_filter( 'wc_get_template', array( $this, 'hide_single_add_to_cart' ), 30, 5 );

			// Add to cart validation on vacation.
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'avoid_add_to_cart' ), 10, 3 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'add_vacation_template' ), 25 );
		}

		/**
		 * Backward vacation meta compatibility
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return mixed
		 */
		public function backward_schedule_compatibility( $vendor ) {
			$value = array_filter(
				array(
					'from' => $vendor->get_meta( 'vacation_start_date' ),
					'to'   => $vendor->get_meta( 'vacation_end_date' ),
				)
			);

			if ( empty( $value ) ) {
				return array();
			}

			// Save the new.
			update_term_meta( $vendor->get_id(), 'vacation_schedule_enabled', 'yes' );
			update_term_meta( $vendor->get_id(), 'vacation_schedule', $value );
			// Delete the old metas.
			delete_term_meta( $vendor->get_id(), 'vacation_start_date' );
			delete_term_meta( $vendor->get_id(), 'vacation_end_date' );

			return $value;
		}

		/**
		 * Get vendor vacation schedule
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return array
		 */
		protected function get_schedule( $vendor ) {
			if ( 'no' === $vendor->get_meta( 'vacation_schedule_enabled' ) ) {
				return array();
			}

			$schedule = $vendor->get_meta( 'vacation_schedule' );
			if ( empty( $schedule ) ) {
				$schedule = $this->backward_schedule_compatibility( $vendor );
			}
			$schedule = ( ! empty( $schedule ) && is_array( $schedule ) ) ? $schedule : array();

			return apply_filters( 'yith_wcmv_get_vacation_schedule', $schedule, $vendor );
		}

		/**
		 * Check if a vendor is on vacation
		 *
		 * @since  4.0.0
		 * @param null|YITH_Vendor $vendor Vendor object or null.
		 * @return boolean
		 */
		public function vendor_is_on_vacation( $vendor = null ) {
			if ( ! ( $vendor instanceof YITH_Vendor ) ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			}

			if ( ! $vendor || ! $vendor->is_valid() || 'no' === $vendor->get_meta( 'vacation_enabled' ) ) {
				return false;
			}

			$schedule = $this->get_schedule( $vendor );
			if ( ! empty( $schedule ) ) {
				$vacation_start = isset( $schedule['from'] ) ? strtotime( $schedule['from'] ) : 0;
				$vacation_end   = isset( $schedule['to'] ) ? strtotime( $schedule['to'] ) : 0;
				$today          = strtotime( date( 'Y-m-d', time() ) ); // phpcs:ignore

				return ( $vacation_start && $today >= $vacation_start ) && ( ! $vacation_end || $today <= $vacation_end );
			}

			return 'yes' === $vendor->get_meta( 'vacation_enabled' ); // Double check meta to be backward compatible with the old system.
		}

		/**
		 * Check if vendor is on vacation
		 *
		 * @since  4.0.0
		 * @param array $to_exclude An array of vendor ids to exclude.
		 * @return array
		 */
		public function check_vendors_in_vacation( $to_exclude ) {

			if ( yith_wcmv_is_vendor_page() ) {
				return $to_exclude;
			}

			// Continue using vacation_selling meta to backward compatibility instead of vacation_enabled.
			$vendors     = yith_wcmv_get_vendors(
				array(
					'vacation_selling' => 'disabled',
					'number'           => -1,
				)
			);
			$on_vacation = array();
			if ( ! empty( $vendors ) ) {
				foreach ( $vendors as $vendor ) {
					if ( $this->vendor_is_on_vacation( $vendor ) ) {
						$on_vacation[] = $vendor->get_id();
					}
				}
			}

			return ! empty( $on_vacation ) ? array_unique( array_merge( $to_exclude, $on_vacation ) ) : $to_exclude;
		}

		/**
		 * Change single add to cart template
		 *
		 * @since    4.0.0
		 * @param string $located       Current template location.
		 * @param string $template_name Template name.
		 * @param array  $args          Arguments. (default: array).
		 * @param string $template_path Template path. (default: '').
		 * @param string $default_path  Default path. (default: '').
		 * @return string Located file
		 */
		public function hide_single_add_to_cart( $located, $template_name, $args, $template_path, $default_path ) {

			if ( is_singular( 'product' ) && preg_match( '/single-product\/add-to-cart\/(\S+).php/', $template_name ) ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'product' );
				if ( $this->vendor_is_on_vacation( $vendor ) && 'disabled' === $vendor->get_meta( 'vacation_selling' ) ) {
					$located = wc_locate_template( 'single-product/store-vacation.php', WC()->template_path(), YITH_WPV_TEMPLATE_PATH . '/woocommerce/' );
				}
			}

			return $located;
		}

		/**
		 * Add vacation part to add to cart template
		 *
		 * @since    4.0.0
		 * @return void
		 */
		public function add_vacation_template() {
			$vendor = yith_wcmv_get_vendor( 'current', 'product' );
			if ( $this->vendor_is_on_vacation( $vendor ) && 'enabled' === $vendor->get_meta( 'vacation_selling' ) ) {
				yith_wcmv_get_template( 'store-vacation', array( 'vendor' => $vendor ), 'woocommerce/single-product' );
			}
		}

		/**
		 * Add vacation part to add to cart template
		 *
		 * @since  4.0.0
		 * @param string     $add_to_cart Add to cart html.
		 * @param WC_Product $product     Current product in loop.
		 * @return string
		 */
		public function hide_loop_add_to_cart( $add_to_cart, $product ) {
			$vendor = yith_wcmv_get_vendor( $product, 'product' );
			if ( $this->vendor_is_on_vacation( $vendor ) && 'disabled' === $vendor->get_meta( 'vacation_selling' ) ) {
				$add_to_cart = '';
			}

			return $add_to_cart;
		}

		/**
		 * Block add to cart for vendor on vacation
		 *
		 * @since  4.0.0
		 * @param boolean $validate   True for passed validation, false otherwise.
		 * @param integer $product_id The product id.
		 * @param integer $quantity   The product quantity.
		 * @return boolean
		 */
		public function avoid_add_to_cart( $validate, $product_id, $quantity ) {
			$vendor = yith_wcmv_get_vendor( $product_id, 'product' );
			if ( ! $validate && $this->vendor_is_on_vacation( $vendor ) && 'disabled' === $vendor->get_meta( 'vacation_selling' ) ) {
				return false;
			}

			return $validate;
		}

		/**
		 * Add vacation message on store header
		 *
		 * @since  4.0.0
		 * @param array       $args   An array of template arguments.
		 * @param YITH_Vendor $vendor Current vendor object.
		 * @return void
		 */
		public function add_vacation_message( $args, $vendor ) {
			if ( $this->vendor_is_on_vacation( $vendor ) ) {
				$vendor_vacation_message = call_user_func( '__', $vendor->get_meta( 'vacation_message' ), 'yith-woocommerce-product-vendors' );
				yith_wcmv_get_template( 'store-vacation', array( 'vacation_message' => $vendor_vacation_message ), 'woocommerce/loop' );
			}
		}

		/**
		 * Extend the vendors query by vendor meta
		 *
		 * @since 5.0.0
		 * @param array $query_args Current query args array.
		 * @param array $param The query params.
		 * @return array
		 */
		public function query_by_vacation_meta( $query_args, $param ) {
			if ( ! empty( $param['vacation_selling'] ) && 'disabled' === $param['vacation_selling'] ) {
				$query_args['meta_query'][] = array(
					'key'   => 'vacation_selling',
					'value' => $param['vacation_selling'],
				);
			}
			return $query_args;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  4.0.0
 * @return YITH_Vendors_Vacation
 */
if ( ! function_exists( 'YITH_Vendors_Vacation' ) ) {
	function YITH_Vendors_Vacation() { // phpcs:ignore
		return YITH_Vendors_Vacation::instance();
	}
}

YITH_Vendors_Vacation();
