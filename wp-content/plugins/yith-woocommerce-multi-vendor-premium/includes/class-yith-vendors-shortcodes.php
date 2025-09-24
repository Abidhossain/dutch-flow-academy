<?php
/**
 * YITH Multi Vendor shortcodes class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Shortcodes' ) ) {
	/**
	 * Plugin shortcodes class.
	 */
	class YITH_Vendors_Shortcodes {

		/**
		 * Add Shortcodes
		 *
		 * @since  1.7
		 * @return void
		 */
		public static function load() {

			// Support for YITH WooCommerce Customize My Account Page.
			add_filter( 'yith_wcmap_is_my_account_page', __CLASS__ . '::is_my_account_page', 15 );

			$shortcodes = array(
				'yith_wcmv_list'                => __CLASS__ . '::vendors_list',
				'yith_wcmv_become_a_vendor'     => __CLASS__ . '::become_a_vendor',
				'yith_wcmv_vendor_name'         => __CLASS__ . '::vendor_name',
				'yith_wcmv_vendor_products'     => __CLASS__ . '::vendor_products',
				'yith_wcmv_vendor_store_header' => __CLASS__ . '::vendor_store_header',
			);

			foreach ( $shortcodes as $shortcode => $callback ) {
				add_shortcode( $shortcode, $callback );
			}
		}

		/**
		 * Print vendors list shortcodes
		 *
		 * @since  1.7
		 * @param array $sc_args (Optional) The Shortcode args.
		 * @return mixed
		 */
		public static function vendors_list( $sc_args = array() ) {
			$default = array(
				'per_page'                => -1,
				'hide_no_products_vendor' => 'false',
				'show_description'        => 'false',
				'description_lenght'      => 40,
				'vendor_image'            => 'store',
				'orderby'                 => 'name', // Allowed values: name, slug, term_group, term_id, id, description.
				'order'                   => 'ASC', // Allowed values: ASC or DESC.
				'include'                 => array(),
			);

			$sc_args = wp_parse_args( $sc_args, $default );

			$vendors_args = array(
				'status'     => 'enabled',
				'order'      => $sc_args['order'],
				'orderby'    => $sc_args['orderby'],
				'include'    => $sc_args['include'],
				'hide_empty' => 'true' === $sc_args['hide_no_products_vendor'],
			);

			$paged    = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
			$per_page = ( -1 !== intval( $sc_args['per_page'] ) ) ? absint( $sc_args['per_page'] ) : 0;

			if ( ! empty( $sc_args['per_page'] ) ) {
				$pagination_args = array(
					'pagination' => array(
						'offset' => ( $paged - 1 ) * absint( $sc_args['per_page'] ),
						'number' => $per_page,
					),
				);
				$vendors_args    = array_merge( $vendors_args, $pagination_args );
			}

			$vendors_args = apply_filters( 'yith_wcmv_vendor_list_shortcode_args', $vendors_args );
			$vendors      = yith_wcmv_get_vendors( $vendors_args );
			$total        = yith_wcmv_count_vendors( $vendors_args );
			$total_pages  = ( $per_page > 0 ) ? ceil( $total / $per_page ) : 1;

			$args = array(
				'vendors'          => $vendors,
				'paginate'         => array(
					'current' => $paged,
					'total'   => $total_pages,
				),
				'show_total_sales' => in_array( 'sales', (array) get_option( 'yith_wpv_vendor_info_to_show', array() ), true ),
				'sc_args'          => $sc_args,
				'icons'            => yith_wcmv_get_font_awesome_icons(),
				'socials_list'     => YITH_Vendors()->get_social_fields(),
			);

			ob_start();
			yith_wcmv_get_template( 'vendors-list', $args, 'shortcodes' );

			return ob_get_clean();
		}

		/**
		 * Show vendor name
		 *
		 * @since  2.2.3
		 * @param array $sc_args (Optional) The shortcode args array.
		 * @return string
		 */
		public static function become_a_vendor( $sc_args = array() ) {

			ob_start();

			// If user is not logged in, do shortcode [woocommerce_my_account].
			if ( ! is_user_logged_in() ) {
				if ( apply_filters( 'yith_wcmv_skip_show_my_account_in_become_a_vendor_shortcode', false ) ) {
					do_action( 'yith_wcmv_show_alternative_content_for_vendors_in_become_a_vendor_shortcode' );
				} else {
					echo do_shortcode( '[woocommerce_my_account]' );
				}
			} else {
				$user   = wp_get_current_user();
				$vendor = yith_wcmv_get_vendor( $user->ID, 'user' );

				$is_customer_or_subscriber = in_array( 'subscriber', $user->roles, true ) || in_array( 'customer', $user->roles, true ) || ( count( array_intersect( apply_filters( 'yith_wcmv_custom_role_to_access_to_become_a_vendor_form', array() ), $user->roles ) ) > 0 );
				$have_no_roles             = empty( $user->roles );
				$can_create_vendor_account = false;

				if ( ( ! $vendor || ! $vendor->is_valid() ) && ( $is_customer_or_subscriber || $have_no_roles ) || current_user_can( 'manage_woocommerce' ) ) {
					$can_create_vendor_account = true;
				}

				if ( $can_create_vendor_account ) {
					$fields = YITH_Vendors_Registration_Form::get_fields_frontend();
					if ( ! empty( $fields ) ) {
						$become_a_vendor_label = sprintf( '%s %s', esc_attr_x( 'Become a', '[part of:] Become a vendor', 'yith-woocommerce-product-vendors' ), YITH_Vendors_Taxonomy::get_singular_label( 'strtolower' ) );
						$args                  = array(
							'fields'                => $fields,
							'become_a_vendor_label' => apply_filters( 'yith_wcmv_become_a_vendor_button_label', $become_a_vendor_label ),
						);
						yith_wcmv_get_template( 'become-a-vendor', $args, 'shortcodes' );
					}
				}
			}

			return ob_get_clean();
		}

		/**
		 * Show vendor name
		 *
		 * @since  2.2.3
		 * @param array $sc_args The Shortcode args.
		 * @return string
		 */
		public static function vendor_name( $sc_args = array() ) {
			$default = array(
				'show_by'  => 'vendor',
				'value'    => 0,
				'type'     => 'link',
				'category' => '',
			);

			$sc_args = wp_parse_args( $sc_args, $default );

			$vendor = yith_wcmv_get_vendor( $sc_args['value'], $sc_args['show_by'] );

			ob_start();

			if ( $vendor && $vendor->is_valid() ) {
				$use_link = 'link' === $sc_args['type'];
				?>
				<span class="by-vendor-name">
					<?php if ( $use_link ) : ?>
						<?php $vendor_url = ! empty( $sc_args['category'] ) ? add_query_arg( array( 'product_cat' => $sc_args['category'] ), $vendor->get_url() ) : $vendor->get_url(); ?>
					<a class="by-vendor-name-link" href="<?php echo esc_url( $vendor_url ); ?>">
					<?php endif; ?>

					<?php echo esc_html( $vendor->get_name() ); ?>

					<?php if ( $use_link ) : ?>
						</a>
				<?php endif; ?>
				</span>
				<br>
				<?php
			}

			return ob_get_clean();
		}

		/**
		 * Check if current page is the become a vendor page
		 *
		 * @since  3.3.2
		 * @return boolean True if the current page is the become a vendor page, false otherwise.
		 */
		public static function is_become_a_vendor_page() {
			return is_page( get_option( 'yith_wpv_become_a_vendor_page_id', 0 ) );
		}

		/**
		 * Check if current page is the become a vendor page
		 * if yes, set it like My Account
		 * Support for YITH WooCommerce Customize My Account Page
		 *
		 * @since  3.3.2
		 * @param boolean $is_my_account_page Default check value.
		 * @return boolean True if the current page is the become a vendor page, false otherwise
		 */
		public static function is_my_account_page( $is_my_account_page ) {
			return self::is_become_a_vendor_page() ? true : $is_my_account_page;
		}

		/**
		 * Show vendor name
		 *
		 * @since  2.2.3
		 * @param array $sc_args (Optional) The shortcode array args. Default empty array.
		 * @return string
		 */
		public static function vendor_products( $sc_args = array() ) {
			if ( is_singular( 'product' ) ) {
				$vendor            = yith_wcmv_get_vendor( 'current', 'product' );
				$default_vendor_id = ( $vendor && $vendor->is_valid() ) ? $vendor->get_id() : 0;
			} else {
				$term              = get_queried_object();
				$default_vendor_id = $term->term_id;
			}

			$default = apply_filters(
				'yith_wcmv_shortcode_vendor_products_default_args',
				array(
					'vendor_id' => $default_vendor_id,
					'show_by'   => 'vendor',
				)
			);

			$sc_args = wp_parse_args( $sc_args, $default );
			if ( empty( $sc_args['vendor_id'] ) ) {
				return false;
			}

			$vendor = apply_filters( 'yith_wcmv_get_vendor_name_shortcode', yith_wcmv_get_vendor( $sc_args['vendor_id'], $sc_args['show_by'] ), $sc_args );

			ob_start();

			if ( $vendor && $vendor->is_valid() ) {
				$products = $vendor->get_products();
				if ( ! empty( $products ) ) {
					$extra_args = '';
					foreach ( $sc_args as $sc_att => $sc_value ) {
						$extra_args .= sprintf( ' %s="%s"', $sc_att, $sc_value );
					}

					$shortcode = sprintf( '[products ids="%s"%s]', implode( ',', $products ), $extra_args );

					echo do_shortcode( $shortcode );
				}
			}

			return ob_get_clean();
		}

		/**
		 * Show vendor name
		 *
		 * @since  3.0
		 * @param array $sc_args (Optional) The Shortcode array args. Default empty array.
		 * @return string
		 */
		public static function vendor_store_header( $sc_args = array() ) {
			if ( ! yith_wcmv_is_vendor_page() ) {
				return '';
			}

			ob_start();
			YITH_Vendors()->frontend->add_store_page_header();

			return ob_get_clean();
		}
	}
}
