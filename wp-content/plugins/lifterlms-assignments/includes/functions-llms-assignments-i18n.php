<?php
/**
 * Internationalization Functions
 *
 * @package LifterLMS_Assignments/Functions
 *
 * @since 2.2.0
 * @version 2.2.0
 */

/**
 * Retrieve permalinks structure to verify if they are set, and any new defaults are saved
 *
 * @since 2.2.0
 *
 * @return void
 */
function llms_assignments_verify_permalinks() {

	if ( ! get_option( 'lifterlms_assignment_permalinks' ) && function_exists( 'llms_switch_to_site_locale' ) ) {
		llms_switch_to_site_locale( 'lifterlms-assignments', LLMS_ASSIGNMENTS_PLUGIN_DIR, 'i18n' );

		// Retrieve the permalink structure, which will also save the default structure if it's not set.
		llms_get_assignments_permalink_structure();

		llms_restore_locale( 'lifterlms-assignments', LLMS_ASSIGNMENTS_PLUGIN_DIR, 'i18n' );
	}

}

/**
 * Retrieve the current permalink slugs for assignments
 *
 * @since 2.2.0
 * @return array
 */
function llms_get_assignments_permalink_structure() {

	$saved_permalinks = (array) get_option( 'llms_assignments_permalinks', array() );

	$permalinks = wp_parse_args(
		// Remove false or empty entries so we can use the default values.
		array_filter( $saved_permalinks ),
		array(
			'assignment_base' => _x( 'assignment', 'assignment url slug', 'lifterlms-assignments' ),
		)
	);

	array_filter( $permalinks, 'untrailingslashit' );

	// Only automatically save if we have the updated version of lifter core.
	if ( $saved_permalinks !== $permalinks && function_exists( 'llms_switch_to_site_locale' ) ) {
		update_option( 'llms_assignments_permalinks', $permalinks );
	}

	return $permalinks;

}

/**
 * Set the assignments permalink structure and only allow keys we know about.
 *
 * @param array $permalinks Array of permalink structure.
 *
 * @since 2.2.0
 * @return void
 */
function llms_set_assignments_permalink_structure( $permalinks ) {
	$defaults = llms_get_assignments_permalink_structure();

	$permalinks = wp_parse_args(
		// Only allow values whose keys are in the defaults array.
		array_intersect_key( $permalinks, $defaults ),
		$defaults
	);

	array_filter( $permalinks, 'untrailingslashit' );

	update_option( 'llms_assignments_permalinks', $permalinks );
}
