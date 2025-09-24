<?php
/**
 * Deprecated filter hooks
 *
 * @since   4.0.0
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit;

if ( ! class_exists( 'YITH_Vendors_Deprecated_Filter_Hooks' ) ) {

	class YITH_Vendors_Deprecated_Filter_Hooks extends WC_Deprecated_Hooks {

		/**
		 * Array of deprecated hooks we need to handle.
		 * Format of 'new' => 'old'.
		 *
		 * @var array
		 */
		protected $deprecated_hooks = array(
			'yith_wcmv_admin_commissions_settings'         => 'yith_wpv_panel_commissions_options',
			'yith_wcmv_vendor_additional_capabilities'     => array(
				'yith_wcmv_premium_caps',
				'yith_wcmv_manage_role_caps',
			),
			'yith_wcmv_get_suborders_ids'                  => 'yith_wcmv_get_suborder_ids',
			'yith_wcmv_required_files'                     => 'yith_wcpv_require_class',
			'yith_wcvm_get_base_commission'                => 'yith_vendor_base_commission',
			'yith_wcmv_register_widgets'                   => 'yith_wpv_register_widgets',
			'yith_wcmv_admin_vendor_dashboard_tabs'        => array(
				'yith_wcmv_vendor_tabs',
				'yith_wcmv_vendor_owner_tabs',
				'yith_wcmv_panel_admin_tabs',
			),
			'yith_wcmv_hide_vendor_settings'               => 'yith_wcmv_hide_vendor_profile',
			'yith_wcmv_admin_vendor_menu_items'            => 'yith_wpv_vendor_menu_items',
			'yith_wcmv_admin_panel_tabs'                   => 'yith_vendors_admin_tabs',
			'yith_wcmv_modules'                            => 'yith_wcmv_add_ons',
			'yith_wcmv_vendor_coupon_types_to_disable'     => 'yith_wc_multi_vendor_coupon_types',
			'yith_wcmv_commissions_list_table_column'      => 'yith_commissions_list_table_column',
			'yith_wcmv_commissions_list_table_args'        => 'yith_wpv_commissions_table_args',
			'yith_wcmv_commissions_list_table_order_column' => 'yith_wcmv_commissions_order_column',
			'yith_wcmv_commissions_list_table_bulk_actions' => 'yith_wcmv_commissions_bulk_actions',
			'yith_wcmv_get_commissions_status_capabilities_map' => 'yith_wcmv_get_status_capability_map',
			'yith_wcmv_current_user_can_edit_users'        => 'yith_wcmv_commissions_list_table_can_edit_users',
			'yith_wcmv_commission_edit_order_url'          => 'yith_wcmv_commissions_list_table_order_url',
			'yith_wcmv_admin_date_format'                  => 'yith_wcmv_commissions_list_table_date_format',
			'yith_wcmv_gateway_paypal-masspay_options'     => 'yith_wcmv_paypal_gateways_options',
			'yith_wcmv_gateway_stripe-connect_options'     => 'yith_wcmv_stripe-connect_gateways_options',
			'yith_wcmv_single_product_vendor_tab_name'     => 'yith_single_product_vendor_tab_name',
			'yith_wcmv_single_product_vendor_tab_args'     => 'yith_woocommerce_product_vendor_tab',
			'yith_wcmv_single_product_vendor_tab_template_args' => 'yith_woocommerce_product_vendor_tab_template',
			'yith_wcmv_frontend_stylesheet_paths'          => 'yith_wpv_stylesheet_paths',
			'yith_wcmv_store_header_image_class'           => 'yith_wcmv_header_img_class',
			'yith_wcmv_vendor_recent_comments_widget_items' => 'vendor_recent_comments_widget_items',
			'yith_wcmv_json_search_found_vendors'          => 'yith_wpv_json_search_found_vendors',
			'yith_wcmv_vendors_products_limit'             => 'yith_wpv_vendors_product_limit',
			'yith_wcmv_shipping_package_name'              => 'yith_vendor_package_name',
			'yith_wcmv_create_vendor_registration_fields'  => 'yith_new_vendor_registration_fields',
			'yith_wcmv_get_vendor_commission'              => 'yith_vendor_commission',
			'yith_wcmv_get_vendor_url'                     => 'yith_vendor_url',
			'yith_wcmv_commission_has_status'              => 'yith_vendors_commission_has_status',
			'yith_wcmv_get_vendor_name_shortcode'          => 'yith_wcan_get_vendor_name_shortcode',
			'yith_wcmv_report_abuse_modal_title'           => 'yith-wpv-report-abuse-title',
			'yith_wcmv_vendor_social_fields'               => 'yith_vendors_admin_social_fields',
			'yith_wcmv_gateway_paypal_payouts_vendor_note' => 'yith_payouts_vendor_note',
			'yith_wcmv_vendor_not_allowed_reports'         => 'yith_vendor_not_allowed_reports',
			'yith_wcmv_header_image_size'                  => 'yith_wcmv_get_header_size',
			'yith_wcmv_avatar_image_size'                  => 'yith_wcmv_get_avatar_size',
			'yith_wcmv_plugin_panel_capability'            => 'yit_wcmv_plugin_options_capability',
			'yith_wcmv_hide_commissions_order_item_meta'   => 'yith_show_commissions_order_item_meta',
			'yith_wcmv_redirect_after_become_a_vendor'     => 'yith_wcmv_process_wp_safe_redirect',
			'yith_wcmv_create_commission_args'             => 'yith_wcmv_add_commission_args',
		);

		/**
		 * Array of versions on each hook has been deprecated.
		 *
		 * @var array
		 */
		protected $deprecated_version = array();

		/**
		 * Hook into the new hook so we can handle deprecated hooks once fired.
		 *
		 * @param string $hook_name Hook name.
		 */
		public function hook_in( $hook_name ) {
			add_filter( $hook_name, array( $this, 'maybe_handle_deprecated_hook' ), -1000, 8 );
		}

		/**
		 * If the old hook is in-use, trigger it.
		 *
		 * @param string $new_hook          New hook name.
		 * @param string $old_hook          Old hook name.
		 * @param array  $new_callback_args New callback args.
		 * @param mixed  $return_value      Returned value.
		 * @return mixed
		 */
		public function handle_deprecated_hook( $new_hook, $old_hook, $new_callback_args, $return_value ) {
			if ( has_filter( $old_hook ) ) {
				$this->display_notice( $old_hook, $new_hook );
				$return_value = $this->trigger_hook( $old_hook, $new_callback_args );
			}

			return $return_value;
		}

		/**
		 * Fire off a legacy hook with it's args.
		 *
		 * @param string $old_hook          Old hook name.
		 * @param array  $new_callback_args New callback args.
		 * @return mixed
		 */
		protected function trigger_hook( $old_hook, $new_callback_args ) {
			return apply_filters_ref_array( $old_hook, $new_callback_args );
		}

		/**
		 * Get deprecated version.
		 *
		 * @param string $old_hook Old hook name.
		 * @return string
		 */
		protected function get_deprecated_version( $old_hook ) {
			return ! empty( $this->deprecated_version[ $old_hook ] ) ? $this->deprecated_version[ $old_hook ] : '4.0.0';
		}
	}
}

new YITH_Vendors_Deprecated_Filter_Hooks();
