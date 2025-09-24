<?php
/**
 * REST API Vendors controller.
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */


defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_REST_Vendors_Controller' ) ) {
	/**
	 * REST API controller class.
	 */
	class YITH_Vendors_REST_Vendors_Controller extends YITH_Vendors_Abstract_REST_CRUD_Controller {
		/**
		 * Object type
		 * Will be used to retrieve data store and base classes
		 *
		 * @var string
		 */
		protected $object = 'vendor';

		/**
		 * Route base.
		 *
		 * @var string
		 */
		protected $rest_base = 'vendors';

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
					'id'                => array(
						'description' => _x( 'Unique identifier for the vendor.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'name'              => array(
						'description' => _x( 'Vendor name.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'slug'              => array(
						'description' => _x( 'Vendor token.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'description'       => array(
						'description' => _x( 'Vendor description.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'status'            => array(
						'description' => _x( 'The vendor account status.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'formatted_address' => array(
						'description' => _x( 'Vendor formatted address.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'address'           => array(
						'description' => _x( 'Vendor address.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
						'properties'  => array(
							'location'  => array(
								'description' => _x( 'Vendor location.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'city'      => array(
								'description' => _x( 'Vendor city.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'zipcode'   => array(
								'description' => _x( 'Vendor zipcode.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'country'   => array(
								'description' => _x( 'Vendor country.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'state'     => array(
								'description' => _x( 'Vendor state.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'vat'       => array(
								'description' => _x( 'Vendor state.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
							'telephone' => array(
								'description' => _x( 'Vendor description.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
						),
					),
					'vat'               => array(
						'description' => _x( 'Vendor state.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'store_email'       => array(
						'description' => _x( 'Vendor description.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'owner'             => array(
						'description' => _x( 'Owner user ID.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					),
					'commission_type'   => array(
						'description' => _x( 'Vendor commission type.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'commission_rate'   => array(
						'description' => _x( 'Vendor commission rate.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'number',
						'context'     => array( 'view', 'edit' ),
					),
					'skip_review'       => array(
						'description' => _x( 'The vendor is allowed to publish products without the admin\'s review.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'enum'        => array( 'yes', 'no' ),
						'context'     => array( 'view', 'edit' ),
					),
					'featured_products' => array(
						'description' => _x( 'The vendor is allowed to manage featured products.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'string',
						'enum'        => array( 'yes', 'no' ),
						'context'     => array( 'view', 'edit' ),
					),
					'socials'           => array(
						'description' => _x( 'Vendor\'s socials list.', '[REST API] Vendor schema', 'yith-woocommerce-product-vendors' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
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
			return 'YITH_Vendors_Factory';
		}
	}
}
