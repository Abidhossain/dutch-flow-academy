<?php
/**
 * A collection of deprecated YITH Vendors functions and utils
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 1.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'yith_wcpv_get_template' ) ) {
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
	 * @deprecated
	 */
	function yith_wcpv_get_template( $filename, $args = array(), $section = '' ) {
		_deprecated_function( 'yith_wcpv_get_template', '4.0.0', 'yith_wcmv_get_template' );
		yith_wcmv_get_template( $filename, $args, $section );
	}
}

if ( ! function_exists( 'yith_wcpv_check_duplicate_term_name' ) ) {
	/**
	 * Check for duplicate vendor name
	 *
	 * @since    1.0
	 * @param $taxonomy string The taxonomy name
	 * @param $term     string The term name
	 * @return mixed term object | WP_Error
	 * @deprecated
	 */
	function yith_wcpv_check_duplicate_term_name( $term, $taxonomy ) {
		_deprecated_function( 'yith_wcpv_check_duplicate_term_name', '4.0.0', 'yith_wcmv_check_duplicate_term_name' );
		yith_wcmv_check_duplicate_term_name( $term, $taxonomy );
	}
}

if ( ! function_exists( 'yith_wcmv_get_order_status' ) ) {
	/**
	 * Get the order status for retro compatibility
	 *
	 * @param WC_Order $order   The order object.
	 * @param string   $context The context.
	 * @return string
	 * @deprecated
	 */
	function yith_wcmv_get_order_status( $order, $context = 'edit' ) {
		_deprecated_function( 'yith_wcmv_get_order_status', '4.0.0', 'wc_get_order_status_name' );

		return wc_get_order_status_name( $order->get_status( $context ) );
	}
}

if ( ! function_exists( 'yith_wcmv_get_order_currency' ) ) {
	/**
	 * Get the order currency for retro compatibility
	 *
	 * @param WC_Order $order The order object.
	 * @return string
	 * @deprecated
	 */
	function yith_wcmv_get_order_currency( $order ) {
		_deprecated_function( 'yith_wcmv_get_order_currency', '4.0.0', 'order->get_currency' );

		return $order->get_currency();
	}
}

if ( ! function_exists( 'yith_wcmv_get_meta_field' ) ) {
	/**
	 * get meta fields wrapper for wc 2.6 or lower
	 *
	 * @param $meta
	 * @return  array meta order value
	 * @deprecated
	 */
	function yith_wcmv_get_meta_field( $meta ) {
		_deprecated_function( 'yith_wcmv_get_meta_field', '4.0.0' );

		return array(
			'meta_id'    => $meta->id,
			'meta_key'   => $meta->key,
			'meta_value' => $meta->value,
		);
	}
}

if ( ! function_exists( 'yith_get_vendor' ) ) {
	/**
	 * Get a vendor
	 *
	 * @since      1.0.0
	 * @param mixed  $vendor
	 * @param string $obj
	 * @return YITH_Vendor
	 * @deprecated Use instead yith_wcmv_get_vendor
	 */
	function yith_get_vendor( $vendor = false, $obj = 'vendor' ) {
		return yith_wcmv_get_vendor( $vendor, $obj );
	}
}

if ( ! function_exists( 'yith_get_vendors' ) ) {
	/**
	 * Get an array of vendors filtered by given params
	 *
	 * @since      4.0.0
	 * @param array $args An array of query params.
	 * @return array
	 * @deprecated Use instead yith_wcmv_get_vendors
	 */
	function yith_get_vendors( $args ) {
		return yith_wcmv_get_vendors( $args );
	}
}

if ( ! function_exists( 'YITH_Vendor_Endpoints' ) ) {
	/**
	 * Get instance of class YITH_Vendor_Endpoints
	 *
	 * @since      1.0.0
	 * @deprecated Use instead YITH_Vendors()->frontend->endpoints
	 */
	function YITH_Vendor_Endpoints() {
		if ( ! empty( YITH_Vendors()->frontend ) && ! empty( YITH_Vendors()->frontend->endpoints ) ) {
			return YITH_Vendors()->frontend->endpoints;
		}

		return new YITH_Vendors_Frontend_Endpoints();
	}
}

if ( ! function_exists( 'yith_vendors_check_commissions_table' ) ) {
	/**
	 * Check if Commission tables are created.
	 *
	 * @since  1.0.0
	 * @return void
	 * @deprecated
	 */
	function yith_vendors_check_commissions_table() {
		_deprecated_function( 'yith_vendors_check_commissions_table', '4.0.0' );

		YITH_Vendors_Install::create_commissions_table();
		YITH_Vendors_Install::create_transaction_table();
	}
}

