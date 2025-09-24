<?php
/**
 * Class YITH_YWGC_Shortcodes_Premium
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Shortcodes_Premium' ) ) {
	/**
	 * YITH_YWGC_Shortcodes_Premium class.
	 */
	class YITH_YWGC_Shortcodes_Premium extends YITH_YWGC_Shortcodes_Extended {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Shortcodes_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author YITH <plugins@yithemes.com>
		 */
		protected function __construct() {
			parent::__construct();

			add_shortcode( 'yith_ywgc_display_gift_this_product_form', array( $this, 'yith_ywgc_display_gift_this_product_form' ) );

			add_shortcode( 'yith_gift_card_check_balance_form', array( $this, 'yith_gift_card_check_balance_form' ) );

			add_shortcode( 'yith_redeem_gift_card_form', array( $this, 'yith_redeem_gift_card_form' ) );

			add_shortcode( 'yith_gift_cards_user_table', array( $this, 'yith_gift_cards_user_table' ) );

		}

		/**
		 * Shortcode to include the necessary hook to display the gift this product form
		 *
		 * @param array  $atts    Shortcode atts.
		 * @param string $content Content.
		 *
		 * @return false|string
		 */
		public function yith_ywgc_display_gift_this_product_form( $atts, $content ) {
			global $product;

			if ( is_object( $product ) ) {
				ob_start();

				wc_get_template(
					'shortcodes/give-product-as-present-shortcode.php',
					array(
						'product' => $product,
						'icon'    => get_option( 'ywgc_gift_this_product_icon', 'no' ),
					),
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR )
				);

				$content = ob_get_clean();
			}

			return $content;
		}

		/**
		 * Shortcode add a check gift card balance form
		 */
		public function yith_gift_card_check_balance_form() {
			ob_start();

			wc_get_template( 'shortcodes/gift-card-check-balance-form.php', '', '', trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );

			return ob_get_clean();
		}

		/**
		 * Shortcode to redeem the gift card manually
		 */
		public function yith_redeem_gift_card_form() {
			ob_start();

			wc_get_template( 'shortcodes/redeem-gift-card-form.php', '', '', trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );

			return ob_get_clean();
		}

		/**
		 * Shortcode to display the user gift card table
		 *
		 * @param array $atts Shortcode atts.
		 *
		 * @return false|string
		 */
		public function yith_gift_cards_user_table( $atts ) {
			ob_start();

			wc_get_template( 'shortcodes/user-gift-card-table.php', $atts, '', trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );

			return ob_get_clean();
		}

	}
}
