<?php
/**
 * LifterLMS Single Assignment Before
 *
 * @package LifterLMS_Assignments/Templates
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.2
 */

defined( 'ABSPATH' ) || exit;

llms_print_notices();

/**
 * Hook: llms_single_assignment_before_summary.
 *
 * @hooked  llms_assignments_template_return_to_lesson_link - 10
 * @hooked  llms_assignments_template_results - 15
 * @hooked  llms_assignments_template_video_embed - 20
 * @hooked  llms_assignments_template_audio_embed - 25
 */
do_action( 'llms_single_assignment_before_summary' );
