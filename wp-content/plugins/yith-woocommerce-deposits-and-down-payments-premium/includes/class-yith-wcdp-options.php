<?php
/**
 * Deposits options class
 * Provides tools for deposit/balance calculation
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Options' ) ) {
	/**
	 * Deposits class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Options {

		/**
		 * An array of supported options, in the form of [
		 *     option_id => [
		 *         default => Default value,
		 *         meta    => Meta name,
		 *         option  => Option name
		 *     ]
		 * ]
		 *
		 * @var array
		 */
		protected static $supported;

		/**
		 * Returns supported options
		 *
		 * @return array
		 */
		public static function get_supported() {
			if ( empty( self::$supported ) ) {
				self::$supported = apply_filters(
					'yith_wcdp_supported_options',
					array(
						'enable_deposit'                => array(
							'default' => 'no',
							'meta'    => '_enable_deposit',
							'option'  => 'general_deposit_enable',
						),
						'force_deposit'                 => array(
							'default' => 'no',
							'meta'    => '_force_deposit',
							'option'  => 'general_deposit_force',
						),
						'deposit_amount'                => array(
							'default'  => array(),
							'option'   => 'general_deposit_amount',
							'override' => false,
						),
						'deposit_default'               => array(
							'default' => 'no',
							'meta'    => '_deposit_default',
							'option'  => 'general_deposit_default',
						),
						'show_product_notes'            => array(
							'default'  => 'yes',
							'meta'     => '_show_product_notes',
							'option'   => 'deposit_labels_show_product_notes',
							'override' => 'deposit',
						),
						'product_note'                  => array(
							'default'  => '',
							'meta'     => '_product_note',
							'option'   => 'deposit_labels_product_note',
							'override' => 'deposit',
						),
						'create_balance'                => array(
							'default' => 'pending',
							'meta'    => '_create_balance_orders',
							'option'  => 'create_balance',
						),
						'enable_expiration'             => array(
							'default' => 'no',
							'meta'    => '_enable_expiration',
							'option'  => 'deposit_expiration_enable',
						),
						'expiration_type'               => array(
							'default' => 'num_of_days',
							'meta'    => '_expiration_type',
							'option'  => 'deposits_expiration_type',
						),
						'expiration_date'               => array(
							'default' => false,
							'meta'    => '_expiration_date',
							'option'  => 'deposits_expiration_date',
						),
						'expiration_duration'           => array(
							'default' => false,
							'meta'    => '_expiration_duration',
							'option'  => 'deposits_expiration_duration',
						),
						'expiration_product_fallback'   => array(
							'default' => 'disable_deposit',
							'meta'    => '_deposit_expiration_product_fallback',
							'option'  => 'deposits_expiration_product_fallback',
						),
						'notify_expiration'             => array(
							'default' => 'yes',
							'option'  => 'notify_customer_deposit_expiring',
							'meta'    => '_notify_expiration',
						),
						'expiration_notification_limit' => array(
							'default' => '15',
							'option'  => 'notify_customer_deposit_expiring_days_limit',
							'meta'    => '_expiration_notification_limit',
						),
					)
				);
			}

			return self::$supported;
		}

		/**
		 * A list of options values, read from database
		 *
		 * @var array
		 */
		protected static $options = array();

		/**
		 * Reads a specific option value.
		 * If product id is provided, checks if option is overridden in that specific value, and eventually returns product
		 * specific value
		 *
		 * @param string $option     Option to read.
		 * @param int    $product_id Product id (optional).
		 *
		 * @return mixed Option value.
		 */
		public static function get( $option, $product_id = false ) {
			// if option doesn't exists among supported ones, return false.
			if ( ! array_key_exists( $option, self::get_supported() ) ) {
				return false;
			}

			$value = false;

			// first of all, try to retrieve product-specific value.
			if ( $product_id ) {
				$value = self::read_product_property( $product_id, $option );
			}

			if ( ! $value ) {
				$value = self::read_option( $option );
			}

			return $value;
		}

		/**
		 * Read options from database, when required
		 *
		 * @param string $option Option to read.
		 *
		 * @return mixed Option value from db, or default value.
		 */
		public static function read_option( $option ) {
			$supported = self::get_supported();

			// if option doesn't exists among supported ones, return false.
			if ( ! array_key_exists( $option, $supported ) ) {
				return false;
			}

			// if already stored in internal cache, return cached value.
			if ( isset( self::$options[ $option ] ) ) {
				return self::$options[ $option ];
			}

			// retrieve option value.
			$default = isset( $supported[ $option ]['default'] ) ? $supported[ $option ]['default'] : false;
			$name    = isset( $supported[ $option ]['option'] ) ? $supported[ $option ]['option'] : $option;
			$name    = "yith_wcdp_$name";

			$value = get_option( $name, $default );

			// store value for future usage.
			self::$options[ $option ] = $value;

			// return value.
			return $value;
		}

		/**
		 * Read product-specific value of an option, if overridden is enabled
		 *
		 * @param int    $product_id Product id.
		 * @param string $option     Option to read.
		 *
		 * @return mixed Property value; false if not overridden.
		 */
		public static function read_product_property( $product_id, $option ) {
			$supported = self::get_supported();

			// if option doesn't exists among supported ones, return false.
			if ( ! array_key_exists( $option, $supported ) ) {
				return false;
			}

			$current_product = wc_get_product( $product_id );
			$property        = isset( $supported[ $option ]['meta'] ) ? $supported[ $option ]['meta'] : $option;

			if ( ! $current_product ) {
				return false;
			}

			$is_overridden = self::is_property_overridden( $product_id, $option );

			if ( ! $is_overridden && $current_product->is_type( 'variation' ) ) {
				return self::read_product_property( $current_product->get_parent_id(), $option );
			} elseif ( ! $is_overridden ) {
				return false;
			}

			$meta_value = $current_product->get_meta( $property );

			if ( 'default' === $meta_value ) {
				return false;
			}

			return $meta_value;
		}

		/**
		 * Checks if a specific property is overridden by the options of a product.
		 *
		 * @param int    $product_id Id of the product to test.
		 * @param string $option     Name of the option that we want to test.
		 *
		 * @return bool Whether property is overridden by product or not.
		 */
		protected static function is_property_overridden( $product_id, $option ) {
			$supported = self::get_supported();

			// if option doesn't exists among supported ones, return false.
			if ( ! array_key_exists( $option, $supported ) ) {
				return false;
			}

			$current_product = wc_get_product( $product_id );
			$property        = isset( $supported[ $option ]['meta'] ) ? $supported[ $option ]['meta'] : $option;

			if ( ! $current_product ) {
				return false;
			}

			// find correct override meta for the specified property.
			if ( isset( $supported[ $option ]['override'] ) ) {
				$override = $supported[ $option ]['override'];
			} elseif ( false !== strpos( $property, 'balance' ) || false !== strpos( $property, 'expiration' ) ) {
				$override = 'balance';
			} elseif ( false !== strpos( $property, 'deposit' ) ) {
				$override = 'deposit';
			}

			if ( empty( $override ) ) {
				return false;
			}

			// retrieve override meta.
			$meta_value = $current_product->get_meta( "_override_{$override}_options" );

			return yith_plugin_fw_is_true( $meta_value );
		}
	}
}
