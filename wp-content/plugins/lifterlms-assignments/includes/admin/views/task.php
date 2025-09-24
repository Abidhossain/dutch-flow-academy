<?php
/**
 * Task List Item View
 *
 * @package LifterLMS_Assignments/Admin/Views
 * @since   1.0.0-beta.1
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;
?>
<script type="text/html" id="tmpl-llms-assignment-task-template">

	<label class="llms-task-marker">
		<i class="fa fa-check" aria-hidden="true"></i>
		<b>{{{ data.get( 'marker' ) }}}</b>
	</label>

	<div class="llms-input-wrapper">
		<div class="llms-editable-title llms-input-formatting" data-attribute="title" data-formatting="bold,italic,underline,link" data-placeholder="<?php esc_attr_e( 'Enter a task...', 'lifterlms-assignments' ); ?>">{{{ data.get( 'title' ) }}}</div>
	</div>

	<div class="llms-action-icons">

		<a class="llms-action-icon danger tip--top-left" data-tip="<?php _e( 'Delete Task', 'lifterlms-assignments' ); ?>" href="#llms-trash-model" tabindex="-1">
			<i class="fa fa-trash" aria-hidden="true"></i>
			<span class="screen-reader-text"><?php _e( 'Delete Task', 'lifterlms-assignments' ); ?></span>
		</a>

	</div>

</script>
