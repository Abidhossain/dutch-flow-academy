<?php
/**
 * YITH Vendors Announcement Post Type Class
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

if ( ! class_exists( 'YITH_Vendors_Announcement' ) ) {
	/**
	 * Announcement post type
	 *
	 * @class      YITH_Vendors_Announcement
	 * @since      Version 4.0.0
	 * @author     YITH
	 * @package YITH\MultiVendor
	 */
	class YITH_Vendors_Announcement extends YITH_Vendors_Announcement_Data_Store {

		/**
		 * YITH_Vendors_Announcement constructor.
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @param integer $announcement_id (Optional) The announcement ID. Default is 0.
		 * @return YITH_Vendors_Announcement|false The current object, false otherwise.
		 */
		public function __construct( $announcement_id = 0 ) {
			try {
				$this->set_id( $announcement_id );
				if ( $this->get_id() ) {
					$this->read();
				}
			} catch ( Exception $e ) {
				return false;
			}

			return $this;
		}

		/**
		 * Get announcement title
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @return string
		 */
		public function get_title() {
			return $this->get_data( 'title' );
		}

		/**
		 * Get announcement content
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @return string
		 */
		public function get_content() {
			return $this->get_data( 'content' );
		}

		/**
		 * Get announcement date
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @param string   $type The type of date to return.
		 * @param int|bool $gmt  Optional. Whether to use GMT timezone. Default false.
		 * @return string
		 */
		public function get_date( $type = 'mysql', $gmt = 0 ) {
			$timestamp = strtotime( $this->get_data( 'date' ) );
			// Don't use non-GMT timestamp, unless you know the difference and really need to.
			if ( $gmt ) {
				$timestamp += (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
			}

			if ( 'timestamp' === $type || 'U' === $type ) {
				return $timestamp;
			}

			if ( 'mysql' === $type ) {
				$type = 'Y-m-d H:i:s';
			}

			$timezone = $gmt ? new DateTimeZone( 'UTC' ) : wp_timezone();
			$datetime = new DateTime( $timestamp, $timezone );

			return $datetime->format( $type );
		}

		/**
		 * Set announcement title
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @param string $value The announcement title value.
		 * @return void
		 */
		public function set_title( $value = '' ) {
			$this->set_data( 'title', $value );
		}

		/**
		 * Get announcement active
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @return string
		 */
		public function get_active() {
			return $this->get_meta( 'active' );
		}

		/**
		 * Get announcement show to value
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @return string
		 */
		public function get_show_to() {
			return $this->get_meta( 'show_to' );
		}

		/**
		 * Get an array of vendors associated to the announcement
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @return array
		 */
		public function get_show_vendors() {
			$vendors = $this->get_meta( 'show_vendors' );
			if ( empty( $vendors ) || ! is_array( $vendors ) ) {
				return array();
			}

			$formatted_vendors = array();
			foreach ( $vendors as $vendor_id ) {
				$vendor = yith_wcmv_get_vendor( $vendor_id );
				if ( $vendor && $vendor->exists() ) {
					$formatted_vendors[ $vendor->get_id() ] = $vendor->get_name();
				}
			}

			return $formatted_vendors;
		}

		/**
		 * Set announcement content
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @param string $value The announcement title value.
		 * @return void
		 */
		public function set_content( $value = '' ) {
			$this->set_data( 'content', wp_kses_post( $value ) );
		}

		/**
		 * Set announcement active
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @param string $value The announcement active value.
		 * @return void
		 */
		public function set_active( $value = '' ) {
			$this->set_meta( 'active', yith_plugin_fw_is_true( $value ) ? 'yes' : 'no' );
		}

		/**
		 * Check if the announcement is active
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @return boolean
		 */
		public function is_active() {
			return 'yes' === $this->get_active();
		}

		/**
		 * Check if the announcement is dismissible
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @return boolean
		 */
		public function is_dismissible() {
			return 'yes' === $this->get_meta( 'dismissible' );
		}

		/**
		 * Check if current announcement is scheduled.
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @return boolean
		 */
		public function is_scheduled() {
			if ( 'auto' === $this->get_meta( 'scheduled' ) ) {
				$now   = current_time( 'timestamp' );
				$start = $this->get_meta( 'scheduled_start' );
				$end   = $this->get_meta( 'scheduled_end' );

				if ( empty( $start ) || empty( $end ) || strtotime( $start ) > $now || strtotime( $end ) < $now ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Check if current announcement is valid. If passed check conditions for vendor also.
		 *
		 * @since  4.0.0
		 * @author YITH
		 * @param YITH_Vendor $vendor (Optional) The vendor object to check. Default is false.
		 * @return boolean
		 */
		public function is_valid( $vendor = false ) {
			$valid = true;
			// Check for schedule first.
			if ( ! $this->is_scheduled() ) {
				$valid = false;
			}

			$show_to = $this->get_show_to();
			if ( $vendor instanceof YITH_Vendor && 'all' !== $show_to ) {
				if ( 'specific-vendors' === $show_to ) {
					$vendors = $this->get_meta( 'show_vendors' );
					$vendors = ! empty( $vendors ) ? array_map( 'absint', $vendors ) : array();
					if ( ! in_array( $vendor->get_id(), $vendors, true ) ) {
						$valid = false;
					}
				} elseif ( 'specific-criteria' === $show_to ) {
					$valid    = false;
					$criteria = $this->get_meta( 'show_criteria' );

					switch ( $criteria ) {
						case 'no-privacy-policy':
							$valid = ! $vendor->has_privacy_policy_accepted();
							break;

						case 'no-terms-condition':
							$valid = ! $vendor->has_terms_and_conditions_accepted();
							break;

						case 'no-vat':
							$valid = empty( $vendor->get_meta( 'vat' ) );
							break;

						case 'new-vendor':
							// A new vendor must be registered at least 24 hours before the announcement is added.
							$valid = $this->get_date( 'U' ) < ( intval( $vendor->get_registration_date( 'timestamp' ) ) + ( 24 * HOUR_IN_SECONDS ) );
							break;

						case 'sales-number':
							$number = (int) $this->get_meta( 'sales_number_criteria' );
							$valid  = $vendor->get_number_of_sales() >= $number;
							break;

						case 'sales-value':
							$stats  = YITH_Vendors_Commissions_Factory::get_stats(
								array(
									'stats'     => array( 'commissions_store_gross_total' ),
									'vendor_id' => $vendor->get_id(),
									'group_by'  => '',
								)
							);
							$amount = isset( $stats['commissions_store_gross_total'] ) ? (float) $stats['commissions_store_gross_total'] : 0;
							$valid  = $amount >= (float) $this->get_meta( 'sales_amount_criteria' );

							break;
					}

					$criteria = str_replace( '-', '_', $criteria );
					$valid    = apply_filters( "yith_wcmv_announcements_is_{$criteria}_criteria_satisfied", $valid, $vendor, $this );
				}
			}

			return apply_filters( 'yith_wcmv_announcements_is_valid', $valid, $vendor, $this );
		}
	}
}
