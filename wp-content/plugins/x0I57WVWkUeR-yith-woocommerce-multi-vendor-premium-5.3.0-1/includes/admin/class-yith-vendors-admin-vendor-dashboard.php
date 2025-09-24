<?php
/**
 * YITH Vendors Admin Vendor Dashboard
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Vendor_Dashboard' ) ) {
	/**
	 * Vendor admin dashboard class.
	 */
	class YITH_Vendors_Admin_Vendor_Dashboard {

		/**
		 * Panel handler class instance
		 *
		 * @since 4.0.0
		 * @var YITH_Vendors_Admin_Vendor_Dashboard_Panel|null
		 */
		protected $panel_handler = null;

		/**
		 * Current vendor
		 *
		 * @var YITH_Vendor | null
		 */
		protected $vendor = null;

		/**
		 * Construct
		 *
		 * @since 4.0.0
		 */
		public function __construct() {
			$this->vendor = yith_wcmv_get_vendor( 'current', 'user' );

			if ( $this->vendor && $this->vendor->is_valid() ) {
				$this->register_common_hooks();

				if ( $this->vendor->has_limited_access() ) {
					$this->register_limited_access_hooks();
				}
			}
		}

		/**
		 * Register common vendor hooks
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function register_common_hooks() {
			// Remove admin only notices.
			add_filter( 'woocommerce_helper_suppress_connect_notice', array( $this, 'suppress_connect_notice' ) );
			remove_action( 'admin_notices', 'update_nag', 3 );
			// Remove 3rd-part meta-boxes.
			add_action( 'add_meta_boxes_product', array( $this, 'remove_meta_boxes' ), 99 );
			add_filter( 'map_meta_cap', array( $this, 'remove_jetpack_menu_page' ), 10, 4 );

			add_filter( 'yith_wcmv_admin_vendor_menu_items', array( $this, 'add_allowed_menu_items' ), 1 );

			do_action( 'yith_wcmv_vendor_dashboard_hooks', $this->vendor );
		}

		/**
		 * Register limited access hooks
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function register_limited_access_hooks() {

			// Init panel handler.
			$this->panel_handler = new YITH_Vendors_Admin_Vendor_Dashboard_Panel( $this->vendor );

			// Style and scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'add_style_and_scripts' ), 1 );
			// Hide YITH Licence messages.
			add_action( 'admin_init', array( $this, 'hide_licence_notices' ), 15 );
			// Customize admin menu.
			add_action( 'admin_menu', array( $this, 'filter_menu_items' ), 99 );
			add_action( 'admin_menu', array( $this, 'customize_dashboard_widgets' ) );
			// Filter content.
			add_action( 'pre_get_posts', array( $this, 'filter_content' ), 20, 1 );
			add_filter( 'wp_count_posts', array( $this, 'filter_count_posts' ), 15, 3 );
			add_action( 'current_screen', array( $this, 'restrict_edit_vendor_content' ), 0 );
			// Restrict add products.
			add_action( 'current_screen', array( $this, 'restrict_add_vendor_product' ), 0, 1 );
			// Essential Grid Support.
			add_action( 'add_meta_boxes', array( $this, 'remove_ess_grid_metabox' ), 20 );
			// Associate vendor taxonomy once post type is saved.
			add_action( 'save_post', array( $this, 'add_vendor_taxonomy_to_post_type' ), 10, 2 );
			// Let's limited vendors be able to add product attributes.
			add_action( 'wp_ajax_woocommerce_add_new_attribute', array( $this, 'add_new_attribute' ), 5 );
			// Allow WooCommerce admin access for vendor admins.
			add_filter( 'woocommerce_prevent_admin_access', array( $this, 'prevent_admin_access' ) );
			// Filter AJAX search products.
			add_filter( 'woocommerce_json_search_found_products', array( $this, 'filter_json_search_found_products' ), 10, 1 );
			// Filter WC admin features config.
			add_filter( 'woocommerce_admin_features', array( $this, 'filter_admin_get_feature_config' ), 1, 1 );
			add_filter( 'woocommerce_admin_get_feature_config', array( $this, 'filter_admin_get_feature_config' ), 1, 1 );

			// Disable GeoDirectory "Prevent admin access" for vendor.
			remove_action( 'admin_init', 'geodir_allow_wpadmin' );
			// WP User Avatar Compatibility. Enabled vendor to manage WP User Avatar dashboard.
			add_filter( 'wpua_subscriber_offlimits', '__return_empty_array' );
			// WordPress User Frontend.
			if ( function_exists( 'wpuf' ) ) {
				remove_action( 'admin_init', array( wpuf(), 'block_admin_access' ) );
			}

			// Handle comments sections.
			add_action( 'admin_init', array( $this, 'allowed_comments' ) );
			add_filter( 'pre_get_comments', array( $this, 'filter_reviews_list' ), 10, 1 );
			add_filter( 'wp_count_comments', array( $this, 'count_comments' ), 5, 2 );
			add_action( 'load-comment.php', array( $this, 'disabled_manage_other_comments' ) );

			// Customize WooCommerce Import/Export.
			add_filter( 'woocommerce_product_export_product_query_args', array( $this, 'customize_export_query' ), 10, 1 );
			add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'filter_import_mapping_options' ), 10, 1 );
			add_filter( 'woocommerce_product_import_process_item_data', array( $this, 'customize_import_item_data' ), 10, 1 );
			add_action( 'woocommerce_product_import_inserted_product_object', array( $this, 'set_vendor_to_imported_product' ), 10, 2 );

			do_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', $this->vendor );
		}

		/**
		 * Get panel handler class instance.
		 *
		 * @since  4.0.0
		 * @return YITH_Vendors_Admin_Vendor_Dashboard_Panel|null
		 */
		public function get_panel_handler() {
			return $this->panel_handler;
		}

		/**
		 * Add allowed menu items to vendor dashboard
		 *
		 * @since  4.0.0
		 * @param array $allowed An array of allowed items.
		 * @return array
		 */
		public function add_allowed_menu_items( $allowed ) {
			$allowed = array_merge( $allowed, array( 'upload.php' ) );
			return $allowed;
		}

		/**
		 * Hide plugin licence notice to vendor.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function hide_licence_notices() {
			remove_action( 'admin_notices', array( YITH_Plugin_Licence(), 'activation_license_notice' ), 15 );
		}

		/**
		 * Filter admin repost dashboard content based on current vendor
		 *
		 * @since  4.0.0
		 * @param array $query_args The report query args.
		 * @return array
		 */
		public function filter_reports_content( $query_args ) {
			$query_args['vendor_id'] = $this->vendor->get_id();
			return $query_args;
		}

		/**
		 * Suppress the WooCommerce connect store notice.
		 *
		 * @since  4.0.0
		 * @param boolean $suppress The current value.
		 * @return boolean
		 */
		public function suppress_connect_notice( $suppress ) {
			return true;
		}

		/**
		 * Remove Jetpack pages for Vendor
		 *
		 * @param array   $caps    Array of capabilities.
		 * @param string  $cap     Current cap.
		 * @param integer $user_id The user ID.
		 * @param array   $args    An array of arguments.
		 * @return array
		 */
		public function remove_jetpack_menu_page( $caps, $cap, $user_id, $args ) {
			if ( 'jetpack_admin_page' === $cap ) {
				$caps[] = 'manage_options';
			}

			return $caps;
		}

		/**
		 * Enqueue custom style and scripts for vendor dashboard
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function add_style_and_scripts() {

			YITH_Vendors_Admin_Assets::add_css( 'vendor-dashboard-admin', 'vendor-dashboard.css' );
			YITH_Vendors_Admin_Assets::add_js(
				'vendors-admin',
				'vendors-dashboard.js',
				array( 'wc-enhanced-select' ),
				array(
					'yith_wcmv_vendors',
					array(
						'uploadFrameTitle'       => esc_html__( 'Choose an image', 'yith-woocommerce-product-vendors' ),
						'uploadFrameButtonText'  => esc_html__( 'Use image', 'yith-woocommerce-product-vendors' ),
						'countries'              => wp_json_encode( WC()->countries->get_states() ),
						'i18n_select_state_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' ), // Keep the WooCommerce text domain.
						'orderDataToShow'        => array(
							'customer' => get_option( 'yith_wpv_vendors_option_order_show_customer', 'no' ),
							'address'  => get_option( 'yith_wpv_vendors_option_order_show_billing_shipping', 'no' ),
							'payment'  => get_option( 'yith_wpv_vendors_option_order_show_payment', 'no' ),
						),
						'hideFeaturedProduct'    => ! $this->vendor->can_handle_featured_products(),
						'hideImportProducts'     => 'yes' !== get_option( 'yith_wpv_vendors_option_product_import_management', 'no' ),
					),
				)
			);
		}

		/**
		 * Get a list of allowed post type for vendor
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_allowed_post_type() {
			$allowed_post_types = apply_filters( 'yith_wcmv_vendor_allowed_vendor_post_type', array( 'product' ) );
			return array_unique( $allowed_post_types );
		}

		/**
		 * Get a list of allowed manu items
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_allowed_menu_items() {
			$items = array(
				'index.php',
				'separator1',
				'profile.php',
				'separator-last',
				YITH_Vendors_Admin::PANEL_PAGE,
				// Backward compatibility with modules.
				'yith-plugins_page_pdf_invoice_for_multivendor',
				'yith_woocommerce_subscription',
				'yith_vendor_nyp_settings',
				'yith_wapo_panel',
				'yith_wpv_deprecated_panel_commissions',
				'yith_wpv_deprecated_panel_dashboard',
			);

			if ( current_user_can( 'moderate_comments' ) ) {
				$items[] = 'edit-comments.php';
			}

			// Add allowed post type.
			foreach ( $this->get_allowed_post_type() as $post_type ) {
				$items[] = "edit.php?post_type={$post_type}";
			}

			return apply_filters( 'yith_wcmv_admin_vendor_menu_items', $items );
		}

		/**
		 * Is menu item registered?
		 *
		 * @since  4.0.0
		 * @param string $menu_slug The menu item slug to search.
		 * @return boolean
		 */
		protected function is_menu_item_registered( $menu_slug ) {
			global $menu;

			foreach ( $menu as $k => $item ) {
				if ( $menu_slug === $item[2] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Filter WP menu items for vendor
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function filter_menu_items() {
			global $menu;

			$allowed_items = $this->get_allowed_menu_items();
			foreach ( $menu as $page ) {
				if ( ! empty( $page[2] ) && ! in_array( $page[2], $allowed_items, true ) ) {
					remove_menu_page( $page[2] );
				}
			}

			do_action( 'yith_wcmv_filtered_vendor_menu_items', $this->vendor );
		}

		/**
		 * Remove widgets for vendor dashboard
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function customize_dashboard_widgets() {
			add_filter( 'jetpack_just_in_time_msgs', '__return_false', 999 );
			add_filter( 'woocommerce_allow_marketplace_suggestions', '__return_false' );

			$to_removes = apply_filters(
				'yith_wcmv_to_remove_dashboard_widgets',
				array(
					array(
						'id'      => 'woocommerce_dashboard_status',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'dashboard_activity',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'dashboard_right_now',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'dashboard_quick_press',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'yith_wcmc_dashboard_widget',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'jetpack_summary_widget',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
					array(
						'id'      => 'wpseo-dashboard-overview',
						'screen'  => 'dashboard',
						'context' => 'normal',
					),
				)
			);

			foreach ( $to_removes as $widget ) {
				remove_meta_box( $widget['id'], $widget['screen'], $widget['context'] );
			}

			$review_management = 'yes' === get_option( 'yith_wpv_vendors_option_review_management', 'no' );

			$to_adds = array(
				array(
					'id'       => 'woocommerce_dashboard_recent_reviews',
					'name'     => __( 'Recent reviews', 'yith-woocommerce-product-vendors' ),
					'callback' => array( $this, 'vendor_recent_reviews_widget' ),
					'context'  => $review_management ? 'side' : 'normal',
				),
			);

			if ( $review_management ) {
				$to_adds[] = array(
					'id'       => 'vendor_recent_reviews',
					'name'     => __( 'Recent comments', 'yith-woocommerce-product-vendors' ),
					'callback' => array( $this, 'vendor_recent_comments_widget' ),
					'context'  => 'normal',
				);
			}

			foreach ( $to_adds as $widget ) {
				add_meta_box( $widget['id'], $widget['name'], $widget['callback'], 'dashboard', $widget['context'], 'high' );
			}
		}

		/**
		 * Filter content based on current vendor
		 *
		 * @since  4.0.0
		 * @param WP_Query $query The Wp_Query instance.
		 * @return void
		 */
		public function filter_content( &$query ) {

			// If this is not an allowed post type, exit.
			$post_type = $query->get( 'post_type' );

			if ( ! $post_type ) {
				return;
			}

			if ( 'attachment' === $post_type ) {

				$vendor_admin_ids = $this->vendor->get_admins();
				if ( ! empty( $vendor_admin_ids ) ) {
					$query->set( 'author__in', $vendor_admin_ids );
				}
			} elseif ( in_array( $post_type, $this->get_allowed_post_type(), true ) ) {

				// Let third party modify the standard query filter.
				if ( false !== has_action( "yith_wcmv_vendor_filter_content_{$post_type}" ) ) {
					do_action_ref_array( "yith_wcmv_vendor_filter_content_{$post_type}", array( &$query, $this->vendor ) );
				} else {

					$conditions = $query->get( 'tax_query' );
					if ( ! is_array( $conditions ) ) {
						$conditions = array();
					}

					if ( ! $this->vendor_condition_applied( $conditions ) ) {
						$conditions[] = array(
							'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
							'field'    => 'id',
							'terms'    => $this->vendor->get_id(),
						);

						$query->set( 'tax_query', $conditions );
					}
				}
			}
		}

		/**
		 * Check if vendor taxonomy conditions is already applied
		 *
		 * @since  4.0.0
		 * @param array $conditions The conditions to check.
		 * @return boolean
		 */
		protected function vendor_condition_applied( $conditions ) {
			foreach ( $conditions as $condition ) {
				if ( isset( $condition['taxonomy'] ) && YITH_Vendors_Taxonomy::TAXONOMY_NAME === $condition['taxonomy'] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Filter WP count posts for vendor
		 *
		 * @since  4.0.0
		 * @param object $counts An object containing the current post_type's post
		 *                       counts by status.
		 * @param string $type   Post type.
		 * @param string $perm   The permission to determine if the posts are 'readable'
		 *                       by the current user.
		 * @return object
		 */
		public function filter_count_posts( $counts, $type, $perm ) {
			global $wpdb;

			if ( ! in_array( $type, $this->get_allowed_post_type(), true ) || apply_filters( "yith_wcmv_skip_{$type}_filter_count_post", false ) ) {
				return $counts;
			}

			// Create a cache key.
			$cache_key = _count_posts_cache_key( 'vendor_' . $type, $perm );
			$counts    = wp_cache_get( $cache_key, 'counts' );
			if ( false !== $counts ) {
				// We may have cached this before every status was registered.
				foreach ( get_post_stati() as $status ) {
					if ( ! isset( $counts->{$status} ) ) {
						$counts->{$status} = 0;
					}
				}

				return $counts;
			}

			// Let third party filter the counts. Useful for module or external plugins.
			$counts = apply_filters( "yith_wcmv_vendor_filter_count_post_{$type}", false, $this->vendor );
			if ( false === $counts ) {

				$result = $wpdb->get_results( // phpcs:ignore
					$wpdb->prepare(
						"SELECT post_status, COUNT( DISTINCT ID ) AS count 
						FROM {$wpdb->posts} AS p 
						INNER JOIN {$wpdb->term_relationships} AS tr ON tr.object_id = p.ID
						INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
						WHERE tt.taxonomy = %s AND tt.term_id = %d AND p.post_type = %s GROUP BY p.post_status",
						YITH_Vendors_Taxonomy::TAXONOMY_NAME,
						$this->vendor->get_id(),
						$type
					)
				);

				$posts  = ! empty( $result ) ? $result : array();
				$counts = array_fill_keys( get_post_stati(), 0 );

				foreach ( $posts as $post ) {
					$counts[ $post->post_status ] = $post->count;
				}
			}

			$counts = (object) $counts;
			wp_cache_set( $cache_key, $counts, 'counts' );

			return $counts;
		}

		/**
		 * Restrict vendors from editing other vendors' posts
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function restrict_edit_vendor_content() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_POST['post_ID'] ) || empty( $_GET['post'] ) || ! apply_filters( 'yith_wcmv_vendor_disabled_manage_other_vendors_posts', true ) ) {
				return;
			}

			$post_id = absint( $_GET['post'] );
			$post    = get_post( $post_id );

			if ( empty( $post ) || ! in_array( $post->post_type, $this->get_allowed_post_type(), true ) ) {
				return;
			}

			$post_type = $post->post_type;
			if ( false !== has_action( "yith_wcmv_restrict_edit_{$post_type}_vendor" ) ) {
				do_action( "yith_wcmv_restrict_edit_{$post_type}_vendor", $post, $this->vendor );
			} else {

				$current_vendor = yith_wcmv_get_vendor( $post_id, 'post' );
				// Let's filter current vendor associated with the post.
				$current_vendor = apply_filters( 'yith_wcmv_vendor_dashboard_vendor_in_post', $current_vendor, $post_id, $post_type );

				if ( $current_vendor && absint( $this->vendor->get_id() ) !== absint( $current_vendor->get_id() ) ) {
					$post_type_obj = get_post_type_object( $post_type );
					// translators: %s stand for the post type name.
					wp_die( sprintf( esc_html__( 'You do not have permission to edit this %s.', 'yith-woocommerce-product-vendors' ), strtolower( $post_type_obj->labels->singular_name ) ) );
				}
			}

			do_action( 'yith_wcmv_restrict_edit_content_vendor', $post, $this->vendor );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Restrict add products for vendors if options is enabled
		 *
		 * @since  4.0.0
		 * @param WP_Screen $screen Current screen instance.
		 * @return void
		 */
		public function restrict_add_vendor_product( $screen ) {
			if ( 'add' === $screen->action && 'product' === $screen->post_type && ! $this->vendor->can_add_products() ) {
				$products_limit = apply_filters( 'yith_wcmv_vendors_products_limit', get_option( 'yith_wpv_vendors_product_limit', 25 ), $this->vendor );
				// translators: %1$s is the product number limit for vendor, %2$s and %3$s are open and close html tag for an anchor.
				wp_die( sprintf( __( 'You are not allowed to create more than %1$s products. %2$sClick here to return to your admin area%3$s.', 'yith-woocommerce-product-vendors' ), $products_limit, '<a href="' . esc_url( 'edit.php?post_type=product' ) . '">', '</a>' ) );
			}
		}

		/**
		 * Remove 3rd-party plugin meta-boxes
		 *
		 * @since  4.0.0
		 * @retur  void
		 */
		public function remove_meta_boxes() {

			$to_remove = array(
				array(
					'id'      => 'ckwc',
					'screen'  => null,
					'context' => 'side',
				),
			);

			if ( 'no' === get_option( 'yith_wpv_vendors_option_product_tags_management', 'yes' ) ) {
				$to_remove[] = array(
					'id'      => 'tagsdiv-product_tag',
					'screen'  => 'product',
					'context' => 'side',
				);
			}

			// Let filter the meta-boxes to remove.
			$to_remove = apply_filters( 'yith_wcmv_remove_product_metaboxes', $to_remove, $this->vendor );

			foreach ( $to_remove as $r ) {
				if ( ! isset( $r['id'] ) ) {
					continue;
				}

				remove_meta_box( $r['id'], $r['screen'] ?? 'null', $r['context'] ?? 'advanced' );
			}
		}

		/**
		 * Remove Essential Grid Meta-box
		 *
		 * @since    4.0.0
		 * @return   void
		 */
		public function remove_ess_grid_metabox() {
			remove_meta_box( 'eg-meta-box', 'product', 'normal' );
		}

		/**
		 * Add vendor taxonomy to post types
		 *
		 * @since  4.0.0
		 * @param integer|string $post_id The post ID.
		 * @param WP_Post        $post    The post object.
		 * @return void
		 */
		public function add_vendor_taxonomy_to_post_type( $post_id, $post ) {
			global $wp_taxonomies, $yith_wcmv_cache;

			// Let's skip taxonomy association.
			$post_type = $post ? $post->post_type : '';
			if ( ! $post_type || ! in_array( $post_type, $this->get_allowed_post_type(), true ) || ! apply_filters( "yith_wcmv_add_vendor_taxonomy_to_{$post_type}", true, $post, $this->vendor ) || ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			// Double check if post type has the taxonomy registered.
			if ( ! isset( $wp_taxonomies[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] ) || ! in_array( $post_type, $wp_taxonomies[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ]->object_type, true ) ) {
				return;
			}

			wp_set_object_terms( $post_id, $this->vendor->get_slug(), YITH_Vendors_Taxonomy::TAXONOMY_NAME );
			// Delete vendor cache for post type.
			$yith_wcmv_cache->delete_vendor_cache( $this->vendor->get_id() );
		}

		/**
		 * Short circuit add new attribute via AJAX function.
		 * Refer to public_html/wp-content/plugins/woocommerce/includes/class-wc-ajax.php:592
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function add_new_attribute() {
			check_ajax_referer( 'add-attribute', 'security' );

			if ( isset( $_POST['taxonomy'], $_POST['term'] ) ) {
				$taxonomy = esc_attr( wp_unslash( $_POST['taxonomy'] ) ); // phpcs:ignore
				$term     = wc_clean( wp_unslash( $_POST['term'] ) ); // phpcs:ignore

				if ( taxonomy_exists( $taxonomy ) ) {

					$result = wp_insert_term( $term, $taxonomy );

					if ( is_wp_error( $result ) ) {
						wp_send_json(
							array(
								'error' => $result->get_error_message(),
							)
						);
					} else {
						$term = get_term_by( 'id', $result['term_id'], $taxonomy );
						wp_send_json(
							array(
								'term_id' => $term->term_id,
								'name'    => $term->name,
								'slug'    => $term->slug,
							)
						);
					}
				}
				wp_die( -1 );
			}
		}

		/**
		 * If a user is a vendor admin remove the woocommerce prevent admin access
		 *
		 * @since  4.0.0
		 * @param boolean $prevent_access Current value: true to prevent admin access, false otherwise.
		 * @return boolean
		 */
		public function prevent_admin_access( $prevent_access ) {
			if ( $this->vendor->is_user_admin() || ! $this->vendor->has_status( 'enabled' ) ) {
				return false;
			}

			return $prevent_access;
		}

		/**
		 * Filter JSON found products for current vendor
		 *
		 * @since  4.0.0
		 * @param array $products An array of current found products.
		 * @return array
		 */
		public function filter_json_search_found_products( $products ) {
			$vendor_products = $this->vendor->get_products( array( 'post_type' => array( 'product', 'product_variation' ) ) );
			if ( empty( $vendor_products ) ) {
				return array();
			}

			$products = array_intersect_key( $products, array_flip( $vendor_products ) );
			return $products;
		}

		/**
		 * Filter woocommerce_admin_get_feature_config to prevent forbidden request
		 *
		 * @since  4.8.0
		 * @param array $features An array of WC admin features.
		 * @return array
		 */
		public function filter_admin_get_feature_config( $features ) {
			unset( $features['marketing'], $features['onboarding'], $features['onboarding-tasks'], $features['store-alerts'] );
			return $features;
		}

		/**
		 * Customize WooCommerce export query.
		 *
		 * @since  4.1.0
		 * @param array $args An array of query arguments.
		 * @return array
		 */
		public function customize_export_query( $args ) {
			$args['include'] = $this->vendor->get_products( array( 'post_type' => array( 'product', 'product_variation' ) ) );
			return $args;
		}

		/**
		 * Customize import item data.
		 * Check for ID|SKU and avoid update no vendor products.
		 *
		 * @since  4.1.0
		 * @param array $data The data to import.
		 * @return array
		 * @throws Exception If product limit is reached.
		 */
		public function customize_import_item_data( $data ) {
			$product_id = ! empty( $data['id'] ) ? absint( $data['id'] ) : 0;
			if ( ! $this->is_vendor_product( $product_id ) ) {
				unset( $data['id'], $product_id );
			}

			// Get product ID from SKU if set.
			if ( ! empty( $data['sku'] ) ) {
				$product_id = wc_get_product_id_by_sku( $data['sku'] );
				if ( ! empty( $product_id ) && ! $this->is_vendor_product( $product_id ) ) {
					unset( $data['sku'], $product_id );
				}
			}

			// Check if it is an update, otherwise check for product limit.
			$product = ! empty( $product_id ) ? wc_get_product( $product_id ) : false;
			if ( ! $product && ! $this->vendor->can_add_products() ) {
				throw new Exception( __( 'Products limit reached. You cannot import more products.', 'yith-woocommerce-product-vendors' ) );
			}

			// Double check for featured.
			if ( 'no' === get_option( 'yith_wpv_vendors_option_featured_management', 'no' ) ) {
				unset( $data['featured'] );
			}

			// Double check for product tags.
			if ( 'no' === get_option( 'yith_wpv_vendors_option_product_tags_management', 'yes' ) ) {
				unset( $data['tag_ids'], $data['tag_ids_spaces'] );
			}

			// Check for up-sell and cross-sells. They must be vendor products.
			if ( ! empty( $data['upsell_ids'] ) ) {
				$data['upsell_ids'] = array_filter( $data['upsell_ids'], array( $this, 'is_vendor_product' ) );
			}
			if ( ! empty( $data['cross_sell_ids'] ) ) {
				$data['cross_sell_ids'] = array_filter( $data['cross_sell_ids'], array( $this, 'is_vendor_product' ) );
			}

			return $data;
		}

		/**
		 * Check if given product ID is a vendor product.
		 *
		 * @since  4.1.0
		 * @param integer $product_id The product ID to check.
		 * @return boolean
		 */
		public function is_vendor_product( $product_id ) {
			if ( empty( $product_id ) ) {
				return false;
			}

			$products = $this->vendor->get_products(
				array(
					'post_type'   => array( 'product', 'product_variation' ),
					'post_status' => array( 'publish', 'pending', 'private', 'draft', 'trash' ),
				)
			);

			return ! empty( $products ) && in_array( $product_id, $products, true );
		}

		/**
		 * Customize WooCommerce import process.
		 * Assign imported products to current vendor
		 *
		 * @since  4.1.0
		 * @param WC_Product $product The product created.
		 * @param array      $data    The data imported.
		 * @return void
		 */
		public function set_vendor_to_imported_product( $product, $data ) {
			// Set taxonomy term for product.
			wp_set_object_terms( $product->get_id(), $this->vendor->get_slug(), YITH_Vendors_Taxonomy::TAXONOMY_NAME );
			$this->vendor->empty_cache();
		}

		/**
		 * Filter import mapping options
		 *
		 * @since  4.1.0
		 * @param array $options The options map array.
		 * @return array
		 */
		public function filter_import_mapping_options( $options ) {
			if ( 'no' === get_option( 'yith_wpv_vendors_option_product_tags_management', 'yes' ) ) {
				unset( $options['tag_ids'], $options['tag_ids_spaces'] );
			}

			if ( 'no' === get_option( 'yith_wpv_vendors_option_featured_management', 'no' ) ) {
				unset( $options['featured'] );
			}

			return $options;
		}

		/**
		 * Allowed comments for vendor
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function allowed_comments() {
			global $pagenow;
			if ( ! current_user_can( 'moderate_comments' ) ) {
				if ( 'comment.php' === $pagenow || 'edit-comments.php' === $pagenow ) {
					wp_die( '<p>' . esc_html__( 'Sorry, you are not allowed to edit comments.', 'yith-woocommerce-product-vendors' ) . '</p>', 403 );
				}
			}
		}

		/**
		 * Filter comments by vendor product.
		 *
		 * @since  4.0.0
		 * @param object $query The WP_Comment_Query object.
		 * @return void
		 */
		public function filter_reviews_list( $query ) {
			$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( empty( $current_screen ) || ! in_array( $current_screen->id, array( 'edit-comments', 'product_page_product-reviews' ), true ) ) {
				return;
			}

			$vendor_products = $this->vendor->get_products();
			/**
			 * If vendor haven't products there isn't comment to show with array(0) the query will abort.
			 * Another way to do this is to use the_comments hook: add_filter( 'the_comments', '__return_empty_array' );
			 */
			$query->query_vars['post__in'] = ! empty( $vendor_products ) ? $vendor_products : array( 0 );
		}

		/**
		 * Filter product reviews
		 *
		 * @since  4.0.0
		 * @param array   $stats   The comment stats.
		 * @param integer $post_id The post ID.
		 * @return bool|mixed|object
		 */
		public function count_comments( $stats, $post_id ) {
			global $wpdb;

			// Remove WooCommerce filter if any.
			$filter_p = has_filter( 'wp_count_comments', array( 'WC_Comments', 'wp_count_comments' ) );
			if ( false !== $filter_p ) {
				remove_filter( 'wp_count_comments', array( 'WC_Comments', 'wp_count_comments' ), $filter_p );
			}

			if ( empty( $post_id ) ) {

				$count = wp_cache_get( 'comments-0', 'counts' );
				if ( false !== $count ) {
					return $count;
				}

				$products = $this->vendor->get_products();
				$count    = array();
				$total    = 0;
				$approved = array(
					'0'            => 'moderated',
					'1'            => 'approved',
					'spam'         => 'spam',
					'trash'        => 'trash',
					'post-trashed' => 'post-trashed',
				);

				if ( ! empty( $products ) ) {
					$sql   = $wpdb->prepare( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} WHERE comment_type != '%s' AND comment_post_ID IN ( '%s' ) GROUP BY comment_approved", 'order_note', implode( "','", $products ) );
					$count = $wpdb->get_results( $sql, ARRAY_A );

					foreach ( (array) $count as $row ) {
						// Don't count post-trashed toward totals.
						if ( 'post-trashed' !== $row['comment_approved'] && 'trash' !== $row['comment_approved'] ) {
							$total += $row['num_comments'];
						}
						if ( isset( $approved[ $row['comment_approved'] ] ) ) {
							$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
						}
					}
				}

				$stats['total_comments'] = $total;
				foreach ( $approved as $key ) {
					if ( empty( $stats[ $key ] ) ) {
						$stats[ $key ] = 0;
					}
				}

				$stats = (object) $stats;
				wp_cache_set( 'comments-0', $stats, 'counts' );
			}

			return $stats;
		}

		/**
		 * Disable to manage other vendor options
		 *
		 * @since  1.6
		 * @return void
		 */
		public function disabled_manage_other_comments() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( 'load-comment.php' === current_action() && isset( $_GET['c'] ) && ! empty( $_GET['action'] ) && 'editcomment' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
				$comment = get_comment( absint( $_GET['c'] ) );
				if ( ! in_array( $comment->comment_post_ID, $this->vendor->get_products() ) ) {
					// translators: %1$s and %2$s are open and close html tag for an anchor.
					wp_die( sprintf( __( 'You do not have permission to edit this review. %1$sClick here to view and edit your product reviews%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit-comments.php' ) . '">', '</a>' ) );
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Vendor Recent Comments Widgets
		 *
		 * @since  4.0.0
		 */
		public function vendor_recent_comments_widget() {

			$comments        = array();
			$vendor_products = $this->vendor->get_products();
			$total_items     = apply_filters( 'yith_wcmv_vendor_recent_comments_widget_items', 5 );
			$comments_query  = array(
				'number'   => $total_items * 5,
				'offset'   => 0,
				'post__in' => ! empty( $vendor_products ) ? $vendor_products : array( 0 ),
			);
			if ( ! current_user_can( 'edit_posts' ) ) {
				$comments_query['status'] = 'approve';
			}

			while ( count( $comments ) < $total_items && $possible = get_comments( $comments_query ) ) {
				if ( ! is_array( $possible ) ) {
					break;
				}
				foreach ( $possible as $comment ) {
					if ( ! current_user_can( 'read_post', $comment->comment_post_ID ) ) {
						continue;
					}
					$comments[] = $comment;
					if ( count( $comments ) === $total_items ) {
						break 2;
					}
				}
				$comments_query['offset'] += $comments_query['number'];
				$comments_query['number']  = $total_items * 10;
			}

			if ( ! empty( $comments ) ) {
				echo '<div id="latest-comments" class="activity-block">';

				echo '<ul id="the-comment-list" data-wp-lists="list:comment">';
				foreach ( $comments as $comment ) {
					_wp_dashboard_recent_comments_row( $comment );
				}
				echo '</ul>';

				wp_comment_reply( -1, false, 'dashboard', false );
				wp_comment_trashnotice();

				echo '</div>';

			} else {
				echo '<p>' . esc_html__( 'There are no comments yet.', 'yith-woocommerce-product-vendors' ) . '</p>';
			}
		}

		/**
		 * Vendor Recent Reviews Widgets
		 *
		 * @since  4.0.0
		 */
		public function vendor_recent_reviews_widget() {
			global $wpdb;

			$comments = $wpdb->get_results(
				"
                SELECT *, SUBSTRING(comment_content,1,100) AS comment_excerpt
                FROM $wpdb->comments
                LEFT JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
                WHERE comment_approved = '1'
                AND comment_type = ''
                AND post_password = ''
                AND post_type = 'product'
                AND comment_post_ID IN ( '" . implode( "','", $this->vendor->get_products( array( 'fields' => 'ids' ) ) ) . "' )
                ORDER BY comment_date_gmt DESC
                LIMIT 8"
			);

			if ( $comments ) {
				echo '<ul>';
				foreach ( $comments as $comment ) {

					echo '<li>';

					echo get_avatar( $comment->comment_author, '32' );

					$rating = get_comment_meta( $comment->comment_ID, 'rating', true );

					echo '<div class="star-rating" title="' . esc_attr( $rating ) . '"><span style="width:' . ( $rating * 20 ) . '%">' . $rating . ' ' . esc_html__( 'out of 5', 'yith-woocommerce-product-vendors' ) . '</span></div>';

					echo '<h4 class="meta"><a href="' . get_permalink( $comment->ID ) . '#comment-' . absint( $comment->comment_ID ) . '">' . esc_html__( apply_filters( 'woocommerce_admin_dashboard_recent_reviews', $comment->post_title, $comment ) ) . '</a> ' . esc_html__( 'reviewed by', 'yith-woocommerce-product-vendors' ) . ' ' . esc_html( $comment->comment_author ) . '</h4>';
					echo '<blockquote>' . wp_kses_data( $comment->comment_excerpt ) . ' [...]</blockquote></li>';

				}
				echo '</ul>';
			} else {
				echo '<p>' . esc_html__( 'There are no product reviews yet.', 'yith-woocommerce-product-vendors' ) . '</p>';
			}
		}
	}
}
