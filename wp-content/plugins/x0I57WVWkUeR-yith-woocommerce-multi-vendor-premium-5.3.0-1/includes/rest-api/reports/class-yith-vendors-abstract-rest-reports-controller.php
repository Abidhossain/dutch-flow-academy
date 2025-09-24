<?php
/**
 * Report REST API abstract controller
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Abstract_REST_Reports_Controller' ) ) {
	/**
	 * Report REST API abstract controller class.
	 *
	 * @extends WC_REST_Reports_Controller
	 */
	abstract class YITH_Vendors_Abstract_REST_Reports_Controller extends WC_REST_Reports_Controller {

		/**
		 * Endpoint namespace.
		 *
		 * @var string
		 */
		protected $namespace = YITH_WPV_REST_NAMESPACE;

		/**
		 * Rest sub-path
		 *
		 * @var string
		 */
		protected $rest_path = '';

		/**
		 * Mapping between external parameter name and name used in query class.
		 *
		 * @var array
		 */
		protected $param_mapping = array();

		/**
		 * Init controller
		 */
		public function __construct() {
			$this->rest_base .= "/{$this->rest_path}";
		}

		/* === ROUTES & REQUEST HANDLING === */

		/**
		 * Register the routes for reports.
		 *
		 * Register base path, to retrieve items in this endpoint; should be overridden
		 * to define other paths.
		 */
		public function register_routes() {
			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_items' ),
						'permission_callback' => array( $this, 'get_items_permissions_check' ),
						'args'                => $this->get_collection_params(),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
		}

		/**
		 * Get the query params for collections.
		 *
		 * @return array
		 */
		public function get_collection_params() {
			$orderby_values = $this->get_valid_orderby_values();

			$params                  = array();
			$params['context']       = $this->get_context_param( array( 'default' => 'view' ) );
			$params['page']          = array(
				'description'       => _x( 'Current collection page.', '[REST API] General collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			);
			$params['per_page']      = array(
				'description'       => _x( 'Maximum number of items to be returned in the result set.', '[REST API] General collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['after']         = array(
				'description'       => _x( 'Limit response to resources published after a given ISO 8601 compliant date.', '[REST API] General collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['before']        = array(
				'description'       => _x( 'Limit response to resources published before a given ISO 8601 compliant date.', '[REST API] General collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['order']         = array(
				'description'       => _x( 'Order sort attribute ascending or descending.', '[REST API] General collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'string',
				'default'           => 'desc',
				'enum'              => array( 'asc', 'desc' ),
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['extended_info'] = array(
				'description'       => _x( 'Add additional info about each item to the report.', '[REST API] General collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'wc_string_to_bool',
				'validate_callback' => 'rest_validate_request_arg',
			);

			if ( ! empty( $orderby_values ) ) {
				$params['orderby'] = array(
					'description'       => _x( 'Sort collection by object attribute.', '[REST API] General collection params.', 'yith-woocommerce-product-vendors' ),
					'type'              => 'string',
					'default'           => $orderby_values[0],
					'enum'              => $orderby_values,
					'validate_callback' => 'rest_validate_request_arg',
				);
			}

			return $params;
		}

		/* === QUERY HANDLING === */

		/**
		 * Returns an array of query args starting from request
		 *
		 * @param WP_REST_Request $request Request object.
		 *
		 * @return array Array of arguments for the query.
		 */
		public function get_query_args( $request ) {
			$query_args = array();
			$query_vars = array_keys( $this->get_collection_params() );

			if ( empty( $query_vars ) ) {
				return $query_args;
			}

			foreach ( $query_vars as $param_name ) {
				if ( ! isset( $request[ $param_name ] ) ) {
					continue;
				}

				if ( isset( $this->param_mapping[ $param_name ] ) ) {
					$query_args[ $this->param_mapping[ $param_name ] ] = $request[ $param_name ];
				} else {
					$query_args[ $param_name ] = $request[ $param_name ];
				}
			}

			// converts request parameter to query args.
			$query_args = $this->get_date_query_args( $query_args, $request );
			$query_args = $this->get_pagination_query_args( $query_args, $request );

			return $query_args;
		}

		/**
		 * Converts date parameters in the request in an interval, used by data store to filter out results.
		 *
		 * @param array           $query_args Arguments for the query.
		 * @param WP_REST_Request $request    Request object.
		 *
		 * @return array Array of filtered query arguments.
		 */
		public function get_date_query_args( $query_args, $request ) {
			if ( ! empty( $request['before'] ) || ! empty( $request['after'] ) ) {
				$query_args['date_query'] = array_merge(
					isset( $request['after'] ) ? array(
						'start_date' => gmdate( 'Y-m-d H:i:s', strtotime( $request['after'] ) ),
					) : array(),
					isset( $request['before'] ) ? array(
						'end_date' => gmdate( 'Y-m-d H:i:s', strtotime( $request['before'] ) ),
					) : array()
				);
			}

			return $query_args;
		}

		/**
		 * Converts page/per_page parameters in limit/offset, that can be used by data store to paginate results.
		 *
		 * @param array           $query_args Arguments for the query.
		 * @param WP_REST_Request $request    Request object.
		 *
		 * @return array Array of filtered query arguments.
		 */
		public function get_pagination_query_args( $query_args, $request ) {
			if ( ! empty( $request['per_page'] ) && 0 < $request['per_page'] ) {
				$page     = ! empty( $request['page'] ) ? (int) $request['page'] : 1;
				$per_page = (int) $request['per_page'];

				$query_args['number'] = $per_page;
				$query_args['offset'] = ( $page - 1 ) * $per_page;
			}

			return $query_args;
		}

		/* === PREPARE RESPONSE === */

		/**
		 * Prepare an object for serialization.
		 *
		 * @param YITH_Vendors_Data_Store|object|array $object  Data object.
		 * @param WP_REST_Request                      $request Request object.
		 *
		 * @return WP_REST_Response
		 */
		public function prepare_item_for_response( $object, $request ) {
			$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

			$data = $this->serialize_object( $object );
			$data = $this->add_additional_fields_to_object( $data, $request );
			$data = $this->filter_response_by_context( $data, $context );

			// Wrap the data in a response object.
			$response = rest_ensure_response( $data );
			$response->add_links( $this->prepare_item_links( $object ) );

			/**
			 * Filter a report returned from the API.
			 *
			 * Allows modification of the report data right before it is returned.
			 *
			 * @param WP_REST_Response $response The response object.
			 * @param object           $object   The original report object.
			 * @param WP_REST_Request  $request  Request used to generate the response.
			 */
			return apply_filters( 'yith_wcmv_rest_prepare_report_object', $response, $object, $request, $this );
		}

		/**
		 * Init pagination params for the response
		 *
		 * @param YITH_Vendors_Object_Collection|array $collection Reference collection, or array of items.
		 * @param WP_REST_Request                      $request    Request object.
		 * @param WP_REST_Response                     $response   Response object.
		 *
		 * @return WP_REST_Response Response object with pagination parameters.
		 */
		public function prepare_pagination_params( $collection, $request, $response ) {
			// if we're dealing with a plain array, we have no meta regarding pagination.
			if ( is_array( $collection ) ) {
				return $response;
			}

			$response->header( 'X-WP-Total', (int) $collection->get_total_items() );
			$response->header( 'X-WP-TotalPages', (int) $collection->get_total_pages() );

			$page      = $collection->get_current_page();
			$max_pages = $collection->get_total_pages();
			$base      = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

			if ( $page > 1 ) {
				$prev_page = $page - 1;
				if ( $prev_page > $max_pages ) {
					$prev_page = $max_pages;
				}
				$prev_link = add_query_arg( 'page', $prev_page, $base );
				$response->link_header( 'prev', $prev_link );
			}

			if ( $max_pages > $page ) {
				$next_page = $page + 1;
				$next_link = add_query_arg( 'page', $next_page, $base );
				$response->link_header( 'next', $next_link );
			}

			return $response;
		}

		/**
		 * Returns an array of valid values for orderby param
		 * Should be overridden on dedicated class to have report-aware response
		 *
		 * @return array
		 */
		public function get_valid_orderby_values() {
			return array();
		}

		/**
		 * Returns the default value for orderby param
		 * Should be overridden on dedicated class to have report-aware response
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_default_orderby_value() {
			return '';
		}

		/**
		 * Maybe restrict query for current vendor
		 *
		 * @since  4.0.0
		 * @param array $query_args The current query args array.
		 * @return array
		 */
		public function maybe_restrict_content_for_vendor( $query_args ) {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$query_args['vendor_id'] = $vendor->get_id();
			}
			return $query_args;
		}

		/**
		 * Retrieves correct query order, depending on the ordering requested via query_args
		 *
		 * @since  4.0.0
		 * @param array $query_args Query arguments.
		 * @return string
		 */
		protected function get_correct_orderby( $query_args ) {
			$valid_values = $this->get_valid_orderby_values();
			$default      = $this->get_default_orderby_value();

			$orderby = isset( $query_args['orderby'] ) ? $query_args['orderby'] : '';
			if ( ! $orderby || ! in_array( $orderby, $valid_values, true ) ) {
				$orderby = $default;
			}

			return $orderby;
		}

		/**
		 * Serialize an YITH_Vendors_Data_Store, according to schema
		 *
		 * @param YITH_Vendors_Data_Store|object|array $object     Data object.
		 * @param array                                $properties Properties to retrieve, defaults to entire schema.
		 *
		 * @return array Serialized object.
		 */
		protected function serialize_object( $object, $properties = array() ) {
			return (array) $object;
		}

		/**
		 * Prepare links for the request.
		 * Should be overridden to add links specific to the item.
		 *
		 * @param array $object Object data.
		 *
		 * @return array Links for the given post.
		 */
		protected function prepare_item_links( $object ) {
			return array();
		}
	}
}
