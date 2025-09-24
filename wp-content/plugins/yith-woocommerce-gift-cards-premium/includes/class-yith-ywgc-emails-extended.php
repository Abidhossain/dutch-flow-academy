<?php
/**
 * Emails class
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Emails_Extended' ) ) {
	/**
	 * YITH_YWGC_Emails_Premium class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Emails_Extended extends YITH_YWGC_Emails {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Emails_Extended
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

			add_action( 'ywgc_start_gift_cards_sending', array( $this, 'send_delayed_gift_cards' ) );
		}

		/**
		 * Send the digital gift cards that should be received on specific date.
		 */
		public function send_delayed_gift_cards() {
			$aux = 'today 23:59';

			/**
			 * APPLY_FILTERS: ywgc_send_delayed_gift_cards_send_date
			 *
			 * Filter the send date for the delayed gift cards.
			 *
			 * @param int the send date in timestamp
			 *
			 * @return int
			 */
			$send_date = apply_filters( 'ywgc_send_delayed_gift_cards_send_date', strtotime( $aux ) - wc_timezone_offset() );

			$gift_card_ids = YITH_YWGC_Gift_Card_Extended::get_postdated_gift_cards( $send_date );

			foreach ( $gift_card_ids as $gift_card_id ) {
				$this->send_gift_card_email( $gift_card_id );
			}
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
				$gift_card = new YITH_YWGC_Gift_Card_Extended( array( 'ID' => $gift_card ) );
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
		}
	}
}
