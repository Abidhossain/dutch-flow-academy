<?php
/**
 * YITH_Vendors_Integrations
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 5.0.0
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

if ( ! class_exists( 'YITH_Vendors_Integrations' ) ) {
	/**
	 * Handle plugin modules.
	 */
	class YITH_Vendors_Integrations {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * An array of available integrations
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $integrations = array();

		/**
		 * An array of integrations post types to handle
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $integrations_post_types = array();

		/**
		 * An array of integrations capabilities to add
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $integrations_capabilities = array();

		/**
		 * Constructor
		 *
		 * @since  5.0.0
		 * @return void
		 */
		private function __construct() {
			$this->integrations = include YITH_WPV_PATH . 'plugin-options/integrations.php';
			if ( ! empty( $this->integrations ) ) {
				$this->init_hooks();
				$this->load_integrations();
			}
		}

		/**
		 * Init modules hooks
		 *
		 * @since  5.0.0
		 * @return void
		 */
		protected function init_hooks() {

			// Add capabilities.
			add_filter( 'yith_wcmv_update_capabilities_required_options', array( $this, 'add_integrations_options' ) );
			add_filter( 'yith_wcmv_vendor_additional_capabilities', array( $this, 'add_integrations_capabilities' ) );
			// Add post types.
			add_filter( 'yith_wcmv_vendor_allowed_vendor_post_type', array( $this, 'add_integrations_post_types' ) );
			// Add vendor taxonomy to module post types.
			add_filter( 'yith_wcmv_register_taxonomy_object_type', array( $this, 'add_integrations_post_types' ) );
		}

		/**
		 * Load available modules
		 *
		 * @since  5.0.0
		 * @return void
		 */
		protected function load_integrations() {
			foreach ( $this->integrations as $key => $integration ) {

				// Always register module capabilities to clean-up caps array.
				if ( ! empty( $integration['capabilities'] ) ) {
					$this->integrations_capabilities[ $key ] = (array) apply_filters( "yith_wcmv_{$key}_capabilities", $integration['capabilities'] );
				}

				// Then check if integrations is active. If not, do not go further.
				if ( ! $this->is_active( $key ) ) {
					continue;
				}

				if ( ! empty( $integration['post_types'] ) ) {
					$this->integrations_post_types[ $key ] = apply_filters( "yith_wcmv_{$key}_post_types", $integration['post_types'] );
				}

				// Includes file if any.
				if ( ! empty( $integration['includes'] ) ) {
					// Base path.
					$path = YITH_WPV_PATH . 'includes/integrations/';

					foreach ( $integration['includes'] as $section => $files ) {
						if ( ! $this->is_section( $section ) ) {
							continue;
						}

						if ( ! is_array( $files ) ) {
							$files = explode( ',', $files );
						}

						// Include files.
						foreach ( $files as $file ) {
							if ( file_exists( $path . $file ) ) {
								include_once $path . $file;
							}
						}
					}
				}
			}
		}

		/**
		 * Get integration option name
		 *
		 * @since 5.0.0
		 * @param string $integration The integration key.
		 * @return string
		 */
		protected function get_option_name( $integration ) {
			return $this->integrations[ $integration ]['option_name'] ?? "yith_wpv_vendors_option_{$integration}_management";
		}

		/**
		 * Check if given integration is active
		 *
		 * @since 5.0.0
		 * @param string $integration The integration key.
		 * @return boolean
		 */
		protected function is_active( $integration ) {
			return ( ! empty( $this->integrations[ $integration ]['autoload'] ) || 'yes' === get_option( $this->get_option_name( $integration ), 'no' ) )
				&& $this->is_available( $integration );
		}

		/**
		 * Check if given integration is available
		 *
		 * @since 5.0.0
		 * @param string $integration The integration key.
		 * @return boolean
		 */
		protected function is_available( $integration ) {
			return $this->integrations[ $integration ]['available'] ?? true;
		}

		/**
		 * Check current section active for modules
		 *
		 * @since  5.0.0
		 * @author YITH
		 * @param string $section The section to check.
		 * @return boolean
		 */
		protected function is_section( $section ) {
			// Handle admin request.
			switch ( $section ) {
				case 'admin':
					$value = yith_wcmv_is_admin_request();
					break;
				case 'frontend':
					$value = yith_wcmv_is_frontend_request();
					break;
				default:
					$value = true;
					break;
			}

			return $value;
		}

		/**
		 * Add integrations options to the list of the option that must update capabilities on update.
		 *
		 * @since 5.0.0
		 * @param array $options An array of options keys.
		 * @return array
		 */
		public function add_integrations_options( $options ) {
			foreach ( array_keys( $this->integrations ) as $key ) {
				$options[] = $this->get_option_name( $key );
			}

			return $options;
		}

		/**
		 * Add integrations capabilities.
		 *
		 * @since  5.0.0
		 * @param array $capabilities The capabilities array.
		 * @return array;
		 */
		public function add_integrations_capabilities( $capabilities ) {

			foreach ( $this->integrations_capabilities as $integration => $integration_capabilities ) {
				if ( ! $this->is_active( $integration ) ) {
					continue;
				}

				$capabilities[ $integration ] = $integration_capabilities;
			}

			return $capabilities;
		}

		/**
		 * Add integrations post type to default array
		 *
		 * @since  5.0.0
		 * @param array $post_types The default array value.
		 * @return array
		 */
		public function add_integrations_post_types( $post_types ) {
			foreach ( $this->integrations_post_types as $integration_post_types ) {
				$post_types = array_merge( $post_types, (array) $integration_post_types );
			}

			return array_unique( $post_types ); // Avoid duplicated.
		}

		/**
		 * Based on array of integrations, prepare checkboxgroup options.
		 *
		 * @since 5.0.0
		 * @param array $integrations The integrations to prepare.
		 * @return array
		 */
		public function prepare_options( $integrations ) {

			$formatted_options = array();
			// translators: %s is the plugin name.
			$label_part = _x( '(you need the %s plugin)', '[Admin] Option label. %s is the plugin name.', 'yith-woocommerce-product-vendors' );

			foreach ( $integrations as $integration ) {
				if ( ! isset( $this->integrations[ $integration ] ) ) {
					continue;
				}

				$formatted_options[] = array(
					'type'              => 'checkbox',
					'checkboxgroup'     => '',
					'id'                => $this->get_option_name( $integration ),
					'desc'              => $this->integrations[ $integration ]['option_desc'] . ' ' . ( $this->is_available( $integration ) ? '' : sprintf( $label_part, '<a href="' . $this->integrations[ $integration ]['landing_uri'] . '" target="_blank">' . $this->integrations[ $integration ]['name'] . '</a>' ) ),
					'default'           => 'no',
					'custom_attributes' => array_filter( array( 'disabled' => ! $this->is_available( $integration ) ? 'disabled' : false ) ),
				);
			}

			usort(
				$formatted_options,
				function ( $a, $b ) {
					return isset( $a['custom_attributes']['disabled'] ) ? 1 : -1;
				}
			);

			return $formatted_options;
		}
	}
}
