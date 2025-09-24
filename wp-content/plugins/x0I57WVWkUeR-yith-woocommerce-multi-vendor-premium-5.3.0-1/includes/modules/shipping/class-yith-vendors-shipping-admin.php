<?php
/**
 * YITH_Vendors_Shipping_Admin class
 *
 * @since      1.11.4
 * @author     YITH
 * @package YITH\MultiVendor
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Shipping_Admin' ) ) {
	/**
	 * YITH Vendors Shipping admin class.
	 */
	class YITH_Vendors_Shipping_Admin {

		/**
		 * Admin AJAX action to add a single shipping method
		 *
		 * @since 4.0.0
		 */
		const ADD_SHIPPING_METHOD = 'add_vendor_shipping_method';

		/**
		 * Admin AJAX action to edit a single shipping method
		 *
		 * @since 4.0.0
		 */
		const EDIT_SHIPPING_METHOD = 'edit_vendor_shipping_method';

		/**
		 * Admin AJAX action to remove a single shipping method
		 *
		 * @since 4.0.0
		 */
		const REMOVE_SHIPPING_METHOD = 'remove_vendor_shipping_method';

		/**
		 * Admin AJAX action to save a shipping zone
		 *
		 * @since 4.0.0
		 */
		const SAVE_SHIPPING_ZONE = 'save_vendor_shipping_zone';

		/**
		 * Admin AJAX action to remove a shipping zone
		 *
		 * @since 4.0.0
		 */
		const REMOVE_SHIPPING_ZONE = 'remove_vendor_shipping_zone';

		/**
		 * Admin AJAX action to order shipping zones
		 *
		 * @since 4.0.0
		 */
		const ORDER_SHIPPING_ZONES = 'order_vendor_shipping_zones';

		/**
		 * Array of shipping methods
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $shipping_methods_settings = array();

		/**
		 * Current panel vendor
		 *
		 * @since 4.0.0
		 * @var null|boolean|YITH_Vendor
		 */
		protected $vendor = null;

		/**
		 * Tab slug
		 *
		 * @var string
		 */
		protected $tab = 'shipping';

		/**
		 * Construct
		 *
		 * @since  1.9.17
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
			add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_id_for_shipping_tab' ) );
			add_filter( 'yith_wcmv_admin_vendor_dashboard_tabs', array( $this, 'add_vendor_tab' ), 10, 2 );
			// Vendor limited access hooks.
			add_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', array( $this, 'vendor_limited_access_hooks' ) );
			// Custom posted data.
			add_filter( 'yith_wcmv_get_posted_data_shipping-zones', array( $this, 'posted_zones_data_field' ), 10, 2 );
			// Output shipping zones settings tab.
			add_filter( 'yith_wcmv_vendor_dashboard_shipping_zones_tab', array( $this, 'output_zones_tab' ), 5, 2 );
			// Template parts.
			add_action( 'yith_wcmv_admin_shipping_zones_single', array( $this, 'print_single_shipping_zone' ), 10, 2 );
			add_action( 'yith_wcmv_admin_shipping_methods_single', array( $this, 'print_single_shipping_method' ), 10, 3 );

			// Add custom option to YITH > Multi Vendor > Store & Product Page > Product Page.
			add_filter( 'yith_wcmv_frontend_pages_product_options', array( $this, 'add_shipping_tab_options' ), 10, 1 );

			// Handle add new shipping method ajax request.
			add_action( 'yith_wcmv_admin_ajax_' . self::ADD_SHIPPING_METHOD, array( $this, 'handle_add_shipping_method' ) );
			add_action( 'yith_wcmv_admin_ajax_' . self::EDIT_SHIPPING_METHOD, array( $this, 'handle_edit_shipping_method' ) );
			add_action( 'yith_wcmv_admin_ajax_' . self::REMOVE_SHIPPING_METHOD, array( $this, 'handle_remove_shipping_method' ) );
			add_action( 'yith_wcmv_admin_ajax_' . self::SAVE_SHIPPING_ZONE, array( $this, 'handle_save_shipping_zone' ) );
			add_action( 'yith_wcmv_admin_ajax_' . self::REMOVE_SHIPPING_ZONE, array( $this, 'handle_remove_shipping_zone' ) );
			add_action( 'yith_wcmv_admin_ajax_' . self::ORDER_SHIPPING_ZONES, array( $this, 'handle_order_shipping_zones' ) );
		}

		/**
		 * Vendor limit access hooks
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function vendor_limited_access_hooks() {
			if ( function_exists( 'YITH_Delivery_Date_Shipping_Manager' ) && YITH_Delivery_Date_Shipping_Manager() instanceof YITH_Delivery_Date_Shipping_Manager ) {
				remove_action( 'admin_init', array( YITH_Delivery_Date_Shipping_Manager(), 'set_shipping_method' ), 99 );
			}
		}

		/**
		 * Check if current admin section is a shipping tab
		 *
		 * @since  4.0.0
		 * @param string $subtab (Optional) A sub-tab to additionally check.
		 * @return boolean
		 */
		public function is_shipping_tab( $subtab = '' ) {
			if ( yith_wcmv_is_plugin_panel() && isset( $_GET['tab'] ) && sanitize_text_field( wp_unslash( $_GET['tab'] ) ) === $this->tab ) { // phpcs:ignore WordPress.Security.NonceVerification
				// If sub-tab is not set in query string, set it to the first one.
				$current_subtab = isset( $_GET['sub_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['sub_tab'] ) ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification
				if ( empty( $subtab ) || $subtab === $current_subtab ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Get panel tab label
		 *
		 * @since  4.0.0
		 * @param string      $tabs   An array of dashboard tabs.
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return string
		 */
		public function add_vendor_tab( $tabs, $vendor ) {
			$tabs[ $this->tab ] = array(
				'title' => _x( 'Shipping', '[Admin]Shipping admin tab title', 'yith-woocommerce-product-vendors' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>',
			);

			return $tabs;
		}

		/**
		 * Handle AJAX add new shipping method
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Edit shipping method errors.
		 */
		public function handle_add_shipping_method() {
			// phpcs:disable WordPress.Security.NonceVerification
			try {
				$zone_id   = isset( $_REQUEST['zone_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['zone_id'] ) ) : null;
				$method_id = ! empty( $_REQUEST['method_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['method_id'] ) ) : null;
				$vendor    = yith_wcmv_get_vendor( 'current', 'user' );

				if ( is_null( $zone_id ) || is_null( $method_id ) || ! $vendor || ! $vendor->is_valid() ) {
					throw new Exception( 'Empty shipping method to add' );
				}

				$method = $this->get_shipping_methods_default( $method_id );
				// Create a unique method ID.
				$zones              = $vendor->get_meta( 'zone_data' );
				$vendor_methods_key = ( ! empty( $zones ) && ! is_null( $zone_id ) && ! empty( $zones[ $zone_id ]['zone_shipping_methods'] ) ) ? array_keys( $zones[ $zone_id ]['zone_shipping_methods'] ) : array();
				$method_id          = $this->get_unique_key( $vendor_methods_key );

				if ( isset( $zones[ $zone_id ] ) ) {
					$zones[ $zone_id ]['zone_shipping_methods'][ $method_id ] = $method;
					$vendor->set_meta( 'zone_data', $zones );
					$vendor->save();
				}

				ob_start();
				$this->print_single_shipping_method( $zone_id, $method_id, $method );
				$html = ob_get_clean();

				wp_send_json_success(
					array(
						'zone_id'   => $zone_id,
						'method_id' => $method_id,
						'html'      => $html,
					)
				);

			} catch ( Exception $e ) {
				wp_send_json_error();
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle AJAX edit shipping method
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Edit shipping method errors.
		 */
		public function handle_edit_shipping_method() {
			// phpcs:disable WordPress.Security.NonceVerification
			try {
				// Check isset and is_numeric to be compliant with old system using numeric keys.
				$zone_id     = isset( $_REQUEST['zone_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['zone_id'] ) ) : null;
				$method_id   = isset( $_REQUEST['method_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['method_id'] ) ) : null;
				$method_data = isset( $_REQUEST['method_data'] ) ? wp_unslash( $_REQUEST['method_data'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

				if ( is_null( $zone_id ) || is_null( $method_id ) ) {
					throw new Exception( 'Empty shipping method ID to edit' );
				}

				if ( empty( $method_data ) || empty( $method_data['type_id'] ) ) {
					throw new Exception( 'Empty data zone to save' );
				}

				$instance_id = $method_data['type_id'];
				// Validate data.
				$method = $this->validate_shipping_method_data( $instance_id, $method_data );
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( $vendor && $vendor->is_valid() ) {
					$zones = $vendor->get_meta( 'zone_data' );
					// If zone ID exists remove and update vendor meta.
					if ( ! empty( $zones ) && isset( $zones[ $zone_id ] ) ) {
						$zones[ $zone_id ]['zone_shipping_methods'][ $method_id ] = $method;
						$vendor->set_meta( 'zone_data', $zones );
						$vendor->save();
					}
				}

				ob_start();
				$this->print_single_shipping_method( $zone_id, $method_id, $method );
				$html = ob_get_clean();

				wp_send_json_success(
					array(
						'zone_id'   => $zone_id,
						'method_id' => $method_id,
						'html'      => $html,
					)
				);
			} catch ( Exception $e ) {
				wp_send_json_error();
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle AJAX remove shipping method
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Delete shipping method errors.
		 */
		public function handle_remove_shipping_method() {
			try {
				// Check isset and is_numeric to be compliant with old system using numeric keys.
				$zone_id   = isset( $_REQUEST['zone_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['zone_id'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$method_id = isset( $_REQUEST['method_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['method_id'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				if ( is_null( $zone_id ) || is_null( $method_id ) ) {
					throw new Exception( 'Empty shipping method ID to delete' );
				}

				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( $vendor && $vendor->is_valid() ) {
					$zones = $vendor->get_meta( 'zone_data' );
					// If zone ID exists remove and update vendor meta.
					if ( ! empty( $zones ) && isset( $zones[ $zone_id ] ) && isset( $zones[ $zone_id ]['zone_shipping_methods'][ $method_id ] ) ) {
						unset( $zones[ $zone_id ]['zone_shipping_methods'][ $method_id ] );
						$vendor->set_meta( 'zone_data', $zones );
						$vendor->save();
					}

					wp_send_json_success();
				}
			} catch ( Exception $e ) {
				wp_send_json_error();
			}
		}

		/**
		 * Handle AJAX remove shipping zone
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Save shipping zone errors.
		 */
		public function handle_save_shipping_zone() {
			try {
				// phpcs:disable WordPress.Security.NonceVerification
				// Check isset and is_numeric to be compliant with old system using numeric keys.
				// Assign a zone id if it is not passed.
				$zone_id   = isset( $_REQUEST['zone_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['zone_id'] ) ) : null;
				$zone_data = $this->posted_zones_data_field( array(), 'zone_data' );
				$default   = array(
					'zone_name'             => '',
					'zone_regions'          => '',
					'zone_post_code'        => '',
					'zone_shipping_methods' => array(),
				);

				if ( empty( $zone_data ) ) {
					throw new Exception( 'Empty data zone to save' );
				}

				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( $vendor && $vendor->is_valid() ) {
					$zones = $vendor->get_meta( 'zone_data' );
					if ( empty( $zones ) ) {
						$zones = array();
					}
					// This action handle one zone at time, get it and merge with default values.
					$zone_data = array_merge( $default, array_shift( $zone_data ) );
					$zone_ids  = array_keys( $zones );
					// If is a new zone, create a unique ID.
					// We cannot do a strict comparison to grant backward compatibility.
					if ( ! in_array( $zone_id, $zone_ids ) ) { // phpcs:ignore
						$zone_id = $this->get_unique_key( array_keys( $zones ) );
					}
					$zones[ $zone_id ] = $zone_data;

					$vendor->set_meta( 'zone_data', $zones );
					$vendor->save();

					ob_start();
					$this->print_single_shipping_zone( $zone_id, $zone_data );
					$html = ob_get_clean();

					wp_send_json_success(
						array(
							'zone_id' => $zone_id,
							'html'    => $html,
						)
					);
				}
			} catch ( Exception $e ) {
				wp_send_json_error();
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle AJAX remove shipping zone
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Delete shipping zone errors.
		 */
		public function handle_remove_shipping_zone() {
			try {
				// Check isset and is_numeric to be compliant with old system using numeric keys.
				$zone_id = isset( $_REQUEST['zone_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['zone_id'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				if ( is_null( $zone_id ) ) {
					throw new Exception( 'Empty zone ID to delete' );
				}

				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( $vendor && $vendor->is_valid() ) {
					$zones = $vendor->get_meta( 'zone_data' );
					// If zone ID exists remove and update vendor meta.
					if ( ! empty( $zones ) && isset( $zones[ $zone_id ] ) ) {
						unset( $zones[ $zone_id ] );
						$vendor->set_meta( 'zone_data', $zones );
						$vendor->save();
					}

					wp_send_json_success();
				}
			} catch ( Exception $e ) {
				wp_send_json_error();
			}
		}

		/**
		 * Get an unique ID for zone
		 *
		 * @since  4.0.0
		 * @param array $zone_keys The zone array keys.
		 * @return string
		 */
		protected function get_unique_key( $zone_keys ) {
			do {
				$key = uniqid();
			} while ( in_array( $key, $zone_keys, true ) );

			return $key;
		}

		/**
		 * Posted custom field zone data
		 *
		 * @since  4.0.0
		 * @param mixed  $value Current posted value.
		 * @param string $key   The field key.
		 * @return mixed
		 */
		public function posted_zones_data_field( $value, $key ) {
			if ( isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$value = array();
				$zones = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
				foreach ( $zones as $zone ) {
					$current = array();
					foreach ( $zone as $key => $v ) {
						if ( 'zone_post_code' === $key ) {
							$current[ $key ] = array_filter( array_map( 'strtoupper', array_map( 'wc_clean', explode( PHP_EOL, $v ) ) ) );
						} else {
							if ( 'zone_shipping_methods' === $key ) {
								$v = array_map(
									function ( $v ) {
										return json_decode( $v, true );
									},
									$v
								);
							}

							$current[ $key ] = wc_clean( $v );
						}
					}
					$value[] = $current;
				}
			}

			return $value;
		}

		/**
		 * Handle AJAX order shipping zones
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Errors ordering zones.
		 */
		public function handle_order_shipping_zones() {
			try {
				$order = ! empty( $_REQUEST['order'] ) ? wc_clean( wp_unslash( $_REQUEST['order'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
				if ( empty( $order ) ) {
					throw new Exception( 'Empty zones order to apply' );
				}

				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( $vendor && $vendor->is_valid() ) {
					$zones = $vendor->get_meta( 'zone_data' );
					if ( empty( $zones ) ) {
						$zones = array();
					}

					$ordered_zones = array();
					foreach ( $order as $key ) {
						if ( ! isset( $zones[ $key ] ) ) {
							continue;
						}
						$ordered_zones[ $key ] = $zones[ $key ];
					}

					$vendor->set_meta( 'zone_data', $ordered_zones );
					$vendor->save();

					wp_send_json_success();
				}
			} catch ( Exception $e ) {
				wp_send_json_error();
			}
		}

		/**
		 * Enqueue admin script for shipping module
		 *
		 * @since  1.9.17
		 * @return void
		 */
		public function enqueue_scripts() {

			wp_register_script(
				'yith-wcmv-admin-shipping',
				YITH_WPV_ASSETS_URL . 'js/admin/' . yit_load_js_file( 'shipping.js' ),
				array(
					'jquery',
					'jquery-blockui',
					'jquery-ui-sortable',
					'jquery-tiptip',
					'selectWoo',
				),
				YITH_WPV_VERSION,
				true
			);

			if ( $this->is_shipping_tab() ) {
				wp_enqueue_script( 'yith-wcmv-admin-shipping' );
				wp_enqueue_style( 'jquery-ui-style' );
				wp_enqueue_style( 'woocommerce_admin_styles' );

				wp_enqueue_script( 'woocommerce_admin' );

				$localized_data = array();
				if ( $this->is_shipping_tab( 'zones' ) ) {
					$localized_data = apply_filters(
						'yith_wcmv_shipping_module_localized_script',
						array(
							'shippingRegions'         => $this->get_available_shipping_regions(),
							'shippingMethodTitle'     => esc_html__( 'Add shipping method', 'yith-woocommerce-product-vendors' ),
							'shippingEditMethodTitle' => esc_html_x( '{{method_title}} settings', '[Admin]Shipping module modal title.', 'yith-woocommerce-product-vendors' ),
							'addShippingMethod'       => self::ADD_SHIPPING_METHOD,
							'editShippingMethod'      => self::EDIT_SHIPPING_METHOD,
							'removeShippingMethod'    => self::REMOVE_SHIPPING_METHOD,
							'saveZoneAction'          => self::SAVE_SHIPPING_ZONE,
							'removeZoneAction'        => self::REMOVE_SHIPPING_ZONE,
							'orderZonesAction'        => self::ORDER_SHIPPING_ZONES,
							'removeButtonLabel'       => esc_html_x( 'Delete', '[Admin]Delete shipping zone button label.', 'yith-woocommerce-product-vendors' ),
							'removeZoneTitle'         => esc_html_x( 'Delete shipping zone', '[Admin]Delete shipping zone modal title.', 'yith-woocommerce-product-vendors' ),
							'removeZoneMessage'       => esc_html_x( 'Are you sure you want to delete this shipping zone?', '[Admin]Delete shipping zone modal message.', 'yith-woocommerce-product-vendors' ),
							'removeMethodTitle'       => esc_html_x( 'Delete shipping method', '[Admin]Delete shipping zone modal title.', 'yith-woocommerce-product-vendors' ),
							'removeMethodMessage'     => esc_html_x( 'Are you sure you want to delete this shipping method?', '[Admin]Delete shipping zone modal message.', 'yith-woocommerce-product-vendors' ),
						)
					);
				}

				! empty( $localized_data ) && wp_localize_script( 'yith-wcmv-admin-shipping', 'yith_wcmv_shipping_general', $localized_data );
			}
		}

		/**
		 * Add shipping tab screen ID to allowed WooCommerce screen ID to enqueue WC admin script
		 *
		 * @since  4.0.0
		 * @param array $screen_ids An array of valid screen ID.
		 * @return array
		 */
		public function add_screen_id_for_shipping_tab( $screen_ids ) {
			if ( $this->is_shipping_tab() ) {
				$screen_ids[] = 'toplevel_page_' . YITH_Vendors_Admin::PANEL_PAGE;
			}
			return $screen_ids;
		}

		/**
		 * Get shipping methods default data
		 *
		 * @since  4.0.0
		 * @param string $method_id (Optional) The method id to return. Default all.
		 * @return array
		 */
		protected function get_shipping_methods_default( $method_id = '' ) {
			$this->set_shipping_methods_settings();
			if ( ! isset( $this->shipping_methods_settings['default'] ) ) {
				return array();
			}
			if ( $method_id ) {
				return isset( $this->shipping_methods_settings['default'][ $method_id ] ) ? $this->shipping_methods_settings['default'][ $method_id ] : array();
			}

			return $this->shipping_methods_settings['default'];
		}

		/**
		 * Get shipping methods fields
		 *
		 * @since  4.0.0
		 * @param string $method_id (Optional) The method id to return. Default all.
		 * @return array
		 */
		public function get_shipping_methods_fields( $method_id = '' ) {
			$this->set_shipping_methods_settings();

			if ( ! isset( $this->shipping_methods_settings['fields'] ) ) {
				return array();
			}
			if ( $method_id ) {
				return isset( $this->shipping_methods_settings['fields'][ $method_id ] ) ? $this->shipping_methods_settings['fields'][ $method_id ] : array();
			}

			return $this->shipping_methods_settings['fields'];
		}

		/**
		 * Set available shipping methods data. This is used on shipping zone table
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function set_shipping_methods_settings() {
			if ( ! empty( $this->shipping_methods_settings ) ) {
				return;
			}

			$wc_shipping      = WC_Shipping::instance();
			$shipping_classes = $wc_shipping->get_shipping_method_class_names();

			foreach ( $shipping_classes as $class_name ) {
				if ( ! class_exists( $class_name ) ) {
					continue;
				}

				$instance = new $class_name();
				if ( ! $instance->supports( 'shipping-zones' ) || ! $instance->supports( 'instance-settings-modal' ) ) {
					continue;
				}

				$fields  = array(
					array(
						'id'    => 'type_id',
						'name'  => 'method_data[type_id]',
						'type'  => 'hidden',
						'value' => $instance->id,
					),
				);
				$default = array( 'type_id' => $instance->id );
				foreach ( $instance->get_instance_form_fields() as $key => $field ) {
					$field['id'] = $instance->get_field_key( $key );
					// Map field key to backward compatibility.
					switch ( $key ) {
						case 'title':
						case 'tax_status':
						case 'cost':
						case 'requires':
							// Set name.
							$field_key = 'method_' . $key;
							break;
						default:
							$field_key = $key;
							break;
					}

					// Set name.
					$field['name'] = 'method_data[' . $field_key . ']';
					$field['desc'] = isset( $field['description'] ) ? $field['description'] : '';
					if ( isset( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
						$field['custom_attributes']['data-value'] = '{{data.' . $field_key . '}}';
					} else {
						$field['custom_attributes'] = array( 'data-value' => '{{data.' . $field_key . '}}' );
					}

					$fields[ $field_key ] = $field;
					// Set default value.
					$default[ $field_key ] = $instance->get_instance_option( $key );
				}

				$this->shipping_methods_settings['default'][ $instance->id ] = $default;
				$this->shipping_methods_settings['fields'][ $instance->id ]  = $fields;
			}
		}

		/**
		 * Validate shipping method posted data.
		 *
		 * @since  4.0.0
		 * @param string $instance_id The shipping method instance ID.
		 * @param array  $data        The poste data to validate.
		 * @return array
		 * @throw  Exception Validation error.
		 */
		protected function validate_shipping_method_data( $instance_id, $data ) {

			foreach ( $this->get_shipping_methods_fields( $instance_id ) as $key => $field ) {
				$type  = empty( $field['type'] ) ? 'text' : $field['type'];
				$value = isset( $data[ $key ] ) ? $data[ $key ] : null;

				if ( is_null( $value ) ) {
					unset( $data[ $key ] );
				}

				if ( isset( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ) {
					$value = call_user_func( $field['sanitize_callback'], $value );
				} elseif ( 'checkbox' === $type || 'onoff' === $type ) {
					$value = ! is_null( $value ) ? 'yes' : 'no';
				} else {
					$value = wp_kses_post( trim( stripslashes( $value ) ) );
				}

				// Fallback to text.
				$data[ $key ] = $value;
			}

			// At the end merge with default merge with default.
			$data = array_merge( $this->get_shipping_methods_default( $instance_id ), $data );
			return $data;
		}

		/**
		 * Get an array of available shipping regions
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_available_shipping_regions() {
			$regions           = array();
			$continents        = WC()->countries->get_continents();
			$allowed_countries = WC()->countries->get_allowed_countries();

			// Add special option for select all.
			$regions[] = array(
				'id'   => 'continent:all',
				'text' => esc_html_x( 'All regions', 'with regions means country, state, province...', 'yith-woocommerce-product-vendors' ),
			);

			foreach ( $continents as $continent_code => $continent ) {
				$regions[] = array(
					'id'   => 'continent:' . $continent_code,
					'text' => esc_html( $continent['name'] ),
				);

				$countries = array_intersect( array_keys( $allowed_countries ), $continent['countries'] );
				foreach ( $countries as $country_code ) {
					$regions[] = array(
						'id'   => 'country:' . $country_code,
						'text' => esc_html( '&nbsp;&nbsp; ' . $allowed_countries[ $country_code ] ),
					);

					$states = WC()->countries->get_states( $country_code );
					if ( is_array( $states ) ) {
						foreach ( $states as $state_code => $state_name ) {
							$regions[] = array(
								'id'   => 'state:' . $country_code . ':' . $state_code,
								'text' => esc_html( '&nbsp;&nbsp;&nbsp;&nbsp; ' . $state_name ),
							);
						}
					}
				}
			}

			return $regions;
		}

		/**
		 * Output shipping zones tab
		 *
		 * @since 4.0.0
		 */
		public function output_zones_tab() {

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			$zones = (  $vendor && $vendor->is_valid() ) ? $vendor->get_meta( 'zone_data' ) : array();

			include YITH_WPV_PATH . 'includes/admin/views/vendor-dashboard/shipping/shipping-zones.php';
		}

		/**
		 * Print single shipping zone
		 *
		 * @since  4.0.0
		 * @param integer $zone_id The zone ID.
		 * @param array   $zone    The zone data.
		 */
		public function print_single_shipping_zone( $zone_id, $zone ) {

			// Backward compatibility with old format.
			if ( ! empty( $zone['zone_post_code'] ) ) {
				if ( is_array( $zone['zone_post_code'] ) ) {
					$zone['zone_post_code'] = implode( PHP_EOL, $zone['zone_post_code'] );
				} else {
					$zone['zone_post_code'] = str_replace( ' ', PHP_EOL, $zone['zone_post_code'] );
				}
			}

			yith_wcmv_include_admin_template(
				'vendor-dashboard/shipping/shipping-zone.php',
				array(
					'zone_id' => $zone_id,
					'zone'    => $zone,
				)
			);
		}

		/**
		 * Print single shipping zone
		 *
		 * @since  4.0.0
		 * @param integer $zone_id   The zone ID.
		 * @param integer $method_id The shipping method ID.
		 * @param array   $method    The shipping method data.
		 */
		public function print_single_shipping_method( $zone_id, $method_id, $method ) {
			yith_wcmv_include_admin_template(
				'vendor-dashboard/shipping/shipping-method.php',
				array(
					'zone_id'   => $zone_id,
					'method_id' => $method_id,
					'method'    => $method,
				)
			);
		}

		/**
		 * Add shipping tab to options
		 *
		 * @since  4.0.0
		 * @param array $options An array of options.
		 * @return array
		 */
		public function add_shipping_tab_options( $options ) {
			$options = array_merge(
				$options,
				array(
					array(
						'type' => 'sectionstart',
					),
					array(
						'title' => __( 'Shipping info tab', 'yith-woocommerce-product-vendors' ),
						'type'  => 'title',
						'id'    => 'yith_wpv_shipping_options_section_title',
					),
					array(
						'title'     => _x( 'Shipping info tab label', '[Admin]Option label', 'yith-woocommerce-product-vendors' ),
						'type'      => 'yith-field',
						'yith-type' => 'text',
						'desc'      => _x( 'Enter the label for the shipping info tab.', '[Admin]Option description', 'yith-woocommerce-product-vendors' ),
						'id'        => 'yith_wpv_shipping_tab_text_text',
						'default'   => _x( 'Shipping info', '[Single Product Page]: Tab name for shipping information', 'yith-woocommerce-product-vendors' ),
					),
					array(
						'type' => 'sectionend',
					),
				)
			);
			return $options;
		}
	}
}
