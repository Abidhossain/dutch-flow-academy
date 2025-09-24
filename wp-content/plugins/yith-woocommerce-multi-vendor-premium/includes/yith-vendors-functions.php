<?php
/**
 * YITH Vendors functions and utils
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 1.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'YITH_Vendors_Gateway' ) ) {
	/**
	 * Get the single instance of YITH_Vendors_Gateway class
	 *
	 * @since  4.0.0
	 * @param string $gateway The gateway slug to load.
	 * @return YITH_Vendors_Gateway Single instance of the class.
	 * @deprecated
	 */
	function YITH_Vendors_Gateway( $gateway ) { // phpcs:ignore
		_deprecated_function( __FUNCTION__, '4.0.0', 'YITH_Vendors_Gateways::get_gateway( $gateway )' );
		return class_exists( 'YITH_Vendors_Gateways' ) ? YITH_Vendors_Gateways::get_gateway( $gateway ) : null;
	}
}

if ( ! function_exists( 'YITH_Vendors_Gateways' ) ) {
	/**
	 * Get the single instance of YITH_Vendors_Gateways class
	 *
	 * @since  4.0.0
	 * @return YITH_Vendors_Gateways Single instance of the class.
	 */
	function YITH_Vendors_Gateways() { // phpcs:ignore
		return class_exists( 'YITH_Vendors_Gateways' ) ? YITH_Vendors_Gateways::get_instance() : null;
	}
}

if ( ! function_exists( 'yith_wcmv_get_template' ) ) {
	/**
	 * Get Plugin Template
	 * It's possible to overwrite the template from theme.
	 * Put your custom template in woocommerce/product-vendors folder
	 *
	 * @since 1.0
	 * @param string $filename The filename template to load.
	 * @param array  $args     An array of arguments.
	 * @param string $section  Section path.
	 * @return void
	 */
	function yith_wcmv_get_template( $filename, $args = array(), $section = '' ) {

		$ext           = false === strpos( $filename, '.php' ) ? '.php' : '';
		$template_name = $section . '/' . $filename . $ext;
		$template_path = WC()->template_path();
		$default_path  = YITH_WPV_TEMPLATE_PATH;

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}
}

if ( ! function_exists( 'yith_wcmv_check_duplicate_term_name' ) ) {
	/**
	 * Check for duplicate vendor name
	 *
	 * @since    1.0
	 * @param string $term     The term name.
	 * @param string $taxonomy (Optional) The taxonomy name. Default vendor taxonomy.
	 * @return mixed term object | WP_Error
	 */
	function yith_wcmv_check_duplicate_term_name( $term, $taxonomy = '' ) {

		if ( apply_filters( 'yith_wcmv_skip_check_duplicate_term_name', false ) ) {
			return false;
		}

		if ( empty( $taxonomy ) ) {
			$taxonomy = YITH_Vendors_Taxonomy::TAXONOMY_NAME;
		}

		$duplicate = get_term_by( 'name', $term, $taxonomy );

		return $duplicate instanceof WP_Term;
	}
}

if ( ! function_exists( 'yith_wcmv_create_capabilities' ) ) {
	/**
	 * Create a capability array
	 *
	 * @since  1.0
	 * @return array
	 */
	function yith_wcmv_create_capabilities( $capability_type ) {
		if ( ! is_array( $capability_type ) ) {
			$capability_type = array( $capability_type, $capability_type . 's' );
		}

		list( $singular_base, $plural_base ) = $capability_type;

		$capabilities = array(
			'edit_' . $singular_base           => true,
			'read_' . $singular_base           => true,
			'delete_' . $singular_base         => true,
			'edit_' . $plural_base             => true,
			'edit_others_' . $plural_base      => true,
			'publish_' . $plural_base          => true,
			'read_private_' . $plural_base     => true,
			'delete_' . $plural_base           => true,
			'delete_private_' . $plural_base   => true,
			'delete_published_' . $plural_base => true,
			'delete_others_' . $plural_base    => true,
			'edit_private_' . $plural_base     => true,
			'edit_published_' . $plural_base   => true,
		);

		return $capabilities;
	}
}

if ( ! function_exists( 'yith_wcmv_get_email_order_number' ) ) {
	/**
	 * Get order number for email
	 *
	 * @since    1.12
	 * @param WC_Order|boolean $order  The order object.
	 * @param boolean          $parent Get the order parent.
	 * @return  string
	 */
	function yith_wcmv_get_email_order_number( $order, $parent = false ) {
		$order_number = '';

		if ( $order instanceof WC_Order ) {
			if ( $parent ) {
				$order_id        = $order->get_id();
				$parent_order_id = $order->get_parent_id();

				$parent_order_id = ! empty( $parent_order_id ) ? $parent_order_id : $order_id;
				$parent_order    = wc_get_order( $parent_order_id );
				if ( $parent_order instanceof WC_Order ) {
					$order_number = $parent_order->get_order_number();
				}
			} else {
				$order_number = $order->get_order_number();
			}
		}

		return $order_number;
	}
}

