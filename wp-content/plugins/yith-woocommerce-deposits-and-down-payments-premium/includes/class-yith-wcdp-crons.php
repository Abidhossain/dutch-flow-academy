<?php
/**
 * Cron Handler class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Crons' ) ) {
	/**
	 * Cron Handling
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Crons {

		/**
		 * Init handlers
		 */
		public static function init() {
			self::schedule();
		}

		/**
		 * Returns a list of available crons
		 *
		 * @return array Array of managed crons.
		 */
		public static function get_crons() {
			return apply_filters(
				'yith_wcdp_crons',
				array(
					'cancel_expired_suborders'  => 'daily',
					'notify_expiring_suborders' => 'daily',
				)
			);
		}

		/**
		 * Returns formatted name for a specific cron
		 *
		 * @param string $cron Cron id.
		 * @return string Cron name.
		 */
		public static function get_cron_action( $cron ) {
			return "yith_wcdp_$cron";
		}

		/**
		 * Schedule crons when needed, and hooks available handlers.
		 */
		public static function schedule() {
			$crons = self::get_crons();

			foreach ( $crons as $cron => $recurrence ) {
				$cron_name = self::get_cron_action( $cron );

				if ( method_exists( self::class, $cron ) ) {
					add_action( $cron_name, array( self::class, $cron ) );
				}

				if ( ! wp_next_scheduled( $cron_name ) ) {
					wp_schedule_event( time() + 10, $recurrence, $cron_name );
				}
			}
		}

		/* === HANDLERS === */

		/**
		 * Cancel expired suborders
		 */
		public static function cancel_expired_suborders() {
			global $wpdb;

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$query = "SELECT o.id
					FROM {$wpdb->prefix}wc_orders AS o
					LEFT JOIN {$wpdb->prefix}wc_orders_meta AS om ON o.id = om.order_id
					WHERE 1=1
					AND o.type = %s
					AND o.parent_order_id <> %d
					AND o.status IN ( %s, %s )
					AND om.meta_key = %s
					AND om.meta_value <= %s";
			} else {
				$query = "SELECT p.ID
					FROM {$wpdb->posts} AS p
					LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
					WHERE 1=1
					AND p.post_type = %s
					AND p.post_parent <> %d
					AND p.post_status IN ( %s, %s )
					AND pm.meta_key = %s
					AND pm.meta_value <= %s";
			}

			$query_args = array(
				'shop_order',
				0,
				'wc-pending',
				'wc-on-hold',
				'_suborder_expiration',
				gmdate( 'Y-m-d', time() ),
			);

			$order_ids = $wpdb->get_col( $wpdb->prepare( $query, $query_args ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			// if no order was found to process, return.
			if ( empty( $order_ids ) ) {
				return;
			}

			$order_ids = array_map( 'intval', $order_ids );

			// remove customer note notification.
			add_filter( 'woocommerce_email_enabled_customer_note', '__return_false' );

			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );

				if ( ! $order ) {
					continue;
				}

				if ( ! apply_filters( 'yith_wcdp_should_cancel_balance_after_expiration', true, $order, $order_id ) ) {
					continue;
				}

				$new_expiration = YITH_WCDP_Suborders()->should_extend_expiration( $order_id );

				// if we can still iterate, update expiration, increase counter, and skip order.
				if ( $new_expiration ) {
					$iterations = $order->get_meta( '_suborder_expiration_iterations' );

					$order->update_meta_data( '_suborder_expiration', $new_expiration );
					$order->update_meta_data( '_suborder_expiration_iterations', $iterations ? ++$iterations : 1 );
					$order->update_meta_data( '_expiring_deposit_notification_sent', 'yes' );
					$order->add_order_note( __( 'Expiring deposit notification sent', 'yith-woocommerce-deposits-and-down-payments' ) );
					$order->save();

					/**
					 * DO_ACTION: yith_wcdp_deposits_expiring
					 *
					 * Action triggered when looping through IDs of deposits expiring.
					 *
					 * @param int      $post_id     Order ID.
					 * @param int|bool $suborder_id Suborder ID, false if none.
					 * @param bool     $force       Whether mail should be sent even if notification was already sent.
					 */
					do_action( 'yith_wcdp_deposits_expiring', $order->get_parent_id(), $order_id, true );

					continue;
				}

				$expiration_date = strtotime( $order->get_meta( '_suborder_expiration' ) );
				$creation_date   = $order->get_date_created()->getTimestamp();
				$expiration_span = floor( max( 0, ( $expiration_date - $creation_date ) ) / DAY_IN_SECONDS );

				// set suborder as cancelled.
				$order->update_status( 'cancelled' );

				/**
				 * APPLY_FILTERS: yith_wcdp_expired_order_notice
				 *
				 * Filters the expired deposit order message.
				 *
				 * @param string $message Default message.
				 *
				 * @return string
				 */
				// translators: 1. NUmber of days before deposit expiration.
				$order->add_order_note( apply_filters( 'yith_wcdp_expired_order_notice', sprintf( __( 'The %d days granted to complete this order have passed. For this reason, it has been switched to canceled, and it cannot be completed anymore.', 'yith-woocommerce-deposits-and-down-payments' ), $expiration_span ) ), true );

				// set meta to mark expired orders.
				$order->update_meta_data( '_has_deposit_expired', 1 );
				$order->save();

				do_action( 'yith_wcdp_balance_expired', $order, $order_id, $expiration_date );
			}
		}

		/**
		 * Notify customers about expiring suborders
		 */
		public static function notify_expiring_suborders() {
			global $wpdb;

			if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
				$query = "SELECT o.id
					FROM {$wpdb->prefix}wc_orders AS o
					LEFT JOIN {$wpdb->prefix}wc_orders_meta AS om ON o.id = om.order_id
					WHERE 1=1
					AND o.type = %s
					AND o.parent_order_id <> %d
					AND o.status IN ( %s, %s )
					AND om.meta_key = %s
					AND om.meta_value <= %s
					AND o.ID NOT IN ( SELECT order_id FROM {$wpdb->prefix}wc_orders_meta AS om2 WHERE om2.meta_key = %s AND om2.meta_value = %s )";
			} else {
				$query = "SELECT p.ID
					FROM {$wpdb->posts} AS p
					LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
					WHERE 1=1
					AND p.post_type = %s
					AND p.post_parent <> %d
					AND p.post_status IN ( %s, %s )
					AND pm.meta_key = %s
					AND pm.meta_value <= %s
					AND p.ID NOT IN ( SELECT post_id FROM {$wpdb->postmeta} AS pm2 WHERE pm2.meta_key = %s AND pm2.meta_value = %s )";
			}

			$query_args = array(
				'shop_order',
				0,
				'wc-pending',
				'wc-on-hold',
				'_suborder_expiration_notification_date',
				gmdate( 'Y-m-d', time() ),
				'_expiring_deposit_notification_sent',
				'yes',
			);

			$order_ids = $wpdb->get_col( $wpdb->prepare( $query, $query_args ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			// if no order was found to process, return.
			if ( empty( $order_ids ) ) {
				return;
			}

			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );

				if ( ! $order ) {
					continue;
				}

				$parent_id    = $order->get_parent_id();
				$parent_order = wc_get_order( $parent_id );

				if ( ! $parent_order ) {
					continue;
				}

				/**
				 * APPLY_FILTERS: yith_wcdp_condition_deposits_expiring
				 *
				 * Filters if should expire the deposit.
				 *
				 * @param bool              Default value: false.
				 * @param int  $order_id    The order ID.
				 * @param int  $suborder_id The suborder ID.
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_wcdp_condition_deposits_expiring', false, $parent_id, $order_id ) ) {
					continue;
				}

				// trigger email notification.
				do_action( 'yith_wcdp_deposits_expiring', $parent_id, $order_id );

				// mark notification as sent.
				$order->update_meta_data( '_expiring_deposit_notification_sent', 'yes' );
				$order->add_order_note( __( 'Expiring deposit notification sent', 'yith-woocommerce-deposits-and-down-payments' ) );
				$order->save();

				// translators: 1. Suborder id.
				$parent_order->add_order_note( sprintf( __( 'Expiring deposit notification sent (#%d)', 'yith-woocommerce-deposits-and-down-payments' ), $order_id ) );
				$parent_order->save();
			}
		}
	}
}
