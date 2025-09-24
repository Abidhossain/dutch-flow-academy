<?php
/**
 * YITH Vendors Announcements List Table Class.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Announcements_List_Table' ) ) {
	/**
	 * Announcements list table class.
	 */
	class YITH_Vendors_Announcements_List_Table extends YITH_Post_Type_Admin {

		/**
		 * The post type.
		 *
		 * @var string
		 */
		protected $post_type = YITH_Vendors_Announcements::POST_TYPE;

		/**
		 * YITH_Vendors_Announcements_List_Table constructor.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function __construct() {
			parent::__construct();

			$this->register_script();
			add_action( 'admin_footer', array( $this, 'add_modal_template' ) );
			// Force is plugin panel check.
			add_filter( 'yith_wcmv_is_admin_plugin_panel', array( $this, 'force_plugin_panel' ), 10, 2 );
			// List table messages.
			add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_messages' ), 10, 2 );
			// Remove views.
			add_filter( 'views_edit-announcement', '__return_empty_array' );
		}

		/**
		 * Enqueue announcements admin scripts
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function register_script() {
			YITH_Vendors_Admin_Assets::add_js(
				'announcements',
				'announcements.js',
				array( 'jquery-blockui', 'wp-util', 'woocommerce_admin' ),
				array(
					'yith_wcmv_announcements',
					array(
						'modalTitle'         => _x( 'Create announcement', '[Admin] Create announcement modal title', 'yith-woocommerce-product-vendors' ),
						'defaultModalValues' => array(
							'show_to'   => 'all',
							'scheduled' => 'manually',
						),
					),
				)
			);
		}

		/**
		 * Force plugin panel check. Return always true if a specific TAB is not set.
		 *
		 * @since  4.0.0
		 * @param boolean $current Current value.
		 * @param string  $tab     Tab to be checked.
		 * @return boolean
		 */
		public function force_plugin_panel( $current, $tab ) {
			return ! empty( $tab ) ? $current : true;
		}

		/**
		 * Add modal template
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function add_modal_template() {
			yith_wcmv_include_admin_template( 'announcement-modal' );
		}


		/**
		 * Pre-fetch any data for the row each column has access to it, by loading $this->object.
		 *
		 * @param int $post_id Post ID being shown.
		 */
		protected function prepare_row_data( $post_id ) {
			$this->object = new YITH_Vendors_Announcement( $post_id );
		}

		/**
		 * Retrieve an array of parameters for blank state.
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_blank_state_params() {
			return array(
				'icon_url' => YITH_WPV_ASSETS_URL . 'icons/megaphone.svg',
				'message'  => _x( 'You haven\'t created announcements yet.', '[Admin] Announcement empty table message', 'yith-woocommerce-product-vendors' ),
			);
		}

		/**
		 * Define which columns to show on this screen.
		 *
		 * @since  4.0.0
		 * @param array $columns Existing columns.
		 * @return array
		 */
		public function define_columns( $columns ) {

			$columns = array_merge(
				$columns,
				array(
					'show-to' => _x( 'Show to', '[Admin] Announcements table column title', 'yith-woocommerce-product-vendors' ),
					'enable'  => _x( 'Enable', '[Admin] Announcements table column title', 'yith-woocommerce-product-vendors' ),
					'actions' => '',
				)
			);

			return $columns;
		}

		/**
		 * Render Actions column
		 *
		 * @since  4.0.0
		 */
		protected function render_show_to_column() {
			if ( 'all' === $this->object->get_show_to() ) {
				echo esc_html_x( 'All vendors', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' );
			} else {
				echo esc_html_x( 'Specific vendors', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' );
			}
		}

		/**
		 * Render Actions column
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function render_enable_column() {
			yith_plugin_fw_get_field(
				array(
					'id'      => 'active',
					'name'    => 'active',
					'type'    => 'onoff',
					'default' => 'no',
					'value'   => $this->object->get_active(),
				),
				true,
				false
			);
		}

		/**
		 * Render Actions column
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function render_actions_column() {
			$actions = yith_plugin_fw_get_default_post_actions(
				$this->post_id,
				array(
					'delete-directly' => true,
					'duplicate-url'   => add_query_arg(
						array(
							'duplicate-announcement' => $this->post_id,
							'__nonce'                => wp_create_nonce( 'duplicate-announcement' ),
						)
					),
				)
			);

			yith_plugin_fw_get_action_buttons( $actions, true );
		}

		/**
		 * Define bulk actions.
		 *
		 * @param array $actions Existing actions.
		 * @return array
		 */
		public function define_bulk_actions( $actions ) {
			return array(
				'delete' => _x( 'Delete', '[Admin]Announcement bulk action', 'yith-woocommerce-product-vendors' ),
			);
		}

		/**
		 * Filters the bulk action updated messages.
		 * By default, custom post types use the messages for the 'post' post type.
		 *
		 * @since  4.0.0
		 * @param array[] $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
		 *                               keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
		 * @param int[]   $bulk_counts   Array of item counts for each message, used to build internationalized strings.
		 * @return array
		 */
		public function bulk_messages( $bulk_messages, $bulk_counts ) {

			// Since there is no way to add custom actions, use the updated one.
			$updated = _x( 'Announcement updated.', '[Admin]Announcement updated message', 'yith-woocommerce-product-vendors' );
			if ( isset( $_REQUEST['created'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$updated = _x( 'Announcement created.', '[Admin]Announcement created message', 'yith-woocommerce-product-vendors' );
			} elseif ( isset( $_REQUEST['duplicated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$updated = _x( 'Announcement duplicated.', '[Admin]Announcement duplicated message', 'yith-woocommerce-product-vendors' );
			}

			$bulk_messages['announcement'] = array(
				'updated'   => $updated,
				/* translators: %s: Number of announcements. */
				'deleted'   => _n( '%s announcement permanently deleted.', '%s announcements permanently deleted.', $bulk_counts['deleted'], 'yith-woocommerce-product-vendors' ),
				/* translators: %s: Number of posts. */
				'trashed'   => _n( '%s announcement moved to the Trash.', '%s announcements moved to the Trash.', $bulk_counts['trashed'], 'yith-woocommerce-product-vendors' ),
				/* translators: %s: Number of posts. */
				'untrashed' => _n( '%s announcement restored from the Trash.', '%s announcements restored from the Trash.', $bulk_counts['untrashed'], 'yith-woocommerce-product-vendors' ),
			);

			return $bulk_messages;
		}
	}
}

