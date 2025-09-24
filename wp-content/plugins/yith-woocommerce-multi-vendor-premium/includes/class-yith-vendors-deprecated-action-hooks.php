<?php
/**
 * Deprecated action hooks
 *
 * @since   4.0.0
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit;

if ( ! class_exists( 'YITH_Vendors_Deprecated_Action_Hooks' ) ) {

	class YITH_Vendors_Deprecated_Action_Hooks extends WC_Deprecated_Hooks {

		/**
		 * Array of deprecated hooks we need to handle.
		 * Format of 'new' => 'old'.
		 *
		 * @var array
		 */
		protected $deprecated_hooks = array(
			'yith_wcmv_vendor_created'              => 'yith_new_vendor_registration',
			'yith_wcmv_vendor_account_approved'     => 'yith_vendors_account_approved',
			'yith_wcmv_vendor_account_approved_notification' => 'yith_vendors_account_approved_notification',
			'yith_wcmv_order_commissions_processed' => 'yith_commissions_processed',
			'yith_wcmv_disable_vendor_to_sale'      => 'yith_mv_disable_vendor_to_sale',
			'yith_wcmv_disable_vendor_to_sale_cron' => 'yith_mv_disable_vendor_to_sale_cron',
			'yith_wcmv_vendor_set_pending_status'   => 'yith_wcmv_vendor_set_in_pending_status',
			'yith_wcmv_vendor_limited_access_dashboard_hooks' => 'yith_wcmv_vendor_limited_access_dashboard_hooks_premium',
		);

		/**
		 * Array of versions on each hook has been deprecated.
		 *
		 * @var array
		 */
		protected $deprecated_version = array(
			'yith_wcmv_vendor_set_in_pending_status' => '5.0.0',
			'yith_wcmv_vendor_limited_access_dashboard_hooks_premium' => '5.0.0',
		);

		/**
		 * Hook into the new hook so we can handle deprecated hooks once fired.
		 *
		 * @param string $hook_name Hook name.
		 */
		public function hook_in( $hook_name ) {
			add_action( $hook_name, array( $this, 'maybe_handle_deprecated_hook' ), -1000, 8 );
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
			if ( has_action( $old_hook ) ) {
				$this->display_notice( $old_hook, $new_hook );
				$this->trigger_hook( $old_hook, $new_callback_args );
			}

			return $return_value;
		}

		/**
		 * Fire off a legacy hook with it's args.
		 *
		 * @param string $old_hook          Old hook name.
		 * @param array  $new_callback_args New callback args.
		 * @return void
		 */
		protected function trigger_hook( $old_hook, $new_callback_args ) {
			do_action_ref_array( $old_hook, $new_callback_args );
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

new YITH_Vendors_Deprecated_Action_Hooks();
