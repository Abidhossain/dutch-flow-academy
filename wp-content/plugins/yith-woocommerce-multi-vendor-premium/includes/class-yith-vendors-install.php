<?php
/**
 * YITH Vendors Install. Perform actions on install plugin
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Install' ) ) {
	/**
	 * YITH Vendors Install class
	 */
	class YITH_Vendors_Install {

		/**
		 * Commission table name
		 *
		 * @since 4.0.0
		 * @const string
		 */
		const COMMISSIONS_TABLE = 'yith_vendors_commissions';

		/**
		 * Commission notes table name
		 *
		 * @since 4.0.0
		 * @const string
		 */
		const COMMISSIONS_NOTES_TABLE = 'yith_vendors_commissions_notes';

		/**
		 * Payments table name
		 *
		 * @const string
		 */
		const PAYMENTS_TABLE = 'yith_vendors_payments';

		/**
		 * Payments relationship table name
		 *
		 * @const string
		 */
		const PAYMENTS_RELATIONSHIP_TABLE = 'yith_vendors_payments_relationship';

		/**
		 * Updates and callbacks that need to be run per version.
		 *
		 * @var array
		 */
		private static $updates = array(
			'4.0.0' => array(
				'update_plugin_options_400',
				'schedule_update_400',
			),
			'5.0.0' => array(
				'update_vendors_status_meta',
			),
		);

		/**
		 * Install plugin process
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function install() {

			// Set up global cache class.
			$GLOBALS['yith_wcmv_cache'] = new YITH_Vendors_Cache();

			// Define class aliases.
			self::define_class_aliases();

			self::define_tables();
			add_action( 'switch_blog', array( __CLASS__, 'define_tables' ), 0 );

			YITH_Vendors_Taxonomy::init();
			if ( apply_filters( 'yith_wcmv_enable_rest_api', true ) ) {
				YITH_Vendors_REST_Install::init();
			}

			// Init hooks.
			add_action( 'yith_wcmv_update_commissions_table_400', array( __CLASS__, 'update_commissions_table_400' ) );

			// Register plugin to licence/update system.
			add_action( 'wp_loaded', array( __CLASS__, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( __CLASS__, 'register_plugin_for_updates' ), 99 );

			if ( self::do_activation() ) {
				// Make sure activate is triggered on init 10 or after to make sure taxonomy and post types are correctly registered.
				if ( did_action( 'init' ) ) {
					self::activate();
				} else {
					add_action( 'init', array( __CLASS__, 'activate' ) );
				}
			}

			do_action( 'yith_wcmv_after_installation_process' );
		}

		/**
		 * Must execute activation process?
		 * Conditions:
		 *  - current version installed is older than current one;
		 *  - forced by query string;
		 *  - register_activation_hook triggered.
		 *
		 * @since  4.0.0
		 * @return boolean
		 */
		protected static function do_activation() {
			return version_compare( self::get_installed_version(), YITH_WPV_VERSION, '<' ) || ! empty( $_GET['yith_wcmv_force_activation_process'] ) || 'yes' === get_option( 'yith_wcmv_do_activation_process', 'no' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Load the plugin fw
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function load_plugin_framework() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @since    2.0.0
		 * @return void
		 */
		public static function register_plugin_for_activation() {
			if ( function_exists( 'YIT_Plugin_Licence' ) ) {
				YIT_Plugin_Licence()->register( YITH_WPV_INIT, YITH_WPV_SECRET_KEY, YITH_WPV_SLUG );
			}
		}

		/**
		 * Register plugins for update tab
		 *
		 * @since    2.0.0
		 * @return void
		 */
		public static function register_plugin_for_updates() {
			if ( function_exists( 'YIT_Upgrade' ) ) {
				YIT_Upgrade()->register( YITH_WPV_SLUG, YITH_WPV_INIT );
			}
		}

		/**
		 * Get current installed plugin version.
		 * If it's first installation get the current version to avoid processing version migration actions.
		 *
		 * @since  4.0.0
		 * @return string
		 */
		protected static function get_installed_version() {
			return get_option( 'yith_wcmv_version', YITH_WPV_VERSION );
		}

		/**
		 * Define plugin tables aliases
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function define_tables() {
			global $wpdb;

			$wpdb->commissions       = $wpdb->prefix . self::COMMISSIONS_TABLE;
			$wpdb->tables[]          = self::COMMISSIONS_TABLE;
			$wpdb->commissions_notes = $wpdb->prefix . self::COMMISSIONS_NOTES_TABLE;
			$wpdb->tables[]          = self::COMMISSIONS_NOTES_TABLE;

			$wpdb->payments              = $wpdb->prefix . self::PAYMENTS_TABLE;
			$wpdb->tables[]              = self::PAYMENTS_TABLE;
			$wpdb->payments_relationship = $wpdb->prefix . self::PAYMENTS_RELATIONSHIP_TABLE;
			$wpdb->tables[]              = self::PAYMENTS_RELATIONSHIP_TABLE;
		}

		/**
		 * Define plugin class aliases for backward compatibility
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected static function define_class_aliases() {
			class_alias( 'YITH_Vendors_Orders', 'YITH_Orders' );
			class_alias( 'YITH_Vendors_Orders', 'YITH_Orders_Premium' );
			class_alias( 'YITH_Vendors_Orders', 'YITH_Vendors_Orders_Premium' );
			class_alias( 'YITH_Vendors_Commission', 'YITH_Commission' );
			class_alias( 'YITH_Vendors_Commissions', 'YITH_Commissions' );
			class_alias( 'YITH_Vendors_Shortcodes', 'YITH_Multi_Vendor_Shortcodes' );
			class_alias( 'YITH_Vendors_Account_Endpoints', 'YITH_Vendor_Endpoints' );
			class_alias( 'YITH_Vendors_Commissions_List_Table', 'YITH_Commissions_List_Table' );
			class_alias( 'YITH_Vendors_Theme_Hello_Elementor', 'YITH_Vendors_Hello_Elementor' );
			class_alias( 'YITH_Vendors_Modules', 'YITH_Vendors_Modules_Handler' );
			class_alias( 'YITH_Vendors_Frontend', 'YITH_Vendors_Frontend_Premium' );
			class_alias( 'YITH_Vendors_Admin', 'YITH_Vendors_Admin_Premium' );
			class_alias( 'YITH_Vendors_Admin_Vendor_Dashboard', 'YITH_Vendors_Admin_Vendor_Dashboard_Premium' );
			class_alias( 'YITH_Vendors_Commissions_List_Table', 'YITH_Vendors_Commissions_List_Table_Premium' );
			class_alias( 'YITH_Vendors', 'YITH_Vendors_Premium' );
			class_alias( 'YITH_Vendors_List_Widget', 'YITH_Woocommerce_Vendors_Widget' );
			class_alias( 'YITH_Vendors_Quick_Info_Widget', 'YITH_Vendor_Quick_Info_Widget' );
			class_alias( 'YITH_Vendors_Store_Location_Widget', 'YITH_Vendor_Store_Location_Widget' );

			// Module aliases.
			if ( class_exists( 'YITH_Vendors_Request_Quote' ) ) {
				class_alias( 'YITH_Vendors_Request_Quote', 'YITH_Vendor_Request_Quote' );
			}
		}

		/**
		 * Activation plugin process
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function activate() {

			// Make sure plugin FW is loaded.
			self::load_plugin_framework();

			// Create tables.
			self::create_commissions_table();
			self::create_transaction_table();

			// Create pages.
			self::create_pages();
			// Create placeholder attachment.
			self::create_placeholder_images();

			// Custom user role. Must be after the taxonomy is registered.
			YITH_Vendors_Capabilities::add_role();
			// Add vendor role to vendor owner and admins.
			YITH_Vendors_Capabilities::set_role_to_vendors();

			// Update callbacks.
			foreach ( self::$updates as $version => $callbacks ) {
				if ( version_compare( self::get_installed_version(), $version, '<' ) ) {
					foreach ( $callbacks as $callback ) {
						self::$callback();
					}
				}
			}

			update_option( 'yith_wcmv_version', YITH_WPV_VERSION );
			delete_option( 'yith_wcmv_do_activation_process' );
			// Regenerate permalink.
			flush_rewrite_rules();

			do_action( 'yith_wcmv_plugin_activation_process_completed' );
		}

		/**
		 * Set activation flag
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function set_activation_flag() {
			update_option( 'yith_wcmv_do_activation_process', 'yes' );
		}

		/**
		 * Deactivation plugin process
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function deactivate() {
			YITH_Vendors_Capabilities::remove_role_to_vendors();
		}

		/**
		 * Uninstall plugin process
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function uninstall() {
		}

		/**
		 * Create commissions table
		 *
		 * @since  1.0
		 * @return void
		 * @see    dbDelta()
		 */
		public static function create_commissions_table() {

			global $wpdb;

			// Check if dbDelta() exists.
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			$charset_collate = $wpdb->get_charset_collate();
			$table_name      = $wpdb->prefix . self::COMMISSIONS_TABLE;
			$create          = "CREATE TABLE $table_name (
                        ID bigint(20) NOT NULL AUTO_INCREMENT,
                        order_id bigint(20) NOT NULL,
                        user_id bigint(20) NOT NULL,
                        vendor_id bigint(20) NOT NULL,
                        product_id bigint(20) NOT NULL,
                        line_item_id bigint(20) NOT NULL,
                        line_total double(15,4) NOT NULL,
                        rate decimal(5,4) NOT NULL,
                        amount double(15,4) NOT NULL,
                        amount_refunded double(15,4) NOT NULL DEFAULT '0',
                        status varchar(100) NOT NULL,
                        type VARCHAR(30) NOT NULL DEFAULT 'product',
                        created_date DATETIME NOT NULL DEFAULT '000-00-00 00:00:00',
                        created_date_gmt DATETIME NOT NULL DEFAULT '000-00-00 00:00:00',
                        last_edit DATETIME NOT NULL DEFAULT '000-00-00 00:00:00',
                        last_edit_gmt DATETIME NOT NULL DEFAULT '000-00-00 00:00:00',
                        PRIMARY KEY (ID)
                        ) $charset_collate;";
			dbDelta( $create );

			$table_name = $wpdb->prefix . self::COMMISSIONS_NOTES_TABLE;
			$create     = "CREATE TABLE $table_name (
                        ID bigint(20) NOT NULL AUTO_INCREMENT,
                        commission_id bigint(20) NOT NULL,
                        note_date DATETIME NOT NULL DEFAULT '000-00-00 00:00:00',
                        description TEXT,
                        PRIMARY KEY (ID)
                        ) $charset_collate;";
			dbDelta( $create );
		}

		/**
		 * Create the tables
		 *
		 * @since  1.6.0
		 * @return void
		 * @see    dbDelta()
		 */
		public static function create_transaction_table() {
			global $wpdb;

			// Check if dbDelta() exists.
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			$charset_collate = $wpdb->get_charset_collate();
			$table_name      = $wpdb->prefix . self::PAYMENTS_TABLE;
			$create          = "CREATE TABLE $table_name (
                        ID bigint(20) NOT NULL AUTO_INCREMENT,
                        vendor_id bigint(20) NOT NULL,
                        user_id bigint(20) NOT NULL,
                        amount double(15,4) NOT NULL,
                        currency varchar(10) NOT NULL,
                        status varchar(100) NOT NULL,
                        note text NOT NULL,
                        payment_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        payment_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                        gateway_id varchar(100),
                        PRIMARY KEY (ID)
                        ) $charset_collate;";
			dbDelta( $create );

			$table_name = $wpdb->prefix . self::PAYMENTS_RELATIONSHIP_TABLE;
			$create     = "CREATE TABLE $table_name (
                        payment_id bigint(20) NOT NULL,
                        commission_id bigint(20) NOT NULL,
                        PRIMARY KEY ( `payment_id`, `commission_id`)
                        ) $charset_collate;";
			dbDelta( $create );
		}

		/**
		 * Create plugin pages.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function create_pages() {

			if ( ! function_exists( 'wc_create_page' ) ) {
				include_once dirname( WC_PLUGIN_FILE ) . '/includes/admin/wc-admin-functions.php';
			}

			// Became a vendor page.
			wc_create_page( 'become-a-vendor', 'yith_wpv_become_a_vendor_page_id', __( 'Become a Vendor', 'yith-woocommerce-product-vendors' ), '[yith_wcmv_become_a_vendor]' );
			// Terms and Conditions pages.
			wc_create_page( 'vendors-terms-and-conditions', 'yith_wpv_terms_and_conditions_page_id', __( 'Terms & Conditions for Vendors', 'yith-woocommerce-product-vendors' ) );
			wc_create_page( 'vendors-privacy-policy', 'yith_wpv_privacy_page', __( 'Privacy Policy for Vendors', 'yith-woocommerce-product-vendors' ) );
		}

		/**
		 * Create a placeholder image in the media library.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		private static function create_placeholder_images() {

			if ( ! get_option( 'yith_wpv_header_default_image', 0 ) ) {
				$image_id = self::upload_placeholder_images( 'vendor-header-placeholder.jpg' );
				update_option( 'yith_wpv_header_default_image', $image_id );
				update_option( 'yith_wpv_header_default_image_default', $image_id );
			}

			if ( ! get_option( 'yith_wpv_avatar_default_image', 0 ) ) {
				$image_id = self::upload_placeholder_images( 'vendor-avatar-placeholder.png' );
				update_option( 'yith_wpv_avatar_default_image', $image_id );
				update_option( 'yith_wpv_avatar_default_image_default', $image_id );
			}
		}

		/**
		 * Upload placeholder image
		 *
		 * @since 4.0.0
		 * @param string $image The image to upload.
		 * @return integer The attachment ID.
		 */
		private static function upload_placeholder_images( $image ) {

			// extract extension.
			if ( ! function_exists( 'wp_get_current_user' ) ) {
				include ABSPATH . 'wp-includes/pluggable.php';
			}

			$upload_dir = wp_upload_dir();
			$source     = YITH_WPV_ASSETS_URL . "images/{$image}";
			$filename   = $upload_dir['basedir'] . "/yith-wcmv-uploads/{$image}";

			// Create upload dir if missing.
			if ( wp_mkdir_p( $upload_dir['basedir'] . '/yith-wcmv-uploads' ) && ! file_exists( $filename ) ) {
				copy( $source, $filename ); // @codingStandardsIgnoreLine.
			}

			if ( ! file_exists( $filename ) ) {
				return 0;
			}

			$filetype   = wp_check_filetype( basename( $filename ), null );
			$attachment = array(
				'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$attach_id = wp_insert_attachment( $attachment, $filename );
			if ( is_wp_error( $attach_id ) ) {
				return 0;
			}

			// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			return $attach_id;
		}

		/***********************
		 * UPDATE TO 4.0.0
		 ***********************/

		/**
		 * Update option
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected static function update_plugin_options_400() {

			// Create an option for manage legacy admin panels.
			add_option( 'yith_wcmv_legacy_admin_panels', array(), '', 'no' );

			// Create a backup array of options before process.
			$backup = array();
			// Handle special option background + opacity.
			$background = get_option( 'yith_skin_background_color', null );
			if ( ! is_null( $background ) ) {
				// Convert to rgb if needed.
				$rgb              = wc_rgb_from_hex( $background );
				$opacity          = get_option( 'yith_skin_background_color_opacity', 1 );
				$background_value = sprintf( 'rgba( %s, %s, %s, %s )', $rgb['R'], $rgb['G'], $rgb['B'], $opacity );
				$text_color       = get_option( 'yith_skin_font_color', '#000000' );

				update_option(
					'yith_wpv_header_color',
					array(
						'text'       => $text_color,
						'background' => $background_value,
					)
				);
			}

			// Handle checkbox group options.
			$checkbox_groups = array(
				'yith_wpv_vendor_data_to_delete' => array(
					'profile'             => 'yith_vendor_remove_vendor_profile_data',
					'commissions_user_id' => 'yith_vendor_remove_user_id_in_commissions',
					'media'               => 'yith_vendor_delete_vendor_media_profile_data',
				),
				'yith_wpv_vendor_info_to_show'   => array(
					'vat-ssn'     => 'yith_wpv_vendor_show_vendor_vat',
					'description' => 'yith_wpv_vendor_store_description',
					'sales'       => 'yith_wpv_vendor_total_sales',
					'website'     => 'yith_wpv_vendor_show_vendor_website',
					'rating'      => 'yith_wpv_vendor_show_average_ratings',
				),
			);
			foreach ( $checkbox_groups as $option_key => $option ) {
				if ( ! is_null( get_option( $option_key, null ) ) ) { // If option already exists, skip!
					continue;
				}

				$new_value = array();
				foreach ( $option as $key => $single_option ) {
					$old_value = get_option( $single_option, null );
					if ( is_null( $old_value ) ) {
						continue;
					}

					if ( 'no' !== $old_value ) {
						$new_value[] = $key;
					}

					$backup[ $single_option ] = $old_value; // Backup!
				}

				if ( ! empty( $new_value ) ) {
					update_option( $option_key, $new_value );
				}
			}

			// Handle checkbox logic change.
			$checkbox_logic = array(
				'yith_wpv_vendors_option_order_show_customer'         => 'yith_wpv_vendors_option_order_hide_customer',
				'yith_wpv_vendors_option_order_show_payment'          => 'yith_wpv_vendors_option_order_hide_payment',
				'yith_wpv_vendors_option_order_show_billing_shipping' => 'yith_wpv_vendors_option_order_hide_shipping_billing',
				'yith_wpv_vendors_option_order_edit_custom_fields'    => 'yith_wpv_vendors_option_order_prevent_edit_custom_fields',
				'yith_wpv_vendors_option_order_resend_email'          => 'yith_wpv_vendors_option_order_prevent_resend_email',
				'yith_wpv_hide_vendors_products_on_vacation'          => 'yith_wpv_show_vendors_products_on_vacation',
			);
			foreach ( $checkbox_logic as $option_key => $option ) {
				if ( ! is_null( get_option( $option_key, null ) ) ) { // If option already exists, skip!
					continue;
				}

				$old_value = get_option( $option, null );
				if ( ! is_null( $old_value ) ) {
					$new_value         = yith_plugin_fw_is_true( $old_value ) ? 'no' : 'yes';
					$backup[ $option ] = $old_value; // Backup!
				}

				if ( ! empty( $new_value ) ) {
					update_option( $option_key, $new_value );
				}
			}

			// Handle generic options.
			$options = array(
				'yith_wpv_vat_label'                       => 'yith_vat_label',
				'yith_wpv_store_header_style'              => 'yith_vendors_skin_header',
				'yith_wpv_show_vendor_logo'                => 'yith_vendors_show_gravatar_image',
				'yith_wpv_vendors_option_staff_management' => 'yith_wpv_vendors_ahop_admins_cap',
				'yith_wpv_vendor_label_text'               => array(
					'singular' => 'yith_wpv_vendor_label_singular_text',
					'plural'   => 'yith_wpv_vendor_label_plural_text',
				),
				'yith_wpv_vendor_color_name'               => array(
					'normal' => 'yith_vendors_color_name',
					'hover'  => 'yith_vendors_color_name_hover',
				),
				'yith_wpv_header_image_size'               => array(
					'width'  => 'yith_vendors_header_image_width',
					'height' => 'yith_vendors_header_image_height',
				),
			);
			foreach ( $options as $option_key => $option ) {

				if ( ! is_null( get_option( $option_key, null ) ) ) { // If option already exists, skip!
					continue;
				}

				$new_value = null;

				if ( is_array( $option ) ) {
					$old_value = array();
					foreach ( $option as $key => $single_option ) {
						$single_value = get_option( $single_option, null );
						if ( is_null( $single_value ) ) {
							continue;
						}

						$old_value[ $key ]        = $single_value;
						$backup[ $single_option ] = $single_value; // Backup!
					}

					if ( ! empty( $old_value ) ) {
						$new_value = $old_value;
					}
				} else {
					$old_value = get_option( $option, null );

					if ( ! is_null( $old_value ) ) {
						$new_value         = $old_value;
						$backup[ $option ] = $old_value; // Backup!
					}
				}

				if ( is_null( $new_value ) ) {
					continue;
				}
				// Update new option.
				update_option( $option_key, $new_value );
			}

			! empty( $backup ) && update_option( 'yith_wcmv_backup_options_' . str_replace( '.', '_', YITH_WPV_VERSION ), $backup );
		}

		/**
		 * Delete old options to clean up the db.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected static function delete_plugin_options_400() {
			$options_to_delete = array(
				'yith_product_vendors_commissions_table_created',
				'yith_product_vendors_payments_table_created',
				'yith_wpv_vendors_option_adaptive_payment',
				'yith_wcmv_setup',
				'yith_skin_background_color',
				'yith_skin_font_color',
				'yith_vendors_header_image_width',
				'yith_vendors_header_image_height',
				'yith_vendors_color_name',
				'yith_vendors_color_name_hover',
				'yith_wpv_vendor_label_singular_text',
				'yith_wpv_vendor_label_plural_text',
				'yith_vat_label',
				'yith_vendors_skin_header',
				'yith_vendors_show_gravatar_image',
				'yith_vendor_remove_vendor_profile_data',
				'yith_vendor_remove_user_id_in_commissions',
				'yith_vendor_delete_vendor_media_profile_data',
				'yith_wpv_vendor_show_vendor_vat',
				'yith_wpv_vendor_store_description',
				'yith_wpv_vendor_total_sales',
				'yith_wpv_vendor_show_vendor_website',
				'yith_wpv_vendor_show_average_ratings',
				'yith_wpv_vendors_option_order_hide_customer',
				'yith_wpv_vendors_option_order_hide_payment',
				'yith_wpv_vendors_option_order_hide_shipping_billing',
				'yith_wpv_vendors_option_order_prevent_edit_custom_fields',
				'yith_wpv_vendors_option_order_prevent_resend_email',
				'yith_wpv_show_vendor_gravatar',
			);

			foreach ( $options_to_delete as $option ) {
				delete_option( $option );
			}
		}

		/**
		 * Update commissions table
		 *
		 * @since  4.0.0
		 * @param integer $offset (Optional) Offset for the query. Default is 0.
		 * @return void
		 */
		protected static function schedule_update_400( $offset = 0 ) {
			WC()->queue()->schedule_single( time(), 'yith_wcmv_update_commissions_table_400', array( 'offset' => $offset ) );
		}

		/**
		 * Update commissions table
		 *
		 * @since  4.0.0
		 * @param integer $offset (Optional) Offset for the query. Default is 0.
		 * @return void
		 */
		public static function update_commissions_table_400( $offset = 0 ) {

			// An array of commissions.
			$commissions = YITH_Vendors_Commissions_Factory::query(
				array(
					'fields' => 'all',
					'number' => 100,
					'offset' => $offset,
				)
			);

			if ( empty( $commissions ) ) {
				return;
			}

			foreach ( $commissions as $commission ) {
				/**
				 * Commission object
				 *
				 * @var $commission YITH_Vendors_Commission
				 */
				$order = $commission->get_order();
				$item  = $commission->get_item();
				if ( empty( $item ) || empty( $order ) || $commission->get_product_id() ) { // Avoid process a commission twice.
					continue;
				}

				$date = $order->get_date_created();
				if ( $date instanceof WC_DateTime ) {
					$date = $date->getTimestamp();
				}

				$commission->set_created_date( $date );
				$commission->set_created_date_gmt( $date );

				// Calculate line total.
				if ( 'shipping' === $commission->get_type() ) {
					$line_total = $order->get_line_total( $item, true, false );
				} else {
					$tax_management = $item->get_meta( '_commission_included_tax' );
					$include_coupon = $item->get_meta( '_commission_included_coupon' );
					if ( empty( $tax_management ) || empty( $include_coupon ) ) {
						$line_total = YITH_Vendors()->commissions->get_line_item_total( $item, $order );
					} else {
						$include_tax     = ! ( 'website' === $tax_management || 'vendor' === $tax_management );
						$get_item_amount = $include_coupon ? 'get_line_total' : 'get_line_subtotal';
						// Retrieve the real amount of single item, with right discounts applied and without taxes.
						$line_total = $order->$get_item_amount( $item, $include_tax, false );
					}
				}
				// Set line total.
				$commission->set_line_total( $line_total );
				// Set product ID.
				$variation_id = is_callable( array( $item, 'get_variation_id' ) ) ? $item->get_variation_id() : 0;
				$product_id   = is_callable( array( $item, 'get_product_id' ) ) ? $item->get_product_id() : 0;
				$commission->set_product_id( $variation_id ? $variation_id : $product_id );

				$commission->save();
			}

			$offset += 100;
			WC()->queue()->schedule_single( time(), 'yith_wcmv_update_commissions_table_400', array( 'offset' => $offset ) );
		}

		/***********************
		 * UPDATE TO 5.0.0
		 ***********************/

		/**
		 * Update vendor meta status
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected static function update_vendors_status_meta() {
			$vendors = yith_wcmv_get_vendors( array( 'number' => -1 ) );

			foreach ( $vendors as $vendor ) {
				if ( 'yes' === $vendor->get_meta( 'pending' ) ) {
					$vendor->set_status( 'pending' );
				} elseif ( 'yes' === $vendor->get_meta( 'enable_selling' ) ) {
					$vendor->set_status( 'enabled' );
				}

				$vendor->set_meta( 'pending', null );
				$vendor->set_meta( 'enable_selling', null );
				$vendor->save();
			}
		}
	}
}
