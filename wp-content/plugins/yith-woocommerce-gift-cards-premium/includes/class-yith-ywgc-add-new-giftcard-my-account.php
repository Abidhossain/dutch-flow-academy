<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Add New Gift Card modal class
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_YWGC_Add_New_Gift_Card' ) ) {
	/**
	 * Handle the modal for the add new gift card feature on my account
	 *
	 * @since 1.0.0
	 */
	class YITH_YWGC_Add_New_Gift_Card {

		/**
		 * Constructor
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function __construct() {
			if ( is_account_page() ) {
				add_action( 'wp_footer', array( $this, 'ywgc_add_modal' ), 10 );
				add_action( 'wp_footer', array( $this, 'ywgc_append_modal_content' ), 10 );
			}
		}

		/**
		 * Output the popup
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function ywgc_add_modal() {
			wc_get_template(
				'myaccount/add-new-gift-card-modal.php',
				array(),
				'',
				YITH_YWGC_TEMPLATES_DIR
			);
		}

		/**
		 * Append the design preset to the gift card preview (modal)
		 */
		public function ywgc_append_modal_content() {
			if ( ! is_account_page() ) {
				return;
			}

			wc_get_template(
				'myaccount/add-new-gift-card-template.php',
				array(),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR )
			);
		}
	}
}
