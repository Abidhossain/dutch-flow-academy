<?php
/**
 * Rate rule class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Deposit_Rule' ) ) {

	/**
	 * Deposit rule object
	 *
	 * @since 2.0.0
	 */
	class YITH_WCDP_Deposit_Rule extends WC_Data {

		/**
		 * Stores meta in cache for future reads.
		 *
		 * A group must be set to to enable caching.
		 *
		 * @var string
		 */
		protected $cache_group = 'deposit_rules';

		/**
		 * Constructor
		 *
		 * @param int|\YITH_WCDP_Deposit_Rule $rule Rule identifier.
		 *
		 * @throws Exception When not able to load Data Store class.
		 */
		public function __construct( $rule = 0 ) {
			// set default values.
			$this->data = array(
				'fixed'              => 0,
				'type'               => '',
				'rate'               => 0,
				'amount'             => 0,
				'product_ids'        => array(),
				'product_categories' => array(),
				'user_roles'         => array(),
			);

			parent::__construct();

			if ( is_numeric( $rule ) && $rule > 0 ) {
				$this->set_id( $rule );
			} elseif ( $rule instanceof self ) {
				$this->set_id( $rule->get_id() );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( 'deposit_rule' );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/* === GETTERS === */

		/**
		 * Return value for fixed property of current rule
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return int Fixed property of the rule.
		 */
		public function get_fixed( $context = 'view' ) {
			return (int) $this->get_prop( 'fixed', $context );
		}

		/**
		 * Checks if current rule is a fixed amount or a percentage value
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return bool Whether rule is a fixed amount or not.
		 */
		public function is_fixed( $context = 'view' ) {
			return (bool) $this->get_fixed( $context );
		}

		/**
		 * Return rate for current rule
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return float Rule rate.
		 */
		public function get_rate( $context = 'view' ) {
			return (float) $this->get_prop( 'rate', $context );
		}

		/**
		 * Return rate for current rule
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return float Rule rate.
		 */
		public function get_formatted_rate( $context = 'view' ) {
			$rate     = $this->get_rate( $context );
			$decimals = floor( $rate ) !== $rate ? 2 : 0;

			return number_format( $rate, $decimals );
		}

		/**
		 * Return amount for current rule
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return float Rule amount.
		 */
		public function get_amount( $context = 'view' ) {
			return (float) $this->get_prop( 'amount', $context );
		}

		/**
		 * Return type for current rule
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return float Rule type.
		 */
		public function get_type( $context = 'view' ) {
			return $this->get_prop( 'type', $context );
		}

		/**
		 * Return type for current rule
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return float Rule type.
		 */
		public function get_formatted_type( $context = 'view' ) {
			$type  = $this->get_type( $context );
			$types = self::get_supported_types();

			if ( ! isset( $types[ $type ] ) ) {
				return '';
			}

			return $types[ $type ];
		}

		/**
		 * Return user roles for current rule
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return array Array of role slugs.
		 */
		public function get_user_roles( $context = 'view' ) {
			return $this->get_prop( 'user_roles', $context );
		}

		/**
		 * Return user ids for current rule
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return array Array of user ids.
		 */
		public function get_product_ids( $context = 'view' ) {
			return $this->get_prop( 'product_ids', $context );
		}

		/**
		 * Get a list of formatted products
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return array Array for formatted products.
		 */
		public function get_formatted_products( $context = 'view' ) {
			$product_ids = $this->get_product_ids( $context );

			if ( ! $product_ids ) {
				return array();
			}

			$formatted = array();

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );

				if ( ! $product ) {
					continue;
				}

				$formatted[ $product_id ] = $product->get_formatted_name();
			}

			return $formatted;
		}

		/**
		 * Return product categories for current rule
		 *
		 * @param string $context Context of the operation.
		 *
		 * @return array Array of category ids.
		 */
		public function get_product_categories( $context = 'view' ) {
			return $this->get_prop( 'product_categories', $context );
		}

		/**
		 * Returns all data for this object.
		 *
		 * @return array
		 */
		public function get_data() {
			$data = parent::get_data();

			// add additional fields.
			$data['products'] = $this->get_formatted_products();

			return $data;
		}

		/* === SETTERS === */

		/**
		 * Set fixed property for the rule
		 *
		 * @param bool $is_fixed Fixed property value.
		 */
		public function set_fixed( $is_fixed ) {
			$is_fixed = (bool) $is_fixed;

			$this->set_prop( 'fixed', $is_fixed );
		}

		/**
		 * Set rate for the rule
		 *
		 * @param float $rate Rule rate.
		 */
		public function set_rate( $rate ) {
			$rate = (float) $rate;

			if ( $rate < 0 || $rate > apply_filters( 'yith_wcdp_max_rate_value', 100 ) ) {
				return;
			}

			$this->set_prop( 'rate', $rate );
		}

		/**
		 * Set amount for the rule
		 *
		 * @param float $amount Rule amount.
		 */
		public function set_amount( $amount ) {
			$amount = (float) $amount;

			if ( $amount < 0 ) {
				return;
			}

			$this->set_prop( 'amount', $amount );
		}

		/**
		 * Set type for the rule
		 *
		 * @param string $type Rule type.
		 */
		public function set_type( $type ) {
			$available_types = array_keys( self::get_supported_types() );

			if ( ! in_array( $type, $available_types, true ) ) {
				return;
			}

			$this->set_prop( 'type', $type );
		}

		/**
		 * Set product ids for current rule
		 *
		 * @param int|int[] $product_ids Product ids.
		 */
		public function set_product_ids( $product_ids ) {
			$product_ids = (array) $product_ids;
			$product_ids = array_map( 'intval', $product_ids );
			$valid_ids   = array();

			foreach ( $product_ids as $product_id ) {
				if ( ! wc_get_product( $product_id ) ) {
					continue;
				}

				$valid_ids[] = $product_id;
			}

			$this->set_prop( 'product_ids', $valid_ids );
		}

		/**
		 * Add a product for current rule.
		 *
		 * @param int $product_id Product id.
		 */
		public function add_product_id( $product_id ) {
			if ( ! wc_get_product( $product_id ) ) {
				return;
			}

			$product_id  = (int) $product_id;
			$product_ids = $this->get_product_ids();

			if ( ! in_array( $product_id, $product_ids, true ) ) {
				$product_ids[] = $product_id;
				$this->set_prop( 'product_ids', $product_ids );
			}
		}

		/**
		 * Removes a product from current rule.
		 *
		 * @param int $product_id Product id.
		 */
		public function remove_product_id( $product_id ) {
			$product_id  = (int) $product_id;
			$product_ids = $this->get_product_ids();
			$id_position = array_search( $product_id, $product_ids, true );

			if ( false !== $id_position ) {
				unset( $product_ids[ $id_position ] );
			}

			$this->set_prop( 'product_ids', $product_ids );
		}

		/**
		 * Set product categories for current rule
		 *
		 * @param int|int[] $product_cats Product categories.
		 */
		public function set_product_categories( $product_cats ) {
			$product_cats = (array) $product_cats;
			$product_cats = array_map( 'intval', $product_cats );
			$valid_ids    = array();

			foreach ( $product_cats as $category_id ) {
				if ( ! term_exists( $category_id, 'product_cat' ) ) {
					continue;
				}

				$valid_ids[] = $category_id;
			}

			$this->set_prop( 'product_categories', $valid_ids );
		}

		/**
		 * Add a product category for current rule.
		 *
		 * @param int $category_id Category id.
		 */
		public function add_product_category( $category_id ) {
			if ( ! term_exists( $category_id, 'product_cat' ) ) {
				return;
			}

			$category_id  = (int) $category_id;
			$product_cats = $this->get_product_categories();

			if ( ! in_array( $category_id, $product_cats, true ) ) {
				$product_cats[] = $category_id;
				$this->set_prop( 'product_categories', $product_cats );
			}
		}

		/**
		 * Removes a product category from current rule.
		 *
		 * @param int $category_id Category id.
		 */
		public function remove_product_category( $category_id ) {
			$category_id  = (int) $category_id;
			$product_cats = $this->get_product_categories();
			$id_position  = array_search( $category_id, $product_cats, true );

			if ( false !== $id_position ) {
				unset( $product_cats[ $id_position ] );
			}

			$this->set_prop( 'product_cats', $product_cats );
		}

		/**
		 * Set user roles for current rule
		 *
		 * @param string|string[] $user_roles User roles.
		 */
		public function set_user_roles( $user_roles ) {
			$user_roles  = (array) $user_roles;
			$valid_roles = array();

			foreach ( $user_roles as $role ) {
				if ( ! array_key_exists( $role, wp_roles()->roles ) ) {
					continue;
				}

				$valid_roles[] = $role;
			}

			$this->set_prop( 'user_roles', $valid_roles );
		}

		/**
		 * Add a user role for current rule.
		 *
		 * @param string $user_role User role.
		 */
		public function add_user_role( $user_role ) {
			if ( ! array_key_exists( $user_role, wp_roles()->roles ) ) {
				return;
			}

			$user_roles = $this->get_user_roles();

			if ( ! in_array( $user_roles, $user_roles, true ) ) {
				$user_roles[] = $user_role;
				$this->set_prop( 'user_roles', $user_roles );
			}
		}

		/**
		 * Removes a user role from current rule.
		 *
		 * @param string $user_role User role.
		 */
		public function remove_user_role( $user_role ) {
			$user_roles  = $this->get_user_roles();
			$id_position = array_search( $user_role, $user_roles, true );

			if ( false !== $id_position ) {
				unset( $user_roles[ $id_position ] );
			}

			$this->set_prop( 'user_roles', $user_roles );
		}

		/* === OVERRIDES === */

		/**
		 * Save should create or update based on object existence.
		 *
		 * @return int
		 */
		public function save() {
			$changes = $this->get_changes();

			$rule_id = parent::save();

			if ( $rule_id && isset( $changes['product_categories'] ) ) {
				$terms_products = wc_get_products(
					array(
						'category' => $changes['product_categories'],
					)
				);

				if ( ! empty( $terms_products ) ) {
					foreach ( $terms_products as $terms_product ) {
						if ( ! $terms_product ) {
							continue;
						}

						// enable deposit for product.
						/**
						 * APPLY_FILTERS: yith_wcdp_auto_enable_product_deposit
						 *
						 * Filters if should enable deposit for a specific product.
						 *
						 * @param bool             Default value: true.
						 * @param int  $product_id The product ID.
						 *
						 * @return bool
						 */
						if ( apply_filters( 'yith_wcdp_auto_enable_product_deposit', true, $terms_product ) ) {
							$terms_product->update_meta_data( '_enable_deposit', 'yes' );
							$terms_product->save();
						}
					}
				}
			}

			return $rule_id;
		}



		/* === GENERAL OBJECT METHODS === */

		/**
		 * Get supported rule types
		 *
		 * @return array Array of supported types.
		 */
		public static function get_supported_types() {
			return apply_filters(
				'yith_wcdp_supported_deposit_rule_types',
				array(
					'product_ids'        => _x( 'Product', '[ADMIN] Deposit rule type', 'yith-woocommerce-deposits-and-down-payments' ),
					'product_categories' => _x( 'Product categories', '[ADMIN] Deposit rule type', 'yith-woocommerce-deposits-and-down-payments' ),
					'user_roles'         => _x( 'User roles', '[ADMIN] Deposit rule type', 'yith-woocommerce-deposits-and-down-payments' ),
				)
			);
		}
	}
}
