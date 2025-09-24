<?php
/**
 * Autoloader.
 *
 * @package YITH\Deposits\Classes
 * @version 2.0.0
 */

defined( 'YITH_WCDP' ) || exit;

if ( ! class_exists( 'YITH_WCDP_Autoloader' ) ) {
	/**
	 * Autoloader class.
	 */
	class YITH_WCDP_Autoloader {

		/**
		 * Path to the includes directory.
		 *
		 * @var string
		 */
		private $include_path = '';

		/**
		 * The Constructor.
		 */
		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload' ) );

			$this->include_path = YITH_WCDP_INC;
		}

		/**
		 * Take a class name and turn it into a file name.
		 *
		 * @param string $class Class name.
		 *
		 * @return string
		 */
		private function get_file_name_from_class( $class ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound
			$base     = str_replace( '_', '-', $class );
			$filename = 'class-' . $base . '.php';

			return $filename;
		}

		/**
		 * Include a class file.
		 *
		 * @param string $path File path.
		 *
		 * @return bool Successful or not.
		 */
		private function load_file( $path ) {
			if ( $path && is_readable( $path ) ) {
				include_once $path;

				return true;
			}

			return false;
		}

		/**
		 * Auto-load plugins' classes on demand to reduce memory consumption.
		 *
		 * @param string $class Class name.
		 */
		public function autoload( $class ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound
			$class = strtolower( $class );

			if ( 0 !== strpos( $class, 'yith_wcdp' ) ) {
				return;
			}

			$file = $this->get_file_name_from_class( $class );
			$path = '';

			if ( false !== strpos( $class, 'data_store' ) ) {
				$path = $this->include_path . 'data-stores/';
			} elseif ( false !== strpos( $class, 'compatibility' ) ) {
				$path = $this->include_path . 'compatibilities/';
			} elseif ( false !== strpos( $class, 'email' ) && false === strpos( $class, 'emails' ) ) {
				$path = $this->include_path . 'emails/';
			} elseif ( false !== strpos( $class, 'block' ) ) {
				$path = $this->include_path . 'blocks/';
			} elseif ( false !== strpos( $class, 'admin_table' ) ) {
				$path = $this->include_path . 'admin/admin-tables/';
			} elseif ( false !== strpos( $class, 'admin' ) ) {
				$path = $this->include_path . 'admin/';
			}

			if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
				$this->load_file( $this->include_path . $file );
			}
		}
	}
}

new YITH_WCDP_Autoloader();
