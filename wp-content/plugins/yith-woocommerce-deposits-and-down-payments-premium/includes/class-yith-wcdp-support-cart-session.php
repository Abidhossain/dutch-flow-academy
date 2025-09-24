<?php
/**
 * Support Cart for Deposit
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Support_Cart_Session' ) ) {
	/**
	 * Support cart session for deposit
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Support_Cart_Session {

		/**
		 * Set session.
		 * Just does nothing because we don't want to save our support cart into session.
		 */
		public function set_session() {
			return null;
		}

		/**
		 * Set cart.
		 * Just does nothing because we don't want to save our support cart into session.
		 */
		public function set_cart() {
			return null;
		}

		/**
		 * Destroy persistent cart.
		 * Just does nothing because we didn't save our support cart.
		 */
		public function persistent_cart_destroy() {
			return null;
		}

		/**
		 * Retrieves cart from session.
		 * Just does nothing because we don't save our support cart into session.
		 */
		public function get_cart_from_session() {
			return null;
		}

		/**
		 * Retrieves cart for session.
		 * Just does nothing because we don't want to save our support cart into session.
		 */
		public function get_cart_for_session() {
			return null;
		}
	}
}