if ( ! function_exists( 'yith_wcmv_switch_back_redirection_url' ) ) {
	/**
	 * Fix redirect URL when a user switches to another user or switches back.
	 * If the administrator click on Switch Back in vendor's profile page
	 * we force to redirect it to default admin_url() to prevent to show message
	 * that he haven't permissions to see the current page.
	 * Please note: The vendor's settings page doesn't exists for administrator
	 *
	 * @since    3.2.2
	 * @use      This matches the WordPress core filter in wp-login.php. user-switching/user-switching.php at line 318
	 * @param string  $redirect_to           The redirect destination URL.
	 * @param string  $requested_redirect_to The requested redirect destination URL passed as a parameter.
	 * @param WP_User $new_user              The WP_User object for the user that's being switched to.
	 * @return   string Url to redirect.
	 */
	function yith_wcmv_switch_back_redirection_url( $redirect_to, $requested_redirect_to, $new_user ) {
		global $user_switching;
		if ( ! empty( $user_switching ) && class_exists( 'user_switching' ) && $user_switching instanceof user_switching ) {
			$is_vendor_settings_page     = strpos( $requested_redirect_to, 'yith_vendor_settings' ) !== false;
			$is_switch_to_olduser_action = isset( $_GET['action'] ) && 'switch_to_olduser' === sanitize_text_field( wp_unslash( $_GET['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$redirect_to                 = $is_switch_to_olduser_action && $is_vendor_settings_page ? admin_url() : $redirect_to;
		}

		return $redirect_to;
	}
}

if ( ! function_exists( 'yith_wcmv_is_premium' ) ) {
	/**
	 * Check if this is the premium version
	 *
	 * @since  1.0
	 * @return bool
	 */
	function yith_wcmv_is_premium() {
		return defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM;
	}
}

if ( ! function_exists( '__yith_wcmv_return_yes' ) ) {
	/**
	 * Return 'yes' string to change default value for panel options
	 *
	 * @return string 'yes' value
	 */
	function __yith_wcmv_return_yes() {
		return 'yes';
	}
}

if ( ! function_exists( 'yith_wcmv_get_wpml_vendor_id' ) ) {
	/**
	 *  WPML Support. Get original vendor id
	 *
	 * @since   1.11.2
	 * @param integer|string $vendor_id The vendor ID.
	 * @param string         $id_type   The vendor ID type ( original_language | current_language ).
	 * @return  string vendor id
	 */
	function yith_wcmv_get_wpml_vendor_id( $vendor_id, $id_type = 'original_language' ) {

		if ( ! apply_filters( 'wpml_setting', false, 'setup_complete' ) ) {
			return $vendor_id;
		}

		$type    = apply_filters( 'wpml_element_type', YITH_Vendors_Taxonomy::TAXONOMY_NAME );
		$trid    = apply_filters( 'wpml_element_trid', null, $vendor_id, $type );
		$vendors = apply_filters( 'wpml_get_element_translations', array(), $trid, $type );

		if ( 'original_language' == $id_type ) {
			foreach ( $vendors as $vendor ) {
				if ( isset( $vendor->original ) && $vendor->original ) {
					$vendor_id = $vendor->element_id;
				}
			}
		} elseif ( 'current_language' == $id_type ) {
			$current_language = apply_filters( 'wpml_current_language', '' );

			if ( ! empty( $vendors[ $current_language ] ) ) {
				$vendor    = $vendors[ $current_language ];
				$vendor_id = $vendor->element_id;
			}
		}

		return $vendor_id;
	}
}

if ( ! function_exists( 'yith_wcmv_aelia_get_order_exchange_rate' ) ) {
	/**
	 * Get an order exchange rate. Aelia compatibility.
	 *
	 * @param WC_Order $order The order object.
	 * @return float|int|string
	 * @deprecated
	 */
	function yith_wcmv_aelia_get_order_exchange_rate( $order ) {
		// Get the exchange rate directly. This meta is going to be available in an upcoming version of the Currency Switcher.
		$exchange_rate = $order->get_meta( '_base_currency_exchange_rate' );

		if ( ! is_numeric( $exchange_rate ) || ( $exchange_rate <= 0 ) ) {
			$order_total = $order->get_total();
			// Get order total in base currency.
			$order_total_base_currency = $order->get_meta( '_order_total_base_currency' );

			// Set a default exchange rate, in case any of the totals is not valid.
			// Here we return "1", implying that no currency conversion was performed.
			$exchange_rate = 1;

			// Ensure that we are dealing with two valid values.
			if ( ( $order_total > 0 ) && ( $order_total_base_currency > 0 ) ) {
				// Calculate the exchange rate from the two totals.
				$exchange_rate = $order_total_base_currency / $order_total;
			}
		}

		return $exchange_rate;
	}
}

if ( ! function_exists( 'yith_wcmv_show_gravatar' ) ) {
	/**
	 * Show avatar or not in frontend vendor page
	 *
	 * @param null|YITH_Vendor $vendor The vendor object or null.
	 * @param string           $where  Avatar location.
	 * @return bool
	 * @deprecated
	 */
	function yith_wcmv_show_gravatar( $vendor = null, $where = 'admin' ) {
		$show_gravatar = false;

		switch ( get_option( 'yith_wpv_show_vendor_gravatar', 'enabled' ) ) {
			case 'enabled':
				$show_gravatar = true;
				break;

			case 'vendor':
				if ( 'admin' === $where ) {
					$show_gravatar = true;
				} else {
					if ( ! $vendor ) {
						$vendor = yith_wcmv_get_vendor( 'current', 'user' );
					}
					$show_gravatar = ( $vendor && $vendor->is_valid() && 'yes' === $vendor->get_meta( 'show_gravatar' ) );
				}
				break;
		}

		return $show_gravatar;
	}
}

if ( ! function_exists( 'yith_wcmv_flatsome_support' ) ) {
	/**
	 * Conditionally remove WooCommerce styles and/or scripts.
	 *
	 * @deprecated
	 */
	function yith_wcmv_flatsome_support() {
		// Remove default WooCommerce Lightbox.
		if ( get_theme_mod( 'product_lightbox', 'default' ) !== 'woocommerce' || ! is_product() ) {
			wp_dequeue_script( 'prettyPhoto-init' );
		}

		if ( ! is_admin() ) {
			wp_dequeue_style( 'woocommerce-layout' );
			wp_deregister_style( 'woocommerce-layout' );
			wp_dequeue_style( 'woocommerce-smallscreen' );
			wp_deregister_style( 'woocommerce-smallscreen' );
			wp_dequeue_style( 'woocommerce-general' );
			wp_deregister_style( 'woocommerce-general' );
		}
	}
}

if ( ! function_exists( 'yith_wcmv_get_registration_modal_form_fields' ) ) {
	/**
	 * Return an array of fields for the registration table modal form
	 *
	 * @since  4.0.0
	 * @return array
	 * @deprecated
	 */
	function yith_wcmv_get_registration_modal_form_fields() {
		return YITH_Vendors_Registration_Form::get_admin_modal_fields();
	}
}

if ( ! function_exists( 'yith_wcmv_get_vendor_registration_fields' ) ) {
	/**
	 * Get vendor registration form fields
	 *
	 * @since  4.0.0
	 * @return array
	 * @deprecated
	 */
	function yith_wcmv_get_vendor_registration_fields() {
		return YITH_Vendors_Registration_Form::get_fields();
	}
}

if ( ! function_exists( 'yith_wcmv_get_registration_modal_form_fields_default_values' ) ) {
	/**
	 * Return an array of fields for the registration table modal form and it's default value
	 *
	 * @since  4.0.0
	 * @return array
	 * @deprecated
	 */
	function yith_wcmv_get_registration_modal_form_fields_default_values() {
		return YITH_Vendors_Registration_Form::get_admin_modal_fields_default();
	}
}

if ( ! function_exists( 'yith_wcmv_get_vendor_registration_default_fields' ) ) {
	/**
	 * Get vendor registration default fields
	 *
	 * @since  4.0.0
	 * @return array
	 * @deprecated
	 */
	function yith_wcmv_get_vendor_registration_default_fields() {
		return YITH_Vendors_Registration_Form::get_default_fields();
	}
}

if ( ! function_exists( 'yith_wcmv_get_vendor_registration_fields_frontend' ) ) {
	/**
	 * Get vendor registration form fields frontend
	 *
	 * @since  4.0.0
	 * @return array
	 * @deprecated
	 */
	function yith_wcmv_get_vendor_registration_fields_frontend() {
		return YITH_Vendors_Registration_Form::get_fields_frontend();
	}
}

if ( ! function_exists( 'yith_wcmv_add_commissions_options' ) ) {
	/**
	 * Add  Stripe Connect General options for commissions
	 *
	 * @param array $options Array of commissions options.
	 * @return array Stripe Connect option array
	 * @deprecated
	 */
	function yith_wcmv_add_commissions_options( $options ) {
		$gateway = YITH_Vendors_Gateways::get_gateway( 'stripe-connect' );
		return ! is_null( $gateway ) ? $gateway->add_commissions_options( $options ) : $options;
	}
}

if ( ! function_exists( 'YITH_Vendor_Request_Quote' ) ) {
	/**
	 * Main instance of YITH_Vendors_Request_Quote class
	 *
	 * @since  1.9
	 * @return /YITH_Vendors_Request_Quote
	 */
	function YITH_Vendor_Request_Quote() { // phpcs:ignore
		return class_exists( 'YITH_Vendors_Request_Quote' ) ? YITH_Vendors_Request_Quote::instance() : null;
	}
}

if ( ! function_exists( 'YITH_Vendors_Coupons' ) ) {
	/**
	 * Main instance of plugin
	 *
	 * @since  4.0.0
	 * @return YITH_Vendors_Coupons
	 * @deprecated
	 */
	function YITH_Vendors_Coupons() { // phpcs:ignore
		return YITH_Vendors()->coupons;
	}
}

if ( ! function_exists( 'YITH_Vendors_Privacy_Premium' ) ) {
	/**
	 * Wrapper to get new YITH_Vendors_Privacy_Premium class instance
	 *
	 * @return YITH_Vendors_Privacy
	 * @deprecated
	 */
	function YITH_Vendors_Privacy_Premium() { // phpcs:ignore
		return YITH_Vendors_Privacy();
	}
}
