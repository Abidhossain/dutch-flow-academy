<?php
/**
 * Display the vendor's store location in Google Maps widget.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 1.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Store_Location_Widget' ) ) {
	/**
	 * YITH_Vendors_Store_Location_Widget
	 */
	class YITH_Vendors_Store_Location_Widget extends WP_Widget {

		/**
		 * Construct
		 */
		public function __construct() {
			parent::__construct(
				'yith-vendor-store-location',
				__( 'YITH Vendor Store Location', 'yith-woocommerce-product-vendors' ),
				array( 'description' => __( 'Display the vendor\'s store location in Google Maps.', 'yith-woocommerce-product-vendors' ) )
			);
		}

		/**
		 * Echo the widget content.
		 * Subclasses should over-ride this function to generate their widget code.
		 *
		 * @param array $args     Display arguments including before_title, after_title, before_widget, and after_widget.
		 * @param array $instance The settings for the particular instance of the widget.
		 */
		public function widget( $args, $instance ) {

			if ( ! yith_wcmv_is_vendor_page() ) {
				return;
			}

			$vendor = yith_wcmv_get_vendor( get_query_var( 'term' ) );
			if ( ! $vendor || ! $vendor->is_valid() || empty( $vendor->get_formatted_address() ) ) {
				return;
			}

			$args = wp_parse_args(
				$instance,
				array(
					'vendor'          => $vendor,
					'gmaps_link'      => esc_url( add_query_arg( array( 'q' => rawurlencode( $vendor->get_formatted_address() ) ), '//maps.google.com/' ) ),
					'show_gmaps_link' => 'yes' === get_option( 'yith_wpv_frontpage_show_gmaps_link', 'yes' ),
				)
			);

			yith_wcmv_get_template( 'store-location', $args, 'widgets' );
		}

		/**
		 * Output the settings update form.
		 *
		 * @param array $instance Current settings.
		 * @return void
		 */
		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => __( 'Store Location', 'yith-woocommerce-product-vendors' ) ) );
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'yith-woocommerce-product-vendors' ); ?>:
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"/>
				</label>
			</p>

			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						'%s <a href="%s">%s</a>. %s <a href="%s" target="_blank">%s</a>',
						__( 'If you have an API key for Google Maps, you can add it', 'yith-woocommerce-product-vendors' ),
						esc_url( admin_url( 'admin.php?page=' . YITH_Vendors_Admin::PANEL_PAGE . '&tab=frontend-pages&sub_tab=frontend-pages-store' ) ),
						_x( 'here.', '[admin] placeholder link', 'yith-woocommerce-product-vendors' ),
						__( 'Don\'t know what an API key is or how to use it? If you need further information, please, click', 'yith-woocommerce-product-vendors' ),
						esc_url( '//developers.google.com/maps/documentation/javascript/get-api-key' ),
						_x( 'here.', '[admin] placeholder link', 'yith-woocommerce-product-vendors' )
					)
				);
				?>
			</p>
			<?php
		}

		/**
		 * Update a particular instance.
		 * This function should check that $new_instance is set correctly. The newly-calculated
		 * value of `$instance` should be returned. If false is returned, the instance won't be
		 * saved/updated.
		 *
		 * @param array $new_instance New settings for this instance as input by the user via.
		 * @param array $old_instance Old settings for this instance.
		 * @return array Settings to save or bool false to cancel saving.
		 * @see    WP_Widget::form()
		 */
		public function update( $new_instance, $old_instance ) {
			$instance          = $old_instance;
			$instance['title'] = wp_strip_all_tags( $new_instance['title'] );

			return $instance;
		}
	}
}
