<?php
/**
 * YITH_Vendors_Modules
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
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

if ( ! class_exists( 'YITH_Vendors_Modules' ) ) {
	/**
	 * Handle plugin modules.
	 */
	class YITH_Vendors_Modules extends YITH_Vendors_Modules_Legacy {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * An array of available modules
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $modules = array();

		/**
		 * An array of active modules
		 *
		 * @since 4.0.0
		 * @var array array module => boolean | True if active, false otherwise
		 */
		protected $active_modules = array();

		/**
		 * An array of modules admin tabs to add
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $modules_admin_tabs = array();

		/**
		 * Modules files list
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $mapped_files = array();

		/**
		 * Constructor
		 *
		 * @since  4.0.0
		 * @return void
		 */
		private function __construct() {
			$this->modules = include YITH_WPV_PATH . 'plugin-options/modules.php';
			if ( ! empty( $this->modules ) ) {
				$this->init_hooks();
				$this->load_modules();
			}
		}

		/**
		 * Init modules hooks
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function init_hooks() {
			// Add mapped files to autoload.
			add_action( 'yith_wcmv_autoload_mapped_files', array( $this, 'add_mapped_files' ), 10, 1 );
			// Handle AJAX activation.
			add_action( 'yith_wcmv_admin_ajax_module_active_switch', array( $this, 'activation_handler' ) );

			// Admin tab settings.
			add_filter( 'yith_wcmv_admin_panel_tabs', array( $this, 'modules_admin_tabs' ) );
			add_filter( 'yith_wcmv_admin_panel_tabs', array( $this, 'modules_tab' ), 25 );
			add_action( 'yith_wcmv_modules_panel_tab', array( $this, 'modules_tab_content' ), 99 );
		}

		/**
		 * Load available modules
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function load_modules() {
			foreach ( $this->modules as $key => $module ) {
				$ukey = str_replace( '-', '_', $key );

				// Then check if module is active. If not, do not go further.
				if ( ! $this->is_module_active( $key ) ) {
					continue;
				}

				if ( ! empty( $module['admin_tabs'] ) ) {
					$this->modules_admin_tabs[ $key ] = $module['admin_tabs'];
				}

				// Add mapped files if any.
				if ( ! empty( $module['autoload'] ) ) {
					$this->mapped_files = array_merge( $this->mapped_files, $module['autoload'] );
				}

				// Includes file if any.
				if ( ! empty( $module['includes'] ) ) {
					// Base path.
					$path = YITH_WPV_PATH . 'includes/modules/';

					foreach ( $module['includes'] as $section => $files ) {
						if ( ! $this->is_section( $section ) ) {
							continue;
						}

						if ( ! is_array( $files ) ) {
							$files = explode( ',', $files );
						}

						// Include files.
						foreach ( $files as $file ) {
							if ( file_exists( $path . $file ) ) {
								include_once $path . $file;
							}
						}
					}
				}
			}
		}

		/**
		 * Return the module active option name. Get backward compatibility with the old system.
		 *
		 * @since  4.0.0
		 * @param string $key The module key.
		 * @return string
		 */
		protected function get_module_option_name( $key ) {
			$ukey = str_replace( '-', '_', $key );
			return "yith_wpv_vendors_option_{$ukey}_management";
		}

		/**
		 * Check current section active for modules
		 *
		 * @since  4.0.0
		 * @param string $section The section to check.
		 * @return boolean
		 */
		protected function is_section( $section ) {
			// Handle admin request.
			switch ( $section ) {
				case 'admin':
					$value = yith_wcmv_is_admin_request();
					break;
				case 'frontend':
					$value = yith_wcmv_is_frontend_request();
					break;
				default:
					$value = true;
					break;
			}

			return $value;
		}

		/**
		 * Get all modules
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_all_modules() {
			return $this->modules;
		}

		/**
		 * Get available modules
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_available_modules() {
			return array_keys( array_filter( $this->available_modules ) );
		}

		/**
		 * Is module active?
		 *
		 * @since  4.0.0
		 * @param string $module The module to check.
		 * @return boolean
		 */
		public function is_module_active( $module ) {
			if ( ! isset( $this->active_modules[ $module ] ) ) {
				$option                          = $this->get_module_option_name( $module );
				$this->active_modules[ $module ] = 'yes' === get_option( $option, 'no' );
			}
			return $this->active_modules[ $module ];
		}

		/**
		 * Get active modules
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_active_modules() {
			return array_keys( array_filter( $this->active_modules ) );
		}

		/**
		 * Handle AJAX module activation
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function activation_handler() {

			$module = isset( $_POST['module'] ) ? sanitize_text_field( wp_unslash( $_POST['module'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$status = ( isset( $_POST['status'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['status'] ) ) ) ? 'yes' : 'no';  // phpcs:ignore WordPress.Security.NonceVerification
			if ( empty( $module ) ) {
				wp_send_json_error();
			}

			// Update option.
			$option = $this->get_module_option_name( $module );
			update_option( $option, $status );
			// Unset current stored status to let check again option.
			unset( $this->active_modules[ $module ] );

			// Update capabilities.
			YITH_Vendors_Capabilities::update_capabilities();

			$module = $this->modules[ $module ];
			$data   = ! empty( $module['admin_tabs'] ) ? array( 'reload' => true ) : null;

			wp_send_json_success( $data );
		}

		/**
		 * Add mapped files
		 *
		 * @since  4.0.0
		 * @param array $files An array of mapped files for autoload.
		 * @return array
		 */
		public function add_mapped_files( $files ) {
			return array_merge( $files, $this->mapped_files );
		}

		/**
		 * Add main modules tab to plugin panel
		 *
		 * @since  5.0.0
		 * @param array $tabs An array of admin tabs.
		 * @return array
		 */
		public function modules_tab( $tabs ) {
			return array_merge(
				$tabs,
				array(
					'modules' => _x( 'Modules', '[Admin]Panel tab name', 'yith-woocommerce-product-vendors' ),
				)
			);
		}

		/**
		 * Add admin module tabs to plugin panel
		 *
		 * @since  5.0.0
		 * @param array $tabs An array of admin tabs.
		 * @return array
		 */
		public function modules_admin_tabs( $tabs ) {

			$position = array_search( 'vendors', array_keys( $tabs ), true );
			if ( false !== $position ) {
				++$position;
				$first = array_slice( $tabs, 0, $position, true );
				$last  = array_slice( $tabs, $position, count( $tabs ), true );

				$tabs = array_merge( $first, $this->modules_admin_tabs, $last );
			} else {
				$tabs = array_merge( $tabs, $this->modules_admin_tabs );
			}

			return $tabs;
		}


		/**
		 * Modules tab content
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function modules_tab_content() {
			yith_wcmv_include_admin_template( 'modules-list', array( 'modules' => $this->get_all_modules() ) );
		}
	}
}
