<?php
/**
 * YITH Vendors Admin AJAX Class. Handle all admin AJAX request
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Ajax' ) ) {
	/**
	 * Handle all AJAX admin requests.
	 */
	class YITH_Vendors_Admin_Ajax {

		/**
		 * Admin panel page
		 *
		 * @const string
		 */
		const AJAX_ACTION = 'yith_wcmv_admin_ajax_action';

		/**
		 * Construct
		 */
		public function __construct() {
			add_action( 'admin_print_scripts', array( $this, 'add_script_data' ) );
			add_action( 'wp_ajax_yith_wcmv_admin_ajax_action', array( $this, 'handle_ajax' ) );
		}

		/**
		 * Enqueue admin script for AJAX request
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function add_script_data() {
			if ( ! yith_wcmv_is_plugin_panel() ) {
				return;
			}

			?>
			<script id="yith-wcmv-ajax-data">
				var yith_wcmv_ajax = {
					"ajaxUrl": "<?php echo esc_url( admin_url( 'admin-ajax.php', 'relative' ) ); ?>",
					"ajaxAction": "<?php echo esc_attr( self::AJAX_ACTION ); ?>",
					"ajaxNonce": "<?php echo esc_attr( wp_create_nonce( self::AJAX_ACTION ) ); ?>",
				};
			</script>
			<?php
		}

		/**
		 * Handle an admin ajax request
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_ajax() {
			check_ajax_referer( self::AJAX_ACTION, 'security' );

			$request = isset( $_REQUEST['request'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['request'] ) ) : '';
			$request = preg_replace( '/[^a-z_]/', '_', trim( strtolower( $request ) ) );

			if ( ! empty( $request ) ) {
				$method = 'handle_' . $request;
				if ( method_exists( $this, $method ) ) {
					$data = $this->$method();
					if ( empty( $data ) ) {
						$data = null;
					}

					wp_send_json_success( $data );
				}

				do_action( 'yith_wcmv_admin_ajax_' . $request );
			}

			wp_send_json_error();
		}

		/**
		 * Handle get single commission details
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_commission_details() {
			// phpcs:disable WordPress.Security.NonceVerification
			$commission_id = isset( $_GET['commission_id'] ) ? absint( $_GET['commission_id'] ) : 0;
			if ( ! $commission_id ) {
				wp_send_json_error();
			}

			// Get commission object.
			$commission = yith_wcmv_get_commission( $commission_id );
			$vendor     = yith_wcmv_get_vendor( 'current', 'user' );
			if ( ! $vendor || ! $commission || ( ! current_user_can( 'manage_woocommerce' ) && absint( $vendor->get_id() ) !== absint( $commission->get_vendor_id() ) ) ) {
				wp_send_json_error();
			}

			$data         = YITH_Vendors_Admin_Commissions::get_commission_details( $commission );
			$data['save'] = current_user_can( 'manage_woocommerce' );

			return $data;
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle change single commission status
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Error changing single commission status.
		 */
		protected function handle_commission_change_status() {
			// phpcs:disable WordPress.Security.NonceVerification
			$commission_id = isset( $_POST['commission_id'] ) ? absint( $_POST['commission_id'] ) : 0;
			$status        = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
			if ( ! $commission_id || ! $status || ! current_user_can( 'manage_woocommerce' ) ) {
				wp_send_json_error();
			}

			try {
				// Get commission object.
				$commission = yith_wcmv_get_commission( $commission_id );
				if ( ! $commission || ! $commission->update_status( $status ) ) {
					// translators: %s could be a single commission id or a list of ids comma separated.
					throw new Exception( sprintf( __( 'Error changing status for commission <b>#%s</b>.', 'yith-woocommerce-product-vendors' ), $commission_id ) );
				}

				YITH_Vendors_Admin_Notices::add( __( 'Commission status changed.', 'yith-woocommerce-product-vendors' ) );

			} catch ( Exception $e ) {
				YITH_Vendors_Admin_Notices::add( $e->getMessage(), 'error' );
				wp_send_json_error();
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle switch gateway enabled
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_switch_gateway_enabled() {
			// phpcs:disable WordPress.Security.NonceVerification
			$gateway_id = isset( $_GET['gateway_id'] ) ? sanitize_text_field( wp_unslash( $_GET['gateway_id'] ) ) : '';
			$enabled    = isset( $_GET['gateway_enabled'] ) && 'yes' === sanitize_text_field( wp_unslash( $_GET['gateway_enabled'] ) );
			if ( ! $gateway_id || ! array_key_exists( $gateway_id, YITH_Vendors_Gateways::get_available_gateways() ) ) {
				wp_send_json_error();
			}

			$gateway = YITH_Vendors_Gateways::get_gateway( $gateway_id );
			if ( empty( $gateway ) || ! $gateway->switch_enabled( $enabled ) ) {
				wp_send_json_error();
			}

			// On success, return empty data.
			return array();
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle switch gateway enabled
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_save_gateway_options() {
			// phpcs:disable WordPress.Security.NonceVerification
			$gateway_id = isset( $_POST['gateway_id'] ) ? sanitize_text_field( wp_unslash( $_POST['gateway_id'] ) ) : '';
			if ( ! $gateway_id || ! array_key_exists( $gateway_id, YITH_Vendors_Gateways::get_available_gateways() ) ) {
				wp_send_json_error();
			}

			$gateway = YITH_Vendors_Gateways::get_gateway( $gateway_id );
			if ( empty( $gateway ) ) {
				wp_send_json_error();
			}

			$options = $gateway->get_options();
			foreach ( $options as $option ) {
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$id   = $option['id'];
				$type = isset( $option['type'] ) ? $option['type'] : 'text';
				if ( 'checkbox' === $type || 'onoff' === $type ) {
					$value = isset( $_POST[ $id ] ) ? 'yes' : 'no';
				} else {
					$value = isset( $_POST[ $id ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id ] ) ) : '';
				}

				update_option( $id, $value );
			}

			do_action( "yith_wcmv_updated_gateway_{$gateway_id}_options" );

			// On success, return empty data.
			return array();
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle search owner to assign to a new vendor.
		 * This is basically an user search but excluding users that are already owner
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_search_for_owner() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				wp_die( -1 );
			}

			$term  = isset( $_GET['term'] ) ? (string) wc_clean( wp_unslash( $_GET['term'] ) ) : '';
			$limit = 0;

			if ( empty( $term ) ) {
				wp_send_json_error();
			}

			// Start getting users that are already owner.
			$excluded = get_users(
				array(
					'fields'       => 'ids',
					'meta_key'     => yith_wcmv_get_user_meta_key(),
					'meta_compare' => 'EXISTS',
				)
			);

			$ids = array();
			// Search by ID.
			if ( is_numeric( $term ) && ! in_array( $term, $excluded ) ) {
				$customer = new WC_Customer( intval( $term ) );
				// Customer does not exists.
				if ( 0 !== $customer->get_id() ) {
					$ids = array( $customer->get_id() );
				}
			}

			// Usernames can be numeric so we first check that no users was found by ID before searching for numeric username, this prevents performance issues with ID lookups.
			if ( empty( $ids ) ) {
				$data_store = WC_Data_Store::load( 'customer' );
				// If search is smaller than 3 characters, limit result set to avoid
				// too many rows being returned.
				if ( 3 > strlen( $term ) ) {
					$limit = 20;
				}
				$ids = $data_store->search_customers( $term, $limit );
			}

			$found = array();
			// Exclude owners.
			$ids = array_diff( $ids, array_map( 'absint', (array) $excluded ) );

			foreach ( $ids as $id ) {
				$customer = new WC_Customer( $id );
				/* translators: 1: user display name 2: user ID 3: user email */
				$found[ $id ] = sprintf(
					esc_html( '%1$s (#%2$s &ndash; %3$s)' ),
					$customer->get_first_name() . ' ' . $customer->get_last_name(),
					$customer->get_id(),
					$customer->get_email()
				);
			}

			return apply_filters( 'yith_wcmv_json_search_found_users_for_owner', $found );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Create an owner for the vendor
		 *
		 * @since  4.0.0
		 * @return mixed
		 * @throws Exception Error creating vendor owner.
		 */
		protected function handle_create_owner() {
			// phpcs:disable WordPress.Security.NonceVerification
			try {

				$email    = ! empty( $_POST['new_owner_email'] ) ? wc_clean( wp_unslash( $_POST['new_owner_email'] ) ) : '';
				$username = ! empty( $_POST['new_owner_username'] ) ? wc_clean( wp_unslash( $_POST['new_owner_username'] ) ) : '';
				$password = ! empty( $_POST['new_owner_password'] ) ? wp_unslash( $_POST['new_owner_password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				// Start build new owner data.
				$new_owner_data = array(
					'first_name' => ! empty( $_POST['new_owner_first_name'] ) ? wc_clean( wp_unslash( $_POST['new_owner_first_name'] ) ) : '',
					'last_name'  => ! empty( $_POST['new_owner_last_name'] ) ? wc_clean( wp_unslash( $_POST['new_owner_last_name'] ) ) : '',
				);

				if ( empty( $email ) || ! is_email( $email ) ) {
					throw new Exception( _x( 'Please, enter a valid email address.', '[Admin]Registration owner error message', 'yith-woocommerce-product-vendors' ), 100 );
				}

				if ( email_exists( $email ) ) {
					throw new Exception( _x( 'An account with this email address already exists.', '[Admin]Registration owner error message', 'yith-woocommerce-product-vendors' ), 100 );
				}

				if ( 'yes' === get_option( 'woocommerce_registration_generate_username', 'yes' ) && empty( $username ) ) {
					$username = wc_create_new_customer_username( $email, $new_owner_data );
				}

				$username = sanitize_user( $username );
				if ( empty( $username ) || ! validate_username( $username ) ) {
					throw new Exception( _x( 'Please, enter a valid username.', '[Admin]Registration owner error message', 'yith-woocommerce-product-vendors' ), 200 );
				}

				if ( username_exists( $username ) ) {
					throw new Exception( _x( 'An account with that username already exists. Please, choose another username.', '[Admin]Registration owner error message', 'yith-woocommerce-product-vendors' ), 200 );
				}

				// Handle password creation.
				$password_generated = ! empty( $password );
				if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && empty( $password ) ) {
					$password           = wp_generate_password();
					$password_generated = true;
				}

				if ( empty( $password ) ) {
					throw new Exception( _x( 'Please enter an account password.', '[Admin]Registration owner error message', 'yith-woocommerce-product-vendors' ), 300 );
				}

				// New owner data. Set customer as starting role to prevent wrong role if vendor creation process failed.
				$new_owner_data = apply_filters(
					'woocommerce_new_customer_data',
					array_merge(
						$new_owner_data,
						array(
							'user_login' => $username,
							'user_pass'  => $password,
							'user_email' => $email,
							'role'       => 'customer',
						)
					)
				);

				$owner_id = wp_insert_user( $new_owner_data );
				if ( is_wp_error( $owner_id ) ) {
					throw new Exception( $owner_id->get_error_message() );
				}

				do_action( 'woocommerce_created_customer', $owner_id, $new_owner_data, $password_generated );

				$owner = new WC_Customer( $owner_id );

				return array(
					'name' => sprintf(
						esc_html( '%1$s (#%2$s &ndash; %3$s)' ),
						$owner->get_first_name() . ' ' . $owner->get_last_name(),
						$owner->get_id(),
						$owner->get_email()
					),
					'id'   => $owner->get_id(),
				);

			} catch ( Exception $e ) {

				$error_code = $e->getCode();
				$codes      = array(
					100 => 'new_owner_email',
					200 => 'new_owner_username',
					300 => 'new_owner_password',
				);

				wp_send_json_error(
					array(
						'message' => $e->getMessage(),
						'field'   => isset( $codes[ $error_code ] ) ? $codes[ $error_code ] : '',
					)
				);
			}

			return array();
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Validate a vendor name
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Error on validate vendor name.
		 */
		protected function handle_validate_vendor_name() {
			// phpcs:disable WordPress.Security.NonceVerification
			try {

				$name      = isset( $_GET['value'] ) ? sanitize_text_field( wp_unslash( $_GET['value'] ) ) : '';
				$vendor_id = isset( $_GET['vendor_id'] ) ? absint( $_GET['vendor_id'] ) : 0;

				if ( empty( $name ) ) {
					throw new Exception( _x( 'Store name cannot be empty!', '[Admin]Vendor creation process error message', 'yith-woocommerce-product-vendors' ) );
				}

				$term = get_term_by( 'name', $name, YITH_Vendors_Taxonomy::TAXONOMY_NAME );
				if ( $term && ( ! $vendor_id || $term->term_id !== $vendor_id ) ) {
					throw new Exception( _x( 'A store with this name already exists!', '[Admin]Vendor creation process error message', 'yith-woocommerce-product-vendors' ) );
				}
			} catch ( Exception $e ) {
				wp_send_json_error(
					array(
						'error' => $e->getMessage(),
					)
				);
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Validate a vendor slug
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Error on validate vendor name.
		 */
		protected function handle_validate_vendor_slug() {
			// phpcs:disable WordPress.Security.NonceVerification
			try {

				$name      = isset( $_GET['value'] ) ? sanitize_text_field( wp_unslash( $_GET['value'] ) ) : '';
				$vendor_id = isset( $_GET['vendor_id'] ) ? absint( $_GET['vendor_id'] ) : 0;

				if ( empty( $name ) ) {
					throw new Exception( _x( 'Store slug cannot be empty!', '[Admin]Vendor creation process error message', 'yith-woocommerce-product-vendors' ) );
				}

				$term = get_term_by( 'slug', $name, YITH_Vendors_Taxonomy::TAXONOMY_NAME );
				if ( $term && ( ! $vendor_id || $term->term_id !== $vendor_id ) ) {
					throw new Exception( _x( 'A store with this slug already exists!', '[Admin]Vendor creation process error message', 'yith-woocommerce-product-vendors' ) );
				}
			} catch ( Exception $e ) {
				wp_send_json_error(
					array(
						'error' => $e->getMessage(),
					)
				);
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Search vendors
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_search_for_vendors() {
			// phpcs:disable WordPress.Security.NonceVerification
			$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
			if ( empty( $term ) ) {
				wp_send_json_error();
			}

			$vendors = YITH_Vendors_Factory::search( $term );
			return apply_filters( 'yith_wcmv_json_search_found_vendors', $vendors );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Approve a vendor application request
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_approve_vendor() {
			// phpcs:disable WordPress.Security.NonceVerification
			$vendor_id = isset( $_POST['vendor_id'] ) ? absint( $_POST['vendor_id'] ) : 0;
			$vendor    = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;

			if ( empty( $vendor ) || ! $vendor->is_valid() ) {
				wp_send_json_error();
			}

			/**
			 * Let third party filter the status on approve action.
			 */
			$vendor->set_status( apply_filters( 'yith_wcmv_vendor_approved_status', 'enabled', $vendor ) );
			$vendor->save();

			// Send email notification to new vendor.
			WC()->mailer();
			do_action( 'yith_wcmv_vendor_account_approved', $vendor->get_owner() );

			// Get table.
			$table = new YITH_Vendors_Vendors_List_Table();

			return array( 'html' => $table->column_status( $vendor ) );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Approve a vendor application request
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_reject_vendor() {
			// phpcs:disable WordPress.Security.NonceVerification
			$vendor_id = isset( $_POST['vendor_id'] ) ? absint( $_POST['vendor_id'] ) : 0;
			$message   = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
			$vendor    = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;

			if ( empty( $vendor ) || ! $vendor->is_valid() ) {
				wp_send_json_error();
			}

			$vendor->set_status( apply_filters( 'yith_wcmv_vendor_rejected_status', 'rejected', $vendor ) );
			$vendor->save();

			WC()->mailer();
			do_action( 'yith_wcmv_vendor_account_rejected', $vendor->get_owner(), $message );

			// Get table.
			$table = new YITH_Vendors_Vendors_List_Table();

			return array( 'html' => $table->column_status( $vendor ) );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Get vendor data
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_get_vendor_data() {
			// phpcs:disable WordPress.Security.NonceVerification
			$vendor_id = isset( $_GET['vendor_id'] ) ? absint( $_GET['vendor_id'] ) : 0;
			return YITH_Vendors_Factory::get_data( $vendor_id );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Return registration form table template
		 *
		 * @since 4.0.0
		 */
		private function registration_form_table_template() {
			ob_start();
			include YITH_WPV_PATH . 'includes/admin/views/fields/vendor-registration-table.php';

			return ob_get_clean();
		}

		/**
		 * Handle reset vendor registration form fields
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_registration_table_fields_reset() {
			delete_option( YITH_Vendors_Registration_Form::OPTION_NAME );
			return array( 'html' => $this->registration_form_table_template() );
		}

		/**
		 * Handle save vendor registration form fields
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_registration_table_field_save() {
			// phpcs:disable WordPress.Security.NonceVerification
			$form_fields = YITH_Vendors_Registration_Form::get_admin_modal_fields();
			$posted      = yith_wcmv_get_posted_data( $form_fields, 'registration_form' );
			$fields      = YITH_Vendors_Registration_Form::get_fields();

			if ( ! empty( $_POST['field_id'] ) ) {
				$id = sanitize_key( wp_unslash( $_POST['field_id'] ) );
				if ( 'vendor-name' === $id ) { // Force required to true for field vendor-name.
					$posted['required']     = 'yes';
					$posted['connected_to'] = 'name';
				}
				$fields[ $id ] = array_merge( $fields[ $id ], $posted );
			} elseif ( isset( $posted['name'] ) ) {
				$id            = sanitize_key( $posted['name'] );
				$fields[ $id ] = $posted;
				// Set new fields active by default.
				$fields[ $id ]['active'] = 'yes';
			} else {
				wp_send_json_error();
			}

			// Manage class option.
			$fields[ $id ]['class'] = array_map( 'wc_clean', explode( ',', $posted['class'] ) );
			// Manage options array.
			$options_array = array();
			if ( ! empty( $posted['options'] ) ) {
				foreach ( $posted['options'] as $option ) {
					$key                   = urldecode( sanitize_title_with_dashes( $option['value'] ) ); // Url decode the string to prevent issue with no Latin charset.
					$options_array[ $key ] = stripslashes( $option['label'] );
				}
			}

			$fields[ $id ]['options'] = array_filter( $options_array );

			update_option( YITH_Vendors_Registration_Form::OPTION_NAME, $fields );

			return array( 'html' => $this->registration_form_table_template() );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle switch active vendor registration form field
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_registration_table_field_active_switch() {
			// phpcs:disable WordPress.Security.NonceVerification
			$id     = ! empty( $_POST['field_id'] ) ? sanitize_text_field( wp_unslash( $_POST['field_id'] ) ) : '';
			$active = ! empty( $_POST['active'] ) ? sanitize_text_field( wp_unslash( $_POST['active'] ) ) : 'yes';
			$fields = YITH_Vendors_Registration_Form::get_fields();

			if ( empty( $id ) || empty( $fields[ $id ] ) ) {
				wp_send_json_error();
			}

			$fields[ $id ]['active'] = $active;
			update_option( YITH_Vendors_Registration_Form::OPTION_NAME, $fields );

			return array( 'html' => $this->registration_form_table_template() );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle delete vendor registration form field
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_registration_table_field_delete() {
			// phpcs:disable WordPress.Security.NonceVerification
			$id     = ! empty( $_POST['field_id'] ) ? sanitize_text_field( wp_unslash( $_POST['field_id'] ) ) : '';
			$fields = YITH_Vendors_Registration_Form::get_fields();

			if ( $id ) {
				unset( $fields[ $id ] );
			}

			update_option( YITH_Vendors_Registration_Form::OPTION_NAME, $fields );

			return array( 'html' => $this->registration_form_table_template() );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Handle order vendor registration form fields
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function handle_registration_table_order_fields() {
			// phpcs:disable WordPress.Security.NonceVerification
			$order  = ! empty( $_POST['order'] ) ? wc_clean( wp_unslash( $_POST['order'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$fields = YITH_Vendors_Registration_Form::get_fields();

			if ( ! empty( $order ) && ! empty( $fields ) ) {
				$order  = array_intersect_key( array_flip( $order ), $fields );
				$fields = array_merge( $order, $fields );

				update_option( YITH_Vendors_Registration_Form::OPTION_NAME, $fields );
			}

			return array( 'html' => $this->registration_form_table_template() );
			// phpcs:enable WordPress.Security.NonceVerification
		}


		/**
		 * Add or Remove publish_products capabilities to vendor admins when global option change
		 *
		 * @since    4.0.0
		 * @return   void
		 */
		protected function handle_force_skip_review_option() {

			$vendors           = yith_wcmv_get_vendors( array( 'number' => -1 ) );
			$skip_option_value = get_option( 'yith_wpv_vendors_option_skip_review', 'no' );

			foreach ( $vendors as $vendor ) {
				$vendor->set_meta( 'skip_review', $skip_option_value );
				$vendor->save();
			}
		}
	}
}
