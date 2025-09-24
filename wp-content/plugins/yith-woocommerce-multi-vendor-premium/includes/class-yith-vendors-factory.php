<?php
/**
 * Factory class for the YITH_Vendor
 *
 * @since      4.0.0
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

if ( ! class_exists( 'YITH_Vendors_Factory' ) ) {
	/**
	 * Factory class for the YITH_Vendor
	 */
	class YITH_Vendors_Factory {

		/**
		 * Main Instance
		 *
		 * @var array
		 */
		protected static $instances = array();

		/**
		 * Create a new vendor
		 *
		 * @since  4.0.0
		 * @param array $data The data for the new vendor.
		 * @return integer|WP_Error The new vendor ID on success, WP_Error otherwise.
		 * @throws Exception Errors on create vendor.
		 */
		public static function create( $data ) {

			try {

				$data = self::validate_data( $data );
				if ( empty( $data ) ) {
					throw new Exception( _x( 'Create vendor failed: empty data for vendor.', '[Notice]Create vendor process error', 'yith-woocommerce-product-vendors' ) );
				}

				$vendor = new YITH_Vendor();
				foreach ( $data as $key => $value ) {
					$method = 'set_' . $key;
					if ( method_exists( $vendor, $method ) ) {
						$vendor->$method( $value );
					} else {
						$vendor->set_meta_data( $key, $value );
					}
				}

				$vendor->save();

				return $vendor->get_id();

			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );

				return new WP_Error( 'vendor-create-failed', $e->getMessage() );
			}
		}

		/**
		 * Retrieve a vendor
		 *
		 * @param mixed  $object      The vendor object.
		 * @param string $object_type What object is if is numeric (vendor|user|post).
		 * @return bool|YITH_Vendor
		 */
		public static function read( $object = false, $object_type = 'vendor' ) {

			try {

				$vendor_id = 0;
				// Change value 'current' to false for $vendor, to make it more rock!
				if ( 'current' === $object ) {
					$object = false;
				}

				switch ( $object_type ) {
					case 'user':
						if ( false === $object ) {
							$user_id = get_current_user_id();
						} elseif ( is_numeric( $object ) ) {
							$user_id = absint( $object );
						} elseif ( $object instanceof WP_User ) {
							$user_id = $object->ID;
						} elseif ( $object instanceof WC_Customer ) {
							$user_id = $object->get_id();
						}

						$vendor_id = ! empty( $user_id ) ? get_user_meta( $user_id, yith_wcmv_get_user_meta_key(), true ) : false;
						break;

					case 'product':
					case 'post':
						if ( false === $object ) {
							global $post;
							$post_id = isset( $post ) ? $post->ID : 0;
						} elseif ( $object instanceof WP_Post ) {
							$post_id = $object->ID;
						} elseif ( $object instanceof WC_Product_Variation ) {
							$post_id = $object->get_parent_id();
						} elseif ( $object instanceof WC_Product ) {
							$post_id = $object->get_id();
						} elseif ( is_numeric( $object ) ) {
							$post_id = absint( $object );
						}

						$terms = ! empty( $post_id ) ? wp_get_post_terms( $post_id, YITH_Vendors_Taxonomy::TAXONOMY_NAME ) : array();

						if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
							$vendor_term = array_shift( $terms );
							$vendor_id   = $vendor_term->term_id;
						}
						break;

					case 'vendor':
						if ( $object instanceof YITH_Vendor ) {
							$vendor_id = $object->get_id();
						} elseif ( isset( $object->slug ) && term_exists( $object->slug, YITH_Vendors_Taxonomy::TAXONOMY_NAME ) ) { // Get vendor by term object.
							$vendor_id = absint( $object->term_id );
						} else {
							foreach ( array( 'term_id', 'slug', 'name' ) as $term_field ) {
								// Prevent search for term_id if object is not numeric. This check will prevent a useless operation.
								if ( 'term_id' === $term_field && ! is_numeric( $object ) ) {
									continue;
								}
								// Try to get vendor.
								$vendor_term = get_term_by( $term_field, $object, YITH_Vendors_Taxonomy::TAXONOMY_NAME );
								if ( $vendor_term instanceof WP_Term ) {
									$vendor_id = $vendor_term->term_id;
									break;
								}
							}
						}

						break;
				}

				// Let's filter vendor_id before read instance.
				$vendor_id = apply_filters( 'yith_wcmv_vendors_factory_read_vendor_id', $vendor_id, $object, $object_type );
				return self::instance( $vendor_id );

			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Update a vendor
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor ID to update.
		 * @param array   $data      The vendor data to update.
		 * @return boolean|WP_Error True on success, WP_Error otherwise.
		 * @throws Exception Errors on update vendor.
		 */
		public static function update( $vendor_id, $data ) {

			try {

				$vendor_id = absint( $vendor_id );
				$vendor    = self::read( $vendor_id );
				if ( ! $vendor_id || ! $vendor->is_valid() ) {
					// translators: %s stand for the vendor ID.
					throw new Exception( sprintf( _x( 'Update vendor failed: no vendor found for ID #%s.', '[Notice]Update vendor process error', 'yith-woocommerce-product-vendors' ), $vendor_id ) );
				}

				$data = self::validate_data( $data );
				if ( empty( $data ) ) {
					// translators: %s stand for the vendor name.
					throw new Exception( sprintf( _x( 'Update vendor failed: empty data for vendor %s.', '[Notice]Update vendor process error', 'yith-woocommerce-product-vendors' ), $vendor->get_name() ) );
				}

				foreach ( $data as $key => $value ) {
					$method = 'set_' . $key;
					if ( method_exists( $vendor, $method ) ) {
						$vendor->$method( $value );
					} else {
						$vendor->set_meta( $key, $value );
					}
				}
				$vendor->save();

				return true;

			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );

				return new WP_Error( 'vendor-update-failed', $e->getMessage() );
			}
		}

		/**
		 * Delete a vendor
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor ID to delete.
		 * @return boolean|WP_Error True on success, WP_Error otherwise.
		 */
		public static function delete( $vendor_id ) {

			try {
				$vendor_id = absint( $vendor_id );
				$vendor    = self::read( $vendor_id );

				if ( $vendor ) {
					$vendor->delete();
					self::unset( $vendor_id );
				}

				return true;

			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );

				return new WP_Error( 'vendor-delete-failed', $e->getMessage() );
			}
		}

		/**
		 * Get cached vendor instance by ID
		 *
		 * @param integer $vendor_id The vendor ID to get.
		 * @return YITH_Vendor
		 * @throws Exception Error retrieving vendor instance.
		 */
		protected static function instance( $vendor_id = 0 ) {

			try {
				if ( empty( $vendor_id ) ) {
					throw new Exception();
				}

				if ( ! isset( self::$instances[ $vendor_id ] ) ) {
					self::$instances[ $vendor_id ] = new YITH_Vendor( $vendor_id );
				}

				return self::$instances[ $vendor_id ];

			} catch ( Exception $e ) {
				return new YITH_Vendor(); // Return empty vendor for backward compatibility. TO BE REMOVED.
			}
		}

		/**
		 * Unset cached vendor instance by ID
		 *
		 * @param integer $vendor_id The vendor ID to unset from instances.
		 */
		public static function unset( $vendor_id = 0 ) {
			unset( self::$instances[ $vendor_id ] );
		}

		/**
		 * Get vendor data for edit
		 *
		 * @since  4.0.0
		 * @param integer $vendor_id The vendor id to get data for.
		 * @param boolean $modal     (Optional) True if the request comes from AJAX modal, false otherwise. Default is true.
		 * @param array   $fields    (Optional) The fields to get data for. Default is all fields.
		 * @return array
		 * @throws Exception Error getting vendor data.
		 */
		public static function get_data( $vendor_id, $modal = true, $fields = array() ) {

			try {

				$vendor = self::read( $vendor_id );
				if ( ! $vendor || ! $vendor->is_valid() ) {
					throw new Exception( sprintf( 'Invalid vendor. No vendor with ID #%s', $vendor_id ) );
				}

				$data = array( 'vendor_id' => $vendor_id );
				if ( empty( $fields ) ) {
					$fields = array_keys( self::get_fields( true ) );
				}

				foreach ( $fields as $key ) {

					switch ( $key ) {
						case 'owner':
							$owner = $vendor->get_owner();
							if ( $owner ) {
								if ( $modal ) {
									$selected           = array();
									$customer           = new WC_Customer( absint( $owner ) );
									$selected[ $owner ] = sprintf(
										esc_html( '%1$s (#%2$s &ndash; %3$s)' ),
										$customer->get_first_name() . ' ' . $customer->get_last_name(),
										$customer->get_id(),
										$customer->get_email()
									);

									$data[ $key ] = wp_json_encode( $selected );
								} else {
									$data[ $key ] = absint( $owner );
								}
							} else {
								$data[ $key ] = '';
							}

							break;

						case 'commission':
							$data[ $key ] = $vendor->get_commission() * 100;
							break;

						case 'header_image':
						case 'avatar':
							$method = "get_{$key}_id";
							$id     = $vendor->$method();

							if ( $modal ) {
								$image_id     = absint( $id );
								$image_url    = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
								$data[ $key ] = $image_url ? wp_json_encode( array( $id => $image_url ) ) : '';
							} else {
								$data[ $key ] = absint( $id );
							}

							break;

						default:
							$method       = "get_{$key}";
							$data[ $key ] = method_exists( $vendor, $method ) ? $vendor->$method() : $vendor->get_meta( $key, 'edit' );
							break;
					}
				}

				return apply_filters( 'yith_wcmv_get_vendor_edit_data', $data, $vendor, $modal, $fields );

			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );
				return array();
			}
		}

		/**
		 * Get an array of vendors filtered by given params
		 *
		 * @since  4.0.0
		 * @param array $args (Optional) An array of query arguments. Default is empty array.
		 * @return mixed
		 */
		public static function query( $args ) {
			$args = wp_parse_args(
				$args,
				array(
					'status'          => '',
					'date_query'      => false,
					// Backward compatibility with the old query meta.
					'enabled_selling' => '',
					'pending'         => '',
				)
			);

			// Let's start build the query args.
			$query_args = array(
				'taxonomy'   => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
				'include'    => isset( $args['include'] ) ? $args['include'] : array(),
				'exclude'    => isset( $args['exclude'] ) ? $args['exclude'] : array(),
				'number'     => isset( $args['number'] ) ? $args['number'] : 10,
				'offset'     => isset( $args['offset'] ) ? $args['offset'] : 0,
				'meta_query' => isset( $args['meta_query'] ) ? $args['meta_query'] : array(), // phpcs:ignore
				'hide_empty' => isset( $args['hide_empty'] ) ? $args['hide_empty'] : false,
				'fields'     => isset( $args['fields'] ) ? $args['fields'] : 'all',
				'search'     => isset( $args['search'] ) ? $args['search'] : '',
			);

			// Add pagination (used by shortcodes).
			if ( isset( $args['pagination'] ) && isset( $args['pagination']['number'] ) && isset( $args['pagination']['offset'] ) ) {
				$query_args['offset'] = absint( $args['pagination']['offset'] );
				$query_args['number'] = intval( $args['pagination']['number'] );
			}

			// Set number correctly for get_terms function.
			if ( -1 === $query_args['number'] ) {
				$query_args['number'] = 0;
			}

			// Add order (used by shortcodes).
			if ( isset( $args['order'] ) && in_array( strtoupper( $args['order'] ), array( 'ASC', 'DESC' ), true ) ) {
				$query_args['order'] = strtoupper( $args['order'] );
			}

			// Add order by (used by shortcodes).
			if ( isset( $args['orderby'] ) ) {
				$query_args['orderby'] = $args['orderby'];
			}

			// Handle owner.
			if ( isset( $args['owner'] ) ) {

				if ( ! empty( $args['owner'] ) ) {
					$owner_meta_query = array(
						'key'     => 'owner',
						'value'   => $args['owner'],
						'compare' => is_array( $args['owner'] ) ? 'IN' : '=',
					);
				} else {
					$owner_meta_query = array(
						array(
							'relation' => 'OR',
							array(
								'key'   => 'owner',
								'value' => '',
							),
							array(
								'key'     => 'owner',
								'compare' => 'NOT EXISTS',
							),
						),
					);
				}

				$query_args['meta_query'][] = $owner_meta_query;
			}

			// Handle status.
			if ( ! empty( $args['status'] ) ) {
				$query_args['meta_query'][] = array(
					'key'     => 'status',
					'value'   => $args['status'],
					'compare' => is_array( $args['status'] ) ? 'IN' : '=',
				);
			} elseif ( ! empty( $args['enabled_selling'] ) || ! empty( $args['pending'] ) ) { // Backward compatibility.
				$status_query_values = array(
					( true === $args['enabled_selling'] || 'yes' === $args['enabled_selling'] ) ? 'enabled' : 'disabled',
					( true === $args['pending'] || 'yes' === $args['pending'] ) ? 'pending' : 'enabled',
				);

				$query_args['meta_query'][] = array(
					'key'     => 'status',
					'value'   => array_unique( $status_query_values ),
					'compare' => 'IN',
				);
			}

			if ( ! empty( $args['date_query'] ) && is_array( $args['date_query'] ) ) {
				foreach ( $args['date_query'] as $key => $value ) {
					$query_args['meta_query'][] = array(
						'key'     => 'registration_date',
						'value'   => $value,
						'compare' => 'after' === $key ? '>=' : '<=',
					);
				}
			}

			// Remove dummy meta_query if empty.
			if ( empty( $query_args['meta_query'] ) ) {
				unset( $query_args['meta_query'] );
			}

			// Let's filter the query args.
			$query_args = apply_filters( 'yith_wcmv_get_vendors_query_args', $query_args, $args );

			$vendors = get_terms( $query_args );
			if ( empty( $vendors ) || is_wp_error( $vendors ) ) {
				return array();
			}

			if ( 'count' === $query_args['fields'] ) {
				$res = absint( $vendors );
			} else {
				$res = array();
				foreach ( $vendors as $vendor ) {
					$res[] = $vendor instanceof WP_Term ? yith_wcmv_get_vendor( $vendor ) : $vendor;
				}
			}

			return $res;
		}

		/**
		 * Return the count of commissions in base of query
		 *
		 * @since 1.0
		 * @param array $q Query parameters.
		 * @return integer
		 */
		public static function count( $q = array() ) {
			// removes pagination parameter.
			if ( isset( $q['pagination'] ) ) {
				unset( $q['pagination'] );
			}

			// set return type.
			$q['fields'] = 'count';

			// query database.
			return (int) self::query( $q );
		}

		/**
		 * Validate data
		 *
		 * @since  4.0.0
		 * @param array $data The data to validate.
		 * @return array
		 */
		protected static function validate_data( $data ) {
			return apply_filters( 'yith_wcmv_vendor_factory_validate_data', $data );
		}

		/**
		 * Get vendor fields
		 *
		 * @since  4.0.0
		 * @param boolean $plain (Optional) True to get a plan array, false otherwise. Default false.
		 * @return array
		 */
		public static function get_fields( $plain = false ) {
			global $wp_rewrite;

			$header_size = get_option(
				'yith_wpv_header_image_size',
				array(
					'width'  => 1400,
					'height' => 460,
				)
			);

			$fields = apply_filters(
				'yith_wcmv_vendor_fields',
				array(
					'account' => array(
						'owner' => array(
							'type'     => 'select_owner',
							'required' => true,
						),
					),
					'store'   => array(
						'avatar'        => array(
							'type'  => 'avatar_image',
							'icon'  => 'user',
							'label' => _x( 'Upload Vendor profile image', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
						),
						'header_image'  => array(
							'type'  => 'header_image',
							'icon'  => 'store',
							// translators: %1$sx%2$s is the image size.
							'label' => sprintf( _x( 'Upload Vendor header image (Size %1$sx%2$s)', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ), $header_size['width'], $header_size['height'] ),
						),
						'field_sep'     => array(
							'type' => 'separator',
						),
						'name'          => array(
							'type'              => 'text',
							'label'             => _x( 'Store Name', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'required'          => true,
							'class'             => array( 'ajax-check' ),
							'custom_attributes' => array(
								'data-action' => 'validate_vendor_name',
							),
						),
						'slug'          => array(
							'type'              => 'text',
							'label'             => _x( 'Store Slug', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'required'          => true,
							'description'       => home_url( $wp_rewrite->get_extra_permastruct( YITH_Vendors_Taxonomy::TAXONOMY_NAME ) ),
							'class'             => array( 'ajax-check' ),
							'custom_attributes' => array(
								'data-action' => 'validate_vendor_slug',
							),

						),
						'section_title' => array(
							'type'  => 'title',
							'label' => _x( 'Address & Contact info', '[Admin] Vendor section title', 'yith-woocommerce-product-vendors' ),
						),
						'location'      => array(
							'type'  => 'text',
							'label' => _x( 'Street', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
						),
						'city'          => array(
							'type'  => 'text',
							'label' => _x( 'City', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
						),
						'zipcode'       => array(
							'type'  => 'text',
							'label' => _x( 'ZIP', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
						),
						'country'       => array(
							'type'  => 'select_country',
							'label' => _x( 'Country', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
						),
						'state'         => array(
							'type'  => 'text',
							'label' => _x( 'State', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'class' => array( 'state-field' ),
						),
						'vat'           => array(
							'type'  => 'text',
							'label' => _x( 'CIF/VAT', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
						),
						'telephone'     => array(
							'type'  => 'text',
							'label' => _x( 'Store phone number', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
						),
						'store_email'   => array(
							'type'  => 'text',
							'label' => _x( 'Store email address', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'class' => array( 'email-validate' ),
						),
						'website'       => array(
							'type'  => 'text',
							'label' => __( 'Website URL', 'yith-woocommerce-product-vendors' ),
						),
						'description'   => array(
							'type'  => 'textarea-editor',
							'label' => _x( 'Store description', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'wide'  => true,
						),
						'legal_notes'   => array(
							'type'  => 'text',
							'label' => _x( 'Company legal notes', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'wide'  => true,
						),
					),
					'payment' => array(
						'bank_account_name'  => array(
							'type'        => 'text',
							'label'       => _x( 'Account name', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'placeholder' => _x( 'Example: Bank of England', '[Admin] Vendor option placeholder', 'yith-woocommerce-product-vendors' ),
						),
						'bank_account'       => array(
							'type'        => 'text',
							'label'       => _x( 'IBAN', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'placeholder' => 'IT1234567',
						),
						'bank_account_swift' => array(
							'type'        => 'text',
							'label'       => _x( 'SWIFT / BIC', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'placeholder' => '12345',
						),
						'paypal_email'       => array(
							'type'        => 'text',
							'label'       => _x( 'PayPal email', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'placeholder' => 'yourmail@email.com',
							'class'       => array( 'email-validate' ),
						),
					),
					'options' => array(
						'commission_type'   => array(
							'type'        => 'radio',
							'label'       => _x( 'Commission base', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'options'     => array(
								// translators: %s is the default commission rate.
								'default' => sprintf( _x( 'Use the default commission base (%s)', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ), yith_wcmv_get_base_commission_formatted() ),
								'custom'  => _x( 'Set a different commission percentage for this vendor', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							),
							'description' => _x( 'Choose whether to use the default commission settings or to set a different value for this vendor.', '[Admin] Vendor option description', 'yith-woocommerce-product-vendors' ),
							'default'     => 'default',
						),
						'commission'        => array(
							'type'               => 'number',
							'label'              => _x( 'Commission', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'class'              => array( 'percentage-input' ),
							'inline_description' => '%',
							'default'            => yith_wcmv_get_base_commission(),
							'deps'               => array(
								'id'    => 'commission_type',
								'value' => 'custom',
								'type'  => 'hide',
							),
							'custom_attributes'  => array(
								'min'  => 0,
								'max'  => 100,
								'step' => 0.1,
							),
						),
						'status'            => array(
							'type'    => 'select',
							'label'   => _x( 'Status', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'options' => yith_wcmv_get_vendor_statuses(),
							'default' => 'pending',
						),
						'skip_review'       => array(
							'type'        => 'onoff',
							'label'       => _x( 'Skip admin review', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'description' => _x( 'Enable this option to allow this vendor to publish products without the admin\'s review.', '[Admin] Vendor option description', 'yith-woocommerce-product-vendors' ),
						),
						'featured_products' => array(
							'type'        => 'onoff',
							'default'     => get_option( 'yith_wpv_vendors_option_featured_management', 'no' ),
							'label'       => _x( 'Allow vendor to set featured products', '[Admin] Vendor option label', 'yith-woocommerce-product-vendors' ),
							'description' => _x( 'Allow this vendor to manage featured products.', '[Admin] Vendor option description', 'yith-woocommerce-product-vendors' ),
						),
					),
				)
			);

			// Add additional fields.
			// Filter removing disabled and connected fields.
			$additional_fields = array_filter(
				YITH_Vendors_Registration_Form::get_fields(),
				function ( $field ) {
					return empty( $field['connected_to'] ) && isset( $field['active'] ) && 'yes' === $field['active'];
				}
			);

			// Add additional fields to main fields array.
			foreach ( $additional_fields as $additional_field ) {
				$key = yith_wcmv_sanitize_custom_meta_key( $additional_field['name'] );
				unset(
					$additional_field['active'],
					$additional_field['name']
				);

				if ( apply_filters( 'yith_wcmv_remove_required_fields_on_additional', true, $additional_field ) ) {
					unset( $additional_field['required'] );
				}

				$fields['additional'][ $key ] = $additional_field;
			}

			// Add social fields.
			$socials = YITH_Vendors()->get_social_fields();
			if ( ! empty( $socials ) && ! empty( $socials['social_fields'] ) ) {
				$fields['additional']['socials'] = array(
					'type'    => 'socials',
					'label'   => _x( 'Socials', '[Admin] Vendor section title', 'yith-woocommerce-product-vendors' ),
					'socials' => $socials['social_fields'],
				);
			}

			// Handle special header image and avatar.
			if ( 'all' === get_option( 'yith_wpv_header_use_default_image', 'no-image' ) ) {
				unset( $fields['store']['header_image'] );
			}
			if ( 'all' === get_option( 'yith_wpv_avatar_use_default_image', 'no-image' ) ) {
				unset( $fields['store']['avatar'] );
			}

			// Let's filter vendor fields.
			$fields = apply_filters( 'yith_wcmv_vendor_admin_fields', $fields, $plain );

			if ( $plain ) {
				$plan_fields = array();
				foreach ( $fields as $key => $key_fields ) {
					$plan_fields = array_merge( $plan_fields, $key_fields );
				}

				return $plan_fields;
			}

			return $fields;
		}

		/**
		 * Get the steps for vendor admin modal
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public static function get_modal_steps() {

			$fields = self::get_fields();

			$steps = array(
				'account'    => array(
					'label'  => _x( 'Account info', '[Admin] Vendor step creation title', 'yith-woocommerce-product-vendors' ),
					'fields' => isset( $fields['account'] ) ? $fields['account'] : array(),
				),
				'store'      => array(
					'label'  => _x( 'Store info', '[Admin] Vendor step creation title', 'yith-woocommerce-product-vendors' ),
					'fields' => isset( $fields['store'] ) ? $fields['store'] : array(),
				),
				'additional' => array(
					'label'  => _x( 'Additional info', '[Admin] Vendor step creation title', 'yith-woocommerce-product-vendors' ),
					'fields' => isset( $fields['additional'] ) ? $fields['additional'] : array(),
				),
				'payment'    => array(
					'label'  => _x( 'Payment info', '[Admin] Vendor step creation title', 'yith-woocommerce-product-vendors' ),
					'fields' => isset( $fields['payment'] ) ? $fields['payment'] : array(),
				),
				'options'    => array(
					'label'  => _x( 'Options', '[Admin] Vendor step creation title', 'yith-woocommerce-product-vendors' ),
					'fields' => isset( $fields['options'] ) ? $fields['options'] : array(),
				),
			);

			// Avoid to return empty step.
			$steps = array_filter(
				$steps,
				function ( $step ) {
					return ! empty( $step['fields'] );
				}
			);

			return apply_filters( 'yith_wcmv_vendor_modal_steps', $steps, $fields );
		}

		/**
		 * Search for vendors by given term
		 *
		 * @since  4.0.0
		 * @param string $term The term to search.
		 * @return array [ vendor_id => vendor_name ]
		 */
		public static function search( $term ) {
			if ( empty( $term ) ) {
				return array();
			}

			$args = array(
				'orderby'    => 'name',
				'order'      => 'ASC',
				'fields'     => 'all',
				'search'     => $term,
				'taxonomy'   => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
				'hide_empty' => false,
			);

			$vendors_obj = get_terms( $args );
			$vendors     = array();
			foreach ( $vendors_obj as $vendor ) {
				$vendors[ $vendor->term_id ] = $vendor->name;
			}

			return $vendors;
		}
	}
}
