<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Update functions
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Enable the option to show the update modal instead of the "welcome" one.
 */
function yith_ywgc_update_show_update_modal_3() {
	update_option( 'yith-ywgc-welcome-modal', 'update' );
}

add_action( 'init', 'yith_wcgc_delete_legacy_cron_jon' );

/**
 * Delete legacy cron job
 */
function yith_wcgc_delete_legacy_cron_jon() {
	$checker = get_option( 'yith_wcgc_legacy_cron_job_deleted_v4.2.0', 'no' );

	if ( 'no' === $checker ) {
		wp_clear_scheduled_hook( 'ywgc_start_gift_cards_sending' );
	}

	update_option( 'yith_wcgc_legacy_cron_job_deleted', 'yes' );
}
