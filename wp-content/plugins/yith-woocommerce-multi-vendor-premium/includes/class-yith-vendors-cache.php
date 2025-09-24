<?php
/**
 * YITH Vendors Cache class. Implements tool for advanced cache management.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Cache' ) ) {
	/**
	 * YITH_Vendors_Cache class
	 */
	class YITH_Vendors_Cache {

		/**
		 * Persistent cache flag.
		 *
		 * @since 4.0.0
		 * @var boolean
		 */
		protected $persistent_cache_enabled;

		/**
		 * Cache group versions
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $group_versions;

		/**
		 * Class construct.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function __construct() {
			$this->group_versions           = array();
			$this->persistent_cache_enabled = $this->is_persistent_cache_enabled();
		}

		/**
		 * Check if persistent cache is enabled
		 *
		 * @since  4.0.0
		 * @return boolean
		 */
		protected function is_persistent_cache_enabled() {
			// phpcs:disable WordPress.Security.NonceVerification
			$enabled = true;
			// Check for wp-config constant.
			if ( defined( 'YITH_WPV_PERSISTENT_CACHE' ) ) {
				$enabled = YITH_WPV_PERSISTENT_CACHE;
			}
			// Check for query string. Useful for debug purpose.
			if ( isset( $_REQUEST['yith_wcmv_enable_persistent_cache'] ) ) {
				$enabled = 'yes' === sanitize_text_field( wp_unslash( $_REQUEST['yith_wcmv_enable_persistent_cache'] ) );
			}

			return apply_filters( 'yith_wcmv_enable_persistent_cache', $enabled );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Create a cache key based on given arguments. Useful for cache query.
		 *
		 * @since  4.0.0
		 * @param mixed $args An array of arguments to use for build a cache key.
		 * @return string
		 */
		public function build_key( $args ) {
			$string = ! is_array( $args ) ? (string) $args : http_build_query( $args );
			return md5( $string );
		}

		/**
		 * Append version to given key
		 *
		 * @since  4.0.0
		 * @param string $key   The key to versioning.
		 * @param string $group The group of the key.
		 * @return string
		 */
		protected function versioned_key( $key, $group ) {
			$version = $this->get_cache_version( $group );
			return "{$key}_{$version}";
		}

		/**
		 * Get transient version.
		 *
		 * @since  4.0.0
		 * @param string  $group   Name for the group of cached items.
		 * @param boolean $refresh (Optional) True to force a new version. Default is false.
		 * @return string transient version based on time(), 10 digits.
		 */
		protected function get_cache_version( $group, $refresh = false ) {
			$option_name = "yith_wcmv_{$group}_cache_version";
			if ( $refresh ) {
				$this->group_versions[ $group ] = (string) time();
				update_option( $option_name, $this->group_versions[ $group ], false );
			} else {
				$this->group_versions[ $group ] = get_option( $option_name, false );
				// If empty version, force a refresh.
				if ( empty( $this->group_versions[ $group ] ) ) {
					$this->get_cache_version( $group, true );
				}
			}

			return $this->group_versions[ $group ];
		}

		/**
		 * Set cache
		 *
		 * @since  4.0.0
		 * @param string  $key    The cache key.
		 * @param mixed   $value  The cache value to store.
		 * @param string  $group  (Optional) The cache group key. Default is vendors.
		 * @param integer $expire (Optional) The expire time. Useful for persistent cache. Default is 0.
		 * @return boolean True on success, false on failure.
		 */
		public function set( $key, $value, $group = 'vendors', $expire = 0 ) {
			// It is a persistent cache, set DB transient.
			if ( $expire && $this->persistent_cache_enabled ) {
				$this->set_transient( $key, $value, $group, $expire );
			}

			$key = $this->versioned_key( $key, $group );
			return wp_cache_set( $key, $value, "yith_wcmv_{$group}", $expire );
		}

		/**
		 * Set transient.
		 *
		 * @since  4.0.0
		 * @param string  $transient  Transient name. Expected to not be SQL-escaped.
		 *                            Must be 172 characters or fewer in length.
		 * @param mixed   $value      Transient value. Must be serializable if non-scalar.
		 *                            Expected to not be SQL-escaped.
		 * @param string  $group      (Optional) Where the cache contents are grouped. Default vendors.
		 * @param integer $expire     (Optional) Time until expiration in seconds. Default 0 (no expiration).
		 * @return bool True if the value was set, false otherwise.
		 */
		public function set_transient( $transient, $value, $group = 'vendors', $expire = 0 ) {
			$version         = self::get_cache_version( $group );
			$versioned_value = array(
				'version' => $version,
				'value'   => $value,
			);

			return set_transient( "yith_wcmv_{$group}_{$transient}", $versioned_value, $expire );
		}

		/**
		 * Set cache for a vendor.
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor ID.
		 * @param string  $key       The cache key.
		 * @param mixed   $value     The cache key value eto set.
		 * @return bool True on success, false on failure.
		 */
		public function set_vendor_cache( $vendor_id, $key, $value ) {
			$cache_key   = "vendor_{$vendor_id}";
			$cache_value = $this->get( $cache_key, 'vendors' );
			if ( empty( $cache_value ) ) {
				$cache_value = array();
			}

			// Cache for vendor is stored as array.
			$cache_value[ $key ] = $value;
			return $this->set( $cache_key, $cache_value );
		}

		/**
		 * Get cache value
		 *
		 * @since  4.0.0
		 * @param string $key   The cache key.
		 * @param string $group (Optional) The cache group key. Default is vendors.
		 * @return mixed The cache value, false if no cache found.
		 */
		public function get( $key, $group = 'vendors' ) {
			$versioned_key = $this->versioned_key( $key, $group );
			$value         = wp_cache_get( $versioned_key, "yith_wcmv_{$group}" );

			if ( $this->persistent_cache_enabled && false === $value ) {
				return $this->get_transient( $key, $group );
			}

			return $value;
		}

		/**
		 * Get transient.
		 *
		 * @since  4.0.0
		 * @param string $transient  Transient name. Expected to not be SQL-escaped.
		 *                           Must be 172 characters or fewer in length.
		 * @param string $group      (Optional) Where the cache contents are grouped. Default vendors.
		 * @return mixed Value of transient.
		 */
		public function get_transient( $transient, $group = 'vendors' ) {
			$transient_value = get_transient( "yith_wcmv_{$group}_{$transient}" );
			// Transient should be versioned and version should be the current one.
			if ( empty( $transient_value['version'] ) || $this->get_cache_version( $group ) !== $transient_value['version'] ) {
				return false;
			}

			return $transient_value['value'];
		}

		/**
		 * Get cache for a vendor.
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor ID.
		 * @param string  $key       The cache key.
		 * @return mixed|false The cache contents on success, false on failure to retrieve contents.
		 */
		public function get_vendor_cache( $vendor_id, $key ) {
			$cache_value = $this->get( "vendor_{$vendor_id}", 'vendors', true );
			return isset( $cache_value[ $key ] ) ? $cache_value[ $key ] : false;
		}

		/**
		 * Delete cache key
		 *
		 * @since  4.0.0
		 * @param string $key   The cache key.
		 * @param string $group (Optional) The cache group key. Default is vendors.
		 */
		public function delete( $key, $group = 'vendors' ) {
			if ( $this->persistent_cache_enabled ) {
				delete_transient( "yith_wcmv_{$group}_{$key}" );
			}

			$key = $this->versioned_key( $key, $group );
			return wp_cache_delete( $key, "yith_wcmv_{$group}" );
		}

		/**
		 * Delete cache for a vendor.
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor ID.
		 * @return bool True on successful removal, false on failure.
		 */
		public function delete_vendor_cache( $vendor_id ) {
			return $this->delete( "vendor_{$vendor_id}" );
		}

		/**
		 * Flush all cache
		 *
		 * @since  4.0.0
		 * @param string $group The name of the group of transient we need to invalidate.
		 */
		public function flush( $group ) {
			$this->get_cache_version( $group, true );
		}
	}
}
