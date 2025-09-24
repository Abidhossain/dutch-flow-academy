<?php
/**
 * Class YITH_YWGC_Backend_Extended
 *
 * @package YITH\GiftCards\Includes\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWGC_Backend_Extended' ) ) {
	/**
	 * YITH_YWGC_Backend_Extended class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_YWGC_Backend_Extended extends YITH_YWGC_Backend {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWGC_Backend_Extended
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
		 */
		protected function __construct() {
			parent::__construct();

			add_action( 'yith_ywgc_product_settings_after_amount_list', array( $this, 'show_advanced_product_settings' ) );

			/**
			 * Save additional product attribute when a gift card product is saved
			 */
			add_action( 'yith_gift_cards_after_product_save', array( $this, 'save_gift_card_product' ) );

			add_action( 'wp_ajax_ywgc_toggle_enabled_action', array( $this, 'ywgc_toggle_enabled_action' ) );

			add_filter( 'enter_title_here', array( $this, 'post_title_placeholder_for_gift_cards' ), 10, 2 );

			/**
			 * Remove default post metabox for gift card post type
			 */
			add_filter( 'get_user_option_screen_layout_gift_card', '__return_true' );
			add_action( 'add_meta_boxes', array( $this, 'remove_submitdiv_metabox' ), 20 );
			add_action( 'dbx_post_sidebar', array( $this, 'print_save_button_in_edit_page' ), 10, 1 );
		}

		/**
		 * Show advanced product settings
		 *
		 * @param int $thepostid Post ID.
		 */
		public function show_advanced_product_settings( $thepostid ) {
			$this->show_gift_card_expiration_date_settings( $thepostid );
		}

		/**
		 * Show input to enter a expiration date for the gift card
		 *
		 * @param int $product_id the product ID.
		 */
		public function show_gift_card_expiration_date_settings( $product_id ) {
			yith_ywgc_get_view( 'gift-cards-expiration-settings.php', compact( 'product_id' ) );
		}

		/**
		 * Save_gift_card_product
		 * Save additional product attribute when a gift card product is saved
		 *
		 * @param mixed $post_id post_id.
		 *
		 * @return void
		 */
		public function save_gift_card_product( $post_id ) {
			$product = new WC_Product_Gift_Card( $post_id );

			// Expiration settings update.
			if ( isset( $_POST[ 'ywgc-expiration-settings-' . $product->get_id() ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$product->update_expiration_settings_status( sanitize_text_field( wp_unslash( $_POST[ 'ywgc-expiration-settings-' . $product->get_id() ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			} else {
				$product->update_expiration_settings_status( false );
			}

			if ( isset( $_POST['gift-card-expiration-date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$date_format               = apply_filters( 'yith_wcgc_date_format', 'Y-m-d' );
				$date                      = 'd/m/Y' === $date_format ? str_replace( '/', '-', sanitize_text_field( wp_unslash( $_POST['gift-card-expiration-date'] ) ) ) : sanitize_text_field( wp_unslash( $_POST['gift-card-expiration-date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$expiration_date           = is_string( $date ) ? strtotime( $date ) : $date;
				$expiration_date_formatted = ! empty( $expiration_date ) ? date_i18n( $date_format, $expiration_date ) : '';

				update_post_meta( $post_id, '_ywgc_expiration', $expiration_date );
				update_post_meta( $post_id, '_ywgc_expiration_date', $expiration_date_formatted );
			}
		}

		/**
		 * Create the gift cards for the order
		 *
		 * @param WC_Order $order Order object.
		 *
		 * @throws Exception Thrown when there are issues to set the gift card expiration date.
		 */
		public function create_gift_cards_for_order( $order ) {
			/**
			 * APPLY_FILTERS: ywgc_custom_condition_to_create_gift_card
			 *
			 * Filter the condition to generate the gift card. Useful to third-party plugins.
			 *
			 * @param bool true to generate the gift card in the order, false for not
			 * @param object $order the order object
			 *
			 * @return bool
			 */
			if ( ! apply_filters( 'ywgc_custom_condition_to_create_gift_card', true, $order ) ) {
				return;
			}

			/**
			 * APPLY_FILTERS: ywgc_apply_race_condition
			 *
			 * Filter the condition to apply a race condition when generating the gift cards in the order. Useful when having issues with duplicated gift card codes.
			 *
			 * @param bool true to apply it, false for not
			 *
			 * @return bool
			 */
			if ( apply_filters( 'ywgc_apply_race_condition', false ) ) {
				if ( ! $this->start_race_condition( $order->get_id() ) ) {
					return;
				}
			}

			$line_items = $order->get_items( 'line_item' );

			foreach ( $line_items as $order_item_id => $order_item_data ) {
				$product_id_alternative = wc_get_order_item_meta( $order_item_id, '_ywgc_product_id' );

				$product_id = '' !== $order_item_data['product_id'] ? $order_item_data['product_id'] : $product_id_alternative;
				$product    = wc_get_product( $product_id );

				if ( ! $product instanceof WC_Product_Gift_Card ) {
					continue;
				}

				$gift_ids = ywgc_get_order_item_giftcards( $order_item_id );

				if ( $gift_ids ) {
					continue;
				}

				/**
				 * APPLY_FILTERS: yith_ywgc_create_gift_card_for_order_item
				 *
				 * Filter the condition to generate the gift card by order item.
				 *
				 * @param bool true to generate it, false for not
				 * @param object $order the order object
				 * @param int $order_item_id the order item ID
				 * @param array $order_item_data the order item data
				 *
				 * @return bool
				 */
				if ( ! apply_filters( 'yith_ywgc_create_gift_card_for_order_item', true, $order, $order_item_id, $order_item_data ) ) {
					continue;
				}

				$order_id = $order->get_id();

				/**
				 * APPLY_FILTERS: yith_ywgc_line_subtotal
				 *
				 * Filter the line subtotal when generating a gift card code.
				 *
				 * @param float the line subtotal
				 * @param array $order_item_data the order item data
				 * @param int $order_id the order ID
				 * @param int $order_item_id the order item ID
				 *
				 * @return bool
				 */
				$line_subtotal = apply_filters( 'yith_ywgc_line_subtotal', $order_item_data['line_subtotal'], $order_item_data, $order_id, $order_item_id );

				/**
				 * APPLY_FILTERS: yith_ywgc_line_subtotal_tax
				 *
				 * Filter the line subtotal tax when generating a gift card code.
				 *
				 * @param float the line subtotal tax
				 * @param array $order_item_data the order item data
				 * @param int $order_id the order ID
				 * @param int $order_item_id the order item ID
				 *
				 * @return bool
				 */
				$line_subtotal_tax = apply_filters( 'yith_ywgc_line_subtotal_tax', $order_item_data['line_subtotal_tax'], $order_item_data, $order_id, $order_item_id );
				$quantity          = $order_item_data['qty'];
				$single_amount     = (float) ( $line_subtotal / $quantity );
				$single_tax        = (float) ( $line_subtotal_tax / $quantity );
				$new_ids           = array();
				$order_currency    = $order->get_currency();
				$product_id        = wc_get_order_item_meta( $order_item_id, '_ywgc_product_id' );
				$is_digital        = wc_get_order_item_meta( $order_item_id, '_ywgc_is_digital' );
				$is_postdated      = false;

				if ( $is_digital ) {
					/**
					 * APPLY_FILTERS: ywgc_recipients_array_on_create_gift_cards_for_order
					 *
					 * Filter the gift card recipients on gift card creation.
					 *
					 * @param array the recipients
					 *
					 * @return array
					 */
					$recipients        = apply_filters( 'ywgc_recipients_array_on_create_gift_cards_for_order', wc_get_order_item_meta( $order_item_id, '_ywgc_recipients' ) );
					$recipient_count   = count( $recipients );
					$sender            = wc_get_order_item_meta( $order_item_id, '_ywgc_sender_name' );
					$recipient_name    = wc_get_order_item_meta( $order_item_id, '_ywgc_recipient_name' );
					$message           = wc_get_order_item_meta( $order_item_id, '_ywgc_message' );
					$has_custom_design = wc_get_order_item_meta( $order_item_id, '_ywgc_has_custom_design' );
					$design_type       = wc_get_order_item_meta( $order_item_id, '_ywgc_design_type' );

					/**
					 * APPLY_FILTERS: ywgc_is_postdated_delivery_date_by_default
					 *
					 * Filter the if the gift card is postdated.
					 *
					 * @param bool true if is postdated, false if not
					 *
					 * @return bool
					 */
					$is_postdated = apply_filters( 'ywgc_is_postdated_delivery_date_by_default', wc_get_order_item_meta( $order_item_id, '_ywgc_postdated', true ) );

					if ( $is_postdated ) {
						$delivery_date = wc_get_order_item_meta( $order_item_id, '_ywgc_delivery_date' );
					}
				}

				for ( $i = 0; $i < $quantity; $i++ ) {
					// Generate a gift card post type and save it.
					$gift_card = new YITH_YWGC_Gift_Card_Extended();

					$gift_card->product_id    = $product_id;
					$gift_card->order_id      = $order_id;
					$gift_card->order_item_id = $order_item_id;
					$gift_card->is_digital    = $is_digital;

					if ( $gift_card->is_digital ) {
						$gift_card->sender_name        = $sender;
						$gift_card->recipient_name     = $recipient_name;
						$gift_card->message            = $message;
						$gift_card->design_type        = $design_type;
						$gift_card->postdated_delivery = $is_postdated;

						if ( $is_postdated ) {
							$gift_card->delivery_date = $delivery_date;
						}

						if ( $has_custom_design ) {
							$gift_card->design = wc_get_order_item_meta( $order_item_id, '_ywgc_design' );
						}

						/**
						 * If the user entered several recipient email addresses, one gift card
						 * for every recipient will be created and it will be the unique recipient for
						 * that email. If only one, or none if allowed, recipient email address was entered
						 * then create '$quantity' specular gift cards
						 */
						if ( ( 1 == $recipient_count ) && ! empty( $recipients[0] ) ) {
							$gift_card->recipient = $recipients[0];
						} elseif ( ( $recipient_count > 1 ) && ! empty( $recipients[ $i ] ) ) {
							$gift_card->recipient = $recipients[ $i ];
						} else {
							/**
							 * APPLY_FILTERS: ywgc_is_postdated_delivery_date_by_default
							 *
							 * Filter the customer as the gift card recipient using the billing email.
							 *
							 * @param string the recipient email
							 *
							 * @return string
							 */
							$gift_card->recipient = apply_filters( 'yith_ywgc_set_default_gift_card_recipient', $order->get_billing_email() );
						}
					}

					if ( $gift_card->is_virtual() && 'yes' === get_option( 'ywgc_enable_pre_printed_virtual', 'no' ) || apply_filters( 'yith_ywgc_custom_condition_set_gift_card_as_preprinted', false, $gift_card ) ) {
						$gift_card->set_as_pre_printed();
					} elseif ( ! $gift_card->is_virtual() && 'yes' === get_option( 'ywgc_enable_pre_printed_physical', 'no' ) || apply_filters( 'yith_ywgc_custom_condition_set_gift_card_as_preprinted', false, $gift_card ) ) {
						$gift_card->set_as_pre_printed();
					} else {
						$attempts = 100;

						do {
							/**
							 * APPLY_FILTERS: yith_wcgc_generated_code
							 *
							 * Filter the generated gift card code.
							 *
							 * @param string the gift card code
							 * @param object $order the order object
							 * @param object $gift_card the gift card object
							 *
							 * @return string
							 */
							$code       = apply_filters( 'yith_wcgc_generated_code', YITH_YWGC()->generate_gift_card_code(), $order, $gift_card );
							$check_code = YITH_YWGC()->get_gift_card_by_code( $code );

							if ( ! $check_code || is_object( $check_code ) && ! $check_code->ID ) {
								$gift_card->gift_card_number = $code;
								break;
							}

							--$attempts;
						} while ( $attempts > 0 );

						if ( ! $attempts ) {
							// Unable to find a unique code, the gift card need a manual code entered.
							$gift_card->set_as_code_not_valid();
						}
					}

					$gift_card->total_amount = $single_amount + $single_tax;

					// Add the default amount and not the converted one by WPML.
					global $woocommerce_wpml;

					$default_currency_amount = wc_get_order_item_meta( $order_item_id, '_ywgc_default_currency_amount' );

					if ( $woocommerce_wpml && $woocommerce_wpml->multi_currency && ! empty( $default_currency_amount ) ) {
						$gift_card->total_amount = $default_currency_amount;
					}

					if ( defined( 'WOOCOMMERCE_MULTICURRENCY_VERSION' ) ) {
						$gift_card->total_amount = $default_currency_amount;
					}

					$gift_card->update_balance( $gift_card->total_amount );
					$gift_card->version  = YITH_YWGC_VERSION;
					$gift_card->currency = $order_currency;

					$expiration_date        = get_post_meta( $product_id, '_ywgc_expiration', true );
					$expiration_date_status = $product->get_expiration_settings_status();

					if ( '' !== $expiration_date && $expiration_date_status ) {
						if ( 0 == intval( $expiration_date ) ) {
							$gift_card->expiration = 0;
						} else {
							$gift_card->expiration = $expiration_date;
						}
					} else {
						try {
							/**
							 * APPLY_FILTERS: ywgc_usage_expiration_in_months
							 *
							 * Filter the gift card usage expiration in months.
							 *
							 * @param string the usage expiration in months
							 * @param object $gift_card the gift card object
							 * @param int $product_id the product ID
							 *
							 * @return string
							 */
							$usage_expiration      = apply_filters( 'ywgc_usage_expiration_in_months', get_option( 'ywgc_usage_expiration', '' ), $gift_card, $product_id );
							$start_usage_date      = $gift_card->delivery_date ? $gift_card->delivery_date : current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
							$gift_card->expiration = '0' !== $usage_expiration ? strtotime( "+$usage_expiration month", $start_usage_date ) : '0';
						} catch ( Exception $e ) {
							error_log( 'An error occurred setting the expiration date for gift card: ' . $gift_card->gift_card_number ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
						}
					}

					/**
					 * DO_ACTION: yith_ywgc_before_gift_card_generation_save
					 *
					 * Allow actions before the gift card generated is save.
					 *
					 * @param object $gift_card the gift card object
					 */
					do_action( 'yith_ywgc_before_gift_card_generation_save', $gift_card );

					$gift_card->save();

					if ( apply_filters( 'yith_ywgc_register_gift_card_purchase_customer', false ) ) {
						$user_id = $order->get_customer_id();

						update_post_meta( $gift_card->ID, YWGC_META_GIFT_CARD_CUSTOMER_USER, $user_id );
					}

					update_post_meta( $gift_card->ID, '_ywgc_default_currency_amount', $default_currency_amount );

					/**
					 * DO_ACTION: yith_ywgc_after_gift_card_generation_save
					 *
					 * Allow actions after the gift card generated is save.
					 *
					 * @param object $gift_card the gift card object
					 */
					do_action( 'yith_ywgc_after_gift_card_generation_save', $gift_card );

					$new_ids[] = $gift_card->ID;

					/**
					 * APPLY_FILTERS: ywgc_send_gift_card_code_by_default
					 *
					 * Filter the condition to send the gift card code.
					 *
					 * @param bool true to send it, false for not. Default: true
					 * @param object $gift_card the gift card object
					 *
					 * @return bool
					 */
					/**
					 * APPLY_FILTERS: yith_wcgc_send_now_gift_card_to_custom_recipient
					 *
					 * Filter the condition to send the gift card code to a custom recipient.
					 *
					 * @param bool true to send it, false for not. Default: false
					 * @param object $gift_card the gift card object
					 *
					 * @return bool
					 */
					if ( ( ! $is_postdated && apply_filters( 'ywgc_send_gift_card_code_by_default', true, $gift_card ) ) && $gift_card->get_code() !== '' || apply_filters( 'yith_wcgc_send_now_gift_card_to_custom_recipient', false, $gift_card ) ) {
						YITH_YWGC_Emails_Extended::get_instance()->send_gift_card_email( $gift_card );
					}
				}

				// save gift card Post ids on order item.
				ywgc_set_order_item_giftcards( $order_item_id, $new_ids );
			}

			if ( apply_filters( 'ywgc_apply_race_condition', false ) ) {
				$this->end_race_condition( $order->get_id() );
			}
		}

		/**
		 * Manage the enable/disable toggle in the gift cards table
		 */
		public function ywgc_toggle_enabled_action() {
			if ( isset( $_POST['id'], $_POST['enabled'] ) && 'no' === $_POST['enabled'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$gift_card = new YITH_YWGC_Gift_Card_Extended( array( 'ID' => intval( $_POST['id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$gift_card->set_enabled_status( false );
			} elseif ( isset( $_POST['id'], $_POST['enabled'] ) && 'yes' === $_POST['enabled'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$gift_card = new YITH_YWGC_Gift_Card_Extended( array( 'ID' => intval( $_POST['id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$gift_card->set_enabled_status( true );
			}
		}
		/**
		 * Enqueue scripts on administration comment page
		 */
		public function enqueue_backend_files() {
			parent::enqueue_backend_files();

			$screen = get_current_screen();

			if ( ( isset( $_REQUEST['page'] ) && 'yith_woocommerce_gift_cards_panel' === $_REQUEST['page'] ) || 'edit-giftcard-category' === $screen->id || 'product' === $screen->id || 'edit-gift_card' === $screen->id ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_enqueue_style(
					'ywgc_gift_cards_admin_panel_css',
					YITH_YWGC_ASSETS_URL . '/css/ywgc-gift-cards-admin-panel.css',
					array(),
					YITH_YWGC_VERSION
				);

				wp_register_script(
					'ywgc_gift_cards_admin_panel',
					YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-gift-cards-admin-panel.js' ),
					array(
						'jquery',
						'jquery-blockui',
					),
					YITH_YWGC_VERSION,
					true
				);

				/**
				 * APPLY_FILTERS: yith_gift_cards_loader
				 *
				 * Filter the URL of the ajax loader gif for the plugin.
				 *
				 * @param string the gif URL
				 *
				 * @return string
				 */
				wp_localize_script(
					'ywgc_gift_cards_admin_panel',
					'ywgc_data',
					array(
						'loader'                    => apply_filters( 'yith_gift_cards_loader', YITH_YWGC_ASSETS_URL . '/images/loading.gif' ),
						'ajax_url'                  => admin_url( 'admin-ajax.php' ),
						'save_email_settings_nonce' => wp_create_nonce( 'yith_ywgc_save_email_settings' ),
						'save_email_status_nonce'   => wp_create_nonce( 'yith_ywgc_save_email_status' ),
					)
				);

				wp_enqueue_script( 'ywgc_gift_cards_admin_panel' );
			}

			$this->add_generate_gift_card_code_button();
		}

		/**
		 * Add the js file to generate code button for gift card post type
		 *
		 * @since 3.15.0
		 * @return void
		 */
		public function add_generate_gift_card_code_button() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			wp_register_script( 'yith-admin-gift-card-meta-boxes', YITH_YWGC_SCRIPT_URL . yit_load_js_file( 'ywgc-meta-boxes-gift-card.js' ), array( 'jquery' ), YITH_YWGC_VERSION, true );
			wp_localize_script(
				'yith-admin-gift-card-meta-boxes',
				'yith_wcgc_admin_meta_boxes_gift_card',
				array(
					'generate_button_text' => esc_html__( 'Generate code', 'yith-woocommerce-gift-cards' ),
				)
			);

			if ( 'gift_card' === $screen_id ) {
				wp_enqueue_script( 'yith-admin-gift-card-meta-boxes' );
			}
		}

		/**
		 * Make some redirect based on the current action being performed
		 *
		 * @since  1.0.0
		 */
		public function redirect_gift_cards_link() {
			/**
			 * Check if the user ask for retrying sending the gift card email that are not shipped yet
			 */
			if ( isset( $_GET[ YWGC_ACTION_RETRY_SENDING ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$gift_card_id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				YITH_YWGC_Emails_Extended::get_instance()->send_gift_card_email( $gift_card_id, false );
				$redirect_url = remove_query_arg( array( YWGC_ACTION_RETRY_SENDING, 'id' ) );

				wp_safe_redirect( $redirect_url );
				exit;
			}

			/**
			 * Check if the user ask for enabling/disabling a specific gift cards
			 */
			if ( isset( $_GET[ YWGC_ACTION_ENABLE_CARD ] ) || isset( $_GET[ YWGC_ACTION_DISABLE_CARD ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$gift_card_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$enabled      = isset( $_GET[ YWGC_ACTION_ENABLE_CARD ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$gift_card = new YITH_YWGC_Gift_Card( array( 'ID' => $gift_card_id ) );

				if ( ! $gift_card->is_dismissed() ) {
					$current_status = $gift_card->is_enabled();

					if ( $current_status !== $enabled ) {
						$gift_card->set_enabled_status( $enabled );
						do_action( 'yith_gift_cards_status_changed', $gift_card, $enabled );
					}

					wp_safe_redirect(
						remove_query_arg(
							array(
								YWGC_ACTION_ENABLE_CARD,
								YWGC_ACTION_DISABLE_CARD,
								'id',
							)
						)
					);
					die();
				}
			}

			if ( ! isset( $_GET['post_type'] ) || ! isset( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			if ( 'shop_coupon' !== ( sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			if ( preg_match( '/(\w{4}-\w{4}-\w{4}-\w{4})(.*)/i', sanitize_text_field( wp_unslash( $_GET['s'] ) ), $matches ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_safe_redirect( admin_url( 'edit.php?s=' . $matches[1] . '&post_type=gift_card' ) );
				die();
			}
		}

		/**
		 * Specify custom bulk actions messages for gift card post type.
		 *
		 * @param string $message post title placeholder.
		 * @param object $post the post.
		 *
		 * @return string
		 */
		public function post_title_placeholder_for_gift_cards( $message, $post ) {
			if ( ! ! $post && isset( $post->post_type ) && YWGC_CUSTOM_POST_TYPE_NAME === $post->post_type ) {
				$message = __( 'Enter code', 'yith-woocommerce-gift-cards' );
			}

			return $message;
		}

		/**
		 * Remove submitdiv metabox from Gift Card post type page
		 */
		public function remove_submitdiv_metabox() {
			remove_meta_box( 'submitdiv', 'gift_card', 'side' );
		}

		/**
		 * Print save button in edit page.
		 *
		 * @since 3.15.0
		 * @param WP_Post $post The post.
		 */
		public function print_save_button_in_edit_page( $post ) {
			if ( ! ! $post && isset( $post->post_type ) && YWGC_CUSTOM_POST_TYPE_NAME === $post->post_type ) {
				global $post_id;

				$is_updating = ! ! $post_id;

				?>
				<div class="yith-wcgf-post-type__actions yith-plugin-ui">
					<?php if ( $is_updating ) : ?>
						<button id="yith-wcgc-post-type__save" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl"><?php esc_html_e( 'Save Gift Card', 'yith-woocommerce-gift-cards' ); ?></button>
					<?php else : ?>
						<input id="yith-wcgc-post-type__save" type="submit" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl" name="publish" value="<?php esc_html_e( 'Save Gift Card', 'yith-woocommerce-gift-cards' ); ?>">
					<?php endif; ?>
					<a id="yith-wcgc-post-type__float-save" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl yith-plugin-fw-animate__appear-from-bottom"><?php esc_html_e( 'Save Gift Card', 'yith-woocommerce-gift-cards' ); ?></a>
				</div>
				<?php
			}
		}
	}
}
