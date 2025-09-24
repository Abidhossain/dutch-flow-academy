<?php
/**
 * LifterLMS Single Assignment After
 *
 * @package LifterLMS_Assignments/Templates
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hook: llms_single_assignment_after_summary.
 *
 * @hooked  llms_assignments_template_content - 10
 * @hooked  llms_assignments_template_footer - 20
 */
do_action( 'llms_single_assignment_after_summary' );
