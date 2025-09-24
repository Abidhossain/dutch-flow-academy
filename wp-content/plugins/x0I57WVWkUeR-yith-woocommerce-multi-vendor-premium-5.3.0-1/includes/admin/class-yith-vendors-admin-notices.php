<?php
/**
 * YITH Vendors Admin Notices class. Handle plugin notices admin side.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Notices' ) ) {
	/**
	 * YITH Vendors Admin Notices class
	 */
	class YITH_Vendors_Admin_Notices {

		/**
		 * An array of registered notices.
		 *
		 * @since  4.0.0
		 * @access protected
		 * @var array
		 */
		protected static $notices = array();

		/**
		 * Init class
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function init() {
			self::load();
			// Print notices if any.
			add_action( 'admin_notices', array( __CLASS__, 'print' ), 99 );
			// Store notice not printed.
			add_action( 'shutdown', array( __CLASS__, 'store' ) );
		}

		/**
		 * Get the transient name
		 *
		 * @since  4.0.0
		 * @return string
		 */
		protected static function get_transient_name() {
			return 'yith_wcmv_admin_notices_' . get_current_user_id();
		}

		/**
		 * Load stored notices if any
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected static function load() {
			// Check for stored value.
			$stored = get_transient( self::get_transient_name() );
			if ( is_array( $stored ) ) {
				self::$notices = $stored;
			}
		}

		/**
		 * Add a new notice class
		 *
		 * @since  4.0.0
		 * @param string $message The message to add.
		 * @param string $type    (Optional) The message type. Possible values: success, error, warning. Default is success.
		 * @return void
		 */
		public static function add( $message, $type = 'success' ) {
			if ( empty( $message ) ) {
				return;
			}

			self::$notices[ $type ][] = $message;
		}

		/**
		 * Print registered notices for current user.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function print() {

			if ( empty( self::$notices ) ) {
				return;
			}

			foreach ( self::$notices as $type => $notices ) {

				foreach ( $notices as $notice ) {
					?>
					<div class="yith-wcmv-admin-notice notice-<?php echo esc_attr( $type ); ?>">
						<?php echo wp_kses_post( $notice ); ?>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text"><?php echo esc_html__( 'Dismiss this notice.', 'yith-woocommerce-product-vendors' ); ?></span>
						</button>
					</div>
					<?php
				}
			}

			self::clear();
		}

		/**
		 * Store registered notices
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function store() {
			if ( ! empty( self::$notices ) ) {
				set_transient( self::get_transient_name(), self::$notices, HOUR_IN_SECONDS );
			}
		}

		/**
		 * Clear stored notices
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function clear() {
			self::$notices = array();
			delete_transient( self::get_transient_name() );
		}
	}
}
