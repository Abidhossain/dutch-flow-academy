<?php
/**
 * Grand total block
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Blocks
 * @version 2.16.0
 */

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Grand_Total_Block' ) ) {
	/**
	 * "Set Referrer" block
	 *
	 * @since 2.16.0
	 */
	class YITH_WCDP_Grand_Total_Block implements IntegrationInterface {

		/**
		 * Register block
		 */
		public static function register() {
			add_action(
				'woocommerce_blocks_cart_block_registration',
				function ( $registry ) {
					$registry->register( new self() );
				}
			);
			add_action(
				'woocommerce_blocks_checkout_block_registration',
				function ( $registry ) {
					$registry->register( new self() );
				}
			);

			add_filter(
				'__experimental_woocommerce_blocks_add_data_attributes_to_block',
				function ( $blocks ) {
					$blocks[] = 'yith/yith-wcdp-grand-total';
					return $blocks;
				}
			);
		}

		/**
		 * The name of the integration.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'yith/yith-wcdp-grand-total';
		}

		/**
		 * When called invokes any initialization/setup for the integration.
		 */
		public function initialize() {
			$this->register_frontend_scripts();
			$this->register_editor_scripts();
		}

		/**
		 * Returns an array of script handles to enqueue in the frontend context.
		 *
		 * @return string[]
		 */
		public function get_script_handles() {
			return array( 'yith-wcdp-blocks-grand-total-frontend' );
		}

		/**
		 * Returns an array of script handles to enqueue in the editor context.
		 *
		 * @return string[]
		 */
		public function get_editor_script_handles() {
			return array( 'yith-wcdp-blocks-grand-total' );
		}

		/**
		 * An array of key, value pairs of data made available to the block on the client side.
		 *
		 * @return array
		 */
		public function get_script_data() {
			return array();
		}

		/**
		 * Register scripts to be used within Editor
		 */
		public function register_editor_scripts() {
			YITH_WCDP_Scripts::register( 'yith-wcdp-blocks-grand-total', 'blocks', array(), true );
		}

		/**
		 * Register scripts to be used on Frontend
		 */
		public function register_frontend_scripts() {
			YITH_WCDP_Scripts::register( 'yith-wcdp-blocks-grand-total-frontend', 'blocks', array(), true );
		}
	}
}
