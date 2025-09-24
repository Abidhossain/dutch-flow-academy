<?php
/**
 * YITH Vendors Frontend Legacy Class
 *
 * @since      1.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 */

/**
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Frontend_Legacy' ) ) {

	/**
	 * Class YITH_Vendors_Frontend_Legacy
	 */
	abstract class YITH_Vendors_Frontend_Legacy {

		/**
		 * Endpoints class instance
		 *
		 * @since 4.0.0
		 * @var null
		 */
		public $endpoints = null;

		/**
		 * Load sub classes for main Frontend
		 *
		 * @since  4.0.0
		 * @return void
		 */
		protected function load_classes() {
			$this->endpoints = YITH_Vendors_Account_Endpoints::instance();
		}

		/**
		 * Check if the user see a store vendor page
		 *
		 * @since  1.0
		 * @return bool
		 * @deprecated
		 */
		public function is_vendor_page() {
			_deprecated_function( __METHOD__, '4.0.0', 'yith_wcmv_is_vendor_page' );

			return yith_wcmv_is_vendor_page();
		}

		/**
		 * Exclude the not enable vendors to Related products
		 *
		 * @since  1.0
		 * @param array $args The related products query args.
		 * @return mixed|array the query args
		 * @deprecated
		 */
		public function related_products_args( $args ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			global $product;

			$vendor = yith_wcmv_get_vendor( $product, 'product' );

			if ( ! $vendor->is_valid() ) {
				$args['tax_query'] = $this->check_vendors_selling_capabilities( false, false ); // phpcs:ignore
			}

			$related = get_option( 'yith_vendors_related_products', 'vendor' );

			if ( 'disabled' === $related ) {
				return false;
			} elseif ( 'default' === $related ) {
				$args['tax_query'] = $this->check_vendors_selling_capabilities( false, false ); // phpcs:ignore
			} elseif ( 'vendor' === $related ) {
				$args['post__in'] = $vendor->get_products();
			}

			return $args;
		}

		/**
		 * Add total sales product meta
		 *
		 * @since    1.0
		 * @return   void
		 */
		public function woocommerce_product_meta() {
			_deprecated_function( __METHOD__, '4.0.0', 'show_product_total_sales' );
			$this->show_product_total_sales();
		}

		/**
		 * Handle my account form submit
		 *
		 * @since 1.0.0
		 * @deprecated
		 */
		public function check_vendor_terms_of_service() {
			_deprecated_function( __METHOD__, '4.0.0' );
			$this->endpoints->handle_form_submit();
		}

		/**
		 * Add a report abuse link in single product page
		 *
		 * @since    1.0
		 * @return   void
		 * @deprecated
		 */
		public function add_report_abuse_link() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Report_Abuse()->add_report_abuse_link()' );
			YITH_Vendors_Report_Abuse()->add_report_abuse_link();
		}

		/**
		 * Send a report to abuse
		 *
		 * @since    1.0
		 * @return   void
		 * @deprecated
		 */
		public function send_report_abuse() {
			_deprecated_function( __METHOD__, '4.0.0', 'YITH_Vendors_Report_Abuse()->report_an_abuse()' );
			YITH_Vendors_Report_Abuse()->report_an_abuse();
		}

		/**
		 * Add vendor dashboard endpoint in my Account
		 *
		 * @since  4.0.0
		 * @param boolean | YITH_Vendor $vendor (Optional) The vendor object instance or false to get current. Default false.
		 * @return void
		 * @deprecated
		 */
		public function vendor_dashboard_endpoint( $vendor = false ) {
			_deprecated_function( __METHOD__, '4.0.0' );
			$this->endpoints->vendor_dashboard_endpoint();
		}

		/**
		 * Get error message in registration form
		 *
		 * @since    1.7
		 * @param string $field The field that require string error message.
		 * @return   mixed
		 */
		public function get_registration_error_string( $field = 'all' ) {
			_deprecated_function( __METHOD__, '4.0.0' );

			$vat_ssn_label = get_option( 'yith_wpv_vat_label', __( 'VAT/SSN', 'yith-woocommerce-product-vendors' ) );
			$errors        = array(
				'firstname'            => __( 'The first name field is mandatory.', 'yith-woocommerce-product-vendors' ),
				'lastname'             => __( 'The last name field is mandatory.', 'yith-woocommerce-product-vendors' ),
				'location'             => __( 'The store address field is mandatory.', 'yith-woocommerce-product-vendors' ),
				'email'                => __( 'The email field is mandatory.', 'yith-woocommerce-product-vendors' ),
				'not_email'            => __( 'The email address entered is not valid.', 'yith-woocommerce-product-vendors' ),
				'paypal_email'         => __( 'The PayPal email field is mandatory.', 'yith-woocommerce-product-vendors' ),
				'not_paypal_email'     => __( 'The PayPal email address entered is not valid.', 'yith-woocommerce-product-vendors' ),
				'telephone'            => __( 'The phone field is mandatory.', 'yith-woocommerce-product-vendors' ),
				// string added @version 1.13.2.
				'vat_ssn'              => sprintf(
					'%s %s %s',
					_x( 'The', '[frontend]: part of sentence. e.g.: The VAT/SSN field is mandatory', 'yith-woocommerce-product-vendors' ),
					$vat_ssn_label,
					_x( 'field is mandatory.', '[frontend]: part of sentence. e.g.: The VAT/SSN field is mandatory', 'yith-woocommerce-product-vendors' )
				),
				'store_name'           => __( 'Insert the vendor\'s name.', 'yith-woocommerce-product-vendors' ),
				'duplicated'           => __( 'A vendor with this name already exists.', 'yith-woocommerce-product-vendors' ),
				'terms_and_conditions' => __( 'Please, read and accept the Terms & Conditions.', 'yith-woocommerce-product-vendors' ),
				'privacy_policy'       => __( 'Please, read and accept the Privacy Policy.', 'yith-woocommerce-product-vendors' ),
				'antispam'             => __( 'Please, no spam here.', 'yith-woocommerce-product-vendors' ),
			);

			if ( 'all' !== $field ) {
				return isset( $errors[ $field ] ) ? $errors[ $field ] : false;
			} else {
				return $errors;
			}
		}

		/**
		 * Check product vendor coupon
		 *
		 * @arg      WC_Coupon $coupon
		 * @since    1.0
		 * @return   bool true if is possible to apply the coupon for current product, false otherwise
		 */
		public function check_vendor_coupon_product( $valid, $product, $coupon, $values ) {

			_deprecated_function( __METHOD__, '4.0.0' );

			if ( $valid ) {
				$vendor_id = $coupon instanceof WC_Coupon ? $coupon->get_meta( 'vendor_id', true ) : 0;
				if ( ! empty( $vendor_id ) ) {
					/**
					 * percent_products doesn't exists in wc 2.7
					 * Use it only for wc 2.6 or previous
					 */
					$discount_types       = array( 'fixed_product', 'percent_product' );
					$discount_type        = $coupon->get_discount_type();
					$discount_product_ids = $coupon->get_product_ids();
					if ( in_array( $discount_type, $discount_types ) && empty( $discount_product_ids ) ) {

						$vendor = yith_wcmv_get_vendor( $product, 'product' );

						return $vendor && $vendor->is_valid() && $vendor->get_id() === absint( $vendor_id );
					}
				}
			}

			return $valid;
		}

		/**
		 * Check if in the cart there is a product from a vendor
		 *
		 * Use to apply a cart coupon
		 *
		 * @since    1.0
		 * @return   bool true if is possibile to apply the coupon for current product, false otherwise
		 */
		public function vendor_coupon_is_valid( $valid, $coupon ) {

			_deprecated_function( __METHOD__, '4.0.0' );

			$vendor_id            = $coupon->get_meta( 'vendor_id', true );
			$discount_product_ids = $coupon->get_product_ids();

			if ( empty( $vendor_id ) ) {
				return $valid;
			}

			$vendor = yith_wcmv_get_vendor( absint( $vendor_id ), 'vendor' );

			$cart = WC()->cart->get_cart();

			foreach ( $cart as $k => $item ) {
				if ( in_array( $item['product_id'], $vendor->get_products() ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Print vendor store header in archive-product template
		 *
		 * @since    1.0
		 * @return void
		 */
		public function add_store_page_header() {
			global $sitepress;

			if ( ! yith_wcmv_is_vendor_page() ) {
				return;
			}

			$has_wpml     = ! empty( $sitepress ) && function_exists( 'wpml_get_default_language' );
			$default_term = null;
			$term         = get_queried_object();
			if ( $has_wpml ) {
				$term_slug    = yit_wpml_object_id( $term->term_id, YITH_Vendors_Taxonomy::TAXONOMY_NAME, true, wpml_get_default_language() );
				$default_term = get_term( $term_slug, YITH_Vendors_Taxonomy::TAXONOMY_NAME );
			} else {
				$term_slug = $term->slug;
			}

			$vendor = yith_wcmv_get_vendor( $term_slug );
			if ( ! $vendor || ! $vendor->is_valid() || ! $vendor->is_selling_enabled() ) {
				return;
			}

			$info_to_show  = get_option( 'yith_wpv_vendor_info_to_show', array( 'vat-ssn', 'rating' ) );
			$header_skin   = get_option( 'yith_wpv_store_header_style', 'small-box' );
			$show_gravatar = yith_wcmv_show_gravatar( $vendor, 'frontend' );
			$gravatar_size = get_option( 'yith_vendors_gravatar_image_size', 128 );

			$website_label = '';
			$website_url   = in_array( 'website', $info_to_show, true ) ? $vendor->get_meta( 'website' ) : '';
			if ( ! empty( $website_url ) ) {
				$website_label = str_ireplace( array( 'http://', 'https://' ), '', $website_url );
				$parsed        = wp_parse_url( $website_url );
				if ( empty( $parsed['scheme'] ) ) {
					$website_url = 'http://' . ltrim( $website_url, '/' );
				}
			}

			$vendor_description      = $has_wpml ? $default_term->description : $vendor->get_description();
			$store_description_class = apply_filters( 'yith_wcmv_store_descritpion_class', 'store-description-wrapper' );
			$header_img_class        = apply_filters( 'yith_wcmv_header_img_class', array( 'class' => 'store-image' ) );
			$header_image_size       = apply_filters( 'yith_wcmv_get_header_size', YITH_Vendors()->get_image_size( 'header' ) );
			$store_header_image      = '';
			$store_header_image_html = '';

			// Build header image keeping backward template compatibility.
			$use_default_image = get_option( 'yith_wpv_header_use_default_image', 'no-image' );
			if ( 'none' !== $use_default_image ) {
				$default_image           = get_option( 'yith_wpv_header_default_image', YITH_WPV_ASSETS_URL . 'images/vendor-header-placeholder.jpg' );
				$store_header_image      = apply_filters( 'yith_wcmv_default_store_header_image_url', $default_image, $vendor->get_id() ); // Backward compatibility with the old filter.
				$store_header_image_html = $default_image ? sprintf( '<img src="%s" class="store-image" alt>', esc_url( $default_image ) ) : '';
			}

			$vendor_header_image_id = $vendor->get_header_image_id();
			if ( 'no-image' === $use_default_image && $vendor_header_image_id ) {
				$header_image            = wp_get_attachment_image_src( $vendor_header_image_id, $header_image_size );
				$store_header_image      = isset( $header_image[0] ) ? $header_image[0] : '';
				$store_header_image_html = wp_get_attachment_image( $vendor_header_image_id, $header_image_size, false, $header_img_class );
			}

			// Build avatar image.
			$avatar_id = $vendor->get_avatar_id();
			if ( $avatar_id ) {
				$avatar_html = wp_get_attachment_image( $vendor->get_avatar_id(), apply_filters( 'yith_wcmv_get_avatar_size', YITH_Vendors()->get_image_size( 'gravatar' ) ) );
			} else {
				$avatar_html = get_avatar( $vendor->get_owner(), $gravatar_size );
			}

			$args = apply_filters(
				'yith_wcmv_store_header_template_arg',
				array(
					'vendor'                  => $vendor,
					'vendor_description'      => $vendor_description,
					'store_description_class' => $store_description_class,
					'name'                    => $has_wpml ? $default_term->name : $vendor->get_name(),
					'header_skin'             => $header_skin,
					'icons'                   => yith_wcmv_get_font_awesome_icons(),
					'vat'                     => in_array( 'vat-ssn', $info_to_show, true ) ? $vendor->get_meta( 'vat' ) : '',
					'avatar'                  => $avatar_html,
					'location'                => $vendor->get_formatted_address(),
					'store_email'             => $vendor->get_meta( 'store_email' ),
					'telephone'               => $vendor->get_meta( 'telephone' ),
					'vendor_reviews'          => $vendor->get_reviews_average_and_product(),
					'total_sales'             => in_array( 'sales', $info_to_show, true ) ? count( $vendor->get_orders() ) : 0,
					'store_header_image'      => $store_header_image,
					'store_header_image_html' => $store_header_image_html,
					'store_header_class'      => apply_filters( 'yith_wcmv_store_header_class', 'store-header-wrapper' ),
					'socials'                 => $vendor->get_socials(),
					'socials_list'            => YITH_Vendors()->get_social_fields(),
					'website'                 => array(
						'show'  => ( in_array( 'website', $info_to_show, true ) && $website_url ) ? 'yes' : 'no',
						'url'   => $website_url,
						'label' => $website_label,
					),
					// Backward compatibility.
					'owner'                   => $vendor->get_owner( 'all' ),
					'header_image_html'       => 'image',
					'header_img_class'        => $header_img_class,
					'header_image'            => 'double-box' !== $header_skin ? $store_header_image : $store_header_image_html,
					'header_image_class'      => empty( $vendor->get_header_image_id() ) ? 'no-image' : 'with-image',
					'show_total_sales'        => in_array( 'sales', $info_to_show, true ),
					'show_vendor_vat'         => in_array( 'vat-ssn', $info_to_show, true ),
					'show_gravatar'           => $show_gravatar,
					'owner_avatar'            => $avatar_html,
				)
			);

			do_action( 'yith_wcmv_before_vendor_header', $args, $vendor );
			yith_wcmv_get_template( 'store-header', $args, 'woocommerce/loop' );

			/* Vendor Description */
			if ( in_array( 'description', $info_to_show, true ) && ! empty( $vendor_description ) ) {

				$vendor_description = do_shortcode( $vendor_description );
				$vendor_description = call_user_func( '__', $vendor_description, 'yith-woocommerce-product-vendors' );

				$args = array(
					'store_description_class' => $store_description_class,
					'vendor_description'      => $vendor_description,
				);
				yith_wcmv_get_template( 'store-description', $args, 'woocommerce/loop' );
			}
			do_action( 'yith_wcmv_after_vendor_header', $args, $vendor );
		}

		/**
		 * Add vendor name after product title
		 *
		 * @since    1.0
		 * @return   void
		 * @deprecated
		 */
		public function woocommerce_template_vendor_name() {
			_deprecated_function( __METHOD__, '4.0.0' );

			global $product;

			if ( yith_wcmv_is_vendor_page() ) {
				return;
			}

			if (
				( 'yes' !== get_option( 'yith_wpv_vendor_name_in_single', 'yes' ) && is_product() ) ||
				( 'yes' !== get_option( 'yith_wpv_vendor_name_in_categories', 'yes' ) && is_product_taxonomy() ) ||
				( 'yes' !== get_option( 'yith_wpv_vendor_name_in_loop', 'yes' ) && is_shop() ) ||
				! apply_filters( 'yith_wcmv_show_vendor_name_template', true )
			) {
				return;
			}

			if ( ! empty( $product ) && is_object( $product ) ) {
				$vendor = yith_wcmv_get_vendor( $product, 'product' );

				if ( $vendor && $vendor->is_valid() ) {

					$template_info = array(
						'name'    => 'vendor-name-title-premium',
						'args'    => array(
							'vendor'      => $vendor,
							'label_color' => 'color: ' . get_option( 'yith_vendors_color_name' ),
						),
						'section' => is_product() ? 'woocommerce/single-product' : 'woocommerce/loop',
					);

					$template_info = apply_filters( 'yith_woocommerce_vendor_name_template_info', $template_info );

					extract( $template_info ); // phpcs:ignore

					yith_wcmv_get_template( $name, $args, $section );
				}
			}
		}

		/**
		 * Add total sales product meta
		 *
		 * @since    1.0
		 * @return   void
		 * @deprecated
		 */
		public function show_product_total_sales() {
			_deprecated_function( __METHOD__, '4.0.0' );

			if ( 'no' === get_option( 'yith_wpv_vendor_show_item_sold', 'no' ) ) {
				return;
			}

			global $product;
			$vendor = yith_wcmv_get_vendor( $product, 'product' );

			if ( ! $vendor || ! $vendor->is_valid() ) {
				return;
			}

			$label = get_option( 'yith_wpv_vendor_item_sold_label', __( 'Item sold', 'yith-woocommerce-product-vendors' ) );
			$sales = $product instanceof WC_Product ? $product->get_total_sales() : 0;

			?>
			<span class="item-sold">
				<?php echo esc_html( $label ); ?>:
				<strong><?php echo absint( $sales ); ?></strong>
			</span>
			<?php
		}


		/**
		 * Exclude the not enable vendors to Related products with woocommerce 3.x
		 *
		 * @since  4.0.0
		 * @param array   $related_products An array of related products ID.
		 * @param integer $product_id       Current product ID.
		 * @param array   $args             An array of arguments.
		 * @return array
		 */
		public function get_vendor_related_product( $related_products, $product_id, $args = array() ) {
			_deprecated_function( __METHOD__, '4.5.0' );
			// Related Product Management.
			$related = get_option( 'yith_vendors_related_products', 'vendor' );
			if ( 'disabled' === $related ) {
				$related_products = array();
			} elseif ( 'vendor' === $related ) {

				$product = wc_get_product( $product_id );
				$vendor  = yith_wcmv_get_vendor( $product, 'product' );

				if ( $vendor && $vendor->is_valid() ) {
					$product_ids = $vendor->get_products();

					$related_product_ids = ! empty( $args['excluded_ids'] ) ? array_diff( $product_ids, $args['excluded_ids'] ) : $product_ids;
					$related_products    = array_intersect( $related_product_ids, $related_products );
				}
			}

			return $related_products;
		}
	}
}

