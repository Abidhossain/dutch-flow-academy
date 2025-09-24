<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function monsterinsights_ads_output_after_script_old( $options ) {
	$track_user = monsterinsights_track_user();
	$ua         = monsterinsights_get_ua_to_output();

	if ( $track_user && $ua ) {
		ob_start();
		echo PHP_EOL;
		?>
        <!-- MonsterInsights Ads Tracking -->
        <script type="text/javascript" data-cfasync="false">
			<?php
			echo "window.google_analytics_uacct = '" . $ua . "';" . PHP_EOL . PHP_EOL;
			?>
        </script>
        <!-- End MonsterInsights Ads Tracking -->
		<?php
		echo PHP_EOL;
		echo ob_get_clean();
	}

}

add_action( 'monsterinsights_tracking_after_analytics', 'monsterinsights_ads_output_after_script_old' );

/**
 * Add Ads Conversion ID to gtag.
 *
 * @uses Hook: monsterinsights_frontend_tracking_gtag_after_pageview
 * @see /plugins/monsterinsights/includes/frontend/tracking/class-tracking-gtag.php Line: 255
 *
 * @since 17.5.0
 *
 * @return string
 */
function add_conversion_id_to_gtag_tracking() {
	$aw_id = esc_attr( monsterinsights_get_option( 'gtag_ads_conversion_id' ) );

	if ( ! empty( $aw_id ) ) {
		echo "__gtagTracker( 'config', '" . $aw_id . "' );";
	}
}

add_action( 'monsterinsights_frontend_tracking_gtag_after_pageview', 'add_conversion_id_to_gtag_tracking' );
