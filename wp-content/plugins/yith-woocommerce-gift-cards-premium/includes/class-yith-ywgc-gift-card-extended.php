<?php
/**
 * Class to handle the gift card object
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Gift_Card_Extended' ) ) {
	/**
	 * YITH_YWGC_Gift_Card_Extended class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Gift_Card_Extended extends YITH_YWGC_Gift_Card {

		const META_IS_POSTDATED = '_ywgc_postdated';

		/**
		 * Postdated_delivery
		 *
		 * @var bool the gift card has a postdated delivery date
		 */
		public $postdated_delivery = false;

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
				$this->postdated_delivery = get_post_meta( $this->ID, self::META_IS_POSTDATED, true );
			}
		}

		/**
		 * Retrieve all scheduled gift cards to be sent on a specific day or up to the specific day if $include_old is true
		 *
		 * @param string $send_date the gift card scheduled day.
		 * @param string $relation  the conditional relation for gift cards date specified.
		 *
		 * @return array
		 */
		public static function get_postdated_gift_cards( $send_date, $relation = '<=' ) {
			$args = array(
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => self::META_DELIVERY_DATE,
						'value'   => $send_date,
						'compare' => $relation,
					),
					array(
						'key'     => self::META_DELIVERY_DATE,
						'value'   => '',
						'compare' => '!=',
					),
					array(
						'key'   => self::META_SEND_DATE,
						'value' => '',
					),
					array(
						'key'     => self::META_IS_DIGITAL,
						'value'   => '1',
						'compare' => '=',
					),
					array(
						'key'     => self::META_RECIPIENT_EMAIL,
						'value'   => '',
						'compare' => '!=',
					),
				),
				'post_type'      => YWGC_CUSTOM_POST_TYPE_NAME,
				'fields'         => 'ids',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			);

			return get_posts( $args );
		}

		/**
		 * Save the current object
		 */
		public function save() {
			parent::save();

			$date_format = apply_filters( 'yith_wcgc_date_format', 'Y-m-d' );

			update_post_meta( $this->ID, self::META_IS_POSTDATED, $this->postdated_delivery );

			if ( $this->postdated_delivery ) {
				$delivery_date_in_timezone_offset = (int)$this->delivery_date - (int)strval( wc_timezone_offset() );
				update_post_meta( $this->ID, self::META_DELIVERY_DATE, $delivery_date_in_timezone_offset );

				// Update also the delivery date with format.
				$delivery_date_format = date_i18n( $date_format, $this->delivery_date );
				update_post_meta( $this->ID, '_ywgc_delivery_date_formatted', $delivery_date_format );
				update_post_meta( $this->ID, self::META_SEND_DATE, $this->delivery_send_date );
			} else {
				$delivery_date_format = date_i18n( $date_format, time() );
				update_post_meta( $this->ID, '_ywgc_delivery_date_formatted', $delivery_date_format );
			}
		}
	}
}
