<?php
/**
 * Emails class
 * Offer generic function for email sending.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Emails' ) ) {
	/**
	 * Emails class
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Emails {
		/**
		 * Init class and hooks methods
		 */
		public static function init() {
			// change default WooCommerce emails.
			add_filter( 'woocommerce_email_order_meta_fields', array( self::class, 'add_parent_order' ), 10, 3 );

			// emails init.
			add_filter( 'woocommerce_email_classes', array( self::class, 'register_classes' ) );
			add_filter( 'woocommerce_email_actions', array( self::class, 'register_actions' ) );
			add_filter( 'woocommerce_locate_core_template', array( self::class, 'locate_template' ), 10, 2 );

			// register resend notification email.
			add_filter( 'woocommerce_order_actions', array( self::class, 'enable_resend' ), 10, 2 );
			add_action( 'woocommerce_order_action_deposits_expiring', array( self::class, 'resend' ), 10, 1 );
			add_action( 'woocommerce_order_action_deposits_created', array( self::class, 'resend' ), 10, 1 );
		}

		/**
		 * Prints Deposit on email for the admin
		 *
		 * @param array    $fields        Array of fields to filter.
		 * @param bool     $sent_to_admin Whether email is sent to admin.
		 * @param WC_Order $order         Current order.
		 *
		 * @return array Filtered array of fields
		 */
		public static function add_parent_order( $fields, $sent_to_admin, $order ) {
			if ( ! $sent_to_admin ) {
				return $fields;
			}

			$order_id         = $order->get_id();
			$has_full_payment = $order->get_meta( '_has_full_payment' );

			if ( ! $has_full_payment ) {
				return $fields;
			}

			$parent_order_id = YITH_WCDP_Suborders()->get_parent_order( $order_id );

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$parent_order_link = add_query_arg(
					array(
						'page'   => 'wc-orders',
						'action' => 'edit',
						'id'     => $parent_order_id,
					),
					admin_url( 'admin.php' )
				);
			} else {
				$parent_order_link = get_edit_post_link( $parent_order_id );
			}

			if ( $parent_order_id ) {
				$fields[] = array(
					'label' => YITH_WCDP_Labels::get_deposit_label(),
					'value' => sprintf( '<a href="%s">%s</a>', $parent_order_link, $parent_order_id ),
				);
			}

			return $fields;
		}

		/**
		 * Register email classes for deposits
		 *
		 * @param array $classes Array of email class instances.
		 *
		 * @return mixed Filtered array of email class instances
		 * @since 1.0.0
		 */
		public static function register_classes( $classes ) {
			require_once YITH_WCDP_INC . 'emails/class-yith-wcdp-email.php';

			$classes['YITH_WCDP_Admin_Deposit_Created_Email']     = new YITH_WCDP_Admin_Deposit_Created_Email();
			$classes['YITH_WCDP_Customer_Deposit_Created_Email']  = new YITH_WCDP_Customer_Deposit_Created_Email();
			$classes['YITH_WCDP_Customer_Deposit_Expiring_Email'] = new YITH_WCDP_Customer_Deposit_Expiring_Email();

			return $classes;
		}

		/**
		 * Register email action for deposits
		 *
		 * @param array $emails Array of registered actions.
		 *
		 * @return mixed Filtered array of registered actions
		 * @since 1.0.0
		 */
		public static function register_actions( $emails ) {
			$emails = array_merge(
				$emails,
				array(
					'yith_wcdp_deposits_created',
					'yith_wcdp_deposits_expiring',
					'woocommerce_order_status_completed',
					'woocommerce_order_status_processing',
				)
			);

			return $emails;
		}

		/**
		 * Adds notify email to re-send options
		 *
		 * @param array    $order_actions Array of available order actions.
		 * @param WC_Order $order Order object.
		 *
		 * @return mixed Filtered array of order actions
		 * @since 1.0.0
		 */
		public static function enable_resend( $order_actions, $order ) {
			// check if current global post is an order.
			if ( ! $order ) {
				return $order_actions;
			}

			// check if current order has a deposit.
			if ( ! $order->get_meta( '_has_deposit' ) ) {
				return $order_actions;
			}

			// retrieve current order suborders.
			$suborders = YITH_WCDP_Suborders()->get_suborders( $order->get_id() );

			// check if order have suborders.
			if ( ! $suborders ) {
				return $order_actions;
			}

			// enable "re-send notify email" only if at least one suborder is not expired, and not completed or cancelled.
			$resend_available = false;
			foreach ( $suborders as $suborder_id ) {
				$suborder = wc_get_order( $suborder_id );

				if ( ! $suborder->get_meta( 'has_expired' ) && ! $suborder->has_status( array( 'completed', 'processing', 'cancelled' ) ) ) {
					$resend_available = true;
				}
			}

			// enable "re-send notify email".
			if ( $resend_available ) {
				$order_actions['deposits_created']  = __( 'Resend new deposit notification', 'yith-woocommerce-deposits-and-down-payments' );
				$order_actions['deposits_expiring'] = __( 'Resend balances payment notification', 'yith-woocommerce-deposits-and-down-payments' );
			}

			return $order_actions;
		}

		/**
		 * Resend expiring sub-orders notification
		 *
		 * @param WC_Order $order Current order.
		 *
		 * @return void
		 * @since 1.1.2
		 */
		public static function resend( $order ) {
			$current_action = current_action();
			$email_action   = str_replace( 'woocommerce_order_action_', '', $current_action );

			do_action( "yith_wcdp_{$email_action}", $order->get_id() );
		}

		/**
		 * Locate default templates of woocommerce in plugin, if exists
		 *
		 * @param string $core_file File to locate in its original location.
		 * @param string $template  Template to locate.
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public static function locate_template( $core_file, $template ) {
			$located = yith_wcdp_locate_template( $template );

			if ( $located && file_exists( $located ) ) {
				return $located;
			} else {
				return $core_file;
			}
		}
	}
}
