<?php
/**
 * Admin panel class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Admin_Panel' ) ) {
	/**
	 * Admin panel
	 *
	 * @since 2.0.0
	 */
	class YITH_WCDP_Admin_Panel {

		/**
		 * Constructor method
		 */
		public function __construct() {
			// admin action system.
			add_action( 'admin_init', array( self::class, 'handle_action' ), 15 );

			// register custom fields.
			add_action( 'yith_wcdp_print_deposit_amount_field', array( $this, 'print_deposit_amount_field' ), 10, 1 );
			add_action( 'yith_wcdp_print_balance_expiration_days_field', array( $this, 'print_balance_expiration_days_field' ), 10, 1 );
			add_action( 'yith_wcdp_print_duration_field', array( $this, 'print_duration_field' ), 10, 1 );
			add_action( 'yith_wcdp_print_expiration_fallback_field', array( $this, 'print_expiration_fallback_field' ), 10, 1 );

			// general panel handling.
			if ( isset( $_GET['tab'], $_GET['sub_tab'] ) && 'settings' === $_GET['tab'] && 'settings-rules' === $_GET['sub_tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				add_action( 'yit_framework_after_print_wc_panel_content', array( $this, 'print_add_deposit_rule_modal' ) );
			}
		}

		/**
		 * Print modal that allow admin to create new/edit existing deposit modals
		 */
		public function print_add_deposit_rule_modal() {
			$product_categories = get_terms(
				array(
					'taxonomy' => 'product_cat',
					'number'   => 0,
					'fields'   => 'id=>name',
				)
			);

			include YITH_WCDP_DIR . 'views/add-deposit-rule-modal.php';
		}

		/* === CUSTOM FIELDS === */

		/**
		 * Print field that allow admin to specify deposit value (rate or fixed amount)
		 *
		 * @param array $field Field options.
		 */
		public function print_deposit_amount_field( $field ) {
			yith_plugin_fw_get_field(
				array_merge(
					$field,
					array(
						'type'   => 'inline-fields',
						'fields' => array(
							'amount' => array(
								'type'              => 'number',
								'default'           => 0,
								'custom_attributes' => array(
									'min'               => 0,
									'step'              => 0.01,
									'data-dependencies' => wp_json_encode( array( $field['name'] . '_type' => 'amount' ) ),
								),
							),
							'rate'   => array(
								'type'              => 'number',
								'default'           => 0,
								'custom_attributes' => array(
									'min'               => 0,
									'max'               => 100,
									'step'              => 0.01,
									'data-dependencies' => wp_json_encode( array( $field['name'] . '_type' => 'rate' ) ),
								),
							),
							'type'   => array(
								'type'    => 'select',
								'options' => array(
									'rate'   => __( '% of product price', 'yith-woocommerce-deposits-and-down-payments' ),
									// translators: 1. Currency symbol.
									'amount' => sprintf( __( '%s - Fixed amount', 'yith-woocommerce-deposits-and-down-payments' ), get_woocommerce_currency_symbol() ),
								),
							),
						),
					)
				),
				true
			);
		}

		/**
		 * Print field that allow admin to specify duration value
		 *
		 * @param array $field Field options.
		 */
		public function print_balance_expiration_days_field( $field ) {
			yith_plugin_fw_get_field(
				array_merge(
					$field,
					array(
						'type'   => 'inline-fields',
						'class'  => 'duration-field',
						'fields' => array(
							'amount' => array(
								'type'              => 'number',
								'default'           => 30,
								'custom_attributes' => array(
									'min'  => 1,
									'max'  => 9999999,
									'step' => 1,
								),
							),
						),
					)
				),
				true
			);

			if ( isset( $field['inline_desc'] ) ) {
				?>
				<span class="inline-desc description">
					<?php echo wp_kses_post( $field['inline_desc'] ); ?>
				</span>
				<?php
			}
		}

		/**
		 * Print field that allow admin to specify duration value
		 *
		 * @param array $field Field options.
		 */
		public function print_duration_field( $field ) {
			yith_plugin_fw_get_field(
				array_merge(
					$field,
					array(
						'type'   => 'inline-fields',
						'class'  => 'duration-field',
						'fields' => array(
							'amount' => array(
								'type'              => 'number',
								'default'           => 0,
								'custom_attributes' => array(
									'min'  => 1,
									'step' => 1,
								),
							),
							'unit'   => array(
								'type'    => 'select',
								'options' => array(
									'day'   => _x( 'Days', '[ADMIN] Time intervals for duration fields', 'yith-woocommerce-deposits-and-down-payments' ),
									'week'  => _x( 'Weeks', '[ADMIN] Time intervals for duration fields', 'yith-woocommerce-deposits-and-down-payments' ),
									'month' => _x( 'Months', '[ADMIN] Time intervals for duration fields', 'yith-woocommerce-deposits-and-down-payments' ),
								),
							),
						),
					)
				),
				true
			);

			if ( isset( $field['inline_desc'] ) ) {
				?>
				<span class="inline-desc description">
					<?php echo wp_kses_post( $field['inline_desc'] ); ?>
				</span>
				<?php
			}
		}

		/**
		 * Print field that allow admin to specify deposit expiration fallback
		 *
		 * @param array $field Field options.
		 */
		public function print_expiration_fallback_field( $field ) {
			if ( ! $field['id'] ) {
				return;
			}

			$defaults = array(
				'max_attempts'     => 2,
				'fail_description' => _x( 'if the balance is still not paid', '[ADMIN] Expiration fallback field', 'yith-woocommerce-deposits-and-down-payments' ),
				'fallbacks'        => array(
					'none'   => __( 'Cancel balance order', 'yith-woocommerce-deposits-and-down-payments' ),
					'refund' => __( 'Cancel balance order and refund deposit', 'yith-woocommerce-deposits-and-down-payments' ),
					'retry'  => __( 'Send reminder', 'yith-woocommerce-deposits-and-down-payments' ),
				),
				'value'            => get_option( $field['id'] ),
			);

			$field = array_merge(
				$defaults,
				$field
			);

			include YITH_WCDP_DIR . 'views/fields/expiration-fallback.php';
		}

		/* === ADMIN ACTION SYSTEM === */

		/**
		 * Returns an array of supported actions, including handlers
		 *
		 * @return array
		 */
		public static function get_action_handlers() {
			return apply_filters(
				'yith_wcdp_admin_action_handlers',
				array(
					'create_rule' => array(),
					'delete_rule' => array(),
				)
			);
		}

		/**
		 * Returns url that admin should visit to trigger a specific action
		 *
		 * @param string $action Action to perform.
		 * @param array  $params Optional array of parameters for the action.
		 *
		 * @return string Action url.
		 */
		public static function get_action_url( $action, $params = array() ) {
			return wp_nonce_url(
				add_query_arg(
					array_merge(
						$params,
						array(
							'yith_wcdp_action' => $action,
						)
					)
				),
				$action
			);
		}

		/**
		 * Handle action when correct url is visited.
		 */
		public static function handle_action() {
			$action = isset( $_GET['yith_wcdp_action'] ) ? sanitize_text_field( wp_unslash( $_GET['yith_wcdp_action'] ) ) : false;

			if ( ! $action ) {
				return;
			}

			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), $action ) ) {
				return;
			}

			$handlers = self::get_action_handlers();

			if ( ! isset( $handlers[ $action ] ) ) {
				return;
			}

			if ( ! isset( $_GET['redirect_to'] ) ) {
				$redirect_url = remove_query_arg( array( 'yith_wcdp_action', '_wpnonce', 'id' ) );
			} else {
				$redirect_url = sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) );
			}

			try {
				if ( isset( $handlers[ $action ]['handler'] ) ) {
					call_user_func( $handlers[ $action ]['handler'] );
				} elseif ( method_exists( self::class, $action ) ) {
					self::{$action}();
				}
			} catch ( Exception $e ) {
				YITH_WCDP_Admin_Notices::add( $e->getMessage(), 'error' );
			}

			wp_safe_redirect( $redirect_url );
			die;
		}

		/* === ACTION HANDLERS === */

		/**
		 * Handler that creates/edit deposit rule
		 *
		 * @throws Exception When an error occurs.
		 */
		public static function create_rule() {
			// phpcs:disable WordPress.Security.NonceVerification
			$id                 = isset( $_POST['id'] ) ? (int) $_POST['id'] : false;
			$type               = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : false;
			$rate               = isset( $_POST['rate'] ) ? (float) $_POST['rate'] : 0;
			$amount             = isset( $_POST['amount'] ) ? (float) $_POST['amount'] : 0;
			$fixed              = isset( $_POST['fixed'] ) ? (bool) $_POST['fixed'] : 0;
			$product_ids        = isset( $_POST['product_ids'] ) ? array_map( 'intval', (array) $_POST['product_ids'] ) : array();
			$product_categories = isset( $_POST['product_categories'] ) ? array_map( 'intval', (array) $_POST['product_categories'] ) : array();
			$user_roles         = isset( $_POST['user_roles'] ) ? wc_clean( array_map( 'wp_unslash', (array) $_POST['user_roles'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			// phpcs:enable WordPress.Security.NonceVerification

			if ( ! $type || ! array_key_exists( $type, YITH_WCDP_Deposit_Rule::get_supported_types() ) ) {
				throw new Exception( esc_html__( 'Couldn\'t create deposit rule: wrong type.', 'yith-woocommerce-deposits-and-down-payments' ) );
			}

			if ( apply_filters( 'yith_wcdp_avoid_exception_rule_creation', ! $amount && ! $rate ) ) {
				throw new Exception( esc_html__( 'Couldn\'t create deposit rule: missing amount or rate.', 'yith-woocommerce-deposits-and-down-payments' ) );
			}

			if ( ! $product_ids && ! $product_categories && ! $user_roles ) {
				throw new Exception( esc_html__( 'Couldn\'t create deposit rule: missing rules.', 'yith-woocommerce-deposits-and-down-payments' ) );
			}

			if ( $id ) {
				$rule = YITH_WCDP_Deposit_Rule_Factory::get_rule( $id );
			} else {
				$rule = new YITH_WCDP_Deposit_Rule();
			}

			$rule->set_props( compact( 'type', 'rate', 'amount', 'fixed', 'product_ids', 'product_categories', 'user_roles' ) );
			$rule->save();
		}

		/**
		 * Deletes a specific deposit rul
		 *
		 * @throws Exception When cannot find rule to delete.
		 */
		public static function delete_rule() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : false;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			$rule = YITH_WCDP_Deposit_Rule_Factory::get_rule( $id );

			if ( ! $rule ) {
				throw new Exception( esc_html__( 'Couldn\'t delete deposit rule: couldn\'t find rule to delete.', 'yith-woocommerce-deposits-and-down-payments' ) );
			}

			$rule->delete();
		}
	}
}
