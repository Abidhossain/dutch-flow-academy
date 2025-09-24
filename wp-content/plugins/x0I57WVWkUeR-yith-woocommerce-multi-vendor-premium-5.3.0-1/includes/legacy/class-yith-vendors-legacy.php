<?php
/*
 * Legacy class for YITH Vendors. This class includes all deprecated methods and arguments that are going to be removed on future release.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Legacy' ) ) {
	/**
	 * Class YITH_Vendors
	 */
	abstract class YITH_Vendors_Legacy {

		/**
		 * User Meta Key
		 *
		 * @since  1.0
		 * @access protected
		 * @var string
		 */
		protected $user_meta_key = 'yith_product_vendor';

		/**
		 * User Meta Key
		 *
		 * @since  1.0
		 * @access protected
		 * @var string
		 */
		protected $user_meta_owner = 'yith_product_vendor_owner';

		/**
		 * Taxonomy handler Class
		 *
		 * @since 1.9.17
		 * @var YITH_Vendors_Taxonomy | null
		 */
		public $taxonomy = null;

		/**
		 * Main Shipping Class
		 *
		 * @since 1.9.17
		 * @var YITH_Vendors_Shipping
		 * @deprecated
		 */
		public $shipping = null;

		/**
		 * YITH_WCMV_Addons class instance
		 *
		 * @var YITH_Vendors_Modules_Handler
		 * @deprecated
		 */
		public $addons = null;

		/**
		 * YITH_Vendors_Gateways class instance
		 *
		 * @var YITH_Vendors_Gateways|null
		 * @deprecated
		 */
		public $gateways = null;

		/**
		 * Required classes
		 *
		 * @since 1.0
		 * @var array
		 */
		public $require = array(
			'admin'    => array(),
			'frontend' => array(),
			'common'   => array(),
		);

		/**
		 * Magic __get method
		 *
		 * @since 4.0.0
		 * @param string $key The key requested.
		 */
		public function __get( $key ) {

			switch ( $key ) {
				case 'termmeta_table':
					global $wpdb;
					return $wpdb->termmeta;

				case 'termmeta_term_id':
					return 'term_id';

				case 'is_wc_lower_2_6':
					return false;

				case 'gateways':
					return function_exists( 'YITH_Vendors_Gateways' ) ? YITH_Vendors_Gateways() : null;

				case 'shipping':
					return function_exists( 'YITH_Vendors_Shipping' ) ? YITH_Vendors_Shipping() : null;

				case 'addons':
					return YITH_Vendors_Modules_Handler::instance();
			}
		}

		/**
		 * Register taxonomy for vendors
		 *
		 * @since  1.0
		 * @return void
		 * @deprecated
		 */
		public function register_vendors_taxonomy() {
			_deprecated_function( __METHOD__, '4.0.0' );
			YITH_Vendors_Taxonomy::register_taxonomy();
		}

		/**
		 * Get the vendors taxonomy label
		 *
		 * @since  1.0.0
		 * @param string $arg The string to return. Default empty. If is empty return all taxonomy labels.
		 * @return Array The taxonomy label
		 * @deprecated
		 */
		public function get_vendors_taxonomy_label( $arg = '' ) {
			// _deprecated_function( __METHOD__, '4.0.0' );
			return YITH_Vendors_Taxonomy::get_taxonomy_labels( $arg );
		}

		/**
		 * Get the vendor singular label
		 *
		 * @param string $callback
		 * @return string
		 * @deprecated
		 */
		public function get_singular_label( $callback = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return YITH_Vendors_Taxonomy::get_singular_label( $callback );
		}

		/**
		 * Set the vendor singular label
		 *
		 * @param string $singular_label The vendor singular label.
		 * @return void
		 * @deprecated
		 */
		public function set_singular_label( $singular_label = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			YITH_Vendors_Taxonomy::set_singular_label( $singular_label );
		}

		/**
		 * Get the vendor  plural  label
		 *
		 * @param string $callback
		 * @return string
		 * @deprecated
		 */
		public function get_plural_label( $callback = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return YITH_Vendors_Taxonomy::get_plural_label( $callback );
		}

		/**
		 * Set the vendor plural label
		 *
		 * @param string $plural_label The vendor plural label.
		 * @return void
		 * @deprecated
		 */
		public function set_plural_label( $plural_label = '' ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			YITH_Vendors_Taxonomy::set_plural_label( $plural_label );
		}

		/**
		 * Update the term meta
		 *
		 * @since  4.0.0
		 * @param int    $term_id    Term ID.
		 * @param string $meta_key   Metadata key.
		 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
		 * @param mixed  $prev_value Optional. Previous value to check before updating.
		 * @return int|bool|WP_Error
		 * @deprecated
		 */
		public function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
			_deprecated_function( __METHOD__, '4.0.0', 'update_term_meta' );

			return update_term_meta( $term_id, $meta_key, $meta_value, $prev_value );
		}

		/**
		 * Delete the term meta
		 *
		 * @since  4.0.0
		 * @param int    $term_id    Term ID.
		 * @param string $meta_key   Metadata name.
		 * @param mixed  $meta_value Optional. Metadata value. If provided,
		 *                           rows will only be removed that match the value.
		 *                           Must be serializable if non-scalar. Default empty.
		 * @return bool True on success, false on failure.
		 * @deprecated
		 */
		public function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
			_deprecated_function( __METHOD__, '4.0.0', 'delete_term_meta' );

			return delete_term_meta( $term_id, $meta_key, $meta_value );
		}

		/**
		 * Add the term meta
		 *
		 * @since  4.0.0
		 * @param int    $term_id    Term ID.
		 * @param string $meta_key   Metadata name.
		 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
		 * @param bool   $unique     Optional. Whether the same key should not be added. Default false.
		 * @return int|false|WP_Error
		 * @deprecated
		 */
		public function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
			_deprecated_function( __METHOD__, '4.0.0', 'add_term_meta' );

			return add_term_meta( $term_id, $meta_key, $meta_value, $unique );
		}

		/**
		 * Get the term meta
		 *
		 * @since  4.0.0
		 * @param int    $term_id Term ID.
		 * @param string $key     The meta key to retrieve.
		 * @param bool   $single  Optional. Whether to return a single value. Default true.
		 * @return mixed
		 * @deprecated
		 */
		public function get_term_meta( $term_id, $key, $single = true ) {
			_deprecated_function( __METHOD__, '4.0.0', 'get_term_meta' );

			return get_term_meta( $term_id, $key, $single );
		}

		/**
		 * Select the termeta table.
		 * The table woocommerce_termeta was removed in WooCommerce 2.6
		 *
		 * @since  1.9.8
		 * @return void
		 * @deprecated
		 */
		public function select_termmeta_table() {
			_deprecated_function( __METHOD__, '4.0.0' );
		}

		/**
		 * Get the protected attribute taxonomy name
		 *
		 * @since  1.0.0
		 * @return string The taxonomy name
		 * @deprecated
		 */
		public function get_taxonomy_name() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Taxonomy::TAXONOMY_NAME' );
			return YITH_Vendors_Taxonomy::TAXONOMY_NAME;
		}

		/**
		 * Add Vendor Role.
		 *
		 * @fire   register_activation_hook
		 * @since  1.6.5
		 * @return void
		 * @deprecated
		 */
		public static function add_vendor_role() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Capabilities::add_role' );
			YITH_Vendors_Capabilities::add_role();
		}

		/**
		 * Remove Vendor Role.
		 *
		 * @fire   register_deactivation_hook
		 * @since  1.6.5
		 * @return void
		 * @deprecated
		 */
		public static function remove_vendor_role() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Capabilities::remove_role' );
			YITH_Vendors_Capabilities::remove_role();
		}

		/**
		 * Set up array of vendor admin capabilities
		 *
		 * @since  1.0
		 * @return array Vendor capabilities
		 * @deprecated
		 */
		public function vendor_enabled_capabilities() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Capabilities::get_capabilities' );
			return YITH_Vendors_Capabilities::get_capabilities();
		}

		/**
		 * Get protected attribute role_name
		 *
		 * @since  1.6.5
		 * @return string
		 * @deprecated
		 */
		public function get_role_name() {
			_deprecated_function( __METHOD__, '4.0.0', 'const YITH_Vendors_Capabilities::ROLE_NAME' );
			return YITH_Vendors_Capabilities::ROLE_NAME;
		}

		/**
		 * Plugin Setup
		 *
		 * @fire   register_activation_hook
		 * @since  1.6.5
		 * @param string $method
		 * @return void
		 * @deprecated
		 */
		public static function setup( $method = '' ) {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Capabilities::setup' );
			YITH_Vendors_Capabilities::setup( $method );
		}

		/**
		 * Get vendors list
		 *
		 * @since  1.0
		 * @param array $args
		 * @return Array Vendor Objects
		 * @deprecated
		 */
		public function get_vendors( $args = array() ) {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_get_vendors' );
			return yith_wcmv_get_vendors( $args );
		}

		/**
		 * Load plugin modules
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function load_admin_modules() {
			_deprecated_function( __METHOD__, '4.0.0' );

			$required = array();

			// WooCommerce Customer/Order CSV Export.
			if ( function_exists( 'wc_customer_order_csv_export' ) ) {
				$required['admin'][] = 'includes/modules/module.yith-wc-customer-order-export-support.php';
			}

			! empty( $required ) && $this->load_required( $required );
		}

		/**
		 * Remove new post and comments wp bar admin menu for vendor
		 *
		 * @since  1.5.1
		 * @return void
		 */
		public function remove_wp_bar_admin_menu() {
			_deprecated_function( __METHOD__, '4.0.0' );

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );

			if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
				remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
				remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 70 );
			}
		}

		/**
		 * Return if PayPal Email is required or not
		 *
		 * @since  1.7
		 * @return bool
		 * @deprecated
		 */
		public function is_paypal_email_enabled() {
			_deprecated_function( __METHOD__, '5.0.0' );
			return 'yes' === get_option( 'yith_wpv_vendors_registration_show_paypal_email', 'yes' );
		}

		/**
		 * Return if VAT/SSN is required or not
		 *
		 * @since  1.7
		 * @return bool
		 * @deprecated
		 */
		public function is_vat_require() {
			_deprecated_function( __METHOD__, '5.0.0' );
			return 'yes' === get_option( 'yith_wpv_vendors_my_account_required_vat', 'no' );
		}

		/**
		 * Return if terms and conditions is required or not
		 *
		 * @since  1.7
		 * @return bool
		 * @deprecated
		 */
		public function is_terms_and_conditions_require() {
			_deprecated_function( __METHOD__, '5.0.0', 'Use function yith_wcmv_is_terms_and_conditions_required' );
			return yith_wcmv_is_terms_and_conditions_required();
		}

		/**
		 * Check if privacy policy is required for vendors.
		 *
		 * @return bool
		 * @deprecated
		 */
		public function is_privacy_policy_require() {
			_deprecated_function( __METHOD__, '5.0.0', 'Use function yith_wcmv_is_privacy_policy_required' );
			return yith_wcmv_is_privacy_policy_required();
		}

		/**
		 * Return if PayPal Email is required or not
		 *
		 * @since  1.7
		 * @return string
		 * @deprecated
		 */
		public function is_paypal_email_required() {
			_deprecated_function( __METHOD__, '5.0.0' );
			return $this->is_paypal_email_enabled() ? 'yes' === get_option( 'yith_wpv_vendors_registration_required_paypal_email', 'no' ) : false;
		}

		/**
		 * Locate core template file
		 *
		 * @since  1.0
		 * @param $core_file
		 * @param $template
		 * @param $template_base
		 * @return array Vendor capabilities
		 */
		public function locate_core_template( $core_file, $template, $template_base ) {
			$custom_template = array(
				// HTML Email
				'emails/commissions-paid.php',
				'emails/commissions-unpaid.php',
				'emails/vendor-commissions-paid.php',
				'emails/new-vendor-registration.php',
				'emails/vendor-new-account.php',
				'emails/vendor-new-order.php',
				'emails/vendor-cancelled-order.php',
				'emails/commissions-bulk.php',

				// Plain Email
				'emails/plain/commissions-paid.php',
				'emails/plain/commissions-unpaid.php',
				'emails/plain/vendor-commissions-paid.php',
				'emails/plain/new-vendor-registration.php',
				'emails/plain/vendor-new-account.php',
				'emails/plain/vendor-new-order.php',
				'emails/plain/vendor-cancelled-order.php',
				'emails/plain/commissions-bulk.php',
			);

			if ( in_array( $template, $custom_template ) ) {
				$core_file = YITH_WPV_TEMPLATE_PATH . $template;
			}

			return $core_file;
		}

		/**
		 * Save extra taxonomy fields for product vendors taxonomy
		 *
		 * @since  1.0
		 * @param float       $commission The vendor commission.
		 * @param integer     $vendor_id  The vendor id.
		 * @param YITH_Vendor $vendor     The vendor instance.
		 * @param integer     $product_id The product id.
		 * @return string The vendor commissions
		 * @deprecated
		 */
		public function get_commission( $commission, $vendor_id, $vendor, $product_id ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			return $commission;
		}

		/**
		 * Gets the message of the privacy to display.
		 * To be overloaded by the implementor.
		 *
		 * @return string
		 * @deprecated
		 */
		public function get_privacy_message() {
			_deprecated_function( __METHOD__, '4.0.0' );
			$content = '
			<div contenteditable="false">' .
				'<p class="wp-policy-help">' .
				__( 'This sample language includes the basics around what personal data your store may be collecting, storing and sharing, as well as who may have access to that data. Depending on what settings are enabled and which additional plugins are used, the specific information shared by your store may vary. We recommend consulting with a lawyer when deciding what information to disclose on your Privacy Policy.', 'yith-woocommerce-product-vendors' ) .
				'</p>' .
				'</div>' .
				'<p>' . __( 'We collect information about you during the checkout process on our store.', 'yith-woocommerce-product-vendors' ) . '</p>' .
				'<h2>' . __( 'What we collect and store', 'yith-woocommerce-product-vendors' ) . '</h2>' .
				'<p>' . __( 'While you visit our site, we’ll track:', 'yith-woocommerce-product-vendors' ) . '</p>' .
				'<ul>' .
				'<li>' . __( 'Vendors data: we will use this information to create vendor profiles and allow them to sell their products on the site in exchange for a commission on sales. ', 'yith-woocommerce-product-vendors' ) . '</li>' .
				'<li>' . __( 'Data required to create a store: store name and description, header image, store logo, address, email address, phone number, VAT/SSN, legal notes, social network links (Facebook, Twitter, LinkedIn, YouTube, Vimeo, Instagram, Pinterest, Flickr, Behance, Tripadvisor), payment information (IBAN and/or PayPal email address), and information related to commissions and payments made.', 'yith-woocommerce-product-vendors' ) . '</li>' .
				'</ul>' .
				'<div contenteditable="false">' .
				'<h2>' . __( 'Who on our team has access', 'yith-woocommerce-product-vendors' ) . '</h2>' .
				'<p>' . __( 'Members of our team have access to the information you provide to us. For example, both Administrators and Shop Managers can access:', 'yith-woocommerce-product-vendors' ) . '</p>' .
				'<p>' . __( 'Our team members have access to this information to help fulfill orders, process refunds and support you.', 'yith-woocommerce-product-vendors' ) . '</p>' .
				'</div>';

			return $content;
		}

		/**
		 * Add or Remove  publish_products capabilities to vendor admins when global option change
		 *
		 * @since    1.0
		 * @param array $vendors An array of vendors.
		 * @return   void|string
		 * @deprecated
		 */
		public function force_skip_review_option( $vendors = array() ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json( 'complete' );
			}
		}

		/**
		 * Return the user meta key
		 *
		 * @since  1.0.0
		 * @return string The protected attribute User Meta Key
		 * @deprecated
		 */
		public function get_user_meta_key() {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_get_user_meta_key' );
			return yith_wcmv_get_user_meta_key();
		}

		/**
		 * Return the user meta key
		 *
		 * @since  1.0.0
		 * @return string The protected attribute User Meta Key
		 * @deprecated
		 */
		public function get_user_meta_owner() {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_get_user_meta_owner' );
			return yith_wcmv_get_user_meta_owner();
		}

		/**
		 * Get the vendor commission
		 *
		 * @since  1.0.0
		 * @return string The vendor commission.
		 * @deprecated
		 */
		public function get_base_commission() {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_get_base_commission' );
			return yith_wcmv_get_base_commission();
		}

		/**
		 * Loads WC Mailer when needed
		 *
		 * @since  1.0
		 * @return void
		 * @deprecated
		 */
		public function load_wc_mailer() {
			_deprecated_function( __METHOD__, '4.0.0' );
			add_action( 'yith_wcmv_vendor_account_approved', array( 'WC_Emails', 'send_transactional_email' ), 10 );
		}

		/**
		 * Register Emails for Vendors
		 *
		 * @since  1.0.0
		 * @param array $emails An array of registered emails.
		 * @return array
		 */
		public function register_emails( $emails ) {
			_deprecated_function( __METHOD__, '5.0.0' );
			return YITH_Vendors_Emails::instance()->register_emails( $emails );
		}

		/**
		 * Get the email vendor order table
		 *
		 * @param YITH_Vendor $vendor              Vendor object.
		 * @param WC_Order    $order               Order object.
		 * @param boolean     $show_download_links (Optional) True to show item download link, false otherwise. Default false.
		 * @param boolean     $show_sku            (Optional) True to show item sku, false otherwise. Default false.
		 * @param boolean     $show_purchase_note  (Optional) True to show purchase note, false otherwise. Default false.
		 * @param boolean     $show_image          (Optional) True to show item image, false otherwise. Default false.
		 * @param array       $image_size          (Optional) The item image size. Default array(32,32).
		 * @param boolean     $plain_text          (Optional) True if is a plain email, false otherwise. Default false.
		 * @return void
		 */
		public function email_order_items_table( $vendor, $order, $show_download_links = false, $show_sku = false, $show_purchase_note = false, $show_image = false, $image_size = array( 32, 32 ), $plain_text = false ) {
			_deprecated_function( __METHOD__, '5.0.0' );
			YITH_Vendors_Emails::instance()->email_order_items_table( $vendor, $order, $show_download_links, $show_sku, $show_purchase_note, $show_image, $image_size, $plain_text );
		}

		/**
		 * Register my account endpoint
		 *
		 * @since  4.0.0
		 * @param array $endpoints An array of WooCommerce endpoints.
		 * @return mixed
		 * @deprecated
		 */
		public function add_endpoint( $endpoints ) {
			_deprecated_function( __METHOD__, '5.0.0' );
			return YITH_Vendors_Frontend_Endpoints::register_endpoints( $endpoints );
		}

		/**
		 * Get my account endpoint slug
		 *
		 * @since  4.0.0
		 * @return string
		 * @deprecated
		 */
		public function get_account_endpoint() {
			_deprecated_function( __METHOD__, '5.0.0', 'Use instead YITH_Vendors_Frontend_Endpoints::get_endpoints()' );
			return apply_filters( 'yith_wcmv_terms_of_service_endpoint', 'terms-of-service' );
		}

		/**
		 * Load common plugin modules
		 *
		 * @since  5.0.0
		 * @return void
		 * @deprecated
		 */
		public function load_common_modules() {
			_deprecated_function( __METHOD__, '5.0.0' );
		}
	}
}
