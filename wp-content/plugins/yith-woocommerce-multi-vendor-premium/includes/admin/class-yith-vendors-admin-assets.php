<?php
/**
 * YITH Vendors Admin Assets class. Handle plugin admin assets.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Assets' ) ) {
	/**
	 * YITH Vendors Admin Assets class
	 */
	class YITH_Vendors_Admin_Assets {

		/**
		 * An array of registered javascript.
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected static $js = array();

		/**
		 * An array of registered javascript.
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected static $css = array();

		/**
		 * Init class
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function init() {
			self::load();
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_assets' ), 5 );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 10 );
			add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_assets' ), 10 );

			// Script Translations.
			add_filter( 'pre_load_script_translations', array( __CLASS__, 'script_translations' ), 10, 4 );
		}

		/**
		 * Populate assets array
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected static function load() {

			self::$js = array(
				'admin'             => array(
					'filename' => 'admin-common.js',
					'deps'     => array( 'wc-enhanced-select', 'wp-util' ),
					'callback' => array( 'yith_wcmv_is_plugin_panel', '' ),
					'localize' => array(
						'yith_vendors',
						array(
							'forceSkipMessage'   => __( 'Are you sure? If you click "Confirm" the skip review option will change for each vendor.', 'yith-woocommerce-product-vendors' ),
							// translators: %gateway_name% is a placeholder for the gateway name.
							'warnPay'            => __( 'If you continue, the commission will be paid automatically to the vendor via %gateway_name%. Do you want to continue?', 'yith-woocommerce-product-vendors' ),
							'reviews'            => get_option( 'yith_wpv_vendors_option_review_management', 'no' ),
							'requiredFieldError' => _x( 'This is a required field', '[Admin]Form generic field error', 'yith-woocommerce-product-vendors' ),
							'emailFieldError'    => _x( 'Please type a valid email address', '[Admin]Form email field error', 'yith-woocommerce-product-vendors' ),

						),
					),
				),
				'vendors-admin'     => array(
					'filename' => 'vendors.js',
					'callback' => array( 'yith_wcmv_is_plugin_panel', 'vendors' ),
					'localize' => array(
						'yith_wcmv_vendors',
						array(
							'uploadFrameTitle'             => esc_html__( 'Choose an image', 'yith-woocommerce-product-vendors' ),
							'uploadFrameButtonText'        => esc_html__( 'Use image', 'yith-woocommerce-product-vendors' ),
							'countries'                    => wp_json_encode( WC()->countries->get_states() ),
							'i18nSelectStateText'          => esc_attr__( 'Select an option&hellip;', 'yith-woocommerce-product-vendors' ),
							'createModalDefault'           => array_filter(
								array_map(
									function ( $field ) {
										return $field['default'] ?? ''; },
									YITH_Vendors_Factory::get_fields( true )
								)
							),
							'registrationTableFieldsDefault' => YITH_Vendors_Registration_Form::get_admin_modal_fields_default(),
							'registrationTableResetTitle'  => _x( 'Restore default', '[Admin]Vendor registration fields modal reset title', 'yith-woocommerce-product-vendors' ),
							'registrationTableResetContent' => _x( 'All fields will be removed and replaced with the default form fields. Do you wish to continue?', '[Admin]Vendor registration fields modal reset content', 'yith-woocommerce-product-vendors' ),
							'registrationTableResetButton' => _x( 'Continue', '[Admin]Vendor registration fields modal reset button label', 'yith-woocommerce-product-vendors' ),
							'registrationDeleteFieldTitle' => _x( 'Remove field', '[Admin]Vendor registration delete field modal title', 'yith-woocommerce-product-vendors' ),
							'registrationDeleteFieldContent' => _x( 'This field will be removed from the form. Do you wish to continue?', '[Admin]Vendor registration delete field modal content', 'yith-woocommerce-product-vendors' ),
							'registrationDeleteFieldButton' => _x( 'Continue', '[Admin]Vendor registration delete field modal button label', 'yith-woocommerce-product-vendors' ),
							'registrationModalAddTitle'    => _x( 'Add field', '[Admin]Vendor registration modal add field title', 'yith-woocommerce-product-vendors' ),
							'registrationModalEditTitle'   => _x( 'Edit field', '[Admin]Vendor registration modal edit field title', 'yith-woocommerce-product-vendors' ),
							'rejectVendorModalTitle'       => _x( 'Reject vendor', '[Admin]Vendor reject modal title', 'yith-woocommerce-product-vendors' ),
							'createVendorButtonLabel'      => _x( 'Add vendor', '[Admin]Create vendor button label', 'yith-woocommerce-product-vendors' ),
						),
					),
				),
				'commissions-admin' => array(
					'filename' => 'commissions.js',
					'callback' => array( 'yith_wcmv_is_plugin_panel', 'commissions' ),
				),
				'dashboard-report'  => array(
					'filename'     => 'dashboard.js',
					'callback'     => array( 'yith_wcmv_is_plugin_panel', 'dashboard' ),
					'deps'         => array(
						'jquery-ui-datepicker',
						'selectWoo',
						'wp-mediaelement',
						// 'woocommerce_settings',
						'wp-api-fetch',
						'wp-components',
						'wp-element',
						'wp-hooks',
						'wp-i18n',
						'wp-data',
						'wp-url',
						'wc-components',
					),
					'localize'     => array(
						'yith_wcmv_dashboard',
						array(
							'baseUrl'           => yith_wcmv_get_admin_panel_url(
								array(
									'tab'     => 'vendors',
									'sub_tab' => 'vendors-list',
								)
							),
							'isVendorDashboard' => yith_wcmv_is_vendor_dashboard(),
							'isLegacyDashboard' => 1 === version_compare( '6.7', WC()->version ),
							'perPageSearch'     => 10,
							'namespace'         => YITH_WPV_REST_NAMESPACE,
						),
					),
					'translatable' => true,
				),
				'modules'           => array(
					'filename' => 'modules.js',
					'callback' => array( 'yith_wcmv_is_plugin_panel', 'modules' ),
				),
				'emails'            => array(
					'filename' => 'emails.js',
					'callback' => array( 'yith_wcmv_is_plugin_panel', 'emails' ),
				),
			);

			self::$css = array(
				'admin' => array(
					'filename' => 'admin.css',
					'callback' => array( 'yith_wcmv_is_plugin_panel', '' ),
					'deps'     => array( 'jquery-ui-style', 'woocommerce_admin_styles' ),
				),
			);
		}

		/**
		 * Get a list of registered JS to be enqueued
		 *
		 * @since  4.0.0
		 * @param string   $handle   Name of the script. Should be unique.
		 * @param string   $filename The script filename.
		 * @param string[] $deps     Optional. An array of registered script handles this script depends on. Default empty array.
		 * @param array    $localize Optional. An array of localized data to add with the script.
		 * @return void
		 */
		public static function add_js( $handle, $filename, $deps = array(), $localize = array() ) {
			self::$js[ $handle ] = array(
				'filename' => $filename,
				'deps'     => $deps,
				'localize' => $localize,
			);
		}

		/**
		 * Get a list of registered JS to be enqueued
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public static function get_js() {
			return apply_filters( 'yith_wcmv_get_admin_js', self::$js );
		}


		/**
		 * Get a list of registered CSS to be enqueued
		 *
		 * @since  4.0.0
		 * @param string   $handle   Name of the script. Should be unique.
		 * @param string   $filename The script filename.
		 * @param string[] $deps     Optional. An array of registered script handles this script depends on. Default empty array.
		 * @return void
		 */
		public static function add_css( $handle, $filename, $deps = array() ) {
			self::$css[ $handle ] = array(
				'filename' => $filename,
				'deps'     => $deps,
			);
		}

		/**
		 * Get a list of registered CSS to be enqueued
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public static function get_css() {
			return apply_filters( 'yith_wcmv_get_admin_css', self::$css );
		}

		/**
		 * Check assets callback
		 *
		 * @since  4.0.0
		 * @param array  $callback The callback data. [ callback, variables ].
		 * @param string $handle   The callback script handle.
		 * @return boolean
		 */
		protected static function do_callback( $callback, $handle ) {
			$response = false;
			if ( is_array( $callback ) ) {
				list( $function, $variables ) = $callback;
				$response                     = call_user_func( $function, $variables );
			}

			return apply_filters( 'yith_wcmv_admin_assets_callback_response', $response, $handle );
		}

		/**
		 * Register assets to be enqueued
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function register_assets() {
			foreach ( self::get_js() as $handle => $data ) {
				if ( empty( $data['filename'] ) ) {
					continue;
				}

				// Merge standard deps.
				$deps = array_unique( array_merge( ( isset( $data['deps'] ) ? $data['deps'] : array() ), array( 'jquery' ) ) );
				self::register_js( $handle, $data['filename'], $deps );

				if ( ! empty( $data['translatable'] ) && function_exists( 'wp_set_script_translations' ) ) {
					wp_set_script_translations( 'yith-wcmv-' . $handle, 'yith-woocommerce-product-vendors', YITH_WPV_PATH . 'languages' );
				}
			}

			foreach ( self::get_css() as $handle => $data ) {
				if ( empty( $data['filename'] ) ) {
					continue;
				}
				$deps = isset( $data['deps'] ) ? $data['deps'] : array();
				self::register_css( $handle, $data['filename'], $deps );
			}
		}

		/**
		 * Register a single js asset
		 *
		 * @since  4.0.0
		 * @param string   $handle   Name of the script. Should be unique.
		 * @param string   $filename The script filename.
		 * @param string[] $deps     Optional. An array of registered script handles this script depends on. Default empty array.
		 * @param bool     $footer   Optional. Whether to enqueue the script before </body> instead of in the <head>.  Default 'true'.
		 * @return void
		 */
		protected static function register_js( $handle, $filename, $deps = array(), $footer = true ) {
			wp_register_script( 'yith-wcmv-' . $handle, YITH_WPV_ASSETS_URL . 'js/admin/' . yit_load_js_file( $filename ), $deps, YITH_WPV_VERSION, $footer );
		}

		/**
		 * Register a single css asset
		 *
		 * @since  4.0.0
		 * @param string   $handle   Name of the stylesheet. Should be unique.
		 * @param string   $filename The name of the stylesheet.
		 * @param string[] $deps     Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
		 * @param string   $media    Optional. The media for which this stylesheet has been defined. Default 'all'.
		 * @return void
		 */
		protected static function register_css( $handle, $filename, $deps = array(), $media = 'all' ) {
			wp_register_style( 'yith-wcmv-' . $handle, YITH_WPV_ASSETS_URL . 'css/' . yit_load_css_file( $filename ), $deps, YITH_WPV_VERSION, $media );
		}

		/**
		 * Enqueue assets
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function enqueue_assets() {

			// Add deps.
			wp_enqueue_media();

			foreach ( self::get_js() as $handle => $data ) {
				// Check for conditions.
				if ( isset( $data['callback'] ) && ! self::do_callback( $data['callback'], $handle ) ) {
					continue;
				}

				$localize = isset( $data['localize'] ) ? $data['localize'] : array();
				self::enqueue_js( $handle, $localize );
			}

			foreach ( self::get_css() as $handle => $data ) {
				// Check for conditions.
				if ( isset( $data['callback'] ) && ! self::do_callback( $data['callback'], $handle ) ) {
					continue;
				}

				self::enqueue_css( $handle );
			}
		}

		/**
		 * Enqueue a single JS asset
		 *
		 * @since  4.0.0
		 * @param string $handle   Name of the script. Should be unique.
		 * @param array  $localize An array of localized data. [ key, data ].
		 * @return void
		 */
		protected static function enqueue_js( $handle, $localize = array() ) {
			wp_enqueue_script( 'yith-wcmv-' . $handle );
			if ( ! empty( $localize ) && is_array( $localize ) ) {
				list( $localize_key, $localize_data ) = $localize;
				$filter                               = str_replace( '-', '_', $handle );
				wp_localize_script( 'yith-wcmv-' . $handle, $localize_key, apply_filters( "yith_wcmv_{$filter}_localize_script_args", $localize_data ) );
			}
		}

		/**
		 * Enqueue a single CSS asset
		 *
		 * @since  4.0.0
		 * @param string $handle Name of the style. Should be unique.
		 * @return void
		 */
		protected static function enqueue_css( $handle ) {
			wp_enqueue_style( 'yith-wcmv-' . $handle );
		}

		/**
		 * Enqueue dedicated assets for gutenberg
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function enqueue_block_assets() {
			wp_enqueue_style( 'yith-wcmv-gutenberg', YITH_WPV_ASSETS_URL . 'css/' . yit_load_css_file( 'gutenberg.css' ), array(), YITH_WPV_VERSION, 'all' );
		}

		/**
		 * Create the json translation through the PHP file
		 * So it's possible using normal translations (with PO files) also for JS translations
		 *
		 * @since  4.0.0
		 * @param string|null $json_translations Json translation.
		 * @param string      $file              File.
		 * @param string      $handle            Handle.
		 * @param string      $domain            Domain.
		 * @return string|null
		 */
		public static function script_translations( $json_translations, $file, $handle, $domain ) {
			if ( 'yith-woocommerce-product-vendors' === $domain && 'yith-wcmv-dashboard-report' === $handle ) {
				$path = YITH_WPV_PATH . 'languages/' . $domain . '.php';
				if ( file_exists( $path ) ) {
					$translations = include $path;

					$json_translations = wp_json_encode(
						array(
							'domain'      => array( 'yith-wcmv-dashboard-report' ),
							'locale_data' => array(
								'messages' =>
									array(
										'' => array(
											'domain'       => array( 'yith-wcmv-dashboard-report' ),
											'lang'         => get_locale(),
											'plural-forms' => 'nplurals=2; plural=(n != 1);',
										),
									)
									+
									$translations,
							),
						)
					);

				}
			}

			return $json_translations;
		}
	}
}
