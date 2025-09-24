<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Autoloader' ) ) {
	/**
	 * @class      YITH_Vendors_Autoloader
	 * @since      4.0.0
	 * @author     YITH
	 * @package YITH\MultiVendor
	 */
	class YITH_Vendors_Autoloader {

		/**
		 * Constructor
		 *
		 * @since  4.0.0
		 */
		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Get mapped file. Array of class => file to use on autoload.
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_mapped_files() {
			return apply_filters( 'yith_wcmv_autoload_mapped_files', array() );
		}

		/**
		 * Autoload callback
		 *
		 * @since  1.0.0
		 * @param string $class The class to load.
		 */
		public function autoload( $class ) {
			$class = str_replace( '_', '-', strtolower( $class ) );
			if ( false === strpos( $class, 'yith-vendor' ) ) {
				return; // Pass over.
			}

			$base_path = YITH_WPV_PATH . 'includes/';
			// Check first for mapped files.
			$mapped = $this->get_mapped_files();
			if ( isset( $mapped[ $class ] ) ) {
				$file = $base_path . $mapped[ $class ];
			} else {
				// Handle traits.
				if ( false !== strpos( $class, 'trait' ) ) {
					$class = str_replace( '-trait', '', $class );
					$file  = $base_path . 'traits/trait-' . $class . '.php';
				} else { // Handle classes.
					if ( false !== strpos( $class, 'legacy' ) ) {
						$base_path .= 'legacy/';
					} elseif ( false !== strpos( $class, '-rest-' ) ) {
						$base_path .= 'rest-api/';
						if ( false !== strpos( $class, 'reports' ) ) {
							$base_path .= 'reports/';
						}
					} elseif ( false !== strpos( $class, 'data-store' ) ) {
						$base_path .= 'data-stores/';
					} elseif ( false !== strpos( $class, 'frontend-manager' ) ) {
						$base_path .= 'frontend-manager/';
					} elseif ( false !== strpos( $class, 'theme' ) ) {
						$base_path .= 'theme-support/';
					} elseif ( false !== strpos( $class, 'admin' ) || false !== strpos( $class, 'privacy' ) ) {
						$base_path .= 'admin/';
					} elseif ( false !== strpos( $class, 'list-table' ) ) {
						$base_path .= 'admin/list-tables/';
					} elseif ( false !== strpos( $class, 'widget' ) ) {
						$base_path .= 'widgets/';
					} elseif ( false !== strpos( $class, 'gateway' ) ) {
						$base_path .= 'gateways/';
					}

					$file = $base_path . 'class-' . $class . '.php';
				}
			}

			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	}
}

new YITH_Vendors_Autoloader();
