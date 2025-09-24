<?php
/**
 * REST CRUD API abstract controller
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Abstract_REST_CRUD_Controller' ) ) {
	/**
	 * CRUD REST API abstract controller class.
	 *
	 * @extends WC_REST_CRUD_Controller
	 */
	abstract class YITH_Vendors_Abstract_REST_CRUD_Controller extends WC_REST_Controller {

		/**
		 * Object type
		 * Will be used to retrieve data store and base classes
		 *
		 * @var string
		 */
		protected $object = '';

		/**
		 * Endpoint namespace.
		 *
		 * @since 4.0.0
		 * @var string
		 */
		protected $namespace = YITH_WPV_REST_NAMESPACE;

		/**
		 * Object factory
		 *
		 * @since 4.0.0
		 * @var null|string
		 */
		protected $object_factory = null;

		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->set_factory();
		}

		/**
		 * Set object factory that will be used by this controller
		 */
		protected function set_factory() {
			$this->object_factory = $this->get_object_factory();
			if ( empty( $this->object_factory ) || ! class_exists( $this->object_factory ) ) {
				wp_die( esc_html_x( 'There was an error while initializing REST endpoint.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ) );
			}
		}

		/* === ROUTES HANDLING === */

		/**
		 * Register the routes for products.
		 */
		public function register_routes() {

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_items' ),
						'permission_callback' => array( $this, 'get_items_permissions_check' ),
						'args'                => $this->get_collection_params(),
					),
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'create_item' ),
						'permission_callback' => array( $this, 'create_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<id>[\d]+)',
				array(
					'args'   => array(
						'id' => array(
							'description' => _x( 'Unique identifier for the resource.', '[REST API] Request param', 'yith-woocommerce-product-vendors' ),
							'type'        => 'integer',
						),
					),
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => array(
							'context' => $this->get_context_param(
								array(
									'default' => 'view',
								)
							),
						),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'update_item' ),
						'permission_callback' => array( $this, 'update_item_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'callback'            => array( $this, 'delete_item' ),
						'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/batch',
				array(
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'batch_items' ),
						'permission_callback' => array( $this, 'batch_items_permissions_check' ),
						'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					),
					'schema' => array( $this, 'get_public_batch_schema' ),
				)
			);
		}

		/**
		 * Get a single item.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_item( $request ) {
			$object = $this->get_object( (int) $request['id'] );

			if ( ! $object || 0 === $object->get_id() ) {
				return new WP_Error( "yith_wcmv_rest_{$this->object}_invalid_id", _x( 'Invalid ID.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), array( 'status' => 404 ) );
			}

			$data     = $this->prepare_object_for_response( $object, $request );
			$response = rest_ensure_response( $data );

			return $response;
		}

		/**
		 * Get a collection of objects.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_items( $request ) {
			$collection = $this->get_objects( $this->get_query_args( $request ) );

			$objects = array();
			foreach ( $collection->get_items() as $object ) {
				$data      = $this->prepare_object_for_response( $object, $request );
				$objects[] = $this->prepare_response_for_collection( $data );
			}

			$page      = $collection->get_current_page();
			$max_pages = $collection->get_total_pages();

			$response = rest_ensure_response( $objects );
			$response->header( 'X-WP-Total', $collection->get_total_items() );
			$response->header( 'X-WP-TotalPages', $max_pages );

			$base          = $this->rest_base;
			$attrib_prefix = '(?P<';
			if ( strpos( $base, $attrib_prefix ) !== false ) {
				$attrib_names = array();
				preg_match( '/\(\?P<[^>]+>.*\)/', $base, $attrib_names, PREG_OFFSET_CAPTURE );
				foreach ( $attrib_names as $attrib_name_match ) {
					$beginning_offset = strlen( $attrib_prefix );
					$attrib_name_end  = strpos( $attrib_name_match[0], '>', $attrib_name_match[1] );
					$attrib_name      = substr( $attrib_name_match[0], $beginning_offset, $attrib_name_end - $beginning_offset );
					if ( isset( $request[ $attrib_name ] ) ) {
						$base = str_replace( "(?P<$attrib_name>[\d]+)", $request[ $attrib_name ], $base );
					}
				}
			}
			$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $base ) ) );

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
		 * Create a single item.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function create_item( $request ) {
			if ( ! empty( $request['id'] ) ) {
				// translators: %s: post type.
				return new WP_Error( "yith_wcmv_rest_{$this->object}_exists", sprintf( _x( 'Cannot create existing %s.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), $this->object ), array( 'status' => 400 ) );
			}

			$data      = $this->prepare_request_for_database( $request );
			$object_id = call_user_func( array( $this->object_factory, 'create' ), $data );

			if ( is_wp_error( $object_id ) ) {
				return new WP_Error( "yith_wcmv_rest_{$this->object}_create_error", $object_id->get_error_message(), array( 'status' => 500 ) );
			}

			// Get object created.
			$object = $this->get_object( $object_id );
			/**
			 * Fires after a single object is created or updated via the REST API.
			 *
			 * @param YITH_Vendors_Data_Store $object   Inserted object.
			 * @param WP_REST_Request         $request  Request object.
			 * @param boolean                 $creating True when creating object, false when updating.
			 */
			do_action( "yith_wcmv_rest_create_{$this->object}_object", $object, $request, true );

			$request->set_param( 'context', 'edit' );
			$response = $this->prepare_object_for_response( $object, $request );
			$response = rest_ensure_response( $response );
			$response->set_status( 201 );
			$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object->get_id() ) ) );

			return $response;
		}

		/**
		 * Update a single item.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|WP_REST_Response
		 */
		public function update_item( $request ) {
			$object = $this->get_object( (int) $request['id'] );

			if ( ! $object || 0 === $object->get_id() ) {
				return new WP_Error( "yith_wcmv_rest_{$this->object}_invalid_id", _x( 'Invalid ID.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), array( 'status' => 400 ) );
			}

			$data = $this->prepare_request_for_database( $request );
			$res  = call_user_func( array( $this->object_factory, 'update' ), $object->get_id(), $data );
			if ( is_wp_error( $res ) ) {
				return new WP_Error( "yith_wcmv_rest_{$this->object}_update_error", $res->get_error_message(), array( 'status' => 500 ) );
			}

			/**
			 * Fires after a single object is created or updated via the REST API.
			 *
			 * @param YITH_Vendors_Data_Store $object   Inserted object.
			 * @param WP_REST_Request         $request  Request object.
			 * @param boolean                 $creating True when creating object, false when updating.
			 */
			do_action( "yith_wcmv_rest_insert_{$this->object}_object", $object, $request, false );

			$request->set_param( 'context', 'edit' );
			$response = $this->prepare_object_for_response( $object, $request );
			return rest_ensure_response( $response );
		}

		/**
		 * Delete a single item.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function delete_item( $request ) {

			$object = $this->get_object( absint( $request['id'] ) );

			if ( ! $object || 0 === $object->get_id() ) {
				return new WP_Error( "yith_wcmv_rest_{$this->object}_invalid_id", _x( 'Invalid ID.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), array( 'status' => 404 ) );
			}

			$request->set_param( 'context', 'edit' );
			$res = call_user_func( array( $this->object_factory, 'delete' ), $object->get_id() );

			if ( is_wp_error( $res ) ) {
				// translators: %s: post type.
				return new WP_Error( 'yith_wcmv_rest_cannot_delete', sprintf( _x( 'The %s cannot be deleted.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), $this->object ), array( 'status' => 500 ) );
			}

			$response = $this->prepare_object_for_response( $object, $request );

			/**
			 * Fires after a single object is deleted or trashed via the REST API.
			 *
			 * @param YITH_Vendors_Data_Store $object   The deleted or trashed object.
			 * @param WP_REST_Response        $response The response data.
			 * @param WP_REST_Request         $request  The request sent to the API.
			 */
			do_action( "yith_wcmv_rest_delete_{$this->object}_object", $object, $response, $request );

			return $response;
		}

		/* === REQUEST HANDLING === */

		/**
		 * Returns an array of valid values for orderby param
		 * Should be overridden on dedicated class to have object-aware response
		 *
		 * @return array
		 */
		public function get_valid_orderby_values() {
			return array();
		}

		/**
		 * Get the query params for collections of attachments.
		 *
		 * @return array
		 */
		public function get_collection_params() {
			$params                       = array();
			$params['context']            = $this->get_context_param();
			$params['context']['default'] = 'view';

			$params['page']     = array(
				'description'       => _x( 'Current collection page.', '[REST API] Vendor collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			);
			$params['per_page'] = array(
				'description'       => _x( 'Maximum number of items to be returned in the result set.', '[REST API] Vendor collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['search']   = array(
				'description'       => _x( 'Limit results to those matching a string.', '[REST API] Vendor collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['after']    = array(
				'description'       => _x( 'Limit response to resources published after a given ISO 8601 compliant date.', '[REST API] Vendor collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['before']   = array(
				'description'       => _x( 'Limit response to resources published before a given ISO 8601 compliant date.', '[REST API] Vendor collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['order']    = array(
				'description'       => _x( 'Order sort attribute ascending or descending.', '[REST API] Vendor collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'string',
				'default'           => 'desc',
				'enum'              => array( 'asc', 'desc' ),
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['exclude']  = array(
				'description'       => _x( 'Ensure the result set excludes specific IDs.', '[REST API] Vendor collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'integer',
				),
				'default'           => array(),
				'sanitize_callback' => 'wp_parse_id_list',
			);
			$params['include']  = array(
				'description'       => _x( 'Limit the result set to specific IDs.', '[REST API] Vendor collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'integer',
				),
				'default'           => array(),
				'sanitize_callback' => 'wp_parse_id_list',
			);
			$params['orderby']  = array(
				'description'       => _x( 'Sort collection by object attribute.', '[REST API] Vendor collection params.', 'yith-woocommerce-product-vendors' ),
				'type'              => 'string',
				'default'           => 'date',
				'enum'              => $this->get_valid_orderby_values(),
				'validate_callback' => 'rest_validate_request_arg',
			);

			return apply_filters( "yith_wcvm_rest_{$this->object}_collection_params", $params );
		}

		/* === OBJECTS HANDLING === */

		/**
		 * Get object.
		 *
		 * @param int $id Object ID.
		 * @return YITH_Vendors_Data_Store|WP_Error WC_Data object or WP_Error object.
		 */
		protected function get_object( $id ) {
			return call_user_func( array( $this->object_factory, 'read' ), $id );
		}

		/**
		 * Get a collection of objects.
		 *
		 * @param array $query_args Arguments to apply to the query.
		 * @return YITH_Vendors_Object_Collection
		 */
		protected function get_objects( $query_args = array() ) {
			$query_args['fields'] = 'all';
			$collection           = new YITH_Vendors_Object_Collection( $this->object_factory, $query_args );
			$collection->query();

			return $collection;
		}

		/**
		 * Creates query args to use to query objects
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return array Array of query args.
		 */
		protected function get_query_args( $request ) {
			$query_args = array();
			$params     = $this->get_collection_params();

			foreach ( $params as $param => $value ) {
				$value = isset( $request[ $param ] ) ? $request[ $param ] : false;

				if ( ! $value ) {
					continue;
				}

				switch ( $param ) {
					case 'before':
					case 'after':
						$query_args['date_query']           = array();
						$query_args['date_query'][ $param ] = gmdate( 'Y-m-d H:i:s', strtotime( $value ) );
						break;
					case 'per_page':
						$query_args['number'] = $value;
						break;
					case 'page':
						$per_page = isset( $request['per_page'] ) ? (int) $request['per_page'] : 10;

						$query_args['offset'] = ( $value - 1 ) * $per_page;
						break;
					default:
						$query_args[ $param ] = $value;
				}
			}

			return $query_args;
		}

		/**
		 * Prepare links for the request.
		 *
		 * @param YITH_Vendors_Data_Store $object  Object data.
		 * @param WP_REST_Request         $request Request object.
		 *
		 * @return array Links for the given post.
		 */
		protected function prepare_links( $object, $request ) {
			$links = array(
				'self'       => array(
					'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object->get_id() ) ),
				),
				'collection' => array(
					'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
				),
			);

			return $links;
		}

		/**
		 * Populate object properties by given schema
		 *
		 * @since  4.0.0
		 * @param YITH_Vendors_Data_Store $object     Object data.
		 * @param array                   $properties An array of properties to populate.
		 * @return array An array of properties
		 */
		protected function populate_object_data_for_response( $object, $properties ) {
			$data = array();
			foreach ( $properties as $property => $property_data ) {

				if ( 'socials' === $property ) {
					$data[ $property ] = (object) $object->get_socials();
				} elseif ( 'object' === $property_data['type'] ) {
					$data[ $property ] = $this->populate_object_data_for_response( $object, $property_data['properties'] );
				} else {
					$getter            = "get_{$property}";
					$value             = method_exists( $object, $getter ) ? $object->$getter() : $object->get_meta( $property );
					$data[ $property ] = $value;
				}
			}

			return $data;
		}

		/**
		 * Prepares the object for the REST response.
		 *
		 * @param YITH_Vendors_Data_Store $object  Object data.
		 * @param WP_REST_Request         $request Request object.
		 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
		 */
		protected function prepare_object_for_response( $object, $request ) {
			$schema     = $this->get_item_schema();
			$properties = $schema['properties'];
			$data       = $this->populate_object_data_for_response( $object, $properties );

			// Prepare response.
			$response = rest_ensure_response( $data );
			$response->add_links( $this->prepare_links( $object, $request ) );

			return $response;
		}

		/**
		 * Populate object data by given request
		 *
		 * @since  4.0.0
		 * @param WP_REST_Request $request    Request object.
		 * @param array           $properties An array of properties to populate.
		 * @return array An array of properties
		 */
		protected function populate_object_data_for_database( $request, $properties ) {
			$data = array();
			foreach ( $properties as $property => $property_data ) {
				if ( 'id' === $property || ! isset( $request[ $property ] ) ) {
					continue;
				}

				if ( 'socials' === $property ) {
					$data[ $property ] = (array) $request[ $property ];

				} elseif ( 'object' === $property_data['type'] ) {
					$subdata = $this->populate_object_data_for_database( $request[ $property ], $property_data['properties'] );
					$data    = array_merge( $data, $subdata );
				} else {
					$value             = $request[ $property ];
					$data[ $property ] = $value;
				}
			}

			return $data;
		}

		/**
		 * Prepares a request data array for being used on create/update operation
		 *
		 * @param WP_REST_Request $request Request object.
		 * @return array The prepared item data.
		 */
		protected function prepare_request_for_database( $request ) {
			$schema     = $this->get_item_schema();
			$properties = array_filter( $schema['properties'], array( $this, 'filter_writable_props' ) );

			$data = $this->populate_object_data_for_database( $request, $properties );

			return apply_filters( "yith_wcmv_rest_api_{$this->object}_request_data", $data, $request );
		}

		/* === PERMISSIONS HANDLING === */

		/**
		 * Check if current user can perform action to object
		 *
		 * @since  4.0.0
		 * @param string $capability User capability to check.
		 * @param object $object     The object to check.
		 * @return boolean True if has access, false otherwise.
		 */
		protected function current_user_can( $capability, $object = null ) {
			$has_cap = false;
			$user_id = get_current_user_id();

			if ( user_can( $user_id, 'manage_woocommerce' ) ) {
				$has_cap = true;
			} elseif ( 'read' === $capability && ! empty( $object ) && method_exists( $object, 'get_user_id' ) ) {
				$has_cap = $object->get_user_id() === $user_id;
			}

			return apply_filters( "yith_wcmv_rest_api_{$this->object}_user_capability", $has_cap, $capability, $user_id, $object );
		}

		/**
		 * Check if a given request has access to read an item.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return bool|WP_Error
		 */
		public function get_item_permissions_check( $request ) {
			$object = $this->get_object( (int) $request['id'] );

			if ( $object && 0 !== $object->get_id() && ! $this->current_user_can( 'read', $object ) ) {
				return new WP_Error( 'yith_wcmv_rest_cannot_view', _x( 'Sorry, you cannot view this resource.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Checks if a given request has access to get items.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return bool|WP_Error True if the request has read access, WP_Error object otherwise.
		 */
		public function get_items_permissions_check( $request ) {
			if ( ! $this->current_user_can( 'query' ) ) {
				return new WP_Error( 'yith_wcmv_rest_cannot_view', _x( 'Sorry, you cannot view this resource.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Checks if a given request has access to get items.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return bool|WP_Error True if the request has read access, WP_Error object otherwise.
		 */
		public function create_item_permissions_check( $request ) {
			if ( ! $this->current_user_can( 'create' ) ) {
				return new WP_Error( 'yith_wcmv_rest_cannot_view', _x( 'Sorry, you cannot view this resource.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Check if a given request has access to update an item.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return bool|WP_Error
		 */
		public function update_item_permissions_check( $request ) {
			$object = $this->get_object( (int) $request['id'] );

			if ( $object && 0 !== $object->get_id() && ! $this->current_user_can( 'edit', $object ) ) {
				return new WP_Error( 'yith_wcmv_rest_cannot_edit', _x( 'Sorry, you are not allowed to edit this resource.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Check if a given request has access to delete an item.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return bool|WP_Error
		 */
		public function delete_item_permissions_check( $request ) {
			$object = $this->get_object( (int) $request['id'] );

			if ( $object && 0 !== $object->get_id() && ! $this->current_user_can( 'delete', $object ) ) {
				return new WP_Error( 'yith_wcmv_rest_cannot_delete', _x( 'Sorry, you are not allowed to delete this resource.', '[REST API] Api error', 'yith-woocommerce-product-vendors' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		/**
		 * Only return writable props from schema.
		 *
		 * @param array $schema Schema.
		 * @return bool
		 */
		protected function filter_writable_props( $schema ) {
			return empty( $schema['readonly'] );
		}
	}
}
