<?php
/**
 * YITH Vendors Staff module class
 *
 * @since      Version 1.0.0
 * @author     YITH
 * @package YITH\MultiVendor
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


if ( ! class_exists( 'YITH_Vendors_Staff' ) ) {
	/**
	 * YITH Vendors Staff module class
	 */
	class YITH_Vendors_Staff {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Admin Instance
		 *
		 * @since 1.9.17
		 * @var null| YITH_Vendors_Staff_Admin
		 */
		public $admin = null;

		/**
		 * Staff permissions meta key.
		 *
		 * @since 1.9.17
		 * @var string
		 */
		protected $permissions_meta_key = 'yith_wcmv_staff_permissions';

		/**
		 * Construct
		 *
		 * @since  4.0.0
		 * @return void
		 */
		private function __construct() {
			// Init permissions meta key.
			if ( is_multisite() && 1 !== get_current_blog_id() ) {
				$this->permissions_meta_key .= '_' . get_current_blog_id();
			}

			if ( yith_wcmv_is_admin_request() && $this->load_admin_class() ) {
				$this->admin = new YITH_Vendors_Staff_Admin();
			}

			add_filter( 'yith_wcmv_plugin_emails_list', array( $this, 'register_email' ) );
			// Filter vendor get_admins method.
			add_filter( 'yith_wcmv_get_vendor_admins', array( $this, 'add_vendor_admins' ), 10, 3 );
			// Filter user caps based on this personal permissions.
			add_filter( 'user_has_cap', array( $this, 'filter_staff_caps' ), 10, 4 );
			// If an user is deleted, remove it from admins.
			add_action( 'delete_user', array( $this, 'handle_delete_user' ), 10, 1 );
		}

		/**
		 * Register Emails for this module
		 *
		 * @since 4.0.0
		 * @param array $emails An array of registered emails.
		 * @return array
		 */
		public function register_email( $emails ) {
			$emails[] = 'YITH_WC_Email_New_Staff_Member';
			return $emails;
		}

		/**
		 * Get permissions meta key.
		 *
		 * @since  4.0.0
		 * @return string
		 */
		public function get_permissions_meta_key() {
			return $this->permissions_meta_key;
		}

		/**
		 * Check if class admin should be loaded.
		 * Conditions:
		 * - user is administrator OR
		 * - user is vendor owner
		 *
		 * @since  4.0.0
		 * @return boolean
		 */
		protected function load_admin_class() {
			return current_user_can( 'manage_woocommerce' ) || ! $this->is_current_user_staff_member();
		}

		/**
		 * Check if current user is a vendor staff
		 *
		 * @since  4.0.0
		 * @return boolean
		 */
		public function is_current_user_staff_member() {
			$user = wp_get_current_user();
			return $user instanceof WP_User && $this->is_user_staff_member( $user );
		}

		/**
		 * Check if given user is a vendor staff member
		 *
		 * @since  4.0.0
		 * @param WP_User $user The user object.
		 * @return boolean
		 */
		public function is_user_staff_member( $user ) {
			$vendor = yith_wcmv_get_vendor( $user, 'user' );
			return ( $vendor && $vendor->is_valid() && $user->ID !== $vendor->get_owner() );
		}


		/**
		 * Add additional vendor admins to get_admins vendor request
		 *
		 * @since  4.0.0
		 * @param array       $admins An array of current vendor admins.
		 * @param string      $type   How return the data: id to get an array of admin id, objects to get an array of WP_User objects.
		 * @param YITH_Vendor $vendor Current vendor object.
		 * @return int[]|WP_User[]
		 */
		public function add_vendor_admins( $admins, $type, $vendor ) {
			$admins = array_merge( $admins, $this->get_vendor_admins( $vendor, $type ) );
			return array_filter( $admins );
		}

		/**
		 * Get staff permissions
		 *
		 * @since  4.0.0
		 * @param integer $user_id The user id.
		 * @return array
		 */
		public function get_staff_permissions( $user_id ) {
			$permissions = get_user_meta( $user_id, $this->permissions_meta_key, true );
			if ( empty( $permissions ) || ! is_array( $permissions ) ) {
				$permissions = array();
			}

			return $permissions;
		}

		/**
		 * Filter staff cap based on individual permissions.
		 *
		 * @since  4.0.0
		 * @param bool[]   $allcaps Array of key/value pairs where keys represent a capability name
		 *                          and boolean values represent whether the user has that capability.
		 * @param string[] $caps    Required primitive capabilities for the requested capability.
		 * @param array    $args    {
		 *                          Arguments that accompany the requested capability check.
		 *
		 * @type string    $0 Requested capability.
		 * @type int       $1 Concerned user ID.
		 * @type mixed  ...$2 Optional second and further parameters, typically object ID.
		 *                          }
		 * @param WP_User  $user    The user object.
		 * @return bool[]
		 */
		public function filter_staff_caps( $allcaps, $caps, $args, $user ) {
			if ( ! $this->is_user_staff_member( $user ) ) {
				return $allcaps;
			}

			// Check for meta permissions.
			$permissions = $this->get_staff_permissions( $user->ID );
			if ( ! empty( $permissions ) ) {
				$capabilities = YITH_Vendors_Capabilities::get_additional_capabilities();
				foreach ( $permissions as $key => $can ) {
					if ( 'no' === $can && isset( $capabilities[ $key ] ) ) {
						$allcaps = array_diff_key( $allcaps, $capabilities[ $key ] );
					}
				}
			}

			return $allcaps;
		}

		/**
		 * Get admins for given vendor
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor Current vendor object.
		 * @param string      $type   (Optional) How return the data: id to get an array of admin id, objects to get an array of WP_User objects.
		 * @return int[]|WP_User[]
		 */
		public function get_vendor_admins( $vendor, $type = 'id' ) {
			global $yith_wcmv_cache;

			if ( ! $vendor || ! $vendor->is_valid() ) {
				return array();
			}

			$admins = $vendor->get_meta_data( 'admins' );
			if ( empty( $admins ) ) {
				$admins = array();
			}

			if ( 'id' === $type ) {
				return array_map( 'absint', $admins );
			}

			$admins_obj = $yith_wcmv_cache->get_vendor_cache( $vendor->get_id(), 'admins' );
			if ( ! empty( $admins ) && false === $admins_obj ) {
				$admins_obj = get_users( array( 'include' => $admins ) );
				$yith_wcmv_cache->set_vendor_cache( $vendor->get_id(), 'admins', $admins_obj );
			}

			return ! empty( $admins_obj ) ? $admins_obj : array();
		}

		/**
		 * Handle delete user action
		 *
		 * @since  4.0.0
		 * @param integer $user_id The user id deleted.
		 * @return void
		 */
		public function handle_delete_user( $user_id ) {
			$vendor_id = get_user_meta( $user_id, yith_wcmv_get_user_meta_key(), true );
			if ( $vendor_id ) {
				$vendor = yith_wcmv_get_vendor( $vendor_id );
				if ( $vendor && $vendor->is_valid() ) {
					$admins = array_map( 'absint', (array) $vendor->get_meta( 'admins' ) );
					// Search for requested user on admins list.
					$index = array_search( $user_id, $admins, true );
					if ( false !== $index ) {
						unset( $admins[ $index ] );
						$vendor->set_meta( 'admins', $admins );
						$vendor->save();
					}
				}
			}
		}
	}
}

/**
 * Main instance of plugin
 *
 * @since  4.0.0
 * @return YITH_Vendors_Staff
 */
if ( ! function_exists( 'YITH_Vendors_Staff' ) ) {
	function YITH_Vendors_Staff() { // phpcs:ignore
		return YITH_Vendors_Staff::instance();
	}
}

YITH_Vendors_Staff();
