<?php
/**
 * Deposit rule Factory class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH/Deposits/Classes
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Deposit_Rule_Factory' ) ) {
	/**
	 * Static class that offers methods to construct YITH_WCDP_Deposit_Rule objects
	 *
	 * @since 2.0.0
	 */
	class YITH_WCDP_Deposit_Rule_Factory {

		/**
		 * Returns a list of rules matching filtering criteria
		 *
		 * @param array $args Filtering criteria (@see \YITH_WCDP_Deposit_Rule_Data_Store::query).
		 *
		 * @return array|string[]|bool Result set; false on failure.
		 */
		public static function get_rules( $args = array() ) {
			try {
				$data_store = WC_Data_Store::load( 'deposit_rule' );

				$res = $data_store->query( $args );
			} catch ( Exception $e ) {
				return false;
			}

			return $res;
		}

		/**
		 * Returns a rule, given the id
		 *
		 * @param int $id Rule's ID.
		 *
		 * @return YITH_WCDP_Deposit_Rule|bool Rule object, or false on failure
		 */
		public static function get_rule( $id ) {
			if ( ! $id ) {
				return false;
			}

			try {
				return new YITH_WCDP_Deposit_Rule( $id );
			} catch ( Exception $e ) {
				return false;
			}
		}
	}
}
