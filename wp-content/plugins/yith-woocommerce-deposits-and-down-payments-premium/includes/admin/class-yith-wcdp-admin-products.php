<?php
/**
 * Admin Product class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Admin_Product' ) ) {
	/**
	 * Admin products handling
	 *
	 * @since 2.0.0
	 */
	class YITH_WCDP_Admin_Products {

		/**
		 * Array of fields available on admin side for each product
		 *
		 * @var array
		 */
		protected static $fields = array();

		/**
		 * Init method
		 */
		public static function init() {
			// register product tabs.
			add_filter( 'woocommerce_product_data_tabs', array( self::class, 'register_product_tabs' ) );
			add_action( 'woocommerce_product_data_panels', array( self::class, 'print_product_deposit_tabs' ), 10 );

			// register quick edit / bulk edit.
			add_action( 'quick_edit_custom_box', array( self::class, 'print_bulk_editing_fields' ), 10, 2 );
			add_action( 'bulk_edit_custom_box', array( self::class, 'print_bulk_editing_fields' ), 10, 2 );
			add_action( 'save_post', array( self::class, 'save_bulk_editing_fields' ), 10, 2 );

			// save tabs options.
			add_action( 'woocommerce_process_product_meta', array( self::class, 'save_product_deposit_tabs' ), 10, 1 );

			// add variation settings.
			add_action( 'woocommerce_product_after_variable_attributes', array( self::class, 'print_variation_deposit_settings' ), 10, 3 );
			add_action( 'woocommerce_save_product_variation', array( self::class, 'save_variation_deposits_settings' ), 10, 2 );

			add_action( 'yith_plugin_fw_get_field_after', array( self::class, 'add_yith_ui_calendar_icon' ) );
		}

		/* === BULK PRODUCT EDITING === */

		/**
		 * Print Quick / Bulk editing fields
		 *
		 * @param string $column_name Current column Name.
		 * @param string $post_type   Current post type.
		 *
		 * @since 1.0.2
		 */
		public static function print_bulk_editing_fields( $column_name, $post_type ) {
			global $post;

			if ( 'product' !== $post_type || 'product_tag' !== $column_name ) {
				return;
			}

			$product = wc_get_product( $post->ID );

			if ( $product ) {
				$override_deposit = yith_plugin_fw_is_true( $product->get_meta( '_override_deposit_options' ) );
				$override_balance = yith_plugin_fw_is_true( $product->get_meta( '_override_balance_options' ) );

				$enable_deposit        = $override_deposit ? $product->get_meta( '_enable_deposit' ) : 'default';
				$deposit_default       = $override_deposit ? $product->get_meta( '_deposit_default' ) : 'default';
				$force_deposit         = $override_deposit ? $product->get_meta( '_force_deposit' ) : 'default';
				$create_balance_orders = $override_balance ? $product->get_meta( '_create_balance_orders' ) : 'default';

				$product_note = $override_deposit && yith_plugin_fw_is_true( $product->get_meta( '_show_product_notes' ) ) ? $product->get_meta( '_product_note' ) : '';
			} else {
				$enable_deposit        = 'default';
				$deposit_default       = 'default';
				$force_deposit         = 'default';
				$create_balance_orders = 'default';
				$product_note          = '';
			}

			include YITH_WCDP_DIR . 'views/product-deposit-bulk-edit.php';
		}

		/**
		 * Save Quick / Bulk editing fields
		 *
		 * @param int     $post_id Post id.
		 * @param WP_Post $post    Post object.
		 *
		 * @return void
		 * @since 1.0.2
		 */
		public static function save_bulk_editing_fields( $post_id, $post ) {
			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Don't save revisions and autosaves.
			if (
				wp_is_post_revision( $post_id ) ||
				wp_is_post_autosave( $post_id ) ||
				'product' !== $post->post_type ||
				! current_user_can( 'edit_post', $post_id )
			) {
				return;
			}

			// Check nonce.
			if (
				! isset( $_REQUEST['woocommerce_quick_edit_nonce'] ) ||
				! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['woocommerce_quick_edit_nonce'] ) ), 'woocommerce_quick_edit_nonce' )
			) {
				return;
			}

			$post_ids = ( ! empty( $_REQUEST['post'] ) ) ? array_map( 'absint', (array) $_REQUEST['post'] ) : array();

			if ( empty( $post_ids ) ) {
				$post_ids = array( $post_id );
			}

			$enable_deposit        = isset( $_REQUEST['_enable_deposit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_enable_deposit'] ) ) : false;
			$deposit_default       = isset( $_REQUEST['_deposit_default'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_deposit_default'] ) ) : false;
			$force_deposit         = isset( $_REQUEST['_force_deposit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_force_deposit'] ) ) : false;
			$create_balance_orders = isset( $_REQUEST['_create_balance_orders'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_create_balance_orders'] ) ) : false;
			$product_note          = isset( $_REQUEST['_product_note'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['_product_note'] ) ) : false;

			// if everything is in order.
			if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
				foreach ( $post_ids as $post_id ) {
					$product = wc_get_product( $post_id );

					if ( ! $product ) {
						continue;
					}

					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						continue;
					}

					// deposits overrides.
					if ( 'default' !== $enable_deposit || 'default' !== $deposit_default || 'default' !== $force_deposit || ! empty( $product_note ) ) {
						$product->update_meta_data( '_override_deposit_options', 'yes' );

						if ( 'default' !== $enable_deposit ) {
							$product->update_meta_data( '_enable_deposit', $enable_deposit );
						}

						if ( 'default' !== $deposit_default ) {
							$product->update_meta_data( '_deposit_default', $deposit_default );
						}

						if ( 'default' !== $force_deposit ) {
							$product->update_meta_data( '_force_deposit', $force_deposit );
						}

						if ( ! empty( $product_note ) ) {
							$product->update_meta_data( '_show_product_notes', 'yes' );
							$product->update_meta_data( '_product_note', $product_note );
						}
					} else {
						$product->update_meta_data( '_override_deposit_options', 'no' );
					}

					// balance overrides.
					if ( 'default' !== $create_balance_orders ) {
						$product->update_meta_data( '_override_balance_options', 'yes' );
						$product->update_meta_data( '_create_balance_orders', $create_balance_orders );
					} else {
						$product->update_meta_data( '_override_balance_options', 'no' );
					}

					$product->save();
				}
			}
		}

		/* == PRODUCT TABS METHODS === */

		/**
		 * Returns deposit meta for passed product
		 *
		 * @param WC_Product $product Product object.
		 *
		 * @return array Array of deposit-related meta for the product.
		 */
		public static function get_product_meta( $product ) {
			$product_meta = array();

			if ( ! $product ) {
				return $product_meta;
			}

			foreach ( self::get_fields() as $meta_key => $meta_options ) {
				$variable = 0 === strpos( $meta_key, '_' ) ? substr( $meta_key, 1 ) : $meta_key;
				$default  = isset( $meta_options['default'] ) ? $meta_options['default'] : '';

				$meta_value = $product->get_meta( $meta_key );

				$product_meta[ $variable ] = ! empty( $meta_value ) ? $meta_value : $default;
			}

			return $product_meta;
		}

		/* === PRODUCT FIELDS === */

		/**
		 * Returns id to use for a specific field in product edit page
		 *
		 * @param string   $field_key Field key.
		 * @param int|bool $loop      Index for current row (in case of variations).
		 *
		 * @return string Field id.
		 */
		public static function get_field_id( $field_key, $loop = false ) {
			$field_id = $field_key;

			if ( false !== $loop ) {
				$field_id = "{$field_id}_{$loop}";
			}

			return $field_id;
		}

		/**
		 * Returns name to use for a specific field in product edit page
		 *
		 * @param string   $field_key Field key.
		 * @param int|bool $loop      Index for current row (in case of variations).
		 *
		 * @return string Field name.
		 */
		public static function get_field_name( $field_key, $loop = false ) {
			$field_name = "_$field_key";

			if ( false !== $loop ) {
				$field_name = "{$field_name}[$loop]";
			}

			return $field_name;
		}

		/**
		 * Returns value of a specific field for a product
		 *
		 * @param string     $field_key Field key.
		 * @param WC_Product $product   Current product.
		 *
		 * @return mixed Field value.
		 */
		public static function get_field_value( $field_key, $product ) {
			$fields   = self::get_fields();
			$meta_key = self::get_field_name( $field_key );

			if ( ! isset( $fields[ $field_key ] ) ) {
				return false;
			}

			$meta_value = $product->get_meta( $meta_key );

			if ( ( ! $meta_value || 'default' === $meta_value ) && isset( $fields[ $field_key ]['default'] ) ) {
				$meta_value = $fields[ $field_key ]['default'];
			}

			if ( 'expiration_notification_limit' === $field_key ) {
				$meta_value = yith_wcdp_days_to_duration( $meta_value );
			}

			return $meta_value;
		}

		/**
		 * Retrieve list of fields for product edit page.
		 *
		 * @return array Array of available product's fields
		 * @since 4.2.0
		 */
		public static function get_fields() {
			if ( ! static::$fields ) {
				static::$fields = apply_filters(
					'yith_wcdp_admin_product_fields',
					array(
						'override_deposit_options'      => array(
							'title'   => __( 'Override deposit options', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'    => __( 'Enable to override the global deposit options for this product.', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'    => 'onoff',
							'default' => 'no',
						),
						'enable_deposit'                => array(
							'title'        => __( 'Enable deposit for this product', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'         => __( 'Enable to offer deposit option for this product', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'onoff',
							'default'      => YITH_WCDP_Options::get( 'enable_deposit' ),
							'dependencies' => array( 'override_deposit_options' => 'yes' ),
						),
						'force_deposit'                 => array(
							'title'        => __( 'Set the deposit payment as:', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'         => __( 'Choose how to manage the deposit option for this product.', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'radio',
							'options'      => array(
								'no'  => __( 'Optional: users can choose between paying the full amount or just a deposit', 'yith-woocommerce-deposits-and-down-payments' ),
								'yes' => __( 'Forced: users can only pay the deposit amount', 'yith-woocommerce-deposits-and-down-payments' ),
							),
							'default'      => YITH_WCDP_Options::get( 'force_deposit' ),
							'dependencies' => array( 'enable_deposit' => 'yes' ),
						),
						'deposit_default'               => array(
							'title'        => __( 'Show Deposit option selected by default', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'         => __( 'Enable to show the deposit option selected by default.', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'onoff',
							'default'      => YITH_WCDP_Options::get( 'deposit_default' ),
							'dependencies' => array( 'force_deposit' => 'no' ),
						),
						'show_product_notes'            => array(
							'title'        => __( 'Show custom notice', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'         => __( 'Enable to show a custom notice about the deposit option in this product.', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'onoff',
							'default'      => YITH_WCDP_Options::get( 'show_product_notes' ),
							'dependencies' => array( 'enable_deposit' => 'yes' ),
						),

						'product_note'                  => array(
							'title'        => __( 'Notice to show', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'         => __( 'Enter the notice you want to show on the product page.', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'textarea',
							'default'      => YITH_WCDP_Options::get( 'product_note' ),
							'dependencies' => array( 'show_product_notes' => 'yes' ),
						),
						'override_balance_options'      => array(
							'title'   => __( 'Override balance options', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'    => __( 'Enable to override the global balance options in this product.', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'    => 'onoff',
							'default' => 'no',
						),
						'create_balance_orders'         => array(
							'title'        => __( 'Balance order creation for this product', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'         => __( 'Choose how to manage the balance for this product.', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'radio',
							'options'      => array(
								'pending' => __( 'Create balance orders with "Pending payment" status, and users will pay the balance online', 'yith-woocommerce-deposits-and-down-payments' ),
								'on-hold' => __( 'Create balance orders with "On hold" status, and manage payments manually (e.g.: users pay cash in your shop)', 'yith-woocommerce-deposits-and-down-payments' ),
								'none'    => __( 'Do not create balance orders', 'yith-woocommerce-deposits-and-down-payments' ),
							),
							'default'      => YITH_WCDP_Options::get( 'create_balance' ),
							'dependencies' => array( 'override_balance_options' => 'yes' ),
						),

						'enable_expiration'             => array(
							'title'        => __( 'Require balance payment to customers', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'         => __( 'Enable to choose when and how to require the balance payment.', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'onoff',
							'default'      => YITH_WCDP_Options::get( 'enable_expiration' ),
							'dependencies' => array( 'create_balance_orders' => '!none' ),
						),
						'expiration_type'               => array(
							'title'        => __( 'The balance payment will be required', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'select',
							'class'        => 'wc-enhanced-select',
							'options'      => apply_filters(
								'yith_wcdp_deposit_expiration_types',
								array(
									'specific_date' => __( 'On a specific date', 'yith-woocommerce-deposits-and-down-payments' ),
									'num_of_days'   => __( 'After a specific range of days from the deposit', 'yith-woocommerce-deposits-and-down-payments' ),
								)
							),
							'default'      => YITH_WCDP_Options::get( 'expiration_type' ),
							'dependencies' => array( 'enable_expiration' => 'yes' ),
						),
						'expiration_date'               => array(
							'title'        => __( 'Require balance payment on', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'datepicker',
							'default'      => YITH_WCDP_Options::get( 'expiration_date' ),
							'data'         => array(
								'date-format' => 'yy-mm-dd',
							),
							'dependencies' => array( 'expiration_type' => 'specific_date' ),
						),
						'expiration_duration'           => array(
							'title'             => __( 'Require balance payment', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'              => __( 'days from the deposit', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'              => 'number',
							'default'           => YITH_WCDP_Options::get( 'expiration_duration' ),
							'dependencies'      => array( 'expiration_type' => 'num_of_days' ),
							'custom_attributes' => array(
								'min'  => 1,
								'max'  => 9999999,
								'step' => 1,
							),
						),
						'deposit_expiration_product_fallback' => array(
							'title'        => __( 'When balance is required on a specific date, and is overdue', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'select',
							'class'        => 'wc-enhanced-select',
							'options'      => array(
								'do_nothing'           => __( 'Do nothing', 'yith-woocommerce-deposits-and-down-payments' ),
								'disable_deposit'      => __( 'Disable deposit for the product', 'yith-woocommerce-deposits-and-down-payments' ),
								'item_not_purchasable' => __( 'Make product no longer purchasable', 'yith-woocommerce-deposits-and-down-payments' ),
								'hide_item'            => __( 'Hide product from catalog', 'yith-woocommerce-deposits-and-down-payments' ),
							),
							'default'      => YITH_WCDP_Options::get( 'expiration_product_fallback' ),
							'dependencies' => array( 'expiration_type' => 'specific_date' ),
						),
						'notify_expiration'             => array(
							'title'        => __( 'Notify balance payment to customer', 'yith-woocommerce-deposits-and-down-payments' ),
							'desc'         => __(
								'Enable to automatically send an email to the customer about the balance payment.<br/>
								<b>Note:</b> if the user has to pay manually, he/she will get an email that redirects him/her to pay.<br/>
								If the Stripe option is enabled and the balance is automatically charged, the user will get an email that notifies the automatic charge of the balance on his/her credit card. ',
								'yith-woocommerce-deposits-and-down-payments'
							),
							'type'         => 'onoff',
							'default'      => YITH_WCDP_Options::get( 'notify_expiration' ),
							'dependencies' => array( 'enable_expiration' => 'yes' ),
						),
						'expiration_notification_limit' => array(
							'title'        => __( 'Send the email about the balance payment', 'yith-woocommerce-deposits-and-down-payments' ),
							'type'         => 'custom',
							'action'       => 'yith_wcdp_print_duration_field',
							'inline_desc'  => __( 'before the payment due date.', 'yith-woocommerce-deposits-and-down-payments' ),
							'default'      => 15,
							'dependencies' => array( 'notify_expiration' => 'yes' ),
						),
					)
				);
			}

			return static::$fields;
		}

		/**
		 * Returns a list of fields formatted to be processed by {@see yith_plugin_fw_get_field}
		 *
		 * @param WC_Product $product Product object, used to retrieve field value.
		 * @param int|bool   $loop    Index for current row (in case of variations).
		 *
		 * @return array Array of formatted fields.
		 */
		public static function get_formatted_fields( $product, $loop = false ) {
			$fields    = self::get_fields();
			$formatted = array();

			foreach ( $fields as $field_key => $field ) {
				$field_id    = self::get_field_id( $field_key, $loop );
				$field_name  = self::get_field_name( $field_key, $loop );
				$field_value = self::get_field_value( $field_key, $product );

				// dependencies handling.
				if ( isset( $field['dependencies'] ) ) {
					$formatted_deps = array();

					foreach ( $field['dependencies'] as $dep_key => $dep_value ) {
						$formatted_deps[ self::get_field_id( $dep_key, $loop ) ] = $dep_value;
					}

					$field['custom_attributes']['data-dependencies'] = wp_json_encode( $formatted_deps );
				}

				$formatted[ $field_key ] = array_merge(
					array(
						'id'    => $field_id,
						'name'  => $field_name,
						'value' => $field_value,
					),
					$field
				);
			}

			return $formatted;
		}

		/* === PRODUCT TAB HANDLING === */

		/**
		 * Register product tabs for deposit plugin
		 *
		 * @param array $tabs Registered tabs.
		 *
		 * @return array Filtered array of registered tabs
		 * @since 1.0.0
		 */
		public static function register_product_tabs( $tabs ) {
			$tabs = array_merge(
				$tabs,
				array(
					'deposit' => array(
						'label'  => __( 'Deposit & Balance', 'yith-woocommerce-deposits-and-down-payments' ),
						'target' => 'yith_wcdp_deposit_tab',
						'class'  => array( 'hide_if_grouped', 'hide_if_external' ),
					),
				)
			);

			return $tabs;
		}

		/**
		 * Print product tab for deposit plugin
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function print_product_deposit_tabs() {
			global $post;

			$product = wc_get_product( $post->ID );
			$fields  = self::get_formatted_fields( $product );

			include YITH_WCDP_DIR . 'views/product-deposit-tab.php';
		}

		/**
		 * Print additional fields on variation tab
		 *
		 * @param int   $loop           Unique ID for current variation row.
		 * @param array $variation_data Array of variation attributes.
		 * @param int   $variation      Variation id.
		 *
		 * @return void
		 * @since 1.0.4
		 */
		public static function print_variation_deposit_settings( $loop, $variation_data, $variation ) {

			if (
				apply_filters( 'yith_wcdp_disable_deposit_variation_option', false, $variation ) ||
				! apply_filters( 'yith_wcdp_generate_add_deposit_to_cart_variations_field', true, $variation )
			) {
				return;
			}

			$product = wc_get_product( $variation );
			$fields  = self::get_formatted_fields( $product, $loop );

			include YITH_WCDP_DIR . 'views/product-deposit-tab.php';
		}

		/**
		 * Save deposit tab options
		 *
		 * @param int $post_id Current product id.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function save_product_deposit_tabs( $post_id ) {
			// we don't need to check for nonce, as WooCommerce already does this on /wp-content/plugins/woocommerce/includes/admin/class-wc-admin-meta-boxes.php:200.
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$product = wc_get_product( $post_id );

			// nonce verification not needed.
			$clean_data = self::get_deposit_posted_data( $_POST );

			foreach ( $clean_data as $meta_key => $meta_value ) {
				$product->update_meta_data( $meta_key, $meta_value );
			}

			$product->save();
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Save additional fields on variation tab
		 *
		 * @param int $variation_id Variation id.
		 * @param int $loop         Unique ID for current variation row.
		 *
		 * @return void
		 * @since 1.0.4
		 */
		public static function save_variation_deposits_settings( $variation_id, $loop ) {
			// we don't need to check for nonce, as WooCommerce already does this on /wp-content/plugins/woocommerce/includes/admin/class-wc-admin-meta-boxes.php:200.
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if (
				apply_filters( 'yith_wcdp_disable_deposit_variation_option', false, $variation_id ) ||
				! apply_filters( 'yith_wcdp_generate_add_deposit_to_cart_variations_field', true, $variation_id )
			) {
				return;
			}

			$variation = wc_get_product( $variation_id );

			// nonce verification not needed.
			$clean_data = self::get_deposit_posted_data( $_POST, $loop );

			foreach ( $clean_data as $meta_key => $meta_value ) {
				$variation->update_meta_data( $meta_key, $meta_value );
			}

			$variation->save();
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Get cleaned product meta, as submitted in the posted data
		 *
		 * @param array    $posted Posted data.
		 * @param int|bool $loop   Index for current row (in case of variations).
		 *
		 * @return array Clean posted data.
		 * @since 4.2.0
		 */
		protected static function get_deposit_posted_data( $posted, $loop = false ) {
			$fields  = self::get_fields();
			$cleaned = array();

			if ( ! $fields ) {
				return $cleaned;
			}

			foreach ( $fields as $field_key => $field ) {
				$posted_value = false;
				$field_name   = self::get_field_name( $field_key );

				if ( false !== $loop && isset( $posted[ $field_name ][ $loop ] ) ) {
					$posted_value = $posted[ $field_name ][ $loop ];
				} elseif ( false === $loop && isset( $posted[ $field_name ] ) ) {
					$posted_value = $posted[ $field_name ];
				}

				// manage posted data for various cases.
				if ( 'expiration_notification_limit' === $field_key ) {
					$cleaned[ $field_name ] = yith_wcdp_duration_to_days( $posted_value );
				} elseif ( isset( $field['options'] ) && ( is_int( $posted_value ) || is_string( $posted_value ) ) && array_key_exists( $posted_value, $field['options'] ) ) {
					$cleaned[ $field_name ] = $posted_value;
				} elseif ( 'onoff' === $field['type'] && empty( $posted_value ) ) {
					$cleaned[ $field_name ] = 'no';
				} else {
					$cleaned[ $field_name ] = sanitize_text_field( wp_unslash( $posted_value ) );
				}

				// set default value.
				if ( empty( $cleaned[ $field_name ] ) && isset( $field['default'] ) ) {
					$cleaned[ $field_name ] = $field['default'];
				}
			}

			return $cleaned;
		}

		/**
		 * Add additional element after print the field.
		 *
		 * @param array $field The field.
		 *
		 */
		public static function add_yith_ui_calendar_icon( $field ) {

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			
			if ( $screen && 'product' === $screen->id && 'expiration_date' === $field['id'] ) {
				switch ( $field['type'] ) {
					case 'datepicker':
						echo '<span class="yith-icon yith-icon-calendar yith-icon--right-overlay"></span>';
						break;
					default:
						break;
				}
			}
		}
	}
}
