<?php
/**
 * YITH Vendors Privacy Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Privacy' ) ) {
	/**
	 * Vendor privacy handler class.
	 */
	class YITH_Vendors_Privacy extends YITH_Privacy_Plugin_Abstract {

		/**
		 * Class constructor.
		 *
		 * @since  2.6.0
		 * @return void
		 */
		public function __construct() {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin_data = get_plugin_data( YITH_WPV_FILE );
			$plugin_name = $plugin_data['Name'];

			parent::__construct( $plugin_name );

			add_action( 'init', array( $this, 'privacy_personal_data_init' ), 99 );
			add_filter( 'wp_privacy_anonymize_data', array( $this, 'privacy_anonymize_data_filter' ), 10, 3 );
			add_filter( 'yith_wcmv_get_vendor_personal_data_fields', array( $this, 'get_vendor_personal_data_fields_premium' ) );
			add_filter( 'yith_wcmv_get_vendor_personal_data_fields_type', array( $this, 'get_vendor_personal_data_fields_type_premium' ) );
		}

		/**
		 * GDPR Privacy Init
		 *
		 * @since  2.6.0
		 * @return void
		 */
		public function privacy_personal_data_init() {
			// Set up vendors data exporter.
			add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
			// Set up vendors data eraser.
			add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
		}

		/**
		 * Register exporters for plugin
		 *
		 * @since  2.6.0
		 * @param array $exporters Array of currently registered exporters.
		 * @return array Array of filtered exporters.
		 */
		public function register_exporter( $exporters ) {
			if ( apply_filters( 'yith_wcmv_export_vendor_data', true ) ) {
				// Exports data about vendor store details.
				$exporters['yith_wcmv_vendor_details'] = array(
					'exporter_friendly_name' => __( 'Vendor Data', 'yith-woocommerce-product-vendors' ),
					'callback'               => array( $this, 'store_details_export' ),
				);
			}

			if ( 'yes' === get_option( 'yith_vendor_exports_commissions', 'yes' ) ) {
				// Exports data about vendor store details.
				$exporters['yith_wcmv_vendor_commissions_data'] = array(
					'exporter_friendly_name' => __( 'Vendor Commissions Data', 'yith-woocommerce-product-vendors' ),
					'callback'               => array( $this, 'commissions_details_export' ),
				);
			}

			return $exporters;
		}

		/**
		 * Register eraser for plugin
		 *
		 * @since  2.6.0
		 * @param array $erasers Array of currently registered erasers.
		 * @return array Array of filtered erasers
		 */
		public function register_eraser( $erasers ) {

			$to_delete = get_option( 'yith_wpv_vendor_data_to_delete', array() );

			if ( in_array( 'commissions_user_id', $to_delete, true ) ) {
				// Erase data about vendor commission details.
				$erasers['yith_wcmv_vendor_commissions_data'] = array(
					'eraser_friendly_name' => __( 'Vendor Commissions Data', 'yith-woocommerce-product-vendors' ),
					'callback'             => array( $this, 'commissions_details_eraser' ),
				);
			}

			if ( in_array( 'profile', $to_delete, true ) ) {
				// Erase data about vendor profile details.
				$erasers['yith_wcmv_vendor_details'] = array(
					'eraser_friendly_name' => __( 'Vendor Data', 'yith-woocommerce-product-vendors' ),
					'callback'             => array( $this, 'store_details_eraser' ),
				);
			}

			return $erasers;
		}

		/**
		 * Export vendor details.
		 *
		 * @since  2.6.0
		 * @param string $user_email The user email of vendor owner.
		 * @return array
		 */
		public function store_details_export( $user_email ) {
			$user           = $this->get_user_by_email( $user_email );
			$data_to_export = array();
			$done           = true;

			if ( $user instanceof WP_User ) {
				$user_id = $user->ID;
				$vendor  = yith_wcmv_get_vendor( $user_id, 'user' );

				if ( $vendor->is_valid() && $vendor->is_owner( $user_id ) ) {
					$personal_data = array();
					$to_exports    = $this->get_vendor_personal_data_fields();

					foreach ( $to_exports as $to_export => $label ) {

						if ( 'socials' !== $to_export ) {
							$method = "get_{$to_export}";
							$value  = method_exists( $vendor, $method ) ? $vendor->$method() : $vendor->get_meta( $to_export );
							if ( ! empty( $value ) ) {
								$personal_data[] = array(
									'name'  => $label,
									'value' => $value,
								);
							}
						} else {
							$social_fields = YITH_Vendors()->get_social_fields();
							foreach ( $vendor->get_socials() as $social => $uri ) {
								if ( ! empty( $uri ) ) {
									$personal_data[] = array(
										'name'  => $social_fields['social_fields'][ $social ]['label'],
										'value' => $uri,
									);
								}
							}
						}
					}

					$data_to_export[] = array(
						'group_id'    => 'yith_wcmv_vendor_data',
						'group_label' => __( 'Vendor Data', 'yith-woocommerce-product-vendors' ),
						'item_id'     => 'vendor-' . $vendor->get_id(),
						'data'        => $personal_data,
					);
				}
			}

			return array(
				'data' => $data_to_export,
				'done' => $done,
			);
		}

		/**
		 * Export vendor commissions details
		 *
		 * @since  2.6.0
		 * @param string  $user_email The user email of vendor owner.
		 * @param integer $page       Page counter.
		 * @return array
		 */
		public function commissions_details_export( $user_email, $page ) {

			$user           = $this->get_user_by_email( $user_email );
			$data_to_export = array();
			$number         = 50;
			$page           = (int) $page;
			$offset         = $number * ( $page - 1 );
			$done           = true;

			if ( $user instanceof WP_User ) {
				$user_id = $user->ID;
				$vendor  = yith_wcmv_get_vendor( $user_id, 'user' );

				if ( $vendor->is_valid() && $vendor->is_owner( $user_id ) ) {

					$commissions = yith_wcmv_get_commissions(
						array(
							'vendor_id' => $vendor->get_id(),
							'status'    => 'all',
							'number'    => $number,
							'paged'     => $page,
							'offset'    => $offset,
						)
					);

					if ( 0 < count( $commissions ) ) {
						$to_exports = array(
							'id'            => __( 'Commission ID', 'yith-woocommerce-product-vendors' ),
							'order_id'      => __( 'Refer to Order ID', 'yith-woocommerce-product-vendors' ),
							'user_id'       => __( 'User ID', 'yith-woocommerce-product-vendors' ),
							'vendor_id'     => __( 'Vendor ID', 'yith-woocommerce-product-vendors' ),
							'rate'          => __( 'Commission rate (%)', 'yith-woocommerce-product-vendors' ),
							'amount'        => __( 'Commission amount', 'yith-woocommerce-product-vendors' ),
							'status'        => __( 'Commission status', 'yith-woocommerce-product-vendors' ),
							'type'          => __( 'Commission type', 'yith-woocommerce-product-vendors' ),
							'last_edit'     => __( 'Last Update', 'yith-woocommerce-product-vendors' ),
							'last_edit_gmt' => __( 'Last Update (GMT)', 'yith-woocommerce-product-vendors' ),
						);

						foreach ( $commissions as $commission_id ) {

							$commission = yith_wcmv_get_commission( $commission_id );
							if ( ! $commission ) {
								continue;
							}

							$personal_data = array();

							foreach ( $to_exports as $to_export => $label ) {
								$method = "get_{$to_export}";
								$value  = method_exists( $commission, $method ) ? $commission->$method() : '';
								if ( ! empty( $value ) ) {

									if ( 'rate' === $to_export ) {
										$value = $value * 100;
									}

									if ( 'amount' === $to_export ) {
										$order = $commission->get_order();
										$value = wc_price( $value, array( 'currency' => $order->get_currency() ) );
									}

									$personal_data[] = array(
										'name'  => $label,
										'value' => $value,
									);
								}
							}

							$data_to_export[] = array(
								'group_id'    => 'yith_wcmv_vendor_commissions_data',
								'group_label' => __( 'Vendor Commissions Data', 'yith-woocommerce-product-vendors' ),
								'item_id'     => 'commissions-' . $commission->get_id(),
								'data'        => $personal_data,
							);
						}
						$done = $number > count( $commissions );
					} else {
						$done = true;
					}
				}
			}

			return array(
				'data' => $data_to_export,
				'done' => $done,
			);
		}

		/**
		 * Eraser Vendor Details
		 *
		 * @since  2.6.0
		 * @param string $user_email The user email of vendor owner.
		 * @return array
		 */
		public function store_details_eraser( $user_email ) {

			$user     = $this->get_user_by_email( $user_email );
			$response = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			if ( $user instanceof WP_User ) {

				// Check if current user is a vendor.
				$user_id = $user->ID;
				$vendor  = yith_wcmv_get_vendor( $user_id, 'user' );

				if ( $vendor && $vendor->is_valid() ) {
					if ( $vendor->is_owner( $user_id ) ) {

						$to_eraser          = $this->get_vendor_personal_data_fields_type();
						$fields_description = $this->get_vendor_personal_data_fields();

						if ( $vendor->remove_owner() ) {
							$response['messages'][] = _x( 'Removed vendor "Owner"', '[GDPR Message]', 'yith-woocommerce-product-vendors' );
						}

						// Remove vendor admins.
						foreach ( $vendor->get_admins() as $admin_id ) {
							$user_meta_key = delete_user_meta( $admin_id, yith_wcmv_get_user_meta_key() );
						}
						if ( $user_meta_key ) {
							$response['messages'][] = _x( 'Removed vendor "Admins"', '[GDPR Message]', 'yith-woocommerce-product-vendors' );
						}
						// No vendor owner no admins.
						$vendor->set_meta_data( 'admins', null );

						foreach ( $to_eraser as $field => $type ) {
							if ( 'socials' !== $field ) {
								$getter          = "get_{$field}";
								$value           = method_exists( $vendor, $getter ) ? $vendor->$getter() : $vendor->get_meta( $field );
								$anonymize_value = $this->privacy_anonymize_data( $type, $value );
								// Set anonymize value.
								$setter = "set_{$field}";
								method_exists( $vendor, $setter ) ? $vendor->$setter( $anonymize_value ) : $vendor->set_meta( $field, $anonymize_value );

								$label                  = isset( $fields_description[ $field ] ) ? $fields_description[ $field ] : ucfirst( str_replace( '_', ' ', $field ) );
								$response['messages'][] = sprintf( '%s "%s"', esc_html_x( 'Removed vendor', '[GDPR Message]', 'yith-woocommerce-product-vendors' ), $label );
							}

							if ( 'socials' === $field ) {
								$socials         = array();
								$removed_socials = false;
								foreach ( $vendor->get_socials() as $social => $uri ) {
									if ( ! empty( $uri ) ) {
										$socials[ $social ] = $this->privacy_anonymize_data( 'url', $uri );
										$removed_socials    = true;
									}
								}
								if ( $removed_socials ) {
									$response['messages'][] = esc_html__( 'Removed vendor "Social Network" URLs', 'yith-woocommerce-product-vendors' );
								}

								$vendor->set_meta_data( 'socials', $socials );
							}
						}
						$response['items_removed'] = true;
					} else {
						// Vendor is valid, but it's not owner...so the user is an administrator.
						$admins    = $vendor->get_admins();
						$admin_key = array_search( $user_id, $admins, true );

						if ( ! empty( $admin_key ) ) {
							unset( $admins[ $admin_key ] );
						}

						$vendor->set_meta_data( 'admins', $admins );
						$user_meta_key = delete_user_meta( $user_id, yith_wcmv_get_user_meta_key() );

						if ( $user_meta_key ) {
							$response['messages'][] = _x( 'Removed vendor "Admins"', '[GDPR Message]', 'yith-woocommerce-product-vendors' );
						}
					}

					$vendor->save();
				}
			}

			return $response;
		}

		/**
		 * Eraser Vendor Commissions Details
		 *
		 * @since  2.6.0
		 * @param string  $user_email The user email of vendor owner.
		 * @param integer $page       Page counter.
		 * @return array
		 */
		public function commissions_details_eraser( $user_email, $page ) {
			$user     = $this->get_user_by_email( $user_email );
			$number   = 50;
			$page     = (int) $page;
			$offset   = $number * ( $page - 1 );
			$response = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			if ( $user instanceof WP_USer ) {
				$user_id = $user->ID;
				$vendor  = yith_wcmv_get_vendor( $user_id, 'user' );

				if ( $vendor->is_valid() && $vendor->is_owner( $user_id ) ) {

					$commissions = yith_wcmv_get_commissions(
						array(
							'vendor_id' => $vendor->get_id(),
							'status'    => 'all',
							'number'    => $number,
							'paged'     => $page,
							'offset'    => $offset,
						)
					);

					if ( 0 < count( $commissions ) ) {
						foreach ( $commissions as $commission_id ) {
							$commission = yith_wcmv_get_commission( $commission_id );
							if ( ! $commission ) {
								continue;
							}

							$commission->set_user_id( 0 );
							$commission->save();
						}

						$message = _x( 'Removed user information from vendor commissions', '[GDPR Message]', 'yith-woocommerce-product-vendors' );

						$response['done']          = $number > count( $commissions );
						$response['messages'][]    = sprintf( '%s (%s/%s)', $message, $offset, ( $offset + $number ) );
						$response['items_removed'] = true;
					} else {
						$response['done'] = true;
					}
				}
			}

			return $response;
		}

		/**
		 * Get WP_User by email.
		 *
		 * @since  2.6.0
		 * @param string $user_email The user email.
		 * @return WP_User|false WP_User object on success, false on failure.
		 */
		public function get_user_by_email( $user_email ) {
			return get_user_by( 'email', $user_email );
		}

		/**
		 * Get vendor personal data field to export/erase.
		 *
		 * @since  2.6.0
		 * @return array Vendor Personal data fields.
		 */
		public function get_vendor_personal_data_fields() {
			return apply_filters(
				'yith_wcmv_get_vendor_personal_data_fields',
				array(
					'id'           => __( 'Vendor ID', 'yith-woocommerce-product-vendors' ),
					'name'         => __( 'Store Name', 'yith-woocommerce-product-vendors' ),
					'slug'         => __( 'Store Slug', 'yith-woocommerce-product-vendors' ),
					'description'  => __( 'Store Description', 'yith-woocommerce-product-vendors' ),
					'paypal_email' => __( 'Owner PayPal Email', 'yith-woocommerce-product-vendors' ),
				)
			);
		}

		/**
		 * Get vendor personal data field type to export/erase.
		 *
		 * @since  2.6.0
		 * @return array Vendor Personal data fields
		 */
		public function get_vendor_personal_data_fields_type() {
			return apply_filters(
				'yith_wcmv_get_vendor_personal_data_fields_type',
				array(
					'name'         => 'yith_wcmv_taxonomy_name',
					'slug'         => 'yith_wcmv_taxonomy_slug',
					'description'  => 'longtext',
					'paypal_email' => 'email',
				)
			);
		}

		/**
		 * Wrapper for anonymize data
		 *
		 * @param string $data_type The type of data to be anonymized.
		 * @param string $value     Optional The data to be anonymized.
		 * @return string The anonymous data for the requested type.
		 */
		public function privacy_anonymize_data( $data_type, $value ) {
			return function_exists( 'wp_privacy_anonymize_data' ) ? wp_privacy_anonymize_data( $data_type, $value ) : '';
		}


		/**
		 * Filters anonymize data.
		 *
		 * @since  2.6.0
		 * @param string $anonymous Anonymized data.
		 * @param string $type      Type of the data.
		 * @param string $data      Original data.
		 */
		public function privacy_anonymize_data_filter( $anonymous, $type, $data ) {

			if ( 'yith_wcmv_taxonomy_name' === $type ) {
				$anonymous = sprintf( '[%s %s]', __( 'deleted vendor', 'yith-woocommerce-product-vendors' ), wc_rand_hash() );
			}

			if ( 'yith_wcmv_taxonomy_slug' === $type ) {
				$anonymous = sprintf( '[%s%s]', __( 'deleted-vendor-', 'yith-woocommerce-product-vendors' ), wc_rand_hash() );
			}

			if ( 'yith_wcmv_profile_media' === $type ) {

				$to_delete = get_option( 'yith_wpv_vendor_data_to_delete', array() );

				if ( in_array( 'media', $to_delete, true ) ) {
					wp_delete_attachment( $data, true );
				}
				$anonymous = 0;
			}

			return $anonymous;
		}

		/**
		 * Gets the message of the privacy to display.
		 * To be overloaded by the implementor.
		 *
		 * @since  2.6.0
		 * @param string $section The message section.
		 * @return string
		 */
		public function get_privacy_message( $section ) {
			$message = '';
			switch ( $section ) {
				case 'collect_and_store':
					$message = '<p>' . __( 'We collect information about you during the registration and checkout processes on our store.', 'yith-woocommerce-product-vendors' ) . '</p>' .
						'<p>' . __( 'While you visit our site, we’ll track:', 'yith-woocommerce-product-vendors' ) . '</p>' .
						'<ul>' .
						'<li>' . __( 'Vendor information: we will use this data to create a vendor profile that allows each vendor to sell products on this website in exchange for a commission fee on each sale.', 'yith-woocommerce-product-vendors' ) . '</li>' .
						'<li>' . __( 'The information required to start a vendor shop is the following: name and store description, header image, shop logo, address, email, phone number, VAT/SSN, legal notes, links to social profiles (Facebook, Twitter, LinkedIn, YouTube, Vimeo, Instagram, Pinterest, Flickr, Behance, TripAdvisor), payment information (IBAN and/or PayPal email), and information related to commissions and issued payments.', 'yith-woocommerce-product-vendors' ) . '</li>' .
						'</ul>';
					break;

				case 'has_access':
					$message = '<p>' . __( 'Members of our team have access to the information you provide to us. For example, both Administrators and Shop Managers can access:', 'yith-woocommerce-product-vendors' ) . '</p>' .
						'<ul>' .
						'<li>' . __( 'Vendor information', 'yith-woocommerce-product-vendors' ) . '</li>' .
						'<li>' . __( 'Data concerning commissions earned by the vendor', 'yith-woocommerce-product-vendors' ) . '</li>' .
						'<li>' . __( 'Data about payments', 'yith-woocommerce-product-vendors' ) . '</li>' .
						'</ul>' .
						'<p>' . __( 'Our team members have access to this information to help fulfill orders, process refunds and support you.', 'yith-woocommerce-product-vendors' ) . '</p>';
					break;

				case 'payments':
					$message = '<p>' . __( 'We send payments to vendors through PayPal. When processing payments, some of your data will be passed to PayPal, including information required to process or support the payment, such as the purchase total and billing information.', 'yith-woocommerce-product-vendors' ) . '</p>' .
						'<p>' . __( 'Please see the <a href="https://www.paypal.com/us/webapps/mpp/ua/privacy-full">PayPal Privacy Policy</a> for more details.', 'yith-woocommerce-product-vendors' ) . '</p>';
					break;

				case 'share':
					$message = '<p>' . __( 'We share information with third parties who help us provide commissions payments to you.', 'yith-woocommerce-product-vendors' ) . '</p>';
					break;

			}

			return $message;
		}

		/**
		 * Get premium vendor personal data field to export/erase.
		 *
		 * @since  2.6.0
		 * @param array $fields Current personal data fields to export.
		 * @return array Vendor Personal data fields.
		 */
		public function get_vendor_personal_data_fields_premium( $fields ) {
			$premium_fields = array(
				'location'              => __( 'Store Location', 'yith-woocommerce-product-vendors' ),
				'store_email'           => __( 'Store Email', 'yith-woocommerce-product-vendors' ),
				'telephone'             => __( 'Vendor Phone', 'yith-woocommerce-product-vendors' ),
				'vat'                   => __( 'VAT/SSN', 'yith-woocommerce-product-vendors' ),
				'bank_account'          => __( 'Vendor Bank Account', 'yith-woocommerce-product-vendors' ),
				'commission'            => __( 'Commission Rate (%)', 'yith-woocommerce-product-vendors' ),
				'registration_date'     => __( 'Registration Date', 'yith-woocommerce-product-vendors' ),
				'registration_date_gmt' => __( 'Registration Date GMT', 'yith-woocommerce-product-vendors' ),
				'socials'               => __( 'Vendor socials URLs', 'yith-woocommerce-product-vendors' ),
			);

			return array_merge( $fields, $premium_fields );
		}

		/**
		 * Get premium vendor personal data field type to export/erase.
		 *
		 * @since  2.6.0
		 * @param array $fields Current personal data fields type to export.
		 * @return array Vendor Personal data fields
		 */
		public function get_vendor_personal_data_fields_type_premium( $fields ) {
			$premium_fields = array(
				'location'     => 'text',
				'store_email'  => 'email',
				'telephone'    => 'text',
				'vat'          => 'text',
				'bank_account' => 'text',
				'socials'      => 'url',
				'legal_notes'  => 'text',
				'header_image' => 'yith_wcmv_profile_media',
				'avatar'       => 'yith_wcmv_profile_media',
			);

			return array_merge( $fields, $premium_fields );
		}
	}
}
