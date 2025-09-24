<?php
/**
 * Class YITH_YWGC_Install
 * Installation related functions and actions.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_YWGC_Install' ) ) {
	/**
	 * YITH_YWGC_Install class.
	 *
	 * @since 3.0.0
	 */
	class YITH_YWGC_Install {

		/**
		 * The updates to fire.
		 *
		 * @var callable[][]
		 */
		private $db_updates = array(
			/** '4.7.0' => array(
				'yith_ywgc_update_show_update_modal_3',
			), */
		);

		/**
		 * Callbacks to be fired soon, instead of being scheduled.
		 *
		 * @var callable[]
		 */
		private $soon_callbacks = array(
			// 'yith_ywgc_update_show_update_modal_3',
		);

		/**
		 * The version option.
		 */
		const VERSION_OPTION = 'yith_ywgc_version';

		/**
		 * The version option.
		 */
		const DB_VERSION_OPTION = 'yith_ywgc_db_version';

		/**
		 * The update scheduled option.
		 */
		const DB_UPDATE_SCHEDULED_OPTION = 'yith_ywgc_db_update_scheduled_for';

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Install
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * YITH_YWGC_Install constructor.
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'check_version' ), 5 );
			add_action( 'yith_ywgc_run_update_callback', array( $this, 'run_update_callback' ) );
			add_action( 'wp_loaded', array( $this, 'maybe_flush_rewrite_rules' ) );
		}

		/**
		 * Check the plugin version and run the updater is required.
		 * This check is done on all requests and runs if the versions do not match.
		 */
		public function check_version() {
			if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( self::VERSION_OPTION, '1.0.0' ), YITH_YWGC_VERSION, '<' ) ) {
				$this->install();
				do_action( 'yith_ywgc_updated' );
			}
		}

		/**
		 * Get list of DB update callbacks.
		 *
		 * @return array
		 */
		public function get_db_update_callbacks() {
			return $this->db_updates;
		}

		/**
		 * Install Gift Cards.
		 */
		public function install() {
			// Check if we are not already running this routine.
			if ( 'yes' === get_transient( 'yith_ywgc_installing' ) ) {
				return;
			}

			set_transient( 'yith_ywgc_installing', 'yes', MINUTE_IN_SECONDS * 10 );

			if ( ! defined( 'YITH_YWGC_INSTALLING' ) ) {
				define( 'YITH_YWGC_INSTALLING', true );
			}

			$this->update_version();
			$this->maybe_update_db_version();

			$this->queue_flush_rewrite_rules();

			delete_transient( 'yith_ywgc_installing' );

			do_action( 'yith_ywgc_installed' );
		}

		/**
		 * Update version to current.
		 */
		private function update_version() {
			delete_option( self::VERSION_OPTION );
			add_option( self::VERSION_OPTION, YITH_YWGC_VERSION );
		}

		/**
		 * The DB needs to be updated?
		 *
		 * @return bool
		 */
		public function needs_db_update() {
			$current_db_version = get_option( self::DB_VERSION_OPTION, null );

			return ! is_null( $current_db_version ) && version_compare( $current_db_version, $this->get_greatest_db_version_in_updates(), '<' );
		}

		/**
		 * Update DB version to current.
		 *
		 * @param string|null $version New DB version or null.
		 */
		public static function update_db_version( $version = null ) {
			delete_option( self::DB_VERSION_OPTION );
			add_option( self::DB_VERSION_OPTION, is_null( $version ) ? YITH_YWGC_DB_CURRENT_VERSION : $version );

			// Delete "update scheduled for" option, to allow future update scheduling.
			delete_option( self::DB_UPDATE_SCHEDULED_OPTION );
		}

		/**
		 * Get DB Version
		 *
		 * @return string
		 */
		public static function get_db_version() {
			return get_option( self::DB_VERSION_OPTION );
		}

		/**
		 * Maybe update db
		 */
		private function maybe_update_db_version() {
			if ( $this->needs_db_update() ) {
				$this->update();
			} else {
				$this->update_db_version();
			}
		}

		/**
		 * Retrieve the major version in update callbacks.
		 *
		 * @return string
		 */
		private function get_greatest_db_version_in_updates() {
			$update_callbacks = $this->get_db_update_callbacks();
			$update_versions  = array_keys( $update_callbacks );
			usort( $update_versions, 'version_compare' );

			return end( $update_versions );
		}

		/**
		 * Return true if the callback needs to be fired soon, instead of being scheduled.
		 *
		 * @param string $callback The callback name.
		 *
		 * @return bool
		 */
		private function is_soon_callback( $callback ) {
			return in_array( $callback, $this->soon_callbacks, true );
		}

		/**
		 * Push all needed DB updates to the queue for processing.
		 */
		private function update() {
			$current_db_version   = get_option( self::DB_VERSION_OPTION );
			$loop                 = 0;
			$greatest_version     = $this->get_greatest_db_version_in_updates();
			$is_already_scheduled = get_option( self::DB_UPDATE_SCHEDULED_OPTION, '' ) === $greatest_version;

			if ( ! $is_already_scheduled ) {
				foreach ( $this->get_db_update_callbacks() as $version => $update_callbacks ) {
					if ( version_compare( $current_db_version, $version, '<' ) ) {
						foreach ( $update_callbacks as $update_callback ) {
							if ( $this->is_soon_callback( $update_callback ) ) {
								$this->run_update_callback( $update_callback );
							} else {
								WC()->queue()->schedule_single(
									time() + $loop,
									'yith_ywgc_run_update_callback',
									array(
										'update_callback' => $update_callback,
									),
									'yith-ywgc-db-updates'
								);
								++$loop;
							}
						}
					}
				}

				update_option( self::DB_UPDATE_SCHEDULED_OPTION, $greatest_version );
			}
		}

		/**
		 * Run an update callback when triggered by ActionScheduler.
		 *
		 * @param string $callback Callback name.
		 */
		public function run_update_callback( $callback ) {
			include_once YITH_YWGC_DIR . 'includes/functions.yith-ywgc-update.php';

			if ( is_callable( $callback ) ) {
				self::run_update_callback_start( $callback );
				$result = (bool) call_user_func( $callback );
				self::run_update_callback_end( $callback, $result );
			}
		}

		/**
		 * Triggered when a callback will run.
		 *
		 * @param string $callback Callback name.
		 */
		protected function run_update_callback_start( $callback ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
			if ( ! defined( 'YITH_YWGC_UPDATING' ) ) {
				define( 'YITH_YWGC_UPDATING', true );
			}
		}

		/**
		 * Triggered when a callback has ran.
		 *
		 * @param string $callback Callback name.
		 * @param bool   $result   Return value from callback. Non-false need to run again.
		 */
		protected function run_update_callback_end( $callback, $result ) {
			if ( $result ) {
				WC()->queue()->add(
					'yith_ywgc_run_update_callback',
					array(
						'update_callback' => $callback,
					),
					'yith-ywgc-db-updates'
				);
			}
		}

		/**
		 * Flush rules if the event is queued.
		 */
		public function maybe_flush_rewrite_rules() {
			if ( 'yes' === get_option( 'yith_ywgc_queue_flush_rewrite_rules' ) ) {
				update_option( 'yith_ywgc_queue_flush_rewrite_rules', 'no' );
				self::flush_rewrite_rules();
			}
		}

		/**
		 * Flush rewrite rules.
		 */
		public function flush_rewrite_rules() {
			flush_rewrite_rules();
		}

		/**
		 * Queue flushing rewrite rules.
		 */
		public function queue_flush_rewrite_rules() {
			update_option( 'yith_ywgc_queue_flush_rewrite_rules', 'yes' );
		}
	}
}
