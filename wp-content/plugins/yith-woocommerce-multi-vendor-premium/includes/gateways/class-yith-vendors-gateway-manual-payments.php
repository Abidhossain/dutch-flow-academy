<?php
/**
 * YITH YITH_Vendors_Gateway_Manual
 * Define methods and properties for class that manages payments via paypal
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Gateway_Manual_Payments' ) ) {

	class YITH_Vendors_Gateway_Manual_Payments extends YITH_Vendors_Gateway {

		/**
		 * The gateway slug.
		 *
		 * @var string
		 */
		protected $id = 'yith-wcmv-manual-payments';

		/**
		 * The gateway name.
		 *
		 * @var string
		 */
		protected $method_title = 'YITH Manual Payments Gateway';

		/**
		 * YITH_Vendors_Gateway_Manual_Payments constructor.
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public function __construct() {}
	}
}
