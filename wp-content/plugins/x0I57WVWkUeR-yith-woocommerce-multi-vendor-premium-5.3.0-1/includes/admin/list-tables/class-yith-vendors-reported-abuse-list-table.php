<?php
/**
 * YITH Vendors Reported Abuse List Table Class.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Reported_Abuse_List_Table' ) ) {
	/**
	 * Class for Reported abuse post type list table
	 */
	class YITH_Vendors_Reported_Abuse_List_Table extends YITH_Post_Type_Admin {

		/**
		 * The post object of current row
		 *
		 * @var WP_Post | null
		 */
		protected $post = null;

		/**
		 * The post type.
		 *
		 * @var string
		 */
		protected $post_type = YITH_Vendors_Report_Abuse::POST_TYPE;

		/**
		 * YITH_Vendors_Announcements_List_Table constructor.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function __construct() {
			parent::__construct();

			// Force is plugin panel check.
			add_filter( 'yith_wcmv_is_admin_plugin_panel', '__return_true' );
			// Customize table views list.
			add_filter( 'views_edit-' . $this->post_type, array( $this, 'customize_table_views' ), 10, 1 );

			$this->register_script();
			add_action( 'admin_footer', array( $this, 'add_modal_template' ) );
		}

		/**
		 * Register reported abuse admin scripts
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function register_script() {
			YITH_Vendors_Admin_Assets::add_js(
				'reported-abuse',
				'reported-abuse.js',
				array( 'jquery-blockui', 'wp-util' ),
				array(
					'yith_wcmv_reported_abuse',
					array(
						'modalTitle' => _x( 'Message', '[Admin] Reported abuse modal title', 'yith-woocommerce-product-vendors' ),
					),
				)
			);
		}

		/**
		 * Add modal template
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function add_modal_template() {
			yith_wcmv_include_admin_template( 'reported-abuse-modal' );
		}

		/**
		 * Customize table views
		 *
		 * @since  4.0.0
		 * @param array $views List of table views.
		 * @return array
		 */
		public function customize_table_views( $views ) {
			unset( $views['publish'] );

			return $views;
		}

		/**
		 * Return false since I don't want to use the object.
		 *
		 * @since  4.0.0
		 * @return bool
		 */
		protected function use_object() {
			return false;
		}

		/**
		 * Get the post row
		 *
		 * @since  4.0.0
		 * @param integer $post_id The post ID to retrieve.
		 * @return WP_Post
		 */
		protected function get_post( $post_id ) {
			if ( empty( $this->post ) || absint( $post_id ) !== absint( $this->post->ID ) ) {
				$this->post = get_post( $post_id );
			}

			return $this->post;
		}

		/**
		 * Retrieve an array of parameters for blank state.
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_blank_state_params() {
			return array(
				'icon_url' => YITH_WPV_ASSETS_URL . 'icons/empty-abuses.svg',
				'message'  => _x( 'You have no abuses reported yet.', '[Admin] Reported abuse empty table message', 'yith-woocommerce-product-vendors' ),
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
			return array(
				'cb'            => '<input type="checkbox" />',
				'product'       => _x( 'Product', '[Admin] Reported abuse table column title', 'yith-woocommerce-product-vendors' ),
				'vendor'        => _x( 'Vendor', '[Admin] Reported abuse table column title', 'yith-woocommerce-product-vendors' ),
				'reported_by'   => _x( 'Reported by', '[Admin] Reported abuse table column title', 'yith-woocommerce-product-vendors' ),
				'reported_date' => _x( 'Date', '[Admin] Reported abuse table column title', 'yith-woocommerce-product-vendors' ),
				'actions'       => '',
			);
		}

		/**
		 * Render product column
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function render_product_column() {
			$product_id = get_post_meta( $this->post_id, '_product_id', true );
			$product    = $product_id ? wc_get_product( $product_id ) : false;

			if ( ! empty( $product ) ) {
				echo sprintf( '<a href="%s" target="_blank">%s</a>', get_edit_post_link( $product->get_id() ), $product->get_title() ); // phpcs:ignore
			} else {
				echo '-';
			}
		}

		/**
		 * Render vendor column
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function render_vendor_column() {
			$vendor_id = get_post_meta( $this->post_id, '_vendor_id', true );
			$vendor    = $vendor_id ? yith_wcmv_get_vendor( $vendor_id ) : false;

			if ( ! empty( $vendor ) ) {
				echo sprintf( '<a href="%s" target="_blank">%s</a>', $vendor->get_url( 'admin' ), $vendor->get_name() ); // phpcs:ignore
			} else {
				echo '-';
			}
		}

		/**
		 * Render date column
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function render_reported_by_column() {

			$email = get_post_meta( $this->post_id, '_from_email', true );
			$user  = get_user_by( 'email', $email );

			if ( ! empty( $user ) && current_user_can( 'edit_user', $user->ID ) ) {
				$username = sprintf( '<a href="%s" target="_blank">%s</a>', get_edit_user_link( $user->ID ), $user->display_name );
			} else {
				$username = get_post_meta( $this->post_id, '_from_name', true );
			}
			$username .= '<br><small>(' . $email . ')</small>';

			echo $username;  // phpcs:ignore
		}

		/**
		 * Render date column
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function render_reported_date_column() {
			$post = $this->get_post( $this->post_id );
			echo yith_wcmv_get_formatted_date_html( $post->post_date );  // phpcs:ignore
		}

		/**
		 * Render Actions column
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function render_actions_column() {

			$post    = $this->get_post( $this->post_id );
			$actions = yith_plugin_fw_get_default_post_actions(
				$post->ID,
				array(
					'confirm-trash-message'  => __( 'Are you sure you want to move this report to the Trash?', 'yith-woocommerce-product-vendors' ),
					'confirm-delete-message' => __( 'Are you sure you want to delete this report?', 'yith-woocommerce-product-vendors' ) . '<br /><br />' . __( 'This action cannot be undone and you will not be able to recover this data.', 'yith-woocommerce-product-vendors' ),
				)
			);
			// Unset default edit action, since this post type cannot be edited.
			unset( $actions['edit'] );
			$actions = array_merge(
				array(
					'view' => array(
						'type'   => 'action-button',
						'title'  => _x( 'View', 'Post action', 'yith-plugin-fw' ),
						'action' => 'view',
						'icon'   => 'eye',
						'url'    => '#',
						'data'   => array(
							'message' => $post->post_content,
							'email'   => get_post_meta( $this->post_id, '_from_email', true ),
						),
					),
				),
				$actions
			);

			yith_plugin_fw_get_action_buttons( $actions, true );
		}

		/**
		 * Define bulk actions.
		 *
		 * @param array $actions Existing actions.
		 *
		 * @return array
		 */
		public function define_bulk_actions( $actions ) {
			unset( $actions['edit'] );
			return $actions;
		}
	}
}

