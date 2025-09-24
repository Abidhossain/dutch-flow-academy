<?php
/**
 * YITH_Vendors_Modules_Legacy
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 5.0.0
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

if ( ! class_exists( 'YITH_Vendors_Modules_Legacy' ) ) {
	/**
	 * Handle plugin modules legacy class.
	 */
	abstract class YITH_Vendors_Modules_Legacy {

		/**
		 * Modules admin sub-tabs
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $modules_admin_sub_tabs = array();

		/**
		 * An array of available modules
		 *
		 * @since 4.0.0
		 * @var array module => boolean | True if available, false otherwise
		 */
		protected $available_modules = array();

		/**
		 * Check if there is at least a modules with admin tab
		 *
		 * @since  4.0.0
		 * @return boolean
		 * @deprecated
		 */
		public function has_active_modules_settings() {
			_deprecated_function( __METHOD__, '5.0.0' );
			return ! empty( $this->modules_admin_sub_tabs );
		}

		/**
		 * Add admin module sub-tabs to add-ons panel
		 *
		 * @since  4.0.0
		 * @param array $sub_tabs An array of sub-tabs.
		 * @return array
		 * @deprecated
		 */
		public function add_modules_sub_tab( $sub_tabs ) {
			_deprecated_function( __METHOD__, '5.0.0' );
			return $sub_tabs;
		}

		/**
		 * Is module available?
		 *
		 * @since  4.0.0
		 * @param string $module The module to check.
		 * @return boolean
		 * @deprecated
		 */
		public function is_module_available( $module ) {
			_deprecated_function( __METHOD__, '5.0.0' );
			return true;
		}

		/**
		 * Check if user has a plugin module. This is an alias for is_module_available method.
		 *
		 * @since  4.0.0
		 * @param string $plugin_name The module to check.
		 * @return boolean
		 * @deprecated
		 */
		public function has_plugin( $plugin_name ) {
			_deprecated_function( __METHOD__, '5.0.0' );
			return true;
		}
	}
}
