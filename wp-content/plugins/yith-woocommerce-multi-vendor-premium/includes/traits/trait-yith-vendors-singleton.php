<?php
/**
 * Traits for handling singleton classes.
 *
 * @since   4.2.0
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

/**
 * YITH_Vendors_Singleton_Trait class.
 *
 * @internal
 */
trait YITH_Vendors_Singleton_Trait {

	/**
	 * Main instance
	 *
	 * @var static|null
	 */
	protected static $instance = null;

	/**
	 * Clone.
	 * Disable class cloning and throw an error on object clone.
	 * The whole idea of the singleton design pattern is that there is a single
	 * object. Therefore, we don't want the object to be cloned.
	 *
	 * @access public
	 * @since  4.2.0
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, 'Something went wrong.', '1.0.0' );
	}

	/**
	 * Wakeup.
	 * Disable unserializing of the class.
	 *
	 * @access public
	 * @since  4.2.0
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, 'Something went wrong.', '1.0.0' );
	}

	/**
	 * Main class instance
	 *
	 * @static
	 * @since  4.2.0
	 * @return static
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}
}
