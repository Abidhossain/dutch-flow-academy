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

if ( ! class_exists( 'YITH_Vendors_Capabilities' ) ) {
	/**
	 * This class handle plugin capabilities.
	 */
	class YITH_Vendors_Capabilities {

		/**
		 * Role Name
		 *
		 * @since 4.0.0
		 * @const string
		 */
		const ROLE_NAME = 'yith_vendor';

		/**
		 * Vendor options role
		 *
		 * @since 4.0.0
		 * @const string
		 */
		const ROLE_ADMIN_CAP = 'manage_vendor_store';

		/**
		 * Add Vendor Role.
		 *
		 * @fire   register_activation_hook
		 * @since  4.0.0
		 * @return void
		 */
		public static function add_role() {
			add_role( self::ROLE_NAME, YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ), self::get_capabilities() );
		}

		/**
		 * Remove Vendor Role.
		 *
		 * @fire   register_deactivation_hook
		 * @since  1.6.5
		 * @return void
		 */
		public static function remove_role() {
			remove_role( self::ROLE_NAME );
		}

		/**
		 * Get an array of vendor capabilities
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public static function get_capabilities() {
			// Basic cap for have access to admin.
			$caps = array( 'read' => true );

			// Add additional capabilities.
			foreach ( self::get_additional_capabilities() as $additional_caps ) {
				$caps = array_merge( $caps, $additional_caps );
			}

			return apply_filters( 'yith_wcmv_vendor_capabilities', $caps );
		}

		/**
		 * Get an array of vendor additional capabilities
		 *
		 * @since  4.0.0
		 * @return array
		 */
		public static function get_additional_capabilities() {
			$caps = array(
				'products' => array(
					'edit_product'              => true,
					'read_product'              => true,
					'delete_product'            => true,
					'edit_products'             => true,
					'edit_others_products'      => true,
					'delete_products'           => true,
					'delete_published_products' => true,
					'delete_others_products'    => true,
					'edit_published_products'   => true,
					'assign_product_terms'      => true,
					'upload_files'              => true,
					'manage_bookings'           => true,
					'edit_posts'                => true,
					'delete_posts'              => true,
				),
				'reports'  => array(
					'view_woocommerce_reports' => true,
				),
				'settings' => array(
					'manage_vendor_store' => true,
				),
			);

			// Add import product cap.
			if ( 'yes' === get_option( 'yith_wpv_vendors_option_product_import_management', 'no' ) ) {
				$caps['products']['import'] = true;
			}
			// Add export product cap.
			if ( 'yes' === get_option( 'yith_wpv_vendors_option_product_export_management', 'no' ) ) {
				$caps['products']['export'] = true;
			}

			// Add review cap if needed.
			if ( 'yes' === get_option( 'yith_wpv_vendors_option_review_management', 'no' ) ) {
				$caps['reviews'] = array(
					'edit_posts'        => true,
					'moderate_comments' => true,
				);
			}

			return apply_filters( 'yith_wcmv_vendor_additional_capabilities', $caps );
		}

		/**
		 * Set capabilities for given vendor
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return void
		 */
		public static function set_vendor_capabilities( $vendor ) {
			$admins = $vendor->get_admins();
			foreach ( array_filter( $admins ) as $user_id ) {
				self::set_vendor_capabilities_for_user( $user_id, $vendor );
			}

			do_action( 'yith_wcmv_setted_vendor_capabilities', $vendor );
		}

		/**
		 * Set vendor capabilities for given user
		 *
		 * @since  4.0.0
		 * @param integer|WP_User $user   Could be either the user ID or the WP_User object.
		 * @param YITH_Vendor     $vendor The vendor object.
		 * @return void
		 */
		public static function set_vendor_capabilities_for_user( $user, $vendor ) {
			if ( ! $user instanceof WP_User ) {
				$user = get_user_by( 'id', absint( $user ) );
			}

			if ( ! $user || ! $user->exists() ) {
				return;
			}

			// Prevent to set it twice.
			if ( ! in_array( self::ROLE_NAME, $user->roles, true ) ) {

				$user->add_role( self::ROLE_NAME );
				foreach ( array( 'customer', 'subscriber' ) as $role ) {
					if ( in_array( $role, $user->roles, true ) ) {
						$user->remove_role( $role );
					}
				}
			}

			update_user_meta( $user->ID, yith_wcmv_get_user_meta_key(), $vendor->get_id() );
			if ( $user->ID === $vendor->get_owner() ) {
				update_user_meta( $user->ID, yith_wcmv_get_user_meta_owner(), $vendor->get_id() );
			}

			// Set publish_products based on option.
			if ( 'yes' === $vendor->get_meta( 'skip_review' ) ) {
				$user->has_cap( 'publish_products' ) || $user->add_cap( 'publish_products' );
			} else {
				$user->has_cap( 'publish_products' ) && $user->remove_cap( 'publish_products' );
			}
		}

		/**
		 * Remove capabilities for given vendor
		 *
		 * @since  4.0.0
		 * @param YITH_Vendor $vendor The vendor object.
		 * @return void
		 */
		public static function remove_vendor_capabilities( $vendor ) {
			$admins = $vendor->get_admins();
			foreach ( array_filter( $admins ) as $user_id ) {
				self::remove_vendor_capabilities_for_user( $user_id );
			}

			do_action( 'yith_wcmv_removed_vendor_capabilities', $vendor );
		}

		/**
		 * Remove vendor capabilities for given user
		 *
		 * @since  4.0.0
		 * @param integer|WP_User $user Could be either the user ID or the WP_User object.
		 * @return void
		 */
		public static function remove_vendor_capabilities_for_user( $user ) {

			if ( ! $user instanceof WP_User ) {
				$user = get_user_by( 'id', absint( $user ) );
			}

			if ( ! $user || ! $user->exists() ) {
				return;
			}

			// Backward compatibility.
			delete_user_meta( $user->ID, yith_wcmv_get_user_meta_key() );
			delete_user_meta( $user->ID, yith_wcmv_get_user_meta_owner() );

			$user->remove_cap( 'publish_products' );
			do_action( 'yith_wcmv_remove_vendor_extra_cap', $user );
			$user->remove_role( self::ROLE_NAME );
			// Set to customer.
			if ( empty( $user->get_role_caps() ) ) {
				$user->add_role( 'customer' );
			}
		}

		/**
		 * Plugin Setup
		 *
		 * @fire   register_activation_hook
		 * @since  1.6.5
		 * @param string $method The method to call.
		 * @return void
		 */
		public static function setup( $method = 'remove_role' ) {

			if ( 'remove_role' === $method ) {
				self::remove_role_to_vendors();
			} else {
				self::set_role_to_vendors();
			}

			do_action( 'yith_wcmv_after_setup' );
		}

		/**
		 * Add role to all vendor admins and owner
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function set_role_to_vendors() {
			// Make sure the taxonomy is registered or this process will fail.
			// Useful when call this method out of the default installation process.
			if ( did_action( 'init' ) && ! taxonomy_exists( YITH_Vendors_Taxonomy::TAXONOMY_NAME ) ) {
				YITH_Vendors_Taxonomy::register_taxonomy();
			}

			$vendors = YITH_Vendors_Factory::query( array( 'number' => -1 ) );
			foreach ( $vendors as $vendor ) {
				if ( $vendor && $vendor->is_valid() ) {
					$admins = $vendor->get_admins( 'all' );
					foreach ( $admins as $admin ) {
						if ( $admin instanceof WP_User ) {
							$admin->add_role( self::ROLE_NAME );
						}
					}
				}
			}
		}

		/**
		 * Remove role to all vendor admins and owner
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function remove_role_to_vendors() {
			// Make sure the taxonomy is registered or this process will fail.
			// Useful when call this method out of the default installation process.
			if ( did_action( 'init' ) && ! taxonomy_exists( YITH_Vendors_Taxonomy::TAXONOMY_NAME ) ) {
				YITH_Vendors_Taxonomy::register_taxonomy();
			}

			$vendors = YITH_Vendors_Factory::query( array( 'number' => -1 ) );
			foreach ( $vendors as $vendor ) {
				if ( $vendor && $vendor->is_valid() ) {
					$admins = $vendor->get_admins( 'all' );
					foreach ( $admins as $admin ) {
						if ( $admin instanceof WP_User ) {
							$admin->remove_role( self::ROLE_NAME );
						}
					}
				}
			}
		}

		/**
		 * Update role capabilities
		 *
		 * @since  4.0.0
		 * @return void
		 */
		public static function update_capabilities() {
			// The easiest way to update capabilities is to remove the role and add it again.
			wp_roles()->remove_role( self::ROLE_NAME );
			self::add_role();
		}
	}
}
