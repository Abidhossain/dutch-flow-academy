<?php
/**
 * YITH Vendors Logger. Handle log action using WC_Logger.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Logger' ) ) {
	/**
	 * This class is a helper to manager the plugin log system.
	 */
	class YITH_Vendors_Logger {

		/**
		 * WC logger instance
		 *
		 * @var WC_Logger
		 */
		public static $logger = null;

		/**
		 * Init the WC Logger
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function init() {
			if ( is_null( self::$logger ) && class_exists( 'WC_Logger' ) ) {
				self::$logger = new WC_Logger();
			}
		}

		/**
		 * Log a message
		 *
		 * @since  4.0.0
		 * @param string $message The message to log.
		 * @param string $type    The message type.
		 * @return void
		 */
		public static function log( $message, $type = 'error' ) {
			self::init();

			if ( ! is_null( self::$logger ) ) {
				self::$logger->log(
					$type,
					$message,
					array(
						'source' => 'yith-vendors-log',
					)
				);
			}
		}
	}
}

