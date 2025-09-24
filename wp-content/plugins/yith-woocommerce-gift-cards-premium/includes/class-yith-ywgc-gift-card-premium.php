<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class to handle the gift card object
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWGC_Gift_Card_Premium' ) ) {
	/**
	 * YWGC_Gift_Card_Premium class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YWGC_Gift_Card_Premium extends YITH_YWGC_Gift_Card_Extended {

		const META_AS_PRESENT              = '_ywgc_product_as_present';
		const META_AS_PRESENT_VARIATION_ID = '_ywgc_present_variation_id';
		const META_AS_PRESENT_PRODUCT_ID   = '_ywgc_present_product_id';
		const META_MANUAL_AMOUNT           = '_ywgc_is_manual_amount';
		const META_DELIVERY_NOTIFICATION   = '_ywgc_delivery_notification_checkbox';

		/**
		 * The product is set as a present
		 *
		 * @var bool
		 */
		public $product_as_present = false;

		/**
		 * The product variation id when the product is used as a present
		 *
		 * @var int
		 */
		public $present_variation_id = 0;

		/**
		 * The product id used as a present
		 *
		 * @var int
		 */
		public $present_product_id = 0;

		/**
		 * The gift card amount was entered manually
		 *
		 * @var bool
		 */
		public $is_manual_amount = false;

		/**
		 * Delivery notification for sender
		 *
		 * @var string
		 */
		public $delivery_notification = true;

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @param array $args Array of arguments.
		 *
		 * @since  1.0
		 */
		public function __construct( $args = array() ) {
			parent::__construct( $args );

			// If $args is related to an existent gift card, load their data.
			if ( $this->ID ) {
				$this->product_as_present    = get_post_meta( $this->ID, self::META_AS_PRESENT, true );
				$this->present_variation_id  = get_post_meta( $this->ID, self::META_AS_PRESENT_VARIATION_ID, true );
				$this->present_product_id    = get_post_meta( $this->ID, self::META_AS_PRESENT_PRODUCT_ID, true );
				$this->is_manual_amount      = get_post_meta( $this->ID, self::META_MANUAL_AMOUNT, true );
				$this->delivery_notification = get_post_meta( $this->ID, self::META_DELIVERY_NOTIFICATION, true );
			}
		}

		/**
		 * Register the date and amount when the gift card was redeemed using the shortcode
		 *
		 * @param string $used_amount Gift card used amount.
		 *
		 * @since  1.0.0
		 */
		public function register_redemption( $used_amount ) {
			$user = wp_get_current_user();

			if ( $this->ID ) {
				// translators: %1$s is the used amount. %2$s is the user who redeemed the gift card. %3$s is the date when the gift card was redeemed.
				$new_redemption = sprintf( __( 'Redeemed %1$s by %2$s on %3$s', 'yith-woocommerce-gift-cards' ), wc_price( $used_amount ), $user->display_name, gmdate( 'Y-m-d' ) );
				$redemptions    = $this->get_redemption_history();
				$redemptions[]  = $new_redemption;

				update_post_meta( $this->ID, 'ywgc_redemption_history', $redemptions );
			}
		}

		/**
		 * Save the current object
		 */
		public function save() {
			parent::save();

			update_post_meta( $this->ID, self::META_AS_PRESENT, $this->product_as_present );

			if ( $this->product_as_present ) {
				update_post_meta( $this->ID, self::META_AS_PRESENT_PRODUCT_ID, $this->present_product_id );
				update_post_meta( $this->ID, self::META_AS_PRESENT_VARIATION_ID, $this->present_variation_id );
			}

			update_post_meta( $this->ID, self::META_MANUAL_AMOUNT, $this->is_manual_amount );
			update_post_meta( $this->ID, self::META_DELIVERY_NOTIFICATION, $this->delivery_notification );
		}
	}
}