if ( ! function_exists( 'yith_wcmv_get_font_awesome_icons' ) ) {
	/**
	 * Get the correct classes for font awesome icons with Font Awesome 5 or greather
	 *
	 * @return array Font awesome classes.
	 */
	function yith_wcmv_get_font_awesome_icons() {
		return apply_filters(
			'yith_wcmv_header_icons_class',
			array(
				'rating'      => 'yith-wcmv-icon__star',
				'sales'       => 'yith-wcmv-icon__chart-bar',
				'vat'         => 'yith-wcmv-icon__doc',
				'legal_notes' => 'yith-wcmv-icon__hammer',
				'website'     => 'yith-wcmv-icon__link',
				'location'    => 'yith-wcmv-icon__location',
				'telephone'   => 'yith-wcmv-icon__phone',
				'store_email' => 'yith-wcmv-icon__mail',
			)
		);
	}
}

if ( ! function_exists( 'yith_wcmv_string_is_url' ) ) {
	/**
	 * Check if current string is a valid URL
	 *
	 * @since  3.4.0
	 * @param string $url The url to check.
	 * @return boolean true on success, false otherwise.
	 */
	function yith_wcmv_string_is_url( $url ) {
		$pattern = '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';

		return preg_match( $pattern, $url );
	}
}

if ( ! function_exists( 'yith_wcmv_get_vendor_statuses' ) ) {
	/**
	 * Get all vendor statuses.
	 *
	 * @since 5.0.0
	 * @return array
	 */
	function yith_wcmv_get_vendor_statuses() {
		return apply_filters(
			'yith_wcmv_get_vendor_statuses',
			array(

				'enabled'  => _x( 'Enabled', 'Vendor status', 'yith-woocommerce-product-vendors' ),
				'disabled' => _x( 'Disabled', 'Vendor status', 'yith-woocommerce-product-vendors' ),
				'rejected' => _x( 'Rejected', 'Vendor status', 'yith-woocommerce-product-vendors' ),
				'pending'  => _x( 'Pending', 'Vendor status', 'yith-woocommerce-product-vendors' ),
			)
		);
	}
}

if ( ! function_exists( 'yith_wcmv_get_vendors' ) ) {
	/**
	 * Get an array of vendors filtered by given params. Wrap for factory method.
	 *
	 * @since  4.0.0
	 * @param array $params (Optional) An array of query parameters.
	 * @return YITH_Vendor[]|integer[]
	 */
	function yith_wcmv_get_vendors( $params = array() ) {
		return apply_filters( 'yith_wcmv_get_vendors', YITH_Vendors_Factory::query( $params ), $params );
	}
}

if ( ! function_exists( 'yith_wcmv_count_vendors' ) ) {
	/**
	 * Returns a count of vendors filtered by given params. Wrap for factory method.
	 *
	 * @since  4.7.0
	 * @param array $params (Optional) An array of query parameters.
	 * @return integer
	 */
	function yith_wcmv_count_vendors( $params = array() ) {
		return apply_filters( 'yith_wcmv_count_vendors', YITH_Vendors_Factory::count( $params ), $params );
	}
}

if ( ! function_exists( 'yith_wcmv_get_vendor' ) ) {
	/**
	 * Get a vendor
	 *
	 * @since  4.0.0
	 * @param mixed  $vendor The vendor to retrieve.
	 * @param string $obj    Get the vendor from.
	 * @return YITH_Vendor
	 */
	function yith_wcmv_get_vendor( $vendor = false, $obj = 'vendor' ) {
		return YITH_Vendors_Factory::read( $vendor, $obj );
	}
}

if ( ! function_exists( 'yith_wcmv_delete_vendor' ) ) {
	/**
	 * Delete a vendor
	 *
	 * @since  4.0.0
	 * @param mixed $vendor_id The vendor to retrieve.
	 * @return boolean
	 */
	function yith_wcmv_delete_vendor( $vendor_id ) {
		return YITH_Vendors_Factory::delete( $vendor_id );
	}
}

