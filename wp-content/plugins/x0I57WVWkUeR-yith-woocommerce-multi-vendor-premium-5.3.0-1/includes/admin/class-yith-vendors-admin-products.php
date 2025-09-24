<?php
/**
 * YITH Vendors Admin Products Helper Class.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

/*
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Admin_Products' ) ) {
	/**
	 * YITH Vendors Admin Products Helper class.
	 */
	class YITH_Vendors_Admin_Products {

		/**
		 * Current vendor instance
		 *
		 * @var null | YITH_Vendor
		 */
		protected $vendor = null;

		/**
		 * Construct
		 *
		 * @since  4.0.0
		 */
		public function __construct() {

			if ( current_user_can( 'manage_woocommerce' ) ) {
				$this->add_product_commissions_tab();

				// Assign vendor taxonomy to product.
				add_action( 'woocommerce_before_product_object_save', array( $this, 'add_vendor_taxonomy_to_product' ), 10, 2 );

				add_action( 'woocommerce_product_quick_edit_end', array( $this, 'quick_edit_render' ) );
				add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'quick_edit_render' ) );
				add_action( 'admin_menu', array( $this, 'products_to_approve' ), 99 );
				// Enqueue custom scripts only in products list table.
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_product_list_table_style' ), 20 );
			}

			add_action( 'yith_wcmv_vendor_limited_access_dashboard_hooks', array( $this, 'vendor_dashboard_hooks' ), 10, 1 );
		}

		/**
		 * If current user can manage_woocommerce add product commissions rate meta tab
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function add_product_commissions_tab() {

			if ( ! apply_filters( 'yith_wcmv_show_single_product_commission_tab_capability', true ) ) {
				return;
			}

			add_filter( 'woocommerce_product_data_tabs', array( $this, 'commission_tab' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'commission_tab_content' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'commission_tab_save' ), 10, 2 );
		}

		/**
		 * Enqueue custom product list table style
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function enqueue_product_list_table_style() {
			wp_add_inline_style( 'woocommerce_admin_styles', 'table.wp-list-table.posts th.column-taxonomy-yith_shop_vendor,table.wp-list-table.posts td.column-taxonomy-yith_shop_vendor{width: 10%;}' );
		}

		/**
		 * Add vendor taxonomy to product
		 *
		 * @since  4.0.0
		 * @param WC_Product $product The product object.
		 * @return void
		 */
		public function add_vendor_taxonomy_to_product( $product ) {
			// phpcs:disable WordPress.Security.NonceVerification
			global $yith_wcmv_cache;

			$current_vendor = yith_wcmv_get_vendor( 'current', 'user' );
			$taxonomy       = YITH_Vendors_Taxonomy::TAXONOMY_NAME;

			// Check if superuser, exclude variation and check if tax is set.
			if ( ! isset( $_REQUEST['yith_vendor_slug_qe'] ) || ! current_user_can( 'manage_woocommerce' ) || $product->is_type( 'variation' ) || ! apply_filters( 'yith_wcmv_add_vendor_taxonomy_to_product', true, $product, $current_vendor ) ) {
				return;
			}

			$vendor_slug = sanitize_text_field( wp_unslash( $_REQUEST['yith_vendor_slug_qe'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( empty( $vendor_slug ) ) {
				$current_vendor = yith_wcmv_get_vendor( $product->get_id(), 'product' );
				if ( $current_vendor && $current_vendor->is_valid() ) {
					// Set taxonomy term for product.
					wp_remove_object_terms( $product->get_id(), $current_vendor->get_slug(), $taxonomy );
					// Delete vendor cache.
					$yith_wcmv_cache->delete_vendor_cache( $current_vendor->get_id() );
				}
			} elseif ( '-1' !== $vendor_slug ) {
				$new_vendor = yith_wcmv_get_vendor( $vendor_slug, 'vendor' );
				// Set taxonomy term for product.
				wp_set_object_terms( $product->get_id(), $new_vendor->get_slug(), $taxonomy );
				// Delete vendor cache.
				$yith_wcmv_cache->delete_vendor_cache( $new_vendor->get_id() );
			}
			// phpcs:enable WordPress.Security.NonceVerification

			// Backward compatibility with old action.
			if ( has_action( 'yith_wcmv_save_post_product' ) ) {
				do_action_deprecated(
					'yith_wcmv_save_post_product',
					array(
						$product->get_id(),
						get_post( $product->get_id() ),
						$current_vendor,
					),
					'4.0.0'
				);
			}
		}

		/**
		 * Register actions for vendor with limited access
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor Current vendor instance.
		 * @return void
		 */
		public function vendor_dashboard_hooks( $vendor ) {
			$this->vendor = $vendor;
			// Filter product types based on vendor permissions.
			add_filter( 'product_type_selector', array( $this, 'filter_product_type' ), 99, 1 );
			add_action( 'pre_get_posts', array( $this, 'filter_products_content' ), 25, 1 );
			// add_filter( 'wp_count_posts', array( $this, 'filter_count_products' ), 20, 3 );
			add_action( 'yith_wcmv_restrict_edit_content_vendor', array( $this, 'restrict_edit_vendor_product' ), 0 );

			// Set product in pending for admin review.
			add_action( 'woocommerce_before_product_object_save', array( $this, 'set_product_to_pending_review' ), 20, 1 );
			// Remove add new product link from menu if needed.
			add_action( 'yith_wcmv_filtered_vendor_menu_items', array( $this, 'remove_add_new_product' ), 10 );
			// Enabled duplicate product for vendor.
			add_filter( 'woocommerce_duplicate_product_capability', array( $this, 'duplicate_product_capability' ) );
			add_filter( 'pre_untrash_post', array( $this, 'pre_untrash_product' ), 10, 2 );
			// Show/hide featured column in product list table.
			add_filter( 'manage_product_posts_columns', array( $this, 'featured_product_column' ), 15 );
			// Add inline style for products section.
			add_action( 'admin_enqueue_scripts', array( $this, 'add_inline_style' ), 20 );
		}

		/**
		 * Filter available product types based on vendor permissions
		 *
		 * @since 5.0.0
		 * @param array $types An array of available product type.
		 * @return array
		 */
		public function filter_product_type( $types ) {

			$permission_types = get_option( 'yith_wpv_vendors_can_sell', array() );
			$disable_types    = array_filter(
				$permission_types,
				function ( $value ) {
					return 'no' === $value;
				}
			);

			return array_diff_key( $types, array_flip( array_keys( $disable_types ) ) );
		}

		/**
		 * Filter content based on current vendor
		 *
		 * @since  4.0.0
		 * @param WP_Query $query The Wp_Query instance.
		 * @return void
		 */
		public function filter_products_content( &$query ) {

			// If this is not an allowed post type, exit.
			$post_type = $query->get( 'post_type' );

			if ( ! $post_type || 'product' !== $post_type ) {
				return;
			}

			$conditions = $query->get( 'tax_query' );
			if ( ! is_array( $conditions ) ) {
				$conditions = array();
			}

			$conditions[] = array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => YITH_Vendors()->products->get_disabled_products_types(),
				'operator' => 'NOT IN',
			);

			$query->set( 'tax_query', $conditions );
		}

		/**
		 * Restrict vendors from editing other vendors' posts
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function restrict_edit_vendor_product() {
			// phpcs:disable WordPress.Security.NonceVerification
			$post_id = absint( $_GET['post'] );
			$product = $post_id ? wc_get_product( $post_id ) : false;

			if ( empty( $product ) ) {
				return;
			}

			if ( in_array( $product->get_type(), YITH_Vendors()->products->get_disabled_products_types(), true ) ) {
				wp_die( sprintf( esc_html__( 'You do not have permission to edit this product type.', 'yith-woocommerce-product-vendors' ) ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Quick Edit output render
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function quick_edit_render() {

			if ( ! apply_filters( 'yith_wcmv_quick_bulk_edit_enabled', true ) ) {
				return;
			}

			$vendors        = yith_wcmv_get_vendors( array( 'number' => -1 ) );
			$singular_label = YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' );
			// translators: %s stand for vendor label.
			$no_vendor = sprintf( __( 'No %s', 'yith-woocommerce-product-vendors' ), strtolower( $singular_label ) );

			?>
			<div class="alignleft vendor_field clear">
				<label>
					<span class="title"><?php echo esc_html( $singular_label ); ?></span>
					<span class="input-text-wrap">
						<select class="vendor-select" name="yith_vendor_slug_qe">
							<option value="-1" id="vendor_no_change"><?php esc_html_e( '— No Change —', 'yith-woocommerce-product-vendors' ); ?></option>
							<option value=""><?php echo esc_html( $no_vendor ); ?></option>
							<?php
							foreach ( $vendors as $vendor ) {
								echo '<option value="' . esc_attr( $vendor->get_slug() ) . '">' . esc_html( $vendor->get_name() ) . '</option>';
							}
							?>
						</select>
					</span>
				</label>
			</div>
			<?php
		}

		/**
		 * Add commission tab in single product "Product Data" section
		 *
		 * @since  4.0.0
		 * @param array $product_data_tabs Product data tabs.
		 * @return array
		 */
		public function commission_tab( $product_data_tabs ) {
			$product_data_tabs['commissions'] = array(
				'label'  => __( 'Commission', 'yith-woocommerce-product-vendors' ),
				'target' => 'yith_wpv_single_commission',
				'class'  => array(),
			);

			return $product_data_tabs;
		}

		/**
		 * Output commission tab in single product "Product Data" section
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function commission_tab_content() {
			global $post;
			$product = apply_filters( 'yith_wcmv_single_product_commission_value_object', $post );

			if ( ! $product instanceof WC_Product ) {
				$product = wc_get_product( $post );
			}

			if ( $product instanceof WC_Product ) {
				$args = apply_filters(
					'yith_wcmv_product_commission_field_args',
					array(
						'field_args' => array(
							'id'                => 'yith_wpv_product_commission',
							'label'             => __( 'Product commission', 'yith-woocommerce-product-vendors' ),
							'desc_tip'          => 'true',
							'description'       => __( 'You can set a specific commission for a single product. Set zero or keep this field blank to use the vendor commission.', 'yith-woocommerce-product-vendors' ),
							'value'             => $product->get_meta( '_product_commission' ),
							'type'              => 'number',
							'custom_attributes' => array(
								'step' => 0.1,
								'min'  => 0,
								'max'  => 100,
							),
						),
					)
				);

				yith_wcmv_include_admin_template( 'product-data-commission', $args );
			}
		}

		/**
		 * Save product commission rate meta
		 *
		 * @since  4.0.0
		 * @param integer $post_id The post id.
		 * @param WP_Post $post    The post object.
		 * @return void
		 */
		public function commission_tab_save( $post_id, $post ) {
			// phpcs:disable WordPress.Security.NonceVerification
			// Save Product Commission Rate.
			$product = wc_get_product( $post_id );

			if ( $product instanceof WC_Product ) {
				if ( ! empty( $_POST['yith_wpv_product_commission'] ) ) {
					$rate = floatval( $_POST['yith_wpv_product_commission'] );
					if ( $rate > 100 ) { // must be lower or equal to 100.
						$rate = 100;
					}
					$product->add_meta_data( '_product_commission', $rate, true );
				} else {
					$product->delete_meta_data( '_product_commission' );
				}

				$product->save_meta_data();
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Add a bubble notification icon for pending products
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function products_to_approve() {
			global $menu, $submenu;

			$num_pending_products = wp_cache_get( 'count_pending_products', 'yith_wcmv' );
			if ( false === $num_pending_products ) {
				$products = get_posts(
					array(
						'post_type'      => 'product',
						'post_status'    => 'pending',
						'posts_per_page' => -1,
					)
				);

				$num_pending_products = count( $products );
				wp_cache_set( 'count_pending_products', $num_pending_products, 'yith_wcmv' );
			}

			if ( $num_pending_products > 0 ) {
				$bubble       = " <span class='awaiting-mod count-{$num_pending_products}'><span class='pending-count'>{$num_pending_products}</span></span>";
				$products_uri = htmlspecialchars( add_query_arg( array( 'post_type' => 'product' ), 'edit.php' ) );

				foreach ( $menu as $key => $value ) {
					if ( $menu[ $key ][2] === $products_uri && $num_pending_products > 0 ) {
						$menu[ $key ][0] .= $bubble; // phpcs:ignore
					}
				}

				foreach ( $submenu as $key => $value ) {
					$submenu_items = $submenu[ $key ];
					foreach ( $submenu_items as $position => $value ) {
						if ( $submenu[ $key ][ $position ][2] === $products_uri ) {
							$submenu[ $key ][ $position ][0] .= $bubble; // phpcs:ignore

							return;
						}
					}
				}
			}
		}

		/**
		 * Set product to pending review on save if needed
		 *
		 * @since  4.0.0
		 * @param WC_Product $product The product object.
		 * @return void
		 */
		public function set_product_to_pending_review( $product ) {
			static $processed = array();

			if (
				did_action( 'woocommerce_before_save_order_items' ) ||
				( 'no' !== $this->vendor->get_meta( 'skip_review' ) || 'yes' !== get_option( 'yith_wpv_vendors_option_pending_post_status', 'no' ) ) ||
				empty( $product->get_changes() )
			) {
				return;
			}

			if ( $product->is_type( 'variation' ) ) {
				$product = wc_get_product( $product->get_parent_id() );
			}

			if ( ! $product || in_array( $product->get_id(), $processed, true ) ) {
				return;
			}

			$product->set_status( 'pending' );

			WC()->mailer();
			do_action( 'yith_wcmv_product_set_in_pending_review_after_edit', $product->get_id(), $product, $this->vendor );

			$processed[] = $product->get_id();
		}

		/**
		 * Remove add new product from submenu if the vendor limit is active
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function remove_add_new_product() {
			global $submenu;

			// Remove add new product from menu if limit is active.
			if ( ! $this->vendor->can_add_products() ) {
				foreach ( $submenu as $menu_slug => $items ) {
					foreach ( $items as $item ) {
						if ( isset( $item[2] ) && 'post-new.php?post_type=product' === $item[2] ) {
							remove_submenu_page( $menu_slug, $item[2] );
							break;
						}
					}
				}
			}
		}

		/**
		 * Enable duplicate product for vendor
		 *
		 * @since  1.9.6
		 * @param string $cap The capability.
		 * @return string
		 */
		public function duplicate_product_capability( $cap ) {
			if ( $this->vendor->can_add_products() ) {
				$cap = YITH_Vendors_Capabilities::ROLE_ADMIN_CAP;
			}

			return $cap;
		}

		/**
		 * Check if current vendor have products limit and untrash a product
		 *
		 * @since  1.9.6
		 * @param boolean|null $untrash Whether to go forward with untrashing.
		 * @param WP_Post      $post    Post object.
		 * @return boolean|null
		 */
		public function pre_untrash_product( $untrash, $post ) {
			if ( 'product' === $post->post_type && ! $this->vendor->can_add_products() ) {
				$products_limit = apply_filters( 'yith_wcmv_vendors_products_limit', get_option( 'yith_wpv_vendors_product_limit', 25 ), $this->vendor );
				echo '<div class="wp-die-message">';
				// translators: %1$s is the product number limit for vendor, %2$s and %3$s are open and close html tag for an anchor.
				printf( __( 'You are not allowed to create more than %1$s products. %2$sClick here to return to your admin area%3$s.', 'yith-woocommerce-product-vendors' ), $products_limit, '<a href="' . esc_url( 'edit.php?post_type=product' ) . '">', '</a>' );
				echo '</div>';
				$untrash = false;
			}

			return $untrash;
		}

		/**
		 * Show/hide featured column from product list table based on vendor setting
		 *
		 * @since  4.0.0
		 * @param array $columns Array of list table columns.
		 * @return array
		 */
		public function featured_product_column( $columns ) {
			if ( ! $this->vendor->can_handle_featured_products() ) {
				unset( $columns['featured'] );
			}

			// Also remove useless vendor column.
			unset( $columns[ 'taxonomy-' . YITH_Vendors_Taxonomy::TAXONOMY_NAME ] );

			return $columns;
		}

		/**
		 * Add inline style for customize products section.
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function add_inline_style() {
			global $current_screen;
			if ( ! $this->vendor->can_add_products() && ! empty( $current_screen ) && 'product' === $current_screen->post_type ) {
				wp_add_inline_style( 'woocommerce_admin_styles', 'body.post-type-product a.page-title-action{display:none;}' );
			}
		}
	}
}
