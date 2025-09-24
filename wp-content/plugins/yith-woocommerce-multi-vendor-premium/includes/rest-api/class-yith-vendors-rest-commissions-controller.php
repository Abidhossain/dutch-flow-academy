<?php
/**
 * Class YITH_WCAF_REST_Commissions_Controller
 *
 * @since      4.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_REST_Commissions_Controller' ) ) {
	/**
	 * REST API controller class.
	 */
	class YITH_Vendors_REST_Commissions_Controller extends YITH_Vendors_Abstract_REST_CRUD_Controller {

		/**
		 * Object type
		 * Will be used to retrieve data store and base classes
		 *
		 * @var string
		 */
		protected $object = 'commission';

		/**
		 * Route base.
		 *
		 * @var string
		 */
		protected $rest_base = 'commissions';

		/**
		 * Retrieves the item's schema, conforming to JSON Schema.
		 *
		 * @return array Item schema data.
		 */
		public function get_item_schema() {
			$schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => $this->object,
				'type'       => 'object',
				'properties' => array(
					'id'               => array(
						'description' => _x( 'Unique identifier for the resource.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'order_id'         => array(
						'description' => _x( 'Order ID.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					),
					'line_item_id'     => array(
						'description' => _x( 'Line item ID.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					),
					'vendor_id'        => array(
						'description' => _x( 'Vendor ID.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					),
					'user_id'          => array(
						'description' => _x( 'User ID.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					),
					'rate'             => array(
						'description' => _x( 'Commission rate.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'number',
						'context'     => array( 'view', 'edit' ),
					),
					'amount'           => array(
						'description' => _x( 'Commission amount.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'number',
						'context'     => array( 'view', 'edit' ),
					),
					'amount_refunded'  => array(
						'description' => _x( 'Commission amount refunded.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'number',
						'context'     => array( 'view', 'edit' ),
					),
					'status'           => array(
						'description' => _x( 'Commission status.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'type'             => array(
						'description' => _x( 'Commission type.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'created_date'     => array(
						'description' => _x( 'Creation date.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'date-time',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'created_date_gmt' => array(
						'description' => _x( 'Creation date.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'date-time',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'last_edit'        => array(
						'description' => _x( 'Last edit date.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'date-time',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'last_edit_gmt'    => array(
						'description' => _x( 'Last edit date GMT.', '[REST API] Commission schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'date-time',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			);
			return $this->add_additional_fields_schema( $schema );
		}

		/**
		 * Returns class of objects used in this controller
		 *
		 * @return string|bool Object class or false on failure.
		 */
		protected function get_object_factory() {
			return 'YITH_Vendors_Commissions_Factory';
		}
	}
}
