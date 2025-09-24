<?php
/**
 * YITH Vendors Staff Module List Table Class.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'YITH_Vendors_Staff_List_Table' ) ) {
	/**
	 * Vendor staff list table class.
	 */
	class YITH_Vendors_Staff_List_Table extends WP_List_Table {

		/**
		 * Current vendor
		 *
		 * @since 4.0.0
		 * @var YITH_Vendor|boolean
		 */
		protected $vendor = false;

		/**
		 * Construct
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'staff',
					'plural'   => 'staff',
					'ajax'     => false,
				)
			);

			$this->vendor = yith_wcmv_get_vendor( 'current', 'user' );
		}

		/**
		 * Returns columns available in table
		 *
		 * @since 1.0.0
		 * @return array Array of columns of the table
		 */
		public function get_columns() {

			return apply_filters(
				'yith_wcmv_staff_list_table_column',
				array(
					'name'            => _x( 'Name', '[Admin] Staff table column name', 'yith-woocommerce-product-vendors' ),
					'email'           => _x( 'Email', '[Admin] Staff table column name', 'yith-woocommerce-product-vendors' ),
					'phone'           => _x( 'Phone', '[Admin] Staff table column name', 'yith-woocommerce-product-vendors' ),
					'registered_date' => _x( 'Registered date', '[Admin] Staff table column name', 'yith-woocommerce-product-vendors' ),
					'actions'         => '',
				)
			);
		}

		/**
		 * Gets a list of CSS classes for the WP_List_Table table tag.
		 *
		 * @since 3.1.0
		 * @return string[] Array of CSS classes for the table tag.
		 */
		protected function get_table_classes() {
			$classes = parent::get_table_classes();
			return array_merge( $classes, array( 'yith-plugin-fw__classic-table' ) );
		}

		/**
		 * Prepare items for table
		 *
		 * @since 4.0.0
		 * @return void
		 */
		public function prepare_items() {

			$items = ( $this->vendor && $this->vendor->is_valid() ) ? $this->vendor->get_meta( 'admins' ) : array();
			// Sets pagination arguments.
			$per_page     = apply_filters( 'yith_wcmv_staff_list_table_per_page', 20 );
			$current_page = absint( $this->get_pagenum() );
			$order        = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby      = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $items ) ) {
				$this->items = get_users(
					apply_filters(
						'yith_wcmv_staff_list_table_args',
						array(
							'number'  => $per_page,
							'pages'   => $current_page,
							'include' => $items,
							'orderby' => $orderby ? $orderby : 'ID',
							'order'   => in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ? $order : 'DESC',
						)
					)
				);
				$total_items = count( $items );
			} else {
				$this->items = array();
				$total_items = 0;
			}

			// Sets columns headers.
			$columns               = $this->get_columns();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $sortable );

			// Sets pagination args.
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page ),
				)
			);
		}

		/**
		 * Display the search box.
		 *
		 * @since  4.0.0
		 * @param string $text     The search button text.
		 * @param string $input_id The search input id.
		 * @return mixed
		 */
		public function add_search_box( $text, $input_id ) {
			return false;
		}

		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @since 4.0.0
		 * @return array The array of columns that can be sorted by the user.
		 */
		public function get_sortable_columns() {
			return array(
				'name'            => array( 'name', false ),
				'registered_date' => array( 'registered_date', false ),
			);
		}

		/**
		 * Sets bulk actions for table
		 *
		 * @since 4.0.0
		 * @return array Array of available actions.
		 */
		public function get_bulk_actions() {
			return array();
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @since  4.0.0
		 * @param WP_User $user The current item.
		 */
		public function single_row( $user ) {
			$permissions = wp_json_encode( YITH_Vendors_Staff()->get_staff_permissions( $user->ID ) );
			?>
			<tr data-id="<?php echo esc_attr( $user->ID ); ?>" data-permissions="<?php echo esc_attr( $permissions ); ?>">
				<?php
				$this->single_row_columns( $user )
				?>
			</tr>
			<?php
		}

		/**
		 * Output name column content
		 *
		 * @since 5.0.0
		 * @param WP_User $user The WP user object.
		 * @return string
		 */
		public function column_name( $user ) {
			$html = sprintf( '<a href="javascript:void(0)" class="edit-staff-permissions">%s</a>', $user->display_name );

			return apply_filters( 'yith_wcmv_staff_list_table_name_column', $html, $user, $this->vendor );
		}

		/**
		 * Output email column content
		 *
		 * @since 5.0.0
		 * @param WP_User $user The WP user object.
		 * @return string
		 */
		public function column_email( $user ) {
			$html = sprintf( '<a href="mailto:%1$s">%1$s</a>', $user->user_email );

			return apply_filters( 'yith_wcmv_staff_list_table_email_column', $html, $user, $this->vendor );
		}

		/**
		 * Output phone column content
		 *
		 * @since 5.0.0
		 * @param WP_User $user The WP user object.
		 * @return string
		 */
		public function column_phone( $user ) {
			return apply_filters( 'yith_wcmv_staff_list_table_phone_column', get_user_meta( $user->ID, 'phone', true ) ?: '-', $user, $this->vendor ); // phpcs:ignore
		}

		/**
		 * Output registered date column content
		 *
		 * @since 5.0.0
		 * @param WP_User $user The WP user object.
		 * @return string
		 */
		public function column_registered_date( $user ) {
			return apply_filters( 'yith_wcmv_staff_list_table_registered_date_column', $user->user_registered, $user, $this->vendor ); // phpcs:ignore
		}

		/**
		 * Output actions date column content
		 *
		 * @since 5.0.0
		 * @param WP_User $user The WP user object.
		 * @return string
		 */
		public function column_actions( $user ) {

			$html = yith_plugin_fw_get_component(
				array(
					'type'   => 'action-button',
					'class'  => 'edit-staff-permissions',
					'action' => 'edit',
					'title'  => __( 'Edit Permissions', 'yith-woocommerce-product-vendors' ),
					'icon'   => 'pencil',
					'url'    => '#',
				),
				false
			);

			$html .= yith_plugin_fw_get_component(
				array(
					'type'         => 'action-button',
					'action'       => 'delete',
					'title'        => _x( 'Delete', 'yith-woocommerce-product-vendors' ),
					'class'        => 'delete-staff',
					'icon'         => 'trash',
					'url'          => YITH_Vendors_Staff()->admin->get_staff_delete_url( $user->ID ),
					'confirm_data' => array(
						'title'               => __( 'Confirm delete', 'yith-woocommerce-product-vendors' ),
						// translators: %s is a placeholder for user full name.
						'message'             => sprintf( __( 'Are you sure you want to remove "%s" from staff?', 'yith-woocommerce-product-vendors' ), '<strong>' . $user->user_email . '</strong>' ),
						'cancel-button'       => __( 'No', 'yith-woocommerce-product-vendors' ),
						'confirm-button'      => _x( 'Yes, delete', 'Delete confirmation action', 'yith-woocommerce-product-vendors' ),
						'confirm-button-type' => 'delete',
					),
				),
				false
			);

			return apply_filters( 'yith_wcmv_staff_list_table_actions_column', $html, $user, $this->vendor ); // phpcs:ignore
		}


		/**
		 * Print the columns information
		 * Let third party use this custom hook to output custom column content.
		 *
		 * @since  4.0.0
		 * @param WP_User $user        The WP user object.
		 * @param string  $column_name Current column name.
		 * @return void
		 */
		protected function column_default( $user, $column_name ) {
			do_action( "yith_wcmv_staff_list_table_col_{$column_name}", $user, $this->vendor, $column_name );
		}

		/**
		 * Whether the table has items to display or not
		 *
		 * @since 3.1.0
		 * @return bool
		 */
		public function has_items() {
			return ! empty( $this->items );
		}
	}
}

