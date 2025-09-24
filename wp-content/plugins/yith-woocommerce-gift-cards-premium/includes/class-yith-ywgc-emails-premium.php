<?php
/**
 * Emails class
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Emails_Premium' ) ) {
	/**
	 * YITH_YWGC_Emails_Premium class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Emails_Premium extends YITH_YWGC_Emails_Extended {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Emails_Premium
		 * @since 1.0.0
		 */
		public static $instance;

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
		 */
		public function __construct() {
			parent::__construct();

			/**
			 * Show the product suggestion on the gift card email
			 */
			add_action( 'yith_wcgc_template_after_message', array( $this, 'show_product_suggestion' ), 15, 2 );
		}

		/**
		 * Show a link that let the customer to go to the website, adding the discount to the cart
		 *
		 * @param YWGC_Gift_Card_Premium $gift_card Gift card object.
		 *
		 * @since  1.0.0
		 */
		public function show_link_for_cart_discount( $gift_card ) {
			if ( 'no' !== get_option( 'ywgc_auto_discount_button_activation', 'yes' ) && ! $gift_card->product_as_present ) {
				/**
				 * APPLY_FILTERS: yith_ywgc_shop_page_url
				 *
				 * Filter the shop page URL added in the gift card to automatically apply the code on the cart.
				 *
				 * @param string URL to redirect the customer, default: the shop page
				 * @param object $gift_card the gift card object
				 *
				 * @return string
				 */
				$shop_page_url = apply_filters( 'yith_ywgc_shop_page_url', get_permalink( wc_get_page_id( 'shop' ) ) ? get_permalink( wc_get_page_id( 'shop' ) ) : site_url(), $gift_card );

				$args = array();

				if ( get_option( 'ywgc_auto_discount', 'yes' ) !== 'no' ) {
					$args = array(
						YWGC_ACTION_ADD_DISCOUNT_TO_CART => $gift_card->gift_card_number,
						YWGC_ACTION_VERIFY_CODE          => YITH_YWGC()->hash_gift_card( $gift_card ),
					);
				}

				if ( get_option( 'ywgc_redirected_page', 'home_page' ) ) {
					$apply_discount_url = esc_url( add_query_arg( $args, get_page_link( get_option( 'ywgc_redirected_page', 'home_page' ) ) ) );
				} else {
					$apply_discount_url = esc_url( add_query_arg( $args, $shop_page_url ) );
				}

				$email_button_label_get_option = get_option( 'ywgc_email_button_label', esc_html__( 'Apply your gift card code', 'yith-woocommerce-gift-cards' ) );

				/**
				 * APPLY_FILTERS: yith_ywgc_email_automatic_cart_discount_url
				 *
				 * Filter the complete URL generated to automatically apply the code on the cart.
				 *
				 * @param string $apply_discount_url URL to apply the discount automatically in the cart
				 * @param array $args arguments to pass to the URL
				 * @param object $gift_card the gift card object
				 *
				 * @return string
				 */
				wc_get_template(
					'emails/automatic-discount.php',
					array(
						'apply_discount_url'            => apply_filters( 'yith_ywgc_email_automatic_cart_discount_url', $apply_discount_url, $args, $gift_card ),
						'gift_card'                     => $gift_card,
						'email_button_label_get_option' => $email_button_label_get_option,
						'context'                       => isset( $_GET['preview_woocommerce_mail'] ) && sanitize_text_field( wp_unslash( $_GET['preview_woocommerce_mail'] ) ) ? 'preview' : 'email', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					),
					'',
					YITH_YWGC_TEMPLATES_DIR
				);
			}
		}

		/**
		 * Show the product suggestion associated to the gift card
		 *
		 * @param YWGC_Gift_Card_Premium $gift_card Gift card object.
		 * @param string                 $context   Context.
		 *
		 * @since  1.0.0
		 */
		public function show_product_suggestion( $gift_card, $context ) {
			if ( ! $gift_card->product_as_present ) {
				return;
			}

			// The customer has suggested a product when he bought the gift card.
			if ( $gift_card->present_variation_id ) {
				$product = wc_get_product( $gift_card->present_variation_id );
			} else {
				$product = wc_get_product( $gift_card->present_product_id );
			}

			wc_get_template(
				'emails/product-suggestion.php',
				array(
					'gift_card' => $gift_card,
					'product'   => $product,
					'context'   => $context,
				),
				'',
				YITH_YWGC_TEMPLATES_DIR
			);
		}

		/**
		 * Send the gift card code email
		 *
		 * @param YWGC_Gift_Card_Premium|int $gift_card the gift card.
		 * @param bool                       $only_new  choose if only never sent gift card should be used.
		 *
		 * @since  1.0.0
		 */
		public function send_gift_card_email( $gift_card, $only_new = true ) {
			if ( is_numeric( $gift_card ) ) {
				$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_card ) );
			}

			if ( ! $gift_card->exists() ) {
				return;
			}

			/**
			 * APPLY_FILTERS: yith_wcgc_deny_gift_card_email
			 *
			 * Filter the condition to deny to send the gift card email.
			 *
			 * @param bool true to deny it, false to allow it. Default: false
			 * @param object $gift_card the gift card object
			 *
			 * @return bool
			 */
			if ( ( ! $gift_card->is_virtual() || empty( $gift_card->recipient ) ) || apply_filters( 'yith_wcgc_deny_gift_card_email', false, $gift_card ) ) {
				// not a digital gift card or missing recipient.
				return;
			}

			if ( $only_new && $gift_card->has_been_sent() ) {
				// avoid sending emails more than one time.
				return;
			}

			/**
			 * APPLY_FILTERS: ywgc_recipient_email_before_sent_email
			 *
			 * Filter the recipient email before sending the gift card email.
			 *
			 * @param string the recipient email
			 * @param object $gift_card the gift card object
			 *
			 * @return string
			 */
			$gift_card->recipient = apply_filters( 'ywgc_recipient_email_before_sent_email', $gift_card->recipient, $gift_card );

			/**
			 * DO_ACTION: ywgc_before_sent_email_gift_card_notification
			 *
			 * Before send the gift card notification via email.
			 *
			 * @param object $gift_card the gift card object
			 */
			do_action( 'ywgc_before_sent_email_gift_card_notification', $gift_card );

			WC()->mailer();

			/**
			 * DO_ACTION: ywgc_email_send_gift_card_notification
			 *
			 * Trigger the gift card notification email.
			 *
			 * @param object $gift_card the gift card object
			 * @param string the recipient case
			 */
			do_action( 'ywgc_email_send_gift_card_notification', $gift_card, 'recipient' );

			/**
			 * DO_ACTION: yith_ywgc_gift_card_email_sent
			 *
			 * After send the gift card notification via email.
			 *
			 * @param object $gift_card the gift card object
			 */
			do_action( 'yith_ywgc_gift_card_email_sent', $gift_card );

			$old_file = get_post_meta( $gift_card->ID, 'ywgc_pdf_file', true );

			if ( file_exists( $old_file ) ) {
				wp_delete_file( $old_file );
				delete_post_meta( $gift_card->ID, 'ywgc_pdf_file' );
			}
		}

		/**
		 * Add an email action for sending the digital gift card
		 *
		 * @param array $actions list of current actions.
		 *
		 * @return array
		 */
		public function add_gift_cards_trigger_action( $actions ) {
			// Add trigger action for sending digital gift card.
			$actions[] = 'ywgc-email-send-gift-card';
			$actions[] = 'ywgc-email-notify-customer';
			$actions[] = 'ywgc-email-delivered-gift-card';

			return $actions;
		}

		/**
		 * Locate the plugin email templates
		 *
		 * @param string $core_file Template full path.
		 * @param string $template  Template in use.
		 *
		 * @return string
		 */
		public function locate_core_template( $core_file, $template ) {
			$custom_template = array(
				'emails/send-gift-card.php',
				'emails/notify-customer.php',
				'emails/delivered-gift-card.php',
			);

			if ( in_array( $template, $custom_template, true ) ) {
				$core_file = YITH_YWGC_TEMPLATES_DIR . $template;
			}

			return $core_file;
		}

		/**
		 * Add the email used to send digital gift card to woocommerce email tab
		 *
		 * @param array $email_classes current email classes.
		 *
		 * @return mixed
		 */
		public function add_woocommerce_email_classes( $email_classes ) {
			include 'emails/class.yith-ywgc-mail.php';

			// add the email class to the list of email classes that WooCommerce loads.
			$email_classes['ywgc-email-send-gift-card']      = include 'emails/class-yith-ywgc-email-send-gift-card.php';
			$email_classes['ywgc-email-notify-customer']     = include 'emails/class-yith-ywgc-email-notify-customer.php';
			$email_classes['ywgc-email-delivered-gift-card'] = include 'emails/class-yith-ywgc-email-delivered-gift-card.php';

			return $email_classes;
		}
	}
}
