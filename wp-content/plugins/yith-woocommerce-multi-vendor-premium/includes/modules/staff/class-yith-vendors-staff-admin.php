<?php
/**
 * YITH Vendors Staff module admin class.
 *
 * @since      Version 1.0.0
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


if ( ! class_exists( 'YITH_Vendors_Staff_Admin' ) ) {
	/**
	 * YITH Vendors Staff module class. Handle admin stuff!
	 */
	class YITH_Vendors_Staff_Admin {

		/**
		 * Add staff action
		 *
		 * @var   4.0.0
		 * @const string
		 */
		const ADMIN_STAFF_ACTION = 'yith_wcmv_admin_staff_action';

		/**
		 * The tab slug
		 *
		 * @since 4.0.0
		 * @var string
		 */
		public $tab = 'staff';

		/**
		 * Current vendor
		 *
		 * @since 4.0.0
		 * @var null|YITH_Vendor
		 */
		public $vendor = null;

		/**
		 * Construct
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function __construct() {
			// Register scripts.
			add_action( 'init', array( $this, 'register_script' ), 10 );
			// Add tab to vendor edit modal.
			add_filter( 'yith_wcmv_vendor_modal_steps', array( $this, 'add_tab_in_modal' ), 10, 2 );
			add_filter( 'yith_wcmv_vendor_admin_fields', array( $this, 'add_admin_fields' ), 10, 1 );
			add_action( 'yith_wcmv_vendor_admin_field_staff_list', array( $this, 'output_staff_list_tab' ), 10, 1 );
			add_filter( 'yith_wcmv_get_posted_data_excluded_type', array( $this, 'exclude_staff_list_from_save' ), 10, 1 );
			add_filter( 'yith_wcmv_get_vendor_edit_data', array( $this, 'filter_get_vendor_data' ), 10, 3 );
			// Vendor dashboard.
			add_filter( 'yith_wcmv_admin_vendor_dashboard_tabs', array( $this, 'add_tab' ), 10, 2 );
			// Register hooks for vendors dashboard.
			add_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', array( $this, 'vendor_dashboard_hooks' ), 10, 1 );
		}

		/**
		 * Register hooks and filter for vendor dashboard
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor Current logged in vendor.
		 * @return void
		 */
		public function vendor_dashboard_hooks( $vendor ) {
			$this->vendor = $vendor;

			// Render vendor dashboard tab.
			add_action( 'yith_wcmv_vendor_dashboard_staff_tab', array( $this, 'output_tab' ), 10 );
			// Handle actions request.
			add_action( 'init', array( $this, 'handle_staff_action' ), 10 );
		}

		/**
		 * Return an array of fields for the add new staff form
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_add_staff_form_fields() {
			return apply_filters(
				'yith_wcmv_add_new_staff_form_fields',
				array(
					'first_name' => array(
						'type'     => 'text',
						'title'    => _x( 'First name', '[Admin]Staff module form field label', 'yith-woocommerce-product-vendors' ),
						'required' => true,
					),
					'last_name'  => array(
						'type'     => 'text',
						'title'    => _x( 'Last name', '[Admin]Staff module form field label', 'yith-woocommerce-product-vendors' ),
						'required' => true,
					),
					'user_email' => array(
						'type'     => 'email',
						'title'    => _x( 'Email', '[Admin]Staff module form field label', 'yith-woocommerce-product-vendors' ),
						'required' => true,
					),
					'phone'      => array(
						'type'  => 'text',
						'title' => _x( 'Phone', '[Admin]Staff module form field label', 'yith-woocommerce-product-vendors' ),
					),
				)
			);
		}

		/**
		 * Get staff permissions array
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_staff_permissions_fields() {
			$permissions = array(
				'orders'   => _x( 'Manage Orders', '[Admin]Staff permission label', 'yith-woocommerce-product-vendors' ),
				'coupons'  => _x( 'Create and edit coupons', '[Admin]Staff permission label', 'yith-woocommerce-product-vendors' ),
				'reviews'  => _x( 'Manage product reviews', '[Admin]Staff permission label', 'yith-woocommerce-product-vendors' ),
				'products' => _x( 'Create and edit products', '[Admin]Staff permission label', 'yith-woocommerce-product-vendors' ),
				'settings' => _x( 'Manage store settings', '[Admin]Staff permission label', 'yith-woocommerce-product-vendors' ),
				'reports'  => _x( 'View store report', '[Admin]Staff permission label', 'yith-woocommerce-product-vendors' ),
			);

			// Filter staff permissions.
			$permissions = array_intersect_key( $permissions, YITH_Vendors_Capabilities::get_additional_capabilities() );

			return apply_filters( 'yith_wcmv_staff_permissions', $permissions );
		}

		/**
		 * Enqueue staff admin scripts
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function register_script() {
			if ( yith_wcmv_is_plugin_panel( array( $this->tab, 'vendors' ) ) ) {
				YITH_Vendors_Admin_Assets::add_js(
					'staff',
					'staff.js',
					array( 'jquery-blockui', 'wp-util' ),
					array(
						'yith_wcmv_staff',
						array(
							'addStaffTitle'             => _x( 'Add staff', '[Admin]Add staff modal title', 'yith-woocommerce-product-vendors' ),
							'addStaffButtonLabel'       => _x( 'Add new staff', '[Admin]Add staff button label', 'yith-woocommerce-product-vendors' ),
							'editPermissionsStaffTitle' => _x( 'Edit permissions', '[Admin]Add staff modal title', 'yith-woocommerce-product-vendors' ),
							'permissionsStaffDefault'   => array_fill_keys( array_keys( $this->get_staff_permissions_fields() ), 'yes' ),
						),
					)
				);
			}
		}

		/**
		 * Add staff tab in vendor edit modal
		 *
		 * @since  4.0.0
		 * @param array $tabs   An array of modal tabs.
		 * @param array $fields An array of modal tabs fields.
		 * @return array
		 */
		public function add_tab_in_modal( $tabs, $fields ) {
			$staff = array(
				$this->tab => array(
					'label'  => _x( 'Staff', '[Admin]Staff module tab title', 'yith-woocommerce-product-vendors' ),
					'fields' => isset( $fields[ $this->tab ] ) ? $fields[ $this->tab ] : array(),
				),
			);

			$ref_pos = array_search( 'payment', array_keys( $tabs ), true );
			return array_slice( $tabs, 0, $ref_pos, true ) + $staff + array_slice( $tabs, $ref_pos, count( $tabs ), true );
		}

		/**
		 * Add admin fields to the main vendor fields array
		 *
		 * @since  4.0.0
		 * @param array $fields An array of vendor fields.
		 * @return array
		 */
		public function add_admin_fields( $fields ) {
			$fields[ $this->tab ] = array(
				'admins' => array(
					'type' => 'staff_list',
				),
			);

			return $fields;
		}

		/**
		 * Output the staff list in tab
		 *
		 * @since  4.0.0
		 * @param array $field The field options.
		 * @return void
		 */
		public function output_staff_list_tab( $field ) {
			?>
			<h4><?php echo esc_html_x( 'Staff', '[Admin]Staff module tab title', 'yith-woocommerce-product-vendors' ); ?></h4>
			<div class="staff-list-wrapper" data-staff="{{data.admins}}">
				<div class="yith-plugin-fw__list-table-blank-state">
					<img class="yith-plugin-fw__list-table-blank-state__icon" src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>icons/staff.svg" width="65" alt=""/>
					<div class="yith-plugin-fw__list-table-blank-state__message"><?php echo esc_html_x( 'No staff member added to this store.', '[Admin]Commissions table empty message', 'yith-woocommerce-product-vendors' ); ?></div>
				</div>
			</div>
			<?php
		}

		/**
		 * Exclude staff list from being saved on vendor modal submit. This field is read only.
		 *
		 * @since  4.0.0
		 * @param array $type An array of excluded field type.
		 * @return array
		 */
		public function exclude_staff_list_from_save( $type ) {
			$type[] = 'staff_list';
			return $type;
		}

		/**
		 * Filter get vendor data. Change admins ids to an array email:name
		 *
		 * @since  4.0.0
		 * @param array       $data   An array of vendor data.
		 * @param YITH_Vendor $vendor The vendor object.
		 * @param boolean     $modal  (Optional) True if it is for modal, false otherwise.
		 * @return array
		 */
		public function filter_get_vendor_data( $data, $vendor, $modal ) {
			if ( ! empty( $data['admins'] ) ) {
				$formatted_admins = array();
				foreach ( $data['admins'] as $user_id ) {
					$user = get_user_by( 'id', $user_id );
					if ( $user && $user->exists() && $vendor->get_owner() !== $user->ID ) {
						$formatted_admins[ $user->user_email ] = $user->display_name;
					}
				}
				$data['admins'] = $modal ? wp_json_encode( $formatted_admins ) : $formatted_admins;
			}

			return $data;
		}

		/**
		 * Set module vendor panel tab
		 *
		 * @since  4.0.0
		 * @param string      $tabs   An array of dashboard tabs.
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return string
		 */
		public function add_tab( $tabs, $vendor ) {
			$tabs[ $this->tab ] = array(
				'title' => _x( 'Staff', '[Admin]Staff module tab title', 'yith-woocommerce-product-vendors' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
			);

			return $tabs;
		}

		/**
		 * Output module vendor panel tab
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function output_tab() {
			$list_table = new YITH_Vendors_Staff_List_Table();
			$list_table->prepare_items();

			yith_wcmv_include_admin_template(
				'vendor-dashboard/staff-list-table',
				array(
					'staff_table'        => $list_table,
					'add_staff_fields'   => $this->get_add_staff_form_fields(),
					'permissions_fields' => $this->get_staff_permissions_fields(),
				)
			);
		}

		/**
		 * Handle staff admin action
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_staff_action() {
			if ( ! isset( $_REQUEST['action'], $_REQUEST['_wpnonce'], $_REQUEST['request'] ) ||
				self::ADMIN_STAFF_ACTION !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ||
				! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), self::ADMIN_STAFF_ACTION ) ) {
				return;
			}

			$request = sanitize_text_field( wp_unslash( $_REQUEST['request'] ) );
			$request = preg_replace( '/[^a-z_]/', '_', trim( strtolower( $request ) ) );
			if ( ! empty( $request ) ) {
				$method = 'handle_' . $request;
				if ( method_exists( $this, $method ) ) {
					$this->$method();
				}
			}

			wp_safe_redirect( apply_filters( 'yith_wcmv_handle_staff_action_url', yith_wcmv_get_admin_panel_url( array( 'tab' => $this->tab ) ) ) );
			exit;
		}

		/**
		 * Handle add new staff form submit
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Error creating staff member.
		 */
		protected function handle_add() {

			try {
				if ( ! $this->vendor || ! $this->vendor->is_valid() ) {
					throw new Exception( _x( 'Only a valid vendor can add a staff member.', '[Admin]Staff modal submit error', 'yith-woocommerce-product-vendors' ) );
				}

				$fields = $this->get_add_staff_form_fields();
				// Get posted fields.
				$posted         = yith_wcmv_get_posted_data( $fields );
				$missing_fields = array();
				foreach ( $fields as $field_key => $field ) {
					// Check for required.
					if ( ! empty( $field['required'] ) && empty( $posted[ $field_key ] ) ) {
						$missing_fields[] = isset( $field['title'] ) ? $field['title'] : $field_key;
					}
				}

				if ( ! empty( $missing_fields ) ) {
					throw new Exception(
						sprintf(
						// translators: %s is a single field name or a lists of fields name comma separated.
							_nx(
								'The field %s is required. Please, make sure to fill in the required field and try again!',
								'The fields %s are required. Please, make sure to fill in all of the required fields and try again!',
								count( $missing_fields ),
								'[Admin]Staff modal submit error',
								'yith-woocommerce-product-vendors'
							),
							implode( ', ', $missing_fields )
						)
					);
				}

				// Check if user already exists.
				$user = get_user_by( 'email', $posted['user_email'] );
				if ( $user && $user->exists() ) {
					$is_new  = false;
					$user_id = $user->ID;
					// Block add user that manage already a store or are shop manager or administrator.
					if ( user_can( $user_id, YITH_Vendors_Capabilities::ROLE_ADMIN_CAP ) || user_can( $user_id, 'manage_woocommerce' ) ) {
						// translators: %s is a placeholder for the user email.
						throw new Exception( sprintf( _x( 'A user with the email %s already exists and cannot be added as staff to your store.', '[Admin]Staff modal submit error', 'yith-woocommerce-product-vendors' ), $posted['user_email'] ) );
					}
				} else { // Create user.
					$is_new   = true;
					$username = wc_create_new_customer_username( $posted['user_email'], $posted );
					$password = wp_generate_password();

					$user_id = wp_insert_user(
						apply_filters(
							'yith_wcmv_new_staff_user_arguments',
							array(
								'user_login' => $username,
								'user_pass'  => $password,
								'user_email' => $posted['user_email'],
								'first_name' => isset( $posted['first_name'] ) ? $posted['first_name'] : '',
								'last_name'  => isset( $posted['last_name'] ) ? $posted['last_name'] : '',
								'meta_input' => array(
									'phone' => isset( $posted['phone'] ) ? $posted['phone'] : '',
								),
								'role'       => 'customer',
							)
						)
					);

					if ( is_wp_error( $user_id ) ) {
						throw new Exception( $user_id->get_error_message() );
					}
				}

				$admins = $this->vendor->get_meta( 'admins' );
				if ( empty( $admins ) ) {
					$admins = array();
				}
				$admins[] = $user_id;
				$this->vendor->set_meta( 'admins', $admins );

				add_filter( 'yith_wcmv_update_vendor_capabilities_on_save', '__return_true' ); // This force vendor capabilities to be reassigned.
				$this->vendor->save();

				WC()->mailer();
				do_action( 'yith_wcmv_new_vendor_staff_member', $user_id, $this->vendor, $is_new );

				YITH_Vendors_Admin_Notices::add( _x( 'Staff member added correctly!', '[Admin]Staff modal submit success', 'yith-woocommerce-product-vendors' ) );
			} catch ( Exception $e ) {
				YITH_Vendors_Admin_Notices::add( $e->getMessage(), 'error' );
			}
		}

		/**
		 * Get delete action url
		 *
		 * @since  4.0.0
		 * @param integer $user_id The user ID to delete.
		 * @return string
		 */
		public function get_staff_delete_url( $user_id ) {
			return yith_wcmv_get_admin_panel_url(
				array(
					'tab'      => $this->tab,
					'action'   => self::ADMIN_STAFF_ACTION,
					'request'  => 'delete',
					'_wpnonce' => wp_create_nonce( self::ADMIN_STAFF_ACTION ),
					'id'       => $user_id,
				)
			);
		}

		/**
		 * Handle a delete staff request
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Error deleting staff member.
		 */
		protected function handle_delete() {

			try {
				$admins  = YITH_Vendors_Staff()->get_vendor_admins( $this->vendor );
				$user_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				// Search for requested user on admins list.
				$index = ( $user_id && $admins ) ? array_search( $user_id, $admins, true ) : false;
				if ( false === $index ) {
					throw new Exception( _x( 'Invalid staff member for vendor.', '[Admin]Staff modal submit error', 'yith-woocommerce-product-vendors' ) );
				}

				// Remove vendor capabilities for user and delete permissions meta.
				YITH_Vendors_Capabilities::remove_vendor_capabilities_for_user( $user_id );
				delete_user_meta( $user_id, YITH_Vendors_Staff()->get_permissions_meta_key() );

				unset( $admins[ $index ] );
				$this->vendor->set_meta( 'admins', $admins );
				$this->vendor->save();

				YITH_Vendors_Admin_Notices::add( _x( 'Staff member removed correctly!', '[Admin]Staff modal submit success', 'yith-woocommerce-product-vendors' ) );
			} catch ( Exception $e ) {
				YITH_Vendors_Admin_Notices::add( $e->getMessage(), 'error' );
			}
		}

		/**
		 * Handle a delete staff request
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Error deleting staff member.
		 */
		protected function handle_edit_permissions() {

			try {
				$admins  = YITH_Vendors_Staff()->get_vendor_admins( $this->vendor );
				$user_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				// Search for requested user on admins list.
				$index = ( $user_id && $admins ) ? array_search( $user_id, $admins, true ) : false;
				if ( false === $index ) {
					throw new Exception( _x( 'Invalid staff member for vendor.', '[Admin]Staff modal submit error', 'yith-woocommerce-product-vendors' ) );
				}

				$permissions = array();
				foreach ( array_keys( $this->get_staff_permissions_fields() ) as $permission ) {
					$permissions[ $permission ] = isset( $_POST[ $permission ] ) ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification
				}

				update_user_meta( $user_id, YITH_Vendors_Staff()->get_permissions_meta_key(), $permissions );

				YITH_Vendors_Admin_Notices::add( _x( 'Staff member permissions saved correctly!', '[Admin]Staff modal submit success', 'yith-woocommerce-product-vendors' ) );
			} catch ( Exception $e ) {
				YITH_Vendors_Admin_Notices::add( $e->getMessage(), 'error' );
			}
		}
	}
}
