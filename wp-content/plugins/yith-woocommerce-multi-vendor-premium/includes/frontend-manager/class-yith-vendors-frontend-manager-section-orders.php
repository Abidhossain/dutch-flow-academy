<?php
/**
 * YITH_Vendors_Frontend_Manager_Section_Vendor class.
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH WooCommerce Multi Vendor
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

if ( ! class_exists( 'YITH_Vendors_Frontend_Manager_Section_Orders' ) ) {
	/**
	 * Handle panel commissions in frontend manager.
	 *
	 * @since   4.0.0
	 * @author  YITH
	 * @package YITH WooCommerce Multi Vendor
	 */
	class YITH_Vendors_Frontend_Manager_Section_Orders extends YITH_Frontend_Manager_Section_Orders {
		
		/**
		 * Current Vendor Object
		 *
		 * @var null|YITH_Vendor
		 */
		public $vendor = null;
		
		
		/**
		 * Constructor method
		 *
		 * @since  4.0.0
		 * @author YITH
		 */
		public function __construct() {
			parent::__construct();
			$this->vendor                = yith_wcmv_get_vendor( 'current', 'user' );
			
		}
  
		public function enqueue_section_scripts() {
            parent::enqueue_section_scripts();
			if( $this->vendor && apply_filters( 'yith_wcmv_load_vendor_dashobard_assets_on_orders', true ) ) {
				YITH_Vendors()
					->admin
					->get_vendor_dashboard_handler()
					->add_style_and_scripts();
				YITH_Vendors_Admin_Assets::register_assets();
				YITH_Vendors_Admin_Assets::enqueue_assets();
			}
		}

	}
}

