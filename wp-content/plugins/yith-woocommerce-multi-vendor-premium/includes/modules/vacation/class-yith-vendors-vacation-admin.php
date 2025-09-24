<?php
/**
 * YITH_Vendors_Vacation_Admin class
 *
 * @since      4.0.0
 * @author     YITH
 * @package YITH\MultiVendor
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

if ( ! class_exists( 'YITH_Vendors_Vacation_Admin' ) ) {
	/**
	 * YITH Vendors Vacation Admin class.
	 */
	class YITH_Vendors_Vacation_Admin {

		/**
		 * Tab slug
		 *
		 * @var string
		 */
		protected $tab = 'vacation';

		/**
		 * Construct
		 *
		 * @since  1.9.17
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
			add_filter( 'yith_wcmv_admin_vendor_dashboard_tabs', array( $this, 'add_vendor_tab' ), 10, 2 );
			add_action( 'yith_wcmv_vendor_dashboard_panel_value', array( $this, 'filter_panel_value' ), 10, 3 );
			// Skip wc_clean for vacation message.
			add_filter( 'yith_wcmv_skip_wc_clean_for_fields_array', array( $this, 'skip_vacation_message_sanitize' ), 10, 1 );
		}

		/**
		 * Add admin vacation scripts
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function enqueue_scripts() {
			wp_register_script( 'yith-wcmv-vendors-vacation', YITH_WPV_ASSETS_URL . 'js/admin/' . yit_load_js_file( 'vacation.js' ), array( 'jquery', 'jquery-ui-datepicker' ), YITH_WPV_VERSION, true );

			if ( yith_wcmv_is_plugin_panel( $this->tab ) ) {
				wp_enqueue_script( 'yith-wcmv-vendors-vacation' );
			}
		}

		/**
		 * Get panel tab label
		 *
		 * @since  4.0.0
		 * @param string      $tabs   An array of dashboard tabs.
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return string
		 */
		public function add_vendor_tab( $tabs, $vendor ) {
			$tabs[ $this->tab ] = array(
				'title' => _x( 'Vacation', '[Admin]Vacation module admin tab title', 'yith-woocommerce-product-vendors' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>',
			);
			return $tabs;
		}

		/**
		 * Filter default panel options value to get Vendor data
		 *
		 * @since  4.0.0
		 * @param mixed  $value Current field value.
		 * @param array  $field The field data.
		 * @param string $id    The field ID.
		 * @return mixed
		 */
		public function filter_panel_value( $value, $field, $id ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( isset( $id ) && 'vacation_schedule' === $id && empty( $value ) && $vendor && $vendor->is_valid() ) {
				$value = YITH_Vendors_Vacation()->backward_schedule_compatibility( $vendor );
			}

			return $value;
		}

		/**
		 * Skip vacation message sanitize wc_clean.
		 *
		 * @since  4.0.0
		 * @param array $keys An array of meta and data keys to skip wc_clean.
		 * @return array
		 */
		public function skip_vacation_message_sanitize( $keys ) {
			$keys[] = 'vacation_message';
			return $keys;
		}
	}
}