if ( ! function_exists( 'yith_wcmv_get_commission_statuses' ) ) {
	/**
	 * Get all commission statuses.
	 *
	 * @since 5.0.0
	 * @param boolean $singular (Optional) True to get singular label, false to get plural. Default is false.
	 * @return array
	 */
	function yith_wcmv_get_commission_statuses( $singular = false ) {
		$count = $singular ? 1 : 0;
		return apply_filters(
			'yith_wcmv_get_commissions_status',
			array(
				'paid'       => _nx( 'Paid', 'Paid', $count, 'Commission status singular/plural', 'yith-woocommerce-product-vendors' ),
				'unpaid'     => _nx( 'Unpaid', 'Unpaid', $count, 'Commission status singular/plural', 'yith-woocommerce-product-vendors' ),
				'pending'    => _nx( 'Pending', 'Pending', $count, 'Commission status singular/plural', 'yith-woocommerce-product-vendors' ),
				'refunded'   => _nx( 'Refunded', 'Refunded', $count, 'Commission status singular/plural', 'yith-woocommerce-product-vendors' ),
				'cancelled'  => _nx( 'Canceled', 'Canceled', $count, 'Commission status singular/plural', 'yith-woocommerce-product-vendors' ),
				'processing' => _nx( 'Processing', 'Processing', $count, 'Commission status singular/plural', 'yith-woocommerce-product-vendors' ),
			)
		);
	}
}

if ( ! function_exists( 'yith_wcmv_get_commission' ) ) {
	/**
	 * Get a vendor commission
	 *
	 * @since  4.0.0
	 * @param integer|string $commission_id The commission ID to retrieve.
	 * @return YITH_Vendors_Commission
	 */
	function yith_wcmv_get_commission( $commission_id = 0 ) {
		return YITH_Vendors_Commissions_Factory::read( $commission_id );
	}
}

if ( ! function_exists( 'yith_wcmv_get_commissions' ) ) {
	/**
	 * Get a vendor commission
	 *
	 * @since  4.0.0
	 * @param array $params (Optional) An array of query parameters.
	 * @return integer[]|YITH_Vendors_Commission[]
	 */
	function yith_wcmv_get_commissions( $params = array() ) {
		return apply_filters( 'yith_wcmv_get_commissions', YITH_Vendors_Commissions_Factory::query( $params ), $params );
	}
}

if ( ! function_exists( 'yith_wcmv_create_commission' ) ) {
	/**
	 * Create a vendor commission
	 *
	 * @since  4.0.0
	 * @param array $args The new commission data.
	 * @return integer|boolean
	 */
	function yith_wcmv_create_commission( $args = array() ) {
		// Let's filter commission args.
		$args = apply_filters( 'yith_wcmv_create_commission_args', $args );
		return YITH_Vendors_Commissions_Factory::create( $args );
	}
}

