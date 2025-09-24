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

if ( ! class_exists( 'YITH_Vendors_Object_Collection' ) ) {
	/**
	 * This is a helper class for create a collection from a query. Useful for REST API and List Tables
	 */
	class YITH_Vendors_Object_Collection {

		/**
		 * The factory class for this collection.
		 *
		 * @since 4.0.0
		 * @var string
		 */
		protected $factory = '';

		/**
		 * The query arguments array used for create this collection.
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $query_args = array();

		/**
		 * The items collected
		 *
		 * @since 4.0.0
		 * @var array
		 */
		protected $items = array();

		/**
		 * The total items collected
		 *
		 * @since 4.0.0
		 * @var integer
		 */
		protected $total_items = 0;

		/**
		 * The total pages for this collection
		 *
		 * @since 4.0.0
		 * @var integer
		 */
		protected $total_pages = 0;

		/**
		 * Current collection page
		 *
		 * @since 4.0.0
		 * @var integer
		 */
		protected $current_page = 0;

		/**
		 * Constructor
		 *
		 * @since  4.0.0
		 * @param string $factory The factory to use.
		 * @param array  $args    (Optional) The query arguments. Default is empty array.
		 */
		public function __construct( $factory, $args = array() ) {
			if ( empty( $factory ) || ! class_exists( $factory ) ) {
				yith_wcmv_doing_it_wrong( 'YITH_Vendors_Object_Collection', 'Please provide a valid factory class to use', YITH_WPV_VERSION );
			} else {
				$this->factory    = $factory;
				$this->query_args = $args;
			}
		}

		/**
		 * Execute the query
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function query() {

			if ( $this->factory && ! method_exists( $this->factory, 'query' ) ) {
				yith_wcmv_doing_it_wrong( 'YITH_Vendors_Object_Collection', sprintf( 'query method not found for factory %s', $this->factory ), YITH_WPV_VERSION );
				return;
			}

			$this->items = call_user_func( array( $this->factory, 'query' ), $this->query_args );
			if ( ! empty( $this->items ) ) {

				$number = isset( $query_args['number'] ) ? absint( $query_args['number'] ) : 10;
				$offset = isset( $query_args['offset'] ) ? absint( $query_args['offset'] ) : 0;

				$this->total_items  = call_user_func( array( $this->factory, 'count' ), $this->query_args );
				$this->total_pages  = ceil( $this->total_items / $number );
				$this->current_page = $offset + 1;
			}
		}


		/**
		 * Execute the get_stats query
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public function get_stats() {

			if ( $this->factory && ! method_exists( $this->factory, 'get_stats' ) ) {
				yith_wcmv_doing_it_wrong( 'YITH_Vendors_Object_Collection', sprintf( 'get_stats method not found for factory %s', $this->factory ), YITH_WPV_VERSION );
				return;
			}

			$this->items = call_user_func( array( $this->factory, 'get_stats' ), $this->query_args );
			if ( ! empty( $this->items ) ) {

				$number = isset( $query_args['number'] ) ? absint( $query_args['number'] ) : 10;
				$offset = isset( $query_args['offset'] ) ? absint( $query_args['offset'] ) : 0;

				$this->total_items  = call_user_func( array( $this->factory, 'count' ), $this->query_args );
				$this->total_pages  = ceil( $this->total_items / $number );
				$this->current_page = $offset + 1;
			}
		}

		/**
		 * Get collection items
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_items() {
			return $this->items;
		}

		/**
		 * Get collection total items
		 *
		 * @since  4.0.0
		 * @return integer
		 */
		public function get_total_items() {
			return $this->total_items;
		}

		/**
		 * Get collection total pages
		 *
		 * @since  4.0.0
		 * @return integer
		 */
		public function get_total_pages() {
			return $this->total_pages;
		}

		/**
		 * Get collection current page
		 *
		 * @since  4.0.0
		 * @return integer
		 */
		public function get_current_page() {
			return $this->current_page;
		}

		/**
		 * Check if current collection is empty
		 *
		 * @since  4.0.0
		 * @return boolean
		 */
		public function is_empty() {
			return empty( $this->items );
		}
	}
}
