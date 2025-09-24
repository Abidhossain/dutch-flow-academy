<?php
/**
 * Class YITH_YWGC_Shortcodes_Extended
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Shortcodes_Extended' ) ) {
	/**
	 * YITH_YWGC_Shortcodes_Extended class.
	 */
	class YITH_YWGC_Shortcodes_Extended extends YITH_YWGC_Shortcodes {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Shortcodes_Extended
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
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author YITH <plugins@yithemes.com>
		 */
		protected function __construct() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
			parent::__construct();
		}
	}
}
