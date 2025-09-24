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

if ( ! class_exists( 'YITH_Vendors_Commission_Legacy' ) ) {
	/**
	 * The legacy class for the YITH_Vendors_Commission
	 */
	class YITH_Vendors_Commission_Legacy extends YITH_Vendors_Commission_Data_Store {

		/**
		 * Flag to indicate the change properties status.
		 * If true, some property is changed and you will update the data to database when php shutdown.
		 *
		 * @var boolean
		 */
		private $changed = false;

		/** @protected array Main Instance */
		protected static $_instance = array();

		/**
		 * Main plugin Instance
		 *
		 * @static
		 *
		 * @since  1.0
		 * @param bool|int $commission_id
		 *
		 * @return YITH_Commission Main instance
		 */
		public static function instance( $commission_id = false ) {
			_deprecated_function( __FUNCTION__, '4.0.0', 'yith_wcmv_get_commission' );
			return yith_wcmv_get_commission( absint( $commission_id ) );
		}

		/**
		 * __isset function.
		 *
		 * @param mixed $property The property key to check.
		 * @return bool
		 */
		public function __isset( $property ) {
			return isset( $this->data[ $property ] );
		}

		/**
		 * __get function.
		 *
		 * @param string $property The property key to get.
		 * @return string
		 */
		public function __get( $property ) {
			yith_wcmv_doing_it_wrong( $property, 'Commission properties should not be accessed directly.', '4.0.0' );
			$method = "get_{$property}";
			return method_exists( $this, $method ) ? $this->$method() : '';
		}

		/**
		 * __set function.
		 *
		 * @param mixed $property The property key to set.
		 * @param mixed $value    The property value.
		 */
		public function __set( $property, $value ) {
			yith_wcmv_doing_it_wrong( $property, 'Commission properties should not be set directly.', '4.0.0' );
			$this->set_data( $property, $value );
			// We need to be backward compatible and save set data on shutdown.
			if ( ! has_action( 'shutdown', array( $this, 'save_data' ) ) ) {
				add_action( 'shutdown', array( $this, 'save_data' ) );
			}
		}

		/**
		 * Retrieve the record of a commission
		 *
		 * @since 1.0
		 * @param integer $commission_id The commission ID.
		 * @deprecated
		 */
		protected function _populate( $commission_id ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Factory::read' );
			$this->read();
		}

		/**
		 * Retrieve the record of a commission
		 *
		 * @since  4.0.0
		 * @param integer $commission_id The commission ID.
		 * @deprecated
		 */
		protected function populate( $commission_id ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Factory::read' );
			$this->read();
		}

		/**
		 * Add new record to DB
		 *
		 * @param array $args The commission data.
		 * @return boolean|integer
		 * @deprecated
		 */
		public function add( $args = array() ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Commissions_Factory::create' );
			return YITH_Vendors_Commissions_Factory::create( $args );
		}

		/**
		 * Remove the commission of this instance from database
		 *
		 * @since  1.0
		 * @deprecated
		 */
		public function remove() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Commissions_Factory::delete' );
			return $this->delete();
		}

		/**
		 * Retrieve the total amount refunded
		 *
		 * @since  4.0.0
		 * @param string $context (Optional) The context where retrieve date.
		 * @return string
		 * @deprecated
		 */
		public function get_refund_amount( $context = '' ) {
			return $this->get_amount_refunded( $context );
		}

		/**
		 * Save data function alias.
		 *
		 * @since  4.0.0
		 * @return boolean|integer
		 */
		public function save_data() {
			return $this->save();
		}

		/**
		 * Change amount to the commission and user associated
		 *
		 * @since  4.0.0
		 * @param integer|float $amount The amount to add to commissions.
		 * @param string        $note   (Optional) A note to add to commission.
		 * @return void
		 * @deprecated
		 */
		public function update_amount( $amount, $note = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Retrieve the table for commission details
		 *
		 * @since  4.0.0
		 * @param boolean $plain_text True for plain text email, false otherwise.
		 * @return void
		 * @deprecated
		 */
		public function email_commission_details_table( $plain_text = false ) {
			_deprecated_function( __METHOD__, '4.0.0', 'Use action yith_wcmv_email_commission_details_table' );
			do_action( 'yith_wcmv_email_commission_details_table', $this, $plain_text );
		}

		/**
		 * Get commission order status
		 *
		 * @since  4.0.0
		 * @param WC_Order|boolean $order (Optional) The order object.
		 * @return string
		 */
		public function get_order_status( $order = false ) {
			if ( ! $order ) {
				/** @var WC_Order $order */
				$order = $this->get_order();
			}

			$wc_order_status = wc_get_order_statuses();
			$order_status    = $order ? $order->get_status() : '';

			return isset( $wc_order_status[ $order_status ] ) ? $wc_order_status[ $order_status ] : $order_status;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  1.0
 * @return YITH_Vendors_Commission
 * @deprecated
 */
if ( ! function_exists( 'YITH_Commission' ) ) {
	/**
	 * Get a commission instance by commission ID
	 *
	 * @param integer $commission_id The commission ID.
	 * @return YITH_Vendors_Commission
	 */
	function YITH_Commission( $commission_id = 0 ) {
		_deprecated_function( __FUNCTION__, '4.0.0', 'yith_wcmv_get_commission' );
		return yith_wcmv_get_commission( absint( $commission_id ) );
	}
}