if ( ! function_exists( 'yith_wcmv_is_admin_request' ) ) {
	/**
	 * Is an admin request?
	 *
	 * @since  4.0.0
	 * @return boolean
	 */
	function yith_wcmv_is_admin_request() {
		$is_admin = is_admin() || ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && isset( $_REQUEST['context'] ) && 'admin' === sanitize_text_field( wp_unslash( $_REQUEST['context'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return apply_filters( 'yith_wcmv_is_admin_request', $is_admin );
	}
}

if ( ! function_exists( 'yith_wcmv_is_frontend_request' ) ) {
	/**
	 * Is a frontend request?
	 *
	 * @since  4.0.0
	 * @return boolean
	 */
	function yith_wcmv_is_frontend_request() {
		$is_admin = ! is_admin() || ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && isset( $_REQUEST['context'] ) && 'frontend' === sanitize_text_field( wp_unslash( $_REQUEST['context'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return apply_filters( 'yith_wcmv_is_frontend_request', $is_admin );
	}
}

if ( ! function_exists( 'yith_wcmv_is_vendor_page' ) ) {
	/**
	 * Check if the user see a store vendor page
	 *
	 * @since  4.0.0
	 * @return bool
	 */
	function yith_wcmv_is_vendor_page() {
		return apply_filters( 'yith_wcmv_is_vendor_page', is_tax( YITH_Vendors_Taxonomy::TAXONOMY_NAME ) );
	}
}

if ( ! function_exists( 'yith_wcmv_doing_it_wrong' ) ) {
	/**
	 * Wrapper for wc_doing_it_wrong().
	 *
	 * @since  3.0.0
	 * @param string $function Function used.
	 * @param string $message  Message to log.
	 * @param string $version  Version the message was added in.
	 */
	function yith_wcmv_doing_it_wrong( $function, $message, $version ) {
		if ( function_exists( 'wc_doing_it_wrong' ) ) {
			wc_doing_it_wrong( $function, $message, $version );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
	}
}

if ( ! function_exists( 'yith_wcmv_get_vendor_id_for_order' ) ) {
	/**
	 * Get vendor ID associated with the given order.
	 *
	 * @since  4.0.0
	 * @param WC_Order $order The order to check.
	 * @return boolean|integer The vendor ID, false otherwise.
	 */
	function yith_wcmv_get_vendor_id_for_order( $order ) {

		if ( $order instanceof WC_Order ) {
			$vendor_id = $order->get_meta( 'vendor_id' );
			if ( empty( $vendor_id ) ) {
				// Backward compatibility for owner set as post_author.
				$owner_id = get_post_field( 'post_author', $order->get_id() );
				$vendor   = $owner_id ? yith_wcmv_get_vendor( $owner_id, 'user' ) : false;
				if ( $vendor && $vendor->is_valid() ) {
					$vendor_id = $vendor->get_id();
				}
			}
		} else {
			$vendor_id = null;
		}

		return $vendor_id;
	}
}

if ( ! function_exists( 'yith_wcmv_get_user_meta_key' ) ) {
	/**
	 * Return the user meta key
	 *
	 * @since  4.0.0
	 * @return string The protected attribute User Meta Key
	 */
	function yith_wcmv_get_user_meta_key() {
		$meta_key = 'yith_product_vendor';
		if ( is_multisite() && 1 !== get_current_blog_id() ) {
			$meta_key = $meta_key . '_' . get_current_blog_id();
		}
		return $meta_key;
	}
}

if ( ! function_exists( 'yith_wcmv_get_user_meta_owner' ) ) {
	/**
	 * Return the user meta key
	 *
	 * @since  4.0.0
	 * @return string The protected attribute User Meta Key
	 */
	function yith_wcmv_get_user_meta_owner() {
		$meta_key = 'yith_product_vendor_owner';
		if ( is_multisite() && 1 !== get_current_blog_id() ) {
			$meta_key = $meta_key . '_' . get_current_blog_id();
		}
		return $meta_key;
	}
}

if ( ! function_exists( 'yith_wcmv_get_base_commission' ) ) {
	/**
	 * Get the vendor commission
	 *
	 * @since  4.0.0
	 * @return float|string The vendor commission.
	 */
	function yith_wcmv_get_base_commission() {
		$base = floatval( get_option( 'yith_vendor_base_commission', 50 ) );
		return apply_filters( 'yith_wcvm_get_base_commission', $base );
	}
}

if ( ! function_exists( 'yith_wcmv_get_base_commission_formatted' ) ) {
	/**
	 * Get the vendor commission
	 *
	 * @since  4.0.0
	 * @return float|string The vendor commission.
	 */
	function yith_wcmv_get_base_commission_formatted() {
		$commission = yith_wcmv_get_base_commission();
		return apply_filters( 'yith_wcvm_get_base_commission_formatted', "{$commission}%", $commission );
	}
}

if ( ! function_exists( 'yith_wcmv_format_price' ) ) {
	/**
	 * Format the price with a currency symbol.
	 *
	 * @param float $price Raw price.
	 * @return string
	 */
	function yith_wcmv_format_price( $price ) {
		// Convert to float to avoid issues on PHP 8.
		$price = number_format( (float) $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );

		return sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $price );
	}
}

if ( ! function_exists( 'yith_wcmv_sanitize_custom_meta_key' ) ) {
	/**
	 * Sanitizes a string meta key.
	 * Lowercase alphanumeric characters and underscores are allowed.
	 *
	 * @since  4.0.0
	 * @param string $key String key.
	 * @return string Sanitized key.
	 */
	function yith_wcmv_sanitize_custom_meta_key( $key ) {
		$sanitized_key = '';
		if ( is_scalar( $key ) ) {
			$sanitized_key = str_replace( '-', '_', strtolower( $key ) );
			// Prepend cm_ to prevent possible conflict with default meta.
			$sanitized_key = 'cm_' . preg_replace( '/[^a-z0-9_]/', '', $sanitized_key );
		}

		/**
		 * Filters a sanitized key string.
		 *
		 * @since  4.0.0
		 * @param string $sanitized_key Sanitized key.
		 * @param string $key           The key prior to sanitization.
		 */
		return apply_filters( 'yith_wcmv_sanitize_custom_meta_key', $sanitized_key, $key );
	}
}

if ( ! function_exists( 'yith_wcmv_is_terms_and_conditions_required' ) ) {
	/**
	 * Return if terms and conditions is required or not
	 *
	 * @since  5.0.0
	 * @return bool
	 */
	function yith_wcmv_is_terms_and_conditions_required() {
		return 'yes' === get_option( 'yith_wpv_vendors_registration_required_terms_and_conditions', 'no' );
	}
}

if ( ! function_exists( 'yith_wcmv_is_privacy_policy_required' ) ) {
	/**
	 * Check if privacy policy is required for vendors.
	 *
	 * @since 5.0.0
	 * @return bool
	 */
	function yith_wcmv_is_privacy_policy_required() {
		return 'yes' === get_option( 'yith_wpv_vendors_registration_required_privacy_policy', 'no' );
	}
}
