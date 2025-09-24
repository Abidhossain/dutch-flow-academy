<?php
/**
 * Class to handle the Gift This Product module.
 *
 * @author  YITH
 * @package YITH\GiftCards\Modules\GiftThisProduct
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_YWGC_Gift_This_Product_Module' ) ) {
	/**
	 * YITH_YWGC_Gift_This_Product_Module class.
	 *
	 * @author  Leanza Francesco <leanzafrancesco@gmail.com>
	 * @since   4.0
	 */
	class YITH_YWGC_Gift_This_Product_Module extends YITH_YWGC_Module {

		const KEY = 'gift-this-product';

		/**
		 * On load.
		 */
		public function on_load() {
			add_filter( 'yith_ywgc_admin_panel_tabs', array( $this, 'add_gift_this_product_settings' ), 10, 1 );
		}

		/**
		 * Add gift this product settings.
		 *
		 * @param array $settings_tabs Settings tabs.
		 *
		 * @return array
		 */
		public function add_gift_this_product_settings( array $settings_tabs ): array {
			$settings_tabs['gift-this-product'] = array(
				'title'       => _x( 'Gift this product', 'Tab title in plugin panel', 'yith-woocommerce-gift-cards' ),
				'description' => __( 'Configure the "Gift this product" module.', 'yith-woocommerce-gift-cards' ),
				'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75v16.5M2.25 12h19.5M6.375 17.25a4.875 4.875 0 004.875-4.875V12m6.375 5.25a4.875 4.875 0 01-4.875-4.875V12m-9 8.25h16.5a1.5 1.5 0 001.5-1.5V5.25a1.5 1.5 0 00-1.5-1.5H3.75a1.5 1.5 0 00-1.5 1.5v13.5a1.5 1.5 0 001.5 1.5zm12.621-9.44c-1.409 1.41-4.242 1.061-4.242 1.061s-.349-2.833 1.06-4.242a2.25 2.25 0 013.182 3.182zM10.773 7.63c1.409 1.409 1.06 4.242 1.06 4.242S9 12.22 7.592 10.811a2.25 2.25 0 113.182-3.182z" /></svg>',
			);

			return $settings_tabs;
		}
	}
}
