<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Commissions_Factory' ) ) {
	/**
	 * Main class for the commission
	 *
	 * @class      YITH_Vendors_Commission_Factory
	 * @since      Version 4.0.0
	 * @author     YITH
	 * @package YITH\MultiVendor
	 */
	class YITH_Vendors_Commissions_Factory {

		/**
		 * Main Instances
		 *
		 * @var array
		 */
		protected static $instances = array();

		/**
		 * Create a new commission
		 *
		 * @since  4.0.0
		 * @param array $args (Optional) An array of commission arguments. Default is empty array.
		 * @return WP_Error|integer WP_Error on error, the commission ID on success.
		 * @throws Exception Errors on create commission.
		 */
		public static function create( $args = array() ) {

			try {

				if ( empty( $args ) ) {
					throw new Exception( _x( 'Create commission failed: empty data for commission.', '[Notice]Create commission process error', 'yith-woocommerce-product-vendors' ) );
				}

				$commission = new YITH_Vendors_Commission();
				foreach ( $args as $key => $value ) {
					$method = "set_{$key}";
					if ( method_exists( $commission, $method ) ) {
						$commission->$method( $value );
					}
				}

				$commission->save();
				return $commission->get_id();

			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );

				return new WP_Error( 'commission-create-failed', $e->getMessage() );
			}
		}

		/**
		 * Read a commission
		 *
		 * @since  4.0.0
		 * @param integer $commission_id The commission ID to retrieve.
		 * @return YITH_Vendors_Commission|boolean A YITH_Vendors_Commission object or false if not found.
		 * @throws Exception Error reading single commission.
		 */
		public static function read( $commission_id = 0 ) {

			try {
				$commission_id = absint( $commission_id );
				if ( empty( $commission_id ) ) { // If commission ID is empty, return false.
					throw new Exception();
				}

				if ( ! isset( self::$instances[ $commission_id ] ) ) {
					self::$instances[ $commission_id ] = new YITH_Vendors_Commission( $commission_id );
				}

				return self::$instances[ $commission_id ];

			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Update a commission
		 *
		 * @since  4.0.0
		 * @param integer $commission_id The commission ID to update.
		 * @param array   $data          The commission data to update.
		 * @return boolean|WP_Error True on success, WP_Error otherwise.
		 * @throws Exception Errors on update commission.
		 */
		public static function update( $commission_id, $data ) {
			try {

				$commission_id = absint( $commission_id );
				$commission    = self::read( $commission_id );
				if ( ! $commission ) {
					// translators: %s stand for the commission ID.
					throw new Exception( sprintf( _x( 'Update commission failed: no commission found for ID #%s.', '[Notice]Update commission process error', 'yith-woocommerce-product-vendors' ), $commission_id ) );
				}

				if ( empty( $data ) ) {
					// translators: %s stand for the commission ID.
					throw new Exception( sprintf( _x( 'Update commission failed: empty data for commission #%s.', '[Notice]Update commission process error', 'yith-woocommerce-product-vendors' ), $commission_id ) );
				}

				foreach ( $data as $key => $value ) {
					$method = 'set_' . $key;
					if ( method_exists( $commission, $method ) ) {
						$commission->$method( $value );
					}
				}

				$commission->save();

				return true;

			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );

				return new WP_Error( 'vendor-update-failed', $e->getMessage() );
			}
		}

		/**
		 * Delete a commission
		 *
		 * @since  4.0.0
		 * @param string|integer $commission_id The commission ID to delete.
		 * @return boolean|WP_Error True on success, WP_Error otherwise.
		 */
		public static function delete( $commission_id ) {

			try {
				$commission_id = absint( $commission_id );
				$commission    = self::read( $commission_id );
				$commission && $commission->delete();

				return true;
			} catch ( Exception $e ) {
				YITH_Vendors_Logger::log( $e->getMessage() );
				return new WP_Error( 'commission-delete-failed', $e->getMessage() );
			}
		}

		/**
		 * Unset cached commission instance by ID
		 *
		 * @param integer $commission_id The commission ID to unset from instances.
		 */
		public static function unset( $commission_id ) {
			unset( self::$instances[ $commission_id ] );
		}

		/**
		 * Get Commissions
		 *
		 * @since  1.0.0
		 * @param array $q Query parameters.
		 * @return integer[]|YITH_Vendors_Commission[]
		 */
		public static function query( $q = array() ) {
			global $wpdb, $yith_wcmv_cache;

			$default_args = array(
				'user_id'      => 0,
				'vendor_id'    => 0,
				'line_item_id' => 0,
				'product_id'   => 0,
				'order_id'     => 0,
				'status'       => '',
				'm'            => false,
				's'            => '',
				'number'       => 10,
				'offset'       => 0,
				'paged'        => 0,
				'orderby'      => 'ID',
				'order'        => 'ASC',
				'fields'       => 'ids',
				'include'      => array(),
				'exclude'      => array(),
				'date_query'   => false,
			);

			$q = wp_parse_args( $q, $default_args );
			$q = apply_filters( 'yith_wcmv_get_commissions_args', $q );

			// Fairly insane upper bound for search string lengths.
			if ( ! is_scalar( $q['s'] ) || ( ! empty( $q['s'] ) && strlen( $q['s'] ) > 1600 ) ) {
				$q['s'] = '';
			}

			// Set number correctly.
			if ( -1 === $q['number'] ) {
				$q['number'] = 0;
			}

			// Search for cached value.
			$cache_key = $yith_wcmv_cache->build_key( $q );
			$res       = $yith_wcmv_cache->get( $cache_key, 'commissions' );

			if ( false === $res ) {
				$count   = 'count' === $q['fields'];
				$where   = 'WHERE 1=1';
				$limits  = '';
				$join    = '';
				$orderby = '';

				// Process filter.
				if ( ! empty( $q['line_item_id'] ) ) {
					$where .= $wpdb->prepare( ' AND c.line_item_id = %d', $q['line_item_id'] );
				}
				if ( ! empty( $q['product_id'] ) ) {
					$join  .= " JOIN {$wpdb->prefix}woocommerce_order_items oi ON ( oi.order_item_id = c.line_item_id AND oi.order_id = c.order_id )";
					$join  .= " JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON ( oim.order_item_id = oi.order_item_id )";
					$where .= $wpdb->prepare( ' AND oim.meta_key = %s AND oim.meta_value = %d', '_product_id', absint( $q['product_id'] ) );
				}
				if ( ! empty( $q['order_id'] ) ) {
					$where .= $wpdb->prepare( ' AND c.order_id = %d', $q['order_id'] );
				}
				if ( ! empty( $q['user_id'] ) ) {
					$where .= $wpdb->prepare( ' AND c.user_id = %d', $q['user_id'] );
				}
                if ( ! empty( $q['vendor_id'] ) ) {
                    $vendor_id = apply_filters( 'yith_wcmv_commission_vendor_id', array( $q['vendor_id'] ), $q['vendor_id'] );
                    $where .= ' AND c.vendor_id IN ( '. implode( ',', array_map( 'absint', $vendor_id ) ) . ')';
                }
				if ( ! empty( $q['type'] ) && 'all' !== $q['type'] ) {
					$where .= $wpdb->prepare( ' AND c.type = %s', $q['type'] );
				}
				if ( ! empty( $q['status'] ) && 'all' !== $q['status'] ) {
					if ( is_array( $q['status'] ) ) {
						$q['status'] = implode( "', '", $q['status'] );
					}
					$where .= sprintf( " AND c.status IN ( '%s' )", $q['status'] );
				}
				if ( ! empty( $q['include'] ) ) {
					if ( is_array( $q['include'] ) ) {
						$q['include'] = implode( ', ', array_map( 'absint', $q['include'] ) );
					}
					$where .= sprintf( ' AND c.ID IN ( %s )', $q['include'] );
				}
				if ( ! empty( $q['exclude'] ) ) {
					if ( is_array( $q['exclude'] ) ) {
						$q['exclude'] = implode( ', ', array_map( 'absint', $q['exclude'] ) );
					}
					$where .= sprintf( ' AND c.ID NOT IN ( %s )', $q['exclude'] );
				}

				// The "m" parameter is meant for months but accepts date times of varying specificity.
				if ( $q['m'] ) {
					$q['m'] = absint( preg_replace( '|[^0-9]|', '', $q['m'] ) );

					$where .= ' AND YEAR(c.created_date)=' . substr( $q['m'], 0, 4 );
					if ( strlen( $q['m'] ) > 5 ) {
						$where .= ' AND MONTH(c.created_date)=' . substr( $q['m'], 4, 2 );
					}
					if ( strlen( $q['m'] ) > 7 ) {
						$where .= ' AND DAYOFMONTH(c.created_date)=' . substr( $q['m'], 6, 2 );
					}
					if ( strlen( $q['m'] ) > 9 ) {
						$where .= ' AND HOUR(c.created_date)=' . substr( $q['m'], 8, 2 );
					}
					if ( strlen( $q['m'] ) > 11 ) {
						$where .= ' AND MINUTE(c.created_date)=' . substr( $q['m'], 10, 2 );
					}
					if ( strlen( $q['m'] ) > 13 ) {
						$where .= ' AND SECOND(c.created_date)=' . substr( $q['m'], 12, 2 );
					}
				}

				// Handle complex date queries.
				if ( ! empty( $q['date_query'] ) ) {
					$where .= self::build_date_query_clause( $q['date_query'], 'c.created_date' );
				}

				// Process search.
				if ( $q['s'] ) {
					// Added slashes screw with quote grouping when done early, so done later.
					$q['s'] = stripslashes( $q['s'] );
					// There are no line breaks in <input /> fields.
					$q['s'] = str_replace( array( "\r", "\n" ), '', $q['s'] );

					// Product.
					$join .= strpos( $join, 'woocommerce_order_items' ) === false ? " JOIN {$wpdb->prefix}woocommerce_order_items oi ON ( oi.order_item_id = c.line_item_id AND oi.order_id = c.order_id )" : '';
					// User.
					$join .= " JOIN $wpdb->users u ON u.ID = c.user_id";
					$join .= " JOIN $wpdb->usermeta um ON um.user_id = c.user_id";
					$join .= " JOIN $wpdb->usermeta um2 ON um2.user_id = c.user_id";

					$s = array(
						// Search by order.
						$wpdb->prepare( 'c.order_id = %d', $q['s'] ),
						// Search by Commission ID.
						$wpdb->prepare( 'c.ID = %d', $q['s'] ),
						// Search by product.
						$wpdb->prepare( '(oi.order_item_type = \'line_item\' AND oi.order_item_name LIKE %s )', "%{$q['s']}%" ),
						// Search by username.
						$wpdb->prepare( 'u.user_login LIKE %s', "%{$q['s']}%" ),
						$wpdb->prepare( 'u.user_nicename LIKE %s', "%{$q['s']}%" ),
						$wpdb->prepare( 'u.user_email LIKE %s', "%{$q['s']}%" ),
						$wpdb->prepare( '(um.meta_key = \'first_name\' AND um.meta_value LIKE %s)', "%{$q['s']}%" ),
						$wpdb->prepare( '(um2.meta_key = \'last_name\' AND um2.meta_value LIKE %s)', "%{$q['s']}%" ),
					);

					$where .= ' AND ( ' . implode( ' OR ', $s ) . ' )';
				}

				// Order.
				if ( ! empty( $q['order'] ) && 'ASC' === strtoupper( $q['order'] ) ) {
					$q['order'] = 'ASC';
				} else {
					$q['order'] = 'DESC';
				}

				// Order by.
				if ( empty( $q['orderby'] ) ) {
					$orderby = '';
				} elseif ( 'date' === $q['orderby'] ) {
					$orderby = 'c.created_date ' . $q['order'];
				} else {
					$orderby_array = array();
					if ( is_array( $q['orderby'] ) ) {
						foreach ( $q['orderby'] as $_orderby => $order ) {
							$orderby = addslashes_gpc( urldecode( $_orderby ) );

							if ( ! is_string( $order ) || empty( $order ) ) {
								$order = 'DESC';
							}

							if ( 'ASC' === strtoupper( $order ) ) {
								$order = 'ASC';
							} else {
								$order = 'DESC';
							}

							$orderby_array[] = $orderby . ' ' . $order;
						}
						$orderby = implode( ', ', $orderby_array );

					} else {
						$q['orderby'] = urldecode( $q['orderby'] );
						$q['orderby'] = addslashes_gpc( $q['orderby'] );

						foreach ( explode( ' ', $q['orderby'] ) as $i => $orderby ) {
							$orderby_array[] = $orderby;
						}
						$orderby = implode( ' ' . $q['order'] . ', ', $orderby_array );

						if ( empty( $orderby ) ) {
							$orderby = 'c.ID ' . $q['order'];
						} elseif ( ! empty( $q['order'] ) ) {
							$orderby .= " {$q['order']}";
						}
					}
				}

				if ( ! empty( $orderby ) ) {
					$orderby = 'ORDER BY ' . $orderby;
				}

				// Paging.
				if ( ! empty( $q['number'] ) ) {
					$offset = 0;
					if ( ! empty( $q['offset'] ) ) {
						$offset = absint( $q['offset'] );
					} elseif ( ! empty( $q['paged'] ) ) {
						$offset = ( absint( $q['paged'] ) - 1 ) * $q['number'];
					}

					$limits = $wpdb->prepare( 'LIMIT %d, %d', $offset, $q['number'] );
				}

				if ( $count ) {
					$res = $wpdb->get_var( "SELECT COUNT( DISTINCT c.ID ) FROM $wpdb->commissions AS c $join $where" ); // phpcs:ignore
				} else {
					$res = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS DISTINCT c.ID FROM $wpdb->commissions AS c $join $where $orderby $limits" ); // phpcs:ignore
				}

				$yith_wcmv_cache->set( $cache_key, $res, 'commissions', DAY_IN_SECONDS );
			}

			if ( isset( $q['fields'] ) && 'all' === $q['fields'] ) {
				$res = array_map( 'yith_wcmv_get_commission', $res );
			}

			return $res;
		}

		/**
		 * Return the count of commissions in base of query
		 *
		 * @since 1.0
		 * @param array $q Query parameters.
		 * @return integer
		 */
		public static function count( $q = array() ) {
			global $wpdb;

			if ( 'last-query' === $q ) {
				return $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // phpcs:ignore
			}

			$q['fields'] = 'count';
			return self::query( $q );
		}

		/**
		 * Return commissions stats by given args.
		 *
		 * @since  4.0.0
		 * @param array $q Query parameters.
		 * @return array
		 */
		public static function get_stats( $q = array() ) {
			global $wpdb, $yith_wcmv_cache;

			$defaults = array(
				'stats'        => array(
					'commissions_count',
					'commissions_total_pending',
					'commissions_total_earnings',
					'commissions_total_refunds',
					'commissions_total_paid',
					'commissions_store_gross_total',
					'commissions_store_net_total',
					'orders_count',
				),
				'include'      => array(),
				'exclude'      => array(),
				'order_id'     => 0,
				'line_item_id' => 0,
				'vendor_id'    => 0,
				'product_id'   => 0,
				'date_query'   => false,
				'intervals'    => array(),
				'orderby'      => 'vendor_id',
				'order'        => 'DESC',
				'number'       => 0,
				'offset'       => 0,
				'group_by'     => 'vendor_id',
			);

			$q = wp_parse_args( $q, $defaults );
			$q = apply_filters( 'yith_wcmv_get_commissions_stats_args', $q );

			// skip if no stat is requested (request error).
			if ( empty( $q['stats'] ) ) {
				return array();
			}

			// skip if grouping by intervals but no interval defined.
			if ( empty( $q['intervals'] ) && 'time_interval' === $q['group_by'] ) {
				return array();
			}

			// Search in cache.
			$cache_key = $yith_wcmv_cache->build_key( $q );
			$res       = $yith_wcmv_cache->get( $cache_key, 'commissions' );

			if ( false === $res ) {
				$query_select = array();
				// Start building the select clause.
				foreach ( $q['stats'] as $stat ) {
					switch ( $stat ) {
						case 'commissions_count':
							$query_select[] = 'COUNT( DISTINCT c.ID ) AS commissions_count';
							break;
						case 'commissions_total_earnings':
							$query_select[] = 'SUM( c.amount ) AS commissions_total_earnings';
							break;
						case 'commissions_total_pending':
							$query_select[] = 'SUM( CASE WHEN c.status = "pending" THEN ( c.amount ) ELSE 0 END ) AS commissions_total_pending';
							break;
						case 'commissions_total_refunds':
							$query_select[] = 'SUM( ABS( c.amount_refunded ) ) AS commissions_total_refunds';
							break;
						case 'commissions_total_paid':
							$query_select[] = 'SUM( CASE WHEN c.status = "paid" THEN ( c.amount ) ELSE 0 END ) AS commissions_total_paid';
							break;
						case 'commissions_store_gross_total':
							$query_select[] = 'SUM( c.line_total ) AS commissions_store_gross_total';
							break;
						case 'commissions_store_net_total':
							$query_select[] = 'SUM( c.line_total - c.amount ) AS commissions_store_net_total';
							break;
						case 'orders_count':
							$query_select[] = 'COUNT( DISTINCT c.order_id ) AS orders_count';
							break;
					}
				}

				$query_select = implode( ', ', $query_select );
				$query_where  = '1=1';
				$query_group  = '';
				$query_order  = '';
				$query_limit  = '';

				if ( ! empty( $q['intervals'] ) && 'time_interval' === $q['group_by'] ) {
					$intervals      = (array) $q['intervals'];
					$intervals_case = 'CASE';

					foreach ( $intervals as $interval ) {
						if ( empty( $interval ) || ! isset( $interval['start_date'] ) || ! isset( $interval['end_date'] ) ) {
							continue;
						}

						$interval_label = "{$interval['start_date']}-{$interval['end_date']}";
						$intervals_case .= $wpdb->prepare( ' WHEN c.created_date >= %s AND c.created_date <= %s THEN %s', $interval['start_date'], $interval['end_date'], $interval_label );
					}

					$intervals_case .= ' END';

					$query_select .= ", {$intervals_case} AS time_interval";
				}

				if ( 'vendor_id' === $q['group_by'] ) {
					$query_select .= ', vendor_id';
				}

				if ( 'product_id' === $q['group_by'] ) {
					$query_select .= ', product_id';
					$query_where  .= ' AND c.type = "product"';
				}

				if ( ! empty( $q['include'] ) ) {
					$query_where .= ' AND c.ID IN (' . esc_sql( implode( ',', (array) $q['include'] ) ) . ')';
				}

				if ( ! empty( $q['exclude'] ) ) {
					$query_where .= ' AND c.ID NOT IN (' . esc_sql( implode( ',', (array) $q['exclude'] ) ) . ')';
				}

				if ( ! empty( $q['order_id'] ) ) {
					$query_where .= $wpdb->prepare( ' AND c.order_id = %d', $q['order_id'] );
				}

				if ( ! empty( $q['line_item_id'] ) ) {
					$query_where .= $wpdb->prepare( ' AND c.line_item_id = %d', $q['line_item_id'] );
				}

				if ( ! empty( $q['vendor_id'] ) ) {
					$query_where .= ' AND c.vendor_id IN (' . esc_sql( implode( ',', (array) $q['vendor_id'] ) ) . ')';
				}

				if ( ! empty( $q['product_id'] ) ) {
					$query_where .= $wpdb->prepare( ' AND c.product_id = %d', $q['product_id'] );
				}

				// Handle complex date queries.
				if ( ! empty( $q['date_query'] ) ) {
					$query_where .= self::build_date_query_clause( $q['date_query'], 'c.created_date' );
				}

				if ( ! empty( $q['intervals'] ) ) {
					$intervals       = (array) $q['intervals'];
					$intervals_where = 'AND (';

					foreach ( $intervals as $interval ) {
						if ( empty( $interval ) || ! isset( $interval['start_date'] ) || ! isset( $interval['end_date'] ) ) {
							continue;
						}
						$intervals_where .= $wpdb->prepare( ' c.created_date >= %s AND c.created_date <= %s OR', $interval['start_date'], $interval['end_date'] );
					}

					$intervals_where = trim( $intervals_where, 'OR' );
					$intervals_where .= ')';

					$query_where .= " {$intervals_where}";
				}

				if ( ! empty( $q['orderby'] ) ) {

					$orderby = (array) $q['orderby'];
					$order   = $q['order'];
					$clause  = '';
					// Validate order.
					if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
						$order = 'DESC';
					}

					foreach ( $orderby as $order_key => $order_value ) {
						if ( is_string( $order_value ) ) {
							$clause .= " {$order_value} {$order},";
						} else {
							if ( in_array( $order_value, array( 'ASC', 'DESC' ), true ) ) {
								$clause .= " {$order_key} {$order_value},";
							} elseif ( ! empty( $order_value ) && is_array( $order_value ) ) {
								if ( isset( $order_value['values'] ) ) {
									$field_values = $order_value['values'];
									$field_order  = isset( $order_value['order'] ) ? $order_value['order'] : $order;
								} else {
									$field_values = $order_value;
									$field_order  = $order;
								}

								$clause .= " FIELD( {$order_key}, " . implode( ', ', $field_values ) . " ) {$field_order},";
							}
						}
					}

					if ( ! empty( $clause ) ) {
						$query_order = ' ORDER BY ' . rtrim( $clause, ',' );
					}
				}

				if ( ! empty( $q['group_by'] ) && in_array( $q['group_by'], array( 'vendor_id', 'product_id', 'time_interval' ), true ) ) {
					$query_group .= sprintf( 'GROUP BY %s', esc_sql( $q['group_by'] ) );
				}

				if ( ! empty( $q['number'] ) && 0 < (int) $q['number'] ) {
					$offset      = ! empty( $q['offset'] ) ? absint( $q['offset'] ) : 0;
					$query_limit .= $wpdb->prepare( ' LIMIT %d, %d', $offset, $q['number'] );
				}

				// compose query.
				$query = "SELECT {$query_select} FROM {$wpdb->commissions} AS c WHERE {$query_where} {$query_group} {$query_order} {$query_limit}";
				$res   = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore

				// Store cache.
				$yith_wcmv_cache->set( $cache_key, $res, 'commissions', DAY_IN_SECONDS );
			}

			if ( empty( $res ) ) {
				return array();
			}

			switch ( $q['group_by'] ) {
				case 'vendor_id':
				case 'product_id':
				case 'time_interval':
					$res = array_combine( wp_list_pluck( $res, $q['group_by'] ), $res );
					break;
				default:
					$res = array_shift( $res );
					break;
			}

			return $res;
		}

		/**
		 * Build a date query clause
		 *
		 * @since  4.0.0
		 * @param array  $query  The query clause.
		 * @param string $column The column to use in the query.
		 * @return string
		 */
		protected static function build_date_query_clause( $query, $column ) {
			// Map fields.
			$date_query = array();
			foreach ( $query as $key => $value ) {
				switch ( $key ) {
					case 'start_date':
						$date_query['after'] = $value;
						break;
					case 'end_date':
						$date_query['before'] = $value;
						break;
					default:
						$date_query[ $key ] = $value;
						break;
				}
			}

			$date_query = new WP_Date_Query( $date_query, 'c.created_date' );
			return $date_query->get_sql();
		}
	}
}
