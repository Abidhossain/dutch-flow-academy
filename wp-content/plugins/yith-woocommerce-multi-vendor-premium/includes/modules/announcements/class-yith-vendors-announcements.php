<?php
/**
 * YITH Vendors Announcements Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Announcements' ) ) {
	/**
	 * Handle announcement post type
	 */
	class YITH_Vendors_Announcements {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Post type name
		 *
		 * @since 4.0.0
		 * @const string
		 */
		const POST_TYPE = 'announcement';

		/**
		 * YITH_Vendors_Announcements_Admin instance
		 *
		 * @var null|YITH_Vendors_Announcements_Admin
		 */
		public $admin = null;

		/**
		 * YITH_Vendors_Announcements constructor.
		 *
		 * @since  4.0.0
		 */
		private function __construct() {
			$this->register_post_type();
			if ( yith_wcmv_is_admin_request() ) {
				$this->admin = new YITH_Vendors_Announcements_Admin();
			}
		}

		/**
		 * Register announcement post type
		 *
		 * @since  4.0.0
		 * @return void
		 */
		private function register_post_type() {
			if ( ! is_blog_installed() || post_type_exists( self::POST_TYPE ) ) {
				return;
			}

			register_post_type(
				self::POST_TYPE,
				apply_filters(
					'yith_wcmc_register_post_type_announcement',
					array(
						'labels'              => array(
							'name'                  => __( 'Announcements', 'yith-woocommerce-product-vendors' ),
							'singular_name'         => _x( 'Announcement', 'announcement post type singular name', 'yith-woocommerce-product-vendors' ),
							'add_new'               => __( 'Add announcement', 'yith-woocommerce-product-vendors' ),
							'add_new_item'          => __( 'Add new announcement', 'yith-woocommerce-product-vendors' ),
							'edit'                  => __( 'Edit', 'yith-woocommerce-product-vendors' ),
							'edit_item'             => __( 'Edit announcement', 'yith-woocommerce-product-vendors' ),
							'new_item'              => __( 'New announcement', 'yith-woocommerce-product-vendors' ),
							'view_item'             => __( 'View announcement', 'yith-woocommerce-product-vendors' ),
							'search_items'          => __( 'Search announcements', 'yith-woocommerce-product-vendors' ),
							'not_found'             => __( 'No announcements found', 'yith-woocommerce-product-vendors' ),
							'not_found_in_trash'    => __( 'No announcements found in the Trash', 'yith-woocommerce-product-vendors' ),
							'filter_items_list'     => __( 'Filter announcements', 'yith-woocommerce-product-vendors' ),
							'items_list_navigation' => __( 'Announcements navigation', 'yith-woocommerce-product-vendors' ),
							'items_list'            => __( 'Announcements list', 'yith-woocommerce-product-vendors' ),
						),
						'public'              => false,
						'show_ui'             => true,
						'capability_type'     => 'post',
						'publicly_queryable'  => false,
						'exclude_from_search' => true,
						'show_in_menu'        => false,
						'hierarchical'        => false,
						'show_in_nav_menus'   => false,
						'rewrite'             => false,
						'query_var'           => false,
						'supports'            => array( 'title', 'editor' ),
						'has_archive'         => false,
					)
				)
			);
		}

		/**
		 * Post type fields
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public function get_post_type_fields() {
			return array(
				'title'                 => array(
					'title' => _x( 'Announcement object', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'  => _x( 'Enter a text to identify the announcement.', '[Admin] Announcement field description', 'yith-woocommerce-product-vendors' ),
				),
				'content'               => array(
					'title'         => _x( 'Content', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'          => _x( 'Use the editor to enter the content for this announcement.', '[Admin] Announcement field description', 'yith-woocommerce-product-vendors' ),
					'type'          => 'textarea-editor',
					'media_buttons' => false,
					'textarea_rows' => 10,
				),
				'show_to'               => array(
					'title'   => _x( 'Show to', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'    => _x( 'Choose whether all vendors will see this announcement or only specific vendors.', '[Admin] Announcement field description', 'yith-woocommerce-product-vendors' ),
					'type'    => 'radio',
					'options' => array(
						'all'               => _x( 'All vendors', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
						'specific-criteria' => _x( 'Vendors that match specific criteria', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
						'specific-vendors'  => _x( 'Specific vendors', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					),
				),
				'show_criteria'         => array(
					'title'       => _x( 'Show to vendors that', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'        => _x( 'Choose the conditions required to show this announcement to vendors.', '[Admin] Announcement field description', 'yith-woocommerce-product-vendors' ),
					'type'        => 'select',
					'options'     => $this->get_announcements_show_criteria(),
					'placeholder' => _x( 'Choose a condition', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'deps'        => array(
						'id'    => 'show_to',
						'value' => 'specific-criteria',
					),
				),
				'sales_number_criteria' => array(
					'title' => _x( 'Number of sales', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'  => _x( 'Set the minimum number of sales required to show this announcement to vendors.', '[Admin] Announcement field description', 'yith-woocommerce-product-vendors' ),
					'type'  => 'number',
					'min'   => 0,
					'step'  => 1,
					'deps'  => array(
						'id'    => 'show_criteria',
						'value' => 'sales-number',
					),
				),
				'sales_amount_criteria' => array(
					'title' => _x( 'Amount in sales', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'  => _x( 'Set the minimum amount vendors should have earned in sales in order to show this announcement to them.', '[Admin] Announcement field description', 'yith-woocommerce-product-vendors' ),
					'type'  => 'price',
					'deps'  => array(
						'id'    => 'show_criteria',
						'value' => 'sales-value',
					),
				),
				'show_vendors'          => array(
					'title'    => _x( 'Show to vendors', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'     => _x( 'Choose the vendors to show this announcement to.', '[Admin] Announcement field description', 'yith-woocommerce-product-vendors' ),
					'type'     => 'ajax-vendors',
					'class'    => 'yith-wcmv-ajax-search',
					'style'    => '',
					'multiple' => true,
					'deps'     => array(
						'id'    => 'show_to',
						'value' => 'specific-vendors',
					),
				),
				'scheduled'             => array(
					'title'   => _x( 'Schedule announcement', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'    => _x( 'Choose when this announcement will be visible and when it will be removed.', '[Admin] Announcement field description', 'yith-woocommerce-product-vendors' ),
					'type'    => 'radio',
					'options' => array(
						'manually' => _x( 'Publish it now and remove it manually', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
						'auto'     => _x( 'Set a start and end date and time', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					),
				),
				'scheduled_start'       => array(
					'title' => _x( 'Schedule start date', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'  => '',
					'type'  => 'datepicker',
					'data'  => array(
						'min-date'    => 0,
						'date-format' => apply_filters( 'yith_wcmv_announcements_date_format', 'yy-m-d 00:00:00' ),
					),
					'deps'  => array(
						'id'    => 'scheduled',
						'value' => 'auto',
					),
				),
				'scheduled_end'         => array(
					'title' => _x( 'Schedule end date', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'  => '',
					'type'  => 'datepicker',
					'data'  => array(
						'min-date'    => 0,
						'date-format' => apply_filters( 'yith_wcmv_announcements_date_format', 'yy-m-d 00:00:00' ),
					),
					'deps'  => array(
						'id'    => 'scheduled',
						'value' => 'auto',
					),
				),
				'dismissible'           => array(
					'title' => _x( 'Vendors can dismiss the notice', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'desc'  => _x( 'Enable this option to allow vendors to dismiss the announcement using a close icon.', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'type'  => 'onoff',
				),
			);
		}

		/**
		 * Get announcement criteria
		 *
		 * @since  4.0.0
		 * @return array
		 */
		protected function get_announcements_show_criteria() {
			return apply_filters(
				'yith_wcmv_announcements_show_criteria',
				array(
					'no-privacy-policy'  => _x( 'Have not accepted the Privacy Policy', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'no-terms-condition' => _x( 'Have not accepted the Terms & Conditions', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'no-vat'             => _x( 'Have not entered VAT/SSN', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'reported'           => _x( 'Are being reported by users', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'sales-number'       => _x( 'Achieved a specific number of sales', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'sales-value'        => _x( 'Achieved a specific amount in sales', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
					'new-vendor'         => _x( 'Are new to the site', '[Admin] Announcement field label', 'yith-woocommerce-product-vendors' ),
				)
			);
		}

		/**
		 * Get announcements
		 *
		 * @since  4.0.0
		 * @param array $args (Optional) The arguments to use in query. Same as get_posts. Default value is empty array.
		 * @return array int[]|YITH_Vendor_Announcement[]
		 */
		public function get( $args = array() ) {
			global $yith_wcmv_cache;

			$q_args = array_merge(
				array(
					'posts_per_page' => 10,
					'post_type'      => self::POST_TYPE,
					'post_status'    => 'publish',
				),
				$args
			);

			// Force fields to be ids.
			$q_args['fields'] = 'ids';

			// Check first on cache.
			$cache_key     = $yith_wcmv_cache->build_key( $q_args );
			$announcements = $yith_wcmv_cache->get( $cache_key, 'announcements' );
			if ( false === $announcements ) {
				$announcements = get_posts( $q_args );
				// Set cache.
				$yith_wcmv_cache->set( $cache_key, $announcements, 'announcements', WEEK_IN_SECONDS );
			}

			if ( empty( $args['fields'] ) || 'ids' !== $args['fields'] ) {
				$announcements = array_filter(
					array_map(
						function ( $id ) {
							return new YITH_Vendors_Announcement( $id );
						},
						$announcements
					)
				);
			}

			return $announcements;
		}

		/**
		 * Get announcements
		 *
		 * @since  4.0.0
		 * @param array $args (Optional) The arguments to use in query. Same as get_posts. Default value is empty array.
		 * @return array int[]|YITH_Vendor_Announcement[]
		 */
		public function get_announcements( $args = array() ) {
			return $this->get( $args );
		}

		/**
		 * Get announcements valid for given vendor
		 *
		 * @since  4.0.0
		 * @param null|YITH_Vendor $vendor (Optional) The current vendor, null to get the current one.
		 * @return YITH_Vendors_Announcement[] An array of announcement object for given vendor
		 */
		public function get_announcements_for_vendor( $vendor = null ) {
			if ( ! $vendor instanceof YITH_Vendor ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			}

			if ( ! $vendor || ! $vendor->is_valid() ) {
				return array();
			}

			$announcements = $this->get(
				array(
					'posts_per_page' => -1,
					'meta_query'     => array( // phpcs:ignore
						array(
							'key'   => '_active',
							'value' => 'yes',
						),
					),
				)
			);

			$valid_announcements = array();
			while ( ! empty( $announcements ) ) {
				$announcement = array_shift( $announcements );
				// Exclude empty content and invalid announcement for given vendor.
				if ( empty( $announcement->get_content() ) || ! $announcement->is_valid( $vendor ) ) {
					continue;
				}

				$valid_announcements[] = $announcement;
			}

			return $valid_announcements;
		}

		/**
		 * Get visible announcements for vendor
		 *
		 * @since  4.0.0
		 * @param null|YITH_Vendor $vendor (Optional) The current vendor, null to get the current one.
		 * @return YITH_Vendors_Announcement[] An array of announcement object for given vendor
		 */
		public function get_visible_announcements_for_vendor( $vendor = null ) {
			$announcements = $this->get_announcements_for_vendor( $vendor );

			// Get dismissed announcement for current user.
			$key       = '_yith_wcmv_dismissed_announcements_' . get_current_blog_id();
			$dismissed = get_user_meta( get_current_user_id(), $key, true );
			if ( empty( $dismissed ) || ! is_array( $dismissed ) ) {
				$dismissed = array();
			}

			$visible_announcements = array();
			while ( ! empty( $announcements ) ) {
				$announcement = array_shift( $announcements );
				if ( $announcement->is_dismissible() && isset( $dismissed[ $announcement->get_id() ] ) && $dismissed[ $announcement->get_id() ] > $announcement->get_date( 'U' ) ) {
					continue;
				}

				$visible_announcements[] = $announcement;
			}

			return $visible_announcements;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  4.0.0
 * @return YITH_Vendors_Announcements
 */
if ( ! function_exists( 'YITH_Vendors_Announcements' ) ) {
	function YITH_Vendors_Announcements() { // phpcs:ignore
		return YITH_Vendors_Announcements::instance();
	}
}

YITH_Vendors_Announcements();
