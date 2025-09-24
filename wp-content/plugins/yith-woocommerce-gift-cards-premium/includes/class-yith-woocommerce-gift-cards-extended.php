<?php
/**
 * YITH_WooCommerce_Gift_Cards_Extended class
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_WooCommerce_Gift_Cards_Extended' ) ) {
	/**
	 * YITH_WooCommerce_Gift_Cards_Extended class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_WooCommerce_Gift_Cards_Extended extends YITH_WooCommerce_Gift_Cards {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WooCommerce_Gift_Cards_Extended
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
		 * Init hooks
		 */
		public function init_hooks() {
			parent::init_hooks();

			/**
			 * Set gift card expiration for gift card created manually
			 */
			add_action( 'save_post', array( $this, 'set_expiration_date_for_gift_card_created_manually' ), 10, 3 );

			add_action( 'wp_ajax_generate_gift_card_code', array( $this, 'generate_gift_card_code_ajax' ) );
		}

		/**
		 * Start
		 *
		 * @return void
		 */
		public function start() {
			parent::start();

			YITH_YWGC_Install::get_instance();
		}

		/**
		 *  Execute all the operation need when the plugin init
		 */
		public function on_plugin_init() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
			parent::on_plugin_init();
		}

		/**
		 * Init_metabox
		 *
		 * @return void
		 */
		public function init_metabox() {
			/**
			 * APPLY_FILTERS: yith_ywgc_gift_card_instance_metabox_custom_fields
			 *
			 * Filter the fields to show when editing a gift card on backend.
			 *
			 * @param array array with the fields to display
			 *
			 * @return array
			 */
			$args = array(
				'label'    => esc_html__( 'Gift card detail', 'yith-woocommerce-gift-cards' ),
				'pages'    => YWGC_CUSTOM_POST_TYPE_NAME,
				'class'    => yith_set_wrapper_class(),
				'context'  => 'normal',
				'priority' => 'high',
				'tabs'     => array(
					'General' => array(
						'label'  => '',
						'fields' => apply_filters(
							'yith_ywgc_gift_card_instance_metabox_custom_fields',
							array(
								YITH_YWGC_Gift_Card::META_AMOUNT_TOTAL => array(
									'label'   => esc_html__( 'Amount', 'yith-woocommerce-gift-cards' ) . ' (' . get_woocommerce_currency_symbol() . ')',
									'desc'    => esc_html__( 'The gift card amount', 'yith-woocommerce-gift-cards' ),
									'type'    => 'text',
									'private' => false,
									'std'     => '',
								),
								YITH_YWGC_Gift_Card::META_BALANCE_TOTAL => array(
									'label'   => esc_html__( 'Current balance', 'yith-woocommerce-gift-cards' ) . ' (' . get_woocommerce_currency_symbol() . ')',
									'desc'    => esc_html__( 'The current amount available for the customer', 'yith-woocommerce-gift-cards' ),
									'type'    => 'text',
									'private' => false,
									'std'     => '',
								),
								'_ywgc_is_digital'        => array(
									'label'   => esc_html__( 'Virtual', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html__( 'Enable if the gift card will be sent via email', 'yith-woocommerce-gift-cards' ),
									'type'    => 'onoff',
									'private' => false,
									'std'     => '',
								),
								'_ywgc_set_default_image' => array(
									'label'   => esc_html_x( 'Set a default image', 'Option title', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html_x( 'Set a default image for this gift card, overriding the actual image if any', 'Option description', 'yith-woocommerce-gift-cards' ),
									'type'    => 'onoff',
									'private' => false,
									'std'     => '',
									'deps'    => array(
										'ids'    => '_ywgc_is_digital',
										'values' => 'yes',
										'type'   => 'hide',
									),
								),
								'ywgc_default_image'      => array(
									'label' => '',
									'desc'  => '',
									'type'  => 'media',
									'deps'  => array(
										'ids'    => '_ywgc_set_default_image',
										'values' => 'yes',
										'type'   => 'hide',
									),
								),
								'_ywgc_recipient'         => array(
									'label'   => esc_html__( 'Recipient\'s email', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html__( 'The email address of the virtual gift card recipient', 'yith-woocommerce-gift-cards' ),
									'type'    => 'text',
									'private' => false,
									'std'     => '',
									'deps'    => array(
										'ids'    => '_ywgc_is_digital',
										'values' => 'yes',
										'type'   => 'hide',
									),
								),
								'_ywgc_sender_name'       => array(
									'label'   => esc_html__( 'Sender\'s name', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html__( 'The name of gift card sender', 'yith-woocommerce-gift-cards' ),
									'type'    => 'text',
									'private' => false,
									'std'     => '',
									'css'     => 'width: 80px;',
								),
								'_ywgc_recipient_name'    => array(
									'label'   => esc_html__( 'Recipient\'s name', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html__( 'The name of the gift card recipient', 'yith-woocommerce-gift-cards' ),
									'type'    => 'text',
									'private' => false,
									'std'     => '',
								),
								'_ywgc_message'           => array(
									'label'   => esc_html__( 'Message', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html__( 'The message attached to the gift card', 'yith-woocommerce-gift-cards' ),
									'type'    => 'textarea',
									'private' => false,
									'std'     => '',
								),
								'_ywgc_delivery_date_toggle' => array(
									'label'   => esc_html_x( 'Set delivery date', 'Option title', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html_x( 'Enable to set a delivery date for the gift card', 'Option description', 'yith-woocommerce-gift-cards' ),
									'type'    => 'onoff',
									'private' => false,
									'std'     => '',
									'deps'    => array(
										'ids'    => '_ywgc_is_digital',
										'values' => 'yes',
										'type'   => 'hide',
									),
								),
								'_ywgc_delivery_date'     => array(
									'type'    => 'text',
									'private' => false,
									'std'     => '',
								),
								'_ywgc_delivery_date_formatted' => array(
									'label'   => esc_html__( 'Delivery date', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html__( 'The date when the virtual gift card will be sent to the recipient', 'yith-woocommerce-gift-cards' ),
									'type'    => 'datepicker',
									'id'      => '_ywgc_delivery_date_formatted',
									'private' => false,
									'std'     => '',
									'data'    => array(
										'date-format' => get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' ),
										'min-date'    => 0,
									),
									'deps'    => array(
										'ids'    => '_ywgc_delivery_date_toggle',
										'values' => 'yes',
										'type'   => 'hide',
									),
								),
								'_ywgc_expiration_date_toggle' => array(
									'label'   => esc_html__( 'Set expiration date', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html__( 'Enable to set an expiration date for the gift card', 'yith-woocommerce-gift-cards' ),
									'type'    => 'onoff',
									'private' => false,
									'std'     => '',
								),
								'_ywgc_expiration'        => array(
									'type'    => 'text',
									'private' => false,
									'std'     => '',
								),
								'_ywgc_expiration_date_formatted' => array(
									'label'   => esc_html__( 'Expiration date', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html__( 'The date when the virtual gift card will expire', 'yith-woocommerce-gift-cards' ),
									'type'    => 'datepicker',
									'id'      => '_ywgc_expiration_date_formatted',
									'private' => false,
									'std'     => '',
									'data'    => array(
										'date-format' => get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' ),
										'min-date'    => 0,
									),
									'deps'    => array(
										'ids'    => '_ywgc_expiration_date_toggle',
										'values' => 'yes',
										'type'   => 'hide',
									),
								),
								'_ywgc_internal_notes'    => array(
									'label'   => esc_html__( 'Internal notes', 'yith-woocommerce-gift-cards' ),
									'desc'    => esc_html__( 'Enter your notes here. This will only be visible to the admin', 'yith-woocommerce-gift-cards' ),
									'type'    => 'textarea',
									'private' => false,
									'std'     => '',
								),
							)
						),
					),
				),
			);

			$metabox = YIT_Metabox( 'yith-ywgc-gift-card-options-metabox' );
			$metabox->init( $args );
		}

		/**
		 * Set the expiration date for the manually created gift cards
		 *
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post    Post object.
		 * @param bool    $update  Whether this is an existing post being updated.
		 */
		public function set_expiration_date_for_gift_card_created_manually( $post_id, $post, $update ) {
			if ( 'gift_card' === $post->post_type && isset( $_POST['yit_metaboxes'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$saved_format = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );

				$delivery_date_timestamp   = '';
				$expiration_date_timestamp = '';

				// Delivery date.
				$delivery_date = isset( $_POST['yit_metaboxes'] ) && isset( $_POST['yit_metaboxes']['_ywgc_delivery_date_formatted'] ) ? sanitize_text_field( wp_unslash( $_POST['yit_metaboxes']['_ywgc_delivery_date_formatted'] ) ) : time(); // phpcs:ignore WordPress.Security.NonceVerification.Missing

				// Expiration date.
				$expiration_date = isset( $_POST['yit_metaboxes'] ) && isset( $_POST['yit_metaboxes']['_ywgc_expiration_date_formatted'] ) ? sanitize_text_field( wp_unslash( $_POST['yit_metaboxes']['_ywgc_expiration_date_formatted'] ) ) : time(); // phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( 'MM d, yy' === $saved_format ) {
					$aux                       = wp_timezone_string() . ' ' . $delivery_date . ' 00:00';
					$delivery_date_timestamp   = strtotime( $aux );
					$expiration_date_timestamp = strtotime( $expiration_date );
				} else {
					$search                 = array( '.', ', ', '/', ' ', ',', 'MM', 'yy', 'mm', 'dd' );
					$replace                = array( '-', '-', '-', '-', '-', 'M', 'y', 'm', 'd' );
					$saved_format_formatted = str_replace( $search, $replace, $saved_format );

					// Delivery date.
					$date_formatted = str_replace( $search, $replace, $delivery_date );
					$delivery_date  = '' !== $date_formatted ? 'mm/dd/yy' !== $saved_format ? gmdate( $saved_format_formatted, strtotime( $date_formatted ) ) : gmdate( $saved_format_formatted, strtotime( $delivery_date ) ) : '';
					$delivery_date  = ! empty( $delivery_date ) ? DateTime::createFromFormat( $saved_format_formatted, $delivery_date ) : '';

					if ( $delivery_date ) {
						$delivery_date_aux       = $delivery_date->format( 'Y-m-d' );
						$aux                     = wp_timezone_string() . ' ' . $delivery_date_aux . ' 00:00';
						$delivery_date_timestamp = strtotime( $aux );
					}

					// Expiration date.
					$date_formatted  = str_replace( $search, $replace, $expiration_date );
					$expiration_date = '' !== $date_formatted ? 'mm/dd/yy' !== $saved_format ? gmdate( $saved_format_formatted, strtotime( $date_formatted ) ) : gmdate( $saved_format_formatted, strtotime( $expiration_date ) ) : '';
					$expiration_date = ! empty( $expiration_date ) ? DateTime::createFromFormat( $saved_format_formatted, $expiration_date ) : '';

					if ( $expiration_date ) {
						$expiration_date_timestamp = $expiration_date->getTimestamp();
					}
				}
				
				// Create a single cron, to send the scheduled gift card.
				if ( $delivery_date_timestamp && apply_filters( 'yith_ywgc_create_cron_on_code_post_save', true ) ){
					// if the cron does not exist create it, if exist remove and create it with the new date
					if ( ! wp_next_scheduled(  'ywgc_send_postponed_gift_card', array( $post_id ) ) ){
						wp_schedule_single_event( apply_filters( 'ywgc_scheduled_gift_card_delivery_date_timestamp', $delivery_date_timestamp + wc_timezone_offset() ), 'ywgc_send_postponed_gift_card', array( $post_id ), true );
					} else {
						wp_clear_scheduled_hook( 'ywgc_send_postponed_gift_card', array( $post_id ), true );
						wp_schedule_single_event( apply_filters( 'ywgc_scheduled_gift_card_delivery_date_timestamp', $delivery_date_timestamp + wc_timezone_offset() ), 'ywgc_send_postponed_gift_card', array( $post_id ), true );
					}
				}

				$_POST['yit_metaboxes']['_ywgc_delivery_date'] = $delivery_date_timestamp;
				$_POST['yit_metaboxes']['_ywgc_expiration']    = $expiration_date_timestamp;

				update_post_meta( $post_id, '_ywgc_delivery_date', $delivery_date_timestamp );
				update_post_meta( $post_id, '_ywgc_expiration', $expiration_date_timestamp );
			}
		}

		/**
		 * Generate a new gift card code using ajax method
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function generate_gift_card_code_ajax() {
			// Create a new gift card number.
			$numeric_code     = (string) wp_rand( 99999999, mt_getrandmax() );
			$numeric_code_len = strlen( $numeric_code );

			/**
			 * APPLY_FILTERS: ywgc_random_generate_gift_card_code
			 *
			 * Filter the random generation of the gift card code.
			 *
			 * @param string the code randomly generated
			 *
			 * @return string
			 */
			$code        = apply_filters( 'ywgc_random_generate_gift_card_code', strtoupper( sha1( uniqid( wp_rand() ) ) ) );
			$code_len    = strlen( $code );
			$pattern     = get_option( 'ywgc_code_pattern', '****-****-****-****' );
			$pattern_len = strlen( $pattern );

			for ( $i = 0; $i < $pattern_len; $i++ ) {
				if ( '*' === $pattern[ $i ] ) {
					// replace all '*'s with one letter from the unique $code generated.
					$pattern[ $i ] = $code[ $i % $code_len ];
				} elseif ( 'D' === $pattern[ $i ] ) {
					// replace all 'D's with one digit from the unique integer $numeric_code generated.
					$pattern[ $i ] = $numeric_code[ $i % $numeric_code_len ];
				}
			}

			return wp_doing_ajax() ? wp_send_json_success( $pattern ) : $pattern;
		}

		/**
		 * Hash the gift card code so it could be used for security checks
		 *
		 * @param YITH_YWGC_Gift_Card_Extended $gift_card Gift card object.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function hash_gift_card( $gift_card ) {
			return hash( 'md5', $gift_card->gift_card_number . $gift_card->ID );
		}
	}
}
