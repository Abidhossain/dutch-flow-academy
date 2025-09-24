<?php
/**
 * YITH Vendors Announcements Admin Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
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

if ( ! class_exists( 'YITH_Vendors_Announcements_Admin' ) ) {
	/**
	 * Handle announcement post type admin sections
	 */
	class YITH_Vendors_Announcements_Admin {

		/**
		 * YITH_Vendors_Announcements_Admin constructor.
		 *
		 * @since  4.0.0
		 */
		public function __construct() {
			// Maybe load post type announcement deps.
			add_action( 'admin_init', array( $this, 'maybe_load_announcement_deps' ) );
			// Handle form submit.
			add_action( 'admin_init', array( $this, 'handle_form_submit' ), 15 );
			add_action( 'admin_init', array( $this, 'handle_duplicate_announcement' ), 15 );

			add_action( 'yith_wcmv_admin_ajax_get_announcement_data', array( $this, 'handle_get_announcement_data' ) );
			add_action( 'yith_wcmv_admin_ajax_announcement_active_switch', array( $this, 'handle_announcement_active_switch' ) );
			add_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', array( $this, 'vendor_dashboard_hooks' ) );

			// Frontend Manager support.
			add_action( 'yith_wcmv_before_fm_vendor_panel', array( $this, 'print_announcements_if_any' ) );
			add_action( 'yith_wcmv_after_fm_vendor_panel', array( $this, 'print_script' ) );

			// Handle announcements dismiss.
			add_action( 'wp_ajax_yith-vendors-dismiss-announcement', array( $this, 'handle_dismiss_announcement' ) );
		}

		/**
		 * Maybe load post type announcement handler and deps
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function maybe_load_announcement_deps() {
			if ( isset( $_GET['post_type'] ) && YITH_Vendors_Announcements::POST_TYPE === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				YITH_Vendors_Announcements_List_Table::instance();
			}
		}

		/**
		 * Handle form submit
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Error on announcement form submit.
		 */
		public function handle_form_submit() {

			if ( ! isset( $_POST['modal_action'] ) || ! isset( $_POST['_wpnonce'] ) || 'yith_wcmv_announcement_save' !== sanitize_text_field( wp_unslash( $_POST['modal_action'] ) ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'yith_wcmv_announcement_save' ) ) {
				return;
			}

			$url = admin_url( 'edit.php?post_type=' . YITH_Vendors_Announcements::POST_TYPE );

			try {
				// Collect data.
				$data            = yith_wcmv_get_posted_data( YITH_Vendors_Announcements()->get_post_type_fields() );
				$announcement_id = ! empty( $_POST['announcement_id'] ) ? absint( $_POST['announcement_id'] ) : 0;
				$announcement    = new YITH_Vendors_Announcement( $announcement_id );
				if ( ! $announcement ) {
					// translators: %s is the announcement ID.
					throw new Exception( sprintf( __( 'Invalid announcement. No announcements found matching ID #%s.', 'yith-woocommerce-product-vendors' ), $announcement_id ) );
				}

				$is_update = $announcement->exists();
				foreach ( $data as $key => $value ) {
					$method = "set_{$key}";
					if ( method_exists( $announcement, $method ) ) {
						$announcement->$method( $value );
					} else {
						$announcement->set_meta( $key, $value );
					}
				}
				$announcement->save();
				$url = add_query_arg( ( $is_update ? 'updated' : 'created' ), '1', $url );

			} catch ( Exception $e ) {
				YITH_Vendors_Admin_Notices::add( $e->getMessage(), 'error' );
			}

			wp_safe_redirect( $url );
			exit;
		}

		/**
		 * Handle duplicate announcement request
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Error on announcement form submit.
		 */
		public function handle_duplicate_announcement() {

			if ( empty( $_GET['duplicate-announcement'] ) || ! isset( $_GET['__nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['__nonce'] ) ), 'duplicate-announcement' ) ) {
				return;
			}

			$url = admin_url( 'edit.php?post_type=' . YITH_Vendors_Announcements::POST_TYPE );

			try {

				$announcement_id = absint( $_GET['duplicate-announcement'] );
				$announcement    = new YITH_Vendors_Announcement( $announcement_id );
				if ( ! $announcement || ! $announcement->exists() ) {
					// translators: %s is the announcement ID.
					throw new Exception( sprintf( __( 'Not possible to duplicate announcement. No announcement found.', 'yith-woocommerce-product-vendors' ), $announcement_id ) );
				}

				$announcement->duplicate();
				$url = add_query_arg(
					array(
						'updated'    => '1',
						'duplicated' => '1',
					),
					$url
				);

			} catch ( Exception $e ) {
				YITH_Vendors_Admin_Notices::add( $e->getMessage(), 'error' );
			}

			wp_safe_redirect( $url );
			exit;
		}

		/**
		 * Get announcement data
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_get_announcement_data() {
			// Get the announcement from request.
			$announcement_id = isset( $_GET['announcement_id'] ) ? absint( $_GET['announcement_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			$announcement    = new YITH_Vendors_Announcement( $announcement_id );
			if ( $announcement && $announcement->exists() ) {
				// Collect data for response.
				$data = array( 'announcement_id' => $announcement_id );
				foreach ( array_keys( YITH_Vendors_Announcements()->get_post_type_fields() ) as $key ) {
					$method = "get_{$key}";
					$value  = method_exists( $announcement, $method ) ? $announcement->$method() : $announcement->get_meta( $key );
					if ( is_array( $value ) ) {
						$value = wp_json_encode( $value );
					}
					$data[ $key ] = $value;
				}

				wp_send_json_success( $data );
			}

			wp_send_json_error();
		}

		/**
		 * Switch active announcement meta
		 *
		 * @since  4.0.0
		 * @return void
		 * @throws Exception Error switching active announcement.
		 */
		public function handle_announcement_active_switch() {
			try {
				$announcement_id = isset( $_POST['announcement_id'] ) ? absint( $_POST['announcement_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$announcement    = new YITH_Vendors_Announcement( $announcement_id );
				if ( ! $announcement || ! $announcement->exists() ) {
					// translators: %s is the announcement ID.
					throw new Exception( sprintf( __( 'Invalid announcement. No announcements found matching ID #%s.', 'yith-woocommerce-product-vendors' ), $announcement_id ) );
				}

				$active = isset( $_POST['active'] ) ? sanitize_text_field( wp_unslash( $_POST['active'] ) ) : 'no'; // phpcs:ignore WordPress.Security.NonceVerification
				$announcement->set_active( $active );
				$announcement->save();

				wp_send_json_success();

			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );
				wp_send_json_error();
			}
		}

		/**
		 * Print announcement admin fields used on announcement create/edit modal
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function print_announcement_fields() {
			foreach ( YITH_Vendors_Announcements()->get_post_type_fields() as $key => $field ) {
				$field['id'] = $key;
				$type        = isset( $field['type'] ) ? $field['type'] : 'text';
				if ( 'ajax-vendors' === $type ) {
					$field['data'] = array( 'value' => "{{data.$key}}" );
				} elseif ( ! in_array( $type, array( 'text', 'number', 'textarea', 'textarea-editor' ), true ) ) {
					$field['custom_attributes']['data-value'] = "{{data.$key}}";
				} else {
					$field['value'] = "{{data.$key}}";
				}

				yith_wcmv_print_panel_field( $field );
			}
		}

		/**
		 * Vendor admin dashboard hooks
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function vendor_dashboard_hooks() {
			// Print announcement if any.
			add_action( 'admin_notices', array( $this, 'print_announcements_if_any' ), 20 );
		}

		/**
		 * Print dashboard announcements if any
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function print_announcements_if_any() {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			$this->print( $vendor );
		}

		/**
		 * Print vendor announcements
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor The current vendor.
		 * @return void
		 */
		protected function print( $vendor ) {

			if ( apply_filters( 'yith_wcmv_skip_print_announcements', false, $vendor ) ) {
				return;
			}

			foreach ( YITH_Vendors_Announcements()->get_visible_announcements_for_vendor( $vendor ) as $announcement ) {
				yith_wcmv_include_admin_template(
					'announcement.php',
					array(
						'id'          => $announcement->get_id(),
						'text'        => $announcement->get_content(),
						'dismissible' => $announcement->is_dismissible(),
					)
				);

				// If there is a dismissible announcement add the script.
				if ( $announcement->is_dismissible() && ! has_action( 'admin_footer', array( $this, 'print_script' ) ) ) {
					add_action( 'admin_footer', array( $this, 'print_script' ) );
				}
			}
		}

		/**
		 * Print script announcements
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function print_script() {
			?>
			<script>
				jQuery(document).on('click', '.announcement-dismiss', function (event) {
					event.preventDefault();
					var announcement = jQuery(this).parent(),
						id = announcement.data('id');
					announcement.fadeOut(function () {
						announcement.remove();
						jQuery.post( yith_wcmv_ajax.ajaxUrl, {
							action: 'yith-vendors-dismiss-announcement',
							_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'yith-vendors-dismiss-announcement' ) ); ?>',
							id: id
						});
					});
				});
			</script>
			<?php
		}

		/**
		 * Handle dismiss announcement by vendor
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function handle_dismiss_announcement() {
			check_ajax_referer( 'yith-vendors-dismiss-announcement' );

			$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
			if ( empty( $id ) ) {
				wp_die( -1, 403 );
			}

			$user_id   = get_current_user_id();
			$key       = '_yith_wcmv_dismissed_announcements_' . get_current_blog_id();
			$dismissed = get_user_meta( $user_id, $key, true );
			if ( empty( $dismissed ) || ! is_array( $dismissed ) ) {
				$dismissed = array();
			}

			$dismissed[ $id ] = time();
			update_user_meta( $user_id, $key, array_unique( $dismissed ) );

			wp_die( 1, 200 );
		}
	}
}
