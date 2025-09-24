<?php
/**
 * Install class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Install' ) ) {
	/**
	 * Performs install operation
	 *
	 * @since 2.0.0
	 */
	class YITH_WCDP_Install {

		/**
		 * Performs init operation for this class
		 */
		public static function init() {
			self::install_tables();
			self::install_data_stores();
			self::maybe_do_upgrade();

			// handle scheduled actions.
			add_action( 'yith_wcdp_set_product_overrides', array( self::class, 'set_product_overrides' ) );
			add_action( 'yith_wcdp_update_balance_handling', array( self::class, 'update_balance_handling' ) );
		}

		/**
		 * Update products, to use new values for _create_balance_orders meta
		 *
		 * @param int $page Page of products to process (progress in the operation will invoke this method with higher page values).
		 * @return bool Status of the operation.
		 */
		public static function update_balance_handling( $page = 1 ) {
			$per_step = 10;

			$product_ids = get_posts(
				array(
					'post_type'      => array(
						'product',
						'product_variation',
					),
					'posts_per_page' => $per_step,
					'paged'          => $page,
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'fields'         => 'ids',
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array(
							'key'     => '_create_balance_orders',
							'value'   => 'default',
							'compare' => '!=',
						),
					),
				)
			);

			if ( empty( $product_ids ) ) {
				return false;
			}

			foreach ( $product_ids as $product_id ) {
				// retrieve old value.
				$product    = wc_get_product( $product_id );
				$old_values = $product->get_meta( '_create_balance_orders' );
				$new_value  = 'yes' === $old_values ? 'pending' : 'on-hold';

				// save new value.
				$product->update_meta_data( '_create_balance_orders', $new_value );
				$product->save();
			}

			// register next step.
			return WC()->queue()->add(
				'yith_wcdp_update_balance_handling',
				array(
					'page' => ++$page,
				),
				'yith-wcdp-db-updates'
			);
		}

		/**
		 * Update balance handling meta for each product/variation that was previously overriding values
		 *
		 * @param int $page Page of products to process (progress in the operation will invoke this method with higher page values).
		 * @return bool Status of the operation.
		 */
		public static function set_product_overrides( $page = 1 ) {
			$per_step = 10;

			$product_ids = get_posts(
				array(
					'post_type'      => array(
						'product',
						'product_variation',
					),
					'posts_per_page' => $per_step,
					'paged'          => $page,
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'fields'         => 'ids',
				)
			);

			if ( empty( $product_ids ) ) {
				return false;
			}

			$meta_to_test = array(
				'deposit' => array(
					'_enable_deposit',
					'_deposit_default',
					'_force_deposit',
					'_product_note',
				),
				'balance' => array(
					'_create_balance_orders',
					'_deposit_expiration_date',
					'_deposit_expiration_product_fallback',
				),
			);

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );

				foreach ( $meta_to_test as $override => $meta_keys ) {
					foreach ( $meta_keys as $meta_key ) {
						// if we find any meta that isn't empty or default, enable override and skip.
						$meta_value = $product->get_meta( $meta_key );

						if ( $meta_value && 'default' !== $meta_value ) {
							$product->update_meta_data( "_override_{$override}_options", 'yes' );
							$product->save();
							break;
						}
					}
				}
			}

			// register next step.
			return WC()->queue()->add(
				'yith_wcdp_set_product_overrides',
				array(
					'page' => ++$page,
				),
				'yith-wcdp-db-updates'
			);
		}

		/**
		 * Add data stores to WooCommerce list
		 *
		 * @param array $data_stores List of available Data_Store.
		 * @reutrn array Array of filtered Data_store.
		 */
		public static function add_data_stores( $data_stores ) {
			$data_stores = array_merge(
				$data_stores,
				array(
					'deposit_rule' => 'YITH_WCDP_Deposit_Rule_Data_Store',
				)
			);

			return $data_stores;
		}

		/**
		 * Checks db version, and perform required database operations.
		 */
		protected static function install_tables() {
			global $wpdb;

			$wpdb->yith_wcdp_deposit_rules    = $wpdb->prefix . 'yith_wcdp_deposit_rules';
			$wpdb->yith_wcdp_deposit_rulemeta = $wpdb->prefix . 'yith_wcdp_deposit_rulemeta';

			// un-prefixed tables (required for WP automatic meta handling).
			$wpdb->deposit_rulemeta = $wpdb->prefix . 'yith_wcdp_deposit_rulemeta';

			$current_db_version = get_option( 'yith_wcdp_db_version' );

			if ( version_compare( $current_db_version, YITH_WCDP::DB_VERSION, '>=' ) ) {
				return;
			}

			// assure dbDelta function is defined.
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			// retrieve table charset.
			$charset_collate = $wpdb->get_charset_collate();

			// adds rate rules table.
			$sql = "CREATE TABLE $wpdb->yith_wcdp_deposit_rules (
                    ID bigint(20) NOT NULL AUTO_INCREMENT,
                    fixed tinyint(1) NOT NULL DEFAULT 0,
                    rate double(9,3) NOT NULL,
                    amount double(9,3) NOT NULL DEFAULT 0,
                    type varchar(255) NOT NULL DEFAULT '',
                    PRIMARY KEY (ID),
                    KEY rule_type (type)
				) $charset_collate;";

			dbDelta( $sql );

			// adds rate rule meta table.
			$sql = "CREATE TABLE $wpdb->yith_wcdp_deposit_rulemeta (
                    meta_id bigint(20) NOT NULL AUTO_INCREMENT,
                    deposit_rule_id bigint(20) NOT NULL,
                    meta_key varchar(255) NOT NULL DEFAULT '',
                    meta_value longtext NOT NULL DEFAULT '',
                    PRIMARY KEY (meta_id),
                    KEY external_rule (deposit_rule_id),
                    KEY object_type (meta_key)
				) $charset_collate;";

			dbDelta( $sql );

			update_option( 'yith_wcdp_db_version', YITH_WCDP::DB_VERSION );
		}

		/**
		 * Register data stores required by the plugin
		 */
		protected static function install_data_stores() {
			add_filter( 'woocommerce_data_stores', array( self::class, 'add_data_stores' ) );
		}

		/**
		 * Performs required upgrade from older versions.
		 */
		protected static function maybe_do_upgrade() {
			$current_version = get_option( 'yith_wcdp_version' );

			if ( version_compare( $current_version, YITH_WCDP::VERSION, '>=' ) ) {
				return;
			}

			if ( version_compare( $current_version, '2.0.0', '<' ) ) {
				self::do_200_upgrade();
			}

			update_option( 'yith_wcdp_version', YITH_WCDP::VERSION );
		}

		/**
		 * Upgrade data when moving to current plugin from a version < 2.0.0
		 */
		protected static function do_200_upgrade() {
			self::upgrade_200_options();
			self::upgrade_200_deposit_rules();
			self::upgrade_200_product_meta();
			self::show_welcome_modal();
		}

		/**
		 * Upgrade options when moving to current plugin from a version < 2.0.0
		 */
		protected static function upgrade_200_options() {
			// update default amount.
			$deposit_type   = get_option( 'yith_wcdp_general_deposit_type', 'amount' );
			$deposit_amount = get_option( 'yith_wcdp_general_deposit_amount', 0 );
			$deposit_rate   = get_option( 'yith_wcdp_general_deposit_rate', 10 );

			// only proceed when not already converted.
			if ( ! is_array( $deposit_amount ) ) {
				update_option(
					'yith_wcdp_general_deposit_amount',
					array(
						'amount' => $deposit_amount,
						'rate'   => $deposit_rate,
						'type'   => $deposit_type,
					)
				);
			}

			// update balance options.
			$balance_type   = get_option( 'yith_wcdp_balance_type', 'multiple' );
			$balance_status = get_option( 'yith_wcdp_general_create_balance_orders', 'yes' );

			if ( 'none' === $balance_type ) {
				update_option( 'yith_wcdp_create_balance', 'none' );
				update_option( 'yith_wcdp_balance_type', 'multiple' );
			} elseif ( 'yes' === $balance_status ) {
				update_option( 'yith_wcdp_create_balance', 'pending' );
			} else {
				update_option( 'yith_wcdp_create_balance', 'on-hold' );
			}

			// update notification options.
			$notification_limit = get_option( 'yith_wcdp_notify_customer_deposit_expiring_days_limit', 15 );

			update_option( 'yith_wcdp_notify_customer_deposit_expiring_days_limit', yith_wcdp_days_to_duration( $notification_limit ) );

			// update expiration fallback option.
			$expiration_fallback = get_option( 'yith_wcdp_deposit_expiration_fallback', 'none' );

			if ( ! is_array( $expiration_fallback ) ) {
				update_option(
					'yith_wcdp_deposit_expiration_fallback',
					array(
						'initial_fallback' => $expiration_fallback,
					)
				);
			}
		}

		/**
		 * Upgrade deposit rules to new system
		 */
		protected static function upgrade_200_deposit_rules() {
			// retrieves registered rules.
			$new_rules = array();
			$old_rules = array(
				'product_ids'        => get_option( 'yith_wcdp_product_deposits', array() ),
				'product_categories' => get_option( 'yith_wcdp_category_deposits', array() ),
				'user_roles'         => get_option( 'yith_wcdp_user_role_deposits', array() ),
			);

			foreach ( $old_rules as $rule_type => $rules_data ) {
				if ( empty( $rules_data ) ) {
					continue;
				}

				foreach ( $rules_data as $identifier => $rule_data ) {
					$fixed = 'amount' === $rule_data['type'];

					$new_rules[] = array_merge(
						array(
							'type'     => $rule_type,
							$rule_type => (array) $identifier,
							'fixed'    => $fixed,
						),
						$fixed ? array(
							'amount' => $rule_data['value'],
						) : array(
							'rate' => $rule_data['value'],
						)
					);
				}
			}

			if ( ! empty( $new_rules ) ) {
				foreach ( $new_rules as $rule_props ) {
					$rule = new YITH_WCDP_Deposit_Rule();

					$rule->set_props( $rule_props );
					$rule->save();
				}
			}
		}

		/**
		 * Run scheduled actions, to be sure that overrides meta are enabled for products that are currently overriding deposit options
		 */
		protected static function upgrade_200_product_meta() {
			// schedule processes that will update product meta.
			WC()->queue()->schedule_single(
				time() + 10,
				'yith_wcdp_set_product_overrides',
				array(
					'page' => 1,
				),
				'yith-wcdp-db-updates'
			);
			WC()->queue()->schedule_single(
				time() + 10,
				'yith_wcdp_update_balance_handling',
				array(
					'page' => 1,
				),
				'yith-wcdp-db-updates'
			);
		}

		/**
		 * Shows Welcome modal after update/Install
		 *
		 * @since 2.0.0
		 */
		protected static function show_welcome_modal() {
			$installed = ! ! get_option( 'yith_wcdp_version' );
			$to_show   = $installed ? 'update' : 'welcome';

			update_option( 'yith_wcdp_welcome_modal_status', $to_show );
		}
	}
}
