<?php
/**
 * Deposit rule data store
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Deposit_Rule_Data_Store' ) ) {
	/**
	 * This class implements CRUD methods for Deposit rules
	 *
	 * @since 2.0.0
	 */
	class YITH_WCDP_Deposit_Rule_Data_Store {

		/**
		 * Database table.
		 *
		 * @var string
		 */
		protected $table;

		/**
		 * Cache group.
		 *
		 * @var string
		 */
		protected $cache_group;

		/**
		 * Table columns.
		 *
		 * @var array
		 */
		protected $columns;

		/**
		 * Orderby.
		 *
		 * @var array
		 */
		protected $orderby;

		/**
		 * Expected meta structure.
		 *
		 * @var array
		 */
		protected $meta = array(
			'product_id',
			'product_cat',
			'user_role',
		);

		/**
		 * Maps object properties to database columns
		 * Every prop not included in this list, match the column name
		 *
		 * @var array
		 */
		protected $props_to_meta = array(
			'product_ids'        => 'product_id',
			'product_categories' => 'product_cat',
			'user_roles'         => 'user_role',
		);

		/**
		 * Constructor method
		 */
		public function __construct() {
			global $wpdb;

			$this->table = $wpdb->yith_wcdp_deposit_rules;

			$this->cache_group = 'deposit_rules';

			$this->columns = array(
				'fixed'  => '%d',
				'rate'   => '%f',
				'amount' => '%f',
				'type'   => '%s',
			);

			$this->orderby = array_merge(
				array_keys( $this->columns ),
				array(
					'ID',
				)
			);
		}

		/* === CRUD === */

		/**
		 * Method to create a new record of a WC_Data based object.
		 *
		 * @param YITH_WCDP_Deposit_Rule $rule Data object.
		 * @throws Exception When rule cannot be created with current information.
		 */
		public function create( &$rule ) {
			global $wpdb;

			if ( ! $rule->get_type() ) {
				throw new Exception( esc_html_x( 'Unable to register rule. Missing required params.', '[DEV] Debug message triggered when unable to create deposit rule record.', 'yith-woocommerce-deposits-and-down-payments' ) );
			}

			list( $columns, $types, $values ) = yith_plugin_fw_extract( $this->generate_query_structure( $rule, false ), 'columns', 'types', 'values' );

			$res = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$this->table,
				array_combine( $columns, $values ),
				array_values( $types )
			);

			if ( $res ) {
				$id = apply_filters( 'yith_wcdp_deposit_rule_correctly_created', intval( $wpdb->insert_id ) );

				$rule->set_id( $id );

				// create metadata.
				$changes = $rule->get_changes();

				foreach ( $this->meta as $meta ) {
					$meta_prop = $this->get_meta_prop_name( $meta );

					if ( empty( $changes[ $meta_prop ] ) ) {
						continue;
					}

					foreach ( $changes[ $meta_prop ] as $meta_value ) {
						add_metadata( 'deposit_rule', $rule->get_id(), $meta, $meta_value );
					}
				}

				$rule->apply_changes();

				$this->clear_cache( $rule );

				do_action( 'yith_wcdp_new_deposit_rule', $rule->get_id(), $rule );
			}
		}

		/**
		 * Method to read a record. Creates a new WC_Data based object.
		 *
		 * @param YITH_WCDP_Deposit_Rule $rule Data object.
		 * @throws Exception When rule cannot be retrieved with current information.
		 */
		public function read( &$rule ) {
			global $wpdb;

			$rule->set_defaults();

			$id = $rule->get_id();

			if ( ! $id ) {
				throw new Exception( esc_html_x( 'Invalid deposit rule.', '[DEV] Debug message triggered when unable to find rate rule record.', 'yith-woocommerce-deposits-and-down-payments' ) );
			}

			$rule_data = $id ? wp_cache_get( 'deposit_rule-' . $id, $this->cache_group ) : false;

			if ( ! $rule_data ) {
				// format query to retrieve rule.
				$query = $wpdb->prepare( "SELECT * FROM {$this->table} WHERE ID = %d", $id ); // phpcs:ignore

				// retrieve rule data.
				$rule_data = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery

				if ( $rule_data ) {
					// now also read useful meta, to store them in cache as well.
					$rule_data->metadata = get_metadata( 'deposit_rule', $rule_data->ID );

					wp_cache_set( 'deposit_rule-' . $rule_data->ID, $rule_data, $this->cache_group );
				}
			}

			if ( ! $rule_data ) {
				throw new Exception( esc_html_x( 'Invalid rate rule.', '[DEV] Debug message triggered when unable to find click record.', 'yith-woocommerce-deposits-and-down-payments' ) );
			}

			$rule->set_id( (int) $rule_data->ID );

			// set rule props.
			foreach ( array_keys( $this->columns ) as $column ) {
				$rule->{"set_{$column}"}( $rule_data->$column );
			}

			// set rule meta.
			$metadata = isset( $rule_data->metadata ) ? $rule_data->metadata : array();

			if ( $metadata ) {
				foreach ( $this->meta as $meta ) {
					if ( empty( $metadata[ $meta ] ) ) {
						continue;
					}

					$rule->{"set_{$this->get_meta_prop_name( $meta )}"}( $metadata[ $meta ] );
				}
			}

			$rule->set_object_read( true );
		}

		/**
		 * Updates a record in the database.
		 *
		 * @param YITH_WCDP_Deposit_Rule $rule Data object.
		 */
		public function update( &$rule ) {
			global $wpdb;

			if ( ! $rule->get_id() ) {
				return;
			}

			list( $columns, $types, $values ) = yith_plugin_fw_extract( $this->generate_query_structure( $rule ), 'columns', 'types', 'values' );

			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$this->table,
				array_combine( $columns, $values ),
				array( 'ID' => $rule->get_id() ),
				$types,
				array( '%d' )
			);

			// update metadata.
			$changes = $rule->get_changes();

			foreach ( $this->meta as $meta ) {
				$prop = $this->get_meta_prop_name( $meta );

				if ( ! isset( $changes[ $prop ] ) ) {
					continue;
				}

				delete_metadata( 'deposit_rule', $rule->get_id(), $meta );

				if ( ! empty( $changes[ $prop ] ) ) {
					foreach ( $changes[ $prop ] as $meta_value ) {
						add_metadata( 'deposit_rule', $rule->get_id(), $meta, $meta_value );
					}
				}
			}

			$rule->apply_changes();

			$this->clear_cache( $rule );

			do_action( 'yith_wcdp_update_deposit_rule', $rule->get_id(), $rule );
		}

		/**
		 * Deletes a record from the database.
		 *
		 * @param YITH_WCDP_Deposit_Rule $rule Data object.
		 * @param array                  $args Not in use.
		 *
		 * @return bool result
		 */
		public function delete( &$rule, $args = array() ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
			global $wpdb;

			$id = $rule->get_id();

			if ( ! $id ) {
				return false;
			}

			do_action( 'yith_wcdp_before_delete_deposit_rule', $id, $rule );

			$this->clear_cache( $rule );

			// delete rule.
			$res = $wpdb->delete( $this->table, array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( $res ) {
				do_action( 'yith_wcdp_delete_deposit_rule', $id, $rule );

				// delete meta.
				$wpdb->delete( $this->table, array( 'deposit_rule_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

				$rule->set_id( 0 );

				do_action( 'yith_wcdp_deleted_deposit_rule', $id, $rule );
			}

			return $res;
		}

		/* === QUERY === */

		/**
		 * Return count of rules matching filtering criteria
		 *
		 * @param array $args Filtering criteria (@see \YITH_WCDP_Deposit_Rule_Data_Store::query).
		 * @return int Count of matching rules.
		 */
		public function count( $args = array() ) {
			$args['fields'] = 'count';

			return (int) $this->query( $args );
		}

		/**
		 * Return rules matching filtering criteria
		 *
		 * @param array $args Filtering criteria<br/>:
		 *              [<br/>
		 *              'product_id' => false,   // rule product id (int)<br/>
		 *              'product_cat' => false,  // rule product id (int)<br/>
		 *              'user_role' => false,    // rule user role (int)<br/>
		 *              'type' => false,         // rule type (int)<br/>
		 *              'order' => 'DESC',       // sorting direction (ASC/DESC)<br/>
		 *              'orderby' => 'ID',       // sorting column (any table valid column)<br/>
		 *              'limit' => 0,            // limit (int)<br/>
		 *              'offset' => 0            // offset (int)<br/>
		 *              ].
		 *
		 * @return array|string[]|int|bool Matching clicks, or clicks count
		 */
		public function query( $args = array() ) {
			global $wpdb;

			$defaults = array(
				'product_id'  => false,
				'product_cat' => false,
				'user_role'   => false,
				'type'        => false,
				'order'       => 'DESC',
				'orderby'     => 'ID',
				'limit'       => 0,
				'offset'      => 0,
				'fields'      => '',
			);

			$args = wp_parse_args( $args, $defaults );

			// checks if we're performing a count query.
			$is_counting = ! empty( $args['fields'] ) && 'count' === $args['fields'];

			$query      = "SELECT ydr.*
					FROM {$this->table} AS ydr
					WHERE 1 = 1";
			$query_args = array();

			if ( $is_counting ) {
				$query = "SELECT COUNT(*)
						FROM {$this->table} AS ydr
						WHERE 1 = 1";
			}

			foreach ( $this->meta as $meta_key ) {
				if ( empty( $args[ $meta_key ] ) ) {
					continue;
				}

				$matching_ids = $this->get_rule_ids_by_meta_query( $meta_key, $args[ $meta_key ] );

				if ( empty( $matching_ids ) ) {
					return array();
				}

				$query_part = trim( str_repeat( '%d, ', count( $matching_ids ) ), ', ' );

				$query     .= ' AND ydr.ID IN ( ' . $query_part . ' )';
				$query_args = array_merge( $query_args, $matching_ids );
			}

			if ( ! empty( $args['type'] ) ) {
				$query       .= ' AND ydr.type = %s';
				$query_args[] = $args['type'];
			}

			if ( ! empty( $args['orderby'] ) && ! $is_counting ) {
				$order   = ! empty( $args['order'] ) && in_array( strtolower( $args['order'] ), array( 'asc', 'desc' ), true ) ? $args['order'] : 'desc';
				$orderby = in_array( $args['orderby'], $this->orderby, true ) ? $args['orderby'] : 'ID';

				$query .= " ORDER BY $orderby $order";
			}

			if ( ! empty( $args['limit'] ) && 0 < (int) $args['limit'] && ! $is_counting ) {
				$query .= sprintf( ' LIMIT %d, %d', ! empty( $args['offset'] ) ? $args['offset'] : 0, $args['limit'] );
			}

			if ( ! empty( $query_args ) ) {
				$query = $wpdb->prepare( $query, $query_args ); // phpcs:ignore WordPress.DB
			}

			if ( $is_counting ) {
				$res = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB
			} else {
				$res = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB
			}

			// if we're counting, return count found.
			if ( $is_counting ) {
				return $res;
			}

			// if we have an empty set from db, return empty array/collection and skip next steps.
			if ( ! $res ) {
				return array();
			}

			$ids = array_map( 'intval', wp_list_pluck( $res, 'ID' ) );

			if ( ! empty( $args['fields'] ) ) {
				// extract required field.
				$indexed = 0 === strpos( $args['fields'], 'id=>' );
				$field   = $indexed ? substr( $args['fields'], 4 ) : $args['fields'];
				$field   = 'ids' === $field ? 'ID' : $field;

				$res = wp_list_pluck( $res, $field );

				if ( $indexed ) {
					$res = array_combine( $ids, $res );
				}
			}

			return array_map( array( 'YITH_WCDP_Deposit_Rule_Factory', 'get_rule' ), $ids );
		}

		/* === UTILITIES === */

		/**
		 * Generate data structure used by methods of this class to create queries
		 *
		 * @param WC_Data $object       Data object.
		 * @param bool    $changes_only Create structure only for updated fields.
		 *
		 * @return array Structure containing details about the query, in the following format
		 * [
		 *     'columns' => array(), // each of table columns
		 *     'types' => array(),   // type for each column
		 *     'values' => array()   // value for each column, retrieved from the data object
		 * ]
		 */
		protected function generate_query_structure( $object, $changes_only = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.objectFound
			$columns = array();
			$types   = array();
			$values  = array();
			$changes = $object->get_changes();

			foreach ( $this->columns as $column_name => $column_type ) {
				$prop   = $column_name;
				$getter = "get_$prop";

				if ( $changes_only && ! array_key_exists( $prop, $changes ) ) {
					continue;
				}

				if ( ! method_exists( $object, $getter ) ) {
					continue;
				}

				$value = $object->$getter();

				$columns[] = $column_name;
				$types[]   = $column_type;
				$values[]  = $value;
			}

			return compact( 'columns', 'types', 'values' );
		}

		/**
		 * Returns id of rate rules matching a specific meta query
		 *
		 * @param string $meta_key   Meta key.
		 * @param string $meta_value Meta value.
		 *
		 * @return int[] Array of rules id.
		 */
		protected function get_rule_ids_by_meta_query( $meta_key, $meta_value ) {
			global $wpdb;

			if ( ! in_array( $meta_key, $this->meta, true ) ) {
				return array();
			}

			$meta_value = (array) $meta_value;
			$where_list = trim( str_repeat( '%s, ', count( $meta_value ) ), ', ' );
			$query      = "SELECT deposit_rule_id FROM {$wpdb->yith_wcdp_deposit_rulemeta} WHERE meta_key = %s AND meta_value IN ( $where_list )";

			$rule_ids_per_meta = $wpdb->get_col( $wpdb->prepare( $query, array_merge( (array) $meta_key, $meta_value ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			return $rule_ids_per_meta;
		}

		/**
		 * Get property name for a meta
		 *
		 * @param string $meta Meta to search for.
		 *
		 * @return string Property name.
		 */
		protected function get_meta_prop_name( $meta ) {
			$meta_to_props = array_flip( $this->props_to_meta );

			if ( ! isset( $meta_to_props[ $meta ] ) ) {
				return $meta;
			}

			return $meta_to_props[ $meta ];
		}

		/**
		 * Clear rule related caches
		 *
		 * @param \YITH_WCDP_Deposit_Rule|int $rule Rule object or rule id.
		 *
		 * @return void
		 */
		protected function clear_cache( $rule ) {
			$rule = YITH_WCDP_Deposit_Rule_Factory::get_rule( $rule );

			if ( ! $rule || ! $rule->get_id() ) {
				return;
			}

			wp_cache_delete( 'deposit_rule-' . $rule->get_id(), $this->cache_group );
		}
	}
}
